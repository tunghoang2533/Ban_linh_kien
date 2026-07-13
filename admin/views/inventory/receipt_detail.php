<?php
/**
 * Chi tiết Phiếu Nhập Kho + Duyệt/Hủy
 * URL: ?page=inventory&action=receipt_detail&id=X
 */
$rcpt  = $receiptDetail ?? null;
$items = $receiptItems  ?? [];
if (!$rcpt): ?>
<main class="admin-main">
<div style="text-align:center;padding:80px 20px;color:var(--text-faint);">
    <i class="fas fa-exclamation-circle" style="font-size:60px;display:block;margin-bottom:16px;opacity:.3;"></i>
    Không tìm thấy phiếu nhập kho
    <br><a href="?page=inventory&action=receipts" class="btn btn-secondary" style="margin-top:20px;">Quay lại</a>
</div>
</main>
<?php return; endif;

$statusInfo = InventoryController::getReceiptStatusLabel($rcpt['status']);
$typeInfo   = InventoryController::getReceiptTypeLabel($rcpt['type']);
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-file-alt" style="color:#6366f1;margin-right:10px;"></i>
            Phiếu Nhập Kho — <code><?= htmlspecialchars($rcpt['receipt_code']) ?></code>
        </h1>
        <p>
            <span style="background:<?= $statusInfo['bg'] ?>;color:<?= $statusInfo['color'] ?>;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;">
                <i class="fas <?= $statusInfo['icon'] ?>"></i> <?= $statusInfo['label'] ?>
            </span>
            &nbsp;
            <span style="background:<?= $typeInfo['bg'] ?>;color:<?= $typeInfo['color'] ?>;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                <i class="fas <?= $typeInfo['icon'] ?>"></i> <?= $typeInfo['label'] ?>
            </span>
        </p>
    </div>
    <div class="page-header-right">
        <a href="?page=inventory&action=receipts" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Danh sách
        </a>
        <?php if ($rcpt['status'] === 'pending'): ?>
        <form method="POST" action="?page=inventory&action=approve_receipt&id=<?= $rcpt['id'] ?>" style="display:inline;" onsubmit="return confirm('Duyệt phiếu này và cộng tồn kho?')">
            <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Duyệt Phiếu</button>
        </form>
        <?php endif; ?>
        <?php if (in_array($rcpt['status'], ['draft', 'pending'])): ?>
        <button onclick="openCancelModal()" class="btn btn-danger"><i class="fas fa-times-circle"></i> Hủy Phiếu</button>
        <?php endif; ?>
        <?php if ($rcpt['status'] === 'draft'): ?>
        <form method="POST" action="?page=inventory&action=submit_receipt&id=<?= $rcpt['id'] ?>" style="display:inline;" onsubmit="return confirm('Gửi phiếu này để duyệt?')">
            <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Gửi Duyệt</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($successMessage)): ?>
<div class="rd-alert rd-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="rd-alert rd-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Timeline trạng thái -->
<div class="rd-timeline">
    <?php
    $steps = [
        ['key'=>'draft',    'label'=>'Tạo nháp',     'icon'=>'fa-edit'],
        ['key'=>'pending',  'label'=>'Chờ duyệt',    'icon'=>'fa-clock'],
        ['key'=>'approved', 'label'=>'Đã duyệt',     'icon'=>'fa-check-circle'],
    ];
    $statusOrder = ['draft'=>0,'pending'=>1,'approved'=>2,'cancelled'=>-1];
    $currentOrder = $statusOrder[$rcpt['status']] ?? 0;
    foreach ($steps as $step):
        $sOrder = $statusOrder[$step['key']] ?? 0;
        $cls = $sOrder < $currentOrder ? 'done' : ($sOrder === $currentOrder ? 'active' : 'pending-step');
    ?>
    <div class="rd-step <?= $cls ?>">
        <div class="rd-step-circle"><i class="fas <?= $step['icon'] ?>"></i></div>
        <div class="rd-step-label"><?= $step['label'] ?></div>
    </div>
    <?php if ($step !== end($steps)): ?><div class="rd-step-line <?= $sOrder < $currentOrder ? 'done' : '' ?>"></div><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($rcpt['status'] === 'cancelled'): ?>
    <div class="rd-step cancelled"><div class="rd-step-circle"><i class="fas fa-times-circle"></i></div><div class="rd-step-label">Đã hủy</div></div>
    <?php endif; ?>
</div>

