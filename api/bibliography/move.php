<?php

/**
 * Babybib API - Move Bibliographies to Project
 * =============================================
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

// Get IDs array
$ids = isset($input['ids']) ? array_map('intval', $input['ids']) : [];
$projectId = $input['project_id'] ?? null;

// Handle empty string as null
if ($projectId === '' || $projectId === 'null' || $projectId === null) {
    $projectId = null;
} else {
    $projectId = intval($projectId);
}

if (empty($ids)) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเลือกรายการที่ต้องการย้าย'], 400);
}

// Filter out invalid IDs
$ids = array_filter($ids, function ($id) {
    return $id > 0;
});

if (empty($ids)) {
    jsonResponse(['success' => false, 'error' => 'ไม่พบรายการที่ถูกต้อง'], 400);
}

try {
    $db = getDB();

    // Validate project ownership if project_id is provided
    if ($projectId !== null && $projectId > 0) {
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'error' => 'ไม่พบโครงการ'], 400);
        }
    }

    // Update bibliographies one by one to ensure ownership
    $moved = 0;
    foreach ($ids as $id) {
        $stmt = $db->prepare("UPDATE bibliographies SET project_id = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $id, $userId]);
        $moved += $stmt->rowCount();
    }

    logActivity($userId, 'move_bibliography', "Moved $moved bibliographies to project " . ($projectId ?: 'none'));

    jsonResponse([
        'success' => true,
        'message' => "ย้าย $moved รายการสำเร็จ",
        'affected' => $moved
    ]);
} catch (Exception $e) {
    error_log("Move bibliography error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
