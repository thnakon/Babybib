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
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Always start by closing the template's default <w:t> and opening one with space preservation
    $out = '</w:t><w:t xml:space="preserve">' . $escaped;
    $out = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $out);
    return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $out);
}

// Similar to formatWordText but do NOT force a leading close/open tag — useful for inserting
// intro text that should remain in the same paragraph (no forced leading line break)
function formatWordIntro($text) {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $escaped);
    return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $escaped);
}

// Special helper to handle <i> tags for Bibliography
function formatWordHtml($html) {
    // 1. Replace tags with markers
    $text = str_replace(['<i>', '</i>', '<em>', '</em>'], ['__IT_ON__', '__IT_OFF__', '__IT_ON__', '__IT_OFF__'], $html);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    // 2. Start by ensuring italics are OFF (in case template is italicized)
    $out = '</w:t><w:rPr><w:i w:val="0"/></w:rPr><w:t xml:space="preserve">';
    
    // 3. Process markers
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
    
    // 4. Handle whitespace
    $out = str_replace("\t", '</w:t><w:tab/><w:t xml:space="preserve">', $out);
    $out = str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $out);
    
    return $out;
}

// 3. Map Cover & Common Variables
$semText = ($coverData['semester'] ?? '') === '1' ? '1' : (($coverData['semester'] ?? '') === '2' ? '2' : 'ฤดูร้อน');
$courseCode = !empty($coverData['courseCode']) ? ' (' . $coverData['courseCode'] . ')' : '';
$course = !empty($coverData['course']) ? $coverData['course'] . $courseCode : '[รายวิชา]';

$templateProcessor->setValue('report_title', formatWordText($coverData['title'] ?? '[ชื่อรายงาน]'));
$templateProcessor->setValue('report_author', formatWordText($coverData['authors'] ?? '[ชื่อ-สกุล ผู้จัดทำ]'));
$templateProcessor->setValue('report_student_ids', formatWordText(!empty($coverData['studentIds']) ? 'รหัสนักศึกษา ' . str_replace("\n", ", ", trim($coverData['studentIds'])) : '[รหัสนักศึกษา]'));
$templateProcessor->setValue('report_course', formatWordText($course));
$templateProcessor->setValue('report_department', formatWordText($coverData['department'] ?? '[ภาควิชา/คณะ]'));
$templateProcessor->setValue('report_institution', formatWordText($coverData['institution'] ?? '[สถาบัน]'));
$templateProcessor->setValue('report_semester', formatWordText($semText));
$templateProcessor->setValue('report_year', formatWordText($coverData['year'] ?? '[ปีการศึกษา]'));
$templateProcessor->setValue('report_degree', formatWordText($coverData['degree'] ?? '[ปริญญา]'));
$templateProcessor->setValue('report_major', formatWordText($coverData['course'] ?? '[สาขาวิชา]')); // Major in UI maps to course
$templateProcessor->setValue('report_instructor', formatWordText($coverData['instructor'] ?? '[อาจารย์ที่ปรึกษา]'));

// Cover placeholders (English) - fallback to Thai if empty for now or placeholders
$templateProcessor->setValue('report_title_en', formatWordText($coverData['title_en'] ?? '[Research Title in English]'));
$templateProcessor->setValue('report_author_en', formatWordText($coverData['author_en'] ?? '[Author Name in English]'));
$templateProcessor->setValue('report_degree_en', formatWordText($coverData['degree_en'] ?? '[Degree Name in English]'));
$templateProcessor->setValue('report_major_en', formatWordText($coverData['major_en'] ?? '[Major Name in English]'));
$templateProcessor->setValue('report_instructor_en', formatWordText($coverData['instructor_en'] ?? '[Advisor Name in English]'));

