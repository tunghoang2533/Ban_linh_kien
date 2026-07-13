<?php
/**
 * Fix migration v5 — handle column existence checks for MySQL < 8.0
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
$db->exec('SET NAMES utf8mb4');

// Helper: check if column exists
function hasColumn($db, $table, $col) {
    $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    return $stmt->fetch() !== false;
}
// Helper: check if index exists
function hasIndex($db, $table, $idx) {
    $stmt = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$idx'");
    return $stmt->fetch() !== false;
}

// 1. Dashboard widgets table
$db->exec("
    CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `user_id`    INT NOT NULL,
        `widget_key` VARCHAR(60) NOT NULL,
        `title`      VARCHAR(200) DEFAULT NULL,
        `enabled`    TINYINT(1) NOT NULL DEFAULT 1,
        `sort_order` INT NOT NULL DEFAULT 0,
        `settings`   TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_user_widget` (`user_id`, `widget_key`),
        INDEX `idx_user_order` (`user_id`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✅ dashboard_widgets table created\n";

// 2. Voucher personalization columns
if (!hasColumn($db, 'vouchers', 'user_id')) {
    $db->exec("ALTER TABLE vouchers ADD COLUMN `user_id` INT DEFAULT NULL AFTER `code`");
    $db->exec("ALTER TABLE vouchers ADD INDEX `idx_vouchers_user` (`user_id`)");
    echo "✅ vouchers.user_id added\n";
} else { echo "⏭ vouchers.user_id already exists\n"; }

if (!hasColumn($db, 'vouchers', 'personal_note')) {
    $db->exec("ALTER TABLE vouchers ADD COLUMN `personal_note` VARCHAR(500) DEFAULT NULL AFTER `description`");
    echo "✅ vouchers.personal_note added\n";
} else { echo "⏭ vouchers.personal_note already exists\n"; }

if (!hasColumn($db, 'vouchers', 'sent_at')) {
    $db->exec("ALTER TABLE vouchers ADD COLUMN `sent_at` DATETIME DEFAULT NULL AFTER `expire_date`");
    echo "✅ vouchers.sent_at added\n";
} else { echo "⏭ vouchers.sent_at already exists\n"; }

// 3. Audit indexes
if (!hasIndex($db, 'audit_logs', 'idx_audit_created')) {
    $db->exec("ALTER TABLE audit_logs ADD INDEX `idx_audit_created` (`created_at`)");
    echo "✅ audit_logs.idx_audit_created added\n";
} else { echo "⏭ audit_logs.idx_audit_created already exists\n"; }

if (!hasIndex($db, 'audit_logs', 'idx_audit_module_action')) {
    $db->exec("ALTER TABLE audit_logs ADD INDEX `idx_audit_module_action` (`module`, `action`)");
    echo "✅ audit_logs.idx_audit_module_action added\n";
} else { echo "⏭ audit_logs.idx_audit_module_action already exists\n"; }

echo "\n🎉 Migration v5 completed!\n";
