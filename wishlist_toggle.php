<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\WishlistModel;

if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'msg' => 'Vui lòng đăng nhập.']);
    exit;
}

$db = Database::getInstance();
$wishlistModel = new WishlistModel($db);
$userId = $_SESSION['user']['id'];
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($productId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'msg' => 'ID sản phẩm không hợp lệ.']);
    exit;
}

$inWishlist = $wishlistModel->toggle($userId, $productId);
$count = $wishlistModel->count($userId);

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'in_wishlist' => $inWishlist, 'count' => $count]);
