<?php
/**
 * Abandoned Cart Worker
 * =====================
 * Cron job script tự động phát hiện giỏ hàng bị bỏ quên và gửi email nhắc nhở.
 *
 * Lịch trình reminder:
 *   Stage 1: 1 giờ  sau khi cart tạo  → "Bạn quên giỏ hàng?"
 *   Stage 2: 24 giờ sau Reminder 1   → "Sản phẩm đang chờ bạn"
 *   Stage 3: 72 giờ sau Reminder 2   → "Ưu đãi cuối cùng"
 *
 * CÁCH CÀI ĐẶT CRON:
 *
 * Linux (mỗi 15 phút):
 *   (mỗi 15 phút) php /path/to/abandoned_cart_worker.php >> /var/log/abandoned_cart.log 2>&1
 *
 * Windows Task Scheduler:
 *   Program: C:\laragon\bin\php\php8.x\php.exe
 *   Arguments: C:\laragon\www\Ban_linh_kien\abandoned_cart_worker.php
 *   Schedule: Every 15 minutes
 *
 * HTTP trigger (dùng để test):
 *   https://banlinh.vn/abandoned_cart_worker.php?token=your_secret_token
 */

// ── Chỉ chạy từ CLI hoặc HTTP với token bảo mật ──
$isCLI = (php_sapi_name() === 'cli');
$isHTTP = !$isCLI;

if ($isHTTP) {
    $secret = 'banlinh_ac_secret_2026';
    if (($_GET['token'] ?? '') !== $secret) {
        http_response_code(403);
        die('403 Forbidden');
    }
}

// ── Load dependencies ──
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = \App\Core\Database::getInstance();
$helper = new \App\Helpers\AbandonedCartHelper($db);

// ── Logging ──
$log = function($msg) use ($isCLI) {
    $prefix = '[' . date('Y-m-d H:i:s') . '] ';
    if ($isCLI) {
        fwrite(STDOUT, $prefix . $msg . "\n");
    }
    error_log($prefix . $msg);
};

$log("═══ ABANDONED CART WORKER START ═══");

$totalSent = 0;
$totalErrors = 0;

// ── Xử lý từng stage ──
for ($stage = 1; $stage <= 3; $stage++) {
    $carts = $helper->getCartsForReminder($stage);

    if (empty($carts)) {
        $log("Stage {$stage}: Không có giỏ hàng cần gửi reminder.");
        continue;
    }

    $log("Stage {$stage}: Tìm thấy " . count($carts) . " giỏ hàng cần gửi reminder.");

    foreach ($carts as $cart) {
        try {
            $queued = $helper->queueReminder($cart, $stage);
            if ($queued) {
                $totalSent++;
                $log("  ✅ [Stage {$stage}] Đã xếp hàng email → {$cart['user_email']} | Giỏ #{$cart['id']} ({$cart['item_count']} SP - " . number_format((float)$cart['cart_total'], 0, ',', '.') . "₫)");
            } else {
                $totalErrors++;
                $log("  ❌ [Stage {$stage}] Không thể xếp hàng email → {$cart['user_email']}");
            }
        } catch (\Exception $e) {
            $totalErrors++;
            $log("  ❌ [Stage {$stage}] Lỗi: " . $e->getMessage());
        }

        // Tránh spam server mail
        usleep(100000); // 0.1 giây
    }
}

// ── Garbage collection: Đánh dấu các cart quá cũ (>= 30 ngày) là expired ──
try {
    $expiredStmt = $db->prepare("
        UPDATE abandoned_carts
        SET status = 'expired'
        WHERE status IN ('active','contacted')
          AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $expiredStmt->execute();
    $expiredCount = $expiredStmt->rowCount();
    if ($expiredCount > 0) {
        $log("🗑️ Đã đánh dấu {$expiredCount} giỏ hàng quá hạn (>30 ngày) là expired.");
    }
} catch (\Exception $e) {
    $log("⚠️ Lỗi garbage collection: " . $e->getMessage());
}

// ── Kết quả ──
$log("═══ KẾT QUẢ ═══");
$log("  Email đã xếp hàng: {$totalSent}");
$log("  Email lỗi:         {$totalErrors}");
$log("═══ ABANDONED CART WORKER END ═══");

if ($isHTTP) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'sent'    => $totalSent,
        'errors'  => $totalErrors,
        'time'    => date('Y-m-d H:i:s'),
    ]);
}
