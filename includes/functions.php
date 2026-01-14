<?php

/**
 * Babybib Helper Functions
 * =========================
 */

require_once __DIR__ . '/config.php';

/**
 * Get all resource types
 */
function getResourceTypes($category = null)
{
    try {
        $db = getDB();
        $sql = "SELECT * FROM resource_types WHERE is_active = 1";
        if ($category) {
            $sql .= " AND category = ?";
        }
        $sql .= " ORDER BY sort_order, name_th";

        $stmt = $db->prepare($sql);
        $stmt->execute($category ? [$category] : []);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get resource type by ID
 */
function getResourceTypeById($id)
{
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM resource_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get resource type categories
 */
function getResourceCategories()
{
    return [
        'books' => ['name_th' => 'หนังสือ', 'name_en' => 'Books', 'icon' => 'fa-book'],
        'journals' => ['name_th' => 'วารสาร', 'name_en' => 'Journals', 'icon' => 'fa-book-journal-whills'],
        'reference' => ['name_th' => 'พจนานุกรม/สารานุกรม', 'name_en' => 'Reference', 'icon' => 'fa-spell-check'],
        'newspapers' => ['name_th' => 'หนังสือพิมพ์', 'name_en' => 'Newspapers', 'icon' => 'fa-newspaper'],
        'reports' => ['name_th' => 'รายงาน', 'name_en' => 'Reports', 'icon' => 'fa-file-contract'],
        'conferences' => ['name_th' => 'งานประชุม', 'name_en' => 'Conferences', 'icon' => 'fa-users'],
        'theses' => ['name_th' => 'วิทยานิพนธ์', 'name_en' => 'Theses', 'icon' => 'fa-graduation-cap'],
        'online' => ['name_th' => 'ออนไลน์', 'name_en' => 'Online', 'icon' => 'fa-globe'],
        'media' => ['name_th' => 'สื่อภาพ/เสียง', 'name_en' => 'Media', 'icon' => 'fa-video'],
        'others' => ['name_th' => 'อื่นๆ', 'name_en' => 'Others', 'icon' => 'fa-ellipsis']
    ];
}

/**
 * Get Thai provinces from settings
 */
function getProvinces()
{
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'provinces'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? json_decode($result['setting_value'], true) : [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get organization types
 */
function getOrganizationTypes()
{
    return [
        'university' => ['th' => 'มหาวิทยาลัย', 'en' => 'University'],
        'high_school' => ['th' => 'โรงเรียนมัธยม', 'en' => 'High School'],
        'opportunity_school' => ['th' => 'โรงเรียนขยายโอกาส', 'en' => 'Opportunity School'],
        'primary_school' => ['th' => 'โรงเรียนประถม', 'en' => 'Primary School'],
        'government' => ['th' => 'หน่วยงานรัฐ', 'en' => 'Government Agency'],
        'private_company' => ['th' => 'บริษัทเอกชน', 'en' => 'Private Company'],
        'personal' => ['th' => 'ส่วนบุคคล', 'en' => 'Personal'],
        'other' => ['th' => 'อื่นๆ', 'en' => 'Other']
    ];
}

/**
 * Get author types
 */
function getAuthorTypes()
{
    return [
        'general' => ['th' => 'ทั่วไป', 'en' => 'General'],
        'anonymous' => ['th' => 'ไม่ปรากฏชื่อ', 'en' => 'Anonymous'],
        'pseudonym' => ['th' => 'นามแฝง', 'en' => 'Pseudonym'],
        'royal' => ['th' => 'ราชสกุล', 'en' => 'Royal Name'],
        'nobility' => ['th' => 'บรรดาศักดิ์', 'en' => 'Nobility Title'],
        'monk' => ['th' => 'พระสงฆ์', 'en' => 'Buddhist Monk'],
        'editor' => ['th' => 'บรรณาธิการ', 'en' => 'Editor'],
        'organization' => ['th' => 'หน่วยงาน', 'en' => 'Organization'],
        'translator' => ['th' => 'ผู้แปล', 'en' => 'Translator']
    ];
}

/**
 * Count user bibliographies
 */
function countUserBibliographies($userId)
{
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bibliographies WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Count user projects
 */
function countUserProjects($userId)
{
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Check user limits
 */
function canCreateBibliography($userId)
{
    return countUserBibliographies($userId) < MAX_BIBLIOGRAPHIES;
}

function canCreateProject($userId)
{
    return countUserProjects($userId) < MAX_PROJECTS;
}

/**
 * Auto-cleanup bibliographies older than 2 years
 */
function cleanupOldBibliographies($userId)
{
    try {
        $db = getDB();
        // Find items older than 2 years
        $stmt = $db->prepare("SELECT id, bibliography_text FROM bibliographies WHERE user_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)");
        $stmt->execute([$userId]);
        $oldItems = $stmt->fetchAll();

        if (count($oldItems) > 0) {
            $ids = array_column($oldItems, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Delete them
            $stmt = $db->prepare("DELETE FROM bibliographies WHERE id IN ($placeholders)");
            $stmt->execute($ids);

            // Log the cleanup
            logActivity(
                $userId,
                'auto_cleanup',
                'ลบบรรณานุกรมที่ค้างเกิน 2 ปี จำนวน ' . count($oldItems) . ' รายการอัตโนมัติ',
                'bibliography',
                null
            );

            return count($oldItems);
        }
        return 0;
    } catch (Exception $e) {
        error_log("Cleanup error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format Thai date
 */
function formatThaiDate($date)
{
    if (empty($date)) return '';
    $months = [
        '',
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    return "$day $month $year";
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...')
{
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}
