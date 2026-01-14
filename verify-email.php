<?php

/**
 * Babybib - Email Verification Page
 * ==================================
 */

$pageTitle = 'ยืนยันอีเมล';

require_once 'includes/header.php';

$email = isset($_GET['email']) ? sanitize($_GET['email']) : '';
$devCode = isset($_GET['code']) ? sanitize($_GET['code']) : '';
?>

<style>
    body {
        background: #f8fafc;
        overflow: hidden;
        height: 100vh;
        width: 100vw;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .auth-page-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        padding: var(--space-4);
    }

    .bg-animation {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        overflow: hidden;
        pointer-events: none;
    }

    .floating-item {
        position: absolute;
        color: rgba(139, 92, 246, 0.15);
        font-size: 2rem;
        user-select: none;
        animation: floatAround linear infinite;
        z-index: 1;
    }

    @keyframes floatAround {
        0% {
            transform: translate(0, 0) rotate(0deg);
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        100% {
            transform: translate(var(--move-x), var(--move-y)) rotate(360deg);
            opacity: 0;
        }
    }

    .auth-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 480px;
        padding: var(--space-10);
        position: relative;
        z-index: 20;
    }

    .auth-logo {
        width: 72px;
        height: 72px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-6);
        box-shadow: var(--shadow-primary);
    }

    .auth-logo.success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    }

    .auth-logo i {
        font-size: 2rem;
        color: var(--white);
    }

    .auth-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        text-align: center;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .auth-header p {
        text-align: center;
        color: var(--text-secondary);
        font-size: var(--text-sm);
        margin-bottom: var(--space-6);
        line-height: 1.6;
    }

    .email-display {
        background: rgba(139, 92, 246, 0.1);
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: var(--space-6);
        text-align: center;
    }

    .code-input-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: var(--space-6);
    }

    .code-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 700;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        transition: all 0.2s;
        background: var(--gray-50);
    }

    .code-input:focus {
        border-color: var(--primary);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .code-input.filled {
        border-color: var(--primary);
        background: white;
    }

    .btn-auth-submit {
        width: 100%;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 14px;
        padding: 16px;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4);
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-auth-submit:hover {
        transform: translateY(-2px);
    }

    .btn-auth-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .resend-section {
        text-align: center;
        margin-top: var(--space-6);
        padding-top: var(--space-4);
        border-top: 1px solid var(--gray-200);
    }

    .resend-btn {
        color: var(--primary);
        font-weight: 600;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .resend-btn:disabled {
        color: var(--gray-400);
        cursor: not-allowed;
    }

    .dev-code-box {
        background: #FEF3C7;
        border: 2px dashed #F59E0B;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: var(--space-6);
        text-align: center;
    }

    .dev-code-box .label {
        font-size: 12px;
        color: #92400E;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .dev-code-box .code {
        font-size: 2rem;
        font-weight: 800;
        color: #B45309;
        letter-spacing: 8px;
    }

    .success-section {
        display: none;
    }

    .success-section.show {
        display: block;
    }

    .success-animation {
        animation: successPop 0.5s ease;
    }

    @keyframes successPop {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }
</style>

<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card slide-up">

        <div id="verify-section">
            <div class="auth-logo">
                <i class="fas fa-envelope-open-text"></i>
            </div>

            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'ยืนยันอีเมลของคุณ' : 'Verify Your Email'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'กรุณากรอกรหัส 6 หลักที่ส่งไปยังอีเมลของคุณ' : 'Please enter the 6-digit code sent to your email'; ?></p>
            </div>

            <?php if ($email): ?>
                <div class="email-display">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?>
                </div>
            <?php endif; ?>

            <?php if ($devCode): ?>
                <div class="dev-code-box">
                    <div class="label">🛠️ DEV MODE - รหัสยืนยัน:</div>
                    <div class="code"><?php echo htmlspecialchars($devCode); ?></div>
                </div>
            <?php endif; ?>

            <form id="verify-form" onsubmit="verifyEmail(event)">
                <div class="code-input-group">
                    <input type="text" class="code-input" maxlength="1" data-index="0" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric" autocomplete="off">
                </div>

                <button type="submit" class="btn-auth-submit" id="btn-verify">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $currentLang === 'th' ? 'ยืนยันอีเมล' : 'Verify Email'; ?>
                </button>
            </form>

            <div class="resend-section">
                <p style="color: var(--text-tertiary); font-size: 13px; margin-bottom: 8px;">
                    <?php echo $currentLang === 'th' ? 'ไม่ได้รับรหัส?' : 'Didn\'t receive the code?'; ?>
                </p>
                <button type="button" class="resend-btn" id="resend-btn" onclick="resendCode()">
                    <?php echo $currentLang === 'th' ? 'ส่งรหัสใหม่' : 'Resend Code'; ?>
                </button>
                <span id="resend-countdown" style="color: var(--gray-400); font-size: 13px;"></span>
            </div>
        </div>

        <div id="success-section" class="success-section">
            <div class="auth-logo success success-animation">
                <i class="fas fa-check"></i>
            </div>
            <div class="auth-header">
                <h1><?php echo $currentLang === 'th' ? 'ยืนยันสำเร็จ!' : 'Verified!'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'อีเมลของคุณได้รับการยืนยันแล้ว กำลังพาไปหน้าเข้าสู่ระบบ...' : 'Your email has been verified. Redirecting to login...'; ?></p>
            </div>
        </div>

    </div>
</div>

<script>
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-envelope', 'fa-check', 'fa-shield-alt', 'fa-lock'];

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
    for (let i = 0; i < 20; i++) createFloatingItem();

    const currentEmail = '<?php echo addslashes($email); ?>';
    const codeInputs = document.querySelectorAll('.code-input');
    let resendCountdown = 0;

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

    // Auto-focus first input
    codeInputs[0].focus();

    async function verifyEmail(e) {
        e.preventDefault();
        const code = Array.from(codeInputs).map(i => i.value).join('');
        if (code.length !== 6) {
            Toast.error('กรุณากรอกรหัส 6 หลัก');
            return;
        }

        const btn = document.getElementById('btn-verify');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/auth/verify-email.php', {
                email: currentEmail,
                code: code
            });
            if (res.success) {
                document.getElementById('verify-section').style.display = 'none';
                document.getElementById('success-section').classList.add('show');
                Toast.success(res.message || 'ยืนยันอีเมลสำเร็จ!');
                setTimeout(() => {
                    window.location.href = '<?php echo SITE_URL; ?>/login.php';
                }, 2000);
            } else {
                Toast.error(res.error || 'รหัสไม่ถูกต้อง');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> ยืนยันอีเมล';
            }
        } catch (err) {
            Toast.error(err.error || 'เกิดข้อผิดพลาด');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> ยืนยันอีเมล';
        }
    }

    async function resendCode() {
        if (resendCountdown > 0) return;

        const btn = document.getElementById('resend-btn');
        btn.disabled = true;

        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/auth/resend-verification.php', {
                email: currentEmail
            });
            if (res.success) {
                Toast.success(res.message || 'ส่งรหัสใหม่แล้ว');
                if (res.verification_code) {
                    // Update dev code box if exists
                    const devBox = document.querySelector('.dev-code-box .code');
                    if (devBox) devBox.textContent = res.verification_code;
                }
                startResendCountdown(60);
            } else {
                Toast.error(res.error || 'ไม่สามารถส่งรหัสได้');
                btn.disabled = false;
            }
        } catch (err) {
            Toast.error(err.error || 'เกิดข้อผิดพลาด');
            btn.disabled = false;
        }
    }

    function startResendCountdown(seconds) {
        resendCountdown = seconds;
        const btn = document.getElementById('resend-btn');
        const countdown = document.getElementById('resend-countdown');
        btn.style.display = 'none';

        const interval = setInterval(() => {
            resendCountdown--;
            countdown.textContent = `(${resendCountdown}s)`;
            if (resendCountdown <= 0) {
                clearInterval(interval);
                btn.style.display = 'inline';
                btn.disabled = false;
                countdown.textContent = '';
            }
        }, 1000);
    }
</script>
</body>

</html>