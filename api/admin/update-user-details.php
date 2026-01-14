<?php

/**
 * Babybib API - Admin: Update User Full Details
 * ============================================
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

if (!$input || empty($input['id'])) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$id = intval($input['id']);
$username = trim($input['username'] ?? '');
$name = trim($input['name'] ?? '');
$surname = trim($input['surname'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? ''; // Optional password update
$org_type = $input['org_type'] ?? 'personal';
$is_lis_cmu = isset($input['is_lis_cmu']) ? (int)$input['is_lis_cmu'] : 0;

// Basic validation
if (empty($username) || empty($name) || empty($email)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (Username, Name, Email)']);
}

try {
    $db = getDB();

    // Check existing mapping
    $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบผู้ใช้งาน']);
    }

    if ($user['role'] === 'admin') {
        jsonResponse(['success' => false, 'error' => 'ไม่สามารถแก้ไขข้อมูลผู้ดูแลระบบได้ที่นี่']);
    }

    // Check if username already exists for OTHER users
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว']);
    }

    // Check if email already exists for OTHER users
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ถูกใช้งานแล้ว']);
    }

    // Update query
    $sql = "UPDATE users SET username = ?, name = ?, surname = ?, email = ?, org_type = ?, is_lis_cmu = ?, updated_at = NOW()";
    $params = [$username, $name, $surname, $email, $org_type, $is_lis_cmu];

    // If password is provided, hash and update it
    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Log the action
    logActivity(getCurrentUserId(), 'admin_update_user', "Updated full details for user: $username (#$id)");

    jsonResponse([
        'success' => true,
        'message' => 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว'
    ]);
} catch (Exception $e) {
    error_log("Admin update user full error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
