<?php

/**
 * Babybib API - Register
 * =======================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/email-helper.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

// Extract and sanitize input
$username = sanitize(trim($input['username'] ?? ''));
$name = sanitize(trim($input['name'] ?? ''));
$surname = sanitize(trim($input['surname'] ?? ''));
$email = sanitize(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';
$orgType = sanitize($input['org_type'] ?? 'personal');
$orgName = sanitize(trim($input['org_name'] ?? ''));
$province = sanitize(trim($input['province'] ?? ''));
$isLisCmu = isset($input['is_lis_cmu']) && $input['is_lis_cmu'] ? 1 : 0;

// Validation
$errors = [];

// Username validation
if (empty($username)) {
    $errors[] = 'กรุณากรอกชื่อผู้ใช้';
} elseif (strlen($username) < 3 || strlen($username) > 20) {
    $errors[] = 'ชื่อผู้ใช้ต้องมี 3-20 ตัวอักษร';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'ชื่อผู้ใช้ใช้ได้เฉพาะ a-z, 0-9 และ _ เท่านั้น';
}

// Name validation
if (empty($name)) {
    $errors[] = 'กรุณากรอกชื่อ';
}

if (empty($surname)) {
    $errors[] = 'กรุณากรอกนามสกุล';
}

// Email validation
if (empty($email)) {
    $errors[] = 'กรุณากรอกอีเมล';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
}

// Password validation
if (empty($password)) {
    $errors[] = 'กรุณากรอกรหัสผ่าน';
} elseif (strlen($password) < 8) {
    $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว';
}

if ($password !== $passwordConfirm) {
    $errors[] = 'รหัสผ่านไม่ตรงกัน';
}

// Province validation
if (empty($province)) {
    $errors[] = 'กรุณาเลือกจังหวัด';
}

// Organization type validation
$validOrgTypes = ['university', 'high_school', 'opportunity_school', 'primary_school', 'government', 'private_company', 'personal', 'other'];
if (!in_array($orgType, $validOrgTypes)) {
    $errors[] = 'ประเภทองค์กรไม่ถูกต้อง';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'error' => implode(', ', $errors)], 400);
}

try {
    $db = getDB();

    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว'], 409);
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'อีเมลนี้ถูกใช้งานแล้ว'], 409);
    }

    // Ensure is_lis_cmu column exists
    $columnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'is_lis_cmu'");
    if ($columnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN is_lis_cmu TINYINT(1) DEFAULT 0 AFTER province");
    }

    // Ensure is_verified column exists
    $columnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if ($columnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active");
    }

    // Create email_verifications table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS email_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            verified_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Generate token
    $token = bin2hex(random_bytes(32));

    // Insert user (is_verified depends on settings)
    $isVerifiedInitially = EMAIL_VERIFICATION_ENABLED ? 0 : 1;
    $stmt = $db->prepare("
        INSERT INTO users (username, name, surname, email, password, org_type, org_name, province, is_lis_cmu, is_verified, token, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $username,
        $name,
        $surname,
        $email,
        $hashedPassword,
        $orgType,
        $orgName,
        $province,
        $isLisCmu,
        $isVerifiedInitially,
        $token
    ]);

    $userId = $db->lastInsertId();

    $emailSent = false;
    $verificationCode = '';

    if (EMAIL_VERIFICATION_ENABLED) {
        // Generate 6-digit verification code
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Store verification code
        $stmt = $db->prepare("
            INSERT INTO email_verifications (user_id, email, code, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $email, $verificationCode, $expiresAt]);

        // Send verification email
        $emailSent = sendVerificationEmail($email, $verificationCode, $name);
    }

    // Log registration
    $logMsg = EMAIL_VERIFICATION_ENABLED ? 'New user registered (pending verification)' : 'New user registered (auto-verified)';
    logActivity($userId, 'register', $logMsg);

    // Check if we should show code (DEV MODE or email failed)
    $showCode = defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE;

    $response = [
        'success' => true,
        'message' => EMAIL_VERIFICATION_ENABLED
            ? ($emailSent ? 'สมัครสมาชิกสำเร็จ กรุณาตรวจสอบอีเมลของคุณ' : 'สมัครสมาชิกสำเร็จ กรุณายืนยันอีเมล')
            : 'สมัครสมาชิกสำเร็จ ยินดีต้อนรับสู่ Babybib',
        'user_id' => $userId,
        'email' => $email,
        'requires_verification' => EMAIL_VERIFICATION_ENABLED,
        'email_sent' => $emailSent
    ];

    // Only return code if DEV mode is enabled
    if ($showCode) {
        $response['verification_code'] = $verificationCode;
        $response['expires_in'] = '15 minutes';
    }

    jsonResponse($response);
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
}
