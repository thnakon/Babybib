<?php
/**
 * Babybib - Admin Dashboard (Modern Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = $currentLang === 'th' ? 'แดชบอร์ดผู้ดูแลระบบ' : 'Admin Dashboard';
// We can remove old layout CSS as we use Tailwind now
$extraStyles = ''; 

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

    // Recent Activity
    $recentUsers = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $recentBibs = $db->query("
        SELECT b.*, u.username, r.name_th as resource_name
        FROM bibliographies b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN resource_types r ON b.resource_type_id = r.id
        ORDER BY b.created_at DESC LIMIT 5
    ")->fetchAll();

    $todayReg = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND role = 'user'")->fetchColumn();
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>

<div class="space-y-12 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight">
                <?php echo __('admin_dashboard'); ?>
            </h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                <?php echo __('admin_system_overview'); ?>
            </p>
        </div>
        <div class="flex items-center gap-4">
             <div class="px-4 py-2 bg-vercel-gray-100 border border-vercel-gray-200 rounded-md flex items-center gap-3">
                 <div class="w-1.5 h-1.5 bg-vercel-emerald rounded-full"></div>
                 <span class="text-xs font-semibold text-vercel-gray-600">
                    <?php echo $todayReg; ?> <?php echo __('admin_new_reg_today'); ?>
                 </span>
             </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-px bg-vercel-gray-200 border border-vercel-gray-200 rounded-lg overflow-hidden">
        <!-- Stat Item 1 -->
        <div class="bg-white p-8 group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-widest text-vercel-gray-400"><?php echo __('admin_total_users'); ?></span>
                <i data-lucide="users" class="w-4 h-4 text-vercel-gray-300 group-hover:text-vercel-black transition-colors"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <div class="text-3xl font-black text-vercel-black"><?php echo number_format($totalUsers); ?></div>
                <div class="text-xs font-bold text-vercel-emerald">+<?php echo $todayReg; ?></div>
            </div>
        </div>

        <!-- Stat Item 2 -->
        <div class="bg-white p-8 group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-widest text-vercel-gray-400"><?php echo __('admin_lis_students'); ?></span>
                <i data-lucide="graduation-cap" class="w-4 h-4 text-vercel-gray-300 group-hover:text-vercel-black transition-colors"></i>
            </div>
            <div class="text-3xl font-black text-vercel-black"><?php echo number_format($totalLis); ?></div>
        </div>

        <!-- Stat Item 3 -->
        <div class="bg-white p-8 group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-widest text-vercel-gray-400"><?php echo __('admin_bibliographies'); ?></span>
                <i data-lucide="book-copy" class="w-4 h-4 text-vercel-gray-300 group-hover:text-vercel-black transition-colors"></i>
            </div>
            <div class="text-3xl font-black text-vercel-black"><?php echo number_format($totalBibs); ?></div>
        </div>

        <!-- Stat Item 4 -->
        <div class="bg-white p-8 group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-widest text-vercel-gray-400"><?php echo __('feedback_management'); ?></span>
                <i data-lucide="message-circle" class="w-4 h-4 text-vercel-gray-300 group-hover:text-vercel-black transition-colors"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <div class="text-3xl font-black text-vercel-black"><?php echo number_format($pendingFeedback); ?></div>
                <?php if ($pendingFeedback > 0): ?>
                    <div class="text-xs font-bold text-vercel-amber"><?php echo __('admin_pending'); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Recent Bibliographies -->
        <div class="lg:col-span-8 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-vercel-black tracking-tight"><?php echo __('admin_recent_bibs'); ?></h2>
                <a href="bibliographies.php" class="text-xs font-bold text-vercel-blue hover:underline"><?php echo __('view_all'); ?></a>
            </div>
            
            <div class="border border-vercel-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-vercel-gray-100 border-b border-vercel-gray-200 text-vercel-gray-500 font-medium">
                        <tr>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wider text-[10px]"><?php echo __('admin_content'); ?></th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wider text-[10px]"><?php echo __('admin_author'); ?></th>
                            <th class="px-6 py-3 font-semibold uppercase tracking-wider text-[10px]"><?php echo __('admin_date'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-vercel-gray-200">
                        <?php if (empty($recentBibs)): ?>
                            <tr><td colspan="3" class="px-6 py-12 text-center text-vercel-gray-400"><?php echo __('admin_no_entries'); ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBibs as $bib): ?>
                                <tr class="hover:bg-vercel-gray-100/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="max-w-md">
                                            <div class="font-bold text-vercel-black truncate"><?php echo strip_tags($bib['bibliography_text']); ?></div>
                                            <div class="text-[10px] text-vercel-gray-400 mt-0.5"><?php echo $bib['resource_name']; ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-vercel-gray-600 font-medium">@<?php echo htmlspecialchars($bib['username']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-vercel-gray-400 text-xs font-medium">
                                        <?php echo date('M d, Y', strtotime($bib['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="lg:col-span-4 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-vercel-black tracking-tight"><?php echo __('admin_new_users'); ?></h2>
                <a href="users.php" class="text-xs font-bold text-vercel-blue hover:underline"><?php echo __('view_all'); ?></a>
            </div>

            <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden divide-y divide-vercel-gray-200">
                <?php foreach ($recentUsers as $u): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-vercel-gray-100/50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-vercel-gray-100 flex items-center justify-center text-[10px] font-black text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white transition-all">
                                <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-vercel-black"><?php echo htmlspecialchars($u['name']); ?></div>
                                <div class="text-[10px] text-vercel-gray-400 font-medium tracking-tight">@<?php echo htmlspecialchars($u['username']); ?></div>
                            </div>
                        </div>
                        <div class="text-[10px] font-bold text-vercel-gray-300"><?php echo date('H:i', strtotime($u['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer-admin.php'; ?>