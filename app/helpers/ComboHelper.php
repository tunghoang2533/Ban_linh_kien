<?php
/**
 * ComboHelper — Quản lý Combo / Bundle sản phẩm
 * Dùng chung cho Admin và User
 */

namespace App\Helpers;

class ComboHelper
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Lấy danh sách combo đang active
     */
    public function getActiveCombos(): array
    {
        $stmt = $this->db->query("
            SELECT pc.*, 
                   (SELECT COUNT(*) FROM product_combo_items WHERE combo_id = pc.id) AS item_count
            FROM product_combos pc
            WHERE pc.is_active = 1
            ORDER BY pc.sort_order ASC, pc.id DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả combo (admin)
     */
    public function getAllCombos(): array
    {
        $stmt = $this->db->query("
            SELECT pc.*, 
                   (SELECT COUNT(*) FROM product_combo_items WHERE combo_id = pc.id) AS item_count
            FROM product_combos pc
            ORDER BY pc.id DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy combo theo ID
     */
    public function getComboById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM product_combos WHERE id = ?");
        $stmt->execute([$id]);
        $combo = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $combo ?: null;
    }

    /**
     * Lấy sản phẩm trong combo
     */
    public function getComboItems(int $comboId): array
    {
        $stmt = $this->db->prepare("
            SELECT pci.*, p.name, p.price, p.image, p.discount_percent, p.quantity AS stock,
                   COALESCE(ROUND(p.price * (1 - p.discount_percent / 100)), p.price) AS sale_price
            FROM product_combo_items pci
            JOIN products p ON p.id = pci.product_id
            WHERE pci.combo_id = ?
            ORDER BY pci.sort_order ASC, pci.id ASC
        ");
        $stmt->execute([$comboId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy combo có chứa 1 sản phẩm cụ thể (dùng cho product detail)
     */
    public function getCombosByProductId(int $productId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT pc.*, 
                   (SELECT COUNT(*) FROM product_combo_items WHERE combo_id = pc.id) AS item_count
            FROM product_combos pc
            JOIN product_combo_items pci ON pci.combo_id = pc.id
            WHERE pc.is_active = 1 AND pci.product_id = ?
            ORDER BY pc.sort_order ASC, pc.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $productId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Tính tổng giá gốc và combo_price nếu chưa set
     */
    public function calculateComboPrice(int $comboId): array
    {
        $items = $this->getComboItems($comboId);
        $originalPrice = 0;
        $comboPrice = 0;
        foreach ($items as $item) {
            $unitPrice = (float)($item['sale_price'] ?: $item['price']);
            $itemTotal = $unitPrice * (int)$item['quantity'];
            $originalPrice += $itemTotal;
            $comboPrice += $itemTotal;
        }

        $combo = $this->getComboById($comboId);
        if ($combo && (float)$combo['discount_percent'] > 0) {
            $comboPrice = round($comboPrice * (1 - (float)$combo['discount_percent'] / 100));
        }

        // Cập nhật DB
        $upd = $this->db->prepare("UPDATE product_combos SET original_price = ?, combo_price = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$originalPrice, $comboPrice, $comboId]);

        return [
            'original_price' => $originalPrice,
            'combo_price'    => $comboPrice,
            'discount_amount' => $originalPrice - $comboPrice,
            'discount_percent' => $originalPrice > 0 ? round(($originalPrice - $comboPrice) / $originalPrice * 100, 1) : 0,
        ];
    }

    /**
     * Tạo combo mới
     */
    public function createCombo(array $data, ?int $createdBy = null): int
    {
        // Generate slug from name
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $data['name']), '-'));
        
        $stmt = $this->db->prepare("
            INSERT INTO product_combos (name, slug, description, image, discount_percent, is_active, sort_order, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['name'],
            $slug,
            $data['description'] ?? '',
            $data['image'] ?? '',
            (float)($data['discount_percent'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            (int)($data['sort_order'] ?? 0),
            $createdBy,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật combo
     */
    public function updateCombo(int $id, array $data): bool
    {
        $sql = "UPDATE product_combos SET 
                    name = ?, description = ?, image = ?, discount_percent = ?, 
                    is_active = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['image'] ?? '',
            (float)($data['discount_percent'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    /**
     * Thêm sản phẩm vào combo
     */
    public function addComboItem(int $comboId, int $productId, int $quantity = 1, int $sortOrder = 0): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO product_combo_items (combo_id, product_id, quantity, sort_order)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$comboId, $productId, $quantity, $sortOrder]);
    }

    /**
     * Xoá sản phẩm khỏi combo
     */
    public function removeComboItem(int $itemId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_combo_items WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    /**
     * Xoá combo
     */
    public function deleteCombo(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_combos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Lấy tất cả sản phẩm (cho dropdown admin)
     */
    public function getAllProducts(): array
    {
        $stmt = $this->db->query("SELECT id, name, price, image FROM products WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

// ── class_alias để gọi từ global namespace ──
class_alias('App\\Helpers\\ComboHelper', '\\ComboHelper');

