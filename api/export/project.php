<?php

/**
 * Babybib API - Export Project Bibliographies
 * ============================================
 * Exports all bibliographies in a project to DOCX or PDF format
 * With disambiguation support for same author-year entries
 */

require_once '../../includes/session.php';

// Require authentication
requireAuth();

$userId = getCurrentUserId();
$projectId = intval($_GET['id'] ?? 0);
$format = strtolower($_GET['format'] ?? 'docx');

if (!$projectId) {
    http_response_code(400);
    die('Project ID is required');
}

if (!in_array($format, ['docx', 'pdf'])) {
    http_response_code(400);
    die('Invalid format');
}

try {
    $db = getDB();

    // Verify project belongs to user
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        http_response_code(404);
        die('Project not found');
    }

    // Get bibliographies sorted properly
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

    // Ensure sorting follows APA 7th rules (including title fallback with article skipping)
    sortBibliographies($bibliographies);

    if (empty($bibliographies)) {
        http_response_code(404);
        die('ไม่มีรายการบรรณานุกรมในโครงการนี้');
    }

    // Apply disambiguation
    $bibliographies = applyDisambiguation($bibliographies);

    $projectName = $project['name'];

    // Generate content
    if ($format === 'docx') {
        exportDocx($bibliographies, $projectName);
    } else {
        exportPdfPreview($bibliographies, $projectName);
    }
} catch (Exception $e) {
    error_log("Project Export error: " . $e->getMessage());
    http_response_code(500);
    die('เกิดข้อผิดพลาดในการส่งออก: ' . $e->getMessage());
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

/**
 * Export to DOCX format
 */
function exportDocx($bibliographies, $projectName = '')
{
    $filename = sanitizeFilename($projectName) . '_bibliography_' . date('Ymd') . '.docx';

    // Create DOCX using XML
    $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>';

    // Title - 18pt (36 half-points), Bold, Centered
    $content .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="480"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Tahoma" w:hAnsi="Tahoma" w:eastAsia="Tahoma" w:cs="Tahoma"/><w:b/><w:sz w:val="36"/><w:szCs w:val="36"/></w:rPr><w:t>บรรณานุกรม</w:t></w:r></w:p>';

    // Thai bibliographies first
    $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
    $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');

    foreach (array_merge($thBibs, $enBibs) as $bib) {
        $text = $bib['bibliography_text'];

        // Hanging indent style (720 twips = 0.5 inch), 16pt font (32 half-points), 1.15 line spacing (276 twips)
        $content .= '<w:p><w:pPr><w:ind w:left="720" w:hanging="720"/><w:spacing w:line="276" w:lineRule="auto"/></w:pPr>';

        // Split text by <i> tags for italic handling
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

    $tempDir = '/Applications/XAMPP/xamppfiles/temp';
    if (!is_dir($tempDir) || !is_writable($tempDir)) {
        $tempDir = sys_get_temp_dir();
    }
    $tempFile = $tempDir . '/project_export_' . uniqid() . '.docx';

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
            http_response_code(500);
            die('เกิดข้อผิดพลาด: ไม่พบไฟล์ที่สร้างขึ้น');
        }
    } else {
        http_response_code(500);
        die('ไม่สามารถสร้างไฟล์ DOCX ได้');
    }
}

/**
 * Export to PDF - Show print preview page
 */
function exportPdfPreview($bibliographies, $projectName = '')
{
    // Thai bibliographies first
    $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
    $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');
    $allBibs = array_merge($thBibs, $enBibs);

    $currentDate = date('d/m/Y, H:i A');

    // Output HTML page that triggers print dialog
    header('Content-Type: text/html; charset=UTF-8');
?>
    <!DOCTYPE html>
    <html lang="th">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>บรรณานุกรม - <?php echo htmlspecialchars($projectName); ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            @page {
                size: A4;
                margin: 2.54cm;
            }

            body {
                font-family: 'Tahoma', 'Tahoma', 'Tahoma', serif;
                font-size: 16px;
                line-height: 1.5;
                color: #000;
                background: #fff;
            }

            .page-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                padding: 8px 20px;
                font-size: 10px;
                color: #666;
                display: flex;
                justify-content: space-between;
                border-bottom: 1px solid #ddd;
            }

            .page-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 8px 20px;
                font-size: 10px;
                color: #666;
                display: flex;
                justify-content: space-between;
            }

            .content {
                padding: 40px 60px;
                max-width: 210mm;
                margin: 0 auto;
            }

            .title {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 24px;
            }

            .bib-entry {
                text-indent: -0.5in;
                margin-left: 0.5in;
                margin-bottom: 12px;
                text-align: left;
                font-size: 16px;
            }

            .bib-entry i,
            .bib-entry em {
                font-style: italic;
            }

            @media print {

                .page-header,
                .page-footer {
                    position: fixed;
                }

                .content {
                    padding-top: 20px;
                }

                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }

            @media screen {
                body {
                    background: #f0f0f0;
                }

                .content {
                    background: white;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                    min-height: 100vh;
                }
            }
        </style>
    </head>

    <body>
        <div class="page-header">
            <span><?php echo $currentDate; ?></span>
            <span>บรรณานุกรม</span>
        </div>

        <div class="content">
            <div class="title">บรรณานุกรม</div>

            <?php foreach ($allBibs as $bib): ?>
                <div class="bib-entry">
                    <?php echo strip_tags($bib['bibliography_text'], '<i><em>'); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="page-footer">
            <span>about:blank</span>
            <span>1/1</span>
        </div>

        <script>
            // Auto-trigger print dialog
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };

            // Close window after print (or cancel)
            window.onafterprint = function() {
                // Optional: close the window
                // window.close();
            };
        </script>
    </body>

    </html>
<?php
    exit;
}

/**
 * Sanitize filename
 */
function sanitizeFilename($name)
{
    $name = preg_replace('/[^a-zA-Z0-9ก-๙\s\-_]/u', '', $name);
    $name = preg_replace('/\s+/', '_', trim($name));
    return $name ?: 'bibliography';
}
