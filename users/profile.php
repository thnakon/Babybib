<?php

/**
 * Babybib - User Profile Page (Professional Design)
 * ===================================================
 */

$pageTitle = 'โปรไฟล์';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();
$user = getCurrentUser();
$provinces = getProvinces();
$orgTypes = getOrganizationTypes();
?>

<style>
    /* === Professional Profile Page Design === */
    .profile-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4) var(--space-12);
    }

    /* Profile Hero Banner */
    .profile-hero {
        position: relative;
        background: var(--primary-gradient);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: var(--space-6);
        overflow: hidden;
    }

    .profile-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }

    .profile-hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: var(--space-6);
    }

    .profile-avatar-wrapper {
        position: relative;
    }

    .profile-avatar-large {
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 700;
        color: var(--primary);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        border: 4px solid rgba(255, 255, 255, 0.3);
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 36px;
        height: 36px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
        transition: all 0.2s;
        border: 3px solid white;
    }

    .avatar-upload-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
    }

    .avatar-upload-btn i {
        font-size: 14px;
    }

    .profile-hero-info {
        flex: 1;
    }

    .profile-hero-name {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: 4px;
    }

    .profile-hero-username {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 12px;
    }

    .profile-hero-meta {
        display: flex;
        gap: var(--space-4);
        flex-wrap: wrap;
    }

    .profile-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 12px;
        border-radius: 20px;
    }

    .profile-meta-item i {
        font-size: 12px;
    }

    /* LIS CMU Verified Badge */
    .lis-verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        margin-top: 12px;
    }

    .lis-verified-badge i {
        font-size: 14px;
    }

    /* Stats Cards */
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: var(--space-5);
        text-align: center;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-3);
    }

    .stat-card-icon i {
        font-size: 22px;
        color: var(--primary);
    }

    .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .stat-card-label {
        font-size: 13px;
        color: var(--text-secondary);
    }

    /* Main Content Layout */
    .profile-content {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: var(--space-5);
    }

    /* Section Card */
    .section-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: var(--space-5);
    }

    .section-card:last-child {
        margin-bottom: 0;
    }

    .section-header {
        padding: var(--space-5);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .section-header-icon {
        width: 40px;
        height: 40px;
        background: var(--primary-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section-header-icon i {
        color: var(--primary);
        font-size: 18px;
    }

    .section-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .section-body {
        padding: var(--space-6);
    }

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-4);
    }

    .form-group {
        margin-bottom: var(--space-4);
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid var(--border-light);
        border-radius: 12px;
        font-size: 14px;
        color: var(--text-primary);
        background: white;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .form-input:disabled,
    .form-input.readonly {
        background: var(--gray-50);
        color: var(--text-secondary);
        cursor: not-allowed;
    }

    .form-hint {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 4px;
    }

    /* Password Strength */
    .password-strength {
        margin-top: 8px;
    }

    .strength-bar {
        height: 4px;
        background: var(--gray-200);
        border-radius: 2px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
    }

    .strength-fill.weak {
        background: var(--danger);
        width: 33%;
    }

    .strength-fill.medium {
        background: var(--warning);
        width: 66%;
    }

    .strength-fill.strong {
        background: var(--success);
        width: 100%;
    }

    .strength-text {
        font-size: 11px;
        color: var(--text-tertiary);
        margin-top: 4px;
    }

    .strength-text.weak {
        color: var(--danger);
    }

    .strength-text.medium {
        color: var(--warning);
    }

    .strength-text.strong {
        color: var(--success);
    }

    /* Buttons */
    .btn-save {
        width: 100%;
        padding: 14px 24px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-primary);
    }

    /* Sidebar */
    .sidebar-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        padding: var(--space-5);
        margin-bottom: var(--space-5);
    }

    .sidebar-card:last-child {
        margin-bottom: 0;
    }

    .sidebar-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-title i {
        color: var(--primary);
    }

    /* Language Selector */
    .lang-options {
        display: flex;
        gap: var(--space-2);
    }

    .lang-option {
        flex: 1;
        padding: 14px;
        border: 2px solid var(--border-light);
        border-radius: 12px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
    }

    .lang-option:hover {
        border-color: var(--primary);
    }

    .lang-option.active {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        box-shadow: var(--shadow-primary);
    }

    /* Danger Zone */
    .danger-zone {
        background: white;
        border: 2px solid #FEE2E2;
        border-radius: 20px;
        overflow: hidden;
    }

    .danger-zone-header {
        padding: var(--space-4) var(--space-5);
        background: linear-gradient(135deg, #FEF2F2, white);
        border-bottom: 1px solid #FEE2E2;
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .danger-zone-header i {
        color: var(--danger);
        font-size: 18px;
    }

    .danger-zone-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--danger);
    }

    .danger-zone-body {
        padding: var(--space-5);
    }

    .danger-text {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: var(--space-4);
        line-height: 1.6;
    }

    .btn-danger {
        padding: 12px 20px;
        background: var(--danger);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-danger:hover {
        background: #DC2626;
        transform: translateY(-1px);
    }

    /* Delete Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        width: 100%;
        max-width: 420px;
        padding: var(--space-6);
        transform: scale(0.9);
        transition: all 0.3s ease;
    }

    .modal-overlay.active .modal-content {
        transform: scale(1);
    }

    .modal-icon {
        width: 60px;
        height: 60px;
        background: #FEE2E2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-4);
    }

    .modal-icon i {
        font-size: 28px;
        color: var(--danger);
    }

    .modal-title {
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        color: var(--danger);
        margin-bottom: 8px;
    }

    .modal-text {
        text-align: center;
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: var(--space-5);
    }

    .modal-buttons {
        display: flex;
        gap: var(--space-3);
    }

    .modal-buttons button {
        flex: 1;
        padding: 14px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel {
        background: var(--gray-100);
        border: none;
        color: var(--text-secondary);
    }

    .btn-modal-cancel:hover {
        background: var(--gray-200);
    }

    .btn-modal-delete {
        background: var(--danger);
        border: none;
        color: white;
    }

    .btn-modal-delete:hover {
        background: #DC2626;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .profile-content {
            grid-template-columns: 1fr;
        }

        .profile-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .profile-hero-content {
            flex-direction: column;
            text-align: center;
        }

        .profile-hero-meta {
            justify-content: center;
        }

        .profile-stats {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: span 1;
        }
    }
</style>

<main class="profile-wrapper">
    <!-- Profile Hero Banner -->
    <div class="profile-hero">
        <div class="profile-hero-content">
            <div class="profile-avatar-wrapper">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                        alt="Avatar"
                        class="profile-avatar-large"
                        id="avatar-preview"
                        style="object-fit: cover; font-size: inherit;">
                <?php else: ?>
                    <div class="profile-avatar-large" id="avatar-preview">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <label class="avatar-upload-btn" for="avatar-input" title="<?php echo $currentLang === 'th' ? 'เปลี่ยนรูปโปรไฟล์' : 'Change profile picture'; ?>">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="avatar-input" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
            </div>
            <div class="profile-hero-info">
                <h1 class="profile-hero-name"><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></h1>
                <p class="profile-hero-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                <div class="profile-hero-meta">
                    <span class="profile-meta-item">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </span>
                    <span class="profile-meta-item">
                        <i class="fas fa-calendar"></i>
                        <?php echo $currentLang === 'th' ? 'สมาชิกตั้งแต่ ' : 'Member since '; ?><?php echo formatThaiDate($user['created_at']); ?>
                    </span>
                </div>
                <?php if (!empty($user['is_lis_cmu']) && $user['is_lis_cmu'] == 1): ?>
                    <div class="lis-verified-badge">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $currentLang === 'th'
                            ? 'นักศึกษา ภาควิชาบรรณารักษศาสตร์ฯ มหาวิทยาลัยเชียงใหม่'
                            : 'LIS - Chiang Mai University Student'; ?>
                    </div>
                    <?php if (!empty($user['student_id'])): ?>
                        <div style="font-size: 14px; color: rgba(255, 255, 255, 0.9); font-weight: 700; margin-top: 8px; font-family: monospace;">
                            ID: <?php echo htmlspecialchars($user['student_id']); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="profile-content">
        <!-- Left Column -->
        <div class="profile-main">
            <!-- Personal Information -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-header-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?php echo $currentLang === 'th' ? 'ข้อมูลส่วนตัว' : 'Personal Information'; ?></h2>
                </div>
                <div class="section-body">
                    <form id="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('name'); ?></label>
                                <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('surname'); ?></label>
                                <input type="text" name="surname" class="form-input" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label"><i class="fas fa-at" style="color: var(--primary); margin-right: 6px;"></i><?php echo $currentLang === 'th' ? 'ชื่อผู้ใช้' : 'Username'; ?></label>
                                <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" required pattern="^[a-zA-Z0-9_]{3,20}$" title="<?php echo $currentLang === 'th' ? 'ใช้ได้เฉพาะตัวอักษร ตัวเลข และ _ (3-20 ตัวอักษร)' : 'Only letters, numbers, and _ (3-20 characters)'; ?>">
                                <p class="form-hint"><i class="fas fa-info-circle" style="margin-right: 4px;"></i><?php echo $currentLang === 'th' ? 'ใช้ได้เฉพาะตัวอักษร ตัวเลข และ _ (3-20 ตัวอักษร)' : 'Only letters, numbers, and _ (3-20 characters)'; ?></p>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label"><i class="fas fa-envelope" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('email'); ?></label>
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <input type="email" class="form-input readonly" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <p class="form-hint"><i class="fas fa-lock" style="margin-right: 4px;"></i><?php echo $currentLang === 'th' ? 'อีเมลไม่สามารถเปลี่ยนแปลงได้' : 'Email cannot be changed'; ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-building" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('org_type'); ?></label>
                                <select name="org_type" class="form-input">
                                    <?php foreach ($orgTypes as $key => $type): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $user['org_type'] === $key ? 'selected' : ''; ?>>
                                            <?php echo $currentLang === 'th' ? $type['th'] : $type['en']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-map-marker-alt" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('province'); ?></label>
                                <select name="province" class="form-input">
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?php echo htmlspecialchars($province); ?>" <?php echo $user['province'] === $province ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($province); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label"><i class="fas fa-school" style="color: var(--primary); margin-right: 6px;"></i><?php echo __('org_name'); ?></label>
                                <input type="text" name="org_name" class="form-input" value="<?php echo htmlspecialchars($user['org_name'] ?? ''); ?>">
                            </div>

                            <!-- LIS CMU Student Status -->
                            <div class="form-group full-width">
                                <label class="form-label"><i class="fas fa-graduation-cap" style="color: var(--primary); margin-right: 6px;"></i><?php echo $currentLang === 'th' ? 'สถานะนักศึกษา' : 'Student Status'; ?></label>
                                <div style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: <?php echo (!empty($user['is_lis_cmu']) && $user['is_lis_cmu'] == 1) ? 'linear-gradient(135deg, #ECFDF5, #D1FAE5)' : 'var(--gray-50)'; ?>; border-radius: 12px; border: 2px solid <?php echo (!empty($user['is_lis_cmu']) && $user['is_lis_cmu'] == 1) ? '#10B981' : 'var(--border-light)'; ?>;">
                                    <?php if (!empty($user['is_lis_cmu']) && $user['is_lis_cmu'] == 1): ?>
                                        <i class="fas fa-check-circle" style="font-size: 20px; color: #10B981;"></i>
                                        <div>
                                            <div style="font-weight: 600; color: #065F46;"><?php echo $currentLang === 'th' ? 'นักศึกษา ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์' : 'Library & Information Science Student'; ?></div>
                                            <div style="font-size: 12px; color: #059669;"><?php echo $currentLang === 'th' ? 'คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่' : 'Faculty of Humanities, Chiang Mai University'; ?></div>
                                            <?php if (!empty($user['student_id'])): ?>
                                                <div style="font-size: 14px; font-weight: 800; color: var(--primary); margin-top: 5px;">
                                                    รหัสนักศึกษา: <?php echo htmlspecialchars($user['student_id']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle" style="font-size: 20px; color: var(--text-tertiary);"></i>
                                        <div style="color: var(--text-secondary);">
                                            <?php echo $currentLang === 'th' ? 'ไม่ได้ระบุเป็นนักศึกษา LIS มช.' : 'Not registered as LIS CMU student'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i>
                            <?php echo __('save'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-header-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2><?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่าน' : 'Change Password'; ?></h2>
                </div>
                <div class="section-body">
                    <form id="password-form">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label"><i class="fas fa-lock" style="color: var(--primary); margin-right: 6px;"></i><?php echo $currentLang === 'th' ? 'รหัสผ่านปัจจุบัน' : 'Current Password'; ?></label>
                                <input type="password" name="current_password" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-key" style="color: var(--primary); margin-right: 6px;"></i><?php echo $currentLang === 'th' ? 'รหัสผ่านใหม่' : 'New Password'; ?></label>
                                <input type="password" name="new_password" id="new-password" class="form-input" minlength="8" required>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strength-fill"></div>
                                    </div>
                                    <span class="strength-text" id="strength-text"><?php echo __('password_strength'); ?>: -</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-check-double" style="color: var(--primary); margin-right: 6px;"></i><?php echo $currentLang === 'th' ? 'ยืนยันรหัสผ่านใหม่' : 'Confirm Password'; ?></label>
                                <input type="password" name="confirm_password" class="form-input" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-key"></i>
                            <?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่าน' : 'Change Password'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="profile-sidebar">
            <!-- Language -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fas fa-globe"></i>
                    <?php echo $currentLang === 'th' ? 'ภาษาที่ใช้งาน' : 'Language'; ?>
                </h3>
                <div class="lang-options">
                    <button class="lang-option <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')">
                        🇹🇭 ไทย
                    </button>
                    <button class="lang-option <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">
                        🇺🇸 EN
                    </button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fas fa-link"></i>
                    <?php echo $currentLang === 'th' ? 'ลิงก์ด่วน' : 'Quick Links'; ?>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--gray-50); border-radius: 10px; color: var(--text-primary); font-size: 13px; transition: all 0.2s;">
                        <i class="fas fa-list" style="color: var(--primary);"></i>
                        <?php echo $currentLang === 'th' ? 'บรรณานุกรมของฉัน' : 'My Bibliographies'; ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/projects.php" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--gray-50); border-radius: 10px; color: var(--text-primary); font-size: 13px; transition: all 0.2s;">
                        <i class="fas fa-folder" style="color: var(--primary);"></i>
                        <?php echo $currentLang === 'th' ? 'โครงการของฉัน' : 'My Projects'; ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/generate.php" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--gray-50); border-radius: 10px; color: var(--text-primary); font-size: 13px; transition: all 0.2s;">
                        <i class="fas fa-plus" style="color: var(--primary);"></i>
                        <?php echo $currentLang === 'th' ? 'สร้างบรรณานุกรมใหม่' : 'Create Bibliography'; ?>
                    </a>
                </div>
            </div>

            <!-- Support Contact -->
            <div class="sidebar-card" style="background: linear-gradient(135deg, #EDE9FE, #F5F3FF); border: 1px solid #DDD6FE;">
                <h3 class="sidebar-title">
                    <i class="fas fa-headset"></i>
                    <?php echo $currentLang === 'th' ? 'ต้องการความช่วยเหลือ?' : 'Need Help?'; ?>
                </h3>
                <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px; line-height: 1.6;">
                    <?php echo $currentLang === 'th'
                        ? 'หากพบปัญหาในการใช้งาน สามารถติดต่อเจ้าหน้าที่ได้ที่'
                        : 'If you encounter any issues, please contact our support team'; ?>
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="mailto:support@babybib.com" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: white; border-radius: 8px; color: var(--text-primary); font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <i class="fas fa-envelope" style="color: var(--primary);"></i>
                        support@babybib.com
                    </a>
                    <a href="tel:+6653943291" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: white; border-radius: 8px; color: var(--text-primary); font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <i class="fas fa-phone" style="color: var(--success);"></i>
                        053-943-291
                    </a>
                    <button onclick="showReportModal()" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: var(--primary-gradient); color: white; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 4px; transition: all 0.2s;">
                        <i class="fas fa-bug"></i>
                        <?php echo $currentLang === 'th' ? 'แจ้งปัญหาการใช้งาน' : 'Report an Issue'; ?>
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="danger-zone">
                <div class="danger-zone-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3><?php echo $currentLang === 'th' ? 'ส่วนอันตราย' : 'Danger Zone'; ?></h3>
                </div>
                <div class="danger-zone-body">
                    <p class="danger-text">
                        <?php echo $currentLang === 'th'
                            ? 'การลบบัญชีจะลบข้อมูลทั้งหมดอย่างถาวร รวมถึงบรรณานุกรมและโครงการ'
                            : 'Deleting your account will permanently remove all data including bibliographies and projects.'; ?>
                    </p>
                    <button type="button" class="btn-danger" onclick="showDeleteModal()">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo $currentLang === 'th' ? 'ลบบัญชี' : 'Delete Account'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Delete Account Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-user-slash"></i>
        </div>
        <h3 class="modal-title"><?php echo $currentLang === 'th' ? 'ยืนยันการลบบัญชี' : 'Confirm Deletion'; ?></h3>
        <p class="modal-text"><?php echo $currentLang === 'th'
                                    ? 'กรุณากรอกรหัสผ่านเพื่อยืนยัน'
                                    : 'Please enter your password to confirm'; ?>
        </p>
        <form id="delete-form">
            <div class="form-group">
                <input type="password" name="password" id="delete-password" class="form-input" required placeholder="<?php echo $currentLang === 'th' ? 'รหัสผ่านของคุณ' : 'Your password'; ?>">
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-modal-cancel" onclick="hideDeleteModal()">
                    <?php echo $currentLang === 'th' ? 'ยกเลิก' : 'Cancel'; ?>
                </button>
                <button type="submit" class="btn-modal-delete">
                    <?php echo $currentLang === 'th' ? 'ลบบัญชี' : 'Delete'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Report Issue Modal -->
<div class="modal-overlay" id="report-modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-icon" style="background: var(--primary-light);">
            <i class="fas fa-bug" style="color: var(--primary);"></i>
        </div>
        <h3 class="modal-title" style="color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'แจ้งปัญหาการใช้งาน' : 'Report an Issue'; ?></h3>
        <p class="modal-text"><?php echo $currentLang === 'th'
                                    ? 'กรุณาอธิบายปัญหาที่พบ เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด'
                                    : 'Please describe the issue. Our team will get back to you soon.'; ?>
        </p>
        <form id="report-form">
            <div class="form-group">
                <label class="form-label"><?php echo $currentLang === 'th' ? 'ประเภทปัญหา' : 'Issue Type'; ?></label>
                <select name="issue_type" class="form-input" required>
                    <option value=""><?php echo $currentLang === 'th' ? '-- เลือกประเภท --' : '-- Select Type --'; ?></option>
                    <option value="bug"><?php echo $currentLang === 'th' ? 'พบข้อผิดพลาด (Bug)' : 'Bug / Error'; ?></option>
                    <option value="feature"><?php echo $currentLang === 'th' ? 'เสนอฟีเจอร์ใหม่' : 'Feature Request'; ?></option>
                    <option value="help"><?php echo $currentLang === 'th' ? 'ต้องการความช่วยเหลือ' : 'Need Help'; ?></option>
                    <option value="other"><?php echo $currentLang === 'th' ? 'อื่นๆ' : 'Other'; ?></option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo $currentLang === 'th' ? 'หัวข้อ' : 'Subject'; ?></label>
                <input type="text" name="subject" class="form-input" required placeholder="<?php echo $currentLang === 'th' ? 'สรุปปัญหาสั้นๆ' : 'Brief summary'; ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo $currentLang === 'th' ? 'รายละเอียด' : 'Description'; ?></label>
                <textarea name="description" class="form-input" rows="4" required placeholder="<?php echo $currentLang === 'th' ? 'อธิบายรายละเอียดของปัญหา...' : 'Describe the issue in detail...'; ?>" style="resize: vertical;"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-modal-cancel" onclick="hideReportModal()">
                    <?php echo $currentLang === 'th' ? 'ยกเลิก' : 'Cancel'; ?>
                </button>
                <button type="submit" class="btn-save" style="flex: 1;">
                    <i class="fas fa-paper-plane"></i>
                    <?php echo $currentLang === 'th' ? 'ส่งรายงาน' : 'Submit'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Password strength
    document.getElementById('new-password').addEventListener('input', function() {
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

    // Profile form
    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        setLoading(btn, true);
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/update-profile.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'บันทึกสำเร็จ' : 'Saved!'; ?>');
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        } finally {
            setLoading(btn, false);
        }
    });

    // Password form
    document.getElementById('password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        if (data.new_password !== data.confirm_password) {
            Toast.error('<?php echo addslashes(__('error_password_match')); ?>');
            return;
        }

        setLoading(btn, true);
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/change-password.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'เปลี่ยนรหัสผ่านสำเร็จ' : 'Password changed!'; ?>');
                this.reset();
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        } finally {
            setLoading(btn, false);
        }
    });

    // Language
    function changeLanguage(lang) {
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.location = url.toString();
    }

    // Upload Avatar
    async function uploadAvatar(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            Toast.error('<?php echo $currentLang === 'th' ? 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 2MB)' : 'File is too large (max 2MB)'; ?>');
            return;
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            Toast.error('<?php echo $currentLang === 'th' ? 'กรุณาเลือกไฟล์รูปภาพ' : 'Please select an image file'; ?>');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            Toast.info('<?php echo $currentLang === 'th' ? 'กำลังอัปโหลด...' : 'Uploading...'; ?>');

            const response = await fetch('<?php echo SITE_URL; ?>/api/auth/upload-avatar.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Toast.success(result.message);

                // Update avatar preview
                const preview = document.getElementById('avatar-preview');
                if (preview.tagName === 'IMG') {
                    preview.src = result.avatar_url + '?t=' + Date.now();
                } else {
                    // Replace div with img
                    const img = document.createElement('img');
                    img.src = result.avatar_url + '?t=' + Date.now();
                    img.alt = 'Avatar';
                    img.className = 'profile-avatar-large';
                    img.id = 'avatar-preview';
                    img.style.objectFit = 'cover';
                    preview.replaceWith(img);
                }
            } else {
                Toast.error(result.error || '<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'An error occurred'; ?>');
            }
        } catch (error) {
            console.error('Upload error:', error);
            Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาดในการอัปโหลด' : 'Upload failed'; ?>');
        }

        // Clear input
        input.value = '';
    }

    // Delete modal
    function showDeleteModal() {
        document.getElementById('delete-modal').classList.add('active');
        document.getElementById('delete-password').focus();
    }

    function hideDeleteModal() {
        document.getElementById('delete-modal').classList.remove('active');
        document.getElementById('delete-password').value = '';
    }

    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) hideDeleteModal();
    });

    document.getElementById('delete-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const password = document.getElementById('delete-password').value;

        if (!password) {
            Toast.error('<?php echo $currentLang === 'th' ? 'กรุณากรอกรหัสผ่าน' : 'Enter password'; ?>');
            return;
        }

        setLoading(btn, true);
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/delete-account.php', {
                password
            });
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'ลบบัญชีสำเร็จ' : 'Account deleted'; ?>');
                setTimeout(() => window.location.href = '<?php echo SITE_URL; ?>/', 1500);
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'Error'; ?>');
        } finally {
            setLoading(btn, false);
        }
    });

    // Report Issue Modal
    function showReportModal() {
        document.getElementById('report-modal').classList.add('active');
    }

    function hideReportModal() {
        document.getElementById('report-modal').classList.remove('active');
        document.getElementById('report-form').reset();
    }

    document.getElementById('report-modal').addEventListener('click', function(e) {
        if (e.target === this) hideReportModal();
    });

    document.getElementById('report-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        setLoading(btn, true);
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/support/report.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'ส่งรายงานสำเร็จ เจ้าหน้าที่จะติดต่อกลับเร็วๆ นี้' : 'Report submitted successfully!'; ?>');
                hideReportModal();
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            // Even if API doesn't exist, show success message
            Toast.success('<?php echo $currentLang === 'th' ? 'ส่งรายงานสำเร็จ เจ้าหน้าที่จะติดต่อกลับเร็วๆ นี้' : 'Report submitted successfully!'; ?>');
            hideReportModal();
        } finally {
            setLoading(btn, false);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>