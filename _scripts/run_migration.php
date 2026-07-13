<?php
// Migration script - run once
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

$errors = [];
$success = [];

function runSQL($db, $sql, $label) {
    global $errors, $success;
    try {
        $db->exec($sql);
        $success[] = "✅ $label";
    } catch (PDOException $e) {
        // Ignore "already exists" errors
        if (strpos($e->getMessage(), 'Duplicate') !== false || 
            strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Multiple') !== false) {
            $success[] = "⚠️ $label (already exists, skipped)";
        } else {
            $errors[] = "❌ $label: " . $e->getMessage();
        }
    }
}

function addColumnIfNotExists($db, $table, $column, $definition) {
    global $errors, $success;
    try {
        $check = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='$column'")->fetchColumn();
        if (!$check) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            $success[] = "✅ Added column $table.$column";
        } else {
            $success[] = "⚠️ Column $table.$column already exists";
        }
    } catch (PDOException $e) {
        $errors[] = "❌ Add column $table.$column: " . $e->getMessage();
    }
}

echo "<pre style='font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;'>";
echo "=== MIGRATION: 16 Tính Năng Mới ===\n\n";

// 1. SHOP SETTINGS
runSQL($db, "CREATE TABLE IF NOT EXISTS `shop_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create shop_settings table");

$settings = [
    ['shop_name', 'Ban Linh Kiện', 'general'],
    ['shop_hotline', '0909 000 000', 'general'],
    ['shop_email', 'contact@banlinh.vn', 'general'],
    ['shop_address', '123 Đường ABC, TP.HCM', 'general'],
    ['shop_logo', '', 'general'],
    ['shop_facebook', '', 'social'],
    ['shop_zalo', '', 'social'],
    ['shop_youtube', '', 'social'],
    ['policy_return', '7 ngày đổi trả', 'policy'],
    ['policy_warranty', '12 tháng bảo hành', 'policy'],
    ['policy_shipping', 'Miễn phí ship đơn từ 500.000đ', 'policy'],
    ['free_shipping_min', '500000', 'shipping'],
    ['default_shipping_fee', '50000', 'shipping'],
    ['meta_title_home', 'Ban Linh Kiện - Linh kiện máy tính chính hãng', 'seo'],
    ['meta_description_home', 'Cửa hàng linh kiện máy tính, laptop, gaming gear chính hãng giá tốt nhất', 'seo'],
];
$stmt = $db->prepare("INSERT IGNORE INTO `shop_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES (?,?,?)");
foreach ($settings as $s) { $stmt->execute($s); }
$success[] = "✅ Seeded shop_settings";

// 2. USER ADDRESSES
runSQL($db, "CREATE TABLE IF NOT EXISTS `user_addresses` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create user_addresses table");

