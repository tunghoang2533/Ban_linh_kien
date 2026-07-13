<?php
class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /** Lấy danh sách tất cả người dùng kèm số đơn hàng */
    public function getUsers() {
        $sql = "SELECT u.*,
                       (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                       (SELECT SUM(o2.total_amount) FROM orders o2 WHERE o2.user_id = u.id AND o2.status = 'completed') AS total_spent
                FROM users u
                ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy thông tin 1 người dùng theo ID */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Lấy lịch sử đơn hàng của 1 người dùng */
    public function getUserOrders($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Thống kê nhanh cho trang chi tiết user */
    public function getUserStats($userId) {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_orders,
                COALESCE(SUM(CASE WHEN status='completed' THEN total_amount ELSE 0 END), 0) AS total_spent,
                COALESCE(SUM(CASE WHEN status='pending' OR status='processing' THEN 1 ELSE 0 END), 0) AS pending_orders,
                COALESCE(SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END), 0) AS cancelled_orders
             FROM orders WHERE user_id = :uid"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Khoá tài khoản */
    public function blockUser($id, $reason = '') {
        $sql = "UPDATE users SET is_blocked = 1, blocked_reason = :reason WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':reason' => $reason]);
    }

    /** Mở khoá tài khoản */
    public function unblockUser($id) {
        $sql = "UPDATE users SET is_blocked = 0, blocked_reason = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>