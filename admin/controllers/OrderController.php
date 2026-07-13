<?php
class OrderController {
    private $db;
    private $orderModel;

    public function __construct($db) {
        $this->db = $db;
        $this->orderModel = new OrderModel($db);
    }

    public function getOrders() {
        $sql = "SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách đơn hàng có search + filter trạng thái + filter khoảng ngày
     */
    public function getOrdersFiltered(array $filters = []) {
        $conditions = [];
        $params     = [];

        // Tìm kiếm theo tên khách / email / mã đơn
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $conditions[] = "(u.full_name LIKE :q OR u.email LIKE :q2 OR CAST(o.id AS CHAR) LIKE :q3
                              OR o.customer_name LIKE :q4 OR o.customer_email LIKE :q5)";
            $params[':q']  = $q;
            $params[':q2'] = $q;
            $params[':q3'] = $q;
            $params[':q4'] = $q;
            $params[':q5'] = $q;
        }

        // Lọc theo trạng thái
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }

        // Lọc theo ngày bắt đầu
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(o.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        // Lọc theo ngày kết thúc
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(o.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT o.*, u.full_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                {$where}
                ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Đếm theo từng trạng thái (để hiển thị tab badge) */
    public function getOrderCountByStatus() {
        $sql = "SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = ['all' => 0];
        foreach ($rows as $r) {
            $result[$r['status']] = (int)$r['cnt'];
            $result['all'] += (int)$r['cnt'];
        }
        return $result;
    }

    public function getOrderById($id) {
        $sql = "SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateOrderStatus($id, $status, $changedBy = null, $changerName = null, $note = null) {
        // Lấy trạng thái cũ trước khi update
        $old = $this->db->prepare("SELECT status FROM orders WHERE id = :id");
        $old->execute([':id' => $id]);
        $oldStatus = $old->fetchColumn();

        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':id' => $id, ':status' => $status]);

        if ($result && $oldStatus !== $status) {
            $this->logStatusChange($id, $oldStatus, $status, $changedBy, $changerName, $note);
        }
        return $result;
    }

    /**
     * Ghi lịch sử thay đổi trạng thái đơn hàng
     */
    public function logStatusChange($orderId, $fromStatus, $toStatus, $changedBy = null, $changerName = null, $note = null) {
        $sql = "INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, changer_name, note)
                VALUES (:order_id, :from_status, :to_status, :changed_by, :changer_name, :note)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':order_id'     => $orderId,
            ':from_status'  => $fromStatus,
            ':to_status'    => $toStatus,
            ':changed_by'   => $changedBy,
            ':changer_name' => $changerName,
            ':note'         => $note,
        ]);
    }

    /**
     * Lấy toàn bộ lịch sử trạng thái của một đơn hàng
     */
    public function getStatusHistory($orderId) {
        $sql = "SELECT * FROM order_status_history WHERE order_id = :order_id ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy các sản phẩm trong đơn hàng (dùng cho hóa đơn)
     */
    public function getOrderItemsById($orderId) {
        $sql = "SELECT oi.*, p.name AS product_name, p.image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>