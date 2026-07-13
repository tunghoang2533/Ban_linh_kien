-- =============================================================
-- Migration v5: Tính năng ưu tiên thấp
--  1. Dashboard Widgets Preferences (kéo thả tùy chỉnh)
--  2. Voucher cá nhân hóa (gán cho user cụ thể)
--  3. Audit UI trực quan (indexes + stats view helper)
-- =============================================================

-- ─── 1. Dashboard Widget Preferences ───────────────────────
CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `widget_key` VARCHAR(60) NOT NULL,
    `title`      VARCHAR(200) DEFAULT NULL,
    `enabled`    TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `settings`   TEXT DEFAULT NULL COMMENT 'JSON settings for this widget',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_widget` (`user_id`, `widget_key`),
    INDEX `idx_user_order` (`user_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Voucher cá nhân hóa ───────────────────────────────
ALTER TABLE `vouchers`
    ADD COLUMN IF NOT EXISTS `user_id` INT DEFAULT NULL AFTER `code`,
    ADD COLUMN IF NOT EXISTS `personal_note` VARCHAR(500) DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `sent_at` DATETIME DEFAULT NULL AFTER `expire_date`,
    ADD INDEX IF NOT EXISTS `idx_vouchers_user` (`user_id`);

-- ─── 3. Audit UI enhancements ─────────────────────────────
ALTER TABLE `audit_logs`
    ADD INDEX IF NOT EXISTS `idx_audit_created` (`created_at`),
    ADD INDEX IF NOT EXISTS `idx_audit_module_action` (`module`, `action`);
