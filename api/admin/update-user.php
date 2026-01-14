<?php

/**
 * Babybib API - Admin: Update User
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$isActive = isset($input['is_active']) ? intval($input['is_active']) : null;

if (!$id) jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);

try {
    $db = getDB();

    // Don't allow modifying admin user
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) jsonResponse(['success' => false, 'error' => 'User not found'], 404);
    if ($user['role'] === 'admin') jsonResponse(['success' => false, 'error' => 'Cannot modify admin'], 403);

    if ($isActive !== null) {
        $stmt = $db->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$isActive, $id]);
    }

    logActivity(getCurrentUserId(), 'update_user', "Updated user #$id");
    jsonResponse(['success' => true, 'message' => $isActive ? 'เปิดใช้งานผู้ใช้แล้ว' : 'ระงับผู้ใช้แล้ว']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => 'Error'], 500);
}
