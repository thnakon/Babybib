<!-- User Navigation Bar -->
<?php
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$currentUser = getCurrentUser();
$bibCount = countUserBibliographies($currentUser['id']);
$projectCount = countUserProjects($currentUser['id']);
?>
<nav class="navbar">
    <div class="navbar-container">
        <!-- Brand -->
        <div class="navbar-brand-wrapper">
            <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="navbar-brand">
                <i class="fas fa-book-open" style="color: var(--primary);"></i>
                <span class="navbar-brand-text">Babybib</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php" class="visit-site-btn" title="<?php echo $currentLang === 'th' ? 'ไปสู่หน้าหลักเว็บไซต์' : 'Go to main website'; ?>">
                <i class="fas fa-arrow-up-right-from-square"></i>
                <span><?php echo $currentLang === 'th' ? 'ไปหน้าเว็บ' : 'Main Site'; ?></span>
            </a>
        </div>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>



        <!-- Main Menu -->
        <div class="navbar-menu" id="navbar-menu">
            <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                <span><?php echo __('nav_dashboard'); ?></span>
            </a>

            <?php if (isset($_SESSION['last_bib'])): ?>
                <a href="<?php echo SITE_URL; ?>/summary.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span><?php echo $currentLang === 'th' ? 'สรุปล่าสุด' : 'Latest Summary'; ?></span>
                </a>
            <?php endif; ?>

            <a href="<?php echo SITE_URL; ?>/generate.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active' : ''; ?>">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span><?php echo __('create'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'bibliography-list.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span><?php echo __('nav_bibliography_list'); ?></span>
                <?php if ($bibCount > 0): ?>
                    <span class="badge"><?php echo $bibCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo SITE_URL; ?>/users/projects.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span><?php echo __('nav_projects'); ?></span>
                <?php if ($projectCount > 0): ?>
                    <span class="badge"><?php echo $projectCount; ?></span>
                <?php endif; ?>
            </a>

            <!-- User Profile Dropdown (inside menu) -->
            <div class="dropdown" id="user-dropdown">
                <button class="navbar-item dropdown-toggle" onclick="toggleDropdown('user-dropdown')">
                    <?php if (!empty($currentUser['profile_picture'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>"
                            alt="Avatar"
                            style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                </button>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/users/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <?php echo __('nav_profile'); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="dropdown-item">
                        <i class="fas fa-list"></i>
                        <?php echo $currentLang === 'th' ? 'บรรณานุกรมของฉัน' : 'My Bibliographies'; ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/projects.php" class="dropdown-item">
                        <i class="fas fa-folder"></i>
                        <?php echo $currentLang === 'th' ? 'โครงการของฉัน' : 'My Projects'; ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/users/activity-history.php" class="dropdown-item">
                        <i class="fas fa-history"></i>
                        <?php echo $currentLang === 'th' ? 'ประวัติการทำงาน' : 'Work History'; ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" onclick="logout(); return false;">
                        <i class="fas fa-sign-out-alt"></i>
                        <?php echo __('logout'); ?>
                    </a>
                </div>
            </div>

            <!-- Vertical Language Toggle (next to profile) -->
            <div class="lang-toggle-vertical">
                <button class="lang-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')" title="ภาษาไทย">TH</button>
                <button class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')" title="English">EN</button>
            </div>
        </div>
    </div>
    <style>
        /* Hover effect for user dropdown */
        @media (min-width: 769px) {
            #user-dropdown {
                position: relative;
            }

            #user-dropdown:hover .dropdown-menu {
                display: block;
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            /* Adjust dropdown position */
            #user-dropdown .dropdown-menu {
                right: 0;
                left: auto;
                margin-top: 0;
                transform: translateY(10px);
                /* Keep vertical offset, remove horizontal center */
            }

            #user-dropdown:hover .dropdown-menu {
                transform: translateY(0);
            }

            /* Move the arrow to the right side */
            #user-dropdown .dropdown-menu::before {
                left: auto;
                right: 20px;
                transform: none;
            }
        }

        /* Visit Site Link Styles */
        .navbar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .visit-site-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--white);
            border: 1px solid var(--gray-100);
            border-radius: 20px;
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
        }

        .visit-site-btn i {
            font-size: 10px;
            color: var(--primary);
        }

        .visit-site-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
        }

        @media (max-width: 600px) {
            .visit-site-btn span {
                display: none;
            }

            .visit-site-btn {
                padding: 8px;
                border-radius: 50%;
            }
        }
    </style>
</nav>

<script>
    function toggleMobileMenu() {
        document.getElementById('navbar-menu').classList.toggle('open');
        document.getElementById('navbar-actions').classList.toggle('open');
    }

    function toggleDropdown(id) {
        document.querySelectorAll('.dropdown').forEach(dd => {
            if (dd.id !== id) dd.classList.remove('open');
        });
        document.getElementById(id).classList.toggle('open');
    }

    function changeLanguage(lang) {
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.location = url.toString();
    }

    async function logout() {
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/logout.php');
            if (response.success) {
                Toast.success('<?php echo $currentLang === "th" ? "ออกจากระบบสำเร็จ! ไว้กลับมาใช้งานอีกนะครับ 👋" : "Logged out successfully! See you again soon 👋"; ?>');
                setTimeout(() => {
                    window.location.href = '<?php echo SITE_URL; ?>';
                }, 1500);
            }
        } catch (e) {
            window.location.href = '<?php echo SITE_URL; ?>';
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(dd => dd.classList.remove('open'));
        }
    });
</script>