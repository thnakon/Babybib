<?php

/**
 * Babybib - Submit Rating API
 * ============================
 * Handles user satisfaction rating submissions
 */

ob_start();

require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/session.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['rating'])) {
        throw new Exception('Rating is required');
    }

    $rating = (int)$input['rating'];
    $pageUrl = $input['page_url'] ?? '';

    // Validate rating (1-5 stars)
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }

    $pdo = getDB();

    // Check if table exists, if not create it
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_ratings` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT(11) UNSIGNED NULL COMMENT 'NULL for guest users',
            `rating` TINYINT(1) NOT NULL COMMENT 'Rating 1-5 stars',
            `page_url` VARCHAR(255) NULL COMMENT 'Page where rating was given',
            `user_agent` VARCHAR(500) NULL,
            `ip_address` VARCHAR(45) NULL,
            `session_id` VARCHAR(128) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_rating` (`rating`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Get user info
    $userId = isLoggedIn() ? getCurrentUserId() : null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $sessionId = session_id();

    // Check for duplicate rating from same session in last hour
    $stmt = $pdo->prepare("
        SELECT id FROM user_ratings 
        WHERE session_id = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$sessionId]);

    if ($stmt->fetch()) {
        // Update existing rating instead of creating new one
        $stmt = $pdo->prepare("
            UPDATE user_ratings 
            SET rating = ?, page_url = ?, updated_at = NOW()
            WHERE session_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$rating, $pageUrl, $sessionId]);

        echo json_encode([
            'success' => true,
            'message' => 'Rating updated',
            'rating' => $rating
        ]);
    } else {
        // Insert new rating
        $stmt = $pdo->prepare("
            INSERT INTO user_ratings (user_id, rating, page_url, user_agent, ip_address, session_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $rating, $pageUrl, $userAgent, $ipAddress, $sessionId]);

        echo json_encode([
            'success' => true,
            'message' => 'Rating submitted',
            'rating' => $rating
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