// 4. Acknowledgment — use same pattern as preface in general report
$ackRaw = $coverData['acknowledgment_content'] ?? 'ขอขอบพระคุณอาจารย์ที่ปรึกษาที่ให้คำแนะนำ...';
$ackLines = array_filter(array_map('trim', preg_split('/\n+/', $ackRaw)));
$ackReps = [];
foreach ($ackLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $ackReps[] = ['ack_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($ackReps)) {
    $ackReps[] = ['ack_text' => formatWordText("\t" . 'ขอขอบพระคุณอาจารย์ที่ปรึกษาที่ให้คำแนะนำ...')];
}
$templateProcessor->cloneBlock('ack_paras', 0, true, false, $ackReps);

$defaultSigner = isset($coverData['authors']) ? explode("\n", $coverData['authors'])[0] : '[ชื่อผู้จัดทำ]';
$templateProcessor->setValue('acknowledgment_signer', formatWordText($coverData['acknowledgment_signer'] ?? $defaultSigner));
$templateProcessor->setValue('acknowledgment_date', formatWordText($coverData['acknowledgment_date'] ?? ($coverData['year'] ?? '[ปี]')));

// 5. Thai Abstract
$absThRaw = $coverData['abstract_th_content'] ?? 'สรุปสาระสำคัญของงานวิจัย...';
$absThLines = array_filter(array_map('trim', preg_split('/\n+/', $absThRaw)));
$absThReps = [];
foreach ($absThLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absThReps[] = ['abs_th_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($absThReps)) {
    $absThReps[] = ['abs_th_text' => formatWordText("\t" . 'สรุปสาระสำคัญของงานวิจัย...')];
}
$templateProcessor->cloneBlock('abs_th_paras', 0, true, false, $absThReps);
$templateProcessor->setValue('abstract_thai_keywords', formatWordText($coverData['keywords_th'] ?? 'คำสำคัญ 1, คำสำคัญ 2'));

// 6. English Abstract
$absEnRaw = $coverData['abstract_en_content'] ?? 'Summary of the research findings in English...';
$absEnLines = array_filter(array_map('trim', preg_split('/\n+/', $absEnRaw)));
$absEnReps = [];
foreach ($absEnLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $absEnReps[] = ['abs_en_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($absEnReps)) {
    $absEnReps[] = ['abs_en_text' => formatWordText("\t" . 'Summary of the research findings in English...')];
}
$templateProcessor->cloneBlock('abs_en_paras', 0, true, false, $absEnReps);
$templateProcessor->setValue('abstract_english_keywords', formatWordText($coverData['keywords_en'] ?? 'Keyword 1, Keyword 2'));

// 7. Figure & Table Lists (Samples for now)
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
    ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์การวิจัย', 'ขอบเขตการวิจัย', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์เฉพาะ'] ],
    ['number' => 2, 'title' => 'เอกสารและงานวิจัยที่เกี่ยวข้อง', 'subsections' => [
        'สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)', 
        'ห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)', 
        'แนวคิดเกี่ยวกับการบริการห้องสมุด', 
        'เครื่องมือที่ใช้พัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขา', 
        'กรณีศึกษา', 
        'งานวิจัยที่เกี่ยวข้อง'
    ]],
    ['number' => 3, 'title' => 'วิธีการดำเนินงาน', 'subsections' => ['ระเบียบวิธีการวิจัย', 'ขั้นตอนการดำเนินงาน', 'เครื่องมือที่ใช้ในการดำเนินงาน', 'ระยะเวลาที่ใช้ในการดำเนินงาน', 'สถิติที่ใช้ในการวิเคราะห์ข้อมูล']],
    ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => [
        'ผลการวิเคราะห์ข้อมูลจากแบบสัมภาษณ์ความต้องการ',
        'ผลการออกแบบเว็บไซต์บริการแนะนำแหล่งสารสนเทศ',
        'ผลการพัฒนาเว็บไซต์บริการแนะนำแหล่งสารสนเทศ',
        'ผลการวิเคราะห์ข้อมูลจากแบบประเมินความพึงพอใจ'
    ]],
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

$templateProcessor->setValue('toc_page_bib', $currentPage);

