<?php

/**
 * Babybib API - Admin: Delete User
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if (!$id) jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);

try {
    $db = getDB();

    // Don't allow deleting admin user
    $stmt = $db->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) jsonResponse(['success' => false, 'error' => 'User not found'], 404);
    if ($user['role'] === 'admin') jsonResponse(['success' => false, 'error' => 'Cannot delete admin'], 403);

    // Delete user's data
    $db->prepare("DELETE FROM bibliographies WHERE user_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM projects WHERE user_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM feedback WHERE user_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM activity_logs WHERE user_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

    logActivity(getCurrentUserId(), 'delete_user', "Deleted user: {$user['username']}");
    jsonResponse(['success' => true, 'message' => 'ลบผู้ใช้สำเร็จ']);
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Error'], 500);
}
