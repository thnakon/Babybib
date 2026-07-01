-- Babybib production schema hardening
-- Apply manually during a maintenance window before enabling production traffic.
-- This migration replaces schema changes that were previously attempted during requests.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    code VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_verify_user (user_id),
    INDEX idx_email_verify_code (code),
    INDEX idx_email_verify_expires (expires_at),
    CONSTRAINT fk_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER surname',
        'SELECT ''users.profile_picture already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'profile_picture'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN is_lis_cmu TINYINT(1) DEFAULT 0 AFTER province',
        'SELECT ''users.is_lis_cmu already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'is_lis_cmu'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN student_id VARCHAR(20) DEFAULT NULL AFTER is_lis_cmu',
        'SELECT ''users.student_id already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'student_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active',
        'SELECT ''users.is_verified already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'is_verified'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL AFTER token',
        'SELECT ''users.token_expiry already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'token_expiry'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE email_verifications
    MODIFY COLUMN email VARCHAR(255) NULL,
    MODIFY COLUMN code VARCHAR(255) NOT NULL;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE email_verifications ADD COLUMN used TINYINT(1) NOT NULL DEFAULT 0 AFTER expires_at',
        'SELECT ''email_verifications.used already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'email_verifications'
      AND COLUMN_NAME = 'used'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE email_verifications ADD COLUMN verified_at DATETIME DEFAULT NULL AFTER used',
        'SELECT ''email_verifications.verified_at already exists'''
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'email_verifications'
      AND COLUMN_NAME = 'verified_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE INDEX idx_users_lis_cmu ON users(is_lis_cmu)',
        'SELECT ''idx_users_lis_cmu already exists'''
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'idx_users_lis_cmu'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
