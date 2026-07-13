<?php
class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getDashboardStats() {
        return [
            'total_products' => $this->countProducts(),
            'total_orders'   => $this->countOrders(),
            'total_users'    => $this->countUsers(),
            'total_revenue'  => $this->calculateTotalRevenue(),
            'recent_orders'  => $this->getRecentOrders(),
        ];
    }

    /** Doanh thu 12 tháng gần nhất (cho biểu đồ đường) */
    public function getRevenueByMonth($months = 12) {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    DATE_FORMAT(created_at, '%m/%Y')  AS month_label,
                    COALESCE(SUM(total_amount), 0)    AS revenue,
                    COUNT(*)                           AS order_count
                FROM orders
                WHERE status = 'completed'
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':m' => $months]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Điền đủ các tháng còn thiếu
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key   = date('Y-m', strtotime("-$i months"));
            $label = date('m/Y', strtotime("-$i months"));
            $result[$key] = ['month_label' => $label, 'revenue' => 0, 'order_count' => 0];
        }
        foreach ($rows as $r) {
            if (isset($result[$r['month_key']])) {
                $result[$r['month_key']]['revenue']     = (float)$r['revenue'];
                $result[$r['month_key']]['order_count'] = (int)$r['order_count'];
            }
        }
        return array_values($result);
    }

    /** Phân bổ đơn hàng theo trạng thái (cho biểu đồ donut) */
    public function getOrdersByStatus() {
        $sql = "SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status ORDER BY cnt DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Top 5 sản phẩm bán chạy nhất (cho biểu đồ bar ngang) */
    public function getTopProducts($limit = 8) {
        $sql = "SELECT p.name, SUM(oi.quantity) AS total_sold, SUM(oi.quantity * oi.price) AS total_revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o   ON oi.order_id   = o.id
                WHERE o.status = 'completed'
                GROUP BY p.id, p.name
                ORDER BY total_sold DESC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Đăng ký người dùng theo 7 ngày gần nhất (cho biểu đồ mini) */
    public function getUserRegistrationsByDay($days = 7) {
        $sql = "SELECT
                    DATE(created_at)          AS day_key,
                    DATE_FORMAT(created_at, '%d/%m') AS day_label,
                    COUNT(*)                  AS cnt
                FROM users
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
                GROUP BY day_key, day_label
                ORDER BY day_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':d' => $days]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key   = date('Y-m-d', strtotime("-$i days"));
            $label = date('d/m',   strtotime("-$i days"));
            $result[$key] = ['day_label' => $label, 'cnt' => 0];
        }
        foreach ($rows as $r) {
            if (isset($result[$r['day_key']])) {
                $result[$r['day_key']]['cnt'] = (int)$r['cnt'];
            }
        }
        return array_values($result);
    }

    /** So sánh doanh thu tháng này vs tháng trước */
    public function getMonthComparison() {
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE())   AND YEAR(created_at)=YEAR(CURDATE())   THEN total_amount END), 0) AS this_month,
            COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) THEN total_amount END), 0) AS last_month,
            COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE())   AND YEAR(created_at)=YEAR(CURDATE())   THEN 1 END), 0) AS orders_this_month,
            COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) THEN 1 END), 0) AS orders_last_month
        FROM orders WHERE status='completed'";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    private function countProducts() {
        return (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }

    private function countOrders() {
        return (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    private function countUsers() {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    private function calculateTotalRevenue() {
        return (float)$this->db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='completed'")->fetchColumn();
    }

    private function getRecentOrders() {
        $sql = "SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>