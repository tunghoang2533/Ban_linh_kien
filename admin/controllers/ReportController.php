<?php
class ReportController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getSummary($from, $to) {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total_revenue, COUNT(*) as total_orders, AVG(total_amount) as avg_order FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status NOT IN ('cancelled')");
        $stmt->execute([$from, $to]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    }

    public function getRevenueByPeriod($from, $to) {
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status NOT IN ('cancelled') GROUP BY DATE(created_at) ORDER BY date ASC");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProducts($from, $to, $limit = 10) {
        $limit = max(1, min(100, (int)$limit));
        $stmt = $this->db->prepare("SELECT p.name, SUM(oi.quantity) as sold_qty, SUM(oi.quantity * oi.price) as revenue FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id LEFT JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status NOT IN ('cancelled') GROUP BY oi.product_id ORDER BY sold_qty DESC LIMIT ?");
        $stmt->bindValue(1, $from);
        $stmt->bindValue(2, $to);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderStatusBreakdown($from, $to) {
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as cnt FROM orders WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowStockProducts($threshold = 5) {
        $threshold = max(0, min(1000000, (int)$threshold));
        $stmt = $this->db->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.quantity <= ? AND p.is_active=1 ORDER BY p.quantity ASC LIMIT 20");
        $stmt->bindValue(1, $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewCustomers($from, $to) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ? AND is_admin=0");
        $stmt->execute([$from, $to]);
        return (int)$stmt->fetchColumn();
    }

    public function getTopCustomers($from, $to, $limit = 10) {
        $limit = max(1, min(100, (int)$limit));
        $stmt = $this->db->prepare("SELECT u.full_name, u.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status NOT IN ('cancelled') GROUP BY o.user_id ORDER BY total_spent DESC LIMIT ?");
        $stmt->bindValue(1, $from);
        $stmt->bindValue(2, $to);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryRevenue($from, $to) {
        $stmt = $this->db->prepare("SELECT c.name, SUM(oi.quantity * oi.price) as revenue FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status NOT IN ('cancelled') GROUP BY c.id ORDER BY revenue DESC LIMIT 10");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ═══════════════════════════════════════════════
    // PROFIT-RELATED METHODS (for enhanced profit report)
    // ═══════════════════════════════════════════════

    /**
     * Lợi nhuận theo ngày — dùng cho biểu đồ line
     */
    public function getProfitByPeriod($from, $to) {
        $stmt = $this->db->prepare("
            SELECT
                DATE(o.created_at) AS day,
                SUM(oi.price * oi.quantity) AS revenue,
                SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
                SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit,
                COUNT(DISTINCT o.id) AS orders
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            WHERE o.status NOT IN ('cancelled','pending')
              AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY DATE(o.created_at)
            ORDER BY day ASC
        ");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lợi nhuận theo danh mục — dùng cho biểu đồ pie/bar
     */
    public function getCategoryProfit($from, $to) {
        $stmt = $this->db->prepare("
            SELECT
                c.name AS cat_name,
                SUM(oi.quantity * oi.price) AS revenue,
                SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
                SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit,
                COUNT(DISTINCT o.id) AS orders,
                CASE WHEN SUM(oi.quantity * oi.price) > 0
                    THEN ROUND(SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity)
                        / SUM(oi.quantity * oi.price) * 100, 1)
                    ELSE 0 END AS margin_pct
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            JOIN categories c ON c.id = p.category_id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status NOT IN ('cancelled','pending')
              AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY c.id, c.name
            ORDER BY profit DESC
            LIMIT 10
        ");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top khách hàng theo lợi nhuận
     */
    public function getTopProfitCustomers($from, $to, $limit = 10) {
        $limit = max(1, min(100, (int)$limit));
        $stmt = $this->db->prepare("
            SELECT
                u.full_name, u.email,
                COUNT(DISTINCT o.id) AS order_count,
                SUM(oi.quantity * oi.price) AS revenue,
                SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
                SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.status NOT IN ('cancelled','pending')
              AND DATE(o.created_at) BETWEEN ? AND ?
              AND o.user_id IS NOT NULL
            GROUP BY o.user_id
            ORDER BY profit DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $from);
        $stmt->bindValue(2, $to);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê số sản phẩm chưa có giá vốn
     */
    public function getNoCostCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM products WHERE (cost_price IS NULL OR cost_price = 0) AND is_active = 1")->fetchColumn();
    }
}
