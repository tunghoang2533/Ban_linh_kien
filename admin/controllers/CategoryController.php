<?php
class CategoryController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllCategories() {
        $sql = "SELECT c.*, COUNT(p.id) AS product_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                GROUP BY c.id, c.name, c.slug
                ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCategory($name, $slug = '') {
        if ($slug === '') $slug = $this->makeSlug($name);
        $stmt = $this->db->prepare("INSERT INTO categories (name, slug) VALUES (:name, :slug)");
        return $stmt->execute([':name' => trim($name), ':slug' => $slug]);
    }

    public function updateCategory($id, $name, $slug = '') {
        if ($slug === '') $slug = $this->makeSlug($name);
        $stmt = $this->db->prepare("UPDATE categories SET name = :name, slug = :slug WHERE id = :id");
        return $stmt->execute([':name' => trim($name), ':slug' => $slug, ':id' => $id]);
    }

    public function deleteCategory($id) {
        // Chỉ xoá nếu không có sản phẩm
        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt2->execute([':id' => $id]);
        if ((int)$stmt2->fetchColumn() > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function canDeleteCategory($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() === 0;
    }

    /* ── Brands ── */
    public function getAllBrands() {
        $sql = "SELECT b.*, COUNT(p.id) AS product_count
                FROM brands b
                LEFT JOIN products p ON p.brand_id = b.id
                GROUP BY b.id, b.name, b.image
                ORDER BY b.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBrandById($id) {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBrand($name, $image = '') {
        $stmt = $this->db->prepare("INSERT INTO brands (name, image) VALUES (:name, :image)");
        return $stmt->execute([':name' => trim($name), ':image' => $image]);
    }

    public function updateBrand($id, $name, $image = null) {
        if ($image !== null) {
            $stmt = $this->db->prepare("UPDATE brands SET name = :name, image = :image WHERE id = :id");
            return $stmt->execute([':name' => trim($name), ':image' => $image, ':id' => $id]);
        }
        $stmt = $this->db->prepare("UPDATE brands SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => trim($name), ':id' => $id]);
    }

    public function deleteBrand($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = :id");
        $stmt->execute([':id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) return false;
        $stmt2 = $this->db->prepare("DELETE FROM brands WHERE id = :id");
        return $stmt2->execute([':id' => $id]);
    }

    public function canDeleteBrand($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = :id");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() === 0;
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    private function makeSlug($str) {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[áàảãạăắặằẩẫấầậâ]/u', 'a', $str);
        $str = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $str);
        $str = preg_replace('/[íìỉĩị]/u', 'i', $str);
        $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $str);
        $str = preg_replace('/[úùủũụưứừửữự]/u', 'u', $str);
        $str = preg_replace('/[ýỳỷỹỵ]/u', 'y', $str);
        $str = preg_replace('/đ/u', 'd', $str);
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        return trim($str, '-');
    }
}
?>
