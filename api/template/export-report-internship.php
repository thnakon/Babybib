<?php

/**
 * Babybib API - Template: Export Internship Report
 * ===========================================================
 * POST /api/template/export-report-internship.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

$userId = isLoggedIn() ? getCurrentUserId() : null;

// Parse payload
$rawPayload = $_POST['payload'] ?? '';
if (empty($rawPayload)) {
    http_response_code(400);
    die('ไม่พบข้อมูล payload');
}

$payload = json_decode($rawPayload, true);
if (!$payload || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die('ข้อมูล payload ไม่ถูกต้อง');
}

$templateId = $payload['template'] ?? 'internship';
$format = strtolower($payload['format'] ?? 'docx');
$coverData = $payload['coverData'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

if ($format !== 'docx') {
    http_response_code(400);
    die('รองรับเฉพาะการส่งออกเป็น DOCX ในตอนนี้');
}

// 1. Initialize Template Processor
// Prefer a dedicated internship template, fallback to existing templates if missing
$templatePath = __DIR__ . '/../../assets/templates/template_internship_report.docx';
if (!file_exists($templatePath)) {
    $fallbacks = [
        __DIR__ . '/../../assets/templates/template_academic_general_logo.docx',
        __DIR__ . '/../../assets/templates/template_academic_research.docx',
        __DIR__ . '/../../assets/templates/template_academic_general.docx'
    ];
    foreach ($fallbacks as $f) {
        if (file_exists($f)) {
            $templatePath = $f;
            break;
        }
    }
}
if (!file_exists($templatePath)) {
    http_response_code(500);
    die('Template file not found.');
}

// Configure local tmp directory
\PhpOffice\PhpWord\Settings::setTempDir(__DIR__ . '/../../tmp');

try {
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('PHPWord TemplateProcessor error: ' . $e->getMessage());
    // Include message in response during local development to aid debugging
    die('Error initializing template processor: ' . $e->getMessage());
}

// 1.5 Resolve logo image (same approach as other export handlers)
$logoTempFile = null;
$logoMeta = null;
if (function_exists('resolveLogoForWord')) {
    $logoMeta = resolveLogoForWord($coverData);
}
if ($logoMeta && !empty($logoMeta['bytes'])) {
    $ext = $logoMeta['ext'] ?? 'png';
    $logoTempFile = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'LOGO_');
    if ($logoTempFile !== false) {
        $logoPathWithExt = $logoTempFile . '.' . $ext;
        if (@file_put_contents($logoPathWithExt, $logoMeta['bytes']) !== false) {
            @unlink($logoTempFile);
            $logoTempFile = $logoPathWithExt;
            $templateProcessor->setImageValue('cover_logo', [
                'path' => $logoTempFile,
                'width' => 130,
                'height' => 130,
                'ratio' => true
            ]);
        } else {
            @unlink($logoTempFile);
            $logoTempFile = null;
            $templateProcessor->setValue('cover_logo', '');
        }
    } else {
        $templateProcessor->setValue('cover_logo', '');
    }
} else {
    $templateProcessor->setValue('cover_logo', '');
}

// Helper to inject run properties for bold and size (size in points)
function formatWordStyled($text, $bold = false, $sizePt = null) {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $rpr = '';
    if ($bold) $rpr .= '<w:b/>';
    if (!is_null($sizePt)) {
        // Word stores size in half-points
        $half = (int)round($sizePt * 2);
        $rpr .= '<w:sz w:val="' . $half . '"/>';
    }
    if ($rpr !== '') {
        $out = '</w:t><w:rPr>' . $rpr . '</w:rPr><w:t xml:space="preserve">' . $escaped;
    } else {
        $out = '</w:t><w:t xml:space="preserve">' . $escaped;
    }
    $out = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $out);
    return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $out);
}

// 2. Global Helper for Newlines in Word
function formatWordText($text) {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $out = '</w:t><w:t xml:space="preserve">' . $escaped;
    $out = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $out);
    return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $out);
}

function formatWordIntro($text) {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $escaped);
    return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $escaped);
}

function formatWordHtml($html) {
    $text = str_replace(['<i>', '</i>', '<em>', '</em>'], ['__IT_ON__', '__IT_OFF__', '__IT_ON__', '__IT_OFF__'], $html);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $out = '</w:t><w:rPr><w:i w:val="0"/></w:rPr><w:t xml:space="preserve">';
    $parts = preg_split('/(__IT_ON__|__IT_OFF__)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $part) {
        if ($part === '__IT_ON__') {
            $out .= '</w:t><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">';
        } elseif ($part === '__IT_OFF__') {
            $out .= '</w:t><w:rPr><w:i w:val="0"/></w:rPr><w:t xml:space="preserve">';
        } else {
            $out .= $part;
        }
    }
    $out = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $out);
    $out = str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $out);
    return $out;
}

// 3. Map Cover & Common Variables (Internship report)
$courseCode = !empty($coverData['courseCode']) ? ' (' . $coverData['courseCode'] . ')' : '';
$course = !empty($coverData['course']) ? $coverData['course'] . $courseCode : '[รายวิชา/หน่วยงาน]';

// Cover: Title needs bold 22pt; other cover fields bold 18pt
$titleText = $coverData['title'] ?? '[รายงานการฝึกงาน]';
$templateProcessor->setValue('report_title', formatWordStyled($titleText, true, 22));
$templateProcessor->setValue('report_author', formatWordStyled($coverData['authors'] ?? '[ชื่อ-สกุล ผู้ฝึกงาน]', true, 18));
$templateProcessor->setValue('report_student_ids', formatWordStyled(!empty($coverData['studentIds']) ? 'รหัสนักศึกษา ' . str_replace("\n", ", ", trim($coverData['studentIds'])) : '[รหัสนักศึกษา]', true, 18));
$templateProcessor->setValue('report_course', formatWordStyled($course, true, 18));
$templateProcessor->setValue('report_department', formatWordStyled($coverData['department'] ?? '[หน่วยงาน/ภาควิชา]', true, 18));
$templateProcessor->setValue('report_institution', formatWordStyled($coverData['institution'] ?? '[สถานประกอบการ]', true, 18));
$templateProcessor->setValue('report_supervisor', formatWordStyled($coverData['supervisor'] ?? '[ผู้ควบคุมการฝึกงาน]', true, 18));
$templateProcessor->setValue('report_duration', formatWordStyled($coverData['duration'] ?? '[ระยะเวลาการฝึกงาน]', true, 18));

// English fallbacks
// English fallbacks (styled smaller)
$templateProcessor->setValue('report_title_en', formatWordStyled($coverData['title_en'] ?? '[Internship Title in English]', true, 18));
$templateProcessor->setValue('report_author_en', formatWordStyled($coverData['author_en'] ?? '[Author Name in English]', true, 18));

// Semester and year placeholders (needed on cover pages)
$semText = ($coverData['semester'] ?? '') === '1' ? '1' : (($coverData['semester'] ?? '') === '2' ? '2' : 'ฤดูร้อน');
$templateProcessor->setValue('report_semester', formatWordStyled($semText, false, 18));
$templateProcessor->setValue('report_year', formatWordStyled($coverData['year'] ?? '[ปีการศึกษา]', false, 18));

// 4. Acknowledgment
$ackRaw = $coverData['acknowledgment_content'] ?? 'ขอขอบพระคุณผู้ให้โอกาสและผู้ให้คำแนะนำตลอดการฝึกงาน...';
$ackLines = array_filter(array_map('trim', preg_split('/\n+/', $ackRaw)));
$ackReps = [];
foreach ($ackLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $ackReps[] = ['ack_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($ackReps)) {
    $ackReps[] = ['ack_text' => formatWordText("\t" . 'ขอขอบพระคุณผู้ให้โอกาสและผู้ให้คำแนะนำตลอดการฝึกงาน...')];
}
$templateProcessor->cloneBlock('ack_paras', 0, true, false, $ackReps);
$templateProcessor->setValue('acknowledgment_signer', formatWordText($coverData['acknowledgment_signer'] ?? (isset($coverData['authors']) ? explode("\n", $coverData['authors'])[0] : '[ชื่อผู้ฝึกงาน]')));
$templateProcessor->setValue('acknowledgment_date', formatWordText($coverData['acknowledgment_date'] ?? ($coverData['year'] ?? '[ปี]')));

// 5. Abstract (Thai & English)
$absThRaw = $coverData['abstract_th_content'] ?? 'สรุปสาระสำคัญของการฝึกงาน...';
$absThLines = array_filter(array_map('trim', preg_split('/\n+/', $absThRaw)));
$absThReps = [];
foreach ($absThLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absThReps[] = ['abs_th_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($absThReps)) $absThReps[] = ['abs_th_text' => formatWordText("\t" . 'สรุปสาระสำคัญของการฝึกงาน...')];
$templateProcessor->cloneBlock('abs_th_paras', 0, true, false, $absThReps);
$templateProcessor->setValue('abstract_thai_keywords', formatWordText($coverData['keywords_th'] ?? 'คำสำคัญ 1, คำสำคัญ 2'));

$absEnRaw = $coverData['abstract_en_content'] ?? 'Summary of the internship activities and findings...';
$absEnLines = array_filter(array_map('trim', preg_split('/\n+/', $absEnRaw)));
$absEnReps = [];
foreach ($absEnLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absEnReps[] = ['abs_en_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($absEnReps)) $absEnReps[] = ['abs_en_text' => formatWordText("\t" . 'Summary of the internship activities and findings...')];
$templateProcessor->cloneBlock('abs_en_paras', 0, true, false, $absEnReps);
$templateProcessor->setValue('abstract_english_keywords', formatWordText($coverData['keywords_en'] ?? 'Keyword 1, Keyword 2'));

// 6. Figures & Tables (samples)
if (!empty($payload['figures'])) {
    $templateProcessor->cloneBlock('figures_entries', count($payload['figures']), true, true);
    foreach ($payload['figures'] as $i => $fig) {
        $idx = $i + 1;
        $templateProcessor->setValue('fig_number#' . $idx, formatWordText($fig['number']));
        $templateProcessor->setValue('fig_title#' . $idx, formatWordText($fig['title']));
        $templateProcessor->setValue('fig_page#' . $idx, formatWordText($fig['page']));
    }
} else {
    $templateProcessor->setValue('figures_entries', '');
    $templateProcessor->setValue('fig_number', '');
    $templateProcessor->setValue('fig_title', '(ไม่มีรายการภาพประกอบ)');
    $templateProcessor->setValue('fig_page', '');
    $templateProcessor->setValue('/figures_entries', '');
}

if (!empty($payload['tables'])) {
    $templateProcessor->cloneBlock('tables_entries', count($payload['tables']), true, true);
    foreach ($payload['tables'] as $i => $tab) {
        $idx = $i + 1;
        $templateProcessor->setValue('tab_number#' . $idx, formatWordText($tab['number']));
        $templateProcessor->setValue('tab_title#' . $idx, formatWordText($tab['title']));
        $templateProcessor->setValue('tab_page#' . $idx, formatWordText($tab['page']));
    }
} else {
    $templateProcessor->setValue('tables_entries', '');
    $templateProcessor->setValue('tab_number', '');
    $templateProcessor->setValue('tab_title', '(ไม่มีรายการตาราง)');
    $templateProcessor->setValue('tab_page', '');
    $templateProcessor->setValue('/tables_entries', '');
}

// 7. TOC placeholders (simple fixed markers)
$templateProcessor->setValue('toc_page_ack', 'ก');
$templateProcessor->setValue('toc_page_abs_th', 'ข');
$templateProcessor->setValue('toc_page_abs_en', 'ค');
$templateProcessor->setValue('toc_page_toc', 'ง');

// 8. Chapters — use a simple internship structure
$internshipChapters = [
    ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและวัตถุประสงค์', 'ขอบเขตและระยะเวลา'] ],
    ['number' => 2, 'title' => 'รายละเอียดสถานประกอบการ', 'subsections' => ['ประวัติและลักษณะของสถานประกอบการ', 'โครงสร้างการทำงาน'] ],
    ['number' => 3, 'title' => 'ลักษณะงานที่ปฏิบัติ', 'subsections' => ['หน้าที่ความรับผิดชอบ', 'โครงการ/งานที่ร่วมปฏิบัติ'] ],
    ['number' => 4, 'title' => 'ผลการปฏิบัติงาน', 'subsections' => ['ผลงานที่ได้', 'ปัญหาและอุปสรรค'] ],
    ['number' => 5, 'title' => 'สรุปและข้อเสนอแนะ', 'subsections' => ['สรุปผลการฝึกงาน', 'ข้อเสนอแนะ'] ],
];

$templateProcessor->cloneBlock('chapters', count($internshipChapters), true, true);

$tocEntries = [];
$currentPage = 1;
foreach ($internshipChapters as $ch) {
    $tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'บทที่ ' . $ch['number'] . ' ' . $ch['title'], 'page' => (string)$currentPage];
    foreach ($ch['subsections'] as $sIndex => $sTitle) {
        $tocEntries[] = ['indent' => '        ', 'sep' => "\t", 'text' => $ch['number'] . '.' . ($sIndex + 1) . ' ' . $sTitle, 'page' => (string)$currentPage];
    }
    $currentPage += count($ch['subsections']) + 1;
}

$templateProcessor->setValue('toc_page_bib', $currentPage);

// Render chapters content
foreach ($internshipChapters as $chIndex => $ch) {
    $idx = $chIndex + 1;
    $templateProcessor->setValue('chapter_number#' . $idx, $ch['number']);
    $templateProcessor->setValue('chapter_title#' . $idx, $ch['title']);

    $chIntroValue = formatWordText(' ');
    if ($ch['number'] >= 2) {
        $chIntroValue = formatWordText("\n");
    }
    $templateProcessor->setValue('chapter_intro#' . $idx, $chIntroValue);

    $subCount = count($ch['subsections']);
    $templateProcessor->cloneBlock('subsections#' . $idx, $subCount, true, true);
    foreach ($ch['subsections'] as $subIndex => $subTitle) {
        $subIdx = $subIndex + 1;
        $templateProcessor->setValue('subsection_index#' . $idx . '#' . $subIdx, formatWordText($subIdx));
        $templateProcessor->setValue('subsection_title#' . $idx . '#' . $subIdx, formatWordText($subTitle));
        $bodyContent = "\tกรอกเนื้อหาในส่วน \"" . $subTitle . "\" ของรายงานการฝึกงาน";
        $templateProcessor->setValue('subsection_content#' . $idx . '#' . $subIdx, formatWordText($bodyContent));
    }
}

// 9. Bibliography (optional)
$bibEntries = [];
if ($projectId > 0 && $userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT bibliography_text FROM bibliographies WHERE project_id = ? ORDER BY language DESC, author_sort_key ASC");
        $stmt->execute([$projectId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $bibEntries[] = ['bib_content' => formatWordHtml($row['bibliography_text'])];
        }
    } catch (Exception $e) {}
}
if (empty($bibEntries)) $bibEntries[] = ['bib_content' => formatWordText('(ไม่มีรายการบรรณานุกรม)')];
$templateProcessor->cloneBlock('bibliography_entries', count($bibEntries), true, false, $bibEntries);

// 10. Biography / Appendices placeholders
$templateProcessor->setValue('bio_name', formatWordText($coverData['authors'] ?? '[ชื่อผู้ฝึกงาน]'));
$templateProcessor->setValue('bio_dob', '');
$templateProcessor->setValue('bio_contact', '');
$templateProcessor->cloneBlock('bio_paras', 0, true, false, [['bio_text' => formatWordText("\t" . ($coverData['biography_content'] ?? 'ประวัติผู้ฝึกงาน...'))]]);

// 11. Output
$tempFile = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'PHPW');
$templateProcessor->saveAs($tempFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="internship-report.docx"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
unlink($tempFile);
exit;

// Resolve logo helper (copied from export-report-logo.php)
function resolveLogoForWord($coverData)
{
    $logoDataUrl = trim((string)($coverData['logoDataUrl'] ?? ''));
    if ($logoDataUrl !== '' && preg_match('#^data:(image/(png|jpeg|jpg|webp));base64,#i', $logoDataUrl, $matches)) {
        $binary = base64_decode(substr($logoDataUrl, strpos($logoDataUrl, ',') + 1), true);
        if ($binary !== false) {
            $subtype = strtolower($matches[2]);
            $extensionMap = ['png' => 'png', 'jpeg' => 'jpeg', 'jpg' => 'jpg', 'webp' => 'webp'];
            return [
                'bytes' => $binary,
                'ext' => $extensionMap[$subtype] ?? 'png',
            ];
        }
    }

    $defaultLogoPath = __DIR__ . '/../../assets/images/Chiang_Mai_University.svg.png';
    if (is_file($defaultLogoPath)) {
        return [
            'bytes' => file_get_contents($defaultLogoPath),
            'ext' => 'png',
        ];
    }

    return null;
}
