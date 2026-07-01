<?php

/**
 * Babybib API - Delete Bibliography
 * ===================================
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
$isAdmin = isAdmin();

// Single or bulk delete
$ids = isset($input['ids']) ? array_map('intval', $input['ids']) : (isset($input['id']) ? [intval($input['id'])] : []);

if (empty($ids)) {
    jsonResponse(['success' => false, 'error' => 'กรุณาระบุรายการที่ต้องการลบ'], 400);
}

try {
    $db = getDB();
    $db->beginTransaction();
    $deleted = 0;
    $affectedUserIds = [];
    $affectedProjectIds = [];

    foreach ($ids as $id) {
        // Check ownership (unless admin)
        $stmt = $db->prepare("SELECT user_id, project_id FROM bibliographies WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $bib = $stmt->fetch();

        if (!$bib) continue;
        if (!$isAdmin && $bib['user_id'] != $userId) continue;

        // Delete bibliography
        $stmt = $db->prepare("DELETE FROM bibliographies WHERE id = ?");
        $stmt->execute([$id]);

        $affectedUserIds[(int) $bib['user_id']] = true;
        if ($bib['project_id']) {
            $affectedProjectIds[(int) $bib['project_id']] = (int) $bib['user_id'];
        }

        $deleted++;
    }

    foreach (array_keys($affectedUserIds) as $affectedUserId) {
        $stmt = $db->prepare("UPDATE users SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE user_id = ?) WHERE id = ?");
        $stmt->execute([$affectedUserId, $affectedUserId]);
    }

    foreach ($affectedProjectIds as $affectedProjectId => $ownerId) {
        $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ? AND user_id = ?) WHERE id = ? AND user_id = ?");
        $stmt->execute([$affectedProjectId, $ownerId, $affectedProjectId, $ownerId]);
    }

    $db->commit();

    logActivity($userId, 'delete_bibliography', "Deleted $deleted bibliographies");

    jsonResponse([
        'success' => true,
        'message' => "ลบ $deleted รายการสำเร็จ",
        'deleted' => $deleted
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Delete bibliography error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
