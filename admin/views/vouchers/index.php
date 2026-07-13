<?php
$allVouchers = $admin->getAllVouchers();
$voucherStats = $admin->getVoucherStats();
$editVoucherId = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;
$showAddVoucherForm = isset($_GET['show_add']);
$personalVoucherCount = $admin->countPersonalVouchers();
$allUsers = $admin->getAllUsers();
$GLOBALS['allUsers'] = $allUsers;
$filterPersonal = isset($_GET['personal']) ? intval($_GET['personal']) : 0;

// Lá»c theo personal náº¿u cáº§n
if ($filterPersonal) {
    $vouchers = array_filter($allVouchers, fn($v) => !empty($v['user_id']));
} else {
    $vouchers = $allVouchers;
}
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-ticket-alt" style="color:#6366f1;margin-right:10px;"></i>Quản lý Voucher</h1>
            <p>Tổng cộng <strong><?php echo count($vouchers); ?></strong> voucher &mdash;
               <span style="color:#10b981;font-weight:600;"><?php echo $voucherStats['active'] ?? 0; ?> đang hoạt động</span>
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="?page=vouchers" class="btn btn-sm <?php echo !$filterPersonal ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius:8px;">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="?page=vouchers&personal=1" class="btn btn-sm <?php echo $filterPersonal ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius:8px;">
                <i class="fas fa-user-tag"></i> Cá nhân <?php echo $personalVoucherCount > 0 ? '('.$personalVoucherCount.')' : ''; ?>
            </a>
            <button type="button" id="btnShowAddVoucher" class="btn btn-primary" onclick="toggleAddForm()">
                <i class="fas fa-plus"></i> Thêm voucher
            </button>
        </div>
    </div>

    <!-- Stats mini -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div class="stat-card" style="padding:18px 20px;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 20px rgba(99,102,241,.3);width:46px;height:46px;font-size:18px;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:22px;"><?php echo $voucherStats['total'] ?? 0; ?></div>
                <div class="stat-label">Tổng voucher</div>
            </div>
        </div>
        <div class="stat-card" style="padding:18px 20px;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 20px rgba(16,185,129,.3);width:46px;height:46px;font-size:18px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:22px;"><?php echo $voucherStats['active'] ?? 0; ?></div>
                <div class="stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="stat-card" style="padding:18px 20px;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 8px 20px rgba(245,158,11,.3);width:46px;height:46px;font-size:18px;">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:22px;"><?php echo $voucherStats['total_used'] ?? 0; ?></div>
                <div class="stat-label">Lượt đã dùng</div>
            </div>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- ── Add Form ── -->
    <section id="addVoucherSection" class="form-card" style="margin-bottom:24px;max-width:100%;<?php echo $showAddVoucherForm ? '' : 'display:none;'; ?>">
        <h2 class="form-section-title"><i class="fas fa-plus-circle" style="color:#6366f1;margin-right:8px;"></i>Thêm voucher mới</h2>
        <form method="post" action="?page=vouchers&action=create">
            <?php echo voucherFormFields([]); ?>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu voucher</button>
                <button type="button" class="btn btn-secondary" onclick="toggleAddForm()"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </section>

    <!-- ── Voucher Table ── -->
    <div class="table-responsive">
        <table class="admin-table" id="voucherTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã voucher</th>
                    <th>Tên</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Đơn tối thiểu</th>
                    <th>Hạn dùng</th>
                    <th>Đã dùng / Giới hạn</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vouchers)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:40px;color:var(--text-faint);">
                            <i class="fas fa-ticket-alt" style="font-size:32px;margin-bottom:10px;display:block;"></i>
                            Chưa có voucher nào. Hãy thêm voucher đầu tiên!
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($vouchers as $v): ?>
                    <tr>
                        <td><span style="font-weight:700;color:#6366f1;">#<?php echo $v['id']; ?></span></td>
                        <td>
                            <span style="font-family:monospace;background:#f0f4ff;border:1px solid #c7d2fe;padding:4px 10px;border-radius:7px;font-weight:700;color:#4f46e5;letter-spacing:.05em;font-size:13px;">
                                <?php echo htmlspecialchars($v['code']); ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($v['name']); ?></div>
                            <?php if (!empty($v['description'])): ?>
                                <div style="font-size:12px;color:var(--text-faint);margin-top:2px;"><?php echo htmlspecialchars($v['description']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($v['user_id'])): ?>
                                <div style="font-size:11px;margin-top:4px;">
                                    <span style="background:rgba(245,158,11,0.12);color:#fbbf24;padding:2px 7px;border-radius:20px;font-weight:700;font-size:10px;">
                                        <i class="fas fa-user-tag"></i> Cá nhân (ID: <?php echo $v['user_id']; ?>)
                                    </span>
                                </div>
                                <?php if (!empty($v['sent_at'])): ?>
                                <div style="font-size:10px;color:var(--text-faint);margin-top:2px;">
                                    Gửi lúc: <?php echo date('d/m/Y H:i', strtotime($v['sent_at'])); ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['type'] === 'percent'): ?>
                                <span style="background:rgba(99,102,241,0.12);color:#6d28d9;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;">
                                    <i class="fas fa-percent"></i> Phần trăm
                                </span>
                            <?php else: ?>
                                <span style="background:rgba(245,158,11,0.12);color:#fbbf24;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;">
                                    <i class="fas fa-dollar-sign"></i> Cố định
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:700;color:#059669;">
                            <?php if ($v['type'] === 'percent'): ?>
                                <?php echo number_format($v['value']); ?>%
                                <?php if ($v['max_discount']): ?>
                                    <div style="font-size:11px;color:var(--text-faint);font-weight:500;">Tối đa: <?php echo number_format($v['max_discount'],0,',','.'); ?>₫</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo number_format($v['value'],0,',','.'); ?>₫
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--text-secondary);">
                            <?php echo $v['min_order'] > 0 ? number_format($v['min_order'],0,',','.').'₫' : '<span style="color:var(--text-faint);">Không giới hạn</span>'; ?>
                        </td>
                        <td>
                            <?php
                                $expDate = strtotime($v['expire_date']);
                                $isExpired = $expDate < time();
                                $color = $isExpired ? '#ef4444' : '#10b981';
                            ?>
                            <span style="color:<?php echo $color; ?>;font-weight:600;font-size:13px;">
                                <?php echo date('d/m/Y', $expDate); ?>
                                <?php if ($isExpired): ?><br><span style="font-size:11px;">(Hết hạn)</span><?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-weight:700;color:#6366f1;"><?php echo intval($v['used_count']); ?></span>
                                <span style="color:var(--text-faint);">/</span>
                                <span style="color:var(--text-secondary);"><?php echo $v['usage_limit'] ?? '∞'; ?></span>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="?page=vouchers&action=toggle" style="display:inline;">
                                <?php echo CsrfHelper::field(); ?>
                                <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                                <button type="submit"
                                   title="<?php echo $v['is_active'] ? 'Nhấn để tắt' : 'Nhấn để bật'; ?>"
                                   style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:99px;font-size:11px;font-weight:700;text-decoration:none;transition:all .2s;border:none;cursor:pointer;
                                   <?php echo $v['is_active']
                                       ? 'background:rgba(34,197,94,0.12);color:#4ade80;'
                                       : 'background:rgba(239,68,68,0.12);color:#f87171;'; ?>">
                                    <i class="fas <?php echo $v['is_active'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" style="font-size:14px;"></i>
                                    <?php echo $v['is_active'] ? 'Hoạt động' : 'Tắt'; ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="?page=vouchers&edit_id=<?php echo $v['id']; ?>"
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <form method="POST" action="?page=vouchers&action=delete" style="display:inline;"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa voucher <?php echo htmlspecialchars(addslashes($v['code'])); ?>? Hành động này không thể hoàn tác.')">
                                    <?php echo CsrfHelper::field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php if ($editVoucherId === intval($v['id'])): ?>
                    <tr id="edit-voucher-<?php echo $v['id']; ?>" style="background:#fefce8;">
                        <td colspan="10">
                            <div style="padding:24px;background:#fffbeb;border-radius:12px;margin:8px;border:1px solid #fde68a;">
                                <h3 class="form-section-title">
                                    <i class="fas fa-edit" style="color:#f59e0b;margin-right:8px;"></i>
                                    Chỉnh sửa voucher #<?php echo $v['id']; ?> — <?php echo htmlspecialchars($v['code']); ?>
                                </h3>
                                <form method="post" action="?page=vouchers&action=update&id=<?php echo $v['id']; ?>">
                                    <?php echo voucherFormFields($v); ?>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                                        <a href="?page=vouchers" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
