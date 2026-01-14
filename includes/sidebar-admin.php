<!-- Admin Sidebar -->
<?php
if (!isAdmin()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
    }

    .admin-layout {
        display: flex;
        min-height: 100vh;
        background: var(--gray-50);
    }

    .admin-sidebar {
        width: var(--sidebar-width);
        background: #1e1e2d;
        color: var(--white);
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    }

    .admin-sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .admin-sidebar-header {
        padding: var(--space-6) var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-3);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .admin-sidebar-brand {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--white);
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .admin-sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: var(--space-4) 0;
    }

    .admin-nav-section {
        padding: var(--space-2) var(--space-5);
        font-size: var(--text-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        margin-top: var(--space-4);
    }

    .admin-nav-item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-5);
        color: var(--gray-400);
        text-decoration: none;
        transition: all 0.2s;
        border-left: 3px solid transparent;
        font-size: 14px;
        font-weight: 500;
    }

    .admin-nav-item:hover {
        background: rgba(255, 255, 255, 0.03);
        color: var(--white);
    }

    .admin-nav-item.active {
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        border-left-color: var(--primary);
        font-weight: 600;
    }

    .admin-nav-item i {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }

    .admin-sidebar-footer {
        padding: var(--space-4) 0;
        border-top: 1px solid var(--gray-700);
        margin-top: auto;
    }

    .admin-sidebar-footer .admin-nav-item {
        margin-bottom: 0;
    }

    .admin-sidebar-footer .logout-item {
        color: var(--gray-400);
    }

    .admin-sidebar-footer .logout-item:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .admin-sidebar-footer .logout-item:hover i {
        color: #ef4444;
    }

    /* Admin Main Content */
    .admin-main {
        flex: 1;
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .admin-main.expanded {
        margin-left: var(--sidebar-collapsed-width);
    }

    .admin-header {
        height: 70px;
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 var(--space-8);
        border-bottom: 1px solid var(--gray-200);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .admin-search {
        position: relative;
        width: 300px;
    }

    .admin-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .admin-search input {
        width: 100%;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        padding: 8px 12px 8px 36px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .admin-search input:focus {
        background: white;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .admin-content {
        padding: var(--space-8);
        flex: 1;
    }

    /* Admin Profile Dropdown */
    .admin-user-info-dropdown {
        position: relative;
    }

    .admin-user-info {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: 6px 8px 6px 16px;
        background: transparent;
        border-radius: 30px;
        border: 1px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }

    .admin-user-info:hover {
        background: var(--gray-50);
        border-color: var(--gray-200);
    }

    .admin-user-avatar {
        width: 36px;
        height: 36px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .admin-user-details {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .admin-user-name {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 13px;
        line-height: 1.2;
    }

    .admin-user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        color: #92400E;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #FCD34D;
        margin-top: 2px;
    }

    .admin-user-role-badge i {
        font-size: 8px;
    }

    .admin-user-menu {
        position: absolute;
        top: 100%;
        right: 0;
        width: 200px;
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--gray-100);
        margin-top: 10px;
        padding: 8px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
    }

    .admin-user-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .admin-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s;
    }

    .admin-menu-item:hover {
        background: var(--gray-50);
        color: var(--primary);
    }

    .admin-menu-item.danger:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .admin-menu-divider {
        height: 1px;
        background: var(--gray-100);
        margin: 6px 0;
    }

    /* Notifications Dropdown */
    .admin-notif-dropdown {
        position: relative;
    }

    .admin-notif-bell {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--gray-50);
        border: none;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .admin-notif-bell:hover {
        background: var(--gray-100);
        color: var(--primary);
    }

    .notif-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        min-width: 18px;
        height: 18px;
        background: #ef4444;
        color: white;
        border-radius: 10px;
        border: 2px solid white;
        font-size: 10px;
        font-weight: 800;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }

    .notif-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        width: 320px;
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--gray-100);
        margin-top: 15px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.2s;
        z-index: 1000;
        overflow: hidden;
    }

    .notif-dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .notif-dropdown-header {
        padding: 16px;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notif-dropdown-header span {
        font-weight: 700;
        font-size: 14px;
        color: var(--text-primary);
    }

    .notif-dropdown-header a {
        font-size: 12px;
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }

    .notif-dropdown-body {
        max-height: 400px;
        overflow-y: auto;
    }

    .notif-dropdown-item {
        padding: 16px;
        border-bottom: 1px solid var(--gray-50);
        display: flex;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .notif-dropdown-item:hover {
        background: var(--gray-50);
    }

    .notif-dropdown-item.unread {
        background: rgba(139, 92, 246, 0.03);
    }

    .notif-dropdown-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .notif-dropdown-icon.feedback {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .notif-dropdown-icon.user {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .notif-dropdown-icon.system {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .notif-dropdown-content {
        flex: 1;
        min-width: 0;
    }

    .notif-dropdown-title {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 2px;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notif-dropdown-time {
        font-size: 11px;
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .notif-dropdown-empty {
        padding: 40px 20px;
        text-align: center;
        color: var(--gray-400);
    }

    .notif-dropdown-empty i {
        font-size: 2rem;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    /* Custom Scrollbar for Dropdown */
    .notif-dropdown-body::-webkit-scrollbar {
        width: 6px;
    }

    .notif-dropdown-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .notif-dropdown-body::-webkit-scrollbar-thumb {
        background: var(--gray-200);
        border-radius: 10px;
    }

    .notif-dropdown-body::-webkit-scrollbar-thumb:hover {
        background: var(--gray-300);
    }

    @media (max-width: 992px) {
        .admin-sidebar {
            left: -100%;
        }

        .admin-sidebar.active {
            left: 0;
        }

        .admin-main {
            margin-left: 0 !important;
        }

        #sidebar-toggle {
            display: flex !important;
        }
    }
</style>

<div class="admin-layout">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="admin-sidebar-brand">
                <i class="fas fa-book-open" style="color: var(--primary);"></i>
                Babybib
            </a>
        </div>

        <nav class="admin-sidebar-nav">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="admin-nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                <span><?php echo __('dashboard'); ?></span>
            </a>

            <div class="admin-nav-section">Management</div>

            <a href="<?php echo SITE_URL; ?>/admin/users.php" class="admin-nav-item <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span><?php echo __('user_management'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/bibliographies.php" class="admin-nav-item <?php echo $currentPage === 'bibliographies.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span><?php echo __('bibliography_management'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/projects.php" class="admin-nav-item <?php echo $currentPage === 'projects.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span><?php echo __('project_management'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/feedback.php" class="admin-nav-item <?php echo $currentPage === 'feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-comment-dots"></i>
                <span><?php echo __('feedback_management'); ?></span>
            </a>

            <div class="admin-nav-section">System</div>

            <a href="<?php echo SITE_URL; ?>/admin/logs.php" class="admin-nav-item <?php echo $currentPage === 'logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span><?php echo __('activity_logs'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/announcements.php" class="admin-nav-item <?php echo $currentPage === 'announcements.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i>
                <span><?php echo __('announcements'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-nav-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span><?php echo __('system_settings'); ?></span>
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/backups.php" class="admin-nav-item <?php echo $currentPage === 'backups.php' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i>
                <span><?php echo $currentLang === 'th' ? 'สำรองข้อมูล' : 'Backups'; ?></span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="#" onclick="showFeedbackModal(); return false;" class="admin-nav-item">
                <i class="fas fa-paper-plane"></i>
                <span><?php echo __('send_feedback'); ?></span>
            </a>
            <a href="#" onclick="adminLogout(); return false;" class="admin-nav-item logout-item">
                <i class="fas fa-sign-out-alt"></i>
                <span><?php echo __('logout'); ?></span>
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="btn btn-ghost" onclick="toggleAdminSidebar()" style="display: none;" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Search Bar (Optional, visual only for now) -->
                <div class="admin-search hidden md:block">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาเมนูหรือข้อมูล...' : 'Search...'; ?>">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Language Toggle -->
                <div class="lang-toggle">
                    <button class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')">TH</button>
                    <button class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">EN</button>
                </div>

                <div style="width: 1px; height: 24px; background: var(--gray-200);"></div>

                <!-- Notification Bell with Dropdown -->
                <div class="admin-notif-dropdown" id="notif-dropdown">
                    <button class="admin-notif-bell" onclick="toggleNotifDropdown(event)" title="<?php echo __('notifications'); ?>">
                        <i class="fas fa-bell"></i>
                        <span class="notif-badge" id="notif-badge" style="display: none;">0</span>
                    </button>

                    <div class="notif-dropdown-menu" id="notif-dropdown-menu">
                        <div class="notif-dropdown-header">
                            <span><?php echo __('notifications'); ?></span>
                            <a href="<?php echo SITE_URL; ?>/admin/notifications.php"><?php echo $currentLang === 'th' ? 'ดูทั้งหมด' : 'View All'; ?></a>
                        </div>
                        <div class="notif-dropdown-body" id="notif-dropdown-body">
                            <div class="notif-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="width: 1px; height: 24px; background: var(--gray-200);"></div>

                <!-- User Profile Dropdown -->
                <div class="admin-user-info-dropdown" onclick="this.querySelector('.admin-user-menu').classList.toggle('active')">
                    <div class="admin-user-info">
                        <div class="admin-user-details">
                            <div class="admin-user-name"><?php echo htmlspecialchars($currentUser['name'] . ' ' . ($currentUser['surname'] ?? '')); ?></div>
                            <div class="admin-user-role-badge">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo $currentLang === 'th' ? 'ผู้ดูแลระบบ' : 'Administrator'; ?>
                            </div>
                        </div>
                        <div class="admin-user-avatar">
                            <?php if (!empty($currentUser['profile_picture'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--text-tertiary);"></i>
                    </div>

                    <div class="admin-user-menu">
                        <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="admin-menu-item">
                            <i class="fas fa-user-circle"></i>
                            <?php echo __('profile'); ?>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/notifications.php" class="admin-menu-item">
                            <i class="fas fa-bell"></i>
                            <?php echo __('notifications'); ?>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-menu-item">
                            <i class="fas fa-cog"></i>
                            <?php echo __('settings'); ?>
                        </a>
                        <div class="admin-menu-divider"></div>
                        <a href="#" onclick="adminLogout()" class="admin-menu-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo __('logout'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">