$appATitle = "ภาคผนวก ก\nแบบสัมภาษณ์ความต้องการ\nการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์\nสถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)";
$appBTitle = "ภาคผนวก ข\nแบบประเมินความพึงพอใจ\nการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์\nสถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)";

$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'บรรณานุกรม', 'page' => (string)$currentPage];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => str_replace("\n", " ", $appATitle), 'page' => (string)($currentPage + 1)];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => str_replace("\n", " ", $appBTitle), 'page' => (string)($currentPage + 2)];
$tocEntries[] = ['indent' => '', 'sep' => "\t", 'text' => 'ประวัติผู้วิจัย', 'page' => (string)($currentPage + 3)];

// Split precisely like the preview (Limit 23)
$p1Entries = array_slice($tocEntries, 0, 23);
$p2Entries = array_slice($tocEntries, 23);

// Prep P1
$p1Reps = [];
foreach ($p1Entries as $e) {
    $p1Reps[] = [
        'toc_p1_indent' => formatWordText($e['indent']),
        'toc_p1_text' => formatWordText($e['text']),
        'toc_p1_sep' => formatWordText($e['sep']),
        'toc_p1_page' => formatWordText($e['page'])
    ];
}
$templateProcessor->cloneBlock('toc_p1_block', count($p1Reps), true, false, $p1Reps);

// Prep P2
if (!empty($p2Entries)) {
    $templateProcessor->cloneBlock('toc_p2_visible', 1, true, false);
    $p2Reps = [];
    foreach ($p2Entries as $e) {
        $p2Reps[] = [
            'toc_p2_indent' => formatWordText($e['indent']),
            'toc_p2_text' => formatWordText($e['text']),
            'toc_p2_sep' => formatWordText($e['sep']),
            'toc_p2_page' => formatWordText($e['page'])
        ];
    }
    $templateProcessor->cloneBlock('toc_p2_block', count($p2Reps), true, false, $p2Reps);
} else {
    $templateProcessor->setValue('toc_p2_visible', '');
    $templateProcessor->setValue('/toc_p2_visible', '');
}

// Special Research Sample Content for Chapter 1 & 2
$researchSampleCh1 = [
    'ความเป็นมาและความสำคัญของปัญหา' => 'สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) (สดร.) จัดตั้งขึ้นเมื่อวันที่ 1 มกราคม พ.ศ. 2552 เป็นองค์กรภายใต้การกำกับดูแลของกระทรวงวิทยาศาสตร์และเทคโนโลยี (วท.) มีภารกิจหลักในการดำเนินการวิจัยดาราศาสตร์และฟิสิกส์ดาราศาสตร์ และให้บริการถ่ายทอดเทคโนโลยีและดาราศาสตร์สู่สังคม...',
    'วัตถุประสงค์การวิจัย' => 'เพื่อพัฒนาเว็บไซต์บริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)',
    'ขอบเขตการวิจัย' => "การพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) มีขอบเขตการศึกษาดังต่อไปนี้\n\t1.3.1 ขอบเขตด้านเนื้อหา จากการสัมภาษณ์บรรณารักษ์ห้องสมุดดาราศาสตร์ เพื่อกำหนดเนื้อหาสารสนเทศที่ต้องการ ผู้ศึกษาจึงได้รวบรวมสารสนเทศให้ตรงกับความต้องการของบรรณารักษ์ สรุปได้ดังนี้\n\t\t1) เนื้อหาสารสนเทศ ได้แก่\n\t\t\t(1) ประวัติศาสตร์ดาราศาสตร์ไทย\n\t\t\t(2) อวกาศ และวัตถุท้องฟ้า ได้แก่ ดาวเคราะห์น้อย ดาวหาง ดาวฤกษ์ อุกกาบาต ดาวเคราะห์ เป็นต้น",
    'นิยามศัพท์เฉพาะ' => "\t1.5.1 บริการแนะนำแหล่งสารสนเทศเฉพาะสาขา หมายถึง บริการสารสนเทศที่รวบรวมแหล่งสารสนเทศที่มีเนื้อหาเกี่ยวกับวิชาใดวิชาหนึ่งโดยเฉพาะ เป็นการรวบรวมสารสนเทศทั้งที่เป็นทรัพยากร...",
    'ประโยชน์ที่คาดว่าจะได้รับ' => "การศึกษาครั้งนี้มีประโยชน์ที่คาดว่าจะได้รับ ดังนี้\n\t1.4.1 ห้องสมุดดาราศาสตร์มีบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาบริการผู้ใช้ เพื่อให้ผู้ใช้บริการหรือผู้ที่ต้องการหาความรู้ทางด้านดาราศาสตร์สามารถใช้บริการแนะนำแหล่งสารสนเทศเพื่อการเรียนรู้และการวิจัย..."
];

