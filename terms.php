<?php

/**
 * Babybib - Terms of Service
 * =========================
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

$pageTitle = $currentLang === 'th' ? 'ข้อกำหนดการใช้งาน' : 'Terms of Service';
require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}
?>

<style>
    .legal-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 60px 20px;
        color: #374151;
        line-height: 1.7;
    }

    .legal-content h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 2rem;
        text-align: center;
    }

    .legal-content h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #F3F4F6;
    }

    .legal-content p {
        margin-bottom: 1.25rem;
    }

    .legal-content ul {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }

    .legal-content li {
        margin-bottom: 0.5rem;
        list-style-type: disc;
    }

    .last-updated {
        font-size: 0.875rem;
        color: #6B7280;
        text-align: center;
        margin-bottom: 3rem;
    }

    .back-nav {
        margin-bottom: 2rem;
    }

    .back-nav a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        font-weight: 500;
        text-decoration: none;
        transition: transform 0.2s;
    }

    .back-nav a:hover {
        transform: translateX(-5px);
    }

    .important-notice {
        background-color: #FEF2F2;
        border-left: 4px solid #EF4444;
        padding: 1rem;
        margin: 1.5rem 0;
        color: #991B1B;
        font-size: 0.95rem;
    }
</style>

<main class="legal-container">
    <div class="back-nav">
        <a href="<?php echo SITE_URL; ?>">
            <i class="fas fa-arrow-left"></i>
            <?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?>
        </a>
    </div>

    <div class="legal-content">
        <h1><?php echo $currentLang === 'th' ? 'ข้อกำหนดและเงื่อนไขการใช้งาน' : 'Terms and Conditions'; ?></h1>

        <?php if ($currentLang === 'th'): ?>
            <p>ยินดีต้อนรับสู่ Babybib กรุณาอ่านข้อกำหนดและเงื่อนไขเหล่านี้อย่างละเอียดก่อนใช้งานเว็บไซต์ของเรา การเข้าใช้งานเว็บไซต์นี้ถือว่าคุณได้ยอมรับข้อกำหนดเหล่านี้ทั้งหมด</p>

            <h2>1. ข้อมูลเกี่ยวกับบริการ</h2>
            <p>Babybib เป็นเครื่องมือออนไลน์ที่ออกแบบมาเพื่อช่วยสร้างบรรณานุกรมและการอ้างอิงตามรูปแบบมาตรฐาน APA 7th Edition ("บริการ") บริการนี้จัดทำขึ้นเพื่อสนับสนุนการศึกษาและวิจัยสำหรับนักศึกษาและบุคคลทั่วไป</p>

            <h2>2. การใช้งานที่อนุญาต</h2>
            <p>คุณตกลงที่จะใช้งานบริการตามวัตถุประสงค์ที่ระบุไว้เท่านั้น และไม่กระทำการใดๆ ดังต่อไปนี้:</p>
            <ul>
                <li>ใช้งานระบบในทางที่ผิด หรือพยายามเข้าถึงส่วนที่ไม่ได้รับอนุญาต</li>
                <li>ใช้โปรแกรมอัตโนมัติ (Bot/Scraper) ในการดึงข้อมูลจากระบบในปริมาณมาก</li>
                <li>ส่งข้อมูลที่ผิดกฎหมาย ลามกอนาจาร หรือคุกคามผู้อื่นเข้าสู่ระบบ</li>
                <li>ละเมิดสิทธิ์ในทรัพย์สินทางปัญญาของเราหรือของผู้อื่น</li>
            </ul>

            <h2>3. บัญชีผู้ใช้งานและความปลอดภัย</h2>
            <p>เมื่อคุณสร้างบัญชีกับเรา คุณมีหน้าที่รับผิดชอบข้อมูลดังนี้:</p>
            <ul>
                <li>ต้องให้ข้อมูลที่เป็นความจริงในการสมัครสมาชิก</li>
                <li>ต้องรักษาความลับของรหัสผ่านและข้อมูลบัญชีของคุณ</li>
                <li>คุณต้องรับผิดชอบต่อกิจกรรมทั้งหมดที่เกิดขึ้นภายใต้บัญชีของคุณ</li>
                <li>หากพบการใช้งานที่ไม่ได้รับอนุญาต คุณต้องแจ้งให้เราทราบทันที</li>
            </ul>

            <h2>4. ข้อจำกัดความรับผิดชอบ</h2>
            <div class="important-notice">
                เราพยายามอย่างเต็มที่ให้ข้อมูลและการสร้างบรรณานุกรมมีความถูกต้องแม่นยำสูงสุด อย่างไรก็ตาม ผู้ใช้มีหน้าที่ตรวจสอบความถูกต้องขั้นสุดท้าย (Final Verification) ตามมาตรฐานคู่มือการเขียนบรรณานุกรมที่เกี่ยวข้องเสมอ
            </div>
            <p>เราไม่รับประกันว่าบริการจะทำงานได้โดยไม่มีข้อผิดพลาดหรือไม่มีการขัดข้อง และจะไม่รับผิดชอบต่อความสูญเสียหรือความเสียหายใดๆ ที่เกิดจากการใช้งาน หรือการไม่สามารถใช้งานบริการได้</p>

            <h2>5. ทรัพย์สินทางปัญญ</h2>
            <p>เนื้อหา โลโก้ ซอร์สโค้ด และการออกแบบทั้งหมดบนเว็บไซต์ Babybib เป็นทรัพย์สินทางปัญญาของเรา ห้ามมิให้ทำซ้ำ ดัดแปลง หรือนำไปใช้เพื่อการค้าโดยไม่ได้รับอนุญาตเป็นลายลักษณ์อักษร</p>

            <h2>6. การยกเลิกและปิดกั้นการเข้าถึง</h2>
            <p>เราขอสงวนสิทธิ์ในการยกเลิกบัญชีหรือระงับการเข้าถึงบริการของคุณชั่วคราวหรือถาวร โดยไม่ต้องแจ้งให้ทราบล่วงหน้า หากพบว่ามีการละเมิดข้อกำหนดการใช้งานเหล่านี้</p>

            <h2>7. การเปลี่ยนแปลงข้อกำหนด</h2>
            <p>เราอาจมีการเปลี่ยนแปลงข้อกำหนดเหล่านี้เมื่อใดก็ได้ การเปลี่ยนแปลงจะมีผลทันทีเมื่อมีการประกาศบนเว็บไซต์ การที่คุณยังคงใช้งานบริการต่อไปถือว่าคุณยอมรับข้อกำหนดที่เปลี่ยนแปลงนั้น</p>

            <h2>8. การติดต่อเรา</h2>
            <p>หากคุณมีคำถามหรือข้อสงสัยเกี่ยวกับข้อกำหนดเหล่านี้ โปรดติดต่อเราทางอีเมลที่ระบุไว้ในส่วนท้ายของเว็บไซต์</p>
        <?php else: ?>
            <p>Welcome to Babybib. Please read these Terms and Conditions carefully before using our website. By accessing and using this website, you accept these terms in full.</p>

            <h2>1. Service Information</h2>
            <p>Babybib is an online tool designed to assist in creating bibliographies and citations according to the APA 7th Edition standards ("Service"). This service is intended to support education and research for students and the general public.</p>

            <h2>2. Permitted Use</h2>
            <p>You agree to use the Service only for its intended purposes and will not engage in any of the following activities:</p>
            <ul>
                <li>Misusing the system or attempting to access unauthorized areas.</li>
                <li>Using automated programs (Bots/Scrapers) to extract large amounts of data.</li>
                <li>Submitting illegal, obscene, or harassing information.</li>
                <li>Infringing on our or others' intellectual property rights.</li>
            </ul>

            <h2>3. User Accounts and Security</h2>
            <p>When you create an account, you are responsible for the following:</p>
            <ul>
                <li>Providing truthful information during registration.</li>
                <li>Maintaining the confidentiality of your password and account credentials.</li>
                <li>You are responsible for all activities occurring under your account.</li>
                <li>Notifying us immediately of any unauthorized use.</li>
            </ul>

            <h2>4. Disclaimer of Liability</h2>
            <div class="important-notice">
                We strive for maximum accuracy in citation generation. However, users are responsible for final verification against official citation standards.
            </div>
            <p>We do not guarantee that the Service will be error-free or uninterrupted, and we will not be held liable for any loss or damage arising from your use or inability to use the Service.</p>

            <h2>5. Intellectual Property</h2>
            <p>All content, logos, source code, and designs on the Babybib website are our intellectual property. Unauthorized reproduction, modification, or commercial use is prohibited without prior written consent.</p>

            <h2>6. Termination and Suspension</h2>
            <p>We reserve the right to terminate your account or suspend access to the Service temporarily or permanently without prior notice if these terms are violated.</p>

            <h2>7. Changes to Terms</h2>
            <p>We may change these terms at any time. Changes are effective immediately upon posting. Your continued use of the Service constitutes acceptance of the modified terms.</p>

            <h2>8. Contact Us</h2>
            <p>If you have any questions or concerns regarding these terms, please contact us via the email provided in the footer.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>