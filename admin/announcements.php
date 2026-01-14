<?php

/**
 * Babybib - Admin Announcements Page
 * ====================================
 */

require_once '../includes/session.php';

$pageTitle = 'ประกาศ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');

try {
    $db = getDB();

    $where = ["1=1"];
    $params = [];

    if ($search) {
        $where[] = "(a.title_th LIKE ? OR a.title_en LIKE ? OR a.content_th LIKE ? OR a.content_en LIKE ? OR u.username LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
    }

    if ($status !== '') {
        $where[] = "a.is_active = ?";
        $params[] = ($status === 'active' ? 1 : 0);
    }

    $whereClause = implode(' AND ', $where);

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM announcements a LEFT JOIN users u ON a.admin_id = u.id WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    $titleCol = $currentLang === 'th' ? 'title_th' : 'title_en';
    $contentCol = $currentLang === 'th' ? 'content_th' : 'content_en';

    $stmt = $db->prepare("
        SELECT a.*, a.$titleCol as title, a.$contentCol as content, u.username, u.name as admin_name
        FROM announcements a 
        LEFT JOIN users u ON a.admin_id = u.id
        WHERE $whereClause
        ORDER BY a.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $announcements = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Admin announcements error: " . $e->getMessage());
    $announcements = [];
    $total = 0;
    $totalPages = 0;
}
?>



<div class="admin-ann-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('announcements'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "บริหารจัดการประกาศสำคัญถึงผู้ใช้ทุกคน" : "Manage important announcements for all users"; ?> <span class="badge-count"><?php echo number_format($total); ?> ITEMS</span></p>
            </div>
        </div>
        <button class="btn btn-primary" onclick="showCreateModal()" style="border-radius: 14px; padding: 12px 25px; font-weight: 700; box-shadow: var(--shadow-primary);">
            <i class="fas fa-plus"></i>
            <span><?php echo $currentLang === 'th' ? 'สร้างประกาศใหม่' : 'Create New'; ?></span>
        </button>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="ann-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาหัวข้อ, เนื้อหา...' : 'Search title, content...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="status-filter">
            <option value=""><?php echo $currentLang === 'th' ? 'ทุกสถานะ' : 'All Status'; ?></option>
            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'เผยแพร่' : 'Active'; ?></option>
            <option value="hidden" <?php echo $status === 'hidden' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ซ่อน' : 'Hidden'; ?></option>
        </select>
    </div>

    <!-- Main Content -->
    <div class="content-section">
        <?php if (empty($announcements)): ?>
            <div style="padding: 100px 0; text-align: center; background: white; border-radius: 24px; border: 2px dashed var(--gray-100);" class="slide-up stagger-2">
                <div class="empty-state">
                    <i class="fas fa-bullhorn fa-3x mb-4" style="color: var(--gray-200);"></i>
                    <p class="text-secondary"><?php echo $currentLang === 'th' ? 'ยังไม่มีรายการประกาศ' : 'No announcements found'; ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="ann-list">
                <?php foreach ($announcements as $index => $ann): ?>
                    <div class="ann-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="ann-header">
                            <div class="ann-title-section">
                                <h3 class="ann-title"><?php echo htmlspecialchars($ann['title']); ?></h3>
                                <div class="ann-meta">
                                    <span><i class="far fa-user"></i> <?php echo htmlspecialchars($ann['username'] ?: 'Admin'); ?></span>
                                    <span><i class="far fa-calendar-alt"></i> <?php echo formatThaiDate($ann['created_at']); ?></span>
                                    <?php if ($ann['updated_at'] && $ann['updated_at'] !== $ann['created_at']): ?>
                                        <span><i class="fas fa-history"></i> แก้ไขเมื่อ <?php echo date('H:i', strtotime($ann['updated_at'])); ?> น.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="status-badge <?php echo $ann['is_active'] ? 'status-active' : 'status-hidden'; ?>">
                                <i class="fas <?php echo $ann['is_active'] ? 'fa-check-circle' : 'fa-eye-slash'; ?>" style="margin-right: 5px;"></i>
                                <?php echo $ann['is_active'] ? ($currentLang === 'th' ? 'เผยแพร่' : 'Active') : ($currentLang === 'th' ? 'ซ่อน' : 'Hidden'); ?>
                            </span>
                        </div>

                        <div class="ann-content">
                            <?php echo htmlspecialchars($ann['content']); ?>
                        </div>

                        <div class="ann-actions">
                            <div class="action-group">
                                <button class="btn-action" onclick="showEditModal(<?php echo htmlspecialchars(json_encode($ann)); ?>)">
                                    <i class="far fa-edit"></i>
                                    <span><?php echo __('edit'); ?></span>
                                </button>
                                <button class="btn-action" onclick="toggleAnnouncement(<?php echo $ann['id']; ?>, <?php echo $ann['is_active'] ? '0' : '1'; ?>)">
                                    <i class="fas <?php echo $ann['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    <span><?php echo $ann['is_active'] ? ($currentLang === 'th' ? 'ซ่อนประกาศ' : 'Hide') : ($currentLang === 'th' ? 'แสดงประกาศ' : 'Show'); ?></span>
                                </button>
                            </div>
                            <button class="btn-action btn-delete" onclick="confirmDelete(<?php echo $ann['id']; ?>)">
                                <i class="far fa-trash-alt"></i>
                                <span><?php echo __('delete'); ?></span>
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
                if ($start > 2) echo '<span style="padding: 0 5px; font-weight: 700; color: var(--text-tertiary);">...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $active = $i === $page ? 'active' : '';
                echo "<button class=\"pagination-btn $active\" onclick=\"goToPage($i)\">$i</button>";
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) echo '<span style="padding: 0 5px; font-weight: 700; color: var(--text-tertiary);">...</span>';
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
    const searchInput = document.getElementById('ann-search');
    const statusFilter = document.getElementById('status-filter');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('status', statusFilter.value);
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    searchInput.addEventListener('input', debounce(updateFilters, 600));
    statusFilter.addEventListener('change', updateFilters);

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function showCreateModal() {
        Modal.create({
            title: '<i class="fas fa-plus-circle" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? 'สร้างประกาศใหม่' : 'Create New Announcement'; ?>',
            content: `
                <form id="create-ann-form" style="padding: 10px;">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'หัวข้อประกาศ' : 'Title'; ?> <span class="required">*</span></label>
                        <input type="text" name="title" class="form-input" style="border-radius: 12px;" placeholder="<?php echo $currentLang === 'th' ? 'พิมพ์หัวข้อประกาศที่นี่' : 'Enter title here'; ?>" required>
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'เนื้อหา' : 'Content'; ?> <span class="required">*</span></label>
                        <textarea name="content" class="form-input" style="border-radius: 12px; height: 180px; resize: vertical;" placeholder="<?php echo $currentLang === 'th' ? 'พิมพ์เนื้อหารายละเอียดของประกาศ...' : 'Enter announcement details...'; ?>" required></textarea>
                    </div>
                    <div>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <div class="checkbox-box"><i class="fas fa-check"></i></div>
                            <span style="font-weight: 700; color: var(--text-secondary); font-size: 14px;"><?php echo $currentLang === 'th' ? 'เผยแพร่ทันที' : 'Publish Immediately'; ?></span>
                        </label>
                    </div>
                </form>
            `,
            footer: `
                <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-primary" id="btn-submit-create" style="padding: 10px 25px; border-radius: 12px; font-weight: 700;"><?php echo __('create'); ?></button>
                </div>
            `,
            onOpen: (modal) => {
                modal.querySelector('#btn-submit-create').addEventListener('click', async (e) => {
                    const btn = e.currentTarget;
                    const form = modal.querySelector('#create-ann-form');
                    if (!form.reportValidity()) return;

                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    data.is_active = data.is_active ? 1 : 0;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const response = await API.post('<?php echo SITE_URL; ?>/api/admin/create-announcement.php', data);
                        if (response.success) {
                            Toast.success('<?php echo $currentLang === 'th' ? 'สร้างประกาศสำเร็จแล้ว' : 'Announcement created'; ?>');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
                            btn.disabled = false;
                            btn.textContent = '<?php echo __('create'); ?>';
                        }
                    } catch (err) {
                        Toast.error('<?php echo __('error_save'); ?>');
                        btn.disabled = false;
                        btn.textContent = '<?php echo __('create'); ?>';
                    }
                });
            }
        });
    }

    function showEditModal(ann) {
        Modal.create({
            title: '<i class="far fa-edit" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? 'แก้ไขประกาศ' : 'Edit Announcement'; ?>',
            content: `
                <form id="edit-ann-form" style="padding: 10px;">
                    <input type="hidden" name="id" value="${ann.id}">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'หัวข้อประกาศ' : 'Title'; ?> <span class="required">*</span></label>
                        <input type="text" name="title" class="form-input" style="border-radius: 12px;" value="${ann.title}" required>
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label class="form-label"><?php echo $currentLang === 'th' ? 'เนื้อหา' : 'Content'; ?> <span class="required">*</span></label>
                        <textarea name="content" class="form-input" style="border-radius: 12px; height: 180px; resize: vertical;" required>${ann.content}</textarea>
                    </div>
                    <div>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="is_active" value="1" ${ann.is_active == 1 ? 'checked' : ''}>
                            <div class="checkbox-box"><i class="fas fa-check"></i></div>
                            <span style="font-weight: 700; color: var(--text-secondary); font-size: 14px;"><?php echo $currentLang === 'th' ? 'เผยแพร่' : 'Active'; ?></span>
                        </label>
                    </div>
                </form>
            `,
            footer: `
                <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-primary" id="btn-submit-update" style="padding: 10px 25px; border-radius: 12px; font-weight: 700;"><?php echo __('save'); ?></button>
                </div>
            `,
            onOpen: (modal) => {
                modal.querySelector('#btn-submit-update').addEventListener('click', async (e) => {
                    const btn = e.currentTarget;
                    const form = modal.querySelector('#edit-ann-form');
                    if (!form.reportValidity()) return;

                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    data.is_active = data.is_active ? 1 : 0;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-announcement.php', data);
                        if (response.success) {
                            Toast.success('<?php echo $currentLang === 'th' ? 'แก้ไขประกาศสำเร็จแล้ว' : 'Announcement updated'; ?>');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
                            btn.disabled = false;
                            btn.textContent = '<?php echo __('save'); ?>';
                        }
                    } catch (err) {
                        Toast.error('<?php echo __('error_save'); ?>');
                        btn.disabled = false;
                        btn.textContent = '<?php echo __('save'); ?>';
                    }
                });
            }
        });
    }

    async function toggleAnnouncement(id, status) {
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-announcement.php', {
                id,
                is_active: status
            });
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'อัปเดตสถานะประกาศแล้ว' : 'Status updated'; ?>');
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        }
    }

    function confirmDelete(id) {
        const adminUsername = '<?php echo $_SESSION['username']; ?>';
        Modal.create({
            title: '<i class="fas fa-trash-alt" style="color: #EF4444; margin-right: 10px;"></i> <?php echo $currentLang === 'th' ? "ลบประกาศ" : "Delete Announcement"; ?>',
            content: `
                <div style="padding: 10px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;"><?php echo $currentLang === 'th' ? "คุณแน่ใจหรือไม่ที่จะลบประกาศนี้? ข้อมูลจะไม่สามารถกู้คืนได้" : "Are you sure you want to delete this announcement? This action cannot be undone."; ?></p>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 12px; border: 1px solid #FEE2E2;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #991B1B; margin-bottom: 8px;">ยืนยันโดยพิมพ์ชื่อผู้ใช้ ($adminUsername)</label>
                        <input type="text" id="delete-confirm-username" class="form-input" style="border-color: #FCA5A5;" placeholder="Username ของคุณ" autocomplete="off">
                    </div>
                </div>
            `,
            footer: `<div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                <button class="btn btn-danger" id="btn-confirm-delete" style="padding: 10px 25px; border-radius: 12px; font-weight: 700; background: #EF4444;"><?php echo __('delete'); ?></button>
            </div>`,
            onOpen: (modal) => {
                const btn = modal.querySelector('#btn-confirm-delete');
                const input = modal.querySelector('#delete-confirm-username');
                btn.addEventListener('click', async () => {
                    if (input.value !== adminUsername) {
                        Toast.error('ชื่อผู้ใช้ไม่ถูกต้อง');
                        return;
                    }
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    try {
                        const response = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-announcement.php', {
                            id
                        });
                        if (response.success) {
                            Toast.success('ลบประกาศสำเร็จ');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
                            btn.disabled = false;
                            btn.textContent = '<?php echo __('delete'); ?>';
                        }
                    } catch (e) {
                        Toast.error('เกิดข้อผิดพลาด');
                        btn.disabled = false;
                        btn.textContent = '<?php echo __('delete'); ?>';
                    }
                });
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>