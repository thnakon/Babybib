<?php

/**
 * Babybib - Start Page (User Guide)
 * ===================================
 */

$pageTitle = 'คู่มือการใช้งาน';
require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}
?>

<style>
    .start-page {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--space-8) var(--space-4);
    }

    .start-header {
        text-align: center;
        margin-bottom: var(--space-10);
    }

    .start-icon {
        width: 100px;
        height: 100px;
        background: var(--primary-gradient);
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        margin: 0 auto var(--space-5);
        box-shadow: 0 16px 32px rgba(139, 92, 246, 0.3);
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .start-header h1 {
        font-size: var(--text-3xl);
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .start-header p {
        color: var(--text-secondary);
        font-size: var(--text-lg);
    }

    .step-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        padding: 0;
        margin-bottom: var(--space-6);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        overflow: hidden;
        position: relative;
    }

    .step-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--primary-gradient);
    }

    .step-content {
        display: flex;
        gap: var(--space-6);
        padding: var(--space-6) var(--space-6) var(--space-6) calc(var(--space-6) + 5px);
        align-items: flex-start;
    }

    .step-number {
        width: 64px;
        height: 64px;
        min-width: 64px;
        background: var(--primary-gradient);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
        font-weight: 800;
        box-shadow: 0 8px 16px rgba(139, 92, 246, 0.25);
    }

    .step-info h3 {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .step-info>p {
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: var(--space-4);
    }

    .feature-tags {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-2);
    }

    .feature-tag {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-3);
        background: var(--primary-light);
        color: var(--primary);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
        font-weight: 500;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: var(--space-3);
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3);
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        font-size: var(--text-sm);
    }

    .feature-item i {
        color: var(--primary);
        font-size: 16px;
    }

    .action-tags {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-3);
    }

    .action-tag {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-lg);
        font-size: var(--text-sm);
        font-weight: 500;
    }

    .action-tag.preview {
        background: #d1fae5;
        color: #059669;
    }

    .action-tag.copy {
        background: #dbeafe;
        color: #2563eb;
    }

    .action-tag.save {
        background: var(--primary-light);
        color: var(--primary);
    }

    .cta-section {
        text-align: center;
        background: linear-gradient(135deg, var(--primary-light), #DDD6FE);
        border-radius: var(--radius-xl);
        padding: var(--space-8);
        margin-top: var(--space-8);
    }

    .cta-section h2 {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: var(--space-3);
    }

    .cta-section p {
        color: var(--text-secondary);
        margin-bottom: var(--space-5);
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--primary);
        font-weight: 500;
        margin-bottom: var(--space-6);
    }

    .back-btn:hover {
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        .step-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .step-info h3,
        .step-info p {
            text-align: center;
        }

        .feature-tags,
        .action-tags {
            justify-content: center;
        }
    }
</style>

