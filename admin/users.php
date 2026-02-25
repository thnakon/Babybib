<?php
/**
 * Babybib - Admin Users Management (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('user_management');
$extraStyles = ''; // Tailwind handles all styles now

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

    // Build query
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

    // Get users
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

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('user_management'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                <?php echo __('admin_manage_users_desc'); ?>
                <span class="ml-2 px-2 py-0.5 border border-vercel-gray-200 text-vercel-black rounded text-[10px] font-bold"><?php echo number_format($total); ?> <?php echo __('admin_users_label'); ?></span>
            </p>
        </div>
        <button onclick="showAddUserModal()" class="flex items-center gap-2 bg-vercel-black text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span><?php echo __('admin_add_user'); ?></span>
        </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px] relative">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-vercel-gray-400"></i>
            <input type="text" id="user-search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="<?php echo __('admin_search_users'); ?>"
                   class="w-full pl-10 pr-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all">
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <select id="filter-role" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_roles'); ?></option>
                <option value="admin" <?php echo $filterRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo $filterRole === 'user' ? 'selected' : ''; ?>>User</option>
            </select>

            <select id="filter-lis" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value=""><?php echo __('admin_all_types'); ?></option>
                <option value="1" <?php echo $filterLis === 1 ? 'selected' : ''; ?>>LIS CMU</option>
                <option value="0" <?php echo $filterLis === 0 ? 'selected' : ''; ?>>General</option>
            </select>

            <select id="sort-order" class="px-3 py-2 bg-white border border-vercel-gray-200 rounded-md text-xs font-semibold text-vercel-gray-500 hover:border-vercel-black transition-all outline-none">
                <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo __('admin_newest'); ?></option>
                <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo __('admin_oldest'); ?></option>
                <option value="name" <?php echo $sortOrder === 'name' ? 'selected' : ''; ?>><?php echo __('admin_by_name'); ?></option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-vercel-gray-100 border-b border-vercel-gray-200 text-vercel-gray-500 font-medium tracking-tight">
                <tr>
                    <th class="px-6 py-4 font-bold uppercase text-[10px]"><?php echo __('admin_user_col'); ?></th>
                    <th class="px-6 py-4 font-bold uppercase text-[10px]"><?php echo __('admin_status'); ?></th>
                    <th class="px-6 py-4 font-bold uppercase text-[10px]"><?php echo __('admin_role'); ?></th>
                    <th class="px-6 py-4 font-bold uppercase text-[10px] text-right"><?php echo __('admin_actions'); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vercel-gray-200">
                <?php if (empty($users)): ?>
                    <tr><td colspan="4" class="px-6 py-20 text-center text-vercel-gray-400 font-medium"><?php echo __('admin_no_match'); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="group hover:bg-vercel-gray-100/50 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-vercel-gray-100 flex items-center justify-center text-xs font-black text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white transition-all overflow-hidden">
                                        <?php if (!empty($u['profile_picture'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($u['profile_picture']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-vercel-black truncate"><?php echo htmlspecialchars($u['name'] . ' ' . $u['surname']); ?></div>
                                        <div class="text-[11px] text-vercel-gray-400 font-medium">@<?php echo htmlspecialchars($u['username']); ?> • <?php echo htmlspecialchars($u['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase <?php echo $u['is_active'] ? 'text-vercel-emerald' : 'text-vercel-red'; ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?php echo $u['is_active'] ? 'bg-vercel-emerald' : 'bg-vercel-red'; ?>"></span>
                                    <?php echo $u['is_active'] ? 'Active' : 'Suspended'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-bold text-vercel-gray-600 uppercase">
                                    <?php echo $u['role']; ?>
                                    <?php if ($u['is_lis_cmu']): ?>
                                        <span class="ml-1 px-1.5 py-0.5 border border-vercel-gray-200 rounded text-[9px] text-vercel-gray-400">LIS</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="viewUserDetails(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="toggleStatus(<?php echo $u['id']; ?>, <?php echo $u['is_active'] ? 0 : 1; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-amber transition-colors">
                                        <i data-lucide="<?php echo $u['is_active'] ? 'user-x' : 'user-check'; ?>" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $u['id']; ?>)" class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between border-t border-vercel-gray-200 pt-8 mt-4">
            <div class="text-xs text-vercel-gray-400 font-medium">
                <?php echo __('admin_showing'); ?> <span class="text-vercel-black font-bold"><?php echo $offset + 1; ?></span> <?php echo __('admin_to'); ?> <span class="text-vercel-black font-bold"><?php echo min($total, $offset + $perPage); ?></span> <?php echo __('admin_of'); ?> <span class="text-vercel-black font-bold"><?php echo $total; ?></span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-all">
                    <?php echo __('previous'); ?>
                </button>
                <div class="px-4 py-1.5 text-xs font-black text-vercel-black">
                    <?php echo __('admin_page'); ?> <?php echo $page; ?> <?php echo __('admin_of'); ?> <?php echo $totalPages; ?>
                </div>
                <button onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>
                        class="px-3 py-1.5 text-xs font-bold border border-vercel-gray-200 rounded-md hover:bg-vercel-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition-all">
                    <?php echo __('next'); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Filters Handling
    const searchInput = document.getElementById('user-search');
    const roleSelect = document.getElementById('filter-role');
    const lisSelect = document.getElementById('filter-lis');
    const sortSelect = document.getElementById('sort-order');

    function updateFilters() {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value.trim());
        url.searchParams.set('role', roleSelect.value);
        url.searchParams.set('lis', lisSelect.value);
        url.searchParams.set('sort', sortSelect.value);
        url.searchParams.delete('page');
        window.location = url.toString();
    }

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateFilters, 500);
    });

    [roleSelect, lisSelect, sortSelect].forEach(el => {
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

    function showAddUserModal() {
        Modal.create({
            title: 'Create User',
            content: `
                <form id="add-user-form" class="space-y-6 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-1 md:col-span-2 space-y-4">
                            <div>
                                <label class="${MODAL_CLASSES.label}">Username</label>
                                <input type="text" name="username" class="${MODAL_CLASSES.input}" required placeholder="john.doe">
                            </div>
                            <div>
                                <label class="${MODAL_CLASSES.label}">Password</label>
                                <input type="password" name="password" class="${MODAL_CLASSES.input}" required placeholder="••••••••">
                            </div>
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">First Name</label>
                            <input type="text" name="name" class="${MODAL_CLASSES.input}" required>
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">Last Name</label>
                            <input type="text" name="surname" class="${MODAL_CLASSES.input}">
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="${MODAL_CLASSES.label}">Email</label>
                            <input type="email" name="email" class="${MODAL_CLASSES.input}" required placeholder="email@example.com">
                        </div>
                        <div class="col-span-1 md:col-span-2 p-5 border border-vercel-gray-200 rounded-lg flex items-center justify-between gap-6">
                             <div>
                                <span class="text-sm font-bold text-vercel-black">LIS CMU Student</span>
                                <p class="text-[11px] text-vercel-gray-400 mt-0.5">Check if this is an LIS student at CMU.</p>
                             </div>
                             <div class="flex items-center gap-4">
                                <input type="text" name="student_id" id="add-sid-box" placeholder="6XXXXXXXX" class="hidden ${MODAL_CLASSES.input} !py-1.5 !w-32">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_lis_cmu" value="1" class="sr-only peer" 
                                           onchange="document.getElementById('add-sid-box').classList.toggle('hidden', !this.checked)">
                                    <div class="w-11 h-6 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-vercel-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-vercel-black"></div>
                                </label>
                             </div>
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center justify-end gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary}" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                    <button class="${MODAL_CLASSES.btnPrimary}" onclick="submitNewUser(this)"><?php echo __('save'); ?></button>
                </div>
            `
        });
    }

    async function submitNewUser(btn) {
        const form = document.getElementById('add-user-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        if (!data.is_lis_cmu) data.is_lis_cmu = 0;

        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/create-user.php', data);
            if (res.success) {
                Toast.success(res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(res.error);
                setLoading(btn, false);
            }
        } catch (e) {
            Toast.error('Save failed');
            setLoading(btn, false);
        }
    }

    function editUser(user) {
        Modal.create({
            title: 'Edit User',
            content: `
                <form id="edit-user-form" class="space-y-6 pt-4">
                    <input type="hidden" name="id" value="${user.id}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label class="${MODAL_CLASSES.label}">Username</label>
                            <input type="text" name="username" value="${user.username}" class="${MODAL_CLASSES.input}">
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">Change Password</label>
                            <input type="password" name="password" placeholder="Leave blank to keep" class="${MODAL_CLASSES.input}">
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">First Name</label>
                            <input type="text" name="name" value="${user.name}" class="${MODAL_CLASSES.input}">
                        </div>
                        <div>
                            <label class="${MODAL_CLASSES.label}">Last Name</label>
                            <input type="text" name="surname" value="${user.surname || ''}" class="${MODAL_CLASSES.input}">
                        </div>
                        <div class="col-span-1 md:col-span-2">
                             <label class="${MODAL_CLASSES.label}">Email</label>
                             <input type="email" name="email" value="${user.email}" class="${MODAL_CLASSES.input}">
                        </div>
                        <div class="col-span-1 md:col-span-2 p-5 border border-vercel-gray-200 rounded-lg flex items-center justify-between gap-6">
                             <div>
                                <span class="text-sm font-bold text-vercel-black">LIS CMU Student</span>
                             </div>
                             <div class="flex items-center gap-4">
                                <input type="text" name="student_id" id="edit-sid-box" value="${user.student_id || ''}" class="${user.is_lis_cmu == 1 ? '' : 'hidden'} ${MODAL_CLASSES.input} !py-1.5 !w-32">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_lis_cmu" value="1" ${user.is_lis_cmu == 1 ? 'checked' : ''} class="sr-only peer" 
                                           onchange="document.getElementById('edit-sid-box').classList.toggle('hidden', !this.checked)">
                                    <div class="w-11 h-6 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-vercel-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-vercel-black"></div>
                                </label>
                             </div>
                        </div>
                    </div>
                </form>
            `,
            footer: `
                <div class="flex items-center justify-end gap-2 w-full">
                    <button class="${MODAL_CLASSES.btnSecondary}" onclick="Modal.close(this)">Cancel</button>
                    <button class="${MODAL_CLASSES.btnPrimary}" onclick="submitEditUser(this)">Save Changes</button>
                </div>
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
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-user-details.php', data);
            if (res.success) {
                Toast.success(res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                Toast.error(res.error);
                setLoading(btn, false);
            }
        } catch (e) {
            Toast.error('Update failed');
            setLoading(btn, false);
        }
    }

    function viewUserDetails(user) {
        Modal.create({
            title: 'User Profile Details',
            content: `
                <div class="space-y-6 pt-2 overflow-x-hidden">
                     <!-- Profile Hero -->
                     <div class="flex flex-col md:flex-row items-center md:items-start gap-6 pb-6 border-b border-vercel-gray-200">
                        <div class="w-20 h-20 shrink-0 rounded border border-vercel-gray-200 bg-vercel-gray-50 flex items-center justify-center text-vercel-black text-2xl font-black shadow-inner overflow-hidden">
                            ${user.profile_picture ? `<img src="<?php echo SITE_URL; ?>/uploads/avatars/${user.profile_picture}" class="w-full h-full object-cover">` : user.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0 text-center md:text-left pt-1">
                            <h3 class="text-2xl font-black text-vercel-black tracking-tighter leading-tight break-words">${user.name} ${user.surname || ''}</h3>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-1.5 mt-2">
                                <span class="px-1.5 py-0.5 border border-vercel-black bg-vercel-black text-white rounded text-[8px] font-black uppercase tracking-widest">@${user.username}</span>
                                <span class="px-1.5 py-0.5 border border-vercel-gray-200 bg-vercel-gray-50 text-vercel-gray-400 rounded text-[8px] font-black uppercase tracking-widest">ID: #${user.id}</span>
                                <span class="px-1.5 py-0.5 border ${user.is_active ? 'border-vercel-emerald/20 bg-vercel-emerald/5 text-vercel-emerald' : 'border-vercel-red/20 bg-vercel-red/5 text-vercel-red'} rounded text-[8px] font-black uppercase tracking-widest">
                                    ${user.is_active ? 'Active' : 'Suspended'}
                                </span>
                            </div>
                        </div>
                     </div>

                     <!-- Info Sections -->
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Primary Details -->
                        <div class="space-y-4">
                            <label class="${MODAL_CLASSES.label}">Credential & Identity</label>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2.5 text-xs text-vercel-black font-bold">
                                    <div class="w-7 h-7 rounded border border-vercel-gray-100 bg-vercel-gray-50 flex items-center justify-center text-vercel-gray-400"><i data-lucide="mail" class="w-3.5 h-3.5"></i></div>
                                    <span class="truncate">${user.email}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-xs text-vercel-black font-bold">
                                    <div class="w-7 h-7 rounded border border-vercel-gray-100 bg-vercel-gray-50 flex items-center justify-center text-vercel-gray-400"><i data-lucide="building" class="w-3.5 h-3.5"></i></div>
                                    <span class="truncate">${user.org_type || 'General Entity'}</span>
                                </div>
                                ${user.is_lis_cmu == 1 ? `
                                <div class="flex items-center gap-2.5 text-xs text-vercel-emerald font-black">
                                    <div class="w-7 h-7 rounded border border-vercel-emerald/20 bg-vercel-emerald/10 flex items-center justify-center text-vercel-emerald"><i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i></div>
                                    <span>LIS Student // ${user.student_id || 'N/A'}</span>
                                </div>` : ''}
                            </div>
                        </div>

                        <!-- Statistics Grid -->
                        <div class="space-y-4">
                            <label class="${MODAL_CLASSES.label}">Operational Stats</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-vercel-gray-50 border border-vercel-gray-200 rounded p-3 text-center group hover:bg-white transition-all">
                                    <div class="text-2xl font-black text-vercel-black tracking-tighter group-hover:scale-105 transition-transform">${user.bib_count}</div>
                                    <div class="text-[7px] font-black text-vercel-gray-400 uppercase tracking-widest mt-0.5 leading-none">Bibs</div>
                                </div>
                                <div class="bg-vercel-gray-50 border border-vercel-gray-200 rounded p-3 text-center group hover:bg-white transition-all">
                                    <div class="text-2xl font-black text-vercel-black tracking-tighter group-hover:scale-105 transition-transform">${user.project_count}</div>
                                    <div class="text-[7px] font-black text-vercel-gray-400 uppercase tracking-widest mt-0.5 leading-none">Projects</div>
                                </div>
                            </div>
                        </div>

                        <!-- System Timestamps -->
                        <div class="col-span-full pt-4 border-t border-vercel-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div class="text-[9px] text-vercel-gray-400 font-bold uppercase tracking-tight">
                                Registered On <span class="text-vercel-black font-black whitespace-nowrap">${user.created_at}</span>
                            </div>
                            <div class="text-[9px] text-vercel-gray-400 font-bold uppercase tracking-tight sm:text-right">
                                Latest Login <span class="text-vercel-black font-black whitespace-nowrap">${user.last_login || 'No Session'}</span>
                            </div>
                        </div>
                     </div>
                </div>
            `,
            footer: `<button class="w-full py-3 bg-vercel-black text-white font-black text-[10px] uppercase tracking-[0.2em] rounded hover:bg-vercel-gray-800 transition-all shadow active:scale-95" onclick="Modal.close(this)">Close Review</button>`
        });
        if (window.lucide) lucide.createIcons();
    }

    async function toggleStatus(id, s) {
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-user.php', { id: id, is_active: s });
            if (res.success) {
                Toast.success(res.message);
                setTimeout(() => location.reload(), 800);
            }
        } catch (e) { Toast.error('Failed to update status'); }
    }

    function confirmDelete(id) {
        const adminUser = '<?php echo $_SESSION['username']; ?>';
        Modal.create({
            title: 'Delete User',
            content: `
                <div class="space-y-6 pt-4">
                    <p class="text-vercel-red text-sm font-bold">Warning: This action is permanent and will delete all user data.</p>
                    <div>
                        <label class="${MODAL_CLASSES.label}">Type your username to confirm: <span class="text-vercel-black underline">${adminUser}</span></label>
                        <input type="text" id="del-confirm" class="${MODAL_CLASSES.input} border-vercel-red/20 focus:border-vercel-red" placeholder="...">
                    </div>
                </div>
            `,
            footer: `
                <div class="flex items-center gap-2 w-full">
                    <button class="flex-1 ${MODAL_CLASSES.btnSecondary}" onclick="Modal.close(this)">Cancel</button>
                    <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del">Delete Permanently</button>
                </div>
            `,
            onOpen: (m) => {
                m.querySelector('#exec-del').onclick = async () => {
                    if (m.querySelector('#del-confirm').value !== adminUser) return Toast.error('Invalid confirmation');
                    try {
                        const res = await API.delete('<?php echo SITE_URL; ?>/api/admin/delete-user.php', { id: id });
                        if (res.success) { Toast.success('User deleted'); setTimeout(() => location.reload(), 800); }
                        else Toast.error(res.error);
                    } catch (e) { Toast.error('Delete failed'); }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>