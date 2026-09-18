<?php
namespace App\Helpers;

use PDO;

/**
 * AbandonedCartHelper
 * ===================
 * Quản lý phát hiện và phục hồi giỏ hàng bị bỏ quên.
 *
 * Lịch trình reminder:
 *   Stage 1: 1 giờ  sau khi cart được tạo  → "Bạn quên giỏ hàng?"
 *   Stage 2: 24 giờ sau Reminder 1         → "Sản phẩm vẫn đang chờ bạn"
 *   Stage 3: 72 giờ sau Reminder 2         → "Ưu đãi đặc biệt cuối cùng"
 */
class AbandonedCartHelper {

    /** Khoảng thời gian (giây) trước khi 1 cart được coi là abandoned */
    const ABANDONED_AFTER = 3600; // 1 giờ

    /** Khoảng thời gian giữa các lần reminder (giây) */
    const REMINDER_INTERVALS = [
        1 => 3600,      // Stage 1: 1h sau abandoned
        2 => 86400,     // Stage 2: 24h sau reminder 1
        3 => 259200,    // Stage 3: 72h sau reminder 2
    ];

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ============================================================
    //  TRACKING — Gọi mỗi khi user thay đổi giỏ hàng
    // ============================================================

    /**
     * Ghi nhận / cập nhật hoạt động giỏ hàng
     */
    public function track(?int $userId, string $sessionId, array $cart): void {
        if (empty($cart)) return;

        // Tính tổng tiền
        $total = 0;
        $count = 0;
        foreach ($cart as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
            $count += $item['quantity'] ?? 1;
        }

        // Lấy thông tin user nếu có
        $userName  = '';
        $userEmail = '';
        $userPhone = '';
        if ($userId) {
            $stmt = $this->db->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $userName  = $user['full_name'] ?? '';
                $userEmail = $user['email'] ?? '';
                $userPhone = $user['phone'] ?? '';
            }
        }

