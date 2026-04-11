<?php

/**
 * Babybib API - Template: Export Full Report
 * ===========================================================
 * POST /api/template/export-report.php
 * 
 * Uses PHPOffice/PHPWord TemplateProcessor to clone blocks 
 * and export an academic_general report.
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

$templateId = $payload['template'] ?? 'academic_general';
$format = strtolower($payload['format'] ?? 'docx');
$coverData = $payload['coverData'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

// Currently only supported for academic_general in this refactor
if ($templateId !== 'academic_general') {
    http_response_code(400);
    die('รองรับเฉพาะรายงานวิชาการทั่วไปในตอนนี้');
}

if ($format !== 'docx') {
    http_response_code(400);
    die('รองรับเฉพาะการส่งออกเป็น DOCX ในตอนนี้');
}

// 1. Initialize Template Processor
$templatePath = __DIR__ . '/../../assets/templates/template_academic_general.docx';
if (!file_exists($templatePath)) {
    http_response_code(500);
    die('Template file not found.');
}

// Configure local tmp directory to avoid systemic permission issues
\PhpOffice\PhpWord\Settings::setTempDir(__DIR__ . '/../../tmp');

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

// 2. Map Scalar Variables
$semText = ($coverData['semester'] ?? '') === '1' ? '1' : (($coverData['semester'] ?? '') === '2' ? '2' : 'ฤดูร้อน');
$courseCode = !empty($coverData['courseCode']) ? ' (' . $coverData['courseCode'] . ')' : '';
$course = !empty($coverData['course']) ? $coverData['course'] . $courseCode : '[รายวิชา]';

$templateProcessor->setValue('report_title', $coverData['title'] ?? '[ชื่อรายงาน]');
// For authors, TemplateProcessor replaces with a single string. If multiple lines, we join them with spaces or comma as Word doesn't natively do newlines in setValue without special tags, though setValue can take `<w:br/>` if using complex value objects. We'll simplify to string.
$authors = !empty($coverData['authors']) ? str_replace("\n", "\n", trim($coverData['authors'])) : '[ชื่อ-สกุล ผู้จัดทำ]';
$studentIds = !empty($coverData['studentIds']) ? 'รหัสนักศึกษา ' . str_replace("\n", ", ", trim($coverData['studentIds'])) : '[รหัสนักศึกษา]';
$templateProcessor->setValue('report_author', $authors);
$templateProcessor->setValue('report_student_ids', $studentIds);

// The following are technically unused in this template now, but safely retained
$templateProcessor->setValue('report_instructor', $coverData['instructor'] ?? '[อาจารย์ผู้สอน]');
$templateProcessor->setValue('report_course', $course);
$templateProcessor->setValue('report_department', $coverData['department'] ?? '[ภาควิชา/คณะ]');
$templateProcessor->setValue('report_institution', $coverData['institution'] ?? '[สถาบัน]');
$templateProcessor->setValue('report_semester', $semText);
$templateProcessor->setValue('report_year', $coverData['year'] ?? '[ปีการศึกษา]');

// 2.5 Process Preface
$prefaceText = $coverData['prefaceContent'] ?? "รายงานฉบับนี้จัดทำขึ้นเพื่อใช้ประกอบการเรียนการสอนสอดคล้องกับเนื้อหาวิชา โดยรวบรวมข้อมูลที่สำคัญครบถ้วน\n\nผู้จัดทำหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะเป็นประโยชน์ต่อผู้ที่สนใจศึกษาค้นคว้า หากมีข้อผิดพลาดประการใดผู้จัดทำขอน้อมรับไว้ด้วยความเคารพ";
$prefaceLines = array_filter(array_map('trim', preg_split('/\n{2,}/', $prefaceText)));

$prefaceReps = [];
foreach ($prefaceLines as $line) {
    if (!empty($line)) {
        $line = str_replace(["\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], '', $line);
        $line = preg_replace('/\x{0E4D}\x{0E32}/u', 'ำ', $line);
        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($line, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $line = $normalized;
            }
        }
        $cleanLine = preg_replace('/\s+/u', ' ', $line);
        // Enforce first-line tab for each preface paragraph at render time.
        $prefaceReps[] = ['preface_content' => "\t" . $cleanLine];
    }
}
$templateProcessor->cloneBlock('preface_paragraphs', 0, true, false, $prefaceReps);

$prefaceSigner = $coverData['prefaceSigner'] ?? (trim(explode("\n", trim($coverData['authors'] ?? ''))[0]) ?: '[ลงลายมือชื่อผู้จัดทำ]');
$prefaceDate = $coverData['prefaceDate'] ?? ($coverData['year'] ?? '[ระบุวันที่/ปี]');

$templateProcessor->setValue('preface_signer', $prefaceSigner);
$templateProcessor->setValue('preface_date', $prefaceDate);

// 3. Process Chapters (Blocks)
// Academic general has Chapters 1, 2, 3
$chaptersData = [
    ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์ของการศึกษา', 'ขอบเขตการศึกษา', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์']],
    ['number' => 2, 'title' => 'เนื้อหา', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'เนื้อหาสาระ', 'รายละเอียดและการวิเคราะห์']],
    ['number' => 3, 'title' => 'สรุปและอภิปรายผล', 'subsections' => ['สรุปผลการศึกษา', 'อภิปรายผล', 'ข้อเสนอแนะ']],
];

// PHPWord TemplateProcessor nested block cloning requires cloning the outer block first, 
// then replacing values using the '#' suffix mechanism.
$templateProcessor->cloneBlock('chapters', count($chaptersData), true, true);

foreach ($chaptersData as $chIndex => $ch) {
    $idx = $chIndex + 1; // 1-indexed for cloned blocks
    $chNum = $ch['number'];
    
    $templateProcessor->setValue('chapter_number#' . $idx, $chNum);
    $templateProcessor->setValue('chapter_title#' . $idx, $ch['title']);
    
    // Now clone the inner block for this specific chapter
    $templateProcessor->cloneBlock('subsections#' . $idx, count($ch['subsections']), true, true);
    
    foreach ($ch['subsections'] as $subIndex => $subTitle) {
        $subIdx = $subIndex + 1;
        $subNum = $chNum . '.' . $subIdx;
        
        $templateProcessor->setValue('subsection_number#' . $idx . '#' . $subIdx, $subNum);
        $templateProcessor->setValue('subsection_title#' . $idx . '#' . $subIdx, $subTitle);
        $templateProcessor->setValue('subsection_content1#' . $idx . '#' . $subIdx, "\t" . 'กรอกเนื้อหาส่วนนี้ในไฟล์ Word ที่ export');
        $templateProcessor->setValue('subsection_content2#' . $idx . '#' . $subIdx, 'ขนาดตัวอักษร 16pt ระยะบรรทัด 1.0 ย่อหน้า 0.5 นิ้ว');
    }
}

// 3.4 Process Bibliography
$bibEntries = [];
if ($projectId > 0 && $userId) {
    try {
        $db = getDB();

        // Ensure selected project belongs to current user
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            $stmt = $db->prepare("
                SELECT b.bibliography_text, b.language, b.author_sort_key, b.year, b.year_suffix
                FROM bibliographies b
                WHERE b.project_id = ?
                ORDER BY
                    CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
                    b.author_sort_key ASC,
                    b.year ASC,
                    b.year_suffix ASC
            ");
            $stmt->execute([$projectId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                if (function_exists('sortBibliographies')) {
                    sortBibliographies($results);
                }
                if (function_exists('applyDisambiguation')) {
                    $results = applyDisambiguation($results);
                } else {
                    $results = applyDisambiguationFallback($results);
                }
            }

            foreach ($results as $row) {
                $entry = trim((string)($row['bibliography_text'] ?? ''));
                if ($entry === '') {
                    continue;
                }

                // TemplateProcessor expects plain text (not HTML tags like <i>).
                $entry = html_entity_decode(strip_tags($entry), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $entry = preg_replace('/\s+/u', ' ', $entry);
                $entry = trim((string)$entry);

                if ($entry !== '') {
                    $bibEntries[] = ['bib_content' => $entry];
                }
            }
        }
    } catch (Exception $e) {
        error_log('export-report bibliography load error: ' . $e->getMessage());
    }
}

if (empty($bibEntries)) {
    $bibEntries[] = ['bib_content' => '[ยังไม่มีรายการบรรณานุกรม - กรุณาเลือกโครงการในหน้าสร้างรายงาน]'];
}
$templateProcessor->cloneBlock('bibliography_entries', count($bibEntries), true, false, $bibEntries);

// 3.5 Process Table of Contents (TOC) mapping
// Based on user's specific page mapping:
// Preface: ก
// Ch 1: 1 (Sub 1,1,2,3,4,5 -> 1,2,3,4,5)
// Ch 2: 6 (Sub 6,7,8)
// Ch 3: 9 (Sub 9,10,11)
// Bib: 12, App: 13
$templateProcessor->setValue('toc_page_preface', 'ก');
$templateProcessor->setValue('toc_page_bib', '12');
$templateProcessor->setValue('toc_page_app', '13');

$tocChapters = [];
$pageMap = [
    1 => [
        'ch' => '1',
        'subs' => ['1', '1', '2', '3', '4', '5'] // Match specific sequence: 1.1->1, 1.2->1, 1.3->2 etc as per requirement
    ],
    2 => [
        'ch' => '6',
        'subs' => ['6', '7', '8']
    ],
    3 => [
        'ch' => '9',
        'subs' => ['9', '10', '11']
    ]
];

$templateProcessor->cloneBlock('toc_chapters', count($chaptersData), true, true);
foreach ($chaptersData as $chIndex => $ch) {
    $idx = $chIndex + 1;
    $chNum = $ch['number'];
    
    $templateProcessor->setValue('toc_chapter_number#' . $idx, $chNum);
    $templateProcessor->setValue('toc_chapter_title#' . $idx, $ch['title']);
    $templateProcessor->setValue('toc_chapter_page#' . $idx, $pageMap[$chNum]['ch']);
    
    $templateProcessor->cloneBlock('toc_subsections#' . $idx, count($ch['subsections']), true, true);
    foreach ($ch['subsections'] as $subIndex => $subTitle) {
        $subIdx = $subIndex + 1;
        $subNum = $chNum . '.' . $subIdx;
        $subPage = $pageMap[$chNum]['subs'][$subIndex] ?? '';
        
        $templateProcessor->setValue('toc_subsection_number#' . $idx . '#' . $subIdx, $subNum);
        $templateProcessor->setValue('toc_subsection_title#' . $idx . '#' . $subIdx, $subTitle);
        $templateProcessor->setValue('toc_subsection_page#' . $idx . '#' . $subIdx, $subPage);
    }
}

// 4. Save and Output
$tempFile = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'PHPW');
$templateProcessor->saveAs($tempFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="report-academic_general.docx"');
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: max-age=0');
header('Pragma: public');

readfile($tempFile);
unlink($tempFile);
exit;

function applyDisambiguationFallback($bibliographies)
{
    $groupMap = [];
    foreach ($bibliographies as $index => $bib) {
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (!empty($bib['year']) && $bib['year'] !== '0') {
            $groupMap[$key][] = $index;
        }
    }

    $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ'];

    foreach ($groupMap as $indices) {
        if (count($indices) <= 1) {
            continue;
        }

        foreach ($indices as $position => $index) {
            $bib = &$bibliographies[$index];
            $text = (string)($bib['bibliography_text'] ?? '');
            $year = (string)($bib['year'] ?? '');
            $lang = (string)($bib['language'] ?? '');

            $suffix = ($lang === 'th') ? ($thaiSuffixes[$position] ?? '') : chr(ord('a') + $position);
            if ($suffix === '' || $year === '' || $year === '0') {
                continue;
            }

            $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
            $text = str_replace('(' . $year . ')', '(' . $year . $suffix . ')', $text);
            $bib['bibliography_text'] = $text;
        }
    }

    return $bibliographies;
}
