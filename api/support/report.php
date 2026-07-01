<?php

/**
 * Babybib API - Report Issue
 * ===========================
 * Submit support report from user
 */

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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$issueType = sanitize($input['issue_type'] ?? '');
$subject = sanitize(trim($input['subject'] ?? ''));
$description = sanitize(trim($input['description'] ?? ''));

// Validation
if (empty($issueType)) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเลือกประเภทปัญหา'], 400);
}

if (!in_array($issueType, ['bug', 'feature', 'help', 'other'])) {
    jsonResponse(['success' => false, 'error' => 'ประเภทปัญหาไม่ถูกต้อง'], 400);
}

if (empty($subject)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกหัวข้อ'], 400);
}

if (empty($description)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกรายละเอียด'], 400);
}

try {
    $db = getDB();
    $userId = getCurrentUserId();
    requireDatabaseSchema($db, [
        'support_reports' => ['user_id', 'issue_type', 'subject', 'description', 'status', 'created_at', 'updated_at', 'resolved_at'],
    ]);

    // Insert report
    $stmt = $db->prepare("
        INSERT INTO support_reports (user_id, issue_type, subject, description) 
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$userId, $issueType, $subject, $description]);

    $reportId = $db->lastInsertId();

    // Log activity
    logActivity($userId, 'support_report', "Submitted support report #$reportId");

    jsonResponse([
        'success' => true,
        'message' => 'ส่งรายงานสำเร็จ',
        'report_id' => $reportId
    ]);
} catch (Exception $e) {
    error_log("Support report error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
