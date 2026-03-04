<?php

/**
 * Babybib - Privacy Policy
 * =========================
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

$pageTitle = $currentLang === 'th' ? 'นโยบายความเป็นส่วนตัว' : 'Privacy Policy';
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
</style>

<main class="legal-container">
    <div class="back-nav">
        <a href="<?php echo SITE_URL; ?>">
            <i class="fas fa-arrow-left"></i>
            <?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Back to Home'; ?>
        </a>
    </div>

    <div class="legal-content">
        <h1><?php echo $currentLang === 'th' ? 'นโยบายความเป็นส่วนตัว' : 'Privacy Policy'; ?></h1>

        <?php if ($currentLang === 'th'): ?>
            <p>Babybib ให้ความสำคัญกับความเป็นส่วนตัว นโยบายความเป็นส่วนตัวนี้อธิบายถึงวิธีการเก็บรวบรวม ใช้ และป้องกันข้อมูลส่วนบุคคลเมื่อมีการใช้งานเว็บไซต์</p>

            <h2>1. ข้อมูลที่เราเก็บรวบรวม</h2>
            <p>ระบบมีการเก็บรวบรวมข้อมูลดังต่อไปนี้เพื่อให้บริการทำงานได้อย่างมีประสิทธิภาพ:</p>
            <ul>
                <li><strong>ข้อมูลบัญชี:</strong> ชื่อ, นามสกุล, อีเมล, และชื่อผู้ใช้ (Username) เมื่อคุณลงทะเบียน</li>
                <li><strong>ข้อมูลบรรณานุกรม:</strong> ข้อมูลทรัพยากรที่คุณบันทึกไว้ในระบบ</li>
                <li><strong>ข้อมูลการใช้งาน:</strong> บันทึกกิจกรรม (Logs) เช่น ที่อยู่ IP, ประเภทเบราว์เซอร์ และวันเวลาที่เข้าใช้งาน เพื่อความปลอดภัยและการวิเคราะห์ระบบ</li>
            </ul>

            <h2>2. การใช้ข้อมูล</h2>
            <p>ข้อมูลที่รวบรวมจะถูกนำไปใช้เพื่อวัตถุประสงค์ดังต่อไปนี้:</p>
            <ul>
                <li>เพื่อให้บริการและจัดการบัญชีผู้ใช้งาน</li>
                <li>เพื่อบันทึกและจัดการรายการบรรณานุกรมและโครงการที่สร้างขึ้น</li>
                <li>เพื่อปรับปรุงคุณภาพการบริการและการรักษาความปลอดภัยของระบบ</li>
                <li>เพื่อติดต่อส่งข้อมูลสำคัญเกี่ยวกับบัญชีหรือประกาศจากระบบ</li>
            </ul>

            <h2>3. การคุ้มครองและความปลอดภัยข้อมูล</h2>
            <p>ระบบใช้มาตรการรักษาความปลอดภัยทางเทคนิคที่เหมาะสมเพื่อป้องกันการเข้าถึงข้อมูลโดยไม่ได้รับอนุญาต การสูญหาย หรือการเปิดเผยข้อมูลส่วนบุคคล อย่างไรก็ตาม การส่งข้อมูลผ่านอินเทอร์เน็ตไม่มีวิธีใดที่ปลอดภัย 100% จึงขอความร่วมมือในการรักษาความลับของรหัสผ่านด้วยเช่นกัน</p>

            <h2>4. ระยะเวลาการเก็บรักษาข้อมูล</h2>
            <p>ระบบจะเก็บรักษาข้อมูลบัญชีไว้ตลอดระยะเวลาที่เป็นสมาชิก สำหรับข้อมูลบัญชีที่ไม่ใช้งานนานเกินกว่าที่กำหนดในเงื่อนไขการใช้งาน อาจมีการดำเนินการลบข้อมูลออกเพื่อประสิทธิภาพของฐานข้อมูล</p>

            <h2>5. สิทธิ์ผู้ใช้งาน</h2>
            <p>ผู้ใช้งานมีสิทธิ์ในการเข้าถึง แก้ไข หรือลบข้อมูลส่วนบุคคลได้ตลอดเวลาผ่านหน้าโปรไฟล์ หากต้องการยกเลิกบัญชีถาวรหรือมีคำถามเกี่ยวกับข้อมูล โปรดติดต่อระบบผ่านทางอีเมลที่ระบุไว้</p>

            <h2>6. คุกกี้ (Cookies)</h2>
            <p>ระบบมีการใช้คุกกี้เพื่อจัดเก็บข้อมูลเซสชัน (Session) เพื่อคงสถานะการเข้าสู่ระบบไว้ในขณะใช้งาน</p>

            <h2>7. การเปลี่ยนแปลงนโยบาย</h2>
            <p>นโยบายความเป็นส่วนตัวนี้อาจมีการปรับปรุงเป็นระยะเพื่อให้สอดคล้องกับการเปลี่ยนแปลงของบริการหรือกฎหมาย โดยจะมีการแจ้งให้ทราบผ่านประกาศบนหน้าเว็บไซต์</p>
        <?php else: ?>
            <p>Babybib ("we", "our") values your privacy. This Privacy Policy describes how we collect, use, and protect your personal information when you use our website.</p>

            <h2>1. Information We Collect</h2>
            <p>We collect the following information to provide efficient service:</p>
            <ul>
                <li><strong>Account Information:</strong> Name, surname, email, and username when you register.</li>
                <li><strong>Bibliography Data:</strong> Resource information you save in the system.</li>
                <li><strong>Usage Data:</strong> Activity logs such as IP address, browser type, and timestamps for security and analytics.</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>We use collected information for the following purposes:</p>
            <ul>
                <li>To provide and manage your account.</li>
                <li>To store and manage bibliographies and projects you create.</li>
                <li>To improve service quality and system security.</li>
                <li>To contact you with important account information or system announcements.</li>
            </ul>

            <h2>3. Information Protection</h2>
            <p>We implement appropriate technical security measures to prevent unauthorized access, loss, or disclosure of your personal information. However, no method of transmission over the internet is 100% secure. You are also responsible for maintaining the confidentiality of your password.</p>

            <h2>4. Data Retention</h2>
            <p>We will retain your account information for as long as you are a member of the system. For accounts inactive for long periods, we may delete data to maintain database efficiency.</p>

            <h2>5. Your Rights</h2>
            <p>You have the right to access, edit, or delete your personal information at any time via your profile. If you wish to permanently delete your account, please contact us via email.</p>

            <h2>6. Cookies</h2>
            <p>We use cookies to store session data so you can remain logged in while using the site.</p>

            <h2>7. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time to reflect changes in service or law. We will notify you of any changes through system announcements.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>