<?php
/**
 * Babybib - Admin Activity Logs Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('activity_logs');
$extraStyles = '';

require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;
$search = sanitize($_GET['search'] ?? '');
$actionFilter = sanitize($_GET['action'] ?? '');

try {
    $db = getDB();

    $where = ["1=1"];
    $params = [];

    if ($search) {
        $where[] = "(al.description LIKE ? OR u.username LIKE ? OR al.action LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }

    if ($actionFilter) {
        $where[] = "al.action = ?";
        $params[] = $actionFilter;
    }

    $whereClause = implode(' AND ', $where);

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    $stmt = $db->prepare("
        SELECT al.*, u.username, u.name as user_name, u.profile_picture
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id
        WHERE $whereClause
        ORDER BY al.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    $allActions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Admin logs error: " . $e->getMessage());
    $logs = [];
    $total = 0;
    $totalPages = 0;
}

// Action Config
$actionConfig = [
    'login' => ['icon' => 'log-in', 'color' => 'bg-emerald-50 text-emerald-600'],
    'logout' => ['icon' => 'log-out', 'color' => 'bg-slate-50 text-slate-600'],
    'register' => ['icon' => 'user-plus', 'color' => 'bg-purple-50 text-purple-600'],
    'create_bibliography' => ['icon' => 'plus-circle', 'color' => 'bg-blue-50 text-blue-600'],
    'update_bibliography' => ['icon' => 'edit-3', 'color' => 'bg-amber-50 text-amber-600'],
    'delete_bibliography' => ['icon' => 'trash-2', 'color' => 'bg-red-50 text-red-600'],
    'create_project' => ['icon' => 'folder-plus', 'color' => 'bg-emerald-50 text-emerald-600'],
    'update_project' => ['icon' => 'folder-open', 'color' => 'bg-amber-50 text-amber-600'],
    'delete_project' => ['icon' => 'folder-x', 'color' => 'bg-red-50 text-red-600'],
    'update_profile' => ['icon' => 'user-cog', 'color' => 'bg-purple-50 text-purple-600'],
    'admin_update_user' => ['icon' => 'shield-check', 'color' => 'bg-red-50 text-red-600'],
    'admin_update_project' => ['icon' => 'layout-grid', 'color' => 'bg-red-50 text-red-600'],
    'admin_update_bibliography' => ['icon' => 'book-open', 'color' => 'bg-red-50 text-red-600'],
    'change_password' => ['icon' => 'key', 'color' => 'bg-amber-50 text-amber-600'],
    'submit_feedback' => ['icon' => 'message-square', 'color' => 'bg-blue-50 text-blue-600'],
    'update_feedback' => ['icon' => 'check-circle-2', 'color' => 'bg-emerald-50 text-emerald-600'],
];

function getActionMeta($action) {
    global $actionConfig;
    return $actionConfig[$action] ?? ['icon' => 'activity', 'color' => 'bg-slate-50 text-slate-400'];
}
?>

<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?php echo __('activity_logs'); ?></h1>
            <p class="text-sm text-slate-500 mt-1">
                <?php echo __('admin_logs_desc'); ?> 
                <span class="ml-2 px-2 py-0.5 bg-primary/10 text-primary rounded-full text-xs font-bold"><?php echo number_format($total); ?> <?php echo __('admin_logs_label'); ?></span>
            </p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-[2rem] border border-slate-200 shadow-sm flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="log-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="<?php echo __('admin_search_logs'); ?>"
                   class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <select id="action-filter" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none">
                <option value=""><?php echo __('admin_all_actions'); ?></option>
                <?php foreach ($allActions as $act): ?>
                    <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $actionFilter === $act ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($act); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Log List -->
    <div class="space-y-3">
        <?php if (empty($logs)): ?>
            <div class="bg-white p-20 rounded-[2.5rem] border border-slate-200 shadow-sm text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="history" class="w-10 h-10 text-slate-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2"><?php echo __('admin_no_logs'); ?></h3>
                <p class="text-slate-500"><?php echo __('admin_logs_empty'); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($logs as $log): 
                $meta = getActionMeta($log['action']);
            ?>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 <?php echo $meta['color']; ?>">
                        <i data-lucide="<?php echo $meta['icon']; ?>" class="w-5 h-5"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-black text-slate-900 uppercase tracking-wider"><?php echo str_replace('_', ' ', $log['action']); ?></span>
                            <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                            <span class="text-[10px] font-bold text-slate-400"><?php echo formatThaiDate($log['created_at']); ?> <?php echo date('H:i:s', strtotime($log['created_at'])); ?></span>
                        </div>
                        <div class="text-sm text-slate-600 font-medium truncate">
                            <?php echo htmlspecialchars($log['description']); ?>
                        </div>
                    </div>

                    <!-- User -->
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-50 hidden sm:flex min-w-[150px]">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400 overflow-hidden">
                             <?php if (!empty($log['profile_picture'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($log['profile_picture']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo $log['username'] ? strtoupper(substr($log['username'], 0, 1)) : 'S'; ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs">
                            <div class="font-bold text-slate-900 leading-tight"><?php echo $log['username'] ? '@' . htmlspecialchars($log['username']) : 'System'; ?></div>
                            <div class="text-[10px] text-slate-400 mt-0.5"><?php echo $log['ip_address'] ?: 'Internal'; ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-12 pb-10">
            <button onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 disabled:opacity-30 transition-all">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <button onclick="goToPage(<?php echo $i; ?>)" 
                        class="w-10 h-10 rounded-xl border text-sm font-bold transition-all <?php echo $i === $page ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' : 'border-slate-200 text-slate-500 hover:bg-slate-50'; ?>">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>
            <button onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 disabled:opacity-30 transition-all">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
    const actionSelect = document.getElementById('action-filter');
    const searchInput = document.getElementById('log-search');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('action', actionSelect.value);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 600);
    });

    actionSelect.addEventListener('change', updateFilters);

    function goToPage(p) {
        const url = new URL(window.location);
        url.searchParams.set('page', p);
        window.location = url.toString();
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>