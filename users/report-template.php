<?php

/**
 * Babybib - Report Template Selection Page
 * =========================================
 * ให้ผู้ใช้เลือก Template รายงานสำหรับเริ่มต้นทำรายงาน
 */

require_once '../includes/session.php';
$isGuestMode = !isLoggedIn();

$pageTitle = __('report_templates_page_title');

require_once '../includes/header.php';
require_once $isGuestMode ? '../includes/navbar-guest.php' : '../includes/navbar-user.php';

$userId = $isGuestMode ? null : getCurrentUserId();
$isEnglish = getCurrentLanguage() === 'en';
$tr = static function ($th, $en) use ($isEnglish) {
    return $isEnglish ? $en : $th;
};

// Get user's projects (for showing project count)
if (!$isGuestMode) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        $projectCount = $stmt->fetch()['total'];
    } catch (Exception $e) {
        $projectCount = 0;
    }
} else {
    $projectCount = 0;
}

$templateCards = [
    [
        'id' => 'academic_general',
        'icon' => 'fa-file-lines',
        'color' => 'linear-gradient(135deg, #8B5CF6, #6366F1)',
        'title' => $tr('รายงานวิชาการทั่วไป', 'General Academic Report'),
        'subtitle' => $tr('มาตรฐานสำหรับรายงานระดับมัธยม–อุดมศึกษา', 'Standard structure for secondary to university-level reports'),
        'badge' => $tr('ยอดนิยม', 'Popular'),
        'badgeStyle' => '',
        'preview' => [
            $tr('หน้าปก', 'Cover Page'),
            $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'),
            $tr('บรรณานุกรม', 'Bibliography'),
        ],
        'footer' => $tr('หน้าปก + 3 บท + บรรณานุกรม', 'Cover + 3 chapters + bibliography'),
    ],
    [
        'id' => 'research',
        'icon' => 'fa-microscope',
        'color' => 'linear-gradient(135deg, #3B82F6, #06B6D4)',
        'title' => $tr('รายงานการวิจัย', 'Research Report'),
        'subtitle' => $tr('โครงสร้างสำหรับงานวิจัยระดับสูง 5 บท', 'Five-chapter structure for advanced research work'),
        'badge' => '',
        'badgeStyle' => '',
        'preview' => [
            $tr('หน้าปก + บทคัดย่อ', 'Cover + Abstract'),
            $tr('5 บท (บทนำ → สรุป)', '5 Chapters (Introduction to Conclusion)'),
            $tr('บรรณานุกรม + ภาคผนวก', 'Bibliography + Appendix'),
        ],
        'footer' => $tr('หน้าปก + 5 บท + บรรณานุกรม', 'Cover + 5 chapters + bibliography'),
    ],
    [
        'id' => 'internship',
        'icon' => 'fa-briefcase',
        'color' => 'linear-gradient(135deg, #10B981, #059669)',
        'title' => $tr('รายงานฝึกงาน / สหกิจ', 'Internship / Cooperative Report'),
        'subtitle' => $tr('ครบถ้วนสำหรับรายงานฝึกประสบการณ์วิชาชีพ', 'Complete structure for professional internship reports'),
        'badge' => '',
        'badgeStyle' => '',
        'preview' => [
            $tr('หน้าปก (ชื่อองค์กร)', 'Cover (Organization Name)'),
            $tr('5 บท + บรรณานุกรม', '5 Chapters + Bibliography'),
            $tr('ภาคผนวก', 'Appendix'),
        ],
        'footer' => $tr('หน้าปก + 5 บท + ภาคผนวก', 'Cover + 5 chapters + appendix'),
    ],
    [
        'id' => 'project',
        'icon' => 'fa-diagram-project',
        'color' => 'linear-gradient(135deg, #F59E0B, #F97316)',
        'title' => $tr('รายงานโครงการ', 'Project Report'),
        'subtitle' => $tr('สำหรับ Senior Project หรือโปรเจควิชาการ', 'For senior projects and academic project work'),
        'badge' => '',
        'badgeStyle' => '',
        'preview' => [
            $tr('หน้าปก + ทีมงาน', 'Cover + Team Information'),
            $tr('5 บท + บรรณานุกรม', '5 Chapters + Bibliography'),
            $tr('ภาคผนวก', 'Appendix'),
        ],
        'footer' => $tr('หน้าปก + 5 บท + ภาคผนวก', 'Cover + 5 chapters + appendix'),
    ],
    [
        'id' => 'thesis',
        'icon' => 'fa-graduation-cap',
        'color' => 'linear-gradient(135deg, #EF4444, #DC2626)',
        'title' => $tr('วิทยานิพนธ์ / สารนิพนธ์', 'Thesis / Independent Study'),
        'subtitle' => $tr('โครงสร้างมาตรฐานระดับบัณฑิตศึกษา', 'Standard graduate-level structure'),
        'badge' => 'Graduate',
        'badgeStyle' => 'background:#FEE2E2; color:#DC2626;',
        'preview' => [
            $tr('หน้าปก + กิตติกรรม + บทคัดย่อ', 'Cover + Acknowledgment + Abstract'),
            $tr('5 บท + บรรณานุกรม + ภาคผนวก', '5 Chapters + Bibliography + Appendix'),
            '',
        ],
        'footer' => $tr('หน้าปก + ส่วนนำ + 5 บท + ภาคผนวก', 'Cover + front matter + 5 chapters + appendix'),
    ],
    [
        'id' => 'thesis_master',
        'icon' => 'fa-user-graduate',
        'color' => 'linear-gradient(135deg, #7C3AED, #5B21B6)',
        'title' => $tr('วิทยานิพนธ์ ป.โท', 'Master Thesis'),
        'subtitle' => $tr('โครงสร้างมาตรฐานระดับปริญญาโท พร้อมหน้าอนุมัติและประวัติผู้เขียน', 'Master-level structure with approval page and author biography'),
        'badge' => "Master's",
        'badgeStyle' => 'background:#EDE9FE; color:#5B21B6;',
        'preview' => [
            $tr('หน้าปก + หน้าอนุมัติ', 'Cover + Approval Page'),
            $tr('บทคัดย่อ ไทย/EN + 5 บท', 'Thai/EN Abstract + 5 Chapters'),
            $tr('บรรณานุกรม + ประวัติผู้เขียน', 'Bibliography + Author Biography'),
        ],
        'footer' => $tr('หน้าอนุมัติ + 5 บท + ประวัติผู้เขียน', 'Approval page + 5 chapters + author biography'),
    ],
];

