<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_ban_linh_kien');
define('DB_USER', 'root');
define('DB_PASS', '23122005');

try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB OK\n";
    
    $tables = ['warehouses','warehouse_receipts','warehouse_receipt_items','purchase_orders','purchase_order_items','stocktake_sessions','stocktake_items'];
    foreach ($tables as $t) {
        $r = $db->query("SHOW TABLES LIKE '$t'");
        echo $t . ': ' . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    // Check columns in products
    $r = $db->query("SHOW COLUMNS FROM products LIKE 'warehouse_id'");
    echo "products.warehouse_id: " . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
    $r = $db->query("SHOW COLUMNS FROM products LIKE 'bin_location'");
    echo "products.bin_location: " . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
    
    // Check warehouse data
    $whs = $db->query("SELECT * FROM warehouses")->fetchAll(PDO::FETCH_ASSOC);
    echo "Warehouses count: " . count($whs) . "\n";
    foreach ($whs as $w) echo "  - [{$w['code']}] {$w['name']}\n";
    
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
