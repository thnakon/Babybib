<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('TH SarabunPSK');
$phpWord->setDefaultFontSize(16);

// Set document language to Thai
$phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('th-TH', 'th-TH', 'th-TH'));

// Define margins (1.5" left/top, 1" right/bottom)
$sectionStyle = [
    'marginTop' => 1.5 * 1440,
    'marginLeft' => 1.5 * 1440,
    'marginRight' => 1 * 1440,
    'marginBottom' => 1 * 1440,
];

// Reusable Paragraph Styles
$phpWord->addParagraphStyle('AcademicBody', [
    'spaceAfter' => 120, // 6pt
    'lineHeight' => 1.5,
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'indentation' => ['firstLine' => 740] 
]);

$phpWord->addParagraphStyle('AcademicBodyNoIndent', [
    'spaceAfter' => 120, // 6pt
    'lineHeight' => 1.5,
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'indentation' => ['firstLine' => 0]
]);

$phpWord->addParagraphStyle('Heading1', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 0,
    'lineHeight' => 1.0,
]);

$phpWord->addParagraphStyle('CoverPara', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 0,
    'lineHeight' => 1,
]);

$phpWord->addParagraphStyle('PrefaceParaTitle', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 0,
    'lineHeight' => 1.0,
]);

$phpWord->addParagraphStyle('PrefaceParaSign', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
    'spacing' => 0,
    'lineHeight' => 1.0,
]);

$phpWord->addParagraphStyle('TOCParaTitle', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'spacing' => 0,
    'lineHeight' => 1.0,
]);

$phpWord->addParagraphStyle('TOCParaLabel', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 0,
    'lineHeight' => 1,
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('center', 8100)
    ]
]);

$phpWord->addParagraphStyle('TOCParaItem', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 0,
    'lineHeight' => 1.0,
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('center', 8100)
    ]
]);

$phpWord->addParagraphStyle('TOCParaSubItem', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 0,
    'lineHeight' => 1.0,
    'indentation' => ['left' => 360],
    'tabs' => [
        new \PhpOffice\PhpWord\Style\Tab('right', 8400)
    ]
]);

