<?php
/**
 * Babybib - Admin Feedback Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('admin_feedback_title');
$extraStyles = '';

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

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('feedback_management'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Review user suggestions and reported issues.
                <span class="ml-2 px-2 py-0.5 border border-vercel-gray-200 text-vercel-black rounded text-[10px] font-bold"><?php echo number_format($total); ?> ITEMS</span>
            </p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-vercel-gray-400"></i>
            <input type="text" id="feedback-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search subject or sender..."
                   class="w-full pl-10 pr-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="status-filter" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_status'); ?></option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Read</option>
                <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
            </select>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-vercel-gray-200">
        <?php if (empty($feedbacks)): ?>
            <div class="px-6 py-20 text-center text-vercel-gray-400 font-medium">No feedback entries found.</div>
        <?php else: ?>
            <?php foreach ($feedbacks as $fb): ?>
                <div class="group hover:bg-vercel-gray-100/50 transition-colors p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Status Indicator -->
                        <div class="w-10 h-10 rounded border border-vercel-gray-200 flex items-center justify-center transition-all
                            <?php echo $fb['status'] === 'pending' ? 'bg-vercel-yellow-100 text-vercel-yellow-600 border-vercel-yellow-200/50' : ($fb['status'] === 'read' ? 'bg-vercel-blue-100 text-vercel-blue-600 border-vercel-blue-200/50' : 'bg-vercel-green-100 text-vercel-green-600 border-vercel-green-200/50'); ?>">
                            <i data-lucide="<?php echo $fb['status'] === 'pending' ? 'clock' : ($fb['status'] === 'read' ? 'eye' : 'check-circle'); ?>" class="w-4 h-4"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                 <h3 class="text-base font-black text-vercel-black tracking-tight truncate"><?php echo htmlspecialchars($fb['subject']); ?></h3>
                                 <span class="px-2 py-0.5 border rounded text-[8px] font-black uppercase tracking-widest
                                    <?php echo $fb['status'] === 'pending' ? 'bg-vercel-yellow-50 text-vercel-yellow-600 border-vercel-yellow-200/50' : ($fb['status'] === 'read' ? 'bg-vercel-blue-50 text-vercel-blue-600 border-vercel-blue-200/50' : 'bg-vercel-green-50 text-vercel-green-600 border-vercel-green-200/50'); ?>">
                                    <?php echo $fb['status']; ?>
                                 </span>
                            </div>
                            
                            <div class="text-sm text-vercel-gray-500 line-clamp-1 mb-4 font-medium leading-relaxed">
                                <?php echo htmlspecialchars($fb['message']); ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 pt-4 border-t border-vercel-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-vercel-gray-100 flex items-center justify-center text-[7px] font-black text-vercel-gray-400">
                                        <i data-lucide="user" class="w-2.5 h-2.5"></i>
                                    </div>
                                    <span class="text-[11px] font-bold text-vercel-black">@<?php echo htmlspecialchars($fb['username'] ?: 'Guest'); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="mail" class="w-3 h-3 text-vercel-gray-300"></i>
                                    <span class="text-[11px] text-vercel-gray-400 font-medium"><?php echo htmlspecialchars($fb['user_email'] ?: ($fb['email'] ?? 'N/A')); ?></span>
                                </div>
                                <div class="flex items-center gap-2 ml-auto">
                                    <i data-lucide="calendar" class="w-3 h-3 text-vercel-gray-300"></i>
                                    <span class="text-[11px] text-vercel-gray-400 font-black uppercase tracking-widest"><?php echo formatThaiDate($fb['created_at']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex md:flex-col items-start justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="viewFeedbackDetails(<?php echo htmlspecialchars(json_encode($fb)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            
                            <?php if ($fb['status'] === 'pending'): ?>
                                <button onclick="updateStatus(<?php echo $fb['id']; ?>, 'read')" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-blue transition-colors">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            <?php elseif ($fb['status'] === 'read'): ?>
                                <button onclick="updateStatus(<?php echo $fb['id']; ?>, 'resolved')" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-green transition-colors">
                                    <i data-lucide="check-check" class="w-4 h-4"></i>
                                </button>
                            <?php endif; ?>

                            <button onclick="confirmDelete(<?php echo $fb['id']; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-vercel-gray-200 pt-8 mt-4">
            <div class="text-xs text-vercel-gray-400 font-medium">
                Showing <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> to <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span> of <span class="text-vercel-black font-bold"><?php echo $total; ?></span> feedback items
            </div>
            <div class="flex items-center gap-1">
                <button onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-all">
                    Previous
                </button>
                <div class="px-4 py-1.5 text-xs font-black text-vercel-black">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </div>
                <button onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-all">
                    Next
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    const statusSelect = document.getElementById('status-filter');
    const searchInput = document.getElementById('feedback-search');

    function updateFilters() {
        const url = new URL(window.location);
        if (statusSelect) url.searchParams.set('status', statusSelect.value);
        if (searchInput) url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(updateFilters, 500);
        });
    }

    if (statusSelect) statusSelect.addEventListener('change', updateFilters);

    function goToPage(p) {
        const url = new URL(window.location);
        url.searchParams.set('page', p);
        window.location = url.toString();
    }

    async function updateStatus(id, s) {
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-feedback.php', { id, status: s });
            if (res.success) { Toast.success('Status updated'); setTimeout(() => location.reload(), 800); }
        } catch (e) { Toast.error('Error saving'); }
    }

    // Modal Style Overrides
    const MODAL_CLASSES = {
        label: 'block text-[10px] font-bold text-vercel-gray-400 uppercase tracking-widest mb-2',
        input: 'w-full px-4 py-2.5 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all',
        btnPrimary: 'px-6 py-2 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all',
        btnSecondary: 'px-6 py-2 text-vercel-gray-500 hover:text-vercel-black font-bold text-sm transition-all'
    };

    function viewFeedbackDetails(fb) {
        Modal.create({
            title: 'Feedback Details',
            content: `
                <div class="space-y-8 py-6">
                    <div class="p-8 border border-vercel-gray-200 rounded-lg">
                        <div class="flex items-center gap-6 mb-8 pb-8 border-b border-vercel-gray-100">
                            <div class="w-12 h-12 rounded border border-vercel-gray-100 flex items-center justify-center
                                ${fb.status === 'pending' ? 'bg-vercel-yellow-50 text-vercel-yellow-600' : (fb.status === 'read' ? 'bg-vercel-blue-50 text-vercel-blue-600' : 'bg-vercel-green-50 text-vercel-green-600')}">
                                <i data-lucide="${fb.status === 'pending' ? 'clock' : (fb.status === 'read' ? 'eye' : 'check-circle')}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-black text-vercel-black tracking-tight leading-tight">${fb.subject}</h4>
                                <p class="text-[10px] text-vercel-gray-400 font-black uppercase tracking-widest mt-2">${fb.created_at}</p>
                            </div>
                        </div>
                        <div class="text-sm font-medium leading-relaxed text-vercel-black bg-vercel-gray-100/30 p-6 rounded-md border border-vercel-gray-100 whitespace-pre-wrap">
                            ${fb.message}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-px bg-vercel-gray-200 rounded border border-vercel-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-white p-6">
                             <label class="${MODAL_CLASSES.label}">Inscribed by</label>
                             <div class="text-sm font-black text-vercel-black truncate">@${fb.username || 'Guest'}</div>
                        </div>
                        <div class="bg-white p-6">
                             <label class="${MODAL_CLASSES.label}">Email channel</label>
                             <div class="text-sm font-medium text-vercel-gray-500 truncate">${fb.user_email || fb.email || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            `,
            footer: `<button class="w-full py-3 bg-vercel-black text-white font-black text-xs uppercase tracking-widest rounded-md hover:bg-vercel-gray-800 transition-all" onclick="Modal.close(this)">Close</button>`
        });
        if (window.lucide) lucide.createIcons();
    }

    function confirmDelete(id) {
        const adminUser = '<?php echo $_SESSION['username']; ?>';
        Modal.create({
            title: 'Delete Feedback',
            content: `
                <div class="space-y-6 pt-4">
                    <p class="text-vercel-red text-sm font-bold border border-vercel-red/20 p-4 bg-vercel-red/5 rounded">Warning: This entry will be permanently removed from the records.</p>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Type your username to confirm: <span class="text-vercel-black underline">${adminUser}</span></label>
                        <input type="text" id="del-fb-conf" class="${MODAL_CLASSES.input} border-vercel-red/20 focus:border-vercel-red" placeholder="...">
                    </div>
                </div>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del-fb">Confirm Deletion</button>
                </div>
            `,
            onOpen: (m) => {
                m.querySelector('#exec-del-fb').onclick = async () => {
                    if (m.querySelector('#del-p-conf') && m.querySelector('#del-fb-conf').value !== adminUser) return Toast.error('Invalid confirmation');
                    if (m.querySelector('#del-fb-conf').value !== adminUser) return Toast.error('Invalid confirmation');
                    try {
                        const res = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-feedback.php', { id });
                        if (res.success) { Toast.success('Feedback deleted'); setTimeout(() => location.reload(), 800); }
                    } catch (e) { Toast.error('Delete failed'); }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>