-- ============================================================
-- WAREHOUSE UPGRADE MIGRATION
-- Nâng cấp quản lý kho: 2 kho (HN+HCM), phiếu nhập/xuất,
-- Purchase Order, Kiểm kê, Bin Location
-- Ngày: 2026-06-29
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ============================================================
-- 1. WAREHOUSES — 2 kho: Hà Nội & HCM
-- ============================================================
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,          -- HN, HCM
    `name` VARCHAR(100) NOT NULL,                -- Kho Hà Nội, Kho TP.HCM
    `address` TEXT,
    `phone` VARCHAR(30) DEFAULT '',
    `manager` VARCHAR(100) DEFAULT '',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `warehouses` (`code`, `name`, `address`, `phone`, `manager`) VALUES
('HN',  'Kho Hà Nội',   '123 Đường Láng, Đống Đa, Hà Nội',         '024 0000 0001', 'Nguyễn Văn A'),
('HCM', 'Kho TP.HCM',   '456 Điện Biên Phủ, Bình Thạnh, TP.HCM',  '028 0000 0002', 'Trần Thị B');

-- ============================================================
-- 2. Thêm cột warehouse_id & bin_location vào products
-- ============================================================
DROP PROCEDURE IF EXISTS _alter_products_warehouse;
CREATE PROCEDURE _alter_products_warehouse()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='warehouse_id'
    ) THEN
        ALTER TABLE `products` ADD COLUMN `warehouse_id` INT DEFAULT 1
            COMMENT 'Kho lưu trữ mặc định';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='bin_location'
    ) THEN
        ALTER TABLE `products` ADD COLUMN `bin_location` VARCHAR(50) DEFAULT NULL
            COMMENT 'Vị trí kệ, ví dụ: A1-B2';
    END IF;
END;
CALL _alter_products_warehouse();
DROP PROCEDURE IF EXISTS _alter_products_warehouse;

-- ============================================================
-- 3. Thêm cột warehouse_id, receipt_id, batch_no vào warehouse_logs
-- ============================================================
DROP PROCEDURE IF EXISTS _alter_wlogs;
CREATE PROCEDURE _alter_wlogs()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_logs' AND COLUMN_NAME='warehouse_id'
    ) THEN
        ALTER TABLE `warehouse_logs` ADD COLUMN `warehouse_id` INT DEFAULT 1;
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_logs' AND COLUMN_NAME='receipt_id'
    ) THEN
        ALTER TABLE `warehouse_logs` ADD COLUMN `receipt_id` INT DEFAULT NULL
            COMMENT 'Liên kết phiếu nhập/xuất chính thức';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_logs' AND COLUMN_NAME='batch_no'
    ) THEN
        ALTER TABLE `warehouse_logs` ADD COLUMN `batch_no` VARCHAR(50) DEFAULT NULL
            COMMENT 'Số lô hàng';
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_logs' AND COLUMN_NAME='po_id'
    ) THEN
        ALTER TABLE `warehouse_logs` ADD COLUMN `po_id` INT DEFAULT NULL
            COMMENT 'Liên kết Purchase Order';
    END IF;
END;
CALL _alter_wlogs();
DROP PROCEDURE IF EXISTS _alter_wlogs;

-- ============================================================
-- 4. WAREHOUSE_RECEIPTS — Phiếu Nhập Kho chính thức (duyệt 2 bước)
-- ============================================================
CREATE TABLE IF NOT EXISTS `warehouse_receipts` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `receipt_code` VARCHAR(30) NOT NULL UNIQUE,    -- PN-YYYYMMDD-XXXX
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `supplier_id` INT DEFAULT NULL,
    `type` ENUM('purchase','return','transfer','adjustment') DEFAULT 'purchase'
        COMMENT 'purchase=mua NCC, return=hàng hoàn, transfer=điều chuyển, adjustment=điều chỉnh',
    `status` ENUM('draft','pending','approved','cancelled') DEFAULT 'draft'
        COMMENT 'draft=nháp, pending=chờ duyệt, approved=đã duyệt, cancelled=hủy',
    `total_qty` INT DEFAULT 0,
    `total_amount` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT,
    `po_id` INT DEFAULT NULL COMMENT 'Nếu tạo từ Purchase Order',
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
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    INDEX (`status`),
    INDEX (`created_at`),
    INDEX (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. WAREHOUSE_RECEIPT_ITEMS — Chi tiết phiếu nhập
-- ============================================================
CREATE TABLE IF NOT EXISTS `warehouse_receipt_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. PURCHASE_ORDERS — Đặt hàng nhà cung cấp
-- ============================================================
CREATE TABLE IF NOT EXISTS `purchase_orders` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_code` VARCHAR(30) NOT NULL UNIQUE,         -- PO-YYYYMMDD-XXXX
    `supplier_id` INT NOT NULL,
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `status` ENUM('draft','pending','approved','ordered','received','cancelled') DEFAULT 'draft'
        COMMENT 'draft→pending→approved→ordered(gửi NCC)→received(nhận hàng)→cancelled',
    `expected_date` DATE DEFAULT NULL,
    `total_qty` INT DEFAULT 0,
    `total_amount` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT,
    `created_by` INT DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `ordered_at` DATETIME DEFAULT NULL,
    `received_at` DATETIME DEFAULT NULL,
    `receipt_id` INT DEFAULT NULL COMMENT 'Phiếu nhập được tạo khi nhận hàng',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`),
    INDEX (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. PURCHASE_ORDER_ITEMS — Chi tiết PO
-- ============================================================
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `po_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_cost` DECIMAL(12,0) DEFAULT 0,
    `subtotal` DECIMAL(15,0) DEFAULT 0,
    `received_qty` INT DEFAULT 0 COMMENT 'Số lượng thực tế nhận được',
    `note` TEXT,
    FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. STOCKTAKE_SESSIONS — Phiên kiểm kê kho
-- ============================================================
CREATE TABLE IF NOT EXISTS `stocktake_sessions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_code` VARCHAR(30) NOT NULL UNIQUE,    -- KK-YYYYMMDD-XXX
    `warehouse_id` INT NOT NULL DEFAULT 1,
    `status` ENUM('open','counting','reviewing','closed','cancelled') DEFAULT 'open',
    `scope` TEXT COMMENT 'Mô tả phạm vi kiểm kê (toàn bộ / theo danh mục...)',
    `total_products` INT DEFAULT 0,
    `counted_products` INT DEFAULT 0,
    `variance_plus` INT DEFAULT 0 COMMENT 'Tổng chênh lệch dương',
    `variance_minus` INT DEFAULT 0 COMMENT 'Tổng chênh lệch âm',
    `note` TEXT,
    `started_by` INT DEFAULT NULL,
    `closed_by` INT DEFAULT NULL,
    `started_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `closed_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    INDEX (`status`),
    INDEX (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 9. STOCKTAKE_ITEMS — Chi tiết kiểm kê từng sản phẩm
-- ============================================================
CREATE TABLE IF NOT EXISTS `stocktake_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `system_qty` INT NOT NULL DEFAULT 0 COMMENT 'Tồn hệ thống tại thời điểm mở session',
    `counted_qty` INT DEFAULT NULL COMMENT 'Số thực tế đếm được (NULL = chưa kiểm)',
    `variance` INT DEFAULT NULL COMMENT 'counted_qty - system_qty',
    `bin_location` VARCHAR(50) DEFAULT NULL,
    `note` TEXT,
    `counted_by` INT DEFAULT NULL,
    `counted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`session_id`) REFERENCES `stocktake_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    UNIQUE KEY `session_product` (`session_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
