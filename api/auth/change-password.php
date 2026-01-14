<?php

/**
 * Babybib API - Change Password
 * ===============================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = getCurrentUserId();
$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

if (strlen($newPassword) < 8) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'], 400);
}

if (!preg_match('/[A-Z]/', $newPassword)) {
    jsonResponse(['success' => false, 'error' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว'], 400);
}

try {
    $db = getDB();

    // Verify current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        jsonResponse(['success' => false, 'error' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'], 401);
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);

    logActivity($userId, 'change_password', 'Changed password');

    jsonResponse([
        'success' => true,
        'message' => 'เปลี่ยนรหัสผ่านสำเร็จ'
    ]);
} catch (Exception $e) {
    error_log("Change password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
