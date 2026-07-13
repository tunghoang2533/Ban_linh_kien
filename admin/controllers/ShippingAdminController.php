<?php
class ShippingAdminController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getZones() {
        return $this->db->query("SELECT * FROM shipping_zones ORDER BY base_fee ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createZone($data) {
        $provinces = array_filter(array_map('trim', explode(',', $data['provinces'] ?? '')));
        $stmt = $this->db->prepare("INSERT INTO shipping_zones (zone_name, provinces, base_fee, free_shipping_min, is_active) VALUES (?,?,?,?,1)");
        return $stmt->execute([$data['zone_name'], json_encode(array_values($provinces)), $data['base_fee'] ?? 50000, $data['free_shipping_min'] ?? 0]);
    }

    public function updateZone($id, $data) {
        $provinces = array_filter(array_map('trim', explode(',', $data['provinces'] ?? '')));
        $stmt = $this->db->prepare("UPDATE shipping_zones SET zone_name=?, provinces=?, base_fee=?, free_shipping_min=? WHERE id=?");
        return $stmt->execute([$data['zone_name'], json_encode(array_values($provinces)), $data['base_fee'] ?? 50000, $data['free_shipping_min'] ?? 0, $id]);
    }

    public function deleteZone($id) {
        return $this->db->prepare("DELETE FROM shipping_zones WHERE id=?")->execute([$id]);
    }

    public function toggleZone($id) {
        return $this->db->prepare("UPDATE shipping_zones SET is_active = !is_active WHERE id=?")->execute([$id]);
    }

    public function calculateFee($province, $orderTotal) {
        $zones = $this->getZones();
        foreach ($zones as $zone) {
            if (!$zone['is_active']) continue;
            $provinces = json_decode($zone['provinces'], true) ?? [];
            if (!empty($provinces) && !in_array($province, $provinces)) continue;
            if ($zone['free_shipping_min'] > 0 && $orderTotal >= $zone['free_shipping_min']) return 0;
            return (int)$zone['base_fee'];
        }
        return 50000;
    }

    public function getZoneById($id) {
        $s = $this->db->prepare("SELECT * FROM shipping_zones WHERE id=?");
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
}
