<!-- Guest Navigation Bar -->
<div class="navbar bg-base-100/90 backdrop-blur-md sticky top-0 z-50 border-b border-base-300 px-4 py-2 shadow-sm transition-all duration-300">
    <div class="navbar-start">
        <!-- Brand -->
        <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2 px-2 select-none">
            <span class="font-bold text-2xl tracking-wide bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 bg-clip-text text-transparent transition-all duration-300 hover:brightness-110">Babybib</span>
        </a>
    </div>

    <!-- Mobile Navigation Toggle Button -->
    <div class="navbar-end lg:hidden flex gap-2">
        <!-- Language Switcher for Mobile -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-sm font-bold">
                <?php echo strtoupper(getCurrentLanguage()); ?>
                <i class="fas fa-chevron-down text-[10px]"></i>
            </div>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-24 border border-base-200 z-50 mt-1">
                <li><a onclick="changeLanguage('th')" class="<?php echo (getCurrentLanguage() === 'th') ? 'active font-bold' : ''; ?>">TH</a></li>
                <li><a onclick="changeLanguage('en')" class="<?php echo (getCurrentLanguage() === 'en') ? 'active font-bold' : ''; ?>">EN</a></li>
            </ul>
        </div>

        <!-- Mobile Menu Trigger -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <i class="fas fa-bars text-lg"></i>
            </div>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-56 border border-base-200 z-50 mt-1">
                <li>
                    <a href="<?php echo SITE_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home w-5"></i><?php echo __('nav_home'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/start.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'start.php' ? 'active' : ''; ?>">
                        <i class="fas fa-play-circle w-5"></i><?php echo __('nav_start'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/generate.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active' : ''; ?>">
                        <i class="fas fa-wand-magic-sparkles w-5"></i><?php echo __('nav_generate'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-file-lines w-5"></i><?php echo __('nav_report_templates'); ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['last_bib'])): ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/summary.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : ''; ?>">
                            <i class="fas fa-file-invoice w-5"></i><?php echo __('nav_latest_summary'); ?>
                            <span class="badge badge-secondary badge-sm">!</span>
                        </a>
                    </li>
                <?php endif; ?>
                <div class="divider my-1"></div>
                <!-- Help list -->
                <li class="menu-title"><span><?php echo __('nav_help'); ?></span></li>
                <li><a href="<?php echo SITE_URL; ?>/sort.php"><i class="fas fa-sort-alpha-down w-5"></i><?php echo __('nav_sort'); ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/help-author.php"><i class="fas fa-user-pen w-5"></i><?php echo __('help_author'); ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/help-place.php"><i class="fas fa-location-dot w-5"></i><?php echo __('help_place'); ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/help-publisher.php"><i class="fas fa-building w-5"></i><?php echo __('help_publisher'); ?></a></li>
                <div class="divider my-1"></div>
                <li>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-sign-in-alt"></i><?php echo __('nav_signin'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Desktop Navigation Menu -->
    <div class="navbar-end hidden lg:flex items-center gap-1">
        <ul class="menu menu-horizontal px-1 gap-1">
            <li>
                <a href="<?php echo SITE_URL; ?>" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-home text-lg"></i>
                    <span><?php echo __('nav_home'); ?></span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/start.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo basename($_SERVER['PHP_SELF']) === 'start.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-play-circle text-lg"></i>
                    <span><?php echo __('nav_start'); ?></span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/generate.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-wand-magic-sparkles text-lg"></i>
                    <span><?php echo __('nav_generate'); ?></span>
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl <?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active bg-primary/10 text-primary' : 'text-base-content/75 hover:bg-base-200'; ?>">
                    <i class="fas fa-file-lines text-lg"></i>
                    <span><?php echo __('nav_report_templates'); ?></span>
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

            <!-- Help Dropdown -->
            <li class="dropdown dropdown-hover dropdown-bottom">
                <div tabindex="0" role="button" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl text-base-content/75 hover:bg-base-200">
                    <i class="fas fa-circle-question text-lg"></i>
                    <span class="flex items-center gap-1"><?php echo __('nav_help'); ?> <i class="fas fa-chevron-down text-[8px]"></i></span>
                </div>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-xl bg-base-100 rounded-xl w-48 border border-base-200 z-50">
                    <li><a href="<?php echo SITE_URL; ?>/sort.php"><i class="fas fa-sort-alpha-down w-5"></i><?php echo __('nav_sort'); ?></a></li>
                    <div class="divider my-1"></div>
                    <li><a href="<?php echo SITE_URL; ?>/help-author.php"><i class="fas fa-user-pen w-5"></i><?php echo __('help_author'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/help-place.php"><i class="fas fa-location-dot w-5"></i><?php echo __('help_place'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/help-publisher.php"><i class="fas fa-building w-5"></i><?php echo __('help_publisher'); ?></a></li>
                </ul>
            </li>

            <!-- Share Dropdown -->
            <li class="dropdown dropdown-hover dropdown-bottom dropdown-end">
                <div tabindex="0" role="button" class="px-3 py-2 flex flex-col items-center gap-1 text-[11px] font-semibold tracking-wide uppercase rounded-xl text-base-content/75 hover:bg-base-200">
                    <i class="fas fa-share-nodes text-lg"></i>
                    <span class="flex items-center gap-1"><?php echo __('nav_share'); ?> <i class="fas fa-chevron-down text-[8px]"></i></span>
                </div>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-xl bg-base-100 rounded-xl w-48 border border-base-200 z-50">
                    <li>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL); ?>" target="_blank">
                            <i class="fab fa-facebook text-blue-600 w-5"></i> Facebook
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fab fa-instagram text-pink-600 w-5"></i> Instagram
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL); ?>&text=<?php echo urlencode(__('tagline')); ?>" target="_blank">
                            <i class="fab fa-x-twitter text-base-content w-5"></i> X
                        </a>
                    </li>
                    <li>
                        <a href="https://line.me/R/msg/text/?<?php echo urlencode(__('tagline') . ' ' . SITE_URL); ?>" target="_blank">
                            <i class="fab fa-line text-emerald-500 w-5"></i> LINE
                        </a>
                    </li>
                </ul>
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

        <!-- Sign In Button -->
        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-sm text-white px-4 rounded-lg shadow-md transition-all duration-300 hover:scale-[1.03]">
            <i class="fas fa-sign-in-alt"></i>
            <span><?php echo __('nav_signin'); ?></span>
        </a>
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