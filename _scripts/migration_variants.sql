-- ============================================================
-- migration_variants.sql — Biến thể sản phẩm (Product Variants)
-- ============================================================
-- Chạy lệnh này qua CLI (không chạy qua URL):
--   php _scripts/run_migration.php
-- Hoặc chạy trực tiếp trong phpMyAdmin / MySQL Workbench
-- ============================================================

-- Bảng biến thể sản phẩm (VD: RAM 16GB - Đỏ, SSD 512GB - Xanh)
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`     INT UNSIGNED NOT NULL,
    `name`           VARCHAR(200) NOT NULL COMMENT 'Tên biến thể đầy đủ, VD: RAM 16GB - Màu Đỏ',
    `sku`            VARCHAR(100) DEFAULT NULL COMMENT 'Mã SKU riêng của biến thể',
    `price_modifier` DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'Chênh lệch giá so với giá gốc (âm = giảm, dương = tăng)',
    `stock`          INT NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`     INT NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_variants_product` (`product_id`),
    KEY `idx_product_variants_active`  (`is_active`),
    CONSTRAINT `fk_variants_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Biến thể sản phẩm (màu sắc, RAM, SSD, v.v.)';

-- Bảng thuộc tính của biến thể (key-value flexible)
-- VD: variant_id=1, attribute_name='Màu sắc', attribute_value='Đỏ'
CREATE TABLE IF NOT EXISTS `variant_attributes` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `variant_id`      INT UNSIGNED NOT NULL,
    `attribute_name`  VARCHAR(100) NOT NULL COMMENT 'VD: Màu sắc, RAM, SSD, Bảo hành',
    `attribute_value` VARCHAR(200) NOT NULL COMMENT 'VD: Đỏ, 16GB, 512GB, 24 tháng',
    PRIMARY KEY (`id`),
    KEY `idx_variant_attrs_variant` (`variant_id`),
    CONSTRAINT `fk_variant_attrs`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Thuộc tính chi tiết của từng biến thể';

-- Thêm cột has_variants vào bảng products để dễ check
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `has_variants` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 nếu sản phẩm có biến thể, 0 nếu không' AFTER `images`;

-- Thêm cột variant_id vào order_items để track biến thể nào được đặt
ALTER TABLE `order_items`
    ADD COLUMN IF NOT EXISTS `variant_id`   INT UNSIGNED DEFAULT NULL AFTER `product_id`,
    ADD COLUMN IF NOT EXISTS `variant_name` VARCHAR(200) DEFAULT NULL AFTER `variant_id`;

-- ============================================================
-- Dữ liệu mẫu (tuỳ chọn — xoá nếu không cần)
-- ============================================================
-- Ví dụ: CPU Intel Core i5-14400F có 2 biến thể (có/không có cooler)
-- INSERT INTO `product_variants` (product_id, name, sku, price_modifier, stock) VALUES
--     (1, 'Không kèm tản nhiệt', 'I5-14400F-TRAY', 0,      10),
--     (1, 'Kèm tản nhiệt box',   'I5-14400F-BOX',  200000, 5);
-- INSERT INTO `variant_attributes` (variant_id, attribute_name, attribute_value) VALUES
--     (1, 'Loại', 'Tray (không box)'),
--     (2, 'Loại', 'Box (kèm tản nhiệt)');
