<?php

/**
 * Babybib API - Template: Export Internship Report (Pure Programmatic)
 * ===================================================================
 * POST /api/template/export-report-internship.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

$userId = isLoggedIn() ? getCurrentUserId() : null;

// 1. Parse payload
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

$coverData = $payload['coverData'] ?? [];
$projectId = intval($payload['projectId'] ?? 0);

// 2. Initialize PHPWord
$phpWord = new \PhpOffice\PhpWord\PhpWord();
\PhpOffice\PhpWord\Settings::setTempDir(__DIR__ . '/../../tmp');

// Configure Default Font
$fontName = 'TH SarabunPSK';
$phpWord->setDefaultFontName($fontName);
$phpWord->setDefaultFontSize(16);

// Define Styles
$phpWord->addTitleStyle(1, ['size' => 18, 'bold' => true, 'name' => $fontName], ['align' => 'center', 'spaceBefore' => 240, 'spaceAfter' => 240]);
$phpWord->addTitleStyle(2, ['size' => 16, 'bold' => true, 'name' => $fontName], ['spaceBefore' => 120, 'spaceAfter' => 120]);
$phpWord->addTitleStyle(3, ['size' => 16, 'bold' => true, 'name' => $fontName], ['spaceBefore' => 80, 'spaceAfter' => 80]);

// Hide spelling and grammar errors (no more red squiggly lines)
$phpWord->getSettings()->setHideGrammaticalErrors(true);
$phpWord->getSettings()->setHideSpellingErrors(true);

$styleBody = ['name' => $fontName, 'size' => 16];
$styleCenterBold = ['name' => $fontName, 'size' => 16, 'bold' => true];
$styleCoverBold18 = ['name' => $fontName, 'size' => 18, 'bold' => true];
$styleAuthorDate = ['name' => $fontName, 'size' => 16, 'italic' => false];

// Define common paragraph styles
$paragraphCenter = ['align' => 'center'];
$paragraphCenterNoSpace = ['align' => 'center', 'spaceAfter' => 0];
$paragraphJustify = ['align' => 'both', 'indentation' => ['firstLine' => 720]]; // 0.5 inch indent

// Standard Thai body style: Left aligned to ensure natural spacing and wrapping
$phpWord->addParagraphStyle('thai_body', [
    'align' => 'left', 
    'indentation' => ['firstLine' => 720],
    'spacing' => ['before' => 0, 'after' => 0]
]);

// New style for 1-inch indentation
$phpWord->addParagraphStyle('thai_body_indent_1', [
    'align' => 'left', 
    'indentation' => ['left' => 1440], 
    'spacing' => ['before' => 0, 'after' => 0]
]);

// New style for 1.5-inch indentation
$phpWord->addParagraphStyle('thai_body_indent_1_5', [
    'align' => 'left', 
    'indentation' => ['left' => 2160], // Whole block indent 1.5 inch
    'spacing' => ['before' => 0, 'after' => 0]
]);

// Style for Bibliography Entry (Hanging Indent 0.5 inch)
$phpWord->addParagraphStyle('bib_entry', [
    'align' => 'left',
    'indentation' => ['left' => 720, 'hanging' => 720], // 0.5 inch hanging indent
    'spacing' => ['before' => 60, 'after' => 60]
]);

// TOC Style: Right-aligned tab for page numbers (NO DOTS as requested)
$phpWord->addParagraphStyle('toc_chapter', [
    'tabs' => [new \PhpOffice\PhpWord\Style\Tab('right', 9000)]
]);
$phpWord->addParagraphStyle('toc_subitem', [
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('left', 720), // 0.5 inch indent for the subitem text
        new \PhpOffice\PhpWord\Style\Tab('right', 9000) // Right align for page number
    ]
]);

// Section Settings (A4)
$sectionStyle = [
    'orientation' => 'portrait',
    'pageSizeW' => 11906, // 21 cm
    'pageSizeH' => 16838, // 29.7 cm
    'marginLeft' => 1440, // 1 inch
    'marginRight' => 1440,
    'marginTop' => 1440,
    'marginBottom' => 1440,
];

$section = $phpWord->addSection($sectionStyle);

// --------------------------------------------------------------------------
// PAGE 1: COVER PAGE
// --------------------------------------------------------------------------
// Resolve and add logo
$logoMeta = resolveLogoForWord($coverData);
if ($logoMeta && !empty($logoMeta['bytes'])) {
    $ext = $logoMeta['ext'] ?? 'png';
    $logoTemp = tempnam(\PhpOffice\PhpWord\Settings::getTempDir(), 'LOGO_') . '.' . $ext;
    if (file_put_contents($logoTemp, $logoMeta['bytes'])) {
        $section->addImage($logoTemp, [
            'width' => 80, 
            'height' => 80, 
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
        ]);
        // Note: we can't easily unlink here if we want it in the doc, though PHPWord might have read it into memory.
        // Usually it's safer to keep it until the end.
    }
}

// Removed addTextBreak here to move title up
$section->addText($coverData['title'] ?? 'รายงานการฝึกประสบการณ์วิชาชีพสารสนเทศ', ['bold' => true, 'size' => 22], $paragraphCenterNoSpace);

$companyVal = !empty($coverData['company']) ? $coverData['company'] : '';
$institutionVal = !empty($companyVal) ? $companyVal : (!empty($coverData['institution']) ? $coverData['institution'] : '');
if (empty($institutionVal)) $institutionVal = '[ชื่อสถานประกอบการ]';
$section->addText($institutionVal, $styleCoverBold18, $paragraphCenter);

$section->addTextBreak(5);
$section->addText($coverData['authors'] ?? '[ชื่อผู้จัดทำ]', $styleCoverBold18, $paragraphCenter);
$section->addText('รหัสนักศึกษา ' . ($coverData['studentIds'] ?? '[รหัส]'), $styleCoverBold18, $paragraphCenter);

$section->addTextBreak(5);
$semText = $coverData['semester'] ?? '2';
$yearText = $coverData['year'] ?? '2566';

$section->addText('รายงานการฝึกประสบการณ์วิชาชีพสารสนเทศนี้ เป็นส่วนหนึ่งของการศึกษา', $styleCoverBold18, $paragraphCenter);
$section->addText('ตามหลักสูตร ปริญญาศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา', $styleCoverBold18, $paragraphCenter);
$section->addText('ภาคเรียนที่ ' . $semText . '   ปีการศึกษา ' . $yearText, $styleCoverBold18, $paragraphCenter);
$section->addText('มหาวิทยาลัยเชียงใหม่', $styleCoverBold18, $paragraphCenter);

$section->addPageBreak();

// --------------------------------------------------------------------------
// PAGE 2: ACKNOWLEDGMENT (ประกาศคุณูปการ)
// --------------------------------------------------------------------------
$section->addTitle('ประกาศคุณูปการ', 1);

$ackDefault = "การฝึกประสบการณ์วิชาชีพสารสนเทศประสบการณ์วิชาชีพสารสนเทศครั้งนี้ เป็นการฝึกประสบการณ์วิชาชีพตามหลักสูตรศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา ข้าพเจ้าได้เริ่มฝึกประสบการณ์วิชาชีพสารสนเทศตั้งแต่วันที่ …………………. ถึงวันที่ ……………………………. ผลจากการฝึกประสบการณ์วิชาชีพสารสนเทศ ทำให้ข้าพเจ้าได้เรียนรู้จากการปฏิบัติจริง และรับความรู้ทักษะใหม่ๆ ในการทำงาน\n\nข้าพเจ้าขอขอบคุณ 1...(ขอบคุณบุคคลที่ช่วยเหลือในการฝึกประสบการณ์วิชาชีพ)\nขอขอบพระคุณ 2\nขอขอบพระคุณ 3\nผลจากการฝึกประสบการณ์วิชาชีพสารสนเทศในครั้งนี้ ข้าพเจ้าจะได้พัฒนา......(อะไรบ้าง.. นำไปใช้อะไร...)";
$ackRaw = $coverData['acknowledgment_content'] ?: $ackDefault;
$ackLines = preg_split('/\n/', $ackRaw);

foreach ($ackLines as $line) {
    if (trim($line) === "") {
        $section->addTextBreak(1);
    } else {
        $section->addText("\t" . trim($line), $styleBody, $paragraphJustify);
    }
}

$section->addTextBreak(1);
$signer = $coverData['acknowledgment_signer'] ?: ($coverData['authors'] ?? '[ชื่อผู้ฝึกงาน]');
$date = $coverData['acknowledgment_date'] ?: ($coverData['year'] ?? date('Y'));
$section->addText($signer, $styleBody, ['align' => 'right']);
$section->addText($date, $styleBody, ['align' => 'right']);

$section->addPageBreak();

// --------------------------------------------------------------------------
// PAGE 3: TABLE OF CONTENTS (สารบัญ)
// --------------------------------------------------------------------------
$section->addTitle('สารบัญ', 1);
$section->addText('หน้า', ['bold' => true], ['align' => 'right']);

$tocItems = [
    ['text' => 'ประกาศคุณูปการ', 'page' => 'ก', 'style' => 'toc_chapter'],
    ['text' => 'สารบัญ', 'page' => 'ง', 'style' => 'toc_chapter'],
    ['text' => 'บทที่ 1 บทนำ', 'page' => '1', 'style' => 'toc_chapter'],
    ['text' => "\t1. ความเป็นมาและความสำคัญของการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '1', 'style' => 'toc_subitem'],
    ['text' => "\t2. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '1', 'style' => 'toc_subitem'],
    ['text' => "\t3. ประโยชน์ที่คาดว่าจะได้รับจากการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '2', 'style' => 'toc_subitem'],
    ['text' => "\t4. ระยะเวลาการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '3', 'style' => 'toc_subitem'],
    ['text' => 'บทที่ 2 เอกสารและการบูรณาการวิชาการที่เกี่ยวข้อง', 'page' => '4', 'style' => 'toc_chapter'],
    ['text' => "\t1. ข้อมูลพื้นฐานของหน่วยงาน", 'page' => '4', 'style' => 'toc_subitem'],
    ['text' => "\t1.1 ประวัติ", 'page' => '4', 'style' => 'toc_subitem'],
    ['text' => "\t1.2 โครงสร้างการบริหาร/แผนผังองค์กร", 'page' => '4', 'style' => 'toc_subitem'],
    ['text' => "\t1.3 ปณิธาน วิสัยทัศน์ พันธกิจ", 'page' => '5', 'style' => 'toc_subitem'],
    ['text' => "\t1.4 แผนภูมิการบริหารงาน", 'page' => '6', 'style' => 'toc_subitem'],
    ['text' => "\t1.5 บุคลากร", 'page' => '7', 'style' => 'toc_subitem'],
    ['text' => "\t1.6 ที่ตั้ง / แผนที่การเดินทาง / การติดต่อ", 'page' => '8', 'style' => 'toc_subitem'],
    ['text' => "\t1.7 เวลาเปิดบริการ", 'page' => '9', 'style' => 'toc_subitem'],
    ['text' => "\t1.8 ขอบเขตงานของหน่วยงาน", 'page' => '9', 'style' => 'toc_subitem'],
    ['text' => "\t2. การบูรณาการวิชาการ", 'page' => '11', 'style' => 'toc_subitem'],
];

foreach ($tocItems as $item) {
    $section->addText($item['text'] . "\t" . $item['page'], $styleBody, $item['style']);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// PAGE 4: TABLE OF CONTENTS CONT (สารบัญต่อ)
// --------------------------------------------------------------------------
$section->addTitle('สารบัญ (ต่อ)', 1);
$section->addText('หน้า', ['bold' => true], ['align' => 'right']);

$tocContItems = [
    ['text' => 'บทที่ 3 ขั้นตอนการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '19', 'style' => 'toc_chapter'],
    ['text' => "\t1. การดำเนินการก่อนออกฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '20', 'style' => 'toc_subitem'],
    ['text' => "\t2. การดำเนินการระหว่างฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '21', 'style' => 'toc_subitem'],
    ['text' => "\t3. การดำเนินการเมื่อสิ้นสุดการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '22', 'style' => 'toc_subitem'],
    ['text' => 'บทที่ 4 ผลของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '42', 'style' => 'toc_chapter'],
    ['text' => "\t1. งานพัฒนาทรัพยากรสารสนเทศ", 'page' => '42', 'style' => 'toc_subitem'],
    ['text' => "\t2. งานบริการ", 'page' => '44', 'style' => 'toc_subitem'],
    ['text' => 'บทที่ 5 สรุป อภิปรายผล ข้อเสนอแนะ', 'page' => '52', 'style' => 'toc_chapter'],
    ['text' => "\t1. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '52', 'style' => 'toc_subitem'],
    ['text' => "\t2. สรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ", 'page' => '53', 'style' => 'toc_subitem'],
    ['text' => "\t3. อภิปรายผล", 'page' => '55', 'style' => 'toc_subitem'],
    ['text' => "\t4. ข้อเสนอแนะ", 'page' => '56', 'style' => 'toc_subitem'],
    ['text' => 'บรรณานุกรม', 'page' => '58', 'style' => 'toc_chapter'],
    ['text' => 'ภาคผนวก', 'page' => '59', 'style' => 'toc_chapter'],
    ['text' => "\tภาคผนวก ก ภาพจากการปฏิบัติงาน", 'page' => '62', 'style' => 'toc_subitem'],
    ['text' => "\tภาคผนวก ข ผลงานหรือชิ้นงานจากการปฏิบัติงาน (ถ้ามี)", 'page' => '62', 'style' => 'toc_subitem'],
    ['text' => 'ประวัติผู้ฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '68', 'style' => 'toc_chapter'],
];

foreach ($tocContItems as $item) {
    $section->addText($item['text'] . "\t" . $item['page'], $styleBody, $item['style']);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 1
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 1', 1);
$section->addTitle('บทนำ', 1);
$section->addTextBreak(1);

$section->addTitle('1. ความเป็นมาและความสำคัญของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$chap1Content = "การเรียนการสอนในศตวรรษที่ 21 นักศึกษาจำเป็นมีต้องมีทักษะและการเตรียมความพร้อมเข้าสู่ตลาดแรงงานและสังคมเพื่อผลิตบัณฑิตให้เป็นแรงงานที่มีความรู้ (Knowledge worker) ที่สำคัญของประเทศ ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ได้เห็นความสำคัญของการมุ่งพัฒนาให้นักศึกษาเป็นผู้ที่มีความรู้ความสามารถและทักษะที่ครบถ้วนตามกรอบมาตรฐานคุณวุฒิระดับอุดมศึกษาแห่งชาติ (TQF) ซึ่งหลักสูตรศิลปศาสตรบัณฑิตสาขาวิชาสารสนเทศศึกษาเป็นสาขาวิชาที่มีการบูรณาการระหว่างความรู้เชิงทฤษฎีและทักษะด้านการปฏิบัติทางวิชาชีพด้านการจัดการสารสนเทศและการจัดการเทคโนโลยีสารสนเทศในหน่วยงานภาครัฐและภาคเอกชน การฝึกประสบการณ์วิชาชีพสารสนเทศเป็นกระบวนการเพิ่มทักษะและประสบการณ์ที่เป็นประโยชน์แก่การประกอบอาชีพของนักศึกษาเมื่อสำเร็จการศึกษา ช่วยให้นักศึกษามีความรู้ความเข้าใจในการปฏิบัติงานจริงเพื่อให้เกิดทักษะและความสามารถในการทำงานที่ดีสอดคล้องกับความต้องการของตลาดแรงงานและสังคม ทั้งในสถานประกอบการและการประกอบอาชีพอิสระ นักศึกษามีโอกาสได้ทราบถึงขั้นตอนการปฏิบัติงานและเทคนิคการทำงานรวมถึงสร้างความเชื่อมั่นและทัศนคติที่ดีต่อวิชาชีพ ฝึกการทำงานร่วมกับผู้อื่น และที่สำคัญเป็นการเสริมสร้างสมรรถภาพในการทำงานเพื่อการประกอบอาชีพในอนาคต";
$section->addText($chap1Content, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle('2. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText("\t1) เพื่อฝึกให้นักศึกษามีความรับผิดชอบต่อหน้าที่ เคารพระเบียบวินัย และทำงานร่วมกับผู้อื่นได้อย่างมีประสิทธิภาพ", $styleBody, 'thai_body');
$section->addText("\t2) เพื่อให้นักศึกษาได้เพิ่มทักษะ สร้างเสริมประสบการณ์ และพัฒนาวิชาชีพตามสภาพความเป็นจริงในสถานประกอบการ รวมถึงสามารถประยุกต์ความรู้ที่ได้จากการเรียนภาคทฤษฎีมาใช้ในภาคปฏิบัติ", $styleBody, 'thai_body');
$section->addText("\t3) เพื่อให้นักศึกษาได้ทราบถึงปัญหาต่างๆ ที่เกิดขึ้นในขณะปฏิบัติงาน สามารถแก้ปัญหาได้อย่างมีเหตุผล และมีเจตคติที่ดีต่อการทำงานเป็นแนวทางในการประกอบอาชีพต่อไป", $styleBody, 'thai_body');
$section->addText("\t4) เพื่อเสริมสร้างสัมพันธ์ภาพที่ดีระหว่างมหาวิทยาลัยเชียงใหม่กับสถานประกอบการ และหน่วยงานภาครัฐ", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle('3. ประโยชน์ที่คาดว่าจะได้รับจากการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText("\t1) มีความรู้ ความเข้าใจ และสามารถบูรณาการหลักการ และทฤษฎีที่เกี่ยวข้องมาปรับใช้ในการฝึกประสบการณ์วิชาชีพสารสนเทศศึกษา", $styleBody, 'thai_body');
$section->addText("\t2) สามารถวิเคราะห์ปัญหา ประยุกต์ความรู้ ทักษะ และเครื่องมือที่เหมาะสมกับการแก้ไขปัญหา", $styleBody, 'thai_body');
$section->addText("\t3) มีความซื่อสัตย์สุจริต เสียสละ และมีจรรยาบรรณทางวิชาการและวิชาชีพ", $styleBody, 'thai_body');
$section->addText("\t4) สร้างความมีวินัยตรงต่อเวลา ความรับผิดชอบต่อตนเองและสังคม เคารพกฎระเบียบและข้อบังคับต่างๆของสถานประกอบการที่ฝึกประสบการณ์วิชาชีพสารสนเทศศึกษา", $styleBody, 'thai_body');
$section->addText("\t5) มีภาวะผู้นำ และสามารถทำงานเป็นทีมได้", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle('4. ระยะเวลาการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$period = $coverData['internshipPeriod'] ?: 'เริ่มฝึกประสบการณ์วิชาชีพสารสนเทศเมื่อวันที่ …………………………………..ถึง……………………………………………………..';
$section->addText($period, $styleBody, $paragraphJustify);

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 2
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 2', 1);
$section->addTitle('เอกสารและการบูรณาการวิชาการที่เกี่ยวข้อง', 1);
$section->addTextBreak(1);

$intro2 = "ในการฝึกประสบการณ์วิชาชีพสารสนเทศ ตามหลักสูตรศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา ข้าพเจ้าได้ศึกษาวัตถุประสงค์ของหลักสูตร และได้ดำเนินการฝึกประสบการณ์วิชาชีพสารสนเทศ ซึ่งสถานที่ในการฝึกประสบการณ์วิชาชีพสารสนเทศของข้าพเจ้า คือ " . ($institutionVal) . " ตลอดระยะเวลาการฝึกประสบการณ์วิชาชีพสารสนเทศ ข้าพเจ้าได้บูรณาการการฝึกประสบการณ์วิชาชีพสารสนเทศ ให้สอดคล้องกับสาขาวิชาที่กำลังศึกษา และกระบวนวิชาต่างๆ โดยมีรายละเอียดดังต่อไปนี้";
$section->addText("\t" . $intro2, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle('1. ข้อมูลพื้นฐานของหน่วยงาน', 2);
$section->addText("\t1.1 ประวัติ", $styleBody, 'thai_body');
$section->addText("\t1.2 โครงสร้างการบริหาร / แผนผังองค์กร", $styleBody, 'thai_body');
$section->addText("\t1.3 ปณิธาน วิสัยทัศน์ พันธกิจ", $styleBody, 'thai_body');
$section->addText("\t1.4 แผนภูมิการบริหารงาน", $styleBody, 'thai_body');
$section->addText("\t1.5 บุคลากร", $styleBody, 'thai_body');
$section->addText("\t1.6 ที่ตั้ง / แผนที่การเดินทาง/ การติดต่อ", $styleBody, 'thai_body');
$section->addText("\t1.7 เวลาเปิดบริการ", $styleBody, 'thai_body');
$section->addText("\t1.8 ขอบเขตงานของหน่วยงาน", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle('2. การบูรณาการวิชาการ', 2);
$section->addText("\t2.1 กระบวนวิชา....................................", $styleBody, 'thai_body');
$section->addText("1) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("2) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("3) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("4) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("5) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');

$section->addTextBreak(1);
$section->addText("\t2.2 กระบวนวิชา....................................", $styleBody, 'thai_body');
$section->addText("1) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("2) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("3) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("4) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');
$section->addText("5) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody, 'thai_body_indent_1');

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 3
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 3', 1);
$section->addTitle('ขั้นตอนการฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addTextBreak(1);

$chap3Intro = "ในการดำเนินการฝึกประสบการณ์วิชาชีพสารสนเทศตามหลักสูตรศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่ มีขั้นตอนดังต่อไปนี้";
$section->addText("\t" . $chap3Intro, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t1. การดำเนินการก่อนออกฝึกประสบการณ์วิชาชีพสารสนเทศ", 2);
$section->addText("\t1) หาสถานประกอบการฝึกประสบการณ์วิชาชีพสารสนเทศ", $styleBody, 'thai_body');
$section->addText("\t2) สาขาวิชาทำหนังสือขอความอนุเคราะห์ถึงสถานประกอบการ", $styleBody, 'thai_body');
$section->addText("\t3) สถานประกอบการตอบรับ", $styleBody, 'thai_body');
$section->addText("\t4) เข้ารับการปฐมนิเทศการฝึกประสบการณ์วิชาชีพสารสนเทศ", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t2. การดำเนินการระหว่างฝึกประสบการณ์วิชาชีพสารสนเทศ", 2);
$section->addText("\t2.1 ข้อควรปฏิบัติในการฝึกประสบการณ์วิชาชีพสารสนเทศ", $styleBody, 'thai_body');
$section->addText("1) ต้องปฏิบัติตนให้ถูกต้องตามระเบียบของหน่วยงาน", $styleBody, 'thai_body_indent_1');
$section->addText("2) ลงเวลาปฏิบัติงานทุกวันทั้งไปและกลับ", $styleBody, 'thai_body_indent_1');
$section->addText("3) เขียนใบลาแจ้งเหตุผล เมื่อมีความจำเป็นไม่สามารถมาปฏิบัติงานได้ตามปกติ", $styleBody, 'thai_body_indent_1');
$section->addText("4) ขออนุญาตควบคุมการฝึกประสบการณ์วิชาชีพสารสนเทศ เมื่อมีความจำเป็นที่จะต้องออกไปนอกสถานที่", $styleBody, 'thai_body_indent_1');
$section->addText("5) ต้องบันทึกการปฏิบัติงานเป็นประจำทุกวันแล้วจัดทำรายงานเพื่อให้ผู้ควบคุมการฝึกประสบการณ์วิชาชีพสารสนเทศตรวจความถูกต้อง", $styleBody, 'thai_body_indent_1');
$section->addText("6) แต่งกายให้สุภาพเรียบร้อย ตามระเบียบของหน่วยงานนั้นๆ", $styleBody, 'thai_body_indent_1');

$section->addTextBreak(1);
$section->addText("\t2.2 การแบ่งความรับผิดชอบของนักศึกษาในการจัดทำรายงาน", $styleCenterBold);
$chap3_2_2 = "ในการจัดทำรายงานการฝึกประสบการณ์วิชาชีพสารสนเทศ ข้าพเจ้าได้แบ่งหน้าที่ความรับผิดชอบในการจัดทำรายงานการฝึกประสบการณ์วิชาชีพสารสนเทศ ดังนี้";
$section->addText("\t" . $chap3_2_2, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t3. การดำเนินการเมื่อสิ้นสุดการฝึกประสบการณ์วิชาชีพสารสนเทศ", 2);
$section->addText("\t3.1 หลังเสร็จสิ้นการฝึกประสบการณ์วิชาชีพสารสนเทศ ให้นำสมุดรายงานการฝึกประสบการณ์วิชาชีพสารสนเทศในสถานประกอบการ และหนังสือรับรองการฝึกประสบการณ์วิชาชีพสารสนเทศมอบอาจารย์ประจำกระบวนวิชา", $styleBody, 'thai_body');
$section->addText("\t3.2 เตรียมตัวสำหรับการนำเสนอการฝึกประสบการณ์วิชาชีพสารสนเทศ", $styleBody, 'thai_body');

$section->addTextBreak(2);
$section->addText("(สามารถใส่รายละเอียดอื่นๆ เพิ่มเติมได้ตามความเหมาะสม)", ['color' => 'FF0000', 'bold' => true], $paragraphCenter);

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 4
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 4', 1);
$section->addTitle('ผลของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addTextBreak(1);

$authorName = ($coverData['authors'] ?? '[ชื่อผู้จัดทำ]');
$intro4 = "ในการฝึกประสบการณ์วิชาชีพสารสนเทศของข้าพเจ้า $authorName ได้ฝึกปฏิบัติงานในสถานประกอบการ $institutionVal ซึ่งได้ปฏิบัติงานตั้งแต่ วันที่ ......................... ถึงวันที่ ........................... ดังรายการปฏิบัติงานดังนี้";
$section->addText("\t" . $intro4, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t1. งานพัฒนาทรัพยากรสารสนเทศ", 2);
$section->addText("\t1.1 ระยะเวลาที่ฝึก ตั้งแต่วันที่ …………………..ถึง……………………………………", $styleBody, 'thai_body');
$section->addText("\t1.2 ชื่อผู้ควบคุมการฝึก", $styleBody, 'thai_body');
$section->addText("1.2.1…………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1');
$section->addText("1.2.2……………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');

$section->addText("\t1.3 ปริมาณงานที่ฝึก (ให้บอกชื่องานและจำนวนงานที่ฝึก) เช่น", $styleBody, 'thai_body');
$section->addText("1.3.1 ร่างหนังสือตอบขอบคุณ จำนวน 2 ฉบับ", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.2 ประทับตราหนังสือ จำนวน 20 เล่ม", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.3 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.4 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.5 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');

$section->addText("\t1.4 เครื่องมือ/อุปกรณ์/คู่มือที่ใช้ฝึก", $styleBody, 'thai_body');
$section->addText("1.4.1 เครื่องมือ หรือวัสดุอุปกรณ์", $styleBody, 'thai_body_indent_1');
$section->addText("1)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("2)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("3)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("4)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("1.4.2 คู่มือ", $styleBody, 'thai_body_indent_1');
$section->addText("1)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("2)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("3)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("4)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');

$section->addText("\t1.5 ลักษณะงานที่ฝึก (ใส่รายละเอียดลักษณะงานพร้อมขั้นตอนการดำเนินงานโดยละเอียดพร้อมตัวอย่าง) เช่น", $styleBody, 'thai_body');
$section->addText("1.5.1 งานพัฒนาทรัพยากรสารสนเทศ …………………………………………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.5.1.1 การจัดซื้อ ขั้นตอนการจัดซื้อ มี 3 ขั้นตอน ดังนี้", $styleBody, 'thai_body_indent_1_5');
$section->addText("1) การเตรียมการก่อนการจัดซื้อ (ให้บอกขั้นตอนโดยละเอียดพร้อมตัวอย่างประกอบ)", $styleBody, 'thai_body_indent_1_5');
$section->addText("2) การดำเนินการจัดซื้อสิ่งพิมพ์ (ให้บอกขั้นตอนโดยละเอียดพร้อมตัวอย่างประกอบ)", $styleBody, 'thai_body_indent_1_5');
$section->addText("3) การดำเนินงานหลังจากได้ตัวเล่มและใบส่งของ (ให้บอกขั้นตอนโดยละเอียดพร้อมตัวอย่างประกอบ)", $styleBody, 'thai_body_indent_1_5');
$section->addText("1.5.1.2 การขอรับบริจาค …………………………………………………..", $styleBody, 'thai_body_indent_1');
$section->addText("1.5.1.3 การแลกเปลี่ยนสิ่งพิมพ์ …………………………………………………..", $styleBody, 'thai_body_indent_1');

$section->addText("\t1.6 ความรู้ใหม่ที่ได้เรียนรู้ .............................................................................................................................................................................................................", $styleBody, 'thai_body');
$section->addText("\t1.7 ปัญหาอุปสรรค .............................................................................................................................................................................................................", $styleBody, 'thai_body');
$section->addText("\t1.8 ข้อเสนอแนะ/การแก้ไขปัญหา .............................................................................................................................................................................................................", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t2. งานบริการ", 2);
$section->addText("\t2.1 ระยะเวลาที่ฝึก ตั้งแต่วันที่ …………………..ถึง……………………………………", $styleBody, 'thai_body');
$section->addText("\t2.2 ชื่อผู้ควบคุมการฝึก", $styleBody, 'thai_body');
$section->addText("1.2.1…………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1');
$section->addText("1.2.2……………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');

$section->addText("\t2.3 ปริมาณงานที่ฝึก (ให้บอกชื่องานและจำนวนงานที่ฝึก) เช่น", $styleBody, 'thai_body');
$section->addText("1.3.1 ให้บริการยืมคืนที่เคาน์เตอร์ จำนวน 3 ชั่วโมง", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.2 จัดชั้นหนังสือและอ่านชั้นหนังสือ", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.3 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.4 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.3.5 …………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');

$section->addText("\t2.4 เครื่องมือ/อุปกรณ์/คู่มือที่ใช้ฝึก", $styleBody, 'thai_body');
$section->addText("1.4.1 เครื่องมือ หรือวัสดุอุปกรณ์", $styleBody, 'thai_body_indent_1');
$section->addText("1)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("2)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("3)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("4)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("1.4.2 คู่มือ", $styleBody, 'thai_body_indent_1');
$section->addText("1)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("2)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("3)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');
$section->addText("4)……………………………………………………………………………………………..", $styleBody, 'thai_body_indent_1_5');

$section->addText("\t2.5 ลักษณะงานที่ฝึก (ใส่รายละเอียดลักษณะงานพร้อมขั้นตอนการดำเนินงานโดยละเอียดพร้อมตัวอย่าง) เช่น", $styleBody, 'thai_body');
$section->addText("1.5.1 งานบริการยืมคืน …………………………………………………………………………………………………………………………………", $styleBody, 'thai_body_indent_1');
$section->addText("1.5.1.1 บริการยืม (ให้บอกขั้นตอนโดยละเอียดพร้อมตัวอย่างประกอบ)", $styleBody, 'thai_body_indent_1_5');
$section->addText("1.5.1.2 บริการคืน", $styleBody, 'thai_body_indent_1_5');

$section->addText("\t2.6 ความรู้ใหม่ที่ได้เรียนรู้ .............................................................................................................................................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.7 ปัญหาอุปสรรค .............................................................................................................................................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.8 ข้อเสนอแนะ/การแก้ไขปัญหา .............................................................................................................................................................................................................", $styleBody, 'thai_body');

$section->addTextBreak(2);
$section->addText("( ให้ทำรูปแบบนี้ไปจนครบงานที่ได้รับมอบหมาย ตลอดระยะเวลาของการฝึกประสบการณ์วิชาชีพสารสนเทศ)", ['color' => 'FF0000', 'bold' => true], $paragraphCenter);

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 5
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 5', 1);
$section->addTitle('สรุป อภิปรายผล ข้อเสนอแนะ', 1);
$section->addTextBreak(1);

$intro5 = "จากการฝึกประสบการณ์วิชาชีพสารสนเทศตลอดหลักสูตรเป็นระยะเวลา จำนวน .... ชั่วโมง ตั้งแต่วันที่ ................ ถึงวันที่ .................. ซึ่งสามารถสรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ อภิปรายผล และมีข้อเสนอแนะดังต่อไปนี้";
$section->addText("\t" . $intro5, $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t1. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ", 2);
$section->addText("\t1) เพื่อฝึกให้นักศึกษามีความรับผิดชอบต่อหน้าที่ เคารพระเบียบวินัย และทำงานร่วมกับผู้อื่นได้อย่างมีประสิทธิภาพ", $styleBody, 'thai_body');
$section->addText("\t2) เพื่อให้นักศึกษาได้เพิ่มทักษะ สร้างเสริมประสบการณ์ และพัฒนาวิชาชีพตามสภาพความเป็นจริงในสถานประกอบการ รวมถึงสามารถประยุกต์ความรู้ที่ได้จากการเรียนภาคทฤษฎีมาใช้ในภาคปฏิบัติ", $styleBody, 'thai_body');
$section->addText("\t3) เพื่อให้นักศึกษาได้ทราบถึงปัญหาต่างๆ ที่เกิดขึ้นในขณะปฏิบัติงาน สามารถแก้ปัญหาได้อย่างมีเหตุผล และมีเจตคติที่ดีต่อการทำงานเป็นแนวทางในการประกอบอาชีพต่อไป", $styleBody, 'thai_body');
$section->addText("\t4) เพื่อเสริมสร้างสัมพันธ์ภาพที่ดีระหว่างมหาวิทยาลัยเชียงใหม่กับสถานประกอบการ และหน่วยงานภาครัฐ", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t2. สรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ", 2);
$intro5_2 = "ผลจากการฝึกประสบการณ์วิชาชีพสารสนเทศในสถานประกอบการ $institutionVal งานที่ข้าพเจ้าได้รับมอบหมายได้แก่งานทางด้านดังต่อไปนี้";
$section->addText("\t" . $intro5_2, $styleBody, 'thai_body');
$section->addText("\t2.1 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.2 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.3 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.4 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t2.5 ....................................................................................", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t3. อภิปรายผล", 2);
$section->addText("\t3.1 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t3.2 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t3.3 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t3.4 ....................................................................................", $styleBody, 'thai_body');
$section->addText("\t3.5 ....................................................................................", $styleBody, 'thai_body');

$section->addTextBreak(1);
$section->addTitle("\t4. ข้อเสนอแนะ", 2);
$section->addText("\t4.1 ข้อเสนอแนะในการนำวิชาความรู้จากการไปประกอบอาชีพ", $styleBody, 'thai_body');
$section->addText("\t..................................................................................................", $styleBody, 'thai_body');
$section->addTextBreak(1);
$section->addText("\t4.2 ข้อเสนอแนะในการฝึกประสบการณ์วิชาชีพสารสนเทศครั้งต่อไป", $styleBody, 'thai_body');
$section->addText("\t..................................................................................................", $styleBody, 'thai_body');

$section->addPageBreak();

// --------------------------------------------------------------------------
// BIBLIOGRAPHY
// --------------------------------------------------------------------------
$section->addTitle('บรรณานุกรม', 1);

$bibEntries = [];
if ($projectId > 0 && $userId) {
    try {
        $db = getDB();
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
                $results = applyDisambiguationFallback($results);
                foreach ($results as $row) {
                    $entry = trim((string)($row['bibliography_text'] ?? ''));
                    if ($entry !== '') {
                        // We NO LONGER strip_tags here because we want to keep <i> for our parser
                        $bibEntries[] = $entry;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('Internship export bib error: ' . $e->getMessage());
    }
}

if (empty($bibEntries)) {
    $section->addText('[ยังไม่มีรายการบรรณานุกรม - กรุณาเลือกโครงการในหน้าสร้างรายงาน]', $styleBody);
} else {
    foreach ($bibEntries as $bibHtml) {
        $textRun = $section->addTextRun('bib_entry');
        
        // Basic parser for <i> and <em> tags to support italics
        // We split by <i> or </i> tags
        $parts = preg_split('#(</?i>|</?em>)#i', $bibHtml, -1, PREG_SPLIT_DELIM_CAPTURE);
        $isItalic = false;
        
        foreach ($parts as $part) {
            $lowerPart = strtolower($part);
            if ($lowerPart === '<i>' || $lowerPart === '<em>') {
                $isItalic = true;
                continue;
            }
            if ($lowerPart === '</i>' || $lowerPart === '</em>') {
                $isItalic = false;
                continue;
            }
            
            if ($part !== '') {
                // Decode entities and add to text run
                $cleanText = html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $textRun->addText($cleanText, array_merge($styleBody, ['italic' => $isItalic]));
            }
        }
    }
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// APPENDICES
// --------------------------------------------------------------------------
$section->addTextBreak(15);
$section->addTitle('ภาคผนวก', 1);
$section->addPageBreak();

$section->addTextBreak(15);
$section->addTitle('ภาคผนวก ก', 1);
$section->addTitle('ภาพจากการปฏิบัติงาน', 1);
$section->addPageBreak();

$section->addTextBreak(15);
$section->addTitle('ภาคผนวก ข', 1);
$section->addTitle('ผลงานหรือชิ้นงานจากการปฏิบัติงาน (ถ้ามี)', 1);

$section->addPageBreak();

// --------------------------------------------------------------------------
// BIOGRAPHY
// --------------------------------------------------------------------------
$section->addTitle('ประวัติผู้ฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addTextBreak(1);

// Style for bio lines with specific tab stop for values
$phpWord->addParagraphStyle('bio_line', [
    'tabs' => [new \PhpOffice\PhpWord\Style\Tab('left', 2880)] // ~2 inches tab stop
]);

$section->addText("ชื่อ-สกุล\t" . $authorName, $styleBody, 'bio_line');
$section->addText("วันเดือนปีเกิด\t....................................................", $styleBody, 'bio_line');
$section->addText("ภูมิลำเนา\tจังหวัด ....................................", $styleBody, 'bio_line');

$section->addTextBreak(1);
$section->addText('ประวัติการศึกษา', $styleCenterBold, ['align' => 'left']);
$section->addText("ปีการศึกษา 2556\tสำเร็จการศึกษาระดับมัธยมศึกษา โรงเรียน....................", $styleBody, 'bio_line');
$section->addText("\tจังหวัด ....................................", $styleBody, 'bio_line');
$section->addText("...................\t........................................................................", $styleBody, 'bio_line');

// --------------------------------------------------------------------------
// OUTPUT
// --------------------------------------------------------------------------
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

// Use a reliable temporary path within the project
$tempDir = __DIR__ . '/../../tmp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}
$tempFile = $tempDir . '/internship_' . uniqid() . '.docx';

try {
    $writer->save($tempFile);
} catch (Exception $e) {
    error_log('PHPWord Save Error: ' . $e->getMessage());
    http_response_code(500);
    die('Error saving Word file: ' . $e->getMessage());
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="internship-report.docx"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
@unlink($tempFile);
if (isset($logoTemp) && file_exists($logoTemp)) @unlink($logoTemp);
exit;

/**
 * Resolve logo image (helper)
 */
