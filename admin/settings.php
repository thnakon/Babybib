<?php

/**
 * Babybib - Admin Settings Page
 * ===============================
 */

require_once '../includes/session.php';

$pageTitle = 'ตั้งค่าระบบ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM system_settings");
    $settingsRaw = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsRaw as $s) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }
} catch (Exception $e) {
    $settings = [];
}
?>



<div class="admin-settings-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('system_settings'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "บริหารจัดการและปรับแต่งการตั้งค่าระบบทั้งหมด" : "Manage and configure all system settings"; ?></p>
            </div>
        </div>
        <button onclick="saveSettings()" id="btn-save-header" class="btn btn-primary" style="border-radius: 14px; padding: 12px 30px; font-weight: 700; box-shadow: var(--shadow-primary);">
            <i class="fas fa-save"></i>
            <span><?php echo __('save'); ?></span>
        </button>
    </header>

    <form id="settings-form">
        <div class="settings-grid">
            <!-- General Settings -->
            <div class="settings-card slide-up stagger-1">
                <div class="card-header">
                    <div class="card-icon" style="background: #DDD6FE; color: #7C3AED;">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="card-title"><?php echo $currentLang === 'th' ? 'การตั้งค่าทั่วไป' : 'General Configuration'; ?></h3>
                </div>
                <div class="card-body">
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'ชื่อเว็บไซต์' : 'Site Title'; ?></label>
                        <input type="text" name="site_title" class="setting-input" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Babybib'); ?>" placeholder="Babybib">
                    </div>
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'คำอธิบายเว็บไซต์' : 'Site Description'; ?></label>
                        <textarea name="site_description" class="setting-input" style="height: 100px; resize: none;" placeholder="Web Application for..."><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'อีเมลติดต่อผู้ดูแลระบบ' : 'Contact Email'; ?></label>
                        <div style="position: relative;">
                            <i class="far fa-envelope" style="position: absolute; left: 14px; top: 14px; color: var(--text-tertiary);"></i>
                            <input type="email" name="contact_email" class="setting-input" style="padding-left: 40px;" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" placeholder="admin@babybib.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Limits -->
            <div class="settings-card slide-up stagger-2">
                <div class="card-header">
                    <div class="card-icon" style="background: #DCFCE7; color: #166534;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h3 class="card-title"><?php echo $currentLang === 'th' ? 'ขีดจำกัดการใช้งาน' : 'Usage Limits'; ?></h3>
                </div>
                <div class="card-body">
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'จำนวนบรรณานุกรมสูงสุดต่อผู้ใช้' : 'Max Bibliographies / User'; ?></label>
                        <input type="number" name="max_bibs_per_user" class="setting-input" value="<?php echo htmlspecialchars($settings['max_bibs_per_user'] ?? $settings['max_bibliographies_per_user'] ?? 300); ?>" min="1">
                        <input type="hidden" name="max_bibliographies_per_user" value="<?php echo htmlspecialchars($settings['max_bibs_per_user'] ?? $settings['max_bibliographies_per_user'] ?? 300); ?>">
                        <span style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 4px; display: block;">Default: 300</span>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'จำนวนโปรเจกต์สูงสุดต่อผู้ใช้' : 'Max Projects / User'; ?></label>
                        <input type="number" name="max_projects_per_user" class="setting-input" value="<?php echo htmlspecialchars($settings['max_projects_per_user'] ?? 30); ?>" min="1">
                        <span style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 4px; display: block;">Default: 30</span>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'อายุของบรรณานุกรม (วัน)' : 'Bibliography Lifetime (Days)'; ?></label>
                        <div style="position: relative;">
                            <i class="far fa-clock" style="position: absolute; left: 14px; top: 14px; color: var(--text-tertiary);"></i>
                            <input type="number" name="bib_lifetime_days" class="setting-input" style="padding-left: 40px;" value="<?php echo htmlspecialchars($settings['bib_lifetime_days'] ?? 730); ?>" min="0">
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 4px; display: block;">
                            <?php echo $currentLang === 'th' ? '0 = ไม่สิ้นสุด' : '0 = Unlimited'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Operations -->
            <div class="settings-card slide-up stagger-3">
                <div class="card-header">
                    <div class="card-icon" style="background: #FEF3C7; color: #B45309;">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="card-title"><?php echo $currentLang === 'th' ? 'สถานะระบบ' : 'System Status'; ?></h3>
                </div>
                <div class="card-body">
                    <div class="setting-group">
                        <div class="switch-wrapper">
                            <div class="switch-info">
                                <h4><?php echo $currentLang === 'th' ? 'โหมดบำรุงรักษา' : 'Maintenance Mode'; ?></h4>
                                <p><?php echo $currentLang === 'th' ? 'ปิดการเข้าถึงสำหรับผู้ใช้ทั่วไป' : 'Disable access for regular users'; ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div style="height: 15px;"></div>
                    <div class="setting-group">
                        <div class="switch-wrapper">
                            <div class="switch-info">
                                <h4><?php echo $currentLang === 'th' ? 'อนุญาตให้สมัครสมาชิก' : 'Allow Registration'; ?></h4>
                                <p><?php echo $currentLang === 'th' ? 'เปิดรับสมาชิกใหม่' : 'Enable new user signups'; ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="allow_registration" value="1" <?php echo ($settings['allow_registration'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div style="height: 15px;"></div>
                    <div class="setting-group">
                        <div class="switch-wrapper">
                            <div class="switch-info">
                                <h4><?php echo $currentLang === 'th' ? 'ระบบยืนยันตัวตนด้วยเมล' : 'Email Verification'; ?></h4>
                                <p><?php echo $currentLang === 'th' ? 'บังคับให้ผู้ใช้ใหม่ต้องยืนยันอีเมลก่อนเข้าใช้งาน' : 'Require new users to verify email before access'; ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="email_verification_enabled" value="1" <?php echo ($settings['email_verification_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Email Settings -->
            <div class="settings-card slide-up stagger-4">
                <div class="card-header">
                    <div class="card-icon" style="background: #FEE2E2; color: #B91C1C;">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3 class="card-title"><?php echo $currentLang === 'th' ? 'การตั้งค่าอีเมล (SMTP)' : 'Email Settings (SMTP)'; ?></h3>
                </div>
                <div class="card-body">
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'อีเมลผู้ส่ง (SMTP Username)' : 'SMTP Username'; ?></label>
                        <div style="position: relative;">
                            <i class="far fa-user" style="position: absolute; left: 14px; top: 14px; color: var(--text-tertiary);"></i>
                            <input type="text" name="smtp_username" class="setting-input" style="padding-left: 40px;" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? 'realwarm001@gmail.com'); ?>" placeholder="your-email@gmail.com">
                        </div>
                    </div>
                    <div class="setting-group">
                        <label class="setting-label"><?php echo $currentLang === 'th' ? 'App Password ของการส่งเมล' : 'Email App Password'; ?></label>
                        <div style="position: relative;">
                            <i class="fas fa-key" style="position: absolute; left: 14px; top: 14px; color: var(--text-tertiary);"></i>
                            <input type="password" name="smtp_password" class="setting-input" style="padding-left: 40px;" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? 'wsob tsis onml khlt'); ?>" placeholder="•••• •••• •••• ••••">
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 4px; display: block;">
                            <?php echo $currentLang === 'th' ? 'ใช้ App Password จาก Google สำหรับการส่งอีเมลผ่าน Gmail' : 'Use App Password from Google for sending emails via Gmail'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    async function saveSettings() {
        const form = document.getElementById('settings-form');
        const btn = document.getElementById('btn-save-header');

        // Prepare data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Sync redundant keys if necessary
        data.max_bibliographies_per_user = data.max_bibs_per_user;

        // Handle unchecked checkboxes (they won't be in formData)
        data.maintenance_mode = form.querySelector('input[name="maintenance_mode"]').checked ? '1' : '0';
        data.allow_registration = form.querySelector('input[name="allow_registration"]').checked ? '1' : '0';
        data.email_verification_enabled = form.querySelector('input[name="email_verification_enabled"]').checked ? '1' : '0';

        // UI Feedback
        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $currentLang === 'th' ? 'กำลังบันทึก...' : 'Saving...'; ?>';

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-settings.php', data);

            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'บันทึกการตั้งค่าเรียบร้อยแล้ว' : 'Settings saved successfully'; ?>');

                // Optional: visual feedback on cards
                document.querySelectorAll('.settings-card').forEach(card => {
                    card.style.borderColor = 'var(--success)';
                    setTimeout(() => card.style.borderColor = 'var(--gray-100)', 1000);
                });
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            console.error(e);
            Toast.error('<?php echo __('error_save'); ?>');
        } finally {
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }, 500);
        }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>