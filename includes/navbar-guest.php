<!-- Guest Navigation Bar -->
<nav class="navbar">
    <div class="navbar-container">
        <!-- Brand -->
        <a href="<?php echo SITE_URL; ?>" class="navbar-brand">
            <i class="fas fa-book-open" style="color: var(--primary);"></i>
            <span class="navbar-brand-text">Babybib</span>
        </a>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Main Menu -->
        <div class="navbar-menu" id="navbar-menu">
            <a href="<?php echo SITE_URL; ?>" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span><?php echo __('nav_about'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/start.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'start.php' ? 'active' : ''; ?>">
                <i class="fas fa-play-circle"></i>
                <span><?php echo __('nav_start'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/generate.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'generate.php' ? 'active' : ''; ?>">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span><?php echo __('nav_generate'); ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/sort.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'sort.php' ? 'active' : ''; ?>">
                <i class="fas fa-sort-alpha-down"></i>
                <span><?php echo __('nav_sort'); ?></span>
            </a>
            <?php if (isset($_SESSION['last_bib'])): ?>
                <a href="<?php echo SITE_URL; ?>/summary.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span><?php echo $currentLang === 'th' ? 'สรุปล่าสุด' : 'Latest Summary'; ?></span>
                </a>
            <?php endif; ?>


            <!-- Help Dropdown -->
            <div class="dropdown" id="help-dropdown">
                <button class="navbar-item dropdown-toggle" onclick="toggleDropdown('help-dropdown')">
                    <i class="fas fa-circle-question"></i>
                    <span><?php echo __('nav_help'); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/help-author.php" class="dropdown-item">
                        <i class="fas fa-user-pen"></i>
                        <?php echo __('help_author'); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/help-place.php" class="dropdown-item">
                        <i class="fas fa-location-dot"></i>
                        <?php echo __('help_place'); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/help-publisher.php" class="dropdown-item">
                        <i class="fas fa-building"></i>
                        <?php echo __('help_publisher'); ?>
                    </a>
                </div>
            </div>

            <!-- Share Dropdown -->
            <div class="dropdown" id="share-dropdown">
                <button class="navbar-item dropdown-toggle" onclick="toggleDropdown('share-dropdown')">
                    <i class="fas fa-share-nodes"></i>
                    <span><?php echo __('nav_share'); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="dropdown-item">
                        <i class="fab fa-facebook" style="color: #1877F2;"></i>
                        Facebook
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fab fa-instagram" style="color: #E4405F;"></i>
                        Instagram
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL); ?>&text=<?php echo urlencode(__('tagline')); ?>" target="_blank" class="dropdown-item">
                        <i class="fab fa-x-twitter" style="color: #000;"></i>
                        X
                    </a>
                    <a href="https://line.me/R/msg/text/?<?php echo urlencode(__('tagline') . ' ' . SITE_URL); ?>" target="_blank" class="dropdown-item">
                        <i class="fab fa-line" style="color: #00B900;"></i>
                        LINE
                    </a>
                </div>
            </div>

            <!-- Sign In Button -->
            <a href="<?php echo SITE_URL; ?>/login.php" class="navbar-item">
                <i class="fas fa-sign-in-alt"></i>
                <span><?php echo __('nav_signin'); ?></span>
            </a>

            <!-- Vertical Language Toggle (same as user navbar) -->
            <div class="lang-toggle-vertical">
                <button class="lang-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')" title="ภาษาไทย">TH</button>
                <button class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')" title="English">EN</button>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        document.getElementById('navbar-menu').classList.toggle('open');
        document.getElementById('navbar-actions').classList.toggle('open');
    }

    function toggleDropdown(id) {
        // Close all other dropdowns
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

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(dd => dd.classList.remove('open'));
        }
    });
</script>