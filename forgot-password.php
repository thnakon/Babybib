<?php

/**
 * Babybib - Forgot Password Page (Premium Full-screen Design)
 * =============================================================
 */

require_once 'includes/session.php';

$pageTitle = 'ลืมรหัสผ่าน';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/forgot-password.css">';
$bodyClass = 'forgot-body';

require_once 'includes/header.php';
?>

<div class="auth-back-home">
    <a href="<?php echo SITE_URL; ?>/login.php" class="btn-back-home">
        <i class="fas fa-chevron-left"></i>
        <span><?php echo $currentLang === 'th' ? 'กลับหน้าเข้าสู่ระบบ' : 'Back to Login'; ?></span>
    </a>
</div>

<div class="auth-lang-switcher">
    <div class="lang-toggle">
        <a href="?lang=th" class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>">TH</a>
        <a href="?lang=en" class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
</div>

<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card slide-up">

        <!-- Step 1: Enter Email -->
        <div id="step-1" class="step-section active">
            <div class="auth-logo"><i class="fas fa-key"></i></div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'ลืมรหัสผ่าน?' : 'Forgot Password?'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'กรอกอีเมลที่ใช้สมัครสมาชิก เราจะส่งรหัสรีเซ็ตให้คุณ' : 'Enter your email address and we will send you a reset code'; ?></p>
            </div>
            <form id="forgot-form" onsubmit="requestReset(event)">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> <?php echo $currentLang === 'th' ? 'อีเมล' : 'Email'; ?></label>
                    <input type="email" id="email-input" class="form-input" placeholder="<?php echo $currentLang === 'th' ? 'กรอกอีเมลของคุณ' : 'Enter your email'; ?>" required>
                </div>
                <button type="submit" class="btn-auth-submit" id="btn-request">
                    <i class="fas fa-paper-plane"></i> <?php echo $currentLang === 'th' ? 'ส่งรหัสรีเซ็ต' : 'Send Reset Code'; ?>
                </button>
            </form>
            <div class="auth-footer-links">
                <p><?php echo $currentLang === 'th' ? 'จำรหัสผ่านได้แล้ว?' : 'Remember your password?'; ?> <a href="login.php"><?php echo $currentLang === 'th' ? 'เข้าสู่ระบบ' : 'Sign In'; ?></a></p>
            </div>
        </div>

        <!-- Step 2: Enter Verification Code -->
        <div id="step-2" class="step-section">
            <div class="auth-logo"><i class="fas fa-shield-alt"></i></div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'กรอกรหัสยืนยัน' : 'Enter Verification Code'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'กรอกรหัส 6 หลักที่ส่งไปยังอีเมลของคุณ' : 'Enter the 6-digit code sent to your email'; ?></p>
            </div>
            <div class="email-display" id="email-display"></div>
            <div id="dev-code-container"></div>
            <form id="verify-code-form" onsubmit="verifyCode(event)">
                <div class="code-input-group">
                    <input type="text" class="code-input" maxlength="1" data-index="0" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric" autocomplete="off">
                </div>
                <button type="submit" class="btn-auth-submit" id="btn-verify">
                    <i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'ยืนยันรหัส' : 'Verify Code'; ?>
                </button>
            </form>
            <div class="auth-footer-links">
                <p><a href="#" onclick="goToStep(1); return false;"><i class="fas fa-arrow-left"></i> <?php echo $currentLang === 'th' ? 'เปลี่ยนอีเมล' : 'Change Email'; ?></a></p>
            </div>
        </div>

        <!-- Step 3: Set New Password -->
        <div id="step-3" class="step-section">
            <div class="auth-logo success"><i class="fas fa-lock"></i></div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'ตั้งรหัสผ่านใหม่' : 'Set New Password'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'กรุณากรอกรหัสผ่านใหม่ของคุณ' : 'Please enter your new password'; ?></p>
            </div>
            <div class="password-requirements">
                <p><?php echo $currentLang === 'th' ? 'รหัสผ่านต้องมี:' : 'Password must have:'; ?></p>
                <ul>
                    <li><?php echo $currentLang === 'th' ? 'อย่างน้อย 8 ตัวอักษร' : 'At least 8 characters'; ?></li>
                    <li><?php echo $currentLang === 'th' ? 'ตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว' : 'At least 1 uppercase letter'; ?></li>
                </ul>
            </div>
            <form id="reset-form" onsubmit="resetPassword(event)">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> <?php echo $currentLang === 'th' ? 'รหัสผ่านใหม่' : 'New Password'; ?></label>
                    <input type="password" id="new-password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> <?php echo $currentLang === 'th' ? 'ยืนยันรหัสผ่าน' : 'Confirm Password'; ?></label>
                    <input type="password" id="confirm-password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-auth-submit success" id="btn-reset">
                    <i class="fas fa-check"></i> <?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่าน' : 'Change Password'; ?>
                </button>
            </form>
        </div>

        <!-- Step 4: Success -->
        <div id="step-4" class="step-section">
            <div class="auth-logo success success-animation"><i class="fas fa-check"></i></div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'สำเร็จ!' : 'Success!'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'รหัสผ่านของคุณถูกเปลี่ยนแล้ว กำลังพาไปหน้าเข้าสู่ระบบ...' : 'Your password has been changed. Redirecting to login...'; ?></p>
            </div>
        </div>

    </div>
