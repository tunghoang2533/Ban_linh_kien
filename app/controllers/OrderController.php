<?php
namespace App\Controllers;

use App\Models\OrderModel;

class OrderController {
    private $orderModel;

    public function __construct($db) {
        if (!isset($_SESSION['user'])) {
            header("Location: taikhoan.php");
            exit();
        }
        $this->orderModel = new OrderModel($db);
    }

    public function history() {
        $userId = $_SESSION['user']['id'];
        
        // Lọc theo trạng thái
        $status = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'completed', 'cancelled'])
            ? $_GET['status']
            : null;

        // Phân trang
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage  = 10;
        $total    = $this->orderModel->countOrdersByUserId($userId, $status);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page     = min($page, $totalPages);

        $orders = $this->orderModel->getOrdersByUserIdPaginated($userId, $page, $perPage, $status);

        include 'app/views/header.php';
        include 'app/views/orders/history_view.php';
        include 'app/views/footer.php';
    }

    public function detail() {
        $userId  = $_SESSION['user']['id'];
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($orderId <= 0) {
            header("Location: lichsu.php");
            exit();
        }

        // Lấy thông tin đơn hàng và kiểm tra quyền sở hữu
        $order = $this->orderModel->getOrderByIdAndUser($orderId, $userId);
        if (!$order) {
            header("Location: lichsu.php");
            exit();
        }

        $orderItems = $this->orderModel->getOrderItems($orderId);

        include 'app/views/header.php';
        include 'app/views/orders/order_detail_view.php';
        include 'app/views/footer.php';
    }

    public function cancel() {
        $userId  = $_SESSION['user']['id'];
        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

        if ($orderId <= 0) {
            header("Location: lichsu.php");
            exit();
        }

        $result = $this->orderModel->cancelOrder($orderId, $userId);

        if ($result) {
            $_SESSION['cancel_success'] = "Đơn hàng #$orderId đã được hủy thành công.";
        } else {
            $_SESSION['cancel_error'] = "Không thể hủy đơn hàng này. Chỉ có thể hủy đơn đang chờ xử lý.";
        }

        header("Location: lichsu.php");
        exit();
    }
}
?>