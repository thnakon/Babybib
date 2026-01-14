<?php

/**
 * API: Update Bibliography Project
 * ================================
 * Assigns a bibliography to a specific project (folder)
 */

header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/session.php';

// Authentication check
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$bibId = $input['bib_id'] ?? null;
$projectId = $input['project_id'] ?? null;
$userId = $_SESSION['user_id'];
$db = getDB();

if (!$bibId) {
    echo json_encode(['success' => false, 'error' => 'Bibliography ID is required']);
    exit;
}

try {
    // 1. Verify bibliography ownership
    $stmt = $db->prepare("SELECT project_id FROM bibliographies WHERE id = ? AND user_id = ?");
    $stmt->execute([$bibId, $userId]);
    $oldBib = $stmt->fetch();

    if (!$oldBib) {
        echo json_encode(['success' => false, 'error' => 'Bibliography not found or access denied']);
        exit;
    }

    $oldProjectId = $oldBib['project_id'];

    // 2. Verify project ownership if ID is provided
    if ($projectId) {
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Project not found or access denied']);
            exit;
        }
    } else {
        $projectId = null;
    }

    // 3. Update bibliography project
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE bibliographies SET project_id = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $bibId, $userId]);

    // 4. Update project counts
    // Recalculating counts for accuracy (safer than increment/decrement)
    if ($oldProjectId) {
        $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ?) WHERE id = ?");
        $stmt->execute([$oldProjectId, $oldProjectId]);
    }
    if ($projectId) {
        $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ?) WHERE id = ?");
        $stmt->execute([$projectId, $projectId]);
    }
    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Update Project API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
