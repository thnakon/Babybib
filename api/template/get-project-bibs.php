<?php

/**
 * Babybib API - Template: Get Project Bibliographies
 * ====================================================
 * GET /api/template/get-project-bibs.php?project_id=X
 * Returns sorted & disambiguated bibliography list for a project
 */

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$userId = getCurrentUserId();
$projectId = intval($_GET['project_id'] ?? 0);

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุ project_id']);
    exit;
}

try {
    $db = getDB();

    // Verify project belongs to current user
    $stmt = $db->prepare("SELECT id, name FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบโครงการ หรือคุณไม่มีสิทธิ์เข้าถึง']);
        exit;
    }

    // Get bibliographies with APA sort order
    $stmt = $db->prepare("
        SELECT b.id, b.bibliography_text, b.language, b.author_sort_key, b.year, b.year_suffix,
               rt.name_th, rt.name_en
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
    $bibliographies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Apply APA sort and disambiguation
    sortBibliographies($bibliographies);
    $bibliographies = applyDisambiguation($bibliographies);

    echo json_encode([
        'success' => true,
        'project_name' => $project['name'],
        'count' => count($bibliographies),
        'bibliographies' => array_values($bibliographies)
    ]);

} catch (Exception $e) {
    error_log("get-project-bibs API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในระบบ']);
}

/**
 * Apply disambiguation suffixes (ก,ข,ค / a,b,c)
 */
function applyDisambiguation($bibliographies)
{
    $groupMap = [];
    foreach ($bibliographies as $index => $bib) {
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (!empty($bib['year']) && $bib['year'] != '0') {
            $groupMap[$key][] = $index;
        }
    }

    $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ'];

    foreach ($groupMap as $indices) {
        if (count($indices) > 1) {
            foreach ($indices as $position => $index) {
                $bib = &$bibliographies[$index];
                $text = $bib['bibliography_text'];
                $year = $bib['year'];
                $lang = $bib['language'];

                $suffix = ($lang === 'th') ? ($thaiSuffixes[$position] ?? '') : chr(ord('a') + $position);
                if ($suffix && $year && $year != '0') {
                    $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
                    $text = str_replace('(' . $year . ')', '(' . $year . $suffix . ')', $text);
                    $bib['bibliography_text'] = $text;
                }
            }
        }
    }

    return $bibliographies;
}
