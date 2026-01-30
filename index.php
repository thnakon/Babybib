<?php

/**
 * Babybib - Homepage
 * ===================
 * Professional Thai Citation Machine
 */

require_once 'includes/session.php';

$pageTitle = 'หน้าแรก';
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
            <h2><?php echo $currentLang === 'th' ? 'เครื่องมือช่วยสร้างบรรณานุกรมภาษาไทย' : 'Thai Citation Machine / Thai Bibliography Generator'; ?></h2>
            <p class="about-subtitle">(Thai Citation Machine / Thai Bibliography Generator)</p>

            <p class="about-description"><?php echo $currentLang === 'th'
                                                ? 'เว็บไซต์นี้จัดทำขึ้นเป็นส่วนหนึ่งของงานวิจัย การพัฒนาเครื่องมือช่วยสร้างบรรณานุกรมภาษาไทย (Thai Citation Machine / Thai Bibliography Generator) ภายใต้การสนับสนุนงบประมาณโดยคณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่ โดยงานวิจัยชิ้นนี้มีวัตถุประสงค์เพื่อพัฒนาเครื่องมือช่วยสร้างบรรณานุกรมภาษาไทย สนับสนุนการศึกษาค้นคว้าของนักเรียนและนักศึกษาในประเทศไทย โดยใช้วิธีการลงบรรณานุกรมแบบ APA 7<sup>th</sup> Edition'
                                                : 'This website was developed as part of a research project to create the Thai Citation Machine / Thai Bibliography Generator, funded by the Faculty of Humanities, Chiang Mai University. The research aims to develop tools for creating Thai bibliographies, supporting research activities of students in Thailand using the APA 7<sup>th</sup> Edition format.'; ?></p>

            <p class="about-contact"><?php echo $currentLang === 'th'
                                            ? 'ปัจจุบันอยู่ระหว่างการทดสอบ หากพบข้อผิดพลาดสามารถให้ข้อแนะนำหรือข้อเสนอแนะได้ที่'
                                            : 'Currently in testing phase. If you find any errors, please send suggestions to'; ?>
                <a href="mailto:thanayok@gmail.com">thanayok@gmail.com</a>
            </p>

            <div class="acknowledgements-grid">
                <div class="ack-item">
                    <i class="fas fa-user-graduate"></i>
                    <div>
                        <strong><?php echo $currentLang === 'th' ? 'ผศ.ลัดดา รุ่งวิสัย' : 'Asst. Prof. Ladda Rungvisai'; ?></strong>
                        <span><?php echo $currentLang === 'th' ? 'สำหรับข้อมูลการลงบรรณานุกรมแบบ APA ภาษาไทย' : 'For Thai APA bibliography data'; ?></span>
                    </div>
                </div>
                <div class="ack-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>
                        <strong><?php echo $currentLang === 'th' ? 'รศ.อังสนา ธงไชย' : 'Assoc. Prof. Angsana Thongchai'; ?></strong>
                        <span><?php echo $currentLang === 'th' ? 'ผู้ให้คำปรึกษาและตรวจสอบระบบ' : 'Advisor and system reviewer'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-features">
    <div class="container">
        <div class="section-header slide-up">
            <h2><?php echo $currentLang === 'th' ? 'ทำไมต้อง Babybib?' : 'Why Babybib?'; ?></h2>
            <p><?php echo $currentLang === 'th' ? 'สร้างบรรณานุกรมที่ถูกต้องได้ง่ายๆ ในไม่กี่คลิก' : 'Create accurate bibliographies in just a few clicks'; ?></p>
        </div>

        <div class="features-grid">
            <div class="feature-item slide-up stagger-1">
                <div class="feature-icon-box" style="--accent: #8b5cf6;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'ถูกต้องตามมาตรฐาน APA 7<sup>th</sup>' : 'APA 7<sup>th</sup> Compliant'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'บรรณานุกรมทุกรายการสร้างตามมาตรฐาน APA รุ่นที่ 7 ล่าสุด' : 'Every bibliography entry follows the latest APA 7<sup>th</sup> edition standards'; ?></p>
            </div>

            <div class="feature-item slide-up stagger-2">
                <div class="feature-icon-box" style="--accent: #D946EF;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'รองรับ 30+ ประเภททรัพยากร' : '30+ Resource Types'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'หนังสือ วารสาร เว็บไซต์ วิทยานิพนธ์ YouTube และอื่นๆ อีกมากมาย' : 'Books, journals, websites, theses, YouTube, and many more'; ?></p>
            </div>

            <div class="feature-item slide-up stagger-3">
                <div class="feature-icon-box" style="--accent: #f59e0b;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'ดูตัวอย่างแบบเรียลไทม์' : 'Real-time Preview'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'ดูผลลัพธ์บรรณานุกรมขณะพิมพ์ ไม่ต้องรอ' : 'See your bibliography result as you type, no waiting needed'; ?></p>
            </div>

            <div class="feature-item slide-up stagger-4">
                <div class="feature-icon-box" style="--accent: #10b981;">
                    <i class="fas fa-language"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'รองรับทั้งไทยและอังกฤษ' : 'Thai & English Support'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'สร้างบรรณานุกรมได้ทั้งภาษาไทยและภาษาอังกฤษ' : 'Create bibliographies in both Thai and English languages'; ?></p>
            </div>

            <div class="feature-item slide-up stagger-5">
                <div class="feature-icon-box" style="--accent: #ec4899;">
                    <i class="fas fa-file-export"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'ส่งออก Word / PDF' : 'Export to Word / PDF'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'ส่งออกรายการบรรณานุกรมเป็นไฟล์ Word หรือ PDF พร้อมใช้งาน' : 'Export your bibliography list to Word or PDF files, ready to use'; ?></p>
            </div>

            <div class="feature-item slide-up stagger-6">
                <div class="feature-icon-box" style="--accent: #6366f1;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3><?php echo $currentLang === 'th' ? 'จัดการโครงการ' : 'Project Management'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'จัดกลุ่มบรรณานุกรมตามโครงการ เพื่อการจัดการที่ง่ายขึ้น' : 'Organize bibliographies by project for easier management'; ?></p>
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
            <h2><?php echo $currentLang === 'th' ? 'รองรับทรัพยากรมากกว่า 30 ประเภท' : 'Over 30 Resource Types Supported'; ?></h2>
            <p><?php echo $currentLang === 'th' ? 'ครอบคลุมทุกประเภททรัพยากรที่คุณต้องการอ้างอิง' : 'Covering all the resource types you need to cite'; ?></p>
        </div>

        <div class="resource-categories slide-up">
            <?php
            $categories = getResourceCategories();
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
            foreach ($categories as $key => $cat):
                $color = $categoryColors[$key] ?? '#334155';
            ?>
                <div class="resource-category-tag" style="--tag-color: <?php echo $color; ?>;">
                    <i class="fas <?php echo $cat['icon']; ?>"></i>
                    <span><?php echo $currentLang === 'th' ? $cat['name_th'] : $cat['name_en']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="popular-resources slide-up">
            <h4><?php echo $currentLang === 'th' ? 'ประเภทยอดนิยม' : 'Most Popular'; ?></h4>
            <div class="popular-resources-grid">
                <?php
                $popularTypes = [
                    ['icon' => 'fa-book', 'name_th' => 'หนังสือ', 'name_en' => 'Book', 'color' => '#7C3AED'],
                    ['icon' => 'fa-newspaper', 'name_th' => 'บทความวารสาร', 'name_en' => 'Journal Article', 'color' => '#0f766e'],
                    ['icon' => 'fa-globe', 'name_th' => 'เว็บเพจ', 'name_en' => 'Web Page', 'color' => '#0369a1'],
                    ['icon' => 'fa-graduation-cap', 'name_th' => 'วิทยานิพนธ์', 'name_en' => 'Thesis', 'color' => '#0e7490'],
                    ['icon' => 'fa-youtube', 'name_th' => 'วิดีโอ YouTube', 'name_en' => 'YouTube Video', 'color' => '#dc2626'],
                    ['icon' => 'fa-robot', 'name_th' => 'AI', 'name_en' => 'AI Content', 'color' => '#7e22ce'],
                ];
                foreach ($popularTypes as $type):
                ?>
                    <a href="<?php echo SITE_URL; ?>/generate.php" class="popular-resource-item" style="--item-color: <?php echo $type['color']; ?>;">
                        <i class="fas <?php echo $type['icon']; ?>"></i>
                        <span><?php echo $currentLang === 'th' ? $type['name_th'] : $type['name_en']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center slide-up">
            <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-primary btn-lg">
                <i class="fas fa-wand-magic-sparkles"></i>
                <?php echo $currentLang === 'th' ? 'เริ่มสร้างบรรณานุกรม' : 'Start Creating Bibliography'; ?>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-cta">
    <div class="container">
        <div class="cta-content slide-up">
            <h2><?php echo $currentLang === 'th' ? 'พร้อมสร้างบรรณานุกรมแล้วหรือยัง?' : 'Ready to Create Your Bibliography?'; ?></h2>
            <p><?php echo $currentLang === 'th' ? 'เริ่มต้นสร้างบรรณานุกรมที่ถูกต้องตามมาตรฐาน APA 7<sup>th</sup> ได้ฟรี' : 'Start creating APA 7<sup>th</sup> compliant bibliographies for free'; ?></p>
            <div class="cta-actions">
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-lg cta-btn-primary">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <?php echo $currentLang === 'th' ? 'เริ่มสร้างเลย' : 'Start Now'; ?>
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