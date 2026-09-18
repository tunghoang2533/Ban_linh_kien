<?php
// Admin Roles & Staff Management View
// POST đã được xử lý trong other.php trước khi include view này.
// Biến $roleCtrl, $currentUserId đã được tạo sẵn từ handler.


// ── Data ─────────────────────────────────────────────────────
$roles      = $roleCtrl->getRoles();
$adminUsers = $roleCtrl->getAdminUsers();
$activeTab  = $_GET['tab'] ?? 'staff';

$roleColors = [
    'super_admin' => ['bg'=>'#fee2e2','color'=>'#dc2626'],
    'warehouse'   => ['bg'=>'#fff7ed','color'=>'#f97316'],
    'cskh'        => ['bg'=>'#eff6ff','color'=>'#2563eb'],
];

$permList = [
    'products'=>'Sản phẩm','orders'=>'Đơn hàng','users'=>'Khách hàng','inventory'=>'Kho hàng',
    'chat'=>'Chat','comments'=>'Bình luận','vouchers'=>'Voucher','banners'=>'Banner',
    'categories'=>'Danh mục','returns'=>'Đổi trả','reports'=>'Báo cáo','cms'=>'CMS nội dung',
    'settings'=>'Cài đặt','suppliers'=>'Nhà cung cấp','shipping'=>'Vận chuyển',
    'notifications'=>'Thông báo','audit'=>'Audit log','roles'=>'Phân quyền',
];

// Flash messages
$flashSuccess = '';
if (isset($_GET['success'])) {
    $msgMap = [
        'assigned'       => '✅ Đã gán role thành công!',
        'admin_created'  => '✅ Đã tạo tài khoản nhân viên thành công!',
        'admin_deleted'  => '✅ Đã xoá tài khoản thành công!',
        'password_reset' => '✅ Đã đặt lại mật khẩu thành công!',
        'role_created'   => '✅ Đã tạo role mới thành công!',
        'role_deleted'   => '✅ Đã xoá role thành công!',
    ];
    $flashSuccess = $msgMap[$_GET['success']] ?? '';
}
$formError = '';
if (isset($_SESSION['admin_form_error'])) {
    $formError = $_SESSION['admin_form_error'];
    unset($_SESSION['admin_form_error']);
}
?>

<div class="page-header" style="margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-user-shield" style="color:#dc2626;"></i> Phân quyền Admin</h1>
</div>

<?php if ($flashSuccess): ?>
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;">
    <?php echo htmlspecialchars($flashSuccess); ?>
</div>
<?php endif; ?>
<?php if ($formError): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;">
    ⚠️ <?php echo htmlspecialchars($formError); ?>
</div>
<?php endif; ?>

<!-- ── Tabs ───────────────────────────────────────────────── -->
<div style="display:flex;gap:4px;margin-bottom:24px;background:var(--bg-elevated);padding:6px;border-radius:14px;width:fit-content;">
    <a href="<?php echo BASE_URL; ?>admin/?page=roles&tab=staff"
       style="padding:9px 22px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;transition:all .2s;
              <?php echo $activeTab==='staff' ? 'background:white;color:#6366f1;box-shadow:0 2px 8px rgba(0,0,0,.1);' : 'color:var(--text-secondary);'; ?>">
        <i class="fas fa-users-cog"></i> Tài khoản nhân viên
    </a>
    <a href="<?php echo BASE_URL; ?>admin/?page=roles&tab=roles"
       style="padding:9px 22px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;transition:all .2s;
              <?php echo $activeTab==='roles' ? 'background:white;color:#6366f1;box-shadow:0 2px 8px rgba(0,0,0,.1);' : 'color:var(--text-secondary);'; ?>">
        <i class="fas fa-shield-alt"></i> Quản lý Roles
    </a>
</div>

<?php if ($activeTab === 'staff'): ?>
<!-- ════════════════════════════════════════════════════════════
     TAB 1: QUẢN LÝ TÀI KHOẢN NHÂN VIÊN
