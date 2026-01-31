<?php

/**
 * Babybib - Help: Publisher Citation Guide
 * =========================================
 */

require_once 'includes/session.php';

$pageTitle = 'สำนักพิมพ์ในการอ้างอิง';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/help.css">';
require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}
?>



<main class="help-page slide-up">
    <a href="<?php echo SITE_URL; ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?>
    </a>

    <div class="help-header">
        <div class="help-icon">
            <i class="fas fa-building"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'สำนักพิมพ์ในการอ้างอิง' : 'Publisher in Citations'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'คู่มือการเขียนชื่อสำนักพิมพ์ตามรูปแบบ APA7th Edition' : 'Guide to writing publisher names in APA 7<sup>th</sup> format'; ?></p>
    </div>

    <!-- หลักการพื้นฐาน -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-lightbulb"></i>
            <?php echo $currentLang === 'th' ? 'หลักการพื้นฐาน' : 'Basic Principles'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'การเขียนชื่อสำนักพิมพ์ตามรูปแบบ APA7th Edition มีหลักการดังนี้:'
                    : 'APA 7<sup>th</sup> publisher formatting follows these principles:'; ?></p>
            <ul>
                <li><?php echo $currentLang === 'th'
                        ? '<strong>เขียนชื่อสำนักพิมพ์ตามที่ปรากฏ</strong> - ใช้ชื่อเต็มตามที่ระบุในหน้าปกในของหนังสือ'
                        : '<strong>Write the publisher name as it appears</strong> - Use the full name as stated on the title page'; ?></li>
                <li><?php echo $currentLang === 'th'
                        ? '<strong>ไม่ต้องใส่สถานที่พิมพ์</strong> - ใน APA7th Edition ไม่ต้องระบุเมืองหรือประเทศของสำนักพิมพ์อีกต่อไป'
                        : '<strong>No publisher location needed</strong> - APA 7<sup>th</sup> no longer requires city or country of the publisher'; ?></li>
                <li><?php echo $currentLang === 'th'
                        ? '<strong>ไม่ต้องใส่คำว่า "สำนักพิมพ์" หรือ "Press"</strong> - ยกเว้นเป็นส่วนหนึ่งของชื่อจริง เช่น MIT Press'
                        : '<strong>Omit business type words</strong> - Remove "Publisher," "Inc.," "Ltd." unless part of the name like "MIT Press"'; ?></li>
            </ul>
        </div>
    </div>

    <!-- DO และ DON'T -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-check-double"></i>
            <?php echo $currentLang === 'th' ? 'ควรทำและไม่ควรทำ' : 'Do\'s and Don\'ts'; ?>
        </div>
        <div class="help-content">
            <div class="do-dont-grid">
                <div class="do-box">
                    <h4><i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'ควรทำ' : 'Do'; ?></h4>
                    <ul>
                        <li><?php echo $currentLang === 'th' ? 'สำนักพิมพ์จุฬาลงกรณ์มหาวิทยาลัย' : 'Pearson'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'แพรวสำนักพิมพ์' : 'Oxford University Press'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'นานมีบุ๊คส์' : 'MIT Press'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'สยามปริทัศน์' : 'Sage Publications'; ?></li>
                    </ul>
                </div>
                <div class="dont-box">
                    <h4><i class="fas fa-times-circle"></i> <?php echo $currentLang === 'th' ? 'ไม่ควรทำ' : 'Don\'t'; ?></h4>
                    <ul>
                        <li><?php echo $currentLang === 'th' ? 'สนพ.จุฬาฯ' : 'Pearson Education, Inc.'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'บริษัท แพรวสำนักพิมพ์ จำกัด' : 'Oxford University Press, Ltd.'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'กรุงเทพฯ: นานมีบุ๊คส์' : 'Cambridge, MA: MIT Press'; ?></li>
                        <li><?php echo $currentLang === 'th' ? 'สยามปริทัศน์ พับลิชชิ่ง' : 'Sage Publications Inc.'; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ประเภทของสำนักพิมพ์ -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-list"></i>
            <?php echo $currentLang === 'th' ? 'ประเภทของสำนักพิมพ์' : 'Types of Publishers'; ?>
        </div>
        <div class="help-content">
            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'ประเภท' : 'Type'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'วิธีเขียน' : 'How to Write'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'สำนักพิมพ์เอกชน' : 'Commercial Publishers'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'เขียนชื่อตามที่ปรากฏ ไม่ต้องใส่ บจก./Ltd.' : 'Write as appears, omit Inc./Ltd.'; ?></td>
                            <td>แพรวสำนักพิมพ์, Penguin Random House</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'สำนักพิมพ์มหาวิทยาลัย' : 'University Press'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'คงคำว่า "Press" ไว้เพราะเป็นส่วนหนึ่งของชื่อ' : 'Keep "Press" as it\'s part of the name'; ?></td>
                            <td>สำนักพิมพ์จุฬาลงกรณ์มหาวิทยาลัย, Cambridge University Press</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'หน่วยงานรัฐ' : 'Government Agency'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ใช้ชื่อหน่วยงานเต็ม' : 'Use full agency name'; ?></td>
                            <td>กระทรวงศึกษาธิการ, U.S. Department of Education</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'องค์กรไม่แสวงหากำไร' : 'Non-profit Organizations'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ใช้ชื่อองค์กรเต็ม' : 'Use full organization name'; ?></td>
                            <td>สมาคมจิตแพทย์แห่งประเทศไทย, American Psychological Association</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'ผู้แต่งเป็นผู้จัดพิมพ์เอง' : 'Self-Published'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ใส่คำว่า "ผู้แต่ง" หรือ "Author"' : 'Use "Author" as publisher'; ?></td>
                            <td>ผู้แต่ง, Author</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- สำนักพิมพ์หลายแห่ง -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-users"></i>
            <?php echo $currentLang === 'th' ? 'สำนักพิมพ์หลายแห่ง' : 'Multiple Publishers'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'หากหนังสือมีสำนักพิมพ์มากกว่าหนึ่งแห่ง ให้เขียนคั่นด้วยเครื่องหมาย semicolon (;)'
                    : 'If a work has more than one publisher, separate them with a semicolon (;)'; ?></p>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
            <div class="example-box">Smith, J. A. (2024). <em>Book title</em>. Publisher One; Publisher Two.</div>

            <div class="tip-box">
                <i class="fas fa-info-circle"></i>
                <div class="tip-box-content">
                    <strong><?php echo $currentLang === 'th' ? 'เคล็ดลับ:' : 'Tip:'; ?></strong>
                    <p style="margin-bottom: 0; margin-top: var(--space-2);">
                        <?php echo $currentLang === 'th'
                            ? 'เรียงลำดับสำนักพิมพ์ตามที่ปรากฏในหนังสือ โดยปกติสำนักพิมพ์ที่มีบทบาทหลักจะถูกระบุก่อน'
                            : 'List publishers in the order they appear in the work. The primary publisher is usually listed first.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- สำนักพิมพ์กับผู้แต่งเป็นคนเดียวกัน -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-arrows-left-right"></i>
            <?php echo $currentLang === 'th' ? 'ผู้แต่งและสำนักพิมพ์เป็นหน่วยงานเดียวกัน' : 'Author and Publisher Are the Same'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'เมื่อผู้แต่ง (หน่วยงาน/องค์กร) เป็นผู้จัดพิมพ์เอง ไม่ต้องเขียนซ้ำชื่อหน่วยงานในส่วนสำนักพิมพ์ ให้เว้นว่างไว้หรือไม่ต้องใส่'
                    : 'When the author (organization) is also the publisher, do not repeat the name. Omit the publisher element.'; ?></p>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่างภาษาไทย' : 'Thai Example'; ?></div>
            <div class="example-box">กระทรวงศึกษาธิการ. (2567). <em>รายงานประจำปี 2567</em>.</div>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่างภาษาอังกฤษ' : 'English Example'; ?></div>
            <div class="example-box">World Health Organization. (2023). <em>World health report 2023</em>.</div>

            <div class="tip-box">
                <i class="fas fa-check"></i>
                <div class="tip-box-content">
                    <strong><?php echo $currentLang === 'th' ? 'สังเกต:' : 'Notice:'; ?></strong>
                    <p style="margin-bottom: 0; margin-top: var(--space-2);">
                        <?php echo $currentLang === 'th'
                            ? 'ไม่มีการใส่ชื่อสำนักพิมพ์ซ้ำหลังชื่อเรื่อง เพราะเป็นหน่วยงานเดียวกับผู้แต่ง'
                            : 'No publisher is listed after the title because the author and publisher are the same organization.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- เปรียบเทียบ APA 6 และ APA 7<sup>th</sup> -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-code-compare"></i>
            <?php echo $currentLang === 'th' ? 'เปรียบเทียบ APA 6 กับ APA7th Edition' : 'APA 6 vs APA 7<sup>th</sup> Comparison'; ?>
        </div>
        <div class="help-content">
            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'รายการ' : 'Item'; ?></th>
                            <th>APA 6</th>
                            <th><?php echo $currentLang === 'th' ? 'APA7th Edition' : 'APA 7<sup>th</sup>'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'สถานที่พิมพ์' : 'Publisher Location'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ต้องใส่ (เมือง, รัฐ/ประเทศ)' : 'Required (City, State/Country)'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ไม่ต้องใส่' : 'Not required'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'รูปแบบ' : 'Format'; ?></td>
                            <td>กรุงเทพฯ: สำนักพิมพ์.</td>
                            <td>สำนักพิมพ์.</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'ผู้แต่ง=สำนักพิมพ์' : 'Author=Publisher'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'เขียน "Author" หรือ "ผู้แต่ง"' : 'Write "Author"'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ไม่ต้องใส่สำนักพิมพ์เลย' : 'Omit publisher entirely'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>