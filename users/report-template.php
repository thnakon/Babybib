<?php

/**
 * Babybib - Report Template Selection Page
 * =========================================
 * ให้ผู้ใช้เลือก Template รายงานสำหรับเริ่มต้นทำรายงาน
 */

require_once '../includes/session.php';
requireAuth();

$pageTitle = __('report_templates_page_title');

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();
$isEnglish = getCurrentLanguage() === 'en';
$tr = static function ($th, $en) use ($isEnglish) {
    return $isEnglish ? $en : $th;
};

// Get user's projects (for showing project count)
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
    $stmt->execute([$userId]);
    $projectCount = $stmt->fetch()['total'];
} catch (Exception $e) {
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
?>

<style>
    .template-page {
        min-height: calc(100vh - 80px);
        background: linear-gradient(135deg, #F5F3FF 0%, #EDE9FE 30%, #F9FAFB 70%);
        padding: 40px 20px 80px;
    }

    .template-page-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 50px;
    }

    .template-page-header .page-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary-light);
        color: var(--primary);
        border: 1.5px solid rgba(139, 92, 246, 0.3);
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .template-page-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        color: #000;
        margin: 0 0 12px;
        line-height: 1.2;
    }

    .template-page-header p {
        font-size: 1rem;
        color: #555;
        line-height: 1.6;
    }

    /* Stats row */
    .template-stats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .template-stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #666;
        background: white;
        padding: 8px 16px;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    .template-stat-item i {
        color: var(--primary);
    }

    /* Template Grid */
    .template-grid-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 20px;
        display: flex;
        align-items: center;
        gap: 8px;
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
        font-weight: 700;
        color: #111;
        margin: 0 0 4px;
        line-height: 1.3;
    }

    .template-card-title-area p {
        font-size: 12px;
        color: #888;
        margin: 0;
        line-height: 1.4;
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
    }

    /* Mini paper preview */
    .template-card-preview {
        margin: 0 24px;
        background: #FAFAFA;
        border: 1px solid #E5E7EB;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .template-chapters-info {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #777;
    }

    .template-chapters-info i {
        font-size: 11px;
        color: #aaa;
    }

    .template-use-btn {
        display: inline-flex;
        align-items: center;
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
    }

    .template-use-btn:hover {
        transform: scale(1.04);
        filter: brightness(1.08);
        color: white;
    }

    /* Tips section */
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

    @media (max-width: 768px) {
        .template-page-header h1 { font-size: 1.6rem; }
        .template-grid { grid-template-columns: 1fr; }
        .template-tips { grid-template-columns: 1fr; }
    }
</style>

<div class="template-page">

    <!-- Page Header -->
    <div class="template-page-header slide-up">
        <div class="page-badge">
            <i class="fas fa-file-lines"></i>
            <?php echo $tr('Template รายงาน', 'Report Templates'); ?>
        </div>
        <h1><?php echo $tr('เริ่มต้นทำรายงานให้เป็นมืออาชีพ', 'Start your report with a professional structure'); ?></h1>
        <p><?php echo $tr('เลือก Template ที่เหมาะกับงานของคุณ จากนั้นกรอกข้อมูล เลือกบรรณานุกรมจากโครงการ<br>แล้ว Export เป็น Word หรือ PDF ได้ทันที', 'Choose the template that fits your work, fill in the details, select bibliography entries from a project,<br>and export to Word or PDF immediately.'); ?></p>

        <div class="template-stats">
            <div class="template-stat-item">
                <i class="fas fa-layer-group"></i>
                <?php echo count($templateCards) . ' ' . $tr('รูปแบบ Template', 'template types'); ?>
            </div>
            <div class="template-stat-item">
                <i class="fas fa-folder"></i>
                <?php echo $projectCount . ' ' . $tr('โครงการของคุณ', 'your projects'); ?>
            </div>
            <div class="template-stat-item">
                <i class="fas fa-file-word"></i>
                <?php echo $tr('Export Word & PDF', 'Export Word & PDF'); ?>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="template-grid-container">

        <p class="section-title">
            <i class="fas fa-grip" style="color: var(--primary)"></i>
            <?php echo $tr('เลือก Template ที่ต้องการ', 'Choose the template you want'); ?>
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

<?php require_once '../includes/footer.php'; ?>
