<?php
/**
 * Babybib - Admin Profile Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('admin_profile_title');
$extraStyles = '';

require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

$userId = getCurrentUserId();
$user = getCurrentUser();
$provinces = getProvinces();
$orgTypes = getOrganizationTypes();

// Get admin stats
try {
    $db = getDB();
    $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $totalBibs = $db->query("SELECT COUNT(*) FROM bibliographies")->fetchColumn();
    $totalFeedback = $db->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();
    $loginCount = $db->query("SELECT COUNT(*) FROM activity_logs WHERE user_id = {$userId} AND action = 'login'")->fetchColumn();
} catch (Exception $e) {
    $totalUsers = 0;
    $totalBibs = 0;
    $totalFeedback = 0;
    $loginCount = 0;
}
?>

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('admin_profile_title'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                Manage personal credentials and operational oversight.
            </p>
        </div>
    </div>

    <!-- Profile Hero -->
    <div class="border border-vercel-gray-200 rounded-lg p-10 bg-white shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex flex-col lg:flex-row items-center lg:items-start gap-10">
            <!-- Avatar -->
            <div class="relative">
                <div class="w-32 h-32 rounded border border-vercel-gray-200 bg-vercel-gray-50 flex items-center justify-center text-4xl font-black text-vercel-black shadow-inner overflow-hidden">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($user['profile_picture']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <label for="avatar-input" class="absolute -bottom-2 -right-2 w-8 h-8 bg-vercel-black text-white rounded border border-vercel-black flex items-center justify-center cursor-pointer hover:bg-vercel-gray-800 transition-all shadow-lg">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </label>
                <input type="file" id="avatar-input" accept="image/*" class="hidden" onchange="uploadAvatar(this)">
            </div>

            <!-- Profile Info -->
            <div class="flex-1 text-center lg:text-left space-y-4">
                <div>
                    <h2 class="text-4xl font-black text-vercel-black tracking-tighter"><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></h2>
                    <div class="mt-2 flex flex-wrap justify-center lg:justify-start items-center gap-3">
                        <span class="px-2 py-0.5 border border-vercel-black bg-vercel-black text-white text-[9px] font-black uppercase tracking-widest rounded">System Admin</span>
                        <span class="px-2 py-0.5 border border-vercel-gray-200 bg-vercel-gray-50 text-vercel-gray-400 text-[9px] font-black uppercase tracking-widest rounded">@<?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                </div>
                
                <div class="flex flex-wrap justify-center lg:justify-start items-center gap-x-6 gap-y-2 text-xs font-bold text-vercel-gray-500 uppercase tracking-tight">
                    <div class="flex items-center gap-2">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Registration // <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Profile Stats -->
            <div class="grid grid-cols-3 gap-8 p-6 border border-vercel-gray-100 bg-vercel-gray-50/50 rounded-lg">
                <div class="text-center px-4">
                    <div class="text-2xl font-black text-vercel-black tracking-tighter"><?php echo number_format($totalUsers); ?></div>
                    <div class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest mt-1">Users</div>
                </div>
                <div class="text-center px-4 border-x border-vercel-gray-200">
                    <div class="text-2xl font-black text-vercel-black tracking-tighter"><?php echo number_format($totalBibs); ?></div>
                    <div class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest mt-1">Bibs</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-2xl font-black text-vercel-black tracking-tighter"><?php echo number_format($loginCount); ?></div>
                    <div class="text-[9px] font-black text-vercel-gray-400 uppercase tracking-widest mt-1">Sess</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form id="profile-form" onsubmit="saveProfile(event)" class="space-y-0 pb-20">
        
        <!-- Personal Info Section -->
        <div class="flex flex-col md:flex-row gap-8 py-10 border-b border-vercel-gray-200 items-start group hover:bg-vercel-gray-50/30 transition-colors -mx-6 px-6 sm:-mx-10 sm:px-10">
            <div class="w-full md:w-1/3 pt-2">
                <h3 class="text-sm font-black text-vercel-black tracking-tight flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-vercel-gray-500"></i>
                    <?php echo __('admin_personal_details'); ?>
                </h3>
                <p class="text-[13px] text-vercel-gray-500 mt-2 font-medium leading-relaxed">
                    Update your personal profile details and how they appear publicly.
                </p>
            </div>
            <div class="w-full md:w-2/3 border border-vercel-gray-200 rounded-lg bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('name'); ?></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('surname'); ?></label>
                        <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('username'); ?></label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled 
                           class="w-full md:w-2/3 px-4 py-2 bg-vercel-gray-50 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-gray-500 cursor-not-allowed">
                </div>
            </div>
        </div>

        <!-- Organization & Contact Section -->
        <div class="flex flex-col md:flex-row gap-8 py-10 border-b border-vercel-gray-200 items-start group hover:bg-vercel-gray-50/30 transition-colors -mx-6 px-6 sm:-mx-10 sm:px-10">
            <div class="w-full md:w-1/3 pt-2">
                <h3 class="text-sm font-black text-vercel-black tracking-tight flex items-center gap-2">
                    <i data-lucide="building" class="w-4 h-4 text-vercel-gray-500"></i>
                    <?php echo __('admin_org_context'); ?> &amp; Contact
                </h3>
                <p class="text-[13px] text-vercel-gray-500 mt-2 font-medium leading-relaxed">
                    Manage your organization affiliation, location, and preferred email address for communications.
                </p>
            </div>
            <div class="w-full md:w-2/3 border border-vercel-gray-200 rounded-lg bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <!-- Location & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-vercel-gray-100">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('email'); ?></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('province'); ?></label>
                        <select name="province" class="w-full px-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all appearance-none cursor-pointer">
                            <option value=""><?php echo __('admin_select_province'); ?></option>
                            <?php foreach ($provinces as $prov): ?>
                                <option value="<?php echo htmlspecialchars($prov); ?>" <?php echo $user['province'] === $prov ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Org -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('org_type'); ?></label>
                        <select name="org_type" class="w-full px-4 py-2 bg-white border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all appearance-none cursor-pointer">
                            <?php foreach ($orgTypes as $key => $org): ?>
                                <option value="<?php echo $key; ?>" <?php echo $user['org_type'] === $key ? 'selected' : ''; ?>>
                                    <?php echo $currentLang === 'th' ? $org['th'] : $org['en']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest"><?php echo __('org_name'); ?></label>
                        <input type="text" name="org_name" value="<?php echo htmlspecialchars($user['org_name'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings Section -->
        <div class="flex flex-col md:flex-row gap-8 py-10 items-start group hover:bg-vercel-gray-50/30 transition-colors -mx-6 px-6 sm:-mx-10 sm:px-10">
            <div class="w-full md:w-1/3 pt-2">
                <h3 class="text-sm font-black text-vercel-black tracking-tight flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-vercel-gray-500"></i>
                    <?php echo __('admin_security_access'); ?>
                </h3>
                <p class="text-[13px] text-vercel-gray-500 mt-2 font-medium leading-relaxed">
                    Protect your account by setting a strong password. Leave this section blank if you do not wish to change your password.
                </p>
            </div>
            <div class="w-full md:w-2/3 border border-vercel-gray-200 rounded-lg bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest">Current Password</label>
                    <input type="password" name="current_password" placeholder="Verify to make changes" 
                           class="w-full md:w-1/2 px-4 py-2 bg-vercel-gray-50 border border-vercel-gray-200 rounded-md text-sm font-bold text-vercel-black outline-none focus:border-vercel-black transition-all placeholder:text-vercel-gray-400">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-vercel-gray-100">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest">New Password</label>
                        <input type="password" name="new_password" placeholder="••••••••" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-black text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" 
                               class="w-full px-4 py-2 border border-vercel-gray-200 rounded-md text-sm font-black text-vercel-black outline-none focus:border-vercel-black transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-8 mt-4 sticky bottom-0 bg-vercel-gray-100/90 backdrop-blur-md p-6 -mx-6 sm:-mx-10 border-t border-vercel-gray-200 z-10">
            <button type="submit" class="px-8 py-2.5 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 active:scale-95 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>

<script>
    async function saveProfile(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if ((data.new_password || data.confirm_password) && !data.current_password) {
            return Toast.error('Current password is required to set a new password.');
        }
        if (data.new_password !== data.confirm_password) {
            return Toast.error('New passwords do not match.');
        }

        setLoading(btn, true);
        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/user/update-profile.php', data);
            if (res.success) {
                Toast.success('Profile updated successfully');
                form.querySelector('[name="current_password"]').value = '';
                form.querySelector('[name="new_password"]').value = '';
                form.querySelector('[name="confirm_password"]').value = '';
            } else {
                Toast.error(res.error);
            }
        } catch (err) {
            Toast.error('Failed to update profile');
        } finally {
            setLoading(btn, false);
        }
    }

    async function uploadAvatar(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) return Toast.error('Asset exceeds 2MB limit');

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            Toast.info('Uploading asset...');
            const res = await fetch('<?php echo SITE_URL; ?>/api/user/upload-avatar.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                Toast.success('Asset deployed');
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.error(data.error);
            }
        } catch (err) { Toast.error('Deployment failed'); }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>