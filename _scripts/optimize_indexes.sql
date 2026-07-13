-- ============================================================
-- optimize_indexes.sql — Tối ưu hiệu năng CSDL
-- Bổ sung các INDEX còn thiếu dựa trên phân tích query patterns
-- 
-- Cách chạy:
--   mysql -u root -D ban_linh_kien < _scripts/optimize_indexes.sql
--   hoặc import vào phpMyAdmin / MySQL Workbench
-- ============================================================
-- DIỄN GIẢI:
-- Mỗi khối dưới đây kiểm tra sự tồn tại của INDEX trước khi tạo,
-- đảm bảo an toàn khi chạy lại nhiều lần.
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- 1. orders — Bảng quan trọng nhất, query nhiều nhất
-- ============================================================
-- WHERE user_id = ? ORDER BY created_at DESC
SET @db = (SELECT DATABASE());
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders'
                   AND INDEX_NAME = 'idx_orders_user_created');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_user_created` (`user_id`, `created_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE status = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders'
                   AND INDEX_NAME = 'idx_orders_status');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_status` (`status`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE tracking_code = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders'
                   AND INDEX_NAME = 'idx_orders_tracking');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_tracking` (`tracking_code`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE payment_status = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders'
                   AND INDEX_NAME = 'idx_orders_payment_status');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_payment_status` (`payment_status`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE payment_method = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders'
                   AND INDEX_NAME = 'idx_orders_payment_method');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_payment_method` (`payment_method`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. order_items — JOIN quan trọng
-- ============================================================
-- WHERE order_id = ?  (JOIN với orders)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_items'
                   AND INDEX_NAME = 'idx_order_items_order');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `order_items` ADD INDEX `idx_order_items_order` (`order_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE product_id = ?  (JOIN + GROUP BY + top-selling)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_items'
                   AND INDEX_NAME = 'idx_order_items_product');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `order_items` ADD INDEX `idx_order_items_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. products — Bảng lõi, query đa dạng
-- ============================================================
-- WHERE category_id = ? AND is_active = 1
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_category_active');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_category_active` (`category_id`, `is_active`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE brand_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_brand');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_brand` (`brand_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE is_active = 1 ORDER BY created_at DESC  (trang chủ, latest)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_active_created');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_active_created` (`is_active`, `created_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE is_featured = 1 AND is_active = 1
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_featured_active');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_featured_active` (`is_featured`, `is_active`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE discount_percent > 0 AND is_active = 1 ORDER BY discount_percent DESC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_discount_active');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_discount_active` (`discount_percent`, `is_active`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE supplier_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_supplier');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD INDEX `idx_products_supplier` (`supplier_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Full-text search index cho tìm kiếm sản phẩm
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
                   AND INDEX_NAME = 'idx_products_name_search');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `products` ADD FULLTEXT INDEX `idx_products_name_search` (`name`, `description`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 4. users — Đăng nhập, tìm kiếm
-- ============================================================
-- WHERE username = ? OR email = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users'
                   AND INDEX_NAME = 'idx_users_email');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD INDEX `idx_users_email` (`email`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE role_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users'
                   AND INDEX_NAME = 'idx_users_role');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD INDEX `idx_users_role` (`role_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE is_blocked = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users'
                   AND INDEX_NAME = 'idx_users_blocked');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD INDEX `idx_users_blocked` (`is_blocked`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. product_specs — JOIN với products
-- ============================================================
-- WHERE product_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_specs'
                   AND INDEX_NAME = 'idx_product_specs_product');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `product_specs` ADD INDEX `idx_product_specs_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE product_id = ? AND spec_name = ? (Build PC filter by Socket)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_specs'
                   AND INDEX_NAME = 'idx_product_specs_product_name');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `product_specs` ADD INDEX `idx_product_specs_product_name` (`product_id`, `spec_name`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 6. product_images — JOIN với products
-- ============================================================
-- WHERE product_id = ? ORDER BY sort_order ASC, id ASC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_images'
                   AND INDEX_NAME = 'idx_product_images_product');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `product_images` ADD INDEX `idx_product_images_product` (`product_id`, `sort_order`, `id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 7. product_comments — JOIN + rating queries
-- ============================================================
-- WHERE product_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_comments'
                   AND INDEX_NAME = 'idx_product_comments_product');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `product_comments` ADD INDEX `idx_product_comments_product` (`product_id`, `is_hidden`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE user_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_comments'
                   AND INDEX_NAME = 'idx_product_comments_user');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `product_comments` ADD INDEX `idx_product_comments_user` (`user_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 8. conversations — Chat
-- ============================================================
-- WHERE user_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'conversations'
                   AND INDEX_NAME = 'idx_conversations_user');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `conversations` ADD INDEX `idx_conversations_user` (`user_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ORDER BY updated_at DESC  (admin list)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'conversations'
                   AND INDEX_NAME = 'idx_conversations_updated');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `conversations` ADD INDEX `idx_conversations_updated` (`updated_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 9. messages — Chat messages
-- ============================================================
-- WHERE conversation_id = ? ORDER BY created_at ASC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'messages'
                   AND INDEX_NAME = 'idx_messages_conversation_created');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `messages` ADD INDEX `idx_messages_conversation_created` (`conversation_id`, `created_at` ASC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE conversation_id = ? AND is_admin_reply = ?  (markAsRead)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'messages'
                   AND INDEX_NAME = 'idx_messages_conversation_admin');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `messages` ADD INDEX `idx_messages_conversation_admin` (`conversation_id`, `is_admin_reply`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 10. notifications — Thông báo user
-- ============================================================
-- WHERE user_id = ? ORDER BY created_at DESC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notifications'
                   AND INDEX_NAME = 'idx_notifications_user_created');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `notifications` ADD INDEX `idx_notifications_user_created` (`user_id`, `created_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE user_id = ? AND is_read = 0  (đếm unread)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notifications'
                   AND INDEX_NAME = 'idx_notifications_user_read');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `notifications` ADD INDEX `idx_notifications_user_read` (`user_id`, `is_read`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 11. warehouse_logs — Lịch sử xuất nhập kho
-- ============================================================
-- WHERE product_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_logs'
                   AND INDEX_NAME = 'idx_warehouse_logs_product');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `warehouse_logs` ADD INDEX `idx_warehouse_logs_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE type = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_logs'
                   AND INDEX_NAME = 'idx_warehouse_logs_type');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `warehouse_logs` ADD INDEX `idx_warehouse_logs_type` (`type`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE reference_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_logs'
                   AND INDEX_NAME = 'idx_warehouse_logs_ref');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `warehouse_logs` ADD INDEX `idx_warehouse_logs_ref` (`reference_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 12. vouchers — Mã giảm giá
-- ============================================================
-- WHERE code = ? AND is_active = 1
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'vouchers'
                   AND INDEX_NAME = 'idx_vouchers_code_active');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `vouchers` ADD INDEX `idx_vouchers_code_active` (`code`, `is_active`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 13. voucher_usages — Lịch sử sử dụng voucher
-- ============================================================
-- WHERE user_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'voucher_usages'
                   AND INDEX_NAME = 'idx_voucher_usages_user');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `voucher_usages` ADD INDEX `idx_voucher_usages_user` (`user_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE voucher_id = ? AND user_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'voucher_usages'
                   AND INDEX_NAME = 'idx_voucher_usages_voucher_user');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `voucher_usages` ADD INDEX `idx_voucher_usages_voucher_user` (`voucher_id`, `user_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 14. email_queue — Hàng đợi email
-- ============================================================
-- WHERE status = ?  (email_queue_worker)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'email_queue'
                   AND INDEX_NAME = 'idx_email_queue_status');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `email_queue` ADD INDEX `idx_email_queue_status` (`status`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ORDER BY scheduled_at ASC, id ASC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'email_queue'
                   AND INDEX_NAME = 'idx_email_queue_scheduled');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `email_queue` ADD INDEX `idx_email_queue_scheduled` (`scheduled_at`, `id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 15. categories / brands
-- ============================================================
-- ORDER BY name ASC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'categories'
                   AND INDEX_NAME = 'idx_categories_name');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `categories` ADD INDEX `idx_categories_name` (`name` ASC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'brands'
                   AND INDEX_NAME = 'idx_brands_name');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `brands` ADD INDEX `idx_brands_name` (`name` ASC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 16. banners — Slider
-- ============================================================
-- WHERE is_active = 1 ORDER BY sort_order ASC, id ASC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'banners'
                   AND INDEX_NAME = 'idx_banners_active_sort');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `banners` ADD INDEX `idx_banners_active_sort` (`is_active`, `sort_order`, `id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 17. cms_articles / news_articles — Tin tức
-- ============================================================
-- WHERE status = 'published' ORDER BY created_at DESC
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cms_articles'
                   AND INDEX_NAME = 'idx_cms_articles_status_created');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `cms_articles` ADD INDEX `idx_cms_articles_status_created` (`status`, `created_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- (Tương tự nếu có bảng news_articles)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'news_articles'
                   AND INDEX_NAME = 'idx_news_articles_status_created');
