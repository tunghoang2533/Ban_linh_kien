<?php
/**
 * Form tạo Purchase Order (Đặt hàng NCC)
 * URL: ?page=inventory&action=po_form
 */
$supplierCtrl = new SupplierController($db);
$suppliers    = $supplierCtrl->getAll();
$warehouses   = $admin->getWarehouses();

// Lấy danh sách sản phẩm để chọn
$allProducts = $db->query("SELECT p.id, p.name, p.quantity, p.price, c.name as cat
                            FROM products p LEFT JOIN categories c ON p.category_id=c.id
                            WHERE p.is_active=1 ORDER BY c.name, p.name")->fetchAll(PDO::FETCH_ASSOC);
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-file-signature" style="color:#6366f1;margin-right:10px;"></i>Tạo Đơn Đặt Hàng (Purchase Order)</h1>
        <p>Đặt hàng từ nhà cung cấp — luồng: Nháp → Duyệt → Đặt hàng → Nhận hàng</p>
    </div>
    <div class="page-header-right">
        <a href="?page=inventory&action=purchase_orders" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Danh sách PO
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;color:#f87171;font-weight:600;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="rf-layout">
    <div class="rf-main">
        <form method="POST" action="?page=inventory&action=po_form" id="poForm">

            <!-- Thông tin đơn đặt hàng -->
            <div class="rf-card">
                <div class="rf-card-header"><i class="fas fa-clipboard-list"></i> Thông tin đơn đặt hàng</div>
                <div class="rf-card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label class="rf-label">Nhà cung cấp <span class="req">*</span></label>
                            <select name="supplier_id" class="rf-select" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?>
                                    <?= $s['phone'] ? '— ' . $s['phone'] : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="rf-label">Kho nhận hàng <span class="req">*</span></label>
                            <select name="warehouse_id" class="rf-select" required>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?> (<?= $wh['code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="rf-label">Ngày dự nhận hàng</label>
                            <input type="date" name="expected_date" class="rf-select" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="rf-label">Ghi chú đơn hàng</label>
                            <input type="text" name="note" class="rf-select" placeholder="Ghi chú thêm...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="rf-card" style="margin-top:16px;">
                <div class="rf-card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-list"></i> Sản phẩm cần đặt</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="addPORow()">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>
                <div class="rf-card-body" style="padding:0;">
                    <table class="rf-table" id="poItemsTable">
                        <thead>
                            <tr>
                                <th style="width:40%">Sản phẩm <span class="req">*</span></th>
                                <th style="width:12%">Tồn kho hiện tại</th>
                                <th style="width:12%">Số lượng đặt <span class="req">*</span></th>
                                <th style="width:16%">Giá dự kiến/đv (₫)</th>
                                <th style="width:14%">Thành tiền</th>
                                <th style="width:6%;"></th>
                            </tr>
                        </thead>
                        <tbody id="poItemsBody">
                            <tr class="po-row" data-idx="0">
                                <td>
                                    <select name="items[0][product_id]" class="rf-select-sm product-select" required onchange="updatePOStock(this, 0)">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        <?php foreach ($allProducts as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-price="<?= $p['price'] ?>">
                                            <?= htmlspecialchars($p['name']) ?> (tồn: <?= $p['quantity'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><span class="po-stock" id="postock-0" style="font-weight:700;color:#6366f1;">—</span></td>
                                <td><input type="number" name="items[0][quantity]" class="rf-input-sm po-qty" min="1" required placeholder="0" oninput="calcPORow(0)"></td>
                                <td><input type="number" name="items[0][unit_cost]" class="rf-input-sm po-cost" min="0" placeholder="0" oninput="calcPORow(0)"></td>
                                <td><span id="posub-0" style="font-weight:700;color:#10b981;">—</span></td>
                                <td><button type="button" onclick="removePORow(this)" class="rf-btn-remove"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:right;font-weight:700;padding:12px 16px;font-size:14px;">Tổng:</td>
                                <td></td>
                                <td style="font-weight:800;font-size:16px;color:#6366f1;padding:12px 16px;" id="poGrandTotal">—</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="rf-actions" style="margin-top:20px;">
                <button type="submit" name="submit_action" value="draft" class="btn btn-secondary btn-lg">
                    <i class="fas fa-save"></i> Lưu Nháp
                </button>
                <button type="submit" name="submit_action" value="pending" class="btn btn-warning btn-lg">
                    <i class="fas fa-paper-plane"></i> Gửi Chờ Duyệt
                </button>
            </div>
        </form>
    </div>

    <!-- Sidebar: Thông tin NCC + hướng dẫn -->
    <div class="rf-sidebar">
        <div class="rf-card rf-guide">
            <div class="rf-card-header"><i class="fas fa-route"></i> Luồng đặt hàng</div>
            <div class="rf-card-body">
                <?php
                $poSteps = [
                    ['color'=>'#6366f1','num'=>1,'title'=>'Tạo đơn (nháp)','desc'=>'Điền sản phẩm và số lượng cần đặt'],
                    ['color'=>'#f59e0b','num'=>2,'title'=>'Gửi chờ duyệt','desc'=>'Quản lý xem xét và duyệt đơn'],
                    ['color'=>'#0ea5e9','num'=>3,'title'=>'Đặt hàng NCC','desc'=>'Gửi đơn đặt hàng cho nhà cung cấp'],
                    ['color'=>'#10b981','num'=>4,'title'=>'Nhận hàng','desc'=>'Xác nhận nhận hàng → Phiếu nhập tự tạo'],
                ];
                foreach ($poSteps as $i => $step):
                ?>
                <div class="rf-step">
                    <div class="rf-step-icon" style="background:<?= $step['color'] ?>;"><?= $step['num'] ?></div>
                    <div>
                        <strong style="font-size:13px;"><?= $step['title'] ?></strong>
                        <div style="font-size:11px;color:var(--text-faint);margin-top:2px;"><?= $step['desc'] ?></div>
                    </div>
                </div>
                <?php if ($i < count($poSteps)-1): ?>
                <div class="rf-step-arrow">↓</div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <!-- Tóm tắt -->
        <div class="rf-card" style="margin-top:16px;">
            <div class="rf-card-header"><i class="fas fa-chart-bar"></i> Tóm tắt đơn</div>
            <div class="rf-card-body" id="poSummaryPanel">
                <div style="text-align:center;color:var(--text-faint);font-size:13px;padding:20px 0;">Thêm sản phẩm để xem tóm tắt</div>
            </div>
        </div>
    </div>
</div>
</main>

<template id="poRowTemplate">
    <tr class="po-row" data-idx="__IDX__">
        <td>
            <select name="items[__IDX__][product_id]" class="rf-select-sm product-select" required onchange="updatePOStock(this, __IDX__)">
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($allProducts as $p): ?>
                <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-price="<?= $p['price'] ?>">
                    <?= htmlspecialchars($p['name']) ?> (tồn: <?= $p['quantity'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><span class="po-stock" id="postock-__IDX__" style="font-weight:700;color:#6366f1;">—</span></td>
        <td><input type="number" name="items[__IDX__][quantity]" class="rf-input-sm po-qty" min="1" required placeholder="0" oninput="calcPORow(__IDX__)"></td>
        <td><input type="number" name="items[__IDX__][unit_cost]" class="rf-input-sm po-cost" min="0" placeholder="0" oninput="calcPORow(__IDX__)"></td>
        <td><span id="posub-__IDX__" style="font-weight:700;color:#10b981;">—</span></td>
        <td><button type="button" onclick="removePORow(this)" class="rf-btn-remove"><i class="fas fa-times"></i></button></td>
    </tr>
</template>

<style>
.rf-layout { display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start; }
.rf-card { background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);overflow:hidden; }
.rf-card-header { padding:16px 20px;font-weight:700;font-size:15px;color:var(--text-primary);background:var(--bg-elevated);border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:8px; }
.rf-card-body { padding:20px; }
.rf-label { display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px; }
.rf-select { width:100%;border:1px solid var(--border-muted);border-radius:8px;padding:10px 12px;font-size:14px;outline:none;background:var(--bg-surface);box-sizing:border-box;transition:border .2s; }
.rf-select:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.rf-table { width:100%;border-collapse:collapse; }
.rf-table thead tr { background:var(--bg-elevated); }
.rf-table th { padding:10px 12px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;border-bottom:1px solid var(--border-subtle); }
.rf-table td { padding:8px 12px;border-bottom:1px solid var(--border-subtle);vertical-align:middle; }
.rf-select-sm { width:100%;border:1px solid var(--border-muted);border-radius:6px;padding:6px 8px;font-size:13px;outline:none;background:var(--bg-surface); }
.rf-input-sm { width:100%;border:1px solid var(--border-muted);border-radius:6px;padding:6px 8px;font-size:13px;outline:none;box-sizing:border-box;text-align:center; }
.rf-btn-remove { background:rgba(239,68,68,0.12);border:none;color:#dc2626;width:28px;height:28px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center; }
.rf-actions { display:flex;gap:12px;justify-content:flex-end; }
.btn-lg { padding:12px 28px;font-size:15px;font-weight:700;border-radius:12px; }
.btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none; }
.rf-guide .rf-step { display:flex;align-items:center;gap:12px;margin-bottom:8px; }
.rf-step-icon { width:28px;height:28px;border-radius:50%;color:white;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.rf-step-arrow { text-align:center;color:#cbd5e1;margin:2px 0 2px 14px; }
.req { color:#ef4444; }
</style>

<script>
let poIdx = 1;
function addPORow() {
    const tpl = document.getElementById('poRowTemplate').innerHTML.replace(/__IDX__/g, poIdx);
    document.getElementById('poItemsBody').insertAdjacentHTML('beforeend', tpl);
    poIdx++; updatePOTotal();
}
function removePORow(btn) {
    if (document.querySelectorAll('.po-row').length <= 1) { alert('Phải có ít nhất 1 sản phẩm.'); return; }
    btn.closest('tr').remove(); updatePOTotal();
}
function updatePOStock(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    const el = document.getElementById('postock-' + idx);
    if (el) el.textContent = opt.getAttribute('data-qty') ?? '—';
    calcPORow(idx);
}
function calcPORow(idx) {
    const row = document.querySelector(`.po-row[data-idx="${idx}"]`);
    if (!row) return;
    const qty = parseInt(row.querySelector('.po-qty').value) || 0;
    const cost = parseInt(row.querySelector('.po-cost').value) || 0;
    const sub = document.getElementById('posub-' + idx);
    if (sub) sub.textContent = qty * cost > 0 ? (qty * cost).toLocaleString('vi-VN') + ' ₫' : '—';
    updatePOTotal();
}
function updatePOTotal() {
    let total = 0, totalQty = 0;
    let summary = '';
    document.querySelectorAll('.po-row').forEach(row => {
        const qty = parseInt(row.querySelector('.po-qty')?.value) || 0;
        const cost = parseInt(row.querySelector('.po-cost')?.value) || 0;
        const sel = row.querySelector('.product-select');
        total += qty * cost; totalQty += qty;
        if (sel.value && qty > 0) {
            const name = sel.options[sel.selectedIndex].text.split(' (tồn:')[0];
            summary += `<div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;border-bottom:1px solid var(--border-subtle);">
                <span style="color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;max-width:150px;">${name}</span>
                <span style="font-weight:700;color:#6366f1;">${qty} SP</span></div>`;
        }
    });
    document.getElementById('poGrandTotal').textContent = total > 0 ? total.toLocaleString('vi-VN') + ' ₫' : '—';
    document.getElementById('poSummaryPanel').innerHTML = summary
        ? `${summary}<div style="text-align:right;margin-top:8px;font-size:13px;">Tổng: <strong style="color:#10b981;">${totalQty} sản phẩm</strong></div>`
        : '<div style="text-align:center;color:var(--text-faint);font-size:13px;padding:20px 0;">Thêm sản phẩm để xem tóm tắt</div>';
}
document.getElementById('poForm').addEventListener('submit', e => {
    let valid = false;
    document.querySelectorAll('.po-row').forEach(r => {
        if (r.querySelector('.product-select').value && parseInt(r.querySelector('.po-qty').value) > 0) valid = true;
    });
    if (!valid) { e.preventDefault(); alert('Vui lòng thêm ít nhất 1 sản phẩm với số lượng > 0.'); }
});
</script>
