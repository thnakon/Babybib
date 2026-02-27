<?php

/**
 * Babybib - Login Page (Premium Design)
 * ======================================
 */

require_once 'includes/session.php';

$pageTitle = 'เข้าสู่ระบบ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/auth.css">';
require_once 'includes/header.php';
// No navbar or footer as requested
?>



<!-- Top Navigation -->
<div class="auth-back-home">
    <a href="<?php echo SITE_URL; ?>" class="btn-back-home">
        <i class="fas fa-chevron-left"></i>
        <span><?php echo $currentLang === 'th' ? 'กลับหน้าหลัก' : 'Home'; ?></span>
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
            <i class="fas fa-book-open"></i>
        </div>

        <div class="auth-header">
            <h1><?php echo $currentLang === 'th' ? 'เข้าสู่ระบบ' : 'Sign In'; ?></h1>
            <p><?php echo $currentLang === 'th' ? 'เข้าสู่ระบบเพื่อจัดการบรรณานุกรมของคุณ' : 'Sign in to manage your bibliographies'; ?></p>
        </div>

        <form id="login-form" autocomplete="off">
            <div class="form-group">
                <label class="form-label">
                    <?php echo __('username'); ?> / <?php echo __('email'); ?>
                </label>
                <input type="text" id="login-input" name="login" class="form-input"
                    placeholder="<?php echo $currentLang === 'th' ? 'กรอกชื่อผู้ใช้หรืออีเมล' : 'Enter username or email'; ?>" required>
            </div>

            <div class="form-group">
                <div class="flex justify-between items-center mb-2">
                    <label class="form-label mb-0" style="margin-bottom: 0;">
                        <?php echo __('password'); ?>
                    </label>
                    <a href="forgot-password.php" class="text-xs font-semibold"><?php echo __('forgot_password'); ?></a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-input"
                        placeholder="••••••••" required>
                    <button type="button" class="btn btn-ghost btn-icon"
                        style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary);"
                        onclick="togglePassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="remember" class="checkbox-animated">
                    <span class="text-sm font-medium text-secondary"><?php echo __('remember_me'); ?></span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full btn-auth-submit">
                <?php echo $currentLang === 'th' ? 'เข้าสู่ระบบ' : 'Sign In'; ?>
            </button>
        </form>

        <div class="auth-footer-links">
            <p>
                <?php echo __('no_account'); ?>
                <a href="register.php"><?php echo $currentLang === 'th' ? 'สมัครสมาชิก' : 'Sign Up'; ?></a>
            </p>
        </div>
    </div>
</div>

<script>
    // Create floating items
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-book', 'fa-pen-nib', 'fa-feather', 'fa-quote-left', 'fa-bookmark', 'fa-file-lines', 'fa-graduation-cap'];

    function createFloatingItem() {
        const item = document.createElement('i');
        const icon = icons[Math.floor(Math.random() * icons.length)];

        item.className = `fas ${icon} floating-item`;

        const startX = Math.random() * 100;
        const startY = Math.random() * 100;
        const moveX = (Math.random() - 0.5) * 400;
        const moveY = (Math.random() - 0.5) * 400;
        const size = 15 + Math.random() * 30;
        const duration = 10 + Math.random() * 20;
        const delay = Math.random() * -20;

        item.style.left = startX + '%';
        item.style.top = startY + '%';
        item.style.fontSize = size + 'px';
        item.style.animationDuration = duration + 's';
        item.style.animationDelay = delay + 's';
        item.style.setProperty('--move-x', moveX + 'px');
        item.style.setProperty('--move-y', moveY + 'px');

        bgAnimation.appendChild(item);

        setTimeout(() => {
            item.remove();
            createFloatingItem();
        }, duration * 1000);
    }

    for (let i = 0; i < 25; i++) {
        createFloatingItem();
    }

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const login = document.getElementById('login-input').value.trim();
        const password = document.getElementById('password').value;

        if (!login || !password) {
            Toast.error('<?php echo addslashes(__("error_required")); ?>');
            return;
        }

        setLoading(btn, true);

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/login.php', {
                login: login,
                password: password,
                remember: this.remember.checked
            });

            if (response.success) {
                Toast.success('<?php echo addslashes($currentLang === "th" ? "เข้าสู่ระบบสำเร็จ" : "Sign In successful"); ?>');
                setTimeout(() => {
                    window.location.href = response.redirect || '<?php echo SITE_URL; ?>/users/dashboard.php';
                }, 1000);
            } else {
                if (response.requires_verification) {
                    Toast.warning(response.error);
                    setTimeout(() => {
                        window.location.href = 'verify.php?user_id=' + response.user_id + '&email=' + encodeURIComponent(response.email);
                    }, 2000);
                } else {
                    Toast.error(response.error || '<?php echo addslashes(__("error_login")); ?>');
                    setLoading(btn, false);
                }
            }
        } catch (error) {
            Toast.error(error.error || '<?php echo addslashes(__("error_login")); ?>');
            setLoading(btn, false);
        }
    });
</script>

</body>

</html>