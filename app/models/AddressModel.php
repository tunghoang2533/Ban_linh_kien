<?php
namespace App\Models;

use PDO;

class AddressModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDefault($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id=? AND is_default=1 LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($userId, $data) {
        // Nếu là địa chỉ đầu tiên, tự động đặt mặc định
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id=?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetchColumn();
        $isDefault = $data['is_default'] ?? ($count == 0 ? 1 : 0);
        if ($isDefault) {
            $this->db->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]);
        }
        $stmt = $this->db->prepare("INSERT INTO user_addresses (user_id, full_name, phone, province, district, ward, address_detail, is_default) VALUES (?,?,?,?,?,?,?,?)");
        return $stmt->execute([$userId, $data['full_name'], $data['phone'], $data['province'], $data['district'], $data['ward'] ?? '', $data['address_detail'], $isDefault]);
    }

    public function update($id, $userId, $data) {
        if (!empty($data['is_default'])) {
            $this->db->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]);
        }
        $stmt = $this->db->prepare("UPDATE user_addresses SET full_name=?, phone=?, province=?, district=?, ward=?, address_detail=?, is_default=? WHERE id=? AND user_id=?");
        return $stmt->execute([$data['full_name'], $data['phone'], $data['province'], $data['district'], $data['ward'] ?? '', $data['address_detail'], $data['is_default'] ?? 0, $id, $userId]);
    }

    public function delete($id, $userId) {
        return $this->db->prepare("DELETE FROM user_addresses WHERE id=? AND user_id=?")->execute([$id, $userId]);
    }

    public function setDefault($id, $userId) {
        $this->db->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]);
        return $this->db->prepare("UPDATE user_addresses SET is_default=1 WHERE id=? AND user_id=?")->execute([$id, $userId]);
    }
}
