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
<div class="navbar bg-base-100/90 backdrop-blur-md sticky top-0 z-50 border-b border-base-300 px-4 py-2 shadow-sm transition-all duration-300">
    <div class="navbar-start flex items-center gap-2">
        <!-- Brand -->
        <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="flex items-center gap-2 px-2 select-none">
            <span class="font-bold text-2xl tracking-wide bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 bg-clip-text text-transparent transition-all duration-300 hover:brightness-110">Babybib</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-ghost btn-xs border border-base-300 rounded-full flex items-center gap-1 font-bold text-[10px] uppercase opacity-80 hover:opacity-100" title="<?php echo __('nav_visit_site_title'); ?>">
            <i class="fas fa-arrow-up-right-from-square text-primary"></i>
            <span><?php echo __('nav_visit_site_text'); ?></span>
        </a>
    </div>

    <!-- Mobile Navigation Toggle Button -->
    <div class="navbar-end lg:hidden flex gap-2">
        <!-- Mobile Menu Trigger -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <i class="fas fa-bars text-lg"></i>
            </div>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-56 border border-base-200 z-50 mt-1">
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active font-bold' : ''; ?>">
                        <i class="fas fa-chart-pie w-5"></i><?php echo __('nav_dashboard'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/generate.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active font-bold' : ''; ?>">
                        <i class="fas fa-wand-magic-sparkles w-5"></i><?php echo __('create'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bibliography-list.php' ? 'active font-bold' : ''; ?>">
                        <i class="fas fa-list w-5"></i><?php echo __('nav_bibliography_list'); ?>
                        <?php if ($bibCount > 0): ?>
                            <span class="badge badge-primary badge-sm"><?php echo $bibCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/projects.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active font-bold' : ''; ?>">
                        <i class="fas fa-folder w-5"></i><?php echo __('nav_projects'); ?>
                        <?php if ($projectCount > 0): ?>
                            <span class="badge badge-primary badge-sm"><?php echo $projectCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active font-bold' : ''; ?>">
                        <i class="fas fa-file-lines w-5"></i><?php echo __('nav_report_templates'); ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['last_bib'])): ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/summary.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active font-bold' : ''; ?>">
                            <i class="fas fa-file-invoice w-5"></i><?php echo __('nav_latest_summary'); ?>
                            <span class="badge badge-secondary badge-sm">!</span>
                        </a>
                    </li>
                <?php endif; ?>
                <div class="divider my-1"></div>
                <li class="menu-title"><span><?php echo __('nav_profile'); ?></span></li>
                <li><a href="<?php echo SITE_URL; ?>/users/profile.php"><i class="fas fa-user w-5"></i><?php echo __('nav_profile'); ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/users/activity-history.php"><i class="fas fa-history w-5"></i><?php echo __('nav_work_history'); ?></a></li>
                <div class="divider my-1"></div>
                <li><a onclick="logout()"><i class="fas fa-sign-out-alt w-5 text-error"></i><?php echo __('logout'); ?></a></li>
            </ul>
        </div>
    </div>

    <!-- Desktop Navigation Menu -->
    <div class="navbar-end hidden lg:flex items-center gap-1">
        <ul class="menu menu-horizontal px-1 gap-1">
            <li>
                <a href="<?php echo SITE_URL; ?>/users/dashboard.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-chart-pie text-lg"></i>
                    <span><?php echo __('nav_dashboard'); ?></span>
                </a>
            </li>

            <?php if (isset($_SESSION['last_bib'])): ?>
                <li>
                    <a href="<?php echo SITE_URL; ?>/summary.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl relative <?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                        <i class="fas fa-file-invoice text-lg"></i>
                        <span><?php echo __('nav_latest_summary'); ?></span>
                        <span class="absolute top-1 right-1 badge badge-secondary badge-xs animate-bounce">!</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?php echo SITE_URL; ?>/generate.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-wand-magic-sparkles text-lg"></i>
                    <span><?php echo __('create'); ?></span>
                </a>
            </li>

            <li>
                <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl relative <?php echo basename($_SERVER['PHP_SELF']) === 'bibliography-list.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-list text-lg"></i>
                    <span><?php echo __('nav_bibliography_list'); ?></span>
                    <?php if ($bibCount > 0): ?>
                        <span class="absolute top-1 right-1 badge badge-primary badge-xs"><?php echo $bibCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="<?php echo SITE_URL; ?>/users/projects.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl relative <?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-folder text-lg"></i>
                    <span><?php echo __('nav_projects'); ?></span>
                    <?php if ($projectCount > 0): ?>
                        <span class="absolute top-1 right-1 badge badge-primary badge-xs"><?php echo $projectCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-file-lines text-lg"></i>
                    <span><?php echo __('nav_report_templates'); ?></span>
                </a>
            </li>
        </ul>

        <!-- Language Selector -->
        <div class="dropdown dropdown-hover dropdown-bottom dropdown-end mx-2">
            <div tabindex="0" role="button" class="btn btn-ghost btn-sm font-bold text-sm tracking-wider uppercase border border-base-300 rounded-lg px-3">
                <?php echo strtoupper(getCurrentLanguage()); ?>
                <i class="fas fa-chevron-down text-[9px] opacity-70"></i>
            </div>
            <ul tabindex="0" class="dropdown-content menu p-1 shadow-xl bg-base-100 rounded-lg w-20 border border-base-200 z-50">
                <li><a onclick="changeLanguage('th')" class="justify-center font-bold <?php echo (getCurrentLanguage() === 'th') ? 'text-primary bg-primary/5' : ''; ?>">TH</a></li>
                <li><a onclick="changeLanguage('en')" class="justify-center font-bold <?php echo (getCurrentLanguage() === 'en') ? 'text-primary bg-primary/5' : ''; ?>">EN</a></li>
            </ul>
        </div>

        <!-- Theme Toggle Button -->
        <button onclick="toggleTheme()" class="btn btn-ghost btn-circle btn-sm mr-2" title="Toggle Theme">
            <i class="fas fa-circle-half-stroke text-lg text-primary"></i>
        </button>

        <!-- User Profile Dropdown -->
        <div class="dropdown dropdown-hover dropdown-bottom dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost flex items-center gap-2 rounded-xl py-1 px-3 border border-base-200 shadow-sm hover:bg-base-200">
                <?php if (!empty($currentUser['profile_picture'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>"
                        alt="Avatar"
                        class="w-6 h-6 rounded-full object-cover border border-primary">
                <?php else: ?>
                    <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                <?php endif; ?>
                <span class="text-xs font-semibold max-w-[100px] truncate text-base-content/90"><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                <i class="fas fa-chevron-down text-[8px] opacity-60"></i>
            </div>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow-xl bg-base-100 rounded-xl w-52 border border-base-200 z-50">
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/profile.php">
                        <i class="fas fa-user w-5 text-primary"></i><?php echo __('nav_profile'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php">
                        <i class="fas fa-list w-5 text-primary"></i><?php echo __('nav_my_bibliographies'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/projects.php">
                        <i class="fas fa-folder w-5 text-primary"></i><?php echo __('nav_my_projects'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/activity-history.php">
                        <i class="fas fa-history w-5 text-primary"></i><?php echo __('nav_work_history'); ?>
                    </a>
                </li>
                <div class="divider my-1"></div>
                <li>
                    <a onclick="logout()" class="text-error hover:bg-error/10">
                        <i class="fas fa-sign-out-alt w-5"></i><?php echo __('logout'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
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
</script>