$researchSampleCh2 = [
    'intro' => 'การศึกษาเอกสารและงานวิจัยที่เกี่ยวข้องกับการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) ผู้ศึกษาแบ่งการศึกษาออกเป็นดังนี้',
    'สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)' => "\t2.1.1 ประวัติความเป็นมาของหน่วยงาน\n\t2.1.2 บริการของหน่วยงาน",
    'ห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)' => "\t2.2.1 มาตรฐานห้องสมุดเฉพาะ\n\t2.2.2 ภารกิจหลักของห้องสมุดดาราศาสตร์\n\t2.2.3 บริการของห้องสมุดดาราศาสตร์",
    'แนวคิดเกี่ยวกับการบริการห้องสมุด' => "\t2.3.1 ความหมายและความสำคัญของห้องสมุด\n\t2.3.2 ความหมายของการบริการ\n\t2.3.3 บริการแนะนำแหล่งสารสนเทศเฉพาะสาขา",
    'เครื่องมือที่ใช้พัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขา' => "\t2.4.1 ดิจิทัลคอนเทนต์ (Digital Content)\n\t2.4.2 การออกแบบเว็บไซต์\n\t2.4.3 ภาษาสำหรับการพัฒนาเว็บไซต์\n\t2.4.4 W3Schools\n\t2.4.5 ระบบบริหารจัดการเนื้อหา (Content Management System - CMS)\n\t2.4.6 WordPress",
    'กรณีศึกษา' => '(เนื้อหากรณีศึกษาที่ศึกษา)',
    'งานวิจัยที่เกี่ยวข้อง' => '(เอกสารและงานวิจัยทั้งในส่วนของในประเทศและต่างประเทศที่เกี่ยวข้อง)'
];

