<?php
namespace App\Helpers;

use PDO;

/**
 * LoyaltyHelper - Quản lý điểm tích lũy khách hàng
 * Tỷ lệ: 1 điểm = 1.000đ  |  Tích: floor(total / 1000) điểm / đơn
 */
class LoyaltyHelper {

    /** Lấy số điểm hiện có */
    public static function getBalance($db, $userId) {
        $stmt = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM loyalty_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        return max(0, (int)$stmt->fetchColumn());
    }

    /** Tích điểm khi đặt hàng thành công */
    public static function earnPoints($db, $userId, $orderId, $orderTotal) {
        $points = (int)floor(floatval($orderTotal) / 1000);
        if ($points <= 0) return 0;
        $db->prepare("INSERT INTO loyalty_points (user_id, points, type, ref_order_id, note) VALUES (?,?,?,?,?)")
           ->execute([$userId, $points, 'earned', $orderId, "Tich diem don #$orderId"]);
        return $points;
    }

    /** Trừ điểm khi dùng giảm giá tại checkout */
    public static function redeemPoints($db, $userId, $orderId, $pointsToUse) {
        $balance     = self::getBalance($db, $userId);
        $pointsToUse = min((int)$pointsToUse, $balance);
        if ($pointsToUse <= 0) return 0;
        $db->prepare("INSERT INTO loyalty_points (user_id, points, type, ref_order_id, note) VALUES (?,?,?,?,?)")
           ->execute([$userId, -$pointsToUse, 'redeemed', $orderId, "Dung diem don #$orderId"]);
        return $pointsToUse;
    }

    /** Lịch sử giao dịch điểm */
    public static function getHistory($db, $userId, $limit = 30) {
        $stmt = $db->prepare("SELECT * FROM loyalty_points WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Điều chỉnh thủ công (admin) */
    public static function adjust($db, $userId, $points, $note = 'Admin dieu chinh') {
        $db->prepare("INSERT INTO loyalty_points (user_id, points, type, note) VALUES (?,?,?,?)")
           ->execute([$userId, (int)$points, 'adjusted', $note]);
    }

    /** Tính giảm giá tối đa được phép dùng điểm (30% đơn hàng) */
    public static function maxRedeemValue($orderTotal, $balance) {
        $maxByPct   = (int)floor(floatval($orderTotal) * 0.30 / 1000); // 30% order, quy ra điểm
        $minBalance = 50; // Tối thiểu 50 điểm
        if ($balance < $minBalance) return 0;
        return min($balance, $maxByPct);
    }
}
?>