════════════════════════════════════════════════════════════ -->
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;align-items:start;">

    <!-- Danh sách nhân viên admin -->
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-users" style="color:#6366f1;"></i>
            Danh sách tài khoản admin
            <span style="font-size:13px;font-weight:600;color:var(--text-faint);margin-left:8px;">(<?php echo count($adminUsers); ?> tài khoản)</span>
        </h5>

        <?php foreach ($adminUsers as $u):
            $rc = $roleColors[$u['role_key'] ?? ''] ?? ['bg'=>'#f1f5f9','color'=>'#64748b'];
            $isMe = ($u['id'] == $currentUserId);
            $isSuperAdmin = empty($u['role_id']) || $u['role_key'] === 'super_admin';
        ?>
        <div style="background:var(--bg-elevated);border-radius:12px;padding:14px 16px;margin-bottom:10px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <!-- Avatar -->
                <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,<?php echo $isSuperAdmin ? '#dc2626,#b91c1c' : '#6366f1,#4f46e5'; ?>);display:flex;align-items:center;justify-content:center;color:white;font-size:16px;font-weight:800;flex-shrink:0;">
                    <?php echo mb_strtoupper(mb_substr($u['full_name'] ?: $u['username'], 0, 1)); ?>
                </div>
                <!-- Info -->
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-weight:700;font-size:14px;color:var(--text-primary);">
                            <?php echo htmlspecialchars($u['full_name'] ?: $u['username']); ?>
                        </span>
                        <?php if ($isMe): ?>
                        <span style="font-size:10px;background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-weight:700;">Bạn</span>
                        <?php endif; ?>
                        <?php if ($u['role_name']): ?>
                        <span style="font-size:11px;padding:2px 10px;border-radius:20px;font-weight:700;background:<?php echo $rc['bg']; ?>;color:<?php echo $rc['color']; ?>;">
                            <?php echo htmlspecialchars($u['role_name']); ?>
                        </span>
                        <?php else: ?>
                        <span style="font-size:11px;background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:20px;font-weight:700;">Super Admin</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-faint);margin-top:2px;">
                        @<?php echo htmlspecialchars($u['username']); ?>
                        <?php if ($u['email']): ?> · <?php echo htmlspecialchars($u['email']); ?><?php endif; ?>
                    </div>
                </div>
                <!-- Actions -->
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <!-- Gán role -->
                    <form method="POST" style="display:flex;align-items:center;gap:6px;">
                        <?php echo \App\Helpers\CsrfHelper::field(); ?>
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <select name="role_id" class="form-control" style="border-radius:8px;border:1px solid var(--border-muted);padding:5px 8px;font-size:12px;min-width:130px;">
                            <option value="">Không có role</option>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($u['role_id']==$r['id'])?'selected':''; ?>>
                                <?php echo htmlspecialchars($r['display_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="assign_role" title="Gán role"
                            style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                            Gán
                        </button>
                    </form>

                    <!-- Reset password (không cho reset chính mình tại đây) -->
                    <?php if (!$isMe): ?>
                    <button onclick="openResetModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>')"
                        title="Đặt lại mật khẩu"
                        style="background:rgba(245,158,11,0.12);color:#d97706;border:none;border-radius:8px;padding:6px 10px;font-size:12px;cursor:pointer;">
                        <i class="fas fa-key"></i>
                    </button>
                    <?php endif; ?>

                    <!-- Xoá tài khoản -->
                    <?php if (!$isMe && !$isSuperAdmin): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xoá tài khoản @<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>? Thao tác không thể hoàn tác.');">
                        <?php echo \App\Helpers\CsrfHelper::field(); ?>
                        <input type="hidden" name="admin_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" name="delete_admin" title="Xoá tài khoản"
                            style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:6px 10px;font-size:12px;cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($adminUsers)): ?>
        <div style="text-align:center;padding:30px;color:var(--text-faint);">Chưa có tài khoản admin nào.</div>
        <?php endif; ?>
    </div></div>

    <!-- Form tạo tài khoản nhân viên mới -->
    <div id="create-staff" class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-user-plus" style="color:#16a34a;"></i> Tạo tài khoản nhân viên
        </h5>
        <form method="POST">
            <?php echo \App\Helpers\CsrfHelper::field(); ?>

            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Username <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="new_username" required
                    class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"
                    placeholder="vd: nhanvienkho1" pattern="[a-zA-Z0-9_]{3,50}">
                <span style="font-size:11px;color:var(--text-faint);">Chỉ gồm chữ, số, dấu _ (3-50 ký tự)</span>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Họ và tên
                </label>
                <input type="text" name="new_full_name"
                    class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"
                    placeholder="vd: Nguyễn Văn A">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Email
                </label>
                <input type="email" name="new_email"
                    class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"
                    placeholder="vd: nhanvien@shop.com">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Mật khẩu <span style="color:#dc2626;">*</span>
                </label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="newPwField" required minlength="6"
                        class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 38px 10px 10px;"
                        placeholder="Tối thiểu 6 ký tự">
                    <button type="button" onclick="toggleNewPw()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-faint);cursor:pointer;">
                        <i class="fas fa-eye" id="newPwEye"></i>
                    </button>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Gán Role ngay
                </label>
                <select name="new_role_id" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                    <option value="">-- Chưa gán role --</option>
                    <?php foreach ($roles as $r): ?>
                    <?php if ($r['name'] !== 'super_admin'): ?>
                    <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['display_name']); ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <span style="font-size:11px;color:var(--text-faint);">Có thể gán sau từ danh sách bên trái</span>
            </div>

            <button type="submit" name="create_admin" class="btn btn-primary"
                style="width:100%;border-radius:12px;padding:12px;font-weight:700;background:linear-gradient(135deg,#16a34a,#15803d);border:none;font-size:15px;">
                <i class="fas fa-user-plus"></i> Tạo tài khoản
            </button>
        </form>
    </div></div>
