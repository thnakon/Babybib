<?php

/**
 * Babybib API - User: Update Profile
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

try {
    $db = getDB();

    // Validate required fields
    $name = sanitize($input['name'] ?? '');
    $surname = sanitize($input['surname'] ?? '');
    $email = sanitize($input['email'] ?? '');

    if (empty($name) || empty($surname) || empty($email)) {
        jsonResponse(['success' => false, 'error' => 'Name, surname and email are required'], 400);
    }

    // Check if email is already used by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Email already in use'], 400);
    }

    // Build update query
    $updateFields = [
        'name = ?',
        'surname = ?',
        'email = ?',
        'province = ?',
        'org_type = ?',
        'org_name = ?'
    ];

    $params = [
        $name,
        $surname,
        $email,
        sanitize($input['province'] ?? ''),
        sanitize($input['org_type'] ?? ''),
        sanitize($input['org_name'] ?? '')
    ];

    // Handle password change
    if (!empty($input['current_password']) && !empty($input['new_password'])) {
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!password_verify($input['current_password'], $user['password'])) {
            jsonResponse(['success' => false, 'error' => 'Current password is incorrect'], 400);
        }

        // Validate new password
        if (strlen($input['new_password']) < 8) {
            jsonResponse(['success' => false, 'error' => 'Password must be at least 8 characters'], 400);
        }

        $updateFields[] = 'password = ?';
        $params[] = password_hash($input['new_password'], PASSWORD_DEFAULT);
    }

    $params[] = $userId;

    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logActivity($userId, 'update_profile', 'Updated profile information');

    jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
