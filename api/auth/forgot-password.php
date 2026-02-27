<?php

/**
 * Babybib API - Forgot Password
 * ==============================
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

$email = trim($input['email'] ?? '');

if (empty($email)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกอีเมล'], 400);
}

try {
    $db = getDB();

    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, surname FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // For security, success message even if email not found
        // but here we might want to be helpful since it's a bib generator
        jsonResponse(['success' => true, 'message' => 'หากอีเมลนี้อยู่ในระบบ เราได้ส่งลิงก์ไปให้แล้ว']);
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));
    
    // Update user with token and expiry (15 mins)
    $stmt = $db->prepare("UPDATE users SET token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
    $stmt->execute([$token, $user['id']]);

    // Send email
    $sent = sendPasswordResetLinkEmail($email, $token, $user['name'] . ' ' . $user['surname']);

    if ($sent) {
        jsonResponse(['success' => true, 'message' => 'ส่งลิงก์รีเซ็ตรหัสผ่านสำเร็จ']);
    } else {
        jsonResponse(['success' => false, 'error' => 'ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่']);
    }

} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
