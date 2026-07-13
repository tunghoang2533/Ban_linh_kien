<?php
// Admin Suppliers View
$supplierCtrl = new SupplierController($db);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_supplier'])) {
        $supplierCtrl->create($_POST);
        header('Location: ?page=suppliers'); exit;
    }
    if (isset($_POST['update_supplier'])) {
        $supplierCtrl->update(intval($_POST['supplier_id']), $_POST);
        header('Location: ?page=suppliers'); exit;
    }
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $supplierCtrl->delete(intval($_GET['delete']));
    header('Location: ?page=suppliers'); exit;
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $supplierCtrl->toggle(intval($_GET['toggle']));
    header('Location: ?page=suppliers'); exit;
}

$suppliers = $supplierCtrl->getAll();
$supStats  = $supplierCtrl->getStats();
$editSup   = isset($_GET['edit']) ? $supplierCtrl->getById(intval($_GET['edit'])) : null;

$formFields = [
    ['name','Tên công ty / nhà cung cấp *','text',true],
    ['contact_person','Người liên hệ','text',false],
    ['phone','Số điện thoại','text',false],
    ['email','Email','email',false],
    ['address','Địa chỉ','text',false],
    ['website','Website','url',false],
    ['tax_code','Mã số thuế','text',false],
    ['bank_account','Số tài khoản ngân hàng','text',false],
    ['bank_name','Tên ngân hàng','text',false],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-industry" style="color:#f59e0b;"></i> Nhà cung cấp</h1>
    <button onclick="document.getElementById('modal-add').style.display='flex'" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706);border:none;border-radius:12px;padding:10px 20px;font-weight:700;">
        <i class="fas fa-plus"></i> Thêm NCC mới
    </button>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div style="background:var(--bg-surface);border-radius:14px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="font-size:28px;font-weight:800;color:var(--text-primary);"><?php echo $supStats['total']; ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px;"><i class="fas fa-industry" style="color:#f59e0b;"></i> Tổng NCC</div>
    </div>
    <div style="background:var(--bg-surface);border-radius:14px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="font-size:28px;font-weight:800;color:#16a34a;"><?php echo $supStats['active']; ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px;"><i class="fas fa-check-circle" style="color:#16a34a;"></i> Đang hoạt động</div>
    </div>
    <div style="background:var(--bg-surface);border-radius:14px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="font-size:28px;font-weight:800;color:#dc2626;"><?php echo $supStats['total'] - $supStats['active']; ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px;"><i class="fas fa-pause-circle" style="color:#dc2626;"></i> Tạm ngừng</div>
    </div>
</div>

<!-- Table -->
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:0;">
<table style="width:100%;border-collapse:collapse;">
    <thead><tr style="background:var(--bg-elevated);">
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;">MÃ</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TÊN NCC</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">LIÊN HỆ</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">SĐT / EMAIL</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TRẠNG THÁI</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);"></th>
    </tr></thead>
    <tbody>
    <?php if (empty($suppliers)): ?>
    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-faint);">Chưa có nhà cung cấp nào</td></tr>
    <?php else: foreach ($suppliers as $sup): ?>
    <tr style="border-bottom:1px solid var(--border-subtle);" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
        <td style="padding:14px 16px;"><code style="background:var(--bg-elevated);padding:2px 8px;border-radius:6px;font-size:12px;"><?php echo htmlspecialchars($sup['code'] ?? '—'); ?></code></td>
        <td style="padding:14px 16px;"><strong style="color:var(--text-primary);"><?php echo htmlspecialchars($sup['name']); ?></strong>
            <?php if ($sup['website']): ?><br><a href="<?php echo htmlspecialchars($sup['website']); ?>" target="_blank" style="font-size:12px;color:#2563eb;"><?php echo htmlspecialchars($sup['website']); ?></a><?php endif; ?></td>
        <td style="padding:14px 16px;font-size:13px;color:var(--text-secondary);"><?php echo htmlspecialchars($sup['contact_person'] ?? '—'); ?></td>
        <td style="padding:14px 16px;font-size:13px;"><div><?php echo htmlspecialchars($sup['phone'] ?? '—'); ?></div><div style="color:var(--text-faint);"><?php echo htmlspecialchars($sup['email'] ?? ''); ?></div></td>
        <td style="padding:14px 16px;">
            <span style="padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;<?php echo $sup['is_active'] ? 'background:rgba(34,197,94,0.12);color:#4ade80;' : 'background:rgba(239,68,68,0.12);color:#f87171;'; ?>">
                <?php echo $sup['is_active'] ? 'Hoạt động' : 'Tạm ngừng'; ?>
            </span>
        </td>
        <td style="padding:14px 16px;white-space:nowrap;">
            <a href="?page=suppliers&edit=<?php echo $sup['id']; ?>" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:5px 10px;font-size:12px;margin-right:4px;"><i class="fas fa-edit"></i></a>
            <a href="?page=suppliers&toggle=<?php echo $sup['id']; ?>" class="btn btn-sm" style="background:rgba(245,158,11,0.12);color:#fbbf24;border:none;border-radius:8px;padding:5px 10px;font-size:12px;margin-right:4px;" title="<?php echo $sup['is_active'] ? 'Tắt' : 'Bật'; ?>"><i class="fas fa-<?php echo $sup['is_active'] ? 'pause' : 'play'; ?>"></i></a>
            <a href="?page=suppliers&delete=<?php echo $sup['id']; ?>" class="btn btn-sm" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:5px 10px;font-size:12px;" onclick="return confirm('Xóa nhà cung cấp này?')"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>

