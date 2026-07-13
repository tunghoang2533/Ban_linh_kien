<?php
/**
 * Handler: Orders — Quản lý đơn hàng
 * Được require từ admin/index.php
 * Sử dụng các biến: $db, $admin, $error, $successMessage, $projectRoot
 */

require_once __DIR__ . '/../controllers/OrderController.php';
$orderCtrl = new OrderController($db);

// Xem hóa đơn (không có layout admin)
if ($action === 'invoice' && isset($_GET['id'])) {
    $orderId     = intval($_GET['id']);
    $orderDetail = $orderCtrl->getOrderById($orderId);
    $orderItems  = $orderCtrl->getOrderItemsById($orderId);
    include __DIR__ . '/../views/orders/invoice.php';
    exit;
}

if ($action === 'detail' && isset($_GET['id'])) {
    $orderId       = intval($_GET['id']);
    $orderDetail   = $admin->getOrderById($orderId);
    $statusHistory = $orderCtrl->getStatusHistory($orderId);
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/orders/detail.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
        // NotificationHelper autoloaded via PSR-4 + class_alias

        $orderBefore = $admin->getOrderById($orderId);
        $adminName = $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'Admin';
        $adminId   = $_SESSION['user_id'] ?? null;
        $adminNote = trim($_POST['admin_note'] ?? '');

        $orderCtrl->updateOrderStatus(
            $orderId, $_POST['status'], $adminId, $adminName,
            $adminNote ?: null
        );

        if ($orderBefore && !empty($orderBefore['user_id'])) {
            NotificationHelper::orderStatusChanged($db, $orderBefore['user_id'], $orderId, $_POST['status'], BASE_URL);
        }

        header('Location: ' . BASE_URL . 'admin/?page=orders&success=updated');
        exit;
    }
    $orderDetail   = $admin->getOrderById($orderId);
    $statusHistory = $orderCtrl->getStatusHistory($orderId);
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/orders/detail.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

// ── Bulk Invoice Printing ──
if ($action === 'bulk_invoice' && !empty($_GET['ids'])) {
    include __DIR__ . '/../views/orders/bulk_invoice.php';
    exit;
}

// ── Vận đơn GHN/GHTK ──
if ($action === 'shipping' && isset($_GET['id'])) {
    $orderId      = intval($_GET['id']);
    $orderDetail  = $admin->getOrderById($orderId);
    $shippingStmt = $db->prepare("SELECT * FROM shipping_orders WHERE order_id=? ORDER BY id DESC LIMIT 1");
    $shippingStmt->execute([$orderId]);
    $shippingOrder = $shippingStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $carrier   = $_POST['carrier']        ?? 'GHN';
        $tracking  = trim($_POST['tracking_code'] ?? '');
        $fee       = floatval($_POST['shipping_fee'] ?? 0);
        $weight    = intval($_POST['weight_gram'] ?? 0);
        $eta       = !empty($_POST['estimated_date']) ? $_POST['estimated_date'] : null;
        $note      = trim($_POST['note'] ?? '');
        $address   = $orderDetail['customer_address'] ?? '';
        $createdBy = $_SESSION['user_id'] ?? null;

        if ($shippingOrder) {
            $db->prepare("UPDATE shipping_orders SET carrier=?,tracking_code=?,shipping_fee=?,weight_gram=?,estimated_date=?,note=?,updated_at=NOW() WHERE id=?")
               ->execute([$carrier,$tracking,$fee,$weight,$eta,$note,$shippingOrder['id']]);
        } else {
            $db->prepare("INSERT INTO shipping_orders (order_id,carrier,tracking_code,shipping_fee,weight_gram,estimated_date,delivery_address,note,created_by) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$orderId,$carrier,$tracking,$fee,$weight,$eta,$address,$note,$createdBy]);
        }
        header('Location: ' . BASE_URL . 'admin/?page=orders&action=shipping&id='.$orderId.'&success=saved'); exit;
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/orders/shipping.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

// Fallback: hiển thị danh sách
if (isset($_GET['success']) && $_GET['success'] === 'updated') {
    $successMessage = 'Cập nhật trạng thái đơn hàng thành công.';
}
include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/orders/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
