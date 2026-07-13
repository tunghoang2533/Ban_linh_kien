<?php
/**
 * run_optimize_indexes.php — Chạy migration tối ưu INDEX
 * 
 * Cách chạy:
 *   php _scripts/run_optimize_indexes.php
 */

// Kết nối trực tiếp (tránh config.php phụ thuộc $_SERVER trong CLI)
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_ban_linh_kien');
define('DB_USER', 'root');
define('DB_PASS', '23122005');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Lỗi kết nối DB: " . $e->getMessage() . "\n");
}

$totalAdded = 0;
$totalSkipped = 0;
$totalErrors = 0;

// Lấy danh sách index hiện có
$existingIndexes = [];
$stmt = $db->query("
    SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY TABLE_NAME, INDEX_NAME
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $key = $row['TABLE_NAME'] . '.' . $row['INDEX_NAME'];
    if (!isset($existingIndexes[$key])) {
        $existingIndexes[$key] = [];
    }
    $existingIndexes[$key][] = $row['COLUMN_NAME'];
}

// Định nghĩa các INDEX cần thêm
$indexesToAdd = [
    ['orders', 'idx_orders_user_created', ['user_id', 'created_at']],
    ['orders', 'idx_orders_status', ['status']],
    ['orders', 'idx_orders_tracking', ['tracking_code']],
    ['orders', 'idx_orders_payment_status', ['payment_status']],
    ['orders', 'idx_orders_payment_method', ['payment_method']],
    ['order_items', 'idx_order_items_order', ['order_id']],
    ['order_items', 'idx_order_items_product', ['product_id']],
    ['products', 'idx_products_category_active', ['category_id', 'is_active']],
    ['products', 'idx_products_brand', ['brand_id']],
    ['products', 'idx_products_active_created', ['is_active', 'created_at']],
    ['products', 'idx_products_featured_active', ['is_featured', 'is_active']],
    ['products', 'idx_products_discount_active', ['discount_percent', 'is_active']],
    ['products', 'idx_products_supplier', ['supplier_id']],
    ['products', 'idx_products_name_search', ['name', 'description']],
    ['users', 'idx_users_email', ['email']],
    ['users', 'idx_users_role', ['role_id']],
    ['users', 'idx_users_blocked', ['is_blocked']],
    ['product_specs', 'idx_product_specs_product', ['product_id']],
    ['product_specs', 'idx_product_specs_product_name', ['product_id', 'spec_name']],
    ['product_images', 'idx_product_images_product', ['product_id', 'sort_order', 'id']],
    ['product_comments', 'idx_product_comments_product', ['product_id', 'is_hidden']],
    ['product_comments', 'idx_product_comments_user', ['user_id']],
    ['conversations', 'idx_conversations_user', ['user_id']],
    ['conversations', 'idx_conversations_updated', ['updated_at']],
    ['messages', 'idx_messages_conversation_created', ['conversation_id', 'created_at']],
    ['messages', 'idx_messages_conversation_admin', ['conversation_id', 'is_admin_reply']],
    ['notifications', 'idx_notifications_user_created', ['user_id', 'created_at']],
    ['notifications', 'idx_notifications_user_read', ['user_id', 'is_read']],
    ['warehouse_logs', 'idx_warehouse_logs_product', ['product_id']],
    ['warehouse_logs', 'idx_warehouse_logs_type', ['type']],
    ['warehouse_logs', 'idx_warehouse_logs_ref', ['reference_id']],
    ['vouchers', 'idx_vouchers_code_active', ['code', 'is_active']],
    ['voucher_usages', 'idx_voucher_usages_user', ['user_id']],
    ['voucher_usages', 'idx_voucher_usages_voucher_user', ['voucher_id', 'user_id']],
    ['email_queue', 'idx_email_queue_status', ['status']],
    ['email_queue', 'idx_email_queue_scheduled', ['scheduled_at', 'id']],
    ['categories', 'idx_categories_name', ['name']],
    ['brands', 'idx_brands_name', ['name']],
    ['banners', 'idx_banners_active_sort', ['is_active', 'sort_order', 'id']],
    ['cms_articles', 'idx_cms_articles_status_created', ['status', 'created_at']],
    ['news_articles', 'idx_news_articles_status_created', ['status', 'created_at']],
    ['warehouse_receipt_items', 'idx_wr_items_receipt', ['receipt_id']],
    ['warehouse_receipt_items', 'idx_wr_items_product', ['product_id']],
    ['purchase_order_items', 'idx_po_items_po', ['po_id']],
    ['purchase_order_items', 'idx_po_items_product', ['product_id']],
    ['broadcast_notifications', 'idx_broadcast_active_expires', ['is_active', 'expires_at']],
    ['inventory_logs', 'idx_inventory_logs_product', ['product_id']],
];

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   OPTIMIZE INDEXES — Thêm INDEX còn thiếu                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Danh sách index hiện có: " . count($existingIndexes) . " indexes\n\n";