<main class="start-page slide-up">
    <a href="<?php echo SITE_URL; ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?>
    </a>

    <div class="start-header">
        <div class="start-icon">
            <i class="fas fa-rocket"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'คู่มือการใช้งาน Babybib' : 'How to Use Babybib'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'เรียนรู้การสร้างบรรณานุกรมตามรูปแบบ APA7th Edition ได้ง่ายๆ ใน 4 ขั้นตอน' : 'Learn to create APA 7<sup>th</sup> bibliographies easily in 4 steps'; ?></p>
    </div>

    <!-- Step 1 -->
    <div class="step-card slide-up stagger-1">
        <div class="step-content">
            <div class="step-number">1</div>
            <div class="step-info">
                <h3><?php echo $currentLang === 'th' ? 'เลือกประเภททรัพยากร' : 'Select Resource Type'; ?></h3>
                <p><?php echo $currentLang === 'th'
                        ? 'เลือกประเภทของแหล่งข้อมูลที่คุณต้องการสร้างบรรณานุกรม เช่น หนังสือ บทความวารสาร เว็บไซต์ หรือวิดีโอออนไลน์ Babybib รองรับทรัพยากรมากกว่า 30 ประเภท'
                        : 'Choose the type of source you want to create a bibliography for, such as book, journal article, website, or online video. Babybib supports over 30 resource types.'; ?></p>
                <div class="feature-tags">
                    <span class="feature-tag"><i class="fas fa-book"></i> <?php echo $currentLang === 'th' ? 'หนังสือ' : 'Book'; ?></span>
                    <span class="feature-tag"><i class="fas fa-newspaper"></i> <?php echo $currentLang === 'th' ? 'วารสาร' : 'Journal'; ?></span>
                    <span class="feature-tag"><i class="fas fa-globe"></i> <?php echo $currentLang === 'th' ? 'เว็บไซต์' : 'Website'; ?></span>
                    <span class="feature-tag"><i class="fab fa-youtube"></i> YouTube</span>
                    <span class="feature-tag"><i class="fas fa-plus"></i> <?php echo $currentLang === 'th' ? 'อื่นๆ 30+' : '30+ more'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2 -->
    <div class="step-card slide-up stagger-2">
        <div class="step-content">
            <div class="step-number">2</div>
            <div class="step-info">
                <h3><?php echo $currentLang === 'th' ? 'เลือกภาษาและกรอกข้อมูล' : 'Select Language & Fill Information'; ?></h3>
                <p><?php echo $currentLang === 'th'
                        ? 'เลือกภาษาที่ต้องการ (ไทย/อังกฤษ) แล้วกรอกข้อมูลของทรัพยากร ระบบจะแสดงเฉพาะฟิลด์ที่จำเป็นสำหรับประเภททรัพยากรที่คุณเลือก'
                        : 'Choose your preferred language (Thai/English) and fill in the resource information. The system will only show fields required for your selected resource type.'; ?></p>
                <div class="feature-grid">
                    <div class="feature-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo $currentLang === 'th' ? 'ผู้แต่ง' : 'Author'; ?></span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo $currentLang === 'th' ? 'ปี' : 'Year'; ?></span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-heading"></i>
                        <span><?php echo $currentLang === 'th' ? 'ชื่อเรื่อง' : 'Title'; ?></span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-building"></i>
                        <span><?php echo $currentLang === 'th' ? 'สำนักพิมพ์' : 'Publisher'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3 -->
    <div class="step-card slide-up stagger-3">
        <div class="step-content">
            <div class="step-number">3</div>
            <div class="step-info">
                <h3><?php echo $currentLang === 'th' ? 'ดูตัวอย่างแบบเรียลไทม์' : 'View Real-time Preview'; ?></h3>
                <p><?php echo $currentLang === 'th'
                        ? 'ขณะกรอกข้อมูล คุณจะเห็นตัวอย่างบรรณานุกรมและการอ้างอิงทั้งแบบวงเล็บและแบบ Narrative แบบเรียลไทม์ สามารถคัดลอกได้ทันที'
                        : 'As you fill in the information, you will see a real-time preview of the bibliography and both parenthetical and narrative citations. You can copy them immediately.'; ?></p>
                <div class="action-tags">
                    <span class="action-tag preview">
                        <i class="fas fa-eye"></i> <?php echo $currentLang === 'th' ? 'ตัวอย่างทันที' : 'Instant Preview'; ?>
                    </span>
                    <span class="action-tag copy">
                        <i class="fas fa-copy"></i> <?php echo $currentLang === 'th' ? 'คัดลอกได้เลย' : 'Copy Instantly'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4 -->
    <div class="step-card slide-up stagger-4">
        <div class="step-content">
            <div class="step-number">4</div>
            <div class="step-info">
                <h3><?php echo $currentLang === 'th' ? 'บันทึกและจัดการ' : 'Save & Manage'; ?></h3>
                <p><?php echo $currentLang === 'th'
                        ? 'สมัครสมาชิกเพื่อบันทึกบรรณานุกรม จัดกลุ่มเป็นโครงการ และส่งออกเป็นไฟล์ Word หรือ PDF พร้อมจัดรูปแบบตามรูปแบบ APA7th Edition โดยอัตโนมัติ'
                        : 'Register to save your bibliographies, organize them into projects, and export to Word or PDF files with automatic APA 7<sup>th</sup> formatting.'; ?></p>
                <div class="action-tags">
                    <span class="action-tag save">
                        <i class="fas fa-save"></i> <?php echo $currentLang === 'th' ? 'บันทึก' : 'Save'; ?>
                    </span>
                    <span class="action-tag save">
                        <i class="fas fa-folder"></i> <?php echo $currentLang === 'th' ? 'โครงการ' : 'Projects'; ?>
                    </span>
                    <span class="action-tag save">
                        <i class="fas fa-file-word"></i> Word
                    </span>
                    <span class="action-tag save">
                        <i class="fas fa-file-pdf"></i> PDF
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section slide-up">
        <h2><?php echo $currentLang === 'th' ? 'พร้อมเริ่มต้นแล้วหรือยัง?' : 'Ready to Get Started?'; ?></h2>
        <p><?php echo $currentLang === 'th' ? 'สร้างบรรณานุกรมตามรูปแบบ APA7th Edition ได้ทันที ไม่ต้องสมัครสมาชิกก็ใช้งานได้เลย!' : 'Create APA 7<sup>th</sup> bibliographies instantly. No registration required to get started!'; ?></p>
        <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-primary btn-lg">
            <i class="fas fa-wand-magic-sparkles"></i>
            <?php echo $currentLang === 'th' ? 'เริ่มสร้างบรรณานุกรม' : 'Start Creating Bibliography'; ?>
        </a>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>