function voucherFormFields($v = []) {
    $code        = htmlspecialchars($v['code'] ?? '');
    $name        = htmlspecialchars($v['name'] ?? '');
    $description = htmlspecialchars($v['description'] ?? '');
    $type        = $v['type'] ?? 'fixed';
    $value       = htmlspecialchars($v['value'] ?? '');
    $max_discount= htmlspecialchars($v['max_discount'] ?? '');
    $min_order   = htmlspecialchars($v['min_order'] ?? '0');
    $usage_limit = htmlspecialchars($v['usage_limit'] ?? '');
    $expire_date = htmlspecialchars($v['expire_date'] ?? date('Y-m-d', strtotime('+30 days')));
    $is_active   = isset($v['is_active']) ? (int)$v['is_active'] : 1;
    ob_start();
    ?>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Mã voucher <span class="req">*</span></label>
            <input class="form-control" type="text" name="code" value="<?php echo $code; ?>"
                   placeholder="VD: SALE10" required style="text-transform:uppercase;font-family:monospace;letter-spacing:.05em;"
                   oninput="this.value=this.value.toUpperCase()">
            <p class="form-note"><i class="fas fa-info-circle"></i> Tự động chuyển thành chữ hoa</p>
        </div>
        <div class="form-group">
            <label class="form-label">Tên hiển thị <span class="req">*</span></label>
            <input class="form-control" type="text" name="name" value="<?php echo $name; ?>"
                   placeholder="VD: Giảm 10% cho đơn từ 500k" required>
        </div>
        <div class="form-group">
            <label class="form-label">Loại voucher <span class="req">*</span></label>
            <select class="form-control" name="type" id="voucherTypeSelect" onchange="toggleMaxDiscount(this)">
                <option value="fixed"   <?php echo $type==='fixed'   ? 'selected' : ''; ?>>💰 Giảm cố định (₫)</option>
                <option value="percent" <?php echo $type==='percent' ? 'selected' : ''; ?>>🎯 Giảm theo % </option>
                <option value="shipping" <?php echo $type==='shipping' ? 'selected' : ''; ?>>🚚 Miễn phí vận chuyển</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Giá trị <span class="req">*</span></label>
            <input class="form-control" type="number" name="value" value="<?php echo $value; ?>"
                   placeholder="VD: 50000 hoặc 10" min="0" step="any" required>
            <p class="form-note"><i class="fas fa-info-circle"></i> Nhập số tiền (₫) hoặc % tùy loại</p>
        </div>
        <div class="form-group" id="maxDiscountGroup" style="<?php echo $type!=='percent' ? 'display:none;' : ''; ?>">
            <label class="form-label">Giảm tối đa (₫)</label>
            <input class="form-control" type="number" name="max_discount" value="<?php echo $max_discount; ?>"
                   placeholder="VD: 200000 (để trống = không giới hạn)" min="0" step="1000">
            <p class="form-note"><i class="fas fa-info-circle"></i> Chỉ dùng với loại phần trăm</p>
        </div>
        <div class="form-group">
            <label class="form-label">Đơn hàng tối thiểu (₫)</label>
            <input class="form-control" type="number" name="min_order" value="<?php echo $min_order; ?>"
                   placeholder="0 = không giới hạn" min="0" step="1000">
        </div>
        <div class="form-group">
            <label class="form-label">Giới hạn lượt dùng</label>
            <input class="form-control" type="number" name="usage_limit" value="<?php echo $usage_limit; ?>"
                   placeholder="Để trống = không giới hạn" min="1" step="1">
        </div>
        <div class="form-group">
            <label class="form-label">Ngày hết hạn <span class="req">*</span></label>
            <input class="form-control" type="date" name="expire_date" value="<?php echo $expire_date; ?>" required>
        </div>
        <div class="form-group form-group-full">
            <label class="form-label">Mô tả</label>
            <textarea class="form-control" name="description" rows="2"
                      placeholder="Mô tả ngắn về voucher..."><?php echo $description; ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Gán cho người dùng cụ thể (để trống = công khai)</label>
            <select name="user_id" class="form-control" id="voucherUserSelect">
                <option value="">— Công khai (tất cả đều dùng được) —</option>
                <?php
                $usersDD = isset($GLOBALS['allUsers']) ? $GLOBALS['allUsers'] : [];
                foreach ($usersDD as $userDD): ?>
                <option value="<?php echo $userDD['id']; ?>" <?php echo ($v['user_id'] ?? '') == $userDD['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($userDD['full_name']); ?> (<?php echo htmlspecialchars($userDD['email']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <p class="form-note"><i class="fas fa-info-circle"></i> Chọn user để tạo voucher cá nhân hóa. User sẽ nhận được thông báo.</p>
        </div>
        <div class="form-group form-group-full">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $is_active ? 'checked' : ''; ?>
                       style="width:18px;height:18px;accent-color:#6366f1;cursor:pointer;">
                <span style="font-weight:600;font-size:14px;color:var(--text-secondary);">Kích hoạt voucher ngay</span>
            </label>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
function toggleAddForm() {
    const sec = document.getElementById('addVoucherSection');
    sec.style.display = (sec.style.display === 'none' || sec.style.display === '') ? 'block' : 'none';
    if (sec.style.display === 'block') sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleMaxDiscount(sel) {
    const grp = document.getElementById('maxDiscountGroup');
    if (grp) grp.style.display = (sel.value === 'percent') ? 'block' : 'none';
}

// Scroll & highlight edit row
document.addEventListener('DOMContentLoaded', function() {
    const editRow = document.getElementById('edit-voucher-<?php echo $editVoucherId ?? 0; ?>');
    if (editRow) {
        editRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    // Cập nhật toggle max discount cho tất cả select
    document.querySelectorAll('[name="type"]').forEach(sel => toggleMaxDiscount(sel));
});
</script>
