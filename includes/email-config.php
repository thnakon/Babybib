<?php

/**
 * Babybib - Email Configuration
 * ==============================
 * Gmail SMTP Settings
 */

// Default values (Fallback) - ⚠️ CHANGE THESE FOR PRODUCTION
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_secure = 'tls';
$smtp_username = 'your-email@gmail.com';        // ⚠️ Change this
$smtp_password = 'xxxx xxxx xxxx xxxx';          // ⚠️ Set via Admin Settings
$email_from = 'your-email@gmail.com';            // ⚠️ Change this
$email_from_name = 'Babybib';

// Try to load from database if available
if (function_exists('getDB')) {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'smtp_password', 'email_from', 'email_from_name')");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (isset($results['smtp_host'])) $smtp_host = $results['smtp_host'];
        if (isset($results['smtp_port'])) $smtp_port = $results['smtp_port'];
        if (isset($results['smtp_secure'])) $smtp_secure = $results['smtp_secure'];
        if (isset($results['smtp_username'])) $smtp_username = $results['smtp_username'];
        if (isset($results['smtp_password'])) $smtp_password = $results['smtp_password'];
        if (isset($results['email_from'])) $email_from = $results['email_from'];
        if (isset($results['email_from_name'])) $email_from_name = $results['email_from_name'];
    } catch (Exception $e) {
        // Fallback to hardcoded defaults
    }
}

// SMTP Configuration
define('SMTP_HOST', $smtp_host);
define('SMTP_PORT', $smtp_port);
define('SMTP_SECURE', $smtp_secure);
define('SMTP_USERNAME', $smtp_username);
define('SMTP_PASSWORD', $smtp_password);

// Email Settings
define('EMAIL_FROM', $email_from);
define('EMAIL_FROM_NAME', $email_from_name);

// Dev Mode (set to false to send real emails)
define('EMAIL_DEV_MODE', false);
