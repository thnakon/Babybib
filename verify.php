<?php
require_once 'includes/session.php';

// If already logged in AND verified, go to dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['is_verified'] == 1) {
        header('Location: users/dashboard.php');
        exit;
    }
}

$userId = $_GET['user_id'] ?? ($_SESSION['pending_verification_user_id'] ?? '');
$email = $_GET['email'] ?? ($_SESSION['pending_verification_email'] ?? '');

if (empty($userId)) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'ยืนยันอีเมล - Babybib';
require_once 'includes/header.php';
?>

<style>
    .otp-container {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin: var(--space-8) 0;
    }

    .otp-input {
        width: 48px;
        height: 60px;
        border: 1.5px solid var(--border-light);
        border-radius: 14px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 800;
        background: rgba(248, 250, 252, 0.8);
        color: var(--text-primary);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .otp-input:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        transform: translateY(-2px);
    }

    .btn-resend {
        background: transparent;
        border: none;
        color: var(--primary);
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        padding: 4px 8px;
        border-radius: 8px;
    }

    .btn-resend:hover:not(:disabled) {
        background: rgba(139, 92, 246, 0.1);
        text-decoration: underline;
    }

    .btn-resend:disabled {
        color: var(--text-tertiary);
        cursor: not-allowed;
    }

    .helper-text {
        text-align: center;
        margin-top: var(--space-6);
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }
</style>

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
        <a href="?lang=th&user_id=<?php echo $userId; ?>&email=<?php echo urlencode($email); ?>" class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>">TH</a>
        <a href="?lang=en&user_id=<?php echo $userId; ?>&email=<?php echo urlencode($email); ?>" class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
</div>

<!-- Floating Elements -->
<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card slide-up">
        <div class="auth-logo">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <div class="auth-header">
            <h1><?php echo $currentLang === 'th' ? 'ยืนยันอีเมล' : 'Verify Email'; ?></h1>
            <p>
                <?php echo $currentLang === 'th' ? 'กรุณากรอกรหัสยืนยัน 6 หลักที่เราส่งไปที่' : 'Please enter the 6-digit code sent to'; ?><br>
                <span class="font-bold text-vercel-black"><?php echo htmlspecialchars($email); ?></span>
            </p>
        </div>

        <form id="verifyForm">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
            <div class="otp-container">
                <input type="text" maxlength="1" class="otp-input" data-index="0" autofocus>
                <input type="text" maxlength="1" class="otp-input" data-index="1">
                <input type="text" maxlength="1" class="otp-input" data-index="2">
                <input type="text" maxlength="1" class="otp-input" data-index="3">
                <input type="text" maxlength="1" class="otp-input" data-index="4">
                <input type="text" maxlength="1" class="otp-input" data-index="5">
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-full btn-auth-submit">
                <?php echo $currentLang === 'th' ? 'ยืนยันรหัส' : 'Verify Code'; ?>
            </button>
        </form>

        <div class="helper-text">
            <?php echo $currentLang === 'th' ? 'หากไม่ได้รับรหัส?' : "Didn't receive the code?"; ?>
            <button type="button" id="resendBtn" class="btn-resend">
                <?php echo $currentLang === 'th' ? 'ส่งรหัสใหม่' : 'Resend Code'; ?>
            </button>
            <p id="timer" class="mt-2 text-xs font-medium text-vercel-gray-400"></p>
        </div>
    </div>
</div>

<script>
    // BG Animation
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-envelope', 'fa-shield-halved', 'fa-key', 'fa-lock', 'fa-paper-plane', 'fa-user-check'];
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
    setInterval(createFloatingItem, 3000);

    // OTP Logic
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').slice(0, 6);
            if (!/^\d+$/.test(pasteData)) return;
            
            pasteData.split('').forEach((char, i) => {

    function updateFullCode() {
        fullCodeInput.value = Array.from(otpInputs).map(input => input.value).join('');
    }

    // Form Submission
    verifyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const code = fullCodeInput.value;
        if (code.length !== 6) {
            showError('กรุณากรอกรหัสให้ครบ 6 หลัก');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        try {
            const userId = verifyForm.querySelector('[name="user_id"]').value;
            const response = await fetch('api/auth/verify-code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, code: code })
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = result.redirect;
            } else {
                showError(result.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'ยืนยันตัวตน';
            }
        } catch (error) {
            showError('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'ยืนยันตัวตน';
        }
    });

    // Handle Resend
    resendBtn.addEventListener('click', async () => {
        const userId = verifyForm.querySelector('[name="user_id"]').value;
        resendBtn.disabled = true;
        resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
        
        try {
            const response = await fetch('api/auth/resend-code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            const result = await response.json();
            
            if (result.success) {
                alert('ส่งรหัสยืนยันใหม่เรียบร้อยแล้ว');
                startCountdown(60);
            } else {
                alert(result.error);
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } catch (error) {
            alert('ไม่สามารถส่งรหัสใหม่ได้');
            resendBtn.disabled = false;
            resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    function showError(msg) {
        errorMsg.textContent = msg;
        errorMsg.classList.remove('hidden');
        setTimeout(() => { errorMsg.classList.add('hidden'); }, 5000);
    }

    function startCountdown(seconds) {
        const countdownEl = document.getElementById('countdown');
        countdownEl.classList.remove('hidden');
        
        let remaining = seconds;
        const interval = setInterval(() => {
            countdownEl.textContent = `(${remaining}s)`;
            remaining--;
            
            if (remaining < 0) {
                clearInterval(interval);
                countdownEl.classList.add('hidden');
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }, 1000);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