// Sample content for Chapter 3 (Methods)
$researchSampleCh3 = [
    'intro' => "การศึกษาและการค้นคว้าอิสระเรื่องการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) ผู้ศึกษาได้ทำการศึกษาวิธีการดำเนินงานที่จะนำไปสู่การพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขา ดังนี้",
    'ระเบียบวิธีการวิจัย' => "การศึกษาค้นคว้าอิสระเรื่อง “การพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน)” ใช้ระเบียบวิธีการวิจัยแบบการวิจัยและพัฒนา (Research and Development)",
    'ขั้นตอนการดำเนินงาน' => "การศึกษาและการค้นคว้าอิสระเรื่องการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) มีขั้นตอนการดำเนินงาน ดังนี้\n\t3.2.1 รวบรวมความต้องการ ทบทวนวรรณกรรม และกรณีศึกษา\n\tศึกษางานบริการสารสนเทศ บริการแนะนำแหล่งสารสนเทศเฉพาะสาขา\n\tตัวอย่างเว็บไซต์บริการแนะนำแหล่งสารสนเทศเฉพาะสาขา เพื่อให้ทราบถึงรูปแบบการให้บริการ",
    'เครื่องมือที่ใช้ในการดำเนินงาน' => "เครื่องมือที่ใช้ในการดำเนินงานพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขา ประกอบด้วย ซอฟต์แวร์และฮาร์ดแวร์ ดังนี้\n\t3.3.1 ซอฟต์แวร์ (Software) ที่ใช้ในการพัฒนามีดังนี้\n\t\t1) โปรแกรม Wordpress 6.3.1 เพื่อใช้ในการสร้างเว็บไซต์\n\t\t2) โปรแกรม Xampp 8.0.23 เพื่อใช้ในการจำลองเว็บไซต์\n\t\t3) โปรแกรม Canva เพื่อใช้ในการจัดทำกราฟิกตกแต่งเว็บไซต์\n\t\t4) โปรแกรม Procreate เพื่อใช้ในการจัดทำ Favicon ของเว็บไซต์\n\n\t3.3.2 ฮาร์ดแวร์ (Hardware) ที่ใช้ในการพัฒนามีดังนี้\n\t\t1) หน่วยประมวลผลกลาง Intel(R) Core(TM) i3-5005U CPU @ 2.0GHz 2.00 GHz\n\t\t2) หน่วยความจำหลักของเครื่องคอมพิวเตอร์ คือ 4.00 GB\n\t\t3) ระบบปฏิบัติการวินโดวส์เซิร์ฟเวอร์ Windows 10 Pro version 22H2\n\n\t3.3.3 แบบสัมภาษณ์กึ่งโครงสร้าง\n\t\tแบบสัมภาษณ์กึ่งโครงสร้างสำหรับบรรณารักษ์ห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) โดยแบ่งออกเป็น 3 ตอน ดังนี้\n\t\t\tตอนที่ 1 ข้อมูลทั่วไปของบรรณารักษ์ห้องสมุดดาราศาสตร์ ได้แก่ ชื่อ-นามสกุล และตำแหน่ง",
    'ระยะเวลาที่ใช้ในการดำเนินงาน' => "ในการศึกษาอิสระครั้งนี้ ผู้ศึกษาได้กำหนดระยะเวลาในการดำเนินงานตั้งแต่เดือนมิถุนายน พ.ศ. 2566 ไปจนถึงเดือนมีนาคม พ.ศ. 2567",
    'สถิติที่ใช้ในการวิเคราะห์ข้อมูล' => "หลังจากทำการรวบรวมแบบสัมภาษณ์และแบบประเมินความพึงพอใจ ผู้ศึกษาได้ดำเนินการวิเคราะห์ข้อมูล ดังนี้\n\t3.5.1 ข้อมูลจากแบบสัมภาษณ์ โดยใช้การวิเคราะห์เชิงพรรณนา\n\t3.5.2 ข้อมูลจากแบบประเมินความพึงพอใจ โดยตอนที่ 1-4 ใช้การวิเคราะห์ระดับความพึงพอใจ โดยใช้สถิติค่าเฉลี่ย"
];

