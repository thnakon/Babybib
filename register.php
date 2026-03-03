<?php

/**
 * Babybib - Sign Up Page (Premium Full-screen Design)
 * ==================================================
 */

require_once 'includes/session.php';

$pageTitle = 'Sign Up';

$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/auth.css">';
require_once 'includes/header.php';
// No navbar or footer as requested

$provinces = getProvinces();
$orgTypes = getOrganizationTypes();
?>

<style>
    .form-input:focus {
        background: var(--white);
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: var(--space-6) 0 var(--space-4);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::after {
        content: '';
        flex-grow: 1;
        height: 1px;
        background: linear-gradient(to right, rgba(139, 92, 246, 0.2), transparent);
    }

    .btn-auth-submit {
        background: var(--primary-gradient);
        border-radius: 14px;
        padding: 14px;
        font-weight: 700;
        font-size: 1rem;
        margin-top: var(--space-6);
        box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4);
    }

    .auth-footer-links {
        margin-top: var(--space-6);
        text-align: center;
        font-size: var(--text-sm);
        color: var(--text-secondary);
    }

    .auth-footer-links a {
        font-weight: 600;
        color: var(--primary);
    }

    /* Password Strength UI */
    .password-strength {
        margin-top: 12px;
    }

    .strength-bar {
        height: 6px;
        background: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        width: 0;
        transition: all 0.3s;
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

    /* Password Requirements Checklist */
    .password-requirements {
        margin-top: 12px;
        padding: 12px 16px;
        background: var(--gray-50);
        border-radius: 12px;
        border: 1px solid var(--gray-200);
    }

    .password-requirements .req-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-tertiary);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .password-requirements ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .password-requirements li {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 6px;
        transition: all 0.2s;
    }

    .password-requirements li:last-child {
        margin-bottom: 0;
    }

    .password-requirements li i {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 9px;
        transition: all 0.3s;
    }

    .password-requirements li.invalid i {
        background: var(--gray-200);
        color: var(--gray-400);
    }

    .password-requirements li.valid i {
        background: var(--success);
        color: white;
    }

    .password-requirements li.valid {
        color: var(--success);
    }

    /* Back Home Button (Top Left) */
    .auth-back-home {
        position: fixed;
        top: var(--space-6);
        left: var(--space-6);
        z-index: 100;
    }

    .btn-back-home {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-primary);
        font-weight: 600;
        font-size: var(--text-sm);
        border: none;
        background: transparent;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 5px 0;
    }

    .btn-back-home:hover {
        transform: translateX(-5px);
        color: var(--primary);
    }
</style>

<!-- Top Navigation -->
<div class="auth-back-home">
    <a href="<?php echo SITE_URL; ?>" class="btn-back-home">
        <i class="fas fa-chevron-left"></i>
        <span><?php echo __('back_home'); ?></span>
    </a>
</div>

<!-- Language Switcher -->
<div class="auth-lang-switcher">
    <div class="lang-toggle">
        <a href="?lang=th" class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>">TH</a>
        <a href="?lang=en" class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
</div>

<!-- Floating Elements -->
<div class="bg-animation" id="bg-animation"></div>

