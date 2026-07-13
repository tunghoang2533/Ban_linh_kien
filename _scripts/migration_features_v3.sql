SET NAMES utf8mb4;

-- ============================================================
-- Bảng: flash_sale_campaigns — Quản lý chiến dịch flash sale
-- ============================================================
CREATE TABLE IF NOT EXISTS `flash_sale_campaigns` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(255) NOT NULL,
    `description`   TEXT DEFAULT NULL,
    `banner_image`  VARCHAR(255) DEFAULT NULL,
    `discount_type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `discount_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `start_time`    DATETIME NOT NULL,
    `end_time`      DATETIME NOT NULL,
    `is_active`     TINYINT(1) DEFAULT 1,
    `sort_order`    INT DEFAULT 0,
    `created_by`    INT DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`is_active`, `start_time`, `end_time`),
    INDEX (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Bảng: flash_sale_products — Sản phẩm trong chiến dịch flash sale
-- ============================================================
CREATE TABLE IF NOT EXISTS `flash_sale_products` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `campaign_id`   INT NOT NULL,
    `product_id`    INT NOT NULL,
    `discount_type` ENUM('percent','fixed') DEFAULT NULL,
    `discount_value` DECIMAL(12,2) DEFAULT NULL,
    `max_quantity`  INT DEFAULT 0 COMMENT 'Số lượng tối đa cho flash sale',
    `sold_quantity` INT DEFAULT 0 COMMENT 'Đã bán trong flash sale',
    `sort_order`    INT DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_campaign_product` (`campaign_id`, `product_id`),
    INDEX (`product_id`),
    FOREIGN KEY (`campaign_id`) REFERENCES `flash_sale_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Bảng: abandoned_carts — Giỏ hàng bị bỏ quên
-- ============================================================
CREATE TABLE IF NOT EXISTS `abandoned_carts` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT DEFAULT NULL,
    `session_id`    VARCHAR(255) DEFAULT NULL,
    `cart_data`     LONGTEXT NOT NULL COMMENT 'Serialized cart contents',
    `cart_total`    DECIMAL(12,2) DEFAULT 0,
    `item_count`    INT DEFAULT 0,
    `user_name`     VARCHAR(255) DEFAULT NULL,
    `user_email`    VARCHAR(255) DEFAULT NULL,
    `user_phone`    VARCHAR(20) DEFAULT NULL,
    `status`        ENUM('active','recovered','expired','contacted') NOT NULL DEFAULT 'active',
    `recovered_at`  DATETIME DEFAULT NULL,
    `reminder_count` INT DEFAULT 0,
    `last_reminder_at` DATETIME DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`user_id`),
    INDEX (`status`, `created_at`),
    INDEX (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Bảng: import_export_logs — Lịch sử import/export dữ liệu
-- ============================================================
CREATE TABLE IF NOT EXISTS `import_export_logs` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type`          ENUM('import','export') NOT NULL,
    `entity_type`   VARCHAR(50) NOT NULL DEFAULT 'products',
    `total_rows`    INT DEFAULT 0,
    `success_rows`  INT DEFAULT 0,
    `error_rows`    INT DEFAULT 0,
    `errors`        LONGTEXT DEFAULT NULL COMMENT 'JSON of error details',
    `file_name`     VARCHAR(255) DEFAULT NULL,
    `created_by`    INT DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`type`, `entity_type`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