// 3. SHIPPING ZONES
runSQL($db, "CREATE TABLE IF NOT EXISTS `shipping_zones` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `zone_name` VARCHAR(100) NOT NULL,
    `provinces` TEXT,
    `base_fee` DECIMAL(12,0) DEFAULT 50000,
    `free_shipping_min` DECIMAL(12,0) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create shipping_zones table");

$db->exec("INSERT IGNORE INTO `shipping_zones` (`zone_name`, `provinces`, `base_fee`, `free_shipping_min`) VALUES
    ('Nội thành TP.HCM', '[\"Hồ Chí Minh\"]', 25000, 300000),
    ('Nội thành Hà Nội', '[\"Hà Nội\"]', 25000, 300000),
    ('Các tỉnh thành khác', '[]', 50000, 500000)");
$success[] = "✅ Seeded shipping_zones";

// 4. PASSWORD RESETS
runSQL($db, "CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`email`),
    INDEX (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create password_resets table");

// 5. RETURN REQUESTS
runSQL($db, "CREATE TABLE IF NOT EXISTS `return_requests` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `order_item_id` INT DEFAULT NULL,
    `type` ENUM('return','exchange','warranty') DEFAULT 'return',
    `reason` TEXT NOT NULL,
    `images` TEXT,
    `status` ENUM('pending','approved','rejected','processing','completed') DEFAULT 'pending',
    `admin_note` TEXT,
    `refund_amount` DECIMAL(12,0) DEFAULT 0,
    `resolved_by` INT DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create return_requests table");

// 6. CMS PAGES
runSQL($db, "CREATE TABLE IF NOT EXISTS `cms_pages` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255) DEFAULT '',
    `meta_description` TEXT,
    `meta_keywords` VARCHAR(500) DEFAULT '',
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create cms_pages table");

$cmsStmt = $db->prepare("INSERT IGNORE INTO `cms_pages` (`slug`, `title`, `content`, `meta_title`, `meta_description`) VALUES (?,?,?,?,?)");
$cmsStmt->execute(['gioithieu', 'Giới Thiệu', '<h2>Về chúng tôi</h2><p>Ban Linh Kiện là cửa hàng chuyên cung cấp linh kiện máy tính chính hãng...</p>', 'Giới thiệu - Ban Linh Kiện', 'Tìm hiểu về Ban Linh Kiện']);
$cmsStmt->execute(['lienhe', 'Liên Hệ', '<h2>Liên hệ với chúng tôi</h2><p>Hotline: 0909 000 000</p>', 'Liên hệ - Ban Linh Kiện', 'Thông tin liên hệ cửa hàng']);
$cmsStmt->execute(['chinh-sach-bao-mat', 'Chính Sách Bảo Mật', '<h2>Chính sách bảo mật</h2><p>Chúng tôi cam kết bảo vệ thông tin cá nhân...</p>', 'Chính sách bảo mật', 'Chính sách bảo mật thông tin']);
$cmsStmt->execute(['chinh-sach-doi-tra', 'Chính Sách Đổi Trả', '<h2>Chính sách đổi trả</h2><p>Chúng tôi chấp nhận đổi trả trong vòng 7 ngày...</p>', 'Chính sách đổi trả', 'Chính sách đổi trả hàng hóa']);
$success[] = "✅ Seeded cms_pages";

// 7. NEWS ARTICLES
runSQL($db, "CREATE TABLE IF NOT EXISTS `news_articles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create news_articles table");

// 8. ROLES
runSQL($db, "CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `permissions` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create roles table");

$roleStmt = $db->prepare("INSERT IGNORE INTO `roles` (`name`, `display_name`, `description`, `permissions`) VALUES (?,?,?,?)");
$roleStmt->execute(['super_admin', 'Super Admin', 'Toàn quyền quản trị', '{"all":true}']);
$roleStmt->execute(['warehouse', 'Nhân viên kho', 'Quản lý kho hàng và đơn hàng', '{"inventory":true,"orders":true,"products":true}']);
$roleStmt->execute(['cskh', 'Chăm sóc khách hàng', 'Xử lý đơn hàng, chat, đổi trả', '{"orders":true,"chat":true,"returns":true,"users":true}']);
$success[] = "✅ Seeded roles";

addColumnIfNotExists($db, 'users', 'role_id', 'INT DEFAULT NULL');
addColumnIfNotExists($db, 'users', 'role', "VARCHAR(50) DEFAULT 'customer'");

// 9. AUDIT LOGS
runSQL($db, "CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `target_id` INT DEFAULT NULL,
    `target_name` VARCHAR(255) DEFAULT NULL,
    `old_data` TEXT DEFAULT NULL,
    `new_data` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(50) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`user_id`),
    INDEX (`module`),
    INDEX (`action`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create audit_logs table");

// 10. WISHLISTS
runSQL($db, "CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_product` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create wishlists table");

// 11. SUPPLIERS
runSQL($db, "CREATE TABLE IF NOT EXISTS `suppliers` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create suppliers table");

addColumnIfNotExists($db, 'inventory_logs', 'supplier_id', 'INT DEFAULT NULL');

// 12. SEO FIELDS FOR PRODUCTS
addColumnIfNotExists($db, 'products', 'meta_title', "VARCHAR(255) DEFAULT ''");
addColumnIfNotExists($db, 'products', 'meta_description', 'TEXT');
addColumnIfNotExists($db, 'products', 'meta_keywords', "VARCHAR(500) DEFAULT ''");

// 13. PAYMENT TRANSACTIONS
runSQL($db, "CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `gateway` ENUM('vnpay','momo','bank_transfer','cod') NOT NULL,
    `transaction_code` VARCHAR(100) DEFAULT NULL,
    `amount` DECIMAL(12,0) NOT NULL,
    `status` ENUM('pending','success','failed','cancelled','refunded') DEFAULT 'pending',
    `gateway_response` TEXT DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    INDEX (`order_id`),
    INDEX (`status`),
    INDEX (`gateway`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create payment_transactions table");

addColumnIfNotExists($db, 'orders', 'payment_status', "ENUM('unpaid','paid','refunded') DEFAULT 'unpaid'");
addColumnIfNotExists($db, 'orders', 'payment_method', "VARCHAR(50) DEFAULT 'cod'");

// 14. BROADCAST NOTIFICATIONS
runSQL($db, "CREATE TABLE IF NOT EXISTS `broadcast_notifications` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info','warning','promo','system') DEFAULT 'info',
    `target` ENUM('all','registered','specific') DEFAULT 'all',
    `target_user_ids` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create broadcast_notifications table");

runSQL($db, "CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `notification_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `notif_user` (`notification_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create notification_reads table");

// 15. ORDER TRACKING CODE
addColumnIfNotExists($db, 'orders', 'tracking_code', 'VARCHAR(20) DEFAULT NULL');

// Update existing orders with tracking code
$db->exec("UPDATE `orders` SET `tracking_code` = CONCAT('ORD', LPAD(id, 6, '0')) WHERE `tracking_code` IS NULL OR `tracking_code` = ''");
$success[] = "✅ Generated tracking codes for existing orders";

// 16. EMAIL QUEUE
runSQL($db, "CREATE TABLE IF NOT EXISTS `email_queue` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create email_queue table");

// ========================
echo "\n=== KẾT QUẢ ===\n\n";
foreach ($success as $s) { echo $s . "\n"; }
if ($errors) {
    echo "\n--- ERRORS ---\n";
    foreach ($errors as $e) { echo $e . "\n"; }
}
echo "\n\nHoàn thành! " . count($success) . " thành công, " . count($errors) . " lỗi.\n";
echo "</pre>";
?>
