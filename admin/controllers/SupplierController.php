<?php
class SupplierController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll() {
        return $this->db->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $s = $this->db->prepare("SELECT * FROM suppliers WHERE id=?");
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $data['name']), 0, 4)) . rand(100, 999);
        $stmt = $this->db->prepare("INSERT INTO suppliers (name, code, contact_person, phone, email, address, website, tax_code, bank_account, bank_name, note) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        return $stmt->execute([
            $data['name'], $code,
            $data['contact_person'] ?? '', $data['phone'] ?? '',
            $data['email'] ?? '', $data['address'] ?? '',
            $data['website'] ?? '', $data['tax_code'] ?? '',
            $data['bank_account'] ?? '', $data['bank_name'] ?? '',
            $data['note'] ?? ''
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=?, website=?, tax_code=?, bank_account=?, bank_name=?, note=?, updated_at=NOW() WHERE id=?");
        return $stmt->execute([
            $data['name'], $data['contact_person'] ?? '', $data['phone'] ?? '',
            $data['email'] ?? '', $data['address'] ?? '',
            $data['website'] ?? '', $data['tax_code'] ?? '',
            $data['bank_account'] ?? '', $data['bank_name'] ?? '',
            $data['note'] ?? '', $id
        ]);
    }

    public function delete($id) {
        return $this->db->prepare("DELETE FROM suppliers WHERE id=?")->execute([$id]);
    }

    public function toggle($id) {
        return $this->db->prepare("UPDATE suppliers SET is_active = !is_active WHERE id=?")->execute([$id]);
    }

    public function getStats() {
        $total  = $this->db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
        $active = $this->db->query("SELECT COUNT(*) FROM suppliers WHERE is_active=1")->fetchColumn();
        return ['total' => $total, 'active' => $active];
    }
}
