<?php

/**
 * Babybib - Sort Page (APA Sorting Guide)
 * =========================================
 */

$pageTitle = 'การเรียงลำดับบรรณานุกรม';
require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}
?>

<style>
    .sort-page {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--space-8) var(--space-4);
    }

    .sort-header {
        text-align: center;
        margin-bottom: var(--space-10);
    }

    .sort-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #6366f1, #8B5CF6);
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        margin: 0 auto var(--space-5);
        box-shadow: 0 16px 32px rgba(99, 102, 241, 0.3);
    }

    .sort-header h1 {
        font-size: var(--text-3xl);
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .sort-header p {
        color: var(--text-secondary);
        font-size: var(--text-lg);
    }

    .rule-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-6);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        overflow: hidden;
    }

    .rule-header {
        background: linear-gradient(135deg, #6366f1, #8B5CF6);
        color: white;
        padding: var(--space-4) var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .rule-number {
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

    .rule-title {
        font-size: var(--text-lg);
        font-weight: 600;
    }

    .rule-body {
        padding: var(--space-5);
    }

    .rule-body>p {
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: var(--space-4);
    }

    .rule-list {
        list-style: none;
        padding: 0;
        margin: 0 0 var(--space-4) 0;
    }

    .rule-list li {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        padding: var(--space-3) 0;
        border-bottom: 1px solid var(--border-light);
    }

    .rule-list li:last-child {
        border-bottom: none;
    }

    .rule-list li i {
        color: #6366f1;
        margin-top: 4px;
    }

    .example-box {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-top: var(--space-4);
    }

    .example-label {
        font-size: var(--text-sm);
        font-weight: 600;
        color: #6366f1;
        margin-bottom: var(--space-3);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .example-content {
        font-family: 'Tahoma', serif;
        font-size: 18px;
        line-height: 1.8;
    }

    .example-content p {
        padding-left: 2em;
        text-indent: -2em;
        margin-bottom: var(--space-2);
    }

    .lang-compare {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-4);
        margin-top: var(--space-4);
    }

    .lang-box {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
    }

    .lang-box-header {
        font-weight: 600;
        margin-bottom: var(--space-3);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .lang-box-content {
        font-family: 'Tahoma', serif;
        font-size: 18px;
    }

    .lang-box-content p {
        margin-bottom: var(--space-2);
    }

    .lang-box-content strong {
        color: #6366f1;
    }

    .info-box {
        background: linear-gradient(135deg, #DDD6FE, #C4B5FD);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-top: var(--space-4);
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
    }

    .info-box i {
        color: #8B5CF6;
        font-size: 20px;
        margin-top: 2px;
    }

    .info-box-content {
        flex: 1;
        color: #3730a3;
    }

    .info-box-content strong {
        color: #6366F1;
    }

    .cta-section {
        text-align: center;
        background: linear-gradient(135deg, #DDD6FE, #C4B5FD);
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
        color: #6366f1;
        font-weight: 500;
        margin-bottom: var(--space-6);
    }

    .back-btn:hover {
        text-decoration: underline;
    }
</style>

<main class="sort-page slide-up">
    <a href="<?php echo SITE_URL; ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?>
    </a>

    <div class="sort-header">
        <div class="sort-icon">
            <i class="fas fa-sort-alpha-down"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'การเรียงลำดับบรรณานุกรม APA 7<sup>th</sup> Edition' : 'APA 7<sup>th</sup> Bibliography Sorting Rules'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'หลักการจัดเรียงรายการอ้างอิงที่ถูกต้องตามรูปแบบ APA 7<sup>th</sup> Edition' : 'Correct reference list ordering principles according to APA 7<sup>th</sup> standards'; ?></p>
    </div>

    <!-- Rule 1: Alphabetical -->
    <div class="rule-card slide-up stagger-1">
        <div class="rule-header">
            <div class="rule-number">1</div>
            <div class="rule-title"><?php echo $currentLang === 'th' ? 'เรียงตามตัวอักษร' : 'Alphabetical Order'; ?></div>
        </div>
        <div class="rule-body">
            <p><?php echo $currentLang === 'th'
                    ? 'เรียงลำดับรายการบรรณานุกรมตามชื่อผู้แต่งคนแรก โดยแยกภาษาตามข้อกำหนดของสถาบัน:'
                    : 'Sort bibliography entries by the first author\'s name, separating languages as per institution guidelines:'; ?></p>

            <ul class="rule-list">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong><?php echo $currentLang === 'th' ? 'ภาษาไทย:' : 'Thai:'; ?></strong>
                        <?php echo $currentLang === 'th' ? ' เรียงจาก ก ถึง ฮ' : ' Sort from ก to ฮ'; ?>
                    </div>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong><?php echo $currentLang === 'th' ? 'ภาษาอังกฤษ:' : 'English:'; ?></strong>
                        <?php echo $currentLang === 'th' ? ' เรียงจาก A ถึง Z' : ' Sort from A to Z'; ?>
                    </div>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <?php echo $currentLang === 'th'
                            ? 'โดยทั่วไป รายการภาษาไทยจะมาก่อนภาษาอังกฤษ (หรือตามข้อกำหนดของสถาบัน)'
                            : 'Generally, Thai entries come before English entries (or as per institution guidelines)'; ?>
                    </div>
                </li>
            </ul>

            <div class="example-box">
                <div class="example-label"><i class="fas fa-lightbulb"></i> <?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
                <div class="example-content">
                    <p>กมลา สมใจ. (2565). ...</p>
                    <p>ชนิดา รักดี. (2564). ...</p>
                    <p>สมศักดิ์ จริงใจ. (2566). ...</p>
                    <p style="margin-top: var(--space-3);">Anderson, J. (2023). ...</p>
                    <p>Smith, M. (2022). ...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule 2: Same Author -->
    <div class="rule-card slide-up stagger-2">
        <div class="rule-header">
            <div class="rule-number">2</div>
            <div class="rule-title"><?php echo $currentLang === 'th' ? 'ผู้แต่งคนเดียวกัน หลายผลงาน' : 'Same Author, Multiple Works'; ?></div>
        </div>
        <div class="rule-body">
            <p><?php echo $currentLang === 'th'
                    ? 'เมื่อผู้แต่งคนเดียวกันมีหลายผลงาน ให้เรียงตามปีที่พิมพ์จากเก่าไปใหม่:'
                    : 'When the same author has multiple works, sort by publication year from oldest to newest:'; ?></p>

            <div class="example-box">
                <div class="example-label"><i class="fas fa-lightbulb"></i> <?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
                <div class="example-content">
                    <p>สมศรี ใจดี. (2560). <em>ผลงานแรก</em>. ...</p>
                    <p>สมศรี ใจดี. (2563). <em>ผลงานที่สอง</em>. ...</p>
                    <p>สมศรี ใจดี. (2566). <em>ผลงานล่าสุด</em>. ...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule 3: Same Author Same Year -->
    <div class="rule-card slide-up stagger-3">
        <div class="rule-header">
            <div class="rule-number">3</div>
            <div class="rule-title"><?php echo $currentLang === 'th' ? 'ผู้แต่งและปีเดียวกัน' : 'Same Author and Year'; ?></div>
        </div>
        <div class="rule-body">
            <p><?php echo $currentLang === 'th'
                    ? 'เมื่อผู้แต่งคนเดียวกันมีหลายผลงานในปีเดียวกัน ให้เพิ่มอักษรต่อท้ายปี และเรียงตามลำดับชื่อเรื่อง:'
                    : 'When the same author has multiple works in the same year, add a letter suffix and sort by title:'; ?></p>

            <div class="lang-compare">
                <div class="lang-box">
                    <div class="lang-box-header">🇹🇭 <?php echo $currentLang === 'th' ? 'ภาษาไทย' : 'Thai'; ?></div>
                    <div class="lang-box-content">
                        <p>สมชาย ใจดี. (2566<strong>ก</strong>). ...</p>
                        <p>สมชาย ใจดี. (2566<strong>ข</strong>). ...</p>
                        <p>สมชาย ใจดี. (2566<strong>ค</strong>). ...</p>
                    </div>
                </div>
                <div class="lang-box">
                    <div class="lang-box-header">🇺🇸 <?php echo $currentLang === 'th' ? 'ภาษาอังกฤษ' : 'English'; ?></div>
                    <div class="lang-box-content">
                        <p>Smith, J. (2023<strong>a</strong>). ...</p>
                        <p>Smith, J. (2023<strong>b</strong>). ...</p>
                        <p>Smith, J. (2023<strong>c</strong>). ...</p>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-magic"></i>
                <div class="info-box-content">
                    <strong><?php echo $currentLang === 'th' ? 'ฟีเจอร์อัตโนมัติ:' : 'Automatic Feature:'; ?></strong>
                    <?php echo $currentLang === 'th'
                        ? ' Babybib จะเพิ่มอักษรต่อท้ายให้โดยอัตโนมัติเมื่อพบผู้แต่งและปีซ้ำกันในโครงการเดียวกัน'
                        : ' Babybib will automatically add the letter suffix when duplicate author and year are found in the same project'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule 4: Different Authors Same Surname -->
    <div class="rule-card slide-up stagger-4">
        <div class="rule-header">
            <div class="rule-number">4</div>
            <div class="rule-title"><?php echo $currentLang === 'th' ? 'ผู้แต่งต่างคน นามสกุลเดียวกัน' : 'Different Authors, Same Surname'; ?></div>
        </div>
        <div class="rule-body">
            <p><?php echo $currentLang === 'th'
                    ? 'เมื่อผู้แต่งคนละคนมีนามสกุลเดียวกัน ให้เรียงตามอักษรย่อของชื่อ (Initials):'
                    : 'When different authors share the same surname, sort by their first name initials:'; ?></p>

            <div class="example-box">
                <div class="example-label"><i class="fas fa-lightbulb"></i> <?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
                <div class="example-content">
                    <p>Smith, A. (2022). ...</p>
                    <p>Smith, J. (2021). ...</p>
                    <p>Smith, M. (2023). ...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule 5: No Author -->
    <div class="rule-card slide-up">
        <div class="rule-header">
            <div class="rule-number">5</div>
            <div class="rule-title"><?php echo $currentLang === 'th' ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'No Author'; ?></div>
        </div>
        <div class="rule-body">
            <p><?php echo $currentLang === 'th'
                    ? 'หากไม่ปรากฏชื่อผู้แต่ง ให้ใช้ชื่อเรื่องในการเรียงลำดับ โดยไม่นับคำนำหน้าเช่น "The" หรือ "A":'
                    : 'If there is no author, use the title for alphabetizing, ignoring articles like "The" or "A":'; ?></p>

            <div class="example-box">
                <div class="example-label"><i class="fas fa-lightbulb"></i> <?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
                <div class="example-content">
                    <p><em>การศึกษาเรื่องสิ่งแวดล้อม</em>. (2565). ...</p>
                    <p><em>A study on environment</em>. (2023). ... <?php echo $currentLang === 'th' ? '(เรียงที่ S ไม่ใช่ A)' : '(Filed under S, not A)'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section slide-up">
        <h2><?php echo $currentLang === 'th' ? 'Babybib จัดเรียงให้อัตโนมัติ!' : 'Babybib Sorts Automatically!'; ?></h2>
        <p><?php echo $currentLang === 'th'
                ? 'ถ้าสมัครสมาชิกเมื่อคุณส่งออกบรรณานุกรมจากโครงการ Babybib จะจัดเรียงให้ถูกต้องตามหลักการข้างต้นโดยอัตโนมัติ'
                : 'When you export bibliography from a project, Babybib will automatically sort according to the rules above.'; ?></p>
        <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-primary btn-lg">
            <i class="fas fa-wand-magic-sparkles"></i>
            <?php echo $currentLang === 'th' ? 'เริ่มสร้างบรรณานุกรม' : 'Start Creating Bibliography'; ?>
        </a>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>