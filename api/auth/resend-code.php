<?php

/**
 * Babybib API - Resend Verification Code
 * ======================================
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

$userId = intval($input['user_id'] ?? 0);

if (empty($userId)) {
    jsonResponse(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน'], 400);
}

try {
    $db = getDB();

    // Get user email and name
    $stmt = $db->prepare("SELECT email, name, surname, is_verified FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบข้อมูลผู้ใช้'], 404);
    }

    if ($user['is_verified'] == 1) {
        jsonResponse(['success' => false, 'error' => 'บัญชีนี้ได้รับการยืนยันแล้ว'], 400);
    }

    // Generate new code
    $verificationCode = sprintf("%06d", mt_rand(1, 999999));

    // Mark previous codes as used/invalid helper 
    // (Optional, just insert new one which will be picked by DESC order)
    
    // Store new code
    $stmt = $db->prepare("INSERT INTO email_verifications (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
    $stmt->execute([$userId, $verificationCode]);

    // Send email
    $sent = sendVerificationEmail($user['email'], $verificationCode, $user['name'] . ' ' . $user['surname']);

    if ($sent) {
        jsonResponse(['success' => true, 'message' => 'ส่งรหัสยืนยันใหม่สำเร็จ']);
    } else {
        jsonResponse(['success' => false, 'error' => 'ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่'], 500);
    }

} catch (Exception $e) {
    error_log("Resend code error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
