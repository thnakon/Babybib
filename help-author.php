<?php

/**
 * Babybib - Help: Author Citation Guide
 * =====================================
 */

require_once 'includes/session.php';

$pageTitle = 'ชื่อผู้แต่งในการอ้างอิง';
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
            <i class="fas fa-user-pen"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'การเขียนชื่อผู้แต่ง' : 'Author Name Guidelines'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'คู่มือการเขียนชื่อผู้แต่งตามมาตรฐาน APA 7<sup>th</sup>' : 'Guide to writing author names according to APA 7<sup>th</sup> standards'; ?></p>
    </div>

    <!-- หลักการพื้นฐาน -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-lightbulb"></i>
            <?php echo $currentLang === 'th' ? 'หลักการพื้นฐาน' : 'Basic Principles'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'การเขียนชื่อผู้แต่งตามมาตรฐาน APA 7<sup>th</sup> มีหลักการพื้นฐานดังนี้:'
                    : 'APA 7<sup>th</sup> author name formatting follows these basic principles:'; ?></p>
            <ul>
                <li><?php echo $currentLang === 'th'
                        ? '<strong>ชื่อภาษาไทย:</strong> เขียนชื่อเต็ม ไม่ต้องกลับนามสกุลก่อน เช่น ชื่อ นามสกุล.'
                        : '<strong>Thai names:</strong> Write full name without inverting, e.g., First name Last name.'; ?></li>
                <li><?php echo $currentLang === 'th'
                        ? '<strong>ชื่อภาษาอังกฤษ:</strong> กลับนามสกุลก่อน แล้วตามด้วยอักษรย่อชื่อต้น เช่น Lastname, F. M.'
                        : '<strong>English names:</strong> Invert with last name first, followed by initials, e.g., Lastname, F. M.'; ?></li>
            </ul>
        </div>
    </div>

    <!-- ผู้แต่งคนเดียว -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-user"></i>
            <?php echo $currentLang === 'th' ? 'ผู้แต่งคนเดียว' : 'Single Author'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'เมื่อมีผู้แต่งเพียงคนเดียว ให้เขียนชื่อผู้แต่งตามปกติ:'
                    : 'For a single author, write the name in standard format:'; ?></p>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่างภาษาไทย' : 'Thai Example'; ?></div>
            <div class="example-box">สมชาย ใจดี. (2567). <em>ชื่อหนังสือ</em>. สำนักพิมพ์.</div>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่างภาษาอังกฤษ' : 'English Example'; ?></div>
            <div class="example-box">Smith, J. A. (2024). <em>Book title</em>. Publisher.</div>
        </div>
    </div>

    <!-- ผู้แต่งหลายคน -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-users"></i>
            <?php echo $currentLang === 'th' ? 'ผู้แต่งหลายคน' : 'Multiple Authors'; ?>
        </div>
        <div class="help-content">
            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'จำนวนผู้แต่ง' : 'Number of Authors'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'รูปแบบการเขียน' : 'Format'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2 <?php echo $currentLang === 'th' ? 'คน' : 'authors'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้ "และ" คั่นระหว่างผู้แต่งทั้งสอง เช่น สมชาย ใจดี และ สมหญิง รักดี.'
                                    : 'Use "and" between both authors, e.g., Smith, J. A., & Doe, B. C.'; ?></td>
                        </tr>
                        <tr>
                            <td>3-20 <?php echo $currentLang === 'th' ? 'คน' : 'authors'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'เขียนชื่อทุกคน คั่นด้วยเครื่องหมายจุลภาค และใส่ "และ" ก่อนคนสุดท้าย'
                                    : 'List all names, separated by commas, with "and" before the last one'; ?></td>
                        </tr>
                        <tr>
                            <td>21+ <?php echo $currentLang === 'th' ? 'คน' : 'authors'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'เขียน 19 คนแรก ใส่จุดไข่ปลา แล้วเขียนชื่อคนสุดท้าย'
                                    : 'List first 19 names, add ellipsis, then the last author name'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่าง 3 คน' : 'Example with 3 authors'; ?></div>
            <div class="example-box">สมชาย ใจดี, สมหญิง รักดี และ สมศักดิ์ มั่นคง. (2567). <em>ชื่อหนังสือ</em>. สำนักพิมพ์.</div>
        </div>
    </div>

    <!-- กรณีพิเศษ -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-star"></i>
            <?php echo $currentLang === 'th' ? 'กรณีพิเศษ' : 'Special Cases'; ?>
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
                            <td><?php echo $currentLang === 'th' ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'Anonymous'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้ชื่อเรื่องแทนชื่อผู้แต่ง'
                                    : 'Use the title in place of author'; ?></td>
                            <td><em>ชื่อหนังสือ</em>. (2567).</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'นามแฝง' : 'Pseudonym'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้นามแฝงตามที่ปรากฏ'
                                    : 'Use the pseudonym as it appears'; ?></td>
                            <td>ทมยันตี. (2560).</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'พระสงฆ์' : 'Buddhist Monk'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้สมณศักดิ์หรือฉายา'
                                    : 'Use ecclesiastical title or Dharma name'; ?></td>
                            <td>พระพรหมคุณาภรณ์ (ป.อ. ปยุตฺโต). (2564).</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'ราชสกุล' : 'Royal Title'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้พระนามหรือพระฐานันดรศักดิ์'
                                    : 'Use royal name or title'; ?></td>
                            <td>สมเด็จพระกนิษฐาธิราชเจ้า กรมสมเด็จพระเทพรัตนราชสุดาฯ. (2566).</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'หน่วยงาน/องค์กร' : 'Organization'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใช้ชื่อหน่วยงานเป็นผู้แต่ง'
                                    : 'Use organization name as author'; ?></td>
                            <td>กระทรวงศึกษาธิการ. (2567).</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'บรรณาธิการ' : 'Editor'; ?></td>
                            <td><?php echo $currentLang === 'th'
                                    ? 'ใส่ (บ.ก.) หรือ (Ed.) หลังชื่อ'
                                    : 'Add (Ed.) or (Eds.) after name'; ?></td>
                            <td>สมชาย ใจดี (บ.ก.). (2567).</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- การอ้างอิงในเนื้อหา -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-quote-left"></i>
            <?php echo $currentLang === 'th' ? 'การอ้างอิงในเนื้อหา (In-text Citation)' : 'In-text Citation'; ?>
        </div>
        <div class="help-content">
            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'จำนวนผู้แต่ง' : 'Authors'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'การอ้างอิงครั้งแรกและครั้งต่อไป' : 'First and Later Citations'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1 <?php echo $currentLang === 'th' ? 'คน' : 'author'; ?></td>
                            <td>(สมชาย ใจดี, 2567) <?php echo $currentLang === 'th' ? 'หรือ' : 'or'; ?> (Smith, 2024)</td>
                        </tr>
                        <tr>
                            <td>2 <?php echo $currentLang === 'th' ? 'คน' : 'authors'; ?></td>
                            <td>(สมชาย ใจดี และ สมหญิง รักดี, 2567) <?php echo $currentLang === 'th' ? 'หรือ' : 'or'; ?> (Smith & Doe, 2024)</td>
                        </tr>
                        <tr>
                            <td>3+ <?php echo $currentLang === 'th' ? 'คน' : 'authors'; ?></td>
                            <td>(สมชาย ใจดี และคณะ, 2567) <?php echo $currentLang === 'th' ? 'หรือ' : 'or'; ?> (Smith et al., 2024)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>