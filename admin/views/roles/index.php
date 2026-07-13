<?php
// Admin Roles View
$roleCtrl = new RoleController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_role'])) {
        $roleCtrl->assignRole(intval($_POST['user_id']), intval($_POST['role_id']) ?: null);
        header('Location: ?page=roles'); exit;
    }
    if (isset($_POST['create_role'])) {
        $roleCtrl->createRole($_POST);
        header('Location: ?page=roles'); exit;
    }
    if (isset($_POST['delete_role'])) {
        $roleCtrl->deleteRole(intval($_POST['role_id']));
        header('Location: ?page=roles'); exit;
    }
}

$roles      = $roleCtrl->getRoles();
$adminUsers = $roleCtrl->getAdminUsers();

$roleColors = ['super_admin'=>['bg'=>'#fee2e2','color'=>'#dc2626'],'warehouse'=>['bg'=>'#fff7ed','color'=>'#f97316'],'cskh'=>['bg'=>'#eff6ff','color'=>'#2563eb']];

$permList = [
    'products'=>'Sản phẩm','orders'=>'Đơn hàng','users'=>'Khách hàng','inventory'=>'Kho hàng',
    'chat'=>'Chat','comments'=>'Bình luận','vouchers'=>'Voucher','banners'=>'Banner',
    'categories'=>'Danh mục','returns'=>'Đổi trả','reports'=>'Báo cáo','cms'=>'CMS nội dung',
    'settings'=>'Cài đặt','suppliers'=>'Nhà cung cấp','shipping'=>'Vận chuyển',
    'notifications'=>'Thông báo','audit'=>'Audit log','roles'=>'Phân quyền',
];
?>
<div class="page-header" style="margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-user-shield" style="color:#dc2626;"></i> Phân quyền Admin</h1>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;">
    <div>
        <!-- Admin Users -->
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:20px;">
        <div class="card-body" style="padding:24px;">
            <h5 style="margin:0 0 18px;font-weight:700;color:var(--text-primary);"><i class="fas fa-users-cog" style="color:#6366f1;"></i> Danh sách Admin</h5>
            <?php foreach ($adminUsers as $u):
                $rc = $roleColors[$u['role_key'] ?? ''] ?? ['bg'=>'#f1f5f9','color'=>'#64748b'];
            ?>
            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border-subtle);">
                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;color:white;font-size:15px;font-weight:800;flex-shrink:0;">
                    <?php echo mb_strtoupper(mb_substr($u['full_name'] ?: $u['username'], 0, 1)); ?>
                </div>
                <div style="flex:1;">
                    <p style="margin:0;font-weight:700;font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($u['full_name'] ?: $u['username']); ?></p>
                    <p style="margin:0;font-size:12px;color:var(--text-faint);"><?php echo htmlspecialchars($u['email'] ?? ''); ?></p>
                </div>
                <form method="POST" style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <select name="role_id" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:6px 10px;font-size:13px;min-width:160px;">
                        <option value="">Không có role</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo ($u['role_id']==$r['id'])?'selected':''; ?>><?php echo htmlspecialchars($r['display_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_role" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;white-space:nowrap;">Gán</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div></div>

        <!-- Roles Table -->
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body" style="padding:24px;">
            <h5 style="margin:0 0 18px;font-weight:700;color:var(--text-primary);"><i class="fas fa-shield-alt" style="color:#7c3aed;"></i> Danh sách Roles</h5>
            <?php foreach ($roles as $r):
                $rc = $roleColors[$r['name']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b'];
                $perms = json_decode($r['permissions'] ?? '{}', true) ?? [];
            ?>
            <div style="background:var(--bg-elevated);border-radius:12px;padding:16px;margin-bottom:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;background:<?php echo $rc['bg']; ?>;color:<?php echo $rc['color']; ?>;"><?php echo htmlspecialchars($r['display_name']); ?></span>
                        <?php if (!empty($perms['all'])): ?><span style="font-size:11px;background:rgba(239,68,68,0.12);color:#dc2626;padding:2px 8px;border-radius:20px;font-weight:700;">Toàn quyền</span><?php endif; ?>
                    </div>
                    <?php if ($r['name'] !== 'super_admin'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="role_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="delete_role" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:4px 10px;font-size:12px;cursor:pointer;" onclick="return confirm('Xóa role này?')"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php if (!empty($perms) && empty($perms['all'])): ?>
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    <?php foreach ($perms as $pk => $pv): if (!$pv) continue; ?>
                    <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;"><?php echo $permList[$pk] ?? $pk; ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div></div>
    </div>

    <!-- Create Role Form -->
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);height:fit-content;">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;color:var(--text-primary);"><i class="fas fa-plus-circle" style="color:#16a34a;"></i> Tạo Role mới</h5>
        <form method="POST">
            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tên role (không dấu)</label>
                <input type="text" name="name" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="vd: content_editor">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tên hiển thị</label>
                <input type="text" name="display_name" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="vd: Biên tập viên">
            </div>
            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:10px;">Quyền hạn</label>
                <div style="background:var(--bg-elevated);border-radius:12px;padding:14px;max-height:260px;overflow-y:auto;">
                    <?php foreach ($permList as $pk => $plabel): ?>
                    <label style="display:flex;align-items:center;gap:10px;padding:6px 0;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="perm_<?php echo $pk; ?>" value="1" style="width:16px;height:16px;accent-color:#6366f1;">
                        <?php echo $plabel; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" name="create_role" class="btn btn-primary" style="width:100%;border-radius:12px;padding:11px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                <i class="fas fa-save"></i> Tạo Role
            </button>
        </form>
    </div></div>
</div>
