<?php

/**
 * Babybib - Homepage
 * ===================
 * Professional Thai Citation Machine
 */

require_once 'includes/session.php';

$pageTitle = __('nav_home');
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/home.css">';
require_once 'includes/header.php';

// Dynamically include navbar based on login status
if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}

// Include announcement toast
require_once 'includes/announcement-toast.php';
?>


<!-- Hero Section -->
<section class="hero">
    <!-- Floating Decorative Elements -->
    <div class="hero-decorations">
        <i class="fas fa-book decor-1"></i>
        <i class="fas fa-newspaper decor-2"></i>
        <i class="fas fa-graduation-cap decor-3"></i>
        <i class="fas fa-bookmark decor-4"></i>
        <i class="fas fa-quote-right decor-5"></i>
        <i class="fas fa-pen-fancy decor-6"></i>
        <i class="fas fa-file-invoice decor-7"></i>
        <i class="fas fa-scroll decor-8"></i>
        <i class="fas fa-medal decor-9"></i>
        <i class="fas fa-microchip decor-10"></i>
        <i class="fas fa-at decor-11"></i>
        <i class="fas fa-link decor-12"></i>
    </div>
    <div class="container">
        <div class="hero-content slide-up">
            <div class="hero-icon">
                <span>BB</span>
            </div>
            <h1 class="hero-title"><?php echo __('about_hero_title'); ?></h1>
            <p class="hero-description"><?php echo __('about_hero_desc'); ?></p>
            <div class="hero-actions">
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-lg hero-btn-primary">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <?php echo __('about_cta_start'); ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/start.php" class="btn btn-lg hero-btn-secondary">
                    <i class="fas fa-play-circle"></i>
                    <?php echo __('about_cta_learn'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- About Project Section -->
<section class="section-about">
    <div class="container">
        <div class="about-content slide-up">
            <div class="section-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <h2><?php echo __('about_section_title'); ?></h2>
            <p class="about-subtitle"><?php echo __('about_section_subtitle'); ?></p>

            <p class="about-description"><?php echo __('about_section_desc'); ?></p>

            <p class="about-contact"><?php echo __('about_section_contact'); ?>
                <a href="mailto:thanayok@gmail.com">thanayok@gmail.com</a>
            </p>

            <div class="acknowledgements-grid">
                <div class="ack-item">
                    <i class="fas fa-user-graduate"></i>
                    <div>
                        <strong><?php echo __('about_ack_ladda_name'); ?></strong>
                        <span><?php echo __('about_ack_ladda_desc'); ?></span>
                    </div>
                </div>
                <div class="ack-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>
                        <strong><?php echo __('about_ack_angsana_name'); ?></strong>
                        <span><?php echo __('about_ack_angsana_desc'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Resource Types Section -->
<section class="section-resources">
    <div class="container">
        <div class="section-header slide-up">
            <div class="section-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <h2><?php echo __('res_types_title'); ?></h2>
            <p><?php echo __('res_types_desc'); ?></p>
        </div>

        <div class="resource-sliders-wrapper slide-up">
            <?php
            $allTypes = getResourceTypes();
            $categoryColors = [
                'books' => '#7C3AED',
                'journals' => '#0f766e',
                'reference' => '#be123c',
                'newspapers' => '#b45309',
                'reports' => '#15803d',
                'conferences' => '#7e22ce',
                'theses' => '#0e7490',
                'online' => '#0369a1',
                'media' => '#be185d',
                'others' => '#334155',
            ];

            // Split into 3 chunks for 3 rows
            $total = count($allTypes);
            $chunkSize = ceil($total / 3);
            $chunks = array_chunk($allTypes, $chunkSize);

            foreach ($chunks as $rowIndex => $rowTypes):
                // Alternate direction and vary speed
                $direction = ($rowIndex % 2 === 0) ? 'normal' : 'reverse';
                $speed = 120 + ($rowIndex * 40); // Very slow flow: 120s, 160s, 200s
            ?>
                <div class="resource-slider-container">
                    <div class="resource-slider-track" style="animation-direction: <?php echo $direction; ?>; animation-duration: <?php echo $speed; ?>s;">
                        <?php
                        // Duplicate for seamless loop
                        $displayTypes = array_merge($rowTypes, $rowTypes);
                        foreach ($displayTypes as $type):
                            $color = $categoryColors[$type['category']] ?? '#334155';
                        ?>
                            <div class="resource-category-tag" style="--tag-color: <?php echo $color; ?>;">
                                <i class="fas <?php echo $type['icon']; ?>"></i>
                                <span><?php echo $currentLang === 'th' ? $type['name_th'] : $type['name_en']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-cta">
    <div class="container">
        <div class="cta-content slide-up">
            <h2><?php echo __('cta_ready_title'); ?></h2>
            <p><?php echo __('cta_ready_desc'); ?></p>
            <div class="cta-actions">
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-lg cta-btn-primary">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <?php echo __('cta_start_now'); ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-lg cta-btn-secondary">
                    <i class="fas fa-user-plus"></i>
                    <?php echo __('register'); ?>
                </a>
            </div>
        </div>
    </div>
</section>




<?php require_once 'includes/footer.php'; ?>