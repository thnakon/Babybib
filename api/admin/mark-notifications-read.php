<?php

/**
 * Babybib API - Admin: Mark Notifications as Read
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

if (!isAdmin()) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

try {
    $db = getDB();

    // Mark all admin_notifications as read
    $db->exec("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");

    // Mark all pending feedback as read (change status to 'read')
    $db->exec("UPDATE feedback SET status = 'read' WHERE status = 'pending'");

    logActivity(getCurrentUserId(), 'mark_notifications_read', 'Marked all notifications as read');

    jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
} catch (Exception $e) {
    error_log("Mark notifications error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
