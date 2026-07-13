<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\WishlistModel;
use App\Helpers\CsrfHelper;

if (!isset($_SESSION['user'])) {
    header('Location: taikhoan.php');
    exit;
}

$db = Database::getInstance();
$wishlistModel = new WishlistModel($db);
$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    try { CsrfHelper::verify(); } catch (Exception $e) {
        Logger::warning('CSRF verification failed on wishlist remove', ['error' => $e->getMessage()]);
    }
    $wishlistModel->remove($userId, (int)$_POST['product_id']);
    header('Location: wishlist.php');
    exit;
}

$items = $wishlistModel->getByUser($userId);

include 'app/views/header.php';
?>
<div class="container" style="max-width:900px;margin:30px auto;padding:0 20px;">
    <h2 style="margin-bottom:20px;"><i class="fa fa-heart" style="color:#ef4444;"></i> Sản phẩm yêu thích</h2>
    <?php if (empty($items)): ?>
        <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
            <p style="font-size:48px;margin:0 0 12px;">💔</p>
            <p style="font-size:16px;">Bạn chưa có sản phẩm yêu thích nào.</p>
            <a href="index.php" style="display:inline-block;margin-top:16px;padding:12px 28px;background:#2563eb;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px;">
            <?php foreach ($items as $item): ?>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;transition:.2s;">
                    <a href="chitietsanpham.php?id=<?php echo $item['product_id']; ?>" style="display:block;padding:16px;text-decoration:none;color:inherit;">
                        <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($item['image']); ?>" style="width:100%;height:150px;object-fit:contain;margin-bottom:10px;">
                        <h3 style="font-size:14px;margin:0 0 6px;color:#1e293b;"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p style="color:#e10c00;font-weight:800;margin:0;"><?php echo number_format($item['price'],0,',','.'); ?>₫</p>
                    </a>
                    <div style="padding:0 16px 14px;display:flex;gap:8px;">
                        <a href="giohang.php?action=add&id=<?php echo $item['product_id']; ?>" style="flex:1;text-align:center;padding:8px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;"><i class="fa fa-cart-plus"></i> Thêm giỏ</a>
                        <form method="POST" style="flex:0;">
                            <?php echo CsrfHelper::field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" name="remove" style="padding:8px 12px;border:1px solid #fecaca;background:#fef2f2;color:#dc2626;border-radius:8px;cursor:pointer;font-size:12px;"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'app/views/footer.php'; ?>
