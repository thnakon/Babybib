<?php

/**
 * Babybib - Admin Projects Management
 * ============================================
 */

require_once '../includes/session.php';

$pageTitle = 'จัดการโครงการ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search and Filters
$search = sanitize($_GET['search'] ?? '');
$filterUser = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? intval($_GET['user_id']) : null;
$sortOrder = sanitize($_GET['sort'] ?? 'newest');

try {
    $db = getDB();

    // Build query
    $where = ["1=1"];
    $params = [];

    if ($search) {
        $where[] = "(p.name LIKE ? OR u.username LIKE ? OR u.name LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }
    if ($filterUser !== null) {
        $where[] = "p.user_id = ?";
        $params[] = $filterUser;
    }

    $whereClause = implode(' AND ', $where);

    // Sort order
    $orderBy = "p.created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "p.created_at ASC";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects p JOIN users u ON p.user_id = u.id WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Get projects with user info and bib count
    $stmt = $db->prepare("
        SELECT p.*, u.username, u.name as user_name, u.surname as user_surname, u.profile_picture,
               (SELECT COUNT(*) FROM bibliographies WHERE project_id = p.id) as bibliography_count
        FROM projects p 
        JOIN users u ON p.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $projects = $stmt->fetchAll();

    // Get unique authors for filter
    $stmt = $db->query("SELECT DISTINCT u.id, u.username, u.name FROM users u JOIN projects p ON u.id = p.user_id ORDER BY u.username ASC");
    $authors = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Admin projects error: " . $e->getMessage());
    $projects = [];
    $total = 0;
    $totalPages = 0;
    $authors = [];
}
?>



<div class="admin-projects-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-folder"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('project_management'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "โครงการทั้งหมดในระบบ " : "All projects in the system "; ?><span class="badge-lis"><?php echo number_format($total); ?> PROJECTS</span></p>
            </div>
        </div>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="project-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาชื่อโครงการ, ชื่อผู้ใช้...' : 'Search project name, username...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="filter-user">
            <option value=""><?php echo $currentLang === 'th' ? 'เจ้าของโครงการทุกคน' : 'All Project Owners'; ?></option>
            <?php foreach ($authors as $author): ?>
                <option value="<?php echo $author['id']; ?>" <?php echo $filterUser == $author['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($author['username']); ?> (<?php echo htmlspecialchars($author['name']); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <select class="filter-select" id="sort-order">
            <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ล่าสุด' : 'Newest'; ?></option>
            <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'เก่าสุด' : 'Oldest'; ?></option>
        </select>
    </div>

    <!-- Main Content -->
    <div class="content-section">
        <?php if (empty($projects)): ?>
            <div class="empty-container slide-up stagger-2">
                <div class="empty-state">
                    <i class="fas fa-folder-open fa-3x mb-4" style="color: var(--gray-200);"></i>
                    <p class="text-secondary"><?php echo __('no_project'); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="project-list">
                <?php foreach ($projects as $index => $project): ?>
                    <div class="project-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="project-rank">
                            <?php echo $offset + $index + 1; ?>
                        </div>

                        <div class="project-type-icon" style="background: <?php echo htmlspecialchars($project['color']); ?>;">
                            <i class="fas fa-folder"></i>
                        </div>

                        <div class="project-main-info">
                            <div class="project-name-row">
                                <div class="project-color-dot" style="background: <?php echo htmlspecialchars($project['color']); ?>;"></div>
                                <span class="project-name"><?php echo htmlspecialchars($project['name']); ?></span>
                            </div>
                            <div class="project-meta">
                                <span class="owner-name"><i class="far fa-user"></i> @<?php echo htmlspecialchars($project['username']); ?></span>
                                <span class="bib-count-badge">
                                    <i class="fas fa-book-open"></i>
                                    <?php echo number_format($project['bibliography_count']); ?> <?php echo __('bibliographies'); ?>
                                </span>
                                <span class="project-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo formatThaiDate($project['created_at']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="action-btn" onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)" title="<?php echo __('edit'); ?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="action-btn" onclick="viewProjectDetails(<?php echo htmlspecialchars(json_encode($project)); ?>)" title="<?php echo __('view'); ?>">
                                <i class="far fa-eye"></i>
                            </button>
                            <button class="action-btn danger" onclick="confirmDelete(<?php echo $project['id']; ?>)" title="<?php echo __('delete'); ?>">
                                <i class="far fa-trash-alt"></i>
                            </button>
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
                if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $active = ($i === $page) ? 'active' : '';
                echo "<button class='pagination-btn $active' onclick='goToPage($i)'>$i</button>";
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                echo "<button class='pagination-btn' onclick='goToPage($totalPages)'>$totalPages</button>";
            }
            ?>

            <button class="pagination-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
    const searchInput = document.getElementById('project-search');
    const filterUser = document.getElementById('filter-user');
    const sortOrder = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('user_id', filterUser.value);
        url.searchParams.set('sort', sortOrder.value);
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    // Debounce search
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 600);
    });

    [filterUser, sortOrder].forEach(el => {
        el.addEventListener('change', updateFilters);
    });

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function viewProjectDetails(project) {
        Modal.create({
            title: '<i class="fas fa-folder-open" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "รายละเอียดโครงการ" : "Project Details"; ?>',
            content: `
                <div style="padding: 10px;">
                    <div style="background: var(--gray-50); padding: 20px; border-radius: 16px; margin-bottom: 20px; border: 1px solid var(--gray-100);">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <div class="project-type-icon" style="width: 50px; height: 50px; border-radius: 14px; background: ${project.color}; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text-primary); font-size: 15px;">${project.user_name}</div>
                                <div style="font-size: 13px; color: var(--primary); font-weight: 700;">@${project.username}</div>
                            </div>
                        </div>
                        
                        <div style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--gray-100);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                <h3 style="font-weight: 800; color: var(--text-primary); margin: 0;">${project.name}</h3>
                            </div>
                            <div style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                                ${project.description || '<?php echo $currentLang === 'th' ? "ไม่มีคำอธิบายโครงการ" : "No project description"; ?>'}
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo __('bibliographies'); ?></div>
                            <div style="font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-book-open" style="color: var(--primary);"></i>
                                ${project.bibliography_count} <?php echo __('items'); ?>
                            </div>
                        </div>
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo __('created_at'); ?></div>
                            <div style="font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                                <i class="far fa-calendar-alt" style="color: var(--primary);"></i>
                                ${project.created_at}
                            </div>
                        </div>
                    </div>
                </div>
            `,
            footer: `<button class="btn btn-primary" onclick="Modal.close(this)" style="border-radius: 12px; padding: 10px 25px; font-weight: 700;"><?php echo __('close'); ?></button>`
        });
    }

    function editProject(project) {
        Modal.create({
            title: '<i class="fas fa-edit" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "แก้ไขโครงการ" : "Edit Project"; ?>',
            content: `
                <div style="padding: 10px;">
                    <form id="edit-project-form">
                        <input type="hidden" name="id" value="${project.id}">
                        
                        <div class="form-group mb-4">
                            <label class="form-label font-bold mb-2 block"><?php echo __('project_name'); ?></label>
                            <input type="text" name="name" class="form-input" value="${project.name}" placeholder="<?php echo __('project_name'); ?>">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label font-bold mb-2 block"><?php echo __('description'); ?></label>
                            <textarea name="description" class="form-input" style="height: 100px; line-height: 1.6; padding: 15px;" placeholder="<?php echo __('description'); ?>">${project.description || ''}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-bold mb-2 block"><?php echo $currentLang === 'th' ? 'สีโครงการ' : 'Project Color'; ?></label>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                ${['#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#EC4899', '#6366F1', '#14B8A6'].map(c => `
                                    <label style="cursor: pointer; position: relative;">
                                        <input type="radio" name="color" value="${c}" ${project.color === c ? 'checked' : ''} style="position: absolute; opacity: 0;">
                                        <div class="color-swatch" style="width: 35px; height: 35px; background: ${c}; border-radius: 10px; border: 3px solid ${project.color === c ? 'rgba(0,0,0,0.2)' : 'white'}; box-shadow: var(--shadow-sm);"></div>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    </form>
                </div>
            `,
            footer: `
                <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-primary" id="btn-save-project" style="padding: 10px 25px; border-radius: 12px; font-weight: 700;">
                        <i class="fas fa-save mr-2"></i> <?php echo __('save'); ?>
                    </button>
                </div>
            `,
            onOpen: (modal) => {
                const btn = modal.querySelector('#btn-save-project');
                const form = modal.querySelector('#edit-project-form');

                // Add click listener for color swatches
                modal.querySelectorAll('.color-swatch').forEach(swatch => {
                    swatch.addEventListener('click', () => {
                        modal.querySelectorAll('.color-swatch').forEach(s => s.style.borderColor = 'white');
                        swatch.style.borderColor = 'rgba(0,0,0,0.2)';
                    });
                });

                btn.addEventListener('click', async () => {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?php echo $currentLang === 'th' ? "กำลังบันทึก..." : "Saving..."; ?>';

                    try {
                        const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-project.php', data);
                        if (response.success) {
                            Toast.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save mr-2"></i> <?php echo __('save'); ?>';
                        }
                    } catch (e) {
                        Toast.error('<?php echo __('error_save'); ?>');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save mr-2"></i> <?php echo __('save'); ?>';
                    }
                });
            }
        });
    }

    function confirmDelete(id) {
        const adminUsername = '<?php echo $_SESSION['username']; ?>';

        Modal.create({
            title: '<i class="fas fa-trash-alt" style="color: #EF4444; margin-right: 10px;"></i> <?php echo __('delete_project'); ?>',
            content: `
                <div style="padding: 10px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.5;">
                        <?php echo __('delete_confirm'); ?>
                    </p>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 12px; border: 1px solid #FEE2E2; margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #991B1B; margin-bottom: 8px;">
                            <?php echo $currentLang === 'th' ? "กรุณาพิมพ์ชื่อผู้ใช้ของคุณ (" . $_SESSION['username'] . ") เพื่อยืนยัน" : "Please type your username (" . $_SESSION['username'] . ") to confirm"; ?>
                        </label>
                        <input type="text" id="delete-confirm-username" class="form-input" placeholder="<?php echo $currentLang === 'th' ? 'พิมพ์ Username ของคุณที่นี่' : 'Type your username here'; ?>" autocomplete="off" style="border-color: #FCA5A5;">
                    </div>
                </div>
            `,
            footer: `
                <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-danger" id="btn-confirm-delete" style="padding: 10px 25px; border-radius: 12px; font-weight: 700; background: #EF4444;">
                        <?php echo __('delete'); ?>
                    </button>
                </div>
            `,
            onOpen: (modal) => {
                const btn = modal.querySelector('#btn-confirm-delete');
                const input = modal.querySelector('#delete-confirm-username');

                btn.addEventListener('click', async () => {
                    if (input.value !== adminUsername) {
                        Toast.error('<?php echo $currentLang === 'th' ? "Username ไม่ถูกต้อง" : "Incorrect username"; ?>');
                        input.style.borderColor = '#EF4444';
                        input.focus();
                        return;
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const response = await API.delete('<?php echo SITE_URL; ?>/api/projects/delete.php', {
                            id: id
                        });
                        if (response.success) {
                            Toast.success('<?php echo __('delete_success'); ?>');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error || '<?php echo __('error_delete'); ?>');
                            btn.disabled = false;
                            btn.textContent = '<?php echo __('delete'); ?>';
                        }
                    } catch (e) {
                        Toast.error('<?php echo __('error_delete'); ?>');
                        btn.disabled = false;
                        btn.textContent = '<?php echo __('delete'); ?>';
                    }
                });
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>