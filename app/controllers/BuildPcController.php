<?php
namespace App\Controllers;

use App\Models\ProductModel;

class BuildPcController {
    private $productModel;

    // ID chuẩn khớp 100% với bảng categories trong DB của bạn
    private $categories = [
        1 => 'Vi xử lý (CPU)',
        3 => 'Bo mạch chủ (Mainboard)',
        2 => 'Bộ nhớ trong (RAM)',
        4 => 'Card màn hình (VGA)',
        5 => 'Ổ cứng (SSD/HDD)',
        6 => 'Nguồn máy tính (PSU)',
        7 => 'Vỏ máy tính (Case)'
    ];

    public function __construct($db) {
        $this->productModel = new ProductModel($db);
        if (!isset($_SESSION['buildpc'])) {
            $_SESSION['buildpc'] = [];
        }
    }

    public function index() {
        $buildCategories = $this->categories;
        $productModel    = $this->productModel; // pass vào view
        include 'app/views/header.php';
        
        // Đã sửa tên file view thành buildpc_view.php
        include 'app/views/buildpc/buildpc_view.php'; 
        
        include 'app/views/footer.php';
    }

    public function select() {
        if (isset($_GET['cat_id'])) {
            $cat_id = $_GET['cat_id'];
            $cat_name = $this->categories[$cat_id] ?? 'Linh kiện';
            
            $required_socket = null;

            // Kiểm tra ràng buộc Socket giữa CPU (1) và Mainboard (3)
            if ($cat_id == 3 && isset($_SESSION['buildpc'][1])) {
                $required_socket = $_SESSION['buildpc'][1]['socket'];
            }
            if ($cat_id == 1 && isset($_SESSION['buildpc'][3])) {
                $required_socket = $_SESSION['buildpc'][3]['socket'];
            }

            // Gọi model để lấy danh sách sản phẩm (có lọc socket nếu cần)
            $products = $this->productModel->getProductsByCategory($cat_id, $required_socket);

            include 'app/views/header.php';
            include 'app/views/buildpc/select_view.php';
            include 'app/views/footer.php';
        } else {
            header("Location: buildpc.php");
        }
    }

    public function add() {
        if (isset($_GET['cat_id']) && isset($_GET['product_id'])) {
            $cat_id    = $_GET['cat_id'];
            $product_id = $_GET['product_id'];

            $product = $this->productModel->getProductById($product_id);

            if ($product) {
                // Kiểm tra tồn kho
                if ((int)$product['quantity'] <= 0) {
                    $_SESSION['buildpc_error'] = "Sản phẩm <strong>{$product['name']}</strong> đã hết hàng, không thể thêm vào cấu hình.";
                    header("Location: buildpc.php");
                    exit();
                }

                $newSocket = isset($product['socket']) ? $product['socket'] : '';

                if ($cat_id == 1 && isset($_SESSION['buildpc'][3])) {
                    if ($_SESSION['buildpc'][3]['socket'] != $newSocket) {
                        unset($_SESSION['buildpc'][3]);
                    }
                }
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
            }
        }
        header("Location: buildpc.php");
    }

    public function remove() {
        if (isset($_GET['cat_id'])) {
            unset($_SESSION['buildpc'][$_GET['cat_id']]);
        }
        header("Location: buildpc.php");
    }

    public function addToCart() {
        if (!empty($_SESSION['buildpc'])) {
            // Kiểm tra tồn kho tất cả sản phẩm trong build
            $stockErrors = [];
            foreach ($_SESSION['buildpc'] as $item) {
                $product = $this->productModel->getProductById($item['id']);
                if (!$product || (int)$product['quantity'] <= 0) {
                    $name = $product['name'] ?? $item['name'];
                    $stockErrors[] = "'{$name}' đã hết hàng.";
                }
            }
            if (!empty($stockErrors)) {
                $_SESSION['buildpc_error'] = "⚠️ Không thể thêm vào giỏ — các món sau đã hết hàng:<br>• " . implode('<br>• ', $stockErrors);
                header("Location: buildpc.php");
                exit();
            }

            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            foreach ($_SESSION['buildpc'] as $item) {
                $id = $item['id'];
                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id]['quantity']++;
                } else {
                    $item['quantity'] = 1;
                    $_SESSION['cart'][$id] = $item;
                }
            }
            unset($_SESSION['buildpc']);
            header("Location: giohang.php");
            exit();
        }
        header("Location: buildpc.php");
    }

    public function buyNow() {
        if (!empty($_SESSION['buildpc'])) {
            // Kiểm tra tồn kho tất cả sản phẩm trong build
            $stockErrors = [];
            foreach ($_SESSION['buildpc'] as $item) {
                $product = $this->productModel->getProductById($item['id']);
                if (!$product || (int)$product['quantity'] <= 0) {
                    $name = $product['name'] ?? $item['name'];
                    $stockErrors[] = "'{$name}' đã hết hàng.";
                }
            }
            if (!empty($stockErrors)) {
                $_SESSION['buildpc_error'] = "⚠️ Không thể mua ngay — các món sau đã hết hàng:<br>• " . implode('<br>• ', $stockErrors);
                header("Location: buildpc.php");
                exit();
            }

            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            foreach ($_SESSION['buildpc'] as $item) {
                $id = $item['id'];
                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id]['quantity']++;
                } else {
                    $item['quantity'] = 1;
                    $_SESSION['cart'][$id] = $item;
                }
            }
            unset($_SESSION['buildpc']);
            header("Location: thanhtoan.php");
            exit();
        }
        header("Location: buildpc.php");
    }
}
?>