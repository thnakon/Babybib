<?php

/**
 * Babybib API - Admin: Delete Announcement
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if (!$id) jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);

try {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    logActivity(getCurrentUserId(), 'delete_announcement', "Deleted announcement #$id");
    jsonResponse(['success' => true, 'message' => 'ลบสำเร็จ']);
} catch (Exception $e) {
    error_log("Delete announcement error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
}
