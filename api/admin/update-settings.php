<?php

/**
 * Babybib API - Admin: Update Settings
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);

$allowedKeys = [
    'site_name',
    'site_title',
    'site_description',
    'contact_email',
    'max_bibliographies',
    'max_bibs_per_user',
    'max_bibliographies_per_user',
    'max_projects',
    'max_projects_per_user',
    'maintenance_mode',
    'allow_registration',
    'admin_delete_token',
    'bib_lifetime_days',
    'smtp_username',
    'smtp_password',
    'email_verification_enabled'
];

try {
    $db = getDB();

    foreach ($input as $key => $value) {
        if (!in_array($key, $allowedKeys)) continue;

        $value = sanitize(trim($value));

        // Check if exists
        $stmt = $db->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);

        if ($stmt->fetch()) {
            $stmt = $db->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        } else {
            // Table doesn't have created_at, using default update_at via CURRENT_TIMESTAMP
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }

    logActivity(getCurrentUserId(), 'update_settings', 'Updated system settings');
    jsonResponse(['success' => true, 'message' => 'บันทึกการตั้งค่าสำเร็จ']);
} catch (Exception $e) {
    error_log("Update settings error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
