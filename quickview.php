<?php
/**
 * quickview.php — AJAX endpoint cho Product Quick View
 * 
 * Trả về JSON với thông tin sản phẩm: ảnh, giá, thông số, rating, variants
 * Gọi: quickview.php?id=123
 */

require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ProductCommentModel;

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID sản phẩm không hợp lệ']);
    exit();
}

$db = Database::getInstance();
$productModel = new ProductModel($db);

$product = $productModel->getProductById($id);
if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Sản phẩm không tồn tại']);
    exit();
}

// Lấy thông số kỹ thuật (lấy 5 cái đầu)
$specs = $productModel->getProductSpecs($id);
$specs = array_slice($specs, 0, 5);

// Lấy ảnh phụ
$productImages = $productModel->getProductImages($id);

// Xây dựng mảng ảnh
$allImages = [];
$defaultImage = BASE_URL . 'public/img/no-image.png';
$baseProductDir = __DIR__ . '/public/img/products/';

if (!empty($product['image'])) {
    if (strpos($product['image'], 'data:') === 0) {
        $allImages[] = $product['image'];
    } elseif (file_exists($baseProductDir . $product['image'])) {
        $allImages[] = BASE_URL . 'public/img/products/' . $product['image'];
    }
}
foreach ($productImages as $pi) {
    if (!empty($pi['image']) && file_exists($baseProductDir . $pi['image'])) {
        $allImages[] = BASE_URL . 'public/img/products/' . $pi['image'];
    }
}
if (empty($allImages)) {
    $allImages[] = $defaultImage;
}

// Lấy rating
$commentModel = new ProductCommentModel($db);
$ratingInfo = $commentModel->getAverageRating($id);

// Lấy danh mục
$catStmt = $db->prepare("SELECT name FROM categories WHERE id = :id");
$catStmt->execute(['id' => $product['category_id']]);
$category = $catStmt->fetch(PDO::FETCH_ASSOC);
$categoryName = $category ? $category['name'] : '';

// Tính giá
$hasDiscount = !empty($product['discount_percent']) && $product['discount_percent'] > 0;
$finalPrice  = $hasDiscount
    ? round($product['price'] * (1 - $product['discount_percent'] / 100))
    : $product['price'];

// Lấy biến thể nếu có
$variants = [];
if (!empty($product['has_variants'])) {
    $variantModel = new ProductVariantModel($db);
    $variants = $variantModel->getByProductId($id);
}

echo json_encode([
    'id'              => (int)$product['id'],
    'name'            => $product['name'],
    'price'           => (float)$product['price'],
    'final_price'     => (float)$finalPrice,
    'discount_percent'=> (float)($product['discount_percent'] ?? 0),
    'category_name'   => $categoryName,
    'description'     => mb_substr(strip_tags($product['description'] ?? ''), 0, 300),
    'quantity'        => (int)$product['quantity'],
    'in_stock'        => (int)$product['quantity'] > 0,
    'images'          => $allImages,
    'specs'           => $specs,
    'average_rating'  => round((float)($ratingInfo['average_rating'] ?? 0), 1),
    'total_reviews'   => (int)($ratingInfo['total_reviews'] ?? 0),
    'variants'        => $variants,
    'add_to_cart_url' => BASE_URL . 'giohang.php?action=add&id=' . $product['id'],
    'buy_now_url'     => BASE_URL . 'giohang.php?action=add&id=' . $product['id'] . '&checkout=1',
    'detail_url'      => BASE_URL . 'chitietsanpham.php?id=' . $product['id'],
], JSON_UNESCAPED_UNICODE);
