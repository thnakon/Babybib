<?php

/**
 * Babybib API - Forgot Password (with 6-digit code)
 * ===================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/email-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$email = sanitize(trim($input['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกอีเมลที่ถูกต้อง'], 400);
}

try {
    $db = getDB();

    // Create password_resets table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            code VARCHAR(6) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_code (code),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Check if email exists
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Don't reveal if email exists for security
        jsonResponse([
            'success' => true,
            'message' => 'หากอีเมลนี้มีในระบบ คุณจะได้รับรหัสรีเซ็ตรหัสผ่าน'
        ]);
    }

    // Check rate limiting (max 3 requests per 10 minutes)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM password_resets 
        WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$user['id']]);
    $recentCount = $stmt->fetchColumn();

    if ($recentCount >= 3) {
        jsonResponse(['success' => false, 'error' => 'คุณขอรหัสบ่อยเกินไป กรุณารอสักครู่'], 429);
    }

    // Invalidate old codes
    $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL");
    $stmt->execute([$user['id']]);

    // Generate 6-digit code and secure token
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Store reset code
    $stmt = $db->prepare("
        INSERT INTO password_resets (user_id, email, code, token, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $email, $code, $token, $expiresAt]);

    // Log activity
    logActivity($user['id'], 'password_reset_request', 'Password reset requested');

    // Send password reset email
    $emailSent = sendPasswordResetEmail($email, $code, $user['name']);

    // Check if we should show code (DEV MODE or email failed)
    $showCode = defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE;

    $response = [
        'success' => true,
        'message' => $emailSent
            ? 'ส่งรหัสรีเซ็ตรหัสผ่านไปที่อีเมลของคุณแล้ว'
            : 'หากอีเมลนี้มีในระบบ คุณจะได้รับรหัสรีเซ็ตรหัสผ่าน',
        'email' => $email,
        'token' => $token,
        'email_sent' => $emailSent
    ];

    // Show code if DEV mode or email failed
    if ($showCode || !$emailSent) {
        $response['reset_code'] = $code;
        $response['expires_in'] = '15 minutes';
    }

    jsonResponse($response);
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
