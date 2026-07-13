-- ============================================================
-- MIGRATION: Bổ sung 16 chức năng còn thiếu
-- Ngày: 2026-06-22
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ============================================================
-- 1. SHOP SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `shop_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `shop_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('shop_name', 'Ban Linh Kiện', 'general'),
('shop_hotline', '0909 000 000', 'general'),
('shop_email', 'contact@banlinh.vn', 'general'),
('shop_address', '123 Đường ABC, TP.HCM', 'general'),
('shop_logo', '', 'general'),
('shop_facebook', '', 'social'),
('shop_zalo', '', 'social'),
('shop_youtube', '', 'social'),
('policy_return', '7 ngày đổi trả', 'policy'),
('policy_warranty', '12 tháng bảo hành', 'policy'),
('policy_shipping', 'Miễn phí ship đơn từ 500.000đ', 'policy'),
('free_shipping_min', '500000', 'shipping'),
('default_shipping_fee', '50000', 'shipping'),
('meta_title_home', 'Ban Linh Kiện - Linh kiện máy tính chính hãng', 'seo'),
('meta_description_home', 'Cửa hàng linh kiện máy tính, laptop, gaming gear chính hãng giá tốt nhất', 'seo');

-- ============================================================
-- 2. USER ADDRESSES (Sổ địa chỉ giao hàng)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `province` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `ward` VARCHAR(100) DEFAULT '',
    `address_detail` TEXT NOT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. SHIPPING ZONES (Phí vận chuyển)
-- ============================================================
CREATE TABLE IF NOT EXISTS `shipping_zones` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `zone_name` VARCHAR(100) NOT NULL,
    `provinces` TEXT COMMENT 'JSON array of province names',
    `base_fee` DECIMAL(12,0) DEFAULT 50000,
    `free_shipping_min` DECIMAL(12,0) DEFAULT 0 COMMENT '0 = no free shipping',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `shipping_zones` (`zone_name`, `provinces`, `base_fee`, `free_shipping_min`) VALUES
('Nội thành TP.HCM', '["Hồ Chí Minh"]', 25000, 300000),
('Nội thành Hà Nội', '["Hà Nội"]', 25000, 300000),
('Các tỉnh thành khác', '[]', 50000, 500000);

-- ============================================================
-- 4. PASSWORD RESETS (Quên mật khẩu)
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`email`),
    INDEX (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. RETURN REQUESTS (Đổi trả / Bảo hành)
-- ============================================================
CREATE TABLE IF NOT EXISTS `return_requests` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `order_item_id` INT DEFAULT NULL,
    `type` ENUM('return','exchange','warranty') DEFAULT 'return' COMMENT 'return=hoàn tiền, exchange=đổi hàng, warranty=bảo hành',
    `reason` TEXT NOT NULL,
    `images` TEXT COMMENT 'JSON array of image filenames',
    `status` ENUM('pending','approved','rejected','processing','completed') DEFAULT 'pending',
    `admin_note` TEXT,
    `refund_amount` DECIMAL(12,0) DEFAULT 0,
    `resolved_by` INT DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. CMS PAGES (Tin tức, Giới thiệu, Liên hệ, Chính sách)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cms_pages` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255) DEFAULT '',
    `meta_description` TEXT,
    `meta_keywords` VARCHAR(500) DEFAULT '',
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `cms_pages` (`slug`, `title`, `content`, `meta_title`, `meta_description`) VALUES
('gioithieu', 'Giới Thiệu', '<h2>Về chúng tôi</h2><p>Ban Linh Kiện là cửa hàng chuyên cung cấp linh kiện máy tính chính hãng...</p>', 'Giới thiệu - Ban Linh Kiện', 'Tìm hiểu về Ban Linh Kiện - cửa hàng linh kiện máy tính uy tín'),
('lienhe', 'Liên Hệ', '<h2>Liên hệ với chúng tôi</h2><p>Hotline: 0909 000 000</p>', 'Liên hệ - Ban Linh Kiện', 'Thông tin liên hệ cửa hàng Ban Linh Kiện'),
('chinh-sach-bao-mat', 'Chính Sách Bảo Mật', '<h2>Chính sách bảo mật</h2><p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn...</p>', 'Chính sách bảo mật - Ban Linh Kiện', 'Chính sách bảo mật thông tin khách hàng'),
('chinh-sach-doi-tra', 'Chính Sách Đổi Trả', '<h2>Chính sách đổi trả</h2><p>Chúng tôi chấp nhận đổi trả trong vòng 7 ngày...</p>', 'Chính sách đổi trả - Ban Linh Kiện', 'Chính sách đổi trả hàng hóa');

CREATE TABLE IF NOT EXISTS `news_articles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `thumbnail` VARCHAR(255) DEFAULT '',
    `summary` TEXT,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255) DEFAULT '',
    `meta_description` TEXT,
    `is_published` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. ROLES & PERMISSIONS (Phân quyền Admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `permissions` JSON COMMENT 'JSON object of permission keys',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `roles` (`name`, `display_name`, `description`, `permissions`) VALUES
('super_admin', 'Super Admin', 'Toàn quyền quản trị', '{"all": true}'),
('warehouse', 'Nhân viên kho', 'Quản lý kho hàng và đơn hàng', '{"inventory": true, "orders": true, "products": true}'),
('cskh', 'Chăm sóc khách hàng', 'Xử lý đơn hàng, chat, đổi trả', '{"orders": true, "chat": true, "returns": true, "users": true}');

-- Thêm cột role_id vào users nếu chưa có
DROP PROCEDURE IF EXISTS add_col_role_id;
CREATE PROCEDURE add_col_role_id()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role_id') THEN
    ALTER TABLE `users` ADD COLUMN `role_id` INT DEFAULT NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role') THEN
    ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) DEFAULT 'customer';
  END IF;
