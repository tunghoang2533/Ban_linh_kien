<?php
/**
 * API kiểm tra trạng thái thanh toán của đơn hàng (dùng cho polling real-time trên giao diện)
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

use App\Core\Database;

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, status, payment_status, total_amount FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $isPaid = ($order['payment_status'] === 'paid');

    // Nếu đơn chưa thanh toán và có cấu hình SEPAY_API_KEY, chủ động kiểm tra qua SePAY API (hoạt động ngay trên localhost không cần Ngrok/PowerShell)
    $sepayApiKey = getenv('SEPAY_API_KEY') ?: '';
    if (!$isPaid && !empty($sepayApiKey)) {
        // Giới hạn tần suất gọi SePAY tối đa 1 lần mỗi 4 giây để tránh vượt rate limit
        $cacheFile = sys_get_temp_dir() . '/sepay_poll_' . $orderId . '.tmp';
        $now = time();
        $lastPoll = file_exists($cacheFile) ? (int)@file_get_contents($cacheFile) : 0;

        if (($now - $lastPoll) >= 4) {
            @file_put_contents($cacheFile, $now);

            $transactions = [];
            $endpoints = [
                'https://userapi.sepay.vn/v2/transactions?per_page=20',
                'https://my.sepay.vn/userapi/transactions/list?limit=20'
            ];

            foreach ($endpoints as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $sepayApiKey,
                    'Content-Type: application/json'
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && !empty($response)) {
                    $json = json_decode($response, true);
                    $list = $json['data'] ?? ($json['messages'] ?? ($json['transactions'] ?? []));
                    if (is_array($list) && !empty($list)) {
                        $transactions = $list;
                        break;
                    }
                }
            }

            // Duyệt danh sách giao dịch tìm mã đơn DH{orderId}
            $orderTotal = (float)$order['total_amount'];
            foreach ($transactions as $tx) {
                $content = $tx['transaction_content'] ?? ($tx['content'] ?? ($tx['description'] ?? ''));
                $amountIn = (float)($tx['amount_in'] ?? ($tx['transferAmount'] ?? ($tx['amount'] ?? 0)));

                if ($amountIn >= $orderTotal && preg_match('/DH\s*' . $orderId . '\b/i', $content)) {
                    // Tìm thấy giao dịch thanh toán thành công -> cập nhật trạng thái đơn
                    $updateStmt = $db->prepare("UPDATE orders SET status = 'processing', payment_status = 'paid' WHERE id = ?");
                    $updateStmt->execute([$orderId]);

                    $isPaid = true;
                    $order['status'] = 'processing';
                    $order['payment_status'] = 'paid';
                    break;
                }
            }
        }
    }

    echo json_encode([
        'success'        => true,
        'order_id'       => (int)$order['id'],
        'paid'           => $isPaid,
        'payment_status' => $order['payment_status'],
        'status'         => $order['status']
    ]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
