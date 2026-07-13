<?php
class VoucherAdminController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllVouchers() {
        $stmt = $this->db->query("SELECT * FROM vouchers ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVoucherById($id) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createVoucher($data) {
        $sql = "INSERT INTO vouchers 
                    (code, name, description, type, value, max_discount, min_order, usage_limit, expire_date, is_active)
                VALUES 
                    (:code, :name, :description, :type, :value, :max_discount, :min_order, :usage_limit, :expire_date, :is_active)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'code'        => strtoupper(trim($data['code'])),
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'type'        => $data['type'],
            'value'       => floatval($data['value']),
            'max_discount'=> ($data['max_discount'] !== '' && $data['max_discount'] !== null) ? floatval($data['max_discount']) : null,
            'min_order'   => floatval($data['min_order'] ?? 0),
            'usage_limit' => ($data['usage_limit'] !== '' && $data['usage_limit'] !== null) ? intval($data['usage_limit']) : null,
            'expire_date' => $data['expire_date'],
            'is_active'   => isset($data['is_active']) ? 1 : 0,
        ]);
    }

    public function updateVoucher($id, $data) {
        $sql = "UPDATE vouchers SET
                    code = :code,
                    name = :name,
                    description = :description,
                    type = :type,
                    value = :value,
                    max_discount = :max_discount,
                    min_order = :min_order,
                    usage_limit = :usage_limit,
                    expire_date = :expire_date,
                    is_active = :is_active
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'          => $id,
            'code'        => strtoupper(trim($data['code'])),
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'type'        => $data['type'],
            'value'       => floatval($data['value']),
            'max_discount'=> ($data['max_discount'] !== '' && $data['max_discount'] !== null) ? floatval($data['max_discount']) : null,
            'min_order'   => floatval($data['min_order'] ?? 0),
            'usage_limit' => ($data['usage_limit'] !== '' && $data['usage_limit'] !== null) ? intval($data['usage_limit']) : null,
            'expire_date' => $data['expire_date'],
            'is_active'   => isset($data['is_active']) ? 1 : 0,
        ]);
    }

    public function deleteVoucher($id) {
        // Xóa usage records trước, sau đó xóa voucher
        $this->db->prepare("DELETE FROM voucher_usages WHERE voucher_id = :id")->execute(['id' => $id]);
        $stmt = $this->db->prepare("DELETE FROM vouchers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function toggleVoucherStatus($id) {
        $stmt = $this->db->prepare("UPDATE vouchers SET is_active = IF(is_active=1, 0, 1) WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getVoucherStats() {
        $row = $this->db->query("SELECT COUNT(*) as total, SUM(is_active) as active, SUM(used_count) as total_used FROM vouchers")->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    /** Láº¥y voucher cÃ¡ nhÃ¢n cho 1 user */
    public function getPersonalVouchers($userId) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Láº¥y danh sÃ¡ch user cho dropdown */
    public function getAllUsers() {
        return $this->db->query("SELECT id, full_name, email FROM users ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Äáº¿m sá»‘ voucher cÃ¡ nhÃ¢n */
    public function countPersonalVouchers() {
        return (int)$this->db->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NOT NULL")->fetchColumn();
    }
}
?>
