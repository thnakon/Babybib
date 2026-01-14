<?php

/**
 * Babybib - Activity History (Premium Design)
 * =========================================
 * Displays the list of user actions and activities in a premium list format
 */

$pageTitle = 'ประวัติการทำงาน';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();
$currentLang = getCurrentLanguage();

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filter
$actionFilter = isset($_GET['action']) ? sanitize($_GET['action']) : '';

try {
    $db = getDB();

    // Count total logs for pagination (Restricted to last 7 days for users)
    $countSql = "SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    if ($actionFilter) $countSql .= " AND action = ?";

    $stmt = $db->prepare($countSql);
    $params = [$userId];
    if ($actionFilter) $params[] = $actionFilter;
    $stmt->execute($params);
    $totalLogs = $stmt->fetchColumn();
    $totalPages = ceil($totalLogs / $limit);

    // Get logs (Restricted to last 7 days)
    $sql = "SELECT * FROM activity_logs WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    if ($actionFilter) $sql .= " AND action = ?";
    $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    // Get unique actions for filter
    $stmt = $db->prepare("SELECT DISTINCT action FROM activity_logs WHERE user_id = ? ORDER BY action ASC");
    $stmt->execute([$userId]);
    $availableActions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $logs = [];
    $totalLogs = 0;
    $totalPages = 0;
}

// Action Helper for Human Readable Text
function getActionLabel($action, $lang)
{
    $labels = [
        'login' => ['th' => 'เข้าสู่ระบบ', 'en' => 'Login'],
        'register' => ['th' => 'สมัครสมาชิก', 'en' => 'Registration'],
        'create_bibliography' => ['th' => 'สร้างบรรณานุกรม', 'en' => 'Created Bibliography'],
        'update_bibliography' => ['th' => 'อัปเดตบรรณานุกรม', 'en' => 'Updated Bibliography'],
        'delete_bibliography' => ['th' => 'ลบบรรณานุกรม', 'en' => 'Deleted Bibliography'],
        'create_project' => ['th' => 'สร้างโครงการ', 'en' => 'Created Project'],
        'update_project' => ['th' => 'อัปเดตโครงการ', 'en' => 'Updated Project'],
        'delete_project' => ['th' => 'ลบโครงการ', 'en' => 'Deleted Project'],
        'update_profile' => ['th' => 'แก้ไขโปรไฟล์', 'en' => 'Updated Profile'],
        'change_password' => ['th' => 'เปลี่ยนรหัสผ่าน', 'en' => 'Changed Password']
    ];

    return $labels[$action][$lang] ?? $action;
}

function getActionIcon($action)
{
    $icons = [
        'login' => 'fa-sign-in-alt',
        'create_bibliography' => 'fa-plus-circle',
        'update_bibliography' => 'fa-edit',
        'delete_bibliography' => 'fa-trash-alt',
        'create_project' => 'fa-folder-plus',
        'update_project' => 'fa-folder-open',
        'delete_project' => 'fa-folder-minus',
        'update_profile' => 'fa-user-cog',
        'change_password' => 'fa-key'
    ];
    return $icons[$action] ?? 'fa-circle-info';
}

function getActionColor($action)
{
    if (strpos($action, 'create') !== false) return '#10B981'; // Success Green
    if (strpos($action, 'update') !== false) return '#3B82F6'; // Info Blue
    if (strpos($action, 'delete') !== false) return '#EF4444'; // Danger Red
    return '#8B5CF6'; // Primary Purple
}
?>

<style>
    /* Reuse Bibliography List Styles */
    .history-page-wrapper {
        max-width: 1000px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4);
    }

    .history-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: var(--space-8);
    }

    .history-header-text {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .history-header-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-gradient);
        color: var(--white);
        border-radius: var(--radius-lg);
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: var(--shadow-primary);
    }

    .history-header-info h1 {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .history-header-info p {
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }

    /* Filter Toolbar */
    .history-toolbar {
        display: flex;
        gap: var(--space-3);
        align-items: center;
        margin-bottom: var(--space-6);
        background: var(--white);
        padding: var(--space-3) var(--space-4);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .history-filter-select {
        padding: var(--space-2) 35px var(--space-2) var(--space-3);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        font-size: var(--text-sm);
        color: var(--text-primary);
        cursor: pointer;
        background-color: var(--white);
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238B5CF6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        min-width: 180px;
        transition: all var(--transition);
    }

    .history-filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    /* Log List Items */
    .history-list-container {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
    }

    .history-item-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.03);
        padding: var(--space-4) var(--space-5);
        display: flex;
        align-items: center;
        gap: var(--space-4);
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .history-item-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--accent-color, var(--primary));
        opacity: 0.8;
    }

    .history-item-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .history-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        background: rgba(var(--accent-rgb, 139, 92, 246), 0.1);
        color: var(--accent-color, var(--primary));
    }

    .history-content {
        flex-grow: 1;
        min-width: 0;
    }

    .history-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2px;
    }

    .history-action-name {
        font-weight: 700;
        color: var(--text-primary);
        font-size: var(--text-base);
    }

    .history-time {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        font-weight: 500;
    }

    .history-description {
        color: var(--text-secondary);
        font-size: var(--text-sm);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 90%;
    }

    .history-meta {
        display: flex;
        gap: var(--space-4);
        margin-top: var(--space-2);
        font-size: 11px;
        color: var(--text-tertiary);
    }

    .history-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Pagination */
    .history-pagination {
        margin-top: var(--space-8);
        display: flex;
        justify-content: center;
        gap: var(--space-2);
    }

    .history-page-btn {
        min-width: 40px;
        height: 40px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-primary);
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition);
        text-decoration: none;
    }

    .history-page-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-light);
    }

    .history-page-btn.active {
        background: var(--primary-gradient);
        color: var(--white);
        border-color: transparent;
        box-shadow: var(--shadow-sm);
    }

    /* Empty State */
    .history-empty-state {
        text-align: center;
        padding: var(--space-12);
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
    }

    .history-empty-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-4);
        background: var(--gray-100);
        color: var(--text-tertiary);
        border-radius: 50%;
        font-size: 1.5rem;
    }
