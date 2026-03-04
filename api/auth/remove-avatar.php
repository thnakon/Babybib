<?php

/**
 * Babybib API - Remove Profile Picture
 * =====================================
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

$userId = getCurrentUserId();

try {
    $db = getDB();

    // Get old avatar to delete
    $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldAvatar = $stmt->fetchColumn();

    if ($oldAvatar) {
        $uploadDir = dirname(dirname(__DIR__)) . '/uploads/avatars/';
        if (file_exists($uploadDir . $oldAvatar)) {
            unlink($uploadDir . $oldAvatar);
        }

        // Update database
        $stmt = $db->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        $stmt->execute([$userId]);

        // Update session
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['profile_picture'] = null;
        }

        jsonResponse([
            'success' => true,
            'message' => 'นำรูปโปรไฟล์ออกสำเร็จ'
        ]);
    } else {
        jsonResponse([
            'success' => true,
            'message' => 'ไม่มีรูปโปรไฟล์ให้ลบ'
        ]);
    }
} catch (Exception $e) {
    error_log("Avatar removal error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
