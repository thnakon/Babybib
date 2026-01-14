<?php

/**
 * Babybib API - Login
 * ====================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

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

try {
    $db = getDB();

    // Find user by username or email
    $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed attempt
        logActivity(null, 'login_failed', "Failed login attempt for: $login");
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    }

    // Check if email is verified (skip for admin users created before verification system)
    $isVerified = isset($user['is_verified']) ? $user['is_verified'] : 1;

    if (!$isVerified && $user['role'] !== 'admin') {
        // Generate new verification code for convenience
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Store new verification code
        $stmt = $db->prepare("
            INSERT INTO email_verifications (user_id, email, code, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user['id'], $user['email'], $verificationCode, $expiresAt]);

        // DEV MODE
        $devMode = true;

        $response = [
            'success' => false,
            'error' => 'กรุณายืนยันอีเมลก่อนเข้าสู่ระบบ',
            'requires_verification' => true,
            'email' => $user['email'],
            'redirect' => SITE_URL . '/verify-email.php?email=' . urlencode($user['email'])
        ];

        if ($devMode) {
            $response['verification_code'] = $verificationCode;
            $response['redirect'] .= '&code=' . $verificationCode;
        }

        jsonResponse($response, 403);
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
