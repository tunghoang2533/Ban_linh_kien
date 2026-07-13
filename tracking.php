<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\CsrfHelper;

$db = Database::getInstance();

$orderInfo = null;
$orderItems = [];
$trackingCode = trim($_GET['code'] ?? '');

if ($trackingCode) {
    $stmt = $db->prepare("SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.tracking_code = ? OR o.id = ?");
    $code = preg_replace('/[^a-zA-Z0-9]/', '', $trackingCode);
    $id = is_numeric($trackingCode) ? (int)$trackingCode : 0;
    $stmt->execute([$code, $id]);
    $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($orderInfo) {
        $itemsStmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itemsStmt->execute([$orderInfo['id']]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include 'app/views/header.php';
?>
<div class="container" style="max-width:800px;margin:40px auto;padding:0 20px;">
    <h2 style="margin-bottom:20px;"><i class="fa fa-truck"></i> Tra cứu đơn hàng</h2>
    <form method="GET" style="display:flex;gap:10px;margin-bottom:30px;">
        <input type="text" name="code" value="<?php echo htmlspecialchars($trackingCode); ?>" placeholder="Nhập mã đơn hàng hoặc mã tra cứu" style="flex:1;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:15px;">
        <button type="submit" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer;">Tra cứu</button>
    </form>
    <?php if ($trackingCode && !$orderInfo): ?>
        <div style="text-align:center;padding:40px;color:#ef4444;background:#fef2f2;border-radius:12px;">
            <i class="fa fa-exclamation-circle" style="font-size:32px;margin-bottom:8px;"></i>
            <p>Không tìm thấy đơn hàng với mã <strong><?php echo htmlspecialchars($trackingCode); ?></strong></p>
        </div>
    <?php endif; ?>
    <?php if ($orderInfo): ?>
        <div style="background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.08);overflow:hidden;">
            <div style="padding:24px;border-bottom:1px solid #e2e8f0;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h3 style="margin:0 0 4px;font-size:18px;">Đơn hàng #<?php echo $orderInfo['id']; ?></h3>
                        <p style="margin:0;color:#64748b;">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($orderInfo['created_at'])); ?></p>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:inline-block;padding:6px 16px;border-radius:20px;font-weight:700;font-size:13px;
                            <?php
                            $status = strtolower($orderInfo['status'] ?? 'pending');
                            $colors = ['pending'=>'background:#fef3c7;color:#b45309;', 'confirmed'=>'background:#dbeafe;color:#1d4ed8;', 'shipping'=>'background:#ede9fe;color:#6d28d9;', 'delivered'=>'background:#dcfce7;color:#16a34a;', 'cancelled'=>'background:#fee2e2;color:#dc2626;'];
                            echo $colors[$status] ?? 'background:#f1f5f9;color:#475569;';
                            ?>">
                            <?php echo ['pending'=>'Chờ xác nhận', 'confirmed'=>'Đã xác nhận', 'shipping'=>'Đang giao', 'delivered'=>'Đã giao', 'cancelled'=>'Đã hủy'][$status] ?? $status; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div style="padding:24px;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:#f8fafc;"><th style="padding:10px;text-align:left;">Sản phẩm</th><th style="padding:10px;text-align:center;">SL</th><th style="padding:10px;text-align:right;">Đơn giá</th></tr></thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr><td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($item['name']); ?></td><td style="padding:10px;border-bottom:1px solid #f1f5f9;text-align:center;"><?php echo $item['quantity']; ?></td><td style="padding:10px;border-bottom:1px solid #f1f5f9;text-align:right;"><?php echo number_format($item['price'],0,',','.'); ?>₫</td></tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="2" style="padding:12px;text-align:right;font-weight:700;">Tổng cộng:</td><td style="padding:12px;text-align:right;font-weight:800;color:#e10c00;"><?php echo number_format($orderInfo['total_amount'],0,',','.'); ?>₫</td></tr></tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include 'app/views/footer.php'; ?>