function resolveLogoForWord($coverData)
{
    $logoDataUrl = trim((string)($coverData['logoDataUrl'] ?? ''));
    if ($logoDataUrl !== '' && preg_match('#^data:(image/(png|jpeg|jpg|webp));base64,#i', $logoDataUrl, $matches)) {
        $binary = base64_decode(substr($logoDataUrl, strpos($logoDataUrl, ',') + 1), true);
        if ($binary !== false) {
            $subtype = strtolower($matches[2]);
            return ['bytes' => $binary, 'ext' => $subtype];
        }
    }

    $defaultLogoPath = __DIR__ . '/../../assets/images/Chiang_Mai_University.svg.png';
    if (is_file($defaultLogoPath)) {
        return ['bytes' => file_get_contents($defaultLogoPath), 'ext' => 'png'];
    }
    return null;
}

/**
 * Apply APA Disambiguation fallback (e.g. 2564ก, 2564ข)
 */
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
        if (count($indices) <= 1) continue;
        foreach ($indices as $position => $index) {
            $bib = &$bibliographies[$index];
            $text = (string)($bib['bibliography_text'] ?? '');
            $year = (string)($bib['year'] ?? '');
            $lang = (string)($bib['language'] ?? '');

            $suffix = ($lang === 'th') ? ($thaiSuffixes[$position] ?? '') : chr(ord('a') + $position);
            if ($suffix === '' || $year === '' || $year === '0') continue;

            $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
            $text = str_replace('(' . $year . ')', '(' . $year . $suffix . ')', $text);
            $bib['bibliography_text'] = $text;
        }
    }

    return $bibliographies;
}
