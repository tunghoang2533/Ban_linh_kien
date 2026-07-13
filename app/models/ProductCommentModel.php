<?php
namespace App\Models;

use PDO;

class ProductCommentModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS product_comments (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            name VARCHAR(100) NOT NULL,
            rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
            comment TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product_id (product_id),
            INDEX idx_user_id (user_id),
            CONSTRAINT fk_comment_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->exec($sql);
    }

    public function getCommentsByProduct($productId) {
        // Chỉ lấy bình luận chưa bị admin ẩn (is_hidden = 0)
        $sql = "SELECT pc.*, u.full_name, u.username
                FROM product_comments pc
                LEFT JOIN users u ON pc.user_id = u.id
                WHERE pc.product_id = :product_id
                  AND pc.is_hidden = 0
                ORDER BY pc.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($productId) {
        // Chỉ tính rating từ bình luận đang hiển thị
        $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews
                FROM product_comments
                WHERE product_id = :product_id
                  AND is_hidden = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addComment($productId, $userId, $name, $rating, $comment) {
        $sql = "INSERT INTO product_comments (product_id, user_id, name, rating, comment, created_at) VALUES (:product_id, :user_id, :name, :rating, :comment, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'name' => $name,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }

    public function getAllComments($filterStatus = 'all', $filterRating = 'all', $q = '') {
        $conditions = [];
        $params = [];

        if ($filterStatus === 'visible') {
            $conditions[] = 'pc.is_hidden = 0';
        } elseif ($filterStatus === 'hidden') {
            $conditions[] = 'pc.is_hidden = 1';
        }

        if ($filterRating !== 'all' && is_numeric($filterRating)) {
            $conditions[] = 'pc.rating = :rating';
            $params[':rating'] = (int)$filterRating;
        }

        if ($q !== '') {
            $conditions[] = '(pc.name LIKE :q OR pc.comment LIKE :q2 OR p.name LIKE :q3)';
            $params[':q']  = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT pc.*, p.name AS product_name, u.full_name, u.username
                FROM product_comments pc
                LEFT JOIN products p ON pc.product_id = p.id
                LEFT JOIN users u ON pc.user_id = u.id
                {$where}
                ORDER BY pc.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentStats() {
        $sql = "SELECT
            COUNT(*) AS total,
            SUM(is_hidden = 0) AS visible,
            SUM(is_hidden = 1) AS hidden,
            ROUND(AVG(rating), 1) AS avg_rating,
            SUM(rating = 5) AS star5,
            SUM(rating = 4) AS star4,
            SUM(rating = 3) AS star3,
            SUM(rating <= 2) AS star_low
        FROM product_comments";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteComment($id) {
        $stmt = $this->db->prepare("DELETE FROM product_comments WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function hideComment($id) {
        $stmt = $this->db->prepare("UPDATE product_comments SET is_hidden = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function showComment($id) {
        $stmt = $this->db->prepare("UPDATE product_comments SET is_hidden = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>