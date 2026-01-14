<?php

/**
 * Babybib - Projects Page (New Design)
 * =====================================
 */

$pageTitle = 'โครงการ';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();

// Search, Filter and Pagination
$search = sanitize($_GET['search'] ?? '');
$sortOrder = sanitize($_GET['sort'] ?? 'newest');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

try {
    $db = getDB();

    $where = "p.user_id = ?";
    $params = [$userId];

    if ($search) {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects p WHERE $where");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Sort order
    $orderBy = "p.created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "p.created_at ASC";
    } elseif ($sortOrder === 'az') {
        $orderBy = "p.name ASC";
    }

    // Get projects with pagination
    $stmt = $db->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM bibliographies WHERE project_id = p.id) as bib_count
        FROM projects p 
        WHERE $where
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $total = 0;
    $totalPages = 0;
}

$colors = ['#8B5CF6', '#EF4444', '#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#6366F1', '#14B8A6'];
?>

<style>
    /* === Projects Page Design (matching bibliography-list) === */
    .proj-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4);
    }

    /* Header Section */
    .proj-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: var(--space-5);
    }

    .proj-header-text {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .proj-header-icon {
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

    .proj-header-info h1 {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .proj-header-info p {
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }

    .btn-create-proj {
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
    }

    .btn-create-proj:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }

    /* Toolbar */
    .proj-toolbar {
        display: flex;
        gap: var(--space-3);
        align-items: center;
        margin-bottom: var(--space-5);
        flex-wrap: wrap;
    }

    .proj-search-wrapper {
        position: relative;
        flex-grow: 1;
        max-width: 350px;
    }

    .proj-search-input {
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

    .proj-search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: var(--shadow-primary);
    }

    .proj-search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
    }

    .proj-total-count {
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        padding: var(--space-1) var(--space-3);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
    }

    .proj-filter-select {
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
        min-width: 160px;
        transition: all var(--transition);
    }

    .proj-filter-select:focus {
        outline: none;
        border-color: var(--primary);
        background-color: var(--white);
        box-shadow: var(--shadow-primary);
    }

    /* View Mode Toggle */
    .proj-view-toggle {
        display: flex;
        gap: 2px;
        background: var(--gray-100);
        padding: 3px;
        border-radius: var(--radius-lg);
        margin-left: auto;
    }

    .proj-view-btn {
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

    .proj-view-btn:hover {
        color: var(--text-primary);
    }

    .proj-view-btn.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    /* Projects Grid */
    .proj-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-4);
    }

    /* List View */
    .proj-grid.list-view {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
    }

    .proj-grid.list-view .proj-card {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .proj-grid.list-view .proj-card-header {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .proj-grid.list-view .proj-card-description {
        flex-grow: 1;
        margin-bottom: 0;
    }

    .proj-grid.list-view .proj-card-meta {
        margin-bottom: 0;
    }

    .proj-grid.list-view .proj-card-footer {
        border-top: none;
        padding-top: 0;
        flex-shrink: 0;
    }

    /* Compact View */
    .proj-grid.compact-view {
        display: flex;
        flex-direction: column;
        gap: var(--space-1);
    }

    .proj-grid.compact-view .proj-card {
        padding: var(--space-3) var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .proj-grid.compact-view .proj-card::before {
        width: 3px;
    }

    .proj-grid.compact-view .proj-card-number {
        width: 24px;
        height: 24px;
        min-width: 24px;
        font-size: 10px;
    }

    .proj-grid.compact-view .proj-card-title {
        font-size: var(--text-sm);
    }

    .proj-grid.compact-view .proj-card-description,
    .proj-grid.compact-view .proj-card-meta,
    .proj-grid.compact-view .proj-card-footer {
        display: none;
    }

    .proj-grid.compact-view .proj-card-actions {
        opacity: 1;
        margin-left: auto;
    }

    /* Project Card */
    .proj-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.03);
        padding: var(--space-5);
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .proj-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--project-color, var(--primary));
    }

    .proj-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .proj-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--space-3);
    }

    .proj-card-title-group {
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .proj-card-number {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        background: var(--project-color, var(--primary));
        color: var(--white);
        font-size: var(--text-xs);
        font-weight: 700;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .proj-card-title {
        font-size: var(--text-lg);
        font-weight: 600;
        color: var(--text-primary);
    }

    .proj-card-actions {
        display: flex;
        gap: var(--space-1);
        opacity: 0.4;
        transition: opacity var(--transition);
    }

    .proj-card:hover .proj-card-actions {
        opacity: 1;
    }

    .proj-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition);
        font-size: var(--text-xs);
    }

    .proj-action-btn:hover {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
        transform: scale(1.1);
    }

    .proj-action-btn.delete:hover {
        background: var(--danger);
        border-color: var(--danger);
    }

    .proj-action-btn.view:hover {
        background: var(--info);
        border-color: var(--info);
    }

    .proj-card-description {
        color: var(--text-secondary);
        font-size: var(--text-sm);
        line-height: 1.5;
        margin-bottom: var(--space-3);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .proj-card-meta {
        display: flex;
        gap: var(--space-3);
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        margin-bottom: var(--space-4);
    }

    .proj-card-meta span {
        display: flex;
        align-items: center;
        gap: var(--space-1);
    }

    .proj-card-meta i {
        font-size: 10px;
    }

    .proj-card-footer {
        display: flex;
        gap: var(--space-2);
        padding-top: var(--space-3);
        border-top: 1px solid var(--border-light);
    }

    .proj-export-btn {
        flex: 1;
        padding: var(--space-2);
        border-radius: var(--radius);
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        font-size: var(--text-xs);
        font-weight: 500;
        transition: all var(--transition);
    }

    .proj-export-btn:hover {
        background: var(--gray-50);
        transform: translateY(-1px);
    }

    .proj-export-btn.word i {
        color: #2b579a;
    }

    .proj-export-btn.pdf i {
        color: #f40f02;
    }

    /* Pulse animation for highlighted buttons */
    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .proj-export-btn.highlight {
        animation: pulse 1s ease-in-out 3;
        box-shadow: 0 0 15px var(--primary);
    }

    /* Export Preview Modal */
    .preview-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 30px;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        overflow-y: auto;
    }

    .preview-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .preview-container {
        background: var(--gray-100);
        border-radius: 16px;
        max-width: 900px;
        width: 100%;
        max-height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
        transform: translateY(20px) scale(0.95);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        overflow: hidden;
    }

    .preview-overlay.active .preview-container {
        transform: translateY(0) scale(1);
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        background: var(--white);
        border-bottom: 1px solid var(--border-light);
    }

    .preview-header h3 {
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-primary);
    }

    .preview-header h3 i {
        margin-right: 10px;
        color: var(--primary);
    }

    .preview-close-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: var(--gray-100);
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .preview-close-btn:hover {
        background: var(--danger);
        color: white;
    }

    .preview-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        justify-content: center;
    }

    /* A4-like Paper */
    .preview-paper {
        background: white;
        width: 100%;
        max-width: 650px;
        min-height: 800px;
        padding: 60px 60px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 4px;
        font-family: 'Angsana New', 'TH Sarabun New', 'Sarabun', serif;
        font-size: 16px;
        line-height: 1.5;
    }

    .preview-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .preview-entries {
        margin-top: 0;
    }

    .preview-entry {
        text-indent: -0.5in;
        padding-left: 0.5in;
        margin-bottom: 8px;
        font-size: 16px;
    }

    .preview-entry em,
    .preview-entry i {
        font-style: italic;
    }

    .preview-empty {
        text-align: center;
        color: var(--text-tertiary);
        padding: 40px;
    }

    .preview-loading {
        text-align: center;
        padding: 60px;
        color: var(--text-secondary);
    }

    .preview-loading i {
        font-size: 32px;
        color: var(--primary);
        margin-bottom: 16px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .preview-footer {
        display: flex;
        justify-content: center;
        gap: 16px;
        padding: 20px 24px;
        background: var(--white);
        border-top: 1px solid var(--border-light);
    }

    .preview-download-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        border: none;
        font-family: inherit;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .preview-download-btn.word {
        background: linear-gradient(135deg, #2b5797, #1e3f6f);
        color: white;
    }

    .preview-download-btn.word:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(43, 87, 151, 0.4);
    }

    .preview-download-btn.pdf {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .preview-download-btn.pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }

    .preview-count {
        background: var(--gray-100);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    /* Empty State */
    .proj-empty-state {
        text-align: center;
        padding: var(--space-12);
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
        grid-column: 1 / -1;
    }

    .proj-empty-icon {
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

    /* Responsive */
    @media (max-width: 768px) {
        .proj-page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--space-4);
        }

        .btn-create-proj {
            width: 100%;
            justify-content: center;
        }

        .proj-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Pagination */
    .bib-pagination {
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
</style>

<main class="proj-page-wrapper">
    <!-- Header -->
    <div class="proj-page-header slide-up">
        <div class="proj-header-text">
            <div class="proj-header-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="proj-header-info">
                <h1><?php echo __('projects'); ?></h1>
                <p><?php echo $currentLang === 'th' ? 'จัดการโครงการเพื่อจัดกลุ่มบรรณานุกรม' : 'Manage projects to organize bibliographies'; ?></p>
            </div>
        </div>
        <button class="btn-create-proj" onclick="showCreateProjectModal()">
            <i class="fas fa-plus"></i>
            <?php echo $currentLang === 'th' ? 'สร้างโครงการใหม่' : 'Create Project'; ?>
        </button>
    </div>

    <!-- Toolbar -->
    <div class="proj-toolbar slide-up stagger-1">
        <div class="proj-search-wrapper">
            <i class="fas fa-search proj-search-icon"></i>
            <input type="text" id="search-input" class="proj-search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาโครงการ...' : 'Search projects...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="proj-filter-select" id="sort-order">
            <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ใหม่ล่าสุด' : 'Newest First'; ?></option>
            <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'เก่าที่สุด' : 'Oldest First'; ?></option>
            <option value="az" <?php echo $sortOrder === 'az' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ชื่อโครงการ (ก-ฮ)' : 'Project Name (A-Z)'; ?></option>
        </select>

        <!-- View Mode Toggle -->
        <div class="proj-view-toggle">
            <button class="proj-view-btn active" data-view="grid" onclick="setViewMode('grid')" title="<?php echo $currentLang === 'th' ? 'แบบตาราง' : 'Grid View'; ?>">
                <i class="fas fa-th-large"></i>
                <span><?php echo $currentLang === 'th' ? 'ตาราง' : 'Grid'; ?></span>
            </button>
            <button class="proj-view-btn" data-view="list" onclick="setViewMode('list')" title="<?php echo $currentLang === 'th' ? 'แบบรายการ' : 'List View'; ?>">
                <i class="fas fa-list"></i>
                <span><?php echo $currentLang === 'th' ? 'รายการ' : 'List'; ?></span>
            </button>
            <button class="proj-view-btn" data-view="compact" onclick="setViewMode('compact')" title="<?php echo $currentLang === 'th' ? 'แบบกระชับ' : 'Compact View'; ?>">
                <i class="fas fa-bars"></i>
                <span><?php echo $currentLang === 'th' ? 'กระชับ' : 'Compact'; ?></span>
            </button>
        </div>

        <span class="proj-total-count"><?php echo $total; ?>/<?php echo MAX_PROJECTS; ?> <?php echo $currentLang === 'th' ? 'โครงการ' : 'projects'; ?></span>
    </div>

    <!-- Projects Grid -->
    <div class="proj-grid">
        <?php if (empty($projects)): ?>
            <div class="proj-empty-state slide-up stagger-2">
                <div class="proj-empty-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h4 class="empty-title"><?php echo __('no_project'); ?></h4>
                <p class="empty-description"><?php echo $currentLang === 'th' ? 'สร้างโครงการเพื่อจัดกลุ่มบรรณานุกรม' : 'Create a project to organize bibliographies'; ?></p>
                <button class="btn btn-primary" onclick="showCreateProjectModal()">
                    <i class="fas fa-plus"></i>
                    <?php echo __('create_project'); ?>
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $index => $project): ?>
                <?php $projectNumber = $index + 1; ?>
                <div class="proj-card slide-up stagger-<?php echo min($index + 2, 8); ?>"
                    style="--project-color: <?php echo $project['color']; ?>;"
                    data-id="<?php echo $project['id']; ?>">

                    <div class="proj-card-header">
                        <div class="proj-card-title-group">
                            <span class="proj-card-number"><?php echo $projectNumber; ?></span>
                            <h3 class="proj-card-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                        </div>
                        <div class="proj-card-actions">
                            <button class="proj-action-btn view" onclick="viewProject(<?php echo $project['id']; ?>)" title="<?php echo __('view'); ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="proj-action-btn" onclick="editProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars($project['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES); ?>', '<?php echo $project['color']; ?>')" title="<?php echo __('edit'); ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="proj-action-btn delete" onclick="deleteProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars($project['name'], ENT_QUOTES); ?>')" title="<?php echo __('delete'); ?>">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <p class="proj-card-description"><?php echo htmlspecialchars($project['description'] ?: ($currentLang === 'th' ? 'ไม่มีคำอธิบาย' : 'No description')); ?></p>

                    <div class="proj-card-meta">
                        <span>
                            <i class="fas fa-book"></i>
                            <?php echo $project['bib_count']; ?> <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar"></i>
                            <?php echo formatThaiDate($project['created_at']); ?>
                        </span>
                    </div>

                    <div class="proj-card-footer">
                        <a href="project-preview.php?id=<?php echo $project['id']; ?>&format=docx" class="proj-export-btn word">
                            <i class="fas fa-file-word"></i> Word
                        </a>
                        <a href="project-preview.php?id=<?php echo $project['id']; ?>&format=pdf" class="proj-export-btn pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <a href="project-preview.php?id=<?php echo $project['id']; ?>" class="proj-export-btn">
                            <i class="fas fa-eye"></i> <?php echo $currentLang === 'th' ? 'ดูตัวอย่าง' : 'Preview'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bib-pagination" style="margin-top: var(--space-8);">
            <button class="bib-page-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-left"></i>
            </button>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <button class="bib-page-btn <?php echo $i === $page ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>

            <button class="bib-page-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    <?php endif; ?>
</main>

<!-- Export Preview Modal -->
<div class="preview-overlay" id="export-preview-modal">
    <div class="preview-container">
        <div class="preview-header">
            <h3>
                <i class="fas fa-file-alt"></i>
                <span id="preview-title"><?php echo $currentLang === 'th' ? 'ตัวอย่างบรรณานุกรม' : 'Bibliography Preview'; ?></span>
            </h3>
            <button class="preview-close-btn" onclick="closePreviewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="preview-body">
            <div class="preview-paper" id="preview-paper">
                <div class="preview-loading" id="preview-loading">
                    <i class="fas fa-spinner"></i>
                    <div><?php echo $currentLang === 'th' ? 'กำลังโหลด...' : 'Loading...'; ?></div>
                </div>
            </div>
        </div>
        <div class="preview-footer">
            <span class="preview-count" id="preview-count">0 <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?></span>
            <button class="preview-download-btn word" id="btn-download-word" onclick="downloadExport('docx')">
                <i class="fas fa-file-word"></i> <?php echo $currentLang === 'th' ? 'ดาวน์โหลด Word' : 'Download Word'; ?>
            </button>
            <button class="preview-download-btn pdf" id="btn-download-pdf" onclick="downloadExport('pdf')">
                <i class="fas fa-file-pdf"></i> <?php echo $currentLang === 'th' ? 'พิมพ์ PDF' : 'Print PDF'; ?>
            </button>
        </div>
    </div>
</div>

<script>
    const colors = <?php echo json_encode($colors); ?>;
    const canCreateProject = <?php echo canCreateProject($userId) ? 'true' : 'false'; ?>;

    // View Mode Toggle
    function setViewMode(mode) {
        const container = document.querySelector('.proj-grid');
        container.classList.remove('grid-view', 'list-view', 'compact-view');

        if (mode !== 'grid') {
            container.classList.add(mode + '-view');
        }

        // Update active button
        document.querySelectorAll('.proj-view-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === mode) {
                btn.classList.add('active');
            }
        });

        // Save preference
        localStorage.setItem('projViewMode', mode);
    }

    // Load saved view mode
    document.addEventListener('DOMContentLoaded', function() {
        const savedMode = localStorage.getItem('projViewMode') || 'grid';
        if (savedMode !== 'grid') {
            setViewMode(savedMode);
        }

        // Check if redirected from bibliography-list for export
        const exportMessage = sessionStorage.getItem('exportMessage');
        const exportFormat = sessionStorage.getItem('exportFormat');

        if (exportMessage) {
            // Show info toast
            Toast.info(exportMessage, {
                title: exportFormat === 'docx' ? 'Export Word' : 'Export PDF'
            });

            // Clear the message so it doesn't show again on refresh
            sessionStorage.removeItem('exportMessage');
            sessionStorage.removeItem('exportFormat');

            // Highlight export buttons on project cards
            document.querySelectorAll('.proj-card').forEach(card => {
                const exportBtn = card.querySelector(exportFormat === 'docx' ? '.proj-export-btn.word' : '.proj-export-btn.pdf');
                if (exportBtn) {
                    exportBtn.classList.add('highlight');
                }
            });
        }
    });

    // Event Listeners for search and filters
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    document.getElementById('sort-order').addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('search-input').value;
        const sort = document.getElementById('sort-order').value;

        const url = new URL(window.location);
        url.searchParams.set('search', search);
        url.searchParams.set('sort', sort);
        url.searchParams.delete('page');

        window.location = url.toString();
    }

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function showCreateProjectModal() {
        if (!canCreateProject) {
            Toast.warning('<?php echo $currentLang === 'th' ? 'คุณสร้างโครงการถึงขีดจำกัดแล้ว' : 'You have reached the project limit'; ?>');
            return;
        }

        const colorOptions = colors.map(c =>
            `<button type="button" class="color-option" style="background: ${c};" onclick="selectColor(this, '${c}')"></button>`
        ).join('');

        Modal.create({
            title: '<?php echo __('create_project'); ?>',
            content: `
            <form id="create-project-form">
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_name'); ?><span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" required placeholder="<?php echo $currentLang === 'th' ? 'ชื่อโครงการ' : 'Project name'; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_description'); ?></label>
                    <textarea name="description" class="form-input form-textarea" rows="3" placeholder="<?php echo $currentLang === 'th' ? 'รายละเอียดโครงการ (ไม่บังคับ)' : 'Description (optional)'; ?>"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_color'); ?></label>
                    <div class="color-picker">${colorOptions}</div>
                    <input type="hidden" name="color" value="#8B5CF6">
                </div>
            </form>
        `,
            footer: `
            <button class="btn btn-secondary" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
            <button class="btn btn-primary" onclick="createProject()"><?php echo __('create'); ?></button>
        `
        });

        setTimeout(() => {
            document.querySelector('.color-option').classList.add('selected');
        }, 100);
    }

    function selectColor(el, color) {
        document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.querySelector('input[name="color"]').value = color;
    }

    async function createProject() {
        const form = document.getElementById('create-project-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.name.trim()) {
            Toast.error('<?php echo $currentLang === 'th' ? 'กรุณากรอกชื่อโครงการ' : 'Please enter project name'; ?>');
            return;
        }

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/projects/create.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'สร้างโครงการสำเร็จ' : 'Project created'; ?>');
                location.reload();
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        }
    }

    function viewProject(id) {
        window.location.href = '<?php echo SITE_URL; ?>/users/bibliography-list.php?project=' + id;
    }

    function editProject(id, name, description, color) {
        const colorOptions = colors.map(c =>
            `<button type="button" class="color-option ${c === color ? 'selected' : ''}" style="background: ${c};" onclick="selectColor(this, '${c}')"></button>`
        ).join('');

        Modal.create({
            title: '<?php echo $currentLang === 'th' ? 'แก้ไขโครงการ' : 'Edit Project'; ?>',
            content: `
            <form id="edit-project-form">
                <input type="hidden" name="id" value="${id}">
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_name'); ?><span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" required value="${name}">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_description'); ?></label>
                    <textarea name="description" class="form-input form-textarea" rows="3">${description}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('project_color'); ?></label>
                    <div class="color-picker">${colorOptions}</div>
                    <input type="hidden" name="color" value="${color}">
                </div>
            </form>
        `,
            footer: `
            <button class="btn btn-secondary" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
            <button class="btn btn-primary" onclick="updateProject()"><?php echo __('save'); ?></button>
        `
        });
    }

    async function updateProject() {
        const form = document.getElementById('edit-project-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.name.trim()) {
            Toast.error('<?php echo $currentLang === 'th' ? 'กรุณากรอกชื่อโครงการ' : 'Please enter project name'; ?>');
            return;
        }

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/projects/update.php', data);
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'อัปเดตโครงการสำเร็จ' : 'Project updated'; ?>');
                location.reload();
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        }
    }

    function deleteProject(id, projectName) {
        const isEn = document.body.classList.contains('lang-en');

        Modal.create({
            title: isEn ? 'Delete Project' : 'ลบโครงการ',
            icon: 'fas fa-exclamation-triangle',
            content: `
                <div style="text-align: left;">
                    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <i class="fas fa-exclamation-circle" style="color: var(--danger); font-size: 20px; margin-top: 2px;"></i>
                            <div>
                                <strong style="color: var(--danger);">${isEn ? 'Warning!' : 'คำเตือน!'}</strong>
                                <p style="margin: 8px 0 0; color: #991B1B; font-size: 0.9rem; line-height: 1.5;">
                                    ${isEn 
                                        ? 'All bibliographies saved in this project will be permanently removed. This action cannot be undone.'
                                        : 'รายการบรรณานุกรมทั้งหมดที่ถูกบันทึกไว้ในโครงการนี้จะถูกลบออกอย่างถาวร การกระทำนี้ไม่สามารถย้อนกลับได้'}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p style="margin-bottom: 12px; color: var(--text-secondary);">
                        ${isEn 
                            ? 'To confirm deletion, please type the project name:' 
                            : 'เพื่อยืนยันการลบ กรุณาพิมพ์ชื่อโครงการ:'}
                    </p>
                    <div style="background: var(--gray-50); padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; font-weight: 600; color: var(--text-primary);">
                        ${projectName}
                    </div>
                    <input type="text" id="confirm-project-name" class="form-input" 
                           style="width: 100%;" 
                           placeholder="${isEn ? 'Type project name here...' : 'พิมพ์ชื่อโครงการที่นี่...'}">
                </div>
            `,
            footer: `
                <button class="btn-modal btn-modal-cancel" onclick="Modal.close(this)">${isEn ? 'Cancel' : 'ยกเลิก'}</button>
                <button class="btn-modal btn-modal-confirm danger" id="confirm-delete-project-btn" disabled>
                    <i class="fas fa-trash"></i> ${isEn ? 'Delete Project' : 'ลบโครงการ'}
                </button>
            `
        });

        // Enable button only when project name matches
        const input = document.getElementById('confirm-project-name');
        const confirmBtn = document.getElementById('confirm-delete-project-btn');

        input.addEventListener('input', function() {
            confirmBtn.disabled = this.value.trim() !== projectName;
        });

        confirmBtn.addEventListener('click', async function() {
            if (input.value.trim() !== projectName) {
                Toast.error(isEn ? 'Project name does not match' : 'ชื่อโครงการไม่ตรงกัน');
                return;
            }

            try {
                const response = await API.delete('<?php echo SITE_URL; ?>/api/projects/delete.php', {
                    id
                });
                if (response.success) {
                    Modal.close(confirmBtn);
                    Toast.success(isEn ? 'Project deleted successfully' : 'ลบโครงการสำเร็จ');
                    document.querySelector(`[data-id="${id}"]`)?.remove();
                } else {
                    Toast.error(response.error || (isEn ? 'Failed to delete project' : 'ไม่สามารถลบโครงการได้'));
                }
            } catch (e) {
                Toast.error(isEn ? 'An error occurred' : 'เกิดข้อผิดพลาด');
            }
        });
    }

    // ===== EXPORT PREVIEW =====
    let currentPreviewProjectId = null;
    const isEn = document.body.classList.contains('lang-en');

    function exportProject(id, format) {
        // Open preview modal instead of direct download
        openExportPreview(id);
    }

    async function openExportPreview(projectId) {
        currentPreviewProjectId = projectId;

        // Show modal with loading state
        const modal = document.getElementById('export-preview-modal');
        const paper = document.getElementById('preview-paper');
        const loading = document.getElementById('preview-loading');
        const countEl = document.getElementById('preview-count');

        paper.innerHTML = `
            <div class="preview-loading">
                <i class="fas fa-spinner"></i>
                <div>${isEn ? 'Loading...' : 'กำลังโหลด...'}</div>
            </div>
        `;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        try {
            const response = await API.get('<?php echo SITE_URL; ?>/api/bibliography/preview.php?project=' + projectId);

            if (response.success) {
                renderPreview(response);
                countEl.textContent = response.count + (isEn ? ' items' : ' รายการ');
            } else {
                paper.innerHTML = `
                    <div class="preview-empty">
                        <i class="fas fa-exclamation-circle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
                        <div>${response.error || (isEn ? 'Failed to load' : 'โหลดข้อมูลไม่สำเร็จ')}</div>
                    </div>
                `;
            }
        } catch (error) {
            paper.innerHTML = `
                <div class="preview-empty">
                    <i class="fas fa-exclamation-circle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
                    <div>${isEn ? 'An error occurred' : 'เกิดข้อผิดพลาด'}</div>
                </div>
            `;
        }
    }

    function renderPreview(data) {
        const paper = document.getElementById('preview-paper');
        const bibs = data.bibliographies;

        let html = `<div class="preview-title">${isEn ? 'Bibliography' : 'บรรณานุกรม'}</div>`;

        if (data.count === 0) {
            html += `
                <div class="preview-empty">
                    <i class="fas fa-folder-open" style="font-size: 48px; color: var(--text-tertiary); margin-bottom: 16px;"></i>
                    <div>${isEn ? 'No bibliography entries in this project' : 'ไม่มีรายการบรรณานุกรมในโครงการนี้'}</div>
                </div>
            `;
        } else {
            html += '<div class="preview-entries">';

            // Thai entries first (no label)
            if (bibs.thai && bibs.thai.length > 0) {
                bibs.thai.forEach(entry => {
                    html += `<div class="preview-entry">${formatBibText(entry.text)}</div>`;
                });
            }

            // English entries (no label)
            if (bibs.english && bibs.english.length > 0) {
                bibs.english.forEach(entry => {
                    html += `<div class="preview-entry">${formatBibText(entry.text)}</div>`;
                });
            }

            html += '</div>';
        }

        paper.innerHTML = html;
    }

    function formatBibText(text) {
        // Convert *text* to <em>text</em> for italics
        return text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    }

    function closePreviewModal() {
        const modal = document.getElementById('export-preview-modal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        currentPreviewProjectId = null;
    }

    function downloadExport(format) {
        if (!currentPreviewProjectId) return;

        if (format === 'pdf') {
            // Open print dialog
            printPreview();
        } else {
            // Download Word
            window.open('<?php echo SITE_URL; ?>/api/bibliography/export.php?project=' + currentPreviewProjectId + '&format=docx', '_blank');
            Toast.success(isEn ? 'Word file downloading...' : 'กำลังดาวน์โหลดไฟล์ Word...');
        }
    }

    function printPreview() {
        const paper = document.getElementById('preview-paper');
        const printWindow = window.open('', '_blank', 'width=800,height=600');

        const content = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>${isEn ? 'Bibliography' : 'บรรณานุกรม'}</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,400;0,700;1,400&display=swap');
                    
                    @page { 
                        size: A4; 
                        margin: 2.54cm; 
                    }
                    
                    body { 
                        font-family: 'TH Sarabun New', 'Sarabun', serif; 
                        font-size: 16pt; 
                        line-height: 2;
                        margin: 0;
                        padding: 0;
                    }
                    
                    .preview-title {
                        text-align: center;
                        font-size: 14pt;
                        font-weight: bold;
                        margin-bottom: 30px;
                    }
                    
                    .preview-entry {
                        text-indent: -0.5in;
                        padding-left: 0.5in;
                        margin-bottom: 8px;
                        font-size: 12pt;
                    }
                    
                    .preview-entry em {
                        font-style: italic;
                    }
                    
                    .preview-empty {
                        text-align: center;
                        color: #999;
                        padding: 40px;
                    }
                    
                    @media print {
                        body { -webkit-print-color-adjust: exact; }
                    }
                </style>
            </head>
            <body>
                ${paper.innerHTML}
            </body>
            </html>
        `;

        printWindow.document.write(content);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
        };

        Toast.info(isEn ? 'Print dialog opening...' : 'กำลังเปิดหน้าต่างพิมพ์...');
    }

    // Close modal on click outside
    document.getElementById('export-preview-modal').addEventListener('click', function(e) {
        if (e.target === this) closePreviewModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePreviewModal();
    });
</script>

<?php require_once '../includes/footer.php'; ?>