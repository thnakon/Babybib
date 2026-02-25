<?php
if (!isAdmin()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="flex min-h-screen bg-white font-sans text-vercel-black" 
     x-data="{ sidebarOpen: window.innerWidth > 1024, userMenuOpen: false, notifOpen: false }"
     @resize.window="sidebarOpen = window.innerWidth > 1024">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-vercel-gray-200 transition-transform duration-200 transform"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="flex flex-col h-full">
            <!-- Logo Area -->
            <div class="h-14 flex items-center px-6">
                <a href="<?php echo SITE_URL; ?>/admin/index.php" class="flex items-center gap-2 font-bold text-vercel-black text-sm tracking-tight">
                    <div class="w-6 h-6 flex items-center justify-center bg-vercel-black rounded-md">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-white"></i>
                    </div>
                    <span>Babybib Admin</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto custom-scrollbar">
                <div class="px-3 py-2 text-[11px] font-medium text-vercel-gray-400 uppercase tracking-wider"><?php echo __('admin_nav_overview'); ?></div>
                <a href="<?php echo SITE_URL; ?>/admin/index.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'index.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_dashboard'); ?></span>
                </a>

                <div class="pt-6 px-3 py-2 text-[11px] font-medium text-vercel-gray-400 uppercase tracking-wider"><?php echo __('admin_nav_management'); ?></div>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'users.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_users'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/bibliographies.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'bibliographies.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="book-copy" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_bibs'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/projects.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'projects.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="folder" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_projects'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/feedback.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'feedback.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_feedback'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/announcements.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'announcements.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_announcements'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/notifications.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'notifications.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_notifications'); ?></span>
                </a>

                <div class="pt-6 px-3 py-2 text-[11px] font-medium text-vercel-gray-400 uppercase tracking-wider"><?php echo __('admin_nav_system'); ?></div>
                <a href="<?php echo SITE_URL; ?>/admin/backups.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'backups.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_backups'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/logs.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'logs.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_logs'); ?></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/settings.php" 
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md transition-colors text-sm <?php echo $currentPage === 'settings.php' ? 'bg-vercel-gray-100 text-vercel-black font-semibold' : 'text-vercel-gray-500 hover:text-vercel-black hover:bg-vercel-gray-100'; ?>">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span><?php echo __('admin_nav_settings'); ?></span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-vercel-gray-200">
                <button onclick="adminLogout()" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-vercel-gray-500 hover:text-vercel-red hover:bg-vercel-gray-100 rounded-md transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="font-medium"><?php echo __('admin_nav_logout'); ?></span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-200 bg-vercel-gray-100"
         :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'">
        
        <!-- Top Header -->
        <header class="h-14 bg-white border-b border-vercel-gray-200 sticky top-0 z-40 px-6 flex items-center justify-between">
            <!-- Left Side -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-vercel-gray-100 text-vercel-gray-500 transition-colors">
                    <i data-lucide="menu" class="w-4 h-4"></i>
                </button>
                <div class="text-xs font-medium text-vercel-gray-400 flex items-center gap-2">
                    <span>Admin</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <span class="text-vercel-black font-semibold"><?php echo str_replace('.php', '', ucfirst($currentPage)); ?></span>
                </div>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-3">
                <!-- Language Toggle -->
                <div class="flex items-center bg-vercel-gray-100 rounded-md p-0.5">
                    <a href="?lang=th" class="px-2.5 py-1 text-[10px] font-bold rounded transition-all <?php echo $currentLang === 'th' ? 'bg-vercel-black text-white shadow-sm' : 'text-vercel-gray-500 hover:text-vercel-black'; ?>">TH</a>
                    <a href="?lang=en" class="px-2.5 py-1 text-[10px] font-bold rounded transition-all <?php echo $currentLang === 'en' ? 'bg-vercel-black text-white shadow-sm' : 'text-vercel-gray-500 hover:text-vercel-black'; ?>">EN</a>
                </div>

                <div class="h-4 w-px bg-vercel-gray-200"></div>

                <!-- Notifications -->
                <div class="relative" @click.away="notifOpen = false">
                    <button @click="notifOpen = !notifOpen" 
                            class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-vercel-gray-100 text-vercel-gray-500 transition-colors relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-vercel-red rounded-full"></span>
                    </button>
                    <!-- Notif Dropdown -->
                    <div x-show="notifOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-80 bg-white border border-vercel-gray-200 rounded-lg shadow-xl z-50 overflow-hidden">
                        <div class="p-4 border-b border-vercel-gray-100 flex items-center justify-between bg-vercel-gray-100/50">
                            <span class="font-bold text-xs"><?php echo __('admin_nav_notifications'); ?></span>
                            <a href="#" class="text-[10px] text-vercel-blue font-bold hover:underline"><?php echo __('admin_nav_mark_read'); ?></a>
                        </div>
                        <div class="max-h-[300px] overflow-y-auto p-4 text-center">
                             <p class="text-xs text-vercel-gray-400"><?php echo __('admin_nav_no_notif'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="h-4 w-px bg-vercel-gray-200"></div>

                <!-- Profile Dropdown -->
                <div class="relative" @click.away="userMenuOpen = false">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-2 p-1 rounded-md hover:bg-vercel-gray-100 transition-colors">
                        <div class="w-6 h-6 rounded-full bg-vercel-gray-200 flex items-center justify-center text-[10px] font-bold text-vercel-gray-600 overflow-hidden">
                            <?php if (!empty($currentUser['profile_picture'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-vercel-gray-400"></i>
                    </button>

                    <!-- Profile Dropdown Menu -->
                    <div x-show="userMenuOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-48 bg-white border border-vercel-gray-200 rounded-lg shadow-xl z-50 p-1">
                        <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-xs text-vercel-gray-600 hover:bg-vercel-gray-100 hover:text-vercel-black transition-colors">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            <span><?php echo __('admin_nav_profile'); ?></span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-xs text-vercel-gray-600 hover:bg-vercel-gray-100 hover:text-vercel-black transition-colors">
                            <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                            <span><?php echo __('admin_nav_settings'); ?></span>
                        </a>
                        <div class="my-1 border-t border-vercel-gray-100"></div>
                        <button onclick="adminLogout()" class="flex items-center gap-2 w-full px-3 py-2 rounded-md text-xs text-vercel-red hover:bg-vercel-gray-100 transition-colors">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span><?php echo __('admin_nav_logout'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 sm:p-10 max-w-7xl mx-auto w-full">