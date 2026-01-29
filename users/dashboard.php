<?php

/**
 * Babybib - User Dashboard (Redesigned)
 * =====================================
 */

require_once '../includes/session.php';

// Fetch current user data
$userId = getCurrentUserId();
$currentUser = getCurrentUser();

if (!$currentUser) {
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$pageTitle = 'แดชบอร์ด';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/user-dashboard.css">';
require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$bibCount = countUserBibliographies($userId);
$projectCount = countUserProjects($userId);

// Get recent bibliographies
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT b.*, rt.name_th, rt.name_en, rt.icon, p.name as project_name
        FROM bibliographies b 
        JOIN resource_types rt ON b.resource_type_id = rt.id 
        LEFT JOIN projects p ON b.project_id = p.id
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([$userId]);
    $recentBibs = $stmt->fetchAll();
} catch (Exception $e) {
    $recentBibs = [];
}

// Include announcement toast
require_once '../includes/announcement-toast.php';
?>

<div class="dash-wrapper">

    <!-- Premium Header Section -->
    <header class="dash-header slide-up">
        <div class="dash-header-text">
            <div class="dash-header-icon">
                <i class="fas fa-wand-magic-sparkles"></i>
            </div>
            <div class="dash-header-info">
                <h1><?php echo $currentLang === 'th' ? 'สวัสดี' : 'Hello'; ?>, <?php echo htmlspecialchars($currentUser['name']); ?>! <span class="wave">👋</span></h1>
                <p><?php echo $currentLang === 'th' ? 'ยินดียินดีต้อนรับกลับสู่แผงควบคุมอัจฉริยะของคุณ' : 'Welcome back to your smart dashboard.'; ?></p>
            </div>
        </div>
        <div class="dash-header-actions">
            <a href="<?php echo SITE_URL; ?>/generate.php" class="btn-primary-gradient">
                <i class="fas fa-plus-circle"></i>
                <?php echo __('create_bibliography'); ?>
            </a>
        </div>
    </header>

    <!-- Stats Grid -->
    <div class="dash-stats-grid">
        <div class="dash-stat-card slide-up stagger-1">
            <div class="dash-stat-icon-box" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                <i class="fas fa-list-check"></i>
            </div>
            <div class="dash-stat-text">
                <h3><?php echo number_format($bibCount); ?></h3>
                <p><?php echo __('total_bibliographies'); ?></p>
            </div>
        </div>

        <div class="dash-stat-card slide-up stagger-2">
            <div class="dash-stat-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="dash-stat-text">
                <h3><?php echo number_format($projectCount); ?></h3>
                <p><?php echo __('total_projects'); ?></p>
            </div>
        </div>

        <div class="dash-stat-card slide-up stagger-3">
            <div class="dash-stat-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                <i class="fas fa-battery-half"></i>
            </div>
            <div class="dash-stat-text">
                <h3><?php echo number_format(MAX_BIBLIOGRAPHIES - $bibCount); ?></h3>
                <p><?php echo $currentLang === 'th' ? 'บรรณานุกรมที่สร้างได้อีก' : 'Quota Left'; ?></p>
            </div>
        </div>

        <div class="dash-stat-card slide-up stagger-4">
            <div class="dash-stat-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="fas fa-folder-plus"></i>
            </div>
            <div class="dash-stat-text">
                <h3><?php echo number_format(MAX_PROJECTS - $projectCount); ?></h3>
                <p><?php echo $currentLang === 'th' ? 'โครงการที่สร้างได้อีก' : 'Projects Left'; ?></p>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="dash-content-layout">
        <!-- Left Side: Recent Activity -->
        <div class="dash-main-column slide-up stagger-5">
            <div class="glass-card">
                <div class="card-header-flex">
                    <h2 class="card-title-modern">
                        <i class="fas fa-history"></i>
                        <?php echo __('recent_activity'); ?>
                    </h2>
                    <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="btn-text-sm">
                        <?php echo $currentLang === 'th' ? 'ดูบรรณานุกรมทั้งหมด' : 'View All'; ?>
                        <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>

                <div class="card-body-modern">
                    <?php if (empty($recentBibs)): ?>
                        <div class="empty-state-dash">
                            <div class="empty-illustration">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h3><?php echo __('no_bibliography'); ?></h3>
                            <p><?php echo $currentLang === 'th' ? 'เริ่มสร้างบรรณานุกรมรายการแรกของคุณได้ง่ายๆ เพียงไม่กี่ขั้นตอน' : "Start creating your first bibliography in just a few steps."; ?></p>
                            <a href="<?php echo SITE_URL; ?>/generate.php" class="btn-primary-sm">
                                <i class="fas fa-plus"></i>
                                <?php echo __('create_bibliography'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="dash-recent-list">
                            <?php foreach ($recentBibs as $bib): ?>
                                <div class="recent-item-row">
                                    <div class="item-icon-circle">
                                        <i class="fas <?php echo $bib['icon'] ?? 'fa-file-alt'; ?>"></i>
                                    </div>
                                    <div class="item-details">
                                        <div class="item-text-preview"><?php echo strip_tags($bib['bibliography_text']); ?></div>
                                        <div class="item-meta-tags">
                                            <span class="meta-tag">
                                                <i class="far fa-calendar-check"></i>
                                                <?php echo formatThaiDate($bib['created_at']); ?>
                                            </span>
                                            <?php if (!empty($bib['project_name'])): ?>
                                                <span class="meta-tag project">
                                                    <i class="far fa-folder"></i>
                                                    <?php echo htmlspecialchars($bib['project_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="meta-tag type">
                                                <i class="fas <?php echo ($bib['language'] === 'th' ? 'fa-language' : 'fa-globe'); ?>"></i>
                                                <?php echo $currentLang === 'th' ? $bib['name_th'] : $bib['name_en']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <button class="action-btn-circle" onclick="copyToClipboard('<?php echo addslashes($bib['bibliography_text']); ?>', this)" title="<?php echo __('copy'); ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Sidebars -->
        <div class="dash-side-column">
            <!-- Quick Actions -->
            <div class="glass-card slide-up stagger-6 mb-6">
                <div class="card-header-minimal">
                    <h3 class="minimal-title">
                        <i class="fas fa-bolt"></i>
                        <?php echo __('quick_actions'); ?>
                    </h3>
                </div>
                <div class="card-body-minimal">
                    <div class="quick-action-grid">
                        <a href="<?php echo SITE_URL; ?>/generate.php" class="quick-action-item">
                            <div class="action-icon" style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);">
                                <i class="fas fa-magic"></i>
                            </div>
                            <div class="action-label"><?php echo __('create_bibliography'); ?></div>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/users/projects.php?action=create" class="quick-action-item">
                            <div class="action-icon" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                                <i class="fas fa-folder-plus"></i>
                            </div>
                            <div class="action-label"><?php echo __('create_project'); ?></div>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/users/projects.php" class="quick-action-item">
                            <div class="action-icon" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                                <i class="fas fa-file-export"></i>
                            </div>
                            <div class="action-label"><?php echo $currentLang === 'th' ? 'ส่งออกโครงการ' : 'Export Projects'; ?></div>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/users/profile.php" class="quick-action-item">
                            <div class="action-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div class="action-label"><?php echo __('edit_profile'); ?></div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usage Progress -->
            <div class="glass-card slide-up stagger-7">
                <div class="card-header-minimal">
                    <h3 class="minimal-title">
                        <i class="fas fa-chart-pie"></i>
                        <?php echo $currentLang === 'th' ? 'การใช้งานของคุณ' : 'Your Usage'; ?>
                    </h3>
                </div>
                <div class="card-body-minimal">
                    <div class="usage-stat-box">
                        <div class="usage-header">
                            <span class="usage-label"><?php echo __('bibliography'); ?></span>
                            <span class="usage-value"><?php echo $bibCount; ?> / <?php echo MAX_BIBLIOGRAPHIES; ?></span>
                        </div>
                        <div class="progress-container">
                            <?php $bibPercent = min(100, ($bibCount / MAX_BIBLIOGRAPHIES) * 100); ?>
                            <div class="progress-bar-fill" style="width: <?php echo $bibPercent; ?>%; <?php echo $bibPercent > 90 ? 'background: linear-gradient(90deg, #EF4444 0%, #DC2626 100%);' : ''; ?>"></div>
                        </div>
                    </div>

                    <div class="usage-stat-box mt-5">
                        <div class="usage-header">
                            <span class="usage-label"><?php echo __('projects'); ?></span>
                            <span class="usage-value"><?php echo $projectCount; ?> / <?php echo MAX_PROJECTS; ?></span>
                        </div>
                        <div class="progress-container">
                            <?php $projPercent = min(100, ($projectCount / MAX_PROJECTS) * 100); ?>
                            <div class="progress-bar-fill" style="width: <?php echo $projPercent; ?>%; background: linear-gradient(90deg, #10B981 0%, #059669 100%);"></div>
                        </div>
                    </div>

                    <?php if ($bibCount >= MAX_BIBLIOGRAPHIES || $projectCount >= MAX_PROJECTS): ?>
                        <div class="alert-limit mt-6">
                            <i class="fas fa-circle-exclamation"></i>
                            <p><?php echo __('limit_reached'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once '../includes/footer.php'; ?>