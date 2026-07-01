<?php

/**
 * Babybib API - Delete Project
 * =============================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = getCurrentUserId();
$projectId = intval($input['id'] ?? 0);

if (!$projectId) {
    jsonResponse(['success' => false, 'error' => 'กรุณาระบุโครงการที่ต้องการลบ'], 400);
}

try {
    $db = getDB();
    $db->beginTransaction();

    // Check ownership
    $stmt = $db->prepare("SELECT id, name FROM projects WHERE id = ? AND user_id = ? FOR UPDATE");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        $db->rollBack();
        jsonResponse(['success' => false, 'error' => 'ไม่พบโครงการหรือไม่มีสิทธิ์ลบ'], 404);
    }

    // Unlink bibliographies from this project
    $stmt = $db->prepare("UPDATE bibliographies SET project_id = NULL WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);

    // Delete project
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);

    $stmt = $db->prepare("UPDATE users SET project_count = (SELECT COUNT(*) FROM projects WHERE user_id = ?) WHERE id = ?");
    $stmt->execute([$userId, $userId]);

    $db->commit();

    logActivity($userId, 'delete_project', "Deleted project: {$project['name']}", 'project', $projectId);

    jsonResponse([
        'success' => true,
        'message' => 'ลบโครงการสำเร็จ'
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Delete project error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
