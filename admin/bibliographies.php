<?php

/**
 * Babybib - Admin Bibliographies Management
 * ============================================
 */

require_once '../includes/session.php';

$pageTitle = 'จัดการบรรณานุกรม';
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
$filterType = isset($_GET['type']) && $_GET['type'] !== '' ? intval($_GET['type']) : null;
$filterUser = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? intval($_GET['user_id']) : null;
$sortOrder = sanitize($_GET['sort'] ?? 'newest');

try {
    $db = getDB();

    // Build query
    $where = ["1=1"];
    $params = [];

    if ($search) {
        $where[] = "(b.bibliography_text LIKE ? OR u.username LIKE ? OR u.name LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }

    if ($filterType !== null) {
        $where[] = "b.resource_type_id = ?";
        $params[] = $filterType;
    }

    if ($filterUser !== null) {
        $where[] = "b.user_id = ?";
        $params[] = $filterUser;
    }

    $whereClause = implode(' AND ', $where);

    // Sort order
    $orderBy = "b.created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "b.created_at ASC";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bibliographies b JOIN users u ON b.user_id = u.id WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Get bibliographies with user info
    $stmt = $db->prepare("
        SELECT b.*, u.username, u.name as user_name, u.surname as user_surname, u.profile_picture,
               rt.name_th, rt.name_en, rt.icon as rt_icon
        FROM bibliographies b 
        JOIN users u ON b.user_id = u.id
        JOIN resource_types rt ON b.resource_type_id = rt.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $bibliographies = $stmt->fetchAll();

    // Get all resource types for filter
    $stmt = $db->query("SELECT id, name_th, name_en, icon FROM resource_types ORDER BY name_th ASC");
    $resourceTypes = $stmt->fetchAll();

    // Get unique authors for filter
    $stmt = $db->query("SELECT DISTINCT u.id, u.username, u.name FROM users u JOIN bibliographies b ON u.id = b.user_id ORDER BY u.username ASC");
    $authors = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Admin bibliographies error: " . $e->getMessage());
    $bibliographies = [];
    $total = 0;
    $totalPages = 0;
    $resourceTypes = [];
    $authors = [];
}
?>

<div class="admin-bib-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('bibliography_management'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "รายการบรรณานุกรมทั้งหมดในระบบ " : "All bibliographies in the system "; ?><span class="badge-lis"><?php echo number_format($total); ?> ITEMS</span></p>
            </div>
        </div>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="bib-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาเนื้อหาบรรณานุกรม, ชื่อผู้ใช้...' : 'Search bibliography text, username...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="filter-type">
            <option value=""><?php echo $currentLang === 'th' ? 'ทุกประเภททรัพยากร' : 'All Resource Types'; ?></option>
            <?php foreach ($resourceTypes as $rt): ?>
                <option value="<?php echo $rt['id']; ?>" <?php echo $filterType == $rt['id'] ? 'selected' : ''; ?>>
                    <?php echo $currentLang === 'th' ? $rt['name_th'] : $rt['name_en']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="filter-select" id="filter-user">
            <option value=""><?php echo $currentLang === 'th' ? 'ผู้เขียนทุกคน' : 'All Authors'; ?></option>
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
        <?php if (empty($bibliographies)): ?>
            <div class="empty-container slide-up stagger-2">
                <div class="empty-state">
                    <i class="fas fa-book-open fa-3x mb-4" style="color: var(--gray-200);"></i>
                    <p class="text-secondary"><?php echo __('no_bibliography'); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="bib-list">
                <?php foreach ($bibliographies as $index => $bib): ?>
                    <div class="bib-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="bib-rank">
                            <?php echo $offset + $index + 1; ?>
                        </div>

                        <div class="type-icon-wrapper" title="<?php echo $currentLang === 'th' ? $bib['name_th'] : $bib['name_en']; ?>">
                            <i class="fas <?php echo $bib['rt_icon']; ?>"></i>
                        </div>

                        <div class="bib-main-info">
                            <div class="bib-text-preview">
                                <?php echo htmlspecialchars(strip_tags($bib['bibliography_text'])); ?>
                            </div>
                            <div class="bib-meta">
                                <span class="owner-name"><i class="far fa-user"></i> @<?php echo htmlspecialchars($bib['username']); ?></span>
                                <span class="badge-type">
                                    <i class="fas <?php echo $bib['rt_icon']; ?>"></i>
                                    <?php echo $currentLang === 'th' ? $bib['name_th'] : $bib['name_en']; ?>
                                </span>
                                <span class="bib-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo formatThaiDate($bib['created_at']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="action-btn" onclick="editBib(<?php echo htmlspecialchars(json_encode($bib)); ?>)" title="<?php echo __('edit'); ?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="action-btn" onclick="viewBibDetails(<?php echo htmlspecialchars(json_encode($bib)); ?>)" title="<?php echo __('view'); ?>">
                                <i class="far fa-eye"></i>
                            </button>
                            <button class="action-btn danger" onclick="confirmDelete(<?php echo $bib['id']; ?>)" title="<?php echo __('delete'); ?>">
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
    const searchInput = document.getElementById('bib-search');
    const filterType = document.getElementById('filter-type');
    const filterUser = document.getElementById('filter-user');
    const sortOrder = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('type', filterType.value);
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

    [filterType, filterUser, sortOrder].forEach(el => {
        el.addEventListener('change', updateFilters);
    });

    function editBib(bib) {
        Modal.create({
            title: '<i class="fas fa-edit" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "แก้ไขบรรณานุกรม" : "Edit Bibliography"; ?>',
            content: `
                <div style="padding: 10px;">
                    <form id="edit-bib-form">
                        <input type="hidden" name="id" value="${bib.id}">
                        
                        <div class="form-group mb-4">
                            <label class="form-label font-bold mb-2 block"><?php echo __('bibliography'); ?> (APA 7<sup>th</sup>
                            <textarea name="bibliography_text" class="form-input" style="height: 120px; line-height: 1.6; padding: 15px;">${bib.bibliography_text}</textarea>
                            <small class="text-tertiary mt-1 block"><?php echo $currentLang === 'th' ? 'สามารถใช้แท็ก &lt;i&gt; สำหรับตัวเอียงได้' : 'Can use &lt;i&gt; tags for italics.'; ?></small>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label font-bold mb-2 block"><?php echo __('resource_type'); ?></label>
                                <select name="resource_type_id" class="form-input">
                                    <?php foreach ($resourceTypes as $rt): ?>
                                        <option value="<?php echo $rt['id']; ?>" ${bib.resource_type_id == <?php echo $rt['id']; ?> ? 'selected' : ''}>
                                            <?php echo $currentLang === 'th' ? $rt['name_th'] : $rt['name_en']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-bold mb-2 block"><?php echo $currentLang === 'th' ? 'ปีที่พิมพ์' : 'Year'; ?></label>
                                <input type="number" name="year" class="form-input" value="${bib.year || ''}" placeholder="2024">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label font-bold mb-2 block"><?php echo __('citation_parenthetical'); ?></label>
                                <input type="text" name="citation_parenthetical" class="form-input" value="${bib.citation_parenthetical || ''}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-bold mb-2 block"><?php echo __('citation_narrative'); ?></label>
                                <input type="text" name="citation_narrative" class="form-input" value="${bib.citation_narrative || ''}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-bold mb-2 block"><?php echo $currentLang === 'th' ? 'ภาษา' : 'Language'; ?></label>
                            <div style="display: flex; gap: 20px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="language" value="th" ${bib.language === 'th' ? 'checked' : ''}> <?php echo $currentLang === 'th' ? 'ภาษาไทย' : 'Thai'; ?>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="language" value="en" ${bib.language === 'en' ? 'checked' : ''}> <?php echo $currentLang === 'th' ? 'ภาษาอังกฤษ' : 'English'; ?>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            `,
            footer: `
                <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
                    <button class="btn btn-ghost" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="btn btn-primary" id="btn-save-bib" style="padding: 10px 25px; border-radius: 12px; font-weight: 700;">
                        <i class="fas fa-save mr-2"></i> <?php echo __('save'); ?>
                    </button>
                </div>
            `,
            onOpen: (modal) => {
                const btn = modal.querySelector('#btn-save-bib');
                const form = modal.querySelector('#edit-bib-form');

                btn.addEventListener('click', async () => {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?php echo $currentLang === 'th' ? "กำลังบันทึก..." : "Saving..."; ?>';

                    try {
                        const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-bibliography.php', data);
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

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url.toString();
    }

    function viewBibDetails(bib) {
        Modal.create({
            title: '<i class="fas fa-book-reader" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "รายละเอียดบรรณานุกรม" : "Bibliography Details"; ?>',
            content: `
                <div style="padding: 10px;">
                    <div style="background: var(--gray-50); padding: 20px; border-radius: 16px; margin-bottom: 20px; border: 1px solid var(--gray-100);">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div class="type-icon-wrapper" style="width: 50px; height: 50px; border-radius: 14px; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <i class="fas ${bib.rt_icon}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text-primary); font-size: 15px;">${'<?php echo $currentLang; ?>' === 'th' ? bib.name_th : bib.name_en}</div>
                                <div style="font-size: 13px; color: var(--primary); font-weight: 700;">@${bib.username} (${bib.user_name})</div>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 12px; font-size: 14px; line-height: 1.6; color: var(--text-primary); border: 1px solid var(--gray-100);">
                            ${bib.bibliography_text}
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo __('resource_type'); ?></div>
                            <div style="font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                                <i class="fas ${bib.rt_icon}" style="color: var(--primary);"></i>
                                ${'<?php echo $currentLang; ?>' === 'th' ? bib.name_th : bib.name_en}
                            </div>
                        </div>
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo __('created_at'); ?></div>
                            <div style="font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                                <i class="far fa-calendar-alt" style="color: var(--primary);"></i>
                                ${bib.created_at}
                            </div>
                        </div>
                    </div>
                </div>
            `,
            footer: `<button class="btn btn-primary" onclick="Modal.close(this)" style="border-radius: 12px; padding: 10px 25px; font-weight: 700;"><?php echo __('close'); ?></button>`
        });
    }

    function confirmDelete(id) {
        const adminUsername = '<?php echo $_SESSION['username']; ?>';

        Modal.create({
            title: '<i class="fas fa-trash-alt" style="color: #EF4444; margin-right: 10px;"></i> <?php echo __('delete_bibliography'); ?>',
            content: `
                <div style="padding: 10px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.5;">
                        <?php echo __('delete_confirm'); ?>
                    </p>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 12px; border: 1px solid #FEE2E2; margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #991B1B; margin-bottom: 8px;">
                            <?php echo $currentLang === 'th' ? "กรุณาพิมพ์ชื่อผู้ใช้ของคุณ (" . $_SESSION['user']['username'] . ") เพื่อยืนยัน" : "Please type your username (" . $_SESSION['user']['username'] . ") to confirm"; ?>
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
                        const response = await API.delete('<?php echo SITE_URL; ?>/api/bibliography/delete.php', {
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