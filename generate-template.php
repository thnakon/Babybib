<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Angsana New');
$phpWord->setDefaultFontSize(16);

// Set document language to Thai to prevent red spell-check underlines
$phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'));

// Define styles (1 inch = 1440 twips)
$sectionStyle = [
    'marginTop' => 1.5 * 1440,
    'marginLeft' => 1.5 * 1440,
    'marginRight' => 1 * 1440,
    'marginBottom' => 1 * 1440,
];

// Document styles
$phpWord->addParagraphStyle('Normal', [
    'spacing' => 120, // 6pt after
    'lineHeight' => 1,
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::DISTRIBUTE,
    'indentation' => ['firstLine' => 709] // 1.25 cm
]);

$phpWord->addParagraphStyle('Heading1', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 120,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('CoverPara', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 120,
    'lineHeight' => 1,
]);

// Complex Script fonts for Thai — lang => Thai to disable English spell check
$phpWord->addFontStyle('Heading1Font', [
    'name' => 'Angsana New',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

$phpWord->addFontStyle('CoverTitleFont', [
    'name' => 'Angsana New',
    'size' => 24,
    'bold' => true,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

$phpWord->addFontStyle('CoverFont', [
    'name' => 'Angsana New',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

$phpWord->addFontStyle('NormalFont', [
    'name' => 'Angsana New',
    'size' => 16,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

// Section
$section = $phpWord->addSection($sectionStyle);

// ==== COVER & INNER COVER PAGE ====
for ($i = 0; $i < 2; $i++) {
    $section->addText('${report_title}', 'CoverTitleFont', 'CoverPara');

    // Add more breaks to push the text roughly to the center of the page
    $section->addTextBreak(3, ['name' => 'Angsana New', 'size' => 18]);

    $section->addText('${report_author}', 'CoverFont', 'CoverPara');
    $section->addText('${report_student_ids}', 'CoverFont', 'CoverPara');

    // Push the bottom section to the bottom
    $section->addTextBreak(3, ['name' => 'Angsana New', 'size' => 18]);

    $section->addText('${report_course}', 'CoverFont', 'CoverPara');
    $section->addText('${report_department}', 'CoverFont', 'CoverPara');
    $section->addText('${report_institution}', 'CoverFont', 'CoverPara');
    $section->addText('ภาคการศึกษาที่ ${report_semester} ปีการศึกษา ${report_year}', 'CoverFont', 'CoverPara');

    $section->addPageBreak();
}

// ==== CONTENT ====
$section->addText('หน้า 2 (โครงสร้างที่จะโคลน)', 'CoverFont', 'CoverPara');

// TemplateProcessor block cloning requires exactly ${block_name} on its own line
$section->addText('${chapters}');
$section->addText('บทที่ ${chapter_number}', 'Heading1Font', 'Heading1');
$section->addText('${chapter_title}', 'Heading1Font', 'Heading1');

$section->addText('${subsections}');
$section->addText('${subsection_number} ${subsection_title}', ['name' => 'Angsana New', 'size' => 16, 'bold' => true, 'hint' => 'cs', 'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH')], ['spacing' => 120, 'lineHeight' => 1]);
$section->addText('${subsection_placeholder1}', 'NormalFont', 'Normal');
$section->addText('${subsection_placeholder2}', 'NormalFont', 'Normal');
$section->addText('${/subsections}');
$section->addTextBreak(1, ['size' => 16]);
$section->addText('${/chapters}');

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save(__DIR__ . '/assets/templates/template_academic_general.docx');

echo "Template created successfully.\n";