END;
CALL add_col_role_id();
DROP PROCEDURE IF EXISTS add_col_role_id;

-- ============================================================
-- 8. AUDIT LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'create, update, delete, login, etc.',
    `module` VARCHAR(100) NOT NULL COMMENT 'products, orders, users, etc.',
    `target_id` INT DEFAULT NULL,
    `target_name` VARCHAR(255) DEFAULT NULL,
    `old_data` JSON DEFAULT NULL,
    `new_data` JSON DEFAULT NULL,
    `ip_address` VARCHAR(50) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`user_id`),
    INDEX (`module`),
    INDEX (`action`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 9. WISHLISTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_product` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 10. SUPPLIERS (Nhà cung cấp)
-- ============================================================
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `code` VARCHAR(50) UNIQUE,
    `contact_person` VARCHAR(150) DEFAULT '',
    `phone` VARCHAR(30) DEFAULT '',
    `email` VARCHAR(255) DEFAULT '',
    `address` TEXT,
    `website` VARCHAR(255) DEFAULT '',
    `tax_code` VARCHAR(50) DEFAULT '',
    `bank_account` VARCHAR(100) DEFAULT '',
    `bank_name` VARCHAR(100) DEFAULT '',
    `note` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Liên kết inventory_logs với supplier
DROP PROCEDURE IF EXISTS add_col_supplier_id;
CREATE PROCEDURE add_col_supplier_id()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='inventory_logs' AND COLUMN_NAME='supplier_id') THEN
    ALTER TABLE `inventory_logs` ADD COLUMN `supplier_id` INT DEFAULT NULL;
  END IF;
END;
CALL add_col_supplier_id();
DROP PROCEDURE IF EXISTS add_col_supplier_id;

-- ============================================================
-- 11. META TAGS (SEO) cho products
-- ============================================================
DROP PROCEDURE IF EXISTS add_col_seo;
CREATE PROCEDURE add_col_seo()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='meta_title') THEN
    ALTER TABLE `products` ADD COLUMN `meta_title` VARCHAR(255) DEFAULT '';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='meta_description') THEN
    ALTER TABLE `products` ADD COLUMN `meta_description` TEXT;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='meta_keywords') THEN
    ALTER TABLE `products` ADD COLUMN `meta_keywords` VARCHAR(500) DEFAULT '';
  END IF;
END;
CALL add_col_seo();
DROP PROCEDURE IF EXISTS add_col_seo;

-- ============================================================
-- 12. PAYMENT TRANSACTIONS (Cổng thanh toán online)
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `gateway` ENUM('vnpay','momo','bank_transfer','cod') NOT NULL,
    `transaction_code` VARCHAR(100) DEFAULT NULL,
    `amount` DECIMAL(12,0) NOT NULL,
    `status` ENUM('pending','success','failed','cancelled','refunded') DEFAULT 'pending',
    `gateway_response` JSON DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    INDEX (`order_id`),
    INDEX (`status`),
    INDEX (`gateway`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm cột payment vào orders nếu chưa có
DROP PROCEDURE IF EXISTS add_col_orders;
CREATE PROCEDURE add_col_orders()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='payment_status') THEN
    ALTER TABLE `orders` ADD COLUMN `payment_status` ENUM('unpaid','paid','refunded') DEFAULT 'unpaid';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='payment_method') THEN
    ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(50) DEFAULT 'cod';
  END IF;
END;
CALL add_col_orders();
DROP PROCEDURE IF EXISTS add_col_orders;

-- ============================================================
-- 13. SYSTEM NOTIFICATIONS BROADCAST (Admin gửi thông báo)
-- ============================================================
CREATE TABLE IF NOT EXISTS `broadcast_notifications` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info','warning','promo','system') DEFAULT 'info',
    `target` ENUM('all','registered','specific') DEFAULT 'all',
    `target_user_ids` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đánh dấu user đã đọc broadcast
CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `notification_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `notif_user` (`notification_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 14. ORDER TRACKING TOKEN (Theo dõi công khai)
-- Dùng order_code thay vì login để tra cứu
-- ============================================================
DROP PROCEDURE IF EXISTS add_col_tracking;
CREATE PROCEDURE add_col_tracking()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='tracking_code') THEN
    ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(20) DEFAULT NULL;
  END IF;
END;
CALL add_col_tracking();
DROP PROCEDURE IF EXISTS add_col_tracking;

-- Tạo tracking_code cho orders hiện có
UPDATE `orders` SET `tracking_code` = CONCAT('ORD', LPAD(id, 6, '0')) WHERE `tracking_code` IS NULL;

-- ============================================================
-- 15. EMAIL QUEUE (Hàng đợi gửi email)
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `to_email` VARCHAR(255) NOT NULL,
    `to_name` VARCHAR(200) DEFAULT '',
    `subject` VARCHAR(500) NOT NULL,
    `body` LONGTEXT NOT NULL,
    `status` ENUM('pending','sent','failed') DEFAULT 'pending',
    `attempts` TINYINT DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `scheduled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `sent_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
