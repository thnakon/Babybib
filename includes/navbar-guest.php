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

            <?php if (isset($_SESSION['last_bib'])): ?>
                <a href="<?php echo SITE_URL; ?>/summary.php" class="navbar-item <?php echo basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span><?php echo __('nav_latest_summary'); ?></span>
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
                    <!-- Moved Sort here -->
                    <a href="<?php echo SITE_URL; ?>/sort.php" class="dropdown-item">
                        <i class="fas fa-sort-alpha-down"></i>
                        <?php echo __('nav_sort'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
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
                    <a href="javascript:void(0)" onclick="Share.facebook()" class="dropdown-item">
                        <i class="fab fa-facebook" style="color: #1877F2;"></i>
                        Facebook
                    </a>
                    <a href="javascript:void(0)" onclick="Share.line()" class="dropdown-item">
                        <i class="fab fa-line" style="color: #00B900;"></i>
                        LINE
                    </a>
                    <a href="javascript:void(0)" onclick="Share.twitter()" class="dropdown-item">
                        <i class="fab fa-x-twitter" style="color: #000;"></i>
                        X (Twitter)
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="Share.copyLink(undefined, this)" class="dropdown-item">
                        <i class="fas fa-link" style="color: #64748b;"></i>
                        <?php echo __('nav_copy_link', 'Copy Link'); ?>
                    </a>
                </div>
            </div>

            <!-- Sign In Button -->
            <a href="<?php echo SITE_URL; ?>/login.php" class="navbar-item navbar-item-cta">
                <i class="fas fa-sign-in-alt"></i>
                <span><?php echo __('nav_signin'); ?></span>
            </a>

            <!-- Vertical Language Toggle (same as user navbar) -->
            <div class="lang-toggle-vertical">
                <button class="lang-btn <?php echo (getCurrentLanguage() === 'th') ? 'active' : ''; ?>" onclick="changeLanguage('th')" title="ภาษาไทย">TH</button>
                <button class="lang-btn <?php echo (getCurrentLanguage() === 'en') ? 'active' : ''; ?>" onclick="changeLanguage('en')" title="English">EN</button>
            </div>
        </div>
    </div>
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

    function toggleDropdown(id) {
        // Close all other dropdowns
        document.querySelectorAll('.dropdown').forEach(dd => {
            if (dd.id !== id) dd.classList.remove('open');
        });
        document.getElementById(id).classList.toggle('open');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(dd => dd.classList.remove('open'));
        }
    });
</script>