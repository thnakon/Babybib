<?php

/**
 * Babybib API - Admin Update Project
 * =====================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Require Admin
requireAdmin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['id'])) {
    jsonResponse(['success' => false, 'error' => 'ข้อมูลไม่ถูกต้อง'], 400);
}

$projectId = intval($input['id']);
$name = sanitize($input['name'] ?? '');
$description = sanitize($input['description'] ?? '');
$color = sanitize($input['color'] ?? '#8B5CF6');

// Validate
if (empty($name)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกชื่อโครงการ'], 400);
}

try {
    $db = getDB();

    // Verify project exists
    $stmt = $db->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบข้อมูลโครงการ'], 404);
    }

    // Update
    $stmt = $db->prepare("
        UPDATE projects 
        SET name = ?, 
            description = ?, 
            color = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $name,
        $description,
        $color,
        $projectId
    ]);

    if ($success) {
        logActivity(getCurrentUserId(), 'admin_update_project', "Admin updated project ID: $projectId ($name)", 'project', $projectId);
        jsonResponse([
            'success' => true,
            'message' => 'อัปเดตข้อมูลโครงการสำเร็จ'
        ]);
    } else {
        jsonResponse(['success' => false, 'error' => 'ไม่สามารถอัปเดตข้อมูลได้'], 500);
    }
} catch (Exception $e) {
    error_log("Admin Project Update error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
