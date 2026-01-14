<?php

/**
 * Babybib API - Admin: Delete Backup
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? '';

if (empty($filename)) {
    jsonResponse(['success' => false, 'error' => 'Filename required'], 400);
}

// Security: validate filename
$filename = basename($filename); // Prevent directory traversal
if (!preg_match('/^[a-zA-Z0-9_.-]+\.(sql\.gz|tar\.gz)$/', $filename)) {
    jsonResponse(['success' => false, 'error' => 'Invalid filename'], 400);
}

$backupDir = __DIR__ . '/../../backups';
$filepath = $backupDir . '/' . $filename;

if (!file_exists($filepath)) {
    jsonResponse(['success' => false, 'error' => 'File not found'], 404);
}

try {
    if (unlink($filepath)) {
        logActivity(getCurrentUserId(), 'delete_backup', "Deleted backup: {$filename}");
        jsonResponse(['success' => true, 'message' => 'ลบ Backup สำเร็จ']);
    } else {
        throw new Exception('Failed to delete file');
    }
} catch (Exception $e) {
    error_log("Delete backup error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