<div class="auth-page-wrapper">
    <div class="auth-card register-card slide-up">
        <div class="auth-logo register-logo">
            <i class="fas fa-book-open"></i>
        </div>

        <div class="auth-header register-header">
            <h1><?php echo __('register_title'); ?></h1>
            <p><?php echo __('register_subtitle'); ?></p>
        </div>

        <form id="register-form" autocomplete="off" class="register-form">
            <div class="section-title">
                <i class="fas fa-user-shield"></i>
                <?php echo __('account_info'); ?>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label"><?php echo __('username'); ?> *</label>
                    <input type="text" id="username" name="username" class="form-input" required minlength="3" placeholder="Username">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('email'); ?> *</label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="email@example.com">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label"><?php echo __('name'); ?> *</label>
                    <input type="text" id="name" name="name" class="form-input" required placeholder="First Name">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('surname'); ?> *</label>
                    <input type="text" id="surname" name="surname" class="form-input" required placeholder="Last Name">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label"><?php echo __('password'); ?> *</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" required minlength="8" placeholder="••••••••">
                        <button type="button" class="btn btn-ghost btn-icon"
                            style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary);"
                            onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Password Strength Bar -->
                    <div class="password-strength" style="margin-top: 10px;">
                        <div class="strength-bar-container" style="display: flex; gap: 4px;">
                            <div class="strength-segment" id="seg1" style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; transition: all 0.3s;"></div>
                            <div class="strength-segment" id="seg2" style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; transition: all 0.3s;"></div>
                            <div class="strength-segment" id="seg3" style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; transition: all 0.3s;"></div>
                        </div>
                        <p id="strength-text" style="font-size: 12px; margin-top: 6px; color: #94a3b8; font-weight: 600;"><?php echo __('password_strength'); ?>: -</p>
                    </div>
                    <!-- Requirements Checklist -->
                    <div class="password-requirements">
                        <p class="req-title"><?php echo __('password_requirements_text'); ?></p>
                        <ul>
                            <li id="req-length" class="invalid">
                                <i class="fas fa-check"></i>
                                <span><?php echo __('req_8_chars'); ?></span>
                            </li>
                            <li id="req-uppercase" class="invalid">
                                <i class="fas fa-check"></i>
                                <span><?php echo __('req_uppercase'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('confirm_password'); ?> *</label>
                    <div style="position: relative;">
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" required placeholder="••••••••">
                        <button type="button" class="btn btn-ghost btn-icon"
                            style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary);"
                            onclick="togglePassword('password_confirm', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-building"></i>
                <?php echo __('org_info'); ?>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label"><?php echo __('org_type'); ?> *</label>
                    <select id="org_type" name="org_type" class="form-input form-select" required>
                        <option value=""><?php echo __('select_type'); ?></option>
                        <?php foreach ($orgTypes as $key => $type): ?>
                            <option value="<?php echo $key; ?>"><?php echo $currentLang === 'th' ? $type['th'] : $type['en']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('province'); ?> *</label>
                    <select id="province" name="province" class="form-input form-select" required>
                        <option value=""><?php echo __('province'); ?></option>
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><?php echo __('org_name'); ?></label>
                <input type="text" id="org_name" name="org_name" class="form-input" placeholder="Organization name">
            </div>

            <!-- LIS CMU Student Info -->
            <div class="mt-4">
                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="is_lis_cmu" id="is_lis_cmu" class="checkbox-animated" onchange="toggleStudentId(this.checked)">
                    <span class="text-sm font-medium text-secondary">
                        <?php echo __('is_lis_cmu'); ?>
                    </span>
                </label>
            </div>

            <div id="student-id-wrapper" style="display: none; margin-top: 15px; padding: 15px; background: rgba(139, 92, 246, 0.05); border-radius: 12px; border: 1px dashed var(--primary-light);">
                <div class="form-group mb-0">
                    <label class="form-label" style="color: var(--primary); font-weight: 700;">
                        <i class="fas fa-id-card"></i>
                        <?php echo __('student_id_label'); ?> *
                    </label>
                    <input type="text" id="student_id" name="student_id" class="form-input" placeholder="6XXXXXXXX" maxlength="15">
                </div>
            </div>

            <div class="mt-4">
                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="agree" class="checkbox-animated" required>
                    <span class="text-sm font-medium text-secondary">
                        <?php echo __('agree_terms'); ?>
                        <a href="#" onclick="showTerms(); return false;"><?php echo __('terms_of_service'); ?></a>
                    </span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full btn-auth-submit">
                <?php echo __('register'); ?>
            </button>
        </form>

        <div class="auth-footer-links">
            <p>
                <?php echo __('have_account'); ?>
                <a href="login.php"><?php echo __('login'); ?></a>
            </p>
        </div>
    </div>
</div>

