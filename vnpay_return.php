<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\VNPayHelper;
use App\Helpers\Logger;

$db = (new Database())->connect();

// Verify VNPay response
$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
$vnp_Amount = $_GET['vnp_Amount'] ?? '';
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';

$inputData = $_GET;
unset($inputData['vnp_SecureHash']);

$secureHash = VNPayHelper::generateSecureHash($inputData);
$isValid = ($secureHash === $vnp_SecureHash);

Logger::info('VNPay return', [
    'order_id' => $vnp_TxnRef,
    'code' => $vnp_ResponseCode,
    'valid' => $isValid,
]);

if ($isValid && $vnp_ResponseCode === '00') {
    // Payment successful
    $stmt = $db->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid', vnpay_transaction = ? WHERE id = ?");
    $stmt->execute([$vnp_TransactionNo, (int)$vnp_TxnRef]);
    $_SESSION['payment_success'] = "Thanh toán VNPay thành công. Mã GD: $vnp_TransactionNo";
    header('Location: thanhtoan_success.php?order_id=' . (int)$vnp_TxnRef);
} else {
    // Payment failed
    $stmt = $db->prepare("UPDATE orders SET status = 'pending', payment_status = 'failed' WHERE id = ?");
    $stmt->execute([(int)$vnp_TxnRef]);
    $_SESSION['payment_error'] = "Thanh toán thất bại. Mã lỗi: $vnp_ResponseCode";
    header('Location: thanhtoan.php');
}
exit;
