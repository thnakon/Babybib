<?php

/**
 * Babybib API - Admin: Create User
 * ================================
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Require admin privileges
requireAdmin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

// Extract and sanitize fields
$username = trim($input['username'] ?? '');
$name = trim($input['name'] ?? '');
$surname = trim($input['surname'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$org_type = $input['org_type'] ?? 'personal';
$is_lis_cmu = isset($input['is_lis_cmu']) ? (int)$input['is_lis_cmu'] : 0;
$student_id = $is_lis_cmu ? trim($input['student_id'] ?? '') : null;

// Basic validation
if (empty($username) || empty($password) || empty($name) || empty($email)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (Username, Password, Name, Email)']);
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-0_]{3,20}$/', $username)) {
    jsonResponse(['success' => false, 'error' => 'Username ต้องเป็นภาษาอังกฤษหรือตัวเลข 3-20 ตัวอักษร']);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'error' => 'รูปแบบอีเมลไม่ถูกต้อง']);
}

try {
    $db = getDB();

    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว']);
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ถูกใช้งานแล้ว']);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO users (username, name, surname, email, password, org_type, is_lis_cmu, student_id, role, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user', 1, NOW())
    ");
    $stmt->execute([$username, $name, $surname, $email, $hashedPassword, $org_type, $is_lis_cmu, $student_id]);

    $newUserId = $db->lastInsertId();

    // Log the action
    logActivity(getCurrentUserId(), 'admin_create_user', "Created new user: $username (#$newUserId)");

    jsonResponse([
        'success' => true,
        'message' => 'เพิ่มผู้ใช้สำเร็จ',
        'user_id' => $newUserId
    ]);
} catch (Exception $e) {
    error_log("Admin create user error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาดทางเทคนิค: ' . $e->getMessage()], 500);
}
