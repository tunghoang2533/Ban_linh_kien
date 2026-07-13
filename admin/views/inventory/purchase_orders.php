<?php
/**
 * Danh sách Purchase Orders — Đặt hàng nhà cung cấp
 * URL: ?page=inventory&action=purchase_orders
 */
$warehouses   = $warehouses   ?? [];
$suppliers    = $suppliers    ?? [];
$pos          = $pos          ?? [];
$poStats      = $poStats      ?? [];
$filterWarehouse = $filterWarehouse ?? null;
$filterStatus    = $filterStatus    ?? 'all';
$filterSupplier  = $filterSupplier  ?? null;

$statusList = [
    'all'       => ['label'=>'Tất cả',         'color'=>'#64748b', 'bg'=>'#f1f5f9', 'icon'=>'fa-list'],
    'draft'     => ['label'=>'Nháp',           'color'=>'#64748b', 'bg'=>'#f1f5f9', 'icon'=>'fa-edit'],
    'pending'   => ['label'=>'Chờ duyệt',      'color'=>'#f59e0b', 'bg'=>'#fef3c7', 'icon'=>'fa-clock'],
    'approved'  => ['label'=>'Đã duyệt',       'color'=>'#6366f1', 'bg'=>'#ede9fe', 'icon'=>'fa-check-circle'],
    'ordered'   => ['label'=>'Đã đặt hàng',    'color'=>'#0ea5e9', 'bg'=>'#e0f2fe', 'icon'=>'fa-truck'],
    'received'  => ['label'=>'Đã nhận hàng',   'color'=>'#10b981', 'bg'=>'#d1fae5', 'icon'=>'fa-box-open'],
    'cancelled' => ['label'=>'Đã hủy',         'color'=>'#ef4444', 'bg'=>'#fee2e2', 'icon'=>'fa-times-circle'],
];
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-file-signature" style="color:#6366f1;margin-right:10px;"></i>Đặt Hàng Nhà Cung Cấp (PO)</h1>
        <p>Quản lý đơn đặt hàng: Nháp → Duyệt → Đặt hàng → Nhận hàng</p>
    </div>
    <div class="page-header-right">
        <a href="?page=inventory&action=po_form" class="btn btn-primary" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;padding:10px 20px;border-radius:10px;font-weight:700;display:flex;align-items:center;gap:8px;text-decoration:none;">
            <i class="fas fa-plus"></i> Tạo Đơn Đặt Hàng
        </a>
    </div>
</div>

<?php if (!empty($successMessage)): ?>
<div class="po-alert po-alert-success" id="poSuccessAlert">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
    <button onclick="this.parentElement.style.display='none'" style="background:none;border:none;cursor:pointer;float:right;color:inherit;font-size:16px;">×</button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="po-alert po-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Luồng trạng thái PO -->
<div class="po-flow-bar">
    <div class="po-flow-step po-flow-draft"><i class="fas fa-edit"></i><span>Nháp</span></div>
    <div class="po-flow-arrow">→</div>
    <div class="po-flow-step po-flow-pending"><i class="fas fa-clock"></i><span>Chờ duyệt</span></div>
    <div class="po-flow-arrow">→</div>
    <div class="po-flow-step po-flow-approved"><i class="fas fa-check-circle"></i><span>Đã duyệt</span></div>
    <div class="po-flow-arrow">→</div>
    <div class="po-flow-step po-flow-ordered"><i class="fas fa-truck"></i><span>Đã đặt hàng</span></div>
    <div class="po-flow-arrow">→</div>
    <div class="po-flow-step po-flow-received"><i class="fas fa-box-open"></i><span>Nhận hàng</span></div>
</div>

<!-- Stat cards -->
<div class="po-stats-grid">
    <?php foreach (['draft','pending','approved','ordered','received','cancelled'] as $s): ?>
    <?php
        $si  = $statusList[$s];
        $cnt = (int)($poStats[$s] ?? 0);
    ?>
    <a href="?page=inventory&action=purchase_orders&status=<?= $s ?><?= $filterWarehouse ? '&warehouse='.$filterWarehouse : '' ?>" class="po-stat-card <?= $filterStatus === $s ? 'po-stat-active' : '' ?>" style="--card-color:<?= $si['color'] ?>;--card-bg:<?= $si['bg'] ?>;">
        <div class="po-stat-icon"><i class="fas <?= $si['icon'] ?>"></i></div>
        <div class="po-stat-count"><?= number_format($cnt) ?></div>
        <div class="po-stat-label"><?= $si['label'] ?></div>
        <?php if ($s === 'pending' && $cnt > 0): ?>
        <div class="po-stat-pulse"></div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Bộ lọc -->
