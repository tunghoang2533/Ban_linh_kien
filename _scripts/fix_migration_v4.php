<?php
// Fix missing columns from migration v4
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$alterStatements = [
    "ALTER TABLE `shipping_orders` ADD COLUMN `carrier_status` VARCHAR(100) DEFAULT NULL AFTER `tracking_code`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `carrier_status_text` VARCHAR(500) DEFAULT NULL AFTER `carrier_status`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `carrier_status_updated_at` DATETIME DEFAULT NULL AFTER `carrier_status_text`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `status` ENUM('pending','picked_up','in_transit','delivered','failed','returned') DEFAULT 'pending' AFTER `carrier_status_updated_at`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `shipping_name` VARCHAR(200) DEFAULT NULL AFTER `delivery_address`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `shipping_phone` VARCHAR(20) DEFAULT NULL AFTER `shipping_name`",
    "ALTER TABLE `shipping_orders` ADD COLUMN `shipping_address` TEXT DEFAULT NULL AFTER `shipping_phone`",
    "ALTER TABLE `notifications` ADD COLUMN `is_push_sent` TINYINT(1) DEFAULT 0 AFTER `is_read`",
    "ALTER TABLE `products` ADD COLUMN `is_featured` TINYINT(1) DEFAULT 0 AFTER `is_active`",
    "ALTER TABLE `products` ADD COLUMN `sale_start` DATETIME DEFAULT NULL AFTER `discount_percent`",
    "ALTER TABLE `products` ADD COLUMN `sale_end` DATETIME DEFAULT NULL AFTER `sale_start`",
];

echo "<pre>Fixing missing columns...\n\n";

foreach ($alterStatements as $sql) {
    // Extract column name
    preg_match('/ADD COLUMN `(\w+)`/', $sql, $m);
    $colName = $m[1] ?? 'unknown';
    preg_match('/TABLE `(\w+)`/', $sql, $m2);
    $tableName = $m2[1] ?? 'unknown';
    
    // Check if column already exists
    try {
        $check = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$tableName' AND COLUMN_NAME='$colName'")->fetchColumn();
        if (!$check) {
            $db->exec($sql);
            echo "✅ Added column `$tableName`.`$colName`\n";
        } else {
            echo "⏭️ Column `$tableName`.`$colName` already exists\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error adding `$tableName`.`$colName`: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n</pre>";