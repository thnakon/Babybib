<?php

/**
 * Babybib - Report Builder Page
 * ==============================
 * สร้างรายงานจาก Template พร้อม Preview และ Export
 */

require_once '../includes/session.php';
$isGuestMode = !isLoggedIn();

$userId = $isGuestMode ? 'guest' : getCurrentUserId();
$templateId = htmlspecialchars($_GET['template'] ?? 'academic_general');

$validTemplates = ['academic_general', 'academic_general_logo', 'research', 'internship', 'project', 'thesis', 'thesis_master'];
if (!in_array($templateId, $validTemplates)) {
    $templateId = 'academic_general';
}

$isEnglish = getCurrentLanguage() === 'en';
$tr = static function ($th, $en) use ($isEnglish) {
    return $isEnglish ? $en : $th;
};

$pageTitle = $tr('สร้างรายงาน', 'Create Report');
$hideRating = true;
require_once '../includes/header.php';
require_once $isGuestMode ? '../includes/navbar-guest.php' : '../includes/navbar-user.php';

// Load user's projects
if (!$isGuestMode) {
    try {
        $db = getDB();
        $stmt = $db->prepare(" 
            SELECT p.id, p.name, p.color,
                   (SELECT COUNT(*) FROM bibliographies WHERE project_id = p.id) as bib_count
            FROM projects p
            WHERE p.user_id = ?
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute([$userId]);
        $userProjects = $stmt->fetchAll();
    } catch (Exception $e) {
        $userProjects = [];
    }
} else {
    $userProjects = [];
}

$builderText = [
    'back' => $tr('ย้อนกลับ', 'Back'),
    'builderTitle' => $tr('สร้างรายงาน', 'Create Report'),
    'loading' => $tr('กำลังโหลด...', 'Loading...'),
    'exportWord' => $tr('ส่งออกเป็น Word', 'Export Word'),
    'exportFailed' => $tr('ไม่สามารถส่งออกไฟล์ได้', 'Unable to export the file'),
    'docStructure' => $tr('โครงสร้างเอกสาร', 'Document Structure'),
    'formatting' => $tr('การจัดรูปแบบ', 'Formatting'),
    'documentFont' => $tr('ฟอนต์เอกสาร', 'Document Font'),
    'fontStandard' => $tr('มาตรฐาน', 'Standard'),
    'bodyFontSize' => $tr('ขนาดตัวอักษรเนื้อหา', 'Body Font Size'),
    'paperMargin' => $tr('ระยะขอบกระดาษ', 'Paper Margin'),
    'marginStandard' => $tr('มาตรฐาน (1.5"/1")', 'Standard (1.5"/1")'),
    'marginWide' => $tr('กว้าง (2"/1.5")', 'Wide (2"/1.5")'),
    'marginNarrow' => $tr('แคบ (1"/1")', 'Narrow (1"/1")'),
    'previewA4' => $tr('ตัวอย่างเอกสาร (A4)', 'Document Preview (A4)'),
    'panelLoadingDesc' => $tr('กรอกข้อมูลสำหรับส่วนนี้', 'Fill in details for this section'),
    'autofill' => $tr('สุ่มข้อมูลตัวอย่าง', 'Randomize Sample Data'),
    'autofillLoading' => $tr('กำลังสุ่มข้อมูลตัวอย่าง...', 'Randomizing sample data...'),
    'clearDraft' => $tr('ล้างข้อมูลร่าง', 'Clear Draft'),
    'clearDraftConfirm' => $tr('ต้องการล้างข้อมูลร่างของแม่แบบนี้หรือไม่? ข้อมูลที่กรอกไว้จะถูกรีเซ็ต', 'Clear the saved draft for this template? Your entered data will be reset.'),
    'clearDraftSuccess' => $tr('ล้างข้อมูลร่างเรียบร้อยแล้ว', 'Draft cleared successfully'),
    'clearDraftCancel' => $tr('ยกเลิก', 'Cancel'),
    'clearDraftDelete' => $tr('ล้างข้อมูลร่าง', 'Clear Draft'),
    'switchTemplate' => $tr('เปลี่ยนแม่แบบรายงาน', 'Change report template'),
    'guestMode' => $tr('โหมดทดลองใช้งาน', 'Guest Mode'),
    'guestBuilderTitle' => $tr('กำลังทดลองใช้แม่แบบรายงานแบบไม่ต้องเข้าสู่ระบบ', 'You are trying the report templates without signing in'),
    'guestBuilderDesc' => $tr('โหมดนี้ไม่สามารถดึงบรรณานุกรมจากโครงการ และข้อมูลที่กรอกอาจหายเมื่อรีเฟรช ออกจากหน้า หรือปิดเบราว์เซอร์ สมัครสมาชิกเพื่อบันทึกงานและใช้งานได้ครบทุกฟีเจอร์', 'In this mode you cannot import bibliography entries from projects, and your report data may be lost when you refresh, leave the page, or close the browser. Sign up to save your work and unlock the full workflow.'),
    'guestSignup' => $tr('สมัครสมาชิก', 'Sign Up'),
    'guestSignin' => $tr('เข้าสู่ระบบ', 'Sign In'),
    'guestBibliographyTitle' => $tr('บรรณานุกรมสำหรับสมาชิก', 'Bibliography import is for members'),
    'guestBibliographyDesc' => $tr('เข้าสู่ระบบหรือสมัครสมาชิกเพื่อเลือกบรรณานุกรมจากโครงการของคุณและแทรกลงรายงานอัตโนมัติ', 'Sign in or create an account to import bibliography entries from your projects into the report automatically.'),
    'coverTitle' => $tr('ข้อมูลหน้าปก', 'Cover Details'),
    'coverDesc' => $tr('กรอกข้อมูลเพื่อสร้างหน้าปกอัตโนมัติ', 'Fill in details to generate the cover automatically'),
    'coverFieldLogo' => $tr('ตราสถาบัน / Logo', 'Institution Logo'),
    'coverFieldLogoHint' => $tr('ใช้โลโก้มาตรฐานของสถาบัน หรืออัปโหลดไฟล์ใหม่เพื่อแสดงบนหน้าปกและในไฟล์ Word', 'Use the default institution logo or upload a new file for the cover and Word export.'),
    'coverFieldLogoFileHelp' => $tr('รองรับไฟล์ PNG, JPG, JPEG และ WEBP', 'Supports PNG, JPG, JPEG, and WEBP files.'),
    'coverFieldLogoUpload' => $tr('อัปโหลด Logo', 'Upload Logo'),
    'coverFieldLogoReset' => $tr('ใช้โลโก้มาตรฐาน', 'Use Default Logo'),
    'coverFieldLogoDefaultBadge' => $tr('มาตรฐาน', 'Default'),
    'coverFieldLogoUploadedBadge' => $tr('อัปโหลดแล้ว', 'Uploaded'),
    'coverFieldLogoAlt' => $tr('ตราสถาบัน', 'Institution logo'),
    'coverFieldLogoInvalid' => $tr('กรุณาอัปโหลดไฟล์ภาพประเภท PNG, JPG, JPEG หรือ WEBP', 'Please upload a PNG, JPG, JPEG, or WEBP image file.'),
    'innerCoverTitle' => $tr('ปกใน', 'Inner Cover'),
    'innerCoverDesc' => $tr('จัดทำเช่นเดียวกับหน้าปกนอก (ใช้ข้อมูลเดียวกัน)', 'Uses the same data as the outer cover'),
    'chapterDesc' => $tr('สารบัญย่อยและแนวทางการเขียน', 'Subsections and writing guidance'),
    'tocTitle' => $tr('สารบัญ', 'Table of Contents'),
    'tocDesc' => $tr('สร้างอัตโนมัติจากโครงสร้างบท', 'Generated automatically from the document structure'),
    'abstractTitle' => $tr('บทคัดย่อ', 'Abstract'),
    'abstractDesc' => $tr('สรุปสาระสำคัญของงาน 150-250 คำ', 'Summarize the work in 150-250 words'),
    'ackTitle' => $tr('กิตติกรรมประกาศ', 'Acknowledgment'),
    'ackDesc' => $tr('ขอบคุณผู้มีส่วนช่วยเหลือ', 'Thank contributors and supporters'),
    'bibTitle' => $tr('บรรณานุกรม', 'Bibliography'),
    'bibDesc' => $tr('เลือกโครงการที่มีรายการบรรณานุกรม', 'Choose a project with bibliography entries'),
    'appendixTitle' => $tr('ภาคผนวก', 'Appendix'),
    'appendixDesc' => $tr('เอกสาร/รูปภาพประกอบเพิ่มเติม', 'Additional supporting documents or images'),
    'prefaceTitle' => $tr('คำนำ', 'Preface'),
    'prefaceDesc' => $tr('แนะนำและชี้แจงวัตถุประสงค์ของรายงาน', 'Introduce the report and explain its objective'),
    'approvalTitle' => $tr('หน้าอนุมัติ', 'Approval Page'),
    'approvalDesc' => $tr('ลายเซ็นผู้อนุมัติและคณะกรรมการ', 'Approval signatures and committee information'),
    'biographyTitle' => $tr('ประวัติผู้เขียน', 'Author Biography'),
    'biographyDesc' => $tr('ประวัติการศึกษาและข้อมูลผู้วิจัย', 'Educational background and researcher information'),
    'autoGeneratedExport' => $tr('ส่วนนี้จะสร้างโดยอัตโนมัติในไฟล์ที่ export', 'This section is generated automatically in the exported file'),
    'coverFieldTitle' => $tr('ชื่อรายงาน / หัวข้อ', 'Report Title / Topic'),
    'coverFieldTitlePlaceholder' => $tr('เช่น การศึกษาผลของ...', 'For example: The effects of...'),
    'coverFieldAuthors' => $tr('ผู้จัดทำ / ชื่อผู้เขียน', 'Author / Prepared By'),
    'coverFieldAuthorsPlaceholder' => $tr('นาย/นางสาว ชื่อ นามสกุล\nหรือหลายคน (แต่ละคนขึ้นบรรทัดใหม่)', 'Full name\nFor multiple authors, use one line per person'),
    'coverFieldStudentIds' => $tr('รหัสนักศึกษา', 'Student ID'),
    'coverFieldStudentIdsPlaceholder' => $tr('XXXXXXXXX\nหลายคน: แต่ละรหัสขึ้นบรรทัดใหม่', 'XXXXXXXXX\nFor multiple authors, use one line per ID'),
    'coverFieldCompany' => $tr('สถานประกอบการ / บริษัท', 'Organization / Company'),
    'coverFieldCompanyPlaceholder' => $tr('ชื่อองค์กร/บริษัท', 'Organization or company name'),
    'coverFieldSupervisor' => $tr('ผู้ควบคุมการฝึกงาน', 'Internship Supervisor'),
    'coverFieldSupervisorPlaceholder' => $tr('ชื่อ-นามสกุล ผู้ควบคุม', 'Supervisor full name'),
    'coverFieldPeriod' => $tr('ช่วงเวลาฝึกงาน', 'Internship Period'),
    'coverFieldPeriodPlaceholder' => $tr('เช่น 1 มิ.ย. - 31 ส.ค. 2567', 'For example: Jun 1 - Aug 31, 2024'),
    'coverFieldProjectType' => $tr('ประเภทโครงการ', 'Project Type'),
    'coverFieldProjectTypePlaceholder' => $tr('เช่น โครงงานคอมพิวเตอร์, Senior Project', 'For example: Computer Project, Senior Project'),
    'coverFieldCourse' => $tr('รายวิชา', 'Course'),
    'coverFieldCoursePlaceholder' => $tr('เช่น ภาษาไทยเพื่อการสื่อสาร, TH101', 'For example: Thai for Communication, TH101'),
    'coverFieldMajor' => $tr('สาขาวิชา', 'Major'),
    'coverFieldMajorPlaceholder' => $tr('เช่น บรรณารักษศาสตร์และสารสนเทศศาสตร์', 'For example: Library and Information Science'),
    'coverFieldDegree' => $tr('ปริญญา', 'Degree'),
    'coverFieldDegreePlaceholder' => $tr('เช่น วิทยาศาสตรมหาบัณฑิต', 'For example: Master of Science'),
    'coverFieldCommittee' => $tr('คณะกรรมการที่ปรึกษา', 'Advisory Committee'),
    'coverFieldCommitteePlaceholder' => $tr('รศ.ดร. ชื่อ นามสกุล (ประธาน)\nผศ.ดร. ชื่อ นามสกุล', 'Assoc. Prof. Name Surname (Chair)\nAsst. Prof. Name Surname'),
    'coverFieldInstructor' => $tr('อาจารย์ผู้สอน / ที่ปรึกษา', 'Instructor / Advisor'),
    'coverFieldInstructorPlaceholder' => $tr('เช่น รศ.ดร. ชื่อ นามสกุล', 'For example: Assoc. Prof. Name Surname'),
    'coverFieldDepartment' => $tr('ภาควิชา / คณะ', 'Department / Faculty'),
    'coverFieldDepartmentPlaceholder' => $tr('เช่น ภาควิชาบรรณารักษศาสตร์, คณะมนุษยศาสตร์', 'For example: Department of Library Science, Faculty of Humanities'),
    'coverFieldInstitution' => $tr('สถาบัน / มหาวิทยาลัย', 'Institution / University'),
    'coverFieldInstitutionPlaceholder' => $tr('เช่น มหาวิทยาลัยเชียงใหม่', 'For example: Chiang Mai University'),
    'coverFieldSemesterYear' => $tr('ภาคเรียน / ปีการศึกษา', 'Semester / Academic Year'),
    'semester1' => $tr('ภาคเรียนที่ 1', 'Semester 1'),
    'semester2' => $tr('ภาคเรียนที่ 2', 'Semester 2'),
    'semester3' => $tr('ภาคฤดูร้อน', 'Summer Session'),
    'coverFieldYearPlaceholder' => $tr('เช่น 2567', 'For example: 2024'),
    'coverFieldStudyYear' => $tr('ปีการศึกษา (พ.ศ.)', 'Academic Year'),
    'chapterGuideTitle' => $tr('เนื้อหาที่ควรมีในบทนี้', 'Recommended content for this chapter'),
    'chapterFormatTitle' => $tr('การจัดรูปแบบบท', 'Chapter formatting'),
    'paperMarginTitle' => $tr('ระยะขอบกระดาษ', 'Paper margins'),
    'chapterHeadingLabel' => $tr('หัวบท (บทที่ X)', 'Chapter heading (Chapter X)'),
    'chapterTitleLabel' => $tr('ชื่อบท', 'Chapter title'),
    'subheadingLabel' => $tr('หัวข้อย่อย', 'Subheading'),
    'contentLabel' => $tr('เนื้อหา', 'Body text'),
    'paragraphLabel' => $tr('ย่อหน้า', 'Paragraph indent'),
    'leftTopLabel' => $tr('ซ้าย / บน', 'Left / Top'),
    'rightBottomLabel' => $tr('ขวา / ล่าง', 'Right / Bottom'),
    'tocAboutTitle' => $tr('เกี่ยวกับสารบัญ', 'About the table of contents'),
    'tocAuto1' => $tr('สารบัญจะสร้างอัตโนมัติใน Word (.docx)', 'The TOC is generated automatically in Word (.docx)'),
    'tocAuto2' => $tr('หน้าสารบัญจะอยู่ก่อนบทที่ 1', 'The TOC page appears before Chapter 1'),
    'tocAuto3' => $tr('แสดงชื่อบทพร้อมหมายเลขหน้า', 'Shows chapter titles with page numbers'),
    'tocAuto4' => $tr('สามารถอัปเดต TOC ใน Word ได้', 'You can update the TOC inside Word'),
    'tocFormatTitle' => $tr('การจัดรูปแบบสารบัญ', 'TOC formatting'),
    'tocHeadingLabel' => $tr('หัวข้อ "สารบัญ"', 'Heading "Table of Contents"'),
    'tocChapterLabel' => $tr('รายการบท', 'Chapter entries'),
    'tocLeaderLabel' => $tr('เส้นนำ', 'Leader dots'),
    'abstractGuideTitle' => $tr('แนวทางการเขียนบทคัดย่อ', 'Abstract writing guidelines'),
    'abstractGuideTitleEn' => 'Abstract writing guidelines (English)',
    'abstractGuide1' => $tr('ความยาว 150–300 คำ', '150–300 words'),
    'abstractGuide2' => $tr('วัตถุประสงค์ของงาน', 'State the objective'),
    'abstractGuide3' => $tr('วิธีดำเนินการโดยย่อ', 'Summarize the method'),
    'abstractGuide4' => $tr('ผลการศึกษาหลัก', 'Summarize the main findings'),
    'abstractGuide5' => $tr('ข้อสรุปและข้อเสนอแนะ', 'State conclusions and implications'),
    'abstractGuide6En' => 'Use active voice',
    'abstractGuide7En' => 'Avoid I/We',
    'formatTitle' => $tr('การจัดรูปแบบ', 'Formatting'),
    'keywordsLabel' => $tr('คำสำคัญ', 'Keywords'),
    'ackGuideTitle' => $tr('แนวทางกิตติกรรมประกาศ', 'Acknowledgment guidelines'),
    'ackGuide1' => $tr('ขอบคุณอาจารย์ที่ปรึกษา', 'Thank your advisor'),
    'ackGuide2' => $tr('ขอบคุณผู้เกี่ยวข้องและผู้ให้ข้อมูล', 'Thank contributors and informants'),
    'ackGuide3' => $tr('ขอบคุณครอบครัว', 'Thank your family'),
    'ackGuide4' => $tr('ลงชื่อผู้วิจัย + วันที่', 'Sign with author name and date'),
    'selectProjectTitle' => $tr('เลือกโครงการ', 'Choose Project'),
    'noProjectYet' => $tr('ยังไม่มีโครงการ', 'No projects yet'),
    'createNewProject' => $tr('สร้างโครงการใหม่ →', 'Create a new project →'),
    'selectOneProject' => $tr('คลิกเพื่อเลือก (เลือกได้ 1 โครงการ)', 'Click to select (one project only)'),
    'itemsSuffix' => $tr('รายการ', 'items'),
    'bibListTitle' => $tr('รายการบรรณานุกรม', 'Bibliography entries'),
    'andMore' => $tr('... และอีก', '... and'),
    'moreItems' => $tr('รายการ', 'more items'),
    'chooseProjectToPreview' => $tr('เลือกโครงการด้านบนเพื่อดูรายการบรรณานุกรม', 'Choose a project above to preview bibliography entries'),
    'bibFormatTitle' => $tr('การจัดรูปแบบบรรณานุกรม (APA 7)', 'Bibliography formatting (APA 7)'),
    'bibHeadingLabel' => $tr('หัวข้อ "บรรณานุกรม"', 'Heading "Bibliography"'),
    'bibItemLabel' => $tr('รายการ', 'Entries'),
    'hangingIndentLabel' => $tr('ย่อหน้าห้อย', 'Hanging Indent'),
    'orderLabel' => $tr('ลำดับ', 'Order'),
    'thaiFirstThenEnglish' => $tr('ภาษาไทยก่อน > อังกฤษ', 'Thai first > English'),
    'chapterBodySpec' => $tr('16pt ระยะ 1.5 บรรทัด', '16pt, 1.5 line spacing'),
    'paragraphSpec' => $tr('เว้นย่อหน้า 1.5 cm', '1.5 cm paragraph indent'),
    'marginTopLeftSpec' => $tr('1.5 นิ้ว (3.81 cm)', '1.5 in (3.81 cm)'),
    'marginBottomRightSpec' => $tr('1 นิ้ว (2.54 cm)', '1 in (2.54 cm)'),
    'bibItemSpec' => $tr('16pt ระยะ 1.5 บรรทัด', '16pt, 1.5 line spacing'),
    'hangingIndentSpec' => $tr('0.5 นิ้ว', '0.5 in'),
    'headingCenterSpec' => $tr('18pt ตัวหนา จัดกึ่งกลาง', '18pt Bold Center'),
    'subheadingSpec' => $tr('16pt ตัวหนา', '16pt Bold'),
    'tocLeaderSpec' => $tr('...... (เลขหน้า)', '...... (page)'),
    'appendixGuideTitle' => $tr('ภาคผนวก', 'Appendix'),
    'appendixGuide1' => $tr('แบบสอบถาม/แบบทดสอบที่ใช้', 'Questionnaires / instruments used'),
    'appendixGuide2' => $tr('รูปภาพประกอบ', 'Supporting images'),
    'appendixGuide3' => $tr('ข้อมูลดิบ', 'Raw data'),
    'appendixGuide4' => $tr('หนังสือขออนุญาต', 'Permission letters'),
    'appendixHint' => $tr('ส่วนนี้จะแสดงเป็นหน้าว่างในไฟล์ export เพื่อให้คุณเพิ่มเนื้อหาเองใน Word', 'This section is exported as a blank page so you can add content in Word later.'),
    'loadingBib' => $tr('กำลังโหลดบรรณานุกรม...', 'Loading bibliography...'),
    'loadingShort' => $tr('กำลังโหลด...', 'Loading...'),
    'loadFailed' => $tr('ไม่สามารถโหลดข้อมูลได้', 'Unable to load data'),
    'pageBreak' => $tr('ตัดหน้า (Page Break)', 'Page Break'),
    'coverPlaceholderTitle' => $tr('[ชื่อรายงาน]', '[Report Title]'),
    'coverPlaceholderAuthor' => $tr('[ชื่อ-สกุล ผู้จัดทำ]', '[Author Name]'),
    'coverPlaceholderCourse' => $tr('[รายวิชา]', '[Course]'),
    'coverPlaceholderInstructor' => $tr('[อาจารย์ผู้สอน]', '[Instructor]'),
    'coverPlaceholderDepartment' => $tr('[ภาควิชา/คณะ]', '[Department/Faculty]'),
    'coverPlaceholderInstitution' => $tr('[สถาบัน]', '[Institution]'),
    'coverPlaceholderCompany' => $tr('[ชื่อสถานประกอบการ]', '[Organization Name]'),
    'coverPlaceholderSupervisor' => $tr('[ผู้ควบคุมการฝึกงาน]', '[Supervisor]'),
    'coverPlaceholderPeriod' => $tr('[ช่วงเวลา]', '[Period]'),
    'coverPlaceholderMajor' => $tr('[สาขาวิชา]', '[Major]'),
    'semesterText3' => $tr('ฤดูร้อน', 'Summer'),
    'studentIdPrefix' => $tr('รหัส', 'Student ID '),
    'preparedBy' => $tr('จัดทำโดย', 'Prepared by'),
    'submittedTo' => $tr('เสนอ', 'Submitted to'),
    'internshipReport' => $tr('รายงานฝึกประสบการณ์วิชาชีพ', 'Internship Report'),
    'thesisSubtitleLine1' => $tr('วิทยานิพนธ์นี้เป็นส่วนหนึ่งของการศึกษาตามหลักสูตร', 'This thesis is a partial fulfillment of the degree requirements for'),
    'projectDefaultType' => $tr('รายงานโครงการ', 'Project Report'),
    'researchReport' => $tr('รายงานการวิจัย', 'Research Report'),
    'internshipOrgLabel' => $tr('สถานประกอบการ:', 'Organization:'),
    'internshipSupervisorLabel' => $tr('ผู้ควบคุม:', 'Supervisor:'),
    'internshipPeriodLabel' => $tr('ช่วงเวลา:', 'Period:'),
    'internshipInstructorLabel' => $tr('อาจารย์นิเทศ:', 'Academic Supervisor:'),
    'committeeLabel' => $tr('คณะกรรมการที่ปรึกษา', 'Advisory Committee'),
    'byLabel' => $tr('โดย', 'By'),
    'academicSemesterYear' => $tr('ภาคการศึกษาที่', 'Semester'),
    'academicYearOnly' => $tr('ปีการศึกษา', 'Academic Year'),
    'chapterPlaceholder1' => $tr('กรอกเนื้อหาส่วนนี้ในไฟล์ Word ที่ export', 'Add this section content in the exported Word file'),
    'chapterPlaceholder2' => $tr('ขนาดตัวอักษร 16pt ระยะบรรทัด 1.5 เว้นย่อหน้า 1.5 cm', 'Use 16pt text, 1.5 line spacing, and a 1.5 cm paragraph indent'),
    'abstractPreviewTh1' => $tr('สรุปสาระสำคัญของงานความยาว 150–300 คำ', 'Write a concise summary of 150–300 words.'),
    'abstractPreviewTh2' => $tr('ระบุวัตถุประสงค์ วิธีการ ผลการศึกษา และข้อสรุป', 'Include objective, method, results, and conclusion.'),
    'keywordsPlaceholder' => $tr('คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3', 'Keyword 1, Keyword 2, Keyword 3'),
    'ackPreview1' => $tr('ขอขอบพระคุณ [ชื่ออาจารย์ที่ปรึกษา] ที่ให้คำปรึกษาและแนะนำ...', 'I would like to express my sincere gratitude to [advisor name] for guidance and support...'),
    'ackPreview2' => $tr('ขอขอบคุณ... ที่ให้ความอนุเคราะห์...', 'I would also like to thank everyone who supported this work...'),
    'ackPreview3' => $tr('ท้ายที่สุด ขอขอบคุณครอบครัว...', 'Finally, I would like to thank my family...'),
    'authorFallback' => $tr('(ผู้จัดทำ)', '(Author)'),
    'yearFallback' => $tr('ปีการศึกษา', 'Academic Year'),
    'bibEmptyTitle' => $tr('ยังไม่ได้เลือกโครงการ', 'No project selected yet'),
    'bibEmptyDesc' => $tr('เลือกโครงการในแผงด้านขวาเพื่อแสดงบรรณานุกรม', 'Select a project in the right panel to show bibliography entries'),
    'appendixPreview1' => $tr('เพิ่มเนื้อหาภาคผนวกในไฟล์ Word ที่ export', 'Add appendix content in the exported Word file'),
    'appendixPreview2' => $tr('เช่น แบบสอบถาม รูปภาพประกอบ เอกสารอ้างอิง', 'For example: questionnaires, images, supporting documents'),
    'prefaceGuideTitle' => $tr('แนวทางการเขียนคำนำ', 'Preface writing guidelines'),
    'prefaceGuide1' => $tr('แนะนำที่มาและวัตถุประสงค์ของรายงาน', 'Introduce the background and objective of the report'),
    'prefaceGuide2' => $tr('ความเป็นมาโดยย่อ', 'Provide brief background information'),
    'prefaceGuide3' => $tr('ขอบคุณผู้ที่ให้ความช่วยเหลือ', 'Thank people who supported the work'),
    'prefaceGuide4' => $tr('ลงชื่อผู้จัดทำ พร้อมวันที่', 'Sign with author name and date'),
    'prefaceHint' => $tr('ความยาวคำนำไม่ควรเกิน 1 หน้า A4', 'The preface should ideally not exceed one A4 page'),
    'prefacePreview1' => $tr('รายงานฉบับนี้จัดทำขึ้นเพื่อเป็นส่วนหนึ่งของการศึกษาในรายวิชา...', 'This report was prepared as part of the coursework for...'),
    'prefacePreview2' => $tr('ผู้จัดทำหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะเป็นประโยชน์...', 'The author sincerely hopes this report will be useful...'),
    'prefaceContentLabel' => $tr('เนื้อหาคำนำ', 'Preface Content'),
    'prefaceContentPlaceholder' => $tr('พิมพ์คำนำที่นี่', 'Write the preface here'),
    'prefaceSignerLabel' => $tr('ชื่อผู้จัดทำ', 'Author Name'),
    'prefaceSignerPlaceholder' => $tr('เช่น กนกศักดิ์ ลอยเลิศ', 'For example: Kanoksak Loylert'),
    'prefaceDateLabel' => $tr('วันที่', 'Date'),
    'prefaceDatePlaceholder' => $tr('เช่น 14 พฤษภาคม 2565', 'For example: 14 May 2022'),
    'prefaceFormatTitle' => $tr('รูปแบบคำนำ', 'Preface formatting'),
    'prefaceHeadingSpec' => $tr('20pt ตัวหนา จัดกึ่งกลาง', '20pt Bold Center'),
    'prefaceBodySpec' => $tr('14-16pt จัดชิดขอบ ย่อหน้าแรก 1.5 ซม. เว้น 1 บรรทัด', '14-16pt justified, 1.5 cm first-line indent, 1 line between paragraphs'),
    'prefaceSignatureSpec' => $tr('ชิดขวา ท้ายหน้า', 'Right-aligned at the bottom'),
    'approvalGuideTitle' => $tr('หน้าอนุมัติ', 'Approval page'),
    'approvalGuide1' => $tr('ชื่อนักศึกษาและรหัสนักศึกษา', 'Student name and ID'),
    'approvalGuide2' => $tr('ลายเซ็นอาจารย์ที่ปรึกษา', 'Advisor signature'),
    'approvalGuide3' => $tr('ลายเซ็นคณะกรรมการสอบ (ป.โท/สหกิจ)', 'Committee signatures (master/co-op)'),
    'approvalGuide4' => $tr('ลายเซ็นคณบดี / หัวหน้าสาขา', 'Dean / head of program signature'),
    'approvalGuide5' => $tr('วันที่อนุมัติ', 'Approval date'),
    'approvalHint' => $tr('หน้าอนุมัติจะสร้างโครงสร้างพื้นฐานใน Word เพื่อให้กรอกลายเซ็นเพิ่มเติมได้', 'The approval page exports with a base structure so signatures can be added later in Word.'),
    'approvalAdvisor' => $tr('อาจารย์ที่ปรึกษา', 'Advisor'),
    'approvalDean' => $tr('หัวหน้าสาขา / คณบดี', 'Head of Program / Dean'),
    'approvalSignatureDate' => $tr('(ลายเซ็น / วันที่)', '(Signature / Date)'),
    'studentFallback' => $tr('[ชื่อ-สกุล นักศึกษา]', '[Student Name]'),
    'biographyGuideTitle' => $tr('ประวัติผู้เขียน', 'Author biography'),
    'biographyGuide1' => $tr('ชื่อ-นามสกุล และวันเดือนปีเกิด', 'Full name and date of birth'),
    'biographyGuide2' => $tr('ประวัติการศึกษา (ป.ตรี, ป.โท)', 'Educational background (Bachelor, Master)'),
    'biographyGuide3' => $tr('ตำแหน่งงานปัจจุบัน (ถ้ามี)', 'Current position (if any)'),
    'biographyGuide4' => $tr('สถานที่ทำงาน/ที่อยู่ (optional)', 'Workplace / address (optional)'),
    'biographyHint' => $tr('ประวัติผู้เขียนอยู่หน้าสุดท้ายของวิทยานิพนธ์', 'The author biography appears on the final page of the thesis'),
    'bioNameLabel' => $tr('ชื่อ-สกุล:', 'Name:'),
    'bioEducationLabel' => $tr('ประวัติการศึกษา:', 'Education:'),
    'bioPositionLabel' => $tr('ตำแหน่งงานปัจจุบัน:', 'Current Position:'),
    'chapterPrefix' => $tr('บทที่', 'Chapter'),
    'thesisYearPrefix' => $tr('พ.ศ.', ''),
    'exportGenerating' => $tr('กำลังสร้าง...', 'Generating...'),
    'englishAbstract' => 'Abstract (English)',
    'thaiAbstract' => $tr('บทคัดย่อ (ไทย)', 'Abstract (Thai)'),
];

$templateDefsLocalized = [
    'academic_general' => [
        'name' => $tr('รายงานวิชาการทั่วไป', 'General Academic Report'),
        'icon' => 'fa-file-lines', 'color' => '#8B5CF6', 'gradient' => 'linear-gradient(135deg, #8B5CF6, #6366F1)', 'coverType' => 'academic',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'inner_cover', 'type' => 'inner_cover', 'label' => $tr('ปกใน', 'Inner Cover'), 'icon' => 'fa-id-card-clip'],
            ['id' => 'preface', 'type' => 'preface', 'label' => $tr('คำนำ', 'Preface'), 'icon' => 'fa-pen-nib'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญของปัญหา', 'Background and significance'), $tr('วัตถุประสงค์ของการศึกษา', 'Objectives'), $tr('ขอบเขตการศึกษา', 'Scope of the study'), $tr('ประโยชน์ที่คาดว่าจะได้รับ', 'Expected benefits'), $tr('นิยามศัพท์', 'Definitions')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 เนื้อหา', 'Chapter 2 Content'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('เนื้อหา', 'Content'), 'subsections' => [$tr('แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'Related concepts and theories'), $tr('เนื้อหาสาระ', 'Main content'), $tr('รายละเอียดและการวิเคราะห์', 'Details and analysis')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 สรุป', 'Chapter 3 Conclusion'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('สรุปและอภิปรายผล', 'Conclusion and discussion'), 'subsections' => [$tr('สรุปผลการศึกษา', 'Summary of findings'), $tr('อภิปรายผล', 'Discussion'), $tr('ข้อเสนอแนะ', 'Recommendations')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'academic_general_logo' => [
        'name' => $tr('รายงานวิชาการทั่วไป พร้อม Logo', 'General Academic Report with Logo'),
        'icon' => 'fa-building-columns', 'color' => '#7C3AED', 'gradient' => 'linear-gradient(135deg, #7C3AED, #5B21B6)', 'coverType' => 'academic', 'showLogo' => true, 'defaultLogoUrl' => SITE_URL . '/assets/images/Chiang_Mai_University.svg.png',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'preface', 'type' => 'preface', 'label' => $tr('คำนำ', 'Preface'), 'icon' => 'fa-pen-nib'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญของปัญหา', 'Background and significance'), $tr('วัตถุประสงค์ของการศึกษา', 'Objectives'), $tr('ขอบเขตการศึกษา', 'Scope of the study'), $tr('ประโยชน์ที่คาดว่าจะได้รับ', 'Expected benefits'), $tr('นิยามศัพท์', 'Definitions')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 เนื้อหา', 'Chapter 2 Content'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('เนื้อหา', 'Content'), 'subsections' => [$tr('แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'Related concepts and theories'), $tr('เนื้อหาสาระ', 'Main content'), $tr('รายละเอียดและการวิเคราะห์', 'Details and analysis')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 สรุป', 'Chapter 3 Conclusion'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('สรุปและอภิปรายผล', 'Conclusion and discussion'), 'subsections' => [$tr('สรุปผลการศึกษา', 'Summary of findings'), $tr('อภิปรายผล', 'Discussion'), $tr('ข้อเสนอแนะ', 'Recommendations')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'research' => [
        'name' => $tr('รายงานการวิจัย', 'Research Report'), 'icon' => 'fa-microscope', 'color' => '#3B82F6', 'gradient' => 'linear-gradient(135deg, #3B82F6, #06B6D4)', 'coverType' => 'research',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'abstract', 'type' => 'abstract', 'label' => $tr('บทคัดย่อ', 'Abstract'), 'icon' => 'fa-align-left'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญ', 'Background and significance'), $tr('คำถามวิจัย', 'Research questions'), $tr('วัตถุประสงค์การวิจัย', 'Research objectives'), $tr('สมมติฐาน', 'Hypotheses'), $tr('ขอบเขตการวิจัย', 'Scope of research'), $tr('ข้อตกลงเบื้องต้น', 'Assumptions'), $tr('นิยามศัพท์', 'Definitions')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 วรรณกรรมที่เกี่ยวข้อง', 'Chapter 2 Literature Review'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('เอกสารและงานวิจัยที่เกี่ยวข้อง', 'Related literature and research'), 'subsections' => [$tr('แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'Related concepts and theories'), $tr('งานวิจัยที่เกี่ยวข้อง', 'Related studies'), $tr('กรอบแนวคิดของการวิจัย', 'Research framework')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 วิธีดำเนินการ', 'Chapter 3 Methodology'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('วิธีดำเนินการวิจัย', 'Research methodology'), 'subsections' => [$tr('ประชากรและกลุ่มตัวอย่าง', 'Population and sample'), $tr('เครื่องมือวิจัย', 'Research instruments'), $tr('การตรวจสอบคุณภาพเครื่องมือ', 'Instrument validation'), $tr('การเก็บรวบรวมข้อมูล', 'Data collection'), $tr('การวิเคราะห์ข้อมูล', 'Data analysis'), $tr('สถิติที่ใช้', 'Statistics used')]],
            ['id' => 'ch4', 'type' => 'chapter', 'label' => $tr('บทที่ 4 ผลการวิจัย', 'Chapter 4 Results'), 'icon' => 'fa-book-open', 'number' => 4, 'title' => $tr('ผลการวิจัย', 'Results'), 'subsections' => [$tr('ลักษณะกลุ่มตัวอย่าง', 'Sample characteristics'), $tr('ผลการวิเคราะห์ข้อมูลตามวัตถุประสงค์', 'Findings by objective')]],
            ['id' => 'ch5', 'type' => 'chapter', 'label' => $tr('บทที่ 5 สรุปอภิปราย', 'Chapter 5 Conclusion and Discussion'), 'icon' => 'fa-book-open', 'number' => 5, 'title' => $tr('สรุป อภิปรายผล และข้อเสนอแนะ', 'Conclusion, discussion, and recommendations'), 'subsections' => [$tr('สรุปผลการวิจัย', 'Summary of findings'), $tr('อภิปรายผล', 'Discussion'), $tr('ข้อเสนอแนะในการนำผลไปใช้', 'Practical recommendations'), $tr('ข้อเสนอแนะสำหรับการวิจัยครั้งต่อไป', 'Recommendations for future research')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'internship' => [
        'name' => $tr('รายงานฝึกงาน / สหกิจ', 'Internship / Cooperative Report'), 'icon' => 'fa-briefcase', 'color' => '#10B981', 'gradient' => 'linear-gradient(135deg, #10B981, #059669)', 'coverType' => 'internship',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'approval', 'type' => 'approval', 'label' => $tr('หน้าอนุมัติ', 'Approval Page'), 'icon' => 'fa-file-signature'],
            ['id' => 'acknowledgment', 'type' => 'acknowledgment', 'label' => $tr('กิตติกรรมประกาศ', 'Acknowledgment'), 'icon' => 'fa-heart'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญ', 'Background and significance'), $tr('วัตถุประสงค์', 'Objectives'), $tr('ขอบเขตของรายงาน', 'Scope of the report'), $tr('ประโยชน์ที่ได้รับ', 'Benefits gained')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 ข้อมูลองค์กร', 'Chapter 2 Organization'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('ข้อมูลสถานประกอบการ', 'Organization information'), 'subsections' => [$tr('ประวัติและความเป็นมา', 'History and background'), $tr('วิสัยทัศน์ พันธกิจ', 'Vision and mission'), $tr('โครงสร้างองค์กร', 'Organizational structure'), $tr('ลักษณะการดำเนินงาน', 'Operations overview')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 งานที่ได้รับ', 'Chapter 3 Assigned Work'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('งานที่ได้รับมอบหมาย', 'Assigned work'), 'subsections' => [$tr('ลักษณะตำแหน่งงาน', 'Role description'), $tr('งานที่ได้รับมอบหมายหลัก', 'Main assignments'), $tr('ขั้นตอนและวิธีการปฏิบัติงาน', 'Procedures and workflow'), $tr('เครื่องมือและอุปกรณ์ที่ใช้', 'Tools and equipment used')]],
            ['id' => 'ch4', 'type' => 'chapter', 'label' => $tr('บทที่ 4 ผลการปฏิบัติงาน', 'Chapter 4 Performance Results'), 'icon' => 'fa-book-open', 'number' => 4, 'title' => $tr('ผลการปฏิบัติงาน', 'Work results'), 'subsections' => [$tr('ผลการปฏิบัติงานโดยภาพรวม', 'Overall results'), $tr('ปัญหาและอุปสรรค', 'Problems and obstacles'), $tr('วิธีแก้ปัญหา', 'Solutions')]],
            ['id' => 'ch5', 'type' => 'chapter', 'label' => $tr('บทที่ 5 สรุป', 'Chapter 5 Conclusion'), 'icon' => 'fa-book-open', 'number' => 5, 'title' => $tr('สรุปและข้อเสนอแนะ', 'Conclusion and recommendations'), 'subsections' => [$tr('สรุปผลการฝึกงาน', 'Internship summary'), $tr('ความรู้และทักษะที่ได้รับ', 'Knowledge and skills gained'), $tr('ข้อเสนอแนะ', 'Recommendations')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'project' => [
        'name' => $tr('รายงานโครงการ', 'Project Report'), 'icon' => 'fa-diagram-project', 'color' => '#F59E0B', 'gradient' => 'linear-gradient(135deg, #F59E0B, #F97316)', 'coverType' => 'project',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ที่มาและความสำคัญ', 'Background and significance'), $tr('วัตถุประสงค์', 'Objectives'), $tr('ขอบเขตของโครงการ', 'Project scope'), $tr('ประโยชน์ที่คาดว่าจะได้รับ', 'Expected benefits')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 ทฤษฎี', 'Chapter 2 Theory'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('แนวคิด ทฤษฎี และงานที่เกี่ยวข้อง', 'Concepts, theories, and related work'), 'subsections' => [$tr('ทฤษฎีที่เกี่ยวข้อง', 'Related theories'), $tr('เทคโนโลยีที่ใช้', 'Technologies used'), $tr('งานที่เกี่ยวข้อง', 'Related work')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 การออกแบบ', 'Chapter 3 Design'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('การออกแบบและพัฒนา', 'Design and development'), 'subsections' => [$tr('การวิเคราะห์ความต้องการ', 'Requirements analysis'), $tr('การออกแบบระบบ/ผลิตภัณฑ์', 'System/product design'), $tr('ขั้นตอนการพัฒนา', 'Development process'), $tr('เครื่องมือที่ใช้', 'Tools used')]],
            ['id' => 'ch4', 'type' => 'chapter', 'label' => $tr('บทที่ 4 ผลลัพธ์', 'Chapter 4 Results'), 'icon' => 'fa-book-open', 'number' => 4, 'title' => $tr('ผลการดำเนินงาน', 'Results'), 'subsections' => [$tr('ผลลัพธ์ที่ได้', 'Outcomes'), $tr('การทดสอบ', 'Testing'), $tr('ปัญหาและแนวทางแก้ไข', 'Problems and solutions')]],
            ['id' => 'ch5', 'type' => 'chapter', 'label' => $tr('บทที่ 5 สรุป', 'Chapter 5 Conclusion'), 'icon' => 'fa-book-open', 'number' => 5, 'title' => $tr('สรุปและข้อเสนอแนะ', 'Conclusion and recommendations'), 'subsections' => [$tr('สรุปผลโครงการ', 'Project summary'), $tr('ข้อเสนอแนะ', 'Recommendations'), $tr('แนวทางการพัฒนาต่อ', 'Future improvements')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'thesis' => [
        'name' => $tr('วิทยานิพนธ์ / สารนิพนธ์', 'Thesis / Independent Study'), 'icon' => 'fa-graduation-cap', 'color' => '#EF4444', 'gradient' => 'linear-gradient(135deg, #EF4444, #DC2626)', 'coverType' => 'thesis',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'acknowledgment', 'type' => 'acknowledgment', 'label' => $tr('กิตติกรรมประกาศ', 'Acknowledgment'), 'icon' => 'fa-heart'],
            ['id' => 'abstract_th', 'type' => 'abstract', 'label' => $tr('บทคัดย่อ (ไทย)', 'Abstract (Thai)'), 'icon' => 'fa-align-left', 'lang' => 'th'],
            ['id' => 'abstract_en', 'type' => 'abstract', 'label' => 'Abstract (English)', 'icon' => 'fa-align-left', 'lang' => 'en'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญ', 'Background and significance'), $tr('คำถามวิจัย', 'Research questions'), $tr('วัตถุประสงค์', 'Objectives'), $tr('สมมติฐาน', 'Hypotheses'), $tr('ขอบเขต', 'Scope'), $tr('นิยามศัพท์', 'Definitions'), $tr('ประโยชน์', 'Benefits')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 วรรณกรรม', 'Chapter 2 Literature Review'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('วรรณกรรมและงานวิจัยที่เกี่ยวข้อง', 'Literature review and related studies'), 'subsections' => [$tr('กรอบแนวคิด', 'Conceptual framework'), $tr('ทฤษฎีที่เกี่ยวข้อง', 'Related theories'), $tr('งานวิจัยที่เกี่ยวข้อง', 'Related studies')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 วิธีวิจัย', 'Chapter 3 Methodology'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('วิธีดำเนินการวิจัย', 'Research methodology'), 'subsections' => [$tr('รูปแบบการวิจัย', 'Research design'), $tr('ประชากรและกลุ่มตัวอย่าง', 'Population and sample'), $tr('เครื่องมือวิจัย', 'Research instruments'), $tr('การตรวจสอบคุณภาพ', 'Quality validation'), $tr('การเก็บข้อมูล', 'Data collection'), $tr('การวิเคราะห์', 'Analysis'), $tr('สถิติ', 'Statistics')]],
            ['id' => 'ch4', 'type' => 'chapter', 'label' => $tr('บทที่ 4 ผลการวิจัย', 'Chapter 4 Results'), 'icon' => 'fa-book-open', 'number' => 4, 'title' => $tr('ผลการวิจัย', 'Results'), 'subsections' => [$tr('ลักษณะกลุ่มตัวอย่าง', 'Sample characteristics'), $tr('ผลการวิเคราะห์ตามวัตถุประสงค์', 'Findings by objective')]],
            ['id' => 'ch5', 'type' => 'chapter', 'label' => $tr('บทที่ 5 สรุปอภิปราย', 'Chapter 5 Conclusion and Discussion'), 'icon' => 'fa-book-open', 'number' => 5, 'title' => $tr('สรุป อภิปรายผล และข้อเสนอแนะ', 'Conclusion, discussion, and recommendations'), 'subsections' => [$tr('สรุปผลการวิจัย', 'Summary of findings'), $tr('อภิปรายผล', 'Discussion'), $tr('ข้อเสนอแนะในการนำผลไปใช้', 'Practical recommendations'), $tr('ข้อเสนอแนะสำหรับการวิจัยต่อไป', 'Recommendations for future research')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
        ],
    ],
    'thesis_master' => [
        'name' => $tr('วิทยานิพนธ์ ป.โท', 'Master Thesis'), 'icon' => 'fa-user-graduate', 'color' => '#7C3AED', 'gradient' => 'linear-gradient(135deg, #7C3AED, #5B21B6)', 'coverType' => 'thesis',
        'sections' => [
            ['id' => 'cover', 'type' => 'cover', 'label' => $tr('หน้าปก', 'Cover'), 'icon' => 'fa-id-card'],
            ['id' => 'approval', 'type' => 'approval', 'label' => $tr('หน้าอนุมัติ', 'Approval Page'), 'icon' => 'fa-file-signature'],
            ['id' => 'abstract_th', 'type' => 'abstract', 'label' => $tr('บทคัดย่อ (ไทย)', 'Abstract (Thai)'), 'icon' => 'fa-align-left', 'lang' => 'th'],
            ['id' => 'abstract_en', 'type' => 'abstract', 'label' => 'Abstract (English)', 'icon' => 'fa-align-left', 'lang' => 'en'],
            ['id' => 'acknowledgment', 'type' => 'acknowledgment', 'label' => $tr('กิตติกรรมประกาศ', 'Acknowledgment'), 'icon' => 'fa-heart'],
            ['id' => 'toc', 'type' => 'toc', 'label' => $tr('สารบัญ', 'Table of Contents'), 'icon' => 'fa-list-ul'],
            ['id' => 'ch1', 'type' => 'chapter', 'label' => $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'), 'icon' => 'fa-book-open', 'number' => 1, 'title' => $tr('บทนำ', 'Introduction'), 'subsections' => [$tr('ความเป็นมาและความสำคัญของปัญหา', 'Background and significance'), $tr('คำถามการวิจัย', 'Research questions'), $tr('วัตถุประสงค์การวิจัย', 'Research objectives'), $tr('ขอบเขตการวิจัย', 'Research scope'), $tr('นิยามศัพท์เฉพาะ', 'Operational definitions'), $tr('ประโยชน์ที่คาดว่าจะได้รับ', 'Expected benefits')]],
            ['id' => 'ch2', 'type' => 'chapter', 'label' => $tr('บทที่ 2 วรรณกรรมที่เกี่ยวข้อง', 'Chapter 2 Literature Review'), 'icon' => 'fa-book-open', 'number' => 2, 'title' => $tr('เอกสารและงานวิจัยที่เกี่ยวข้อง', 'Related literature and research'), 'subsections' => [$tr('กรอบแนวคิดการวิจัย', 'Research framework'), $tr('แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'Related concepts and theories'), $tr('งานวิจัยที่เกี่ยวข้องในประเทศ', 'Domestic studies'), $tr('งานวิจัยที่เกี่ยวข้องต่างประเทศ', 'International studies')]],
            ['id' => 'ch3', 'type' => 'chapter', 'label' => $tr('บทที่ 3 วิธีดำเนินการวิจัย', 'Chapter 3 Methodology'), 'icon' => 'fa-book-open', 'number' => 3, 'title' => $tr('วิธีดำเนินการวิจัย', 'Research methodology'), 'subsections' => [$tr('รูปแบบการวิจัย', 'Research design'), $tr('ประชากรและกลุ่มตัวอย่าง', 'Population and sample'), $tr('เครื่องมือที่ใช้ในการวิจัย', 'Research instruments'), $tr('การตรวจสอบคุณภาพเครื่องมือ', 'Instrument validation'), $tr('การเก็บรวบรวมข้อมูล', 'Data collection'), $tr('การวิเคราะห์ข้อมูล', 'Data analysis'), $tr('สถิติที่ใช้ในการวิเคราะห์', 'Statistics used')]],
            ['id' => 'ch4', 'type' => 'chapter', 'label' => $tr('บทที่ 4 ผลการวิจัย', 'Chapter 4 Results'), 'icon' => 'fa-book-open', 'number' => 4, 'title' => $tr('ผลการวิจัย', 'Results'), 'subsections' => [$tr('ลักษณะของกลุ่มตัวอย่าง', 'Sample characteristics'), $tr('ผลการวิเคราะห์ข้อมูลตามวัตถุประสงค์การวิจัย', 'Findings by objective'), $tr('ผลการทดสอบสมมติฐาน', 'Hypothesis testing results')]],
            ['id' => 'ch5', 'type' => 'chapter', 'label' => $tr('บทที่ 5 สรุปอภิปรายผล', 'Chapter 5 Conclusion and Discussion'), 'icon' => 'fa-book-open', 'number' => 5, 'title' => $tr('สรุป อภิปรายผล และข้อเสนอแนะ', 'Conclusion, discussion, and recommendations'), 'subsections' => [$tr('สรุปผลการวิจัย', 'Summary of findings'), $tr('อภิปรายผลการวิจัย', 'Discussion'), $tr('ข้อเสนอแนะในการนำผลไปใช้', 'Practical recommendations'), $tr('ข้อเสนอแนะสำหรับการวิจัยครั้งต่อไป', 'Recommendations for future research')]],
            ['id' => 'bibliography', 'type' => 'bibliography', 'label' => $tr('บรรณานุกรม', 'Bibliography'), 'icon' => 'fa-book'],
            ['id' => 'appendix', 'type' => 'appendix', 'label' => $tr('ภาคผนวก', 'Appendix'), 'icon' => 'fa-paperclip'],
            ['id' => 'biography', 'type' => 'biography', 'label' => $tr('ประวัติผู้เขียน', 'Author Biography'), 'icon' => 'fa-user-circle'],
        ],
    ],
];
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ===== BUILDER LAYOUT ===== */
    body { overflow: hidden; }

    :root {
        --builder-bg: linear-gradient(180deg, #dddddd 0%, #ececec 100%);
        --builder-surface: #f6f6f6;
        --builder-surface-alt: #efefef;
        --builder-border: #d2d2d2;
        --builder-border-strong: #bebebe;
        --builder-text: #2f3135;
        --builder-muted: #6c7078;
        --builder-soft: #8d929b;
        --builder-accent: #2b579a;
        --builder-accent-soft: #e5edf9;
        --builder-danger: #c25151;
        --builder-danger-soft: #fff0f0;
        --builder-preview-bg: linear-gradient(180deg, #e5e5e5 0%, #efefef 100%);
    }

    .builder-wrap {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--nav-height, 96px));
        background: #1a1a2e;
        overflow: hidden;
    }

    /* Top bar */
    .builder-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        height: 54px;
        background: #0f0f1a;
        border-bottom: 1px solid #2a2a3e;
        flex-shrink: 0;
        z-index: 10;
    }

    .builder-guest-banner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #fff7ec, #fffdf9);
        border-bottom: 1px solid #ead9bc;
    }

    .builder-guest-banner-copy {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .builder-guest-banner-copy i {
        color: #b7791f;
        font-size: 18px;
        margin-top: 2px;
    }

    .builder-guest-banner-copy strong {
        display: block;
        color: #5f4518;
        font-size: 13px;
        margin-bottom: 3px;
    }

    .builder-guest-banner-copy p {
        margin: 0;
        color: #7a6541;
        font-size: 12px;
        line-height: 1.65;
        max-width: 760px;
    }

    .builder-guest-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .builder-guest-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .builder-guest-btn.primary {
        background: linear-gradient(135deg, #2b579a, #4c7bd9);
        color: #fff;
        box-shadow: 0 10px 22px rgba(43, 87, 154, 0.16);
    }

    .builder-guest-btn.secondary {
        background: rgba(255,255,255,0.85);
        border: 1px solid #decaa6;
        color: #6d5936;
    }

    .builder-guest-btn:hover {
        transform: translateY(-1px);
        color: inherit;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .topbar-back {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #aaa;
        text-decoration: none;
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 8px;
        transition: all 0.15s;
    }

    .topbar-back:hover {
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .topbar-title {
        font-size: 14px;
        font-weight: 600;
        color: white;
    }

    .topbar-template-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 6px 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        position: relative;
        overflow: visible;
    }

    .topbar-template-switcher {
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 6px;
        background: rgba(255,255,255,0.16);
        color: currentColor;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
        margin-left: 2px;
    }

    .topbar-template-switcher:hover {
        background: rgba(255,255,255,0.24);
        transform: translateY(-1px);
    }

    .topbar-template-switcher i {
        font-size: 11px;
    }

    .topbar-template-menu {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        min-width: 230px;
        padding: 8px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(199, 210, 225, 0.9);
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(14px);
        display: none;
        z-index: 40;
    }

    .topbar-template-menu.open {
        display: block;
    }

    .topbar-template-option {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: #344054;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .topbar-template-option:hover {
        background: #f3f6fb;
    }

    .topbar-template-option.active {
        background: #edf3ff;
        color: #2b579a;
        font-weight: 700;
    }

    .topbar-template-option-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.28);
    }

    .topbar-template-option-icon i {
        font-size: 13px;
    }

    .topbar-template-option-label {
        min-width: 0;
        flex: 1;
        font-size: 12px;
        line-height: 1.35;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .topbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .topbar-btn-pdf {
        background: rgba(239, 68, 68, 0.15);
        color: #FCA5A5;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .topbar-btn-pdf:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #FCA5A5;
    }

    .topbar-btn-docx {
        background: linear-gradient(135deg, #8B5CF6, #6366F1);
        color: white;
    }

    .topbar-btn-docx:hover {
        filter: brightness(1.1);
    }

    .topbar-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .topbar-btn.is-success {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: #fff;
        box-shadow: 0 10px 20px rgba(34, 197, 94, 0.18);
    }

    .topbar-btn.is-hidden {
        opacity: 0;
        transform: scale(0.94);
        pointer-events: none;
    }

    /* Main body */
    .builder-body {
        display: grid;
        grid-template-columns: 240px 1fr 320px;
        flex: 1;
        overflow: hidden;
    }

    /* ===== LEFT SIDEBAR ===== */
    .builder-sidebar {
        background: #13131f;
        border-right: 1px solid #2a2a3e;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sidebar-section-title {
        padding: 14px 16px 10px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
    }

    .section-nav-list {
        flex: 1;
        overflow-y: auto;
        padding: 0 8px 8px;
    }

    .section-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s;
        margin-bottom: 2px;
    }

    .section-nav-item:hover {
        background: rgba(255,255,255,0.05);
    }

    .section-nav-item.active {
        background: rgba(139, 92, 246, 0.15);
    }

    .section-nav-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.05);
        color: #666;
    }

    .section-nav-item.active .section-nav-icon {
        background: rgba(139, 92, 246, 0.2);
        color: #A78BFA;
    }

    .section-nav-label {
        font-size: 12.5px;
        color: #888;
        font-weight: 500;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .section-nav-item.active .section-nav-label {
        color: #D4BBFF;
        font-weight: 600;
    }

    /* Format settings in sidebar */
    .sidebar-format {
        padding: 12px;
        border-top: 1px solid #2a2a3e;
        flex-shrink: 0;
    }

    .sidebar-format-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
        margin-bottom: 10px;
    }

    .format-row {
        margin-bottom: 8px;
    }

    .format-row label {
        display: block;
        font-size: 11px;
        color: #666;
        margin-bottom: 4px;
    }

    .format-row select {
        width: 100%;
        background: #1e1e2e;
        border: 1px solid #333;
        color: #ccc;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 12px;
        appearance: none;
        cursor: pointer;
    }

    /* ===== CENTER PREVIEW ===== */
    .builder-preview {
        background: #1e1e2e;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 20px;
        gap: 16px;
    }

    .preview-header-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #555;
        align-self: flex-start;
        margin-left: 10px;
    }

    /* A4 Paper preview
       Use 96dpi A4 sizing to visually match the default paper model most browsers use.
       Base body size stays at 16px and academic cover preview remains smaller than Word by design. */
    .a4-paper {
        width: min(794px, 100%);
        max-width: 794px;
        min-height: 1123px;
        --page-top: 145px;
        --page-right: 96px;
        --page-bottom: 96px;
        --page-left: 145px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        padding: 145px 96px 96px 145px; /* 1.5in top/left, 1in right/bottom at 96dpi */
        position: relative;
        font-family: 'Sarabun', 'Tahoma', serif;
        font-size: 16px;
        line-height: 1.65;
        color: #111;
        box-sizing: border-box;
        transition: all 0.3s;
    }

    /* Cover page styles */
    .cover-institution {
        text-align: center;
        font-size: 13px;
        margin-bottom: 6px;
        color: #333;
    }

    .cover-logo-placeholder {
        text-align: center;
        margin: 20px 0;
        color: #DDD;
        font-size: 50px;
    }

    .cover-logo-block {
        text-align: center;
        margin: 0 0 16px;
    }

    .cover-logo-image {
        width: 132px;
        max-width: 100%;
        height: auto;
        object-fit: contain;
        display: inline-block;
    }

    .logo-upload-panel {
        display: grid;
        gap: 12px;
    }

    .logo-upload-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #ddd6fe;
        background: linear-gradient(180deg, #faf7ff 0%, #f4efff 100%);
    }

    .logo-upload-preview img {
        width: 72px;
        height: 72px;
        object-fit: contain;
        background: #fff;
        border-radius: 12px;
        padding: 6px;
        border: 1px solid rgba(124, 58, 237, 0.16);
    }

    .logo-upload-meta {
        display: grid;
        gap: 4px;
        min-width: 0;
    }

    .logo-upload-title {
        font-size: 13px;
        font-weight: 700;
        color: #2f3135;
    }

    .logo-upload-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(124, 58, 237, 0.12);
        color: #6d28d9;
        font-size: 11px;
        font-weight: 700;
    }

    .logo-upload-filename {
        font-size: 11px;
        color: #6c7078;
        word-break: break-word;
    }

    .logo-upload-actions {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        align-items: stretch;
    }

    .logo-upload-btn,
    .logo-upload-reset {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        text-decoration: none;
        flex: 1 1 0;
        width: 100%;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .logo-upload-btn {
        background: #7c3aed;
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .logo-upload-btn:hover {
        background: #6d28d9;
    }

    .logo-upload-btn i,
    .logo-upload-btn span {
        color: #fff;
        opacity: 1;
    }

    .logo-upload-reset {
        background: #fff;
        color: #5b21b6;
        border-color: #a78bfa;
    }

    .logo-upload-reset:hover {
        background: #f5f3ff;
        border-color: #8b5cf6;
        color: #4c1d95;
    }

    .logo-upload-reset i,
    .logo-upload-reset span {
        color: inherit;
    }

    .logo-upload-hint {
        font-size: 11px;
        line-height: 1.6;
        color: #6c7078;
    }

    .logo-upload-hint span {
        color: #8b5cf6;
    }

    .cover-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        margin: 30px 0 12px;
        color: #000;
    }

    .cover-subtitle {
        text-align: center;
        font-size: 14px;
        color: #444;
        margin-bottom: 40px;
    }

    .cover-info-block {
        text-align: center;
        margin-bottom: 14px;
    }

    .cover-info-label {
        font-size: 13px;
        color: #555;
        margin-bottom: 2px;
    }

    .cover-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #111;
    }

    .cover-bottom {
        position: absolute;
        bottom: var(--page-bottom, 96px);
        left: var(--page-left, 145px);
        right: var(--page-right, 96px);
        text-align: center;
        font-size: 16px;
        color: #444;
        line-height: 1.8;
    }

    /* Chapter styles */
    .chapter-heading {
        text-align: center;
        font-size: 18px; /* 18pt at 72dpi */
        font-weight: 700;
        margin-bottom: 8px;
        color: #000;
    }

    .chapter-sub-heading {
        font-size: 16px; /* 16pt at 72dpi */
        font-weight: 700;
        margin: 18px 0 8px;
        color: #000;
    }

    .chapter-body-placeholder {
        background: #F9FAFB;
        border-left: 3px solid #E5E7EB;
        padding: 12px 16px;
        border-radius: 0 6px 6px 0;
        margin: 8px 0;
    }

    .chapter-body-placeholder p {
        font-size: 12px;
        color: #9CA3AF;
        margin: 0 0 4px;
        line-height: 1.6;
    }

    .preface-preview-body {
        font-size: 16px;
        line-height: 1.5;
        color: #111;
        text-align: justify;
        text-justify: inter-character;
        word-break: break-word;
    }

    .preface-preview-body p {
        margin: 0 0 24px;
        text-indent: 1.5cm;
    }

    .preface-preview-body p:last-child {
        margin-bottom: 0;
    }

    /* Bibliography preview */
    .bib-section-title {
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 24px;
        color: #000;
    }

    .bib-preview-item {
        text-indent: -36px;
        padding-left: 36px;
        margin-bottom: 12px;
        font-size: 16px; /* 16pt at 72dpi */
        line-height: 1.6;
        color: #222;
    }

    .bib-preview-item i {
        font-style: italic;
    }

    .bib-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #aaa;
    }

    .bib-empty-state i {
        font-size: 32px;
        margin-bottom: 12px;
        display: block;
    }

    /* Page break hint */
    .page-break-hint {
        width: 794px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #444;
        font-size: 11px;
    }

    .page-break-hint::before,
    .page-break-hint::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #333;
        border-top: 1px dashed #333;
    }

    /* ===== RIGHT PANEL ===== */
    .builder-panel {
        background: #13131f;
        border-left: 1px solid #2a2a3e;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #2a2a3e;
        flex-shrink: 0;
        position: relative;
    }

    .panel-header-copy {
        min-width: 0;
        flex: 1 1 auto;
        padding-right: 82px;
    }

    .panel-header h3 {
        font-size: 13px;
        font-weight: 700;
        color: #ddd;
        margin: 0 0 2px;
    }

    .panel-header p {
        font-size: 11px;
        color: #555;
        margin: 0;
    }

    .panel-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: flex-end;
        position: absolute;
        top: 12px;
        right: 16px;
    }

    .panel-header-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px;
        border-radius: 7px;
        border: 1px solid rgba(139, 92, 246, 0.28);
        background: rgba(139, 92, 246, 0.12);
        color: #c4b5fd;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .panel-header-action:hover {
        background: rgba(139, 92, 246, 0.18);
        color: #ddd6fe;
    }

    .panel-header-action:disabled {
        opacity: 0.7;
        cursor: wait;
    }

    .panel-header-action.panel-header-action-danger {
        border-color: rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.12);
        color: #fca5a5;
    }

    .panel-header-action.panel-header-action-danger:hover {
        background: rgba(239, 68, 68, 0.18);
        color: #fecaca;
    }

    .panel-header-action[hidden] {
        display: none;
    }

    .panel-header-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        justify-content: center;
        position: relative;
    }

    .panel-header-icon-btn i {
        font-size: 11px;
        margin: 0;
    }

    .panel-header-icon-btn::after,
    .panel-header-icon-btn::before {
        position: absolute;
        right: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s ease, transform 0.15s ease;
        z-index: 20;
    }

    .panel-header-icon-btn::after {
        content: attr(data-tooltip);
        top: calc(100% + 10px);
        transform: translateY(4px);
        background: rgba(32, 38, 48, 0.96);
        color: #fff;
        font-size: 11px;
        line-height: 1.3;
        font-weight: 600;
        padding: 7px 10px;
        border-radius: 8px;
        white-space: normal;
        width: 96px;
        text-align: center;
        box-shadow: 0 10px 24px rgba(21, 31, 46, 0.18);
    }

    .panel-header-icon-btn::before {
        content: '';
        top: calc(100% + 4px);
        transform: translateY(4px);
        right: 10px;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid rgba(32, 38, 48, 0.96);
    }

    .panel-header-icon-btn:hover::after,
    .panel-header-icon-btn:hover::before,
    .panel-header-icon-btn:focus-visible::after,
    .panel-header-icon-btn:focus-visible::before {
        opacity: 1;
        transform: translateY(0);
    }

    .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    /* Panel form groups */
    .panel-form-group {
        margin-bottom: 14px;
    }

    .panel-form-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: #888;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .panel-form-group label i {
        font-size: 10px;
        color: #666;
    }

    .panel-input,
    .panel-textarea,
    .panel-select {
        width: 100%;
        background: #1e1e2e;
        border: 1.5px solid #2a2a3e;
        color: #ddd;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }

    .panel-input:focus,
    .panel-textarea:focus,
    .panel-select:focus {
        outline: none;
        border-color: #8B5CF6;
        background: #1a1a2e;
    }

    .panel-textarea {
        min-height: 70px;
        resize: vertical;
    }

    .panel-select option {
        background: #1e1e2e;
    }

    .panel-hint {
        font-size: 11px;
        color: #444;
        margin-top: 4px;
        line-height: 1.4;
    }

    .panel-divider {
        border: none;
        border-top: 1px solid #2a2a3e;
        margin: 16px 0;
    }

    /* Chapter info panel */
    .chapter-guide-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .chapter-guide-title {
        font-size: 12px;
        font-weight: 700;
        color: #A78BFA;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .chapter-guide-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .chapter-guide-list li {
        font-size: 12px;
        color: #888;
        padding: 3px 0;
        padding-left: 16px;
        position: relative;
    }

    .chapter-guide-list li::before {
        content: '•';
        position: absolute;
        left: 4px;
        color: #555;
    }

    /* Format specs display */
    .format-spec-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
    }

    .format-spec-card h4 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        margin: 0 0 10px;
    }

    .spec-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .spec-label {
        font-size: 12px;
        color: #666;
    }

    .spec-value {
        font-size: 12px;
        font-weight: 600;
        color: #A78BFA;
        background: rgba(139, 92, 246, 0.1);
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* Project selector */
    .project-selector-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .project-selector-card h4 {
        font-size: 12px;
        font-weight: 700;
        color: #aaa;
        margin: 0 0 10px;
    }

    .project-option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
        margin-bottom: 4px;
        border: 1.5px solid transparent;
    }

    .project-option-item:hover {
        background: rgba(255,255,255,0.04);
    }

    .project-option-item.selected {
        background: rgba(139, 92, 246, 0.1);
        border-color: rgba(139, 92, 246, 0.3);
    }

    .project-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .project-option-name {
        flex: 1;
        font-size: 12.5px;
        color: #ccc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-option-count {
        font-size: 11px;
        color: #555;
        white-space: nowrap;
    }

    .no-projects-hint {
        text-align: center;
        padding: 20px;
        color: #555;
        font-size: 12px;
    }

    .no-projects-hint a {
        color: #A78BFA;
    }

    .bib-loading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #555;
        padding: 10px 0;
        justify-content: center;
    }

    .bib-count-badge {
        display: inline-block;
        background: rgba(139, 92, 246, 0.15);
        color: #A78BFA;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        margin-left: 6px;
    }

    /* Loading spinner */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #333;
        border-top-color: #A78BFA;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    /* Bright document workspace theme */
    .builder-wrap {
        background: var(--builder-bg);
    }

    .builder-topbar {
        background: #ececec;
        border-bottom: 1px solid #d8d8d8;
        box-shadow: 0 14px 26px rgba(107, 114, 128, 0.14);
        backdrop-filter: blur(10px);
    }

    .topbar-back {
        color: #6b7280;
    }

    .topbar-back:hover {
        background: rgba(255,255,255,0.7);
        color: #1f2937;
    }

    .topbar-title {
        color: #1f2937;
    }

    .topbar-btn-docx {
        background: linear-gradient(135deg, #2b579a, #4c7bd9);
        color: #fff;
        box-shadow: 0 10px 20px rgba(43, 87, 154, 0.16);
    }

    .topbar-btn-docx:hover {
        filter: none;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(43, 87, 154, 0.18);
    }

    .builder-sidebar,
    .builder-panel {
        background: var(--builder-surface);
        border-color: var(--builder-border);
    }

    .sidebar-section-title,
    .sidebar-format-title,
    .preview-header-label,
    .panel-header p,
    .format-row label,
    .spec-label,
    .project-option-count,
    .panel-hint,
    .bib-loading,
    .no-projects-hint,
    .chapter-guide-list li::before {
        color: var(--builder-soft);
    }

    .section-nav-item:hover {
        background: #eeeeef;
    }

    .section-nav-item.active {
        background: var(--builder-accent-soft);
    }

    .section-nav-icon {
        background: #ebebec;
        color: var(--builder-muted);
    }

    .section-nav-item.active .section-nav-icon {
        background: #dce9ff;
        color: var(--builder-accent);
    }

    .section-nav-label {
        color: var(--builder-muted);
    }

    .section-nav-item.active .section-nav-label,
    .chapter-guide-title,
    .spec-value,
    .bib-count-badge,
    .no-projects-hint a {
        color: var(--builder-accent);
    }

    .sidebar-format {
        border-top: 1px solid var(--builder-border);
    }

    .format-row select,
    .panel-input,
    .panel-textarea,
    .panel-select {
        background: #fff;
        border-color: #d8deea;
        color: var(--builder-text);
    }

    .format-row select:focus,
    .panel-input:focus,
    .panel-textarea:focus,
    .panel-select:focus {
        background: #fff;
        border-color: var(--builder-accent);
        box-shadow: 0 0 0 3px rgba(43, 87, 154, 0.08);
    }

    .panel-select option {
        background: #fff;
        color: var(--builder-text);
    }

    .builder-preview {
        background: var(--builder-preview-bg);
    }

    .a4-paper {
        box-shadow: 0 18px 42px rgba(44, 58, 86, 0.12);
    }

    .page-break-hint {
        color: #8a94a4;
    }

    .page-break-hint::before,
    .page-break-hint::after {
        background: #c8d2df;
        border-top-color: #c8d2df;
    }

    .panel-header {
        border-bottom: 1px solid var(--builder-border);
    }

    .panel-header h3,
    .project-selector-card h4,
    .format-spec-card h4 {
        color: var(--builder-text);
    }

    .panel-header-action {
        border-color: #c8d8f0;
        background: #eef4ff;
        color: var(--builder-accent);
    }

    .panel-header-action:hover {
        background: #e2edff;
        color: #21457a;
    }

    .panel-header-action.panel-header-action-danger {
        border-color: #efc3c3;
        background: var(--builder-danger-soft);
        color: var(--builder-danger);
    }

    .panel-header-action.panel-header-action-danger:hover {
        background: #ffe7e7;
        color: #9f3131;
    }

    .panel-form-group label,
    .panel-form-group label i,
    .chapter-guide-list li,
    .spec-label,
    .project-option-name {
        color: var(--builder-muted);
    }

    .panel-divider {
        border-top-color: var(--builder-border);
    }

    .chapter-guide-card,
    .format-spec-card,
    .project-selector-card {
        background: var(--builder-surface-alt);
        border-color: var(--builder-border);
        box-shadow: 0 6px 20px rgba(112, 94, 70, 0.04);
    }

    .spec-value,
    .bib-count-badge {
        background: #e7eefb;
    }

    .project-option-item:hover {
        background: #f1f3f6;
    }

    .project-option-item.selected {
        background: #edf3ff;
        border-color: #c7d8fb;
    }

    .project-option-count,
    .no-projects-hint,
    .bib-loading {
        color: var(--builder-soft);
    }

    .spinner {
        border-color: #d2dae6;
        border-top-color: var(--builder-accent);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .builder-body {
            grid-template-columns: 200px 1fr 280px;
        }
        .a4-paper { width: 640px; }
        .page-break-hint { width: 640px; }
    }

    @media (max-width: 768px) {
        body { overflow: auto; }
        .builder-body {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
        }
        .builder-guest-banner {
            flex-direction: column;
        }
        .builder-sidebar {
            border-right: none;
            border-bottom: 1px solid var(--builder-border);
        }
        .section-nav-list {
            display: flex;
            flex-direction: row;
            gap: 4px;
            overflow-x: auto;
            padding: 8px;
        }
        .section-nav-item {
            flex-shrink: 0;
        }
        .a4-paper {
            width: 95%;
            padding: 40px 30px;
        }
        .page-break-hint { width: 95%; }
    }
</style>

<div class="builder-wrap">

    <!-- Top Bar -->
    <div class="builder-topbar">
        <div class="topbar-left">
            <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="topbar-back">
                <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars($builderText['back']); ?>
            </a>
            <span style="color: var(--builder-border-strong); font-size: 14px;">|</span>
            <span class="topbar-title"><?php echo htmlspecialchars($builderText['builderTitle']); ?></span>
            <span class="topbar-template-badge" id="template-badge">
                <i class="fas fa-file-lines"></i>
                <span id="template-badge-name"><?php echo htmlspecialchars($builderText['loading']); ?></span>
                <button type="button" class="topbar-template-switcher" id="template-switcher-toggle" onclick="toggleTemplateSwitcher(event)" aria-label="<?php echo htmlspecialchars($builderText['switchTemplate']); ?>" title="<?php echo htmlspecialchars($builderText['switchTemplate']); ?>">
                    <i class="fas fa-pen-to-square"></i>
                </button>
                <div class="topbar-template-menu" id="template-switcher-menu"></div>
            </span>
        </div>
        <div class="topbar-actions">
            <button class="topbar-btn topbar-btn-docx" onclick="exportReport('docx')" id="btn-docx">
                <i class="fas fa-file-word"></i> <?php echo htmlspecialchars($builderText['exportWord']); ?>
            </button>
        </div>
    </div>

    <?php if ($isGuestMode): ?>
        <div class="builder-guest-banner">
            <div class="builder-guest-banner-copy">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <strong><?php echo htmlspecialchars($builderText['guestBuilderTitle']); ?></strong>
                    <p><?php echo htmlspecialchars($builderText['guestBuilderDesc']); ?></p>
                </div>
            </div>
            <div class="builder-guest-actions">
                <a href="<?php echo SITE_URL; ?>/register.php" class="builder-guest-btn primary">
                    <i class="fas fa-user-plus"></i>
                    <?php echo htmlspecialchars($builderText['guestSignup']); ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/login.php" class="builder-guest-btn secondary">
                    <i class="fas fa-right-to-bracket"></i>
                    <?php echo htmlspecialchars($builderText['guestSignin']); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Builder Body -->
    <div class="builder-body">

        <!-- ===== LEFT: Section Navigation ===== -->
        <div class="builder-sidebar">
            <div class="sidebar-section-title"><?php echo htmlspecialchars($builderText['docStructure']); ?></div>
            <div class="section-nav-list" id="section-nav-list">
                <!-- Populated by JS -->
            </div>
            <div class="sidebar-format">
                <div class="sidebar-format-title"><?php echo htmlspecialchars($builderText['formatting']); ?></div>
                <div class="format-row">
                    <label><?php echo htmlspecialchars($builderText['documentFont']); ?></label>
                    <select id="setting-font" onchange="updateFormatSettings()">
                        <option value="Angsana New">Angsana New (<?php echo htmlspecialchars($builderText['fontStandard']); ?>)</option>
                        <option value="TH Sarabun New">TH Sarabun New</option>
                        <option value="TH Niramit AS">TH Niramit AS</option>
                        <option value="Times New Roman">Times New Roman</option>
                    </select>
                </div>
                <div class="format-row">
                    <label><?php echo htmlspecialchars($builderText['bodyFontSize']); ?></label>
                    <select id="setting-body-size" onchange="updateFormatSettings()">
                        <option value="14">14pt</option>
                        <option value="15">15pt</option>
                        <option value="16" selected>16pt (<?php echo htmlspecialchars($builderText['fontStandard']); ?>)</option>
                    </select>
                </div>
                <div class="format-row">
                    <label><?php echo htmlspecialchars($builderText['paperMargin']); ?></label>
                    <select id="setting-margin" onchange="updateFormatSettings()">
                        <option value="standard" selected><?php echo htmlspecialchars($builderText['marginStandard']); ?></option>
                        <option value="wide"><?php echo htmlspecialchars($builderText['marginWide']); ?></option>
                        <option value="narrow"><?php echo htmlspecialchars($builderText['marginNarrow']); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ===== CENTER: A4 Preview ===== -->
        <div class="builder-preview" id="builder-preview">
            <div class="preview-header-label">
                <i class="fas fa-eye"></i> <?php echo htmlspecialchars($builderText['previewA4']); ?>
            </div>
            <!-- Sections rendered by JS -->
            <div id="preview-pages"></div>
        </div>

        <!-- ===== RIGHT: Content Panel ===== -->
        <div class="builder-panel">
            <div class="panel-header">
                <div class="panel-header-copy">
                    <h3 id="panel-section-title"><?php echo htmlspecialchars($builderText['loading']); ?></h3>
                    <p id="panel-section-desc"><?php echo htmlspecialchars($builderText['panelLoadingDesc']); ?></p>
                </div>
                <div class="panel-header-actions">
                    <button type="button" class="panel-header-action panel-header-action-danger panel-header-icon-btn" id="panel-clear-draft-btn" onclick="clearDraftState()" data-tooltip="<?php echo htmlspecialchars($builderText['clearDraft']); ?>" title="<?php echo htmlspecialchars($builderText['clearDraft']); ?>" aria-label="<?php echo htmlspecialchars($builderText['clearDraft']); ?>">
                        <i class="fas fa-trash-can"></i>
                    </button>
                    <button type="button" class="panel-header-action panel-header-icon-btn" id="panel-autofill-btn" onclick="handleAutofillSample()" data-tooltip="<?php echo htmlspecialchars($builderText['autofill']); ?>" title="<?php echo htmlspecialchars($builderText['autofill']); ?>" aria-label="<?php echo htmlspecialchars($builderText['autofill']); ?>" hidden>
                        <i class="fas fa-shuffle"></i>
                    </button>
                </div>
            </div>
            <div class="panel-body" id="panel-body">
                <!-- Template-specific form loaded by JS -->
            </div>
        </div>

    </div>
</div>

<script>
const UI_TEXT = <?php echo json_encode($builderText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const IS_ENGLISH = <?php echo $isEnglish ? 'true' : 'false'; ?>;
const PROJECTS = <?php echo json_encode($userProjects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
// ======================================================
//  Template Definitions
// ======================================================
const TEMPLATE_DEFS = <?php echo json_encode($templateDefsLocalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

// Current template
const CURRENT_USER_ID = <?php echo json_encode((string) $userId); ?>;
const IS_GUEST_MODE = <?php echo $isGuestMode ? 'true' : 'false'; ?>;
const CAN_PERSIST_DRAFT = <?php echo $isGuestMode ? 'false' : 'true'; ?>;
const REPORT_BUILDER_BASE_URL = <?php echo json_encode(SITE_URL . '/users/report-builder.php'); ?>;
const DEFAULT_TEMPLATE_LOGO_URL = <?php echo json_encode(SITE_URL . '/assets/images/Chiang_Mai_University.svg.png'); ?>;
const templateId = <?php echo json_encode($templateId); ?>;
const template = TEMPLATE_DEFS[templateId];
const TEMPLATE_SWITCHER_IDS = ['academic_general', 'academic_general_logo', 'research', 'internship', 'thesis_master'];
const REPORT_DRAFT_STORAGE_PREFIX = 'babybib-report-draft-v1';
let activeSection = 'cover';
let selectedProjectId = null;
let loadedBibliographies = [];
let isAutofillingSample = false;
let draftSaveTimer = null;

// Cover data
function getDefaultCoverData() {
    return {
        title: '',
        authors: '',
        studentIds: '',
        course: '',
        courseCode: '',
        instructor: '',
        department: '',
        institution: '',
        company: '',
        supervisor: '',
        projectType: '',
        internshipPeriod: '',
        degree: '',
        major: '',
        committee: '',
        prefaceContent: '',
        prefaceSigner: '',
        prefaceDate: '',
        semester: '1',
        year: '<?php echo (date('Y') + 543); ?>',
        logoDataUrl: '',
        logoFileName: ''
    };
}

let coverData = getDefaultCoverData();

let formatSettings = getDefaultFormatSettings();

// ======================================================
//  INIT
// ======================================================
document.addEventListener('DOMContentLoaded', function() {
    initBuilder();
});

function getDraftStorageKey() {
    return `${REPORT_DRAFT_STORAGE_PREFIX}:${CURRENT_USER_ID}:${templateId}`;
}

function getDefaultFormatSettings() {
    return {
        font: 'Angsana New',
        bodySize: 16,
        margin: 'standard'
    };
}

function scheduleDraftSave() {
    if (!CAN_PERSIST_DRAFT) return;
    window.clearTimeout(draftSaveTimer);
    draftSaveTimer = window.setTimeout(saveDraftState, 250);
}

function saveDraftState() {
    if (!CAN_PERSIST_DRAFT) return;
    try {
        const payload = {
            activeSection,
            selectedProjectId,
            loadedBibliographies,
            coverData,
            formatSettings,
            savedAt: new Date().toISOString()
        };
        window.localStorage.setItem(getDraftStorageKey(), JSON.stringify(payload));
    } catch (error) {
        console.warn('Unable to save report draft', error);
    }
}

function restoreDraftState() {
    if (!CAN_PERSIST_DRAFT) return;
    try {
        const rawDraft = window.localStorage.getItem(getDraftStorageKey());
        if (!rawDraft) return;

        const draft = JSON.parse(rawDraft);
        if (!draft || typeof draft !== 'object') return;

        if (draft.coverData && typeof draft.coverData === 'object') {
            coverData = {
                ...coverData,
                ...draft.coverData
            };
        }

        if (draft.formatSettings && typeof draft.formatSettings === 'object') {
            const defaults = getDefaultFormatSettings();
            formatSettings = {
                ...defaults,
                ...draft.formatSettings,
                bodySize: Number.isFinite(Number(draft.formatSettings.bodySize)) ? Number(draft.formatSettings.bodySize) : defaults.bodySize
            };
        }

        if (typeof draft.activeSection === 'string' && template.sections.some(section => section.id === draft.activeSection)) {
            activeSection = draft.activeSection;
        }

        const savedProjectId = Number(draft.selectedProjectId);
        if (Number.isInteger(savedProjectId) && PROJECTS.some(project => Number(project.id) === savedProjectId)) {
            selectedProjectId = savedProjectId;
        }

        if (Array.isArray(draft.loadedBibliographies)) {
            loadedBibliographies = draft.loadedBibliographies;
        }
    } catch (error) {
        console.warn('Unable to restore report draft', error);
    }
}

function syncFormatControls() {
    const fontControl = document.getElementById('setting-font');
    const bodySizeControl = document.getElementById('setting-body-size');
    const marginControl = document.getElementById('setting-margin');

    if (fontControl) fontControl.value = formatSettings.font;
    if (bodySizeControl) bodySizeControl.value = String(formatSettings.bodySize);
    if (marginControl) marginControl.value = formatSettings.margin;
}

function initBuilder() {
    // Measure actual navbar height to prevent scroll bleed
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        document.documentElement.style.setProperty('--nav-height', navbar.offsetHeight + 'px');
    }

    // Set badge
    const badge = document.getElementById('template-badge');
    badge.style.background = template.color + '22';
    badge.style.border = '1px solid ' + template.color + '55';
    badge.style.color = template.color;
    document.getElementById('template-badge-name').textContent = template.name;
    populateTemplateSwitcherMenu();

    restoreDraftState();
    syncFormatControls();

    // Build section nav
    buildSectionNav();

    // Show first section
    selectSection(activeSection);

    // Restore bibliography list from cache first, then refresh from server if possible.
    if (selectedProjectId) {
        fetchProjectBibliographies(selectedProjectId, { showLoading: loadedBibliographies.length === 0 });
    }

    updateFormatSettings();

    // Observe scroll in preview — update nav + panel when a page enters view
    initScrollObserver();
}

function populateTemplateSwitcherMenu() {
    const menu = document.getElementById('template-switcher-menu');
    if (!menu) return;

    menu.innerHTML = TEMPLATE_SWITCHER_IDS
        .map((id) => [id, TEMPLATE_DEFS[id]])
        .filter(([, def]) => Boolean(def))
        .map(([id, def]) => `
        <button type="button" class="topbar-template-option${id === templateId ? ' active' : ''}" data-template-id="${id}" onclick="switchTemplateReport(this.dataset.templateId)">
            <span class="topbar-template-option-icon" style="background:${escHtmlAttr(def.gradient || def.color)}">
                <i class="fas ${escHtmlAttr(def.icon)}"></i>
            </span>
            <span class="topbar-template-option-label">${escHtml(def.name)}</span>
        </button>
    `).join('');
}

function toggleTemplateSwitcher(event) {
    event.preventDefault();
    event.stopPropagation();

    const menu = document.getElementById('template-switcher-menu');
    if (!menu) return;
    menu.classList.toggle('open');
}

function switchTemplateReport(nextTemplateId) {
    if (!nextTemplateId || nextTemplateId === templateId) {
        const menu = document.getElementById('template-switcher-menu');
        if (menu) menu.classList.remove('open');
        return;
    }

    window.location.href = `${REPORT_BUILDER_BASE_URL}?template=${encodeURIComponent(nextTemplateId)}`;
}

document.addEventListener('click', function(event) {
    const menu = document.getElementById('template-switcher-menu');
    const toggle = document.getElementById('template-switcher-toggle');
    if (!menu || !toggle) return;
    if (menu.contains(event.target) || toggle.contains(event.target)) return;
    menu.classList.remove('open');
});

// ======================================================
//  SCROLL OBSERVER: เลื่อนถึงหน้าไหน → ซ้าย/ขวาเปลี่ยน
// ======================================================
let _scrollObserver = null;
let _isClickScrolling = false; // ป้องกัน observer ยิงตอนคลิก nav

function initScrollObserver() {
    if (_scrollObserver) _scrollObserver.disconnect();

    const previewEl = document.getElementById('builder-preview');
    if (!previewEl) return;

    _scrollObserver = new IntersectionObserver((entries) => {
        if (_isClickScrolling) return;

        // หาหน้าที่มองเห็นมากที่สุด (intersectionRatio สูงสุด)
        let best = null;
        entries.forEach(entry => {
            if (!best || entry.intersectionRatio > best.intersectionRatio) {
                best = entry;
            }
        });

        if (best && best.isIntersecting) {
            const sectionId = best.target.id.replace('preview-', '');
            const section = template.sections.find(s => s.id === sectionId);
            if (section && sectionId !== activeSection) {
                activeSection = sectionId;

                // อัปเดต nav ซ้าย
                document.querySelectorAll('.section-nav-item').forEach(el => {
                    el.classList.toggle('active', el.dataset.sectionId === sectionId);
                });
                // เลื่อน nav ให้ item ปัจจุบันอยู่ในวิว
                const navItem = document.querySelector(`.section-nav-item[data-section-id="${sectionId}"]`);
                if (navItem) navItem.scrollIntoView({ block: 'nearest' });

                // อัปเดต panel ขวา
                renderPanel(section);
            }
        }
    }, {
        root: previewEl,
        threshold: [0.1, 0.3, 0.5, 0.7]
    });

    // Observe ทุก a4-paper
    document.querySelectorAll('.a4-paper').forEach(el => {
        _scrollObserver.observe(el);
    });
}

function buildSectionNav() {
    const list = document.getElementById('section-nav-list');
    list.innerHTML = '';

    template.sections.forEach(section => {
        const item = document.createElement('div');
        item.className = 'section-nav-item' + (section.id === activeSection ? ' active' : '');
        item.dataset.sectionId = section.id;
        item.onclick = () => selectSection(section.id);
        item.innerHTML = `
            <div class="section-nav-icon">
                <i class="fas ${section.icon}"></i>
            </div>
            <span class="section-nav-label">${section.label}</span>
        `;
        list.appendChild(item);
    });
}

function selectSection(sectionId) {
    activeSection = sectionId;

    // Update active nav item
    document.querySelectorAll('.section-nav-item').forEach(el => {
        el.classList.toggle('active', el.dataset.sectionId === sectionId);
    });

    // Find section definition
    const section = template.sections.find(s => s.id === sectionId);
    if (!section) return;

    // Render panel
    renderPanel(section);

    // Render preview
    renderAllPreviews();

    // Re-attach observer to newly rendered pages
    initScrollObserver();

    // Scroll preview to this section (flag prevents observer from interfering)
    _isClickScrolling = true;
    setTimeout(() => {
        const el = document.getElementById('preview-' + sectionId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        // Clear flag after scroll animation settles
        setTimeout(() => { _isClickScrolling = false; }, 800);
    }, 100);

    scheduleDraftSave();
}

// ======================================================
//  PANEL RENDERING
// ======================================================
function renderPanel(section) {
    const panelTitle = document.getElementById('panel-section-title');
    const panelDesc = document.getElementById('panel-section-desc');
    const panelBody = document.getElementById('panel-body');
    const panelAutofillBtn = document.getElementById('panel-autofill-btn');
    const panelClearDraftBtn = document.getElementById('panel-clear-draft-btn');

    if (panelAutofillBtn) {
        const showAutofill = ['academic_general', 'academic_general_logo'].includes(templateId);
        panelAutofillBtn.hidden = !showAutofill;
        panelAutofillBtn.disabled = isAutofillingSample;
        updateAutofillButton();
    }

    if (panelClearDraftBtn) {
        panelClearDraftBtn.hidden = !CAN_PERSIST_DRAFT;
    }

    switch (section.type) {
        case 'cover':
            panelTitle.textContent = UI_TEXT.coverTitle;
            panelDesc.textContent = UI_TEXT.coverDesc;
            renderCoverPanel(panelBody);
            break;
        case 'inner_cover':
            panelTitle.textContent = UI_TEXT.innerCoverTitle;
            panelDesc.textContent = UI_TEXT.innerCoverDesc;
            renderCoverPanel(panelBody);
            break;
        case 'chapter':
            panelTitle.textContent = section.label;
            panelDesc.textContent = UI_TEXT.chapterDesc;
            renderChapterPanel(panelBody, section);
            break;
        case 'toc':
            panelTitle.textContent = UI_TEXT.tocTitle;
            panelDesc.textContent = UI_TEXT.tocDesc;
            renderTocPanel(panelBody);
            break;
        case 'abstract':
            panelTitle.textContent = UI_TEXT.abstractTitle;
            panelDesc.textContent = UI_TEXT.abstractDesc;
            renderAbstractPanel(panelBody, section);
            break;
        case 'acknowledgment':
            panelTitle.textContent = UI_TEXT.ackTitle;
            panelDesc.textContent = UI_TEXT.ackDesc;
            renderAcknowledgmentPanel(panelBody);
            break;
        case 'bibliography':
            panelTitle.textContent = UI_TEXT.bibTitle;
            panelDesc.textContent = UI_TEXT.bibDesc;
            renderBibliographyPanel(panelBody);
            break;
        case 'appendix':
            panelTitle.textContent = UI_TEXT.appendixTitle;
            panelDesc.textContent = UI_TEXT.appendixDesc;
            renderAppendixPanel(panelBody);
            break;
        case 'preface':
            panelTitle.textContent = UI_TEXT.prefaceTitle;
            panelDesc.textContent = UI_TEXT.prefaceDesc;
            renderPrefacePanel(panelBody);
            break;
        case 'approval':
            panelTitle.textContent = UI_TEXT.approvalTitle;
            panelDesc.textContent = UI_TEXT.approvalDesc;
            renderApprovalPanel(panelBody);
            break;
        case 'biography':
            panelTitle.textContent = UI_TEXT.biographyTitle;
            panelDesc.textContent = UI_TEXT.biographyDesc;
            renderBiographyPanel(panelBody);
            break;
        default:
            panelTitle.textContent = section.label;
            panelDesc.textContent = '';
            panelBody.innerHTML = `<p style="color:#555; font-size:12px; padding:10px 0;">${UI_TEXT.autoGeneratedExport}</p>`;
    }
}

// Cover panel
function renderCoverPanel(container) {
    let coverFields = '';
    const type = template.coverType;

    if (template.showLogo) {
        coverFields += formGroup(UI_TEXT.coverFieldLogo, 'fa-image',
            `<div class="logo-upload-panel">
                <div class="logo-upload-preview" id="logo-upload-preview"></div>
                <div class="logo-upload-actions">
                    <label class="logo-upload-btn" for="cv-logo-upload">
                        <i class="fas fa-upload"></i>
                        <span>${UI_TEXT.coverFieldLogoUpload}</span>
                    </label>
                    <input id="cv-logo-upload" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" hidden onchange="handleLogoUpload(this)">
                    <button type="button" class="logo-upload-reset" onclick="resetLogoToDefault()">
                        <i class="fas fa-rotate-left"></i>
                        <span>${UI_TEXT.coverFieldLogoReset}</span>
                    </button>
                </div>
                <div class="logo-upload-hint">${UI_TEXT.coverFieldLogoHint}<br><span>${UI_TEXT.coverFieldLogoFileHelp}</span></div>
            </div>`);
    }

    // Common fields
    coverFields += formGroup(UI_TEXT.coverFieldTitle, 'fa-heading',
        `<textarea class="panel-textarea" id="cv-title" placeholder="${escHtmlAttr(UI_TEXT.coverFieldTitlePlaceholder)}" rows="3" oninput="coverData.title=this.value; updateCoverPreview()">${escHtml(coverData.title)}</textarea>`);

    coverFields += formGroup(UI_TEXT.coverFieldAuthors, 'fa-user',
        `<textarea class="panel-textarea" id="cv-authors" placeholder="${escHtmlAttr(UI_TEXT.coverFieldAuthorsPlaceholder).replace(/\n/g, '&#10;')}" rows="3" oninput="coverData.authors=this.value; updateCoverPreview()">${escHtml(coverData.authors)}</textarea>`);

    if (type !== 'thesis') {
        coverFields += formGroup(UI_TEXT.coverFieldStudentIds, 'fa-id-badge',
            `<textarea class="panel-textarea" id="cv-ids" placeholder="${escHtmlAttr(UI_TEXT.coverFieldStudentIdsPlaceholder).replace(/\n/g, '&#10;')}" rows="2" oninput="coverData.studentIds=this.value; updateCoverPreview()">${escHtml(coverData.studentIds)}</textarea>`);
    }

    if (type === 'internship') {
        coverFields += formGroup(UI_TEXT.coverFieldCompany, 'fa-building',
            `<input class="panel-input" id="cv-company" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldCompanyPlaceholder)}" value="${escHtml(coverData.company)}" oninput="coverData.company=this.value; updateCoverPreview()">`);
        coverFields += formGroup(UI_TEXT.coverFieldSupervisor, 'fa-user-tie',
            `<input class="panel-input" id="cv-supervisor" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldSupervisorPlaceholder)}" value="${escHtml(coverData.supervisor)}" oninput="coverData.supervisor=this.value; updateCoverPreview()">`);
        coverFields += formGroup(UI_TEXT.coverFieldPeriod, 'fa-calendar',
            `<input class="panel-input" id="cv-period" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldPeriodPlaceholder)}" value="${escHtml(coverData.internshipPeriod)}" oninput="coverData.internshipPeriod=this.value; updateCoverPreview()">`);
    }

    if (type === 'project') {
        coverFields += formGroup(UI_TEXT.coverFieldProjectType, 'fa-tag',
            `<input class="panel-input" id="cv-projtype" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldProjectTypePlaceholder)}" value="${escHtml(coverData.projectType)}" oninput="coverData.projectType=this.value; updateCoverPreview()">`);
    }

    if (type !== 'internship') {
        const courseLabel = type === 'thesis' ? UI_TEXT.coverFieldMajor : UI_TEXT.coverFieldCourse;
        coverFields += formGroup(courseLabel, 'fa-book',
            `<input class="panel-input" id="cv-course" type="text" placeholder="${escHtmlAttr(type === 'thesis' ? UI_TEXT.coverFieldMajorPlaceholder : UI_TEXT.coverFieldCoursePlaceholder)}" value="${escHtml(coverData.course)}" oninput="coverData.course=this.value; updateCoverPreview()">`);
    }

    if (type === 'thesis') {
        coverFields += formGroup(UI_TEXT.coverFieldDegree, 'fa-graduation-cap',
            `<input class="panel-input" id="cv-degree" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldDegreePlaceholder)}" value="${escHtml(coverData.degree)}" oninput="coverData.degree=this.value; updateCoverPreview()">`);
        coverFields += formGroup(UI_TEXT.coverFieldCommittee, 'fa-users',
            `<textarea class="panel-textarea" id="cv-committee" placeholder="${escHtmlAttr(UI_TEXT.coverFieldCommitteePlaceholder).replace(/\n/g, '&#10;')}" rows="3" oninput="coverData.committee=this.value; updateCoverPreview()">${escHtml(coverData.committee)}</textarea>`);
    } else if (type !== 'academic') {
        coverFields += formGroup(UI_TEXT.coverFieldInstructor, 'fa-user-graduate',
            `<input class="panel-input" id="cv-instructor" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldInstructorPlaceholder)}" value="${escHtml(coverData.instructor)}" oninput="coverData.instructor=this.value; updateCoverPreview()">`);
    }

    coverFields += formGroup(UI_TEXT.coverFieldDepartment, 'fa-landmark',
        `<input class="panel-input" id="cv-dept" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldDepartmentPlaceholder)}" value="${escHtml(coverData.department)}" oninput="coverData.department=this.value; updateCoverPreview()">`);

    coverFields += formGroup(UI_TEXT.coverFieldInstitution, 'fa-university',
        `<input class="panel-input" id="cv-inst" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldInstitutionPlaceholder)}" value="${escHtml(coverData.institution)}" oninput="coverData.institution=this.value; updateCoverPreview()">`);

    if (type !== 'thesis') {
        coverFields += `<div class="panel-form-group">
            <label><i class="fas fa-calendar-alt"></i> ${UI_TEXT.coverFieldSemesterYear}</label>
            <div style="display:flex; gap:8px;">
                <select class="panel-select" id="cv-semester" style="flex:1" onchange="coverData.semester=this.value; updateCoverPreview()">
                    <option value="1" ${coverData.semester==='1'?'selected':''}>${UI_TEXT.semester1}</option>
                    <option value="2" ${coverData.semester==='2'?'selected':''}>${UI_TEXT.semester2}</option>
                    <option value="3" ${coverData.semester==='3'?'selected':''}>${UI_TEXT.semester3}</option>
                </select>
                <input class="panel-input" id="cv-year" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldYearPlaceholder)}" style="width:80px;"
                    value="${escHtml(coverData.year)}" oninput="coverData.year=this.value; updateCoverPreview()">
            </div>
        </div>`;
    } else {
        coverFields += formGroup(UI_TEXT.coverFieldStudyYear, 'fa-calendar',
            `<input class="panel-input" id="cv-year" type="text" placeholder="${escHtmlAttr(UI_TEXT.coverFieldYearPlaceholder)}" value="${escHtml(coverData.year)}" oninput="coverData.year=this.value; updateCoverPreview()">`);
    }

    container.innerHTML = coverFields;

    if (template.showLogo) {
        renderLogoUploadState();
    }
}

function updateAutofillButton() {
    const panelAutofillBtn = document.getElementById('panel-autofill-btn');
    if (!panelAutofillBtn) return;

    panelAutofillBtn.disabled = isAutofillingSample;
    const tooltipText = isAutofillingSample ? UI_TEXT.autofillLoading : UI_TEXT.autofill;
    panelAutofillBtn.setAttribute('data-tooltip', tooltipText);
    panelAutofillBtn.setAttribute('title', tooltipText);
    panelAutofillBtn.setAttribute('aria-label', tooltipText);
    panelAutofillBtn.innerHTML = isAutofillingSample
        ? `<i class="fas fa-spinner fa-spin"></i>`
        : `<i class="fas fa-shuffle"></i>`;
}

function clearDraftState() {
    if (!CAN_PERSIST_DRAFT) {
        return;
    }

    const runClearDraft = () => {
        window.clearTimeout(draftSaveTimer);

        try {
            window.localStorage.removeItem(getDraftStorageKey());
        } catch (error) {
            console.warn('Unable to clear report draft', error);
        }

        activeSection = 'cover';
        selectedProjectId = null;
        loadedBibliographies = [];
        coverData = getDefaultCoverData();
        formatSettings = getDefaultFormatSettings();

        syncFormatControls();
        buildSectionNav();
        selectSection('cover');
        updateFormatSettings();

        if (typeof Toast !== 'undefined' && Toast.success) {
            Toast.success(UI_TEXT.clearDraftSuccess);
        }
    };

    if (typeof Modal !== 'undefined' && typeof Modal.confirm === 'function') {
        Modal.confirm({
            title: UI_TEXT.clearDraft,
            message: UI_TEXT.clearDraftConfirm,
            confirmText: UI_TEXT.clearDraftDelete,
            cancelText: UI_TEXT.clearDraftCancel,
            danger: true,
            onConfirm: runClearDraft
        });
        return;
    }

    if (window.confirm(UI_TEXT.clearDraftConfirm)) {
        runClearDraft();
    }
}

function handleAutofillSample() {
    if (!['academic_general', 'academic_general_logo'].includes(templateId) || isAutofillingSample) {
        return;
    }

    isAutofillingSample = true;
    updateAutofillButton();

    window.setTimeout(() => {
        applyAcademicCoverSample();
        isAutofillingSample = false;
        updateAutofillButton();
    }, 2000);
}

function pickRandom(list) {
    return list[Math.floor(Math.random() * list.length)];
}

function applyAcademicCoverSample() {
    const thaiSamples = [
        {
            title: 'ผลกระทบของแพลตฟอร์มการเรียนออนไลน์\nต่อพฤติกรรมการเรียนรู้ของนักศึกษา',
            authors: 'นางสาวปาณิสรา วัฒนชัย',
            studentIds: '651234501',
            course: 'รายงานกระบวนวิชาการรู้สารสนเทศและการนำเสนอสารสนเทศ',
            department: 'ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์',
            institution: 'คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่',
            prefaceSigner: 'ปาณิสรา วัฒนชัย',
            prefaceDate: '18 สิงหาคม 2567',
            prefaceContent: 'รายงานเรื่องผลกระทบของแพลตฟอร์มการเรียนออนไลน์ต่อพฤติกรรมการเรียนรู้ของนักศึกษานี้จัดทำขึ้นเพื่อศึกษาการปรับตัวของผู้เรียนในบริบทการเรียนรู้แบบดิจิทัล โดยมุ่งวิเคราะห์รูปแบบการใช้แพลตฟอร์ม ความต่อเนื่องในการเรียน และปัจจัยที่ส่งผลต่อประสิทธิภาพในการเรียนรู้\n\nผู้จัดทำได้รวบรวมข้อมูลจากเอกสารวิชาการ งานวิจัย และแหล่งสารสนเทศที่เชื่อถือได้ เพื่อสังเคราะห์สาระสำคัญให้สอดคล้องกับกรอบการศึกษาทางวิชาการ และเป็นประโยชน์ต่อการทำความเข้าใจแนวโน้มการเรียนรู้ร่วมสมัย\n\nผู้จัดทำขอขอบคุณอาจารย์ผู้สอนและผู้เกี่ยวข้องทุกท่านที่ให้คำแนะนำและสนับสนุนการจัดทำรายงานฉบับนี้จนสำเร็จลุล่วง'
        },
        {
            title: 'การวิเคราะห์พฤติกรรมการใช้สื่อสังคมออนไลน์\nเพื่อการสื่อสารทางวิชาการของนักศึกษา',
            authors: 'นายธีรภัทร ศรีสกุล',
            studentIds: '651245778',
            course: 'รายงานกระบวนวิชาการรู้สารสนเทศและการนำเสนอสารสนเทศ',
            department: 'ภาควิชาสื่อสารมวลชน',
            institution: 'คณะการสื่อสารมวลชน มหาวิทยาลัยเชียงใหม่',
            prefaceSigner: 'ธีรภัทร ศรีสกุล',
            prefaceDate: '7 กันยายน 2567',
            prefaceContent: 'รายงานฉบับนี้มุ่งศึกษาพฤติกรรมการใช้สื่อสังคมออนไลน์เพื่อการสื่อสารทางวิชาการของนักศึกษา โดยให้ความสำคัญกับการค้นคว้า การแลกเปลี่ยนองค์ความรู้ และการสร้างเครือข่ายการเรียนรู้ผ่านสื่อดิจิทัล\n\nเนื้อหาในรายงานได้รับการเรียบเรียงจากเอกสารวิชาการและแหล่งข้อมูลที่ผ่านการคัดกรองอย่างเหมาะสม เพื่อสะท้อนประเด็นสำคัญเกี่ยวกับบทบาทของสื่อสังคมออนไลน์ในบริบทการศึกษา\n\nผู้จัดทำหวังว่ารายงานฉบับนี้จะเป็นประโยชน์ต่อการศึกษาค้นคว้าและการประยุกต์ใช้สื่อดิจิทัลเพื่อการเรียนรู้อย่างมีประสิทธิภาพ'
        },
        {
            title: 'แนวทางการจัดการขยะอาหารในโรงอาหารมหาวิทยาลัย\nเพื่อความยั่งยืนของชุมชนการศึกษา',
            authors: 'นางสาวชลธิชา พิพัฒน์กุล',
            studentIds: '651267190',
            course: 'รายงานวิชาการเพื่อการศึกษาทั่วไป',
            department: 'ภาควิชาสิ่งแวดล้อม',
            institution: 'คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่',
            prefaceSigner: 'ชลธิชา พิพัฒน์กุล',
            prefaceDate: '22 กรกฎาคม 2567',
            prefaceContent: 'รายงานเรื่องแนวทางการจัดการขยะอาหารในโรงอาหารมหาวิทยาลัยเพื่อความยั่งยืนของชุมชนการศึกษานี้จัดทำขึ้นเพื่อศึกษาสถานการณ์ปัญหา สาเหตุ และแนวทางในการลดปริมาณขยะอาหารภายในมหาวิทยาลัย\n\nผู้จัดทำได้ศึกษาข้อมูลจากเอกสาร งานวิจัย และกรณีศึกษาที่เกี่ยวข้อง เพื่อสังเคราะห์แนวทางที่สามารถนำไปประยุกต์ใช้ได้จริงในบริบทของสถาบันการศึกษา\n\nผู้จัดทำขอขอบคุณอาจารย์และแหล่งข้อมูลต่างๆ ที่มีส่วนช่วยให้รายงานฉบับนี้มีความสมบูรณ์และเป็นประโยชน์ต่อผู้อ่าน'
        },
        {
            title: 'บทบาทของปัญญาประดิษฐ์\nต่อการพัฒนางานบริการสารสนเทศสมัยใหม่',
            authors: 'นายณัฐวุฒิ วรรณประเสริฐ',
            studentIds: '651289443',
            course: 'เทคโนโลยีสารสนเทศเพื่อการจัดการความรู้',
            department: 'ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์',
            institution: 'คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่',
            prefaceSigner: 'ณัฐวุฒิ วรรณประเสริฐ',
            prefaceDate: '3 ตุลาคม 2567',
            prefaceContent: 'รายงานฉบับนี้ศึกษาบทบาทของปัญญาประดิษฐ์ต่อการพัฒนางานบริการสารสนเทศสมัยใหม่ โดยพิจารณาทั้งด้านการสืบค้น การจัดหมวดหมู่ข้อมูล และการให้บริการเชิงตอบสนองแก่ผู้ใช้\n\nผู้จัดทำมุ่งนำเสนอประเด็นทางวิชาการที่เชื่อมโยงเทคโนโลยีกับการจัดการสารสนเทศ เพื่อสะท้อนแนวโน้มการเปลี่ยนแปลงของงานบริการในยุคดิจิทัล\n\nผู้จัดทำหวังว่ารายงานฉบับนี้จะช่วยส่งเสริมความเข้าใจเกี่ยวกับการประยุกต์ใช้ปัญญาประดิษฐ์ในบริบทห้องสมุดและศูนย์สารสนเทศ'
        }
    ];

    const englishSamples = [
        {
            title: 'The Role of Artificial Intelligence\nin Modern Information Services',
            authors: 'Pimchanok Srisuk',
            studentIds: '660410112',
            course: 'Information Literacy and Information Presentation',
            department: 'Department of Library and Information Science',
            institution: 'Faculty of Humanities, Chiang Mai University',
            prefaceSigner: 'Pimchanok Srisuk',
            prefaceDate: '18 August 2024',
            prefaceContent: 'This report examines the role of artificial intelligence in modern information services, with emphasis on discovery tools, metadata support, and user-centered digital assistance. The study aims to present a broad academic perspective on how intelligent systems are reshaping access to information.\n\nThe content has been compiled from scholarly publications, academic articles, and reliable digital sources in order to provide a concise yet meaningful overview of the topic.\n\nThe author would like to express sincere appreciation to the course instructor and all supporting sources that contributed to the completion of this report.'
        },
        {
            title: 'Online Learning Platforms\nand Student Learning Behavior',
            authors: 'Thanawat Kittipong',
            studentIds: '660410245',
            course: 'Information Literacy and Information Presentation',
            department: 'Department of Educational Technology',
            institution: 'Faculty of Education, Chiang Mai University',
            prefaceSigner: 'Thanawat Kittipong',
            prefaceDate: '7 September 2024',
            prefaceContent: 'This report explores the relationship between online learning platforms and student learning behavior. The discussion focuses on participation patterns, self-directed learning, and the factors that influence effective engagement in digital learning environments.\n\nRelevant academic literature and research-based materials were reviewed to support the discussion and to frame the topic within a formal academic context.\n\nThe author gratefully acknowledges the guidance of the course instructor and the assistance of all sources used in preparing this report.'
        },
        {
            title: 'Food Waste Management in University Cafeterias\nfor a Sustainable Campus Community',
            authors: 'Nicha Wongsa',
            studentIds: '660410378',
            course: 'Academic Writing for General Education',
            department: 'Department of Environmental Science',
            institution: 'Faculty of Science, Chiang Mai University',
            prefaceSigner: 'Nicha Wongsa',
            prefaceDate: '3 October 2024',
            prefaceContent: 'This report investigates food waste management in university cafeterias as part of a broader effort to promote sustainability within campus communities. It highlights the causes of food waste, institutional challenges, and practical management approaches.\n\nThe report was developed through a review of academic documents, case studies, and reliable reference materials in order to present a balanced and informative discussion.\n\nThe author hopes that this report will serve as a useful reference for further study and for sustainable initiatives in educational settings.'
        }
    ];

    const sample = pickRandom(IS_ENGLISH ? englishSamples : thaiSamples);

    coverData.title = sample.title;
    coverData.authors = sample.authors;
    coverData.studentIds = sample.studentIds;
    coverData.course = sample.course;
    coverData.department = sample.department;
    coverData.institution = sample.institution;
    coverData.prefaceContent = sample.prefaceContent;
    coverData.prefaceSigner = sample.prefaceSigner;
    coverData.prefaceDate = sample.prefaceDate;

    const active = template.sections.find(s => s.id === activeSection) || template.sections.find(s => s.id === 'cover');
    if (active) {
        renderPanel(active);
    }

    updateCoverPreview();
    renderAllPreviews();
    scheduleDraftSave();
}

function formGroup(label, icon, input) {
    return `<div class="panel-form-group">
        <label><i class="fas ${icon}"></i> ${label}</label>
        ${input}
    </div>`;
}

function getResolvedLogoSrc() {
    if (!template.showLogo) return '';
    return coverData.logoDataUrl || template.defaultLogoUrl || DEFAULT_TEMPLATE_LOGO_URL;
}

function renderLogoUploadState() {
    const preview = document.getElementById('logo-upload-preview');
    if (!preview || !template.showLogo) return;

    const usingCustomLogo = Boolean(coverData.logoDataUrl);
    const logoSrc = getResolvedLogoSrc();
    const badgeText = usingCustomLogo ? UI_TEXT.coverFieldLogoUploadedBadge : UI_TEXT.coverFieldLogoDefaultBadge;
    const fileText = usingCustomLogo
        ? (coverData.logoFileName || UI_TEXT.coverFieldLogoUploadedBadge)
        : 'assets/images/Chiang_Mai_University.svg.png';

    preview.innerHTML = `
        <img src="${escHtmlAttr(logoSrc)}" alt="${escHtmlAttr(UI_TEXT.coverFieldLogoAlt)}">
        <div class="logo-upload-meta">
            <div class="logo-upload-title">${UI_TEXT.coverFieldLogo}</div>
            <span class="logo-upload-badge">${badgeText}</span>
            <div class="logo-upload-filename">${escHtml(fileText)}</div>
        </div>`;
}

function handleLogoUpload(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    if (!/^image\/(png|jpe?g|webp)$/i.test(file.type)) {
        if (typeof Toast !== 'undefined' && Toast.error) {
            Toast.error(UI_TEXT.coverFieldLogoInvalid);
        } else {
            window.alert(UI_TEXT.coverFieldLogoInvalid);
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
        coverData.logoDataUrl = String(event.target?.result || '');
        coverData.logoFileName = file.name;
        updateCoverPreview();
        renderLogoUploadState();
    };
    reader.readAsDataURL(file);
    input.value = '';
}

function resetLogoToDefault() {
    coverData.logoDataUrl = '';
    coverData.logoFileName = '';
    updateCoverPreview();
    renderLogoUploadState();
}

// Chapter panel
function renderChapterPanel(container, section) {
    let html = `<div class="chapter-guide-card">
        <div class="chapter-guide-title"><i class="fas fa-list-check"></i> ${UI_TEXT.chapterGuideTitle}</div>
        <ul class="chapter-guide-list">`;
    section.subsections.forEach(sub => {
        html += `<li>${sub}</li>`;
    });
    html += `</ul></div>`;

    html += `<hr class="panel-divider">
    <div class="format-spec-card">
        <h4>${UI_TEXT.chapterFormatTitle}</h4>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.chapterHeadingLabel}</span>
            <span class="spec-value">${UI_TEXT.headingCenterSpec}</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.chapterTitleLabel}</span>
            <span class="spec-value">${UI_TEXT.headingCenterSpec}</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.subheadingLabel}</span>
            <span class="spec-value">${UI_TEXT.subheadingSpec}</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.contentLabel}</span>
            <span class="spec-value">${UI_TEXT.chapterBodySpec}</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.paragraphLabel}</span>
            <span class="spec-value">${UI_TEXT.paragraphSpec}</span>
        </div>
    </div>
    <hr class="panel-divider">
    <div class="format-spec-card">
        <h4>${UI_TEXT.paperMarginTitle}</h4>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.leftTopLabel}</span>
            <span class="spec-value">${UI_TEXT.marginTopLeftSpec}</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">${UI_TEXT.rightBottomLabel}</span>
            <span class="spec-value">${UI_TEXT.marginBottomRightSpec}</span>
        </div>
    </div>`;

    container.innerHTML = html;
}

function renderTocPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-info-circle"></i> ${UI_TEXT.tocAboutTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.tocAuto1}</li>
                <li>${UI_TEXT.tocAuto2}</li>
                <li>${UI_TEXT.tocAuto3}</li>
                <li>${UI_TEXT.tocAuto4}</li>
            </ul>
        </div>
        <hr class="panel-divider">
        <div class="format-spec-card">
            <h4>${UI_TEXT.tocFormatTitle}</h4>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.tocHeadingLabel}</span><span class="spec-value">${UI_TEXT.headingCenterSpec}</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.tocChapterLabel}</span><span class="spec-value">16pt</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.tocLeaderLabel}</span><span class="spec-value">${UI_TEXT.tocLeaderSpec}</span></div>
        </div>`;
}

function renderAbstractPanel(container, section) {
    const isEn = section.lang === 'en';
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-pen"></i> ${isEn ? UI_TEXT.abstractGuideTitleEn : UI_TEXT.abstractGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.abstractGuide1}</li>
                <li>${UI_TEXT.abstractGuide2}</li>
                <li>${UI_TEXT.abstractGuide3}</li>
                <li>${UI_TEXT.abstractGuide4}</li>
                <li>${UI_TEXT.abstractGuide5}</li>
                ${isEn ? `<li>${UI_TEXT.abstractGuide6En}</li><li>${UI_TEXT.abstractGuide7En}</li>` : ''}
            </ul>
        </div>
        <hr class="panel-divider">
        <div class="format-spec-card">
            <h4>${UI_TEXT.formatTitle}</h4>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.abstractTitle}</span><span class="spec-value">${UI_TEXT.headingCenterSpec}</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.contentLabel}</span><span class="spec-value">${UI_TEXT.chapterBodySpec}</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.keywordsLabel}</span><span class="spec-value">${UI_TEXT.keywordsLabel}</span></div>
        </div>`;
}

function renderAcknowledgmentPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-heart"></i> ${UI_TEXT.ackGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.ackGuide1}</li>
                <li>${UI_TEXT.ackGuide2}</li>
                <li>${UI_TEXT.ackGuide3}</li>
                <li>${UI_TEXT.ackGuide4}</li>
            </ul>
        </div>`;
}

function renderBibliographyPanel(container) {
    if (IS_GUEST_MODE) {
        container.innerHTML = `
            <div class="project-selector-card">
                <h4><i class="fas fa-lock" style="color:#2b579a; margin-right:6px;"></i>${UI_TEXT.guestBibliographyTitle}</h4>
                <p class="panel-hint" style="font-size:12px; line-height:1.7; margin:0 0 14px; color:#6c7078;">${UI_TEXT.guestBibliographyDesc}</p>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="<?php echo SITE_URL; ?>/register.php" class="builder-guest-btn primary">${UI_TEXT.guestSignup}</a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="builder-guest-btn secondary">${UI_TEXT.guestSignin}</a>
                </div>
            </div>
            <hr class="panel-divider">
            <div class="format-spec-card">
                <h4>${UI_TEXT.bibFormatTitle}</h4>
                <div class="spec-row"><span class="spec-label">${UI_TEXT.bibHeadingLabel}</span><span class="spec-value">${UI_TEXT.headingCenterSpec}</span></div>
                <div class="spec-row"><span class="spec-label">${UI_TEXT.bibItemLabel}</span><span class="spec-value">${UI_TEXT.bibItemSpec}</span></div>
                <div class="spec-row"><span class="spec-label">${UI_TEXT.hangingIndentLabel}</span><span class="spec-value">${UI_TEXT.hangingIndentSpec}</span></div>
                <div class="spec-row"><span class="spec-label">${UI_TEXT.orderLabel}</span><span class="spec-value">${UI_TEXT.thaiFirstThenEnglish}</span></div>
            </div>`;
        return;
    }

    const projects = PROJECTS;

    let html = `<div class="project-selector-card">
        <h4><i class="fas fa-folder" style="color:#A78BFA; margin-right:6px;"></i>${UI_TEXT.selectProjectTitle}</h4>`;

    if (projects.length === 0) {
        html += `<div class="no-projects-hint">
            <i class="fas fa-folder-open" style="font-size:24px; margin-bottom:8px; display:block; color:#333;"></i>
            ${UI_TEXT.noProjectYet}<br>
            <a href="<?php echo SITE_URL; ?>/users/projects.php" target="_blank">${UI_TEXT.createNewProject}</a>
        </div>`;
    } else {
        html += `<div style="margin-bottom:4px; font-size:11px; color:#555;">${UI_TEXT.selectOneProject}</div>`;
        projects.forEach(p => {
            html += `<div class="project-option-item ${selectedProjectId === p.id ? 'selected' : ''}"
                id="proj-${p.id}"
                onclick="selectProject(${p.id})">
                <div class="project-dot" style="background: ${escHtmlAttr(p.color)}"></div>
                <span class="project-option-name">${escHtmlJs(p.name)}</span>
                <span class="project-option-count">${p.bib_count} ${UI_TEXT.itemsSuffix}</span>
            </div>`;
        });
    }

    html += `</div>`;

    // Loaded bibliographies preview
    html += `<div id="bib-panel-list">`;
    if (selectedProjectId && loadedBibliographies.length > 0) {
        html += renderBibPanelList();
    } else if (selectedProjectId) {
        html += `<div class="bib-loading"><span class="spinner"></span> ${UI_TEXT.loadingShort}</div>`;
    } else {
        html += `<p style="font-size:12px; color:#444; text-align:center; padding:16px 0;">
            ${UI_TEXT.chooseProjectToPreview}</p>`;
    }
    html += `</div>`;

    // Format specs
    html += `<hr class="panel-divider">
    <div class="format-spec-card">
        <h4>${UI_TEXT.bibFormatTitle}</h4>
        <div class="spec-row"><span class="spec-label">${UI_TEXT.bibHeadingLabel}</span><span class="spec-value">${UI_TEXT.headingCenterSpec}</span></div>
        <div class="spec-row"><span class="spec-label">${UI_TEXT.bibItemLabel}</span><span class="spec-value">${UI_TEXT.bibItemSpec}</span></div>
        <div class="spec-row"><span class="spec-label">${UI_TEXT.hangingIndentLabel}</span><span class="spec-value">${UI_TEXT.hangingIndentSpec}</span></div>
        <div class="spec-row"><span class="spec-label">${UI_TEXT.orderLabel}</span><span class="spec-value">${UI_TEXT.thaiFirstThenEnglish}</span></div>
    </div>`;

    container.innerHTML = html;
}

function renderBibPanelList() {
    if (!loadedBibliographies.length) return '';
    let html = `<div style="margin-bottom:10px;">
        <span style="font-size:12px; color:#777;">${UI_TEXT.bibListTitle}</span>
        <span class="bib-count-badge">${loadedBibliographies.length} ${UI_TEXT.itemsSuffix}</span>
    </div>`;

    loadedBibliographies.slice(0, 5).forEach(bib => {
        const shortText = bib.bibliography_text.replace(/<[^>]*>/g, '').substring(0, 80);
        html += `<div style="font-size:11px; color:#777; padding:6px 8px; background:#1e1e2e; border-radius:6px; margin-bottom:4px; line-height:1.4;">
            ${escHtmlJs(shortText)}${bib.bibliography_text.length > 80 ? '...' : ''}
        </div>`;
    });
    if (loadedBibliographies.length > 5) {
        html += `<div style="font-size:11px; color:#555; text-align:center; padding:4px;">${UI_TEXT.andMore} ${loadedBibliographies.length - 5} ${UI_TEXT.moreItems}</div>`;
    }
    return html;
}

function renderAppendixPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-paperclip"></i> ${UI_TEXT.appendixGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.appendixGuide1}</li>
                <li>${UI_TEXT.appendixGuide2}</li>
                <li>${UI_TEXT.appendixGuide3}</li>
                <li>${UI_TEXT.appendixGuide4}</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">${UI_TEXT.appendixHint}</p>`;
}

// ======================================================
//  PROJECT SELECTION & BIBLIOGRAPHY LOADING
// ======================================================
function selectProject(projectId) {
    if (IS_GUEST_MODE) return;
    selectedProjectId = projectId;
    scheduleDraftSave();

    // Update UI
    document.querySelectorAll('.project-option-item').forEach(el => {
        el.classList.toggle('selected', el.id === 'proj-' + projectId);
    });

    fetchProjectBibliographies(projectId, { showLoading: true });
}

function fetchProjectBibliographies(projectId, options = {}) {
    if (IS_GUEST_MODE) return;
    const { showLoading = true } = options;

    const listEl = document.getElementById('bib-panel-list');
    if (showLoading && listEl) {
        listEl.innerHTML = `<div class="bib-loading"><span class="spinner"></span> ${UI_TEXT.loadingBib}</div>`;
    }

    fetch(`<?php echo SITE_URL; ?>/api/template/get-project-bibs.php?project_id=${encodeURIComponent(projectId)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadedBibliographies = data.bibliographies;
                if (listEl) {
                    listEl.innerHTML = renderBibPanelList();
                }
                renderAllPreviews();
                scheduleDraftSave();
            } else {
                if (listEl) {
                    listEl.innerHTML = `<p style="color:#EF4444; font-size:12px; text-align:center; padding:10px;">${escHtmlJs(data.message || UI_TEXT.error)}</p>`;
                }
            }
        })
        .catch(() => {
            if (listEl) {
                listEl.innerHTML = `<p style="color:#EF4444; font-size:12px; text-align:center; padding:10px;">${UI_TEXT.loadFailed}</p>`;
            }
        });
}

// ======================================================
//  PREVIEW RENDERING
// ======================================================
function renderAllPreviews() {
    const container = document.getElementById('preview-pages');
    container.innerHTML = '';

    template.sections.forEach((section, i) => {
        // Page break hint (not before first)
        if (i > 0) {
            const hint = document.createElement('div');
            hint.className = 'page-break-hint';
            hint.textContent = UI_TEXT.pageBreak;
            container.appendChild(hint);
        }

        // A4 page
        const page = document.createElement('div');
        page.className = 'a4-paper';
        page.id = 'preview-' + section.id;
        page.innerHTML = renderSectionPreview(section);
        container.appendChild(page);
    });

    scheduleDraftSave();
}

function renderSectionPreview(section) {
    const font = formatSettings.font;
    const bodySize = formatSettings.bodySize;

    switch (section.type) {
        case 'cover': return renderCoverPreview();
        case 'inner_cover': return renderCoverPreview();
        case 'chapter': return renderChapterPreview(section);
        case 'toc': return renderTocPreview();
        case 'abstract': return renderAbstractPreview(section);
        case 'acknowledgment': return renderAcknowledgmentPreview();
        case 'bibliography': return renderBibliographyPreview();
        case 'appendix': return renderAppendixPreview();
        case 'preface': return renderPrefacePreview();
        case 'approval': return renderApprovalPreview();
        case 'biography': return renderBiographyPreview();
        default: return `<div style="text-align:center; color:#aaa; padding:40px;">${section.label}</div>`;
    }
}

function renderCoverPreview() {
    const title = coverData.title || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderTitle}</span>`;
    const authors = coverData.authors || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderAuthor}</span>`;
    const ids = coverData.studentIds
        ? coverData.studentIds.split('\n').map(line => line.trim()).filter(Boolean)
        : [];
    const studentIdPrefix = UI_TEXT.studentIdPrefix.trim();
    const prefixedIdHtml = ids.map(line => `${studentIdPrefix} ${escHtml(line)}`).join('<br>');
    const rawIdHtml = ids.map(line => escHtml(line)).join('<br>');
    const course = coverData.course || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderCourse}</span>`;
    const courseCode = coverData.courseCode ? ` (${coverData.courseCode})` : '';
    const instructor = coverData.instructor || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderInstructor}</span>`;
    const department = coverData.department || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderDepartment}</span>`;
    const institution = coverData.institution || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderInstitution}</span>`;

    const type = template.coverType;
    let html = '';

    function getSemesterLabel(semester) {
        if (semester === '2') return UI_TEXT.semester2;
        if (semester === '3') return UI_TEXT.semester3;
        return UI_TEXT.semester1;
    }

    function getAcademicSemesterShort(semester) {
        if (semester === '2') return '2';
        if (semester === '3') return UI_TEXT.semesterText3;
        return '1';
    }

    // ===== Academic General: 3-zone layout (Title | Author+ID | Course info) =====
    if (type === 'academic') {
        const semText = getAcademicSemesterShort(coverData.semester);
        const academicTitleSize = template.showLogo ? 22 : 20;
        const academicMetaSize = template.showLogo ? 18 : 20;
        if (template.showLogo) {
            html += `<div class="cover-logo-block"><img class="cover-logo-image" src="${escHtmlAttr(getResolvedLogoSrc())}" alt="${escHtmlAttr(UI_TEXT.coverFieldLogoAlt)}"></div>`;
        }
        html += `<div style="text-align:center; font-size:${academicTitleSize}px; font-weight:700; line-height:1.5;">${title}</div>`;
        html += `
            <div style="position:absolute; left:var(--page-left, 145px); right:var(--page-right, 96px); top:${template.showLogo ? '51%' : '50%'}; transform:translateY(-50%); text-align:center; line-height:1.5; font-size:${academicMetaSize}px; font-weight:700;">
                <div>${authors.replace(/\n/g, '<br>')}</div>
                ${prefixedIdHtml ? `<div style="margin-top:0.3em;">${prefixedIdHtml}</div>` : ''}
            </div>`;
        html += `
            <div class="cover-bottom" style="font-size:${academicMetaSize}px; font-weight:700; line-height:1.5;">
                <div>${course}${courseCode}</div>
                <div>${department}</div>
                <div>${institution}</div>
                ${coverData.year ? `<div>${UI_TEXT.academicSemesterYear} ${semText}/${coverData.year}</div>` : ''}
            </div>`;
        return html;
    }

    // ===== Other cover types =====

    // Institution at top
    html += `<div class="cover-institution">${institution}</div>`;
    if (coverData.department) {
        html += `<div class="cover-institution">${department}</div>`;
    }

    html += `<div class="cover-logo-placeholder"><i class="fas fa-university"></i></div>`;

    // Title
    html += `<div class="cover-title">${title}</div>`;

    // Type label
    if (type === 'internship') {
        html += `<div class="cover-subtitle">${UI_TEXT.internshipReport}</div>`;
    } else if (type === 'thesis') {
        const degree = coverData.degree || UI_TEXT.coverFieldDegreePlaceholder;
        const major = coverData.course || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderMajor}</span>`;
        html += `<div class="cover-subtitle">${UI_TEXT.thesisSubtitleLine1}<br>${degree} ${UI_TEXT.coverFieldMajor}${major}</div>`;
    } else if (type === 'project') {
        const projType = coverData.projectType || UI_TEXT.projectDefaultType;
        html += `<div class="cover-subtitle">${projType}</div>`;
    } else if (type === 'research') {
        html += `<div class="cover-subtitle">${UI_TEXT.researchReport}</div>`;
    }

    // Bottom block
    let bottomContent = '';

    if (type === 'internship') {
        const company = coverData.company || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderCompany}</span>`;
        const supervisor = coverData.supervisor || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderSupervisor}</span>`;
        const period = coverData.internshipPeriod || `<span style="color:#ccc">${UI_TEXT.coverPlaceholderPeriod}</span>`;
        bottomContent = `
            <div style="margin-bottom:10px;">${UI_TEXT.preparedBy}</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${rawIdHtml ? `<div style="font-size:12px; color:#555;">${rawIdHtml}</div>` : ''}
            <div style="margin:8px 0 4px;">${UI_TEXT.internshipOrgLabel} ${company}</div>
            ${coverData.supervisor ? `<div>${UI_TEXT.internshipSupervisorLabel} ${supervisor}</div>` : ''}
            ${coverData.internshipPeriod ? `<div>${UI_TEXT.internshipPeriodLabel} ${period}</div>` : ''}
            ${coverData.instructor ? `<div style="margin-top:4px;">${UI_TEXT.internshipInstructorLabel} ${coverData.instructor}</div>` : ''}
            <div style="margin-top:8px;">${institution}</div>
            ${coverData.year ? `<div>${UI_TEXT.academicYearOnly} ${coverData.year}</div>` : ''}
        `;
    } else if (type === 'thesis') {
        const committee = coverData.committee || '';
        bottomContent = `
            <div style="margin-bottom:10px;">${UI_TEXT.byLabel}</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${committee ? `<div style="margin:12px 0 4px; font-size:13px;">${UI_TEXT.committeeLabel}</div><div style="font-size:13px;">${committee.replace(/\n/g, '<br>')}</div>` : ''}
            <div style="margin-top:12px;">${institution}</div>
            ${coverData.year ? `<div>${IS_ENGLISH ? coverData.year : `${UI_TEXT.thesisYearPrefix} ${coverData.year}`}</div>` : ''}
        `;
    } else {
        const semesterLabel = getSemesterLabel(coverData.semester);
        bottomContent = `
            <div style="margin-bottom:8px;">${UI_TEXT.preparedBy}</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${rawIdHtml ? `<div style="font-size:12px; color:#555;">${rawIdHtml}</div>` : ''}
            <div style="margin:10px 0 4px;">${UI_TEXT.submittedTo}</div>
            <div>${instructor}</div>
            <div style="margin-top:8px;">${institution}</div>
            ${coverData.year ? `<div>${semesterLabel} ${UI_TEXT.academicYearOnly} ${coverData.year}</div>` : ''}
        `;
    }

    html += `<div class="cover-bottom">${bottomContent}</div>`;

    return html;
}

function renderChapterPreview(section) {
    let html = `
        <div class="chapter-heading">${UI_TEXT.chapterPrefix} ${section.number}</div>
        <div class="chapter-heading" style="margin-bottom:24px;">${section.title}</div>`;

    section.subsections.forEach(sub => {
        html += `
        <div class="chapter-sub-heading">${sub}</div>
        <div class="chapter-body-placeholder">
            <p>${UI_TEXT.chapterPlaceholder1}</p>
            <p>${UI_TEXT.chapterPlaceholder2}</p>
        </div>`;
    });

    return html;
}

function renderTocPreview() {
    const isAcademicGeneralToc = template.coverType === 'academic' && template.sections.some(section => section.type === 'preface');
    let html = isAcademicGeneralToc
        ? `<div class="chapter-heading" style="margin-bottom:24px; line-height:1.5; font-size:20px; font-weight:700;">${UI_TEXT.tocTitle}</div><div style="text-align:right; font-size:16px; line-height:1; margin-bottom:16px; color:#111;">หน้า</div>`
        : `<div class="chapter-heading" style="margin-bottom:24px;">${UI_TEXT.tocTitle}</div>`;

    function tocLine(label, page, indent = 0) {
        const lineStyle = isAcademicGeneralToc
            ? `display:flex; align-items:flex-start; gap:12px; margin-bottom:6px; font-size:16px; line-height:1.35; color:#111; padding-left:${indent * 24}px;`
            : `display:flex; margin-bottom:6px; font-size:14px; padding-left:${indent * 16}px;`;
        const pageStyle = isAcademicGeneralToc
            ? 'color:#111; font-size:16px; min-width:36px; text-align:right;'
            : 'color:#999; font-size:12px;';
        return `<div style="${lineStyle}">
            <span style="flex:1;">${label}</span>
            <span style="${pageStyle}">${page}</span>
        </div>`;
    }

    if (isAcademicGeneralToc) {
        let contentPage = 1;
        html += tocLine(UI_TEXT.prefaceTitle, 'ก');
        template.sections.forEach(section => {
            if (section.type !== 'chapter') return;
            html += tocLine(section.label, contentPage);
            if (section.subsections) {
                section.subsections.forEach((sub, i) => {
                    const subNum = `${section.number}.${i + 1} ${sub}`;
                    html += tocLine(subNum, contentPage, 1);
                    contentPage++;
                });
            }
        });
        html += tocLine(UI_TEXT.bibTitle, contentPage);
        if (template.sections.some(section => section.type === 'appendix')) {
            html += tocLine(UI_TEXT.appendixTitle, contentPage + 1);
        }
    } else {
        let pageNum = 1;
        template.sections.forEach(section => {
            if (section.type === 'cover') return;
            if (section.type === 'inner_cover') return;
            if (section.type === 'toc') return;
            pageNum++;
            html += tocLine(section.label, pageNum);
            if (section.type === 'chapter' && section.subsections) {
                section.subsections.forEach((sub, i) => {
                    const subNum = `${section.number}.${i+1} ${sub}`;
                    html += `<div style="display:flex; margin-bottom:4px; font-size:13px; padding-left:16px;">
                        <span style="flex:1; color:#555;">${subNum}</span>
                        <span style="color:#bbb; font-size:11px;">${pageNum}</span>
                    </div>`;
                });
            }
        });
    }

    return html;
}

function renderAbstractPreview(section) {
    const isEn = section.lang === 'en';
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">${isEn ? 'Abstract' : UI_TEXT.abstractTitle}</div>
        <div class="chapter-body-placeholder">
            <p>${isEn ? 'Write a concise summary of 150–300 words.' : UI_TEXT.abstractPreviewTh1}</p>
            <p>${isEn ? 'Include: objective, method, results, conclusion.' : UI_TEXT.abstractPreviewTh2}</p>
        </div>
        <div style="margin-top:20px; font-size:13px;">
            <strong>${isEn ? 'Keywords:' : `${UI_TEXT.keywordsLabel}:`}</strong>
            <span style="color:#aaa;"> ${UI_TEXT.keywordsPlaceholder}</span>
        </div>`;
}

function renderAcknowledgmentPreview() {
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">${UI_TEXT.ackTitle}</div>
        <div class="chapter-body-placeholder">
            <p>${UI_TEXT.ackPreview1}</p>
            <p>${UI_TEXT.ackPreview2}</p>
            <p>${UI_TEXT.ackPreview3}</p>
        </div>
        <div style="text-align:right; margin-top:30px; font-size:13px;">
            <div>${coverData.authors ? coverData.authors.split('\n')[0] : UI_TEXT.authorFallback}</div>
            <div style="color:#aaa;">${coverData.year || UI_TEXT.yearFallback}</div>
        </div>`;
}

function renderBibliographyPreview() {
    let html = `<div class="bib-section-title">${UI_TEXT.bibTitle}</div>`;

    if (loadedBibliographies.length === 0) {
        html += `<div class="bib-empty-state">
            <i class="fas fa-book-open" style="color:#DDD;"></i>
            <div style="font-size:14px; color:#BBB; margin-bottom:8px;">${UI_TEXT.bibEmptyTitle}</div>
            <div style="font-size:12px; color:#999;">${UI_TEXT.bibEmptyDesc}</div>
        </div>`;
    } else {
        loadedBibliographies.forEach(bib => {
            const text = bib.bibliography_text;
            html += `<div class="bib-preview-item">${text}</div>`;
        });
    }

    return html;
}

function renderAppendixPreview() {
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">${UI_TEXT.appendixTitle}</div>
        <div class="chapter-body-placeholder">
            <p>${UI_TEXT.appendixPreview1}</p>
            <p>${UI_TEXT.appendixPreview2}</p>
        </div>`;
}

function renderPrefacePanel(container) {
    container.innerHTML = `
        ${formGroup(UI_TEXT.prefaceContentLabel, 'fa-align-left',
            `<textarea class="panel-textarea" id="preface-content" placeholder="${escHtmlAttr(UI_TEXT.prefaceContentPlaceholder)}" rows="12" oninput="coverData.prefaceContent=this.value; renderAllPreviews()">${escHtml(coverData.prefaceContent)}</textarea>`)}
        ${formGroup(UI_TEXT.prefaceSignerLabel, 'fa-signature',
            `<input class="panel-input" id="preface-signer" type="text" placeholder="${escHtmlAttr(UI_TEXT.prefaceSignerPlaceholder)}" value="${escHtml(coverData.prefaceSigner)}" oninput="coverData.prefaceSigner=this.value; renderAllPreviews()">`)}
        ${formGroup(UI_TEXT.prefaceDateLabel, 'fa-calendar-day',
            `<input class="panel-input" id="preface-date" type="text" placeholder="${escHtmlAttr(UI_TEXT.prefaceDatePlaceholder)}" value="${escHtml(coverData.prefaceDate)}" oninput="coverData.prefaceDate=this.value; renderAllPreviews()">`)}
        <hr class="panel-divider">
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-pen-nib"></i> ${UI_TEXT.prefaceGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.prefaceGuide1}</li>
                <li>${UI_TEXT.prefaceGuide2}</li>
                <li>${UI_TEXT.prefaceGuide3}</li>
                <li>${UI_TEXT.prefaceGuide4}</li>
            </ul>
        </div>
        <div class="format-spec-card">
            <h4>${UI_TEXT.prefaceFormatTitle}</h4>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.prefaceTitle}</span><span class="spec-value">${UI_TEXT.prefaceHeadingSpec}</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.contentLabel}</span><span class="spec-value">${UI_TEXT.prefaceBodySpec}</span></div>
            <div class="spec-row"><span class="spec-label">${UI_TEXT.prefaceSignerLabel}</span><span class="spec-value">${UI_TEXT.prefaceSignatureSpec}</span></div>
        </div>
        <p class="panel-hint" style="margin-top:10px;">${UI_TEXT.prefaceHint}</p>`;
}

function renderPrefacePreview() {
    const prefaceContent = (coverData.prefaceContent || `${UI_TEXT.prefacePreview1}\n\n${UI_TEXT.prefacePreview2}`)
        .split(/\n{2,}/)
        .map(paragraph => paragraph.replace(/\s*\n\s*/g, ' ').trim())
        .filter(Boolean);
    const signer = coverData.prefaceSigner || (coverData.authors ? coverData.authors.split('\n')[0] : UI_TEXT.authorFallback);
    const dateText = coverData.prefaceDate || coverData.year || '';
    return `
        <div class="chapter-heading" style="margin-bottom:24px; line-height:1.5; font-size:20px; font-weight:700;">${UI_TEXT.prefaceTitle}</div>
        <div class="preface-preview-body">
            ${prefaceContent.map(paragraph => `<p>${escHtml(paragraph)}</p>`).join('')}
        </div>
        <div style="text-align:right; margin-top:34px; font-size:16px; line-height:1.6; color:#111;">
            <div>${escHtml(signer)}</div>
            ${dateText ? `<div>${escHtml(dateText)}</div>` : ''}
        </div>`;
}

function renderApprovalPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-file-signature"></i> ${UI_TEXT.approvalGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.approvalGuide1}</li>
                <li>${UI_TEXT.approvalGuide2}</li>
                <li>${UI_TEXT.approvalGuide3}</li>
                <li>${UI_TEXT.approvalGuide4}</li>
                <li>${UI_TEXT.approvalGuide5}</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">${UI_TEXT.approvalHint}</p>`;
}

function renderApprovalPreview() {
    const authorLine = coverData.authors ? coverData.authors.split('\n')[0] : UI_TEXT.studentFallback;
    const institution = coverData.institution || UI_TEXT.coverPlaceholderInstitution;
    return `
        <div class="chapter-heading" style="margin-bottom:20px;">${UI_TEXT.approvalTitle}</div>
        <div style="text-align:center; font-size:13px; margin-bottom:24px;">
            <div style="font-weight:600;">${authorLine}</div>
            ${coverData.course ? `<div style="color:#555;">${coverData.course}</div>` : ''}
            <div style="color:#555;">${institution}</div>
        </div>
        <div style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:30px; font-size:13px;">
                <div style="width:45%; text-align:center;">
                    <div style="border-top:1px solid #ccc; padding-top:6px; color:#555;">${UI_TEXT.approvalAdvisor}</div>
                    <div style="color:#bbb; font-size:12px;">${UI_TEXT.approvalSignatureDate}</div>
                </div>
                <div style="width:45%; text-align:center;">
                    <div style="border-top:1px solid #ccc; padding-top:6px; color:#555;">${UI_TEXT.approvalDean}</div>
                    <div style="color:#bbb; font-size:12px;">${UI_TEXT.approvalSignatureDate}</div>
                </div>
            </div>
        </div>`;
}

function renderBiographyPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-user-circle"></i> ${UI_TEXT.biographyGuideTitle}</div>
            <ul class="chapter-guide-list">
                <li>${UI_TEXT.biographyGuide1}</li>
                <li>${UI_TEXT.biographyGuide2}</li>
                <li>${UI_TEXT.biographyGuide3}</li>
                <li>${UI_TEXT.biographyGuide4}</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">${UI_TEXT.biographyHint}</p>`;
}

function renderBiographyPreview() {
    const authorLine = coverData.authors ? coverData.authors.split('\n')[0] : UI_TEXT.coverPlaceholderAuthor;
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">${UI_TEXT.biographyTitle}</div>
        <div style="display:flex; gap:24px; margin-bottom:16px;">
            <div style="width:80px; height:100px; background:#F3F4F6; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-user" style="font-size:28px; color:#D1D5DB;"></i>
            </div>
            <div style="flex:1;">
                <div class="chapter-body-placeholder">
                    <p>${UI_TEXT.bioNameLabel} ${authorLine}</p>
                    <p>${UI_TEXT.bioEducationLabel} ...</p>
                    <p>${UI_TEXT.bioPositionLabel} ...</p>
                </div>
            </div>
        </div>`;
}

// ======================================================
//  UPDATE TRIGGERS
// ======================================================
function updateCoverPreview() {
    const coverPage = document.getElementById('preview-cover');
    if (coverPage) {
        coverPage.innerHTML = renderCoverPreview();
    }
    const innerCoverPage = document.getElementById('preview-inner_cover');
    if (innerCoverPage) {
        innerCoverPage.innerHTML = renderCoverPreview();
    }

    if (template.showLogo) {
        renderLogoUploadState();
    }

    scheduleDraftSave();
}

function updateFormatSettings() {
    formatSettings.font = document.getElementById('setting-font').value;
    formatSettings.bodySize = parseInt(document.getElementById('setting-body-size').value);
    formatSettings.margin = document.getElementById('setting-margin').value;

    // Apply to all preview pages
    const marginMap = {
        standard: {top: '145px', right: '96px', bottom: '96px', left: '145px'},
        wide: {top: '192px', right: '145px', bottom: '145px', left: '192px'},
        narrow: {top: '96px', right: '96px', bottom: '96px', left: '96px'}
    };
    const m = marginMap[formatSettings.margin];

    // CSS font stack: web-safe fallbacks so preview looks close to Word fonts
    const fontStackMap = {
        'Angsana New':    '"Angsana New", "Angsana UPC", Georgia, serif',
        'TH Sarabun New': '"TH Sarabun New", "Sarabun", sans-serif',
        'TH Niramit AS':  '"TH Niramit AS", "Niramit", sans-serif',
        'Times New Roman': '"Times New Roman", Times, serif'
    };
    const fontStack = fontStackMap[formatSettings.font] || fontStackMap['Angsana New'];

    document.querySelectorAll('.a4-paper').forEach(el => {
        el.style.paddingTop    = m.top;
        el.style.paddingRight  = m.right;
        el.style.paddingBottom = m.bottom;
        el.style.paddingLeft   = m.left;
        el.style.setProperty('--page-top', m.top);
        el.style.setProperty('--page-right', m.right);
        el.style.setProperty('--page-bottom', m.bottom);
        el.style.setProperty('--page-left', m.left);
        el.style.fontSize      = formatSettings.bodySize + 'px';
        el.style.fontFamily    = fontStack;
    });

    scheduleDraftSave();
}

// ======================================================
//  EXPORT
// ======================================================
function exportReport(format) {
    const btn = document.getElementById('btn-' + format);
    const origHtml = btn.innerHTML;
    btn.classList.remove('is-success', 'is-hidden');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> ${UI_TEXT.exportGenerating}`;

    const payload = {
        template: templateId,
        format: format,
        coverData: coverData,
        formatSettings: formatSettings,
        projectId: IS_GUEST_MODE ? null : selectedProjectId
    };

    if (format === 'docx') {
        const formData = new FormData();
        formData.append('payload', JSON.stringify(payload));

        fetch('<?php echo SITE_URL; ?>/api/template/export-report.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(async (response) => {
                if (!response.ok) {
                    const message = (await response.text()) || UI_TEXT.exportFailed;
                    throw new Error(message);
                }

                const blob = await response.blob();
                const disposition = response.headers.get('content-disposition') || '';
                const fileNameMatch = disposition.match(/filename\*?=(?:UTF-8''|"?)([^";]+)/i);
                const fileName = fileNameMatch ? decodeURIComponent(fileNameMatch[1].replace(/"/g, '')) : `report-${templateId}.docx`;
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(downloadUrl);

                btn.disabled = true;
                btn.classList.add('is-success');
                btn.innerHTML = `<i class="fas fa-check"></i>`;

                window.setTimeout(() => {
                    btn.classList.add('is-hidden');
                }, 650);

                window.setTimeout(() => {
                    window.location.reload();
                }, 1150);
            })
            .catch((error) => {
                btn.disabled = false;
                btn.classList.remove('is-success', 'is-hidden');
                btn.innerHTML = origHtml;

                if (typeof Toast !== 'undefined' && Toast.error) {
                    Toast.error(error.message || UI_TEXT.exportFailed);
                } else {
                    window.alert(error.message || UI_TEXT.exportFailed);
                }
            });
    } else {
        // PDF: open print preview
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo SITE_URL; ?>/api/template/export-report.php';
        form.target = '_blank';

        [['payload', JSON.stringify(payload)]].forEach(([name, val]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = name;
            inp.value = val;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }, 2000);
    }
}

// ======================================================
//  HELPERS
// ======================================================
function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escHtmlAttr(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
function escHtmlJs(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>