<div class="dashboard-section" style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;flex:1;">
        <input type="hidden" name="page" value="inventory">
        <input type="hidden" name="action" value="purchase_orders">
        <?php if ($filterStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>

        <select name="warehouse" class="po-filter-select" onchange="this.form.submit()">
            <option value="">🏭 Tất cả kho</option>
            <?php foreach ($warehouses as $wh): ?>
            <option value="<?= $wh['id'] ?>" <?= $filterWarehouse == $wh['id'] ? 'selected' : '' ?>>
                Kho <?= htmlspecialchars($wh['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="supplier" class="po-filter-select" onchange="this.form.submit()">
            <option value="">🏢 Tất cả NCC</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $filterSupplier == $s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <?php if ($filterWarehouse || $filterSupplier || $filterStatus !== 'all'): ?>
        <a href="?page=inventory&action=purchase_orders" class="btn btn-sm" style="background:var(--bg-elevated);color:var(--text-muted);border:none;padding:8px 14px;border-radius:8px;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>

    <!-- Tabs trạng thái -->
    <div style="display:flex;gap:4px;flex-wrap:wrap;">
        <?php foreach ($statusList as $sk => $si): ?>
        <a href="?page=inventory&action=purchase_orders&status=<?= $sk ?><?= $filterWarehouse ? '&warehouse='.$filterWarehouse : '' ?><?= $filterSupplier ? '&supplier='.$filterSupplier : '' ?>"
           style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;background:<?= $filterStatus === $sk ? $si['bg'] : '#f8fafc' ?>;color:<?= $filterStatus === $sk ? $si['color'] : '#64748b' ?>;border:1.5px solid <?= $filterStatus === $sk ? $si['color'] : '#e2e8f0' ?>;transition:.2s;white-space:nowrap;">
            <?= $si['label'] ?>
            <?php $c = $sk === 'all' ? array_sum($poStats) : (int)($poStats[$sk]??0); if ($c > 0): ?>
            <span style="background:<?= $si['color'] ?>;color:white;padding:0 5px;border-radius:10px;font-size:10px;margin-left:4px;"><?= $c ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bảng PO -->
<div class="dashboard-section" style="padding:0;overflow:hidden;">
    <table class="admin-table po-table">
        <thead>
            <tr>
                <th>Mã PO</th>
                <th>Kho nhận</th>
                <th>Nhà cung cấp</th>
                <th>Trạng thái</th>
                <th style="text-align:center;">SL</th>
                <th style="text-align:right;">Tổng giá trị</th>
                <th>Ngày dự nhận</th>
                <th>Người tạo</th>
                <th>Ngày tạo</th>
                <th style="text-align:center;min-width:160px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pos)): ?>
            <tr>
                <td colspan="10">
                    <div style="text-align:center;padding:60px 20px;color:var(--text-faint);">
                        <i class="fas fa-file-signature" style="font-size:54px;display:block;margin-bottom:16px;opacity:.25;"></i>
                        <div style="font-size:17px;font-weight:600;margin-bottom:8px;">Chưa có đơn đặt hàng nào</div>
                        <div style="font-size:13px;margin-bottom:20px;">Tạo đơn đầu tiên để quản lý quy trình đặt hàng NCC</div>
                        <a href="?page=inventory&action=po_form" class="btn btn-primary" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;padding:10px 22px;border-radius:10px;font-weight:700;text-decoration:none;">
                            <i class="fas fa-plus"></i> Tạo đơn đặt hàng
                        </a>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($pos as $po):
                $si = $statusList[$po['status']] ?? $statusList['draft'];
            ?>
            <tr class="po-row-<?= $po['status'] ?>" onclick="window.location='?page=inventory&action=purchase_orders&view_id=<?= $po['id'] ?>'" style="cursor:pointer;">
                <td onclick="event.stopPropagation()">
                    <div style="display:flex;flex-direction:column;gap:2px;">
                        <span style="font-family:monospace;font-weight:700;font-size:13px;color:#6366f1;background:rgba(99,102,241,0.12);padding:2px 8px;border-radius:6px;display:inline-block;"><?= htmlspecialchars($po['po_code']) ?></span>
                        <?php if ($po['status'] === 'pending'): ?>
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#f59e0b;font-weight:600;"><span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;animation:pulse-badge 1.5s infinite;"></span>Cần duyệt</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="width:28px;height:28px;border-radius:6px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;flex-shrink:0;">
                            <?= htmlspecialchars($po['warehouse_code'] ?? '—') ?>
                        </div>
                        <span style="font-size:13px;color:var(--text-secondary);"><?= htmlspecialchars($po['warehouse_name'] ?? '—') ?></span>
                    </div>
                </td>
                <td>
                    <?php if ($po['supplier_name']): ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#10b981,#34d399);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;">
                            <?= strtoupper(mb_substr($po['supplier_name'], 0, 1)) ?>
                        </div>
                        <span style="font-size:13px;font-weight:600;color:var(--text-primary);"><?= htmlspecialchars($po['supplier_name']) ?></span>
                    </div>
                    <?php else: ?>
                    <span style="color:#cbd5e1;font-size:13px;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;background:<?= $si['bg'] ?>;color:<?= $si['color'] ?>;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        <i class="fas <?= $si['icon'] ?>" style="font-size:10px;"></i>
                        <?= $si['label'] ?>
                    </span>
                </td>
                <td style="text-align:center;font-size:18px;font-weight:800;color:#6366f1;"><?= number_format($po['total_qty']) ?></td>
                <td style="text-align:right;font-weight:700;color:#10b981;font-size:13px;">
                    <?= $po['total_amount'] > 0 ? number_format($po['total_amount'],0,',','.') . ' ₫' : '<span style="color:#cbd5e1;">—</span>' ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">
                    <?= $po['expected_date'] ? '📅 ' . date('d/m/Y', strtotime($po['expected_date'])) : '<span style="color:#cbd5e1;">—</span>' ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($po['created_by_name'] ?? '—') ?></td>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                    <i class="fas fa-calendar" style="color:#cbd5e1;margin-right:4px;"></i>
                    <?= date('d/m/Y', strtotime($po['created_at'])) ?>
                    <div style="font-size:10px;color:var(--text-faint);"><?= date('H:i', strtotime($po['created_at'])) ?></div>
                </td>
                <td style="text-align:center;" onclick="event.stopPropagation()">
                    <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                        <?php if ($po['status'] === 'draft'): ?>
                            <form method="POST" action="?page=inventory&action=submit_po&id=<?= $po['id'] ?>" style="display:inline;" onsubmit="return confirm('Gửi đơn này để duyệt?')">
                                <button type="submit" class="po-btn po-btn-warning" title="Gửi duyệt"><i class="fas fa-paper-plane"></i></button>
                            </form>
                            <a href="?page=inventory&action=po_form&edit_id=<?= $po['id'] ?>" class="po-btn po-btn-secondary" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                            <button onclick="openCancelPO(<?= $po['id'] ?>)" class="po-btn po-btn-danger" title="Hủy"><i class="fas fa-times"></i></button>
                        <?php elseif ($po['status'] === 'pending'): ?>
                            <form method="POST" action="?page=inventory&action=approve_po&id=<?= $po['id'] ?>" style="display:inline;" onsubmit="return confirm('Duyệt đơn đặt hàng này?')">
                                <button type="submit" class="po-btn po-btn-success" title="Duyệt"><i class="fas fa-check"></i></button>
                            </form>
                            <button onclick="openCancelPO(<?= $po['id'] ?>)" class="po-btn po-btn-danger" title="Hủy"><i class="fas fa-times"></i></button>
                        <?php elseif ($po['status'] === 'approved'): ?>
                            <form method="POST" action="?page=inventory&action=order_po&id=<?= $po['id'] ?>" style="display:inline;" onsubmit="return confirm('Đánh dấu đã gửi đơn cho NCC?')">
                                <button type="submit" class="po-btn po-btn-info" title="Đặt hàng NCC"><i class="fas fa-truck"></i></button>
                            </form>
                            <form method="POST" action="?page=inventory&action=receive_po&id=<?= $po['id'] ?>" style="display:inline;" onsubmit="return confirm('Xác nhận đã nhận hàng?\nPhiếu nhập kho sẽ tự động tạo và tồn kho sẽ được cộng.')">
                                <button type="submit" class="po-btn po-btn-success" title="Nhận hàng"><i class="fas fa-box-open"></i></button>
                            </form>
                        <?php elseif ($po['status'] === 'ordered'): ?>
                            <form method="POST" action="?page=inventory&action=receive_po&id=<?= $po['id'] ?>" style="display:inline;" onsubmit="return confirm('Xác nhận đã nhận hàng?\nPhiếu nhập kho sẽ tự động tạo và tồn kho sẽ được cộng.')">
                                <button type="submit" class="po-btn po-btn-success" title="Nhận hàng"><i class="fas fa-box-open"></i> Nhận hàng</button>
                            </form>
                        <?php else: ?>
                            <span style="font-size:11px;color:var(--text-faint);padding:4px 8px;">
                                <?= $si['label'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal hủy PO -->
<div id="cancelPOModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:18px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.3);">
        <div style="background:linear-gradient(135deg,#ef4444,#dc2626);padding:20px 24px;color:white;">
            <h2 style="margin:0;font-size:18px;"><i class="fas fa-times-circle"></i> Hủy Đơn Đặt Hàng</h2>
        </div>
        <form id="cancelPOForm" method="POST">
            <div style="padding:24px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:8px;">Lý do hủy <span style="color:#ef4444;">*</span></label>
                <textarea name="cancel_reason" rows="3" required placeholder="Nhập lý do hủy đơn..."
                    style="width:100%;border:1px solid var(--border-muted);border-radius:8px;padding:10px;font-size:14px;resize:none;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;gap:10px;padding:0 24px 24px;">
                <button type="button" onclick="closeCancelPO()" class="btn btn-secondary" style="flex:1;">Quay lại</button>
                <button type="submit" style="flex:2;background:linear-gradient(135deg,#ef4444,#dc2626);color:white;border:none;padding:10px 20px;border-radius:10px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-times"></i> Xác Nhận Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* PO Flow bar */
.po-flow-bar { display:flex;align-items:center;background:var(--bg-surface);border-radius:14px;padding:14px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;gap:6px;overflow-x:auto; }
.po-flow-step { display:flex;flex-direction:column;align-items:center;gap:4px;font-size:11px;font-weight:600;white-space:nowrap;padding:4px 10px;border-radius:8px; }
.po-flow-step i { font-size:16px;margin-bottom:2px; }
.po-flow-arrow { color:#cbd5e1;font-size:16px;flex-shrink:0; }
.po-flow-draft   { color:var(--text-muted);background:var(--bg-elevated); }
.po-flow-pending { color:#f59e0b;background:rgba(245,158,11,0.12); }
.po-flow-approved{ color:#6366f1;background:rgba(99,102,241,0.12); }
.po-flow-ordered { color:#0ea5e9;background:rgba(6,182,212,0.12); }
.po-flow-received{ color:#10b981;background:rgba(34,197,94,0.12); }
/* Stat cards */
.po-stats-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px; }
@media(max-width:1100px){.po-stats-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.po-stats-grid{grid-template-columns:repeat(2,1fr);}}
.po-stat-card { background:var(--bg-surface);border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-decoration:none;border:2px solid transparent;transition:.25s;position:relative;overflow:hidden;text-align:center; }
.po-stat-card:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.1);border-color:var(--card-color); }
.po-stat-active { border-color:var(--card-color) !important;background:var(--card-bg); }
.po-stat-icon { font-size:22px;color:var(--card-color);margin-bottom:6px; }
.po-stat-count { font-size:26px;font-weight:800;color:var(--text-primary);line-height:1; }
.po-stat-label { font-size:11px;font-weight:600;color:var(--text-muted);margin-top:4px; }
.po-stat-pulse { position:absolute;top:10px;right:10px;width:8px;height:8px;border-radius:50%;background:#f59e0b;animation:pulse-badge 1.5s infinite; }
/* Filter */
.po-filter-select { border:1px solid var(--border-muted);border-radius:8px;padding:7px 12px;font-size:13px;outline:none;background:var(--bg-surface);cursor:pointer; }
.po-filter-select:focus { border-color:#6366f1; }
/* Table */
.po-table td { font-size:13px; }
.po-row-pending { border-left:3px solid #f59e0b; }
.po-row-received td:first-child { border-left:3px solid #10b981; }
/* Buttons */
.po-btn { padding:5px 8px;border:none;border-radius:7px;cursor:pointer;font-size:12px;font-weight:700;transition:.2s;display:inline-flex;align-items:center;gap:4px; }
.po-btn:hover { filter:brightness(.9);transform:scale(.97); }
.po-btn-success { background:rgba(34,197,94,0.12);color:#4ade80; }
.po-btn-warning { background:rgba(245,158,11,0.12);color:#fbbf24; }
.po-btn-danger  { background:rgba(239,68,68,0.12);color:#f87171; }
.po-btn-info    { background:rgba(6,182,212,0.12);color:#22d3ee; }
.po-btn-secondary { background:var(--bg-elevated);color:var(--text-muted);text-decoration:none; }
/* Alerts */
.po-alert { padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-weight:600; }
.po-alert-success { background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;color:#4ade80; }
.po-alert-error   { background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;color:#f87171; }
</style>

<script>
function openCancelPO(id) {
    document.getElementById('cancelPOForm').action = '?page=inventory&action=cancel_po&id=' + id;
    document.getElementById('cancelPOModal').style.display = 'flex';
}
function closeCancelPO() { document.getElementById('cancelPOModal').style.display = 'none'; }

// Auto-hide success
const alert = document.getElementById('poSuccessAlert');
if (alert) setTimeout(() => { alert.style.opacity = '0'; alert.style.transition = 'opacity .5s'; setTimeout(() => alert.remove(), 500); }, 5000);
</script>