</style>

<div class="history-page-wrapper">
    <!-- Header -->
    <div class="history-page-header slide-up">
        <div class="history-header-text">
            <div class="history-header-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="history-header-info">
                <h1><?php echo $currentLang === 'th' ? 'ประวัติการทำงาน' : 'Work History'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'ตรวจสอบกิจกรรมใน 7 วันล่าสุด (ระบบจะลบประวัติย้อนหลังอัตโนมัติ)' : 'Review activities for the last 7 days (History is cleared automatically)'; ?></p>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="history-toolbar slide-up stagger-1">
        <div style="display: flex; align-items: center; gap: var(--space-3);">
            <i class="fas fa-filter text-tertiary"></i>
            <span class="text-sm font-semibold text-secondary"><?php echo $currentLang === 'th' ? 'กรอง:' : 'Filter:'; ?></span>
        </div>

        <select class="history-filter-select" onchange="location.href='?action=' + this.value">
            <option value=""><?php echo $currentLang === 'th' ? 'กิจกรรมทั้งหมด' : 'All Actions'; ?></option>
            <?php foreach ($availableActions as $action): ?>
                <option value="<?php echo $action; ?>" <?php echo $actionFilter === $action ? 'selected' : ''; ?>>
                    <?php echo getActionLabel($action, $currentLang); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="margin-left: auto;">
            <span class="badge" style="background: var(--gray-100); color: var(--text-secondary); font-weight: 600;">
                <?php echo number_format($totalLogs); ?> <?php echo $currentLang === 'th' ? 'รายการ' : 'entries'; ?>
            </span>
        </div>
    </div>

    <!-- History List -->
    <div class="history-list-container">
        <?php if (empty($logs)): ?>
            <div class="history-empty-state slide-up stagger-2">
                <div class="history-empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="font-semibold text-primary mb-2"><?php echo $currentLang === 'th' ? 'ไม่พบประวัติการทำงาน' : 'No activity found'; ?></h3>
                <p class="text-sm text-secondary"><?php echo $currentLang === 'th' ? 'ลองเปลี่ยนตัวกรอง หรือเริ่มสร้างบรรณานุกรม' : 'Try clouding filters or start creating bibliographies'; ?></p>
            </div>
        <?php else: ?>
            <div class="slide-up stagger-2">
                <?php foreach ($logs as $log):
                    $color = getActionColor($log['action']);
                    // Simple hack to get RGB for transparent background
                    $rgb = '139, 92, 246'; // Default purple
                    if ($color === '#10B981') $rgb = '16, 185, 129';
                    if ($color === '#3B82F6') $rgb = '59, 130, 246';
                    if ($color === '#EF4444') $rgb = '239, 68, 68';
                ?>
                    <div class="history-item-card mb-3" style="--accent-color: <?php echo $color; ?>; --accent-rgb: <?php echo $rgb; ?>;">
                        <div class="history-icon-wrapper">
                            <i class="fas <?php echo getActionIcon($log['action']); ?>"></i>
                        </div>
                        <div class="history-content">
                            <div class="history-title-row">
                                <span class="history-action-name"><?php echo getActionLabel($log['action'], $currentLang); ?></span>
                                <span class="history-time"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></span>
                            </div>
                            <div class="history-description">
                                <?php echo htmlspecialchars($log['description']); ?>
                            </div>
                            <div class="history-meta">
                                <div class="history-meta-item">
                                    <i class="fas fa-network-wired"></i>
                                    <span>IP: <?php echo $log['ip_address'] ?? '-'; ?></span>
                                </div>
                                <?php if ($log['entity_type']): ?>
                                    <div class="history-meta-item">
                                        <i class="fas fa-link"></i>
                                        <span><?php echo ucfirst($log['entity_type']); ?> ID: <?php echo $log['entity_id']; ?></span>
                                    </div>
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
        <div class="history-pagination slide-up stagger-3">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&action=<?php echo $actionFilter; ?>" class="history-page-btn <?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>