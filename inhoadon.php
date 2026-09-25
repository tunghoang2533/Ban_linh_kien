<?php
/**
 * Trang xem và in hóa đơn người dùng
 * URL: inhoadon.php?id={order_id}
 */
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database;
use App\Models\OrderModel;

if (!isset($_SESSION['user']) && empty($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "taikhoan.php");
    exit();
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    header("Location: " . BASE_URL . "lichsu.php");
    exit();
}

$db = Database::getInstance();
$orderModel = new OrderModel($db);

$orderDetail = null;
if (!empty($_SESSION['user'])) {
    $userId = (int)$_SESSION['user']['id'];
    $orderDetail = $orderModel->getOrderByIdAndUser($orderId, $userId);
}

// Nếu là Admin thì cho phép xem hóa đơn của bất kỳ đơn nào
if (!$orderDetail && !empty($_SESSION['admin'])) {
    $orderDetail = $orderModel->getOrderById($orderId);
}

if (!$orderDetail) {
    $_SESSION['history_error'] = "Không tìm thấy hóa đơn hoặc bạn không có quyền xem đơn hàng này.";
    header("Location: " . BASE_URL . "lichsu.php");
    exit();
}

$orderItems = $orderModel->getOrderItems($orderId);

include __DIR__ . '/admin/views/orders/invoice.php';
