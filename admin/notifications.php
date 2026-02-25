<?php
/**
 * Babybib - Admin Notifications Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('admin_notif_title');
$extraStyles = '';

require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Filter
$filterType = sanitize($_GET['type'] ?? '');

try {
    $db = getDB();

    // Create temporary table-like array from both sources
    $allNotifications = [];

    // Fetch from admin_notifications
    $notifQuery = "SELECT * FROM admin_notifications ORDER BY created_at DESC";
    $notifications = $db->query($notifQuery)->fetchAll();
    foreach ($notifications as $n) {
        $allNotifications[] = [
            'id' => 'notif_' . $n['id'],
            'type' => $n['type'],
            'title' => $n['title'],
            'message' => $n['message'] ?? '',
            'link' => $n['link'],
            'is_read' => $n['is_read'],
            'created_at' => $n['created_at']
        ];
    }

    // Fetch pending feedback as notifications  
    $feedbackQuery = "SELECT id, subject, message, created_at FROM feedback WHERE status = 'pending' ORDER BY created_at DESC";
    $pendingFeedback = $db->query($feedbackQuery)->fetchAll();
    foreach ($pendingFeedback as $f) {
        $allNotifications[] = [
            'id' => 'feedback_' . $f['id'],
            'type' => 'feedback',
            'title' => __('admin_new_feedback') . $f['subject'],
            'message' => substr($f['message'], 0, 150),
            'link' => '/admin/feedback.php?id=' . $f['id'],
            'is_read' => 0,
            'created_at' => $f['created_at']
        ];
    }

    // Sort by created_at DESC
    usort($allNotifications, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Filter by type
    if ($filterType) {
        $allNotifications = array_filter($allNotifications, function ($n) use ($filterType) {
            return $n['type'] === $filterType;
        });
        $allNotifications = array_values($allNotifications);
    }

    // Pagination
    $total = count($allNotifications);
    $totalPages = ceil($total / $perPage);
    $allNotifications = array_slice($allNotifications, $offset, $perPage);
} catch (Exception $e) {
    error_log("Notifications error: " . $e->getMessage());
    $allNotifications = [];
    $total = 0;
    $totalPages = 0;
}

// Icon Mapping
$typeMeta = [
    'feedback' => ['icon' => 'message-square-plus', 'color' => 'bg-blue-50 text-blue-600'],
    'user' => ['icon' => 'user-plus', 'color' => 'bg-purple-50 text-purple-600'],
    'system' => ['icon' => 'shield-check', 'color' => 'bg-slate-50 text-slate-600'],
    'announcement' => ['icon' => 'bullhorn', 'color' => 'bg-amber-50 text-amber-600'],
    'error' => ['icon' => 'alert-circle', 'color' => 'bg-red-50 text-red-600'],
];

function getTypeMeta($type) {
    global $typeMeta;
    return $typeMeta[$type] ?? ['icon' => 'bell', 'color' => 'bg-slate-50 text-slate-400'];
}
?>

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('notifications'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Monitor system-wide events, security alerts, and user interactions.
            </p>
        </div>
        <button onclick="markAllRead()" class="px-6 py-2.5 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all flex items-center gap-2">
            <i data-lucide="check-check" class="w-4 h-4"></i>
            <span><?php echo __('admin_mark_all_read'); ?></span>
        </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <select onchange="filterByType(this.value)" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_notif_types'); ?></option>
                <option value="feedback" <?php echo $filterType === 'feedback' ? 'selected' : ''; ?>>Feedback</option>
                <option value="user" <?php echo $filterType === 'user' ? 'selected' : ''; ?>>New Users</option>
                <option value="system" <?php echo $filterType === 'system' ? 'selected' : ''; ?>>System Audit</option>
                <option value="announcement" <?php echo $filterType === 'announcement' ? 'selected' : ''; ?>>Broadcasts</option>
            </select>
        </div>

        <div class="px-3 py-1 border border-vercel-gray-200 bg-vercel-gray-50 rounded flex items-center gap-2">
            <span class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest leading-none">Inbound Alerts</span>
            <span class="text-xs font-black text-vercel-black"><?php echo number_format($total); ?></span>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="divide-y divide-vercel-gray-200 border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
        <?php if (empty($allNotifications)): ?>
            <div class="py-20 text-center text-vercel-gray-400 font-medium">Clear. No active notifications.</div>
        <?php else: ?>
            <?php foreach ($allNotifications as $notif): 
                $meta = getTypeMeta($notif['type']);
                $link = isset($notif['link']) ? SITE_URL . $notif['link'] : '#';
                $isUnread = !$notif['is_read'];
            ?>
                <a href="<?php echo htmlspecialchars($link); ?>" 
                   class="block p-6 hover:bg-vercel-gray-50 transition-colors relative group border-l-4 <?php echo $isUnread ? 'border-vercel-black bg-vercel-gray-50/30' : 'border-transparent'; ?>">
                    
                    <div class="flex items-center gap-6">
                        <div class="w-10 h-10 rounded border border-vercel-gray-200 bg-white flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                            <i data-lucide="<?php echo $meta['icon']; ?>" class="w-4 h-4"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-4 mb-1">
                                <h4 class="text-sm font-black text-vercel-black tracking-tight group-hover:underline decoration-vercel-gray-200 underline-offset-4 truncate"><?php echo htmlspecialchars($notif['title']); ?></h4>
                                <span class="text-[10px] font-bold text-vercel-gray-400 uppercase tracking-tight flex-shrink-0"><?php echo formatThaiDate($notif['created_at']); ?></span>
                            </div>
                            <?php if (!empty($notif['message'])): ?>
                                <p class="text-xs text-vercel-gray-500 font-medium line-clamp-1 italic"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                             <div class="w-8 h-8 rounded-md flex items-center justify-center text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                             </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-vercel-gray-200 pt-8 mt-4">
            <div class="text-xs text-vercel-gray-400 font-medium italic">
                Scanning alerts <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> - <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 transition-all">
                    Back
                </button>
                <div class="px-4 py-1.5 text-xs font-black text-vercel-black uppercase tracking-widest">
                    Vol <?php echo $page; ?> / <?php echo $totalPages; ?>
                </div>
                <button onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 transition-all">
                    Next
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function filterByType(type) {
        const url = new URL(window.location);
        if (type) url.searchParams.set('type', type);
        else url.searchParams.delete('type');
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    function goToPage(p) {
        const url = new URL(window.location);
        url.searchParams.set('page', p);
        window.location = url.toString();
    }

    async function markAllRead() {
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/mark-notifications-read.php');
            if (res.success) {
                Toast.success('Memory purged');
                setTimeout(() => location.reload(), 800);
            }
        } catch (e) { Toast.error('Communication error'); }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>