<?php
require_once 'includes/session.php';

if (isLoggedIn()) {
    header('Location: users/dashboard.php');
    exit;
}

$pageTitle = 'ลืมรหัสผ่าน - Babybib';
require_once 'includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-vercel-gray-50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-vercel-gray-200">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-vercel-black tracking-tight">ลืมรหัสผ่าน?</h2>
            <p class="mt-3 text-vercel-gray-500 font-medium">
                กรอกอีเมลของคุณเพื่อรับลิงก์สำหรับรีเซ็ตรหัสผ่าน
            </p>
        </div>

        <form id="forgotForm" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-bold text-vercel-black mb-2">อีเมลของคุณ</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-vercel-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email" required 
                           class="block w-full pl-11 pr-4 py-3.5 border border-vercel-gray-200 rounded-xl text-vercel-black text-sm font-medium outline-none focus:border-vercel-blue transition-all" 
                           placeholder="your-email@example.com">
                </div>
            </div>

            <div id="status-msg" class="hidden p-4 rounded-xl text-sm font-medium text-center"></div>

            <button type="submit" id="submitBtn" class="w-full py-3.5 bg-vercel-black text-white rounded-xl font-bold text-sm hover:bg-vercel-black/90 transition-all flex items-center justify-center gap-2 group shadow-lg shadow-black/10">
                <span>ส่งลิงก์รีเซ็ตรหัสผ่าน</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
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
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const submitBtn = document.getElementById('submitBtn');
    const statusMsg = document.getElementById('status-msg');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

    try {
        const response = await fetch('api/auth/forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });
        const result = await response.json();

        statusMsg.classList.remove('hidden', 'bg-vercel-red/5', 'text-vercel-red', 'bg-vercel-green/5', 'text-vercel-green');
        
        if (result.success) {
            statusMsg.textContent = 'เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปที่อีเมลของคุณแล้ว กรุณาตรวจสอบภายใน 15 นาที';
            statusMsg.classList.add('bg-vercel-green/5', 'text-vercel-green', 'border', 'border-vercel-green/10');
            document.getElementById('forgotForm').reset();
        } else {
            statusMsg.textContent = result.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            statusMsg.classList.add('bg-vercel-red/5', 'text-vercel-red', 'border', 'border-vercel-red/10');
        }
        statusMsg.classList.remove('hidden');
    } catch (error) {
        statusMsg.textContent = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
        statusMsg.classList.add('bg-vercel-red/5', 'text-vercel-red', 'border', 'border-vercel-red/10', 'hidden');
        statusMsg.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>ส่งลิงก์รีเซ็ตรหัสผ่าน</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>