<script>
    // Create floating items
    const bgAnimation = document.getElementById('bg-animation');
    const icons = ['fa-book', 'fa-pen-nib', 'fa-feather', 'fa-quote-left', 'fa-bookmark', 'fa-file-lines', 'fa-graduation-cap'];

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
        const delay = Math.random() * -20;

        item.style.left = startX + '%';
        item.style.top = startY + '%';
        item.style.fontSize = size + 'px';
        item.style.animationDuration = duration + 's';
        item.style.animationDelay = delay + 's';
        item.style.setProperty('--move-x', moveX + 'px');
        item.style.setProperty('--move-y', moveY + 'px');

        bgAnimation.appendChild(item);
        setTimeout(() => {
            item.remove();
            createFloatingItem();
        }, duration * 1000);
    }

    for (let i = 0; i < 20; i++) createFloatingItem();

    function toggleStudentId(checked) {
        const wrapper = document.getElementById('student-id-wrapper');
        const input = document.getElementById('student_id');
        if (checked) {
            wrapper.style.display = 'block';
            wrapper.classList.add('slide-up');
            input.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            input.removeAttribute('required');
            input.value = '';
        }
    }

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }

    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const seg1 = document.getElementById('seg1');
        const seg2 = document.getElementById('seg2');
        const seg3 = document.getElementById('seg3');
        const strengthText = document.getElementById('strength-text');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');

        // Calculate strength
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) strength++;

        // Reset segments
        const grayColor = '#e2e8f0';
        const redColor = '#ef4444';
        const yellowColor = '#f59e0b';
        const greenColor = '#10b981';

        seg1.style.background = grayColor;
        seg2.style.background = grayColor;
        seg3.style.background = grayColor;

        // Update strength bar segments
        if (password.length === 0) {
            strengthText.textContent = '<?php echo $currentLang === 'th' ? 'ความปลอดภัยรหัสผ่าน' : 'Password Strength'; ?>: -';
            strengthText.style.color = '#94a3b8';
        } else if (strength === 1) {
            seg1.style.background = redColor;
            strengthText.textContent = '<?php echo $currentLang === 'th' ? 'ความปลอดภัยรหัสผ่าน: อ่อน' : 'Password Strength: Weak'; ?>';
            strengthText.style.color = redColor;
        } else if (strength === 2) {
            seg1.style.background = yellowColor;
            seg2.style.background = yellowColor;
            strengthText.textContent = '<?php echo $currentLang === 'th' ? 'ความปลอดภัยรหัสผ่าน: ปานกลาง' : 'Password Strength: Medium'; ?>';
            strengthText.style.color = yellowColor;
        } else if (strength >= 3) {
            seg1.style.background = greenColor;
            seg2.style.background = greenColor;
            seg3.style.background = greenColor;
            strengthText.textContent = '<?php echo $currentLang === 'th' ? 'ความปลอดภัยรหัสผ่าน: แข็งแรง' : 'Password Strength: Strong'; ?>';
            strengthText.style.color = greenColor;
        }

        // Update requirements checklist
        if (password.length >= 8) {
            reqLength.classList.remove('invalid');
            reqLength.classList.add('valid');
        } else {
            reqLength.classList.remove('valid');
            reqLength.classList.add('invalid');
        }

        if (/[A-Z]/.test(password)) {
            reqUppercase.classList.remove('invalid');
            reqUppercase.classList.add('valid');
        } else {
            reqUppercase.classList.remove('valid');
            reqUppercase.classList.add('invalid');
        }
    });

    function showTerms() {
        const title = '<?php echo __('terms_of_service'); ?>';
        const content = `
            <div class="text-sm space-y-4">
                <p><b><?php echo __('terms_usage_title'); ?>:</b> <?php echo __('terms_usage_desc'); ?></p>
                <p><b><?php echo __('terms_data_retention_title'); ?>:</b> <?php echo __('terms_data_retention_desc'); ?></p>
                <p><b><?php echo __('terms_liability_title'); ?>:</b> <?php echo __('terms_liability_desc'); ?></p>
                <p class="mt-4"><a href="terms.php" target="_blank" class="text-primary font-bold"><?php echo __('read_full_terms'); ?></a></p>
            </div>
        `;
        Modal.create({
            title: title,
            content: content,
            footer: '<button class="btn btn-primary" onclick="Modal.close(this)"><?php echo __('close'); ?></button>'
        });
    }

    document.getElementById('register-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Handle is_lis_cmu checkbox properly
        const isLisCmuCheckbox = document.getElementById('is_lis_cmu');
        data.is_lis_cmu = isLisCmuCheckbox && isLisCmuCheckbox.checked ? true : false;

        if (data.password !== data.password_confirm) {
            Toast.error('<?php echo addslashes(__('error_password_match')); ?>');
            return;
        }

        setLoading(btn, true);
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/register.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === "th" ? "สำเร็จ!" : "Success!"; ?>');
                if (response.requires_verification) {
                    setTimeout(() => window.location.href = 'verify.php?user_id=' + response.user_id + '&email=' + encodeURIComponent(response.email), 1500);
                } else {
                    setTimeout(() => window.location.href = 'login.php', 1500);
                }
            } else {
                Toast.error(response.error);
                setLoading(btn, false);
            }
        } catch (error) {
            Toast.error('An error occurred');
            setLoading(btn, false);
        }
    });
</script>
</body>

</html>