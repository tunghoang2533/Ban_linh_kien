<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\CsrfHelper;
use App\Helpers\UploadHelper;

if (!isset($_SESSION['user'])) {
    header('Location: taikhoan.php');
    exit;
}

$db = (new Database())->connect();

// AJAX endpoint for order items (before any output)
if (isset($_GET['order_id']) && empty($_POST)) {
    header('Content-Type: application/json');
    $stmt = $db->prepare("SELECT oi.product_id, oi.quantity, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([(int)$_GET['order_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

$userId = $_SESSION['user']['id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }
    if (!$error) {
        // Process return request
        $orderId = (int)($_POST['order_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($orderId <= 0 || $productId <= 0 || empty($reason)) {
            $error = 'Vui lòng điền đầy đủ thông tin.';
        } else {
            // Insert return request
            $stmt = $db->prepare("INSERT INTO return_requests (order_id, product_id, user_id, quantity, reason, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            if ($stmt->execute([$orderId, $productId, $userId, $quantity, $reason])) {
                $success = 'Yêu cầu trả hàng đã được gửi. Chúng tôi sẽ liên hệ bạn trong 24h.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}

// Get user's orders for return selection
$orders = $db->prepare("SELECT id, total_amount, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$orders->execute([$userId]);
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

include 'app/views/header.php';
?>
<div class="container" style="max-width:800px;margin:30px auto;padding:0 20px;">
    <h2 style="margin-bottom:20px;"><i class="fa fa-rotate-left"></i> Yêu cầu trả hàng / Đổi trả</h2>
    <?php if ($error): ?><div style="background:#fef2f2;color:#dc2626;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div style="background:#f0fdf4;color:#16a34a;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <form method="POST" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.08);">
        <?php echo CsrfHelper::field(); ?>
        <div style="margin-bottom:16px;">
            <label style="font-size:13px;font-weight:600;color:#475569;">Chọn đơn hàng</label>
            <select name="order_id" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;" onchange="loadOrderItems(this.value)">
                <option value="">-- Chọn đơn hàng --</option>
                <?php foreach ($orders as $o): ?>
                    <option value="<?php echo $o['id']; ?>">#<?php echo $o['id']; ?> - <?php echo number_format($o['total_amount'],0,',','.'); ?>₫ (<?php echo date('d/m/Y', strtotime($o['created_at'])); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:13px;font-weight:600;color:#475569;">Sản phẩm</label>
            <select name="product_id" id="productSelect" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;" disabled>
                <option value="">-- Chọn đơn hàng trước --</option>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:13px;font-weight:600;color:#475569;">Số lượng</label>
            <input type="number" name="quantity" value="1" min="1" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:13px;font-weight:600;color:#475569;">Lý do trả hàng</label>
            <textarea name="reason" required rows="4" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;" placeholder="Mô tả lý do trả hàng..."></textarea>
        </div>
        <button type="submit" style="padding:12px 28px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
            <i class="fa fa-paper-plane"></i> Gửi yêu cầu
        </button>
    </form>
</div>

<script>
function loadOrderItems(orderId) {
    const sel = document.getElementById('productSelect');
    sel.innerHTML = '<option>Đang tải...</option>';
    sel.disabled = true;
    fetch('doitra.php?order_id=' + orderId)
        .then(r => r.json())
        .then(items => {
            sel.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
            items.forEach(item => {
                sel.innerHTML += '<option value="'+item.product_id+'">'+item.name+' (x'+item.quantity+')</option>';
            });
            sel.disabled = false;
        })
        .catch(() => {
            sel.innerHTML = '<option value="">-- Lỗi tải sản phẩm --</option>';
        });
}
</script>
<?php include 'app/views/footer.php'; ?>
