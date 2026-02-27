<?php

/**
 * Babybib - Email Configuration
 * ==============================
 * Gmail SMTP Settings
 */

// Default values (Fallback) - Loaded from .env if available
$smtp_host = function_exists('env') ? env('SMTP_HOST', 'smtp.gmail.com') : 'smtp.gmail.com';
$smtp_port = function_exists('env') ? env('SMTP_PORT', 587) : 587;
$smtp_secure = 'tls';
$smtp_username = function_exists('env') ? env('SMTP_USER', 'your-email@gmail.com') : 'your-email@gmail.com';
$smtp_password = function_exists('env') ? env('SMTP_PASS', '') : '';
$email_from = function_exists('env') ? env('SMTP_FROM_EMAIL', $smtp_username) : $smtp_username;
$email_from_name = function_exists('env') ? env('SMTP_FROM_NAME', 'Babybib') : 'Babybib';

// Try to load from database if available
if (function_exists('getDB')) {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'smtp_password', 'email_from', 'email_from_name', 'email_verification_enabled')");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (!empty($results['smtp_host'])) $smtp_host = $results['smtp_host'];
        if (!empty($results['smtp_port'])) $smtp_port = $results['smtp_port'];
        if (!empty($results['smtp_secure'])) $smtp_secure = $results['smtp_secure'];
        if (!empty($results['smtp_username'])) $smtp_username = $results['smtp_username'];
        if (!empty($results['smtp_password'])) $smtp_password = $results['smtp_password'];
        if (!empty($results['email_from'])) $email_from = $results['email_from'];
        if (!empty($results['email_from_name'])) $email_from_name = $results['email_from_name'];
        if (isset($results['email_verification_enabled'])) $email_verification_enabled = $results['email_verification_enabled'] == '1';
    } catch (Exception $e) {
        // Fallback to defaults
    }
}

// SMTP Configuration
if (!defined('SMTP_HOST')) define('SMTP_HOST', $smtp_host);
if (!defined('SMTP_PORT')) define('SMTP_PORT', (int)$smtp_port);
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', $smtp_secure);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', $smtp_username);
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', $smtp_password);

// Email Settings
if (!defined('EMAIL_FROM')) define('EMAIL_FROM', $email_from ?: $smtp_username);
if (!defined('EMAIL_FROM_NAME')) define('EMAIL_FROM_NAME', $email_from_name);
if (!defined('EMAIL_VERIFICATION_ENABLED')) define('EMAIL_VERIFICATION_ENABLED', $email_verification_enabled ?? false);

// Dev Mode (set to false to send real emails)
if (!defined('EMAIL_DEV_MODE')) define('EMAIL_DEV_MODE', false);
