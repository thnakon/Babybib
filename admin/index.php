<?php

/**
 * Babybib - Admin Dashboard (Premium Redesign)
 * =========================
 */

require_once '../includes/session.php';

$pageTitle = 'แดชบอร์ดผู้ดูแลระบบ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

try {
    $db = getDB();

    // Stats
    $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $totalLis = $db->query("SELECT COUNT(*) FROM users WHERE is_lis_cmu = 1")->fetchColumn();
    $totalBibs = $db->query("SELECT COUNT(*) FROM bibliographies")->fetchColumn();
    $totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $pendingFeedback = $db->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();

    // Recent Users
    $recentUsers = $db->query("
        SELECT *, (SELECT COUNT(*) FROM bibliographies WHERE user_id = users.id) as bib_count 
        FROM users 
        WHERE role = 'user'
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();

    // Recent Bibliographies with usernames
    $recentBibs = $db->query("
        SELECT b.*, u.username, r.name_th as resource_name
        FROM bibliographies b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN resource_types r ON b.resource_type_id = r.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Recent Projects with usernames
    $recentProjectsList = $db->query("
        SELECT p.*, u.username
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Recent Pending Feedback
    $recentFeedback = $db->query("
        SELECT f.*, u.username 
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.id
        WHERE f.status = 'pending'
        ORDER BY f.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Recent System Logs
    $recentLogs = $db->query("
        SELECT l.*, u.username 
        FROM activity_logs l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 6
    ")->fetchAll();

    // LIS Students List
    $lisStudents = $db->query("
        SELECT *, (SELECT COUNT(*) FROM bibliographies WHERE user_id = users.id) as bib_count
        FROM users 
        WHERE role = 'user' AND is_lis_cmu = 1
        ORDER BY created_at DESC 
        LIMIT 8
    ")->fetchAll();

    // Registration trend (very basic logic for the "pulse" feel)
    $todayReg = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND role = 'user'")->fetchColumn();
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>



<div class="admin-dash-container">
    <!-- Premium Welcome Banner -->
    <section class="welcome-banner slide-up">
        <div class="welcome-content">
            <h1><?php echo $currentLang === 'th' ? 'แผงควบคุมอัจฉริยะ' : 'Smart Admin Dashboard'; ?></h1>
            <p><?php echo $currentLang === 'th' ? 'ติดตามความเคลื่อนไหวและสถิติสำคัญของ Babybib ได้ที่นี่' : 'Monitor all key movements and statistics of Babybib here.'; ?></p>
        </div>
        <div class="banner-stats">
            <div class="banner-stat-item">
                <span class="banner-stat-value"><?php echo $todayReg; ?></span>
                <span class="banner-stat-label"><?php echo $currentLang === 'th' ? 'ผู้สมัครวันนี้' : "Today's New Users"; ?></span>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2);"></div>
            <div class="banner-stat-item">
                <span class="banner-stat-value"><?php echo number_format($totalUsers); ?></span>
                <span class="banner-stat-label"><?php echo $currentLang === 'th' ? 'สมาชิกทั้งหมด' : 'Total Members'; ?></span>
            </div>
        </div>
    </section>

    <!-- Refined Stats Grid -->
    <div class="grid-stats">
        <div class="stat-card-premium slide-up stagger-1">
            <div class="stat-icon-box" style="background: rgba(79, 70, 229, 0.1); color: #4F46E5;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-info-box">
                <h4>LIS Members</h4>
                <div class="value"><?php echo number_format($totalLis); ?></div>
            </div>
        </div>

        <div class="stat-card-premium slide-up stagger-2">
            <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-info-box">
                <h4>Bibliographies</h4>
                <div class="value"><?php echo number_format($totalBibs); ?></div>
            </div>
        </div>

        <div class="stat-card-premium slide-up stagger-3">
            <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="fas fa-project-diagram"></i>
            </div>
            <div class="stat-info-box">
                <h4>Active Projects</h4>
                <div class="value"><?php echo number_format($totalProjects); ?></div>
            </div>
        </div>

        <div class="stat-card-premium slide-up stagger-4">
            <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div class="stat-info-box">
                <h4>Pending Feedback</h4>
                <div class="value"><?php echo number_format($pendingFeedback); ?></div>
            </div>
        </div>
    </div>

    <!-- Enhanced Content Layout -->
    <div class="dashboard-layout" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">

        <!-- Recent Bibliographies -->
        <div class="card-modern slide-up stagger-5">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-file-invoice"></i>
                    <?php echo $currentLang === 'th' ? 'บรรณานุกรมล่าสุด' : 'Recent Bibliographies'; ?>
                </h3>
                <a href="bibliographies.php" class="btn btn-ghost btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-modern-body" style="padding: 0;">
                <?php if (empty($recentBibs)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-tertiary);">No data</div>
                <?php else: ?>
                    <?php foreach ($recentBibs as $bib): ?>
                        <div class="log-row">
                            <div class="log-indicator" style="background: var(--primary);"></div>
                            <div class="log-content-box">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">
                                    <?php echo truncateText(strip_tags($bib['bibliography_text']), 60); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-tertiary);">
                                    <i class="far fa-user"></i> @<?php echo htmlspecialchars($bib['username'] ?? 'Guest'); ?> •
                                    <i class="far fa-folder"></i> <?php echo htmlspecialchars($bib['resource_name'] ?? 'Other'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="card-modern slide-up stagger-6">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-project-diagram"></i>
                    <?php echo $currentLang === 'th' ? 'โครงการล่าสุด' : 'Recent Projects'; ?>
                </h3>
                <a href="projects.php" class="btn btn-ghost btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-modern-body" style="padding: 0;">
                <?php if (empty($recentProjectsList)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-tertiary);">No data</div>
                <?php else: ?>
                    <?php foreach ($recentProjectsList as $proj): ?>
                        <div class="log-row">
                            <div class="log-indicator" style="background: var(--success);"></div>
                            <div class="log-content-box">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($proj['name']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-tertiary);">
                                    <i class="far fa-user"></i> @<?php echo htmlspecialchars($proj['username'] ?? 'User'); ?> •
                                    <i class="far fa-calendar-alt"></i> <?php echo formatThaiDate($proj['created_at']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Feedback -->
        <div class="card-modern slide-up stagger-7">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-comment-dots"></i>
                    <?php echo $currentLang === 'th' ? 'ข้อเสนอแนะที่รอดำเนินการ' : 'Pending Feedback'; ?>
                </h3>
                <a href="feedback.php" class="btn btn-ghost btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-modern-body" style="padding: 0;">
                <?php if (empty($recentFeedback)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-tertiary);">No pending feedback</div>
                <?php else: ?>
                    <?php foreach ($recentFeedback as $fb): ?>
                        <div class="log-row">
                            <div class="log-indicator" style="background: var(--warning);"></div>
                            <div class="log-content-box">
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($fb['subject']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-tertiary); margin-bottom: 4px;">
                                    <?php echo truncateText(htmlspecialchars($fb['message']), 50); ?>
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-tertiary); opacity: 0.8;">
                                    <i class="far fa-user"></i> @<?php echo htmlspecialchars($fb['username'] ?? 'Guest'); ?> •
                                    <i class="far fa-clock"></i> <?php echo formatThaiDate($fb['created_at']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="card-modern slide-up stagger-8">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-history"></i>
                    <?php echo $currentLang === 'th' ? 'ประวัติกิจกรรม' : 'Activity Logs'; ?>
                </h3>
            </div>
            <div class="card-modern-body" style="padding: 0;">
                <?php foreach ($recentLogs as $log): ?>
                    <div class="log-row">
                        <div class="log-indicator" style="background: var(--info);"></div>
                        <div class="log-content-box">
                            <div style="font-size: 0.8rem; color: var(--text-primary);">
                                <strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong>
                                <span style="color: var(--text-secondary);"><?php echo ' ' . htmlspecialchars($log['action']); ?></span>
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-tertiary);">
                                <?php echo formatThaiDate($log['created_at']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- LIS Students -->
        <div class="card-modern slide-up stagger-9">
            <div class="card-modern-header">
                <h3 class="card-modern-title">
                    <i class="fas fa-user-graduate"></i>
                    <?php echo $currentLang === 'th' ? 'รายชื่อนักศึกษา LIS' : 'LIS Students'; ?>
                </h3>
                <a href="users.php?lis=1" class="btn btn-ghost btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-modern-body">
                <div class="user-list">
                    <?php if (empty($lisStudents)): ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-tertiary);">No LIS students found</div>
                    <?php else: ?>
                        <?php foreach ($lisStudents as $student): ?>
                            <div class="user-pill-sm">
                                <div class="user-avatar-sm">
                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                </div>
                                <div class="user-details-box">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($student['name'] . ' ' . $student['surname']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-tertiary);">
                                        @<?php echo htmlspecialchars($student['username']); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge-lis"><?php echo $student['bib_count']; ?> Bibs</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer-admin.php'; ?>