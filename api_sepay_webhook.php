<?php
/**
 * SePAY Webhook Handler
 * Tự động nhận biến động số dư MB Bank (2312200566656) từ SePAY
 * và cập nhật trạng thái đơn hàng sang "Đã thanh toán (paid)".
 *
 * Tài liệu SePAY: https://docs.sepay.vn/tich-hop-webhooks.html
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

use App\Core\Database;
use App\Helpers\Logger;

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Xác thực API Key nếu có cấu hình SEPAY_API_KEY trong .env
$configuredApiKey = getenv('SEPAY_API_KEY') ?: '';
if (!empty($configuredApiKey)) {
    // Lấy Authorization header từ request
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($authHeader) && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
    }

    // Format SePAY: "Apikey YOUR_API_KEY"
    $receivedKey = trim(preg_replace('/^Apikey\s+/i', '', $authHeader));
    if ($receivedKey !== $configuredApiKey) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Invalid SePAY API Key']);
        exit;
    }
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

// SePAY payload parameters
$transferType   = $data['transferType'] ?? 'in'; // 'in': tiền vào
$transferAmount = (float)($data['transferAmount'] ?? 0);
$content        = $data['content'] ?? '';
$referenceCode  = $data['referenceCode'] ?? '';
$accountNumber  = $data['accountNumber'] ?? '';

// Chỉ xử lý giao dịch tiền vào (transferType = 'in')
if ($transferType !== 'in') {
    echo json_encode(['success' => true, 'message' => 'Ignored non-incoming transaction']);
    exit;
}

// Bóc tách mã đơn hàng từ nội dung: tìm "DH" + số (ví dụ: DH948, DH 948, DH10025)
$orderId = 0;
if (preg_match('/DH\s*(\d+)/i', $content, $matches)) {
    $orderId = (int)$matches[1];
}

if ($orderId <= 0) {
    if (class_exists('App\Helpers\Logger')) {
        Logger::warning('SePAY Webhook: Không tìm thấy mã đơn DH trong nội dung', ['content' => $content]);
    }
    echo json_encode(['success' => false, 'message' => 'Order ID not found in transaction content']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, total_amount, status, payment_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        if (class_exists('App\Helpers\Logger')) {
            Logger::warning('SePAY Webhook: Đơn hàng không tồn tại', ['order_id' => $orderId]);
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Idempotency: nếu đơn đã được xác nhận thanh toán rồi thì trả lời OK luôn
    if ($order['payment_status'] === 'paid') {
        echo json_encode(['success' => true, 'message' => 'Order already confirmed as paid']);
        exit;
    }

    // Kiểm tra số tiền chuyển có đủ với giá trị đơn hàng không
    $orderTotal = (float)$order['total_amount'];
    if ($transferAmount < $orderTotal) {
        if (class_exists('App\Helpers\Logger')) {
            Logger::warning('SePAY Webhook: Số tiền chuyển không đủ', [
                'order_id'        => $orderId,
                'order_total'     => $orderTotal,
                'transfer_amount' => $transferAmount
            ]);
        }
        echo json_encode(['success' => false, 'message' => 'Transfer amount is less than order total']);
        exit;
    }

    // Cập nhật đơn hàng: chuyển status sang processing và payment_status sang paid
    $updateStmt = $db->prepare("UPDATE orders SET status = 'processing', payment_status = 'paid' WHERE id = ?");
    $updateStmt->execute([$orderId]);

    if (class_exists('App\Helpers\Logger')) {
        Logger::info('SePAY Webhook: Cập nhật đơn hàng thành công', [
            'order_id'        => $orderId,
            'transfer_amount' => $transferAmount,
            'reference_code'  => $referenceCode
        ]);
    }

    echo json_encode([
        'success'   => true,
        'message'   => 'Order payment confirmed successfully',
        'order_id'  => $orderId
    ]);
} catch (\Exception $e) {
    if (class_exists('App\Helpers\Logger')) {
        Logger::error('SePAY Webhook Error', ['error' => $e->getMessage()]);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
