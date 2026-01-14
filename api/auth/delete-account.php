<?php

/**
 * Babybib API - Delete Account
 * =============================
 * Permanently delete user account after password verification
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check if logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเข้าสู่ระบบ'], 401);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$password = $input['password'] ?? '';

if (empty($password)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกรหัสผ่าน'], 400);
}

try {
    $db = getDB();
    $userId = getCurrentUserId();

    // Get current user's password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบบัญชีผู้ใช้'], 404);
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        jsonResponse(['success' => false, 'error' => 'รหัสผ่านไม่ถูกต้อง'], 403);
    }

    // Begin transaction
    $db->beginTransaction();

    try {
        // Delete user's bibliographies
        $stmt = $db->prepare("DELETE FROM bibliographies WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete user's projects
        $stmt = $db->prepare("DELETE FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete user's activity logs
        $stmt = $db->prepare("DELETE FROM activity_logs WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete the user account
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        // Commit transaction
        $db->commit();

        // Destroy session
        session_destroy();

        jsonResponse([
            'success' => true,
            'message' => 'ลบบัญชีสำเร็จ'
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    error_log("Delete account error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
