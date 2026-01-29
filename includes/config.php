<?php

/**
 * Babybib Database Configuration
 * ================================
 * All sensitive values are loaded from .env file
 */

// Load environment variables first
require_once __DIR__ . '/env.php';

// Environment mode
$isProduction = env('SITE_ENV', 'development') === 'production';
$debugMode = env('DEBUG_MODE', false);

// Error reporting based on environment
if ($isProduction && !$debugMode) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}
ini_set('log_errors', 1);

// Session configuration - only set if session not active
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', env('SESSION_COOKIE_SECURE', 0));
}

// Database configuration from .env
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'babybib_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Site Configuration
// Determine the base site URL dynamically (fallback if not in .env)
$envSiteUrl = env('SITE_URL');
if ($envSiteUrl) {
    define('SITE_URL', rtrim($envSiteUrl, '/'));
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseDir = str_replace(basename($_SERVER['SCRIPT_NAME'] ?? ''), '', $_SERVER['SCRIPT_NAME'] ?? '');
    $rootPath = explode('/', trim($baseDir, '/'));
    $projectDir = !empty($rootPath[0]) ? '/' . $rootPath[0] : '';
    $dynamicUrl = $protocol . $domainName . $projectDir;
    define('SITE_URL', rtrim($dynamicUrl, '/'));
}

define('SITE_NAME', env('SITE_NAME', 'Babybib'));
define('SITE_VERSION', '2.0.0');
define('SITE_ENV', env('SITE_ENV', 'development'));

// User limits
define('MAX_BIBLIOGRAPHIES', (int) env('MAX_BIBLIOGRAPHIES', 300));
define('MAX_PROJECTS', (int) env('MAX_PROJECTS', 30));

// Session timeout
define('SESSION_TIMEOUT', (int) env('SESSION_TIMEOUT', 600));

// Email Configuration (Managed via includes/email-config.php and Admin Settings)
/*
define('MAIL_ENABLED', env('MAIL_ENABLED', false));
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int) env('SMTP_PORT', 587));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'Babybib'));
define('SMTP_FROM_EMAIL', env('SMTP_FROM_EMAIL', ''));
*/

// Timezone
date_default_timezone_set(env('TIMEZONE', 'Asia/Bangkok'));

/**
 * Database connection using PDO
 */
function getDB()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode(['success' => false, 'error' => 'Database connection failed']));
        }
    }

    return $pdo;
}

/**
 * Sanitize input
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description = '', $entityType = null, $entityId = null)
{
    try {
        $db = getDB();

        // Auto-cleanup: Delete member logs older than 7 days
        // but keep admin logs and system logs forever as requested
        $db->exec("DELETE al FROM activity_logs al 
                   JOIN users u ON al.user_id = u.id 
                   WHERE u.role != 'admin' 
                   AND al.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, description, entity_type, entity_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $description,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
