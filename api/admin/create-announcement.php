<?php

/**
 * Babybib API - Admin: Create Announcement
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$title = sanitize(trim($input['title'] ?? ''));
$content = sanitize(trim($input['content'] ?? ''));
$isActive = intval($input['is_active'] ?? 1);

if (empty($title) || empty($content)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO announcements (admin_id, title_th, title_en, content_th, content_en, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([getCurrentUserId(), $title, $title, $content, $content, $isActive]);
    logActivity(getCurrentUserId(), 'create_announcement', "Created announcement: $title");
    jsonResponse(['success' => true, 'message' => 'สร้างประกาศสำเร็จ']);
} catch (Exception $e) {
    error_log("Create announcement error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
