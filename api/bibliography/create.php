<?php

/**
 * Babybib API - Create Bibliography
 * ===================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Require authentication
requireAuth();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'error' => 'Invalid input'], 400);
}

$userId = getCurrentUserId();

// Extract data
$bibId = !empty($input['bib_id']) ? intval($input['bib_id']) : null;
$isEditing = $bibId !== null;
$resourceTypeId = intval($input['resource_type_id'] ?? 0);
$projectId = !empty($input['project_id']) ? intval($input['project_id']) : null;
$language = in_array($input['language'] ?? 'th', ['th', 'en']) ? $input['language'] : 'th';
$bibliographyText = $input['bibliography_text'] ?? ''; // Preserve italics
$bibliographyText = strip_tags($bibliographyText, '<i>');
$citationParenthetical = sanitize($input['citation_parenthetical'] ?? '');
$citationNarrative = sanitize($input['citation_narrative'] ?? '');
$year = intval($input['year'] ?? 0);

// Validate
if (!$resourceTypeId) {
    jsonResponse(['success' => false, 'error' => 'กรุณาเลือกประเภททรัพยากร'], 400);
}

if (empty($bibliographyText)) {
    jsonResponse(['success' => false, 'error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
}

// Prepare data JSON
$dataFields = ['title', 'authors', 'edition', 'publisher', 'journal_name', 'volume', 'issue', 'pages', 'doi', 'url', 'website_name', 'channel_name', 'is_secondary_source', 'original_author', 'original_year'];
$data = [];
foreach ($dataFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = is_array($input[$field]) ? $input[$field] : sanitize($input[$field]);
    }
}

// Get author sort key
$authorSortKey = '';
if (!empty($input['authors']) && is_array($input['authors']) && count($input['authors']) > 0) {
    $firstAuthor = $input['authors'][0];
    $type = $firstAuthor['type'] ?? 'normal';
    if ($type === 'anonymous') {
        $authorSortKey = $language === 'th' ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'Anonymous';
    } elseif ($type === 'organization' || $type === 'pseudonym') {
        $authorSortKey = $firstAuthor['display'] ?? '';
    } else {
        $authorSortKey = $firstAuthor['lastName'] ?: $firstAuthor['firstName'] ?: ($firstAuthor['display'] ?? '');
    }
} else {
    // Case: No author - Use title for sorting
    $title = $input['title'] ?? '';
    if ($language === 'en') {
        // Skip common English articles: A, An, The (APA 7th rule)
        $authorSortKey = preg_replace('/^(A|An|The)\s+/i', '', trim($title));
    } else {
        $authorSortKey = trim($title);
    }
}

try {
    $db = getDB();
    $db->beginTransaction();

    if (!$isEditing) {
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'ไม่พบผู้ใช้'], 404);
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM bibliographies WHERE user_id = ?");
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= MAX_BIBLIOGRAPHIES) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'คุณสร้างบรรณานุกรมถึงขีดจำกัดแล้ว (' . MAX_BIBLIOGRAPHIES . ' รายการ)'], 403);
        }
    }

    // Verify if editing
    $existingProject = null;
    if ($isEditing) {
        $stmt = $db->prepare("SELECT project_id FROM bibliographies WHERE id = ? AND user_id = ? FOR UPDATE");
        $stmt->execute([$bibId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'ไม่พบข้อมูลบรรณานุกรมที่ต้องการแก้ไข'], 404);
        }
        $existingProject = $row['project_id'];
    }

    // Verify resource type exists
    $stmt = $db->prepare("SELECT id FROM resource_types WHERE id = ?");
    $stmt->execute([$resourceTypeId]);
    if (!$stmt->fetch()) {
        $db->rollBack();
        jsonResponse(['success' => false, 'error' => 'ประเภททรัพยากรไม่ถูกต้อง'], 400);
    }

    // Verify project if provided
    if ($projectId) {
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        if (!$stmt->fetch()) {
            $projectId = null;
        }
    }

    // Check for year suffix
    $yearSuffix = null;
    if ($authorSortKey && $year) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count FROM bibliographies 
            WHERE user_id = ? AND author_sort_key = ? AND year = ? AND id != ?
        ");
        $stmt->execute([$userId, $authorSortKey, $year, $bibId ?? 0]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            $suffixIndex = $result['count'];
            if ($language === 'th') {
                $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ', 'ฎ', 'ฏ', 'ฐ', 'ฑ', 'ฒ', 'ณ', 'ด', 'ต', 'ถ', 'ท', 'ธ', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ภ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'ฬ', 'อ', 'ฮ'];
                $yearSuffix = $thaiSuffixes[$suffixIndex] ?? '';
            } else {
                $yearSuffix = chr(ord('a') + $suffixIndex);
            }

            if ($yearSuffix) {
                // Append suffix to year in bibliography text, citation parenthetical and narrative
                $search = '(' . $year . ')';
                $replace = '(' . $year . $yearSuffix . ')';

                $bibliographyText = str_replace($search, $replace, $bibliographyText);
                $citationParenthetical = str_replace($search, $replace, $citationParenthetical);
                $citationNarrative = str_replace($search, $replace, $citationNarrative);

                // Handle no date case
                if ($year == 0) {
                    $search = $language === 'th' ? '(ม.ป.ป.)' : '(n.d.)';
                    $replace = ($language === 'th' ? '(ม.ป.ป.' : '(n.d.') . $yearSuffix . ')';
                    $bibliographyText = str_replace($search, $replace, $bibliographyText);
                    $citationParenthetical = str_replace($search, $replace, $citationParenthetical);
                    $citationNarrative = str_replace($search, $replace, $citationNarrative);
                }
            }
        }
    }

    if ($isEditing) {
        // Update
        $stmt = $db->prepare("
            UPDATE bibliographies 
            SET resource_type_id = ?, project_id = ?, data = ?, bibliography_text = ?, 
                citation_parenthetical = ?, citation_narrative = ?, language = ?, 
                author_sort_key = ?, year = ?, year_suffix = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $resourceTypeId,
            $projectId,
            json_encode($data, JSON_UNESCAPED_UNICODE),
            $bibliographyText,
            $citationParenthetical,
            $citationNarrative,
            $language,
            $authorSortKey,
            $year,
            $yearSuffix,
            $bibId,
            $userId
        ]);

        // Update project counts if changed
        if ($existingProject != $projectId) {
            if ($existingProject) {
                $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ? AND user_id = ?) WHERE id = ? AND user_id = ?");
                $stmt->execute([$existingProject, $userId, $existingProject, $userId]);
            }
            if ($projectId) {
                $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ? AND user_id = ?) WHERE id = ? AND user_id = ?");
                $stmt->execute([$projectId, $userId, $projectId, $userId]);
            }
        }
    } else {
        // Insert
        $stmt = $db->prepare("
            INSERT INTO bibliographies 
            (user_id, resource_type_id, project_id, data, bibliography_text, citation_parenthetical, citation_narrative, language, author_sort_key, year, year_suffix, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $resourceTypeId,
            $projectId,
            json_encode($data, JSON_UNESCAPED_UNICODE),
            $bibliographyText,
            $citationParenthetical,
            $citationNarrative,
            $language,
            $authorSortKey,
            $year,
            $yearSuffix
        ]);

        $bibId = $db->lastInsertId();

        $stmt = $db->prepare("UPDATE users SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE user_id = ?) WHERE id = ?");
        $stmt->execute([$userId, $userId]);

        // Update project count
        if ($projectId) {
            $stmt = $db->prepare("UPDATE projects SET bibliography_count = (SELECT COUNT(*) FROM bibliographies WHERE project_id = ? AND user_id = ?) WHERE id = ? AND user_id = ?");
            $stmt->execute([$projectId, $userId, $projectId, $userId]);
        }
    }

    $db->commit();

    if ($isEditing) {
        logActivity($userId, 'update_bibliography', "Updated bibliography ID: $bibId", 'bibliography', $bibId);
    } else {
        logActivity($userId, 'create_bibliography', "Created bibliography ID: $bibId", 'bibliography', $bibId);
    }

    jsonResponse([
        'success' => true,
        'message' => $isEditing ? 'อัปเดตบรรณานุกรมสำเร็จ' : 'บันทึกบรรณานุกรมสำเร็จ',
        'data' => [
            'id' => $bibId,
            'year_suffix' => $yearSuffix
        ]
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Bibliography API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่: ' . $e->getMessage()], 500);
}
