<?php

/**
 * Babybib - Terms of Service
 * =========================
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

$pageTitle = 'ข้อกำหนดการใช้งาน';
require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}
?>

<style>
    .terms-page {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--space-8) var(--space-4);
    }

    .terms-header {
        text-align: center;
        margin-bottom: var(--space-10);
    }

    .terms-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        margin: 0 auto var(--space-5);
        box-shadow: 0 16px 32px rgba(139, 92, 246, 0.3);
    }

    .terms-header h1 {
        font-size: var(--text-3xl);
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .terms-header p {
        color: var(--text-secondary);
        font-size: var(--text-lg);
    }

    .section-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-6);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
        color: white;
        padding: var(--space-4) var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .section-number {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: var(--text-lg);
    }

    .section-title {
        font-size: var(--text-lg);
        font-weight: 600;
        color: white;
        /* Contrast fix */
        margin: 0;
    }

    .section-body {
        padding: var(--space-6);
    }

    .section-body p {
        color: var(--text-secondary);
        line-height: 1.8;
        margin-bottom: var(--space-4);
    }

    .section-body p:last-child {
        margin-bottom: 0;
    }

    .highlight-box {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-top: var(--space-4);
        border-left: 4px solid var(--primary);
    }

    .highlight-label {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--primary);
        margin-bottom: var(--space-2);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--primary);
        font-weight: 500;
        margin-bottom: var(--space-6);
        transition: all 0.2s;
    }

    .back-btn:hover {
        transform: translateX(-5px);
        text-decoration: none;
    }

    .footer-note {
        text-align: center;
        margin-top: var(--space-10);
        padding-top: var(--space-6);
        border-top: 1px solid var(--border-light);
        color: var(--text-tertiary);
        font-size: var(--text-sm);
    }

    .cta-section {
        text-align: center;
        background: linear-gradient(135deg, #EDE9FE, #DDD6FE);
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
</style>

<main class="terms-page slide-up">
    <a href="register.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <?php echo $currentLang === 'th' ? 'กลับไปยังหน้าลงทะเบียน' : 'Back to Registration'; ?>
    </a>

    <div class="terms-header">
        <div class="terms-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'ข้อกำหนดและเงื่อนไขการใช้งาน' : 'Terms and Conditions'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'กรุณาอ่านและทำความเข้าใจก่อนเริ่มต้นใช้งาน Babybib' : 'Please read and understand before using Babybib'; ?></p>
    </div>

    <!-- Section 1 -->
    <div class="section-card slide-up stagger-1">
        <div class="section-header">
            <div class="section-number">1</div>
            <h2 class="section-title"><?php echo $currentLang === 'th' ? 'การยอมรับข้อกำหนด' : 'Acceptance of Terms'; ?></h2>
        </div>
        <div class="section-body">
            <p>
                <?php echo $currentLang === 'th'
                    ? 'โดยการเข้าถึงหรือใช้งาน Babybib คุณตกลงที่จะผูกพันตามข้อกำหนดและเงื่อนไขเหล่านี้ หากคุณไม่ยอมรับข้อกำหนดใดๆ โปรดหยุดการใช้งานบริการทันที'
                    : 'By accessing or using Babybib, you agree to be bound by these terms. If you do not agree to any part of the terms, you must not use our service.'; ?>
            </p>
        </div>
    </div>

    <!-- Section 2 -->
    <div class="section-card slide-up stagger-2">
        <div class="section-header">
            <div class="section-number">2</div>
            <h2 class="section-title"><?php echo $currentLang === 'th' ? 'วัตถุประสงค์ของบริการ' : 'Purpose of Service'; ?></h2>
        </div>
        <div class="section-body">
            <p>
                <?php echo $currentLang === 'th'
                    ? 'Babybib เป็นเครื่องมือช่วยสร้างการอ้างอิงและบรรณานุกรมตามรูปแบบ APA7th Edition แม้ว่าเราจะพยายามให้ข้อมูลมีความถูกต้องที่สุด แต่ผู้ใช้มีหน้าที่รับผิดชอบในการตรวจสอบความถูกต้องขั้นสุดท้ายตามคู่มือมาตรฐาน'
                    : 'Babybib is a tool designed to assist in creating citations and bibliographies according to APA 7<sup>th</sup> Edition standards. While we strive for maximum accuracy, users are responsible for final verification against official standards.'; ?>
            </p>
            <div class="highlight-box">
                <div class="highlight-label"><i class="fas fa-info-circle"></i> <?php echo $currentLang === 'th' ? 'ข้อควรจำ' : 'Note'; ?></div>
                <p class="text-sm">
                    <?php echo $currentLang === 'th'
                        ? 'ผลลัพธ์ที่ได้จากโปรแกรมนี้เป็นเพียงส่วนช่วยอำนวยความสะดวก โปรดตรวจสอบความถูกต้องของชื่อผู้แต่งและชื่อเรื่องตามต้นฉบับเสมอ'
                        : 'Results from this tool are for convenience only. Always verify author names and titles against original sources.'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Section 3 -->
    <div class="section-card slide-up stagger-3">
        <div class="section-header">
            <div class="section-number">3</div>
            <h2 class="section-title"><?php echo $currentLang === 'th' ? 'นโยบายการเก็บรักษาข้อมูล' : 'Data Retention Policy'; ?></h2>
        </div>
        <div class="section-body">
            <p>
                <?php echo $currentLang === 'th'
                    ? 'เพื่อประสิทธิภาพของระบบ บรรณานุกรมที่ถูกสร้างและบันทึกไว้จะถูกเก็บรักษาเป็นเวลา 2 ปี (730 วัน) นับจากวันที่สร้าง'
                    : 'For system efficiency, created and saved bibliographies will be retained for 2 years (730 days) from the creation date.'; ?>
            </p>
            <div class="highlight-box" style="border-left-color: var(--danger);">
                <div class="highlight-label" style="color: var(--danger);"><i class="fas fa-trash-alt"></i> <?php echo $currentLang === 'th' ? 'นโยบายการลบข้อมูล' : 'Deletion Policy'; ?></div>
                <p class="text-sm">
                    <?php echo $currentLang === 'th'
                        ? 'ระบบจะทำการลบข้อมูลที่เก่ากว่ากำหนดโดยอัตโนมัติและไม่สามารถกู้คืนได้ โปรดสำรองข้อมูลหรือส่งออกบรรณานุกรมเป็นระยะ'
                        : 'Data older than this period will be automatically deleted and cannot be recovered. Please export your work regularly.'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Section 4 -->
    <div class="section-card slide-up stagger-4">
        <div class="section-header">
            <div class="section-number">4</div>
            <h2 class="section-title"><?php echo $currentLang === 'th' ? 'บัญชีผู้ใช้งาน' : 'User Accounts'; ?></h2>
        </div>
        <div class="section-body">
            <p>
                <?php echo $currentLang === 'th'
                    ? 'ผู้ใช้ต้องรักษาความลับของชื่อผู้ใช้และรหัสผ่าน และรับผิดชอบต่อกิจกรรมทั้งหมดที่เกิดขึ้นภายใต้บัญชีของตน'
                    : 'Users are responsible for maintaining the confidentiality of their account credentials and for all activities that occur under their account.'; ?>
            </p>
        </div>
    </div>

    <!-- Section 5 -->
    <div class="section-card slide-up">
        <div class="section-header">
            <div class="section-number">5</div>
            <h2 class="section-title"><?php echo $currentLang === 'th' ? 'ข้อจำกัดความรับผิดชอบ' : 'Limitation of Liability'; ?></h2>
        </div>
        <div class="section-body">
            <p>
                <?php echo $currentLang === 'th'
                    ? 'Babybib ให้บริการ "ตามที่เป็นอยู่" โดยไม่มีการรับประกัน Weจะไม่รับผิดชอบต่อความเสียหายใดๆ ที่เกิดจากการใช้บริการ'
                    : 'Babybib is provided "as is" without any warranties. We shall not be liable for any damages arising from the use of the service.'; ?>
            </p>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section slide-up">
        <h2><?php echo $currentLang === 'th' ? 'พร้อมเริ่มต้นใช้งานหรือไม่?' : 'Ready to Get Started?'; ?></h2>
        <p><?php echo $currentLang === 'th'
                ? 'สมัครสมาชิกเพื่อเริ่มบันทึกและจัดการบรรณานุกรมของคุณ'
                : 'Sign up to start saving and managing your bibliographies.'; ?></p>
        <a href="register.php" class="btn btn-primary btn-lg">
            <i class="fas fa-user-plus"></i>
            <?php echo $currentLang === 'th' ? 'ลงทะเบียนเดี๋ยวนี้' : 'Register Now'; ?>
        </a>
    </div>

    <div class="footer-note">
        <?php echo $currentLang === 'th' ? 'อัปเดตล่าสุด: ' : 'Last Updated: '; ?> 2024-12-29
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>