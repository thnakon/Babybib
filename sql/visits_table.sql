-- ===================================
-- Babybib - Page Visits Table
-- ===================================

CREATE TABLE IF NOT EXISTS `page_visits` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `visit_date` DATE NOT NULL,
    `visit_count` INT(11) UNSIGNED DEFAULT 1,
    `unique_visitors` INT(11) UNSIGNED DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `idx_visit_date` (`visit_date`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores daily page visit counts';
