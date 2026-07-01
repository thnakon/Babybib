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
        // Log only non-secret metadata. Never write CSRF/session token values to logs.
        $hasSessionToken = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token']) && $_SESSION['csrf_token'] !== '';
        $hasHeaderToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) && trim((string) $_SERVER['HTTP_X_CSRF_TOKEN']) !== '';
        $sessionIdHash = session_id() !== '' ? substr(hash('sha256', session_id()), 0, 12) : 'none';
        error_log(sprintf('[CSRF_DEBUG] remote=%s session_id_hash=%s has_session_token=%s has_header_token=%s request_uri=%s',
            $_SERVER['REMOTE_ADDR'] ?? '-', $sessionIdHash, $hasSessionToken ? 'yes' : 'no', $hasHeaderToken ? 'yes' : 'no', $_SERVER['REQUEST_URI'] ?? '-'));
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
 * Check whether a table exists in the active database
 */
function databaseTableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?"
    );
    $stmt->execute([$table]);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Check whether a column exists in the active database
 */
function databaseColumnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Require tables and columns created by production migrations.
 */
function requireDatabaseSchema(PDO $db, array $requirements): void
{
    $missing = [];

    foreach ($requirements as $table => $columns) {
        if (!databaseTableExists($db, $table)) {
            $missing[] = $table;
            continue;
        }

        foreach ($columns as $column) {
            if (!databaseColumnExists($db, $table, $column)) {
                $missing[] = $table . '.' . $column;
            }
        }
    }

    if (!empty($missing)) {
        throw new RuntimeException(
            'Database schema is missing required objects: ' . implode(', ', $missing)
            . '. Apply database/migrations/20260701_001_production_schema_hardening.sql before serving traffic.'
        );
    }
}

/**
 * Ensure password reset columns exist without changing schema during requests
 */
function ensurePasswordResetSchema(PDO $db): void
{
    requireDatabaseSchema($db, [
        'users' => ['token', 'token_expiry'],
    ]);
}

/**
 * Ensure email verification storage matches runtime expectations without DDL
 */
function ensureEmailVerificationSchema(PDO $db): void
{
    requireDatabaseSchema($db, [
        'users' => ['is_verified'],
        'email_verifications' => ['user_id', 'email', 'code', 'expires_at', 'used', 'verified_at', 'created_at'],
    ]);
}

/**
 * Ensure registration profile fields exist without changing schema during requests
 */
function ensureRegistrationSchema(PDO $db): void
{
    requireDatabaseSchema($db, [
        'users' => ['is_lis_cmu', 'student_id', 'is_verified'],
    ]);
}

/**
 * Ensure profile picture storage exists without changing schema during requests
 */
function ensureProfilePictureSchema(PDO $db): void
{
    requireDatabaseSchema($db, [
        'users' => ['profile_picture'],
    ]);
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
