-- ============================================================
-- MIGRATION: Product Combos / Bundles
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `product_combos` (
    `id`              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) DEFAULT NULL,
    `description`     TEXT DEFAULT NULL,
    `image`           VARCHAR(255) DEFAULT NULL,
    `discount_percent` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Phần trăm giảm so với tổng giá gốc',
    `combo_price`     DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Giá combo (có thể tính tự động từ discount)',
    `original_price`  DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Tổng giá gốc (tính tự động)',
    `is_active`       TINYINT(1) DEFAULT 1,
    `sort_order`      INT DEFAULT 0,
    `created_by`      INT DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`is_active`, `sort_order`),
    INDEX (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Combo / Bundle sản phẩm';

CREATE TABLE IF NOT EXISTS `product_combo_items` (
    `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `combo_id`   INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity`   INT NOT NULL DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`combo_id`) REFERENCES `product_combos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_combo_product` (`combo_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sản phẩm trong combo';
