<?php

/**
 * Babybib API - Template: Export Full Report
 * ===========================================================
 * POST /api/template/export-report.php
 * Body: payload (JSON string)
 *
 * Generates a full-document DOCX or PDF from template data
 * including cover page, chapter structure, and bibliography.
 *
 * Font: Angsana New (standard Thai academic)
 * Margins: 1.5" left/top, 1" right/bottom
 * Body: 16pt, 1.5 line spacing
 * Headings: 18pt Bold Centered
 * Title (cover): 20pt Bold Centered
 */

require_once '../../includes/session.php';
require_once '../../includes/functions.php';

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
$formatSettings = $payload['formatSettings'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

$validTemplates = ['academic_general', 'academic_general_logo', 'research', 'internship', 'project', 'thesis', 'thesis_master'];
if (!in_array($templateId, $validTemplates)) {
    http_response_code(400);
    die('Template ไม่ถูกต้อง');
}

if (!in_array($format, ['docx', 'pdf'])) {
    http_response_code(400);
    die('รูปแบบไม่ถูกต้อง');
}

if ($templateId === 'internship' && $format === 'docx') {
    $staticDocxPath = dirname(__DIR__, 2) . '/docs/template-รายงานผลการฝึกประสบการณ์.docx';
    if (!is_file($staticDocxPath)) {
        http_response_code(500);
        die('ไม่พบไฟล์แม่แบบรายงานฝึกงาน');
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode(basename($staticDocxPath)));
    header('Content-Length: ' . filesize($staticDocxPath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    readfile($staticDocxPath);
    exit;
}

// Sanitize cover data
$cover = [];
$allowedCoverKeys = ['title', 'authors', 'studentIds', 'course', 'courseCode', 'instructor', 'department',
    'institution', 'company', 'supervisor', 'projectType', 'internshipPeriod', 'degree', 'major',
    'committee', 'prefaceContent', 'prefaceSigner', 'prefaceDate', 'semester', 'year', 'logoDataUrl'];
foreach ($allowedCoverKeys as $key) {
    $value = htmlspecialchars_decode(strip_tags($coverData[$key] ?? ''), ENT_QUOTES);
    $value = str_replace(["\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], '', $value);
    $value = preg_replace('/\x{0E4D}\x{0E32}/u', 'ำ', $value);
    if (class_exists('Normalizer')) {
        $normalized = Normalizer::normalize($value, Normalizer::FORM_C);
        if ($normalized !== false) {
            $value = $normalized;
        }
    }
    $cover[$key] = $value;
}

// Font & margin settings
$marginMap = [
    'standard' => ['top' => 2160, 'bottom' => 1440, 'left' => 2160, 'right' => 1440],
    'wide'     => ['top' => 2880, 'bottom' => 2160, 'left' => 2880, 'right' => 2160],
    'narrow'   => ['top' => 1440, 'bottom' => 1440, 'left' => 1440, 'right' => 1440],
];
$marginKey = in_array($formatSettings['margin'] ?? '', array_keys($marginMap)) ? $formatSettings['margin'] : 'standard';
$margins = $marginMap[$marginKey];

$allowedFonts = ['Angsana New', 'TH Sarabun New', 'TH Niramit AS', 'Times New Roman'];
$font = in_array($formatSettings['font'] ?? '', $allowedFonts) ? $formatSettings['font'] : 'Angsana New';

$allowedSizes = [14, 15, 16];
$bodyPt = in_array(intval($formatSettings['bodySize'] ?? 16), $allowedSizes) ? intval($formatSettings['bodySize']) : 16;

// Load bibliographies
$bibliographies = [];
if ($projectId > 0) {
    if (!$userId) {
        http_response_code(401);
        die('กรุณาเข้าสู่ระบบเพื่อใช้งานบรรณานุกรมจากโครงการ');
    }
    try {
        $db = getDB();

        $stmt = $db->prepare("SELECT id, name FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch();

        if ($project) {
            $stmt = $db->prepare("
                SELECT b.id, b.bibliography_text, b.language, b.author_sort_key, b.year, b.year_suffix
                FROM bibliographies b
                WHERE b.project_id = ?
                ORDER BY
                    CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
                    b.author_sort_key ASC,
                    b.year ASC,
                    b.year_suffix ASC
            ");
            $stmt->execute([$projectId]);
            $bibliographies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sortBibliographies($bibliographies);
            $bibliographies = applyDisambiguation($bibliographies);
        }
    } catch (Exception $e) {
        error_log("export-report bibliography load error: " . $e->getMessage());
    }
}

// Template section definitions (PHP mirror of JS)
$templateDefs = [
    'academic_general' => [
        'name' => 'รายงานวิชาการทั่วไป',
        'coverType' => 'academic',
        'hasInnerCover' => true,
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์ของการศึกษา', 'ขอบเขตการศึกษา', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์']],
            ['number' => 2, 'title' => 'เนื้อหา', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'เนื้อหาสาระ', 'รายละเอียดและการวิเคราะห์']],
            ['number' => 3, 'title' => 'สรุปและอภิปรายผล', 'subsections' => ['สรุปผลการศึกษา', 'อภิปรายผล', 'ข้อเสนอแนะ']],
        ],
        'hasPreface' => true, 'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
    ],
    'academic_general_logo' => [
        'name' => 'รายงานวิชาการทั่วไป พร้อม Logo',
        'coverType' => 'academic',
        'showLogo' => true,
        'hasInnerCover' => false,
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์ของการศึกษา', 'ขอบเขตการศึกษา', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์']],
            ['number' => 2, 'title' => 'เนื้อหา', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'เนื้อหาสาระ', 'รายละเอียดและการวิเคราะห์']],
            ['number' => 3, 'title' => 'สรุปและอภิปรายผล', 'subsections' => ['สรุปผลการศึกษา', 'อภิปรายผล', 'ข้อเสนอแนะ']],
        ],
        'hasPreface' => true, 'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
    ],
    'research' => [
        'name' => 'รายงานการวิจัย',
        'coverType' => 'academic',
        'hasInnerCover' => true,
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์การวิจัย', 'ขอบเขตการวิจัย', 'นิยามศัพท์เฉพาะ']],
            ['number' => 2, 'title' => 'เอกสารและงานวิจัยที่เกี่ยวข้อง', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง', 'กรอบแนวคิดการวิจัย']],
            ['number' => 3, 'title' => 'วิธีดำเนินการวิจัย', 'subsections' => ['ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือที่ใช้ในการวิจัย', 'การเก็บรวบรวมข้อมูล', 'การวิเคราะห์ข้อมูล']],
            ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => ['ผลการวิเคราะห์ข้อมูล', 'ผลการทดสอบสมมติฐาน', 'สรุปผลตามวัตถุประสงค์']],
            ['number' => 5, 'title' => 'สรุป อภิปรายผล และข้อเสนอแนะ', 'subsections' => ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะ']],
        ],
        'hasPreface' => false, 'hasToc' => true, 'hasAbstract' => true, 'hasAbstractEnglish' => true, 'hasAcknowledgment' => true, 'hasAppendix' => true, 'hasTableList' => true, 'hasFigureList' => true, 'hasBiography' => true,
    ],
    'internship' => [
        'name' => 'รายงานฝึกงาน / สหกิจ',
        'coverType' => 'internship',
        'showLogo' => true,
        'fixedCoverTitle' => 'รายงานผลการฝึกประสบการณ์วิชาชีพสารสนเทศ',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตของรายงาน', 'ประโยชน์ที่ได้รับ']],
            ['number' => 2, 'title' => 'ข้อมูลสถานประกอบการ', 'subsections' => ['ประวัติและความเป็นมา', 'วิสัยทัศน์ พันธกิจ', 'โครงสร้างองค์กร', 'ลักษณะการดำเนินงาน']],
            ['number' => 3, 'title' => 'งานที่ได้รับมอบหมาย', 'subsections' => ['ลักษณะตำแหน่งงาน', 'งานที่ได้รับมอบหมายหลัก', 'ขั้นตอนและวิธีการปฏิบัติงาน']],
            ['number' => 4, 'title' => 'ผลการปฏิบัติงาน', 'subsections' => ['ผลการปฏิบัติงานโดยภาพรวม', 'ปัญหาและอุปสรรค', 'วิธีแก้ปัญหา']],
            ['number' => 5, 'title' => 'สรุปและข้อเสนอแนะ', 'subsections' => ['สรุปผลการฝึกงาน', 'ความรู้และทักษะที่ได้รับ', 'ข้อเสนอแนะ']],
        ],
        'hasPreface' => false, 'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
    ],
    'project' => [
        'name' => 'รายงานโครงการ',
        'coverType' => 'project',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ที่มาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตของโครงการ', 'ประโยชน์ที่คาดว่าจะได้รับ']],
            ['number' => 2, 'title' => 'แนวคิด ทฤษฎี และงานที่เกี่ยวข้อง', 'subsections' => ['ทฤษฎีที่เกี่ยวข้อง', 'เทคโนโลยีที่ใช้', 'งานที่เกี่ยวข้อง']],
            ['number' => 3, 'title' => 'การออกแบบและพัฒนา', 'subsections' => ['การวิเคราะห์ความต้องการ', 'การออกแบบระบบ/ผลิตภัณฑ์', 'ขั้นตอนการพัฒนา']],
            ['number' => 4, 'title' => 'ผลการดำเนินงาน', 'subsections' => ['ผลลัพธ์ที่ได้', 'การทดสอบ', 'ปัญหาและแนวทางแก้ไข']],
            ['number' => 5, 'title' => 'สรุปและข้อเสนอแนะ', 'subsections' => ['สรุปผลโครงการ', 'ข้อเสนอแนะ', 'แนวทางการพัฒนาต่อ']],
        ],
        'hasPreface' => false, 'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
    ],
    'thesis' => [
        'name' => 'วิทยานิพนธ์ / สารนิพนธ์',
        'coverType' => 'thesis',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญ', 'คำถามวิจัย', 'วัตถุประสงค์', 'สมมติฐาน', 'ขอบเขต', 'นิยามศัพท์', 'ประโยชน์']],
            ['number' => 2, 'title' => 'วรรณกรรมและงานวิจัยที่เกี่ยวข้อง', 'subsections' => ['กรอบแนวคิด', 'ทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง']],
            ['number' => 3, 'title' => 'วิธีดำเนินการวิจัย', 'subsections' => ['รูปแบบการวิจัย', 'ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือวิจัย', 'การตรวจสอบคุณภาพ', 'การเก็บข้อมูล', 'การวิเคราะห์']],
            ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => ['ลักษณะกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ตามวัตถุประสงค์']],
            ['number' => 5, 'title' => 'สรุป อภิปรายผล และข้อเสนอแนะ', 'subsections' => ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยต่อไป']],
        ],
        'hasPreface' => false, 'hasToc' => true, 'hasAbstract' => true, 'hasAcknowledgment' => true, 'hasAppendix' => true,
    ],
    'thesis_master' => [
        'name' => 'วิทยานิพนธ์ ป.โท',
        'coverType' => 'thesis',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญ', 'คำถามวิจัย', 'วัตถุประสงค์', 'สมมติฐาน', 'ขอบเขต', 'นิยามศัพท์', 'ประโยชน์']],
            ['number' => 2, 'title' => 'วรรณกรรมและงานวิจัยที่เกี่ยวข้อง', 'subsections' => ['กรอบแนวคิด', 'ทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง']],
            ['number' => 3, 'title' => 'วิธีดำเนินการวิจัย', 'subsections' => ['รูปแบบการวิจัย', 'ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือวิจัย', 'การตรวจสอบคุณภาพ', 'การเก็บข้อมูล', 'การวิเคราะห์']],
            ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => ['ลักษณะกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ตามวัตถุประสงค์']],
            ['number' => 5, 'title' => 'สรุป อภิปรายผล และข้อเสนอแนะ', 'subsections' => ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยต่อไป']],
        ],
        'hasPreface' => false, 'hasToc' => true, 'hasAbstract' => true, 'hasAcknowledgment' => true, 'hasAppendix' => true,
    ],
];

