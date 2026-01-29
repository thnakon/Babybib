<?php

/**
 * Babybib - Bibliography List (New Design)
 * =========================================
 */

$pageTitle = 'รายการบรรณานุกรม';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();

// Auto-cleanup old records (> 2 years)
$cleanedCount = cleanupOldBibliographies($userId);

// Set session flag for auto-cleanup toast (only show once)
if ($cleanedCount > 0 && !isset($_SESSION['cleanup_notified'])) {
    $_SESSION['cleanup_notified'] = $cleanedCount;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Search and filter
$search = sanitize($_GET['search'] ?? '');
$filterType = intval($_GET['type'] ?? 0);
$filterProject = intval($_GET['project'] ?? 0);
$sortOrder = sanitize($_GET['sort'] ?? 'newest');

try {
    $db = getDB();

    // Build query
    $where = ["b.user_id = ?"];
    $params = [$userId];

    if ($search) {
        $where[] = "b.bibliography_text LIKE ?";
        $params[] = "%$search%";
    }

    if ($filterType) {
        $where[] = "b.resource_type_id = ?";
        $params[] = $filterType;
    }

    if ($filterProject) {
        $where[] = "b.project_id = ?";
        $params[] = $filterProject;
    }

    $whereClause = implode(' AND ', $where);

    // Sort order
    $orderBy = "b.created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "b.created_at ASC";
    } elseif ($sortOrder === 'az') {
        $orderBy = "b.bibliography_text ASC";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bibliographies b WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Get bibliographies
    $stmt = $db->prepare("
        SELECT b.*, rt.name_th, rt.name_en, rt.icon, rt.code as resource_code, p.name as project_name, p.color as project_color
        FROM bibliographies b 
        JOIN resource_types rt ON b.resource_type_id = rt.id 
        LEFT JOIN projects p ON b.project_id = p.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $bibliographies = $stmt->fetchAll();

    // Get resource types for filter
    $resourceTypes = getResourceTypes();

    // Get user projects for filter
    $stmt = $db->prepare("SELECT id, name, color FROM projects WHERE user_id = ? ORDER BY name");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $bibliographies = [];
    $total = 0;
    $totalPages = 0;
}
?>

<style>
    /* === New Bibliography List Design === */
    .bib-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4);
    }

    /* Header Section */
    .bib-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: var(--space-5);
    }

    .bib-header-text {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .bib-header-icon {
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

    .bib-header-info h1 {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .bib-header-info p {
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }

    .btn-create-bib {
        background: var(--primary-gradient);
        color: var(--white);
        border: none;
        padding: var(--space-3) var(--space-6);
        border-radius: var(--radius-full);
        font-size: var(--text-base);
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: var(--space-2);
        box-shadow: var(--shadow-primary);
        transition: all var(--transition);
        text-decoration: none;
    }

    .btn-create-bib:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }

    /* Toolbar */
    .bib-toolbar {
        display: flex;
        gap: var(--space-3);
        align-items: center;
        margin-bottom: var(--space-5);
        flex-wrap: nowrap;
    }

    .bib-search-wrapper {
        position: relative;
        flex: 1;
        min-width: 180px;
    }

    .bib-search-input {
        width: 100%;
        padding: var(--space-3) var(--space-3) var(--space-3) 45px;
        border: 1px solid transparent;
        border-radius: var(--radius-lg);
        font-size: var(--text-sm);
        color: var(--text-primary);
        background: var(--white);
        box-shadow: var(--shadow);
        transition: all var(--transition);
    }

    .bib-search-input:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--white);
        box-shadow: var(--shadow-primary);
    }

    .bib-search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
    }

    .bib-filter-select {
        padding: var(--space-3) 35px var(--space-3) var(--space-3);
        border: 1px solid transparent;
        border-radius: var(--radius-lg);
        font-size: var(--text-sm);
        color: var(--text-primary);
        cursor: pointer;
        background-color: var(--white);
        box-shadow: var(--shadow);
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238B5CF6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        min-width: 120px;
        flex-shrink: 0;
        transition: all var(--transition);
    }

    .bib-filter-select:focus {
        outline: none;
        border-color: var(--primary);
        background-color: var(--white);
        box-shadow: var(--shadow-primary);
    }

    .bib-export-group {
        display: flex;
        gap: var(--space-2);
        margin-left: auto;
        flex-shrink: 0;
    }

    .btn-export {
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: var(--space-2);
        transition: all var(--transition);
        font-size: var(--text-sm);
        font-weight: 500;
    }

    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        color: var(--primary);
    }

    /* List Controls */
    .bib-list-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 var(--space-2) var(--space-3);
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }

    .bib-select-all-wrapper {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        cursor: pointer;
        font-weight: 500;
        color: var(--text-primary);
    }

    .bib-total-count {
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
    }

    /* View Mode Toggle */
    .bib-view-toggle {
        display: flex;
        gap: 2px;
        background: var(--gray-100);
        padding: 3px;
        border-radius: var(--radius-lg);
    }

    .bib-view-btn {
        padding: var(--space-2) var(--space-3);
        border: none;
        background: transparent;
        color: var(--text-tertiary);
        border-radius: var(--radius);
        cursor: pointer;
        transition: all var(--transition);
        display: flex;
        align-items: center;
        gap: var(--space-1);
        font-size: var(--text-xs);
        font-weight: 500;
    }

    .bib-view-btn:hover {
        color: var(--text-primary);
    }

    .bib-view-btn.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    /* Bibliography List */
    .bib-list-container {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
    }

    /* Grid View */
    .bib-list-container.grid-view {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-4);
    }

    .bib-list-container.grid-view .bib-item-card {
        flex-direction: column;
        align-items: stretch;
    }

    .bib-list-container.grid-view .bib-item-number {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
    }

    .bib-list-container.grid-view .bib-item-checkbox {
        position: absolute;
        top: var(--space-3);
        left: var(--space-3);
    }

    .bib-list-container.grid-view .bib-item-content {
        padding-top: var(--space-6);
    }

    .bib-list-container.grid-view .bib-item-actions {
        margin-top: var(--space-3);
        padding-top: var(--space-3);
        border-top: 1px solid var(--border-light);
        justify-content: center;
        opacity: 1;
    }

    /* Compact View */
    .bib-list-container.compact-view {
        gap: var(--space-1);
    }

    .bib-list-container.compact-view .bib-item-card {
        padding: var(--space-3) var(--space-4);
        border-radius: var(--radius);
    }

    .bib-list-container.compact-view .bib-item-card::before {
        width: 3px;
    }

    .bib-list-container.compact-view .bib-item-number {
        width: 24px;
        height: 24px;
        min-width: 24px;
        font-size: 10px;
    }

    .bib-list-container.compact-view .bib-item-title {
        font-size: var(--text-sm);
    }

    .bib-list-container.compact-view .bib-item-details {
        font-size: var(--text-xs);
        -webkit-line-clamp: 1;
        line-clamp: 1;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bib-list-container.compact-view .bib-item-meta {
        display: none;
    }

    .bib-list-container.compact-view .bib-action-btn {
        width: 28px;
        height: 28px;
    }

    /* Individual Item Card */
    .bib-item-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.03);
        padding: var(--space-5);
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .bib-item-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-gradient);
        opacity: 0.8;
    }

    .bib-item-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .bib-item-card.selected {
        background-color: var(--primary-light);
        border-color: var(--primary);
    }

    .bib-item-number {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        background: var(--primary-gradient);
        color: var(--white);
        font-size: var(--text-xs);
        font-weight: 700;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .bib-item-checkbox {
        margin-top: var(--space-1);
    }

    .bib-item-content {
        flex-grow: 1;
        cursor: pointer;
        min-width: 0;
    }

    .bib-item-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: var(--text-base);
        margin-bottom: var(--space-1);
        display: block;
        transition: color var(--transition);
    }

    .bib-item-card:hover .bib-item-title {
        color: var(--primary);
    }

    .bib-item-details {
        color: var(--text-secondary);
        font-size: var(--text-sm);
        line-height: 1.6;
        padding-left: 2em;
        text-indent: -2em;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .bib-item-meta {
        display: flex;
        gap: var(--space-2);
        margin-top: var(--space-3);
        font-size: var(--text-xs);
        flex-wrap: wrap;
    }

    .bib-tag {
        background: var(--primary-light);
        color: var(--primary);
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: var(--space-1);
    }

    .bib-tag-project {
        background: var(--gray-100);
        color: var(--text-secondary);
    }

    .bib-item-actions {
        display: flex;
        gap: var(--space-2);
        opacity: 0.4;
        transition: opacity var(--transition);
    }

    .bib-item-card:hover .bib-item-actions {
        opacity: 1;
    }

    .bib-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition);
    }

    .bib-action-btn:hover {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
        transform: scale(1.1);
    }

    .bib-action-btn.delete:hover {
        background: var(--danger);
        border-color: var(--danger);
    }

    .bib-action-btn.view:hover {
        background: var(--info);
        border-color: var(--info);
    }

    /* Pagination */
    .bib-pagination {
        margin-top: var(--space-8);
        display: flex;
        justify-content: center;
        gap: var(--space-2);
    }

    .bib-page-btn {
        min-width: 44px;
        height: 44px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-primary);
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .bib-page-btn:hover:not(:disabled) {
        background: var(--primary-light);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .bib-page-btn.active {
        background: var(--primary-gradient);
        color: var(--white);
        border-color: transparent;
        box-shadow: var(--shadow-primary);
    }

    .bib-page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: var(--gray-50);
    }

    .bib-page-ellipsis {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        color: var(--text-tertiary);
        font-weight: 600;
    }

    .bib-page-info {
        display: flex;
        align-items: center;
        margin-left: var(--space-4);
        padding-left: var(--space-4);
        border-left: 1px solid var(--border-light);
        color: var(--text-secondary);
        font-size: 14px;
        white-space: nowrap;
    }

    /* Bulk Action Bar */
    .bib-bulk-actions-bar {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--gray-900);
        color: var(--white);
        padding: var(--space-3) var(--space-6);
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        gap: var(--space-4);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        z-index: 900;
    }

    .bib-bulk-actions-bar.active {
        transform: translateX(-50%) translateY(0);
    }

    .bib-bulk-count {
        background: rgba(255, 255, 255, 0.2);
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-weight: 600;
    }

    .btn-bulk {
        background: rgba(255, 255, 255, 0.2);
        color: var(--white);
        border: none;
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        cursor: pointer;
        font-weight: 500;
        transition: all var(--transition);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .btn-bulk:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }

    .btn-bulk.delete {
        background: var(--danger);
    }

    .btn-bulk.delete:hover {
        background: #dc2626;
    }

    /* Empty State */
    .bib-empty-state {
        text-align: center;
        padding: var(--space-12);
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
    }

    .bib-empty-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-4);
        background: var(--gray-100);
        color: var(--text-tertiary);
        border-radius: 50%;
        font-size: var(--text-3xl);
    }

    .btn-help-policy {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--white);
        border: 1px solid var(--border-light);
        color: var(--text-tertiary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition);
        font-size: 1.1rem;
        box-shadow: var(--shadow-sm);
    }

    .btn-help-policy:hover {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 900px) {
        .bib-page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--space-4);
        }

        .bib-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .bib-export-group {
            margin-left: 0;
            justify-content: flex-start;
            padding-top: var(--space-3);
            border-top: 1px dashed var(--border-light);
            width: 100%;
        }

        .btn-create-bib {
            width: 100%;
            justify-content: center;
        }

        .bib-item-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .bib-item-actions {
            width: 100%;
            justify-content: flex-end;
            opacity: 1;
            margin-top: var(--space-3);
        }
    }
