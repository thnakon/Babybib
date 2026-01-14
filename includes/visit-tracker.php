<?php

/**
 * Babybib - Visit Tracker
 * ========================
 * Tracks page visits and provides statistics
 */

/**
 * Initialize visit tracking table
 */
function initVisitTable()
{
    try {
        $pdo = getDB();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `page_visits` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `visit_date` DATE NOT NULL,
                `visit_count` INT(11) UNSIGNED DEFAULT 1,
                `unique_visitors` INT(11) UNSIGNED DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_visit_date` (`visit_date`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Track a page visit
 */
function trackVisit()
{
    // Don't track bots
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/bot|crawl|spider|slurp|googlebot/i', $userAgent)) {
        return;
    }

    // Check if already tracked in this session
    if (isset($_SESSION['visit_tracked']) && $_SESSION['visit_tracked'] === date('Y-m-d')) {
        return;
    }

    try {
        $pdo = getDB();
        initVisitTable();

        $today = date('Y-m-d');
        $isNewVisitor = !isset($_SESSION['visit_tracked']);

        // Try to update existing record for today
        $stmt = $pdo->prepare("
            INSERT INTO page_visits (visit_date, visit_count, unique_visitors)
            VALUES (?, 1, ?)
            ON DUPLICATE KEY UPDATE 
                visit_count = visit_count + 1,
                unique_visitors = unique_visitors + ?
        ");
        $stmt->execute([$today, $isNewVisitor ? 1 : 0, $isNewVisitor ? 1 : 0]);

        // Mark as tracked for today
        $_SESSION['visit_tracked'] = $today;
    } catch (Exception $e) {
        // Silently fail - don't break the page
        error_log('Visit tracking error: ' . $e->getMessage());
    }
}

/**
 * Get visit statistics
 * @return array ['today' => int, 'month' => int, 'total' => int]
 */
function getVisitStats()
{
    try {
        $pdo = getDB();
        initVisitTable();

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        // Today's visits
        $stmt = $pdo->prepare("SELECT visit_count FROM page_visits WHERE visit_date = ?");
        $stmt->execute([$today]);
        $todayVisits = (int)($stmt->fetchColumn() ?: 0);

        // This month's visits
        $stmt = $pdo->prepare("SELECT SUM(visit_count) FROM page_visits WHERE visit_date >= ?");
        $stmt->execute([$monthStart]);
        $monthVisits = (int)($stmt->fetchColumn() ?: 0);

        // Total visits
        $stmt = $pdo->query("SELECT SUM(visit_count) FROM page_visits");
        $totalVisits = (int)($stmt->fetchColumn() ?: 0);

        return [
            'today' => $todayVisits,
            'month' => $monthVisits,
            'total' => $totalVisits
        ];
    } catch (Exception $e) {
        error_log('Visit stats error: ' . $e->getMessage());
        return [
            'today' => 0,
            'month' => 0,
            'total' => 0
        ];
    }
}

/**
 * Format number with K/M suffix for large numbers
 */
function formatVisitCount($count)
{
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return number_format($count);
}

// Auto-track visit when this file is included
trackVisit();
