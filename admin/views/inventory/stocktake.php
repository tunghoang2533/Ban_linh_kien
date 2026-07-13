<?php
/**
 * Quản lý Kiểm Kê Kho (Stocktake Sessions)
 * URL: ?page=inventory&action=stocktake
 */
$warehouses   = $warehouses  ?? [];
$sessions     = $sessions    ?? [];
$filterWarehouse = $filterWarehouse ?? null;
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-clipboard-check" style="color:#f59e0b;margin-right:10px;"></i>Kiểm Kê Kho</h1>
        <p>Đối chiếu tồn kho thực tế với hệ thống — phát hiện và điều chỉnh chênh lệch</p>
    </div>
    <div class="page-header-right">
        <button onclick="openNewSessionModal()" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;padding:10px 20px;border-radius:10px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
            <i class="fas fa-plus-circle"></i> Mở Phiên Kiểm Kê Mới
        </button>
    </div>
</div>

<?php if (!empty($successMessage)): ?>
<div class="sk-alert sk-alert-success" id="skSuccess">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;float:right;color:inherit;font-size:16px;">×</button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="sk-alert sk-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Hướng dẫn kiểm kê -->
<div class="sk-guide-banner">
    <div class="sk-guide-step">
        <div class="sk-guide-num" style="background:#6366f1;">1</div>
        <div>
            <strong>Mở phiên</strong>
            <div>Chọn kho, hệ thống snapshot tồn kho hiện tại</div>
        </div>
    </div>
    <div class="sk-guide-arrow">→</div>
    <div class="sk-guide-step">
        <div class="sk-guide-num" style="background:#f59e0b;">2</div>
        <div>
            <strong>Đếm thực tế</strong>
            <div>Nhân viên nhập số lượng thực tế từng sản phẩm</div>
        </div>
    </div>
    <div class="sk-guide-arrow">→</div>
    <div class="sk-guide-step">
        <div class="sk-guide-num" style="background:#10b981;">3</div>
        <div>
            <strong>Đóng phiên & Áp dụng</strong>
            <div>Hệ thống tự điều chỉnh tồn kho theo chênh lệch</div>
        </div>
    </div>
</div>

<!-- Filter kho -->
<?php if (!empty($warehouses)): ?>
<div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
    <span style="font-size:13px;color:var(--text-muted);font-weight:600;">Kho:</span>
    <a href="?page=inventory&action=stocktake" style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;background:<?= !$filterWarehouse ? '#6366f1' : '#f1f5f9' ?>;color:<?= !$filterWarehouse ? 'white' : '#64748b' ?>;border:1.5px solid <?= !$filterWarehouse ? '#6366f1' : '#e2e8f0' ?>;">
        🏭 Tất cả
    </a>
    <?php foreach ($warehouses as $wh): ?>
    <a href="?page=inventory&action=stocktake&warehouse=<?= $wh['id'] ?>" style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;background:<?= $filterWarehouse == $wh['id'] ? '#6366f1' : '#f1f5f9' ?>;color:<?= $filterWarehouse == $wh['id'] ? 'white' : '#64748b' ?>;border:1.5px solid <?= $filterWarehouse == $wh['id'] ? '#6366f1' : '#e2e8f0' ?>;">
        <?= htmlspecialchars($wh['code']) ?> — <?= htmlspecialchars($wh['name']) ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Danh sách phiên kiểm kê -->
