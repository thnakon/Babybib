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

// Session configuration - apply before session_start()
if (session_status() === PHP_SESSION_NONE) {
    $isHttpsRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
    $cookieSecure = env('SESSION_COOKIE_SECURE', $isHttpsRequest ? 1 : 0) ? true : false;

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', $cookieSecure ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');

    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'] ?? 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
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
define('APP_KEY', env('APP_KEY', 'babybib-change-this-app-key'));

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

// Google Books API Keys (Array for rotation to prevent Rate Limits)
define('GOOGLE_BOOKS_API_KEYS', [
    // สามารถใส่ Key เพิ่มเติมใน Array นี้ได้ในอนาคต (เว้นว่างไว้ 1 ตัวเพื่อไม่ให้โค้ดพังเวลาไม่มี Key)
    env('GOOGLE_BOOKS_API_KEY_1', ''),
    env('GOOGLE_BOOKS_API_KEY_2', ''),
    env('GOOGLE_BOOKS_API_KEY_3', '')
]);

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
 * Get CSRF token from request headers or form payload
 */
function getRequestCSRFToken()
{
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return trim((string) $_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    if (!empty($_POST['csrf_token'])) {
        return trim((string) $_POST['csrf_token']);
    }

    if (!empty($_POST['_csrf'])) {
        return trim((string) $_POST['_csrf']);
    }

    return '';
}

/**
 * Require a valid CSRF token for state-changing requests
 */
function requireValidCSRFToken()
{
    $token = getRequestCSRFToken();
    if ($token === '' || !verifyCSRFToken($token)) {
        jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 419);
    }
}

/**
 * Deterministic token hashing for reset and verification flows
 */
function hashSensitiveToken($token)
{
    return hash_hmac('sha256', (string) $token, APP_KEY);
}

/**
 * Compare a raw secret against hashed storage while supporting legacy plaintext rows
 */
function matchesStoredSecret($rawSecret, $storedSecret)
{
    if ($storedSecret === null || $storedSecret === '') {
        return false;
    }

    $rawSecret = (string) $rawSecret;
    $storedSecret = (string) $storedSecret;

    return hash_equals(hashSensitiveToken($rawSecret), $storedSecret)
        || hash_equals($rawSecret, $storedSecret);
}

/**
 * Ensure password reset columns exist on legacy databases
 */
function ensurePasswordResetSchema(PDO $db)
{
    try {
        $columnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'token_expiry'");
        if ($columnCheck->rowCount() === 0) {
            $db->exec("ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL AFTER token");
        }
    } catch (Exception $e) {
        error_log("Failed to ensure password reset schema: " . $e->getMessage());
    }
}

/**
 * Ensure email verification storage matches runtime expectations on legacy databases
 */
function ensureEmailVerificationSchema(PDO $db)
{
    try {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS email_verifications (
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
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $columnCheck = $db->query("SHOW COLUMNS FROM email_verifications LIKE 'email'");
        if ($columnCheck->rowCount() === 0) {
            $db->exec("ALTER TABLE email_verifications ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER user_id");
        }

        try {
            $db->exec("ALTER TABLE email_verifications MODIFY COLUMN email VARCHAR(255) NULL");
        } catch (Exception $e) {
        }

        try {
            $db->exec("ALTER TABLE email_verifications MODIFY COLUMN code VARCHAR(255) NOT NULL");
        } catch (Exception $e) {
        }

        $columnCheck = $db->query("SHOW COLUMNS FROM email_verifications LIKE 'used'");
        if ($columnCheck->rowCount() === 0) {
            $db->exec("ALTER TABLE email_verifications ADD COLUMN used TINYINT(1) NOT NULL DEFAULT 0 AFTER expires_at");
        }

        $columnCheck = $db->query("SHOW COLUMNS FROM email_verifications LIKE 'verified_at'");
        if ($columnCheck->rowCount() === 0) {
            $db->exec("ALTER TABLE email_verifications ADD COLUMN verified_at DATETIME DEFAULT NULL AFTER used");
        }
    } catch (Exception $e) {
        error_log("Failed to ensure email verification schema: " . $e->getMessage());
    }
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

/**
 * Get system setting from database
 */
function getSystemSetting($key, $default = null)
{
    static $settings = null;

    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settings = [];
            if ($rows) {
                foreach ($rows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}