// Sample content for Chapter 4 (Results)
$researchSampleCh4 = [
    'intro' => 'การศึกษาค้นคว้าอิสระเรื่องการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ และ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) มีวัตถุประสงค์เพื่อพัฒนาเว็บไซต์บริการแนะนำแหล่งสารสนเทศเฉพาะสาขา ผลการวิเคราะห์ข้อมูลแบ่งออกเป็นดังนี้',
    'ผลการวิเคราะห์ข้อมูลจากแบบสัมภาษณ์ความต้องการ' => "จากแบบสัมภาษณ์ความต้องการของบรรณารักษ์ห้องสมุดดาราศาสตร์ และประชาชนทั่วไปที่มีความสนใจทางด้านดาราศาสตร์ต่อการพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขา ได้ผลสรุปดังนี้\n\t4.1.1 ด้านเนื้อหา: ประกอบไปด้วย วัตถุท้องฟ้า ปรากฏการณ์ดาราศาสตร์ ประวัติศาสตร์ดาราศาสตร์ไทย เทคโนโลยีดาราศาสตร์ และสิ่งมีชีวิตนอกโลก\n\t4.1.2 ด้านทรัพยากรสารสนเทศ: ประกอบด้วยสื่อสิ่งพิมพ์ (หนังสือ) และทรัพยากรอิเล็กทรอนิกส์ (e-Book, บทความบนเว็บไซต์ และแหล่งข้อมูลที่เกี่ยวข้อง)\n\t4.1.3 ด้านรูปแบบการนำเสนอ:\n\t\t1) แสดงข้อมูลติดต่อและแหล่งเชื่อมโยงไปยังห้องสมุดดาราศาสตร์\n\t\t2) เชื่อมโยงไปยังแหล่งสารสนเทศต้นฉบับผ่านหน้าเว็บไซต์\n\t\t3) แสดงบรรณานุกรม (Citation) เพื่อแจ้งที่มาของแต่ละรายการ\n\t\t4) แสดงรูปภาพและวิดีโอที่เกี่ยวกับดาราศาสตร์ประกอบเนื้อหา\n\t\t5) แสดงหัวเรื่องและหมวดหมู่ที่ชัดเจน\n\t4.1.4 ด้านข้อเสนอแนะ: ควรเลือกเฉพาะเนื้อหาที่สำคัญและจัดแบ่งหมวดหมู่ให้เข้าใจง่ายเพื่อความสะดวกในการเข้าถึง",
    'ผลการออกแบบเว็บไซต์บริการแนะนำแหล่งสารสนเทศ' => "ผลการออกแบบระบบและส่วนติดต่อผู้ใช้งาน มีรายละเอียดดังนี้\n\t4.2.1 การเลือกใช้โปรแกรม: ผู้ศึกษาได้เลือกใช้โปรแกรม WordPress ในการพัฒนา เนื่องจากเป็นระบบการจัดการเนื้อหา (CMS) ที่มีความยืดหยุ่นและรองรับความต้องการด้านการแสดงผลสื่อประสมได้ดี",
    'ผลการพัฒนาเว็บไซต์บริการแนะนำแหล่งสารสนเทศ' => "จากการดำเนินงานพัฒนาเว็บไซต์ตามขอบเขตที่กำหนด ผู้ศึกษาได้พัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาที่พร้อมใช้งาน โดยมีการจัดหมวดหมู่สารสนเทศดาราศาสตร์และเชื่อมโยงแหล่งข้อมูลที่สำคัญครบถ้วน",
    'ผลการวิเคราะห์ข้อมูลจากแบบประเมินความพึงพอใจ' => "จากการประเมินความพึงพอใจของผู้ใช้บริการต่อเว็บไซต์แนะนำแหล่งสารสนเทศเฉพาะสาขา พบว่าผู้ใช้มีความพึงพอใจในภาพรวมอยู่ในระดับมากที่สุด โดยเฉพาะในด้านความสะดวกในการเข้าถึงข้อมูลและการออกแบบที่ทันสมัย"
];

