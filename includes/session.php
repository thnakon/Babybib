<?php

/**
 * Babybib Session Management
 * ===========================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

// Inactivity Timeout Logic (10 minutes)
if (isLoggedIn()) {
    $timeout_duration = 600; // 600 seconds = 10 minutes

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Session expired
        destroySession();

        // If it's an AJAX request, return error
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Session expired due to inactivity', 'expired' => true]);
            exit;
        }

        // Redirect to login with message
        header('Location: ' . SITE_URL . '/login.php?msg=timeout');
        exit;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $db = getDB();

        // First, ensure profile_picture column exists
        try {
            $db->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL");
        } catch (PDOException $e) {
            // Column already exists, ignore
        }

        $stmt = $db->prepare("SELECT id, username, name, surname, profile_picture, email, role, org_type, org_name, province, language, bibliography_count, project_count, is_lis_cmu, created_at FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetch();
    } catch (Exception $e) {
        // Fallback without profile_picture
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, name, surname, email, role, org_type, org_name, province, language, bibliography_count, project_count, is_lis_cmu, created_at FROM users WHERE id = ?");
            $stmt->execute([getCurrentUserId()]);
            $user = $stmt->fetch();
            if ($user) {
                $user['profile_picture'] = null;
            }
            return $user;
        } catch (Exception $e2) {
            return null;
        }
    }
}

/**
 * Set user session
 */
function setUserSession($user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'] . ' ' . $user['surname'];
    $_SESSION['user_language'] = $user['language'] ?? 'th';

    // Update last login
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    } catch (Exception $e) {
        error_log("Failed to update last login: " . $e->getMessage());
    }
}

/**
 * Destroy user session
 */
function destroySession()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Require authentication
 */
function requireAuth()
{
    if (!isLoggedIn()) {
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        } else {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
}

/**
 * Require admin role
 */
function requireAdmin()
{
    requireAuth();
    if (!isAdmin()) {
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'error' => 'Admin access required'], 403);
        } else {
            header('Location: ' . SITE_URL . '/users/dashboard.php');
            exit;
        }
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current language
 */
function getCurrentLanguage()
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
        $_SESSION['language'] = $_GET['lang'];
    }
    return $_SESSION['language'] ?? $_SESSION['user_language'] ?? 'th';
}

/**
 * Redirect to previous page or default
 */
function redirectBack($default = null)
{
    $default = $default ?? SITE_URL;
    $referer = $_SERVER['HTTP_REFERER'] ?? $default;
    header('Location: ' . $referer);
    exit;
}
