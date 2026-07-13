<?php
class RoleController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdminUsers() {
        return $this->db->query("SELECT u.id, u.username, u.full_name, u.email, u.is_admin, u.role_id, r.display_name as role_name, r.name as role_key FROM users u LEFT JOIN roles r ON u.role_id=r.id WHERE u.is_admin=1 ORDER BY u.id")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignRole($userId, $roleId) {
        return $this->db->prepare("UPDATE users SET role_id=? WHERE id=?")->execute([$roleId ?: null, $userId]);
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

    public static function hasPermission($db, $userId, $permission) {
        $stmt = $db->prepare("SELECT r.permissions FROM users u LEFT JOIN roles r ON u.role_id=r.id WHERE u.id=?");
        $stmt->execute([(int)$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['permissions']) return false;
        $perms = json_decode($row['permissions'], true) ?? [];
        return !empty($perms['all']) || !empty($perms[$permission]);
    }
}
