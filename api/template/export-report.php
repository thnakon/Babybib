<?php

/**
 * Babybib API - Template: Export Full Report
 * ===========================================================
 * POST /api/template/export-report.php
 * 
 * Note: The export functionality has been removed, 
 * leaving this file as a blank slate for you to rewrite.
 */

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$userId = isLoggedIn() ? getCurrentUserId() : null;

// Parse payload
$rawPayload = $_POST['payload'] ?? '';
if (empty($rawPayload)) {
    http_response_code(400);
    die('ไม่พบข้อมูล payload');
}

$payload = json_decode($rawPayload, true);
if (!$payload || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die('ข้อมูล payload ไม่ถูกต้อง');
}

$templateId = $payload['template'] ?? 'academic_general';
$format = strtolower($payload['format'] ?? 'docx');
$coverData = $payload['coverData'] ?? [];
$formatSettings = $payload['formatSettings'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

// TODO: เขียนตรรกะใหม่สำหรับการส่งออกไฟล์ Word/PDF ที่นี่

// แจ้งเตือนเมื่อมีการกดปุ่ม (ชั่วคราว) เพื่อให้หน้าเว็บจับ Error ได้
http_response_code(501);
die('ฟีเจอร์นี้ได้ถูกลบออกแล้ว และกำลังรอการรื้อระบบใหม่');
