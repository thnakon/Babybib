<?php

/**
 * Babybib API - Logout
 * =====================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

// Log before destroying session
if (isLoggedIn()) {
    logActivity(getCurrentUserId(), 'logout', 'User logged out');
}

// Destroy session
destroySession();

jsonResponse([
    'success' => true,
    'message' => 'ออกจากระบบสำเร็จ',
    'redirect' => SITE_URL
]);
