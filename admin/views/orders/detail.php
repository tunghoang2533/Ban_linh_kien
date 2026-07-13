<?php
$orderDetail = isset($orderDetail) ? $orderDetail : null;
$action      = isset($_GET['action']) ? $_GET['action'] : 'detail';
$isEdit      = ($action === 'edit');

$statusOptions = [
    'pending'    => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipped'    => 'Đang giao',
    'completed'  => 'Hoàn thành',
    'cancelled'  => 'Đã hủy',
];

$statusColor = [
    'pending'    => ['bg' => 'rgba(245,158,11,0.15)', 'text' => '#fbbf24', 'icon' => 'clock'],
    'processing' => ['bg' => 'rgba(99,102,241,0.15)', 'text' => '#818cf8', 'icon' => 'cog'],
    'shipped'    => ['bg' => 'rgba(6,182,212,0.15)',  'text' => '#22d3ee', 'icon' => 'truck'],
    'completed'  => ['bg' => 'rgba(34,197,94,0.15)',  'text' => '#4ade80', 'icon' => 'check-circle'],
    'cancelled'  => ['bg' => 'rgba(239,68,68,0.15)',  'text' => '#f87171', 'icon' => 'times-circle'],
];

$statusHistory = $statusHistory ?? [];
$orderId = intval($orderDetail['id'] ?? 0);
?>
<style>
/* ── Order Status Timeline ── */
.order-timeline { position: relative; padding-left: 0; list-style: none; margin: 0; }
.order-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0; bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--accent), var(--border-subtle));
    border-radius: 2px;
}
.tl-item {
    display: flex;
    gap: 16px;
    padding: 0 0 24px 0;
    position: relative;
}
.tl-item:last-child { padding-bottom: 0; }
.tl-dot {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
    box-shadow: 0 0 0 4px var(--bg-surface);
}
.tl-body { flex: 1; min-width: 0; padding-top: 8px; }
.tl-title { font-size: 14px; font-weight: 600; color: var(--text-primary); margin: 0 0 4px; }
.tl-meta  { font-size: 12px; color: var(--text-muted); margin: 0; }
.tl-note  { font-size: 12px; color: var(--text-secondary); margin-top: 4px; font-style: italic; background: var(--bg-elevated); border-radius: 6px; padding: 4px 10px; display: inline-block; border: 1px solid var(--border-subtle); }
</style>



