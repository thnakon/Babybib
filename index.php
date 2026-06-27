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
<section class="hero flex flex-col justify-start pt-16 pb-16">
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
    
    <div class="container mx-auto px-4 z-10">
        <div class="hero-content max-w-4xl mx-auto text-center flex flex-col items-center gap-6">
            <!-- Early Adopter / Feature Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 text-white text-xs font-semibold border border-white/20 shadow-sm animate-pulse">
                <span>✨ <?php echo $currentLang === 'th' ? 'เครื่องมือสร้างบรรณานุกรมรูปแบบ APA 7th Edition ฟรี 100%' : '100% Free APA 7th Edition Citation Generator'; ?></span>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-white leading-tight">
                <?php if ($currentLang === 'th'): ?>
                    สร้างบรรณานุกรมอ้างอิง <br>
                    <span class="relative inline-block px-4 py-1 mt-2 rounded-2xl bg-amber-200 text-neutral-800 font-black shadow-sm transform -rotate-1">
                        ได้ง่ายและถูกต้อง
                    </span>
                <?php else: ?>
                    Create academic citations <br>
                    <span class="relative inline-block px-4 py-1 mt-2 rounded-2xl bg-amber-200 text-neutral-800 font-black shadow-sm transform -rotate-1">
                        easily and correctly
                    </span>
                <?php endif; ?>
            </h1>

            <!-- Description -->
            <p class="text-lg md:text-xl text-white/90 max-w-2xl leading-relaxed mt-2">
                <?php echo __('about_hero_desc'); ?>
            </p>

            <!-- Actions -->
            <div class="flex flex-row gap-4 justify-center items-center w-full mt-4">
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn bg-violet-600 hover:bg-violet-700 text-white border-none btn-xs sm:btn-sm md:btn-md lg:btn-md rounded-full px-6 shadow-lg shadow-violet-950/20 hover:scale-[1.03] transition-transform duration-200">
                    <i class="fas fa-wand-magic-sparkles mr-1"></i>
                    <?php echo __('about_cta_start'); ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/start.php" class="btn bg-white/20 hover:bg-white/30 text-white border-none btn-xs sm:btn-sm md:btn-md lg:btn-md rounded-full px-6 hover:scale-[1.03] transition-transform duration-200">
                    <i class="fas fa-circle-play mr-1"></i>
                    <?php echo __('nav_start'); ?>
                </a>
            </div>

            <!-- Premium Mockup Browser Dashboard -->
            <div class="w-full max-w-4xl mt-8 bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden relative z-20 -mb-36 md:-mb-44 text-slate-800 dark:text-zinc-100">
                <!-- Browser Window Top Bar -->
                <div class="flex items-center justify-between px-4 py-3 bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-200 dark:border-zinc-800">
                    <!-- Dots -->
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                    </div>
                    <!-- Address Bar -->
                    <div class="bg-white dark:bg-zinc-900 text-xs px-8 py-1 rounded-md text-slate-500 dark:text-zinc-400 border border-slate-200 dark:border-zinc-800 font-mono hidden sm:block">
                        babybib.com/generate
                    </div>
                    <!-- Empty box for balance -->
                    <div class="w-12"></div>
                </div>

                <!-- Mockup Content Panel -->
                <div class="grid grid-cols-1 md:grid-cols-[200px_1fr] bg-white dark:bg-zinc-950 text-left text-sm min-h-[350px]">
                    <!-- Sidebar Mockup -->
                    <div class="bg-slate-50 dark:bg-zinc-900/50 border-r border-slate-200 dark:border-zinc-800 p-4 hidden md:flex flex-col gap-2">
                        <div class="h-6 w-24 bg-primary/20 rounded-md mb-4"></div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-violet-500/10 text-violet-600 dark:text-violet-400 font-semibold rounded-lg">
                            <i class="fas fa-wand-magic-sparkles"></i> <span><?php echo __('nav_generate'); ?></span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-zinc-400 rounded-lg">
                            <i class="fas fa-list"></i> <span><?php echo __('nav_bibliography_list'); ?></span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-zinc-400 rounded-lg">
                            <i class="fas fa-folder"></i> <span><?php echo __('nav_projects'); ?></span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-zinc-400 rounded-lg">
                            <i class="fas fa-file-lines"></i> <span><?php echo __('nav_report_templates'); ?></span>
                        </div>
                    </div>

                    <!-- Main Panel Mockup -->
                    <div class="p-6 flex flex-col gap-6 bg-white dark:bg-zinc-950">
                        <!-- Top header -->
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 dark:text-zinc-100"><?php echo __('bibliography_preview'); ?></h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">APA 7th Edition Standard</p>
                            </div>
                            <span class="badge badge-success text-white text-xs font-bold gap-1">
                                <i class="fas fa-circle-check"></i> <?php echo __('form_complete'); ?>
                            </span>
                        </div>

                        <!-- Sample Citation Output -->
                        <div class="bg-violet-50/50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-800/30 rounded-xl p-5 relative overflow-hidden group">
                            <!-- Copy overlay hint -->
                            <div class="absolute top-2 right-2 flex gap-2">
                                <span class="badge badge-primary text-xs font-bold shadow-md shadow-primary/10">APA 7th</span>
                                <button class="btn btn-sm btn-circle btn-ghost text-primary hover:bg-primary/10">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <!-- Real formatted reference -->
                            <div class="font-serif text-base text-slate-800 dark:text-slate-200 pl-6 -indent-6 pr-12 leading-relaxed">
                                ลัดดา รุ่งวิสัย. (2566). <span class="italic font-bold text-violet-700 dark:text-violet-300">การพัฒนาเครื่องมือช่วยสร้างบรรณานุกรมภาษาไทย (Thai Citation Machine)</span>. สำนักพิมพ์มหาวิทยาลัยเชียงใหม่.
                            </div>
                            <div class="mt-4 flex gap-4 text-xs text-slate-500 dark:text-zinc-400 border-t border-slate-200 dark:border-zinc-800 pt-3">
                                <span><i class="fas fa-tag mr-1 text-violet-600 dark:text-violet-400"></i> <?php echo $currentLang === 'th' ? 'หนังสือ' : 'Book'; ?></span>
                                <span><i class="fas fa-globe mr-1 text-violet-600 dark:text-violet-400"></i> <?php echo $currentLang === 'th' ? 'ภาษาไทย' : 'Thai'; ?></span>
                            </div>
                        </div>

                        <!-- Mini mockup inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">ประเภททรัพยากร</span>
                                <div class="bg-slate-50 dark:bg-zinc-900 px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-800 text-xs font-medium text-slate-800 dark:text-zinc-200 flex items-center justify-between">
                                    <span><?php echo $currentLang === 'th' ? 'หนังสือ (Book)' : 'Book'; ?></span>
                                    <i class="fas fa-chevron-down text-slate-400 dark:text-zinc-500 text-[10px]"></i>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">ผู้แต่ง / บรรณาธิการ</span>
                                <div class="bg-slate-50 dark:bg-zinc-900 px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-800 text-xs text-slate-800 dark:text-zinc-200">
                                    ลัดดา รุ่งวิสัย
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Project Section -->
<section class="section-about pt-48 md:pt-56">
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
                'journals' => '#87F527',
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