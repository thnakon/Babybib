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
        'badgeStyle' => 'background:#DCFCE7; color:#166534; border:1px solid #86EFAC; box-shadow:0 8px 18px rgba(34, 197, 94, 0.18);',
        'badgeClass' => 'badge-popular',
        'preview' => [
            $tr('หน้าปก', 'Cover Page'),
            $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'),
            $tr('บรรณานุกรม', 'Bibliography'),
        ],
        'footer' => $tr('หน้าปก + 3 บท + บรรณานุกรม', 'Cover + 3 chapters + bibliography'),
    ],
    [
        'id' => 'academic_general_logo',
        'icon' => 'fa-building-columns',
        'color' => 'linear-gradient(135deg, #7C3AED, #5B21B6)',
        'title' => $tr('รายงานวิชาการทั่วไป พร้อม Logo', 'General Academic Report with Logo'),
        'subtitle' => $tr('โครงสร้างเดียวกับรายงานวิชาการทั่วไป พร้อมหน้าปกแบบมีตราสถาบัน', 'The same academic report structure with a logo-ready cover layout.'),
        'badge' => $tr('ใหม่', 'New'),
        'badgeStyle' => 'background:#EDE9FE; color:#5B21B6;',
        'preview' => [
            $tr('หน้าปก + Logo', 'Cover + Logo'),
            $tr('บทที่ 1 บทนำ', 'Chapter 1 Introduction'),
            $tr('บรรณานุกรม', 'Bibliography'),
        ],
        'footer' => $tr('หน้าปก + Logo + 3 บท + บรรณานุกรม', 'Cover + logo + 3 chapters + bibliography'),
    ],
    [
        'id' => 'research',
        'icon' => 'fa-microscope',
        'color' => 'linear-gradient(135deg, #3B82F6, #06B6D4)',
        'title' => $tr('รายงานโปรเจค', 'Project Report'),
        'subtitle' => $tr('โครงสร้างสำหรับรายงานโปรเจค 5 บท', 'Five-chapter structure for project reports'),
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
        'badge' => $tr('เฉพาะภาควิชา', 'Department Only'),
        'badgeTooltip' => $tr('สำหรับ ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่', 'For Library and Information Science, Faculty of Humanities, Chiang Mai University'),
        'badgeStyle' => 'background:#ECFDF5; color:#047857; border:1px solid #6EE7B7; box-shadow:0 8px 18px rgba(16, 185, 129, 0.16);',
        'badgeClass' => 'badge-tooltip',
        'preview' => [
            $tr('หน้าปก (ชื่อองค์กร)', 'Cover (Organization Name)'),
            $tr('5 บท + บรรณานุกรม', '5 Chapters + Bibliography'),
            $tr('ภาคผนวก', 'Appendix'),
        ],
        'footer' => $tr('หน้าปก + 5 บท + ภาคผนวก', 'Cover + 5 chapters + appendix'),
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
    'title' => $tr('เลือกแม่แบบรายงานตามรูปแบบงานวิชาการ', 'Select a report template aligned with academic writing formats'),
    'desc' => $tr('เลือก Template ที่เหมาะกับงานของคุณ จากนั้นกรอกข้อมูล เลือกบรรณานุกรมจากโครงการ แล้ว Export เป็น Word หรือ PDF ได้ทันที', 'Choose the template that fits your work, fill in the details, select bibliography entries from a project, and export to Word or PDF immediately.'),
    'guestMode' => $tr('โหมดทดลองใช้งาน', 'Guest Mode'),
    'guestWarningTitle' => $tr('ทดลองใช้แม่แบบได้ทันทีโดยไม่ต้องเข้าสู่ระบบ', 'Try the report templates immediately without signing in'),
    'guestWarningDesc' => $tr('โหมดทดลองไม่สามารถดึงรายการบรรณานุกรมจากโครงการ และข้อมูลที่กรอกอาจหายเมื่อออกจากหน้า รีเฟรช หรือปิดเบราว์เซอร์ สมัครสมาชิกเพื่อบันทึกงานและใช้งานได้ครบกว่าเดิม', 'Guest mode cannot import bibliography entries from projects, and any data you enter may be lost when you leave the page, refresh, or close the browser. Sign up to save your work and unlock the full workflow.'),
    'guestSignup' => $tr('สมัครสมาชิก', 'Sign Up'),
    'guestLogin' => $tr('เข้าสู่ระบบ', 'Sign In'),
];
?>