</div>

<script>
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-key', 'fa-lock', 'fa-shield-alt', 'fa-unlock', 'fa-user-lock'];

    function createFloatingItem() {
        const item = document.createElement('i');
        item.className = `fas ${icons[Math.floor(Math.random() * icons.length)]} floating-item`;
        const startX = Math.random() * 100,
            startY = Math.random() * 100;
        const moveX = (Math.random() - 0.5) * 400,
            moveY = (Math.random() - 0.5) * 400;
        const size = 15 + Math.random() * 30,
            duration = 10 + Math.random() * 20;
        item.style.cssText = `left:${startX}%;top:${startY}%;font-size:${size}px;animation-duration:${duration}s;animation-delay:${Math.random() * -20}s;--move-x:${moveX}px;--move-y:${moveY}px`;
        bgAnimation.appendChild(item);
        setTimeout(() => {
            item.remove();
            createFloatingItem();
        }, duration * 1000);
    }
    for (let i = 0; i < 25; i++) createFloatingItem();

    let currentEmail = '',
        currentToken = '',
        currentCode = '';
    const codeInputs = document.querySelectorAll('.code-input');

    codeInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value && index < codeInputs.length - 1) codeInputs[index + 1].focus();
            e.target.classList.toggle('filled', !!e.target.value);
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) codeInputs[index - 1].focus();
        });
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const digits = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            digits.split('').forEach((d, i) => {
                if (codeInputs[i]) {
                    codeInputs[i].value = d;
                    codeInputs[i].classList.add('filled');
                }
            });
            if (digits.length > 0) codeInputs[Math.min(digits.length, 5)].focus();
        });
    });

    function goToStep(step) {
        document.querySelectorAll('.step-section').forEach(s => s.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        if (step === 2) setTimeout(() => codeInputs[0].focus(), 100);
    }

    async function requestReset(e) {
        e.preventDefault();
        const email = document.getElementById('email-input').value.trim();
        if (!email) {
            Toast.error('กรุณากรอกอีเมล');
            return;
        }
        const btn = document.getElementById('btn-request');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/auth/forgot-password.php', {
                email
            });
            if (res.success) {
                currentEmail = res.email || email;
                currentToken = res.token || '';
                document.getElementById('email-display').innerHTML = '<i class="fas fa-envelope"></i> ' + currentEmail;
                if (res.reset_code) {
                    document.getElementById('dev-code-container').innerHTML = `<div class="dev-code-box"><div class="label">🛠️ DEV MODE - รหัสรีเซ็ต:</div><div class="code">${res.reset_code}</div></div>`;
                }
                Toast.success(res.message);
                goToStep(2);
            } else {
                Toast.error(res.error || 'Error occurred');
            }
        } catch (err) {
            Toast.error(err.error || 'เกิดข้อผิดพลาด');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> ส่งรหัสรีเซ็ต';
    }

    async function verifyCode(e) {
        e.preventDefault();
        const code = Array.from(codeInputs).map(i => i.value).join('');
        if (code.length !== 6) {
            Toast.error('กรุณากรอกรหัส 6 หลัก');
            return;
        }
        currentCode = code;
        goToStep(3);
        Toast.success('รหัสถูกต้อง กรุณาตั้งรหัสผ่านใหม่');
    }

    async function resetPassword(e) {
        e.preventDefault();
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        if (newPassword.length < 8) {
            Toast.error('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
            return;
        }
        if (!/[A-Z]/.test(newPassword)) {
            Toast.error('รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว');
            return;
        }
        if (newPassword !== confirmPassword) {
            Toast.error('รหัสผ่านไม่ตรงกัน');
            return;
        }
        const btn = document.getElementById('btn-reset');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/auth/reset-password.php', {
                email: currentEmail,
                code: currentCode,
                token: currentToken,
                new_password: newPassword,
                confirm_password: confirmPassword
            });
            if (res.success) {
                Toast.success(res.message);
                goToStep(4);
                setTimeout(() => {
                    window.location.href = '<?php echo SITE_URL; ?>/login.php';
                }, 2000);
            } else {
                Toast.error(res.error || 'Error occurred');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> เปลี่ยนรหัสผ่าน';
            }
        } catch (err) {
            Toast.error(err.error || 'เกิดข้อผิดพลาด');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> เปลี่ยนรหัสผ่าน';
        }
    }
</script>
</body>

</html>