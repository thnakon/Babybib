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

$pageTitle = 'รีเซ็ตรหัสผ่าน - Babybib';
require_once 'includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-vercel-gray-50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-vercel-gray-200">
        <?php if (!$isValidToken): ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-vercel-red/10 rounded-full mb-4">
                    <svg class="w-8 h-8 text-vercel-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.268 14c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-vercel-black tracking-tight">ลิงก์หมดอายุหรือไมถูกต้อง</h2>
                <p class="mt-3 text-vercel-gray-500 font-medium">
                    ลิงก์สำหรับรีเซ็ตรหัสผ่านนี้อาจหมดอายุแล้ว หรือถูกใช้งานไปแล้ว กรุณาขอลิงก์ใหม่อีกครั้ง
                </p>
                <div class="mt-8">
                    <a href="forgot-password.php" class="inline-flex items-center justify-center px-6 py-3 bg-vercel-black text-white rounded-xl font-bold text-sm hover:bg-vercel-black/90 transition-all">
                        ขอลิงก์ใหม่
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-vercel-black tracking-tight">ตั้งรหัสผ่านใหม่</h2>
                <p class="mt-3 text-vercel-gray-500 font-medium">
                    กรุณากำหนดรหัสผ่านใหม่สำหรับบัญชีของคุณ
                </p>
            </div>

            <form id="resetForm" class="space-y-6">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div>
                    <label for="password" class="block text-sm font-bold text-vercel-black mb-2">รหัสผ่านใหม่</label>
                    <input type="password" id="password" name="password" required 
                           class="block w-full px-4 py-3.5 border border-vercel-gray-200 rounded-xl text-vercel-black text-sm font-medium outline-none focus:border-vercel-blue transition-all" 
                           placeholder="อย่างน้อย 8 ตัวอักษร">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-bold text-vercel-black mb-2">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="block w-full px-4 py-3.5 border border-vercel-gray-200 rounded-xl text-vercel-black text-sm font-medium outline-none focus:border-vercel-blue transition-all" 
                           placeholder="กรอกรหัสผ่านอีกครั้ง">
                </div>

                <div id="error-msg" class="hidden p-4 bg-vercel-red/5 border border-vercel-red/10 rounded-xl text-vercel-red text-sm font-medium text-center"></div>

                <button type="submit" id="submitBtn" class="w-full py-3.5 bg-vercel-black text-white rounded-xl font-bold text-sm hover:bg-vercel-black/90 transition-all flex items-center justify-center gap-2 group shadow-lg shadow-black/10">
                    <span>เปลี่ยนรหัสผ่าน</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
if (document.getElementById('resetForm')) {
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const token = document.querySelector('[name="token"]').value;
        const errorMsg = document.getElementById('error-msg');
        const submitBtn = document.getElementById('submitBtn');

        if (password.length < 8) {
            showError('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
            return;
        }

        if (password !== confirm) {
            showError('รหัสผ่านไม่ตรงกัน');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        try {
            const response = await fetch('api/auth/reset-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password })
            });
            const result = await response.json();

            if (result.success) {
                alert('เปลี่ยนรหัสผ่านสำเร็จ กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
                window.location.href = 'login.php';
            } else {
                showError(result.error || 'เกิดข้อผิดพลาด');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'เปลี่ยนรหัสผ่าน';
            }
        } catch (error) {
            showError('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'เปลี่ยนรหัสผ่าน';
        }
    });
}

function showError(msg) {
    const errorMsg = document.getElementById('error-msg');
    errorMsg.textContent = msg;
    errorMsg.classList.remove('hidden');
    setTimeout(() => { errorMsg.classList.add('hidden'); }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>