<?php
/**
 * Database fix for email verification
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    $db = getDB();
    
    // Add 'used' column to email_verifications
    $check = $db->query("SHOW COLUMNS FROM email_verifications LIKE 'used'");
    if ($check->rowCount() === 0) {
        $db->exec("ALTER TABLE email_verifications ADD COLUMN used TINYINT(1) DEFAULT 0 AFTER expires_at");
        echo "Added 'used' column to email_verifications\n";
    }

    // Make 'email' column nullable in email_verifications since we already have user_id
    $db->exec("ALTER TABLE email_verifications MODIFY COLUMN email VARCHAR(255) NULL");
    echo "Made 'email' column nullable in email_verifications\n";

    // Re-check users table for is_verified (just in case)
    $checkUser = $db->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if ($checkUser->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active");
        echo "Added 'is_verified' column to users\n";
    }

    // Ensure token_expiry exists in users (for forgot password)
    $checkExpiry = $db->query("SHOW COLUMNS FROM users LIKE 'token_expiry'");
    if ($checkExpiry->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN token_expiry DATETIME DEFAULT NULL AFTER token");
        echo "Added 'token_expiry' column to users\n";
    }

    echo "Database schema updated successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
