<?php
namespace App\Models;

use PDO;

class VoucherModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE code = :code AND is_active = 1");
        $stmt->execute(['code' => strtoupper(trim($code))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAvailableForUser($userId) {
        $sql = "SELECT v.* FROM vouchers v
                WHERE v.is_active = 1
                  AND v.expire_date >= CURDATE()
                  AND (v.usage_limit IS NULL OR v.used_count < v.usage_limit)
                  AND v.id NOT IN (
                      SELECT voucher_id FROM voucher_usages WHERE user_id = :uid
                  )
                ORDER BY v.type DESC, v.value DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasUserUsed($voucherId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT id FROM voucher_usages WHERE voucher_id = :vid AND user_id = :uid LIMIT 1"
        );
        $stmt->execute(['vid' => $voucherId, 'uid' => $userId]);
        return (bool) $stmt->fetch();
    }

    public function validate($code, $userId, $cartTotal) {
        $voucher = $this->getByCode($code);

        if (!$voucher) {
            return ['ok' => false, 'msg' => 'Mã voucher không tồn tại hoặc đã bị vô hiệu hóa.'];
        }
        if ($voucher['expire_date'] < date('Y-m-d')) {
            return ['ok' => false, 'msg' => 'Voucher đã hết hạn sử dụng.'];
        }
        if ($voucher['usage_limit'] !== null && $voucher['used_count'] >= $voucher['usage_limit']) {
            return ['ok' => false, 'msg' => 'Voucher đã hết lượt sử dụng.'];
        }
        if ($this->hasUserUsed($voucher['id'], $userId)) {
            return ['ok' => false, 'msg' => 'Bạn đã sử dụng voucher này rồi.'];
        }
        if ($voucher['min_order'] > 0 && $cartTotal < $voucher['min_order']) {
            return [
                'ok'  => false,
                'msg' => 'Đơn hàng tối thiểu ' . number_format($voucher['min_order'], 0, ',', '.') . '₫ để dùng voucher này.'
            ];
        }

        $discount = $this->calcDiscount($voucher, $cartTotal);
        return ['ok' => true, 'voucher' => $voucher, 'discount' => $discount];
    }

    public function calcDiscount($voucher, $cartTotal) {
        if ($voucher['type'] === 'percent') {
            $d = $cartTotal * $voucher['value'] / 100;
            if ($voucher['max_discount'] !== null) $d = min($d, $voucher['max_discount']);
            return round($d);
        }
        if ($voucher['type'] === 'fixed') {
            return min($voucher['value'], $cartTotal);
        }
        return 0;
    }

    public function markUsed($voucherId, $userId, $orderId = null) {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO voucher_usages (voucher_id, user_id, order_id) VALUES (:vid, :uid, :oid)"
        );
        $stmt->execute(['vid' => $voucherId, 'uid' => $userId, 'oid' => $orderId]);
        $this->db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id")
                 ->execute(['id' => $voucherId]);
    }
}
?>
