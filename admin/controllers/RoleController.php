<?php
class RoleController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdminUsers() {
        return $this->db->query(
            "SELECT u.id, u.username, u.full_name, u.email, u.is_admin, u.role_id,
                    r.display_name as role_name, r.name as role_key
             FROM users u LEFT JOIN roles r ON u.role_id=r.id
             WHERE u.is_admin=1 ORDER BY u.id"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignRole($userId, $roleId) {
        $result = $this->db->prepare("UPDATE users SET role_id=? WHERE id=?")->execute([$roleId ?: null, $userId]);
        PermissionMiddleware::clearCache((int)$userId);
        return $result;
    }

    public function createRole($data) {
        $perms = [];
        $permList = ['products','orders','users','inventory','chat','comments','vouchers','banners','categories','returns','reports','cms','settings','suppliers','shipping','notifications','audit','roles'];
        foreach ($permList as $perm) {
            if (!empty($data['perm_' . $perm])) $perms[$perm] = true;
        }
        $stmt = $this->db->prepare("INSERT INTO roles (name, display_name, description, permissions) VALUES (?,?,?,?)");
        return $stmt->execute([$data['name'], $data['display_name'], $data['description'] ?? '', json_encode($perms)]);
    }

    public function updateRole($id, $data) {
        $perms = [];
        $permList = ['products','orders','users','inventory','chat','comments','vouchers','banners','categories','returns','reports','cms','settings','suppliers','shipping','notifications','audit','roles'];
        foreach ($permList as $perm) {
            if (!empty($data['perm_' . $perm])) $perms[$perm] = true;
        }
        $stmt = $this->db->prepare("UPDATE roles SET display_name=?, description=?, permissions=? WHERE id=? AND name != 'super_admin'");
        return $stmt->execute([$data['display_name'], $data['description'] ?? '', json_encode($perms), $id]);
    }

    public function deleteRole($id) {
        return $this->db->prepare("DELETE FROM roles WHERE id=? AND name != 'super_admin'")->execute([$id]);
    }

    // ─────────────────────────────────────────────────────────────
    // Quản lý tài khoản nhân viên admin
    // ─────────────────────────────────────────────────────────────

    /**
     * Tạo tài khoản admin mới cho nhân viên
     * @return array ['ok'=>bool, 'error'=>string]
     */
    public function createAdminUser(array $data): array {
        $username  = trim($data['new_username'] ?? '');
        $fullName  = trim($data['new_full_name'] ?? '');
        $email     = trim($data['new_email'] ?? '');
        $password  = $data['new_password'] ?? '';
        $roleId    = intval($data['new_role_id'] ?? 0) ?: null;

        // Validate
        if (!$username || !$password)
            return ['ok' => false, 'error' => 'Username và mật khẩu không được để trống.'];
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username))
            return ['ok' => false, 'error' => 'Username chỉ gồm chữ, số, dấu gạch dưới, 3-50 ký tự.'];
        if (strlen($password) < 6)
            return ['ok' => false, 'error' => 'Mật khẩu phải có ít nhất 6 ký tự.'];
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['ok' => false, 'error' => 'Email không hợp lệ.'];

        // Kiểm tra username đã tồn tại chưa
        $chk = $this->db->prepare("SELECT id FROM users WHERE username=?");
        $chk->execute([$username]);
        if ($chk->fetch())
            return ['ok' => false, 'error' => "Username \"$username\" đã tồn tại."];

        // Kiểm tra email đã tồn tại chưa (nếu có)
        if ($email) {
            $chkEmail = $this->db->prepare("SELECT id FROM users WHERE email=?");
            $chkEmail->execute([$email]);
            if ($chkEmail->fetch())
                return ['ok' => false, 'error' => "Email \"$email\" đã được dùng."];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, full_name, email, password, is_admin, role_id, created_at)
             VALUES (?, ?, ?, ?, 1, ?, NOW())"
        );
        $ok = $stmt->execute([$username, $fullName, $email ?: null, $hash, $roleId]);
        return $ok ? ['ok' => true, 'error' => ''] : ['ok' => false, 'error' => 'Không thể tạo tài khoản. Vui lòng thử lại.'];
    }

    /**
     * Xoá tài khoản admin (không cho xoá chính mình và super_admin gốc role=null)
     * @return array ['ok'=>bool, 'error'=>string]
     */
    public function deleteAdminUser(int $id, int $currentUserId): array {
        if ($id === $currentUserId)
            return ['ok' => false, 'error' => 'Không thể tự xoá tài khoản của chính mình.'];

        // Lấy thông tin user cần xoá
        $stmt = $this->db->prepare("SELECT id, username, role_id FROM users WHERE id=? AND is_admin=1");
        $stmt->execute([$id]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target)
            return ['ok' => false, 'error' => 'Tài khoản không tồn tại hoặc không phải admin.'];

        // Không cho xoá nếu không có role (admin gốc / super admin cũ)
        if (empty($target['role_id'])) {
            // Kiểm tra xem đây có phải role super_admin không
            $roleChk = $this->db->prepare(
                "SELECT r.name FROM users u LEFT JOIN roles r ON u.role_id=r.id WHERE u.id=?"
            );
            $roleChk->execute([$id]);
            $roleRow = $roleChk->fetch(PDO::FETCH_ASSOC);
            if (!$roleRow || $roleRow['name'] === 'super_admin' || empty($target['role_id'])) {
                return ['ok' => false, 'error' => 'Không thể xoá Super Admin hoặc tài khoản không có role.'];
            }
        }

        $del = $this->db->prepare("DELETE FROM users WHERE id=? AND is_admin=1");
        $ok = $del->execute([$id]);
        PermissionMiddleware::clearCache($id);
        return $ok ? ['ok' => true, 'error' => ''] : ['ok' => false, 'error' => 'Không thể xoá tài khoản.'];
    }

    /**
     * Reset mật khẩu nhân viên
     * @return array ['ok'=>bool, 'error'=>string]
     */
    public function resetAdminPassword(int $id, string $newPassword): array {
        if (strlen($newPassword) < 6)
            return ['ok' => false, 'error' => 'Mật khẩu phải có ít nhất 6 ký tự.'];

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=? AND is_admin=1");
        $ok = $stmt->execute([$hash, $id]);
        return $ok ? ['ok' => true, 'error' => ''] : ['ok' => false, 'error' => 'Không thể đặt lại mật khẩu.'];
    }

    /**
     * Kiểm tra quyền của user cho một permission cụ thể
     */
    public static function hasPermission($db, $userId, $permission) {
        $userId = (int)$userId;

        $permCacheKey = 'admin_permissions_' . $userId;
        $roleCacheKey = 'admin_role_' . $userId;

        if (isset($_SESSION[$roleCacheKey]) && !empty($_SESSION[$roleCacheKey]['is_super'])) {
            return true;
        }

        if (isset($_SESSION[$permCacheKey])) {
            $perms = $_SESSION[$permCacheKey];
            return !empty($perms['all']) || !empty($perms[$permission]);
        }

        // Fallback: query DB trực tiếp
        $stmt = $db->prepare(
            "SELECT u.role_id, u.is_admin, r.permissions
             FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        // Admin gốc (is_admin=1, role_id=NULL) → full access
        if (!empty($row['is_admin']) && empty($row['role_id'])) return true;

        if (!$row['permissions']) return false;
        $perms = json_decode($row['permissions'], true) ?? [];
        return !empty($perms['all']) || !empty($perms[$permission]);
    }
}
