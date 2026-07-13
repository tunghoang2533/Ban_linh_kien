<?php
/**
 * Back-in-Stock Alert — AJAX Endpoint
 * POST /back_in_stock.php
 *   product_id: int (required)
 *   email:      string (required)
 *   _token:     CSRF token
 *
 * Trả về JSON: { ok: true/false, message: string }
 */

require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;
use App\Helpers\CsrfHelper;

header('Content-Type: application/json; charset=utf-8');

// ── Chỉ nhận POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Validate CSRF ──────────────────────────────────────────
try {
    CsrfHelper::verify();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Token bảo mật không hợp lệ. Vui lòng tải lại trang.']);
    exit;
}

// ── Validate input ─────────────────────────────────────────
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$email     = trim($_POST['email'] ?? '');

if ($productId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Mã sản phẩm không hợp lệ.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Vui lòng nhập email hợp lệ.']);
    exit;
}

// ── Kết nối DB & kiểm tra sản phẩm ─────────────────────────
$db = Database::getInstance();
$productModel = new ProductModel($db);

$product = $productModel->getProductById($productId);
if (!$product) {
    echo json_encode(['ok' => false, 'message' => 'Sản phẩm không tồn tại.']);
    exit;
}

// ── Kiểm tra sản phẩm đã có hàng chưa ────────────────────
if ((int)$product['quantity'] > 0) {
    echo json_encode(['ok' => false, 'message' => 'Sản phẩm này hiện đã có hàng trở lại! Bạn có thể đặt mua ngay.']);
    exit;
}

// ── Kiểm tra đã đăng ký chưa ──────────────────────────────
if ($productModel->isSubscribed($productId, $email)) {
    echo json_encode(['ok' => false, 'message' => 'Email này đã được đăng ký nhận thông báo cho sản phẩm này rồi. Chúng tôi sẽ thông báo ngay khi có hàng!']);
    exit;
}

// ── Đăng ký ────────────────────────────────────────────────
$userId = $_SESSION['user_id'] ?? null;
$result = $productModel->subscribeBackInStock($productId, $email, $userId);

if ($result) {
    echo json_encode(['ok' => true, 'message' => '✅ Đăng ký thành công! Chúng tôi sẽ gửi email thông báo ngay khi sản phẩm này có hàng trở lại.']);
} else {
    echo json_encode(['ok' => false, 'message' => '❌ Đã có lỗi xảy ra. Vui lòng thử lại sau.']);
}
