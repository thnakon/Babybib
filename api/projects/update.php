<?php

/**
 * Babybib - Update Project API
 * ============================
 */

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check authentication
requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

require_once '../../includes/security-utils.php';

$id = validateInt($input['id'] ?? null, 1);
$name = validateString($input['name'] ?? '', 1, 100);
$description = validateString($input['description'] ?? '', 0, 1000);
$color = sanitize($input['color'] ?? '#8B5CF6');

if (!$id) {
    jsonResponse(['success' => false, 'error' => 'Invalid project ID'], 400);
}
if ($name === false) {
    jsonResponse(['success' => false, 'error' => 'ชื่อโครงการไม่ถูกต้อง (ต้องการ 1-100 ตัวอักษร)'], 400);
}
if ($description === false) {
    jsonResponse(['success' => false, 'error' => 'คำอธิบายโครงการยาวเกินไป (สูงสุด 1000 ตัวอักษร)'], 400);
}

$name = sanitize($name);
$description = sanitize($description);

if (empty($name)) {
    jsonResponse(['success' => false, 'error' => 'Project name required'], 400);
}

// Validate color format
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
    $color = '#8B5CF6';
}

$userId = getCurrentUserId();

try {
    $db = getDB();

    // Check ownership
    $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);

    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Project not found'], 404);
    }

    // Update project
    $stmt = $db->prepare("
        UPDATE projects 
        SET name = ?, description = ?, color = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$name, $description, $color, $id, $userId]);

    logActivity($userId, 'update_project', "Updated project: $name", 'project', $id);

    jsonResponse([
        'success' => true,
        'message' => 'Project updated successfully'
    ]);
} catch (Exception $e) {
    error_log("Update project error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
