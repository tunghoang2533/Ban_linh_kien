<?php
/**
 * FlashSaleController — Quản lý chiến dịch Flash Sale
 * 
 * Tính năng:
 * - CRUD chiến dịch flash sale (có thời gian bắt đầu/kết thúc)
 * - Thêm/xóa sản phẩm vào chiến dịch
 * - Tự động áp dụng giảm giá trong thời gian diễn ra
 * - Hiển thị đếm ngược trên trang chủ
 */
class FlashSaleController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Lấy danh sách tất cả chiến dịch flash sale
     */
    public function getAllCampaigns() {
        $sql = "SELECT fsc.*, 
                       (SELECT COUNT(*) FROM flash_sale_products WHERE campaign_id = fsc.id) AS product_count,
                       u.username AS created_by_name 
                FROM flash_sale_campaigns fsc
                LEFT JOIN users u ON fsc.created_by = u.id
                ORDER BY fsc.created_at DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chiến dịch đang hoạt động (hiện tại)
     */
    public function getActiveCampaigns() {
        $sql = "SELECT fsc.*,
                       (SELECT COUNT(*) FROM flash_sale_products WHERE campaign_id = fsc.id) AS product_count
                FROM flash_sale_campaigns fsc
                WHERE fsc.is_active = 1 
                  AND fsc.start_time <= NOW() 
                  AND fsc.end_time >= NOW()
                ORDER BY fsc.sort_order ASC, fsc.created_at DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chiến dịch sắp diễn ra
     */
    public function getUpcomingCampaigns() {
        $sql = "SELECT fsc.*,
                       (SELECT COUNT(*) FROM flash_sale_products WHERE campaign_id = fsc.id) AS product_count
                FROM flash_sale_campaigns fsc
                WHERE fsc.is_active = 1 
                  AND fsc.start_time > NOW()
                ORDER BY fsc.start_time ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy 1 chiến dịch theo ID
     */
    public function getCampaignById($id) {
        $stmt = $this->db->prepare("SELECT * FROM flash_sale_campaigns WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm trong chiến dịch
     */
    public function getCampaignProducts($campaignId) {
        $stmt = $this->db->prepare("
            SELECT fsp.*, p.name AS product_name, p.image, p.price, p.quantity,
                   c.name AS category_name,
                   ROUND(p.price * (1 - COALESCE(fsp.discount_value, fsc.discount_value)/100)) AS sale_price,
                   ROUND(p.price * COALESCE(fsp.discount_value, fsc.discount_value)/100) AS discount_amount
            FROM flash_sale_products fsp
            JOIN products p ON fsp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN flash_sale_campaigns fsc ON fsp.campaign_id = fsc.id
            WHERE fsp.campaign_id = :cid
            ORDER BY fsp.sort_order ASC, p.name ASC
        ");
        $stmt->execute([':cid' => (int)$campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo chiến dịch mới
     */
    public function createCampaign($data, $createdBy = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO flash_sale_campaigns 
                    (name, description, banner_image, discount_type, discount_value, start_time, end_time, is_active, sort_order, created_by, created_at)
                VALUES 
                    (:name, :desc, :banner, :dtype, :dval, :start, :end, :active, :sort, :by, NOW())
            ");
            return $stmt->execute([
                ':name'   => trim($data['name']),
                ':desc'   => trim($data['description'] ?? ''),
                ':banner' => $data['banner_image'] ?? '',
                ':dtype'  => $data['discount_type'] ?? 'percent',
                ':dval'   => floatval($data['discount_value'] ?? 0),
                ':start'  => $data['start_time'],
                ':end'    => $data['end_time'],
                ':active' => isset($data['is_active']) ? 1 : 0,
                ':sort'   => intval($data['sort_order'] ?? 0),
                ':by'     => $createdBy,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cập nhật chiến dịch
     */
    public function updateCampaign($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE flash_sale_campaigns SET
                    name = :name,
                    description = :desc,
                    discount_type = :dtype,
                    discount_value = :dval,
                    start_time = :start,
                    end_time = :end,
                    is_active = :active,
                    sort_order = :sort
                WHERE id = :id
            ");
            return $stmt->execute([
                ':name'   => trim($data['name']),
                ':desc'   => trim($data['description'] ?? ''),
                ':dtype'  => $data['discount_type'] ?? 'percent',
                ':dval'   => floatval($data['discount_value'] ?? 0),
                ':start'  => $data['start_time'],
                ':end'    => $data['end_time'],
                ':active' => isset($data['is_active']) ? 1 : 0,
                ':sort'   => intval($data['sort_order'] ?? 0),
                ':id'     => (int)$id,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Xóa chiến dịch
     */
    public function deleteCampaign($id) {
        try {
            $this->db->prepare("DELETE FROM flash_sale_products WHERE campaign_id = :id")
                     ->execute([':id' => (int)$id]);
            $stmt = $this->db->prepare("DELETE FROM flash_sale_campaigns WHERE id = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Bật/tắt trạng thái chiến dịch
     */
    public function toggleCampaign($id) {
        $stmt = $this->db->prepare("UPDATE flash_sale_campaigns SET is_active = !is_active WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    /**
     * Thêm sản phẩm vào chiến dịch
     */
    public function addProduct($campaignId, $productId, $discountType = null, $discountValue = null, $maxQty = 0) {
        try {
            // Kiểm tra sản phẩm đã tồn tại chưa
            $check = $this->db->prepare("SELECT id FROM flash_sale_products WHERE campaign_id = :cid AND product_id = :pid");
            $check->execute([':cid' => (int)$campaignId, ':pid' => (int)$productId]);
            if ($check->fetch()) return false;

            $stmt = $this->db->prepare("
                INSERT INTO flash_sale_products (campaign_id, product_id, discount_type, discount_value, max_quantity)
                VALUES (:cid, :pid, :dtype, :dval, :maxqty)
            ");
            return $stmt->execute([
                ':cid'    => (int)$campaignId,
                ':pid'    => (int)$productId,
                ':dtype'  => $discountType,
                ':dval'   => $discountValue !== null ? floatval($discountValue) : null,
                ':maxqty' => (int)$maxQty,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Xóa sản phẩm khỏi chiến dịch
     */
    public function removeProduct($id) {
        $stmt = $this->db->prepare("DELETE FROM flash_sale_products WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    /**
     * Xóa nhiều sản phẩm khỏi chiến dịch
     */
    public function removeProducts($campaignId, $productIds) {
        if (empty($productIds)) return true;
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $params = array_merge([$campaignId], $productIds);
        $types = str_repeat('i', count($productIds) + 1);
        
        $stmt = $this->db->prepare("DELETE FROM flash_sale_products WHERE campaign_id = ? AND product_id IN ($placeholders)");
        return $stmt->execute($params);
    }

    /**
     * Lấy sản phẩm flash sale đang hoạt động (cho front-end)
     */
    public function getActiveFlashSaleProducts() {
        $sql = "SELECT p.*, 
                       fsp.id AS fsp_id,
                       fsc.name AS campaign_name,
                       fsc.end_time,
                       COALESCE(fsp.discount_value, fsc.discount_value) AS flash_discount,
                       fsp.max_quantity,
                       fsp.sold_quantity,
                       ROUND(p.price * (1 - COALESCE(fsp.discount_value, fsc.discount_value)/100)) AS flash_price,
                       c.name AS category_name
                FROM flash_sale_campaigns fsc
                JOIN flash_sale_products fsp ON fsp.campaign_id = fsc.id
                JOIN products p ON fsp.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE fsc.is_active = 1
                  AND fsc.start_time <= NOW()
                  AND fsc.end_time >= NOW()
                  AND p.is_active = 1
                ORDER BY fsc.sort_order ASC, fsp.sort_order ASC, p.name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê nhanh
     */
    public function getStats() {
        $active  = $this->db->query("SELECT COUNT(*) FROM flash_sale_campaigns WHERE is_active=1 AND start_time<=NOW() AND end_time>=NOW()")->fetchColumn();
        $upcoming = $this->db->query("SELECT COUNT(*) FROM flash_sale_campaigns WHERE is_active=1 AND start_time>NOW()")->fetchColumn();
        $total   = $this->db->query("SELECT COUNT(*) FROM flash_sale_campaigns")->fetchColumn();
        $products = $this->db->query("SELECT COUNT(DISTINCT product_id) FROM flash_sale_products fsp JOIN flash_sale_campaigns fsc ON fsp.campaign_id=fsc.id WHERE fsc.is_active=1 AND fsc.start_time<=NOW() AND fsc.end_time>=NOW()")->fetchColumn();
        return [
            'active'   => (int)$active,
            'upcoming' => (int)$upcoming,
            'total'    => (int)$total,
            'products' => (int)$products,
        ];
    }

    /**
     * Lấy danh sách sản phẩm để thêm vào chiến dịch (có search)
     */
    public function searchProducts($keyword = '') {
        $sql = "SELECT p.id, p.name, p.price, p.image, p.quantity, p.is_active,
                       c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE (p.name LIKE :kw OR c.name LIKE :kw2) AND p.is_active = 1
                ORDER BY p.name ASC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $kw = '%' . $keyword . '%';
        $stmt->execute([':kw' => $kw, ':kw2' => $kw]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