<div class="rd-layout">
    <div class="rd-main">
        <!-- Thông tin cơ bản -->
        <div class="rd-card">
            <div class="rd-card-header"><i class="fas fa-info-circle"></i> Thông tin phiếu</div>
            <div class="rd-card-body">
                <div class="rd-info-grid">
                    <div class="rd-info-item"><div class="rd-info-label">Mã phiếu</div><div class="rd-info-value" style="color:#6366f1;font-weight:700;"><?= htmlspecialchars($rcpt['receipt_code']) ?></div></div>
                    <div class="rd-info-item"><div class="rd-info-label">Kho</div><div class="rd-info-value"><?= htmlspecialchars($rcpt['warehouse_name'] ?? '—') ?></div></div>
                    <div class="rd-info-item"><div class="rd-info-label">Nhà cung cấp</div><div class="rd-info-value"><?= htmlspecialchars($rcpt['supplier_name'] ?? '—') ?></div></div>
                    <div class="rd-info-item"><div class="rd-info-label">Người tạo</div><div class="rd-info-value"><?= htmlspecialchars($rcpt['created_by_name'] ?? '—') ?></div></div>
                    <div class="rd-info-item"><div class="rd-info-label">Ngày tạo</div><div class="rd-info-value"><?= date('d/m/Y H:i', strtotime($rcpt['created_at'])) ?></div></div>
                    <?php if ($rcpt['submitted_at']): ?>
                    <div class="rd-info-item"><div class="rd-info-label">Gửi duyệt lúc</div><div class="rd-info-value"><?= date('d/m/Y H:i', strtotime($rcpt['submitted_at'])) ?></div></div>
                    <?php endif; ?>
                    <?php if ($rcpt['approved_at']): ?>
                    <div class="rd-info-item"><div class="rd-info-label">Người duyệt</div><div class="rd-info-value" style="color:#10b981;font-weight:600;"><?= htmlspecialchars($rcpt['approved_by_name'] ?? '—') ?></div></div>
                    <div class="rd-info-item"><div class="rd-info-label">Duyệt lúc</div><div class="rd-info-value" style="color:#10b981;"><?= date('d/m/Y H:i', strtotime($rcpt['approved_at'])) ?></div></div>
                    <?php endif; ?>
                    <?php if ($rcpt['status'] === 'cancelled' && $rcpt['cancelled_at']): ?>
                    <div class="rd-info-item"><div class="rd-info-label">Lý do hủy</div><div class="rd-info-value" style="color:#ef4444;"><?= htmlspecialchars($rcpt['cancel_reason'] ?? '—') ?></div></div>
                    <?php endif; ?>
                    <?php if ($rcpt['note']): ?>
                    <div class="rd-info-item" style="grid-column:1/-1;"><div class="rd-info-label">Ghi chú</div><div class="rd-info-value"><?= htmlspecialchars($rcpt['note']) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="rd-card" style="margin-top:16px;">
            <div class="rd-card-header"><i class="fas fa-boxes"></i> Sản phẩm trong phiếu</div>
            <table class="rd-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align:center;">Số lượng</th>
                        <th style="text-align:right;">Giá nhập</th>
                        <th style="text-align:right;">Thành tiền</th>
                        <th>Số lô</th>
                        <th>Vị trí kệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($item['product_image']): ?>
                                <img src="<?= BASE_URL ?>public/img/products/<?= htmlspecialchars($item['product_image']) ?>" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                                <?php else: ?>
                                <div style="width:36px;height:36px;border-radius:6px;background:#6366f1;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fas fa-microchip"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div style="font-size:11px;color:var(--text-faint);"><?= htmlspecialchars($item['category_name'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:center;font-size:18px;font-weight:800;color:#10b981;">+<?= number_format($item['quantity']) ?></td>
                        <td style="text-align:right;color:var(--text-muted);"><?= $item['unit_cost'] > 0 ? number_format($item['unit_cost'],0,',','.') . ' ₫' : '<span style="color:#cbd5e1;">—</span>' ?></td>
                        <td style="text-align:right;font-weight:700;color:#6366f1;"><?= $item['subtotal'] > 0 ? number_format($item['subtotal'],0,',','.') . ' ₫' : '<span style="color:#cbd5e1;font-weight:400;">—</span>' ?></td>
                        <td style="color:var(--text-muted);font-size:13px;"><?= htmlspecialchars($item['batch_no'] ?? '—') ?></td>
                        <td style="color:var(--text-muted);font-size:13px;"><?= htmlspecialchars($item['bin_location'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:700;font-size:14px;padding:12px 16px;">Tổng cộng:</td>
                        <td style="font-weight:800;font-size:16px;color:#6366f1;padding:12px 16px;"><?= number_format($rcpt['total_amount'],0,',','.') ?> ₫</td>
                        <td colspan="2" style="padding:12px 16px;color:var(--text-muted);font-size:13px;"><?= $rcpt['total_qty'] ?> sản phẩm</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Sidebar info -->
    <div class="rd-sidebar-col">
        <div class="rd-card">
            <div class="rd-card-header"><i class="fas fa-chart-pie"></i> Tóm tắt</div>
            <div class="rd-card-body">
                <div style="text-align:center;padding:10px 0 20px;">
                    <div style="font-size:36px;font-weight:800;color:#6366f1;"><?= number_format($rcpt['total_qty']) ?></div>
                    <div style="font-size:13px;color:var(--text-muted);">sản phẩm nhập</div>
                    <?php if ($rcpt['total_amount'] > 0): ?>
                    <div style="font-size:20px;font-weight:700;color:#10b981;margin-top:8px;"><?= number_format($rcpt['total_amount'],0,',','.') ?> ₫</div>
                    <div style="font-size:12px;color:var(--text-faint);">tổng giá trị</div>
                    <?php endif; ?>
                </div>
                <div style="border-top:1px solid #f1f5f9;padding-top:14px;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;">Phân bổ sản phẩm:</div>
                    <?php foreach (array_slice($items, 0, 5) as $it): ?>
                    <div style="display:flex;justify-content:space-between;font-size:13px;padding:3px 0;">
                        <span style="color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;"><?= htmlspecialchars($it['product_name']) ?></span>
                        <span style="font-weight:700;color:#6366f1;flex-shrink:0;margin-left:6px;">+<?= $it['quantity'] ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($items) > 5): ?>
                    <div style="font-size:12px;color:var(--text-faint);margin-top:4px;">... và <?= count($items) - 5 ?> sản phẩm khác</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Modal hủy phiếu -->
<div id="cancelModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:18px;width:100%;max-width:440px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.3);">
        <div style="background:linear-gradient(135deg,#ef4444,#dc2626);padding:20px 24px;color:white;">
            <h2 style="margin:0;font-size:18px;"><i class="fas fa-times-circle"></i> Hủy Phiếu Nhập Kho</h2>
        </div>
        <form method="POST" action="?page=inventory&action=cancel_receipt&id=<?= $rcpt['id'] ?>">
            <div style="padding:24px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:8px;">Lý do hủy <span style="color:#ef4444;">*</span></label>
                <textarea name="cancel_reason" rows="3" required placeholder="Nhập lý do hủy phiếu..."
                    style="width:100%;border:1px solid var(--border-muted);border-radius:8px;padding:10px;font-size:14px;resize:none;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;gap:10px;padding:0 24px 24px;">
                <button type="button" onclick="closeCancelModal()" class="btn btn-secondary" style="flex:1;">Quay lại</button>
                <button type="submit" class="btn btn-danger" style="flex:2;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;">
                    <i class="fas fa-times"></i> Xác Nhận Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.rd-layout { display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start;margin-top:16px; }
@media(max-width:900px){.rd-layout{grid-template-columns:1fr;}}
.rd-card { background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);overflow:hidden; }
.rd-card-header { padding:14px 20px;font-weight:700;font-size:14px;color:var(--text-primary);background:var(--bg-elevated);border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:8px; }
.rd-card-body { padding:20px; }
.rd-info-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:14px; }
.rd-info-item { }
.rd-info-label { font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;margin-bottom:4px; }
.rd-info-value { font-size:14px;color:var(--text-primary); }
.rd-table { width:100%;border-collapse:collapse; }
.rd-table th { padding:10px 16px;font-size:12px;font-weight:700;color:var(--text-muted);background:var(--bg-elevated);border-bottom:1px solid var(--border-subtle);text-align:left; }
.rd-table td { padding:10px 16px;border-bottom:1px solid var(--border-subtle);vertical-align:middle; }
.rd-table tbody tr:hover { background:var(--bg-surface); }
.rd-alert { padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-weight:600; }
.rd-alert-success { background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;color:#4ade80; }
.rd-alert-error { background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;color:#f87171; }
.btn-danger { background:linear-gradient(135deg,#ef4444,#dc2626);color:white;border:none; }
/* Timeline */
.rd-timeline { display:flex;align-items:center;background:var(--bg-surface);border-radius:14px;padding:16px 24px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px; }
.rd-step { display:flex;flex-direction:column;align-items:center;gap:6px; }
.rd-step-circle { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px; }
.rd-step.done .rd-step-circle { background:#10b981;color:white; }
.rd-step.active .rd-step-circle { background:#6366f1;color:white;box-shadow:0 0 0 4px rgba(99,102,241,.2); }
.rd-step.pending-step .rd-step-circle { background:var(--bg-elevated);color:var(--text-faint); }
.rd-step.cancelled .rd-step-circle { background:#ef4444;color:white; }
.rd-step-label { font-size:11px;font-weight:600;color:var(--text-muted);white-space:nowrap; }
.rd-step.done .rd-step-label { color:#10b981; }
.rd-step.active .rd-step-label { color:#6366f1; }
.rd-step-line { flex:1;height:2px;background:#e2e8f0;margin:0 8px;margin-bottom:20px; }
.rd-step-line.done { background:#10b981; }
</style>
<script>
function openCancelModal(){ document.getElementById('cancelModal').style.display='flex'; }
function closeCancelModal(){ document.getElementById('cancelModal').style.display='none'; }
</script>
