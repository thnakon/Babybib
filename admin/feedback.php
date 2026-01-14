<?php

/**
 * Babybib - Admin Feedback Page
 * ===============================
 */

require_once '../includes/session.php';

$pageTitle = 'ข้อเสนอแนะ';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;
$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

try {
    $db = getDB();

    $where = ["1=1"];
    $params = [];

    if ($status) {
        $where[] = "f.status = ?";
        $params[] = $status;
    }

    if ($search) {
        $where[] = "(f.subject LIKE ? OR f.message LIKE ? OR u.username LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }

    $whereClause = implode(' AND ', $where);

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM feedback f LEFT JOIN users u ON f.user_id = u.id WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    $stmt = $db->prepare("
        SELECT f.*, u.username, u.name as user_name, u.email as user_email
        FROM feedback f 
        LEFT JOIN users u ON f.user_id = u.id
        WHERE $whereClause
        ORDER BY f.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $feedbacks = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Admin feedback error: " . $e->getMessage());
    $feedbacks = [];
    $total = 0;
    $totalPages = 0;
}
?>



<div class="admin-feedback-wrapper">
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('feedback_management'); ?></h1>
                <p><?php echo $currentLang === 'th' ? "ข้อเสนอแนะและแจ้งปัญหาที่ส่งมา " : "Feedback and reports from users "; ?><span class="badge-lis"><?php echo number_format($total); ?> ITEMS</span></p>
            </div>
        </div>
    </header>

    <div class="toolbar-card slide-up stagger-1">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="feedback-search" class="search-input"
                placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาหัวข้อ, ชื่อผู้ใช้...' : 'Search subject, username...'; ?>"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select class="filter-select" id="status-filter">
            <option value=""><?php echo $currentLang === 'th' ? 'ทุกสถานะ' : 'All Status'; ?></option>
            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'รอดำเนินการ' : 'Pending'; ?></option>
            <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'อ่านแล้ว' : 'Read'; ?></option>
            <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'แก้ไขแล้ว' : 'Resolved'; ?></option>
        </select>
    </div>

    <div class="content-section">
        <?php if (empty($feedbacks)): ?>
            <div style="padding: 100px 0; text-align: center; background: white; border-radius: 20px; border: 2px dashed var(--gray-100);" class="slide-up stagger-2">
                <i class="fas fa-comments-slash fa-3x mb-4" style="color: var(--gray-200);"></i>
                <p class="text-secondary"><?php echo $currentLang === 'th' ? 'ไม่พบข้อเสนอแนะ' : 'No feedback found'; ?></p>
            </div>
        <?php else: ?>
            <div class="feedback-list">
                <?php foreach ($feedbacks as $index => $fb): ?>
                    <div class="feedback-card slide-up stagger-<?php echo ($index % 5) + 2; ?>">
                        <div class="feedback-status-icon <?php echo 'status-' . $fb['status']; ?>">
                            <i class="fas <?php echo $fb['status'] === 'pending' ? 'fa-clock' : ($fb['status'] === 'read' ? 'fa-eye' : 'fa-check-circle'); ?>"></i>
                        </div>

                        <div class="feedback-main-info">
                            <h3 class="feedback-subject">
                                <?php echo htmlspecialchars($fb['subject']); ?>
                                <span class="status-badge <?php echo 'status-' . $fb['status']; ?>">
                                    <?php echo $fb['status'] === 'pending' ? ($currentLang === 'th' ? 'รอดำเนินการ' : 'Pending') : ($fb['status'] === 'read' ? ($currentLang === 'th' ? 'อ่านแล้ว' : 'Read') : ($currentLang === 'th' ? 'แก้ไขแล้ว' : 'Resolved')); ?>
                                </span>
                            </h3>

                            <div class="feedback-message-preview">
                                <?php echo htmlspecialchars($fb['message']); ?>
                            </div>

                            <div class="feedback-meta">
                                <span><i class="far fa-user"></i> <?php echo $fb['username'] ? '@' . htmlspecialchars($fb['username']) : ($currentLang === 'th' ? 'ผู้ใช้งานทั่วไป' : 'Guest'); ?></span>
                                <span><i class="far fa-envelope"></i> <?php echo htmlspecialchars($fb['user_email'] ?: ($fb['email'] ?? 'N/A')); ?></span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo formatThaiDate($fb['created_at']); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <button class="action-btn" onclick="viewFeedbackDetails(<?php echo htmlspecialchars(json_encode($fb)); ?>)" title="<?php echo __('view'); ?>">
                                <i class="far fa-eye"></i>
                            </button>
                            <?php if ($fb['status'] === 'pending'): ?>
                                <button class="action-btn success" onclick="updateStatus(<?php echo $fb['id']; ?>, 'read')" title="<?php echo $currentLang === 'th' ? 'ทำเครื่องหมายว่าอ่านแล้ว' : 'Mark as Read'; ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php elseif ($fb['status'] === 'read'): ?>
                                <button class="action-btn success" onclick="updateStatus(<?php echo $fb['id']; ?>, 'resolved')" title="<?php echo $currentLang === 'th' ? 'ทำเครื่องหมายว่าแก้ไขแล้ว' : 'Mark as Resolved'; ?>">
                                    <i class="fas fa-check-double"></i>
                                </button>
                            <?php endif; ?>
                            <button class="action-btn danger" onclick="confirmDelete(<?php echo $fb['id']; ?>)" title="<?php echo __('delete'); ?>">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination slide-up">
            <button class="pagination-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>><i class="fas fa-chevron-left"></i></button>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <button class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)"><?php echo $i; ?></button>
            <?php endfor; ?>
            <button class="pagination-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>><i class="fas fa-chevron-right"></i></button>
        </div>
    <?php endif; ?>
</div>

<script>
    const statusFilter = document.getElementById('status-filter');
    const searchInput = document.getElementById('feedback-search');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('status', statusFilter.value);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    statusFilter.addEventListener('change', updateFilters);
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

    async function updateStatus(id, newStatus) {
        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/update-feedback.php', {
                id,
                status: newStatus
            });
            if (response.success) {
                Toast.success('<?php echo $currentLang === 'th' ? 'อัปเดตสถานะสำเร็จ' : 'Status updated'; ?>');
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo __('error_save'); ?>');
        }
    }

    function viewFeedbackDetails(fb) {
        Modal.create({
            title: '<i class="fas fa-comment-alt" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $currentLang === 'th' ? "รายละเอียดข้อเสนอแนะ" : "Feedback Details"; ?>',
            content: `
                <div style="padding: 10px;">
                    <div style="background: var(--gray-50); padding: 20px; border-radius: 16px; margin-bottom: 20px; border: 1px solid var(--gray-100);">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <div class="feedback-status-icon status-${fb.status}" style="width: 50px; height: 50px; border-radius: 14px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas ${fb.status === 'pending' ? 'fa-clock' : (fb.status === 'read' ? 'fa-eye' : 'fa-check-circle')}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text-primary); font-size: 16px;">${fb.subject}</div>
                                <div style="font-size: 13px; color: var(--text-tertiary);">${fb.created_at}</div>
                            </div>
                        </div>
                        <div style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--gray-100); font-size: 15px; color: var(--text-primary); line-height: 1.8; white-space: pre-wrap;">${fb.message}</div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo $currentLang === 'th' ? 'ผู้ส่ง' : 'Sender'; ?></div>
                            <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px;"><i class="far fa-user"></i> ${fb.username ? '@' + fb.username : 'Guest'}</div>
                        </div>
                        <div style="padding: 12px; background: white; border: 1px solid var(--gray-100); border-radius: 12px;">
                            <div style="font-size: 10px; color: var(--text-tertiary); text-transform: uppercase; font-weight: 800; margin-bottom: 5px;"><?php echo $currentLang === 'th' ? 'อีเมล' : 'Email'; ?></div>
                            <div style="font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;"><i class="far fa-envelope" style="color: var(--primary);"></i> ${fb.user_email || fb.email || 'N/A'}</div>
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
            title: '<i class="fas fa-trash-alt" style="color: #EF4444; margin-right: 10px;"></i> <?php echo $currentLang === 'th' ? "ลบข้อเสนอแนะ" : "Delete Feedback"; ?>',
            content: `
                <div style="padding: 10px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;"><?php echo $currentLang === 'th' ? "คุณแน่ใจหรือไม่ที่จะลบข้อเสนอแนะนี้?" : "Are you sure you want to delete this feedback?"; ?></p>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 12px; border: 1px solid #FEE2E2;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #991B1B; margin-bottom: 8px;">ยืนยันโดยพิมพ์ชื่อผู้ใช้ ($adminUsername)</label>
                        <input type="text" id="delete-confirm-username" class="form-input" placeholder="Username ของคุณ" autocomplete="off">
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
                    try {
                        const response = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-feedback.php', {
                            id
                        });
                        if (response.success) {
                            Toast.success('ลบสำเร็จแล้ว');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            Toast.error(response.error);
                            btn.disabled = false;
                        }
                    } catch (e) {
                        Toast.error('เกิดข้อผิดพลาด');
                        btn.disabled = false;
                    }
                });
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>