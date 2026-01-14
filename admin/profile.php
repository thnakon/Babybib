<?php

/**
 * Babybib - Admin Profile Page
 * =============================
 */

require_once '../includes/session.php';

$pageTitle = 'โปรไฟล์ผู้ดูแลระบบ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

$userId = getCurrentUserId();
$user = getCurrentUser();
$provinces = getProvinces();
$orgTypes = getOrganizationTypes();

// Get admin stats
try {
    $db = getDB();
    $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $totalBibs = $db->query("SELECT COUNT(*) FROM bibliographies")->fetchColumn();
    $totalFeedback = $db->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();
    $loginCount = $db->query("SELECT COUNT(*) FROM activity_logs WHERE user_id = {$userId} AND action = 'login'")->fetchColumn();
} catch (Exception $e) {
    $totalUsers = 0;
    $totalBibs = 0;
    $totalFeedback = 0;
    $loginCount = 0;
}
?>


<div class="admin-profile-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="header-info">
                <h1><?php echo $currentLang === 'th' ? 'โปรไฟล์ผู้ดูแลระบบ' : 'Admin Profile'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'จัดการข้อมูลส่วนตัวและการตั้งค่าบัญชี' : 'Manage your personal information and account settings'; ?></p>
            </div>
        </div>
    </header>

    <!-- Profile Hero Card -->
    <div class="profile-hero-card slide-up stagger-1">
        <div class="admin-avatar-wrapper">
            <div class="admin-avatar">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Avatar">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <label class="avatar-edit-btn" for="avatar-input" title="<?php echo $currentLang === 'th' ? 'เปลี่ยนรูปโปรไฟล์' : 'Change avatar'; ?>">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="avatar-input" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
        </div>

        <div class="profile-hero-info">
            <div class="profile-hero-name"><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></div>
            <div class="profile-hero-role">
                <i class="fas fa-shield-alt"></i>
                <?php echo $currentLang === 'th' ? 'ผู้ดูแลระบบ' : 'Administrator'; ?>
            </div>
            <div class="profile-hero-meta">
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                <span><i class="fas fa-user"></i> @<?php echo htmlspecialchars($user['username']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo $currentLang === 'th' ? 'เข้าร่วมเมื่อ ' : 'Joined '; ?><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>

        <div class="profile-hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-value"><?php echo number_format($totalUsers); ?></div>
                <div class="hero-stat-label"><?php echo $currentLang === 'th' ? 'ผู้ใช้' : 'Users'; ?></div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-value"><?php echo number_format($totalBibs); ?></div>
                <div class="hero-stat-label"><?php echo $currentLang === 'th' ? 'บรรณานุกรม' : 'Bibs'; ?></div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-value"><?php echo number_format($loginCount); ?></div>
                <div class="hero-stat-label"><?php echo $currentLang === 'th' ? 'เข้าสู่ระบบ' : 'Logins'; ?></div>
            </div>
        </div>
    </div>

    <form id="profile-form" onsubmit="saveProfile(event)">
        <div class="form-grid">
            <!-- Personal Information -->
            <div class="form-card slide-up stagger-2">
                <div class="form-card-header">
                    <div class="form-card-icon blue">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="form-card-title"><?php echo $currentLang === 'th' ? 'ข้อมูลส่วนตัว' : 'Personal Information'; ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?php echo __('name'); ?></label>
                        <input type="text" class="form-input" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo __('surname'); ?></label>
                        <input type="text" class="form-input" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('username'); ?></label>
                    <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-card slide-up stagger-3">
                <div class="form-card-header">
                    <div class="form-card-icon purple">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="form-card-title"><?php echo $currentLang === 'th' ? 'ข้อมูลติดต่อ' : 'Contact Information'; ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('email'); ?></label>
                    <input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('province'); ?></label>
                    <select class="form-input" name="province">
                        <option value=""><?php echo $currentLang === 'th' ? '-- เลือกจังหวัด --' : '-- Select Province --'; ?></option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?php echo htmlspecialchars($prov); ?>" <?php echo $user['province'] === $prov ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prov); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Organization -->
            <div class="form-card slide-up stagger-4">
                <div class="form-card-header">
                    <div class="form-card-icon green">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="form-card-title"><?php echo $currentLang === 'th' ? 'ข้อมูลองค์กร' : 'Organization'; ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('org_type'); ?></label>
                    <select class="form-input" name="org_type">
                        <?php foreach ($orgTypes as $key => $org): ?>
                            <option value="<?php echo $key; ?>" <?php echo $user['org_type'] === $key ? 'selected' : ''; ?>>
                                <?php echo $currentLang === 'th' ? $org['th'] : $org['en']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('org_name'); ?></label>
                    <input type="text" class="form-input" name="org_name" value="<?php echo htmlspecialchars($user['org_name'] ?? ''); ?>">
                </div>
            </div>

            <!-- Security -->
            <div class="form-card slide-up stagger-5">
                <div class="form-card-header">
                    <div class="form-card-icon orange">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="form-card-title"><?php echo $currentLang === 'th' ? 'ความปลอดภัย' : 'Security'; ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo $currentLang === 'th' ? 'รหัสผ่านปัจจุบัน' : 'Current Password'; ?></label>
                    <input type="password" class="form-input" name="current_password" placeholder="<?php echo $currentLang === 'th' ? 'กรอกเพื่อเปลี่ยนรหัสผ่าน' : 'Enter to change password'; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'รหัสผ่านใหม่' : 'New Password'; ?></label>
                        <input type="password" class="form-input" name="new_password" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo __('confirm_password'); ?></label>
                        <input type="password" class="form-input" name="confirm_password" placeholder="••••••••">
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <button type="button" class="btn-cancel" onclick="window.location.reload()">
                <i class="fas fa-times"></i>
                <?php echo __('cancel'); ?>
            </button>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i>
                <?php echo $currentLang === 'th' ? 'บันทึกการเปลี่ยนแปลง' : 'Save Changes'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    async function saveProfile(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validate password change
        if (data.new_password || data.confirm_password) {
            if (!data.current_password) {
                Toast.error('<?php echo $currentLang === "th" ? "กรุณากรอกรหัสผ่านปัจจุบัน" : "Please enter current password"; ?>');
                return;
            }
            if (data.new_password !== data.confirm_password) {
                Toast.error('<?php echo __("error_password_match"); ?>');
                return;
            }
        }

        const btn = form.querySelector('.btn-save');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $currentLang === "th" ? "กำลังบันทึก..." : "Saving..."; ?>';

        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/user/update-profile.php', data);
            if (res.success) {
                Toast.success('<?php echo $currentLang === "th" ? "บันทึกข้อมูลสำเร็จ" : "Profile updated successfully"; ?>');
                // Clear password fields
                form.querySelector('[name="current_password"]').value = '';
                form.querySelector('[name="new_password"]').value = '';
                form.querySelector('[name="confirm_password"]').value = '';
            } else {
                Toast.error(res.error || 'Error');
            }
        } catch (err) {
            console.error(err);
            Toast.error('<?php echo __("error_save"); ?>');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    async function uploadAvatar(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            Toast.error('<?php echo $currentLang === "th" ? "ไฟล์ใหญ่เกิน 2MB" : "File too large (max 2MB)"; ?>');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            Toast.info('<?php echo $currentLang === "th" ? "กำลังอัปโหลด..." : "Uploading..."; ?>');
            const res = await fetch('<?php echo SITE_URL; ?>/api/user/upload-avatar.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                Toast.success('<?php echo $currentLang === "th" ? "อัปโหลดสำเร็จ" : "Avatar updated"; ?>');
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.error(data.error || 'Upload failed');
            }
        } catch (err) {
            Toast.error('Upload error');
        }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>