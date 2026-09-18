-- ═══════════════════════════════════════════════════
-- Migration: Cascading Address Dropdown (Province/District/Ward)
-- ═══════════════════════════════════════════════════

-- Bảng tỉnh/thành phố
CREATE TABLE IF NOT EXISTS `vietnam_provinces` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `province_id` INT UNSIGNED NOT NULL COMMENT 'Mã tỉnh từ API',
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(120) NOT NULL DEFAULT '',
    `type`       VARCHAR(30)  NOT NULL DEFAULT 'tinh',
    UNIQUE KEY `idx_province_id` (`province_id`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng quận/huyện
CREATE TABLE IF NOT EXISTS `vietnam_districts` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `district_id` INT UNSIGNED NOT NULL COMMENT 'Mã quận từ API',
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL DEFAULT '',
    `type`        VARCHAR(30)  NOT NULL DEFAULT 'quan',
    `province_id` INT UNSIGNED NOT NULL,
    KEY `idx_province_id` (`province_id`),
    UNIQUE KEY `idx_district_id` (`district_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phường/xã
CREATE TABLE IF NOT EXISTS `vietnam_wards` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ward_id`   INT UNSIGNED NOT NULL COMMENT 'Mã phường từ API',
    `name`      VARCHAR(100) NOT NULL,
    `slug`      VARCHAR(120) NOT NULL DEFAULT '',
    `type`      VARCHAR(30)  NOT NULL DEFAULT 'phuong',
    `district_id` INT UNSIGNED NOT NULL,
    KEY `idx_district_id` (`district_id`),
    UNIQUE KEY `idx_ward_id` (`ward_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
