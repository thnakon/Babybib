<?php

/**
 * Babybib - Admin Users Management (Re-designed)
 * ============================================
 */

require_once '../includes/session.php';

$pageTitle = 'จัดการผู้ใช้';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search and Filters
$search = sanitize($_GET['search'] ?? '');
$filterLis = isset($_GET['lis']) && $_GET['lis'] !== '' ? intval($_GET['lis']) : null;
$filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : null;
$filterRole = sanitize($_GET['role'] ?? '');
$sortOrder = sanitize($_GET['sort'] ?? 'newest');

try {
    $db = getDB();

    // Build query - show all users including admins
    $where = ["1=1"];
    $params = [];

    if ($filterRole) {
        $where[] = "role = ?";
        $params[] = $filterRole;
    }

    if ($search) {
        $where[] = "(username LIKE ? OR name LIKE ? OR surname LIKE ? OR email LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
    }

    if ($filterLis !== null) {
        $where[] = "is_lis_cmu = ?";
        $params[] = $filterLis;
    }

    if ($filterStatus !== null) {
        $where[] = "is_active = ?";
        $params[] = $filterStatus;
    }

    $whereClause = implode(' AND ', $where);

    // Sort order
    $orderBy = "created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "created_at ASC";
    } elseif ($sortOrder === 'name') {
        $orderBy = "name ASC, surname ASC";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Get users with bib count
    $stmt = $db->prepare("
        SELECT u.*, 
        (SELECT COUNT(*) FROM bibliographies WHERE user_id = u.id) as bib_count,
        (SELECT COUNT(*) FROM projects WHERE user_id = u.id) as project_count
        FROM users u
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    $orgTypes = getOrganizationTypes();
} catch (Exception $e) {
    error_log("Admin users error: " . $e->getMessage());
    $users = [];
    $total = 0;
    $totalPages = 0;
}
?>



<div class="admin-users-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('user_management'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "จัดการสมาชิกระบบทั้งหมด " : "Manage all system members "; ?><span class="badge-lis"><?php echo number_format($total); ?> USERS</span></p>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-add-quick" onclick="showAddUserModal()">
                <i class="fas fa-plus"></i>
                <?php echo $currentLang === 'th' ? 'เพิ่มผู้ใช้ด่วน' : 'Add Quick User'; ?>
            </button>
        </div>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="user-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาชื่อ, อีเมล หรือชื่อคนใช้...' : 'Search by name, email or username...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="filter-role">
            <option value=""><?php echo $currentLang === 'th' ? 'บทบาททั้งหมด' : 'All Roles'; ?></option>
            <option value="admin" <?php echo $filterRole === 'admin' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ผู้ดูแลระบบ' : 'Admin'; ?></option>
            <option value="user" <?php echo $filterRole === 'user' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ผู้ใช้งาน' : 'User'; ?></option>
        </select>

        <select class="filter-select" id="filter-lis">
            <option value=""><?php echo $currentLang === 'th' ? 'นักศึกษาทั้งหมด' : 'All Students'; ?></option>
            <option value="1" <?php echo $filterLis === 1 ? 'selected' : ''; ?>>LIS CMU</option>
            <option value="0" <?php echo $filterLis === 0 ? 'selected' : ''; ?>>บุคคลทั่วไป</option>
        </select>

        <select class="filter-select" id="filter-status">
            <option value=""><?php echo $currentLang === 'th' ? 'สถานะทั้งหมด' : 'All Status'; ?></option>
            <option value="1" <?php echo $filterStatus === 1 ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ใช้งานปกติ' : 'Active'; ?></option>
            <option value="0" <?php echo $filterStatus === 0 ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ระงับการใช้งาน' : 'Suspended'; ?></option>
        </select>

        <select class="filter-select" id="sort-order">
            <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ล่าสุด' : 'Newest'; ?></option>
            <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'เก่าสุด' : 'Oldest'; ?></option>
            <option value="name" <?php echo $sortOrder === 'name' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ชื่อ ก-ฮ' : 'Name A-Z'; ?></option>
        </select>
    </div>

    <!-- Users List Area -->
    <div class="users-list-container">
        <?php if (empty($users)): ?>
            <div class="empty-container slide-up stagger-2">
                <div style="opacity: 0.2; transform: scale(3); margin-bottom: 20px;">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary"><?php echo $currentLang === 'th' ? 'ไม่พบข้อมูลผู้ใช้งาน' : 'No users found'; ?></h3>
                <p class="text-tertiary"><?php echo $currentLang === 'th' ? 'ลองปรับเปลี่ยนคำค้นหาหรือตัวกรอง' : 'Try adjusting your search or filters'; ?></p>
            </div>
        <?php else: ?>
            <div class="users-list">
                <?php foreach ($users as $index => $user): ?>
                    <div class="user-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="user-rank">
                            <?php echo $offset + $index + 1; ?>
                        </div>
                        <div class="user-avatar-wrapper">
                            <div class="user-avatar">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="<?php echo SITE_URL . '/uploads/avatars/' . htmlspecialchars($user['profile_picture']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="status-dot <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>"></div>
                        </div>

                        <div class="user-main-info">
                            <div class="user-title-row">
                                <span class="user-full-name"><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></span>
                                <span class="badge-role <?php echo $user['role']; ?>">
                                    <?php echo $user['role'] === 'admin' ? ($currentLang === 'th' ? 'ผู้ดูแล' : 'Admin') : ($currentLang === 'th' ? 'ผู้ใช้' : 'User'); ?>
                                </span>
                                <?php if ($user['is_lis_cmu']): ?>
                                    <span class="badge-lis">LIS STUDENT <?php echo !empty($user['student_id']) ? '(' . htmlspecialchars($user['student_id']) . ')' : ''; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="user-email-row">
                                <span class="user-username">@<?php echo htmlspecialchars($user['username']); ?></span>
                                <span style="opacity: 0.3;">•</span>
                                <i class="far fa-envelope"></i>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>

                        <div class="user-card-stats">
                            <div class="card-stat-item">
                                <span class="stat-val"><?php echo number_format($user['bib_count']); ?></span>
                                <span class="stat-lbl"><?php echo __('bibliographies'); ?></span>
                            </div>
                            <div class="card-stat-item">
                                <span class="stat-val"><?php echo number_format($user['project_count']); ?></span>
                                <span class="stat-lbl"><?php echo __('projects'); ?></span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="action-btn" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="<?php echo __('edit'); ?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="action-btn" onclick="viewUserDetails(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="<?php echo __('view'); ?>">
                                <i class="far fa-eye"></i>
                            </button>
                            <button class="action-btn" onclick="toggleStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 0 : 1; ?>)"
                                title="<?php echo $user['is_active'] ? ($currentLang === 'th' ? 'ระงับ' : 'Suspend') : ($currentLang === 'th' ? 'เปิดใช้งาน' : 'Activate'); ?>">
                                <i class="fas <?php echo $user['is_active'] ? 'fa-user-slash' : 'fa-user-check'; ?>" style="color: <?php echo $user['is_active'] ? '#F59E0B' : '#10B981'; ?>;"></i>
                            </button>
                            <button class="action-btn danger" onclick="confirmDelete(<?php echo $user['id']; ?>)" title="<?php echo __('delete'); ?>">
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
        <div class="pagination mt-8 slide-up">
            <button class="pagination-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                <i class="fas fa-chevron-left"></i>
            </button>

            <?php
            $range = 2; // Pages around current page
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
    // Handle Search and Filters
    const searchInput = document.getElementById('user-search');
    const filterRole = document.getElementById('filter-role');
    const filterLis = document.getElementById('filter-lis');
    const filterStatus = document.getElementById('filter-status');
    const sortOrder = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('role', filterRole.value);
        url.searchParams.set('lis', filterLis.value);
        url.searchParams.set('status', filterStatus.value);
        url.searchParams.set('sort', sortOrder.value);
        url.searchParams.delete('page'); // Back to first page on filter change
        window.location = url.toString();
    }

    // Debounce search
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 600);
    });

    [filterRole, filterLis, filterStatus, sortOrder].forEach(el => {
        el.addEventListener('change', updateFilters);
    });

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function toggleAdminStudentId(checked, mode) {
        const wrapper = document.getElementById(`admin-${mode}-student-id-wrapper`);
        const card = document.getElementById(`${mode === 'add' ? '' : 'edit-'}lis-toggle-card`);

        if (checked) {
            wrapper.style.display = 'block';
            card.classList.add('active');
        } else {
            wrapper.style.display = 'none';
            card.classList.remove('active');
            wrapper.querySelector('input').value = '';
        }
    }

    // Action Functions
    function showAddUserModal() {
        Modal.create({
            title: '<i class="fas fa-user-plus" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "เพิ่มสมาชิกใหม่" : "Create New Member"; ?>',
            content: `
                <div class="modal-form-header" style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--gray-100);">
                    <p style="font-size: 13px; color: var(--text-tertiary);">
                        <i class="fas fa-info-circle"></i> <?php echo $currentLang === 'th' ? 'กรอกข้อมูลด้านล่างเพื่อสร้างบัญชีผู้ใช้งานใหม่ในระบบ' : 'Fill in the information below to create a new user account.'; ?>
                    </p>
                </div>
                
                <form id="add-user-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- Section: Account -->
                        <div style="grid-column: span 2;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group" style="grid-column: span 2;">
                                    <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                        <i class="fas fa-at" style="width: 18px; color: var(--primary);"></i> <?php echo __('username'); ?> <span class="required">*</span>
                                    </label>
                                    <input type="text" name="username" class="form-input" placeholder="somchai_cmu" required style="border-radius: 12px; padding: 12px 15px;">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                        <i class="fas fa-lock" style="width: 18px; color: var(--primary);"></i> <?php echo __('password'); ?> <span class="required">*</span>
                                    </label>
                                    <input type="password" name="password" class="form-input" placeholder="••••••••" required style="border-radius: 12px; padding: 12px 15px;">
                                </div>
                            </div>
                        </div>

                        <!-- Section: Personal -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="far fa-user" style="width: 18px; color: var(--primary);"></i> <?php echo __('name'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-input" placeholder="<?php echo $currentLang === 'th' ? 'ชื่อ' : 'First Name'; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <?php echo __('surname'); ?>
                            </label>
                            <input type="text" name="surname" class="form-input" placeholder="<?php echo $currentLang === 'th' ? 'นามสกุล' : 'Surname'; ?>" style="border-radius: 12px;">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="far fa-envelope" style="width: 18px; color: var(--primary);"></i> <?php echo __('email'); ?> <span class="required">*</span>
                            </label>
                            <input type="email" name="email" class="form-input" placeholder="example@email.com" required style="border-radius: 12px;">
                        </div>

                        <!-- Section: Organization -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="fas fa-layer-group" style="width: 18px; color: var(--primary);"></i> <?php echo __('org_type'); ?>
                            </label>
                            <select name="org_type" class="form-input" style="border-radius: 12px; appearance: auto; cursor: pointer;">
                                <?php foreach ($orgTypes as $key => $type): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $currentLang === 'th' ? $type['th'] : $type['en']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-end;">
                            <label class="lis-status-toggle" id="lis-toggle-card">
                                <input type="checkbox" name="is_lis_cmu" value="1" id="lis_checkbox_admin" onchange="toggleAdminStudentId(this.checked, 'add')">
                                <span style="font-weight: 700; font-size: 13px; color: var(--text-secondary);">LIS STUDENT MEMBER</span>
                            </label>
                            
                            <div id="admin-add-student-id-wrapper" style="display: none; margin-top: 10px;">
                                <input type="text" name="student_id" class="form-input" placeholder="รหัสนักศึกษา (6XXXXXXXX)" style="border-radius: 12px; font-size: 13px;">
                            </div>
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <button class="btn btn-secondary" onclick="Modal.close(this)" style="border-radius: 12px; padding: 10px 20px;"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="submitNewUser(this)" style="border-radius: 12px; padding: 10px 25px; font-weight: 700; box-shadow: var(--shadow-primary);"><?php echo __('save'); ?></button>
            `
        });
    }

    async function submitNewUser(btn) {
        const form = document.getElementById('add-user-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Handle checkbox manually if not checked
        if (!data.is_lis_cmu) data.is_lis_cmu = 0;

        setLoading(btn, true);

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/create-user.php', data);

            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(response.error);
                setLoading(btn, false);
            }
        } catch (error) {
            Toast.error('<?php echo __('error_save'); ?>');
            setLoading(btn, false);
        }
    }

    function editUser(user) {
        Modal.create({
            title: '<i class="fas fa-user-edit" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "แก้ไขข้อมูลผู้ใช้งาน" : "Edit User Details"; ?>',
            content: `
                <div class="modal-form-header" style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--gray-100);">
                    <p style="font-size: 13px; color: var(--text-tertiary);">
                        <i class="fas fa-fingerprint"></i> <?php echo $currentLang === 'th' ? 'แก้ไขข้อมูลของสมาชิก #' : 'Updating information for member #'; ?>${user.id}
                    </p>
                </div>
                
                <form id="edit-user-form">
                    <input type="hidden" name="id" value="${user.id}">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- Section: Account -->
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="fas fa-at" style="width: 18px; color: var(--primary);"></i> <?php echo __('username'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" name="username" class="form-input" value="${user.username}" required style="border-radius: 12px; padding: 12px 15px;">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="fas fa-key" style="width: 18px; color: var(--primary);"></i> <?php echo __('password'); ?> 
                                <span style="font-weight: 400; color: var(--text-tertiary); font-size: 11px;">(<?php echo $currentLang === 'th' ? 'เว้นว่างไว้หากไม่ต้องการเปลี่ยน' : 'Leave blank to keep current'; ?>)</span>
                            </label>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" style="border-radius: 12px; padding: 12px 15px;">
                        </div>

                        <!-- Section: Personal -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="far fa-user" style="width: 18px; color: var(--primary);"></i> <?php echo __('name'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-input" value="${user.name}" required style="border-radius: 12px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <?php echo __('surname'); ?>
                            </label>
                            <input type="text" name="surname" class="form-input" value="${user.surname || ''}" style="border-radius: 12px;">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="far fa-envelope" style="width: 18px; color: var(--primary);"></i> <?php echo __('email'); ?> <span class="required">*</span>
                            </label>
                            <input type="email" name="email" class="form-input" value="${user.email}" required style="border-radius: 12px;">
                        </div>

                        <!-- Section: Organization -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: block; font-size: 13px;">
                                <i class="fas fa-layer-group" style="width: 18px; color: var(--primary);"></i> <?php echo __('org_type'); ?>
                            </label>
                            <select name="org_type" class="form-input" style="border-radius: 12px; appearance: auto; cursor: pointer;">
                                <?php foreach ($orgTypes as $key => $type): ?>
                                    <option value="<?php echo $key; ?>" ${user.org_type === '<?php echo $key; ?>' ? 'selected' : ''}>
                                        <?php echo $currentLang === 'th' ? $type['th'] : $type['en']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-end;">
                            <label class="lis-status-toggle ${user.is_lis_cmu == 1 ? 'active' : ''}" id="edit-lis-toggle-card">
                                <input type="checkbox" name="is_lis_cmu" value="1" ${user.is_lis_cmu == 1 ? 'checked' : ''} onchange="toggleAdminStudentId(this.checked, 'edit')">
                                <span style="font-weight: 700; font-size: 13px; color: var(--text-secondary);">LIS STUDENT MEMBER</span>
                            </label>

                            <div id="admin-edit-student-id-wrapper" style="display: ${user.is_lis_cmu == 1 ? 'block' : 'none'}; margin-top: 10px;">
                                <input type="text" name="student_id" class="form-input" value="${user.student_id || ''}" placeholder="รหัสนักศึกษา (6XXXXXXXX)" style="border-radius: 12px; font-size: 13px;">
                            </div>
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <button class="btn btn-secondary" onclick="Modal.close(this)" style="border-radius: 12px; padding: 10px 20px;"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary" onclick="submitEditUser(this)" style="border-radius: 12px; padding: 10px 25px; font-weight: 700; box-shadow: var(--shadow-primary);"><?php echo __('save'); ?></button>
            `
        });
    }

    async function submitEditUser(btn) {
        const form = document.getElementById('edit-user-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.is_lis_cmu) data.is_lis_cmu = 0;

        setLoading(btn, true);

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-user-details.php', data);

            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(response.error);
                setLoading(btn, false);
            }
        } catch (error) {
            Toast.error('<?php echo __('error_save'); ?>');
            setLoading(btn, false);
        }
    }

    function viewUserDetails(user) {
        const orgTypesMap = <?php echo json_encode($orgTypes); ?>;
        const currentLang = '<?php echo $currentLang; ?>';
        const orgLabel = orgTypesMap[user.org_type] ? (currentLang === 'th' ? orgTypesMap[user.org_type].th : orgTypesMap[user.org_type].en) : user.org_type;

        Modal.create({
            title: '<i class="fas fa-id-card" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "รายละเอียดผู้ใช้งาน" : "User Information"; ?>',
            content: `
                <div class="user-detail-modal" style="padding: 10px;">
                    <!-- Minimalist Profile Header -->
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border-bottom: 2px solid var(--gray-50); padding-bottom: 20px;">
                        <div style="position: relative;">
                            <div style="width: 70px; height: 70px; background: var(--primary-gradient); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem; font-weight: 800; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.2); overflow: hidden;">
                                ${user.profile_picture ? `<img src="<?php echo SITE_URL; ?>/uploads/avatars/${user.profile_picture}" style="width: 100%; height: 100%; object-fit: cover;">` : user.name.charAt(0).toUpperCase()}
                            </div>
                            <div style="position: absolute; bottom: -2px; right: -2px; width: 18px; height: 18px; background: ${user.is_active == 1 ? '#10B981' : '#EF4444'}; border: 3px solid var(--white); border-radius: 50%;"></div>
                        </div>
                        <div>
                            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-primary); margin-bottom: 5px;">${user.name} ${user.surname || ''}</h3>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 13px; font-weight: 700; color: var(--primary);"><i class="fas fa-at"></i> ${user.username}</span>
                                <span style="font-size: 11px; font-weight: 600; color: var(--text-tertiary); background: var(--gray-100); padding: 2px 8px; border-radius: 6px;">#${user.id}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <!-- Col 1: Details -->
                        <div>
                            <div style="margin-bottom: 20px;">
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 10px;">
                                    <i class="fas fa-address-book" style="color: var(--primary);"></i> <?php echo $currentLang === 'th' ? 'ข้อมูลติดต่อ' : 'Contact Details'; ?>
                                </label>
                                <div style="display: flex; flex-direction: column; gap: 12px; padding-left: 20px;">
                                    <div>
                                        <div style="font-size: 10px; color: var(--text-tertiary);"><?php echo __('email'); ?></div>
                                        <div style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">${user.email}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 10px; color: var(--text-tertiary);"><?php echo __('org_type'); ?></div>
                                        <div style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">${orgLabel}</div>
                                    </div>
                                    ${user.province ? `
                                    <div>
                                        <div style="font-size: 10px; color: var(--text-tertiary);"><?php echo $currentLang === 'th' ? 'จังหวัดที่อยู่' : 'Province'; ?></div>
                                        <div style="font-size: 14px; font-weight: 600; color: var(--text-secondary);"><i class="fas fa-map-marker-alt"></i> ${user.province}</div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Col 2: Activity -->
                        <div>
                            <div style="margin-bottom: 25px;">
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 15px;">
                                    <i class="fas fa-stream" style="color: var(--primary);"></i> <?php echo $currentLang === 'th' ? 'สถิติการใช้งาน' : 'User Activity'; ?>
                                </label>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div style="background: #F8F9FF; border: 1px solid #DDD6FE; padding: 15px; border-radius: 16px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 800; color: #8B5CF6;">${user.bib_count}</div>
                                        <div style="font-size: 9px; font-weight: 700; color: #6366F1;"><?php echo __('bibliographies'); ?></div>
                                    </div>
                                    <div style="background: #FAF5FF; border: 1px solid #F3E8FF; padding: 15px; border-radius: 16px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 800; color: #7C3AED;">${user.project_count}</div>
                                        <div style="font-size: 9px; font-weight: 700; color: #8B5CF6;"><?php echo __('projects'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div style="padding: 15px; background: var(--gray-50); border-radius: 16px; border: 1px solid var(--gray-100);">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 12px;">
                                        <span style="color: var(--text-tertiary);"><?php echo $currentLang === 'th' ? 'วันที่สมัคร' : 'Member Since'; ?></span>
                                        <span style="font-weight: 700; color: var(--text-secondary);">${user.created_at}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 12px;">
                                        <span style="color: var(--text-tertiary);"><?php echo $currentLang === 'th' ? 'ใช้งานล่าสุด' : 'Last Activity'; ?></span>
                                        <span style="font-weight: 700; color: var(--primary);">${user.last_login || '-'}</span>
                                    </div>
                                </div>
                            </div>
                            
                            ${user.is_lis_cmu == 1 ? `
                            <div style="margin-top: 15px; background: #F0FDF4; border: 1px solid #DCFCE7; padding: 15px; border-radius: 12px; display: flex; flex-direction: column; gap: 5px; color: #16A34A;">
                                <div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 11px;">
                                    <i class="fas fa-check-circle"></i> LIS STUDENT MEMBER
                                </div>
                                ${user.student_id ? `
                                <div style="font-size: 14px; font-weight: 700; padding-left: 24px;">
                                    ID: ${user.student_id}
                                </div>
                                ` : ''}
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `,
            footer: `
                <button class="btn btn-primary" onclick="Modal.close(this)" style="border-radius: 12px; padding: 10px 25px; font-weight: 700;"><?php echo __('close'); ?></button>
            `
        });
    }

    async function toggleStatus(id, newStatus) {
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-user.php', {
                id: id,
                is_active: newStatus
            });

            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(response.error);
            }
        } catch (error) {
            Toast.error('<?php echo __('error_save'); ?>');
        }
    }

    function confirmDelete(id) {
        const adminUsername = '<?php echo $_SESSION['username']; ?>';

        Modal.create({
            title: '<i class="fas fa-exclamation-triangle" style="color: #EF4444; margin-right: 10px;"></i> <?php echo $currentLang === 'th' ? "ยืนยันการลบผู้ใช้งาน" : "Confirm User Deletion"; ?>',
            content: `
                <div style="padding: 10px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.5;">
                        <?php echo $currentLang === 'th' ? "คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้? ข้อมูลบรรณานุกรมและโครงการทั้งหมดจะถูกลบออกถาวร" : "Are you sure you want to delete this user? All their bibliographies and projects will be permanently removed."; ?>
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
                        const response = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-user.php', {
                            id: id
                        });
                        if (response.success) {
                            Toast.success('<?php echo __('delete_success'); ?>');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
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
```