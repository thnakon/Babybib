<?php

/**
 * Babybib - Admin Activity Logs Page
 * ====================================
 */

require_once '../includes/session.php';

$pageTitle = 'บันทึกกิจกรรม';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
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

    // Get unique actions for filter
    $stmt = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action ASC");
    $allActions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Admin logs error: " . $e->getMessage());
    $logs = [];
    $total = 0;
    $totalPages = 0;
    $allActions = [];
}

$actionIcons = [
    'login' => ['icon' => 'fa-sign-in-alt', 'color' => '#10B981'],
    'logout' => ['icon' => 'fa-sign-out-alt', 'color' => '#6B7280'],
    'register' => ['icon' => 'fa-user-plus', 'color' => '#8B5CF6'],
    'create_bibliography' => ['icon' => 'fa-plus-circle', 'color' => '#3B82F6'],
    'update_bibliography' => ['icon' => 'fa-edit', 'color' => '#F59E0B'],
    'delete_bibliography' => ['icon' => 'fa-trash-alt', 'color' => '#EF4444'],
    'create_project' => ['icon' => 'fa-folder-plus', 'color' => '#10B981'],
    'update_project' => ['icon' => 'fa-folder-open', 'color' => '#F59E0B'],
    'delete_project' => ['icon' => 'fa-folder-minus', 'color' => '#EF4444'],
    'update_profile' => ['icon' => 'fa-user-cog', 'color' => '#8B5CF6'],
    'admin_update_user' => ['icon' => 'fa-user-shield', 'color' => '#EF4444'],
    'admin_update_project' => ['icon' => 'fa-project-diagram', 'color' => '#EF4444'],
    'admin_update_bibliography' => ['icon' => 'fa-book-reader', 'color' => '#EF4444'],
    'change_password' => ['icon' => 'fa-key', 'color' => '#F59E0B'],
    'submit_feedback' => ['icon' => 'fa-comment-alt', 'color' => '#3B82F6'],
    'update_feedback' => ['icon' => 'fa-tasks', 'color' => '#10B981'],
];
?>



<div class="admin-logs-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('activity_logs'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "ตรวจสอบความเคลื่อนไหวทั้งหมดในระบบ" : "Monitor all activities in the system"; ?> <span class="badge-count"><?php echo number_format($total); ?> LOGS</span></p>
            </div>
        </div>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="log-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหารายละเอียด, ชื่อผู้ใช้, กิจกรรม...' : 'Search description, username, action...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="action-filter">
            <option value=""><?php echo $currentLang === 'th' ? 'กิจกรรมทั้งหมด' : 'All Actions'; ?></option>
            <?php foreach ($allActions as $act): ?>
                <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $actionFilter === $act ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($act); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Log List -->
    <div class="content-section">
        <?php if (empty($logs)): ?>
            <div style="padding: 100px 0; text-align: center; background: white; border-radius: 24px; border: 2px dashed var(--gray-100);" class="slide-up stagger-2">
                <i class="fas fa-clipboard-list fa-3x mb-4" style="color: var(--gray-200);"></i>
                <p class="text-secondary"><?php echo $currentLang === 'th' ? 'ไม่พบประวัติการใช้งาน' : 'No activity logs found'; ?></p>
            </div>
        <?php else: ?>
            <div class="log-list">
                <?php foreach ($logs as $index => $log):
                    $actionData = $actionIcons[$log['action']] ?? ['icon' => 'fa-circle', 'color' => '#94A3B8'];
                ?>
                    <div class="log-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="log-icon-wrapper" style="background: <?php echo $actionData['color']; ?>15; color: <?php echo $actionData['color']; ?>;">
                            <i class="fas <?php echo $actionData['icon']; ?>"></i>
                        </div>

                        <div class="log-main">
                            <div class="log-user-line">
                                <span class="log-username">
                                    <?php if ($log['username']): ?>
                                        <i class="far fa-user-circle"></i> @<?php echo htmlspecialchars($log['username']); ?>
                                    <?php else: ?>
                                        <i class="fas fa-robot"></i> System
                                    <?php endif; ?>
                                </span>
                                <span class="log-action-tag"><?php echo htmlspecialchars($log['action']); ?></span>
                            </div>

                            <div class="log-description">
                                <?php echo htmlspecialchars($log['description']); ?>
                            </div>

                            <div class="log-meta">
                                <span><i class="far fa-clock"></i> <?php echo formatThaiDate($log['created_at']); ?> <?php echo date('H:i:s', strtotime($log['created_at'])); ?> น.</span>
                                <?php if ($log['ip_address']): ?>
                                    <span><i class="fas fa-network-wired"></i> IP: <?php echo htmlspecialchars($log['ip_address']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination slide-up">
            <button class="pagination-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-left"></i>
            </button>
            <?php
            $range = 2;
            $start = max(1, $page - $range);
            $end = min($totalPages, $page + $range);

            if ($start > 1) {
                echo '<button class="pagination-btn" onclick="goToPage(1)">1</button>';
                if ($start > 2) echo '<span class="pagination-ellipsis" style="padding: 0 5px; color: var(--text-tertiary); font-weight: 700;">...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $active = $i === $page ? 'active' : '';
                echo "<button class=\"pagination-btn $active\" onclick=\"goToPage($i)\">$i</button>";
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis" style="padding: 0 5px; color: var(--text-tertiary); font-weight: 700;">...</span>';
                echo "<button class=\"pagination-btn\" onclick=\"goToPage($totalPages)\">$totalPages</button>";
            }
            ?>
            <button class="pagination-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
    const actionFilter = document.getElementById('action-filter');
    const searchInput = document.getElementById('log-search');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('action', actionFilter.value);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    actionFilter.addEventListener('change', updateFilters);

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 600);
    });

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>