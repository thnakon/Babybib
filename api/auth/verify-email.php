<?php

/**
 * Babybib API - Verify Email
 * ===========================
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

$email = sanitize(trim($input['email'] ?? ''));
$code = sanitize(trim($input['code'] ?? ''));

if (empty($email) || empty($code)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกอีเมลและรหัสยืนยัน'], 400);
}

try {
    $db = getDB();

    // Find verification record
    $stmt = $db->prepare("
        SELECT ev.*, u.id as user_id, u.name, u.is_verified 
        FROM email_verifications ev
        JOIN users u ON ev.user_id = u.id
        WHERE ev.email = ? AND ev.code = ? AND ev.verified_at IS NULL
        ORDER BY ev.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    $verification = $stmt->fetch();

    if (!$verification) {
        jsonResponse(['success' => false, 'error' => 'รหัสยืนยันไม่ถูกต้อง'], 400);
    }

    // Check if already verified
    if ($verification['is_verified']) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ได้รับการยืนยันแล้ว'], 400);
    }

    // Check if expired
    if (strtotime($verification['expires_at']) < time()) {
        jsonResponse(['success' => false, 'error' => 'รหัสยืนยันหมดอายุแล้ว กรุณาขอรหัสใหม่'], 400);
    }

    // Mark verification as used
    $stmt = $db->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE id = ?");
    $stmt->execute([$verification['id']]);

    // Update user as verified
    $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$verification['user_id']]);

    // Log activity
    logActivity($verification['user_id'], 'email_verified', 'Email verified successfully');

    jsonResponse([
        'success' => true,
        'message' => 'ยืนยันอีเมลสำเร็จ! คุณสามารถเข้าสู่ระบบได้แล้ว',
        'user_name' => $verification['name']
    ]);
} catch (Exception $e) {
    error_log("Email verification error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