$tips = [
    [
        'icon' => 'fa-wand-magic-sparkles',
        'title' => $tr('กรอกข้อมูลหน้าปก', 'Fill in cover details'),
        'desc' => $tr('ระบุชื่อรายงาน ผู้จัดทำ วิชา อาจารย์ผู้สอน และข้อมูลสถาบัน เพื่อสร้างหน้าปกแบบมืออาชีพ', 'Provide the report title, authors, course, instructor, and institution details to generate a professional cover page.'),
    ],
    [
        'icon' => 'fa-book-open',
        'title' => $tr('เลือกบรรณานุกรมจากโครงการ', 'Choose bibliography from a project'),
        'desc' => $tr('เลือกโครงการที่มีรายการบรรณานุกรม เพื่อแทรกลงท้ายเอกสารโดยอัตโนมัติ', 'Select a project that already contains bibliography entries so they can be inserted automatically at the end of the document.'),
    ],
    [
        'icon' => 'fa-file-export',
        'title' => $tr('Export Word & PDF', 'Export to Word & PDF'),
        'desc' => $tr('ดาวน์โหลดเป็นไฟล์ Word (.docx) ที่แก้ไขได้ หรือ PDF สำหรับส่งงาน', 'Download an editable Word (.docx) file or a PDF version ready for submission.'),
    ],
];

$templatePageText = [
    'badge' => $tr('แม่แบบรายงาน', 'Report Templates'),
    'title' => $tr('เลือกโครงรายงานที่สะอาด พร้อมใช้งานทันที', 'Choose a clean report structure and start immediately'),
    'desc' => $tr('เลือกแม่แบบที่เหมาะกับงานของคุณ แล้วไปกรอกข้อมูลต่อในหน้า builder เพื่อจัดหน้ารายงานและ export ได้อย่างรวดเร็ว', 'Choose the template that fits your work, then continue in the builder to prepare the report layout and export it quickly.'),
    'sectionTitle' => $tr('เลือกแม่แบบที่ต้องการ', 'Choose a template'),
    'memberMode' => $tr('โหมดสมาชิก', 'Member Mode'),
    'guestMode' => $tr('โหมดทดลองใช้งาน', 'Guest Mode'),
    'guestWarningTitle' => $tr('ทดลองใช้แม่แบบได้ทันทีโดยไม่ต้องเข้าสู่ระบบ', 'Try the report templates immediately without signing in'),
    'guestWarningDesc' => $tr('โหมดทดลองไม่สามารถดึงรายการบรรณานุกรมจากโครงการ และข้อมูลที่กรอกอาจหายเมื่อออกจากหน้า รีเฟรช หรือปิดเบราว์เซอร์ สมัครสมาชิกเพื่อบันทึกงานและใช้งานได้ครบกว่าเดิม', 'Guest mode cannot import bibliography entries from projects, and any data you enter may be lost when you leave the page, refresh, or close the browser. Sign up to save your work and unlock the full workflow.'),
    'guestSignup' => $tr('สมัครสมาชิก', 'Sign Up'),
    'guestLogin' => $tr('เข้าสู่ระบบ', 'Sign In'),
    'memberNoticeTitle' => $tr('บัญชีสมาชิกช่วยให้ทำงานต่อได้ลื่นกว่า', 'A member account keeps your report workflow smoother'),
    'memberNoticeDesc' => $tr('เลือกบรรณานุกรมจากโครงการของคุณได้ทันที และกลับมาทำต่อจากหน้าเดิมได้สะดวกกว่าเดิม', 'Pull bibliography entries from your projects and continue working from your report setup more easily.'),
];
?>

