<?php
namespace App\Models;

use PDO;

/**
 * ProductVariantModel — CRUD cho biến thể sản phẩm
 *
 * Yêu cầu: đã chạy _scripts/migration_variants.sql
 */
class ProductVariantModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ── Lấy tất cả biến thể của một sản phẩm ─────────────────
    public function getByProductId(int $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, GROUP_CONCAT(CONCAT(a.attribute_name,':', a.attribute_value) ORDER BY a.id SEPARATOR '|') AS attributes
             FROM product_variants v
             LEFT JOIN variant_attributes a ON a.variant_id = v.id
             WHERE v.product_id = :pid AND v.is_active = 1
             GROUP BY v.id
             ORDER BY v.sort_order ASC, v.id ASC"
        );
        $stmt->execute(['pid' => $productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parse attributes string thành array
        foreach ($rows as &$row) {
            $row['attributes_parsed'] = [];
            if (!empty($row['attributes'])) {
                foreach (explode('|', $row['attributes']) as $pair) {
                    [$name, $value] = explode(':', $pair, 2) + ['', ''];
                    $row['attributes_parsed'][] = ['name' => $name, 'value' => $value];
                }
            }
        }

        return $rows;
    }

    // ── Lấy 1 biến thể theo ID ────────────────────────────────
    public function getById(int $variantId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $variantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ── Thêm biến thể mới ─────────────────────────────────────
    public function create(int $productId, string $name, int $priceModifier, int $stock, string $sku = ''): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO product_variants (product_id, name, sku, price_modifier, stock)
             VALUES (:pid, :name, :sku, :price_mod, :stock)"
        );
        $stmt->execute([
            'pid'       => $productId,
            'name'      => $name,
            'sku'       => $sku,
            'price_mod' => $priceModifier,
            'stock'     => $stock,
        ]);

        $variantId = (int) $this->db->lastInsertId();

        // Đánh dấu sản phẩm có biến thể
        $this->db->prepare("UPDATE products SET has_variants = 1 WHERE id = :pid")
                 ->execute(['pid' => $productId]);

        return $variantId;
    }

    // ── Thêm thuộc tính cho biến thể ─────────────────────────
    public function addAttribute(int $variantId, string $name, string $value): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO variant_attributes (variant_id, attribute_name, attribute_value)
             VALUES (:vid, :name, :value)"
        );
        $stmt->execute(['vid' => $variantId, 'name' => $name, 'value' => $value]);
    }

    // ── Cập nhật biến thể ─────────────────────────────────────
    public function update(int $variantId, string $name, int $priceModifier, int $stock, string $sku = '', int $isActive = 1): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE product_variants
             SET name = :name, sku = :sku, price_modifier = :pm, stock = :stock, is_active = :active
             WHERE id = :id"
        );
        return $stmt->execute([
            'name'   => $name,
            'sku'    => $sku,
            'pm'     => $priceModifier,
            'stock'  => $stock,
            'active' => $isActive,
            'id'     => $variantId,
        ]);
    }

    // ── Xoá biến thể (cascade xoá attributes) ─────────────────
    public function delete(int $variantId): bool
    {
        $variant = $this->getById($variantId);
        if (!$variant) return false;

        $this->db->prepare("DELETE FROM product_variants WHERE id = :id")->execute(['id' => $variantId]);

        // Nếu sản phẩm không còn biến thể nào, reset has_variants
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM product_variants WHERE product_id = :pid AND is_active = 1"
        );
        $stmt->execute(['pid' => $variant['product_id']]);
        $count = (int) $stmt->fetchColumn();

        if ($count === 0) {
            $this->db->prepare("UPDATE products SET has_variants = 0 WHERE id = :pid")
                     ->execute(['pid' => $variant['product_id']]);
        }

        return true;
    }

    // ── Giảm tồn kho biến thể khi đặt hàng ───────────────────
    public function decreaseStock(int $variantId, int $qty): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE product_variants SET stock = stock - :qty
             WHERE id = :id AND stock >= :qty"
        );
        $stmt->execute(['qty' => $qty, 'id' => $variantId]);
        return $stmt->rowCount() > 0;
    }

    // ── Lấy thuộc tính của biến thể ──────────────────────────
    public function getAttributes(int $variantId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM variant_attributes WHERE variant_id = :vid ORDER BY id ASC");
        $stmt->execute(['vid' => $variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Xoá toàn bộ attributes rồi insert lại (dùng khi save) ─
    public function replaceAttributes(int $variantId, array $attributes): void
    {
        $this->db->prepare("DELETE FROM variant_attributes WHERE variant_id = :vid")->execute(['vid' => $variantId]);
        foreach ($attributes as $attr) {
            if (!empty($attr['name']) && !empty($attr['value'])) {
                $this->addAttribute($variantId, $attr['name'], $attr['value']);
            }
        }
    }
}
