-- ============================================================
-- MIGRATION: Bảng lịch sử trạng thái đơn hàng
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id`          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT NOT NULL,
    `from_status` VARCHAR(50) DEFAULT NULL COMMENT 'Trạng thái trước',
    `to_status`   VARCHAR(50) NOT NULL    COMMENT 'Trạng thái mới',
    `changed_by`  INT DEFAULT NULL        COMMENT 'ID user/admin thực hiện đổi',
    `changer_name` VARCHAR(150) DEFAULT NULL COMMENT 'Tên người thực hiện',
    `note`        TEXT DEFAULT NULL       COMMENT 'Ghi chú thêm',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`order_id`),
    INDEX (`created_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Lịch sử thay đổi trạng thái đơn hàng';

-- Ghi lại trạng thái ban đầu cho tất cả đơn hàng hiện có
INSERT INTO `order_status_history` (`order_id`, `from_status`, `to_status`, `changer_name`, `note`, `created_at`)
SELECT
    `id`,
    NULL,
    `status`,
    'System',
    'Trạng thái khởi tạo (migration)',
    `created_at`
FROM `orders`
WHERE `id` NOT IN (SELECT DISTINCT `order_id` FROM `order_status_history`);
