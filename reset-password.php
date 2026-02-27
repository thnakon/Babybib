<?php
require_once 'includes/session.php';

if (isLoggedIn()) {
    header('Location: users/dashboard.php');
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE token = ? AND token_expiry > NOW() AND is_active = 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

$isValidToken = (bool)$user;

$pageTitle = __('reset_password') . ' - Babybib';
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

<!-- Floating Elements -->
<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card slide-up">
        <?php if (!$isValidToken): ?>
            <div class="auth-logo bg-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'ลิงก์หมดอายุ' : 'Link Expired'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'ลิงก์นี้อาจหมดอายุแล้ว หรือถูกใช้งานไปแล้ว กรุณาขอลิงก์ใหม่อีกครั้ง' : 'This link may have expired or already been used. Please request a new link.'; ?></p>
            </div>
            <div class="mt-8">
                <a href="forgot-password.php" class="btn btn-primary btn-lg w-full btn-auth-submit text-center flex items-center justify-center">
                    <?php echo $currentLang === 'th' ? 'ขอลิงก์ใหม่' : 'Request New Link'; ?>
                </a>
            </div>
        <?php else: ?>
            <div class="auth-logo">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div class="auth-header">
                <h1><?php echo __('reset_password'); ?></h1>
                <p><?php echo $currentLang === 'th' ? 'กรุณากำหนดรหัสผ่านใหม่สำหรับบัญชีของคุณ' : 'Please set a new password for your account'; ?></p>
            </div>

            <form id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('password'); ?>ใหม่</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" required 
                               placeholder="••••••••">
                        <button type="button" class="btn btn-ghost btn-icon"
                            style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary);"
                            onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Password Strength Checklist (Mirroring Registration) -->
                    <div class="password-requirements mt-4 p-4 bg-vercel-gray-50 rounded-xl border border-vercel-gray-200">
                        <p class="text-[11px] font-bold text-vercel-gray-400 uppercase tracking-wider mb-2">Password requirements:</p>
                        <ul class="space-y-2">
                            <li id="req-length" class="flex items-center gap-2 text-xs text-vercel-gray-500">
                                <i class="fas fa-circle text-[6px]"></i> อย่างน้อย 8 ตัวอักษร
                            </li>
                            <li id="req-uppercase" class="flex items-center gap-2 text-xs text-vercel-gray-500">
                                <i class="fas fa-circle text-[6px]"></i> ตัวพิมพ์ใหญ่ 1 ตัว (A-Z)
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                           placeholder="••••••••">
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-full btn-auth-submit">
                    <?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่าน' : 'Reset Password'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // BG Animation
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-shield-halved', 'fa-key', 'fa-lock', 'fa-rotate-left'];
    function createFloatingItem() {
        const item = document.createElement('i');
        const icon = icons[Math.floor(Math.random() * icons.length)];
        item.className = `fas ${icon} floating-item`;
        const startX = Math.random() * 100;
        const startY = Math.random() * 100;
        const size = 15 + Math.random() * 30;
        const duration = 15 + Math.random() * 25;
        item.style.left = startX + '%';
        item.style.top = startY + '%';
        item.style.fontSize = size + 'px';
        item.style.animationDuration = duration + 's';
        item.style.setProperty('--move-x', (Math.random() - 0.5) * 400 + 'px');
        item.style.setProperty('--move-y', (Math.random() - 0.5) * 400 + 'px');
        bgAnimation.appendChild(item);
        setTimeout(() => item.remove(), duration * 1000);
    }
    for (let i = 0; i < 15; i++) createFloatingItem();

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }

    // Password Validation
    document.getElementById('password')?.addEventListener('input', function() {
        const val = this.value;
        const reqLen = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-uppercase');
        
        if (val.length >= 8) reqLen.classList.add('text-success'); else reqLen.classList.remove('text-success');
        if (/[A-Z]/.test(val)) reqUpper.classList.add('text-success'); else reqUpper.classList.remove('text-success');
    });

    document.getElementById('resetForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const token = document.querySelector('[name="token"]').value;
        const btn = document.getElementById('submitBtn');

        if (password.length < 8 || !/[A-Z]/.test(password)) {
            Toast.error('<?php echo $currentLang === "th" ? "รหัสผ่านไม่ตรงตามเงื่อนไข" : "Password requirements not met"; ?>');
            return;
        }

        if (password !== confirm) {
            Toast.error('<?php echo $currentLang === "th" ? "รหัสผ่านไม่ตรงกัน" : "Passwords do not match"; ?>');
            return;
        }

        setLoading(btn, true);

        try {
            const response = await API.post('api/auth/reset-password.php', { token, password });
            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => window.location.href = 'login.php', 2000);
            } else {
                Toast.error(response.error);
                setLoading(btn, false);
            }
        } catch (error) {
            Toast.error('System error');
            setLoading(btn, false);
        }
    });
</script>
</body>
</html>