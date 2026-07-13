<?php
// Fix index order for idx_products_discount_active
// Change from (discount_percent, is_active) to (is_active, discount_percent)
// Because queries use: WHERE discount_percent > 0 AND is_active = 1
// Equality condition should come before range condition

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_ban_linh_kien');
define('DB_USER', 'root');
define('DB_PASS', '23122005');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database\n\n";
    
    // Check if index exists
    $check = $db->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products'
        AND INDEX_NAME = 'idx_products_discount_active'
    ")->fetchColumn();
    
    if ($check > 0) {
        echo "Dropping old idx_products_discount_active (discount_percent, is_active)...\n";
        $db->exec("ALTER TABLE `products` DROP INDEX `idx_products_discount_active`");
        echo "✅ Dropped successfully\n";
    }
    
    echo "Adding new idx_products_discount_active (is_active, discount_percent)...\n";
    $db->exec("ALTER TABLE `products` ADD INDEX `idx_products_discount_active` (`is_active`, `discount_percent`)");
    echo "✅ Added successfully\n";
    
    echo "\n🎉 Index order fixed! Now equality (is_active=1) is resolved first, then range (discount_percent>0) is scanned.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