<style>
    .template-page {
        min-height: calc(100vh - 80px);
        background:
            radial-gradient(circle at top left, rgba(43, 87, 154, 0.07), transparent 32%),
            linear-gradient(180deg, #f4f5f7 0%, #eceff3 100%);
        padding: 36px 20px 88px;
    }

    .template-page-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .template-page-header {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.9fr);
        gap: 24px;
        align-items: stretch;
        margin: 0 auto 34px;
    }

    .template-hero,
    .template-side-note,
    .template-tips {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #d9dee7;
        border-radius: 28px;
        box-shadow: 0 18px 44px rgba(34, 44, 65, 0.06);
        backdrop-filter: blur(10px);
    }

    .template-hero {
        padding: 34px 34px 30px;
    }

    .page-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #edf3ff;
        color: #2b579a;
        border: 1px solid #c8d8f0;
        padding: 7px 15px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        margin-bottom: 18px;
    }

    .template-page-header h1 {
        font-size: clamp(2rem, 3vw, 3rem);
        font-weight: 800;
        color: #1f2430;
        margin: 0 0 12px;
        line-height: 1.08;
        letter-spacing: -0.03em;
    }

    .template-page-header p {
        font-size: 1rem;
        color: #596070;
        line-height: 1.75;
        margin: 0;
        max-width: 760px;
    }

    .template-stats {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 22px;
        flex-wrap: wrap;
    }

    .template-stat-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #5f6674;
        background: #f8fafc;
        border: 1px solid #e1e7f0;
        padding: 10px 14px;
        border-radius: 14px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }

    .template-stat-item i {
        color: #2b579a;
    }

    .template-side-note {
        padding: 28px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 18px;
        background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,249,252,0.96));
    }

    .template-side-note h2 {
        margin: 0;
        font-size: 1.1rem;
        color: #1f2430;
        line-height: 1.35;
    }

    .template-side-note p {
        font-size: 13px;
        color: #667080;
        line-height: 1.75;
        margin: 0;
    }

    .template-side-mode {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background: #edf3ff;
        color: #2b579a;
        border: 1px solid #d1def7;
    }

    .template-side-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .template-side-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 132px;
        padding: 11px 16px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .template-side-btn.primary {
        background: linear-gradient(135deg, #2b579a, #4573be);
        color: #fff;
        box-shadow: 0 12px 24px rgba(43, 87, 154, 0.18);
    }

    .template-side-btn.secondary {
        background: #fff;
        color: #2f3642;
        border: 1px solid #d7dde7;
    }

    .template-side-btn:hover {
        transform: translateY(-1px);
        color: inherit;
    }

    .template-grid-container {
        max-width: 1180px;
        margin: 0 auto;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 800;
        color: #2e3440;
        margin: 0 0 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.01em;
    }

    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 22px;
        margin-bottom: 34px;
    }

    .template-card {
        background: rgba(255,255,255,0.94);
        border-radius: 26px;
        border: 1px solid #dde3ec;
        overflow: hidden;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        cursor: pointer;
        position: relative;
        box-shadow: 0 16px 34px rgba(34, 44, 65, 0.05);
    }

    .template-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 42px rgba(43, 87, 154, 0.14);
        border-color: #c7d7f4;
    }

    .template-card-header {
        padding: 24px 24px 16px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
    }

    .template-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
    }

    .template-card-title-area {
        flex: 1;
    }

    .template-card-title-area h3 {
        font-size: 1rem;
        font-weight: 800;
        color: #1f2430;
        margin: 0 0 4px;
        line-height: 1.3;
    }

    .template-card-title-area p {
        font-size: 12px;
        color: #697181;
        margin: 0;
        line-height: 1.4;
    }

    .template-card-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: #eef3fb;
        color: #345c98;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
    }

    /* Mini paper preview */
    .template-card-preview {
        margin: 0 24px;
        background: linear-gradient(180deg, #fcfcfd 0%, #f5f7fa 100%);
        border: 1px solid #e4e8ef;
        border-radius: 10px;
        padding: 16px 14px;
        position: relative;
        min-height: 120px;
        overflow: hidden;
    }

    .template-card-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 10px 10px 0 0;
        background: var(--card-color, #8B5CF6);
    }

    .preview-line {
        height: 6px;
        border-radius: 3px;
        background: #E5E7EB;
        margin-bottom: 5px;
    }

    .preview-line.bold { background: #CBD5E1; height: 8px; }
    .preview-line.center { width: 65%; margin: 0 auto 5px; }
    .preview-line.short { width: 45%; }
    .preview-line.medium { width: 75%; }
    .preview-line.long { width: 95%; }
    .preview-line.full { width: 100%; }
    .preview-line.indent { margin-left: 16px; width: calc(100% - 16px); }
    .preview-line.gap { margin-top: 10px; }
    .preview-line.color {
        background: var(--card-color, #8B5CF6);
        opacity: 0.3;
    }

    .preview-section-label {
        font-size: 8px;
        color: #8b95a5;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        margin-top: 10px;
    }

    /* Card footer */
    .template-card-footer {
        padding: 18px 24px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .template-chapters-info {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #677081;
    }

    .template-chapters-info i {
        font-size: 11px;
        color: #99a5b5;
    }

    .template-use-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .template-use-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.08);
        color: white;
    }

    .template-tips {
        max-width: 1180px;
        margin: 0 auto;
        padding: 28px 30px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 24px;
    }

    .tip-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .tip-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .tip-text h4 {
        font-size: 13px;
        font-weight: 700;
        color: #1f2430;
        margin: 0 0 4px;
    }

    .tip-text p {
        font-size: 12px;
        color: #6d7584;
        margin: 0;
        line-height: 1.5;
    }

    .template-guest-banner {
        margin: 0 auto 24px;
        max-width: 1180px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 22px;
        background: linear-gradient(135deg, #fff8ef, #fffdf8);
        border: 1px solid #ead9bc;
        border-radius: 22px;
        box-shadow: 0 14px 34px rgba(120, 92, 34, 0.06);
    }

    .template-guest-banner i {
        color: #b7791f;
        font-size: 18px;
        margin-top: 1px;
    }

    .template-guest-copy {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        flex: 1;
    }

    .template-guest-copy h3 {
        margin: 0 0 4px;
        font-size: 14px;
        color: #5f4518;
    }

    .template-guest-copy p {
        margin: 0;
        font-size: 12px;
        color: #7a6541;
        line-height: 1.7;
    }

    .template-guest-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .template-guest-actions .template-side-btn.secondary {
        background: rgba(255,255,255,0.8);
    }

    @media (max-width: 768px) {
        .template-page-header {
            grid-template-columns: 1fr;
        }
        .template-page-header h1 { font-size: 1.8rem; }
        .template-grid { grid-template-columns: 1fr; }
        .template-tips { grid-template-columns: 1fr; }
        .template-guest-banner {
            flex-direction: column;
        }
    }
</style>

<div class="template-page">
    <div class="template-page-shell">

    <div class="template-page-header slide-up">
        <div class="template-hero">
            <div class="page-badge">
                <i class="fas fa-file-lines"></i>
                <?php echo htmlspecialchars($templatePageText['badge']); ?>
            </div>
            <h1><?php echo htmlspecialchars($templatePageText['title']); ?></h1>
            <p><?php echo htmlspecialchars($templatePageText['desc']); ?></p>

            <div class="template-stats">
                <div class="template-stat-item">
                    <i class="fas fa-layer-group"></i>
                    <?php echo count($templateCards) . ' ' . $tr('รูปแบบแม่แบบ', 'template types'); ?>
                </div>
                <div class="template-stat-item">
                    <i class="fas <?php echo $isGuestMode ? 'fa-bolt' : 'fa-folder'; ?>"></i>
                    <?php echo $isGuestMode ? htmlspecialchars($templatePageText['guestMode']) : $projectCount . ' ' . $tr('โครงการของคุณ', 'your projects'); ?>
                </div>
                <div class="template-stat-item">
                    <i class="fas fa-file-word"></i>
                    <?php echo $tr('Export Word / PDF', 'Export Word / PDF'); ?>
                </div>
            </div>
        </div>

        <aside class="template-side-note">
            <span class="template-side-mode">
                <i class="fas <?php echo $isGuestMode ? 'fa-unlock-keyhole' : 'fa-user-check'; ?>"></i>
                <?php echo htmlspecialchars($isGuestMode ? $templatePageText['guestMode'] : $templatePageText['memberMode']); ?>
            </span>
            <div>
                <h2><?php echo htmlspecialchars($isGuestMode ? $templatePageText['guestWarningTitle'] : $templatePageText['memberNoticeTitle']); ?></h2>
                <p><?php echo htmlspecialchars($isGuestMode ? $templatePageText['guestWarningDesc'] : $templatePageText['memberNoticeDesc']); ?></p>
            </div>
            <?php if ($isGuestMode): ?>
                <div class="template-side-actions">
                    <a href="<?php echo SITE_URL; ?>/register.php" class="template-side-btn primary">
                        <i class="fas fa-user-plus"></i>
                        <?php echo htmlspecialchars($templatePageText['guestSignup']); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="template-side-btn secondary">
                        <i class="fas fa-right-to-bracket"></i>
                        <?php echo htmlspecialchars($templatePageText['guestLogin']); ?>
                    </a>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <?php if ($isGuestMode): ?>
        <div class="template-guest-banner">
            <div class="template-guest-copy">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <h3><?php echo htmlspecialchars($templatePageText['guestWarningTitle']); ?></h3>
                    <p><?php echo htmlspecialchars($templatePageText['guestWarningDesc']); ?></p>
                </div>
            </div>
            <div class="template-guest-actions">
                <a href="<?php echo SITE_URL; ?>/register.php" class="template-side-btn primary"><?php echo htmlspecialchars($templatePageText['guestSignup']); ?></a>
                <a href="<?php echo SITE_URL; ?>/login.php" class="template-side-btn secondary"><?php echo htmlspecialchars($templatePageText['guestLogin']); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Templates Grid -->
    <div class="template-grid-container">

        <p class="section-title">
            <i class="fas fa-grip" style="color: var(--primary)"></i>
            <?php echo htmlspecialchars($templatePageText['sectionTitle']); ?>
        </p>

        <div class="template-grid">
            <?php foreach ($templateCards as $card): ?>
                <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=<?php echo $card['id']; ?>'">
                    <div class="template-card-header">
                        <div class="template-card-icon" style="background: <?php echo $card['color']; ?>;">
                            <i class="fas <?php echo $card['icon']; ?>"></i>
                        </div>
                        <div class="template-card-title-area">
                            <h3><?php echo htmlspecialchars($card['title']); ?></h3>
                            <p><?php echo htmlspecialchars($card['subtitle']); ?></p>
                        </div>
                        <?php if ($card['badge']): ?>
                            <span class="template-card-badge"<?php echo $card['badgeStyle'] ? ' style="' . $card['badgeStyle'] . '"' : ''; ?>><?php echo htmlspecialchars($card['badge']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="template-card-preview" style="--card-color: <?php echo preg_match('/#([A-Fa-f0-9]{6})/', $card['color'], $matches) ? '#' . $matches[1] : '#8B5CF6'; ?>">
                        <?php foreach ($card['preview'] as $index => $label): ?>
                            <?php if (!$label) continue; ?>
                            <div class="preview-section-label"><?php echo htmlspecialchars($label); ?></div>
                            <div class="preview-line <?php echo $index === 0 ? 'bold color center' : 'full'; ?>"></div>
                            <?php if ($index === 0): ?>
                                <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                            <?php elseif ($index === 1): ?>
                                <div class="preview-line long"></div>
                                <div class="preview-line medium"></div>
                            <?php else: ?>
                                <div class="preview-line indent medium"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="template-card-footer">
                        <div class="template-chapters-info">
                            <i class="fas fa-layer-group"></i>
                            <?php echo htmlspecialchars($card['footer']); ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=<?php echo $card['id']; ?>" class="template-use-btn" style="background: <?php echo $card['color']; ?>;">
                            <?php echo $tr('ใช้ Template นี้', 'Use This Template'); ?> <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Tips -->
        <div class="template-tips">
            <?php foreach ($tips as $tip): ?>
                <div class="tip-item">
                    <div class="tip-icon">
                        <i class="fas <?php echo $tip['icon']; ?>"></i>
                    </div>
                    <div class="tip-text">
                        <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                        <p><?php echo htmlspecialchars($tip['desc']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
