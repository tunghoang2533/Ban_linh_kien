<?php
/**
 * Patch Migration - Thêm các cột còn thiếu cho products, users và bảng mới
 * Chạy 1 lần tại: http://localhost/Ban_linh_kien/patch_migration.php
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = (new Database())->connect();
$results = [];

function patch($db, $label, callable $fn) {
    global $results;
    try {
        $fn($db);
        $results[] = ['ok' => true, 'msg' => $label];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        $skip = strpos($msg, 'Duplicate') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Multiple') !== false;
        $results[] = ['ok' => $skip, 'msg' => $label . ($skip ? ' (đã tồn tại, bỏ qua)' : ': ' . $msg)];
    }
}

function addCol($db, $table, $col, $def) {
    $exists = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='$col'")->fetchColumn();
    if (!$exists) $db->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
}

// === Cột bổ sung cho products ===
patch($db, 'products.meta_title', fn($d) => addCol($d, 'products', 'meta_title', "VARCHAR(150) DEFAULT NULL"));
patch($db, 'products.meta_description', fn($d) => addCol($d, 'products', 'meta_description', "VARCHAR(300) DEFAULT NULL"));
patch($db, 'products.meta_keywords', fn($d) => addCol($d, 'products', 'meta_keywords', "VARCHAR(200) DEFAULT NULL"));
patch($db, 'products.supplier_id', fn($d) => addCol($d, 'products', 'supplier_id', "INT DEFAULT NULL"));
patch($db, 'products.cost_price', fn($d) => addCol($d, 'products', 'cost_price', "DECIMAL(15,2) DEFAULT NULL COMMENT 'Giá nhập'"));
patch($db, 'products.min_stock', fn($d) => addCol($d, 'products', 'min_stock', "INT DEFAULT 0 COMMENT 'Ngưỡng cảnh báo tồn kho thấp'"));
patch($db, 'products.weight', fn($d) => addCol($d, 'products', 'weight', "DECIMAL(8,2) DEFAULT NULL COMMENT 'Cân nặng (kg)'"));
patch($db, 'products.warranty_months', fn($d) => addCol($d, 'products', 'warranty_months', "INT DEFAULT 12 COMMENT 'Tháng bảo hành'"));

// === Cột bổ sung cho users ===
patch($db, 'users.role_id', fn($d) => addCol($d, 'users', 'role_id', "INT DEFAULT NULL"));
patch($db, 'users.is_blocked', fn($d) => addCol($d, 'users', 'is_blocked', "TINYINT(1) DEFAULT 0"));
patch($db, 'users.blocked_reason', fn($d) => addCol($d, 'users', 'blocked_reason', "VARCHAR(255) DEFAULT NULL"));
patch($db, 'users.phone', fn($d) => addCol($d, 'users', 'phone', "VARCHAR(20) DEFAULT NULL"));
patch($db, 'users.avatar', fn($d) => addCol($d, 'users', 'avatar', "VARCHAR(255) DEFAULT NULL"));

// === Cột bổ sung cho orders ===
patch($db, 'orders.tracking_code', fn($d) => addCol($d, 'orders', 'tracking_code', "VARCHAR(50) DEFAULT NULL UNIQUE"));
patch($db, 'orders.shipping_fee', fn($d) => addCol($d, 'orders', 'shipping_fee', "DECIMAL(15,2) DEFAULT 0"));
patch($db, 'orders.discount_amount', fn($d) => addCol($d, 'orders', 'discount_amount', "DECIMAL(15,2) DEFAULT 0"));
patch($db, 'orders.payment_method', fn($d) => addCol($d, 'orders', 'payment_method', "VARCHAR(30) DEFAULT 'cod'"));
patch($db, 'orders.payment_status', fn($d) => addCol($d, 'orders', 'payment_status', "ENUM('pending','paid','failed','refunded') DEFAULT 'pending'"));
patch($db, 'orders.customer_phone', fn($d) => addCol($d, 'orders', 'customer_phone', "VARCHAR(20) DEFAULT NULL"));
patch($db, 'orders.customer_name', fn($d) => addCol($d, 'orders', 'customer_name', "VARCHAR(100) DEFAULT NULL"));
patch($db, 'orders.customer_address', fn($d) => addCol($d, 'orders', 'customer_address', "VARCHAR(255) DEFAULT NULL"));
patch($db, 'orders.customer_email', fn($d) => addCol($d, 'orders', 'customer_email', "VARCHAR(100) DEFAULT NULL"));
patch($db, 'orders.voucher_code', fn($d) => addCol($d, 'orders', 'voucher_code', "VARCHAR(50) DEFAULT NULL"));
patch($db, 'orders.note', fn($d) => addCol($d, 'orders', 'note', "TEXT DEFAULT NULL"));

// === Tạo tracking code cho orders cũ ===
patch($db, 'Generate tracking codes for existing orders', function($d) {
    $orders = $d->query("SELECT id FROM orders WHERE tracking_code IS NULL OR tracking_code=''")->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $d->prepare("UPDATE orders SET tracking_code=? WHERE id=?");
    foreach ($orders as $oid) {
        $code = 'ORD' . str_pad($oid, 6, '0', STR_PAD_LEFT);
        $stmt->execute([$code, $oid]);
    }
});

// === Bảng mới: wishlists ===
patch($db, 'CREATE TABLE wishlists', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_product` (`user_id`, `product_id`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: password_resets ===
patch($db, 'CREATE TABLE password_resets', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `token` VARCHAR(100) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token` (`token`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: user_addresses ===
patch($db, 'CREATE TABLE user_addresses', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `province` VARCHAR(80) NOT NULL,
    `district` VARCHAR(80) NOT NULL,
    `ward` VARCHAR(80) DEFAULT '',
    `address_detail` VARCHAR(255) NOT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: return_requests ===
patch($db, 'CREATE TABLE return_requests', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `return_requests` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `type` ENUM('return','exchange','warranty') DEFAULT 'return',
    `reason` TEXT NOT NULL,
    `images` JSON DEFAULT NULL,
    `status` ENUM('pending','approved','processing','completed','rejected') DEFAULT 'pending',
    `admin_note` TEXT DEFAULT NULL,
    `refund_amount` DECIMAL(15,2) DEFAULT 0,
    `resolved_by` INT DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: suppliers ===
patch($db, 'CREATE TABLE suppliers', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `code` VARCHAR(30) DEFAULT NULL UNIQUE,
    `contact_person` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `website` VARCHAR(200) DEFAULT NULL,
    `tax_code` VARCHAR(50) DEFAULT NULL,
    `bank_account` VARCHAR(50) DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: shipping_zones ===
patch($db, 'CREATE TABLE shipping_zones', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `shipping_zones` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `zone_name` VARCHAR(100) NOT NULL,
    `provinces` JSON DEFAULT NULL COMMENT 'Mảng tỉnh/thành áp dụng. Null = tất cả',
    `base_fee` DECIMAL(15,2) DEFAULT 50000,
    `free_shipping_min` DECIMAL(15,2) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// Seed 1 zone mặc định
patch($db, 'Seed default shipping zone', function($d) {
    $cnt = $d->query("SELECT COUNT(*) FROM shipping_zones")->fetchColumn();
    if ($cnt == 0) {
        $d->exec("INSERT INTO shipping_zones (zone_name, provinces, base_fee, free_shipping_min) VALUES ('Toàn quốc', '[]', 50000, 500000)");
    }
});

// === Bảng mới: shop_settings ===
patch($db, 'CREATE TABLE shop_settings', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `shop_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// Seed default settings
patch($db, 'Seed default shop settings', function($d) {
    $defaults = [
        'shop_name'          => 'Ban Linh Kiện',
        'shop_hotline'       => '0909 000 000',
        'shop_email'         => 'contact@banlinh.vn',
        'shop_address'       => '123 Nguyễn Văn A, Quận 1, TP.HCM',
        'default_shipping_fee' => '50000',
        'free_shipping_min'  => '500000',
        'meta_title_home'    => 'Ban Linh Kiện - Linh kiện máy tính chính hãng giá tốt',
        'meta_description_home' => 'Mua linh kiện máy tính chính hãng tại Ban Linh Kiện. CPU, GPU, RAM, SSD, Mainboard với giá tốt nhất, bảo hành uy tín.',
    ];
    $stmt = $d->prepare("INSERT IGNORE INTO shop_settings (setting_key, setting_value) VALUES (?,?)");
    foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);
});

// === Bảng mới: audit_logs ===
patch($db, 'CREATE TABLE audit_logs', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `module` VARCHAR(100) DEFAULT NULL,
    `target_id` INT DEFAULT NULL,
    `target_name` VARCHAR(200) DEFAULT NULL,
    `old_data` JSON DEFAULT NULL,
    `new_data` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_module` (`module`),
    INDEX `idx_action` (`action`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: roles ===
patch($db, 'CREATE TABLE roles', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `permissions` JSON DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

patch($db, 'Seed default roles', function($d) {
    $cnt = $d->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($cnt == 0) {
        $superPerms = json_encode(['all' => true]);
        $warehousePerms = json_encode(['products'=>true,'inventory'=>true,'suppliers'=>true,'orders'=>true]);
        $cskh = json_encode(['orders'=>true,'chat'=>true,'returns'=>true,'users'=>true]);
        $d->exec("INSERT INTO roles (name, display_name, description, permissions) VALUES
            ('super_admin','Super Admin','Toàn quyền hệ thống','$superPerms'),
            ('warehouse','Thủ kho','Quản lý kho và sản phẩm','$warehousePerms'),
            ('cskh','CSKH','Chăm sóc khách hàng','$cskh')
        ");
    }
});

// === Bảng mới: broadcast_notifications ===
patch($db, 'CREATE TABLE broadcast_notifications', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `broadcast_notifications` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info','warning','promo','system') DEFAULT 'info',
    `target` ENUM('all','registered','specific') DEFAULT 'all',
    `target_user_ids` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `expires_at` DATETIME DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

patch($db, 'CREATE TABLE notification_reads', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `notification_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_notif_user` (`notification_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// === Bảng mới: cms_pages ===
patch($db, 'CREATE TABLE cms_pages', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `cms_pages` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `content` LONGTEXT DEFAULT NULL,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

patch($db, 'Seed default CMS pages', function($d) {
    $cnt = $d->query("SELECT COUNT(*) FROM cms_pages")->fetchColumn();
    if ($cnt == 0) {
        $d->exec("INSERT INTO cms_pages (title, slug, content, sort_order) VALUES
            ('Về chúng tôi','ve-chung-toi','<h2>Về Ban Linh Kiện</h2><p>Chúng tôi là cửa hàng linh kiện máy tính uy tín...</p>',1),
            ('Chính sách bảo hành','chinh-sach-bao-hanh','<h2>Chính sách bảo hành</h2><p>Tất cả sản phẩm được bảo hành theo tiêu chuẩn nhà sản xuất...</p>',2),
            ('Chính sách đổi trả','chinh-sach-doi-tra','<h2>Chính sách đổi trả</h2><p>Đổi trả trong vòng 30 ngày kể từ ngày mua...</p>',3),
            ('Liên hệ','lien-he','<h2>Liên hệ với chúng tôi</h2><p>Hotline: 0909 000 000</p>',4)
        ");
    }
});

// === Bảng mới: cms_articles ===
patch($db, 'CREATE TABLE cms_articles', fn($d) => $d->exec("
CREATE TABLE IF NOT EXISTS `cms_articles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(300) NOT NULL,
    `slug` VARCHAR(300) NOT NULL UNIQUE,
    `excerpt` TEXT DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `tags` VARCHAR(500) DEFAULT NULL,
    `author_id` INT DEFAULT NULL,
    `status` ENUM('draft','published','archived') DEFAULT 'draft',
    `views` INT DEFAULT 0,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
"));

// =============== RENDER OUTPUT ===============
echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Patch Migration</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;margin:0;padding:30px;}
.wrap{max-width:820px;margin:0 auto;}
h1{color:#6366f1;margin-bottom:24px;}
.item{padding:10px 16px;border-radius:8px;margin:6px 0;display:flex;align-items:center;gap:10px;font-size:14px;}
.ok{background:#0d2d17;color:#4ade80;}
.fail{background:#2d0d0d;color:#f87171;}
.summary{margin-top:24px;padding:20px;border-radius:12px;background:#1e293b;}
a{color:#6366f1;text-decoration:none;}
</style></head><body><div class='wrap'>
<h1>⚡ Patch Migration – Bổ sung cột & Bảng mới</h1>";

$ok = 0; $fail = 0;
foreach ($results as $r) {
    $cls = $r['ok'] ? 'ok' : 'fail';
    $icon = $r['ok'] ? '✅' : '❌';
    echo "<div class='item $cls'>$icon " . htmlspecialchars($r['msg']) . "</div>";
    $r['ok'] ? $ok++ : $fail++;
}

echo "<div class='summary'>";
echo "<p style='margin:0 0 8px;font-size:16px;font-weight:700;'>📊 Kết quả: <span style='color:#4ade80;'>$ok thành công</span> · <span style='color:#f87171;'>$fail lỗi</span></p>";
if ($fail === 0) {
    echo "<p style='margin:0;color:#4ade80;font-size:14px;'>🎉 Tất cả migration hoàn tất! Hệ thống đã sẵn sàng.</p>";
    echo "<p style='margin:8px 0 0;'><a href='admin/'>→ Vào Admin Dashboard</a></p>";
} else {
    echo "<p style='margin:0;color:#f87171;font-size:14px;'>Có $fail lỗi. Kiểm tra và chạy lại nếu cần.</p>";
}
echo "</div></div></body></html>";
