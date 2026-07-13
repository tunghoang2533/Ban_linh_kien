<?php
/**
 * Handler: Products — Quản lý sản phẩm
 * Được require từ admin/index.php
 * Sử dụng các biến: $db, $admin, $error, $successMessage, $editId, $showAddForm, $productId
 */

if ($action === 'delete_image' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {
    $imageId = intval($_POST['image_id']);
    $pid = intval($_POST['pid'] ?? 0);
    $deletedFile = $admin->deleteProductImage($imageId);
    if ($deletedFile) {
        $imgPath = __DIR__ . '/../public/img/products/' . $deletedFile;
        if (file_exists($imgPath)) @unlink($imgPath);
    }
    header('Location: ' . BASE_URL . 'admin/?page=products&action=index&edit_id=' . $pid . '#edit-' . $pid);
    exit;
}

// ── Export CSV ──
if ($action === 'export' && isset($_GET['type'])) {
    $exportType = $_GET['type'];
    header('Content-Type: text/csv; charset=UTF-8');
    
    if ($exportType === 'template') {
        // Export file mẫu để import
        header('Content-Disposition: attachment; filename="product_import_template.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['name','price','cost_price','category_id','brand_id','quantity','discount_percent','description']);
        fputcsv($out, ['RAM DDR4 8GB',500000,350000,1,1,10,0,'RAM chính hãng']);
        fputcsv($out, ['SSD 240GB',700000,500000,1,1,5,10,'SSD tốc độ cao']);
        fclose($out);
        exit;
    }
    
    // Export tất cả sản phẩm ra CSV
    header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name','price','cost_price','category_id','brand_id','quantity','discount_percent','description','image']);
    $products = $admin->getProducts();
    foreach ($products as $p) {
        fputcsv($out, [
            $p['name'],
            $p['price'],
            $p['cost_price'] ?? 0,
            $p['category_id'],
            $p['brand_id'] ?? '',
            $p['quantity'],
            $p['discount_percent'] ?? 0,
            strip_tags($p['description'] ?? ''),
            $p['image'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

// ── Price History page ──
if ($action === 'price_history' && isset($_GET['id'])) {
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/products/price_history.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

// ── Import page ──
if ($action === 'import') {
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/products/import.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $admin->deleteProduct($id);
    header('Location: ' . BASE_URL . 'admin/?page=products');
    exit;
}

if ($action === 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $admin->toggleProductStatus(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=products');
    exit;
}

// ── Back-in-Stock: kiểm tra stock tăng từ 0 → >0 để gửi thông báo ──
$bisNotifySent = false;
$bisProductModel = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = [
        'category_id'      => trim($_POST['category_id'] ?? ''),
        'brand_id'         => trim($_POST['brand_id'] ?? ''),
        'name'             => trim($_POST['name'] ?? ''),
        'price'            => trim($_POST['price'] ?? ''),
        'quantity'         => trim($_POST['quantity'] ?? ''),
        'image'            => '',
        'description'      => trim($_POST['description'] ?? ''),
        'discount_percent' => floatval($_POST['discount_percent'] ?? 0)
    ];
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;

    $uploadDir = __DIR__ . '/../public/img/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $fileName = UploadHelper::storeImage($_FILES['image'], $uploadDir, 'product_', 4 * 1024 * 1024);
        if ($fileName) {
            $product['image'] = $fileName;
        } else {
            $error = 'Anh khong hop le hoac vuot qua dung luong cho phep.';
        }
    } elseif ($productId) {
        $existingProduct    = $admin->getProductById($productId);
        $product['image']   = $existingProduct['image'] ?? '';
    }

    if (!$product['name'] || !$product['price']) {
        $error = $error ?: 'Vui lòng nhập tên sản phẩm và giá.';
        if ($productId) { $editId = $productId; } else { $showAddForm = true; }
    } elseif (!$product['image'] && !$productId) {
        $error = $error ?: 'Vui lòng chọn ảnh sản phẩm.';
        $showAddForm = true;
    }

    if (!$error) {
        if ($productId) {
            // Lấy quantity cũ để phát hiện stock thay đổi
            $oldProduct   = $admin->getProductById($productId);
            $oldQty       = (int)($oldProduct['quantity'] ?? 0);
            $newQty       = (int)$product['quantity'];
            
            // Ghi lịch sử thay đổi giá (nếu có thay đổi)
            try {
                $priceHistoryData = [
                    'price'           => $oldProduct['price'] ?? 0,
                    'cost_price'      => $oldProduct['cost_price'] ?? 0,
                    'discount_percent' => $oldProduct['discount_percent'] ?? 0,
                ];
                $changeNote = isset($_POST['price_change_note']) ? trim($_POST['price_change_note']) : null;
                PriceHistoryHelper::record($db, $productId, $priceHistoryData, $product, $_SESSION['user_id'] ?? null, $changeNote);
            } catch (Exception $e) {
                    Logger::warning('Failed to record price history', ['product_id' => $productId ?? null, 'error' => $e->getMessage()]);
                }

            if ($admin->updateProduct($productId, $product)) {
                // ── Back-in-Stock: nếu stock từ 0 → >0, gửi thông báo ──
                if ($oldQty === 0 && $newQty > 0) {
                    if ($bisProductModel === null) {
                        // ProductModel, NotificationHelper autoloaded via PSR-4 + class_alias
                        $bisProductModel = new ProductModel($db);
                    }
                    $bisSubscribers = $bisProductModel->getBackInStockSubscribers($productId);
                    if (!empty($bisSubscribers)) {
                        $productName  = htmlspecialchars($product['name']);
                        $productUrl   = BASE_URL . 'chitietsanpham.php?id=' . $productId;
                        foreach ($bisSubscribers as $sub) {
                            EmailHelper::backInStockNotify($sub['email'], '', $productName, $productUrl);
                            // Nếu có user_id, gửi thông báo trong app
                            if (!empty($sub['user_id'])) {
                                NotificationHelper::send($db, $sub['user_id'],
                                    "🔔 Sản phẩm đã có hàng!",
                                    "Sản phẩm \"{$productName}\" bạn quan tâm đã có hàng trở lại!",
                                    'promotion',
                                    $productUrl
                                );
                            }
                        }
                        $bisProductModel->markNotified($productId);
                        $bisNotifySent = true;
                    }
                }

                if (!empty($_FILES['extra_images']['name'][0])) {
                    $uploadedExtra = [];
                    foreach ($_FILES['extra_images']['tmp_name'] as $k => $tmpName) {
                        if ($_FILES['extra_images']['error'][$k] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                            $file = UploadHelper::normalizeMultiFile($_FILES['extra_images'], $k);
                            $fn2 = UploadHelper::storeImage($file, $uploadDir, 'product_ex_', 4 * 1024 * 1024);
                            if ($fn2) $uploadedExtra[] = $fn2;
                        }
                    }
                    if (!empty($uploadedExtra)) {
                        $admin->addProductImages($productId, $uploadedExtra);
                    }
                }
                header('Location: ' . BASE_URL . 'admin/?page=products&success=' . ($bisNotifySent ? 'bis_notified' : 'updated') . '&edit_id=' . $productId . '#edit-' . $productId);
                exit;
            }
            $error  = 'Không thể cập nhật sản phẩm. Vui lòng thử lại.';
            $editId = $productId;
        } else {
            $newProductId = $admin->addProduct($product);
            if ($newProductId) {
                if (!empty($_FILES['extra_images']['name'][0])) {
                    $uploadedExtra = [];
                    foreach ($_FILES['extra_images']['tmp_name'] as $k => $tmpName) {
                        if ($_FILES['extra_images']['error'][$k] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                            $file = UploadHelper::normalizeMultiFile($_FILES['extra_images'], $k);
                            $fn2 = UploadHelper::storeImage($file, $uploadDir, 'product_ex_', 4 * 1024 * 1024);
                            if ($fn2) $uploadedExtra[] = $fn2;
                        }
                    }
                    if (!empty($uploadedExtra)) {
                        $admin->addProductImages($newProductId, $uploadedExtra);
                    }
                }
                header('Location: ' . BASE_URL . 'admin/?page=products&success=added');
                exit;
            }
            $error       = 'Không thể thêm sản phẩm. Vui lòng thử lại.';
            $showAddForm = true;
        }
    }
}

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
}

if (isset($_GET['show_add_form'])) {
    $showAddForm = true;
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $successMessage = 'Thêm sản phẩm thành công.';
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = 'Cập nhật sản phẩm thành công.';
    } elseif ($_GET['success'] === 'bis_notified') {
        $successMessage = '✅ Cập nhật thành công! Đã gửi thông báo có hàng đến khách hàng đã đăng ký.';
    }
}

// Render view
include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/products/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
