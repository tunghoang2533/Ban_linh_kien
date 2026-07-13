<?php
namespace App\Models;

use PDO;

class WishlistModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT w.*, p.name, p.price, p.image, p.discount_percent, p.quantity as stock, p.is_active FROM wishlists w LEFT JOIN products p ON w.product_id=p.id WHERE w.user_id=? ORDER BY w.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggle($userId, $productId) {
        $check = $this->db->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
        $check->execute([$userId, $productId]);
        if ($check->fetch()) {
            $this->db->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$userId, $productId]);
            return false;
        }
        $this->db->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?,?)")->execute([$userId, $productId]);
        return true;
    }

    public function isInWishlist($userId, $productId) {
        $stmt = $this->db->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }

    public function remove($userId, $productId) {
        return $this->db->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$userId, $productId]);
    }

    public function count($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id=?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function clear($userId) {
        return $this->db->prepare("DELETE FROM wishlists WHERE user_id=?")->execute([$userId]);
    }
}
