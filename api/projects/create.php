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

$name = sanitize(trim($input['name'] ?? ''));
$description = sanitize(trim($input['description'] ?? ''));
$color = sanitize($input['color'] ?? '#8B5CF6');

if (empty($name)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกชื่อโครงการ'], 400);
}

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