<style>
    .template-page {
        background: transparent;
        padding-bottom: 80px;
    }

    .template-page-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .template-page-header h1 {
        font-size: clamp(1.7rem, 3vw, 2.35rem);
        font-weight: 800;
        color: #fff;
        margin: 0 0 12px;
        line-height: 1.2;
        white-space: nowrap;
        text-shadow: 0 12px 30px rgba(0, 0, 0, 0.22);
    }

    .template-page-header p {
        font-size: clamp(0.78rem, 1.45vw, 1rem);
        color: rgba(255,255,255,0.88);
        line-height: 1.6;
        margin: 0;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        max-width: 920px;
        margin-left: auto;
        margin-right: auto;
    }

    .template-hero-shell {

    padding: 146px 0 var(--space-26);
        min-height: 150px;
        align-items: flex-start;
    }

    .template-hero-shell .hero-content {
        margin-top: 30px;
    }

    .template-main {
        margin-top: 22px;
        position: relative;
        z-index: 100;
        padding: 0 20px 0;
    }

    .template-grid-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 50px;
    }

    .template-card {
        background: white;
        border-radius: 18px;
        border: 2px solid #F0EDFF;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
        position: relative;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        min-height: 390px;
        height: 100%;
    }

    .template-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(139, 92, 246, 0.15);
        border-color: var(--primary);
    }

    .template-card-header {
        padding: 24px 24px 16px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
        min-height: 112px;
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
        display: grid;
        align-content: start;
        gap: 4px;
        min-height: 60px;
        padding-right: 64px;
    }

    .template-card-title-area h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #111;
        margin: 0;
        line-height: 1.3;
    }

    .template-card-title-area p {
        font-size: 12px;
        color: #888;
        margin: 0;
        line-height: 1.4;
        min-height: 34px;
    }

    .template-card-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: #F3F4F6;
        color: #555;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        border: 1px solid transparent;
        max-width: 112px;
        text-align: center;
        line-height: 1.2;
    }

    .template-card-badge.badge-popular {
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .template-card-badge.badge-tooltip {
        max-width: 118px;
        padding: 6px 10px;
        border-radius: 14px;
        font-size: 10px;
        font-weight: 700;
        cursor: help;
    }

    .template-card-badge.badge-tooltip::after {
        content: attr(data-tooltip);
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 220px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(17, 24, 39, 0.96);
        color: #F9FAFB;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.45;
        text-align: left;
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.24);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px);
        transition: opacity 0.18s ease, transform 0.18s ease;
        white-space: normal;
        z-index: 5;
    }

    .template-card-badge.badge-tooltip::before {
        content: '';
        position: absolute;
        top: calc(100% + 4px);
        right: 18px;
        border-width: 6px;
        border-style: solid;
        border-color: transparent transparent rgba(17, 24, 39, 0.96) transparent;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-4px);
        transition: opacity 0.18s ease, transform 0.18s ease;
        z-index: 5;
    }

    .template-card-badge.badge-tooltip:hover::after,
    .template-card-badge.badge-tooltip:hover::before,
    .template-card-badge.badge-tooltip:focus-visible::after,
    .template-card-badge.badge-tooltip:focus-visible::before {
        opacity: 1;
        transform: translateY(0);
    }

    /* Mini paper preview */
    .template-card-preview {
        margin: 0 24px;
        background: #FAFAFA;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 16px 14px;
        position: relative;
        min-height: 132px;
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
        color: #999;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        margin-top: 10px;
    }

    /* Card footer */
    .template-card-footer {
        padding: 16px 24px 20px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
        align-items: stretch;
        margin-top: auto;
    }

    .template-chapters-info {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12px;
        color: #777;
        min-height: 32px;
    }

    .template-chapters-info i {
        font-size: 11px;
        color: #aaa;
    }

    .template-use-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        width: 100%;
        min-height: 44px;
        box-sizing: border-box;
    }

    .template-use-btn:hover {
        transform: scale(1.04);
        filter: brightness(1.08);
        color: white;
    }

    .template-tips {
        max-width: 1100px;
        margin: 0 auto;
        background: white;
        border-radius: 18px;
        padding: 28px 32px;
        border: 1.5px solid #E9E3FF;
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
        color: #111;
        margin: 0 0 4px;
    }

    .tip-text p {
        font-size: 12px;
        color: #777;
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

    .template-guest-btn {
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

    .template-guest-btn.primary {
        background: linear-gradient(135deg, #2b579a, #4573be);
        color: #fff;
        box-shadow: 0 12px 24px rgba(43, 87, 154, 0.18);
    }

    .template-guest-btn.secondary {
        background: rgba(255,255,255,0.8);
        color: #2f3642;
        border: 1px solid #d7dde7;
    }

    .template-guest-btn:hover {
        transform: translateY(-1px);
        color: inherit;
    }

    @media (max-width: 768px) {
        .template-page {
            padding-bottom: 56px;
        }
        .template-hero-shell {
            padding: 128px 0 var(--space-20);
            min-height: 330px;
        }
        .template-page-header h1 { font-size: 1.5rem; }
        .template-grid { grid-template-columns: 1fr; }
        .template-tips { grid-template-columns: 1fr; }
        .template-guest-banner {
            flex-direction: column;
        }
    }
</style>

<div class="template-page">
    <section class="hero template-hero-shell">
        <div class="hero-decorations">
            <i class="fas fa-file-lines decor-1"></i>
            <i class="fas fa-book-open decor-2"></i>
            <i class="fas fa-pen-ruler decor-3"></i>
            <i class="fas fa-scroll decor-4"></i>
            <i class="fas fa-graduation-cap decor-5"></i>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="template-page-header slide-up">
                    <h1><?php echo htmlspecialchars($templatePageText['title']); ?></h1>
                    <p><?php echo htmlspecialchars($templatePageText['desc']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <main class="template-main">
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
                    <a href="<?php echo SITE_URL; ?>/register.php" class="template-guest-btn primary"><?php echo htmlspecialchars($templatePageText['guestSignup']); ?></a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="template-guest-btn secondary"><?php echo htmlspecialchars($templatePageText['guestLogin']); ?></a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Templates Grid -->
        <div class="template-grid-container">

            <div class="template-grid">
                <?php foreach ($templateCards as $card): ?>
                    <div class="template-card<?php echo !empty($card['cardClass']) ? ' ' . htmlspecialchars($card['cardClass']) : ''; ?>" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=<?php echo $card['id']; ?>'">
                        <div class="template-card-header">
                            <div class="template-card-icon" style="background: <?php echo $card['color']; ?>;">
                                <i class="fas <?php echo $card['icon']; ?>"></i>
                            </div>
                            <div class="template-card-title-area">
                                <h3><?php echo htmlspecialchars($card['title']); ?></h3>
                                <p><?php echo htmlspecialchars($card['subtitle']); ?></p>
                            </div>
                            <?php if ($card['badge']): ?>
                                <span class="template-card-badge<?php echo !empty($card['badgeClass']) ? ' ' . htmlspecialchars($card['badgeClass']) : ''; ?>"<?php echo !empty($card['badgeTooltip']) ? ' data-tooltip="' . htmlspecialchars($card['badgeTooltip']) . '"' : ' title="' . htmlspecialchars($card['badge']) . '"'; ?><?php echo $card['badgeStyle'] ? ' style="' . $card['badgeStyle'] . '"' : ''; ?> tabindex="0"><?php echo htmlspecialchars($card['badge']); ?></span>
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
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
