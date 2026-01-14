<?php

/**
 * Babybib API - Upload Profile Picture
 * =====================================
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check if logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเข้าสู่ระบบ'], 401);
}

$userId = getCurrentUserId();

// Check if file uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'กรุณาเลือกไฟล์รูปภาพ';
    if (isset($_FILES['avatar'])) {
        switch ($_FILES['avatar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 2MB)';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'ไม่พบไฟล์ที่อัปโหลด';
                break;
        }
    }
    jsonResponse(['success' => false, 'error' => $errorMsg], 400);
}

$file = $_FILES['avatar'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Validate file type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    jsonResponse(['success' => false, 'error' => 'รองรับเฉพาะไฟล์ JPG, PNG, GIF, WEBP'], 400);
}

// Validate file size
if ($file['size'] > $maxSize) {
    jsonResponse(['success' => false, 'error' => 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 2MB)'], 400);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . strtolower($extension);

$uploadDir = dirname(dirname(__DIR__)) . '/uploads/avatars/';
$uploadPath = $uploadDir . $filename;

// Create directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    jsonResponse(['success' => false, 'error' => 'ไม่สามารถอัปโหลดไฟล์ได้'], 500);
}

try {
    $db = getDB();

    // Ensure column exists
    try {
        $db->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $e) {
        // Column already exists, ignore
    }

    // Get old avatar to delete
    $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldAvatar = $stmt->fetchColumn();

    // Update database
    $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$filename, $userId]);

    // Delete old avatar if exists
    if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
        unlink($uploadDir . $oldAvatar);
    }

    // Update session
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['profile_picture'] = $filename;
    }

    $avatarUrl = SITE_URL . '/uploads/avatars/' . $filename;

    jsonResponse([
        'success' => true,
        'message' => 'อัปโหลดรูปโปรไฟล์สำเร็จ',
        'avatar_url' => $avatarUrl,
        'filename' => $filename
    ]);
} catch (Exception $e) {
    error_log("Avatar upload error: " . $e->getMessage());
    // Delete uploaded file on error
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
