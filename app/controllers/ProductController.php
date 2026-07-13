<?php
namespace App\Controllers;

use App\Helpers\CsrfHelper;
use App\Models\ProductModel;
use App\Models\ProductCommentModel;

class ProductController {
    private $productModel;
    private $commentModel;

    public function __construct($db) {
        $this->productModel = new ProductModel($db);
        $this->commentModel = new ProductCommentModel($db);
    }

    public function detail() {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
                $this->handleCommentSubmit($id);
            }
            
            $product = $this->productModel->getProductById($id);
            $specs = $this->productModel->getProductSpecs($id);
            $productImages = $this->productModel->getProductImages($id);
            $comments = $this->commentModel->getCommentsByProduct($id);
            $ratingInfo = $this->commentModel->getAverageRating($id);
            $averageRating = $ratingInfo['average_rating'] ? round($ratingInfo['average_rating'], 1) : 0;
            $totalReviews = $ratingInfo['total_reviews'] ?? 0;

            $fbtProducts = [];
            if ($product) {
                $fbtProducts = $this->productModel->getFrequentlyBoughtTogether($id, 4);
            }

            if ($product) {
                // ── Track sản phẩm đã xem vào session ──
                if (!isset($_SESSION['recently_viewed'])) {
                    $_SESSION['recently_viewed'] = [];
                }
                // Bỏ ID cũ (nếu có) để thêm lại lên đầu
                $_SESSION['recently_viewed'] = array_diff($_SESSION['recently_viewed'], [$id]);
                array_unshift($_SESSION['recently_viewed'], $id);
                // Giới hạn tối đa 12 sản phẩm
                $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 12);

                include __DIR__ . '/../views/header.php';
                include __DIR__ . '/../views/products/detail_view.php';
                include __DIR__ . '/../views/footer.php';
            } else {
                echo "Sản phẩm không tồn tại!";
            }
        } else {
            header("Location: index.php");
            exit;
        }
    }

    private function handleCommentSubmit($productId) {
        // Xác thực CSRF token trước khi xử lý
        try { CsrfHelper::verify(); } catch (Exception $e) {
            $_SESSION['comment_error'] = $e->getMessage();
            header('Location: ' . BASE_URL . 'chitietsanpham.php?id=' . $productId . '#product-comments');
            exit;
        }

        // Fix #7: Yêu cầu đăng nhập trước khi đánh giá
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['comment_error'] = 'Bạn cần đăng nhập để đánh giá sản phẩm.';
            header('Location: ' . BASE_URL . 'chitietsanpham.php?id=' . $productId . '#product-comments');
            exit;
        }

        if (empty($_POST['rating']) || empty(trim($_POST['comment'] ?? ''))) {
            $_SESSION['comment_error'] = 'Vui lòng chọn đánh giá và nhập bình luận.';
            header('Location: ' . BASE_URL . 'chitietsanpham.php?id=' . $productId . '#product-comments');
            exit;
        }

        $rating = (int)$_POST['rating'];
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        $comment = trim($_POST['comment']);
        // Fix #7: dùng tên từ session, không cho phép nhập tên tùy ý
        $name   = $_SESSION['full_name'] ?? ($_SESSION['user']['fullname'] ?? 'Khách hàng');
        $userId = $_SESSION['user_id'];

        $this->commentModel->addComment($productId, $userId, $name, $rating, $comment);
        $_SESSION['comment_success'] = 'Cảm ơn bạn đã đánh giá sản phẩm!';
        header('Location: ' . BASE_URL . 'chitietsanpham.php?id=' . $productId . '#product-comments');
        exit;
    }
}

?>