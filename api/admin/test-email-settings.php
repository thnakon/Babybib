<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/email-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$testEmail = filter_var(trim((string) ($input['test_email'] ?? '')), FILTER_VALIDATE_EMAIL);
if (!$testEmail) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกอีเมลสำหรับทดสอบให้ถูกต้อง'], 400);
}

$smtpHost = trim((string) ($input['smtp_host'] ?? ''));
$smtpPort = (int) ($input['smtp_port'] ?? 0);
$smtpSecure = trim((string) ($input['smtp_secure'] ?? 'tls'));
$smtpUsername = trim((string) ($input['smtp_username'] ?? ''));
$smtpPassword = (string) ($input['smtp_password'] ?? '');
$emailFrom = trim((string) ($input['email_from'] ?? $smtpUsername));
$emailFromName = trim((string) ($input['email_from_name'] ?? 'Babybib'));

if ($smtpHost === '' || $smtpPort <= 0 || $smtpUsername === '' || $smtpPassword === '') {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูล SMTP ให้ครบก่อนทดสอบ'], 400);
}

if (!in_array($smtpSecure, ['tls', 'ssl'], true)) {
    jsonResponse(['success' => false, 'error' => 'ค่า SMTP Secure ไม่ถูกต้อง'], 400);
}

if (!filter_var($smtpUsername, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'error' => 'SMTP Username ต้องเป็นอีเมลที่ถูกต้อง'], 400);
}

if ($emailFrom !== '' && !filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'error' => 'อีเมลผู้ส่งไม่ถูกต้อง'], 400);
}

$result = sendSmtpTestEmail($testEmail, [
    'smtp_host' => $smtpHost,
    'smtp_port' => $smtpPort,
    'smtp_secure' => $smtpSecure,
    'smtp_username' => $smtpUsername,
    'smtp_password' => $smtpPassword,
    'email_from' => $emailFrom,
    'email_from_name' => $emailFromName,
]);

if (!$result['success']) {
    jsonResponse(['success' => false, 'error' => 'ส่งอีเมลทดสอบไม่สำเร็จ กรุณาตรวจสอบค่า SMTP'], 500);
}

logActivity(getCurrentUserId(), 'test_email_settings', 'Sent SMTP test email to ' . $testEmail);

jsonResponse(['success' => true, 'message' => 'ส่งอีเมลทดสอบสำเร็จ']);