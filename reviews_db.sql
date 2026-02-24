-- ============================================================
-- GB Laser Soldering — Reviews Table
-- Run this SQL once in Hostinger phpMyAdmin or MySQL terminal
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gb_reviews_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `gb_reviews_db`;

CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)     NOT NULL,
    `service`     VARCHAR(100)     NOT NULL DEFAULT 'General',
    `rating`      TINYINT(1)       NOT NULL DEFAULT 5 CHECK (`rating` BETWEEN 1 AND 5),
    `review_text` TEXT             NOT NULL,
    `is_approved` TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '0=pending, 1=approved',
    `ip_address`  VARCHAR(45)      DEFAULT NULL COMMENT 'for spam prevention',
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_approved`    (`is_approved`),
    KEY `idx_created_at`  (`created_at`),
    KEY `idx_rating`      (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Optional: seed a few sample approved reviews so the page
-- isn't empty the first time you open the site.
-- ============================================================
INSERT INTO `reviews` (`name`, `service`, `rating`, `review_text`, `is_approved`) VALUES
('Ramesh K.',  'Laser Gold Soldering',   5, 'Got my gold chain repaired here. The laser soldering was perfect — you can''t even tell where the break was. Very professional service and done within an hour!', 1),
('Priya S.',   'Laser Jewelry Repairs',  5, 'I had my mangalsutra repaired. They were very careful with the stones. Excellent laser work — the knotted area is strong and clean. Highly recommended!', 1),
('Suresh M.',  'NG Gold Testing',        4, 'Used the NG gold testing service before buying old gold jewellery. The machine reading was accurate and the report was clear. Saved me from a bad deal. Very trustworthy!', 1),
('Anita R.',   'Precision Stone Setting',5, 'Diamond ring stone setting came loose. Gautam bhai fixed it with laser without removing the stone — no damage at all! Work is clean, price is fair. Best in Vijayawada.', 1);
