<?php
/**
 * Runner: Back-in-Stock Alert migration
 * Usage:   php _scripts/run_back_in_stock.php
 */

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config.php';
require_once $projectRoot . '/core/Database.php';

echo "🔧 Running Back-in-Stock migration...\n";

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/migration_back_in_stock.sql');

    $statements = explode(';', $sql);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            $db->exec($stmt);
            $count++;
        }
    }

    echo "✅ Migration complete! Executed $count statement(s).\n";

    // Verify table exists
    $check = $db->query("SHOW TABLES LIKE 'back_in_stock_subscriptions'");
    if ($check->rowCount() > 0) {
        echo "✅ Table `back_in_stock_subscriptions` ready.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
