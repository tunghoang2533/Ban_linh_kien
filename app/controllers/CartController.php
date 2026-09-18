<?php
namespace App\Controllers;

use App\Helpers\AbandonedCartHelper;
use App\Models\OrderModel;
use App\Models\ProductModel;

class CartController {
    private $productModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new ProductModel($db);

        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Ghi nhận hoạt động giỏ hàng vào bảng abandoned_carts
     */
    private function trackCartActivity(): void {
        if (empty($_SESSION['cart'])) return;
        try {
            $helper = new AbandonedCartHelper($this->db);
            $userId = $_SESSION['user']['id'] ?? null;
            $sessionId = session_id();
            $helper->track($userId, $sessionId, $_SESSION['cart']);
        } catch (\Exception $e) {
            error_log('Abandoned cart tracking failed: ' . $e->getMessage());
        }
    }

    // Hiển thị trang giỏ hàng
    public function index() {
        $this->trackCartActivity();
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/cart/cart_view.php';
        include __DIR__ . '/../views/footer.php';
    }

    // Thêm sản phẩm vào giỏ
    public function add() {
        // Hỗ trợ cả GET (action=add&id=) và POST (từ wishlist)
        $id = $_GET['id'] ?? $_POST['product_id'] ?? null;
        $isBuyNow = isset($_GET['checkout']) && $_GET['checkout'] == '1';

        if ($id !== null) {

            // Luôn lấy thông tin sản phẩm mới nhất từ DB để kiểm tra tồn kho
            $product = $this->productModel->getProductById($id);

            if ($product) {
                $stock = (int)$product['quantity'];

                if (isset($_SESSION['cart'][$id])) {
                    $currentQty = (int)$_SESSION['cart'][$id]['quantity'];

                    if ($currentQty < $stock) {
                        $_SESSION['cart'][$id]['quantity']++;
                    } else {
                        // Vượt quá tồn kho, báo lỗi
                        $_SESSION['cart_error'] = "Sản phẩm <strong>{$product['name']}</strong> chỉ còn <strong>{$stock}</strong> cái trong kho. Bạn đã thêm tối đa số lượng cho phép.";
                        header("Location: " . BASE_URL . "giohang.php");
                        exit();
                    }
                } else {
                    if ($stock > 0) {
                        $discountPct = floatval($product['discount_percent'] ?? 0);
                        $finalPrice  = $discountPct > 0
                            ? round($product['price'] * (1 - $discountPct / 100))
                            : $product['price'];
                        $_SESSION['cart'][$id] = [
                            'id'       => $product['id'],
                            'name'     => $product['name'],
                            'price'    => $finalPrice,
                            'image'    => $product['image'],
                            'quantity' => 1
                        ];
                    } else {
                        $_SESSION['cart_error'] = "Sản phẩm <strong>{$product['name']}</strong> đã hết hàng.";
                        header("Location: " . BASE_URL . "giohang.php");
                        exit();
                    }
                }
            }
        }

        // Nếu là "Mua ngay": đánh dấu session để giỏ hàng chỉ tick sản phẩm này
        if ($isBuyNow && isset($id)) {
            $_SESSION['buy_now_id'] = $id;
        } else {
            // Nút "Thêm vào giỏ": xóa trạng thái buy_now để tick tất cả
            unset($_SESSION['buy_now_id']);
        }

        $this->trackCartActivity();
        header("Location: " . BASE_URL . "giohang.php");
        exit();
    }

    // Xóa sản phẩm
    public function remove() {
        if (isset($_GET['id'])) {
            unset($_SESSION['cart'][$_GET['id']]);
        }
        $this->trackCartActivity();
        header("Location: " . BASE_URL . "giohang.php");
        exit();
    }

    // Cập nhật số lượng (AJAX)
    public function updateQty() {
        header('Content-Type: application/json');
        $id  = $_POST['id']  ?? null;
        $qty = (int)($_POST['qty'] ?? 0);

        if (!$id || $qty < 1) {
            echo json_encode(['ok' => false, 'msg' => 'Dữ liệu không hợp lệ.']);
            exit();
        }

        if (!isset($_SESSION['cart'][$id])) {
            echo json_encode(['ok' => false, 'msg' => 'Sản phẩm không tồn tại trong giỏ.']);
            exit();
        }

        // Kiểm tra tồn kho
        $product = $this->productModel->getProductById($id);
        $stock   = $product ? (int)$product['quantity'] : 0;

        if ($qty > $stock) {
            echo json_encode(['ok' => false, 'msg' => "Chỉ còn {$stock} sản phẩm trong kho.", 'max' => $stock]);
            exit();
        }

        $_SESSION['cart'][$id]['quantity'] = $qty;
        $price    = (float)$_SESSION['cart'][$id]['price'];
        $subtotal = $price * $qty;

        // Track sau khi cập nhật
        $this->trackCartActivity();

        echo json_encode(['ok' => true, 'qty' => $qty, 'subtotal' => $subtotal]);
        exit();
    }

    /**
     * Lightweight tracking endpoint — gọi từ JS beacon, không render full page
     */
    public function trackCart(): void {
        header('Content-Type: application/json');
        $this->trackCartActivity();
        echo json_encode(['ok' => true, 't' => time()]);
        exit();
    }

    // Mua lại đơn hàng cũ: thêm tất cả sản phẩm vào giỏ
    public function reorder() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "taikhoan.php");
            exit();
        }

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $userId  = $_SESSION['user']['id'];

        if ($orderId <= 0) {
            header("Location: " . BASE_URL . "lichsu.php");
            exit();
        }

        // Lấy sản phẩm trong đơn hàng cũ (chỉ lấy đơn của user này)
        $orderModel = new OrderModel($this->productModel->getDb());
        $order      = $orderModel->getOrderByIdAndUser($orderId, $userId);

        if (!$order) {
            header("Location: " . BASE_URL . "lichsu.php");
            exit();
        }

        $items = $orderModel->getOrderItems($orderId);
        $addedCount  = 0;
        $skippedList = [];

        foreach ($items as $item) {
            $product = $this->productModel->getProductById($item['product_id']);
            if (!$product || (int)$product['quantity'] <= 0) {
                $skippedList[] = $item['name'];
                continue;
            }

            $pid   = $product['id'];
            $stock = (int)$product['quantity'];
            $discountPct = floatval($product['discount_percent'] ?? 0);
            $finalPrice  = $discountPct > 0
                ? round($product['price'] * (1 - $discountPct / 100))
                : $product['price'];

            if (isset($_SESSION['cart'][$pid])) {
                $newQty = min($_SESSION['cart'][$pid]['quantity'] + $item['quantity'], $stock);
                $_SESSION['cart'][$pid]['quantity'] = $newQty;
            } else {
                $qty = min((int)$item['quantity'], $stock);
                $_SESSION['cart'][$pid] = [
                    'id'       => $pid,
                    'name'     => $product['name'],
                    'price'    => $finalPrice,
                    'image'    => $product['image'],
                    'quantity' => $qty
                ];
            }
            $addedCount++;
        }

        if ($addedCount > 0) {
            $_SESSION['reorder_success'] = "Đã thêm $addedCount sản phẩm vào giỏ hàng!" . (!empty($skippedList) ? " (Bỏ qua: " . implode(', ', $skippedList) . " - hết hàng)" : "");
        } else {
            $_SESSION['reorder_success'] = "Không có sản phẩm nào còn hàng để thêm vào giỏ.";
        }

        header("Location: " . BASE_URL . "giohang.php");
        exit();
    }
}