$researchSampleCh5 = [
    'intro' => 'การศึกษาค้นคว้าอิสระเรื่อง การพัฒนาบริการแนะนำแหล่งสารสนเทศเฉพาะสาขาของห้องสมุดดาราศาสตร์ สถาบันวิจัยดาราศาสตร์แห่งชาติ (องค์การมหาชน) มีวัตถุประสงค์เพื่อพัฒนาเว็บไซต์บริการแนะนำแหล่งสารสนเทศเฉพาะสาขาโดยใช้ระเบียบวิธีวิจัยและพัฒนา ผลสรุปและข้อเสนอแนะมีดังนี้',
    'สรุปผลการศึกษา' => "การวิจัยครั้งนี้เป็นการวิจัยและพัฒนา (Research and Development) ซึ่งสรุปผลได้ดังนี้\n\t5.1.1 สรุปผลด้านความต้องการ: ผู้ใช้ต้องการเข้าถึงสารสนเทศที่รวดเร็วและเชื่อมโยงไปยังแหล่งต้นฉบับได้ทันที\n\t5.1.2 สรุปผลการพัฒนาเว็บไซต์:\n\t\t1) หน้าแรก (Front Page) ประกอบด้วย 5 ส่วนสำคัญ ได้แก่ ส่วนแนะนำ, เนื้อหาดาราศาสตร์, ทรัพยากรสารสนเทศ, ภาพถ่าย และส่วนประเมินผล\n\t\t2) เมนูหลัก (Main Menu) จัดทำเป็นระบบ Drop-down Menu เพื่อความสะดวกในการเข้าถึงข้อมูลแต่ละหมวดหมู่",
    'อภิปรายผล' => "จากการดำเนินงานพบว่าการเลือกใช้ WordPress เป็นเครื่องมือหลักร่วมกับการสัมภาษณ์บรรณารักษ์ผู้เชี่ยวชาญ ทำให้เว็บไซต์สามารถตอบโจทย์ความต้องการเฉพาะทางของห้องสมุดดาราศาสตร์ได้อย่างมีประสิทธิภาพ",
    'ปัญหาและอุปสรรค' => "\t5.3.1 ด้านการปรับแต่ง: ธีมที่เลือกใช้มีข้อจำกัดในการปรับเปลี่ยนรูปลักษณ์บางส่วนตามความต้องการ\n\t5.3.2 ด้านโครงสร้างข้อมูล: การจัดหมวดหมู่บุคคลสำคัญทางดาราศาสตร์ใน WordPress ไม่สามารถแยกย่อยเป็นหมวดหมู่รองในระดับที่ละเอียดตามความต้องการที่วางไว้ได้",
    'ข้อจำกัด' => "\t5.4.1 ด้านเนื้อหา: เนื่องจากระยะเวลาที่จำกัดและเนื้อหาสารสนเทศดาราศาสตร์ที่มีความกว้างขวาง ทำให้การรวบรวมเนื้อหาในบางส่วนยังไม่ลึกพอต่อความต้องการทั้งหมด",
    'ข้อเสนอแนะ' => "\t5.5.1 ข้อเสนอแนะในการนำไปใช้: สามารถนำเว็บไซต์ไปเผยแพร่เป็นแหล่งค้นคว้าสำหรับผู้สนใจทั่วไป\n\t5.5.2 ข้อเสนอแนะเพื่อการวิจัยในอนาคต:\n\t\t1) ควรจัดหมวดหมู่ตามประเภททรัพยากรสารสนเทศเพิ่ม เพื่อเพิ่มความหลากหลายในการเข้าถึงข้อมูล"
];

