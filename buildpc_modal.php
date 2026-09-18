<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;

$db = Database::getInstance();
$productModel = new ProductModel($db);

// 1. AJAX: Get products in category (with socket constraint if applicable)
if (isset($_GET['ajax_products']) && isset($_GET['cat_id'])) {
    header('Content-Type: application/json');
    $cat_id = (int)$_GET['cat_id'];
    $required_socket = null;
    
    // Check Socket compatibility between CPU (1) and Mainboard (3)
    if ($cat_id == 3 && isset($_SESSION['buildpc'][1])) {
        $required_socket = $_SESSION['buildpc'][1]['socket'];
    }
    if ($cat_id == 1 && isset($_SESSION['buildpc'][3])) {
        $required_socket = $_SESSION['buildpc'][3]['socket'];
    }
    
    $products = $productModel->getProductsByCategory($cat_id, $required_socket);
    
    // Return compatible products list
    echo json_encode([
        'products' => $products,
        'req_sock' => $required_socket
    ]);
    exit();
}

// 2. AJAX: Add component to buildpc session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add'])) {
    header('Content-Type: application/json');
    $cat_id = (int)$_POST['cat_id'];
    $product_id = (int)$_POST['product_id'];
    
    $product = $productModel->getProductById($product_id);
    if ($product) {
        if ((int)$product['quantity'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng.']);
            exit();
        }
        
        $newSocket = isset($product['socket']) ? $product['socket'] : '';
        
        // Remove Mainboard if CPU socket mismatch
        if ($cat_id == 1 && isset($_SESSION['buildpc'][3])) {
            if ($_SESSION['buildpc'][3]['socket'] != $newSocket) {
                unset($_SESSION['buildpc'][3]);
            }
        }
        // Remove CPU if Mainboard socket mismatch
        if ($cat_id == 3 && isset($_SESSION['buildpc'][1])) {
            if ($_SESSION['buildpc'][1]['socket'] != $newSocket) {
                unset($_SESSION['buildpc'][1]);
            }
        }
        
        $_SESSION['buildpc'][$cat_id] = [
            'id'     => $product['id'],
            'name'   => $product['name'],
            'price'  => (floatval($product['discount_percent'] ?? 0) > 0)
                        ? round($product['price'] * (1 - floatval($product['discount_percent']) / 100))
                        : $product['price'],
            'image'  => $product['image'],
            'socket' => $newSocket
        ];
        
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
        exit();
    }
}

// 3. AJAX: Remove component from buildpc session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_remove'])) {
    header('Content-Type: application/json');
    $cat_id = (int)$_POST['cat_id'];
    unset($_SESSION['buildpc'][$cat_id]);
    echo json_encode(['success' => true]);
    exit();
}

// 4. Fallback: If accessed directly (non-AJAX), redirect to buildpc.php
header("Location: " . BASE_URL . "buildpc.php");
exit();