<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><?php echo $isEdit ? 'Cập nhật đơn hàng' : 'Chi tiết đơn hàng'; ?> #<?php echo $orderId; ?></h1>
            <p>Thông tin chi tiết, trạng thái và lịch sử đơn hàng</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <!-- Nút vận đơn GHN/GHTK -->
            <a href="?page=orders&action=shipping&id=<?php echo $orderId; ?>"
               class="btn btn-sm"
               style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-truck"></i> Vận đơn
            </a>
            <!-- Nút in hóa đơn -->
            <a href="?page=orders&action=invoice&id=<?php echo $orderId; ?>" target="_blank"
               class="btn btn-sm"
               style="background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-print"></i> In hóa đơn
            </a>
            <a href="?page=orders" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <?php if (!$orderDetail): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Không tìm thấy đơn hàng.
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

            <!-- ── Cột trái: Thông tin đơn + Sản phẩm ── -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Thông tin đơn hàng -->
                <div class="form-card" style="max-width:100%;">
                    <h2 class="form-section-title">
                        <i class="fas fa-file-invoice" style="color:#6366f1;margin-right:8px;"></i>
                        Thông tin đơn hàng
                    </h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Mã đơn hàng</p>
                            <p style="font-weight:700;font-size:18px;color:#6366f1;">#<?php echo $orderDetail['id']; ?></p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Trạng thái</p>
                            <?php
                            $st = strtolower($orderDetail['status'] ?? 'pending');
                            $col = $statusColor[$st] ?? ['bg'=>'#f1f5f9','text'=>'#64748b','icon'=>'circle'];
                            ?>
                            <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $col['bg']; ?>;color:<?php echo $col['text']; ?>;">
                                <i class="fas fa-<?php echo $col['icon']; ?>"></i>
                                <?php echo $statusOptions[$st] ?? $orderDetail['status']; ?>
                            </span>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Khách hàng</p>
                            <p style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($orderDetail['full_name'] ?? $orderDetail['customer_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Email</p>
                            <p style="color:var(--text-secondary);"><?php echo htmlspecialchars($orderDetail['email'] ?? $orderDetail['customer_email'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Số điện thoại</p>
                            <p style="color:var(--text-secondary);"><?php echo htmlspecialchars($orderDetail['customer_phone'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Ngày tạo</p>
                            <p style="color:var(--text-secondary);"><?php echo isset($orderDetail['created_at']) ? date('d/m/Y H:i', strtotime($orderDetail['created_at'])) : 'N/A'; ?></p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Tổng tiền</p>
                            <p style="font-weight:800;font-size:20px;color:#10b981;"><?php echo number_format($orderDetail['total_amount'] ?? 0, 0, ',', '.'); ?> ₫</p>
                        </div>
                        <div>
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Thanh toán</p>
                            <p style="color:var(--text-secondary);font-weight:600;"><?php echo strtoupper($orderDetail['payment_method'] ?? 'COD'); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($orderDetail['customer_address']) || !empty($orderDetail['shipping_address'])): ?>
                        <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;">
                            <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Địa chỉ giao hàng</p>
                            <p style="color:var(--text-secondary);line-height:1.6;"><?php echo nl2br(htmlspecialchars($orderDetail['customer_address'] ?? $orderDetail['shipping_address'] ?? '')); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($orderDetail['voucher_code'])): ?>
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;gap:16px;">
                            <div>
                                <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Voucher</p>
                                <span style="background:rgba(245,158,11,0.12);color:#fbbf24;border-radius:6px;padding:2px 10px;font-size:13px;font-weight:700;"><?php echo htmlspecialchars($orderDetail['voucher_code']); ?></span>
                            </div>
                            <?php if (!empty($orderDetail['discount_amount'])): ?>
                            <div>
                                <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Giảm giá</p>
                                <p style="color:#dc2626;font-weight:700;">-<?php echo number_format($orderDetail['discount_amount'], 0, ',', '.'); ?>₫</p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($orderDetail['shipping_fee'])): ?>
                            <div>
                                <p style="font-size:12px;color:var(--text-faint);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Phí vận chuyển</p>
                                <p style="color:var(--text-secondary);font-weight:600;"><?php echo number_format($orderDetail['shipping_fee'], 0, ',', '.'); ?>₫</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Lịch sử trạng thái -->
                <div class="form-card" style="max-width:100%;">
                    <h2 class="form-section-title">
                        <i class="fas fa-history" style="color:#8b5cf6;margin-right:8px;"></i>
                        Lịch sử trạng thái
                    </h2>
                    <?php if (empty($statusHistory)): ?>
                        <p style="color:var(--text-faint);text-align:center;padding:20px 0;font-size:14px;">Chưa có lịch sử thay đổi trạng thái</p>
                    <?php else: ?>
                    <ul class="order-timeline">
                        <?php
                        $statusLabelMap = [
                            'pending'    => ['label'=>'Chờ xử lý',   'bg'=>'#fef3c7','text'=>'#92400e','icon'=>'clock'],
                            'processing' => ['label'=>'Đang xử lý',  'bg'=>'#ede9fe','text'=>'#5b21b6','icon'=>'cog'],
                            'shipped'    => ['label'=>'Đang giao',    'bg'=>'#e0f2fe','text'=>'#075985','icon'=>'truck'],
                            'completed'  => ['label'=>'Hoàn thành',   'bg'=>'#d1fae5','text'=>'#065f46','icon'=>'check-circle'],
                            'cancelled'  => ['label'=>'Đã hủy',       'bg'=>'#fee2e2','text'=>'#991b1b','icon'=>'times-circle'],
                        ];
                        foreach ($statusHistory as $i => $h):
                            $toSt = strtolower($h['to_status'] ?? 'pending');
                            $cfg  = $statusLabelMap[$toSt] ?? ['label'=>$h['to_status'],'bg'=>'#f1f5f9','text'=>'#64748b','icon'=>'circle'];
                        ?>
                        <li class="tl-item">
                            <div class="tl-dot" style="background:<?php echo $cfg['bg']; ?>;color:<?php echo $cfg['text']; ?>;">
                                <i class="fas fa-<?php echo $cfg['icon']; ?>"></i>
                            </div>
                            <div class="tl-body">
                                <p class="tl-title">
                                    <?php echo htmlspecialchars($cfg['label']); ?>
                                    <?php if (!empty($h['from_status']) && $h['from_status'] !== $h['to_status']): ?>
                                        <span style="font-size:11px;color:var(--text-faint);font-weight:400;margin-left:6px;">
                                            (từ <?php echo htmlspecialchars($statusLabelMap[strtolower($h['from_status'])]['label'] ?? $h['from_status']); ?>)
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p class="tl-meta">
                                    <i class="fas fa-user" style="margin-right:4px;"></i><?php echo htmlspecialchars($h['changer_name'] ?? 'Hệ thống'); ?>
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-clock" style="margin-right:4px;"></i><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                                </p>
                                <?php if (!empty($h['note'])): ?>
                                    <span class="tl-note"><i class="fas fa-comment-alt" style="margin-right:4px;"></i><?php echo htmlspecialchars($h['note']); ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ── Cột phải: Cập nhật trạng thái ── -->
            <div>
                <div class="form-card" style="max-width:100%;">
                    <h2 class="form-section-title">
                        <i class="fas fa-edit" style="color:#f59e0b;margin-right:8px;"></i>
                        Cập nhật trạng thái
                    </h2>
                    <form method="POST" action="?page=orders&action=edit&id=<?php echo $orderId; ?>">
                        <?php echo CsrfHelper::field(); ?>
                        <div class="form-group">
                            <label class="form-label" for="order-status">Trạng thái đơn hàng</label>
                            <select id="order-status" name="status" class="form-control">
                                <?php foreach ($statusOptions as $val => $label): ?>
                                    <option value="<?php echo $val; ?>" <?php echo strtolower($orderDetail['status']) === $val ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="admin-note">
                                <i class="fas fa-sticky-note" style="color:#f59e0b;"></i>
                                Ghi chú (tuỳ chọn)
                            </label>
                            <textarea id="admin-note" name="admin_note"
                                      class="form-control"
                                      rows="3"
                                      placeholder="VD: Đã giao cho shipper GHN, dự kiến 2-3 ngày..."
                                      style="resize:vertical;"></textarea>
                        </div>
                        <div class="form-actions" style="border-top:none;padding-top:0;">
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Nút In hóa đơn nổi bật -->
                <div class="form-card" style="max-width:100%;margin-top:16px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;">
                    <h2 class="form-section-title" style="color:#4ade80;">
                        <i class="fas fa-print" style="color:#10b981;margin-right:8px;"></i>
                        In hóa đơn
                    </h2>
                    <p style="font-size:13px;color:#16a34a;margin-bottom:14px;">In hóa đơn đầy đủ thông tin đơn hàng để giao cho shipper hoặc khách hàng.</p>
                    <a href="?page=orders&action=invoice&id=<?php echo $orderId; ?>" target="_blank"
                       style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px;background:linear-gradient(135deg,#10b981,#059669);color:white;border-radius:12px;text-decoration:none;font-weight:700;font-size:14px;box-shadow:0 4px 12px rgba(16,185,129,.3);transition:all .2s;">
                        <i class="fas fa-print"></i> Xem & In hóa đơn
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
