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
        <!-- New Mobile Overlay -->
        <div id="mobile-nav-overlay" onclick="toggleMobileMenu()"></div>

        <!-- Brand -->
        <div class="navbar-brand-wrapper">
            <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="navbar-brand">
                <span class="navbar-brand-text comfortaa-1">Babybib</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php" class="visit-site-btn" title="<?php echo __('nav_visit_site_title'); ?>">
                <i class="fas fa-arrow-up-right-from-square"></i>
                <span><?php echo __('nav_visit_site_text'); ?></span>
            </a>
        </div>

        <!-- Mobile Menu Button (Animated) -->
        <button class="mobile-menu-btn" id="mobile-menu-btn" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
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
                    <span><?php echo __('nav_latest_summary'); ?></span>
                    <span class="badge-new">!</span>
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
            <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="navbar-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active' : ''; ?>">
                <i class="fas fa-file-lines"></i>
                <span><?php echo __('nav_report_templates'); ?></span>
               
            </a>

            <!-- User Profile Dropdown (inside menu) -->
            <details class="dropdown" id="user-dropdown">
                <summary class="navbar-item dropdown-toggle flex items-center gap-1 cursor-pointer list-none bg-transparent border-none">
                    <?php if (!empty($currentUser['profile_picture'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>"
                            alt="Avatar"
                            style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                    <i class="fas fa-chevron-down text-[10px] ml-1"></i>
                </summary>
                <ul class="dropdown-content menu p-2 shadow-lg bg-base-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-box w-48 z-[100] mt-1 text-base-content md:right-0 md:left-auto">
                    <li>
                        <a href="<?php echo SITE_URL; ?>/users/profile.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-user text-primary dark:text-violet-400"></i>
                            <span><?php echo __('nav_profile'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-list text-primary dark:text-violet-400"></i>
                            <span><?php echo __('nav_my_bibliographies'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/users/projects.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-folder text-primary dark:text-violet-400"></i>
                            <span><?php echo __('nav_my_projects'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/users/activity-history.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-history text-primary dark:text-violet-400"></i>
                            <span><?php echo __('nav_work_history'); ?></span>
                        </a>
                    </li>
                    <div class="h-[1px] bg-slate-200 dark:bg-zinc-700 my-1"></div>
                    <li>
                        <a href="#" onclick="logout(); return false;" class="flex items-center gap-2 py-2">
                            <i class="fas fa-sign-out-alt text-rose-500"></i>
                            <span><?php echo __('logout'); ?></span>
                        </a>
                    </li>
                </ul>
            </details>

            <!-- Language Toggle Dropdown -->
            <details class="dropdown" id="lang-dropdown">
                <summary class="navbar-item dropdown-toggle flex items-center gap-1 cursor-pointer list-none bg-transparent border-none font-bold text-slate-500 dark:text-slate-400" style="padding: 6px 8px;">
                    <span><?php echo strtoupper(getCurrentLanguage()); ?></span>
                    <i class="fas fa-chevron-down text-[9px] ml-1"></i>
                </summary>
                <ul class="dropdown-content menu p-2 shadow-lg bg-base-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-box w-24 z-[100] mt-1 text-center text-base-content">
                    <li>
                        <a href="#" onclick="changeLanguage('th'); return false;" class="justify-center font-bold <?php echo (getCurrentLanguage() === 'th') ? 'text-primary' : ''; ?>">TH</a>
                    </li>
                    <li>
                        <a href="#" onclick="changeLanguage('en'); return false;" class="justify-center font-bold <?php echo (getCurrentLanguage() === 'en') ? 'text-primary' : ''; ?>">EN</a>
                    </li>
                </ul>
            </details>
        </div>
    </div>
    <style>


        /* Visit Site Link Styles */
        .navbar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .visit-site-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: var(--white);
            border: 1px solid var(--gray-100);
            border-radius: 18px;
            color: var(--text-secondary);
            font-size: 9px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
        }

        .visit-site-btn i {
            font-size: 9px;
            color: var(--primary);
        }

        .visit-site-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
        }

        .badge-new {
            background: linear-gradient(135deg, #fb7185, #ec4899);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            padding: 3px 7px;
            border-radius: 999px;
            line-height: 1;
            box-shadow: 0 6px 14px rgba(236, 72, 153, 0.2);
        }

        @media (max-width: 600px) {
            .visit-site-btn span {
                display: none;
            }

            .visit-site-btn {
                padding: 7px;
                border-radius: 50%;
            }
        }
    </style>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('navbar-menu');
        const btn = document.getElementById('mobile-menu-btn');
        const overlay = document.getElementById('mobile-nav-overlay');

        menu.classList.toggle('open');
        btn.classList.toggle('open');
        overlay.classList.toggle('active');

        // Prevent body scroll when menu is open
        if (menu.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    async function logout() {
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/auth/logout.php');
            if (response.success) {
                Toast.success('<?php echo __('logout_success_msg'); ?>');
                setTimeout(() => {
                    window.location.href = '<?php echo SITE_URL; ?>';
                }, 1500);
            }
        } catch (e) {
            window.location.href = '<?php echo SITE_URL; ?>';
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('details.dropdown[open]').forEach(dd => {
                dd.removeAttribute('open');
            });
        } else {
            // Close other details dropdowns when one is opened
            const targetDetails = e.target.closest('details.dropdown');
            if (targetDetails) {
                document.querySelectorAll('details.dropdown[open]').forEach(dd => {
                    if (dd !== targetDetails) {
                        dd.removeAttribute('open');
                    }
                });
            }
        }
    });
</script>