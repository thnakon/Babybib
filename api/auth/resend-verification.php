<?php

/**
 * Babybib API - Resend Verification Code
 * =======================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/email-helper.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$email = sanitize(trim($input['email'] ?? ''));

if (empty($email)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกอีเมล'], 400);
}

try {
    $db = getDB();

    // Find user by email
    $stmt = $db->prepare("SELECT id, name, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบอีเมลนี้ในระบบ'], 404);
    }

    if ($user['is_verified']) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ได้รับการยืนยันแล้ว'], 400);
    }

    // Check rate limiting (max 3 requests per 10 minutes)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM email_verifications 
        WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$user['id']]);
    $recentCount = $stmt->fetchColumn();

    if ($recentCount >= 3) {
        jsonResponse(['success' => false, 'error' => 'คุณขอรหัสบ่อยเกินไป กรุณารอสักครู่'], 429);
    }

    // Invalidate old codes
    $stmt = $db->prepare("DELETE FROM email_verifications WHERE user_id = ? AND verified_at IS NULL");
    $stmt->execute([$user['id']]);

    // Generate new 6-digit verification code
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Store verification code
    $stmt = $db->prepare("
        INSERT INTO email_verifications (user_id, email, code, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $email, $verificationCode, $expiresAt]);

    // Log activity
    logActivity($user['id'], 'resend_verification', 'Verification code resent');

    // Send verification email
    $emailSent = sendVerificationEmail($email, $verificationCode, $user['name']);

    // Check if we should show code (DEV MODE or email failed)
    $showCode = defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE;

    $response = [
        'success' => true,
        'message' => $emailSent
            ? 'ส่งรหัสยืนยันใหม่ไปที่อีเมลของคุณแล้ว'
            : 'ส่งรหัสยืนยันใหม่แล้ว',
        'expires_in' => '15 minutes',
        'email_sent' => $emailSent
    ];

    // Show code if DEV mode or email failed
    if ($showCode || !$emailSent) {
        $response['verification_code'] = $verificationCode;
    }

    jsonResponse($response);
} catch (Exception $e) {
    error_log("Resend verification error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
