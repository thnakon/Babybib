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

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-vercel-gray-50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-vercel-gray-200">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-vercel-blue/10 rounded-full mb-4">
                <svg class="w-8 h-8 text-vercel-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-vercel-black tracking-tight">ยืนยันอีเมลของคุณ</h2>
            <p class="mt-3 text-vercel-gray-500 font-medium">
                เราได้ส่งรหัสยืนยัน 6 หลักไปที่ <br>
                <span class="text-vercel-black font-bold"><?php echo htmlspecialchars($email); ?></span>
            </p>
        </div>

        <form id="verifyForm" class="space-y-6">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
            
            <div>
                <label for="code" class="block text-sm font-bold text-vercel-black mb-2 text-center">รหัสยืนยัน 6 หลัก</label>
                <div class="flex justify-between gap-2 max-w-[280px] mx-auto" id="otp-inputs">
                    <?php for($i=1; $i<=6; $i++): ?>
                    <input type="text" maxlength="1" data-index="<?php echo $i; ?>" 
                           class="otp-input w-10 h-12 text-center text-xl font-bold border border-vercel-gray-200 rounded-lg outline-none focus:border-vercel-blue transition-all"
                           inputmode="numeric" pattern="[0-9]*">
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="code" id="full-code">
            </div>

            <div id="error-msg" class="hidden p-4 bg-vercel-red/5 border border-vercel-red/10 rounded-xl text-vercel-red text-sm font-medium text-center"></div>

            <button type="submit" id="submitBtn" class="w-full py-3.5 bg-vercel-black text-white rounded-xl font-bold text-sm hover:bg-vercel-black/90 transition-all flex items-center justify-center gap-2 group shadow-lg shadow-black/10">
                <span>ยืนยันตัวตน</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>

            <div class="text-center">
                <p class="text-sm text-vercel-gray-500 font-medium">
                    ไม่ได้รับอีเมล? 
                    <button type="button" id="resendBtn" class="text-vercel-blue font-bold hover:underline">ส่งรหัสใหม่</button>
                    <span id="countdown" class="hidden ml-1 text-vercel-gray-400"></span>
                </p>
            </div>
        </form>

        <div class="mt-8 pt-6 border-top border-vercel-gray-100 text-center">
            <a href="login.php" class="text-sm font-bold text-vercel-gray-500 hover:text-vercel-black transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                กลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const fullCodeInput = document.getElementById('full-code');
    const verifyForm = document.getElementById('verifyForm');
    const errorMsg = document.getElementById('error-msg');
    const submitBtn = document.getElementById('submitBtn');
    const resendBtn = document.getElementById('resendBtn');

    // Handle OTP Input Focus/Navigation
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1) {
                if (index < otpInputs.length - 1) otpInputs[index + 1].focus();
            }
            updateFullCode();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value) {
                if (index > 0) otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').slice(0, 6);
            if (!/^\d+$/.test(pasteData)) return;
            
            pasteData.split('').forEach((char, i) => {
                if (otpInputs[i]) otpInputs[i].value = char;
            });
            if (otpInputs[pasteData.length - 1]) otpInputs[pasteData.length - 1].focus();
            updateFullCode();
        });
    });

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
