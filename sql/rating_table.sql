-- ===================================
-- Babybib - User Rating Table
-- ===================================

CREATE TABLE IF NOT EXISTS `user_ratings` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NULL COMMENT 'NULL for guest users',
    `rating` TINYINT(1) NOT NULL COMMENT 'Rating 1-5 stars',
    `page_url` VARCHAR(255) NULL COMMENT 'Page where rating was given',
    `user_agent` VARCHAR(500) NULL,
    `ip_address` VARCHAR(45) NULL,
    `session_id` VARCHAR(128) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_rating` (`rating`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores user satisfaction ratings';