$tpl = $templateDefs[$templateId] ?? $templateDefs['academic_general'];

const DEFAULT_TEMPLATE_LOGO_FILE = __DIR__ . '/../../assets/images/Chiang_Mai_University.svg.png';

if ($format === 'docx') {
    exportFullDocx($tpl, $cover, $bibliographies, $margins, $font, $bodyPt);
} else {
    exportPdfPreview($tpl, $cover, $bibliographies, $margins, $font, $bodyPt);
}

function resolveTemplateLogoBinary($cover, $tpl)
{
    if (empty($tpl['showLogo'])) {
        return null;
    }

    $logoDataUrl = trim((string)($cover['logoDataUrl'] ?? ''));
    if ($logoDataUrl !== '' && preg_match('#^data:(image/(png|jpeg|jpg|webp));base64,#i', $logoDataUrl, $matches)) {
        $binary = base64_decode(substr($logoDataUrl, strpos($logoDataUrl, ',') + 1), true);
        if ($binary !== false) {
            $subtype = strtolower($matches[2]);
            $extensionMap = ['png' => 'png', 'jpeg' => 'jpeg', 'jpg' => 'jpg', 'webp' => 'webp'];
            return [
                'bytes' => $binary,
                'mime' => strtolower($matches[1]),
                'ext' => $extensionMap[$subtype] ?? 'png',
            ];
        }
    }

    if (is_file(DEFAULT_TEMPLATE_LOGO_FILE)) {
        return [
            'bytes' => file_get_contents(DEFAULT_TEMPLATE_LOGO_FILE),
            'mime' => 'image/png',
            'ext' => 'png',
        ];
    }

    return null;
}

function resolveTemplateLogoDataUri($cover, $tpl)
{
    $logo = resolveTemplateLogoBinary($cover, $tpl);
    if (!$logo || empty($logo['bytes']) || empty($logo['mime'])) {
        return '';
    }

    return 'data:' . $logo['mime'] . ';base64,' . base64_encode($logo['bytes']);
}

function buildWordInlineImage($relationshipId, $name, $cx, $cy)
{
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_XML1, 'UTF-8');
    return '<w:r>'
        . '<w:drawing>'
        . '<wp:inline distT="0" distB="0" distL="0" distR="0" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
        . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
        . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
        . '<wp:docPr id="1" name="' . $safeName . '"/>'
        . '<wp:cNvGraphicFramePr/>'
        . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
        . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<pic:nvPicPr><pic:cNvPr id="0" name="' . $safeName . '"/><pic:cNvPicPr/></pic:nvPicPr>'
        . '<pic:blipFill><a:blip r:embed="' . $relationshipId . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>'
        . '<a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
        . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
        . '</pic:pic>'
        . '</a:graphicData>'
        . '</a:graphic>'
        . '</wp:inline>'
        . '</w:drawing>'
        . '</w:r>';
}

