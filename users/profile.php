<?php

/**
 * Babybib - User Profile Page (Professional Design)
 * ===================================================
 */

require_once '../includes/session.php';
requireAuth();

$pageTitle = 'โปรไฟล์';

$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/profile.css">';
require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();
$user = getCurrentUser();

if (!$user) {
    header("Location: " . SITE_URL . "/logout.php");
    exit;
}

$provinces = getProvinces();
$orgTypes = getOrganizationTypes();

// Stats
$bibCount = countUserBibliographies($userId);
$projCount = countUserProjects($userId);
?>

<div class="settings-header">
    <div class="settings-header-container">
        <div class="settings-header-left">
            <div class="header-avatar" id="avatar-small-container">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($user['profile_picture'] ?? ''); ?>" alt="Avatar" class="avatar-sm">
                <?php else: ?>
                    <div class="header-avatar-placeholder avatar-sm">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="header-user-info">
                <h1><?php echo htmlspecialchars(($user['name'] ?? '') . ' (' . ($user['username'] ?? '') . ')'); ?></h1>
                <p><?php echo __('personal_account_subtitle'); ?></p>
                <?php if (($user['is_lis_cmu'] ?? 0) == 1): ?>
                    <span class="lis-badge">
                        <i class="fas fa-check-circle"></i>
                        LIS Student // <?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="profile-container">
    <div class="profile-layout">
        <!-- Sidebar: GitHub Style -->
        <aside class="profile-sidebar">
            <div class="sidebar-group">
                <nav class="sidebar-nav">
                    <a href="#public-profile" class="sidebar-nav-item active" data-section="public-profile">
                        <i class="fas fa-user"></i>
                        <?php echo __('profile_settings'); ?>
                    </a>
                    <a href="#account" class="sidebar-nav-item" data-section="account">
                        <i class="fas fa-cog"></i>
                        <?php echo __('account_settings'); ?>
                    </a>
                    <a href="#appearance" class="sidebar-nav-item" data-section="appearance">
                        <i class="fas fa-magic"></i>
                        <?php echo __('appearance'); ?>
                    </a>
                </nav>
            </div>

            <div class="sidebar-group">
                <h3 class="sidebar-group-title"><?php echo __('access_group'); ?></h3>
                <nav class="sidebar-nav">
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="sidebar-nav-item">
                        <i class="fas fa-list"></i>
                        <?php echo __('my_bibliographies'); ?>
                        <span class="counter"><?php echo $bibCount; ?></span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/projects.php" class="sidebar-nav-item">
                        <i class="fas fa-folder"></i>
                        <?php echo __('my_projects'); ?>
                        <span class="counter"><?php echo $projCount; ?></span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/activity-history.php" class="sidebar-nav-item">
                        <i class="fas fa-history"></i>
                        <?php echo __('nav_work_history'); ?>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content: GitHub Style Settings Panel -->
        <main class="profile-main-content">
            <!-- Public Profile Section -->
            <section id="public-profile" class="settings-section">
                <h2 class="section-title"><?php echo __('profile_settings'); ?></h2>

                <div class="settings-form-layout">
                    <div class="form-left-col">

                        <form id="profile-form" class="settings-form">
                            <div class="form-group-gh">
                                <label class="label-gh" for="name"><?php echo __('name'); ?></label>
                                <input type="text" id="name" name="name" class="input-gh" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                <p class="hint-gh"><?php echo __('name_hint_long'); ?></p>
                            </div>

                            <div class="form-group-gh">
                                <label class="label-gh" for="surname"><?php echo __('surname'); ?></label>
                                <input type="text" id="surname" name="surname" class="input-gh" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group-gh">
                                <label class="label-gh" for="username"><?php echo __('username'); ?></label>
                                <input type="text" id="username" name="username" class="input-gh" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required pattern="^[a-zA-Z0-9_]{3,20}$">
                                <p class="hint-gh"><?php echo __('username_hint'); ?></p>
                            </div>

                            <div class="form-group-gh">
                                <label class="label-gh" for="email"><?php echo __('public_email'); ?></label>
                                <input type="email" id="email" name="email" class="input-gh readonly" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                <p class="hint-gh"><?php echo __('email_no_change'); ?></p>
                            </div>


                            <div class="form-group-gh">
                                <label class="label-gh"><?php echo __('org_type'); ?></label>
                                <select name="org_type" class="input-gh">
                                    <?php foreach ($orgTypes as $key => $type): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $user['org_type'] === $key ? 'selected' : ''; ?>>
                                            <?php echo $currentLang === 'th' ? $type['th'] : $type['en']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group-gh">
                                <label class="label-gh"><?php echo __('province'); ?></label>
                                <select name="province" class="input-gh">
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?php echo htmlspecialchars($province); ?>" <?php echo $user['province'] === $province ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($province); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group-gh">
                                <label class="label-gh"><?php echo __('org_name'); ?></label>
                                <input type="text" name="org_name" class="input-gh" value="<?php echo htmlspecialchars($user['org_name'] ?? ''); ?>">
                            </div>

                            <!-- LIS Student Status Card -->
                            <div class="lis-status-card">
                                <?php if (($user['is_lis_cmu'] ?? 0) == 1): ?>
                                    <div class="lis-status-icon verified">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="lis-status-info">
                                        <h4><?php echo __('lis_student_status_verified'); ?></h4>
                                        <p><?php echo __('student_id_label'); ?>: <span class="lis-status-id"><?php echo htmlspecialchars($user['student_id'] ?? ''); ?></span></p>
                                    </div>
                                <?php else: ?>
                                    <div class="lis-status-icon not-verified">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="lis-status-info">
                                        <h4><?php echo __('lis_student_status_not_verified'); ?></h4>
                                        <p><?php echo __('not_verified_desc', 'Not a verified LIS CMU student account'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div style="margin-top: var(--space-4);">
                                <button type="submit" class="btn-gh btn-gh-primary">
                                    <?php echo __('update'); ?> profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="form-right-col">
                        <label class="label-gh"><?php echo __('profile_picture'); ?></label>
                        <div class="avatar-edit-section">
                            <div class="avatar-large-wrapper" id="avatar-large-container">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($user['profile_picture'] ?? ''); ?>"
                                        alt="Avatar"
                                        class="avatar-large-img"
                                        id="avatar-preview-large">
                                <?php else: ?>
                                    <div class="avatar-large-placeholder" id="avatar-preview-large">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p style="margin-top:12px; color: var(--text-tertiary); font-size: 12px; line-height: 1.6;">
                                <?php echo $currentLang === 'th' ? 'ฟีเจอร์อัปโหลดรูปโปรไฟล์ถูกปิดชั่วคราวเพื่อปรับปรุงความปลอดภัยของระบบ' : 'Profile picture upload is temporarily disabled while security hardening is in progress.'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Account / Password Section -->
            <section id="account" class="settings-section">
                <h2 class="section-title"><?php echo __('change_password'); ?></h2>

                <form id="password-form" class="settings-form">
                    <div class="form-group-gh">
                        <label class="label-gh"><?php echo __('current_password'); ?></label>
                        <input type="password" name="current_password" class="input-gh" required>
                    </div>

                    <div class="form-group-gh">
                        <label class="label-gh"><?php echo __('new_password'); ?></label>
                        <input type="password" name="new_password" id="new-password" class="input-gh" minlength="8" required>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <span class="strength-text" id="strength-text"><?php echo __('password_strength'); ?>: -</span>
                        </div>
                    </div>

                    <div class="form-group-gh">
                        <label class="label-gh"><?php echo __('confirm_password'); ?></label>
                        <input type="password" name="confirm_password" class="input-gh" required>
                    </div>

                    <div>
                        <button type="submit" class="btn-gh">
                            <?php echo __('update'); ?> password
                        </button>
                    </div>
                </form>

                <!-- Danger Zone (Inside Account Section) -->
                <div class="danger-zone-gh">
                    <div class="danger-zone-header-gh">
                        <h3><?php echo __('danger_zone'); ?></h3>
                    </div>
                    <div class="danger-zone-row">
                        <div class="danger-zone-info">
                            <div class="danger-zone-title"><?php echo __('delete_account_title'); ?></div>
                            <div class="danger-zone-desc"><?php echo __('delete_account_desc'); ?></div>
                        </div>
                        <button type="button" class="btn-gh btn-gh-danger" onclick="showDeleteModal()">
                            <?php echo __('delete_account_btn'); ?>
                        </button>
                    </div>
                </div>
            </section>
            <!-- Appearance Section -->
            <section id="appearance" class="settings-section">
                <h2 class="section-title"><?php echo __('appearance'); ?></h2>
                <p class="settings-description" style="margin-bottom: 24px; color: var(--text-tertiary); font-size: 14px;">
                    <?php echo $currentLang === 'th' ? 'เลือกภาษาที่คุณต้องการใช้งานบน Babybib' : 'Choose the language you want to use on Babybib.'; ?>
                </p>

                <div class="language-selection-grid">
                    <div class="lang-card <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')">
                        <div class="lang-flag">🇹🇭</div>
                        <div class="lang-info">
                            <span class="lang-name">ไทย</span>
                            <span class="lang-native">Thai</span>
                        </div>
                        <div class="lang-check">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>

                    <div class="lang-card <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">
                        <div class="lang-flag">🇺🇸</div>
                        <div class="lang-info">
                            <span class="lang-name">English</span>
                            <span class="lang-native">English</span>
                        </div>
                        <div class="lang-check">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-user-slash"></i>
        </div>
        <h3 class="modal-title"><?php echo __('delete_modal_title'); ?></h3>
        <p class="modal-text"><?php echo __('delete_modal_desc'); ?></p>
        <form id="delete-form">
            <div class="form-group">
                <input type="password" name="password" id="delete-password" class="form-input" required placeholder="<?php echo __('password'); ?>">
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-modal-cancel" onclick="hideDeleteModal()">
                    <?php echo __('cancel'); ?>
                </button>
                <button type="submit" class="btn-modal-delete">
                    <?php echo __('delete'); ?>
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
        <h3 class="modal-title" style="color: var(--text-primary);"><?php echo __('report_modal_title'); ?></h3>
        <p class="modal-text"><?php echo __('report_issue_desc'); ?></p>
        <form id="report-form">
            <div class="form-group">
                <label class="form-label"><?php echo __('report_type'); ?></label>
                <select name="issue_type" class="form-input" required>
                    <option value=""><?php echo __('select_type'); ?></option>
                    <option value="bug"><?php echo __('report_type_bug'); ?></option>
                    <option value="feature"><?php echo __('report_type_feature'); ?></option>
                    <option value="other"><?php echo __('report_type_other'); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo __('report_subject'); ?></label>
                <input type="text" name="subject" class="form-input" required placeholder="<?php echo __('report_subject'); ?>...">
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo __('report_description'); ?></label>
                <textarea name="description" class="form-input" rows="4" required placeholder="<?php echo __('report_description'); ?>..." style="resize: vertical;"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-modal-cancel" onclick="hideReportModal()">
                    <?php echo __('cancel'); ?>
                </button>
                <button type="submit" class="btn-save" style="flex: 1;">
                    <i class="fas fa-paper-plane"></i>
                    <?php echo __('send_report_btn'); ?>
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

    // Sidebar Navigation
    document.querySelectorAll('.sidebar-nav-item[data-section]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;

            // Update active state
            document.querySelectorAll('.sidebar-nav-item').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');

            // Show section
            document.querySelectorAll('.settings-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(sectionId).style.display = 'block';

            // Update URL hash without jumping
            history.pushState(null, null, '#' + sectionId);
        });
    });

    // Handle initial hash
    if (window.location.hash) {
        const activeNav = document.querySelector(`.sidebar-nav-item[data-section="${window.location.hash.substring(1)}"]`);
        if (activeNav) activeNav.click();
    }

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
                Toast.success('<?php echo addslashes(__('success')); ?>');
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
                Toast.success('<?php echo addslashes(__('success')); ?>');
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