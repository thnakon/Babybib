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

$styleBody = ['name' => $fontName, 'size' => 16];
$styleCenterBold = ['name' => $fontName, 'size' => 16, 'bold' => true];
$styleAuthorDate = ['name' => $fontName, 'size' => 16, 'italic' => false];

// Define common paragraph styles
$paragraphCenter = ['align' => 'center'];
$paragraphJustify = ['align' => 'both', 'indentation' => ['firstLine' => 720]]; // 0.5 inch indent

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
$section->addTextBreak(3);
$section->addText($coverData['title'] ?? 'รายงานการฝึกประสบการณ์วิชาชีพสารสนเทศ', ['bold' => true, 'size' => 22], $paragraphCenter);
$section->addTextBreak(2);

$companyVal = !empty($coverData['company']) ? $coverData['company'] : '';
$institutionVal = !empty($companyVal) ? $companyVal : (!empty($coverData['institution']) ? $coverData['institution'] : '');
if (empty($institutionVal)) $institutionVal = '[ชื่อสถานประกอบการ]';
$section->addText($institutionVal, ['bold' => true, 'size' => 18], $paragraphCenter);

$section->addTextBreak(4);
$section->addText('โดย', $styleBody, $paragraphCenter);
$section->addText($coverData['authors'] ?? '[ชื่อผู้จัดทำ]', $styleCenterBold, $paragraphCenter);
$section->addText('รหัสนักศึกษา ' . ($coverData['studentIds'] ?? '[รหัส]'), $styleBody, $paragraphCenter);