<div class="dashboard-section" style="padding:0;overflow:hidden;">
    <?php if (empty($sessions)): ?>
    <div style="text-align:center;padding:70px 20px;color:var(--text-faint);">
        <i class="fas fa-clipboard-check" style="font-size:60px;display:block;margin-bottom:16px;opacity:.25;"></i>
        <div style="font-size:17px;font-weight:600;margin-bottom:8px;color:var(--text-secondary);">Chưa có phiên kiểm kê nào</div>
        <div style="font-size:13px;margin-bottom:24px;">Bắt đầu phiên đầu tiên để đối chiếu tồn kho thực tế</div>
        <button onclick="openNewSessionModal()" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;padding:12px 26px;border-radius:12px;font-weight:700;cursor:pointer;font-size:15px;">
            <i class="fas fa-plus-circle"></i> Mở Phiên Kiểm Kê
        </button>
    </div>
    <?php else: ?>
    <table class="admin-table sk-sessions-table">
        <thead>
            <tr>
                <th>Mã phiên</th>
                <th>Kho</th>
                <th>Trạng thái</th>
                <th>Tiến độ kiểm</th>
                <th style="text-align:center;">Chênh lệch</th>
                <th>Phạm vi</th>
                <th>Người tạo</th>
                <th>Thời gian</th>
                <th style="text-align:center;min-width:120px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $sess):
                $statusInfo = InventoryController::getStocktakeStatusLabel($sess['status']);
                $counted    = (int)$sess['counted_products'];
                $total      = (int)$sess['total_products'];
                $pct        = $total > 0 ? min(100, round($counted / $total * 100)) : 0;
                $isActive   = in_array($sess['status'], ['open', 'counting', 'reviewing']);
            ?>
            <tr class="sk-sess-row" style="<?= $isActive ? 'background:#fffbf0;' : '' ?>">
                <td>
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        <span style="font-family:monospace;font-weight:700;font-size:13px;color:#6366f1;background:rgba(99,102,241,0.12);padding:2px 8px;border-radius:6px;display:inline-block;"><?= htmlspecialchars($sess['session_code']) ?></span>
                        <?php if ($isActive): ?>
                        <span style="font-size:10px;color:#f59e0b;font-weight:600;display:flex;align-items:center;gap:4px;">
                            <span style="width:5px;height:5px;border-radius:50%;background:#f59e0b;animation:pulse-badge 1.5s infinite;display:inline-block;"></span>
                            Đang hoạt động
                        </span>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="width:28px;height:28px;border-radius:6px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;">
                            <?= htmlspecialchars($sess['warehouse_code'] ?? '?') ?>
                        </div>
                        <span style="font-size:13px;"><?= htmlspecialchars($sess['warehouse_name'] ?? '—') ?></span>
                    </div>
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;background:<?= $statusInfo['bg'] ?>;color:<?= $statusInfo['color'] ?>;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        <?= $statusInfo['label'] ?>
                    </span>
                </td>
                <td style="min-width:160px;">
                    <div style="margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:12px;color:var(--text-secondary);font-weight:600;"><?= $counted ?> / <?= $total ?> SP</span>
                        <span style="font-size:11px;font-weight:700;color:<?= $pct >= 100 ? '#10b981' : '#6366f1' ?>;"><?= $pct ?>%</span>
                    </div>
                    <div style="width:100%;height:6px;background:var(--bg-elevated);border-radius:6px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,<?= $pct >= 100 ? '#10b981,#34d399' : '#6366f1,#8b5cf6' ?>);border-radius:6px;transition:width .5s;"></div>
                    </div>
                </td>
                <td style="text-align:center;">
                    <?php if ($sess['variance_plus'] > 0 || $sess['variance_minus'] > 0): ?>
                    <div style="display:flex;flex-direction:column;gap:2px;align-items:center;">
                        <?php if ($sess['variance_plus'] > 0): ?>
                        <span style="background:rgba(34,197,94,0.12);color:#4ade80;padding:2px 8px;border-radius:8px;font-size:12px;font-weight:700;">+<?= $sess['variance_plus'] ?></span>
                        <?php endif; ?>
                        <?php if ($sess['variance_minus'] > 0): ?>
                        <span style="background:rgba(239,68,68,0.12);color:#f87171;padding:2px 8px;border-radius:8px;font-size:12px;font-weight:700;">-<?= $sess['variance_minus'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($sess['status'] === 'closed'): ?>
                    <span style="color:var(--text-faint);font-size:12px;">Không chênh lệch</span>
                    <?php else: ?>
                    <span style="color:#e2e8f0;font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);max-width:200px;">
                    <?= $sess['scope'] ? htmlspecialchars(mb_substr($sess['scope'], 0, 60)) . (mb_strlen($sess['scope']) > 60 ? '...' : '') : '<span style="color:#cbd5e1;">Toàn bộ sản phẩm</span>' ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($sess['started_by_name'] ?? '—') ?></td>
                <td style="font-size:11px;color:var(--text-faint);white-space:nowrap;">
                    <i class="fas fa-play" style="color:#10b981;margin-right:3px;"></i>
                    <?= date('d/m/Y H:i', strtotime($sess['started_at'])) ?>
                    <?php if ($sess['closed_at']): ?>
                    <div style="margin-top:3px;"><i class="fas fa-lock" style="color:#6366f1;margin-right:3px;"></i><?= date('d/m/Y H:i', strtotime($sess['closed_at'])) ?></div>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;">
                        <?php if ($isActive): ?>
                        <a href="?page=inventory&action=stocktake_count&id=<?= $sess['id'] ?>" class="sk-btn sk-btn-primary" title="Tiến hành kiểm kê">
                            <i class="fas fa-clipboard-list"></i> Kiểm kê
                        </a>
                        <form method="POST" action="?page=inventory&action=cancel_stocktake&id=<?= $sess['id'] ?>" style="display:inline;" onsubmit="return confirm('Hủy phiên kiểm kê? Dữ liệu sẽ bị bỏ.')">
                            <button type="submit" class="sk-btn sk-btn-danger" title="Hủy phiên"><i class="fas fa-times"></i></button>
                        </form>
                        <?php elseif ($sess['status'] === 'closed'): ?>
                        <a href="?page=inventory&action=stocktake_count&id=<?= $sess['id'] ?>" class="sk-btn sk-btn-secondary" title="Xem kết quả">
                            <i class="fas fa-eye"></i> Xem
                        </a>
                        <?php else: ?>
                        <span style="font-size:11px;color:var(--text-faint);padding:4px;">—</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</main>

<!-- Modal tạo phiên kiểm kê mới -->
<div id="newSessionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--bg-surface);border-radius:20px;width:100%;max-width:500px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,.3);animation:sk-modal-in .3s ease;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:24px;color:white;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:22px;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:20px;font-weight:800;">Mở Phiên Kiểm Kê</h2>
                    <p style="margin:4px 0 0;opacity:.8;font-size:13px;">Hệ thống sẽ snapshot tồn kho hiện tại</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="?page=inventory&action=open_stocktake">
            <div style="padding:24px;">
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">
                        Kho cần kiểm kê <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
                        <?php foreach ($warehouses as $i => $wh): ?>
                        <label class="sk-wh-option" style="cursor:pointer;">
                            <input type="radio" name="warehouse_id" value="<?= $wh['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> style="position:absolute;opacity:0;">
                            <div class="sk-wh-card" data-id="<?= $wh['id'] ?>">
                                <div style="font-size:24px;font-weight:800;color:#f59e0b;"><?= htmlspecialchars($wh['code']) ?></div>
                                <div style="font-size:13px;font-weight:600;color:var(--text-primary);"><?= htmlspecialchars($wh['name']) ?></div>
                                <?php if ($wh['address']): ?>
                                <div style="font-size:11px;color:var(--text-faint);margin-top:3px;"><?= htmlspecialchars(mb_substr($wh['address'], 0, 50)) ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                        <?php if (empty($warehouses)): ?>
                        <div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--text-faint);font-size:13px;">
                            <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-right:6px;"></i>
                            Chưa có kho nào. Vui lòng chạy migration trước.
                        </div>
                        <input type="hidden" name="warehouse_id" value="1">
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Phạm vi kiểm kê</label>
                    <input type="text" name="scope" class="sk-modal-input" placeholder="VD: Toàn bộ kho, Linh kiện điện tử, Kệ A1-A5...">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Ghi chú</label>
                    <textarea name="note" rows="2" class="sk-modal-input" style="resize:none;" placeholder="Ghi chú thêm về phiên kiểm kê..."></textarea>
                </div>

                <div style="margin-top:16px;padding:12px 16px;background:rgba(245,158,11,0.12);border-radius:10px;font-size:12px;color:#fbbf24;display:flex;gap:8px;align-items:flex-start;">
                    <i class="fas fa-exclamation-triangle" style="margin-top:1px;flex-shrink:0;"></i>
                    <span>Sau khi mở phiên, hệ thống sẽ tạm khóa việc điều chỉnh tồn kho trực tiếp. Mọi thay đổi sẽ được áp dụng khi đóng phiên kiểm kê.</span>
                </div>
            </div>

            <div style="display:flex;gap:10px;padding:0 24px 24px;">
                <button type="button" onclick="closeNewSessionModal()" style="flex:1;background:var(--bg-elevated);color:var(--text-muted);border:none;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px;">Hủy bỏ</button>
                <button type="submit" style="flex:2;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-play-circle"></i> Bắt Đầu Kiểm Kê
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.sk-guide-banner { display:flex;align-items:center;gap:10px;background:var(--bg-surface);border-radius:14px;padding:16px 24px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;overflow-x:auto; }
.sk-guide-step { display:flex;align-items:center;gap:12px;flex-shrink:0; }
.sk-guide-num { width:30px;height:30px;border-radius:50%;color:white;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.sk-guide-step > div { font-size:13px;color:var(--text-secondary); }
.sk-guide-step > div strong { display:block;font-weight:700; }
.sk-guide-step > div div { font-size:11px;color:var(--text-faint);margin-top:2px; }
.sk-guide-arrow { color:#cbd5e1;font-size:18px;flex-shrink:0; }
/* Table */
.sk-sessions-table td { font-size:13px; }
.sk-sess-row { transition:background .2s; }
.sk-sess-row:hover { background:var(--bg-surface) !important; }
/* Buttons */
.sk-btn { padding:5px 10px;border:none;border-radius:7px;cursor:pointer;font-size:12px;font-weight:600;transition:.2s;display:inline-flex;align-items:center;gap:4px;text-decoration:none; }
.sk-btn:hover { filter:brightness(.9);transform:scale(.97); }
.sk-btn-primary   { background:linear-gradient(135deg,#f59e0b,#d97706);color:white; }
.sk-btn-danger    { background:rgba(239,68,68,0.12);color:#f87171; }
.sk-btn-secondary { background:var(--bg-elevated);color:var(--text-muted); }
/* Alerts */
.sk-alert { padding:14px 20px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-weight:600; }
.sk-alert-success { background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;color:#4ade80; }
.sk-alert-error   { background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;color:#f87171; }
/* Modal */
@keyframes sk-modal-in { from{transform:scale(.92);opacity:0} to{transform:scale(1);opacity:1} }
.sk-modal-input { width:100%;border:1px solid var(--border-muted);border-radius:8px;padding:10px 12px;font-size:14px;outline:none;box-sizing:border-box;transition:border .2s; }
.sk-modal-input:focus { border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.12); }
/* Warehouse radio cards */
.sk-wh-option input:checked ~ .sk-wh-card { border-color:#f59e0b;background:#fffbf0;box-shadow:0 0 0 2px rgba(245,158,11,.3); }
.sk-wh-card { border:2px solid #e2e8f0;border-radius:12px;padding:14px;transition:.2s;cursor:pointer;background:var(--bg-surface); }
.sk-wh-card:hover { border-color:#f59e0b;background:#fffbf0; }
</style>

<script>
function openNewSessionModal()  { document.getElementById('newSessionModal').style.display = 'flex'; }
function closeNewSessionModal() { document.getElementById('newSessionModal').style.display = 'none'; }

// Handle warehouse radio card visual selection
document.querySelectorAll('.sk-wh-option input[type=radio]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.sk-wh-card').forEach(c => c.style.borderColor = '#e2e8f0');
        if (this.checked) this.nextElementSibling.style.borderColor = '#f59e0b';
    });
    // Init first selected
    if (radio.checked) radio.nextElementSibling.style.borderColor = '#f59e0b';
});

// Close modal on backdrop click
document.getElementById('newSessionModal').addEventListener('click', function(e) {
    if (e.target === this) closeNewSessionModal();
});

// Auto-hide success
const sk = document.getElementById('skSuccess');
if (sk) setTimeout(() => { sk.style.opacity = '0'; sk.style.transition = 'opacity .5s'; setTimeout(() => sk.remove(), 500); }, 5000);
</script>
