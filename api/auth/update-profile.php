<?php

/**
 * Babybib API - Update Profile
 * ==============================
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

$username = sanitize(trim($input['username'] ?? ''));
$name = sanitize(trim($input['name'] ?? ''));
$surname = sanitize(trim($input['surname'] ?? ''));
$email = sanitize(trim($input['email'] ?? ''));
$orgType = sanitize($input['org_type'] ?? '');
$orgName = sanitize(trim($input['org_name'] ?? ''));
$province = sanitize(trim($input['province'] ?? ''));

if (empty($name) || empty($surname) || empty($email)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'error' => 'รูปแบบอีเมลไม่ถูกต้อง'], 400);
}

// Validate username if provided
if (!empty($username)) {
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้ต้องมี 3-20 ตัวอักษร (a-z, 0-9, _)'], 400);
    }
}

try {
    $db = getDB();

    // Check if email already used by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ถูกใช้งานแล้ว'], 409);
    }

    // Check if username already used by another user
    if (!empty($username)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว'], 409);
        }
    }

    // Update user
    if (!empty($username)) {
        $stmt = $db->prepare("
            UPDATE users SET 
                username = ?, name = ?, surname = ?, email = ?, 
                org_type = ?, org_name = ?, province = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$username, $name, $surname, $email, $orgType, $orgName, $province, $userId]);
        $_SESSION['username'] = $username;
    } else {
        $stmt = $db->prepare("
            UPDATE users SET 
                name = ?, surname = ?, email = ?, 
                org_type = ?, org_name = ?, province = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$name, $surname, $email, $orgType, $orgName, $province, $userId]);
    }

    // Update session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_surname'] = $surname;
    $_SESSION['user_email'] = $email;

    logActivity($userId, 'update_profile', 'Updated profile');

    jsonResponse([
        'success' => true,
        'message' => 'บันทึกข้อมูลสำเร็จ'
    ]);
} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