<!-- Modal Add -->
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:20px;padding:32px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--text-primary);"><i class="fas fa-plus-circle" style="color:#f59e0b;"></i> Thêm nhà cung cấp</h3>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-faint);">&times;</button>
        </div>
        <form method="POST" action="?page=suppliers">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <?php foreach ($formFields as [$fname, $flabel, $ftype, $freq]): ?>
            <div style="<?php echo $fname === 'name' || $fname === 'address' ? 'grid-column:1/-1;' : ''; ?>">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;"><?php echo $flabel; ?></label>
                <input type="<?php echo $ftype; ?>" name="<?php echo $fname; ?>" class="form-control"
                       style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" <?php echo $freq ? 'required' : ''; ?>>
            </div>
            <?php endforeach; ?>
            <div style="grid-column:1/-1;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Ghi chú</label>
                <textarea name="note" rows="2" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:none;"></textarea>
            </div>
            </div>
            <button type="submit" name="create_supplier" class="btn btn-primary" style="width:100%;margin-top:20px;border-radius:12px;padding:12px;font-weight:700;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;">
                <i class="fas fa-save"></i> Lưu nhà cung cấp
            </button>
        </form>
    </div>
</div>

<?php if ($editSup): ?>
<!-- Modal Edit -->
<div id="modal-edit" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:20px;padding:32px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="margin:0;font-size:18px;font-weight:800;color:var(--text-primary);"><i class="fas fa-edit" style="color:#2563eb;"></i> Sửa nhà cung cấp</h3>
            <a href="?page=suppliers" style="color:var(--text-faint);font-size:20px;text-decoration:none;">&times;</a>
        </div>
        <form method="POST" action="?page=suppliers">
            <input type="hidden" name="supplier_id" value="<?php echo $editSup['id']; ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <?php foreach ($formFields as [$fname, $flabel, $ftype, $freq]): ?>
            <div style="<?php echo $fname === 'name' || $fname === 'address' ? 'grid-column:1/-1;' : ''; ?>">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;"><?php echo $flabel; ?></label>
                <input type="<?php echo $ftype; ?>" name="<?php echo $fname; ?>" value="<?php echo htmlspecialchars($editSup[$fname] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" <?php echo $freq ? 'required' : ''; ?>>
            </div>
            <?php endforeach; ?>
            <div style="grid-column:1/-1;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Ghi chú</label>
                <textarea name="note" rows="2" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:none;"><?php echo htmlspecialchars($editSup['note'] ?? ''); ?></textarea>
            </div>
            </div>
            <button type="submit" name="update_supplier" class="btn btn-primary" style="width:100%;margin-top:20px;border-radius:12px;padding:12px;font-weight:700;background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
