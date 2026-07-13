<?php
/**
 * CLI Migration Runner — chạy trực tiếp qua php cli
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_ban_linh_kien');
define('DB_USER', 'root');
define('DB_PASS', '23122005');

try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8mb4");
} catch (Exception $e) {
    die("KONNECT ERROR: " . $e->getMessage() . "\n");
}

function run($db, $sql, $label) {
    try {
        $db->exec($sql);
        echo "  OK  $label\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Duplicate column') !== false ||
            strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "  --  $label (already exists)\n";
        } else {
            echo "  ERR $label: " . $e->getMessage() . "\n";
        }
    }
}

function col($db, $tbl, $col) {
    return (int)$db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$tbl' AND COLUMN_NAME='$col'")->fetchColumn() > 0;
}

echo "\n=== WAREHOUSE UPGRADE MIGRATION ===\n\n";

// 1. warehouses (already exists probably)
run($db, "CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT,
    `phone` VARCHAR(30) DEFAULT '',
    `manager` VARCHAR(100) DEFAULT '',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: warehouses");

run($db, "INSERT IGNORE INTO `warehouses` (`code`,`name`,`address`,`phone`,`manager`) VALUES
('HN','Kho H\u00e0 N\u1ed9i','123 \u0110\u01b0\u1eddng L\u00e1ng, \u0110\u1ed1ng \u0110a, H\u00e0 N\u1ed9i','024 0000 0001','Nguy\u1ec5n V\u0103n A'),
('HCM','Kho TP.HCM','456 \u0110i\u1ec7n Bi\u00ean Ph\u1ee7, B\u00ecnh Th\u1ea1nh, TP.HCM','028 0000 0002','Tr\u1ea7n Th\u1ecb B')", "Data: 2 warehouses");

// 2. products columns
if (!col($db,'products','warehouse_id')) {
    run($db, "ALTER TABLE `products` ADD COLUMN `warehouse_id` INT DEFAULT 1", "Column: products.warehouse_id");
} else { echo "  --  Column: products.warehouse_id (exists)\n"; }
if (!col($db,'products','bin_location')) {
    run($db, "ALTER TABLE `products` ADD COLUMN `bin_location` VARCHAR(50) DEFAULT NULL", "Column: products.bin_location");
} else { echo "  --  Column: products.bin_location (exists)\n"; }

// 3. warehouse_logs columns
foreach ([
    ['warehouse_id', "ALTER TABLE `warehouse_logs` ADD COLUMN `warehouse_id` INT DEFAULT 1"],
    ['receipt_id',   "ALTER TABLE `warehouse_logs` ADD COLUMN `receipt_id` INT DEFAULT NULL"],
    ['batch_no',     "ALTER TABLE `warehouse_logs` ADD COLUMN `batch_no` VARCHAR(50) DEFAULT NULL"],
    ['po_id',        "ALTER TABLE `warehouse_logs` ADD COLUMN `po_id` INT DEFAULT NULL"],
] as [$c, $sql]) {
    if (!col($db,'warehouse_logs',$c)) run($db, $sql, "Column: warehouse_logs.$c");
    else echo "  --  Column: warehouse_logs.$c (exists)\n";
}

// 4. warehouse_receipts
run($db, "CREATE TABLE IF NOT EXISTS `warehouse_receipts` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `receipt_code` VARCHAR(30) NOT NULL UNIQUE,
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `supplier_id` INT DEFAULT NULL,
    `type` ENUM('purchase','return','transfer','adjustment') DEFAULT 'purchase',
    `status` ENUM('draft','pending','approved','cancelled') DEFAULT 'draft',
    `total_qty` INT DEFAULT 0,
    `total_amount` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT,
    `po_id` INT DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `submitted_by` INT DEFAULT NULL,
    `submitted_at` DATETIME DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `cancelled_by` INT DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `cancel_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`status`), INDEX (`warehouse_id`), INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: warehouse_receipts");

// 5. warehouse_receipt_items
run($db, "CREATE TABLE IF NOT EXISTS `warehouse_receipt_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `receipt_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `unit_cost` DECIMAL(12,0) DEFAULT 0,
    `subtotal` DECIMAL(15,0) DEFAULT 0,
    `batch_no` VARCHAR(50) DEFAULT NULL,
    `bin_location` VARCHAR(50) DEFAULT NULL,
    `note` TEXT,
    FOREIGN KEY (`receipt_id`) REFERENCES `warehouse_receipts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: warehouse_receipt_items");

// 6. purchase_orders
run($db, "CREATE TABLE IF NOT EXISTS `purchase_orders` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_code` VARCHAR(30) NOT NULL UNIQUE,
    `supplier_id` INT NOT NULL,
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `status` ENUM('draft','pending','approved','ordered','received','cancelled') DEFAULT 'draft',
    `expected_date` DATE DEFAULT NULL,
    `total_qty` INT DEFAULT 0,
    `total_amount` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT,
    `created_by` INT DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `ordered_at` DATETIME DEFAULT NULL,
    `received_at` DATETIME DEFAULT NULL,
    `receipt_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`), INDEX (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: purchase_orders");

// 7. purchase_order_items
run($db, "CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_cost` DECIMAL(12,0) DEFAULT 0,
    `subtotal` DECIMAL(15,0) DEFAULT 0,
    `received_qty` INT DEFAULT 0,
    `note` TEXT,
    FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: purchase_order_items");

// 8. stocktake_sessions
run($db, "CREATE TABLE IF NOT EXISTS `stocktake_sessions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_code` VARCHAR(30) NOT NULL UNIQUE,
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `status` ENUM('open','counting','reviewing','closed','cancelled') DEFAULT 'open',
    `scope` TEXT,
    `total_products` INT DEFAULT 0,
    `counted_products` INT DEFAULT 0,
    `variance_plus` INT DEFAULT 0,
    `variance_minus` INT DEFAULT 0,
    `note` TEXT,
    `started_by` INT DEFAULT NULL,
    `closed_by` INT DEFAULT NULL,
    `started_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `closed_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`), INDEX (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: stocktake_sessions");

// 9. stocktake_items
run($db, "CREATE TABLE IF NOT EXISTS `stocktake_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `system_qty` INT NOT NULL DEFAULT 0,
    `counted_qty` INT DEFAULT NULL,
    `variance` INT DEFAULT NULL,
    `bin_location` VARCHAR(50) DEFAULT NULL,
    `note` TEXT,
    `counted_by` INT DEFAULT NULL,
    `counted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`session_id`) REFERENCES `stocktake_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    UNIQUE KEY `session_product` (`session_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Table: stocktake_items");

echo "\n=== DONE ===\n";