        // Kiểm tra đã có record chưa
        $existing = null;
        if ($userId) {
            $stmt = $this->db->prepare("SELECT id, status FROM abandoned_carts WHERE user_id = ? AND status IN ('active','contacted')");
            $stmt->execute([$userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$existing && $sessionId) {
            $stmt = $this->db->prepare("SELECT id, status FROM abandoned_carts WHERE session_id = ? AND status IN ('active','contacted')");
            $stmt->execute([$sessionId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $cartData  = json_encode($cart, JSON_UNESCAPED_UNICODE);
        $now       = date('Y-m-d H:i:s');

        if ($existing) {
            // Cập nhật — reset đồng hồ abandoned
            $stmt = $this->db->prepare("
                UPDATE abandoned_carts
                SET cart_data  = :cart,
                    cart_total = :total,
                    item_count = :count,
                    user_name  = :name,
                    user_email = :email,
                    user_phone = :phone,
                    session_id = :sid,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':cart'   => $cartData,
                ':total'  => $total,
                ':count'  => $count,
                ':name'   => $userName,
                ':email'  => $userEmail,
                ':phone'  => $userPhone,
                ':sid'    => $sessionId,
                ':id'     => $existing['id'],
            ]);
        } else {
            // Tạo mới
            $stmt = $this->db->prepare("
                INSERT INTO abandoned_carts
                    (user_id, session_id, cart_data, cart_total, item_count,
                     user_name, user_email, user_phone, status, created_at, updated_at)
                VALUES (:uid, :sid, :cart, :total, :count, :name, :email, :phone, 'active', NOW(), NOW())
            ");
            $stmt->execute([
                ':uid'   => $userId,
                ':sid'   => $sessionId,
                ':cart'  => $cartData,
                ':total' => $total,
                ':count' => $count,
                ':name'  => $userName,
                ':email' => $userEmail,
                ':phone' => $userPhone,
            ]);
        }
    }

    /**
     * Xoá tracking khi user checkout thành công
     */
    public function clearCart(int $userId, string $sessionId): void {
        $this->db->prepare("
            UPDATE abandoned_carts
            SET status = 'recovered', recovered_at = NOW()
            WHERE (user_id = ? OR session_id = ?)
              AND status IN ('active','contacted')
        ")->execute([$userId, $sessionId]);
    }

    // ============================================================
    //  DETECTION — Tìm giỏ hàng cần gửi reminder
    // ============================================================

    /**
     * Lấy danh sách giỏ hàng abandoned cần gửi reminder
     * @param int $stage 1, 2, hoặc 3
     * @return array
     */
    public function getCartsForReminder(int $stage): array {
        if ($stage < 1 || $stage > 3) return [];

        $interval = self::REMINDER_INTERVALS[$stage];

        if ($stage === 1) {
            // Lần đầu: cart active > 1h và chưa có reminder nào
            $sql = "
                SELECT ac.*
                FROM abandoned_carts ac
                WHERE ac.status = 'active'
                  AND ac.reminder_count = 0
                  AND ac.user_email IS NOT NULL
                  AND ac.user_email != ''
                  AND TIMESTAMPDIFF(SECOND, ac.created_at, NOW()) >= :interval
                ORDER BY ac.created_at ASC
                LIMIT 30
            ";
        } else {
            // Lần 2, 3: cart contacted và đã qua interval kể từ lần reminder cuối
            $expectedPrevCount = $stage - 1;
            $sql = "
                SELECT ac.*
                FROM abandoned_carts ac
                WHERE ac.status IN ('active','contacted')
                  AND ac.reminder_count = :prev
                  AND ac.user_email IS NOT NULL
                  AND ac.user_email != ''
                  AND TIMESTAMPDIFF(SECOND, ac.last_reminder_at, NOW()) >= :interval
                ORDER BY ac.last_reminder_at ASC
                LIMIT 30
            ";
        }

        $stmt = $this->db->prepare($sql);
        if ($stage === 1) {
            $stmt->bindValue(':interval', $interval, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':prev', $expectedPrevCount, PDO::PARAM_INT);
            $stmt->bindValue(':interval', $interval, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    //  REMINDER QUEUEING
    // ============================================================

    /**
     * Xếp hàng đợi email reminder cho 1 giỏ hàng
     * @return bool
     */
    public function queueReminder(array $cart, int $stage): bool {
        if (empty($cart['user_email'])) return false;

        try {
            $this->db->beginTransaction();

            $subject  = $this->getSubject($stage);
            $body     = $this->buildEmailBody($cart, $stage);

            // Lưu vào email_queue
            $stmt = $this->db->prepare("
                INSERT INTO email_queue (to_email, to_name, subject, body, status, scheduled_at)
                VALUES (:email, :name, :subject, :body, 'pending', NOW())
            ");
            $stmt->execute([
                ':email'   => $cart['user_email'],
                ':name'    => $cart['user_name'] ?? '',
                ':subject' => $subject,
                ':body'    => $body,
            ]);

            // Cập nhật reminder_count + status
            $newCount = (int)$cart['reminder_count'] + 1;

            $stmt2 = $this->db->prepare("
                UPDATE abandoned_carts
                SET reminder_count   = :cnt,
                    last_reminder_at = NOW(),
                    status           = 'contacted'
                WHERE id = :id
            ");
            $stmt2->execute([
                ':cnt' => $newCount,
                ':id'  => $cart['id'],
            ]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('AbandonedCart queueReminder failed: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    //  EMAIL TEMPLATES
    // ============================================================

    private function getSubject(int $stage): string {
        return match ($stage) {
            1 => '🛒 Bạn đã bỏ quên giỏ hàng? — Ban Linh Kiện',
            2 => '⏳ Sản phẩm trong giỏ vẫn đang chờ bạn — Ban Linh Kiện',
            3 => '🎁 Ưu đãi đặc biệt: Giỏ hàng của bạn sắp hết hạn!',
            default => '🛒 Nhắc nhở giỏ hàng — Ban Linh Kiện',
        };
    }

    /**
     * Xây dựng HTML email reminder
     */
    public function buildEmailBody(array $cart, int $stage): string {
        $items      = json_decode($cart['cart_data'], true) ?: [];
        $totalFmt   = number_format((float)$cart['cart_total'], 0, ',', '.');
        $cartUrl    = BASE_URL . 'giohang.php?utm_source=email&utm_medium=abandoned_cart&utm_campaign=reminder_' . $stage;
        $shopUrl    = BASE_URL;
        $userName   = htmlspecialchars($cart['user_name'] ?? 'bạn');
        $itemCount  = (int)$cart['item_count'];
        $stageEmoji = match($stage) { 1 => '🛒', 2 => '⏳', 3 => '🎁', default => '📬' };
        $stageTitle = match($stage) {
            1 => 'Bạn đã bỏ quên giỏ hàng?',
            2 => 'Giỏ hàng vẫn đang chờ bạn!',
            3 => 'Đừng bỏ lỡ — Ưu đãi cuối!',
            default => 'Bạn còn sản phẩm chưa mua',
        };
        $urgencyText = match($stage) {
            1 => 'Đừng lo, chúng tôi đã giữ giỏ hàng cho bạn!',
            2 => 'Sản phẩm bạn yêu thích đang chờ — đừng để hết hàng!',
            3 => 'Đây là email nhắc cuối cùng. Giỏ hàng của bạn sẽ được giải phóng sau 72 giờ nữa.',
            default => '',
        };

        // Build items table
        $itemsHtml = '';
        $maxShow = 4;
        $shown = 0;
        foreach ($items as $item) {
            if ($shown >= $maxShow) break;
            $name  = htmlspecialchars($item['name'] ?? 'Sản phẩm');
            $qty   = (int)($item['quantity'] ?? 1);
            $price = number_format((float)($item['price'] ?? 0), 0, ',', '.');
            $img   = '';
            if (!empty($item['image'])) {
                if (strpos($item['image'], 'data:') === 0 || strpos($item['image'], 'http') === 0) {
                    $img = $item['image'];
                } else {
                    $img = BASE_URL . 'public/img/products/' . $item['image'];
                }
            }
            $imgHtml = $img ? "<img src=\"{$img}\" style=\"width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;flex-shrink:0;\" alt=\"{$name}\">" : '';
            $itemsHtml .= "
            <tr>
                <td style=\"padding:10px 12px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;\">
                    {$imgHtml}
                    <span style=\"font-size:13px;font-weight:600;color:#1e293b;\">{$name}</span>
                </td>
                <td style=\"padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:13px;color:#64748b;\">x{$qty}</td>
                <td style=\"padding:10px 12px;border-bottom:1px solid #f1f5f9;text-align:right;font-size:13px;font-weight:600;color:#475569;\">{$price}₫</td>
            </tr>";
            $shown++;
        }
        if (count($items) > $maxShow) {
            $itemsHtml .= "<tr><td colspan=\"3\" style=\"padding:8px 12px;text-align:center;font-size:12px;color:#94a3b8;\">+ " . (count($items) - $maxShow) . " sản phẩm khác</td></tr>";
        }

        // Discount incentive for stage 2 & 3
        $discountHtml = '';
        if ($stage >= 2) {
            $discountCode = 'WELCOME' . rand(10, 99);
            $discountHtml = "
            <div style=\"background:linear-gradient(135deg,#fef3c7,#fde68a);border:2px dashed #f59e0b;border-radius:12px;padding:16px;margin:16px 0;text-align:center;\">
                <p style=\"margin:0 0 6px;font-size:16px;\">🎫</p>
                <p style=\"margin:0 0 4px;font-size:14px;font-weight:700;color:#92400e;\">MÃ GIẢM GIÁ ĐẶC BIỆT</p>
                <p style=\"margin:0 0 8px;font-size:12px;color:#78350f;\">Dành riêng cho bạn — hoàn tất đơn hàng ngay!</p>
                <span style=\"display:inline-block;background:#1e293b;color:#38bdf8;padding:8px 24px;border-radius:8px;font-size:18px;font-weight:900;letter-spacing:2px;\">{$discountCode}</span>
                <p style=\"margin:8px 0 0;font-size:11px;color:#78350f;\">*Giảm 5% tối đa 50.000₫. Áp dụng cho đơn hàng &ge;500.000₫</p>
            </div>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:30px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:white;border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.08);">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px 40px;text-align:center;">
            <p style="margin:0 0 8px;font-size:42px;">{$stageEmoji}</p>
            <h1 style="margin:0;color:white;font-size:24px;font-weight:800;">{$stageTitle}</h1>
            <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;line-height:1.6;">
                Xin chào <strong style="color:#fff;">{$userName}</strong>
            </p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:28px 40px 20px;">
            <p style="margin:0 0 16px;font-size:14px;color:#475569;line-height:1.7;">
                {$urgencyText}
            </p>

            <!-- Cart summary -->
            <div style="background:#f8fafc;border-radius:12px;padding:16px 20px;margin-bottom:16px;border:1px solid #e2e8f0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:14px;font-weight:600;color:#1e293b;">
                        📦 {$itemCount} sản phẩm trong giỏ
                    </span>
                    <span style="font-size:16px;font-weight:800;color:#6366f1;">
                        {$totalFmt}₫
                    </span>
                </div>
            </div>

            <!-- Items table -->
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:20px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">Sản phẩm</th>
                        <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">SL</th>
                        <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">Giá</th>
                    </tr>
                </thead>
                <tbody>{$itemsHtml}</tbody>
            </table>

            {$discountHtml}

            <!-- CTA -->
            <a href="{$cartUrl}"
               style="display:block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;padding:16px 24px;border-radius:12px;text-align:center;text-decoration:none;font-size:16px;font-weight:700;margin:20px 0;box-shadow:0 8px 24px rgba(99,102,241,.3);">
                🛒 Thanh toán ngay
            </a>

            <div style="text-align:center;margin-top:12px;">
                <a href="{$shopUrl}" style="color:#64748b;font-size:13px;text-decoration:none;">← Tiếp tục mua sắm</a>
            </div>
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
            <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">
                Ban Linh Kiện · Hotline: 1800 6975
            </p>
            <p style="margin:0;font-size:11px;color:#94a3b8;">
                Email này được gửi tự động vì bạn có sản phẩm trong giỏ hàng.
                <br>Nếu không còn nhu cầu, vui lòng bỏ qua email này.
            </p>
        </td>
    </tr>

</table>
</td></tr></table>

</body>
</html>
HTML;
    }

    // ============================================================
    //  STATISTICS (cho admin)
    // ============================================================

    public function getStats(): array {
        $stats = [];
        $rows = $this->db->query("SELECT status, COUNT(*) AS cnt FROM abandoned_carts GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $stats[$r['status']] = (int)$r['cnt'];
        }
        $stats['total'] = array_sum($stats);

        // Doanh thu tiềm năng
        $stmt = $this->db->query("SELECT COALESCE(SUM(cart_total), 0) AS potential FROM abandoned_carts WHERE status IN ('active','contacted')");
        $stats['potential_revenue'] = (float)$stmt->fetchColumn();

        return $stats;
    }

    /**
     * Lấy tổng số cart đang active/contacted
     */
    public function getActiveCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM abandoned_carts WHERE status IN ('active','contacted')")->fetchColumn();
    }
}
