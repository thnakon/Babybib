<?php

/**
 * Babybib API - Template: Export Research Report
 * ===========================================================
 * POST /api/template/export-report-research.php
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

$templateId = $payload['template'] ?? 'research';
$format = strtolower($payload['format'] ?? 'docx');
$coverData = $payload['coverData'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

if ($format !== 'docx') {
    http_response_code(400);
    die('รองรับเฉพาะการส่งออกเป็น DOCX ในตอนนี้');
}

// 1. Initialize Template Processor
$templatePath = __DIR__ . '/../../assets/templates/template_academic_research.docx';
if (!file_exists($templatePath)) {
    http_response_code(500);
    die('Template file not found.');
}

// Configure local tmp directory
\PhpOffice\PhpWord\Settings::setTempDir(__DIR__ . '/../../tmp');

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

// 2. Global Helper for Newlines in Word
function formatWordText($text) {
    return str_replace("\n", '</w:t><w:br/><w:t>', htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}

// 3. Map Cover & Common Variables
$semText = ($coverData['semester'] ?? '') === '1' ? '1' : (($coverData['semester'] ?? '') === '2' ? '2' : 'ฤดูร้อน');
$courseCode = !empty($coverData['courseCode']) ? ' (' . $coverData['courseCode'] . ')' : '';
$course = !empty($coverData['course']) ? $coverData['course'] . $courseCode : '[รายวิชา]';

$templateProcessor->setValue('report_title', $coverData['title'] ?? '[ชื่อรายงาน]');
$templateProcessor->setValue('report_author', $coverData['authors'] ?? '[ชื่อ-สกุล ผู้จัดทำ]');
$templateProcessor->setValue('report_student_ids', !empty($coverData['studentIds']) ? 'รหัสนักศึกษา ' . str_replace("\n", ", ", trim($coverData['studentIds'])) : '[รหัสนักศึกษา]');
$templateProcessor->setValue('report_course', $course);
$templateProcessor->setValue('report_department', $coverData['department'] ?? '[ภาควิชา/คณะ]');
$templateProcessor->setValue('report_institution', $coverData['institution'] ?? '[สถาบัน]');
$templateProcessor->setValue('report_semester', $semText);
$templateProcessor->setValue('report_year', $coverData['year'] ?? '[ปีการศึกษา]');
$templateProcessor->setValue('report_degree', $coverData['degree'] ?? '[ปริญญา]');
$templateProcessor->setValue('report_major', $coverData['course'] ?? '[สาขาวิชา]'); // Major in UI maps to course
$templateProcessor->setValue('report_instructor', $coverData['instructor'] ?? '[อาจารย์ที่ปรึกษา]');

// Cover placeholders (English) - fallback to Thai if empty for now or placeholders
$templateProcessor->setValue('report_title_en', $coverData['title_en'] ?? '[Research Title in English]');
$templateProcessor->setValue('report_author_en', $coverData['author_en'] ?? '[Author Name in English]');
$templateProcessor->setValue('report_degree_en', $coverData['degree_en'] ?? '[Degree Name in English]');
$templateProcessor->setValue('report_major_en', $coverData['major_en'] ?? '[Major Name in English]');
$templateProcessor->setValue('report_instructor_en', $coverData['instructor_en'] ?? '[Advisor Name in English]');

// 4. Acknowledgment — use same pattern as preface in general report
$ackRaw = $coverData['acknowledgment_content'] ?? 'ขอขอบพระคุณอาจารย์ที่ปรึกษาที่ให้คำแนะนำ...';
$ackLines = array_filter(array_map('trim', preg_split('/\n+/', $ackRaw)));
$ackReps = [];
foreach ($ackLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $ackReps[] = ['ack_text' => "\t" . $cleanLine];
    }
}
if (empty($ackReps)) {
    $ackReps[] = ['ack_text' => "\t" . 'ขอขอบพระคุณอาจารย์ที่ปรึกษาที่ให้คำแนะนำ...'];
}
$templateProcessor->cloneBlock('ack_paras', 0, true, false, $ackReps);

$defaultSigner = isset($coverData['authors']) ? explode("\n", $coverData['authors'])[0] : '[ชื่อผู้จัดทำ]';
$templateProcessor->setValue('acknowledgment_signer', $coverData['acknowledgment_signer'] ?? $defaultSigner);
$templateProcessor->setValue('acknowledgment_date', $coverData['acknowledgment_date'] ?? ($coverData['year'] ?? '[ปี]'));

// 5. Thai Abstract
$absThRaw = $coverData['abstract_th_content'] ?? 'สรุปสาระสำคัญของงานวิจัย...';
$absThLines = array_filter(array_map('trim', preg_split('/\n+/', $absThRaw)));
$absThReps = [];
foreach ($absThLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absThReps[] = ['abs_th_text' => "\t" . $cleanLine];
    }
}
if (empty($absThReps)) {
    $absThReps[] = ['abs_th_text' => "\t" . 'สรุปสาระสำคัญของงานวิจัย...'];
}
$templateProcessor->cloneBlock('abs_th_paras', 0, true, false, $absThReps);
$templateProcessor->setValue('abstract_thai_keywords', $coverData['keywords_th'] ?? 'คำสำคัญ 1, คำสำคัญ 2');

// 6. English Abstract
$absEnRaw = $coverData['abstract_en_content'] ?? 'Summary of the research findings in English...';
$absEnLines = array_filter(array_map('trim', preg_split('/\n+/', $absEnRaw)));
$absEnReps = [];
foreach ($absEnLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absEnReps[] = ['abs_en_text' => "\t" . $cleanLine];
    }
}
if (empty($absEnReps)) {
    $absEnReps[] = ['abs_en_text' => "\t" . 'Summary of the research findings in English...'];
}
$templateProcessor->cloneBlock('abs_en_paras', 0, true, false, $absEnReps);
$templateProcessor->setValue('abstract_english_keywords', $coverData['keywords_en'] ?? 'Keyword 1, Keyword 2');

// 7. Figure & Table Lists (Samples for now)
if (!empty($payload['figures'])) {
    $templateProcessor->cloneBlock('figures_entries', count($payload['figures']), true, true);
    foreach ($payload['figures'] as $i => $fig) {
        $idx = $i + 1;
        $templateProcessor->setValue('fig_number#' . $idx, $fig['number']);
        $templateProcessor->setValue('fig_title#' . $idx, $fig['title']);
        $templateProcessor->setValue('fig_page#' . $idx, $fig['page']);
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
        $templateProcessor->setValue('tab_number#' . $idx, $tab['number']);
        $templateProcessor->setValue('tab_title#' . $idx, $tab['title']);
        $templateProcessor->setValue('tab_page#' . $idx, $tab['page']);
    }
} else {
    $templateProcessor->setValue('tables_entries', '');
    $templateProcessor->setValue('tab_number', '');
    $templateProcessor->setValue('tab_title', '(ไม่มีรายการตาราง)');
    $templateProcessor->setValue('tab_page', '');
    $templateProcessor->setValue('/tables_entries', '');
}

// 8. TOC Page Numbers (Fixed Sequence Logic)
// Sequence: Ack(ก), AbsTh(ข), AbsEn(ค), TOC(ง), Figs(ฉ), Tabs(ช), Ch1(1)
$templateProcessor->setValue('toc_page_ack', 'ก');
$templateProcessor->setValue('toc_page_abs_th', 'ข');
$templateProcessor->setValue('toc_page_abs_en', 'ค');
$templateProcessor->setValue('toc_page_toc', 'ง');
$templateProcessor->setValue('toc_page_figs', 'ฉ');
$templateProcessor->setValue('toc_page_tabs', 'ช');

// 9. Chapters 1-5
$researchChapters = [
    ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์การวิจัย', 'ขอบเขตการวิจัย', 'นิยามศัพท์เฉพาะ']],
    ['number' => 2, 'title' => 'เอกสารและงานวิจัยที่เกี่ยวข้อง', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง', 'กรอบแนวคิดการวิจัย']],
    ['number' => 3, 'title' => 'วิธีดำเนินการวิจัย', 'subsections' => ['ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือที่ใช้ในการวิจัย', 'การเก็บรวบรวมข้อมูล', 'การวิเคราะห์ข้อมูล']],
    ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => ['ผลการวิเคราะห์ข้อมูล', 'ผลการทดสอบสมมติฐาน', 'สรุปผลตามวัตถุประสงค์']],
    ['number' => 5, 'title' => 'สรุป อภิปรายผล และข้อเสนอแนะ', 'subsections' => ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะ']],
];

$templateProcessor->cloneBlock('chapters', count($researchChapters), true, true);

// Unified TOC Entry Builders
$tocEntries = [
    ['indent' => '', 'sep' => "\t", 'text' => 'กิตติกรรมประกาศ', 'page' => 'ก'],
    ['indent' => '', 'sep' => "\t", 'text' => 'บทคัดย่อภาษาไทย', 'page' => 'ข'],
    ['indent' => '', 'sep' => "\t", 'text' => 'ABSTRACT', 'page' => 'ค'],
    ['indent' => '', 'sep' => "\t", 'text' => 'สารบัญ', 'page' => 'ง'],
    ['indent' => '', 'sep' => "\t", 'text' => 'สารบัญภาพ', 'page' => 'ฉ'],
    ['indent' => '', 'sep' => "\t", 'text' => 'สารบัญตาราง', 'page' => 'ช'],
];

$currentPage = 1;
foreach ($researchChapters as $ch) {
    $tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'บทที่ ' . $ch['number'] . ' ' . $ch['title'], 'page' => (string)$currentPage];
    foreach ($ch['subsections'] as $sIndex => $sTitle) {
        $tocEntries[] = ['indent' => '        ', 'sep' => "\t", 'text' => $ch['number'] . '.' . ($sIndex + 1) . ' ' . $sTitle, 'page' => (string)$currentPage];
    }
    $currentPage += count($ch['subsections']) + 1;
}

$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'บรรณานุกรม', 'page' => (string)$currentPage];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'ภาคผนวก ก', 'page' => (string)($currentPage + 1)];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'ภาคผนวก ข', 'page' => (string)($currentPage + 2)];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'ประวัติผู้วิจัย', 'page' => (string)($currentPage + 3)];

// Split precisely like the preview (Limit 23)
$p1Entries = array_slice($tocEntries, 0, 23);
$p2Entries = array_slice($tocEntries, 23);

// Prep P1
$p1Reps = [];
foreach ($p1Entries as $e) {
    $p1Reps[] = [
        'toc_p1_indent' => $e['indent'],
        'toc_p1_text' => $e['text'],
        'toc_p1_sep' => $e['sep'],
        'toc_p1_page' => $e['page']
    ];
}
$templateProcessor->cloneBlock('toc_p1_block', count($p1Reps), true, false, $p1Reps);

// Prep P2
if (!empty($p2Entries)) {
    $templateProcessor->cloneBlock('toc_p2_visible', 1, true, false);
    $p2Reps = [];
    foreach ($p2Entries as $e) {
        $p2Reps[] = [
            'toc_p2_indent' => $e['indent'],
            'toc_p2_text' => $e['text'],
            'toc_p2_sep' => $e['sep'],
            'toc_p2_page' => $e['page']
        ];
    }
    $templateProcessor->cloneBlock('toc_p2_block', count($p2Reps), true, false, $p2Reps);
} else {
    $templateProcessor->setValue('toc_p2_visible', '');
    $templateProcessor->setValue('/toc_p2_visible', '');
}

// Render main document chapters (content)
foreach ($researchChapters as $chIndex => $ch) {
    $idx = $chIndex + 1;
    $templateProcessor->setValue('chapter_number#' . $idx, $ch['number']);
    $templateProcessor->setValue('chapter_title#' . $idx, $ch['title']);
    $subCount = count($ch['subsections']);
    $templateProcessor->cloneBlock('subsections#' . $idx, $subCount, true, true);
    foreach ($ch['subsections'] as $subIndex => $subTitle) {
        $subIdx = $subIndex + 1;
        $templateProcessor->setValue('subsection_index#' . $idx . '#' . $subIdx, $subIdx);
        $templateProcessor->setValue('subsection_title#' . $idx . '#' . $subIdx, $subTitle);
        $templateProcessor->setValue('subsection_content#' . $idx . '#' . $subIdx, 'กรอกเนื้อหาในส่วน "' . $subTitle . '" ในที่นี้');
    }
}

// 10. TOC Final Pages
$templateProcessor->setValue('toc_page_bib', $currentPage);
$templateProcessor->setValue('toc_page_app_a', $currentPage + 1);
$templateProcessor->setValue('toc_page_app_b', $currentPage + 2);
$templateProcessor->setValue('toc_page_bio', $currentPage + 3);

// 11. Bibliography
$bibEntries = [];
if ($projectId > 0 && $userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT bibliography_text FROM bibliographies WHERE project_id = ? ORDER BY language DESC, author_sort_key ASC");
        $stmt->execute([$projectId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $bibEntries[] = ['bib_content' => strip_tags($row['bibliography_text'])];
        }
    } catch (Exception $e) {}
}
if (empty($bibEntries)) $bibEntries[] = ['bib_content' => '(ไม่มีรายการบรรณานุกรม)'];
$templateProcessor->cloneBlock('bibliography_entries', count($bibEntries), true, false, $bibEntries);

// 12. Biography
$bioRaw = $coverData['biography_content'] ?? 'ชื่อ-สกุล ประวัติการศึกษา และผลงาน...';
$bioLines = array_filter(array_map('trim', preg_split('/\n+/', $bioRaw)));
$bioReps = [];
foreach ($bioLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $bioReps[] = ['bio_text' => "\t" . $cleanLine];
    }
}
if (empty($bioReps)) {
    $bioReps[] = ['bio_text' => "\t" . 'ชื่อ-สกุล ประวัติการศึกษา และผลงาน...'];
}
$templateProcessor->cloneBlock('bio_paras', 0, true, false, $bioReps);

// 13. Output
$tempFile = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'PHPW');
$templateProcessor->saveAs($tempFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="research-report.docx"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
unlink($tempFile);
exit;
