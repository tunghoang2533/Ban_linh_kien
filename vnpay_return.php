<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\VNPayHelper;
use App\Helpers\Logger;

$db = Database::getInstance();

// Xác thực callback VNPay — verifyCallback() tự xử lý:
//   - tách & verify chữ ký HMAC-SHA512
//   - parse vnp_TxnRef "{orderId}_{timestamp}" → order_id
//   - kiểm tra vnp_ResponseCode === '00' → success
$result = VNPayHelper::verifyCallback($_GET);

$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';

Logger::info('VNPay return', [
    'order_id' => $result['order_id'],
    'valid'    => $result['valid'],
    'success'  => $result['success'],
    'message'  => $result['message'],
]);

if ($result['valid'] && $result['success']) {
    // Thanh toán thành công
    $stmt = $db->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid', vnpay_transaction = ? WHERE id = ?");
    $stmt->execute([$vnp_TransactionNo, $result['order_id']]);
    $_SESSION['payment_success'] = "Thanh toán VNPay thành công. Mã GD: $vnp_TransactionNo";
    header('Location: ' . BASE_URL . 'thanhtoan_success.php?order_id=' . $result['order_id']);
} else {
    // Thanh toán thất bại hoặc chữ ký không hợp lệ
    if ($result['valid'] && $result['order_id'] > 0) {
        $stmt = $db->prepare("UPDATE orders SET status = 'pending', payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$result['order_id']]);
    }
    $_SESSION['payment_error'] = $result['message'];
    header('Location: ' . BASE_URL . 'thanhtoan.php');
}
exit;
