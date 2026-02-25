<?php
/**
 * Babybib - Admin Projects Management (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('admin_projects_title');
$extraStyles = '';

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

    // Get projects
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

    // Authors for filter
    $authors = $db->query("SELECT DISTINCT u.id, u.username, u.name FROM users u JOIN projects p ON u.id = p.user_id ORDER BY u.username ASC")->fetchAll();
} catch (Exception $e) {
    error_log("Admin projects error: " . $e->getMessage());
    $projects = [];
    $total = 0;
    $totalPages = 0;
}
?>

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('project_management'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Overview of all research projects created by users.
                <span class="ml-2 px-2 py-0.5 border border-vercel-gray-200 text-vercel-black rounded text-[10px] font-bold"><?php echo number_format($total); ?> PROJECTS</span>
            </p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-vercel-gray-400"></i>
            <input type="text" id="project-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Search project name or owner..."
                   class="w-full pl-10 pr-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="filter-user" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_owners'); ?></option>
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

    <!-- Projects Table -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
        <div class="px-8 py-6 border-b border-vercel-gray-100 bg-vercel-gray-50/50 flex items-center justify-between">
            <h3 class="text-xs font-black text-vercel-black uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="folder" class="w-4 h-4"></i>
                <?php echo __('project_management'); ?>
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <?php if (empty($projects)): ?>
                <div class="py-20 text-center text-vercel-gray-400 font-medium">No projects match your criteria.</div>
            <?php else: ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-vercel-gray-50/50">
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest">Project</th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest">Owner</th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest text-center">Entries</th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest">Created</th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-vercel-gray-100">
                        <?php foreach ($projects as $p): ?>
                            <tr class="hover:bg-vercel-gray-50/40 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-3 h-3 rounded-full shrink-0" style="background: <?php echo htmlspecialchars($p['color']); ?>"></div>
                                        <div>
                                            <div class="text-sm font-bold text-vercel-black tracking-tight mb-1"><?php echo htmlspecialchars($p['name']); ?></div>
                                            <div class="text-xs text-vercel-gray-500 font-medium max-w-md truncate"><?php echo htmlspecialchars($p['description'] ?: 'No description provided.'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-vercel-gray-100 flex items-center justify-center text-[10px] font-black text-vercel-gray-400 overflow-hidden shrink-0 border border-vercel-gray-200">
                                            <?php if (!empty($p['profile_picture'])): ?>
                                                <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($p['profile_picture']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($p['user_name'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs font-bold text-vercel-gray-600">@<?php echo htmlspecialchars($p['username']); ?></div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-full bg-vercel-gray-100 text-xs font-black text-vercel-black border border-vercel-gray-200">
                                        <?php echo number_format($p['bibliography_count']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo formatThaiDate($p['created_at']); ?></div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="viewProjectDetails(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors" title="View Details">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="editProject(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors" title="Edit Project">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $p['id']; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors" title="Delete Project">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-vercel-gray-200 pt-8 mt-4">
            <div class="text-xs text-vercel-gray-400 font-medium">
                Showing <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> to <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span> of <span class="text-vercel-black font-bold"><?php echo $total; ?></span> projects
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
    const searchInput = document.getElementById('project-search');
    const userSelect = document.getElementById('filter-user');
    const sortSelect = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
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

    [userSelect, sortSelect].forEach(el => {
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

    function viewProjectDetails(project) {
        Modal.create({
            title: 'Project Overview',
            content: `
                <div class="space-y-10 py-6">
                    <div class="flex items-center gap-8 pb-8 border-b border-vercel-gray-100">
                        <div class="w-20 h-20 rounded border border-vercel-gray-200 flex items-center justify-center text-3xl shadow-inner relative" style="color: ${project.color}">
                            <i data-lucide="folder"></i>
                            <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white shadow-sm" style="background: ${project.color}"></div>
                        </div>
                        <div>
                             <h4 class="text-3xl font-black text-vercel-black tracking-tight leading-tight">${project.name}</h4>
                             <p class="text-[11px] font-black text-vercel-gray-400 uppercase tracking-wider mt-2 italic">Creator: @${project.username}</p>
                        </div>
                    </div>
                    <div class="space-y-8">
                        <div class="p-6 border border-vercel-gray-200 rounded-lg">
                            <label class="${MODAL_CLASSES.label}">Project Goal / Description</label>
                            <p class="text-sm text-vercel-black leading-relaxed font-medium mt-3">
                                ${project.description || 'No description provided.'}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-px bg-vercel-gray-200 rounded border border-vercel-gray-200 overflow-hidden shadow-sm">
                            <div class="bg-white p-6 text-center group">
                                <span class="text-3xl font-black text-vercel-black block group-hover:scale-110 transition-transform">${project.bibliography_count}</span>
                                <span class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest mt-2 block">Entries collected</span>
                            </div>
                            <div class="bg-white p-6 text-center group">
                                <span class="text-3xl font-black text-vercel-black block group-hover:scale-110 transition-transform" style="color: ${project.color}">●</span>
                                <span class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest mt-2 block">Theme color</span>
                            </div>
                        </div>
                        <div class="text-[10px] text-vercel-gray-400 font-bold uppercase tracking-widest">
                            Initialized on: <span class="text-vercel-black">${project.created_at}</span>
                        </div>
                    </div>
                </div>
            `,
            footer: `<button class="w-full py-3 bg-vercel-black text-white font-black text-xs uppercase tracking-widest rounded-md hover:bg-vercel-gray-800 transition-all" onclick="Modal.close(this)">Close</button>`
        });
        if (window.lucide) lucide.createIcons();
    }

    function editProject(project) {
        Modal.create({
            title: 'Edit Project',
            content: `
                <form id="edit-project-form" class="space-y-6 pt-4">
                    <input type="hidden" name="id" value="${project.id}">
                    <div>
                        <label class="${MODAL_CLASSES.label}">Project Name</label>
                        <input type="text" name="name" value="${project.name}" class="${MODAL_CLASSES.input}">
                    </div>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Description</label>
                        <textarea name="description" rows="3" class="${MODAL_CLASSES.input}">${project.description || ''}</textarea>
                    </div>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Project Tone</label>
                        <div class="grid grid-cols-4 sm:grid-cols-8 gap-3">
                            ${['#000000', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#EC4899', '#6366F1', '#14B8A6'].map(c => `
                                <label class="cursor-pointer group">
                                    <input type="radio" name="color" value="${c}" ${project.color === c ? 'checked' : ''} class="sr-only peer">
                                    <div class="w-full aspect-square rounded border-2 border-white shadow-sm peer-checked:border-vercel-black transition-all" style="background: ${c}"></div>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="${MODAL_CLASSES.btnPrimary} flex-1" onclick="submitEditProject(this)">Save Changes</button>
                </div>
            `
        });
    }

    async function submitEditProject(btn) {
        const form = document.getElementById('edit-project-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-project.php', data);
            if (res.success) { Toast.success(res.message); setTimeout(() => location.reload(), 800); }
            else { Toast.error(res.error); setLoading(btn, false); }
        } catch (e) { Toast.error('Failed to save'); setLoading(btn, false); }
    }

    function confirmDelete(id) {
        const adminUser = '<?php echo $_SESSION['username']; ?>';
        Modal.create({
            title: 'Delete Project',
            content: `
                <div class="space-y-6 pt-4">
                    <p class="text-vercel-red text-sm font-bold border border-vercel-red/20 p-4 bg-vercel-red/5 rounded">Warning: This will dissolve the project. Bibliographies will be detached but not deleted.</p>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Type your username to confirm: <span class="text-vercel-black underline">${adminUser}</span></label>
                        <input type="text" id="del-p-conf" class="${MODAL_CLASSES.input} border-vercel-red/20 focus:border-vercel-red" placeholder="...">
                    </div>
                </div>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)">Cancel</button>
                    <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del-p">Confirm Dissolution</button>
                </div>
            `,
            onOpen: (m) => {
                m.querySelector('#exec-del-p').onclick = async () => {
                    if (m.querySelector('#del-p-conf').value !== adminUser) return Toast.error('Invalid confirmation');
                    try {
                        const res = await API.delete('<?php echo SITE_URL; ?>/api/projects/delete.php', { id: id });
                        if (res.success) { Toast.success('Project deleted'); setTimeout(() => location.reload(), 800); }
                    } catch (e) { Toast.error('Delete failed'); }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>