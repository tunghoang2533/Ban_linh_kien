<?php
/**
 * Warehouse Upgrade Migration Script
 * Chạy: http://localhost/Ban_linh_kien/run_warehouse_upgrade.php
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = (new Database())->connect();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$steps = [];
$errors = [];

function runSQL($db, $sql, $label) {
    global $steps, $errors;
    try {
        $db->exec($sql);
        $steps[] = "✅ $label";
        return true;
    } catch (PDOException $e) {
        // Bỏ qua lỗi "already exists"
        if (strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Duplicate column') !== false ||
            strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $steps[] = "⏭️ $label (đã tồn tại, bỏ qua)";
            return true;
        }
        $errors[] = "❌ $label: " . $e->getMessage();
        return false;
    }
}

function columnExists($db, $table, $col) {
    $r = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='$col'");
    return (int)$r->fetchColumn() > 0;
}

// ── 1. Bảng warehouses ──────────────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code`       VARCHAR(20) NOT NULL UNIQUE,
    `name`       VARCHAR(100) NOT NULL,
    `address`    TEXT,
    `phone`      VARCHAR(30) DEFAULT '',
    `manager`    VARCHAR(100) DEFAULT '',
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng warehouses");

runSQL($db, "
INSERT IGNORE INTO `warehouses` (`code`, `name`, `address`, `phone`, `manager`) VALUES
('HN',  'Kho Hà Nội',  '123 Đường Láng, Đống Đa, Hà Nội',        '024 0000 0001', 'Nguyễn Văn A'),
('HCM', 'Kho TP.HCM', '456 Điện Biên Phủ, Bình Thạnh, TP.HCM',  '028 0000 0002', 'Trần Thị B')
", "Thêm dữ liệu 2 kho");

// ── 2. Thêm cột vào products ──────────────────────────────────
if (!columnExists($db, 'products', 'warehouse_id')) {
    runSQL($db, "ALTER TABLE `products` ADD COLUMN `warehouse_id` INT DEFAULT 1", "Thêm warehouse_id vào products");
} else {
    $steps[] = "⏭️ Cột warehouse_id đã tồn tại trong products";
}
if (!columnExists($db, 'products', 'bin_location')) {
    runSQL($db, "ALTER TABLE `products` ADD COLUMN `bin_location` VARCHAR(50) DEFAULT NULL", "Thêm bin_location vào products");
} else {
    $steps[] = "⏭️ Cột bin_location đã tồn tại trong products";
}

// ── 3. Thêm cột vào warehouse_logs ───────────────────────────
foreach ([
    ['warehouse_id', "ALTER TABLE `warehouse_logs` ADD COLUMN `warehouse_id` INT DEFAULT 1",   "Thêm warehouse_id vào warehouse_logs"],
    ['receipt_id',   "ALTER TABLE `warehouse_logs` ADD COLUMN `receipt_id` INT DEFAULT NULL",   "Thêm receipt_id vào warehouse_logs"],
    ['batch_no',     "ALTER TABLE `warehouse_logs` ADD COLUMN `batch_no` VARCHAR(50) DEFAULT NULL", "Thêm batch_no vào warehouse_logs"],
    ['po_id',        "ALTER TABLE `warehouse_logs` ADD COLUMN `po_id` INT DEFAULT NULL",        "Thêm po_id vào warehouse_logs"],
] as [$col, $sql, $label]) {
    if (!columnExists($db, 'warehouse_logs', $col)) {
        runSQL($db, $sql, $label);
    } else {
        $steps[] = "⏭️ Cột $col đã tồn tại trong warehouse_logs";
    }
}

// ── 4. Bảng warehouse_receipts ────────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `warehouse_receipts` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `receipt_code`  VARCHAR(30) NOT NULL UNIQUE,
    `warehouse_id`  INT NOT NULL DEFAULT 1,
    `supplier_id`   INT DEFAULT NULL,
    `type`          ENUM('purchase','return','transfer','adjustment') DEFAULT 'purchase',
    `status`        ENUM('draft','pending','approved','cancelled') DEFAULT 'draft',
    `total_qty`     INT DEFAULT 0,
    `total_amount`  DECIMAL(15,0) DEFAULT 0,
    `note`          TEXT,
    `po_id`         INT DEFAULT NULL,
    `created_by`    INT DEFAULT NULL,
    `submitted_by`  INT DEFAULT NULL,
    `submitted_at`  DATETIME DEFAULT NULL,
    `approved_by`   INT DEFAULT NULL,
    `approved_at`   DATETIME DEFAULT NULL,
    `cancelled_by`  INT DEFAULT NULL,
    `cancelled_at`  DATETIME DEFAULT NULL,
    `cancel_reason` TEXT,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`status`),
    INDEX (`warehouse_id`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng warehouse_receipts");

// ── 5. Bảng warehouse_receipt_items ──────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `warehouse_receipt_items` (
    `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `receipt_id`   INT NOT NULL,
    `product_id`   INT NOT NULL,
    `quantity`     INT NOT NULL DEFAULT 0,
    `unit_cost`    DECIMAL(12,0) DEFAULT 0,
    `subtotal`     DECIMAL(15,0) DEFAULT 0,
    `batch_no`     VARCHAR(50) DEFAULT NULL,
    `bin_location` VARCHAR(50) DEFAULT NULL,
    `note`         TEXT,
    FOREIGN KEY (`receipt_id`) REFERENCES `warehouse_receipts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng warehouse_receipt_items");

// ── 6. Bảng purchase_orders ──────────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `purchase_orders` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_code`       VARCHAR(30) NOT NULL UNIQUE,
    `supplier_id`   INT NOT NULL,
    `warehouse_id`  INT NOT NULL DEFAULT 1,
    `status`        ENUM('draft','pending','approved','ordered','received','cancelled') DEFAULT 'draft',
    `expected_date` DATE DEFAULT NULL,
    `total_qty`     INT DEFAULT 0,
    `total_amount`  DECIMAL(15,0) DEFAULT 0,
    `note`          TEXT,
    `created_by`    INT DEFAULT NULL,
    `approved_by`   INT DEFAULT NULL,
    `approved_at`   DATETIME DEFAULT NULL,
    `ordered_at`    DATETIME DEFAULT NULL,
    `received_at`   DATETIME DEFAULT NULL,
    `receipt_id`    INT DEFAULT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`),
    INDEX (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng purchase_orders");

// ── 7. Bảng purchase_order_items ─────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_id`        INT NOT NULL,
    `product_id`   INT NOT NULL,
    `quantity`     INT NOT NULL DEFAULT 1,
    `unit_cost`    DECIMAL(12,0) DEFAULT 0,
    `subtotal`     DECIMAL(15,0) DEFAULT 0,
    `received_qty` INT DEFAULT 0,
    `note`         TEXT,
    FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng purchase_order_items");

// ── 8. Bảng stocktake_sessions ───────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `stocktake_sessions` (
    `id`                INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_code`      VARCHAR(30) NOT NULL UNIQUE,
    `warehouse_id`      INT NOT NULL DEFAULT 1,
    `status`            ENUM('open','counting','reviewing','closed','cancelled') DEFAULT 'open',
    `scope`             TEXT,
    `total_products`    INT DEFAULT 0,
    `counted_products`  INT DEFAULT 0,
    `variance_plus`     INT DEFAULT 0,
    `variance_minus`    INT DEFAULT 0,
    `note`              TEXT,
    `started_by`        INT DEFAULT NULL,
    `closed_by`         INT DEFAULT NULL,
    `started_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `closed_at`         DATETIME DEFAULT NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`),
    INDEX (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng stocktake_sessions");

// ── 9. Bảng stocktake_items ──────────────────────────────────
runSQL($db, "
CREATE TABLE IF NOT EXISTS `stocktake_items` (
    `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_id`   INT NOT NULL,
    `product_id`   INT NOT NULL,
    `system_qty`   INT NOT NULL DEFAULT 0,
    `counted_qty`  INT DEFAULT NULL,
    `variance`     INT DEFAULT NULL,
    `bin_location` VARCHAR(50) DEFAULT NULL,
    `note`         TEXT,
    `counted_by`   INT DEFAULT NULL,
    `counted_at`   DATETIME DEFAULT NULL,
    FOREIGN KEY (`session_id`) REFERENCES `stocktake_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    UNIQUE KEY `session_product` (`session_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
", "Tạo bảng stocktake_items");

// ── Tổng kết ──────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Warehouse Upgrade Migration</title>
<style>
body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f8fafc; }
h1 { color: #1e293b; display: flex; align-items: center; gap: 10px; }
.card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,.08); margin-bottom: 20px; }
.step { padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
.step:last-child { border-bottom: none; }
.success-box { background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-left: 4px solid #10b981; padding: 16px 20px; border-radius: 10px; }
.error-box { background: linear-gradient(135deg, #fee2e2, #fecaca); border-left: 4px solid #ef4444; padding: 16px 20px; border-radius: 10px; margin-top: 12px; }
a.btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; margin-top: 20px; }
</style>
</head>
<body>
<h1>📦 Warehouse Upgrade Migration</h1>

<?php if (empty($errors)): ?>
<div class="success-box">
    <strong>✅ Migration hoàn thành thành công!</strong>
    <p style="margin:6px 0 0;font-size:14px;">Tất cả <?= count($steps) ?> bước đã thực hiện.</p>
</div>
<?php else: ?>
<div class="error-box">
    <strong>⚠️ Có <?= count($errors) ?> lỗi xảy ra</strong>
</div>
<?php endif; ?>

<div class="card" style="margin-top: 20px;">
    <h3 style="margin-top:0; color:#374151;">Chi tiết thực thi</h3>
    <?php foreach ($steps as $s): ?>
        <div class="step"><?= htmlspecialchars($s) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="step" style="color:#dc2626;"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
</div>

<a href="admin/" class="btn">🏠 Vào Admin Panel</a>
</body>
</html>
