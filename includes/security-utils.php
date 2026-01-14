<?php

/**
 * Babybib - Input Validation & Sanitization
 * ==========================================
 * Security utilities for input validation
 */

/**
 * Validate and sanitize email
 */
function validateEmail($email)
{
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return $email;
}

/**
 * Validate password strength
 * Rules: min 8 chars, at least 1 uppercase, 1 lowercase, 1 number
 */
function validatePassword($password)
{
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'];
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว'];
    }
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'รหัสผ่านต้องมีตัวพิมพ์เล็กอย่างน้อย 1 ตัว'];
    }
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'error' => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว'];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validate username
 * Rules: 3-50 chars, alphanumeric and underscore only
 */
function validateUsername($username)
{
    $username = trim($username);
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['valid' => false, 'error' => 'ชื่อผู้ใช้ต้องมี 3-50 ตัวอักษร'];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'error' => 'ชื่อผู้ใช้ใช้ได้เฉพาะ a-z, 0-9 และ _ เท่านั้น'];
    }
    return ['valid' => true, 'error' => null, 'username' => $username];
}

/**
 * Sanitize string for safe output
 */
function sanitizeOutput($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate file upload for safety
 */
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = 5242880)
{
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }

    // Check file size (default 5MB)
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด ' . ($maxSize / 1024 / 1024) . 'MB)'];
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'ประเภทไฟล์ไม่ได้รับอนุญาต'];
    }

    // Check for hidden PHP code in images
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php|<\?=|<%|<script/i', $content)) {
        return ['valid' => false, 'error' => 'ไฟล์มีเนื้อหาที่ไม่ปลอดภัย'];
    }

    return ['valid' => true, 'error' => null, 'mime' => $mimeType];
}

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Rate limiting check (simple implementation)
 * Returns true if action is allowed, false if rate limited
 */
function checkRateLimit($action, $userId, $maxAttempts = 5, $window = 300)
{
    $cacheFile = sys_get_temp_dir() . '/babybib_rate_' . md5($action . '_' . $userId);

    $attempts = [];
    if (file_exists($cacheFile)) {
        $attempts = json_decode(file_get_contents($cacheFile), true) ?: [];
    }

    // Remove old attempts
    $now = time();
    $attempts = array_filter($attempts, function ($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });

    // Check if rate limited
    if (count($attempts) >= $maxAttempts) {
        return false;
    }

    // Record new attempt
    $attempts[] = $now;
    file_put_contents($cacheFile, json_encode($attempts));

    return true;
}

/**
 * Validate CSRF token
 */
function validateCSRF($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = [], $severity = 'warning')
{
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'severity' => $severity,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'details' => $details
    ];

    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}
