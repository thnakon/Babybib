<?php

/**
 * Babybib API - Admin: Get Notifications
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if (!isAdmin()) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

try {
    $db = getDB();

    // Get unread notification count from admin_notifications table
    $notifCount = $db->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0")->fetchColumn();

    // Also count pending feedback as notifications
    $feedbackCount = $db->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();

    $totalCount = (int)$notifCount + (int)$feedbackCount;

    // Get recent notifications
    $notifications = [];

    if ($_GET['action'] ?? '' === 'list') {
        $stmt = $db->query("
            SELECT * FROM admin_notifications 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $notifications = $stmt->fetchAll();

        // Add pending feedback as notifications
        $feedbackStmt = $db->query("
            SELECT 
                id, 
                'feedback' as type,
                CONCAT('ข้อเสนอแนะใหม่: ', subject) as title,
                LEFT(message, 100) as message,
                CONCAT('/admin/feedback.php?id=', id) as link,
                0 as is_read,
                created_at
            FROM feedback 
            WHERE status = 'pending'
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $feedbackNotifs = $feedbackStmt->fetchAll();
        $notifications = array_merge($feedbackNotifs, $notifications);

        // Sort by created_at
        usort($notifications, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $notifications = array_slice($notifications, 0, 10);
    }

    jsonResponse([
        'success' => true,
        'count' => $totalCount,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    error_log("Notification API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
