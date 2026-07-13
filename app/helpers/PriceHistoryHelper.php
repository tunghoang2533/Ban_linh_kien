<?php
namespace App\Helpers;

/**
 * PriceHistoryHelper — Tự động ghi lịch sử thay đổi giá sản phẩm
 * 
 * Ghi lại mỗi khi admin cập nhật: giá bán, giá vốn, giảm giá
 */
class PriceHistoryHelper {

    /**
     * Ghi lại thay đổi giá khi sản phẩm được cập nhật
     */
    public static function record($db, $productId, $oldData, $newData, $changedBy = null, $note = null) {
        $oldPrice    = floatval($oldData['price'] ?? 0);
        $newPrice    = floatval($newData['price'] ?? 0);
        $oldCost     = isset($oldData['cost_price']) ? floatval($oldData['cost_price']) : null;
        $newCost     = isset($newData['cost_price']) ? floatval($newData['cost_price']) : null;
        $oldDiscount = isset($oldData['discount_percent']) ? floatval($oldData['discount_percent']) : null;
        $newDiscount = isset($newData['discount_percent']) ? floatval($newData['discount_percent']) : null;

        // Chỉ ghi nếu có thay đổi
        if ($oldPrice == $newPrice && $oldCost == $newCost && $oldDiscount == $newDiscount) {
            return false;
        }

        $stmt = $db->prepare("
            INSERT INTO price_history 
                (product_id, old_price, new_price, old_cost, new_cost, old_discount, new_discount, changed_by, change_note, created_at)
            VALUES 
                (:pid, :oldp, :newp, :oldc, :newc, :oldd, :newd, :by, :note, NOW())
        ");
        return $stmt->execute([
            ':pid'  => (int)$productId,
            ':oldp' => $oldPrice,
            ':newp' => $newPrice,
            ':oldc' => $oldCost !== null ? $oldCost : null,
            ':newc' => $newCost !== null ? $newCost : null,
            ':oldd' => $oldDiscount !== null ? $oldDiscount : null,
            ':newd' => $newDiscount !== null ? $newDiscount : null,
            ':by'   => $changedBy,
            ':note' => $note,
        ]);
    }

    /**
     * Lấy lịch sử giá của 1 sản phẩm
     */
    public static function getHistory($db, $productId, $limit = 30) {
        $stmt = $db->prepare("
            SELECT ph.*, u.username AS changed_by_name
            FROM price_history ph
            LEFT JOIN users u ON ph.changed_by = u.id
            WHERE ph.product_id = :pid
            ORDER BY ph.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':pid', (int)$productId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy giá thấp nhất và cao nhất từ trước đến nay
     */
    public static function getPriceRange($db, $productId) {
        $stmt = $db->prepare("
            SELECT 
                MIN(new_price) AS min_price,
                MAX(new_price) AS max_price,
                MIN(created_at) AS first_recorded,
                MAX(created_at) AS last_changed
            FROM price_history 
            WHERE product_id = :pid
        ");
        $stmt->execute([':pid' => (int)$productId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Xóa lịch sử giá của 1 sản phẩm
     */
    public static function deleteHistory($db, $productId) {
        $stmt = $db->prepare("DELETE FROM price_history WHERE product_id = :pid");
        return $stmt->execute([':pid' => (int)$productId]);
    }
}
