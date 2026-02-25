<?php

/**
 * Babybib API - Login
 * ====================
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/includes/session.php';
require_once dirname(__DIR__, 2) . '/includes/email-config.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$login = trim($input['login'] ?? '');
$password = $input['password'] ?? '';
$remember = $input['remember'] ?? false;

// Validate
if (empty($login) || empty($password)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

// Rate Limiting - Prevent brute force attacks
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'login_attempts_' . md5($ip);

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'first_attempt' => time()];
}

$rateLimit = &$_SESSION[$rateLimitKey];

// Reset after 15 minutes
if (time() - $rateLimit['first_attempt'] > 900) {
    $rateLimit = ['count' => 0, 'first_attempt' => time()];
}

// Block after 5 failed attempts
if ($rateLimit['count'] >= 5) {
    $remainingSeconds = 900 - (time() - $rateLimit['first_attempt']);
    $remainingMinutes = ceil($remainingSeconds / 60);
    jsonResponse([
        'success' => false,
        'error' => "เข้าสู่ระบบผิดพลาดหลายครั้ง กรุณารอ $remainingMinutes นาที"
    ], 429);
}

try {
    $db = getDB();

    // Find user by username or email
    $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Increment rate limit counter
        $rateLimit['count']++;

        // Log failed attempt
        logActivity(null, 'login_failed', "Failed login attempt for: $login (attempt {$rateLimit['count']})");
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    }

    // Reset rate limit on successful login
    unset($_SESSION[$rateLimitKey]);



    // Set session
    setUserSession($user);

    // Log successful login
    logActivity($user['id'], 'login', 'User logged in');

    // Remember me - extend session
    if ($remember) {
        // Set cookie for 30 days
        ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
        ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    }

    // Determine redirect URL based on role
    $redirect = $user['role'] === 'admin'
        ? SITE_URL . '/admin/index.php'
        : SITE_URL . '/users/dashboard.php';

    jsonResponse([
        'success' => true,
        'message' => 'เข้าสู่ระบบสำเร็จ',
        'redirect' => $redirect,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'] . ' ' . $user['surname'],
            'role' => $user['role']
        ]
    ]);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
