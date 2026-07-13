<?php
/**
 * Kiểm kê từng sản phẩm trong 1 phiên
 * URL: ?page=inventory&action=stocktake_count&id=X
 */
$session = $stocktakeSession ?? null;
$items   = $stocktakeItems   ?? [];
if (!$session): ?>
<main class="admin-main">
<div style="text-align:center;padding:80px 20px;color:var(--text-faint);">
    <i class="fas fa-exclamation-circle" style="font-size:60px;display:block;margin-bottom:16px;opacity:.3;"></i>
    Không tìm thấy phiên kiểm kê
    <br><a href="?page=inventory&action=stocktake" class="btn btn-secondary" style="margin-top:20px;">Quay lại</a>
</div>
</main>
<?php return; endif;

$statusInfo = InventoryController::getStocktakeStatusLabel($session['status']);
$counted    = (int)$session['counted_products'];
$total      = (int)$session['total_products'];
$pct        = $total > 0 ? min(100, round($counted / $total * 100)) : 0;
$isClosed   = in_array($session['status'], ['closed', 'cancelled']);
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-clipboard-check" style="color:#f59e0b;margin-right:10px;"></i>
            Kiểm Kê — <code><?= htmlspecialchars($session['session_code']) ?></code>
        </h1>
        <p>
            <span style="background:<?= $statusInfo['bg'] ?>;color:<?= $statusInfo['color'] ?>;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;"><?= $statusInfo['label'] ?></span>
            &nbsp;
            <span style="font-size:13px;color:var(--text-muted);">Kho: <strong><?= htmlspecialchars($session['warehouse_name']) ?></strong></span>
        </p>
    </div>
    <div class="page-header-right">
        <a href="?page=inventory&action=stocktake" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Danh sách</a>
        <?php if (!$isClosed): ?>
        <form method="POST" action="?page=inventory&action=close_stocktake&id=<?= $session['id'] ?>" style="display:inline;"
              onsubmit="return confirm('Đóng phiên kiểm kê này?\n\nCác sản phẩm chưa kiểm sẽ KHÔNG bị điều chỉnh.\nChỉ sản phẩm có số liệu thực tế mới được áp dụng.')">
            <button type="submit" class="btn btn-success"><i class="fas fa-lock"></i> Đóng Phiên & Áp Dụng</button>
        </form>
        <form method="POST" action="?page=inventory&action=cancel_stocktake&id=<?= $session['id'] ?>" style="display:inline;"
              onsubmit="return confirm('Hủy phiên kiểm kê? Dữ liệu đã nhập sẽ bị bỏ.')">
            <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Hủy Phiên</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($successMessage)): ?>
<div style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-weight:600;color:#4ade80;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
</div>
<?php endif; ?>

