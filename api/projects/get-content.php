<?php
/**
 * Babybib API - Get Project Content
 * ================================
 * Returns project details and all bibliography entries in a sorted list.
 */

header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Verify authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();
$projectId = intval($_GET['id'] ?? 0);

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

try {
    $db = getDB();

    // Get project info
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // Get bibliographies for this project - properly sorted
    // Sorting: 1. Thai then English, 2. Author Sort Key, 3. Year
    $stmt = $db->prepare("
        SELECT b.*, rt.name_th, rt.name_en 
        FROM bibliographies b 
        JOIN resource_types rt ON b.resource_type_id = rt.id 
        WHERE b.project_id = ? 
        ORDER BY 
            CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
            b.author_sort_key ASC,
            b.year ASC,
            b.year_suffix ASC
    ");
    $stmt->execute([$projectId]);
    $bibliographies = $stmt->fetchAll();

    // Apply disambiguation for same author-year (Logic from project-preview.php)
    $bibliographies = applyDisambiguation($bibliographies);

    echo json_encode([
        'success' => true,
        'project' => $project,
        'bibliographies' => $bibliographies,
        'count' => count($bibliographies)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

/**
 * Apply disambiguation suffixes (ก,ข,ค for Thai / a,b,c for English)
 */
function applyDisambiguation($bibliographies)
{
    // Group by author_sort_key + year + language
    $groupMap = [];
    foreach ($bibliographies as $index => $bib) {
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (!empty($bib['year']) && $bib['year'] != '0') {
            $groupMap[$key][] = $index;
        }
    }

    // Apply suffixes to duplicates
    foreach ($groupMap as $key => $indices) {
        if (count($indices) > 1) {
            foreach ($indices as $position => $index) {
                $bib = &$bibliographies[$index];
                $text = $bib['bibliography_text'];
                $year = $bib['year'];
                $lang = $bib['language'];

                // Determine suffix
                if ($lang === 'th') {
                    $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ', 'ฎ', 'ฏ', 'ฐ', 'ฑ', 'ฒ', 'ณ', 'ด', 'ต', 'ถ', 'ท', 'ธ', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ภ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'ฬ', 'อ', 'ฮ'];
                    $suffix = $thaiSuffixes[$position] ?? '';
                } else {
                    $suffix = chr(ord('a') + $position);
                }

                if ($suffix && $year && $year != '0') {
                    // Remove existing suffix first
                    $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
                    // Apply new suffix
                    $search = '(' . $year . ')';
                    $replace = '(' . $year . $suffix . ')';
                    $text = str_replace($search, $replace, $text);
                    $bib['bibliography_text'] = $text;
                }
            }
        }
    }

    return $bibliographies;
}