// Font Styles
$phpWord->addFontStyle('Heading1Font', [
    'name' => 'TH SarabunPSK',
    'size' => 20,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('CoverTitleFont', [
    'name' => 'TH SarabunPSK',
    'size' => 24,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('CoverFont', [
    'name' => 'TH SarabunPSK',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('NormalFont', [
    'name' => 'TH SarabunPSK',
    'size' => 16,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('NormalBoldFont', [
    'name' => 'TH SarabunPSK',
    'size' => 16,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('PrefaceTitleFont', [
    'name' => 'TH SarabunPSK',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addFontStyle('TOCLabelFont', [
    'name' => 'TH SarabunPSK',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
]);

// Subsection heading style (for 1.1, 2.1 etc.)
$phpWord->addFontStyle('SubsectionTitleFont', [
    'name' => 'TH SarabunPSK',
    'size' => 18,
    'bold' => true,
    'hint' => 'cs',
]);

$phpWord->addParagraphStyle('SubsectionHeading', [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
    'spacing' => 120,
    'lineHeight' => 1.2,
    // Flush-left subsection headings (no left indent)
    'indentation' => ['left' => 0, 'firstLine' => 0],
]);

// 1. COVER PAGE
$section = $phpWord->addSection($sectionStyle);
$section->addText('${report_title}', 'CoverTitleFont', 'CoverPara');
$section->addTextBreak(5); // title -> author: 4 lines
$section->addText('${report_author}', 'CoverFont', 'CoverPara');
$section->addText('${report_student_ids}', 'CoverFont', 'CoverPara');
$section->addTextBreak(6); // student IDs -> degree: 6 lines
$section->addText('${report_degree} สาขาวิชา${report_major}', 'CoverFont', 'CoverPara');
$section->addText('${report_department}', 'CoverFont', 'CoverPara');
$section->addText('${report_institution}', 'CoverFont', 'CoverPara');
$section->addText('ภาคการศึกษาที่ ${report_semester} ปีการศึกษา ${report_year}', 'CoverFont', 'CoverPara');

// 2. BLANK PAGE (Behind Cover)
$section = $phpWord->addSection($sectionStyle);
$section->addText('', 'NormalFont', 'AcademicBody');

// 3. INNER COVER
$section = $phpWord->addSection($sectionStyle);
$section->addText('${report_title}', 'CoverTitleFont', 'CoverPara');
$section->addTextBreak(5); // title -> author: 4 lines
$section->addText('${report_author}', 'CoverFont', 'CoverPara');
$section->addText('${report_student_ids}', 'CoverFont', 'CoverPara');
$section->addTextBreak(6); // student IDs -> degree: 6 lines
$section->addText('${report_degree} สาขาวิชา${report_major}', 'CoverFont', 'CoverPara');
$section->addText('${report_department}', 'CoverFont', 'CoverPara');
$section->addText('${report_institution}', 'CoverFont', 'CoverPara');
$section->addText('ภาคการศึกษาที่ ${report_semester} ปีการศึกษา ${report_year}', 'CoverFont', 'CoverPara');

// 4. ACKNOWLEDGMENT
$section = $phpWord->addSection($sectionStyle);
$section->addText('กิตติกรรมประกาศ', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('${ack_paras}');
$section->addText('${ack_text}', 'NormalFont', 'AcademicBody');
$section->addText('${/ack_paras}');
$section->addTextBreak(1);
$section->addText('${acknowledgment_signer}', 'NormalFont', 'PrefaceParaSign');
$section->addText('${acknowledgment_date}', 'NormalFont', 'PrefaceParaSign');

$section = $phpWord->addSection($sectionStyle);

// Table for Metadata
$table = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);

// Column 1 width: ~4.5cm (2551 twips), Column 2: ~11cm (6236 twips)
$labelWidth = 3000; 
$valueWidth = 6200;

// Row 1: Title
$table->addRow();
$table->addCell($labelWidth)->addText('ชื่อเรื่องการศึกษาอิสระ', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_title}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 2: Author
$table->addRow();
$table->addCell($labelWidth)->addText('ผู้เขียน', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_author}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 3: Degree
$table->addRow();
$table->addCell($labelWidth)->addText('ปริญญา', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_degree} ${report_major}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 4: Advisor
$table->addRow();
$table->addCell($labelWidth)->addText('อาจารย์ที่ปรึกษา', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_instructor}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

$section->addTextBreak(1);
$section->addText('บทคัดย่อ', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('${abs_th_paras}');
$section->addText('${abs_th_text}', 'NormalFont', 'AcademicBody');
$section->addText('${/abs_th_paras}');
$section->addTextBreak(1);
$section->addText('คำสำคัญ: ${abstract_thai_keywords}', 'NormalFont', 'AcademicBodyNoIndent');

$section = $phpWord->addSection($sectionStyle);

// Table for Metadata
$table = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 0]);

// Row 1: Title
$table->addRow();
$table->addCell($labelWidth)->addText('Independent Study Title', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_title_en}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 2: Author
$table->addRow();
$table->addCell($labelWidth)->addText('Author', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_author_en}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 3: Degree
$table->addRow();
$table->addCell($labelWidth)->addText('Degree', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_degree_en} ${report_major_en}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

// Row 4: Advisor
$table->addRow();
$table->addCell($labelWidth)->addText('Advisor', 'NormalBoldFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);
$table->addCell($valueWidth)->addText('${report_instructor_en}', 'NormalFont', ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 60]);

$section->addTextBreak(1);
$section->addText('ABSTRACT', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('${abs_en_paras}');
$section->addText('${abs_en_text}', 'NormalFont', 'AcademicBody');
$section->addText('${/abs_en_paras}');
$section->addTextBreak(1);
$section->addText('Keywords: ${abstract_english_keywords}', 'NormalFont', 'AcademicBodyNoIndent');

// 7. TOC (Page 1)
$section = $phpWord->addSection($sectionStyle);
$section->addText('สารบัญ', 'PrefaceTitleFont', 'TOCParaTitle');
$section->addText("\t" . 'หน้า', 'TOCLabelFont', 'TOCParaLabel');

$section->addText('${toc_p1_block}');
$section->addText('${toc_p1_indent}${toc_p1_text}' . '${toc_p1_sep}' . '${toc_p1_page}', 'NormalFont', 'TOCParaItem');
$section->addText('${/toc_p1_block}');

// 7.1 TOC (Page 2 - Continued)
$section = $phpWord->addSection($sectionStyle);
$section->addText('${toc_p2_visible}');
$section->addText('สารบัญ (ต่อ)', 'PrefaceTitleFont', 'TOCParaTitle');
$section->addText("\t" . 'หน้า', 'TOCLabelFont', 'TOCParaLabel');

$section->addText('${toc_p2_block}');
$section->addText('${toc_p2_indent}${toc_p2_text}' . '${toc_p2_sep}' . '${toc_p2_page}', 'NormalFont', 'TOCParaItem');
$section->addText('${/toc_p2_block}');
$section->addText('${/toc_p2_visible}');

// 8. LIST OF FIGURES
$section = $phpWord->addSection($sectionStyle);
$section->addText('สารบัญภาพ', 'PrefaceTitleFont', 'TOCParaTitle');
$section->addText('ภาพที่' . "\t" . 'หน้า', 'TOCLabelFont', 'TOCParaLabel');
$section->addText('${figures_entries}');
$section->addText('ภาพที่ ${fig_number} ${fig_title}' . "\t" . '${fig_page}', 'NormalFont', 'TOCParaItem');
$section->addText('${/figures_entries}');

// 10. LIST OF TABLES
$section = $phpWord->addSection($sectionStyle);
$section->addText('สารบัญตาราง', 'PrefaceTitleFont', 'TOCParaTitle');
$section->addText('ตารางที่' . "\t" . 'หน้า', 'TOCLabelFont', 'TOCParaLabel');
$section->addText('${tables_entries}');
$section->addText('ตารางที่ ${tab_number} ${tab_title}' . "\t" . '${tab_page}', 'NormalFont', 'TOCParaItem');
$section->addText('${/tables_entries}');

// 11-15. CHAPTERS 1-5
$section = $phpWord->addSection($sectionStyle);
$section->addText('${chapters}');
$section->addText('บทที่ ${chapter_number}', 'Heading1Font', 'Heading1');
$section->addText('${chapter_title}', 'Heading1Font', 'Heading1');

$section->addText('${chapter_intro}', 'NormalFont', 'AcademicBody');

$section->addText('${subsections}');
$section->addText('${chapter_number}.${subsection_index} ${subsection_title}', 'SubsectionTitleFont', 'SubsectionHeading');
$section->addText('${subsection_content}', 'NormalFont', 'AcademicBody');
$section->addText('${/subsections}');

$section->addPageBreak();
$section->addText('${/chapters}');

// 16. BIBLIOGRAPHY
$section = $phpWord->addSection($sectionStyle);
$section->addText('บรรณานุกรม', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('${bibliography_entries}');
$section->addText('${bib_content}', 'NormalFont', 'AcademicBody'); // Note: AcademicBody has firstLine indent, usually Bib is Hanging Indent
$section->addText('${/bibliography_entries}');

// 17. APPENDIX A
$section = $phpWord->addSection($sectionStyle);
$section->addText('ภาคผนวก ก', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('[ส่วนสำหรับแทรกเนื้อหาภาคผนวก ก]', 'NormalFont', 'AcademicBody');

// 18. APPENDIX B
$section = $phpWord->addSection($sectionStyle);
$section->addText('ภาคผนวก ข', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('[ส่วนสำหรับแทรกเนื้อหาภาคผนวก ข]', 'NormalFont', 'AcademicBody');

// 19. BIOGRAPHY
$section = $phpWord->addSection($sectionStyle);
$section->addText('ประวัติผู้วิจัย', 'PrefaceTitleFont', 'PrefaceParaTitle');
$section->addTextBreak(1);
$section->addText('${bio_paras}');
$section->addText('${bio_text}', 'NormalFont', 'AcademicBody');
$section->addText('${/bio_paras}');

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$outputFile = __DIR__ . '/assets/templates/template_academic_research.docx';
$objWriter->save($outputFile);

echo "Research template created successfully at: $outputFile\n";
