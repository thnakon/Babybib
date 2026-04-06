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

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    die('กรุณาเข้าสู่ระบบ');
}

$userId = getCurrentUserId();

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

$validTemplates = ['academic_general', 'research', 'internship', 'project', 'thesis'];
if (!in_array($templateId, $validTemplates)) {
    http_response_code(400);
    die('Template ไม่ถูกต้อง');
}

if (!in_array($format, ['docx', 'pdf'])) {
    http_response_code(400);
    die('รูปแบบไม่ถูกต้อง');
}

// Sanitize cover data
$cover = [];
$allowedCoverKeys = ['title', 'authors', 'studentIds', 'course', 'courseCode', 'instructor', 'department',
    'institution', 'company', 'supervisor', 'projectType', 'internshipPeriod', 'degree', 'major',
    'committee', 'semester', 'year'];
foreach ($allowedCoverKeys as $key) {
    $cover[$key] = htmlspecialchars_decode(strip_tags($coverData[$key] ?? ''), ENT_QUOTES);
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
    try {
        $db = getDB();

        // Verify project ownership
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
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์ของการศึกษา', 'ขอบเขตการศึกษา', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์']],
            ['number' => 2, 'title' => 'เนื้อหา', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'เนื้อหาสาระ', 'รายละเอียดและการวิเคราะห์']],
            ['number' => 3, 'title' => 'สรุปและอภิปรายผล', 'subsections' => ['สรุปผลการศึกษา', 'อภิปรายผล', 'ข้อเสนอแนะ']],
        ],
        'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => false,
    ],
    'research' => [
        'name' => 'รายงานการวิจัย',
        'coverType' => 'research',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญ', 'คำถามวิจัย', 'วัตถุประสงค์การวิจัย', 'สมมติฐาน', 'ขอบเขตการวิจัย', 'นิยามศัพท์']],
            ['number' => 2, 'title' => 'เอกสารและงานวิจัยที่เกี่ยวข้อง', 'subsections' => ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง', 'กรอบแนวคิดของการวิจัย']],
            ['number' => 3, 'title' => 'วิธีดำเนินการวิจัย', 'subsections' => ['ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือวิจัย', 'การตรวจสอบคุณภาพ', 'การเก็บรวบรวมข้อมูล', 'การวิเคราะห์ข้อมูล']],
            ['number' => 4, 'title' => 'ผลการวิจัย', 'subsections' => ['ลักษณะกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ข้อมูลตามวัตถุประสงค์']],
            ['number' => 5, 'title' => 'สรุป อภิปรายผล และข้อเสนอแนะ', 'subsections' => ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยครั้งต่อไป']],
        ],
        'hasToc' => true, 'hasAbstract' => true, 'hasAcknowledgment' => false, 'hasAppendix' => true,
    ],
    'internship' => [
        'name' => 'รายงานฝึกงาน / สหกิจ',
        'coverType' => 'internship',
        'chapters' => [
            ['number' => 1, 'title' => 'บทนำ', 'subsections' => ['ความเป็นมาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตของรายงาน', 'ประโยชน์ที่ได้รับ']],
            ['number' => 2, 'title' => 'ข้อมูลสถานประกอบการ', 'subsections' => ['ประวัติและความเป็นมา', 'วิสัยทัศน์ พันธกิจ', 'โครงสร้างองค์กร', 'ลักษณะการดำเนินงาน']],
            ['number' => 3, 'title' => 'งานที่ได้รับมอบหมาย', 'subsections' => ['ลักษณะตำแหน่งงาน', 'งานที่ได้รับมอบหมายหลัก', 'ขั้นตอนและวิธีการปฏิบัติงาน']],
            ['number' => 4, 'title' => 'ผลการปฏิบัติงาน', 'subsections' => ['ผลการปฏิบัติงานโดยภาพรวม', 'ปัญหาและอุปสรรค', 'วิธีแก้ปัญหา']],
            ['number' => 5, 'title' => 'สรุปและข้อเสนอแนะ', 'subsections' => ['สรุปผลการฝึกงาน', 'ความรู้และทักษะที่ได้รับ', 'ข้อเสนอแนะ']],
        ],
        'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
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
        'hasToc' => true, 'hasAbstract' => false, 'hasAcknowledgment' => false, 'hasAppendix' => true,
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
        'hasToc' => true, 'hasAbstract' => true, 'hasAcknowledgment' => true, 'hasAppendix' => true,
    ],
];

$tpl = $templateDefs[$templateId];

if ($format === 'docx') {
    exportFullDocx($tpl, $cover, $bibliographies, $margins, $font, $bodyPt);
} else {
    exportPdfPreview($tpl, $cover, $bibliographies, $margins, $font, $bodyPt);
}

