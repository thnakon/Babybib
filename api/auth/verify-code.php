<?php

/**
 * Babybib API - Verify Email Code
 * ===============================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireValidCSRFToken();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = intval($input['user_id'] ?? 0);
$code = trim($input['code'] ?? '');

if (empty($userId) || empty($code)) {
    jsonResponse(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน'], 400);
}

try {
    $db = getDB();
    ensureEmailVerificationSchema($db);

    // Check code in email_verifications table while supporting legacy plaintext codes
    $stmt = $db->prepare("
        SELECT * FROM email_verifications 
        WHERE user_id = ? AND used = 0 AND expires_at > NOW()
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$userId]);
    $verification = null;
    foreach ($stmt->fetchAll() as $row) {
        if (matchesStoredSecret($code, $row['code'])) {
            $verification = $row;
            break;
        }
    }

    if (!$verification) {
        jsonResponse(['success' => false, 'error' => 'รหัสยืนยันไม่ถูกต้องหรือหมดอายุ'], 400);
    }

    // Mark code as used
    $stmt = $db->prepare("UPDATE email_verifications SET used = 1, verified_at = NOW() WHERE id = ?");
    $stmt->execute([$verification['id']]);

    // Update user status
    $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$userId]);

    // Get user data for auto-login
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Set session
    setUserSession($user);

    // Log activity
    logActivity($userId, 'verify_email', 'User verified email successfully');

    jsonResponse([
        'success' => true,
        'message' => 'ยืนยันอีเมลสำเร็จ ยินดีต้อนรับ!',
        'redirect' => SITE_URL . '/users/dashboard.php'
    ]);

} catch (Exception $e) {
    error_log("Verification error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
