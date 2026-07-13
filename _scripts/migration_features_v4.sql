SET NAMES utf8mb4;

-- ============================================================
-- Bảng: price_history — Lịch sử thay đổi giá sản phẩm
-- ============================================================
CREATE TABLE IF NOT EXISTS `price_history` (
    `id`          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT NOT NULL,
    `old_price`   DECIMAL(12,0) NOT NULL DEFAULT 0,
    `new_price`   DECIMAL(12,0) NOT NULL DEFAULT 0,
    `old_cost`    DECIMAL(12,0) DEFAULT NULL,
    `new_cost`    DECIMAL(12,0) DEFAULT NULL,
    `old_discount` DECIMAL(5,2) DEFAULT NULL,
    `new_discount` DECIMAL(5,2) DEFAULT NULL,
    `changed_by`  INT DEFAULT NULL,
    `change_note` VARCHAR(255) DEFAULT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`product_id`, `created_at`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Bảng: push_subscriptions — Đăng ký nhận thông báo push
-- ============================================================
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT DEFAULT NULL,
    `endpoint`      TEXT NOT NULL,
    `auth_key`      VARCHAR(255) DEFAULT NULL,
    `p256dh_key`    VARCHAR(255) DEFAULT NULL,
    `user_agent`    VARCHAR(500) DEFAULT NULL,
    `is_active`     TINYINT(1) DEFAULT 1,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`user_id`),
    UNIQUE KEY `uniq_endpoint` (`endpoint`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Bảng: VAPID keys cho Web Push
-- ============================================================
INSERT IGNORE INTO `shop_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('vapid_public_key',  'BICWlwY8Yv0kXm7h9fKqR0n3d5s7t9v1x3z5b7n9m1q3w5e7r8t9y0u2i4o6p8', 'push'),
('vapid_private_key', 'ABC123def456GHI789jkl012MNO345pqr678STU901vwx234YZA567', 'push'),
('vapid_subject',     'mailto:contact@banlinh.vn', 'push');

-- ============================================================
-- Cột mới cho shipping_orders — carrier_status + timeline
-- ============================================================
ALTER TABLE `shipping_orders` 
  ADD COLUMN IF NOT EXISTS `carrier_status` VARCHAR(100) DEFAULT NULL AFTER `tracking_code`,
  ADD COLUMN IF NOT EXISTS `carrier_status_text` VARCHAR(500) DEFAULT NULL AFTER `carrier_status`,
  ADD COLUMN IF NOT EXISTS `carrier_status_updated_at` DATETIME DEFAULT NULL AFTER `carrier_status_text`,
  ADD COLUMN IF NOT EXISTS `shipping_address` TEXT DEFAULT NULL AFTER `delivery_address`,
  ADD COLUMN IF NOT EXISTS `shipping_name` VARCHAR(200) DEFAULT NULL AFTER `shipping_address`,
  ADD COLUMN IF NOT EXISTS `shipping_phone` VARCHAR(20) DEFAULT NULL AFTER `shipping_name`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','picked_up','in_transit','delivered','failed','returned') DEFAULT 'pending' AFTER `carrier_status_updated_at`;

-- ============================================================
-- Bảng: shipping_tracking_events — Timeline vận chuyển
-- ============================================================
CREATE TABLE IF NOT EXISTS `shipping_tracking_events` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `shipping_order_id` INT NOT NULL,
    `order_id`      INT NOT NULL,
    `status`        VARCHAR(50) NOT NULL,
    `location`      VARCHAR(255) DEFAULT NULL,
    `description`   TEXT DEFAULT NULL,
    `event_date`    DATETIME NOT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`shipping_order_id`),
    INDEX (`order_id`),
    FOREIGN KEY (`shipping_order_id`) REFERENCES `shipping_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Thêm cột notifications table cho push
-- ============================================================
ALTER TABLE `notifications` 
  ADD COLUMN IF NOT EXISTS `is_push_sent` TINYINT(1) DEFAULT 0 AFTER `is_read`;

-- ============================================================
-- Thêm cột products cho featured và sale scheduling
-- ============================================================
ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) DEFAULT 0 AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `sale_start` DATETIME DEFAULT NULL AFTER `discount_percent`,
  ADD COLUMN IF NOT EXISTS `sale_end` DATETIME DEFAULT NULL AFTER `sale_start`;

-- ============================================================
-- Tạo bảng shipping_carriers (đơn vị vận chuyển)
-- ============================================================
CREATE TABLE IF NOT EXISTS `shipping_carriers` (
    `id`          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(50) NOT NULL UNIQUE,
    `name`        VARCHAR(200) NOT NULL,
    `logo`        VARCHAR(255) DEFAULT NULL,
    `tracking_url` VARCHAR(500) DEFAULT NULL,
    `api_key`     VARCHAR(255) DEFAULT NULL,
    `api_secret`  VARCHAR(255) DEFAULT NULL,
    `is_active`   TINYINT(1) DEFAULT 1,
    `sort_order`  INT DEFAULT 0,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed carriers
INSERT IGNORE INTO `shipping_carriers` (`code`, `name`, `tracking_url`, `sort_order`) VALUES
('GHN',    'Giao Hàng Nhanh',    'https://donhang.ghn.vn/?order_code=', 1),
('GHTK',   'Giao Hàng Tiết Kiệm','https://i.ghtk.vn/', 2),
('VNPOST', 'Vietnam Post',       'https://www.vnpost.vn/en-us/dinh-vi/buu-pham?ms=', 3),
('JT',     'J&T Express',        'https://jtexpress.vn/vi/tracking?bills=', 4),
('NINJA',  'Ninja Van',          'https://www.ninjavan.co/vi-vn/tracking?id=', 5);
