-- ============================================================
-- Migration: Back-in-Stock Alert
-- Bảng lưu email đăng ký nhận thông báo khi sản phẩm có hàng lại
-- ============================================================

CREATE TABLE IF NOT EXISTS `back_in_stock_subscriptions` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT          NOT NULL,
    `email`       VARCHAR(255) NOT NULL,
    `user_id`     INT          DEFAULT NULL COMMENT 'ID người dùng (nếu đã đăng nhập)',
    `status`      ENUM('pending','notified','cancelled') DEFAULT 'pending',
    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `notified_at` DATETIME     DEFAULT NULL,
    INDEX `idx_bis_product_status` (`product_id`, `status`),
    INDEX `idx_bis_email`         (`email`),
    UNIQUE KEY `uq_bis_product_email` (`product_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
