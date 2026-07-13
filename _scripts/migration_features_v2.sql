SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `loyalty_points` (
    `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT NOT NULL,
    `points`       INT NOT NULL,
    `type`         ENUM('earned','redeemed','adjusted','expired') NOT NULL DEFAULT 'earned',
    `ref_order_id` INT DEFAULT NULL,
    `note`         VARCHAR(255) DEFAULT NULL,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`user_id`),
    INDEX (`ref_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `serial_numbers` (
    `id`              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id`      INT NOT NULL,
    `serial`          VARCHAR(100) NOT NULL,
    `status`          ENUM('in_stock','sold','returned','defective') DEFAULT 'in_stock',
    `order_item_id`   INT DEFAULT NULL,
    `receipt_item_id` INT DEFAULT NULL,
    `note`            TEXT DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_serial` (`serial`),
    INDEX (`product_id`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shipping_orders` (
    `id`               INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id`         INT NOT NULL,
    `carrier`          VARCHAR(50) DEFAULT 'GHN',
    `tracking_code`    VARCHAR(100) DEFAULT NULL,
    `carrier_status`   VARCHAR(100) DEFAULT NULL,
    `shipping_fee`     DECIMAL(12,2) DEFAULT 0,
    `weight_gram`      INT DEFAULT 0,
    `estimated_date`   DATE DEFAULT NULL,
    `pickup_address`   TEXT DEFAULT NULL,
    `delivery_address` TEXT DEFAULT NULL,
    `note`             TEXT DEFAULT NULL,
    `created_by`       INT DEFAULT NULL,
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`order_id`),
    INDEX (`tracking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `shop_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('loyalty_rate',           '1',    'loyalty'),
('loyalty_redeem_rate',    '1000', 'loyalty'),
('loyalty_enabled',        '1',    'loyalty'),
('loyalty_min_redeem',     '50',   'loyalty'),
('loyalty_max_redeem_pct', '30',   'loyalty');
