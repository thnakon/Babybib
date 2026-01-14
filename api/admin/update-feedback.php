<?php

/**
 * Babybib API - Admin: Update Feedback Status
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$status = sanitize($input['status'] ?? '');

if (!$id || !in_array($status, ['pending', 'read', 'resolved'])) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE feedback SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    logActivity(getCurrentUserId(), 'update_feedback', "Updated feedback #$id to $status");
    jsonResponse(['success' => true, 'message' => 'อัปเดตสำเร็จ']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