<!-- Progress bar + stats -->
<div class="sk-progress-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
            <span style="font-size:22px;font-weight:800;color:var(--text-primary);"><?= $counted ?></span>
            <span style="font-size:14px;color:var(--text-faint);"> / <?= $total ?> sản phẩm đã kiểm</span>
        </div>
        <div style="font-size:24px;font-weight:800;color:<?= $pct >= 100 ? '#10b981' : '#6366f1'; ?>"><?= $pct ?>%</div>
    </div>
    <div style="width:100%;height:10px;background:var(--bg-elevated);border-radius:10px;overflow:hidden;">
        <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,#6366f1,#10b981);border-radius:10px;transition:width .5s;"></div>
    </div>
    <div style="display:flex;gap:24px;margin-top:14px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:10px;height:10px;border-radius:50%;background:#10b981;"></div>
            <span style="font-size:13px;color:var(--text-muted);">Chưa kiểm: <strong><?= $total - $counted ?></strong></span>
        </div>
        <?php if ($session['variance_plus'] > 0 || $session['variance_minus'] > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:#10b981;font-weight:700;">+<?= $session['variance_plus'] ?></span>
            <span style="font-size:13px;color:#ef4444;font-weight:700;">-<?= $session['variance_minus'] ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Search + filter -->
<div style="display:flex;gap:10px;margin-bottom:16px;align-items:center;">
    <form method="GET" style="display:flex;gap:8px;flex:1;">
        <input type="hidden" name="page" value="inventory">
        <input type="hidden" name="action" value="stocktake_count">
        <input type="hidden" name="id" value="<?= $session['id'] ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
               placeholder="Tìm sản phẩm..."
               style="flex:1;padding:8px 14px;border:1px solid var(--border-muted);border-radius:8px;font-size:14px;outline:none;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);background:var(--bg-surface);border:1px solid var(--border-muted);border-radius:8px;padding:6px 12px;cursor:pointer;">
            <input type="checkbox" name="only_variance" value="1" <?= !empty($_GET['only_variance']) ? 'checked' : '' ?>>
            Chỉ xem chênh lệch
        </label>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
    </form>
</div>

<!-- Bảng sản phẩm kiểm kê -->
<div class="dashboard-section">
    <table class="admin-table sk-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Vị trí kệ</th>
                <th style="text-align:center;">Tồn hệ thống</th>
                <th style="text-align:center;min-width:140px;">Số thực tế <span style="color:#ef4444;">*</span></th>
                <th style="text-align:center;">Chênh lệch</th>
                <th style="text-align:center;">Người kiểm</th>
                <th style="text-align:center;">Lưu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-faint);">
                <i class="fas fa-box-open" style="font-size:48px;display:block;margin-bottom:12px;opacity:.3;"></i>Không có sản phẩm nào
            </td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item):
                $hasCounted = $item['counted_qty'] !== null;
                $variance   = $hasCounted ? ((int)$item['counted_qty'] - (int)$item['system_qty']) : null;
                $rowBg = !$hasCounted ? '' : ($variance > 0 ? 'background:var(--success-bg);' : ($variance < 0 ? 'background:#fef2f2;' : 'background:#f0f9ff;'));
            ?>
            <tr style="<?= $rowBg ?>" id="row-<?= $item['id'] ?>">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php if ($item['product_image']): ?>
                        <img src="<?= BASE_URL ?>public/img/products/<?= htmlspecialchars($item['product_image']) ?>"
                             style="width:38px;height:38px;border-radius:7px;object-fit:cover;border:1px solid var(--border-subtle);">
                        <?php else: ?>
                        <div style="width:38px;height:38px;border-radius:7px;background:#6366f1;display:flex;align-items:center;justify-content:center;color:white;font-size:15px;"><i class="fas fa-microchip"></i></div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div style="font-size:11px;color:var(--text-faint);"><?= htmlspecialchars($item['category_name'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">
                    <?php if ($item['bin_location']): ?>
                    <span style="background:rgba(99,102,241,0.12);color:#7c3aed;padding:2px 8px;border-radius:6px;font-family:monospace;font-size:12px;"><?= htmlspecialchars($item['bin_location']) ?></span>
                    <?php else: ?>
                    <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;font-size:20px;font-weight:800;color:#6366f1;"><?= $item['system_qty'] ?></td>
                <td style="text-align:center;">
                    <?php if (!$isClosed): ?>
                    <input type="number" min="0"
                           class="sk-count-input" id="counted-<?= $item['id'] ?>"
                           value="<?= $hasCounted ? $item['counted_qty'] : '' ?>"
                           placeholder="Nhập..."
                           oninput="calcVariance(<?= $item['id'] ?>, <?= $item['system_qty'] ?>)"
                           style="width:90px;padding:6px 8px;border:1px solid var(--border-muted);border-radius:8px;font-size:16px;font-weight:700;text-align:center;outline:none;transition:border .2s;">
                    <?php else: ?>
                    <span style="font-size:18px;font-weight:800;color:<?= $hasCounted ? '#1e293b' : '#cbd5e1' ?>;"><?= $hasCounted ? $item['counted_qty'] : '—' ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <span class="sk-variance" id="var-<?= $item['id'] ?>"
                          style="font-size:16px;font-weight:800;">
                        <?php if ($variance !== null):
                            $vc = $variance > 0 ? '#10b981' : ($variance < 0 ? '#ef4444' : '#94a3b8');
                            $vt = $variance > 0 ? "+$variance" : ($variance === 0 ? '±0' : "$variance");
                        ?>
                        <span style="color:<?= $vc ?>;"><?= $vt ?></span>
                        <?php else: ?>
                        <span style="color:#e2e8f0;">—</span>
                        <?php endif; ?>
                    </span>
                </td>
                <td style="text-align:center;font-size:12px;color:var(--text-muted);">
                    <?= $item['counted_by_name'] ? htmlspecialchars($item['counted_by_name']) : '<span style="color:#cbd5e1;">—</span>' ?>
                    <?php if ($item['counted_at']): ?>
                    <div style="font-size:10px;color:var(--text-faint);"><?= date('H:i d/m', strtotime($item['counted_at'])) ?></div>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php if (!$isClosed): ?>
                    <button onclick="saveCount(<?= $item['id'] ?>, <?= $session['id'] ?>, <?= $item['system_qty'] ?>)"
                            class="btn btn-sm btn-primary sk-save-btn" id="savebtn-<?= $item['id'] ?>"
                            title="Lưu số liệu">
                        <i class="fas fa-save"></i>
                    </button>
                    <?php elseif ($hasCounted): ?>
                    <i class="fas fa-check-circle" style="color:#10b981;font-size:16px;"></i>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</main>

<style>
.sk-progress-card { background:var(--bg-surface);border-radius:16px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,.06);margin-bottom:20px; }
.sk-table td { padding:10px 14px;vertical-align:middle; }
.sk-count-input:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.sk-save-btn { transition:all .2s; }
.sk-save-btn.saved { background:linear-gradient(135deg,#10b981,#34d399) !important; }
.btn-danger { background:linear-gradient(135deg,#ef4444,#dc2626);color:white;border:none; }
</style>

<script>
function calcVariance(itemId, sysQty) {
    const inp = document.getElementById('counted-' + itemId);
    const varEl = document.getElementById('var-' + itemId);
    const val = inp.value.trim();
    if (val === '' || isNaN(parseInt(val))) {
        varEl.innerHTML = '<span style="color:#e2e8f0;">—</span>';
        return;
    }
    const counted = parseInt(val);
    const diff = counted - sysQty;
    const color = diff > 0 ? '#10b981' : (diff < 0 ? '#ef4444' : '#94a3b8');
    const text  = diff > 0 ? '+' + diff : (diff === 0 ? '±0' : '' + diff);
    varEl.innerHTML = `<span style="color:${color};font-size:16px;font-weight:800;">${text}</span>`;
}

function saveCount(itemId, sessionId, sysQty) {
    const inp = document.getElementById('counted-' + itemId);
    const val = inp.value.trim();
    if (val === '' || isNaN(parseInt(val)) || parseInt(val) < 0) {
        inp.style.borderColor = '#ef4444';
        inp.focus();
        return;
    }
    inp.style.borderColor = '#e2e8f0';

    const btn = document.getElementById('savebtn-' + itemId);
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    const fd = new FormData();
    fd.append('session_id', sessionId);
    fd.append('product_id', itemId);
    fd.append('counted_qty', parseInt(val));

    fetch('?page=inventory&action=save_stocktake_count', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    }).then(r => r.json()).then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.add('saved');
            calcVariance(itemId, sysQty);

            // Cập nhật progress bar
            if (data.counted !== undefined && data.total !== undefined) {
                const pct = data.total > 0 ? Math.min(100, Math.round(data.counted / data.total * 100)) : 0;
                document.querySelector('.sk-progress-card [style*="transition"]').style.width = pct + '%';
            }

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save"></i>';
                btn.classList.remove('saved');
                btn.disabled = false;
            }, 2000);
        } else {
            btn.innerHTML = '<i class="fas fa-times"></i>';
            btn.style.background = '#ef4444';
            btn.disabled = false;
        }
    }).catch(() => {
        btn.innerHTML = '<i class="fas fa-times"></i>';
        btn.disabled = false;
    });
}

// Auto-focus next input on Enter
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('sk-count-input')) {
        e.preventDefault();
        const inputs = Array.from(document.querySelectorAll('.sk-count-input'));
        const idx = inputs.indexOf(e.target);
        if (idx >= 0) {
            // Auto-save current
            const id = e.target.id.replace('counted-', '');
            const row = e.target.closest('tr');
            const sysQty = parseInt(row.querySelector('[style*="font-size:20px"]').textContent) || 0;
            saveCount(parseInt(id), <?= $session['id'] ?>, sysQty);
            // Focus next
            if (idx + 1 < inputs.length) inputs[idx + 1].focus();
        }
    }
});
</script>
