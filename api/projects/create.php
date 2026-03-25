<?php

/**
 * Babybib API - Create Project
 * =============================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = getCurrentUserId();

// Check limits
if (!canCreateProject($userId)) {
    jsonResponse(['success' => false, 'error' => 'คุณสร้างโครงการถึงขีดจำกัดแล้ว (30 โครงการ)'], 403);
}

require_once '../../includes/security-utils.php';

$name = validateString($input['name'] ?? '', 1, 100);
$description = validateString($input['description'] ?? '', 0, 1000);
$color = sanitize($input['color'] ?? '#8B5CF6');

if ($name === false) {
    jsonResponse(['success' => false, 'error' => 'ชื่อโครงการไม่ถูกต้อง (ต้องการ 1-100 ตัวอักษร)'], 400);
}
if ($description === false) {
    jsonResponse(['success' => false, 'error' => 'คำอธิบายโครงการยาวเกินไป (สูงสุด 1000 ตัวอักษร)'], 400);
}

$name = sanitize($name);
$description = sanitize($description);

// Validate color format
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
    $color = '#8B5CF6';
}

try {
    $db = getDB();

    $stmt = $db->prepare("INSERT INTO projects (user_id, name, description, color, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $name, $description, $color]);

    $projectId = $db->lastInsertId();

    // Update user project count
    $stmt = $db->prepare("UPDATE users SET project_count = project_count + 1 WHERE id = ?");
    $stmt->execute([$userId]);

    logActivity($userId, 'create_project', "Created project: $name", 'project', $projectId);

    jsonResponse([
        'success' => true,
        'message' => 'สร้างโครงการสำเร็จ',
        'data' => ['id' => $projectId]
    ]);
} catch (Exception $e) {
    error_log("Create project error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
