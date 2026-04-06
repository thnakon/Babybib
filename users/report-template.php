<?php

/**
 * Babybib - Report Template Selection Page
 * =========================================
 * ให้ผู้ใช้เลือก Template รายงานสำหรับเริ่มต้นทำรายงาน
 */

$pageTitle = 'Template รายงาน';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

requireAuth();

$userId = getCurrentUserId();

// Get user's projects (for showing project count)
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
    $stmt->execute([$userId]);
    $projectCount = $stmt->fetch()['total'];
} catch (Exception $e) {
    $projectCount = 0;
}
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
            Template รายงาน
        </div>
        <h1>เริ่มต้นทำรายงานให้เป็นมืออาชีพ</h1>
        <p>เลือก Template ที่เหมาะกับงานของคุณ จากนั้นกรอกข้อมูล เลือกบรรณานุกรมจากโครงการ<br>แล้ว Export เป็น Word หรือ PDF ได้ทันที</p>

        <div class="template-stats">
            <div class="template-stat-item">
                <i class="fas fa-layer-group"></i>
                5 รูปแบบ Template
            </div>
            <div class="template-stat-item">
                <i class="fas fa-folder"></i>
                <?php echo $projectCount; ?> โครงการของคุณ
            </div>
            <div class="template-stat-item">
                <i class="fas fa-file-word"></i>
                Export Word & PDF
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="template-grid-container">

        <p class="section-title">
            <i class="fas fa-grip" style="color: var(--primary)"></i>
            เลือก Template ที่ต้องการ
        </p>

        <div class="template-grid">

            <!-- Academic General -->
            <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=academic_general'">
                <div class="template-card-header">
                    <div class="template-card-icon" style="background: linear-gradient(135deg, #8B5CF6, #6366F1);">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <div class="template-card-title-area">
                        <h3>รายงานวิชาการทั่วไป</h3>
                        <p>มาตรฐานสำหรับรายงานระดับมัธยม–อุดมศึกษา</p>
                    </div>
                    <span class="template-card-badge">ยอดนิยม</span>
                </div>
                <div class="template-card-preview" style="--card-color: #8B5CF6">
                    <div class="preview-section-label">หน้าปก</div>
                    <div class="preview-line bold color center"></div>
                    <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                    <div class="preview-section-label">บทที่ 1 บทนำ</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line long"></div>
                    <div class="preview-line medium"></div>
                    <div class="preview-section-label">บรรณานุกรม</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line indent medium"></div>
                </div>
                <div class="template-card-footer">
                    <div class="template-chapters-info">
                        <i class="fas fa-layer-group"></i>
                        หน้าปก + 3 บท + บรรณานุกรม
                    </div>
                    <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=academic_general" class="template-use-btn" style="background: linear-gradient(135deg, #8B5CF6, #6366F1);">
                        ใช้ Template นี้ <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Research Report -->
            <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=research'">
                <div class="template-card-header">
                    <div class="template-card-icon" style="background: linear-gradient(135deg, #3B82F6, #06B6D4);">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div class="template-card-title-area">
                        <h3>รายงานการวิจัย</h3>
                        <p>โครงสร้างสำหรับงานวิจัยระดับสูง 5 บท</p>
                    </div>
                </div>
                <div class="template-card-preview" style="--card-color: #3B82F6">
                    <div class="preview-section-label">หน้าปก + บทคัดย่อ</div>
                    <div class="preview-line bold color center"></div>
                    <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                    <div class="preview-section-label">5 บท (บทนำ → สรุป)</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line long"></div>
                    <div class="preview-line medium"></div>
                    <div class="preview-section-label">บรรณานุกรม + ภาคผนวก</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line indent medium"></div>
                </div>
                <div class="template-card-footer">
                    <div class="template-chapters-info">
                        <i class="fas fa-layer-group"></i>
                        หน้าปก + 5 บท + บรรณานุกรม
                    </div>
                    <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=research" class="template-use-btn" style="background: linear-gradient(135deg, #3B82F6, #06B6D4);">
                        ใช้ Template นี้ <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Internship Report -->
            <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=internship'">
                <div class="template-card-header">
                    <div class="template-card-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="template-card-title-area">
                        <h3>รายงานฝึกงาน / สหกิจ</h3>
                        <p>ครบถ้วนสำหรับรายงานฝึกประสบการณ์วิชาชีพ</p>
                    </div>
                </div>
                <div class="template-card-preview" style="--card-color: #10B981">
                    <div class="preview-section-label">หน้าปก (ชื่อองค์กร)</div>
                    <div class="preview-line bold color center"></div>
                    <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                    <div class="preview-section-label">5 บท + บรรณานุกรม</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line long"></div>
                    <div class="preview-line medium"></div>
                    <div class="preview-section-label">ภาคผนวก</div>
                    <div class="preview-line medium"></div>
                </div>
                <div class="template-card-footer">
                    <div class="template-chapters-info">
                        <i class="fas fa-layer-group"></i>
                        หน้าปก + 5 บท + ภาคผนวก
                    </div>
                    <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=internship" class="template-use-btn" style="background: linear-gradient(135deg, #10B981, #059669);">
                        ใช้ Template นี้ <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Project Report -->
            <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=project'">
                <div class="template-card-header">
                    <div class="template-card-icon" style="background: linear-gradient(135deg, #F59E0B, #F97316);">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <div class="template-card-title-area">
                        <h3>รายงานโครงการ</h3>
                        <p>สำหรับ Senior Project หรือโปรเจควิชาการ</p>
                    </div>
                </div>
                <div class="template-card-preview" style="--card-color: #F59E0B">
                    <div class="preview-section-label">หน้าปก + ทีมงาน</div>
                    <div class="preview-line bold color center"></div>
                    <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                    <div class="preview-section-label">5 บท + บรรณานุกรม</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line long"></div>
                    <div class="preview-line medium"></div>
                    <div class="preview-section-label">ภาคผนวก</div>
                    <div class="preview-line medium"></div>
                </div>
                <div class="template-card-footer">
                    <div class="template-chapters-info">
                        <i class="fas fa-layer-group"></i>
                        หน้าปก + 5 บท + ภาคผนวก
                    </div>
                    <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=project" class="template-use-btn" style="background: linear-gradient(135deg, #F59E0B, #F97316);">
                        ใช้ Template นี้ <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Thesis -->
            <div class="template-card" onclick="window.location='<?php echo SITE_URL; ?>/users/report-builder.php?template=thesis'">
                <div class="template-card-header">
                    <div class="template-card-icon" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="template-card-title-area">
                        <h3>วิทยานิพนธ์ / สารนิพนธ์</h3>
                        <p>โครงสร้างมาตรฐานระดับบัณฑิตศึกษา</p>
                    </div>
                    <span class="template-card-badge" style="background:#FEE2E2; color:#DC2626;">Graduate</span>
                </div>
                <div class="template-card-preview" style="--card-color: #EF4444">
                    <div class="preview-section-label">หน้าปก + กิตติกรรม + บทคัดย่อ</div>
                    <div class="preview-line bold color center"></div>
                    <div class="preview-line color center short" style="margin:4px auto 0;"></div>
                    <div class="preview-section-label">5 บท + บรรณานุกรม + ภาคผนวก</div>
                    <div class="preview-line full"></div>
                    <div class="preview-line long"></div>
                    <div class="preview-line indent medium"></div>
                </div>
                <div class="template-card-footer">
                    <div class="template-chapters-info">
                        <i class="fas fa-layer-group"></i>
                        หน้าปก + ส่วนนำ + 5 บท + ภาคผนวก
                    </div>
                    <a href="<?php echo SITE_URL; ?>/users/report-builder.php?template=thesis" class="template-use-btn" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                        ใช้ Template นี้ <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- Tips -->
        <div class="template-tips">
            <div class="tip-item">
                <div class="tip-icon">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="tip-text">
                    <h4>กรอกข้อมูลหน้าปก</h4>
                    <p>ระบุชื่อรายงาน ผู้จัดทำ วิชา อาจารย์ผู้สอน และข้อมูลสถาบัน เพื่อสร้างหน้าปกแบบมืออาชีพ</p>
                </div>
            </div>
            <div class="tip-item">
                <div class="tip-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="tip-text">
                    <h4>เลือกบรรณานุกรมจากโครงการ</h4>
                    <p>เลือกโครงการที่มีรายการบรรณานุกรม เพื่อแทรกลงท้ายเอกสารโดยอัตโนมัติ</p>
                </div>
            </div>
            <div class="tip-item">
                <div class="tip-icon">
                    <i class="fas fa-file-export"></i>
                </div>
                <div class="tip-text">
                    <h4>Export Word & PDF</h4>
                    <p>ดาวน์โหลดเป็นไฟล์ Word (.docx) ที่แก้ไขได้ หรือ PDF สำหรับส่งงาน</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
