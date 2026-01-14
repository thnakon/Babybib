<?php

/**
 * Babybib API - Admin: Download Backup
 */
require_once '../../includes/session.php';
requireAdmin();

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    die('Filename required');
}

// Security: validate filename
$filename = basename($filename); // Prevent directory traversal
if (!preg_match('/^[a-zA-Z0-9_.-]+\.(sql\.gz|tar\.gz)$/', $filename)) {
    die('Invalid filename');
}

$backupDir = __DIR__ . '/../../backups';
$filepath = $backupDir . '/' . $filename;

if (!file_exists($filepath)) {
    die('File not found');
}

// Log download
logActivity(getCurrentUserId(), 'download_backup', "Downloaded backup: {$filename}");

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Output file
readfile($filepath);
exit;
