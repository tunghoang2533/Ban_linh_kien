<?php
/**
 * Handler: Categories — Quản lý danh mục & thương hiệu
 */
$uploadBrandDir = __DIR__ . '/../public/img/brands/';
if (!is_dir($uploadBrandDir)) mkdir($uploadBrandDir, 0755, true);

if ($action === 'create_cat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $catName = trim($_POST['cat_name'] ?? '');
    $catSlug = trim($_POST['cat_slug'] ?? '');
    if (!$catName) { $error = 'Vui lòng nhập tên danh mục.'; }
    else {
        $admin->createCategory($catName, $catSlug);
        header('Location: ' . BASE_URL . 'admin/?page=categories&tab=categories&success=cat_created'); exit;
    }
}
if ($action === 'update_cat' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $catName = trim($_POST['cat_name'] ?? '');
    $catSlug = trim($_POST['cat_slug'] ?? '');
    if (!$catName) { $error = 'Vui lòng nhập tên danh mục.'; }
    else {
        $admin->updateCategory(intval($_GET['id']), $catName, $catSlug);
        header('Location: ' . BASE_URL . 'admin/?page=categories&tab=categories&success=cat_updated'); exit;
    }
}
if ($action === 'delete_cat' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $res = $admin->deleteCategory(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=categories&tab=categories&success=' . ($res ? 'cat_deleted' : 'cat_has_products')); exit;
}

if ($action === 'create_brand' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bName = trim($_POST['brand_name'] ?? '');
    if (!$bName) { $error = 'Vui lòng nhập tên thương hiệu.'; }
    else {
        $bImg = '';
        if (!empty($_FILES['brand_logo']['name']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadedLogo = UploadHelper::storeImage($_FILES['brand_logo'], $uploadBrandDir, 'brand_', 2 * 1024 * 1024);
            if ($uploadedLogo) $bImg = $uploadedLogo;
        }
        $admin->createBrand($bName, $bImg);
        header('Location: ' . BASE_URL . 'admin/?page=categories&tab=brands&success=brand_created'); exit;
    }
}
if ($action === 'update_brand' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bName = trim($_POST['brand_name'] ?? '');
    if (!$bName) { $error = 'Vui lòng nhập tên thương hiệu.'; }
    else {
        $bImg = null;
        if (!empty($_FILES['brand_logo']['name']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadedLogo = UploadHelper::storeImage($_FILES['brand_logo'], $uploadBrandDir, 'brand_', 2 * 1024 * 1024);
            if ($uploadedLogo) $bImg = $uploadedLogo;
        }
        $admin->updateBrand(intval($_GET['id']), $bName, $bImg);
        header('Location: ' . BASE_URL . 'admin/?page=categories&tab=brands&success=brand_updated'); exit;
    }
}
if ($action === 'delete_brand' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $res = $admin->deleteBrand(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=categories&tab=brands&success=' . ($res ? 'brand_deleted' : 'brand_has_products')); exit;
}

if (isset($_GET['success'])) {
    $successMap = [
        'cat_created'      => 'Thêm danh mục thành công!',
        'cat_updated'      => 'Cập nhật danh mục thành công!',
        'cat_deleted'      => 'Xoá danh mục thành công!',
        'cat_has_products' => 'Không thể xoá: danh mục này đang có sản phẩm.',
        'brand_created'    => 'Thêm thương hiệu thành công!',
        'brand_updated'    => 'Cập nhật thương hiệu thành công!',
        'brand_deleted'    => 'Xoá thương hiệu thành công!',
        'brand_has_products' => 'Không thể xoá: thương hiệu này đang có sản phẩm.',
    ];
    $msg = $successMap[$_GET['success']] ?? '';
    if (strpos($_GET['success'], 'has_products') !== false) $error = $msg;
    else $successMessage = $msg;
}

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/categories/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
