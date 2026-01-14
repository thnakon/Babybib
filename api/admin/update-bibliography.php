<?php

/**
 * Babybib API - Admin Update Bibliography
 * =========================================
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

$bibId = intval($input['id']);
$resourceTypeId = intval($input['resource_type_id'] ?? 0);
$bibliographyText = $input['bibliography_text'] ?? '';
$citationParenthetical = sanitize($input['citation_parenthetical'] ?? '');
$citationNarrative = sanitize($input['citation_narrative'] ?? '');
$language = in_array($input['language'] ?? 'th', ['th', 'en']) ? $input['language'] : 'th';
$year = intval($input['year'] ?? 0);

// Validate
if (!$resourceTypeId) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเลือกประเภททรัพยากร'], 400);
}

if (empty($bibliographyText)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกบรรณานุกรม'], 400);
}

// Preserve italics
$bibliographyText = strip_tags($bibliographyText, '<i>');

try {
    $db = getDB();

    // Verify bibliography exists
    $stmt = $db->prepare("SELECT id FROM bibliographies WHERE id = ?");
    $stmt->execute([$bibId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'ไม่พบข้อมูลบรรณานุกรม'], 404);
    }

    // Update
    $stmt = $db->prepare("
        UPDATE bibliographies 
        SET resource_type_id = ?, 
            bibliography_text = ?, 
            citation_parenthetical = ?, 
            citation_narrative = ?, 
            language = ?, 
            year = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $resourceTypeId,
        $bibliographyText,
        $citationParenthetical,
        $citationNarrative,
        $language,
        $year,
        $bibId
    ]);

    if ($success) {
        logActivity(getCurrentUserId(), 'admin_update_bibliography', "Admin updated bibliography ID: $bibId", 'bibliography', $bibId);
        jsonResponse([
            'success' => true,
            'message' => 'อัปเดตบรรณานุกรมสำเร็จ'
        ]);
    } else {
        jsonResponse(['success' => false, 'error' => 'ไม่สามารถอัปเดตข้อมูลได้'], 500);
    }
} catch (Exception $e) {
    error_log("Admin Bibliography Update error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
