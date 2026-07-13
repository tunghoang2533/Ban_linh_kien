<?php
namespace App\Models;

use PDO;
use App\Helpers\CacheHelper;

class ProductModel {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    public function getDb() {
        return $this->db;
    }

    // Lấy danh sách linh kiện mới nhất cho Trang chủ (có cache 5 phút)
    public function getLatestProducts($limit = 8) {
        $cacheKey = 'products_latest_' . $limit;
        return CacheHelper::remember($cacheKey, 300, function() use ($limit) {
            $sql = "SELECT p.*, c.name as category_name, b.name as brand_name 
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    JOIN brands b ON p.brand_id = b.id
                    WHERE p.is_active = 1
                    ORDER BY p.created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    // Lấy chi tiết 1 sản phẩm (chỉ hiện nếu is_active)
    public function getProductById($id) {
        $sql = "SELECT p.*, p.image, ps.spec_value as socket 
                FROM products p
                LEFT JOIN product_specs ps ON p.id = ps.product_id AND ps.spec_name = 'Socket'
                WHERE p.id = :id AND p.is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm theo danh mục và lọc tương thích Socket cho Build PC
    public function getProductsByCategory($categoryId, $requiredSocket = null) {
        if ($requiredSocket != null) {
            $sql = "SELECT p.*, ps.spec_value as socket 
                    FROM products p
                    JOIN product_specs ps ON p.id = ps.product_id 
                    WHERE p.category_id = :cat_id 
                      AND p.is_active = 1
                      AND ps.spec_name = 'Socket' 
                      AND ps.spec_value = :socket";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cat_id' => $categoryId, 
                'socket' => $requiredSocket
            ]);
        } else {
            $sql = "SELECT p.*, ps.spec_value as socket 
                    FROM products p
                    LEFT JOIN product_specs ps ON p.id = ps.product_id AND ps.spec_name = 'Socket'
                    WHERE p.category_id = :cat_id AND p.is_active = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cat_id' => $categoryId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // HÀM MỚI THÊM ĐỂ LẤY THÔNG SỐ KỸ THUẬT
    // ==========================================
    public function getProductSpecs($product_id) {
        $sql = "SELECT spec_name, spec_value FROM product_specs WHERE product_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // HÀM LẤY DANH SÁCH ẢNH PHỤ CỦA SẢN PHẨM
    // ==========================================
    public function getProductImages($product_id) {
        $sql = "SELECT id, product_id, image_url AS image, is_primary, sort_order, created_at FROM product_images WHERE product_id = :id ORDER BY sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProductImage($product_id, $image, $sort_order = 0) {
        $sql = "INSERT INTO product_images (product_id, image_url, image, sort_order) VALUES (:product_id, :image_url, :image, :sort_order)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'product_id' => $product_id,
            'image_url'  => $image,
            'image'      => $image,
            'sort_order' => $sort_order
        ]);
    }

    public function deleteProductImage($image_id) {
        // Lấy tên file trước khi xóa
        $stmt = $this->db->prepare("SELECT image_url AS image FROM product_images WHERE id = :id");
        $stmt->execute(['id' => $image_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sql = "DELETE FROM product_images WHERE id = :id";
            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute(['id' => $image_id]);
            return $row['image'];
        }
        return false;
    }

    public function deleteAllProductImages($product_id) {
        $stmt = $this->db->prepare("SELECT image_url AS image FROM product_images WHERE product_id = :id");
        $stmt->execute(['id' => $product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->db->prepare("DELETE FROM product_images WHERE product_id = :id")->execute(['id' => $product_id]);
        return $images;
    }

    // Lấy tất cả sản phẩm cho Admin
    public function getAll() {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top lượt mua (dựa vào order_items) — có cache 5 phút
    public function getTopSelling($limit = 8) {
        $cacheKey = 'products_top_selling_' . $limit;
        return CacheHelper::remember($cacheKey, 300, function() use ($limit) {
            $sql = "SELECT p.*, c.name as category_name,
                           COALESCE(SUM(oi.quantity), 0) as total_sold
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN order_items oi ON p.id = oi.product_id
                    WHERE p.is_active = 1
                    GROUP BY p.id
                    ORDER BY total_sold DESC, p.created_at DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    // Top tìm kiếm (dùng is_featured làm proxy) — có cache 5 phút
    public function getFeatured($limit = 8) {
        $cacheKey = 'products_featured_' . $limit;
        return CacheHelper::remember($cacheKey, 300, function() use ($limit) {
            $sql = "SELECT p.*, c.name as category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_featured = 1 AND p.is_active = 1
                    ORDER BY p.created_at DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Nếu chưa có is_featured, lấy sản phẩm mới nhất
            if (empty($rows)) {
                $sql2 = "SELECT p.*, c.name as category_name
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         WHERE p.is_active = 1
                         ORDER BY p.id DESC
                         LIMIT :limit";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt2->execute();
                $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            }
            return $rows;
        });
    }

    // Đang giảm giá (discount_percent > 0) — có cache 5 phút
    public function getOnSale($limit = 8) {
        $cacheKey = 'products_on_sale_' . $limit;
        return CacheHelper::remember($cacheKey, 300, function() use ($limit) {
            $sql = "SELECT p.*, c.name as category_name,
                           ROUND(p.price * (1 - p.discount_percent/100)) as sale_price
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.discount_percent > 0 AND p.is_active = 1
                    ORDER BY p.discount_percent DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    // ==========================================
    // TÌM KIẾM SẢN PHẨM (có lọc + sắp xếp + phân trang)
    // ==========================================
    public function search($keyword = '', $options = []) {
        $categoryId = isset($options['category_id']) ? (int)$options['category_id'] : 0;
        $brandId    = isset($options['brand_id'])    ? (int)$options['brand_id']    : 0;
        $priceMin   = isset($options['price_min'])   ? (float)$options['price_min'] : 0;
        $priceMax   = isset($options['price_max'])   ? (float)$options['price_max'] : 0;
        $sortBy     = $options['sort'] ?? 'newest';
        $page       = max(1, (int)($options['page'] ?? 1));
        $perPage    = max(1, min(100, (int)($options['per_page'] ?? 20)));
        $offset     = ($page - 1) * $perPage;

        $where  = ['p.is_active = 1'];
        $params = [];

        // Từ khóa tìm kiếm
        if ($keyword !== '') {
            $where[]            = '(p.name LIKE :kw OR p.description LIKE :kw2 OR c.name LIKE :kw3 OR b.name LIKE :kw4)';
            $params[':kw']      = '%' . $keyword . '%';
            $params[':kw2']     = '%' . $keyword . '%';
            $params[':kw3']     = '%' . $keyword . '%';
            $params[':kw4']     = '%' . $keyword . '%';
        }
        if ($categoryId > 0) {
            $where[]            = 'p.category_id = :cat_id';
            $params[':cat_id']  = $categoryId;
        }
        if ($brandId > 0) {
            $where[]            = 'p.brand_id = :brand_id';
            $params[':brand_id'] = $brandId;
        }
        if ($priceMin > 0) {
            $where[]            = 'COALESCE(ROUND(p.price*(1-p.discount_percent/100)), p.price) >= :pmin';
            $params[':pmin']    = $priceMin;
        }
        if ($priceMax > 0) {
            $where[]            = 'COALESCE(ROUND(p.price*(1-p.discount_percent/100)), p.price) <= :pmax';
            $params[':pmax']    = $priceMax;
        }

        $whereSQL = implode(' AND ', $where);

        // Sắp xếp
        $orderSQL = match($sortBy) {
            'price_asc'  => 'final_price ASC',
            'price_desc' => 'final_price DESC',
            'bestsell'   => 'total_sold DESC, p.created_at DESC',
            'rating'     => 'avg_rating DESC, p.created_at DESC',
            default      => 'p.created_at DESC',  // newest
        };

        $baseSql = "FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b     ON p.brand_id    = b.id
                    LEFT JOIN (
                        SELECT product_id, SUM(quantity) as total_sold
                        FROM order_items GROUP BY product_id
                    ) oi ON p.id = oi.product_id
                    LEFT JOIN (
                        SELECT product_id, AVG(rating) as avg_rating
                        FROM product_comments GROUP BY product_id
                    ) pc ON p.id = pc.product_id
                    WHERE $whereSQL";

        // Đếm tổng kết quả
        $countStmt = $this->db->prepare("SELECT COUNT(*) $baseSql");
        foreach ($params as $k => $v) { $countStmt->bindValue($k, $v); }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Lấy dữ liệu
        $dataStmt = $this->db->prepare(
            "SELECT p.*,
                    c.name as category_name,
                    b.name as brand_name,
                    COALESCE(ROUND(p.price*(1-p.discount_percent/100)), p.price) as final_price,
                    COALESCE(oi.total_sold, 0) as total_sold,
                    COALESCE(pc.avg_rating, 0) as avg_rating
             $baseSql
             ORDER BY $orderSQL
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) { $dataStmt->bindValue($k, $v); }
        $dataStmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $dataStmt->execute();
        $products = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'products'     => $products,
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $perPage,
            'total_pages'  => $total > 0 ? (int)ceil($total / $perPage) : 0,
        ];
    }

    // Lấy tất cả categories để filter
    public function getAllCategories() {
        $stmt = $this->db->query("SELECT id, name FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả brands để filter
    public function getAllBrands() {
        try {
            $stmt = $this->db->query("SELECT id, name FROM brands ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ==========================================
    // LẤY SẢN PHẨM THEO DANH SÁCH ID (giữ thứ tự)
    // ==========================================
    public function getProductsByIds(array $ids) {
        if (empty($ids)) return [];
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id IN ($placeholders) AND p.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($ids));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sắp xếp lại theo thứ tự IDs gốc
        $indexed = [];
        foreach ($products as $p) {
            $indexed[$p['id']] = $p;
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $ordered[] = $indexed[$id];
            }
        }
        return $ordered;
    }

    // ==========================================
    // FREQUENTLY BOUGHT TOGETHER — có cache 10 phút
    // ==========================================
    // ==========================================
    // BACK-IN-STOCK ALERT
    // ==========================================
    /**
     * Đăng ký nhận thông báo khi sản phẩm có hàng lại
     */
    public function subscribeBackInStock(int $productId, string $email, ?int $userId = null): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO back_in_stock_subscriptions (product_id, email, user_id, status, created_at)
                VALUES (:pid, :email, :uid, 'pending', NOW())
            ");
            $stmt->execute([
                ':pid'   => $productId,
                ':email' => $email,
                ':uid'   => $userId,
            ]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách subscriber đang pending của một sản phẩm
     */
    public function getBackInStockSubscribers(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT id, email, user_id, status, created_at, notified_at
            FROM back_in_stock_subscriptions
            WHERE product_id = :pid AND status = 'pending'
            ORDER BY created_at ASC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đánh dấu subscribers đã được thông báo
     */
    public function markNotified(int $productId): int {
        $stmt = $this->db->prepare("
            UPDATE back_in_stock_subscriptions
            SET status = 'notified', notified_at = NOW()
            WHERE product_id = :pid AND status = 'pending'
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->rowCount();
    }

    /**
     * Đếm số subscriber đang pending của một sản phẩm
     */
    public function countPendingSubscribers(int $productId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM back_in_stock_subscriptions
            WHERE product_id = :pid AND status = 'pending'
        ");
        $stmt->execute([':pid' => $productId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Kiểm tra email đã đăng ký cho sản phẩm này chưa
     */
    public function isSubscribed(int $productId, string $email): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM back_in_stock_subscriptions
            WHERE product_id = :pid AND email = :email AND status = 'pending'
        ");
        $stmt->execute([':pid' => $productId, ':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Lấy tất cả sản phẩm có subscriber (dùng cho admin)
     */
    public function getProductsWithSubscribers(): array {
        $stmt = $this->db->query("
            SELECT p.id, p.name, p.quantity, p.image,
                   COUNT(s.id) AS subscriber_count
            FROM products p
            JOIN back_in_stock_subscriptions s ON s.product_id = p.id AND s.status = 'pending'
            GROUP BY p.id, p.name, p.quantity, p.image
            ORDER BY subscriber_count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFrequentlyBoughtTogether($productId, $limit = 4) {
        $cacheKey = 'fbt_' . $productId . '_' . $limit;
        $db = $this->db;
        return CacheHelper::remember($cacheKey, 600, function() use ($productId, $limit, $db) {
            $sql = "SELECT p.id, p.name, p.price, p.discount_percent, p.image, p.quantity,
                           COUNT(*) as co_count
                    FROM order_items oi1
                    JOIN order_items oi2 ON oi2.order_id = oi1.order_id AND oi2.product_id != oi1.product_id
                    JOIN products p ON p.id = oi2.product_id
                    WHERE oi1.product_id = :pid AND p.is_active = 1 AND p.quantity > 0
                    GROUP BY p.id, p.name, p.price, p.discount_percent, p.image, p.quantity
                    ORDER BY co_count DESC
                    LIMIT :lim";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }
}