$section->addTextBreak(3);
$section->addText('รายงานนี้เป็นส่วนหนึ่งของการฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleBody, $paragraphCenter);
$section->addText('ตามหลักสูตรศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา', $styleBody, $paragraphCenter);
$section->addText('ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ คณะมนุษยศาสตร์', $styleBody, $paragraphCenter);
$section->addText('มหาวิทยาลัยเชียงใหม่', $styleBody, $paragraphCenter);

$semText = ($coverData['semester'] ?? '') === '1' ? '1' : (($coverData['semester'] ?? '') === '2' ? '2' : 'ฤดูร้อน');
$yearText = $coverData['year'] ?? '[ปีการศึกษา]';
$section->addText('ภาคการศึกษาที่ ' . $semText . ' ปีการศึกษา ' . $yearText, $styleBody, $paragraphCenter);

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
        $section->addText(trim($line), $styleBody, $paragraphJustify);
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
    ['text' => 'ประกาศคุณูปการ', 'page' => 'ก'],
    ['text' => 'สารบัญ', 'page' => 'ง'],
    ['text' => 'บทที่ 1 บทนำ', 'page' => '1'],
    ['text' => '    1. ความเป็นมาและความสำคัญของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '1'],
    ['text' => '    2. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '1'],
    ['text' => '    3. ประโยชน์ที่คาดว่าจะได้รับจากการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '2'],
    ['text' => '    4. ระยะเวลาการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '3'],
    ['text' => 'บทที่ 2 เอกสารและการบูรณาการวิชาการที่เกี่ยวข้อง', 'page' => '4'],
    ['text' => '    1. ข้อมูลพื้นฐานของหน่วยงาน', 'page' => '4'],
    ['text' => '    1.1 ประวัติ', 'page' => '4'],
    ['text' => '    1.2 โครงสร้างการบริหาร/แผนผังองค์กร', 'page' => '4'],
    ['text' => '    1.3 ปณิธาน วิสัยทัศน์ พันธกิจ', 'page' => '5'],
    ['text' => '    1.4 แผนภูมิการบริหารงาน', 'page' => '6'],
    ['text' => '    1.5 บุคลากร', 'page' => '7'],
    ['text' => '    1.6 ที่ตั้ง / แผนที่การเดินทาง / การติดต่อ', 'page' => '8'],
    ['text' => '    1.7 เวลาเปิดบริการ', 'page' => '9'],
    ['text' => '    1.8 ขอบเขตงานของหน่วยงาน', 'page' => '9'],
    ['text' => '    2. การบูรณาการวิชาการ', 'page' => '11'],
    ['text' => 'บทที่ 3 ขั้นตอนการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '19'],
    ['text' => '    1. การดำเนินการก่อนออกฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '20'],
    ['text' => '    2. การดำเนินการระหว่างฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '21'],
    ['text' => '    3. การดำเนินการเมื่อสิ้นสุดการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '22'],
];

foreach ($tocItems as $item) {
    $textRun = $section->addTextRun(['align' => 'both']);
    $textRun->addText($item['text'], $styleBody);
    
    // Safety check for mb_strlen and length
    $labelLen = function_exists('mb_strlen') ? mb_strlen($item['text'], 'UTF-8') : strlen($item['text']);
    $dotCount = max(0, 80 - $labelLen);
    
    $textRun->addText(str_repeat('.', $dotCount), ['color' => 'FFFFFF']); // invisible dot spacer hack
    $textRun->addText($item['page'], $styleBody);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// PAGE 4: TABLE OF CONTENTS CONT (สารบัญต่อ)
// --------------------------------------------------------------------------
$section->addTitle('สารบัญ (ต่อ)', 1);
$section->addText('หน้า', ['bold' => true], ['align' => 'right']);

$tocContItems = [
    ['text' => 'บทที่ 4 ผลของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '42'],
    ['text' => '    1. งานพัฒนาทรัพยากรสารสนเทศ', 'page' => '42'],
    ['text' => '    2. งานบริการ', 'page' => '44'],
    ['text' => 'บทที่ 5 สรุป อภิปรายผล ข้อเสนอแนะ', 'page' => '52'],
    ['text' => '    1. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '52'],
    ['text' => '    2. สรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '53'],
    ['text' => '    3. อภิปรายผล', 'page' => '55'],
    ['text' => '    4. ข้อเสนอแนะ', 'page' => '56'],
    ['text' => 'บรรณานุกรม', 'page' => '58'],
    ['text' => 'ภาคผนวก', 'page' => '59'],
    ['text' => '    ภาคผนวก ก ภาพจากการปฏิบัติงาน', 'page' => '62'],
    ['text' => '    ภาคผนวก ข ผลงานหรือชิ้นงานจากการปฏิบัติงาน (ถ้ามี)', 'page' => '62'],
    ['text' => 'ประวัติผู้ฝึกประสบการณ์วิชาชีพสารสนเทศ', 'page' => '68'],
];

foreach ($tocContItems as $item) {
    $textRun = $section->addTextRun(['align' => 'both']);
    $textRun->addText($item['text'], $styleBody);
    
    $labelLen = function_exists('mb_strlen') ? mb_strlen($item['text'], 'UTF-8') : strlen($item['text']);
    $dotCount = max(0, 80 - $labelLen);
    
    $textRun->addText(str_repeat('.', $dotCount), ['color' => 'FFFFFF']);
    $textRun->addText($item['page'], $styleBody);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 1
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 1', 1);
$section->addTitle('บทนำ', 1);
$section->addTextBreak(1);

$section->addTitle('1. ความเป็นมาและความสำคัญของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText('การเรียนการสอนในศตวรรษที่ 21 นักศึกษาจำเป็นมีต้องมีทักษะและการเตรียมความพร้อมเข้าสู่ตลาดแรงงานและสังคม เพื่อผลิตบัณฑิตให้เป็นแรงงานที่มีความรู้ (Knowledge worker) ที่สำคัญของประเทศ ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ ได้เห็นความสำคัญของการมุ่งพัฒนาให้นักศึกษาเป็นผู้ที่มีความรู้ ความสามารถ และทักษะที่ครบถ้วนตามกรอบมาตรฐานคุณวุฒิระดับอุดมศึกษาแห่งชาติ (TQF) ซึ่งหลักสูตรศิลปศาสตรบัณฑิต สาขาวิชาสารสนเทศศึกษา เป็นสาขาวิชาที่มีการบูรณาการระหว่างความรู้เชิงทฤษฎี และทักษะด้านการปฏิบัติทางวิชาชีพด้านการจัดการสารสนเทศ และการจัดการเทคโนโลยีสารสนเทศในหน่วยงานภาครัฐและภาคเอกชน การฝึกประสบการณ์วิชาชีพสารสนเทศ เป็นกระบวนการเพิ่มทักษะและประสบการณ์ที่เป็นประโยชน์แก่การประกอบอาชีพของนักศึกษาเมื่อสำเร็จการศึกษา ช่วยให้นักศึกษามีความรู้ ความเข้าใจในการปฏิบัติงานจริง เพื่อให้เกิดทักษะและความสามารถในการทำงานที่ดี สอดคล้องกับความต้องการของตลาดแรงงานและสังคม ทั้งในสถานประกอบการและการประกอบอาชีพอิสระ นักศึกษามีโอกาสได้ทราบถึงขั้นตอนการปฏิบัติงาน และเทคนิคการทำงาน รวมถึงสร้างความเชื่อมั่นและทัศนคติที่ดีต่อวิชาชีพ ฝึกการทำงานร่วมกับผู้อื่น และที่สำคัญเป็นการเสริมสร้างสมรรถภาพในการทำงาน เพื่อการประกอบอาชีพในอนาคต', $styleBody, $paragraphJustify);

$section->addTextBreak(1);
$section->addTitle('2. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addListItem('เพื่อฝึกให้นักศึกษามีความรับผิดชอบต่อหน้าที่ เคารพระเบียบวินัย และทำงานร่วมกับผู้อื่นได้อย่างมีประสิทธิภาพ', 0, $styleBody);
$section->addListItem('เพื่อให้นักศึกษาได้เพิ่มทักษะ สร้างเสริมประสบการณ์ และพัฒนาวิชาชีพตามสภาพความเป็นจริงในสถานประกอบการ รวมถึงสามารถประยุกต์ความรู้ที่ได้จากการเรียนภาคทฤษฎีมาใช้ในภาคปฏิบัติ', 0, $styleBody);
$section->addListItem('เพื่อให้นักศึกษาได้ทราบถึงปัญหาต่างๆ ที่เกิดขึ้นในขณะปฏิบัติงาน สามารถแก้ปัญหาได้อย่างมีเหตุผล และมีเจตคติที่ดีต่อการทำงานเป็นแนวทางในการประกอบอาชีพต่อไป', 0, $styleBody);
$section->addListItem('เพื่อเสริมสร้างสัมพันธ์ภาพที่ดีระหว่างมหาวิทยาลัยเชียงใหม่กับสถานประกอบการ และหน่วยงานภาครัฐ', 0, $styleBody);

$section->addTextBreak(1);
$section->addTitle('3. ประโยชน์ที่คาดว่าจะได้รับจากการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addListItem('มีความรู้ ความเข้าใจ และสามารถบูรณาการหลักการ และทฤษฎีที่เกี่ยวข้องมาปรับใช้ในการฝึกประสบการณ์วิชาชีพสารสนเทศศึกษา', 0, $styleBody);
$section->addListItem('สามารถวิเคราะห์ปัญหา ประยุกต์ความรู้ ทักษะ และเครื่องมือที่เหมาะสมกับการแก้ไขปัญหา', 0, $styleBody);
$section->addListItem('มีความซื่อสัตย์สุจริต เสียสละ และมีจรรยาบรรณทางวิชาการและวิชาชีพ', 0, $styleBody);
$section->addListItem('สร้างความมีวินัยตรงต่อเวลา ความรับผิดชอบต่อตนเองและสังคม เคารพกฎระเบียบและข้อบังคับต่างๆของสถานประกอบการที่ฝึกประสบการณ์วิชาชีพสารสนเทศศึกษา', 0, $styleBody);
$section->addListItem('มีภาวะผู้นำ และสามารถทำงานเป็นทีมได้', 0, $styleBody);

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
$section->addText($intro2, $styleBody, $paragraphJustify);

$section->addTextBreak(1);
$section->addTitle('1. ข้อมูลพื้นฐานของหน่วยงาน', 2);
$section->addText('1.1 ประวัติ', $styleBody);
$section->addText('1.2 โครงสร้างการบริหาร / แผนผังองค์กร', $styleBody);
$section->addText('1.3 ปณิธาน วิสัยทัศน์ พันธกิจ', $styleBody);
$section->addText('1.4 แผนภูมิการบริหารงาน', $styleBody);
$section->addText('1.5 บุคลากร', $styleBody);
$section->addText('1.6 ที่ตั้ง / แผนที่การเดินทาง/ การติดต่อ', $styleBody);
$section->addText('1.7 เวลาเปิดบริการ', $styleBody);
$section->addText('1.8 ขอบเขตงานของหน่วยงาน', $styleBody);

$section->addTextBreak(1);
$section->addTitle('2. การบูรณาการวิชาการ', 2);
$section->addText('2.1 กระบวนวิชา....................................', $styleBody);
for($i=1; $i<=5; $i++) {
    $section->addText("    $i) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody);
}
$section->addText('2.2 กระบวนวิชา....................................', $styleBody);
for($i=1; $i<=5; $i++) {
    $section->addText("    $i) (เนื้อหาวิชา......................) (ใช้ในการทำงานดังนี้ .....................................)", $styleBody);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 3
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 3', 1);
$section->addTitle('ขั้นตอนการฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addTextBreak(1);

$section->addText('ในการดำเนินการฝึกประสบการณ์วิชาชีพสารสนเทศตามหลักสูตรศิลปศาสตรบัณฑิต (ศศ.บ.) สาขาวิชาสารสนเทศศึกษา ข้าพเจ้าได้ดำเนินการตามขั้นตอนต่อไปนี้', $styleBody, $paragraphJustify);
$section->addText('1. การดำเนินการก่อนออกฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleBody);
$section->addText('2. การดำเนินการระหว่างฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleBody);
$section->addText('3. การดำเนินการเมื่อสิ้นสุดฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleBody);

$section->addTextBreak(1);
$section->addTitle('1. การดำเนินการก่อนออกฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addListItem('หาสถานประกอบการฝึกประสบการณ์วิชาชีพสารสนเทศ', 0, $styleBody);
$section->addListItem('สาขาวิชาทำหนังสือขอความอนุเคราะห์ถึงสถานประกอบการ', 0, $styleBody);
$section->addListItem('สถานประกอบการตอบรับ', 0, $styleBody);
$section->addListItem('เข้ารับการปฐมนิเทศการฝึกประสบการณ์วิชาชีพสารสนเทศ', 0, $styleBody);

$section->addTextBreak(1);
$section->addTitle('2. การดำเนินการระหว่างฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText('2.1 ข้อควรปฏิบัติในการฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleCenterBold);
$section->addListItem('ต้องปฏิบัติตนให้ถูกต้องตามระเบียบของหน่วยงาน', 0, $styleBody);
$section->addListItem('ลงเวลาปฏิบัติงานทุกวันทั้งไปและกลับ', 0, $styleBody);
$section->addListItem('เขียนใบลาแจ้งเหตุผล เมื่อมีความจำเป็นไม่สามารถมาปฏิบัติงานได้ตามปกติ', 0, $styleBody);
$section->addListItem('ขออนุญาตควบคุมการฝึกประสบการณ์วิชาชีพสารสนเทศ เมื่อมีความจำเป็นที่จะต้องออกไปนอกสถานที่', 0, $styleBody);
$section->addListItem('ต้องบันทึกการปฏิบัติงานเป็นประจำทุกวันแล้วจัดทำรายงานเพื่อให้ผู้ควบคุมการฝึกประสบการณ์วิชาชีพสารสนเทศตรวจความถูกต้อง', 0, $styleBody);
$section->addListItem('แต่งกายให้สุภาพเรียบร้อย ตามระเบียบของหน่วยงานนั้นๆ', 0, $styleBody);

$section->addTextBreak(1);
$section->addText('2.2 การแบ่งความรับผิดชอบของนักศึกษาในการจัดทำรายงาน', $styleCenterBold);

$section->addTextBreak(1);
$section->addTitle('3. การดำเนินการเมื่อสิ้นสุดการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText('3.1 หลังเสร็จสิ้นการฝึกประสบการณ์วิชาชีพสารสนเทศ ให้นำสมุดรายงานการฝึกประสบการณ์วิชาชีพสารสนเทศในสถานประกอบการ และหนังสือรับรองการฝึกประสบการณ์วิชาชีพสารสนเทศมอบอาจารย์ประจำกระบวนวิชา', $styleBody);
$section->addText('3.2 เตรียมตัวสำหรับการนำเสนอการฝึกประสบการณ์วิชาชีพสารสนเทศ', $styleBody);

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 4
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 4', 1);
$section->addTitle('ผลของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addTextBreak(1);

$authorName = ($coverData['authors'] ?? '[ชื่อผู้จัดทำ]');
$section->addText("ในการฝึกประสบการณ์วิชาชีพสารสนเทศของข้าพเจ้า $authorName ได้ฝึกปฏิบัติงานในสถานประกอบการ $institutionVal ซึ่งได้ปฏิบัติงานตั้งแต่ วันที่ ......................... ถึงวันที่ ........................... ดังรายการปฏิบัติงานดังนี้", $styleBody, $paragraphJustify);

$section->addTextBreak(1);
$section->addTitle('1. งานพัฒนาทรัพยากรสารสนเทศ', 2);
$section->addText('1.1 ระยะเวลาที่ฝึก ตั้งแต่วันที่ …………………..ถึง……………………………………', $styleBody);
$section->addText('1.2 ชื่อผู้ควบคุมการฝึก', $styleBody);
$section->addText('    1.2.1…………………………………………………………………………………………..', $styleBody);
$section->addText('    1.2.2……………………………………………………………………………………………', $styleBody);
$section->addText('1.3 ปริมาณงานที่ฝึก (ให้บอกชื่องานและจำนวนงานที่ฝึก) เช่น', $styleBody);
$section->addText('    1.3.1 ร่างหนังสือตอบขอบคุณ จำนวน 2 ฉบับ', $styleBody);
$section->addText('    1.3.2 ประทับตราหนังสือ จำนวน 20 เล่ม', $styleBody);
$section->addText('1.4 เครื่องมือ/อุปกรณ์/คู่มือที่ใช้ฝึก', $styleBody);
$section->addText('    1.4.1 เครื่องมือ หรือวัสดุอุปกรณ์', $styleBody);
$section->addText('    1.4.2 คู่มือ', $styleBody);

$section->addTextBreak(1);
$section->addTitle('2. งานบริการ', 2);
$section->addText('2.1 ระยะเวลาที่ฝึก ตั้งแต่วันที่ …………………..ถึง……………………………………', $styleBody);

$section->addPageBreak();

// --------------------------------------------------------------------------
// CHAPTER 5
// --------------------------------------------------------------------------
$section->addTitle('บทที่ 5', 1);
$section->addTitle('สรุป อภิปรายผล ข้อเสนอแนะ', 1);
$section->addTextBreak(1);

$section->addText("จากการฝึกประสบการณ์วิชาชีพสารสนเทศตลอดหลักสูตรเป็นระยะเวลา จำนวน .... ชั่วโมง ตั้งแต่วันที่ ................ ถึงวันที่ .................. ซึ่งสามารถสรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ อภิปรายผล และมีข้อเสนอแนะดังต่อไปนี้", $styleBody, $paragraphJustify);

$section->addTextBreak(1);
$section->addTitle('1. วัตถุประสงค์ของการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addListItem('เพื่อฝึกให้นักศึกษามีความรับผิดชอบต่อหน้าที่ เคารพระเบียบวินัย และทำงานร่วมกับผู้อื่นได้อย่างมีประสิทธิภาพ', 0, $styleBody);
$section->addListItem('เพื่อให้นักศึกษาได้เพิ่มทักษะ สร้างเสริมประสบการณ์ และพัฒนาวิชาชีพตามสภาพความเป็นจริงในสถานประกอบการ รวมถึงสามารถประยุกต์ความรู้ที่ได้จากการเรียนภาคทฤษฎีมาใช้ในภาคปฏิบัติ', 0, $styleBody);

$section->addTextBreak(1);
$section->addTitle('2. สรุปผลการฝึกประสบการณ์วิชาชีพสารสนเทศ', 2);
$section->addText("ผลจากการฝึกประสบการณ์วิชาชีพสารสนเทศในสถานประกอบการ $institutionVal งานที่ข้าพเจ้าได้รับมอบหมายได้แก่งานทางด้านดังต่อไปนี้", $styleBody);

$section->addPageBreak();

// --------------------------------------------------------------------------
// BIBLIOGRAPHY
// --------------------------------------------------------------------------
$section->addTitle('บรรณานุกรม', 1);
$bibSamples = [
    'ชุติมา วิชาการ. (2564ก). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. วารสารห้องสมุด, 12(3), 31-45.',
    'ชุติมา วิชาการ. (2564ข). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. วารสารห้องสมุด, 22(1), 481-495.',
    'ประยุกต์ วิชาการ. (2560). พฤติกรรมการแสวงหาสารสนเทศ. สำนักพิมพ์แห่งจุฬาฯ.',
    'Robinson, K. (2021a). Digital transformation in academic libraries. Journal of Documentation, 42(3), 120-135.',
];
foreach ($bibSamples as $bib) {
    $section->addText($bib, $styleBody);
}

$section->addPageBreak();

// --------------------------------------------------------------------------
// APPENDICES
// --------------------------------------------------------------------------
$section->addTitle('ภาคผนวก', 1);
$section->addPageBreak();
$section->addTitle('ภาคผนวก ก', 1);
$section->addTitle('ภาพจากการปฏิบัติงาน', 1);
$section->addPageBreak();
$section->addTitle('ภาคผนวก ข', 1);
$section->addTitle('ผลงานหรือชิ้นงานจากการปฏิบัติงาน (ถ้ามี)', 1);

$section->addPageBreak();

// --------------------------------------------------------------------------
// BIOGRAPHY
// --------------------------------------------------------------------------
$section->addTitle('ประวัติผู้ฝึกประสบการณ์วิชาชีพสารสนเทศ', 1);
$section->addText('ชื่อ-สกุล', $styleCenterBold);
$section->addText($authorName, $styleBody);
$section->addText('วันเดือนปีเกิด', $styleCenterBold);
$section->addText('....................................................', $styleBody);
$section->addText('ภูมิลำเนา', $styleCenterBold);
$section->addText('....................................................', $styleBody);
$section->addText('ประวัติการศึกษา', $styleCenterBold);
$section->addText('..........................................................................................................................................................................................', $styleBody);

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
exit;
