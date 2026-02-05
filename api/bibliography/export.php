<?php

/**
 * Babybib API - Export Bibliography
 * ===================================
 * Exports bibliographies to DOCX or PDF format
 */

require_once '../../includes/session.php';

// Require authentication
requireAuth();

$userId = getCurrentUserId();
$format = strtolower($_GET['format'] ?? 'docx');
$projectId = intval($_GET['project'] ?? 0);
$typeId = intval($_GET['type'] ?? 0);

if (!in_array($format, ['docx', 'pdf'])) {
    die('Invalid format');
}

try {
    $db = getDB();

    // Build query
    $where = ["b.user_id = ?"];
    $params = [$userId];

    if ($projectId) {
        $where[] = "b.project_id = ?";
        $params[] = $projectId;
    }

    if ($typeId) {
        $where[] = "b.resource_type_id = ?";
        $params[] = $typeId;
    }

    $whereClause = implode(' AND ', $where);

    // Get bibliographies sorted by: Thai first, then English, alphabetically by author, then by year, then by year suffix
    $stmt = $db->prepare("
        SELECT b.*, rt.name_th, rt.name_en
        FROM bibliographies b
        JOIN resource_types rt ON b.resource_type_id = rt.id
        WHERE $whereClause
        ORDER BY 
            CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
            b.author_sort_key ASC,
            b.year ASC,
            b.year_suffix ASC
    ");
    $stmt->execute($params);
    $bibliographies = $stmt->fetchAll();

    // Ensure sorting follows APA 7th rules (including title fallback with article skipping)
    sortBibliographies($bibliographies);

    if (empty($bibliographies)) {
        error_log("Export Error: No bibliographies found for query.");
        die('ไม่มีรายการบรรณานุกรม');
    }

    // Get project name if filtering by project
    $projectName = '';
    if ($projectId) {
        $stmt = $db->prepare("SELECT name FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch();
        $projectName = $project ? $project['name'] : '';
    }

    // Generate content
    if ($format === 'docx') {
        exportDocx($bibliographies, $projectName);
    } else {
        exportPdf($bibliographies, $projectName);
    }
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    die('เกิดข้อผิดพลาดในการส่งออก: ' . $e->getMessage());
}

/**
 * Export to DOCX format
 */
function exportDocx($bibliographies, $projectName = '')
{
    $filename = 'bibliography_' . date('Ymd_His') . '.docx';

    // Create DOCX using XML - sz value is in half-points (18pt = 36, 16pt = 32)
    $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>';

    // Title - 18pt (36 half-points), Bold, Centered (no project name)
    $content .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="480"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Tahoma" w:hAnsi="Tahoma" w:eastAsia="Tahoma" w:cs="Tahoma"/><w:b/><w:sz w:val="36"/><w:szCs w:val="36"/></w:rPr><w:t>บรรณานุกรม</w:t></w:r></w:p>';

    // Thai bibliographies first
    $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
    $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');

    // For disambiguation
    $groupMap = [];
    foreach ($bibliographies as $bib) {
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (!empty($bib['year']) && $bib['year'] != '0') {
            $groupMap[$key][] = $bib['id'];
        }
    }

    foreach (array_merge($thBibs, $enBibs) as $bib) {
        $text = $bib['bibliography_text'];

        // Strip existing suffix if any to prevent double-suffixing
        if (!empty($bib['year']) && $bib['year'] != '0') {
            $year = $bib['year'];
            $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
        } else {
            $text = preg_replace("/\(ม\.ป\.ป\.[ก-ฮ]\)/u", "(ม.ป.ป.)", $text);
            $text = preg_replace("/\(n\.d\.[a-z]\)/u", "(n.d.)", $text);
        }

        // Apply disambiguation suffix
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (isset($groupMap[$key]) && count($groupMap[$key]) > 1) {
            $index = array_search($bib['id'], $groupMap[$key]);
            $suffix = '';
            if ($bib['language'] === 'th') {
                $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ', 'ฎ', 'ฏ', 'ฐ', 'ฑ', 'ฒ', 'ณ', 'ด', 'ต', 'ถ', 'ท', 'ธ', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ภ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'ฬ', 'อ', 'ฮ'];
                $suffix = $thaiSuffixes[$index] ?? '';
            } else {
                $suffix = chr(ord('a') + $index);
            }

            if ($suffix) {
                $yearVal = $bib['year'] ?? 0;
                $search = '(' . $yearVal . ')';
                $replace = '(' . $yearVal . $suffix . ')';
                if ($yearVal == 0) {
                    $search = $bib['language'] === 'th' ? '(ม.ป.ป.)' : '(n.d.)';
                    $replace = ($bib['language'] === 'th' ? '(ม.ป.ป.' : '(n.d.') . $suffix . ')';

                    // Fallback for ม.ป.ป. if exact match fails
                    if (strpos($text, $search) === false && $bib['language'] === 'th') {
                        $text = preg_replace("/\(ม\.ป\.ป\.\)/u", $replace, $text);
                    }
                }
                $text = str_replace($search, $replace, $text);
            }
        }

        // Handle italics for Word XML
        // Hanging indent style (720 twips = 0.5 inch), 16pt font (32 half-points), 1.5 line spacing (360 twips)
        $content .= '<w:p><w:pPr><w:ind w:left="720" w:hanging="720"/><w:spacing w:line="360" w:lineRule="auto"/></w:pPr>';

        // Split text by <i> tags
        $parts = preg_split('/(<i>.*?<\/i>)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $part) {
            $isItalic = false;
            $cleanPart = $part;
            if (preg_match('/^<i>(.*)<\/i>$/u', $part, $matches)) {
                $isItalic = true;
                $cleanPart = $matches[1];
            }

            if ($cleanPart === '') continue;

            $cleanPart = htmlspecialchars($cleanPart, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $content .= '<w:r><w:rPr><w:rFonts w:ascii="Tahoma" w:hAnsi="Tahoma" w:eastAsia="Tahoma" w:cs="Tahoma"/>';
            if ($isItalic) $content .= '<w:i/><w:iCs/>';
            $content .= '<w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr><w:t>' . $cleanPart . '</w:t></w:r>';
        }
        $content .= '</w:p>';
    }

    $content .= '</w:body></w:document>';

    // Create ZIP (DOCX is a ZIP file)
    $zip = new ZipArchive();

    // Use XAMPP's temp directory which is world-writable
    $tempDir = '/Applications/XAMPP/xamppfiles/temp';
    if (!is_dir($tempDir) || !is_writable($tempDir)) {
        $tempDir = sys_get_temp_dir();
    }
    $tempFile = $tempDir . '/export_' . uniqid() . '.docx';

    $zipRes = $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($zipRes === TRUE) {
        // Content Types
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');

        // Relationships
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');

        // Document
        $zip->addFromString('word/document.xml', $content);

        // Word relationships
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
</Relationships>');

        $zip->close();

        // Output file
        if (file_exists($tempFile)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempFile));
            readfile($tempFile);
            unlink($tempFile);
            exit;
        } else {
            die('เกิดข้อผิดพลาด: ไม่พบไล์ที่สร้างขึ้น');
        }
    } else {
        error_log("Export Error: ZipArchive::open failed with code: " . $zipRes);
        die('ไม่สามารถสร้างไฟล์ DOCX ได้ (Zip Error: ' . $zipRes . ')');
    }
}

/**
 * Export to PDF format (simple HTML to PDF)
 */
function exportPdf($bibliographies, $projectName = '')
{
    $filename = 'bibliography_' . date('Ymd_His') . '.pdf';

    // Build HTML content
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 2.54cm; }
        body { font-family: "Tahoma", "Tahoma", Tahoma, sans-serif; font-size: 16pt; line-height: 1.5; }
        h1 { text-align: center; font-size: 18pt; margin-bottom: 20px; font-weight: bold; }
        .project { text-align: center; margin-bottom: 20px; }
        .bib { padding-left: 1.27cm; text-indent: -1.27cm; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>บรรณานุกรม</h1>';

    if ($projectName) {
        $html .= '<div class="project">' . htmlspecialchars($projectName) . '</div>';
    }

    // Thai bibliographies first
    $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
    $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');

    foreach (array_merge($thBibs, $enBibs) as $bib) {
        // Strip any other tags but allow <i> for italics
        $cleanBib = strip_tags($bib['bibliography_text'], '<i>');
        $html .= '<div class="bib">' . $cleanBib . '</div>';
    }

    $html .= '</body></html>';

    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="bibliography.html"');
    echo $html;
    exit;
}
