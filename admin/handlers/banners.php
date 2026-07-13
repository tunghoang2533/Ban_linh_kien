<?php
/**
 * Handler: Banners — Quản lý banner slider
 */
require_once __DIR__ . '/../controllers/BannerController.php';
$bannerCtrl = new BannerController($db);

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $bannerCtrl->deleteBanner(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=banners&success=deleted');
    exit;
}
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $bannerCtrl->toggleBannerStatus(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=banners&success=toggled');
    exit;
}
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $bannerCtrl->addBanner($_POST, $_FILES['image'] ?? null);
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'admin/?page=banners&success=added');
        exit;
    }
    $error = $result['message'];
}
if ($action === 'edit' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $bannerCtrl->updateBanner(intval($_GET['id']), $_POST, $_FILES['image'] ?? null);
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'admin/?page=banners&success=updated');
        exit;
    }
    $error = $result['message'];
}
if ($action === 'sort' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $orders = json_decode($raw, true);
    if (is_array($orders)) {
        $bannerCtrl->updateSortOrder($orders);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if (isset($_GET['success'])) {
    $msgMap = [
        'added'   => '✅ Thêm banner thành công!',
        'updated' => '✅ Cập nhật banner thành công!',
        'deleted' => '✅ Đã xóa banner.',
        'toggled' => '✅ Đã thay đổi trạng thái banner.',
    ];
    $successMessage = $msgMap[$_GET['success']] ?? '';
}

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/banners/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
