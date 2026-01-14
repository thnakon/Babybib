<?php

/**
 * Babybib API - Create Feedback
 * ===============================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = isLoggedIn() ? getCurrentUserId() : null;
$subject = sanitize(trim($input['subject'] ?? ''));
$message = sanitize(trim($input['message'] ?? ''));

if (empty($subject) || empty($message)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

try {
    $db = getDB();

    $stmt = $db->prepare("INSERT INTO feedback (user_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $subject, $message]);

    $feedbackId = $db->lastInsertId();

    if ($userId) {
        logActivity($userId, 'submit_feedback', "Submitted feedback: $subject", 'feedback', $feedbackId);
    }

    jsonResponse([
        'success' => true,
        'message' => 'ส่งข้อเสนอแนะสำเร็จ'
    ]);
} catch (Exception $e) {
    error_log("Create feedback error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
