<?php
/**
 * Babybib - Admin Bibliographies Management (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('admin_bibs_title');
$extraStyles = '';

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

    // Get bibliographies
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

    // Filters data
    $resourceTypes = $db->query("SELECT id, name_th, name_en, icon FROM resource_types ORDER BY name_th ASC")->fetchAll();
    $authors = $db->query("SELECT DISTINCT u.id, u.username, u.name FROM users u JOIN bibliographies b ON u.id = b.user_id ORDER BY u.username ASC")->fetchAll();
} catch (Exception $e) {
    error_log("Admin bibliographies error: " . $e->getMessage());
    $bibliographies = [];
    $total = 0;
    $totalPages = 0;
}

// Map Font Awesome icons to Lucide icons (fallback if necessary)
function getLucideIcon($fa) {
    $map = [
        'fa-book' => 'book',
        'fa-newspaper' => 'newspaper',
        'fa-journal-whills' => 'book-open',
        'fa-globe' => 'globe',
        'fa-video' => 'video',
        'fa-file-pdf' => 'file-text',
        'fa-graduation-cap' => 'graduation-cap',
        'fa-file-lines' => 'file-text',
        'fa-scroll' => 'scroll'
    ];
    return $map[$fa] ?? 'file-text';
}
?>

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('bibliography_management'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Review and manage all bibliographic entries in the system.
                <span class="ml-2 px-2 py-0.5 border border-vercel-gray-200 text-vercel-black rounded text-[10px] font-bold"><?php echo number_format($total); ?> ITEMS</span>
            </p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-vercel-gray-400"></i>
            <input type="text" id="bib-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search content or author..."
                   class="w-full pl-10 pr-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="filter-type" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_bib_types'); ?></option>
                <?php foreach ($resourceTypes as $rt): ?>
                    <option value="<?php echo $rt['id']; ?>" <?php echo $filterType == $rt['id'] ? 'selected' : ''; ?>>
                        <?php echo $currentLang === 'th' ? $rt['name_th'] : $rt['name_en']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filter-user" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_authors'); ?></option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo $author['id']; ?>" <?php echo $filterUser == $author['id'] ? 'selected' : ''; ?>>
                        @<?php echo htmlspecialchars($author['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="sort-order" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
            </select>
        </div>
    </div>

    <!-- Bibliography List -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-vercel-gray-200">
        <?php if (empty($bibliographies)): ?>
            <div class="px-6 py-20 text-center text-vercel-gray-400 font-medium">No bibliographies match your criteria.</div>
        <?php else: ?>
            <?php foreach ($bibliographies as $bib): ?>
                <div class="group hover:bg-vercel-gray-100/50 transition-colors p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Icon area -->
                        <div class="w-10 h-10 rounded border border-vercel-gray-200 flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                            <i data-lucide="<?php echo getLucideIcon($bib['rt_icon']); ?>" class="w-4 h-4"></i>
                        </div>

                        <!-- Content area -->
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-vercel-black leading-relaxed mb-3">
                                <?php echo htmlspecialchars(strip_tags($bib['bibliography_text'])); ?>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-4">
                                <span class="px-2 py-0.5 border border-vercel-gray-100 bg-vercel-gray-100 text-vercel-gray-500 rounded text-[10px] font-black uppercase tracking-widest">
                                    <?php echo $currentLang === 'th' ? $bib['name_th'] : $bib['name_en']; ?>
                                </span>
                                <span class="text-[11px] text-vercel-gray-400 flex items-center gap-1 font-medium">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <?php echo formatThaiDate($bib['created_at']); ?>
                                </span>
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-vercel-gray-100 flex items-center justify-center text-[8px] font-black text-vercel-gray-400 overflow-hidden">
                                        <?php if (!empty($bib['profile_picture'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($bib['profile_picture']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($bib['user_name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[11px] text-vercel-gray-500 font-bold">@<?php echo htmlspecialchars($bib['username']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action area -->
                        <div class="flex items-start justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="viewBibDetails(<?php echo htmlspecialchars(json_encode($bib)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <button onclick="editBib(<?php echo htmlspecialchars(json_encode($bib)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button onclick="confirmDelete(<?php echo $bib['id']; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors">
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
                Showing <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> to <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span> of <span class="text-vercel-black font-bold"><?php echo $total; ?></span> bibliographies
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
    const searchInput = document.getElementById('bib-search');
    const typeSelect = document.getElementById('filter-type');
    const userSelect = document.getElementById('filter-user');
    const sortSelect = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('type', typeSelect.value);
        url.searchParams.set('user_id', userSelect.value);
        url.searchParams.set('sort', sortSelect.value);
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 500);
    });

    [typeSelect, userSelect, sortSelect].forEach(el => {
        if (el) el.addEventListener('change', updateFilters);
    });

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

    function editBib(bib) {
        Modal.create({
            title: 'Edit Bibliography',
            content: `
                <form id="edit-bib-form" class="space-y-6 pt-4">
                    <input type="hidden" name="id" value="${bib.id}">
                    <div>
                        <label class="${MODAL_CLASSES.label}">Bibliography Text (APA 7th)</label>
                        <textarea name="bibliography_text" rows="5" class="${MODAL_CLASSES.input}">${bib.bibliography_text}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="${MODAL_CLASSES.label}">Resource Type</label>
                            <select name="resource_type_id" class="${MODAL_CLASSES.input}">
                                <?php foreach ($resourceTypes as $rt): ?>
                                    <option value="<?php echo $rt['id']; ?>" ${bib.resource_type_id == <?php echo $rt['id']; ?> ? 'selected' : ''}>
                                        <?php echo $currentLang === 'th' ? $rt['name_th'] : $rt['name_en']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">Year</label>
                            <input type="text" name="year" value="${bib.year || ''}" class="${MODAL_CLASSES.input}">
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center justify-end gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary}" onclick="Modal.close(this)">Cancel</button>
                    <button class="${MODAL_CLASSES.btnPrimary}" onclick="submitEditBib(this)">Save Changes</button>
                </div>
            `
        });
    }

    async function submitEditBib(btn) {
        const form = document.getElementById('edit-bib-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-bibliography.php', data);
            if (res.success) { Toast.success(res.message); setTimeout(() => location.reload(), 800); }
            else { Toast.error(res.error); setLoading(btn, false); }
        } catch (e) { Toast.error('Failed to save'); setLoading(btn, false); }
    }

    function viewBibDetails(bib) {
        Modal.create({
            title: 'Entry Overview',
            content: `
                <div class="space-y-8 py-6">
                    <div class="p-8 border border-vercel-gray-200 rounded-lg">
                        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-vercel-gray-100">
                            <div class="w-10 h-10 border border-vercel-gray-200 rounded flex items-center justify-center text-vercel-gray-400">
                                <i data-lucide="${getLucideIcon(bib.rt_icon)}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-vercel-black uppercase tracking-tight">${'<?php echo $currentLang; ?>' === 'th' ? bib.name_th : bib.name_en}</h4>
                                <p class="text-[11px] text-vercel-gray-400 font-bold uppercase tracking-widest mt-1">By @${bib.username}</p>
                            </div>
                        </div>
                        <div class="text-sm font-medium leading-relaxed text-vercel-black italic italic bg-vercel-gray-100/30 p-4 rounded-md border border-vercel-gray-100">
                            ${bib.bibliography_text}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-px bg-vercel-gray-200 rounded border border-vercel-gray-200 overflow-hidden">
                        <div class="bg-white p-6">
                            <div class="${MODAL_CLASSES.label}">Created date</div>
                            <div class="text-sm font-bold text-vercel-black">${bib.created_at}</div>
                        </div>
                         <div class="bg-white p-6">
                            <div class="${MODAL_CLASSES.label}">Language</div>
                            <div class="text-sm font-bold text-vercel-black uppercase tracking-widest">${bib.language}</div>
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
            title: 'Delete Bibliography',
            content: `
                <div class="space-y-6 pt-4">
                    <p class="text-vercel-red text-sm font-bold">Warning: This entry will be permanently removed from the system.</p>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Confirm with admin username: <span class="text-vercel-black underline">${adminUser}</span></label>
                        <input type="text" id="del-bib-conf" class="${MODAL_CLASSES.input} border-vercel-red/20 focus:border-vercel-red" placeholder="...">
                    </div>
                </div>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="flex-1 ${MODAL_CLASSES.btnSecondary}" onclick="Modal.close(this)">Cancel</button>
                    <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del-bib">Delete Permanently</button>
                </div>
            `,
            onOpen: (m) => {
                m.querySelector('#exec-del-bib').onclick = async () => {
                    if (m.querySelector('#del-bib-conf').value !== adminUser) return Toast.error('Invalid confirmation');
                    try {
                        const res = await API.delete('<?php echo SITE_URL; ?>/api/bibliography/delete.php', { id: id });
                        if (res.success) { Toast.success('Deleted successfully'); setTimeout(() => location.reload(), 800); }
                    } catch (e) { Toast.error('Delete failed'); }
                }
            }
        });
    }

    function getLucideIcon(fa) {
        const map = { 'fa-book': 'book', 'fa-newspaper': 'newspaper', 'fa-journal-whills': 'book-open', 'fa-globe': 'globe', 'fa-video': 'video', 'fa-file-pdf': 'file-text', 'fa-graduation-cap': 'graduation-cap', 'fa-file-lines': 'file-text', 'fa-scroll': 'scroll' };
        return map[fa] || 'file-text';
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>