</div>

<!-- Modal reset password -->
<div id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border-radius:16px;padding:28px;width:380px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <h5 style="margin:0 0 6px;font-weight:800;">🔑 Đặt lại mật khẩu</h5>
        <p style="margin:0 0 20px;font-size:13px;color:var(--text-secondary);">
            Tài khoản: <strong id="resetUsername"></strong>
        </p>
        <form method="POST">
            <?php echo \App\Helpers\CsrfHelper::field(); ?>
            <input type="hidden" name="admin_id" id="resetAdminId">
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">Mật khẩu mới</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="resetPwField" required minlength="6"
                        class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 38px 10px 10px;"
                        placeholder="Tối thiểu 6 ký tự">
                    <button type="button" onclick="toggleResetPw()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-faint);cursor:pointer;">
                        <i class="fas fa-eye" id="resetPwEye"></i>
                    </button>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeResetModal()"
                    style="flex:1;padding:10px;border-radius:10px;background:var(--bg-elevated);border:none;font-weight:600;cursor:pointer;color:var(--text-secondary);">
                    Huỷ
                </button>
                <button type="submit" name="reset_password"
                    style="flex:1;padding:10px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;font-weight:700;cursor:pointer;color:white;">
                    <i class="fas fa-key"></i> Đặt lại
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(id, username) {
    document.getElementById('resetAdminId').value = id;
    document.getElementById('resetUsername').textContent = '@' + username;
    document.getElementById('resetPwField').value = '';
    document.getElementById('resetModal').style.display = 'flex';
}
function closeResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}
function toggleNewPw() {
    var f = document.getElementById('newPwField');
    var e = document.getElementById('newPwEye');
    f.type = f.type === 'password' ? 'text' : 'password';
    e.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
function toggleResetPw() {
    var f = document.getElementById('resetPwField');
    var e = document.getElementById('resetPwEye');
    f.type = f.type === 'password' ? 'text' : 'password';
    e.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
document.getElementById('resetModal').addEventListener('click', function(e) {
    if (e.target === this) closeResetModal();
});
</script>

<?php else: ?>
<!-- ════════════════════════════════════════════════════════════
     TAB 2: QUẢN LÝ ROLES
════════════════════════════════════════════════════════════ -->
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;align-items:start;">

    <!-- Danh sách roles -->
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 18px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-shield-alt" style="color:#7c3aed;"></i> Danh sách Roles
        </h5>
        <?php foreach ($roles as $r):
            $rc = $roleColors[$r['name']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b'];
            $perms = json_decode($r['permissions'] ?? '{}', true) ?? [];
            // Đếm user đang dùng role này
            $userCountStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role_id=? AND is_admin=1");
            $userCountStmt->execute([$r['id']]);
            $userCount = (int)$userCountStmt->fetchColumn();
        ?>
        <div style="background:var(--bg-elevated);border-radius:12px;padding:16px;margin-bottom:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:800;background:<?php echo $rc['bg']; ?>;color:<?php echo $rc['color']; ?>;">
                        <?php echo htmlspecialchars($r['display_name']); ?>
                    </span>
                    <?php if (!empty($perms['all'])): ?>
                    <span style="font-size:11px;background:rgba(239,68,68,0.12);color:#dc2626;padding:2px 8px;border-radius:20px;font-weight:700;">Toàn quyền</span>
                    <?php endif; ?>
                    <span style="font-size:11px;color:var(--text-faint);">
                        <i class="fas fa-user"></i> <?php echo $userCount; ?> người dùng
                    </span>
                </div>
                <?php if ($r['name'] !== 'super_admin'): ?>
                <form method="POST" style="display:inline;">
                    <?php echo \App\Helpers\CsrfHelper::field(); ?>
                    <input type="hidden" name="role_id" value="<?php echo $r['id']; ?>">
                    <button type="submit" name="delete_role"
                        style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:4px 10px;font-size:12px;cursor:pointer;"
                        onclick="return confirm('Xoá role \"<?php echo htmlspecialchars($r['display_name'], ENT_QUOTES); ?>\"?\nCác nhân viên đang dùng role này sẽ mất quyền truy cập.')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php if (!empty($perms) && empty($perms['all'])): ?>
            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                <?php foreach ($perms as $pk => $pv): if (!$pv) continue; ?>
                <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;">
                    <?php echo $permList[$pk] ?? $pk; ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php elseif (empty($perms)): ?>
            <span style="font-size:12px;color:var(--text-faint);font-style:italic;">Chưa có quyền nào</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div></div>

    <!-- Form tạo role mới -->
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-plus-circle" style="color:#16a34a;"></i> Tạo Role mới
        </h5>
        <form method="POST">
            <?php echo \App\Helpers\CsrfHelper::field(); ?>
            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Tên role (không dấu) <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="name" required
                    class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"
                    placeholder="vd: content_editor">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">
                    Tên hiển thị <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="display_name" required
                    class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"
                    placeholder="vd: Biên tập viên">
            </div>
            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:10px;">Quyền hạn</label>
                <div style="background:var(--bg-elevated);border-radius:12px;padding:14px;max-height:280px;overflow-y:auto;">
                    <!-- Chọn tất cả -->
                    <label style="display:flex;align-items:center;gap:10px;padding:6px 0;cursor:pointer;font-size:13px;font-weight:700;border-bottom:1px solid var(--border-subtle);margin-bottom:6px;">
                        <input type="checkbox" id="checkAll" style="width:16px;height:16px;accent-color:#6366f1;" onchange="toggleAllPerms(this.checked)">
                        Chọn tất cả
                    </label>
                    <?php foreach ($permList as $pk => $plabel): ?>
                    <label style="display:flex;align-items:center;gap:10px;padding:5px 0;cursor:pointer;font-size:13px;" class="perm-label">
                        <input type="checkbox" name="perm_<?php echo $pk; ?>" value="1" style="width:16px;height:16px;accent-color:#6366f1;" class="perm-check">
                        <?php echo $plabel; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" name="create_role" class="btn btn-primary"
                style="width:100%;border-radius:12px;padding:11px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                <i class="fas fa-save"></i> Tạo Role
            </button>
        </form>
    </div></div>
</div>

<script>
function toggleAllPerms(checked) {
    document.querySelectorAll('.perm-check').forEach(function(cb) { cb.checked = checked; });
}
</script>

<?php endif; ?>
