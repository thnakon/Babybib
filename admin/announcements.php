<?php
/**
 * Babybib - Admin Announcements Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('announcements');
$extraStyles = '';

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

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('announcements'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Broadcast important news and updates to all system users.
                <span class="ml-2 px-2 py-0.5 border border-vercel-gray-200 text-vercel-black rounded text-[10px] font-bold"><?php echo number_format($total); ?> ITEMS</span>
            </p>
        </div>
        <button onclick="showCreateModal()" class="px-6 py-2.5 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span><?php echo __('admin_create_ann'); ?></span>
        </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-vercel-gray-400"></i>
            <input type="text" id="ann-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search titles or content..."
                   class="w-full pl-10 pr-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="status-filter" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_status'); ?></option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="hidden" <?php echo $status === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
            </select>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="space-y-6">
        <?php if (empty($announcements)): ?>
            <div class="py-20 text-center text-vercel-gray-400 font-medium border border-dashed border-vercel-gray-200 rounded-lg">No announcements found.</div>
        <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
                <div class="bg-white border border-vercel-gray-200 rounded-lg p-8 group hover:border-vercel-black transition-colors relative">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-black text-vercel-black tracking-tight group-hover:underline decoration-vercel-gray-200 underline-offset-4"><?php echo htmlspecialchars($ann['title']); ?></h3>
                                <span class="px-2 py-0.5 border rounded text-[8px] font-black uppercase tracking-widest
                                    <?php echo $ann['is_active'] ? 'bg-vercel-green-50 text-vercel-green-600 border-vercel-green-200/50' : 'bg-vercel-gray-50 text-vercel-gray-500 border-vercel-gray-200/50'; ?>">
                                    <?php echo $ann['is_active'] ? 'Active' : 'Hidden'; ?>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                                <div class="flex items-center gap-1.5 text-[11px] text-vercel-gray-500 font-bold italic">
                                    <div class="w-4 h-4 rounded bg-vercel-gray-100 flex items-center justify-center text-[7px] font-black text-vercel-gray-400">
                                         <?php echo strtoupper(substr($ann['username'] ?: 'A', 0, 1)); ?>
                                    </div>
                                    <span>@<?php echo htmlspecialchars($ann['username'] ?: 'Admin'); ?></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-vercel-gray-400 font-bold uppercase tracking-tight">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span><?php echo formatThaiDate($ann['created_at']); ?></span>
                                </div>
                                <?php if ($ann['updated_at'] && $ann['updated_at'] !== $ann['created_at']): ?>
                                     <div class="flex items-center gap-1.5 text-[10px] text-vercel-blue-500 font-black uppercase tracking-widest">
                                        <i data-lucide="history" class="w-3 h-3"></i>
                                        <span>Updated</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                             <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($ann)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button onclick="toggleAnnouncement(<?php echo $ann['id']; ?>, <?php echo $ann['is_active'] ? '0' : '1'; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors" title="<?php echo $ann['is_active'] ? 'Hide' : 'Show'; ?>">
                                <i data-lucide="<?php echo $ann['is_active'] ? 'eye-off' : 'eye'; ?>" class="w-4 h-4"></i>
                            </button>
                            <button onclick="confirmDelete(<?php echo $ann['id']; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-sm font-medium text-vercel-gray-600 leading-relaxed bg-vercel-gray-100/30 p-6 rounded-md border border-vercel-gray-100 whitespace-pre-wrap">
                        <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-vercel-gray-200 pt-8 mt-4">
            <div class="text-xs text-vercel-gray-400 font-medium">
                Showing <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> to <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span> of <span class="text-vercel-black font-bold"><?php echo $total; ?></span> announcements
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
    const searchInput = document.getElementById('ann-search');
    const statusSelect = document.getElementById('status-filter');

    function updateFilters() {
        const url = new URL(window.location);
        if (searchInput) url.searchParams.set('search', searchInput.value.trim());
        if (statusSelect) url.searchParams.set('status', statusSelect.value);
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

    // Modal Style Overrides
    const MODAL_CLASSES = {
        label: 'block text-[10px] font-bold text-vercel-gray-400 uppercase tracking-widest mb-2',
        input: 'w-full px-4 py-2.5 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all',
        btnPrimary: 'px-6 py-2 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all',
        btnSecondary: 'px-6 py-2 text-vercel-gray-500 hover:text-vercel-black font-bold text-sm transition-all'
    };

    function showCreateModal() {
        Modal.create({
            title: 'New Announcement',
            content: `
                <form id="create-ann-form" class="space-y-6 pt-4">
                    <div>
                        <label class="${MODAL_CLASSES.label}">Headline</label>
                        <input type="text" name="title" class="${MODAL_CLASSES.input}">
                    </div>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Message Content</label>
                        <textarea name="content" rows="6" class="${MODAL_CLASSES.input}"></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-vercel-gray-50 rounded-md border border-vercel-gray-100">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-10 h-5 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-vercel-black"></div>
                        </label>
                        <span class="text-[11px] font-black text-vercel-black uppercase tracking-tight">Deploy immediately</span>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="${MODAL_CLASSES.btnPrimary} flex-1" onclick="submitCreateAnn(this)">Create Announcement</button>
                </div>
            `
        });
    }

    async function submitCreateAnn(btn) {
        const form = document.getElementById('create-ann-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        data.is_active = data.is_active ? 1 : 0;
        
        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/create-announcement.php', data);
            if (res.success) { Toast.success('Success'); setTimeout(() => location.reload(), 800); }
            else { Toast.error(res.error); setLoading(btn, false); }
        } catch (e) { Toast.error('System error'); setLoading(btn, false); }
    }

    function showEditModal(ann) {
        Modal.create({
            title: 'Modify Announcement',
            content: `
                <form id="edit-ann-form" class="space-y-6 pt-4">
                    <input type="hidden" name="id" value="${ann.id}">
                    <div>
                        <label class="${MODAL_CLASSES.label}">Headline</label>
                        <input type="text" name="title" value="${ann.title}" class="${MODAL_CLASSES.input}">
                    </div>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Message Content</label>
                        <textarea name="content" rows="6" class="${MODAL_CLASSES.input}">${ann.content}</textarea>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-vercel-gray-50 rounded-md border border-vercel-gray-100">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" ${ann.is_active == 1 ? 'checked' : ''} class="sr-only peer">
                            <div class="w-10 h-5 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-vercel-black"></div>
                        </label>
                        <span class="text-[11px] font-black text-vercel-black uppercase tracking-tight">Active Visibility</span>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="${MODAL_CLASSES.btnPrimary} flex-1" onclick="submitEditAnn(this)">Save Changes</button>
                </div>
            `
        });
    }

    async function submitEditAnn(btn) {
        const form = document.getElementById('edit-ann-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        data.is_active = data.is_active ? 1 : 0;
        
        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-announcement.php', data);
            if (res.success) { Toast.success('Updated'); setTimeout(() => location.reload(), 800); }
            else { Toast.error(res.error); setLoading(btn, false); }
        } catch (e) { Toast.error('Error'); setLoading(btn, false); }
    }

    async function toggleAnnouncement(id, s) {
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-announcement.php', { id, is_active: s });
            if (res.success) { Toast.success('Status synced'); setTimeout(() => location.reload(), 800); }
        } catch (e) { Toast.error('Sync failed'); }
    }

    function confirmDelete(id) {
        const adminUser = '<?php echo $_SESSION['username']; ?>';
        Modal.create({
            title: 'Obliterate Announcement',
            content: `
                <div class="space-y-6 pt-4">
                    <p class="text-vercel-red text-sm font-bold border border-vercel-red/20 p-4 bg-vercel-red/5 rounded">Warning: This post will be permanently wiped from the archives.</p>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Type your username to confirm: <span class="text-vercel-black underline">${adminUser}</span></label>
                        <input type="text" id="del-ann-conf" class="${MODAL_CLASSES.input} border-vercel-red/20 focus:border-vercel-red" placeholder="...">
                    </div>
                </div>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del-ann">Confirm Wiping</button>
                </div>
            `,
            onOpen: (m) => {
                m.querySelector('#exec-del-ann').onclick = async () => {
                    if (m.querySelector('#del-ann-conf').value !== adminUser) return Toast.error('Invalid confirmation');
                    try {
                        const res = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-announcement.php', { id });
                        if (res.success) { Toast.success('Wiped'); setTimeout(() => location.reload(), 800); }
                    } catch (e) { Toast.error('Wipe failed'); }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>