</style>

<main class="bib-page-wrapper">
    <!-- Header -->
    <div class="bib-page-header slide-up">
        <div class="bib-header-text">
            <div class="bib-header-icon">
                <i class="fas fa-list-ul"></i>
            </div>
            <div class="bib-header-info">
                <h1><?php echo __('bibliography_list'); ?></h1>
                <p><?php echo $currentLang === 'th' ? 'จัดการบรรณานุกรมทั้งหมดของคุณ' : 'Manage all your bibliographies'; ?></p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: var(--space-3);">
            <button class="btn-help-policy" onclick="showRetentionPolicy()" title="<?php echo $currentLang === 'th' ? 'นโยบายการจัดเก็บข้อมูล' : 'Data Retention Policy'; ?>">
                <i class="fas fa-exclamation-circle"></i>
            </button>
            <a href="<?php echo SITE_URL; ?>/generate.php" class="btn-create-bib">
                <i class="fas fa-plus"></i>
                <?php echo $currentLang === 'th' ? 'สร้างรายการใหม่' : 'Create New'; ?>
            </a>
        </div>
    </div>

    <!-- Auto-deletion Notice (Toast Logic) -->
    <?php if (isset($_SESSION['cleanup_notified'])):
        $count = $_SESSION['cleanup_notified'];
        unset($_SESSION['cleanup_notified']); // Only show once
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toast.info('<?php echo $currentLang === 'th'
                                ? "นโยบายระบบ: ลบบรรณานุกรมที่ค้างเกิน 2 ปี จำนวน $count รายการอัตโนมัติ"
                                : "Policy Cleanup: $count items older than 2 years were automatically deleted."; ?>', {
                    duration: 6000
                });
            });
        </script>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="bib-toolbar slide-up stagger-1">
        <div class="bib-search-wrapper">
            <i class="fas fa-search bib-search-icon"></i>
            <input type="text" id="search-input" class="bib-search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาชื่อเรื่อง, ผู้แต่ง...' : 'Search title, author...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="bib-filter-select" id="filter-type">
            <option value=""><?php echo $currentLang === 'th' ? 'ทุกประเภท' : 'All Types'; ?></option>
            <?php foreach ($resourceTypes as $type): ?>
                <option value="<?php echo $type['id']; ?>" <?php echo $filterType == $type['id'] ? 'selected' : ''; ?>>
                    <?php echo $currentLang === 'th' ? $type['name_th'] : $type['name_en']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="bib-filter-select" id="filter-project">
            <option value=""><?php echo $currentLang === 'th' ? 'ทุกโครงการ' : 'All Projects'; ?></option>
            <?php foreach ($projects as $proj): ?>
                <option value="<?php echo $proj['id']; ?>" <?php echo $filterProject == $proj['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($proj['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="bib-filter-select" id="filter-sort">
            <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ล่าสุด' : 'Newest'; ?></option>
            <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'เก่าสุด' : 'Oldest'; ?></option>
            <option value="az" <?php echo $sortOrder === 'az' ? 'selected' : ''; ?>>A-Z</option>
        </select>

        <div class="bib-export-group">
            <button class="btn-export" onclick="exportBibliographies('docx')">
                <i class="fas fa-file-word" style="color: #2b579a;"></i> Word
            </button>
            <button class="btn-export" onclick="exportBibliographies('pdf')">
                <i class="fas fa-file-pdf" style="color: #f40f02;"></i> PDF
            </button>
        </div>
    </div>

    <!-- List Controls -->
    <div class="bib-list-controls slide-up stagger-2">
        <label class="bib-select-all-wrapper">
            <input type="checkbox" class="checkbox-animated" id="select-all-checkbox" onchange="toggleSelectAll(this.checked)">
            <span><?php echo $currentLang === 'th' ? 'เลือกทั้งหมด' : 'Select All'; ?></span>
        </label>

        <!-- View Mode Toggle -->
        <div class="bib-view-toggle">
            <button class="bib-view-btn active" data-view="list" onclick="setViewMode('list')" title="<?php echo $currentLang === 'th' ? 'แบบรายการ' : 'List View'; ?>">
                <i class="fas fa-list"></i>
                <span><?php echo $currentLang === 'th' ? 'รายการ' : 'List'; ?></span>
            </button>
            <button class="bib-view-btn" data-view="grid" onclick="setViewMode('grid')" title="<?php echo $currentLang === 'th' ? 'แบบตาราง' : 'Grid View'; ?>">
                <i class="fas fa-th-large"></i>
                <span><?php echo $currentLang === 'th' ? 'ตาราง' : 'Grid'; ?></span>
            </button>
            <button class="bib-view-btn" data-view="compact" onclick="setViewMode('compact')" title="<?php echo $currentLang === 'th' ? 'แบบกระชับ' : 'Compact View'; ?>">
                <i class="fas fa-bars"></i>
                <span><?php echo $currentLang === 'th' ? 'กระชับ' : 'Compact'; ?></span>
            </button>
        </div>

        <span class="bib-total-count"><?php echo $total; ?>/<?php echo MAX_BIBLIOGRAPHIES; ?> <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?></span>
    </div>

    <!-- Bibliography List -->
    <div class="bib-list-container">
        <?php if (empty($bibliographies)): ?>
            <div class="bib-empty-state slide-up stagger-3">
                <div class="bib-empty-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h4 class="empty-title"><?php echo __('no_bibliography'); ?></h4>
                <p class="empty-description"><?php echo $currentLang === 'th' ? 'ยังไม่มีบรรณานุกรม เริ่มสร้างรายการแรกของคุณ' : 'No bibliographies found. Start creating your first one'; ?></p>
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo __('create_bibliography'); ?>
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($bibliographies as $index => $bib): ?>
                <?php $rowNumber = $offset + $index + 1; ?>
                <div class="bib-item-card slide-up stagger-<?php echo min($index + 3, 10); ?>"
                    data-id="<?php echo $bib['id']; ?>"
                    data-parenthetical="<?php echo htmlspecialchars($bib['citation_parenthetical'], ENT_QUOTES); ?>"
                    data-narrative="<?php echo htmlspecialchars($bib['citation_narrative'], ENT_QUOTES); ?>"
                    data-raw='<?php echo htmlspecialchars($bib['data'], ENT_QUOTES); ?>'>

                    <span class="bib-item-number"><?php echo $rowNumber; ?></span>

                    <input type="checkbox" class="checkbox-animated bib-checkbox bib-item-checkbox" value="<?php echo $bib['id']; ?>" onchange="updateSelection()">

                    <div class="bib-item-content" onclick="viewBibliography(<?php echo $bib['id']; ?>)">
                        <?php
                        // Extract title from data
                        $bibData = json_decode($bib['data'], true);
                        $title = $bibData['title'] ?? $bibData['book_title'] ?? $bibData['article_title'] ?? $bibData['page_title'] ?? '';
                        if (!$title) {
                            $title = mb_substr(strip_tags($bib['bibliography_text']), 0, 50) . '...';
                        }
                        ?>
                        <span class="bib-item-title"><?php echo htmlspecialchars($title); ?></span>
                        <div class="bib-item-details"><?php echo $bib['bibliography_text']; ?></div>
                        <div class="bib-item-meta">
                            <span class="bib-tag bib-type">
                                <i class="fas <?php echo $bib['icon']; ?>"></i>
                                <?php echo $currentLang === 'th' ? $bib['name_th'] : $bib['name_en']; ?>
                            </span>
                            <?php if ($bib['project_name']): ?>
                                <span class="bib-tag bib-tag-project" style="color: <?php echo $bib['project_color']; ?>;">
                                    <i class="fas fa-folder"></i>
                                    <?php echo htmlspecialchars($bib['project_name']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="bib-tag bib-tag-project">
                                <i class="fas fa-calendar"></i>
                                <?php echo formatThaiDate($bib['created_at']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="bib-item-actions">
                        <button class="bib-action-btn" onclick="copyToClipboard(<?php echo htmlspecialchars(json_encode($bib['bibliography_text']), ENT_QUOTES); ?>, this)" title="<?php echo __('copy'); ?>">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="bib-action-btn view" onclick="viewBibliography(<?php echo $bib['id']; ?>)" title="<?php echo __('view'); ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="bib-action-btn" onclick="moveSingleItem(<?php echo $bib['id']; ?>)" title="<?php echo $currentLang === 'th' ? 'ย้ายไปโครงการ' : 'Move to Project'; ?>">
                            <i class="fas fa-folder-open"></i>
                        </button>
                        <button class="bib-action-btn" onclick="editBibliography(<?php echo $bib['id']; ?>)" title="<?php echo __('edit'); ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="bib-action-btn delete" onclick="deleteBibliography(<?php echo $bib['id']; ?>)" title="<?php echo __('delete'); ?>">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bib-pagination">
            <button class="bib-page-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-left"></i>
            </button>

            <?php
            // Smart pagination logic
            $showPages = [];

            // Always show first page
            $showPages[] = 1;

            // Show pages around current page
            for ($i = max(2, $page - 2); $i <= min($totalPages - 1, $page + 2); $i++) {
                $showPages[] = $i;
            }

            // Always show last page if more than 1 page
            if ($totalPages > 1) {
                $showPages[] = $totalPages;
            }

            // Remove duplicates and sort
            $showPages = array_unique($showPages);
            sort($showPages);

            $prevPage = 0;
            foreach ($showPages as $i):
                if ($prevPage > 0 && $i - $prevPage > 1):
            ?>
                    <span class="bib-page-ellipsis">...</span>
                <?php endif; ?>
                <button class="bib-page-btn <?php echo $i === $page ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)">
                    <?php echo $i; ?>
                </button>
            <?php
                $prevPage = $i;
            endforeach;
            ?>

            <button class="bib-page-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Page info -->
            <span class="bib-page-info">
                <?php echo $currentLang === 'th' ? 'หน้า' : 'Page'; ?> <?php echo $page; ?>/<?php echo $totalPages; ?>
            </span>
        </div>
    <?php endif; ?>
</main>

<!-- Bulk Action Bar -->
<div class="bib-bulk-actions-bar" id="bulk-actions">
    <span><?php echo $currentLang === 'th' ? 'เลือกแล้ว' : 'Selected'; ?> <span id="selected-count" class="bib-bulk-count">0</span> <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?></span>
    <button class="btn-bulk" onclick="openMoveModal()">
        <i class="fas fa-folder-open"></i>
        <?php echo $currentLang === 'th' ? 'ย้ายไปโครงการ' : 'Move to Project'; ?>
    </button>
    <button class="btn-bulk delete" onclick="bulkDelete()">
        <i class="fas fa-trash-can"></i>
        <?php echo $currentLang === 'th' ? 'ลบที่เลือก' : 'Delete Selected'; ?>
    </button>
</div>

<script>
    // View Mode Toggle
    function setViewMode(mode) {
        const container = document.querySelector('.bib-list-container');
        container.classList.remove('list-view', 'grid-view', 'compact-view');

        if (mode !== 'list') {
            container.classList.add(mode + '-view');
        }

        // Update active button
        document.querySelectorAll('.bib-view-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === mode) {
                btn.classList.add('active');
            }
        });

        // Save preference
        localStorage.setItem('bibViewMode', mode);
    }

    // Load saved view mode
    document.addEventListener('DOMContentLoaded', function() {
        const savedMode = localStorage.getItem('bibViewMode') || 'list';
        if (savedMode !== 'list') {
            setViewMode(savedMode);
        }
    });

    // Live Search - Client-side filtering
    let searchTimeout;
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();

        searchTimeout = setTimeout(() => {
            liveSearch(query);
        }, 150);
    });

    function liveSearch(query) {
        const items = document.querySelectorAll('.bib-item-card');
        let visibleCount = 0;

        items.forEach(item => {
            const title = item.querySelector('.bib-item-title')?.textContent.toLowerCase() || '';
            const details = item.querySelector('.bib-item-details')?.textContent.toLowerCase() || '';
            const type = item.querySelector('.bib-type')?.textContent.toLowerCase() || '';
            const project = item.querySelector('.bib-tag-project')?.textContent.toLowerCase() || '';

            const searchText = title + ' ' + details + ' ' + type + ' ' + project;

            if (query === '' || searchText.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Update visible count display
        const countDisplay = document.querySelector('.bib-total-count');
        if (countDisplay && query !== '') {
            countDisplay.innerHTML = `${visibleCount} <?php echo $currentLang === 'th' ? 'รายการที่พบ' : 'items found'; ?>`;
        } else if (countDisplay) {
            countDisplay.innerHTML = `<?php echo $total; ?>/<?php echo MAX_BIBLIOGRAPHIES; ?> <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?>`;
        }

        // Show/hide empty message
        const listContainer = document.querySelector('.bib-list-container');
        let noResultsMsg = document.getElementById('no-search-results');

        if (visibleCount === 0 && query !== '') {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'no-search-results';
                noResultsMsg.className = 'bib-empty-state';
                noResultsMsg.innerHTML = `
                    <div class="bib-empty-icon"><i class="fas fa-search"></i></div>
                    <h4 class="empty-title"><?php echo $currentLang === 'th' ? 'ไม่พบผลลัพธ์' : 'No results found'; ?></h4>
                    <p class="empty-description"><?php echo $currentLang === 'th' ? 'ลองค้นหาด้วยคำอื่น' : 'Try searching with different keywords'; ?></p>
                `;
                listContainer.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = '';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }

    // Filter selects still trigger page reload for server-side filtering
    document.getElementById('filter-type').addEventListener('change', applyFilters);
    document.getElementById('filter-project').addEventListener('change', applyFilters);
    document.getElementById('filter-sort').addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('search-input').value;
        const type = document.getElementById('filter-type').value;
        const project = document.getElementById('filter-project').value;
        const sort = document.getElementById('filter-sort').value;

        const url = new URL(window.location);
        url.searchParams.set('search', search);
        url.searchParams.set('type', type);
        url.searchParams.set('project', project);
        url.searchParams.set('sort', sort);
        url.searchParams.delete('page');

        window.location = url.toString();
    }

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function updateSelection() {
        const checkboxes = document.querySelectorAll('.bib-checkbox:checked');
        const count = checkboxes.length;
        const bulkActions = document.getElementById('bulk-actions');

        if (count > 0) {
            bulkActions.classList.add('active');
            document.getElementById('selected-count').textContent = count;
        } else {
            bulkActions.classList.remove('active');
        }

        // Update item card selected state
        document.querySelectorAll('.bib-item-card').forEach(card => {
            const checkbox = card.querySelector('.bib-checkbox');
            if (checkbox && checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }

    function toggleSelectAll(checked) {
        document.querySelectorAll('.bib-checkbox').forEach(cb => cb.checked = checked);
        updateSelection();
    }

    function viewBibliography(id) {
        const item = document.querySelector(`[data-id="${id}"]`);
        const bibText = item.querySelector('.bib-item-details').innerHTML;
        const rawData = JSON.parse(item.dataset.raw || '{}');
        const parenthetical = item.dataset.parenthetical || '';
        const narrative = item.dataset.narrative || '';
        const typeLabel = item.querySelector('.bib-type').innerText.trim();

        // Escape for safe use
        const safeBibText = bibText.replace(/`/g, '\\`').replace(/\$/g, '\\$').replace(/'/g, "\\'");
        const safeParenthetical = parenthetical.replace(/'/g, "\\'");
        const safeNarrative = narrative.replace(/'/g, "\\'");

        // Build author display
        let authorDisplay = '-';
        if (rawData.authors && rawData.authors.length > 0) {
            authorDisplay = rawData.authors.map(a => {
                if (a.condition === "1") return "<?php echo $currentLang === 'th' ? 'ไม่ระบุชื่อ' : 'Anonymous'; ?>";
                if (a.condition === "2" || a.condition === "7") return a.conditionValue || '';
                if (a.condition === "3" || a.condition === "4") return `${a.conditionValue || ''}${a.firstName || ''} ${a.lastName || ''}`.trim();
                return `${a.firstName || ''} ${a.lastName || ''}`.trim();
            }).filter(a => a).join(", ") || '-';
        }

        // Build detail rows
        let detailRows = '';
        const fieldConfig = {
            'title': '<?php echo $currentLang === "th" ? "ชื่อเรื่อง" : "Title"; ?>',
            'book_title': '<?php echo $currentLang === "th" ? "ชื่อหนังสือ" : "Book Title"; ?>',
            'article_title': '<?php echo $currentLang === "th" ? "ชื่อบทความ" : "Article Title"; ?>',
            'year': '<?php echo $currentLang === "th" ? "ปีที่พิมพ์" : "Year"; ?>',
            'publisher': '<?php echo $currentLang === "th" ? "สำนักพิมพ์" : "Publisher"; ?>',
            'journal_name': '<?php echo $currentLang === "th" ? "ชื่อวารสาร" : "Journal"; ?>',
            'volume': '<?php echo $currentLang === "th" ? "เล่มที่" : "Volume"; ?>',
            'issue': '<?php echo $currentLang === "th" ? "ฉบับที่" : "Issue"; ?>',
            'pages': '<?php echo $currentLang === "th" ? "เลขหน้า" : "Pages"; ?>',
            'edition': '<?php echo $currentLang === "th" ? "ครั้งที่พิมพ์" : "Edition"; ?>',
            'url': 'URL',
            'doi': 'DOI',
            'institution': '<?php echo $currentLang === "th" ? "สถาบัน" : "Institution"; ?>',
            'website_name': '<?php echo $currentLang === "th" ? "ชื่อเว็บไซต์" : "Website"; ?>',
            'platform': '<?php echo $currentLang === "th" ? "แพลตฟอร์ม" : "Platform"; ?>',
            'channel_name': '<?php echo $currentLang === "th" ? "ชื่อช่อง" : "Channel"; ?>'
        };

        for (const [key, label] of Object.entries(fieldConfig)) {
            if (rawData[key]) {
                const val = (key === 'url' || key === 'doi') ?
                    `<a href="${rawData[key]}" target="_blank" style="color: var(--primary); text-decoration: underline;">${rawData[key]}</a>` :
                    rawData[key];
                detailRows += `
                    <div style="display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                        <div style="width: 140px; font-weight: 600; color: var(--primary); flex-shrink: 0;">${label}</div>
                        <div style="color: #4A4A4A; line-height: 1.5; flex-grow: 1;">${val}</div>
                    </div>
                `;
            }
        }

        const modalStyles = `
            <style>
                .view-modal-content { font-family: 'Sarabun', sans-serif; }
                .view-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
                .view-row:last-child { border-bottom: none; }
                .view-label { width: 140px; font-weight: 600; color: var(--primary); flex-shrink: 0; font-size: 14px; }
                .view-value { color: #4A4A4A; line-height: 1.6; flex-grow: 1; font-size: 14px; }
                .bib-box { background: #F9F9F9; border: 1px solid #E0E0E0; border-radius: 8px; padding: 16px; font-size: 14px; line-height: 1.7; color: #333; margin-bottom: 16px; }
                .citation-box { display: flex; gap: 12px; margin-bottom: 20px; }
                .citation-card { flex: 1; background: #f8f9ff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 14px; }
                .citation-card-label { font-size: 11px; font-weight: 600; color: #333; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
                .citation-card-value { font-size: 14px; font-weight: 500; color: #333; }
                .citation-card .copy-btn { background: #eee; border: none; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; color: #1a1a1a; margin-left: auto; }
                .citation-card .copy-btn:hover { background: var(--primary); color: white; }
                .divider { border-bottom: 1px dashed #ddd; margin: 16px 0; }
            </style>
        `;

        Modal.create({
            title: '<?php echo $currentLang === "th" ? "รายละเอียดบรรณานุกรม" : "Bibliography Details"; ?>',
            size: "lg",
            content: `
                ${modalStyles}
                <div class="view-modal-content">
                    <!-- Bibliography Box -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12px; font-weight: 600; color: #333; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-quote-left" style="color: var(--primary);"></i>
                            <?php echo $currentLang === "th" ? "รูปแบบบรรณานุกรม" : "Bibliography Format"; ?>
                            <span style="margin-left: auto; font-size: 11px; background: var(--primary-light); color: var(--primary); padding: 2px 8px; border-radius: 4px; font-weight: 500;">${typeLabel}</span>
                        </div>
                        <div class="bib-box bibliography-preview">${bibText}</div>
                    </div>

                    <!-- Citation Cards -->
                    <div class="citation-box">
                        <div class="citation-card">
                            <div class="citation-card-label">
                                <i class="fas fa-brackets" style="color: #3b82f6;"></i>
                                <?php echo $currentLang === "th" ? "อ้างอิงแบบวงเล็บ" : "Parenthetical"; ?>
                                <button class="copy-btn" onclick="copyToClipboard('${safeParenthetical}', this)" title="<?php echo __('copy'); ?>">
                                    <i class="fas fa-copy" style="font-size: 11px;"></i>
                                </button>
                            </div>
                            <div class="citation-card-value">${parenthetical || '-'}</div>
                        </div>
                        <div class="citation-card">
                            <div class="citation-card-label">
                                <i class="fas fa-pen" style="color: #10b981;"></i>
                                <?php echo $currentLang === "th" ? "อ้างอิงแบบเนื้อความ" : "Narrative"; ?>
                                <button class="copy-btn" onclick="copyToClipboard('${safeNarrative}', this)" title="<?php echo __('copy'); ?>">
                                    <i class="fas fa-copy" style="font-size: 11px;"></i>
                                </button>
                            </div>
                            <div class="citation-card-value">${narrative || '-'}</div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Author Row -->
                    <div class="view-row">
                        <div class="view-label"><i class="fas fa-user" style="margin-right: 6px; font-size: 12px;"></i><?php echo $currentLang === "th" ? "ผู้แต่ง" : "Author"; ?></div>
                        <div class="view-value">${authorDisplay}</div>
                    </div>

                    <!-- Detail Rows -->
                    ${detailRows}

                    <!-- Type Row -->
                    <div class="view-row">
                        <div class="view-label"><i class="fas fa-tag" style="margin-right: 6px; font-size: 12px;"></i><?php echo $currentLang === "th" ? "ประเภท" : "Type"; ?></div>
                        <div class="view-value">${typeLabel}</div>
                    </div>
                </div>
            `,
            footer: `
                <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)">
                        <?php echo __("close"); ?>
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-secondary" onclick="copyToClipboard(\`${safeBibText}\`, this)">
                            <i class="fas fa-copy"></i> <?php echo $currentLang === "th" ? "คัดลอก" : "Copy"; ?>
                        </button>
                        <button class="btn btn-primary" onclick="editBibliography(${id})">
                            <i class="fas fa-edit"></i> <?php echo __("edit"); ?>
                        </button>
                    </div>
                </div>
            `
        });
    }

    function editBibliography(id) {
        window.location.href = '<?php echo SITE_URL; ?>/generate.php?edit=' + id;
    }

    function deleteBibliography(id) {
        Modal.confirm({
            title: '<?php echo __('delete_bibliography'); ?>',
            message: '<?php echo __('delete_confirm'); ?>',
            confirmText: '<?php echo __('delete'); ?>',
            cancelText: '<?php echo __('cancel'); ?>',
            danger: true,
            onConfirm: async () => {
                try {
                    const response = await API.delete('<?php echo SITE_URL; ?>/api/bibliography/delete.php', {
                        id
                    });
                    if (response.success) {
                        Toast.success('<?php echo __('delete_success'); ?>');
                        document.querySelector(`[data-id="${id}"]`).remove();
                    }
                } catch (e) {
                    Toast.error('<?php echo __('error_delete'); ?>');
                }
            }
        });
    }

    function bulkDelete() {
        const ids = Array.from(document.querySelectorAll('.bib-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        Modal.confirm({
            title: '<?php echo $currentLang === 'th' ? 'ลบหลายรายการ' : 'Delete Multiple'; ?>',
            message: '<?php echo $currentLang === 'th' ? 'คุณต้องการลบ' : 'Are you sure you want to delete'; ?> ' + ids.length + ' <?php echo $currentLang === 'th' ? 'รายการ?' : 'items?'; ?>',
            confirmText: '<?php echo __('delete'); ?>',
            cancelText: '<?php echo __('cancel'); ?>',
            danger: true,
            onConfirm: async () => {
                try {
                    const response = await API.delete('<?php echo SITE_URL; ?>/api/bibliography/delete.php', {
                        ids
                    });
                    if (response.success) {
                        Toast.success('<?php echo __('delete_success'); ?>');
                        location.reload();
                    }
                } catch (e) {
                    Toast.error('<?php echo __('error_delete'); ?>');
                }
            }
        });
    }

    // Move single item to project
    let singleMoveItemId = null;

    function moveSingleItem(id) {
        singleMoveItemId = id;
        const projects = <?php echo json_encode($projects); ?>;
        const projectOptions = projects.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

        Modal.create({
            title: '<?php echo $currentLang === "th" ? "ย้ายไปยังโครงการ" : "Move to Project"; ?>',
            content: `
                <p style="color: var(--text-secondary); margin-bottom: 20px;"><?php echo $currentLang === "th" ? "เลือกโครงการปลายทางที่ต้องการย้ายรายการนี้ไป:" : "Select the destination project:"; ?></p>
                <div class="form-group">
                    <label class="form-label"><?php echo $currentLang === "th" ? "เลือกโครงการ" : "Select Project"; ?></label>
                    <select class="form-select" id="single-target-project">
                        <option value=""><?php echo $currentLang === "th" ? "-- ไม่มีโครงการ --" : "-- No Project --"; ?></option>
                        ${projectOptions}
                    </select>
                </div>
            `,
            footer: `
                <button class="btn btn-secondary" onclick="Modal.close(this)"><?php echo __("cancel"); ?></button>
                <button class="btn btn-primary" onclick="confirmMoveSingle()">
                    <i class="fas fa-folder-open"></i> <?php echo $currentLang === "th" ? "ยืนยันการย้าย" : "Confirm Move"; ?>
                </button>
            `
        });
    }

    async function confirmMoveSingle() {
        if (!singleMoveItemId) return;

        const projectId = document.getElementById('single-target-project').value;

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/bibliography/move.php', {
                ids: [String(singleMoveItemId)],
                project_id: projectId ? projectId : null
            });
            if (response.success) {
                Toast.success('<?php echo $currentLang === "th" ? "ย้ายรายการสำเร็จ" : "Item moved successfully"; ?>');
                // Close modal by removing overlay
                document.querySelector('.modal-overlay')?.remove();
                location.reload();
            } else {
                Toast.error(response.error || '<?php echo $currentLang === "th" ? "เกิดข้อผิดพลาด" : "An error occurred"; ?>');
            }
        } catch (e) {
            console.error('Move error:', e);
            Toast.error('<?php echo $currentLang === "th" ? "เกิดข้อผิดพลาด" : "An error occurred"; ?>');
        }
    }

    function openMoveModal() {
        const ids = Array.from(document.querySelectorAll('.bib-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        const projects = <?php echo json_encode($projects); ?>;
        const projectOptions = projects.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

        Modal.create({
            title: '<?php echo $currentLang === "th" ? "ย้ายไปยังโครงการ" : "Move to Project"; ?>',
            content: `
                <p style="color: var(--text-secondary); margin-bottom: 20px;"><?php echo $currentLang === "th" ? "เลือกโครงการปลายทางที่ต้องการย้ายรายการที่เลือกไป:" : "Select the destination project:"; ?></p>
                <div class="form-group">
                    <label class="form-label"><?php echo $currentLang === "th" ? "เลือกโครงการ" : "Select Project"; ?></label>
                    <select class="form-select" id="target-project-select">
                        <option value=""><?php echo $currentLang === "th" ? "-- ไม่มีโครงการ --" : "-- No Project --"; ?></option>
                        ${projectOptions}
                    </select>
                </div>
            `,
            footer: `
                <button class="btn btn-secondary" onclick="Modal.close(this)"><?php echo __("cancel"); ?></button>
                <button class="btn btn-primary" onclick="confirmMove()">
                    <i class="fas fa-folder-open"></i> <?php echo $currentLang === "th" ? "ยืนยันการย้าย" : "Confirm Move"; ?>
                </button>
            `
        });
    }

    async function confirmMove() {
        const ids = Array.from(document.querySelectorAll('.bib-checkbox:checked')).map(cb => cb.value);
        const projectId = document.getElementById('target-project-select').value;

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/bibliography/move.php', {
                ids,
                project_id: projectId || null
            });
            if (response.success) {
                Toast.success('<?php echo $currentLang === "th" ? "ย้ายรายการสำเร็จ" : "Items moved successfully"; ?>');
                location.reload();
            }
        } catch (e) {
            Toast.error('<?php echo $currentLang === "th" ? "เกิดข้อผิดพลาด" : "An error occurred"; ?>');
        }
    }

    function exportBibliographies(format) {
        const isEn = document.body.classList.contains('lang-en');

        // Redirect to projects page with export format parameter
        const projectsUrl = '<?php echo SITE_URL; ?>/users/projects.php?export=' + format;

        // Store message in sessionStorage to show after redirect
        const message = isEn ?
            'Please select a project to export as ' + format.toUpperCase() :
            'โปรดเลือกโครงการที่จะบันทึกเป็น ' + format.toUpperCase();
        sessionStorage.setItem('exportMessage', message);
        sessionStorage.setItem('exportFormat', format);

        window.location.href = projectsUrl;
    }

    function showRetentionPolicy() {
        Toast.info('<?php echo $currentLang === 'th'
                        ? "นโยบายระบบ: บรรณานุกรมจะถูกเก็บไว้เป็นเวลา 2 ปี นับจากวันสร้าง และจะถูกลบออกอัตโนมัติหลังจากนั้น"
                        : "System Policy: Bibliographies are stored for 2 years from creation and will be automatically deleted thereafter."; ?>', {
            duration: 8000
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>