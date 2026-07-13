<?php
class ProductController {
    private $db;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new ProductModel($db);
    }

    public function getProducts() {
        return $this->productModel->getAll();
    }

    public function getProductById($id) {
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function addProduct($data) {
        $sql = "INSERT INTO products (category_id, brand_id, name, price, cost_price, quantity, image, description, discount_percent, created_at) VALUES (:cat_id, :brand_id, :name, :price, :cost_price, :quantity, :image, :description, :discount, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':cat_id'      => $data['category_id'],
            ':brand_id'    => $data['brand_id'],
            ':name'        => $data['name'],
            ':price'       => $data['price'],
            ':cost_price'  => floatval($data['cost_price'] ?? 0),
            ':quantity'    => $data['quantity'],
            ':image'       => $data['image'],
            ':description' => $data['description'],
            ':discount'    => floatval($data['discount_percent'] ?? 0)
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    public function updateProduct($id, $data) {
        $sql = "UPDATE products SET category_id = :cat_id, brand_id = :brand_id, name = :name, price = :price, cost_price = :cost_price, quantity = :quantity, image = :image, description = :description, discount_percent = :discount WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':cat_id'      => $data['category_id'],
            ':brand_id'    => $data['brand_id'],
            ':name'        => $data['name'],
            ':price'       => $data['price'],
            ':cost_price'  => floatval($data['cost_price'] ?? 0),
            ':quantity'    => $data['quantity'],
            ':image'       => $data['image'],
            ':description' => $data['description'],
            ':discount'    => floatval($data['discount_percent'] ?? 0)
        ]);
    }

    // ==========================================
    // QUẢN LÝ GIẢM GIÁ
    // ==========================================
    public function updateDiscount($id, $discount_percent) {
        $discount = max(0, min(100, floatval($discount_percent)));
        $sql = "UPDATE products SET discount_percent = :discount WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':discount' => $discount, ':id' => intval($id)]);
    }

    public function getSaleProducts() {
        $sql = "SELECT p.*, c.name as category_name,
                       ROUND(p.price * (1 - p.discount_percent/100)) as sale_price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.discount_percent > 0 AND p.is_active = 1
                ORDER BY p.discount_percent DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllProductsWithDiscount() {
        $sql = "SELECT p.*, c.name as category_name,
                       ROUND(p.price * (1 - p.discount_percent/100)) as sale_price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.discount_percent DESC, p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteProduct($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function toggleProductStatus($id) {
        $sql = "UPDATE products SET is_active = IF(is_active=1, 0, 1) WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ==========================================
    // QUẢN LÝ ẢNH PHỤ CỦA SẢN PHẨM
    // ==========================================
    public function getProductImages($product_id) {
        return $this->productModel->getProductImages($product_id);
    }

    public function addProductImages($product_id, $fileNames) {
        foreach ($fileNames as $idx => $fileName) {
            $this->productModel->addProductImage($product_id, $fileName, $idx);
        }
    }

    public function deleteProductImage($image_id) {
        return $this->productModel->deleteProductImage($image_id);
    }
}
?>