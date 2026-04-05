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

try {
    $db = getDB();

    // Check DB rate limit
    $stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $attemptRecord = $stmt->fetch();
    
    if ($attemptRecord) {
        $lastAttemptTime = strtotime($attemptRecord['last_attempt']);
        $attempts = $attemptRecord['attempts'];
        
        // Block attempts logic
        $maxAttempts = (SITE_ENV === 'development') ? 10 : 5;
        $blockTime = (SITE_ENV === 'development') ? 60 : 900; // 1 min vs 15 min

        if ($attempts >= $maxAttempts) {
            $timePassed = time() - $lastAttemptTime;
            if ($timePassed < $blockTime) {
                $remainingSecs = $blockTime - $timePassed;
                $msg = (SITE_ENV === 'development') 
                     ? "Too many attempts. Wait $remainingSecs seconds (dev mode-relaxed)."
                     : "เข้าสู่ระบบผิดพลาดหลายครั้ง กรุณารอ " . ceil($remainingSecs / 60) . " นาที";
                
                jsonResponse([
                    'success' => false,
                    'error' => $msg
                ], 429);
            } else {
                // Reset after block time
                $db->prepare("UPDATE login_attempts SET attempts = 0 WHERE ip_address = ?")->execute([$ip]);
                $attempts = 0;
            }
        }
    } else {
        $attempts = 0;
    }

    // Find user by username or email
    $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Increment rate limit counter
        $newAttempts = $attempts + 1;
        if ($attemptRecord) {
            $db->prepare("UPDATE login_attempts SET attempts = ? WHERE ip_address = ?")->execute([$newAttempts, $ip]);
        } else {
            $db->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1)")->execute([$ip]);
        }

        // Log failed attempt
        logActivity(null, 'login_failed', "Failed login attempt for: $login (attempt {$newAttempts})");
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    }

    // Check if email verification is required and user is not verified
    if (EMAIL_VERIFICATION_ENABLED && $user['is_verified'] == 0 && $user['role'] !== 'admin') {
        jsonResponse([
            'success' => false,
            'error' => 'กรุณายืนยันอีเมลของคุณก่อนเข้าสู่ระบบ',
            'requires_verification' => true,
            'user_id' => $user['id'],
            'email' => $user['email']
        ], 403);
    }

    // Reset rate limit on successful login
    if ($attemptRecord) {
        $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
    }



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
