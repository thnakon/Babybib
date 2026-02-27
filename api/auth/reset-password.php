<?php

/**
 * Babybib API - Reset Password
 * ============================
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

$token = trim($input['token'] ?? '');
$password = $input['password'] ?? '';

if (empty($token) || empty($password)) {
    jsonResponse(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน'], 400);
}

if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'], 400);
}

try {
    $db = getDB();

    // Verify token
    $stmt = $db->prepare("SELECT id FROM users WHERE token = ? AND token_expiry > NOW() AND is_active = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'ลิงก์หมดอายุหรือไมถูกต้อง'], 400);
    }

    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update password and clear token
    $stmt = $db->prepare("UPDATE users SET password = ?, token = NULL, token_expiry = NULL WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    // Log activity
    logActivity($user['id'], 'reset_password', 'User reset password successfully');

    jsonResponse(['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
