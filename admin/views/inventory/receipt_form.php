<?php
/**
 * Form tạo Phiếu Nhập Kho — có thể nhập nhiều sản phẩm cùng lúc
 * URL: ?page=inventory&action=receipt_form
 */
$supplierCtrl = new SupplierController($db);
$suppliers    = $supplierCtrl->getAll();
$warehouses   = $admin->getWarehouses();

// Lấy danh sách sản phẩm để chọn
$allProducts  = $db->query("SELECT p.id, p.name, p.quantity, p.price, c.name as cat
                             FROM products p LEFT JOIN categories c ON p.category_id=c.id
                             WHERE p.is_active=1 ORDER BY c.name, p.name")->fetchAll(PDO::FETCH_ASSOC);
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-file-import" style="color:#10b981;margin-right:10px;"></i>Tạo Phiếu Nhập Kho</h1>
        <p>Nhập hàng chính thức có mã phiếu và quy trình duyệt 2 bước</p>
    </div>
    <div class="page-header-right">
        <a href="?page=inventory&action=receipts" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Danh sách phiếu nhập
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="rf-alert rf-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="rf-layout">
    <!-- Form chính -->
    <div class="rf-main">
        <form method="POST" action="?page=inventory&action=receipt_form" id="receiptForm">
            <!-- Header info -->
            <div class="rf-card">
                <div class="rf-card-header">
                    <i class="fas fa-info-circle"></i> Thông tin phiếu nhập
                </div>
                <div class="rf-card-body">
                    <div class="rf-grid-3">
                        <div>
                            <label class="rf-label">Kho nhập <span class="req">*</span></label>
                            <select name="warehouse_id" class="rf-select" required>
                                <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?> (<?= $wh['code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="rf-label">Loại nhập <span class="req">*</span></label>
                            <select name="type" class="rf-select" required>
                                <option value="purchase">🛒 Mua từ nhà cung cấp</option>
                                <option value="return">↩️ Hàng hoàn từ khách</option>
                                <option value="transfer">🔄 Điều chuyển nội bộ</option>
                                <option value="adjustment">⚙️ Điều chỉnh kho</option>
                            </select>
                        </div>
                        <div>
                            <label class="rf-label">Nhà cung cấp</label>
                            <select name="supplier_id" class="rf-select">
                                <option value="">-- Chọn NCC (tùy chọn) --</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        <label class="rf-label">Ghi chú phiếu</label>
                        <textarea name="note" rows="2" class="rf-textarea" placeholder="Ghi chú chung cho phiếu nhập..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Bảng sản phẩm -->
            <div class="rf-card" style="margin-top:16px;">
                <div class="rf-card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-list"></i> Danh sách sản phẩm nhập</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="addRow()">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>
                <div class="rf-card-body" style="padding:0;">
                    <table class="rf-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:35%">Sản phẩm <span class="req">*</span></th>
                                <th style="width:12%">Tồn kho</th>
                                <th style="width:12%">Số lượng <span class="req">*</span></th>
                                <th style="width:15%">Giá nhập/đv (₫)</th>
                                <th style="width:13%">Thành tiền</th>
                                <th style="width:10%">Số lô</th>
                                <th style="width:3%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Dòng đầu tiên mặc định -->
                            <tr class="item-row" data-idx="0">
                                <td>
                                    <select name="items[0][product_id]" class="rf-select-sm product-select" required onchange="updateStock(this, 0)">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        <?php foreach ($allProducts as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-price="<?= $p['price'] ?>">
                                            <?= htmlspecialchars($p['name']) ?> (tồn: <?= $p['quantity'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><span class="stock-display" id="stock-0" style="font-weight:700;color:#6366f1;">—</span></td>
                                <td><input type="number" name="items[0][quantity]" class="rf-input-sm qty-input" min="1" required placeholder="0" oninput="calcRow(0)"></td>
                                <td><input type="number" name="items[0][unit_cost]" class="rf-input-sm cost-input" min="0" placeholder="0" oninput="calcRow(0)"></td>
                                <td><span class="subtotal-display" id="sub-0" style="font-weight:700;color:#10b981;">—</span></td>
                                <td><input type="text" name="items[0][batch_no]" class="rf-input-sm" placeholder="Lô A001"></td>
                                <td><button type="button" onclick="removeRow(this)" class="rf-btn-remove" title="Xóa dòng"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right;font-weight:700;padding:12px 16px;font-size:14px;">Tổng cộng:</td>
                                <td style="font-weight:800;font-size:16px;color:#6366f1;padding:12px 16px;" id="grandTotal">—</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="rf-actions">
                <button type="submit" name="submit_action" value="draft" class="btn btn-secondary btn-lg">
                    <i class="fas fa-save"></i> Lưu Nháp
                </button>
                <button type="submit" name="submit_action" value="pending" class="btn btn-warning btn-lg">
                    <i class="fas fa-paper-plane"></i> Gửi Chờ Duyệt
                </button>
            </div>
        </form>
    </div>

    <!-- Sidebar hướng dẫn -->
    <div class="rf-sidebar">
        <div class="rf-card rf-guide">
            <div class="rf-card-header"><i class="fas fa-route"></i> Quy trình duyệt</div>
            <div class="rf-card-body">
                <div class="rf-step">
                    <div class="rf-step-icon" style="background:#6366f1;">1</div>
                    <div><strong>Tạo phiếu nháp</strong><br><small style="color:var(--text-faint);">Điền thông tin, lưu nháp để chỉnh sửa</small></div>
                </div>
                <div class="rf-step-arrow">↓</div>
                <div class="rf-step">
                    <div class="rf-step-icon" style="background:#f59e0b;">2</div>
                    <div><strong>Gửi chờ duyệt</strong><br><small style="color:var(--text-faint);">Người có quyền sẽ nhận phiếu để duyệt</small></div>
                </div>
                <div class="rf-step-arrow">↓</div>
                <div class="rf-step">
                    <div class="rf-step-icon" style="background:#10b981;">3</div>
                    <div><strong>Duyệt → Cộng kho</strong><br><small style="color:var(--text-faint);">Khi duyệt, tồn kho tự động cập nhật</small></div>
                </div>
            </div>
        </div>

        <div class="rf-card" style="margin-top:16px;">
            <div class="rf-card-header"><i class="fas fa-chart-bar"></i> Tóm tắt đơn</div>
            <div class="rf-card-body" id="summaryPanel">
                <div style="text-align:center;color:var(--text-faint);font-size:13px;padding:20px 0;">
                    Thêm sản phẩm để xem tóm tắt
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Template row (ẩn) -->
<template id="rowTemplate">
    <tr class="item-row" data-idx="__IDX__">
        <td>
            <select name="items[__IDX__][product_id]" class="rf-select-sm product-select" required onchange="updateStock(this, __IDX__)">
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($allProducts as $p): ?>
                <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-price="<?= $p['price'] ?>">
                    <?= htmlspecialchars($p['name']) ?> (tồn: <?= $p['quantity'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><span class="stock-display" id="stock-__IDX__" style="font-weight:700;color:#6366f1;">—</span></td>
        <td><input type="number" name="items[__IDX__][quantity]" class="rf-input-sm qty-input" min="1" required placeholder="0" oninput="calcRow(__IDX__)"></td>
        <td><input type="number" name="items[__IDX__][unit_cost]" class="rf-input-sm cost-input" min="0" placeholder="0" oninput="calcRow(__IDX__)"></td>
        <td><span class="subtotal-display" id="sub-__IDX__" style="font-weight:700;color:#10b981;">—</span></td>
        <td><input type="text" name="items[__IDX__][batch_no]" class="rf-input-sm" placeholder="Lô A001"></td>
        <td><button type="button" onclick="removeRow(this)" class="rf-btn-remove"><i class="fas fa-times"></i></button></td>
    </tr>
</template>

<style>
.rf-layout { display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start; }
@media(max-width:900px){.rf-layout{grid-template-columns:1fr;}}
.rf-card { background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);overflow:hidden; }
.rf-card-header { padding:16px 20px;font-weight:700;font-size:15px;color:var(--text-primary);background:var(--bg-elevated);border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:8px; }
.rf-card-body { padding:20px; }
.rf-grid-3 { display:grid;grid-template-columns:repeat(3,1fr);gap:14px; }
@media(max-width:700px){.rf-grid-3{grid-template-columns:1fr;}}
.rf-label { display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px; }
.rf-select,.rf-textarea { width:100%;border:1px solid var(--border-muted);border-radius:8px;padding:10px 12px;font-size:14px;outline:none;background:var(--bg-surface);box-sizing:border-box;transition:border .2s; }
.rf-select:focus,.rf-textarea:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.rf-textarea { resize:vertical; }
.rf-table { width:100%;border-collapse:collapse; }
.rf-table thead tr { background:var(--bg-elevated); }
.rf-table th { padding:10px 12px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;border-bottom:1px solid var(--border-subtle); }
.rf-table td { padding:8px 12px;border-bottom:1px solid var(--border-subtle);vertical-align:middle; }
.rf-table tbody tr:hover { background:var(--bg-surface); }
.rf-select-sm { width:100%;border:1px solid var(--border-muted);border-radius:6px;padding:6px 8px;font-size:13px;outline:none;background:var(--bg-surface); }
.rf-input-sm { width:100%;border:1px solid var(--border-muted);border-radius:6px;padding:6px 8px;font-size:13px;outline:none;box-sizing:border-box;text-align:center; }
.rf-input-sm:focus,.rf-select-sm:focus { border-color:#6366f1; }
.rf-btn-remove { background:rgba(239,68,68,0.12);border:none;color:#dc2626;width:28px;height:28px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s; }
.rf-btn-remove:hover { background:#fecaca; }
.rf-actions { display:flex;gap:12px;margin-top:20px;justify-content:flex-end; }
.btn-lg { padding:12px 28px;font-size:15px;font-weight:700;border-radius:12px; }
.btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none; }
.rf-guide .rf-step { display:flex;align-items:center;gap:12px;margin-bottom:8px; }
.rf-step-icon { width:28px;height:28px;border-radius:50%;color:white;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.rf-step-arrow { text-align:center;color:#cbd5e1;font-size:18px;margin:2px 0 2px 14px; }
.rf-alert { padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px; }
.rf-alert-error { background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;color:#f87171; }
.req { color:#ef4444; }
</style>

<script>
let rowIdx = 1;

function addRow() {
    const tpl = document.getElementById('rowTemplate').innerHTML.replace(/__IDX__/g, rowIdx);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', tpl);
    rowIdx++;
    updateGrandTotal();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) { alert('Phiếu nhập phải có ít nhất 1 sản phẩm.'); return; }
    btn.closest('tr').remove();
    updateGrandTotal();
}

function updateStock(select, idx) {
    const opt = select.options[select.selectedIndex];
    const stockEl = document.getElementById('stock-' + idx);
    if (stockEl) {
        const qty = opt.getAttribute('data-qty');
        stockEl.textContent = qty !== null ? qty : '—';
    }
    calcRow(idx);
}

function calcRow(idx) {
    const row   = document.querySelector(`.item-row[data-idx="${idx}"]`);
    if (!row) return;
    const qty   = parseInt(row.querySelector('.qty-input').value) || 0;
    const cost  = parseInt(row.querySelector('.cost-input').value) || 0;
    const sub   = qty * cost;
    const subEl = document.getElementById('sub-' + idx);
    if (subEl) subEl.textContent = sub > 0 ? sub.toLocaleString('vi-VN') + ' ₫' : '—';
    updateGrandTotal();
}

function updateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty  = parseInt(row.querySelector('.qty-input')?.value) || 0;
        const cost = parseInt(row.querySelector('.cost-input')?.value) || 0;
        total += qty * cost;
    });
    document.getElementById('grandTotal').textContent = total > 0 ? total.toLocaleString('vi-VN') + ' ₫' : '—';

    // Update summary panel
    const items = document.querySelectorAll('.item-row');
    let summary = '';
    let totalQty = 0;
    items.forEach(row => {
        const sel = row.querySelector('.product-select');
        const qty = parseInt(row.querySelector('.qty-input')?.value) || 0;
        totalQty += qty;
        if (sel.value && qty > 0) {
            const name = sel.options[sel.selectedIndex].text.split(' (tồn:')[0];
            summary += `<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border-subtle);font-size:13px;">
                <span style="color:var(--text-secondary);">${name}</span><span style="font-weight:700;color:#6366f1;">+${qty}</span></div>`;
        }
    });
    document.getElementById('summaryPanel').innerHTML = summary
        ? `${summary}<div style="text-align:right;margin-top:8px;font-size:13px;">Tổng: <strong style="color:#10b981;">${totalQty} sản phẩm</strong></div>`
        : '<div style="text-align:center;color:var(--text-faint);font-size:13px;padding:20px 0;">Thêm sản phẩm để xem tóm tắt</div>';
}

// Validate trước khi submit
document.getElementById('receiptForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.item-row');
    let valid = false;
    rows.forEach(row => {
        const pid = row.querySelector('.product-select').value;
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        if (pid && qty > 0) valid = true;
    });
    if (!valid) { e.preventDefault(); alert('Vui lòng thêm ít nhất 1 sản phẩm với số lượng > 0.'); }
});
</script>
