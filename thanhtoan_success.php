<?php
require_once 'session_check.php';
require_once 'config.php';

if (empty($_SESSION['order_success'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$data          = $_SESSION['order_success'];
unset($_SESSION['order_success']);

$orderId       = (int)$data['order_id'];
$finalTotal    = (float)$data['final_total'];
$voucherCode   = $data['voucher_code'] ?? null;
$discount      = (float)($data['discount'] ?? 0);
$paymentMethod = $data['payment_method'] ?? 'cod';

include 'app/views/header.php';
?>
<style>
.success-page {
    min-height: calc(100vh - 200px);
    display: flex; align-items: center; justify-content: center;
    padding: 40px 16px;
    background: #f4f7fb;
}
.success-card {
    background: #fff;
    border-radius: 28px;
    box-shadow: 0 22px 80px rgba(22,163,74,.13);
    overflow: hidden;
    max-width: 560px; width: 100%;
    text-align: center;
}
.success-header {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    padding: 48px 32px 40px;
    color: #fff;
    position: relative; overflow: hidden;
}
.success-header::before {
    content: ''; position: absolute; width: 200px; height: 200px;
    background: rgba(255,255,255,.1); border-radius: 50%;
    top: -70px; right: -70px;
}
.success-header .s-icon { font-size: 68px; display: block; margin-bottom: 16px; position: relative; z-index: 1; animation: popIn .5s cubic-bezier(.34,1.56,.64,1); }
@keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.success-header h1 { margin: 0 0 8px; font-size: 28px; font-weight: 800; position: relative; z-index: 1; }
.success-header p { margin: 0; font-size: 14px; opacity: .88; line-height: 1.6; position: relative; z-index: 1; }
.success-body { padding: 32px 28px 36px; }
.order-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: #f0fdf4; border: 2px solid #bbf7d0;
    border-radius: 999px; padding: 8px 18px;
    color: #166534; font-weight: 700; font-size: 14px;
    margin-bottom: 24px;
}
.detail-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #f1f5f9;
    font-size: 14px; color: #475569;
}
.detail-row:last-of-type { border-bottom: none; }
.detail-row .label { font-weight: 500; }
.detail-row .val { font-weight: 700; color: #1e293b; }
.detail-row .val.red { color: #dc2626; }
.detail-row .val.green { color: #16a34a; }
.total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 18px; margin: 16px 0;
    background: #f0fdf4; border-radius: 14px;
    border: 2px solid #bbf7d0;
}
.total-row .label { font-size: 15px; font-weight: 700; color: #166534; }
.total-row .amount { font-size: 22px; font-weight: 900; color: #dc2626; }
.payment-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: <?php echo $paymentMethod === 'bank' ? '#eff6ff' : '#fff7ed'; ?>;
    border: 1.5px solid <?php echo $paymentMethod === 'bank' ? '#bfdbfe' : '#fed7aa'; ?>;
    border-radius: 999px; padding: 6px 14px;
    color: <?php echo $paymentMethod === 'bank' ? '#1d4ed8' : '#c2410c'; ?>;
    font-size: 13px; font-weight: 600;
    margin-bottom: 8px;
}
.voucher-badge {
    background: #fefce8; border: 1.5px solid #fde68a;
    border-radius: 12px; padding: 10px 16px;
    color: #92400e; font-size: 13px; font-weight: 600;
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}
.success-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 24px; }
.btn-success { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px 20px; border-radius: 14px; font-size: 15px; font-weight: 700; text-decoration: none; transition: transform .18s, box-shadow .18s; }
.btn-success.primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 8px 24px rgba(37,99,235,.25); }
.btn-success.primary:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(37,99,235,.3); }
.btn-success.secondary { background: #f8fafc; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-success.secondary:hover { background: #f1f5f9; }
</style>

<div class="success-page">
    <div class="success-card">
        <div class="success-header">
            <span class="s-icon">🎉</span>
            <h1>Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã tin tưởng Shop Linh Kiện.<br>Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất.</p>
        </div>
        <div class="success-body">
            <div class="order-badge">
                <i class="fa fa-check-circle"></i>
                Mã đơn hàng #<?php echo $orderId; ?>
            </div>

            <?php if ($voucherCode && $discount > 0): ?>
            <div class="voucher-badge">
                🎟️ Voucher <strong><?php echo htmlspecialchars($voucherCode); ?></strong> — Tiết kiệm <strong><?php echo number_format($discount, 0, ',', '.'); ?>₫</strong>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="label">Phương thức thanh toán</span>
                <span class="payment-badge">
                    <?php if ($paymentMethod === 'bank'): ?>
                        🏦 Chuyển khoản ngân hàng
                    <?php else: ?>
                        💵 Thanh toán khi nhận hàng (COD)
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($paymentMethod === 'bank'): ?>
            <div style="background:#eff6ff;border-radius:12px;padding:14px 16px;margin:10px 0;font-size:13.5px;color:#1d4ed8;text-align:left;line-height:1.7;">
                <strong>📋 Thông tin chuyển khoản:</strong><br>
                Ngân hàng: <strong>Vietcombank</strong><br>
                Số tài khoản: <strong>1234567890</strong><br>
                Tên tài khoản: <strong>CONG TY PC STORE</strong><br>
                Nội dung CK: <strong>DH<?php echo $orderId; ?> – <?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?></strong>
            </div>
            <?php endif; ?>

            <div class="total-row">
                <span class="label">💰 Tổng thanh toán</span>
                <span class="amount"><?php echo number_format($finalTotal, 0, ',', '.'); ?>₫</span>
            </div>

            <div class="success-actions">
                <a href="<?php echo BASE_URL; ?>lichsu.php" class="btn-success primary">
                    <i class="fa fa-history"></i> Xem đơn hàng của tôi
                </a>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn-success secondary">
                    <i class="fa fa-home"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/footer.php'; ?>
