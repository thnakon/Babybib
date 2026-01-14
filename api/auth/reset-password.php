<?php

/**
 * Babybib API - Reset Password (with 6-digit code)
 * ==================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$email = sanitize(trim($input['email'] ?? ''));
$code = sanitize(trim($input['code'] ?? ''));
$token = sanitize(trim($input['token'] ?? ''));
$newPassword = $input['new_password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Validation
if (empty($email) || empty($code) || empty($newPassword)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

if (strlen($newPassword) < 8) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'], 400);
}

if (!preg_match('/[A-Z]/', $newPassword)) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว'], 400);
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านไม่ตรงกัน'], 400);
}

try {
    $db = getDB();

    // Find valid reset record
    $stmt = $db->prepare("
        SELECT pr.*, u.id as user_id, u.name 
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.email = ? AND pr.code = ? AND pr.used_at IS NULL
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    $reset = $stmt->fetch();

    if (!$reset) {
        jsonResponse(['success' => false, 'error' => 'รหัสยืนยันไม่ถูกต้อง'], 400);
    }

    // Check if expired
    if (strtotime($reset['expires_at']) < time()) {
        jsonResponse(['success' => false, 'error' => 'รหัสยืนยันหมดอายุแล้ว กรุณาขอรหัสใหม่'], 400);
    }

    // Optional: Verify token for extra security
    if (!empty($token) && $reset['token'] !== $token) {
        jsonResponse(['success' => false, 'error' => 'ข้อมูลไม่ถูกต้อง'], 400);
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $reset['user_id']]);

    // Mark reset as used
    $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$reset['id']]);

    // Invalidate all other reset codes for this user
    $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ? AND id != ?");
    $stmt->execute([$reset['user_id'], $reset['id']]);

    // Log activity
    logActivity($reset['user_id'], 'password_reset', 'Password reset successfully');

    jsonResponse([
        'success' => true,
        'message' => 'เปลี่ยนรหัสผ่านสำเร็จ! คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว'
    ]);
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
