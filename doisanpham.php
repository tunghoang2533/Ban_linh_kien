<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;

$db = Database::getInstance();
$productModel = new ProductModel($db);

$ids = isset($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];
$compareProducts = [];
if (!empty($ids)) {
    foreach ($ids as $id) {
        $p = $productModel->getProductById($id);
        if ($p) $compareProducts[] = $p;
    }
}

include 'app/views/header.php';
?>
<div class="container" style="max-width:1200px;margin:30px auto;padding:0 20px;">
    <h2 style="margin-bottom:20px;"><i class="fa fa-scale-balanced"></i> So sánh sản phẩm</h2>
    <?php if (count($compareProducts) < 2): ?>
        <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
            <p style="font-size:48px;margin:0 0 12px;">🔍</p>
            <p>Vui lòng chọn ít nhất 2 sản phẩm để so sánh.</p>
            <a href="index.php" style="display:inline-block;margin-top:16px;padding:12px 28px;background:#2563eb;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">Quay lại trang chủ</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
                <tr style="background:#f8fafc;">
                    <th style="padding:16px;text-align:left;min-width:140px;border-bottom:1px solid #e2e8f0;">Thông tin</th>
                    <?php foreach ($compareProducts as $p): ?>
                        <th style="padding:16px;text-align:center;border-bottom:1px solid #e2e8f0;border-left:1px solid #e2e8f0;">
                            <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($p['image']); ?>" style="width:120px;height:120px;object-fit:contain;margin-bottom:8px;">
                            <h3 style="margin:0;font-size:14px;"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p style="margin:4px 0 0;color:#e10c00;font-weight:800;font-size:16px;"><?php echo number_format($p['price'],0,',','.'); ?>₫</p>
                        </th>
                    <?php endforeach; ?>
                </tr>
                <?php
                $specKeys = [];
                foreach ($compareProducts as $p) {
                    $specs = $productModel->getProductSpecs($p['id']);
                    foreach ($specs as $s) { $specKeys[$s['spec_name']] = true; }
                }
                ?>
                <?php foreach (array_keys($specKeys) as $specName): ?>
                <tr>
                    <td style="padding:12px 16px;font-weight:600;color:#475569;border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($specName); ?></td>
                    <?php foreach ($compareProducts as $p):
                        $specs = $productModel->getProductSpecs($p['id']);
                        $val = '';
                        foreach ($specs as $s) { if ($s['spec_name'] === $specName) { $val = $s['spec_value']; break; } }
                    ?>
                        <td style="padding:12px 16px;text-align:center;border-bottom:1px solid #f1f5f9;border-left:1px solid #f1f5f9;color:#64748b;">
                            <?php echo htmlspecialchars($val ?: '-'); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include 'app/views/footer.php'; ?>