// ======================================================
//  DOCX Export
// ======================================================
function exportFullDocx($tpl, $cover, $bibliographies, $margins, $font, $bodyPt)
{
    $titleSz   = 40;   // 20pt in half-points
    $headingSz = 36;   // 18pt
    $prefaceHeadingSz = 36; // 18pt
    $subSz     = 32;   // 16pt (or use bodyPt)
    $bodySz    = $bodyPt * 2; // e.g. 32 for 16pt

    $lineSpacing = 360; // 1.5 lines (auto)
    $paraAfter   = 0;
    $paraBefore  = 0;
    $isAcademicGeneralDocument = !empty($tpl['hasPreface']) && $tpl['coverType'] === 'academic';
    $logoAsset = resolveTemplateLogoBinary($cover, $tpl);
    $hasCoverLogo = !empty($tpl['showLogo']) && !empty($logoAsset['bytes']);

    $m = $margins;
    $sectPr = '<w:sectPr>'
        . '<w:pgSz w:w="11906" w:h="16838"/>'
        . "<w:pgMar w:top=\"{$m['top']}\" w:right=\"{$m['right']}\" w:bottom=\"{$m['bottom']}\" "
        . "w:left=\"{$m['left']}\" w:header=\"720\" w:footer=\"720\" w:gutter=\"0\"/>"
        . '</w:sectPr>';
    $textWidth = 11906 - $m['left'] - $m['right'];
    $usableHeight = 16838 - $m['top'] - $m['bottom'];

    // Helper: make a run with optional bold/italic/center
    // All params except $text are optional
    function wRun($text, $font, $sz, $bold = false, $italic = false, $lang = null)
    {
        $text = htmlspecialchars((string)$text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $hint = $lang ? ' w:hint="cs"' : '';
        $rpr = "<w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"{$hint}/>"
            . ($bold   ? '<w:b/><w:bCs/>'   : '')
            . ($italic ? '<w:i/><w:iCs/>'   : '')
            . ($lang ? "<w:lang w:val=\"{$lang}\" w:eastAsia=\"{$lang}\" w:bidi=\"{$lang}\"/>" : '')
            . "<w:sz w:val=\"{$sz}\"/><w:szCs w:val=\"{$sz}\"/>";
        return "<w:r><w:rPr>{$rpr}</w:rPr><w:t xml:space=\"preserve\">{$text}</w:t></w:r>";
    }

    // Helper: paragraph
    function wPara($runs, $align = '', $spacingLine = 0, $indLeft = 0, $indHanging = 0, $spaceBefore = 0, $spaceAfter = 0, $indFirstLine = 0, $noAutoSpace = false)
    {
        $ppr = '';
        if ($noAutoSpace) {
            $ppr .= '<w:autoSpaceDE w:val="0"/><w:autoSpaceDN w:val="0"/><w:adjustRightInd w:val="0"/>';
        }
        if ($align) $ppr .= "<w:jc w:val=\"{$align}\"/>";

        $spacingAttr = '';
        if ($spacingLine || $spaceBefore || $spaceAfter) {
            $spacingAttr = "<w:spacing";
            if ($spacingLine) $spacingAttr .= " w:line=\"{$spacingLine}\" w:lineRule=\"auto\"";
            if ($spaceBefore !== 'skip') $spacingAttr .= " w:before=\"{$spaceBefore}\"";
            if ($spaceAfter !== 'skip') $spacingAttr .= " w:after=\"{$spaceAfter}\"";
            $spacingAttr .= "/>";
        }
        if ($spacingAttr) $ppr .= $spacingAttr;
        if ($indLeft || $indHanging || $indFirstLine) {
            $ppr .= "<w:ind"
                . ($indLeft ? " w:left=\"{$indLeft}\"" : '')
                . ($indHanging ? " w:hanging=\"{$indHanging}\"" : '')
                . ($indFirstLine ? " w:firstLine=\"{$indFirstLine}\"" : '')
                . '/>';
        }

        $pprXml = $ppr ? "<w:pPr>{$ppr}</w:pPr>" : '';
        return "<w:p>{$pprXml}" . implode('', $runs) . "</w:p>";
    }

    // Helper: blank paragraph
    function wBlank($font, $sz, $lines = 1)
    {
        $spacer = "<w:p><w:pPr><w:spacing w:line=\"360\" w:lineRule=\"auto\" w:before=\"0\" w:after=\"0\"/></w:pPr>"
            . "<w:r><w:rPr><w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
            . "<w:sz w:val=\"{$sz}\"/><w:szCs w:val=\"{$sz}\"/></w:rPr><w:t> </w:t></w:r></w:p>";
        return str_repeat($spacer, $lines);
    }

    function wBlankWithSpacing($font, $sz, $lines = 1, $spacingLine = 360)
    {
        $spacer = "<w:p><w:pPr><w:spacing w:line=\"{$spacingLine}\" w:lineRule=\"auto\" w:before=\"0\" w:after=\"0\"/></w:pPr>"
            . "<w:r><w:rPr><w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
            . "<w:sz w:val=\"{$sz}\"/><w:szCs w:val=\"{$sz}\"/></w:rPr><w:t> </w:t></w:r></w:p>";
        return str_repeat($spacer, $lines);
    }

    function normalizeThaiParagraphText($text)
    {
        // Convert newlines to spaces, collapse multiple spaces to one
        $text = trim((string) $text);
        $text = str_replace(["\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], '', $text);
        $text = preg_replace('/\x{0E4D}\x{0E32}/u', 'ำ', $text);
        $text = preg_replace('/\s*\R\s*/u', ' ', $text);
        $text = preg_replace('/\s{2,}/u', ' ', $text);
        if (class_exists('Normalizer')) {
            $normalized = Normalizer::normalize($text, Normalizer::FORM_C);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }
        return trim($text);
    }

    function wTableRow($width, $height, $vAlign, $content)
    {
        return '<w:tr>'
            . '<w:trPr><w:trHeight w:val="' . $height . '" w:hRule="exact"/></w:trPr>'
            . '<w:tc>'
            . '<w:tcPr>'
            . '<w:tcW w:w="' . $width . '" w:type="dxa"/>'
            . '<w:vAlign w:val="' . $vAlign . '"/>'
            . '</w:tcPr>'
            . $content
            . '</w:tc>'
            . '</w:tr>';
    }

    function wCenteredPageBlock($width, $height, $content)
    {
        return '<w:tbl>'
            . '<w:tblPr>'
            . '<w:tblW w:w="' . $width . '" w:type="dxa"/>'
            . '<w:tblBorders>'
            . '<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/>'
            . '<w:insideH w:val="nil"/><w:insideV w:val="nil"/>'
            . '</w:tblBorders>'
            . '<w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="0" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="0" w:type="dxa"/></w:tblCellMar>'
            . '</w:tblPr>'
            . '<w:tblGrid><w:gridCol w:w="' . $width . '"/></w:tblGrid>'
            . wTableRow($width, $height, 'center', $content)
            . '</w:tbl>';
    }

    function wAcademicCoverPage($cover, $font, $titleSz, $metaSz, $textWidth, $usableHeight, $logoRelationshipId = null, $logoAsset = null, $options = [])
    {
        $coverLineSpacing = 240; // single line
        $reservedBreakSpace = 480; // keep room for the following page-break paragraph
        $contentHeight = max(1, $usableHeight - $reservedBreakSpace);
        $semText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
        $courseCode = $cover['courseCode'] ? ' (' . $cover['courseCode'] . ')' : '';
        $showLogo = $logoRelationshipId && !empty($logoAsset['bytes']);
        $titleLinesOverride = $options['titleLines'] ?? null;
        $bottomLines = $options['bottomLines'] ?? null;

        $titleLines = is_array($titleLinesOverride)
            ? $titleLinesOverride
            : ($cover['title'] ? explode("\n", $cover['title']) : ['[ชื่อรายงาน]']);
        $authorLines = $cover['authors'] ? explode("\n", $cover['authors']) : ['[ชื่อ-สกุล ผู้จัดทำ]'];
        $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];

        $topXml = '';
        if ($showLogo) {
            $imageInfo = @getimagesizefromstring($logoAsset['bytes']);
            $imageWidth = !empty($imageInfo[0]) ? (int) $imageInfo[0] : 512;
            $imageHeight = !empty($imageInfo[1]) ? (int) $imageInfo[1] : 512;
            $targetWidthCm = 3.4;
            $targetHeightCm = $targetWidthCm * ($imageHeight / max($imageWidth, 1));
            $topXml .= wPara([
                buildWordInlineImage(
                    $logoRelationshipId,
                    'institution-logo.' . ($logoAsset['ext'] ?? 'png'),
                    (int) round($targetWidthCm * 360000),
                    (int) round($targetHeightCm * 360000)
                )
            ], 'center', $coverLineSpacing, 0, 0, 0, 0);
            $topXml .= wBlankWithSpacing($font, $metaSz, 1, $coverLineSpacing);
        }
        foreach ($titleLines as $line) {
            $topXml .= wPara([wRun(trim($line), $font, $titleSz, true)], 'center', $coverLineSpacing, 0, 0, 0, 0);
        }

        $middleXml = '';
        foreach ($authorLines as $i => $authorLine) {
            $middleXml .= wPara([wRun(trim($authorLine), $font, $metaSz, true)], 'center', $coverLineSpacing, 0, 0, 0, 0);
            if (isset($idLines[$i]) && trim($idLines[$i])) {
                $middleXml .= wPara([wRun('รหัส ' . trim($idLines[$i]), $font, $metaSz, true)], 'center', $coverLineSpacing, 0, 0, 0, 0);
            }
        }

        $bottomXml = '';
        $resolvedBottomLines = is_array($bottomLines) ? $bottomLines : [
            $cover['course'] ? $cover['course'] . $courseCode : '[รายวิชา]',
            $cover['department'] ?: '[ภาควิชา/คณะ]',
            $cover['institution'] ?: '[สถาบัน]',
            $cover['year'] ? 'ภาคการศึกษาที่ ' . $semText . '/' . $cover['year'] : ''
        ];
        foreach ($resolvedBottomLines as $line) {
            if ($line === null || trim((string) $line) === '') {
                continue;
            }
            $bottomXml .= wPara([wRun($line, $font, $metaSz, true)], 'center', $coverLineSpacing, 0, 0, 0, 0);
        }

        $topHeight = (int) round($contentHeight * ($showLogo ? 0.33 : 0.24));
        $middleHeight = (int) round($contentHeight * ($showLogo ? 0.23 : 0.26));
        $bottomHeight = $contentHeight - $topHeight - $middleHeight;

        return '<w:tbl>'
            . '<w:tblPr>'
            . '<w:tblW w:w="' . $textWidth . '" w:type="dxa"/>'
            . '<w:tblBorders>'
            . '<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/>'
            . '<w:insideH w:val="nil"/><w:insideV w:val="nil"/>'
            . '</w:tblBorders>'
            . '<w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="0" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="0" w:type="dxa"/></w:tblCellMar>'
            . '</w:tblPr>'
            . '<w:tblGrid><w:gridCol w:w="' . $textWidth . '"/></w:tblGrid>'
            . wTableRow($textWidth, $topHeight, 'top', $topXml)
            . wTableRow($textWidth, $middleHeight, 'center', $middleXml)
            . wTableRow($textWidth, $bottomHeight, 'bottom', $bottomXml)
            . '</w:tbl>';
    }

    // Helper: page break
    function wPageBreak()
    {
        return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="1" w:lineRule="auto"/></w:pPr><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    function wAbstractMetaBlock($font, $metaSz, $lineSpacing, $isEnglish, $cover)
    {
        $labels = $isEnglish
            ? ['Independent Study Title', 'Author', 'Degree', 'Advisor']
            : ['หัวข้อการค้นคว้าอิสระ', 'ผู้เขียน', 'ปริญญา', 'อาจารย์ที่ปรึกษา'];
        $html = '';

        foreach ($labels as $label) {
            $html .= wPara([wRun($label, $font, $metaSz, true, false, $isEnglish ? 'en-US' : 'th-TH')], 'left', $lineSpacing, 0, 0, 0, 0);
        }

        return $html;
    }

    function buildResearchTocEntries($tpl)
    {
        $entries = [
            ['label' => 'กิตติกรรมประกาศ', 'page' => 'ก', 'indent' => 0],
            ['label' => 'บทคัดย่อภาษาไทย', 'page' => 'ข', 'indent' => 0],
            ['label' => 'ABSTRACT', 'page' => 'ค', 'indent' => 0],
            ['label' => 'สารบัญ', 'page' => 'ง', 'indent' => 0],
            ['label' => 'สารบัญ(ต่อ)', 'page' => 'จ', 'indent' => 0],
            ['label' => 'สารบัญภาพ', 'page' => 'ฉ', 'indent' => 0],
            ['label' => 'สารบัญตาราง', 'page' => 'ช', 'indent' => 0],
        ];

        $chapterPage = 1;
        foreach ($tpl['chapters'] as $chapter) {
            $entries[] = ['label' => 'บทที่ ' . $chapter['number'] . ' ' . $chapter['title'], 'page' => (string) $chapterPage, 'indent' => 0];
            foreach ($chapter['subsections'] as $index => $subsection) {
                $entries[] = ['label' => $chapter['number'] . '.' . ($index + 1) . ' ' . $subsection, 'page' => (string) ($chapterPage + $index), 'indent' => 1];
            }
            $chapterPage += count($chapter['subsections']) + 1;
        }

        $entries[] = ['label' => 'บรรณานุกรม', 'page' => (string) $chapterPage, 'indent' => 0];
        $entries[] = ['label' => 'ภาคผนวก ก', 'page' => (string) ($chapterPage + 1), 'indent' => 0];
        $entries[] = ['label' => 'ภาคผนวก ข', 'page' => (string) ($chapterPage + 2), 'indent' => 0];
        $entries[] = ['label' => 'ประวัติผู้วิจัย', 'page' => (string) ($chapterPage + 3), 'indent' => 0];

        return [
            array_slice($entries, 0, 12),
            array_slice($entries, 12),
        ];
    }

    function buildResearchTableEntries()
    {
        return [
            ['label' => '3.1 แสดงจำนวนกลุ่มตัวอย่าง', 'page' => '14'],
            ['label' => '4.1 แสดงผลการวิเคราะห์ข้อมูล', 'page' => '19'],
            ['label' => '4.2 แสดงผลการทดสอบสมมติฐาน', 'page' => '21'],
        ];
    }

    function buildResearchFigureEntries()
    {
        return [
            ['label' => '2.1 กรอบแนวคิดการวิจัย', 'page' => '9'],
            ['label' => '3.1 ขั้นตอนการดำเนินการวิจัย', 'page' => '13'],
            ['label' => '4.1 สรุปผลการวิเคราะห์ข้อมูล', 'page' => '20'],
        ];
    }

    $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . "\n<w:document xmlns:w=\"http://schemas.openxmlformats.org/wordprocessingml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">\n<w:body>\n";

    // ==========================================
    // COVER PAGE
    // ==========================================
    $coverType = $tpl['coverType'];

    if ($coverType === 'academic') {
        $titleCoverSz = !empty($tpl['showLogo']) ? 44 : 48;
        $metaCoverSz = !empty($tpl['showLogo']) ? 36 : 48;
        $academicCoverOptions = [];

        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $major = $cover['course'] ?: '[สาขาวิชา]';
            $academicCoverOptions['bottomLines'] = [
                $major,
                $cover['department'] ?: '[ภาควิชา/คณะ]',
                $cover['institution'] ?: '[สถาบัน]',
                $cover['year'] ? 'ภาคการศึกษาที่ ' . ($cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน')) . '/' . $cover['year'] : ''
            ];
        }

        $content .= wAcademicCoverPage(
            $cover,
            $font,
            $titleCoverSz,
            $metaCoverSz,
            $textWidth,
            $usableHeight,
            $hasCoverLogo ? 'rIdCoverLogo1' : null,
            $logoAsset,
            $academicCoverOptions
        );

        $content .= wPageBreak();

        if (!empty($tpl['hasInnerCover'])) {
            if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
                $content .= wPageBreak();
            }

            $content .= wAcademicCoverPage($cover, $font, $titleCoverSz, $metaCoverSz, $textWidth, $usableHeight, null, null, $academicCoverOptions);

            $content .= wPageBreak();
        }

    } elseif ($coverType === 'internship' && !empty($tpl['showLogo'])) {
        $titleCoverSz = 44;
        $metaCoverSz = 36;
        $internshipTitleLines = [
            $tpl['fixedCoverTitle'] ?? 'รายงานผลการฝึกประสบการณ์วิชาชีพสารสนเทศ',
            $cover['company'] ?: '[ชื่อสถานประกอบการ]'
        ];
        $internshipBottomLines = [
            $cover['department'] ?: '[ภาควิชา/คณะ]',
            $cover['institution'] ?: '[สถาบัน]',
            $cover['year'] ? 'ภาคการศึกษาที่ ' . ($cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน')) . '/' . $cover['year'] : ''
        ];

        $content .= wAcademicCoverPage(
            $cover,
            $font,
            $titleCoverSz,
            $metaCoverSz,
            $textWidth,
            $usableHeight,
            $hasCoverLogo ? 'rIdCoverLogo1' : null,
            $logoAsset,
            [
                'titleLines' => $internshipTitleLines,
                'bottomLines' => $internshipBottomLines,
            ]
        );

        $content .= wPageBreak();

    } else {
        // ===== Non-academic covers (thesis, research, internship, project) =====
        if ($cover['institution']) {
            $content .= wPara([wRun($cover['institution'], $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($cover['department']) {
            $content .= wPara([wRun($cover['department'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        $content .= wBlank($font, $bodySz, 4);

        if ($cover['title']) {
            foreach (explode("\n", $cover['title']) as $line) {
                $content .= wPara([wRun(trim($line), $font, $titleSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
            }
        } else {
            $content .= wPara([wRun('[ชื่อรายงาน]', $font, $titleSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        $content .= wBlank($font, $bodySz, 2);

        if ($coverType === 'internship') {
            $content .= wPara([wRun('รายงานฝึกประสบการณ์วิชาชีพ', $font, $headingSz)], 'center', $lineSpacing, 0, 0, 0, 0);
        } elseif ($coverType === 'thesis') {
            $degree = $cover['degree'] ?: 'วิทยาศาสตรมหาบัณฑิต';
            $major  = $cover['course'] ?: '[สาขาวิชา]';
            $content .= wPara([wRun('วิทยานิพนธ์นี้เป็นส่วนหนึ่งของการศึกษาตามหลักสูตร', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            $content .= wPara([wRun("{$degree} สาขา{$major}", $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        } elseif ($coverType === 'project') {
            $projType = $cover['projectType'] ?: 'รายงานโครงการ';
            $content .= wPara([wRun($projType, $font, $headingSz)], 'center', $lineSpacing, 0, 0, 0, 0);
        } elseif ($coverType === 'research') {
            $major  = $cover['course'] ?: '[สาขาวิชา]';
            $content .= wPara([wRun($major, $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        $content .= wBlank($font, $bodySz, 4);

        $content .= wPara([wRun('จัดทำโดย', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        $authorLines = $cover['authors'] ? explode("\n", $cover['authors']) : ['[ชื่อผู้จัดทำ]'];
        $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];
        foreach ($authorLines as $i => $authorLine) {
            $content .= wPara([wRun(trim($authorLine), $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
            if (isset($idLines[$i]) && trim($idLines[$i])) {
                $content .= wPara([wRun(trim($idLines[$i]), $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            }
        }
        $content .= wBlank($font, $bodySz, 2);

        if ($coverType === 'internship') {
            if ($cover['company'])         $content .= wPara([wRun('สถานประกอบการ: ' . $cover['company'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            if ($cover['supervisor'])      $content .= wPara([wRun('ผู้ควบคุมการฝึกงาน: ' . $cover['supervisor'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            if ($cover['internshipPeriod']) $content .= wPara([wRun('ช่วงเวลา: ' . $cover['internshipPeriod'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            if ($cover['instructor'])      $content .= wPara([wRun('อาจารย์นิเทศ: ' . $cover['instructor'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        } elseif ($coverType === 'thesis') {
            if ($cover['committee']) {
                $content .= wBlank($font, $bodySz, 1);
                $content .= wPara([wRun('คณะกรรมการที่ปรึกษา', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
                foreach (explode("\n", $cover['committee']) as $cl) {
                    $content .= wPara([wRun(trim($cl), $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
                }
            }
        } elseif ($coverType !== 'research') {
            if ($cover['instructor']) {
                $content .= wPara([wRun('เสนอ', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
                $content .= wPara([wRun($cover['instructor'], $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
            }
        }
        $content .= wBlank($font, $bodySz, 2);

        if ($coverType === 'research' && $cover['department']) {
            $content .= wPara([wRun($cover['department'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($cover['institution']) {
            $content .= wPara([wRun($cover['institution'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($coverType === 'thesis') {
            if ($cover['year']) $content .= wPara([wRun('พ.ศ. ' . $cover['year'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        } else {
            if ($cover['semester'] || $cover['year']) {
                $semText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
                $yearStr = $cover['year'] ? ' ปีการศึกษา ' . $cover['year'] : '';
                $content .= wPara([wRun('ภาคเรียนที่ ' . $semText . $yearStr, $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            }
        }
        $content .= wPageBreak();
    }

    // ==========================================
    // PREFACE (Academic General)
    // ==========================================
    if (!empty($tpl['hasPreface'])) {
        $prefaceLineSpacing = 240; // single line
        $prefaceFirstLineIndent = 850; // 1.5 cm
        $prefaceParagraphSpaceAfter = 240; // 1 line

        $content .= wPara([wRun('คำนำ', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', $prefaceLineSpacing, 0, 0, 0, 0);
        $content .= wBlankWithSpacing($font, $bodySz, 2, 240);

        $prefaceContent = trim((string) ($cover['prefaceContent'] ?? ''));
        if ($prefaceContent === '') {
            $prefaceContent = 'รายงานฉบับนี้จัดทำขึ้นเพื่อเป็นส่วนหนึ่งของการศึกษาในรายวิชา...\n\nผู้จัดทำหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะเป็นประโยชน์...';
        }

        $prefaceParagraphs = preg_split('/\R{2,}/u', $prefaceContent) ?: [$prefaceContent];
        foreach ($prefaceParagraphs as $paragraph) {
            $paragraph = normalizeThaiParagraphText($paragraph);
            if ($paragraph === '') continue;
            $content .= wPara([wRun($paragraph, $font, $bodySz, false, false, 'th-TH')], 'thaiDistribute', $prefaceLineSpacing, 0, 0, 0, $prefaceParagraphSpaceAfter, $prefaceFirstLineIndent, true);
        }

        $prefaceSigner = trim((string) ($cover['prefaceSigner'] ?? ''));
        if ($prefaceSigner === '' && !empty($cover['authors'])) {
            $prefaceSigner = trim(explode("\n", $cover['authors'])[0]);
        }
        $prefaceDate = trim((string) ($cover['prefaceDate'] ?? ''));
        if ($prefaceSigner !== '') {
            $content .= wBlank($font, $bodySz, 2);
            $content .= wPara([wRun($prefaceSigner, $font, $bodySz, false, false, 'th-TH')], 'right', $prefaceLineSpacing, 0, 0, 0, 0);
            if ($prefaceDate !== '') {
                $content .= wPara([wRun($prefaceDate, $font, $bodySz, false, false, 'th-TH')], 'right', $prefaceLineSpacing, 0, 0, 0, 0);
            }
        }
        $content .= wPageBreak();
    }

    // ==========================================
    // ACKNOWLEDGMENT
    // ==========================================
    if ($tpl['hasAcknowledgment']) {
        $ackHeadingSize = (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? $prefaceHeadingSz : $headingSz;
        if (($tpl['name'] ?? '') !== 'รายงานการวิจัย') {
            $content .= wBlank($font, $bodySz, 2);
        }
        $content .= wPara([wRun('กิตติกรรมประกาศ', $font, $ackHeadingSize, true)], 'center', (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 240 : $lineSpacing, 0, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $ackText = 'ขอขอบพระคุณ' . ($cover['instructor'] ?: '...') . ' ที่ให้คำปรึกษาและแนะนำแนวทางการวิจัยอย่างดียิ่งตลอดระยะเวลาการศึกษา'
            . ' ขอขอบคุณท่านผู้เกี่ยวข้องทุกท่านที่ให้ความอนุเคราะห์ในการดำเนินการวิจัย'
            . ' และขอขอบคุณครอบครัวที่ให้การสนับสนุนและเป็นกำลังใจตลอดมา';
        $content .= wPara([wRun($ackText, $font, $bodySz)], 'thaiDistribute', $lineSpacing, 720, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 3);
        $authorFirst = $cover['authors'] ? explode("\n", $cover['authors'])[0] : '';
        if ($authorFirst) {
            $content .= wPara([wRun(trim($authorFirst), $font, $bodySz)], 'right', $lineSpacing, 0, 0, 0, 0);
        }
        $content .= wPageBreak();
    }

    // ==========================================
    // ABSTRACT (Research/Thesis)
    // ==========================================
    if ($tpl['hasAbstract']) {
        $abstractHeadingSize = (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? $prefaceHeadingSz : $headingSz;
        $abstractMetaSize = 32;
        if (($tpl['name'] ?? '') !== 'รายงานการวิจัย') {
            $content .= wBlank($font, $bodySz, 2);
        }
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $content .= wAbstractMetaBlock($font, $abstractMetaSize, 240, false, $cover);
        }
        $content .= wPara([wRun('บทคัดย่อ', $font, $abstractHeadingSize, true)], 'center', (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 240 : $lineSpacing, 0, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $abstractText = 'กรอกบทคัดย่อภาษาไทยในที่นี้ ความยาว 150–300 คำ ระบุวัตถุประสงค์ วิธีดำเนินการ ผลการศึกษา และข้อสรุป';
        $content .= wPara([wRun($abstractText, $font, $bodySz)], 'thaiDistribute', $lineSpacing, 720, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $content .= wPara([wRun('คำสำคัญ: คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
        $content .= wPageBreak();

        if (!empty($tpl['hasAbstractEnglish'])) {
            if (($tpl['name'] ?? '') !== 'รายงานการวิจัย') {
                $content .= wBlank($font, $bodySz, 2);
            }
            if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
                $content .= wAbstractMetaBlock($font, $abstractMetaSize, 240, true, $cover);
            }
            $abstractHeadingText = (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'ABSTRACT' : 'Abstract';
            $content .= wPara([wRun($abstractHeadingText, $font, $abstractHeadingSize, true)], 'center', (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 240 : $lineSpacing, 0, 0, 0, 0);
            $content .= wBlank($font, $bodySz, 1);
            $abstractTextEn = 'Write the English abstract here in 150-300 words, covering the objective, methodology, findings, and conclusion.';
            $content .= wPara([wRun($abstractTextEn, $font, $bodySz)], 'both', $lineSpacing, 720, 0, 0, 0);
            $content .= wBlank($font, $bodySz, 1);
            $content .= wPara([wRun('Keywords: keyword 1, keyword 2, keyword 3', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
            $content .= wPageBreak();
        }
    }

    // ==========================================
    // TABLE OF CONTENTS (all templates)
    // ==========================================
    if ($tpl['hasToc']) {
        $isAcademicGeneralToc = !empty($tpl['hasPreface']) && $tpl['coverType'] === 'academic';
        $tocRightTabPos = 11906 - $m['left'] - $m['right'];

        if ($isAcademicGeneralToc) {
            $content .= wPara([wRun('สารบัญ', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 360);
                $content .= wPara([wRun('หน้า', $font, $subSz, true, false, 'th-TH')], 'right', 240, 0, 0, 0, 240);
        } elseif (($tpl['name'] ?? '') !== 'รายงานการวิจัย') {
            $content .= wBlank($font, $bodySz, 2);
            $content .= wPara([wRun('สารบัญ', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
            $content .= wBlank($font, $bodySz, 1);
        }

        // TOC entries
        function tocEntry($label, $page, $font, $bodySz, $indent = 0)
        {
            $lineSpacing = 360;
            $indentTwips = $indent * 360;
            return "<w:p><w:pPr><w:jc w:val=\"left\"/>"
                . "<w:spacing w:line=\"{$lineSpacing}\" w:lineRule=\"auto\" w:before=\"0\" w:after=\"0\"/>"
                . ($indentTwips ? "<w:ind w:left=\"{$indentTwips}\"/>" : '')
                . "</w:pPr>"
                . "<w:r><w:rPr><w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
                . "<w:sz w:val=\"{$bodySz}\"/><w:szCs w:val=\"{$bodySz}\"/></w:rPr>"
                . "<w:t xml:space=\"preserve\">{$label}</w:t></w:r>"
                . "<w:r><w:rPr><w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
                . "<w:sz w:val=\"{$bodySz}\"/><w:szCs w:val=\"{$bodySz}\"/></w:rPr>"
                . "<w:t xml:space=\"preserve\"> .............. {$page}</w:t></w:r>"
                . "</w:p>";
        }

        function tocEntryAcademicGeneral($label, $page, $font, $bodySz, $tabPos, $indent = 0)
        {
            $indentTwips = $indent * 720;
            return '<w:p><w:pPr>'
                . '<w:jc w:val="left"/>'
                . '<w:spacing w:line="240" w:lineRule="auto" w:before="0" w:after="0"/>'
                . '<w:tabs><w:tab w:val="right" w:pos="' . $tabPos . '"/></w:tabs>'
                . ($indentTwips ? '<w:ind w:left="' . $indentTwips . '"/>' : '')
                . '</w:pPr>'
                . wRun($label, $font, $bodySz, false, false, 'th-TH')
                . '<w:r><w:tab/></w:r>'
                . wRun((string) $page, $font, $bodySz, false, false, 'th-TH')
                . '</w:p>';
        }

        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $tocPages = buildResearchTocEntries($tpl);

            foreach ($tocPages as $pageIndex => $tocEntries) {
                $content .= wPara([wRun($pageIndex === 0 ? 'สารบัญ' : 'สารบัญ(ต่อ)', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 360);
                $content .= wPara([wRun('หน้า', $font, $subSz, true, false, 'th-TH')], 'right', 240, 0, 0, 0, 240);
                foreach ($tocEntries as $entry) {
                    $content .= tocEntryAcademicGeneral($entry['label'], $entry['page'], $font, $bodySz, $tocRightTabPos, $entry['indent'] ?? 0);
                }
                $content .= wPageBreak();
            }

            if (!empty($tpl['hasFigureList'])) {
                $content .= wPara([wRun('สารบัญภาพ', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 360);
                $content .= '<w:p><w:pPr><w:tabs><w:tab w:val="right" w:pos="' . $tocRightTabPos . '"/></w:tabs><w:spacing w:line="240" w:lineRule="auto" w:before="0" w:after="240"/></w:pPr>'
                    . wRun('ภาพที่', $font, $subSz, true, false, 'th-TH')
                    . '<w:r><w:tab/></w:r>'
                    . wRun('หน้า', $font, $subSz, true, false, 'th-TH')
                    . '</w:p>';
                foreach (buildResearchFigureEntries() as $entry) {
                    $content .= tocEntryAcademicGeneral($entry['label'], $entry['page'], $font, $bodySz, $tocRightTabPos);
                }
                $content .= wPageBreak();
            }

            if (!empty($tpl['hasTableList'])) {
                $content .= wPara([wRun('สารบัญตาราง', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 360);
                $content .= '<w:p><w:pPr><w:tabs><w:tab w:val="right" w:pos="' . $tocRightTabPos . '"/></w:tabs><w:spacing w:line="240" w:lineRule="auto" w:before="0" w:after="240"/></w:pPr>'
                    . wRun('ตารางที่', $font, $subSz, true, false, 'th-TH')
                    . '<w:r><w:tab/></w:r>'
                    . wRun('หน้า', $font, $subSz, true, false, 'th-TH')
                    . '</w:p>';
                foreach (buildResearchTableEntries() as $entry) {
                    $content .= tocEntryAcademicGeneral($entry['label'], $entry['page'], $font, $bodySz, $tocRightTabPos);
                }
                $content .= wPageBreak();
            }
        } elseif ($isAcademicGeneralToc) {
            $contentPage = 1;
            if (!empty($tpl['hasPreface'])) {
                $content .= tocEntryAcademicGeneral('คำนำ', 'ก', $font, $bodySz, $tocRightTabPos);
            }

            foreach ($tpl['chapters'] as $ch) {
                $label = "บทที่ {$ch['number']} {$ch['title']}";
                $content .= tocEntryAcademicGeneral($label, $contentPage, $font, $bodySz, $tocRightTabPos);
                foreach ($ch['subsections'] as $i => $sub) {
                    $subLabel = "{$ch['number']}." . ($i + 1) . " {$sub}";
                    $content .= tocEntryAcademicGeneral($subLabel, $contentPage, $font, $bodySz, $tocRightTabPos, 1);
                    $contentPage++;
                }
            }

            $content .= tocEntryAcademicGeneral('บรรณานุกรม', $contentPage, $font, $bodySz, $tocRightTabPos);
        } else {
            $pg = 1;
            if (!empty($tpl['hasPreface'])) {
                $pg++;
                $content .= tocEntry(htmlspecialchars('คำนำ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
            }
            if ($tpl['hasAcknowledgment']) {
                $pg++;
                $content .= tocEntry(htmlspecialchars('กิตติกรรมประกาศ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
            }
            if ($tpl['hasAbstract']) {
                $pg++;
                $content .= tocEntry(htmlspecialchars('บทคัดย่อ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
                if (!empty($tpl['hasAbstractEnglish'])) {
                    $pg++;
                    $content .= tocEntry(htmlspecialchars('Abstract', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
                }
            }
            $pg++;
            $content .= tocEntry(htmlspecialchars('สารบัญ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);

            foreach ($tpl['chapters'] as $ch) {
                $pg++;
                $label = "บทที่ {$ch['number']} {$ch['title']}";
                $content .= tocEntry(htmlspecialchars($label, ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
                foreach ($ch['subsections'] as $i => $sub) {
                    $subLabel = "{$ch['number']}." . ($i + 1) . " {$sub}";
                    $content .= tocEntry(htmlspecialchars($subLabel, ENT_XML1, 'UTF-8'), $pg, $font, $bodySz, 1);
                }
            }

            $pg++;
            $content .= tocEntry(htmlspecialchars('บรรณานุกรม', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);

            if ($tpl['hasAppendix']) {
                $pg++;
                $content .= tocEntry(htmlspecialchars('ภาคผนวก', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
            }
            $content .= wPageBreak();
        }
    }

    // ==========================================
    // CHAPTERS
    // ==========================================
    foreach ($tpl['chapters'] as $ch) {
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $content .= wPara([wRun("บทที่ {$ch['number']}", $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 0);
            $content .= wPara([wRun($ch['title'], $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 240);
        } elseif ($isAcademicGeneralDocument) {
            $content .= wPara([wRun("บทที่ {$ch['number']}", $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 0);
            $content .= wPara([wRun($ch['title'], $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 0);
            $content .= wBlankWithSpacing($font, $bodySz, 2, 240);
        } else {
            $content .= wBlank($font, $bodySz, 2);
            $content .= wPara([wRun("บทที่ {$ch['number']}", $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
            $content .= wPara([wRun($ch['title'], $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 480, 0);
        }

        foreach ($ch['subsections'] as $subIndex => $sub) {
            if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
                $subHeadingBefore = $subIndex === 0 ? 0 : 120;
                $content .= wPara([wRun($sub, $font, $subSz, true, false, 'th-TH')], '', 240, 0, 0, $subHeadingBefore, 120);
            } elseif ($isAcademicGeneralDocument) {
                $subHeadingBefore = $subIndex === 0 ? 0 : 120;
                $content .= wPara([wRun($sub, $font, $headingSz, true, false, 'th-TH')], '', 240, 0, 0, $subHeadingBefore, 120);
            } else {
                $content .= wPara([wRun($sub, $font, $subSz, true)], '', $lineSpacing, 0, 0, 240, 0);
            }
            // Body placeholder paragraph
            $placeholder = 'กรอกเนื้อหาในส่วน"' . $sub . '"ในที่นี้ ใช้ขนาดตัวอักษร ' . $bodyPt . 'pt ระยะบรรทัด 1.5 เว้นย่อหน้า 1.5 cm กดลบข้อความนี้แล้วพิมพ์เนื้อหาของท่านได้เลย';
            $content .= wPara([wRun($placeholder, $font, $bodySz)], 'thaiDistribute', $lineSpacing, 720, 0, 0, 0);
        }

        $content .= wPageBreak();
    }

    // ==========================================
    // BIBLIOGRAPHY
    // ==========================================
    if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
        $content .= wPara([wRun('บรรณานุกรม', $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 240);
    } elseif ($isAcademicGeneralDocument) {
        $content .= wPara([wRun('บรรณานุกรม', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 0);
        $content .= wBlankWithSpacing($font, $bodySz, 2, 240);
    } else {
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun('บรรณานุกรม', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 480);
    }

    if (empty($bibliographies)) {
        $content .= wPara([wRun('(ไม่มีรายการบรรณานุกรม — เพิ่มรายการบรรณานุกรมในที่นี้)', $font, $bodySz)], '', $lineSpacing, 720, 720, 0, 0);
    } else {
        // Thai first, then English
        $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
        $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');

        foreach (array_merge($thBibs, $enBibs) as $bib) {
            $text = $bib['bibliography_text'];
            $content .= '<w:p><w:pPr><w:jc w:val="thaiDistribute"/>'
                . '<w:ind w:left="720" w:hanging="720"/>'
                . '<w:spacing w:line="360" w:lineRule="auto" w:after="0" w:before="0"/>'
                . '</w:pPr>';

            $parts = preg_split('/(<i>.*?<\/i>)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($parts as $part) {
                $isItalic = false;
                $cleanPart = $part;
                if (preg_match('/^<i>(.*)<\/i>$/us', $part, $matches)) {
                    $isItalic = true;
                    $cleanPart = $matches[1];
                }
                if ($cleanPart === '') continue;
                $cleanPart = htmlspecialchars($cleanPart, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $content .= "<w:r><w:rPr><w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
                    . ($isItalic ? '<w:i/><w:iCs/>' : '')
                    . "<w:sz w:val=\"{$bodySz}\"/><w:szCs w:val=\"{$bodySz}\"/></w:rPr>"
                    . "<w:t xml:space=\"preserve\">{$cleanPart}</w:t></w:r>";
            }
            $content .= '</w:p>';
        }
    }

    // ==========================================
    // APPENDIX
    // ==========================================
    if ($tpl['hasAppendix']) {
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $content .= wPageBreak();
            $content .= wCenteredPageBlock($textWidth, $usableHeight - 240, wPara([wRun('ภาคผนวก ก', $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 240)
                . wPara([wRun('(ตัวอย่างเครื่องมือวิจัย แบบสอบถาม หรือเอกสารประกอบ)', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0));
            $content .= wPageBreak();
            $content .= wCenteredPageBlock($textWidth, $usableHeight - 240, wPara([wRun('ภาคผนวก ข', $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 240)
                . wPara([wRun('(ตัวอย่างภาพประกอบ ผลงาน หรือข้อมูลเพิ่มเติม)', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0));
        } else {
            $content .= wPageBreak();
            $content .= wPara([wRun('ภาคผนวก', $font, $prefaceHeadingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 0);
            $content .= wBlankWithSpacing($font, $bodySz, 2, 240);
            $content .= wPara([wRun('(เพิ่มเนื้อหาภาคผนวกในที่นี้ เช่น แบบสอบถาม รูปภาพ เอกสารประกอบ)', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    }

    if (!empty($tpl['hasBiography'])) {
        $content .= wPageBreak();
        $content .= wPara([wRun('ประวัติผู้วิจัย', $font, $headingSz, true, false, 'th-TH')], 'center', 240, 0, 0, 0, 240);
        $content .= wBlankWithSpacing($font, $bodySz, 2, 240);
        $content .= wPara([wRun('ชื่อ-สกุล ............................................................', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
        $content .= wPara([wRun('ประวัติการศึกษา ....................................................', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
        $content .= wPara([wRun('ประสบการณ์หรือผลงานที่เกี่ยวข้อง ................................', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
    }

    // End section
    $content .= $sectPr;
    $content .= "</w:body>\n</w:document>";

    // Create DOCX ZIP
    $tempDir = __DIR__ . '/../../tmp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    $tempFile = $tempDir . '/report_' . uniqid() . '.docx';

    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        die('ไม่สามารถสร้างไฟล์ DOCX ได้');
    }

    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        (!empty($logoAsset['bytes']) ? '<Default Extension="' . htmlspecialchars($logoAsset['ext'], ENT_QUOTES, 'UTF-8') . '" ContentType="' . htmlspecialchars($logoAsset['mime'], ENT_QUOTES, 'UTF-8') . '"/>' : '') .
        '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
        '</Relationships>');

    $zip->addFromString('word/document.xml', $content);

    if ($hasCoverLogo) {
        $zip->addFromString('word/media/institution-logo.' . $logoAsset['ext'], $logoAsset['bytes']);
        $zip->addFromString('word/_rels/document.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rIdCoverLogo1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/institution-logo.' . htmlspecialchars($logoAsset['ext'], ENT_QUOTES, 'UTF-8') . '"/>' .
            '</Relationships>');
    } else {
        $zip->addFromString('word/_rels/document.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
    }

    $zip->close();

    if (!file_exists($tempFile)) {
        http_response_code(500);
        die('เกิดข้อผิดพลาด: ไม่พบไฟล์ที่สร้างขึ้น');
    }

    $reportTitle = $cover['title'] ?: 'report';
    $filename = sanitizeReportFilename($reportTitle) . '_' . date('Ymd') . '.docx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: private, no-cache, no-store');
    readfile($tempFile);
    unlink($tempFile);
    exit;
}

// ======================================================
//  PDF Export (Print-ready HTML)
// ======================================================
function exportPdfPreview($tpl, $cover, $bibliographies, $margins, $font, $bodyPt)
{
    $ptToCm = function($twips) { return round($twips / 1440 * 2.54, 2); };
    $mTop    = $ptToCm($margins['top']);
    $mBottom = $ptToCm($margins['bottom']);
    $mLeft   = $ptToCm($margins['left']);
    $mRight  = $ptToCm($margins['right']);

    $titleName = htmlspecialchars($cover['title'] ?: 'รายงาน', ENT_QUOTES, 'UTF-8');
    $webFont   = ($font === 'Angsana New' || strpos($font, 'TH') !== false) ? 'Sarabun' : $font;
    $logoDataUri = resolveTemplateLogoDataUri($cover, $tpl);

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titleName; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: '<?php echo htmlspecialchars($webFont, ENT_QUOTES); ?>', 'Tahoma', serif;
            font-size: <?php echo $bodyPt; ?>pt;
            line-height: 1.5;
            color: #000;
            background: #ccc;
        }
        .page {
            width: 21cm;
            min-height: 29.7cm;
            background: white;
            margin: 20px auto;
            padding: <?php echo $mTop; ?>cm <?php echo $mRight; ?>cm <?php echo $mBottom; ?>cm <?php echo $mLeft; ?>cm;
            position: relative;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            page-break-after: always;
        }
        @media print {
            body { background: white; }
            .page {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }
            .no-print { display: none !important; }
        }

        /* Cover */
        .cover-page { text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: calc(29.7cm - <?php echo $mTop + $mBottom; ?>cm); }
        .cover-top { width: 100%; }
        .cover-middle { width: 100%; }
        .cover-bottom { width: 100%; }
        .cover-title { font-size: <?php echo $bodyPt + 4; ?>pt; font-weight: bold; line-height: 1.4; margin: 20px 0; }
        .cover-subtitle { font-size: <?php echo $bodyPt; ?>pt; margin-bottom: 20px; }
        .cover-info { font-size: <?php echo $bodyPt; ?>pt; line-height: 1.8; }
        .cover-logo-mark { font-size: 18pt; font-weight: bold; margin-bottom: 16px; }
        .cover-logo-image { width: 3.4cm; height: auto; object-fit: contain; display: block; margin: 0 auto 0.45cm; }
        .cover-title-academic-logo { font-size: 22pt; line-height: 1.45; margin: 0; }
        .cover-info-academic-logo { font-size: 18pt; font-weight: bold; line-height: 1.5; }

        /* Headings */
        .section-heading { font-size: 18pt; font-weight: bold; text-align: center; margin: 0 0 12px; line-height: 1.45; }
        .preface-heading { font-size: 18pt; font-weight: bold; text-align: center; margin: 0 0 12px; line-height: 1.45; }
        .abstract-meta-block { margin-bottom: 8px; }
        .abstract-meta-line { font-size: 16pt; font-weight: bold; text-align: left; line-height: 1.65; }
        .preface-body { font-size: <?php echo $bodyPt; ?>pt; line-height: 1; text-align: justify; text-justify: inter-character; margin-bottom: 1em; text-indent: 1.5cm; }
        .preface-body:last-of-type { margin-bottom: 0; }
        .chapter-heading-num { font-size: 18pt; font-weight: bold; text-align: center; line-height: 1.45; margin-bottom: 4px; }
        .chapter-heading-title { font-size: 18pt; font-weight: bold; text-align: center; line-height: 1.45; margin-bottom: 16px; }
        .sub-heading { font-size: 16pt; font-weight: bold; margin: 12px 0 6px; }
        .body-placeholder { font-size: <?php echo $bodyPt; ?>pt; text-align: justify; margin-bottom: 10px; padding-left: 1.5cm; }
        .page-center-block { min-height: calc(29.7cm - <?php echo $mTop + $mBottom; ?>cm); display: flex; flex-direction: column; justify-content: center; }

        /* TOC */
        .toc-line { display: flex; font-size: <?php echo $bodyPt; ?>pt; margin-bottom: 4px; }
        .toc-line-indent { padding-left: 1cm; }
        .toc-label { flex: 1; }
        .toc-page { white-space: nowrap; }
        .toc-dots { flex: 1; overflow: hidden; }
        .toc-dots::after { content: '................................................'; }

        /* Bibliography */
        .bib-item { font-size: <?php echo $bodyPt; ?>pt; text-indent: -1.27cm; padding-left: 1.27cm; margin-bottom: 10px; text-align: justify; line-height: 1.5; }

        /* Print controls */
        .print-controls { position: fixed; top: 0; left: 0; right: 0; background: #1a1a2e; color: white; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .print-btn { padding: 8px 20px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; background: linear-gradient(135deg,#8B5CF6,#6366F1); color: white; }
        .print-close { padding: 8px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-size: 14px; background: transparent; color: #aaa; }

        @media screen { body { padding-top: 56px; } }
    </style>
</head>
<body>

<!-- Print Controls (hidden on print) -->
<div class="print-controls no-print">
    <div>
        <strong><?php echo htmlspecialchars($tpl['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
        &nbsp;—&nbsp; <?php echo $titleName; ?>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="print-btn" onclick="window.print()">
            <i>⬇</i> พิมพ์ / บันทึก PDF
        </button>
        <button class="print-close" onclick="window.close()">ปิด</button>
    </div>
</div>

<?php
    // Helper: HTML entity encode
    $h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $nl2br_safe = fn($s) => nl2br($h($s));

    // ---- COVER ----
    echo '<div class="page"><div class="cover-page">';
    $coverType = $tpl['coverType'];
    if ($coverType === 'academic') {
        $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];
        $authorLines = $cover['authors'] ? explode("\n", $cover['authors']) : ['[ชื่อ-สกุล ผู้จัดทำ]'];
        $semesterText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
        $courseCode = $cover['courseCode'] ? ' (' . $h($cover['courseCode']) . ')' : '';

        echo '<div class="cover-top">';
        if (!empty($tpl['showLogo']) && $logoDataUri !== '') {
            echo '<img class="cover-logo-image" src="' . $h($logoDataUri) . '" alt="ตราสถาบัน">';
        }
        echo '<div class="cover-title cover-title-academic-logo">' . nl2br($h($cover['title'] ?: '[ชื่อรายงาน]')) . '</div>';
        echo '</div>';

        echo '<div class="cover-middle cover-info cover-info-academic-logo">';
        foreach ($authorLines as $index => $authorLine) {
            echo '<div>' . $h(trim($authorLine)) . '</div>';
            if (isset($idLines[$index]) && trim($idLines[$index]) !== '') {
                echo '<div>รหัส ' . $h(trim($idLines[$index])) . '</div>';
            }
        }
        echo '</div>';

        echo '<div class="cover-bottom cover-info cover-info-academic-logo">';
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            echo '<div>' . $h($cover['course'] ?: '[สาขาวิชา]') . '</div>';
        } else {
            echo '<div>' . ($cover['course'] ? $h($cover['course']) . $courseCode : '[รายวิชา]') . '</div>';
        }
        echo '<div>' . $h($cover['department'] ?: '[ภาควิชา/คณะ]') . '</div>';
        echo '<div>' . $h($cover['institution'] ?: '[สถาบัน]') . '</div>';
        if ($cover['year']) {
            echo '<div>ภาคการศึกษาที่ ' . $semesterText . '/' . $h($cover['year']) . '</div>';
        }
        echo '</div>';
    } elseif ($coverType === 'internship' && !empty($tpl['showLogo'])) {
        $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];
        $authorLines = $cover['authors'] ? explode("\n", $cover['authors']) : ['[ชื่อ-สกุล ผู้จัดทำ]'];
        $semesterText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');

        echo '<div class="cover-top">';
        if ($logoDataUri !== '') {
            echo '<img class="cover-logo-image" src="' . $h($logoDataUri) . '" alt="ตราสถาบัน">';
        }
        echo '<div class="cover-title cover-title-academic-logo">' . $h($tpl['fixedCoverTitle'] ?? 'รายงานผลการฝึกประสบการณ์วิชาชีพสารสนเทศ') . '<br>' . $h($cover['company'] ?: '[ชื่อสถานประกอบการ]') . '</div>';
        echo '</div>';

        echo '<div class="cover-middle cover-info cover-info-academic-logo">';
        foreach ($authorLines as $index => $authorLine) {
            echo '<div>' . $h(trim($authorLine)) . '</div>';
            if (isset($idLines[$index]) && trim($idLines[$index]) !== '') {
                echo '<div>รหัส ' . $h(trim($idLines[$index])) . '</div>';
            }
        }
        echo '</div>';

        echo '<div class="cover-bottom cover-info cover-info-academic-logo">';
        echo '<div>' . $h($cover['department'] ?: '[ภาควิชา/คณะ]') . '</div>';
        echo '<div>' . $h($cover['institution'] ?: '[สถาบัน]') . '</div>';
        if ($cover['year']) {
            echo '<div>ภาคการศึกษาที่ ' . $semesterText . '/' . $h($cover['year']) . '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="cover-top">';
        if ($cover['institution']) echo '<div>' . $h($cover['institution']) . '</div>';
        if ($cover['department']) echo '<div>' . $h($cover['department']) . '</div>';
        echo '</div>';
        echo '<div class="cover-middle">';
        if (!empty($tpl['showLogo'])) echo '<div class="cover-logo-mark">[ตราสถาบัน]</div>';
        echo '<div class="cover-title">' . nl2br($h($cover['title'] ?: '[ชื่อรายงาน]')) . '</div>';
        if ($coverType === 'academic') {
            if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
                $major = $h($cover['course'] ?: '[สาขาวิชา]');
                $courseLabel = $major;
            } else {
                $courseLabel = $cover['course'] ? "รายงานนี้เป็นส่วนหนึ่งของรายวิชา " . $h($cover['course']) : 'รายงานนี้เป็นส่วนหนึ่งของรายวิชา';
            }
            echo '<div class="cover-subtitle">' . $courseLabel . '</div>';
        } elseif ($coverType === 'thesis') {
        $degree = $h($cover['degree'] ?: 'วิทยาศาสตรมหาบัณฑิต');
        $major  = $h($cover['course'] ?: '[สาขาวิชา]');
        echo '<div class="cover-subtitle">วิทยานิพนธ์นี้เป็นส่วนหนึ่งของการศึกษาตามหลักสูตร<br>' . $degree . ' สาขา' . $major . '</div>';
        } elseif ($coverType === 'internship') {
        echo '<div class="cover-subtitle">รายงานฝึกประสบการณ์วิชาชีพ</div>';
        } elseif ($coverType === 'project') {
        echo '<div class="cover-subtitle">' . $h($cover['projectType'] ?: 'รายงานโครงการ') . '</div>';
        }
        echo '</div>';
        echo '<div class="cover-bottom cover-info">';
        echo '<div>จัดทำโดย</div>';
        if ($cover['authors']) {
            foreach (explode("\n", $cover['authors']) as $i => $aLine) {
                echo '<div><strong>' . $h($aLine) . '</strong></div>';
                $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];
                if (isset($idLines[$i]) && trim($idLines[$i])) echo '<div>' . $h(trim($idLines[$i])) . '</div>';
            }
        }
        if ($coverType === 'internship') {
            if ($cover['company']) echo '<br><div>สถานประกอบการ: ' . $h($cover['company']) . '</div>';
            if ($cover['supervisor']) echo '<div>ผู้ควบคุม: ' . $h($cover['supervisor']) . '</div>';
            if ($cover['internshipPeriod']) echo '<div>ช่วงเวลา: ' . $h($cover['internshipPeriod']) . '</div>';
            if ($cover['instructor']) echo '<div>อาจารย์นิเทศ: ' . $h($cover['instructor']) . '</div>';
        } elseif ($coverType === 'thesis') {
            if ($cover['committee']) {
                echo '<br><div>คณะกรรมการที่ปรึกษา</div>';
                foreach (explode("\n", $cover['committee']) as $cl) echo '<div>' . $h($cl) . '</div>';
            }
        } else {
            if ($cover['instructor']) {
                echo '<br><div>เสนอ</div><div><strong>' . $h($cover['instructor']) . '</strong></div>';
            }
        }
        echo '<br><div>' . $h($cover['institution'] ?: '') . '</div>';
        if ($coverType === 'thesis') {
            if ($cover['year']) echo '<div>พ.ศ. ' . $h($cover['year']) . '</div>';
        } else {
            $semStr = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
            $yearStr = $cover['year'] ? ' ปีการศึกษา ' . $h($cover['year']) : '';
            echo '<div>ภาคเรียนที่ ' . $semStr . $yearStr . '</div>';
        }
        echo '</div>';
    }
    echo '</div></div></div>';

    if (($tpl['name'] ?? '') === 'รายงานการวิจัย' && !empty($tpl['hasInnerCover'])) {
        echo '<div class="page"></div>';
        echo '<div class="page"><div class="cover-page">';
        echo '<div class="cover-top">';
        if (!empty($tpl['showLogo']) && $logoDataUri !== '') {
            echo '<img class="cover-logo-image" src="' . $h($logoDataUri) . '" alt="ตราสถาบัน">';
        }
        echo '<div class="cover-title cover-title-academic-logo">' . nl2br($h($cover['title'] ?: '[ชื่อรายงาน]')) . '</div>';
        echo '</div>';
        echo '<div class="cover-middle cover-info cover-info-academic-logo">';
        foreach (($cover['authors'] ? explode("\n", $cover['authors']) : ['[ชื่อ-สกุล ผู้จัดทำ]']) as $index => $authorLine) {
            echo '<div>' . $h(trim($authorLine)) . '</div>';
            $idLines = $cover['studentIds'] ? explode("\n", $cover['studentIds']) : [];
            if (isset($idLines[$index]) && trim($idLines[$index]) !== '') {
                echo '<div>รหัส ' . $h(trim($idLines[$index])) . '</div>';
            }
        }
        echo '</div>';
        echo '<div class="cover-bottom cover-info cover-info-academic-logo">';
        echo '<div>' . $h($cover['course'] ?: '[สาขาวิชา]') . '</div>';
        echo '<div>' . $h($cover['department'] ?: '[ภาควิชา/คณะ]') . '</div>';
        echo '<div>' . $h($cover['institution'] ?: '[สถาบัน]') . '</div>';
        if ($cover['year']) {
            $semesterText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
            echo '<div>ภาคการศึกษาที่ ' . $semesterText . '/' . $h($cover['year']) . '</div>';
        }
        echo '</div>';
        echo '</div></div></div>';
    }

    // ---- PREFACE ----
    if (!empty($tpl['hasPreface'])) {
        $prefaceContent = trim((string) ($cover['prefaceContent'] ?? ''));
        if ($prefaceContent === '') {
            $prefaceContent = 'รายงานฉบับนี้จัดทำขึ้นเพื่อเป็นส่วนหนึ่งของการศึกษาในรายวิชา...\n\nผู้จัดทำหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะเป็นประโยชน์...';
        }
        $prefaceSigner = trim((string) ($cover['prefaceSigner'] ?? ''));
        if ($prefaceSigner === '' && !empty($cover['authors'])) {
            $prefaceSigner = trim(explode("\n", $cover['authors'])[0]);
        }
        $prefaceDate = trim((string) ($cover['prefaceDate'] ?? ''));

        echo '<div class="page">';
        echo '<div class="preface-heading">คำนำ</div>';
        foreach (preg_split('/\R{2,}/u', $prefaceContent) as $paragraph) {
            $paragraph = normalizeThaiParagraphText($paragraph);
            if ($paragraph === '') continue;
            echo '<p class="preface-body">' . $h($paragraph) . '</p>';
        }
        if ($prefaceSigner !== '' || $prefaceDate !== '') {
            echo '<div style="text-align:right; margin-top:60px; line-height:1.8;">';
            if ($prefaceSigner !== '') echo '<div>' . $h($prefaceSigner) . '</div>';
            if ($prefaceDate !== '') echo '<div>' . $h($prefaceDate) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    // ---- ACKNOWLEDGMENT ----
    if ($tpl['hasAcknowledgment']) {
        echo '<div class="page">';
        echo '<div class="' . ((($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'preface-heading' : 'section-heading') . '">กิตติกรรมประกาศ</div>';
        echo '<p class="body-placeholder">ขอขอบพระคุณ' . $h($cover['instructor'] ?: '...') . ' ที่ให้คำปรึกษาและแนะนำแนวทางการวิจัยอย่างดียิ่งตลอดระยะเวลาการศึกษา ขอขอบคุณท่านผู้เกี่ยวข้องทุกท่านที่ให้ความอนุเคราะห์ในการดำเนินการวิจัย และขอขอบคุณครอบครัวที่ให้การสนับสนุนและเป็นกำลังใจตลอดมา</p>';
        $firstAuthor = $cover['authors'] ? trim(explode("\n", $cover['authors'])[0]) : '';
        echo '<div style="text-align:right; margin-top:60px;">' . ($firstAuthor ? $h($firstAuthor) : '') . '</div>';
        echo '</div>';
    }

    // ---- ABSTRACT ----
    if ($tpl['hasAbstract']) {
        echo '<div class="page">';
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            echo '<div class="abstract-meta-block">';
            echo '<div class="abstract-meta-line">หัวข้อการค้นคว้าอิสระ</div>';
            echo '<div class="abstract-meta-line">ผู้เขียน</div>';
            echo '<div class="abstract-meta-line">ปริญญา</div>';
            echo '<div class="abstract-meta-line">อาจารย์ที่ปรึกษา</div>';
            echo '</div>';
        }
        echo '<div class="' . ((($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'preface-heading' : 'section-heading') . '">บทคัดย่อ</div>';
        echo '<p class="body-placeholder">กรอกบทคัดย่อภาษาไทยในที่นี้ ความยาว 150–300 คำ ระบุวัตถุประสงค์ วิธีดำเนินการ ผลการศึกษา และข้อสรุป</p>';
        echo '<p class="body-placeholder"><strong>คำสำคัญ:</strong> คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3</p>';
        echo '</div>';

        if (!empty($tpl['hasAbstractEnglish'])) {
            echo '<div class="page">';
            if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
                echo '<div class="abstract-meta-block">';
                echo '<div class="abstract-meta-line">Independent Study Title</div>';
                echo '<div class="abstract-meta-line">Author</div>';
                echo '<div class="abstract-meta-line">Degree</div>';
                echo '<div class="abstract-meta-line">Advisor</div>';
                echo '</div>';
            }
            $abstractHeadingHtml = (($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'ABSTRACT' : 'Abstract';
            echo '<div class="' . ((($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'preface-heading' : 'section-heading') . '">' . $abstractHeadingHtml . '</div>';
            echo '<p class="body-placeholder" style="padding-left:0; text-indent:1.5cm;">Write the English abstract here in 150-300 words, covering the objective, methodology, findings, and conclusion.</p>';
            echo '<p class="body-placeholder" style="padding-left:0;"><strong>Keywords:</strong> keyword 1, keyword 2, keyword 3</p>';
            echo '</div>';
        }
    }

    // ---- TOC ----
    if ($tpl['hasToc']) {
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            $tocPages = buildResearchTocEntries($tpl);
            foreach ($tocPages as $pageIndex => $tocEntries) {
                echo '<div class="page">';
                echo '<div class="preface-heading">' . ($pageIndex === 0 ? 'สารบัญ' : 'สารบัญ(ต่อ)') . '</div>';
                echo '<div style="text-align:right; font-size:16pt; font-weight:bold; line-height:1; margin-bottom:16px; color:#111;">หน้า</div>';
                foreach ($tocEntries as $entry) {
                    $indentClass = !empty($entry['indent']) ? ' toc-line-indent' : '';
                    echo "<div class='toc-line{$indentClass}'><span class='toc-label'>" . $h($entry['label']) . "</span><span class='toc-dots'></span><span class='toc-page'>" . $h($entry['page']) . "</span></div>";
                }
                echo '</div>';
            }

            if (!empty($tpl['hasFigureList'])) {
                echo '<div class="page">';
                echo '<div class="preface-heading">สารบัญภาพ</div>';
                echo '<div style="display:flex; justify-content:space-between; align-items:flex-end; font-size:16pt; font-weight:bold; line-height:1; margin-bottom:16px; color:#111;"><span>ภาพที่</span><span>หน้า</span></div>';
                foreach (buildResearchFigureEntries() as $entry) {
                    echo "<div class='toc-line'><span class='toc-label'>" . $h($entry['label']) . "</span><span class='toc-dots'></span><span class='toc-page'>" . $h($entry['page']) . "</span></div>";
                }
                echo '</div>';
            }

            if (!empty($tpl['hasTableList'])) {
                echo '<div class="page">';
                echo '<div class="preface-heading">สารบัญตาราง</div>';
                echo '<div style="display:flex; justify-content:space-between; align-items:flex-end; font-size:16pt; font-weight:bold; line-height:1; margin-bottom:16px; color:#111;"><span>ตารางที่</span><span>หน้า</span></div>';
                foreach (buildResearchTableEntries() as $entry) {
                    echo "<div class='toc-line'><span class='toc-label'>" . $h($entry['label']) . "</span><span class='toc-dots'></span><span class='toc-page'>" . $h($entry['page']) . "</span></div>";
                }
                echo '</div>';
            }
        } else {
            echo '<div class="page">';
            echo '<div class="section-heading">สารบัญ</div><br>';
            $pg = 1;
            if (!empty($tpl['hasPreface'])) { $pg++; echo "<div class='toc-line'><span class='toc-label'>คำนำ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>"; }
            if ($tpl['hasAcknowledgment']) { $pg++; echo "<div class='toc-line'><span class='toc-label'>กิตติกรรมประกาศ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>"; }
            if ($tpl['hasAbstract']) {
                $pg++;
                echo "<div class='toc-line'><span class='toc-label'>บทคัดย่อ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
                if (!empty($tpl['hasAbstractEnglish'])) {
                    $pg++;
                    echo "<div class='toc-line'><span class='toc-label'>Abstract</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
                }
            }
            $pg++;
            echo "<div class='toc-line'><span class='toc-label'>สารบัญ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
            foreach ($tpl['chapters'] as $ch) {
                $pg++;
                $lbl = "บทที่ {$ch['number']} {$ch['title']}";
                echo "<div class='toc-line'><span class='toc-label'>" . $h($lbl) . "</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
                foreach ($ch['subsections'] as $i => $sub) {
                    $subLbl = "{$ch['number']}." . ($i + 1) . " {$sub}";
                    echo "<div class='toc-line toc-line-indent'><span class='toc-label'>" . $h($subLbl) . "</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
                }
            }
            $pg++;
            echo "<div class='toc-line'><span class='toc-label'>บรรณานุกรม</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>";
            if ($tpl['hasAppendix']) { $pg++; echo "<div class='toc-line'><span class='toc-label'>ภาคผนวก</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>"; }
            echo '</div>';
        }
    }

    // ---- CHAPTERS ----
    foreach ($tpl['chapters'] as $ch) {
        echo '<div class="page">';
        echo '<div class="chapter-heading-num">บทที่ ' . $ch['number'] . '</div>';
        echo '<div class="chapter-heading-title">' . $h($ch['title']) . '</div>';
        foreach ($ch['subsections'] as $sub) {
            echo '<div class="sub-heading">' . $h($sub) . '</div>';
            echo '<p class="body-placeholder">กรอกเนื้อหาในส่วน "' . $h($sub) . '" ในที่นี้ ใช้ขนาดตัวอักษร ' . $bodyPt . 'pt ระยะบรรทัด 1.5 เว้นย่อหน้า 1.5 cm</p>';
        }
        echo '</div>';
    }

    // ---- BIBLIOGRAPHY ----
    echo '<div class="page">';
    echo '<div class="' . ((($tpl['name'] ?? '') === 'รายงานการวิจัย') ? 'preface-heading' : 'section-heading') . '">บรรณานุกรม</div>';
    if (empty($bibliographies)) {
        echo '<p class="bib-item">(ไม่มีรายการบรรณานุกรม)</p>';
    } else {
        $thBibs = array_filter($bibliographies, fn($b) => $b['language'] === 'th');
        $enBibs = array_filter($bibliographies, fn($b) => $b['language'] !== 'th');
        foreach (array_merge($thBibs, $enBibs) as $bib) {
            echo '<p class="bib-item">' . $bib['bibliography_text'] . '</p>';
        }
    }
    echo '</div>';

    // ---- APPENDIX ----
    if ($tpl['hasAppendix']) {
        if (($tpl['name'] ?? '') === 'รายงานการวิจัย') {
            echo '<div class="page">';
            echo '<div class="page-center-block">';
            echo '<div class="preface-heading">ภาคผนวก ก</div>';
            echo '<p class="body-placeholder" style="padding-left:0; text-align:center;">(ตัวอย่างเครื่องมือวิจัย แบบสอบถาม หรือเอกสารประกอบ)</p>';
            echo '</div>';
            echo '</div>';
            echo '<div class="page">';
            echo '<div class="page-center-block">';
            echo '<div class="preface-heading">ภาคผนวก ข</div>';
            echo '<p class="body-placeholder" style="padding-left:0; text-align:center;">(ตัวอย่างภาพประกอบ ผลงาน หรือข้อมูลเพิ่มเติม)</p>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="page">';
            echo '<div class="section-heading">ภาคผนวก</div><br>';
            echo '<p class="body-placeholder">(เพิ่มเนื้อหาภาคผนวกในที่นี้)</p>';
            echo '</div>';
        }
    }

    if (!empty($tpl['hasBiography'])) {
        echo '<div class="page">';
        echo '<div class="preface-heading">ประวัติผู้วิจัย</div>';
        echo '<p class="body-placeholder">ชื่อ-สกุล ..............................................................................</p>';
        echo '<p class="body-placeholder">ประวัติการศึกษา .....................................................................</p>';
        echo '<p class="body-placeholder">ประสบการณ์หรือผลงานที่เกี่ยวข้อง ..........................................</p>';
        echo '</div>';
    }
?>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 800);
        };
    </script>
</body>
</html>
<?php
    $html = ob_get_clean();
    echo $html;
    exit;
}

// ======================================================
//  Helpers
// ======================================================
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

function sanitizeReportFilename($name)
{
    $name = preg_replace('/[^a-zA-Z0-9ก-๙\s\-_]/u', '', $name);
    $name = preg_replace('/\s+/', '_', trim($name));
    return $name ?: 'report';
}