-- Sử dụng prepared statement an toàn cho bảng có thể không tồn tại
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'news_articles');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `news_articles` ADD INDEX `idx_news_articles_status_created` (`status`, `created_at` DESC)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 18. warehouse_receipt_items — Chi tiết phiếu nhập
-- ============================================================
-- WHERE receipt_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_receipt_items'
                   AND INDEX_NAME = 'idx_wr_items_receipt');
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_receipt_items');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `warehouse_receipt_items` ADD INDEX `idx_wr_items_receipt` (`receipt_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE product_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'warehouse_receipt_items'
                   AND INDEX_NAME = 'idx_wr_items_product');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `warehouse_receipt_items` ADD INDEX `idx_wr_items_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 19. purchase_order_items — Chi tiết PO
-- ============================================================
-- WHERE po_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'purchase_order_items'
                   AND INDEX_NAME = 'idx_po_items_po');
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'purchase_order_items');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `purchase_order_items` ADD INDEX `idx_po_items_po` (`po_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- WHERE product_id = ?
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'purchase_order_items'
                   AND INDEX_NAME = 'idx_po_items_product');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `purchase_order_items` ADD INDEX `idx_po_items_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 20. broadcast_notifications — Thông báo broadcast
-- ============================================================
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'broadcast_notifications'
                   AND INDEX_NAME = 'idx_broadcast_active_expires');
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'broadcast_notifications');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `broadcast_notifications` ADD INDEX `idx_broadcast_active_expires` (`is_active`, `expires_at`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 21. inventory_logs — Log tồn kho (nếu tồn tại)
-- ============================================================
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'inventory_logs'
                   AND INDEX_NAME = 'idx_inventory_logs_product');
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'inventory_logs');
SET @sql = IF(@table_exists > 0 AND @idx_exists = 0,
    'ALTER TABLE `inventory_logs` ADD INDEX `idx_inventory_logs_product` (`product_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- KẾT THÚC — Tổng kết
-- ============================================================
SELECT '✅ optimize_indexes.sql hoàn tất! Đã thêm các INDEX còn thiếu.' AS result;
