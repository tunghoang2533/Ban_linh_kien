<?php
class ReturnAdminController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll($status = 'all') {
        $where = $status !== 'all' ? "WHERE r.status = " . $this->db->quote($status) : '';
        return $this->db->query("SELECT r.*, u.full_name, u.phone, o.tracking_code FROM return_requests r LEFT JOIN users u ON r.user_id=u.id LEFT JOIN orders o ON r.order_id=o.id $where ORDER BY r.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT r.*, u.full_name, u.phone, u.email, o.tracking_code, o.total_amount FROM return_requests r LEFT JOIN users u ON r.user_id=u.id LEFT JOIN orders o ON r.order_id=o.id WHERE r.id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $adminNote, $refundAmount, $adminId) {
        $resolvedAt = in_array($status, ['approved','rejected','completed']) ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare("UPDATE return_requests SET status=?, admin_note=?, refund_amount=?, resolved_by=?, resolved_at=?, updated_at=NOW() WHERE id=?");
        return $stmt->execute([$status, $adminNote, $refundAmount, $adminId, $resolvedAt, $id]);
    }

    public function getStats() {
        $stats = [];
        $rows = $this->db->query("SELECT status, COUNT(*) as cnt FROM return_requests GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) $stats[$r['status']] = $r['cnt'];
        return $stats;
    }

    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM return_requests")->fetchColumn();
    }
}