// ======================================================
//  DOCX Export
// ======================================================
function exportFullDocx($tpl, $cover, $bibliographies, $margins, $font, $bodyPt)
{
    $titleSz   = 40;   // 20pt in half-points
    $headingSz = 36;   // 18pt
    $subSz     = 32;   // 16pt (or use bodyPt)
    $bodySz    = $bodyPt * 2; // e.g. 32 for 16pt

    $lineSpacing = 360; // 1.5 lines (auto)
    $paraAfter   = 0;
    $paraBefore  = 0;

    $m = $margins;
    $sectPr = '<w:sectPr>'
        . '<w:pgSz w:w="11906" w:h="16838"/>'
        . "<w:pgMar w:top=\"{$m['top']}\" w:right=\"{$m['right']}\" w:bottom=\"{$m['bottom']}\" "
        . "w:left=\"{$m['left']}\" w:header=\"720\" w:footer=\"720\" w:gutter=\"0\"/>"
        . '</w:sectPr>';

    // Helper: make a run with optional bold/italic/center
    // All params except $text are optional
    function wRun($text, $font, $sz, $bold = false, $italic = false)
    {
        $text = htmlspecialchars((string)$text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $rpr = "<w:rFonts w:ascii=\"{$font}\" w:hAnsi=\"{$font}\" w:eastAsia=\"{$font}\" w:cs=\"{$font}\"/>"
            . ($bold   ? '<w:b/><w:bCs/>'   : '')
            . ($italic ? '<w:i/><w:iCs/>'   : '')
            . "<w:sz w:val=\"{$sz}\"/><w:szCs w:val=\"{$sz}\"/>";
        return "<w:r><w:rPr>{$rpr}</w:rPr><w:t xml:space=\"preserve\">{$text}</w:t></w:r>";
    }

    // Helper: paragraph
    function wPara($runs, $align = '', $spacingLine = 0, $indLeft = 0, $indHanging = 0, $spaceBefore = 0, $spaceAfter = 0)
    {
        $ppr = '';
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
        if ($indLeft || $indHanging) {
            $ppr .= "<w:ind w:left=\"{$indLeft}\" w:hanging=\"{$indHanging}\"/>";
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

    // Helper: page break
    function wPageBreak()
    {
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . "\n<w:document xmlns:w=\"http://schemas.openxmlformats.org/wordprocessingml/2006/main\">\n<w:body>\n";

    // ==========================================
    // COVER PAGE
    // ==========================================
    $coverType = $tpl['coverType'];

    // Institution at top
    if ($cover['institution']) {
        $content .= wPara([wRun($cover['institution'], $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
    }
    if ($cover['department']) {
        $content .= wPara([wRun($cover['department'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
    }

    $content .= wBlank($font, $bodySz, 4);

    // Title
    if ($cover['title']) {
        $lines = explode("\n", $cover['title']);
        foreach ($lines as $line) {
            $content .= wPara([wRun(trim($line), $font, $titleSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    } else {
        $content .= wPara([wRun('[ชื่อรายงาน]', $font, $titleSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
    }

    $content .= wBlank($font, $bodySz, 2);

    // Subtitle / type label
    if ($coverType === 'academic') {
        $courseLabel = $cover['course'] ? "รายงานนี้เป็นส่วนหนึ่งของรายวิชา {$cover['course']}" : 'รายงานนี้เป็นส่วนหนึ่งของรายวิชา';
        $content .= wPara([wRun($courseLabel, $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
    } elseif ($coverType === 'internship') {
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
        $content .= wPara([wRun('รายงานการวิจัย', $font, $headingSz)], 'center', $lineSpacing, 0, 0, 0, 0);
    }

    $content .= wBlank($font, $bodySz, 4);

    // Author block
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

    // Instructor / supervisor
    if ($coverType === 'internship') {
        if ($cover['company']) {
            $content .= wPara([wRun('สถานประกอบการ: ' . $cover['company'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($cover['supervisor']) {
            $content .= wPara([wRun('ผู้ควบคุมการฝึกงาน: ' . $cover['supervisor'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($cover['internshipPeriod']) {
            $content .= wPara([wRun('ช่วงเวลา: ' . $cover['internshipPeriod'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
        if ($cover['instructor']) {
            $content .= wPara([wRun('อาจารย์นิเทศ: ' . $cover['instructor'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    } elseif ($coverType === 'thesis') {
        if ($cover['committee']) {
            $content .= wBlank($font, $bodySz, 1);
            $content .= wPara([wRun('คณะกรรมการที่ปรึกษา', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            $committeeLines = explode("\n", $cover['committee']);
            foreach ($committeeLines as $cl) {
                $content .= wPara([wRun(trim($cl), $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            }
        }
    } else {
        if ($cover['instructor']) {
            $content .= wPara([wRun('เสนอ', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
            $content .= wPara([wRun($cover['instructor'], $font, $bodySz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    }

    $content .= wBlank($font, $bodySz, 2);

    // Bottom: institution + semester/year
    if ($cover['institution']) {
        $content .= wPara([wRun($cover['institution'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
    }

    if ($coverType === 'thesis') {
        if ($cover['year']) {
            $content .= wPara([wRun('พ.ศ. ' . $cover['year'], $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    } else {
        if ($cover['semester'] || $cover['year']) {
            $semText = $cover['semester'] === '1' ? '1' : ($cover['semester'] === '2' ? '2' : 'ฤดูร้อน');
            $yearStr = $cover['year'] ? ' ปีการศึกษา ' . $cover['year'] : '';
            $content .= wPara([wRun('ภาคเรียนที่ ' . $semText . $yearStr, $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
        }
    }

    $content .= wPageBreak();

    // ==========================================
    // ACKNOWLEDGMENT (Thesis only)
    // ==========================================
    if ($tpl['hasAcknowledgment']) {
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun('กิตติกรรมประกาศ', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
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
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun('บทคัดย่อ', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $abstractText = 'กรอกบทคัดย่อภาษาไทยในที่นี้ ความยาว 150–300 คำ ระบุวัตถุประสงค์ วิธีดำเนินการ ผลการศึกษา และข้อสรุป';
        $content .= wPara([wRun($abstractText, $font, $bodySz)], 'thaiDistribute', $lineSpacing, 720, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $content .= wPara([wRun('คำสำคัญ: คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3', $font, $bodySz)], '', $lineSpacing, 0, 0, 0, 0);
        $content .= wPageBreak();
    }

    // ==========================================
    // TABLE OF CONTENTS (all templates)
    // ==========================================
    if ($tpl['hasToc']) {
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun('สารบัญ', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);

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

        $pg = 1;
        if ($tpl['hasAcknowledgment']) {
            $pg++;
            $content .= tocEntry(htmlspecialchars('กิตติกรรมประกาศ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
        }
        if ($tpl['hasAbstract']) {
            $pg++;
            $content .= tocEntry(htmlspecialchars('บทคัดย่อ', ENT_XML1, 'UTF-8'), $pg, $font, $bodySz);
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

    // ==========================================
    // CHAPTERS
    // ==========================================
    foreach ($tpl['chapters'] as $ch) {
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun("บทที่ {$ch['number']}", $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        $content .= wPara([wRun($ch['title'], $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 480, 0);

        foreach ($ch['subsections'] as $sub) {
            $content .= wPara([wRun($sub, $font, $bodySz, true)], '', $lineSpacing, 0, 0, 240, 0);
            // Body placeholder paragraph
            $placeholder = 'กรอกเนื้อหาในส่วน"' . $sub . '"ในที่นี้ ใช้ขนาดตัวอักษร ' . $bodyPt . 'pt ระยะบรรทัด 1.5 เว้นย่อหน้า 1.5 cm กดลบข้อความนี้แล้วพิมพ์เนื้อหาของท่านได้เลย';
            $content .= wPara([wRun($placeholder, $font, $bodySz)], 'thaiDistribute', $lineSpacing, 720, 0, 0, 0);
        }

        $content .= wPageBreak();
    }

    // ==========================================
    // BIBLIOGRAPHY
    // ==========================================
    $content .= wBlank($font, $bodySz, 2);
    $content .= wPara([wRun('บรรณานุกรม', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 480);

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
        $content .= wPageBreak();
        $content .= wBlank($font, $bodySz, 2);
        $content .= wPara([wRun('ภาคผนวก', $font, $headingSz, true)], 'center', $lineSpacing, 0, 0, 0, 0);
        $content .= wBlank($font, $bodySz, 1);
        $content .= wPara([wRun('(เพิ่มเนื้อหาภาคผนวกในที่นี้ เช่น แบบสอบถาม รูปภาพ เอกสารประกอบ)', $font, $bodySz)], 'center', $lineSpacing, 0, 0, 0, 0);
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
        '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
        '</Relationships>');

    $zip->addFromString('word/document.xml', $content);

    $zip->addFromString('word/_rels/document.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');

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

        /* Headings */
        .section-heading { font-size: <?php echo $bodyPt + 2; ?>pt; font-weight: bold; text-align: center; margin: 20px 0 10px; }
        .chapter-heading-num { font-size: <?php echo $bodyPt + 2; ?>pt; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .chapter-heading-title { font-size: <?php echo $bodyPt + 2; ?>pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .sub-heading { font-size: <?php echo $bodyPt; ?>pt; font-weight: bold; margin: 14px 0 6px; }
        .body-placeholder { font-size: <?php echo $bodyPt; ?>pt; text-align: justify; margin-bottom: 10px; padding-left: 1.5cm; }

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
    echo '<div class="cover-top">';
    if ($cover['institution']) echo '<div>' . $h($cover['institution']) . '</div>';
    if ($cover['department']) echo '<div>' . $h($cover['department']) . '</div>';
    echo '</div>';
    echo '<div class="cover-middle">';
    echo '<div class="cover-title">' . nl2br($h($cover['title'] ?: '[ชื่อรายงาน]')) . '</div>';
    // Type label
    $coverType = $tpl['coverType'];
    if ($coverType === 'academic') {
        $courseLabel = $cover['course'] ? "รายงานนี้เป็นส่วนหนึ่งของรายวิชา " . $h($cover['course']) : 'รายงานนี้เป็นส่วนหนึ่งของรายวิชา';
        echo '<div class="cover-subtitle">' . $courseLabel . '</div>';
    } elseif ($coverType === 'thesis') {
        $degree = $h($cover['degree'] ?: 'วิทยาศาสตรมหาบัณฑิต');
        $major  = $h($cover['course'] ?: '[สาขาวิชา]');
        echo '<div class="cover-subtitle">วิทยานิพนธ์นี้เป็นส่วนหนึ่งของการศึกษาตามหลักสูตร<br>' . $degree . ' สาขา' . $major . '</div>';
    } elseif ($coverType === 'internship') {
        echo '<div class="cover-subtitle">รายงานฝึกประสบการณ์วิชาชีพ</div>';
    } elseif ($coverType === 'project') {
        echo '<div class="cover-subtitle">' . $h($cover['projectType'] ?: 'รายงานโครงการ') . '</div>';
    } elseif ($coverType === 'research') {
        echo '<div class="cover-subtitle">รายงานการวิจัย</div>';
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
    echo '</div></div></div>';

    // ---- ACKNOWLEDGMENT ----
    if ($tpl['hasAcknowledgment']) {
        echo '<div class="page">';
        echo '<div class="section-heading">กิตติกรรมประกาศ</div>';
        echo '<p class="body-placeholder">ขอขอบพระคุณ' . $h($cover['instructor'] ?: '...') . ' ที่ให้คำปรึกษาและแนะนำแนวทางการวิจัยอย่างดียิ่งตลอดระยะเวลาการศึกษา ขอขอบคุณท่านผู้เกี่ยวข้องทุกท่านที่ให้ความอนุเคราะห์ในการดำเนินการวิจัย และขอขอบคุณครอบครัวที่ให้การสนับสนุนและเป็นกำลังใจตลอดมา</p>';
        $firstAuthor = $cover['authors'] ? trim(explode("\n", $cover['authors'])[0]) : '';
        echo '<div style="text-align:right; margin-top:60px;">' . ($firstAuthor ? $h($firstAuthor) : '') . '</div>';
        echo '</div>';
    }

    // ---- ABSTRACT ----
    if ($tpl['hasAbstract']) {
        echo '<div class="page">';
        echo '<div class="section-heading">บทคัดย่อ</div>';
        echo '<p class="body-placeholder">กรอกบทคัดย่อภาษาไทยในที่นี้ ความยาว 150–300 คำ ระบุวัตถุประสงค์ วิธีดำเนินการ ผลการศึกษา และข้อสรุป</p>';
        echo '<p class="body-placeholder"><strong>คำสำคัญ:</strong> คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3</p>';
        echo '</div>';
    }

    // ---- TOC ----
    if ($tpl['hasToc']) {
        echo '<div class="page">';
        echo '<div class="section-heading">สารบัญ</div><br>';
        $pg = 1;
        if ($tpl['hasAcknowledgment']) { $pg++; echo "<div class='toc-line'><span class='toc-label'>กิตติกรรมประกาศ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>"; }
        if ($tpl['hasAbstract'])       { $pg++; echo "<div class='toc-line'><span class='toc-label'>บทคัดย่อ</span><span class='toc-dots'></span><span class='toc-page'>{$pg}</span></div>"; }
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
    echo '<div class="section-heading">บรรณานุกรม</div><br>';
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
        echo '<div class="page">';
        echo '<div class="section-heading">ภาคผนวก</div><br>';
        echo '<p class="body-placeholder">(เพิ่มเนื้อหาภาคผนวกในที่นี้)</p>';
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
