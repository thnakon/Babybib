<?php

/**
 * Babybib API - Admin: Update Announcement
 * ========================================
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Require admin privileges
requireAdmin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if (!$id) {
    jsonResponse(['success' => false, 'error' => 'Invalid ID'], 400);
}

// Extract fields
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');
$isActive = isset($input['is_active']) ? intval($input['is_active']) : null;

try {
    $db = getDB();

    $updates = [];
    $params = [];

    if ($title !== '') {
        $updates[] = "title_th = ?";
        $updates[] = "title_en = ?";
        $params[] = $title;
        $params[] = $title;
    }

    if ($content !== '') {
        $updates[] = "content_th = ?";
        $updates[] = "content_en = ?";
        $params[] = $content;
        $params[] = $content;
    }

    if ($isActive !== null) {
        $updates[] = "is_active = ?";
        $params[] = $isActive;
    }

    if (empty($updates)) {
        jsonResponse(['success' => false, 'error' => 'ไม่มีข้อมูลสำหรับอัปเดต'], 400);
    }

    $sql = "UPDATE announcements SET " . implode(', ', $updates) . " WHERE id = ?";
    $params[] = $id;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logActivity(getCurrentUserId(), 'update_announcement', "Updated announcement #$id: " . ($title ?: 'Status only'));

    jsonResponse(['success' => true, 'message' => 'อัปเดตประกาศเรียบร้อยแล้ว']);
} catch (Exception $e) {
    error_log("Update announcement error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
