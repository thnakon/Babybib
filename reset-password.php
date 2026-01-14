<?php

/**
 * Babybib - Reset Password Page
 * ===============================
 */

require_once 'includes/session.php';

$pageTitle = 'รีเซ็ตรหัสผ่าน';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/auth.css">';
require_once 'includes/header.php';

$token = sanitize($_GET['token'] ?? '');
$validToken = false;
$userId = null;

if ($token) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND is_active = 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $validToken = true;
            $userId = $user['id'];
        }
    } catch (Exception $e) {
        // Invalid token
    }
}

require_once 'includes/navbar-guest.php';
?>

<main class="container" style="padding: var(--space-12) 0;">
    <div class="card container-sm slide-up" style="margin: 0 auto;">
        <div class="card-body" style="padding: var(--space-8);">
            <?php if (!$validToken): ?>
                <!-- Invalid/Expired Token -->
                <div class="text-center">
                    <div style="width: 64px; height: 64px; background: var(--danger-light); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                        <i class="fas fa-times" style="font-size: var(--text-2xl); color: var(--danger);"></i>
                    </div>
                    <h2><?php echo $currentLang === 'th' ? 'ลิงก์ไม่ถูกต้องหรือหมดอายุ' : 'Invalid or Expired Link'; ?></h2>
                    <p class="text-secondary mt-2"><?php echo $currentLang === 'th' ? 'กรุณาขอลิงก์รีเซ็ตรหัสผ่านใหม่' : 'Please request a new password reset link'; ?></p>
                    <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="btn btn-primary mt-4">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo $currentLang === 'th' ? 'ขอลิงก์ใหม่' : 'Request New Link'; ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- Reset Password Form -->
                <div class="text-center mb-6">
                    <div style="width: 64px; height: 64px; background: var(--primary-gradient); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                        <i class="fas fa-lock" style="font-size: var(--text-2xl); color: var(--white);"></i>
                    </div>
                    <h2><?php echo $currentLang === 'th' ? 'ตั้งรหัสผ่านใหม่' : 'Set New Password'; ?></h2>
                    <p class="text-secondary mt-2"><?php echo $currentLang === 'th' ? 'กรอกรหัสผ่านใหม่ของคุณ' : 'Enter your new password'; ?></p>
                </div>

                <form id="reset-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'รหัสผ่านใหม่' : 'New Password'; ?><span class="required">*</span></label>
                        <input type="password" name="password" id="password" class="form-input" minlength="8" required>
                        <div class="password-strength mt-2">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <span class="strength-text" id="strength-text"><?php echo __('password_strength'); ?>: -</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'ยืนยันรหัสผ่านใหม่' : 'Confirm New Password'; ?><span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>

                    <p class="form-help mb-4"><?php echo __('password_requirements'); ?></p>

                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-check"></i>
                        <?php echo $currentLang === 'th' ? 'บันทึกรหัสผ่านใหม่' : 'Save New Password'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php if ($validToken): ?>
    <script>
        document.getElementById('password').addEventListener('input', function() {
            const strength = Validator.getPasswordStrength(this.value);
            const fill = document.getElementById('strength-fill');
            const text = document.getElementById('strength-text');

            fill.className = 'strength-fill ' + strength.level;
            fill.style.width = strength.score + '%';

            const labels = {
                weak: '<?php echo addslashes(__('password_weak')); ?>',
                medium: '<?php echo addslashes(__('password_medium')); ?>',
                strong: '<?php echo addslashes(__('password_strong')); ?>'
            };
            text.className = 'strength-text ' + strength.level;
            text.textContent = '<?php echo addslashes(__('password_strength')); ?>: ' + labels[strength.level];
        });

        document.getElementById('reset-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = this.querySelector('button[type="submit"]');
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            if (data.password !== data.confirm_password) {
                Toast.error('<?php echo addslashes(__('error_password_match')); ?>');
                return;
            }

            if (!Validator.password(data.password)) {
                Toast.error('<?php echo addslashes(__('error_password_weak')); ?>');
                return;
            }

            setLoading(btn, true);

            try {
                const response = await API.post('<?php echo SITE_URL; ?>/api/auth/reset-password.php', data);

                if (response.success) {
                    Toast.success('<?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่านสำเร็จ' : 'Password changed successfully'; ?>');
                    setTimeout(() => {
                        window.location.href = '<?php echo SITE_URL; ?>/login.php';
                    }, 2000);
                } else {
                    Toast.error(response.error);
                    setLoading(btn, false);
                }
            } catch (e) {
                Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'An error occurred'; ?>');
                setLoading(btn, false);
            }
        });
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>