foreach ($indexesToAdd as $idx) {
    [$table, $indexName, $columns] = $idx;
    
    // Kiểm tra bảng tồn tại
    $tableExists = $db->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $db->quote($table)
    )->fetchColumn();
    
    if (!$tableExists) {
        echo "⏭️  Bảng `$table` không tồn tại — bỏ qua `$indexName`\n";
        $totalSkipped++;
        continue;
    }
    
    // Kiểm tra index đã tồn tại
    $key = $table . '.' . $indexName;
    if (isset($existingIndexes[$key])) {
        echo "  ✅ `$table`.`$indexName` — đã tồn tại\n";
        $totalSkipped++;
        continue;
    }
    
    // Tạo ALTER TABLE
    $colList = '`' . implode('`, `', $columns) . '`';
    $isFTS = ($indexName === 'idx_products_name_search');
    
    if ($isFTS) {
        $sql = "ALTER TABLE `$table` ADD FULLTEXT INDEX `$indexName` ($colList)";
    } else {
        $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` ($colList)";
    }
    
    try {
        $db->exec($sql);
        echo "  ✅ `$table`.`$indexName` — ĐÃ THÊM thành công\n";
        $totalAdded++;
    } catch (PDOException $e) {
        echo "  ❌ `$table`.`$indexName` — LỖI: " . $e->getMessage() . "\n";
        $totalErrors++;
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║   KẾT QUẢ                                                     ║\n";
echo "╠════════════════════════════════════════════════════════════════╣\n";
echo "║  ✅ Đã thêm mới:   " . str_pad($totalAdded, 40, ' ', STR_PAD_LEFT) . " ║\n";
echo "║  ⏭️  Đã có sẵn:    " . str_pad($totalSkipped, 40, ' ', STR_PAD_LEFT) . " ║\n";
echo "║  ❌ Lỗi:           " . str_pad($totalErrors, 40, ' ', STR_PAD_LEFT) . " ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

// Bảng tóm tắt
echo "\n📊 Tổng quan index theo bảng:\n";
$tablesToCheck = ['orders', 'order_items', 'products', 'users', 'product_specs', 
                  'product_images', 'product_comments', 'notifications', 'messages',
                  'conversations', 'warehouse_logs', 'vouchers', 'voucher_usages',
                  'email_queue', 'banners', 'return_requests', 'cms_articles',
                  'categories', 'brands', 'warehouse_receipt_items', 'purchase_order_items',
                  'broadcast_notifications', 'inventory_logs', 'serial_numbers',
                  'shipping_orders', 'loyalty_points', 'payment_transactions',
                  'wishlists', 'audit_logs'];

foreach ($tablesToCheck as $table) {
    $tableExists = $db->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $db->quote($table)
    )->fetchColumn();
    if (!$tableExists) continue;
    
    $indexCount = $db->query("
        SELECT COUNT(DISTINCT INDEX_NAME) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $db->quote($table)
    )->fetchColumn();
    
    echo "   📊 `$table`: $indexCount indexes\n";
}

if ($totalAdded > 0) {
    echo "\n🎉 Đã thêm $totalAdded INDEX mới! Hiệu năng sẽ cải thiện đáng kể.\n";
} else {
    echo "\n💡 Tất cả INDEX đã được tối ưu từ trước. Không cần thêm gì mới.\n";
}
