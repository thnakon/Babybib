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

$phpWord->addParagraphStyle('PrefaceParaTitle', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 120,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('PrefaceParaContent', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::THAI_DISTRIBUTE, // Thai Distributed (thaiDistribute)
    'spacing' => 120,
    'lineHeight' => 1,
    'indentation' => ['firstLine' => 720] // 1 inch (Tab ด้านหน้าแต่ละย่อหน้า)
]);

$phpWord->addParagraphStyle('PrefaceParaSign', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
    'spacing' => 120,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('TOCParaTitle', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 120,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('TOCParaLabel', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
    'spacing' => 0,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('TOCParaItem', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 0,
    'lineHeight' => 1,
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('right', 8200) // ลบจุดไข่ปลาออก ให้เป็นที่ว่างเปล่าๆ
    ]
]);

$phpWord->addParagraphStyle('TOCParaSubItem', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 0,
    'lineHeight' => 1,
    'indentation' => ['left' => 360],
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('right', 8200) // ลบจุดไข่ปลาออก
    ]
]);

// Complex Script fonts for Thai — lang => Thai to disable English spell check
$phpWord->addFontStyle('Heading1Font', [
    'name' => 'Angsana New',
    'size' => 20,
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

$phpWord->addFontStyle('PrefaceTitleFont', [
    'name' => 'Angsana New',
    'size' => 20,
    'bold' => true,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

$phpWord->addFontStyle('PrefaceContentFont', [
    'name' => 'Angsana New',
    'size' => 16,
    'bold' => false,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

$phpWord->addFontStyle('TOCLabelFont', [
    'name' => 'Angsana New',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
    'lang' => new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'),
]);

// Section
$section = $phpWord->addSection($sectionStyle);

// ==== COVER & INNER COVER PAGE (each as its own section) ====
for ($i = 0; $i < 2; $i++) {
    // Each cover is a separate section so content never overflows to create blank pages
    if ($i > 0) {
        $section = $phpWord->addSection($sectionStyle);
    }
    $section->addText('${report_title}', 'CoverTitleFont', 'CoverPara');
    $section->addTextBreak(6, ['name' => 'Angsana New', 'size' => 18]);
    $section->addText('${report_author}', 'CoverFont', 'CoverPara');
    $section->addText('${report_student_ids}', 'CoverFont', 'CoverPara');
    $section->addTextBreak(6, ['name' => 'Angsana New', 'size' => 18]);
    $section->addText('${report_course}', 'CoverFont', 'CoverPara');
    $section->addText('${report_department}', 'CoverFont', 'CoverPara');
    $section->addText('${report_institution}', 'CoverFont', 'CoverPara');
    $section->addText('ภาคการศึกษาที่ ${report_semester} ปีการศึกษา ${report_year}', 'CoverFont', 'CoverPara');
}

// Start a new section for preface onwards
$section = $phpWord->addSection($sectionStyle);

// ==== PREFACE PAGE ====
$section->addText('คำนำ', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1, ['size' => 16]); // เว้น 1 บรรทัด
$section->addText('${preface_paragraphs}');
$section->addText('${preface_content}', 'PrefaceContentFont', 'PrefaceParaContent');
$section->addText('${/preface_paragraphs}');
$section->addTextBreak(1, ['size' => 16]);
$section->addText('${preface_signer}', 'PrefaceContentFont', 'PrefaceParaSign');
$section->addText('${preface_date}', 'PrefaceContentFont', 'PrefaceParaSign');
$section->addPageBreak();

// ==== TABLE OF CONTENTS PAGE ====
$section->addText('สารบัญ', 'PrefaceTitleFont', 'TOCParaTitle');
$section->addText('หน้า', 'TOCLabelFont', 'TOCParaLabel');

$section->addText('คำนำ' . "\t" . '${toc_page_preface}', 'PrefaceContentFont', 'TOCParaItem');

$section->addText('${toc_chapters}');
$section->addText('บทที่ ${toc_chapter_number} ${toc_chapter_title}' . "\t" . '${toc_chapter_page}', 'PrefaceContentFont', 'TOCParaItem');
$section->addText('${toc_subsections}');
$section->addText('${toc_subsection_number} ${toc_subsection_title}' . "\t" . '${toc_subsection_page}', 'PrefaceContentFont', 'TOCParaSubItem');
$section->addText('${/toc_subsections}');
$section->addText('${/toc_chapters}');

$section->addText('บรรณานุกรม' . "\t" . '${toc_page_bib}', 'PrefaceContentFont', 'TOCParaItem');
$section->addText('ภาคผนวก' . "\t" . '${toc_page_app}', 'PrefaceContentFont', 'TOCParaItem');

$section->addPageBreak();

// ==== CONTENT ====
$section->addText('หน้า 2 (โครงสร้างที่จะโคลน)', 'CoverFont', 'CoverPara');

// TemplateProcessor block cloning requires exactly ${block_name} on its own line
$section->addText('${chapters}');
$section->addText('บทที่ ${chapter_number}', 'Heading1Font', 'Heading1');
$section->addText('${chapter_title}', 'Heading1Font', 'Heading1');

$section->addTextBreak(1, ['size' => 16]); // เว้น 1 บรรทัดจากหัวข้อ

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
