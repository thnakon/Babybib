<?php

/**
 * Babybib API - Preview Bibliography
 * ===================================
 * Returns bibliography data for preview modal
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

requireAuth();

$userId = getCurrentUserId();
$projectId = intval($_GET['project'] ?? 0);

if (!$projectId) {
    jsonResponse(['success' => false, 'error' => 'Project ID required'], 400);
}

try {
    $db = getDB();

    // Get project info
    $stmt = $db->prepare("SELECT id, name, description, color FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        jsonResponse(['success' => false, 'error' => 'Project not found'], 404);
    }

    // Get bibliographies sorted by: Thai first, then English, alphabetically
    $stmt = $db->prepare("
        SELECT 
            b.id, 
            b.bibliography_text, 
            b.language,
            b.year,
            b.year_suffix,
            b.author_sort_key,
            rt.name_th as type_th,
            rt.name_en as type_en
        FROM bibliographies b
        JOIN resource_types rt ON b.resource_type_id = rt.id
        WHERE b.project_id = ? AND b.user_id = ?
        ORDER BY 
            CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
            b.author_sort_key ASC,
            b.year ASC,
            b.year_suffix ASC
    ");
    $stmt->execute([$projectId, $userId]);
    $bibliographies = $stmt->fetchAll();

    // Format for response with disambiguation
    $entries = [];
    $groupMap = []; // To track duplicates for suffix assignment

    // First pass: Group by Author + Year + Language
    foreach ($bibliographies as $bib) {
        $key = $bib['author_sort_key'] . '|' . $bib['year'] . '|' . $bib['language'];
        if ($bib['year'] && $bib['year'] != '0') {
            $groupMap[$key][] = $bib['id'];
        }
    }

    // Second pass: Create entries with suffixes
    foreach ($bibliographies as $bib) {
        $key = $bib['author_sort_key'] . '|' . $bib['year'] . '|' . $bib['language'];
        $suffix = '';

        if (isset($groupMap[$key]) && count($groupMap[$key]) > 1) {
            $index = array_search($bib['id'], $groupMap[$key]);
            if ($bib['language'] === 'th') {
                $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ', 'ฎ', 'ฏ', 'ฐ', 'ฑ', 'ฒ', 'ณ', 'ด', 'ต', 'ถ', 'ท', 'ธ', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ภ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'ฬ', 'อ', 'ฮ'];
                $suffix = $thaiSuffixes[$index] ?? '';
            } else {
                $suffix = chr(ord('a') + $index);
            }
        }

        $text = $bib['bibliography_text'];

        // Strip existing suffix if any to prevent double-suffixing
        if ($bib['year'] && $bib['year'] != '0') {
            $year = $bib['year'];
            $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
        } else {
            $text = preg_replace("/\(ม\.ป\.ป\.[ก-ฮ]\)/u", "(ม.ป.ป.)", $text);
            $text = preg_replace("/\(n\.d\.[a-z]\)/u", "(n.d.)", $text);
        }

        if ($suffix) {
            // Find the year pattern (Year) and insert suffix (YearSuffix)
            $search = '(' . $bib['year'] . ')';
            $replace = '(' . $bib['year'] . $suffix . ')';

            // Also handle n.d. / ม.ป.ป.
            if ($bib['year'] == 0 || !$bib['year']) {
                $search = $bib['language'] === 'th' ? '(ม.ป.ป.)' : '(n.d.)';
                $replace = ($bib['language'] === 'th' ? '(ม.ป.ป.' : '(n.d.') . $suffix . ')';

                // Fallback for ม.ป.ป. if exact match fails
                if (strpos($text, $search) === false && $bib['language'] === 'th') {
                    $text = preg_replace("/\(ม\.ป\.ป\.\)/u", $replace, $text);
                }
            }

            $text = str_replace($search, $replace, $text);
        }

        $entries[] = [
            'id' => $bib['id'],
            'text' => $text,
            'language' => $bib['language'],
            'year' => $bib['year'],
            'suffix' => $suffix,
            'type' => $bib['language'] === 'th' ? $bib['type_th'] : $bib['type_en']
        ];
    }

    // Separate Thai and English
    $thaiEntries = array_filter($entries, fn($e) => $e['language'] === 'th');
    $englishEntries = array_filter($entries, fn($e) => $e['language'] !== 'th');

    jsonResponse([
        'success' => true,
        'project' => [
            'id' => $project['id'],
            'name' => $project['name'],
            'description' => $project['description'],
            'color' => $project['color']
        ],
        'bibliographies' => [
            'thai' => array_values($thaiEntries),
            'english' => array_values($englishEntries),
            'all' => $entries
        ],
        'count' => count($entries),
        'count_thai' => count($thaiEntries),
        'count_english' => count($englishEntries)
    ]);
} catch (Exception $e) {
    error_log("Preview error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Server error'], 500);
}
