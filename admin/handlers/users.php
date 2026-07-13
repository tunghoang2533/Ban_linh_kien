<?php
/**
 * Handler: Users — Quản lý người dùng
 */

if ($action === 'view' && isset($_GET['id'])) {
    $uid        = intval($_GET['id']);
    $userDetail = $admin->getUserById($uid);
    $userStats  = $admin->getUserStats($uid);
    $userOrders = $admin->getUserOrders($uid);
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/users/detail.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'block' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid    = intval($_GET['id']);
    $reason = trim($_POST['blocked_reason'] ?? '');
    $admin->blockUser($uid, $reason);
    header('Location: ' . BASE_URL . 'admin/?page=users&action=view&id=' . $uid . '&success=blocked');
    exit;
}

if ($action === 'unblock' && isset($_GET['id'])) {
    $uid = intval($_GET['id']);
    $admin->unblockUser($uid);
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($ref, 'action=view') !== false) {
        header('Location: ' . BASE_URL . 'admin/?page=users&action=view&id=' . $uid . '&success=unblocked');
    } else {
        header('Location: ' . BASE_URL . 'admin/?page=users&success=unblocked');
    }
    exit;
}

if (isset($_GET['success'])) {
    $msgMap = [
        'blocked'   => 'Đã khoá tài khoản người dùng.',
        'unblocked' => 'Đã mở khoá tài khoản người dùng.',
    ];
    $successMessage = $msgMap[$_GET['success']] ?? '';
}

// Danh sách users
include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/users/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
