<!-- Guest Navigation Bar -->
<nav class="navbar">
    <div class="navbar-container">
        <!-- New Mobile Overlay -->
        <div id="mobile-nav-overlay" onclick="toggleMobileMenu()"></div>

        <!-- Brand -->
        <a href="<?php echo SITE_URL; ?>" class="navbar-brand">
            <span class="navbar-brand-text comfortaa-1">Babybib</span>
        </a>

        <!-- Mobile Menu Button (Animated) -->
        <button class="mobile-menu-btn" id="mobile-menu-btn" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Main Menu -->
        <div class="navbar-menu" id="navbar-menu">
            <a href="<?php echo SITE_URL; ?>" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span><?php echo __('nav_home'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/start.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'start.php' ? 'active' : ''; ?>">
                <i class="fas fa-play-circle"></i>
                <span><?php echo __('nav_start'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/generate.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active' : ''; ?>">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span><?php echo __('nav_generate'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="navbar-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['report-template.php', 'report-builder.php']) ? 'active' : ''; ?>">
                <i class="fas fa-file-lines"></i>
                <span><?php echo __('nav_report_templates'); ?></span>
                
            </a>

            <?php if (isset($_SESSION['last_bib'])): ?>
                <a href="<?php echo SITE_URL; ?>/summary.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span><?php echo __('nav_latest_summary'); ?></span>
                </a>
            <?php endif; ?>


            <!-- Help Dropdown -->
            <details class="dropdown" id="help-dropdown">
                <summary class="navbar-item dropdown-toggle flex items-center gap-1 cursor-pointer list-none bg-transparent border-none">
                    <i class="fas fa-circle-question"></i>
                    <span><?php echo __('nav_help'); ?></span>
                    <i class="fas fa-chevron-down text-[10px] ml-1"></i>
                </summary>
                <ul class="dropdown-content menu p-2 shadow-lg bg-base-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-box w-48 z-[100] mt-1 text-base-content">
                    <li>
                        <a href="<?php echo SITE_URL; ?>/sort.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-sort-alpha-down text-primary dark:text-violet-400"></i>
                            <span><?php echo __('nav_sort'); ?></span>
                        </a>
                    </li>
                    <div class="h-[1px] bg-slate-200 dark:bg-zinc-700 my-1"></div>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/help-author.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-user-pen text-primary dark:text-violet-400"></i>
                            <span><?php echo __('help_author'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/help-place.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-location-dot text-primary dark:text-violet-400"></i>
                            <span><?php echo __('help_place'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/help-publisher.php" class="flex items-center gap-2 py-2">
                            <i class="fas fa-building text-primary dark:text-violet-400"></i>
                            <span><?php echo __('help_publisher'); ?></span>
                        </a>
                    </li>
                </ul>
            </details>

            <!-- Share Dropdown -->
            <details class="dropdown" id="share-dropdown">
                <summary class="navbar-item dropdown-toggle flex items-center gap-1 cursor-pointer list-none bg-transparent border-none">
                    <i class="fas fa-share-nodes"></i>
                    <span><?php echo __('nav_share'); ?></span>
                    <i class="fas fa-chevron-down text-[10px] ml-1"></i>
                </summary>
                <ul class="dropdown-content menu p-2 shadow-lg bg-base-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-box w-48 z-[100] mt-1 text-base-content">
                    <li>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="flex items-center gap-2 py-2">
                            <i class="fab fa-facebook text-[#1877F2]"></i>
                            <span>Facebook</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-2 py-2">
                            <i class="fab fa-instagram text-[#E4405F]"></i>
                            <span>Instagram</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL); ?>&text=<?php echo urlencode(__('tagline')); ?>" target="_blank" class="flex items-center gap-2 py-2">
                            <i class="fab fa-x-twitter text-base-content"></i>
                            <span>X</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://line.me/R/msg/text/?<?php echo urlencode(__('tagline') . ' ' . SITE_URL); ?>" target="_blank" class="flex items-center gap-2 py-2">
                            <i class="fab fa-line text-[#00B900]"></i>
                            <span>LINE</span>
                        </a>
                    </li>
                </ul>
            </details>

            <!-- Sign In Button -->
            <a href="<?php echo SITE_URL; ?>/login.php" class="navbar-item navbar-item-cta">
                <i class="fas fa-sign-in-alt"></i>
                <span><?php echo __('nav_signin'); ?></span>
            </a>

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
</nav>

<style>
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
</style>

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