// Render main document chapters (content)
foreach ($researchChapters as $chIndex => $ch) {
    $idx = $chIndex + 1;
    $templateProcessor->setValue('chapter_number#' . $idx, $ch['number']);
    $templateProcessor->setValue('chapter_title#' . $idx, $ch['title']);

    // Chapter Intro logic: support sample intros for chapters 2, 3, 4, 5
    $chIntroValue = formatWordText(' '); // Default is a blank line
    
    // Extra spacing for Chapters 2, 3, 4, 5 as requested
    if ($ch['number'] >= 2 && $ch['number'] <= 5) {
        $chIntroValue = formatWordText("\n");
    }

    if ($ch['number'] == 2 && isset($researchSampleCh2['intro'])) {
        $chIntroValue .= formatWordIntro("\t" . $researchSampleCh2['intro']);
    } elseif ($ch['number'] == 3 && isset($researchSampleCh3['intro'])) {
        $chIntroValue .= formatWordIntro("\t" . $researchSampleCh3['intro']);
    } elseif ($ch['number'] == 4 && isset($researchSampleCh4['intro'])) {
        $chIntroValue .= formatWordIntro("\t" . $researchSampleCh4['intro']);
    } elseif ($ch['number'] == 5 && isset($researchSampleCh5['intro'])) {
        $chIntroValue .= formatWordIntro("\t" . $researchSampleCh5['intro']);
    }
    $templateProcessor->setValue('chapter_intro#' . $idx, $chIntroValue);

    $subCount = count($ch['subsections']);
    $templateProcessor->cloneBlock('subsections#' . $idx, $subCount, true, true);
    foreach ($ch['subsections'] as $subIndex => $subTitle) {
        $subIdx = $subIndex + 1;
        $templateProcessor->setValue('subsection_index#' . $idx . '#' . $subIdx, formatWordText($subIdx));
        $templateProcessor->setValue('subsection_title#' . $idx . '#' . $subIdx, formatWordText($subTitle));
        
        $bodyContent = 'กรอกเนื้อหาในส่วน "' . $subTitle . '" ในที่นี้';
        if ($ch['number'] == 1 && isset($researchSampleCh1[$subTitle])) {
            $bodyContent = $researchSampleCh1[$subTitle];
        } else if ($ch['number'] == 2 && isset($researchSampleCh2[$subTitle])) {
            $bodyContent = $researchSampleCh2[$subTitle];
        } else if ($ch['number'] == 3 && isset($researchSampleCh3[$subTitle])) {
            $bodyContent = $researchSampleCh3[$subTitle];
        } else if ($ch['number'] == 4 && isset($researchSampleCh4[$subTitle])) {
            $bodyContent = $researchSampleCh4[$subTitle];
        } else if ($ch['number'] == 5 && isset($researchSampleCh5[$subTitle])) {
            $bodyContent = $researchSampleCh5[$subTitle];
        }
        
        // Ensure first line is tab-indented
        if (!isset($bodyContent[0]) || $bodyContent[0] !== "\t") {
            $bodyContent = "\t" . $bodyContent;
        }

        $templateProcessor->setValue('subsection_content#' . $idx . '#' . $subIdx, formatWordText($bodyContent));
    }
}

// 10. Appendix Pages
$templateProcessor->setValue('appendix_a_title', formatWordText($appATitle));
$templateProcessor->setValue('appendix_b_title', formatWordText($appBTitle));
$templateProcessor->setValue('appendix_a_content', '');
$templateProcessor->setValue('appendix_b_content', '');

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
            $bibEntries[] = ['bib_content' => formatWordHtml($row['bibliography_text'])];
        }
    } catch (Exception $e) {}
}
if (empty($bibEntries)) $bibEntries[] = ['bib_content' => formatWordText('(ไม่มีรายการบรรณานุกรม)')];
$templateProcessor->cloneBlock('bibliography_entries', count($bibEntries), true, false, $bibEntries);

// 12. Biography
$bioRaw = $coverData['biography_content'] ?? 'ชื่อ-สกุล ประวัติการศึกษา และผลงาน...';
$bioLines = array_filter(array_map('trim', preg_split('/\n+/', $bioRaw)));
$bioReps = [];
foreach ($bioLines as $line) {
    if (!empty($line)) {
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        $bioReps[] = ['bio_text' => formatWordText("\t" . $cleanLine)];
    }
}
if (empty($bioReps)) {
    $bioReps[] = ['bio_text' => formatWordText("\t" . 'ชื่อ-สกุล ประวัติการศึกษา และผลงาน...')];
}
$templateProcessor->cloneBlock('bio_paras', 0, true, false, $bioReps);

// Technical placeholders in biography template
$defaultDocBioName = isset($coverData['authors']) ? explode("\n", $coverData['authors'])[0] : '[ชื่อผู้วิจัย]';
$templateProcessor->setValue('bio_name', formatWordText($defaultDocBioName));
$templateProcessor->setValue('bio_dob', '');
$templateProcessor->setValue('bio_domicile', '');
$templateProcessor->setValue('bio_contact', '');
$templateProcessor->setValue('bio_edu_details', '');

// 13. Output
$tempFile = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'PHPW');
$templateProcessor->saveAs($tempFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="research-report.docx"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
unlink($tempFile);
exit;
