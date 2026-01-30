<?php

/**
 * Babybib - Help: Place Citation Guide
 * =====================================
 */

require_once 'includes/session.php';

$pageTitle = 'สถานที่ในการอ้างอิง';
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
            <i class="fas fa-location-dot"></i>
        </div>
        <h1><?php echo $currentLang === 'th' ? 'สถานที่ในการอ้างอิง' : 'Place of Publication'; ?></h1>
        <p><?php echo $currentLang === 'th' ? 'คู่มือการใส่สถานที่พิมพ์ตามมาตรฐาน APA 7<sup>th</sup>' : 'Guide to place of publication in APA 7<sup>th</sup> format'; ?></p>
    </div>

    <!-- ข้อมูลสำคัญ -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-info-circle"></i>
            <?php echo $currentLang === 'th' ? 'ข้อมูลสำคัญเกี่ยวกับ APA 7<sup>th</sup>' : 'Important APA 7<sup>th</sup> Update'; ?>
        </div>
        <div class="help-content">
            <div class="note-box">
                <i class="fas fa-lightbulb"></i>
                <div class="note-box-content">
                    <strong><?php echo $currentLang === 'th' ? 'หมายเหตุ:' : 'Note:'; ?></strong>
                    <p style="margin-bottom: 0; margin-top: var(--space-2);">
                        <?php echo $currentLang === 'th'
                            ? 'ใน APA 7<sup>th</sup> edition <strong>ไม่จำเป็นต้องใส่สถานที่พิมพ์</strong>สำหรับหนังสือและเอกสารที่ตีพิมพ์แล้ว ต่างจาก APA 6 ที่ต้องระบุเมืองและรัฐ/ประเทศ อย่างไรก็ตาม สำหรับงานบางประเภท เช่น วิทยานิพนธ์หรือเอกสารที่ไม่ได้เผยแพร่ ยังคงต้องระบุสถาบันที่ตั้งอยู่'
                            : 'In APA 7<sup>th</sup> edition, <strong>publisher location is no longer required</strong> for published books and documents. Unlike APA 6, you do not need to include city and state/country. However, for certain works like theses or unpublished documents, institution location may still be needed.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- กรณีที่ต้องใส่สถานที่ -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-check-circle"></i>
            <?php echo $currentLang === 'th' ? 'กรณีที่ต้องใส่สถานที่' : 'When to Include Location'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'แม้ว่า APA 7<sup>th</sup> จะไม่บังคับให้ใส่สถานที่พิมพ์สำหรับหนังสือทั่วไป แต่ยังมีบางกรณีที่ควรระบุสถานที่:'
                    : 'Although APA 7<sup>th</sup> does not require publisher location for general books, there are some cases where location should be included:'; ?></p>

            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'ประเภทเอกสาร' : 'Document Type'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'ต้องใส่สถานที่?' : 'Include Location?'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'หมายเหตุ' : 'Notes'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'หนังสือทั่วไป' : 'Published Books'; ?></td>
                            <td><span style="color: #dc2626;"><i class="fas fa-times"></i> <?php echo $currentLang === 'th' ? 'ไม่จำเป็น' : 'Not Required'; ?></span></td>
                            <td><?php echo $currentLang === 'th' ? 'ใส่แค่ชื่อสำนักพิมพ์' : 'Only publisher name needed'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'วิทยานิพนธ์/ดุษฎีนิพนธ์' : 'Thesis/Dissertation'; ?></td>
                            <td><span style="color: #16a34a;"><i class="fas fa-check"></i> <?php echo $currentLang === 'th' ? 'ควรใส่' : 'Recommended'; ?></span></td>
                            <td><?php echo $currentLang === 'th' ? 'ระบุมหาวิทยาลัยและที่ตั้ง' : 'Include university and location'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'เอกสารที่ไม่ได้เผยแพร่' : 'Unpublished Works'; ?></td>
                            <td><span style="color: #16a34a;"><i class="fas fa-check"></i> <?php echo $currentLang === 'th' ? 'ควรใส่' : 'Recommended'; ?></span></td>
                            <td><?php echo $currentLang === 'th' ? 'เพื่อระบุแหล่งที่มา' : 'To identify the source'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'รายงานของหน่วยงาน' : 'Agency Reports'; ?></td>
                            <td><span style="color: #f59e0b;"><i class="fas fa-exclamation"></i> <?php echo $currentLang === 'th' ? 'ขึ้นอยู่กับบริบท' : 'Context-dependent'; ?></span></td>
                            <td><?php echo $currentLang === 'th' ? 'ถ้าหน่วยงานไม่เป็นที่รู้จัก อาจใส่เพื่อความชัดเจน' : 'May include if agency is not well-known'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'งานประชุม/สัมมนา' : 'Conference Papers'; ?></td>
                            <td><span style="color: #16a34a;"><i class="fas fa-check"></i> <?php echo $currentLang === 'th' ? 'ต้องใส่' : 'Required'; ?></span></td>
                            <td><?php echo $currentLang === 'th' ? 'ระบุสถานที่จัดงาน' : 'Include conference location'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- รูปแบบการเขียนสถานที่ -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-pen"></i>
            <?php echo $currentLang === 'th' ? 'รูปแบบการเขียนสถานที่' : 'Location Format'; ?>
        </div>
        <div class="help-content">
            <p><?php echo $currentLang === 'th'
                    ? 'หากจำเป็นต้องใส่สถานที่ ให้เขียนตามรูปแบบต่อไปนี้:'
                    : 'If location is needed, use the following format:'; ?></p>

            <h4 style="margin: var(--space-4) 0 var(--space-2); color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'สถานที่ในประเทศไทย' : 'Locations in Thailand'; ?></h4>
            <ul>
                <li><?php echo $currentLang === 'th' ? 'ใส่ชื่อจังหวัดเท่านั้น เช่น กรุงเทพฯ, เชียงใหม่, ขอนแก่น' : 'Include only province name, e.g., Bangkok, Chiang Mai, Khon Kaen'; ?></li>
            </ul>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
            <div class="example-box">สมชาย ใจดี. (2567). <em>ชื่อวิทยานิพนธ์</em> [วิทยานิพนธ์ปริญญาโท, มหาวิทยาลัยเกษตรศาสตร์]. กรุงเทพฯ.</div>

            <h4 style="margin: var(--space-4) 0 var(--space-2); color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'สถานที่ในสหรัฐอเมริกา' : 'Locations in the United States'; ?></h4>
            <ul>
                <li><?php echo $currentLang === 'th' ? 'ใส่ชื่อเมืองและตัวย่อรัฐ เช่น New York, NY หรือ Los Angeles, CA' : 'Include city and state abbreviation, e.g., New York, NY or Los Angeles, CA'; ?></li>
            </ul>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
            <div class="example-box">Smith, J. A. (2024). <em>Thesis title</em> [Doctoral dissertation, University of California]. Los Angeles, CA.</div>

            <h4 style="margin: var(--space-4) 0 var(--space-2); color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'สถานที่ในประเทศอื่น' : 'Locations in Other Countries'; ?></h4>
            <ul>
                <li><?php echo $currentLang === 'th' ? 'ใส่ชื่อเมืองและประเทศ เช่น London, United Kingdom หรือ Tokyo, Japan' : 'Include city and country, e.g., London, United Kingdom or Tokyo, Japan'; ?></li>
            </ul>

            <div class="example-label"><?php echo $currentLang === 'th' ? 'ตัวอย่าง' : 'Example'; ?></div>
            <div class="example-box">Tanaka, H. (2023). <em>Research paper title</em>. Paper presented at International Conference. Tokyo, Japan.</div>
        </div>
    </div>

    <!-- เปรียบเทียบ APA 6 และ APA 7<sup>th</sup> -->
    <div class="help-section">
        <div class="help-section-title">
            <i class="fas fa-code-compare"></i>
            <?php echo $currentLang === 'th' ? 'เปรียบเทียบ APA 6 กับ APA 7<sup>th</sup>' : 'APA 6 vs APA 7<sup>th</sup> Comparison'; ?>
        </div>
        <div class="help-content">
            <div class="table-wrapper">
                <table class="help-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'รายการ' : 'Item'; ?></th>
                            <th>APA 6</th>
                            <th>APA 7<sup>th</sup></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'หนังสือ' : 'Book'; ?></td>
                            <td>กรุงเทพฯ: สำนักพิมพ์.</td>
                            <td>สำนักพิมพ์.</td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'วารสาร' : 'Journal'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ไม่ต้องใส่สถานที่' : 'No location needed'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ไม่ต้องใส่สถานที่ (เหมือนเดิม)' : 'No location needed (same)'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $currentLang === 'th' ? 'วิทยานิพนธ์' : 'Thesis'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ใส่ที่ตั้งมหาวิทยาลัย' : 'Include university location'; ?></td>
                            <td><?php echo $currentLang === 'th' ? 'ใส่ที่ตั้งมหาวิทยาลัย (เหมือนเดิม)' : 'Include university location (same)'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>