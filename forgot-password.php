<?php
require_once 'includes/session.php';

if (isLoggedIn()) {
    header('Location: users/dashboard.php');
    exit;
}

$pageTitle = __('forgot_password') . ' - Babybib';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/auth.css">';
require_once 'includes/header.php';
?>

<!-- Top Navigation -->
<div class="auth-back-home">
    <a href="login.php" class="btn-back-home">
        <i class="fas fa-chevron-left"></i>
        <span><?php echo $currentLang === 'th' ? 'กลับหน้าเข้าสู่ระบบ' : 'Back to Login'; ?></span>
    </a>
</div>

<!-- Language Switcher -->
<div class="auth-lang-switcher">
    <div class="lang-toggle">
        <a href="?lang=th" class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>">TH</a>
        <a href="?lang=en" class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
</div>

<!-- Floating Elements -->
<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card slide-up">
        <div class="auth-logo">
            <i class="fas fa-key"></i>
        </div>

        <div class="auth-header">
            <h1><?php echo __('forgot_password'); ?></h1>
            <p><?php echo $currentLang === 'th' ? 'กรอกอีเมลของคุณเพื่อรับลิงก์สำหรับรีเซ็ตรหัสผ่าน' : 'Enter your email to receive a password reset link'; ?></p>
        </div>

        <form id="forgotForm">
            <div class="form-group mb-6">
                <label class="form-label"><?php echo __('email'); ?></label>
                <div style="position: relative;">
                    <input type="email" id="email" name="email" class="form-input" required 
                           placeholder="your-email@example.com">
                    <i class="fas fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary);"></i>
                </div>
                <style>#email { padding-left: 45px; }</style>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-full btn-auth-submit">
                <?php echo $currentLang === 'th' ? 'ส่งลิงก์รีเซ็ต' : 'Send Reset Link'; ?>
            </button>
        </form>

        <div class="auth-footer-links">
            <p>
                <?php echo $currentLang === 'th' ? 'จำรหัสผ่านได้แล้ว?' : 'Remembered your password?'; ?>
                <a href="login.php"><?php echo __('login'); ?></a>
            </p>
        </div>
    </div>
</div>

<script>
    // BG Animation
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-envelope', 'fa-key', 'fa-lock', 'fa-paper-plane', 'fa-shield-halved'];
    function createFloatingItem() {
        const item = document.createElement('i');
        const icon = icons[Math.floor(Math.random() * icons.length)];
        item.className = `fas ${icon} floating-item`;
        const startX = Math.random() * 100;
        const startY = Math.random() * 100;
        const moveX = (Math.random() - 0.5) * 400;
        const moveY = (Math.random() - 0.5) * 400;
        const size = 15 + Math.random() * 30;
        const duration = 15 + Math.random() * 25;
        item.style.left = startX + '%';
        item.style.top = startY + '%';
        item.style.fontSize = size + 'px';
        item.style.animationDuration = duration + 's';
        item.style.setProperty('--move-x', moveX + 'px');
        item.style.setProperty('--move-y', moveY + 'px');
        bgAnimation.appendChild(item);
        setTimeout(() => item.remove(), duration * 1000);
    }
    for (let i = 0; i < 15; i++) createFloatingItem();

    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const btn = document.getElementById('submitBtn');

        setLoading(btn, true);

        try {
            const response = await API.post('api/auth/forgot-password.php', { email: email });
            if (response.success) {
                Toast.success(response.message);
                document.getElementById('forgotForm').reset();
            } else {
                Toast.error(response.error);
            }
        } catch (error) {
            Toast.error('System error');
        } finally {
            setLoading(btn, false);
        }
    });
</script>
</body>
</html>