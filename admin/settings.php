<?php
/**
 * Babybib - Admin Settings Page (Tailwind Redesign)
 */

require_once '../includes/session.php';

$pageTitle = __('system_settings');
$extraStyles = '';

require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM system_settings");
    $settingsRaw = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsRaw as $s) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }
} catch (Exception $e) {
    $settings = [];
}
?>

<div class="animate-in fade-in duration-500 pb-20">
    <!-- Page Header -->
    <div class="mb-10 border-b border-vercel-gray-200 pb-8">
        <h1 class="text-3xl font-bold text-vercel-black tracking-tight"><?php echo __('system_settings'); ?></h1>
        <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
            <?php echo __('admin_settings_desc'); ?>
        </p>
    </div>

    <!-- Layout -->
    <div class="flex flex-col md:flex-row gap-8 lg:gap-16">
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 flex-shrink-0 flex flex-col gap-1">
            <button onclick="showTab('general')" id="tab-btn-general" class="w-full text-left px-4 py-2.5 rounded-lg font-bold text-sm bg-vercel-gray-100 text-vercel-black transition-colors tab-button">
                <?php echo __('admin_tab_general'); ?>
            </button>
            <button onclick="showTab('limits')" id="tab-btn-limits" class="w-full text-left px-4 py-2.5 rounded-lg font-bold text-sm text-vercel-gray-500 hover:bg-vercel-gray-50 hover:text-vercel-black transition-colors tab-button">
                <?php echo __('admin_tab_limits'); ?>
            </button>
            <button onclick="showTab('security')" id="tab-btn-security" class="w-full text-left px-4 py-2.5 rounded-lg font-bold text-sm text-vercel-gray-500 hover:bg-vercel-gray-50 hover:text-vercel-black transition-colors tab-button">
                <?php echo __('admin_tab_security'); ?>
            </button>
            <button onclick="showTab('smtp')" id="tab-btn-smtp" class="w-full text-left px-4 py-2.5 rounded-lg font-bold text-sm text-vercel-gray-500 hover:bg-vercel-gray-50 hover:text-vercel-black transition-colors tab-button">
                <?php echo __('admin_tab_smtp'); ?>
            </button>
        </div>

        <!-- Main Content -->
        <div class="w-full md:flex-1">
            <form id="settings-form" class="space-y-6">
                <!-- General Settings -->
                <div id="tab-general" class="tab-content block">
                    <h3 class="text-lg font-semibold text-vercel-black"><?php echo __('admin_general_config'); ?></h3>
                    <p class="text-sm text-vercel-gray-500 mt-1 mb-6 font-medium">
                        <?php echo __('admin_desc_general'); ?>
                    </p>
                    
                    <div class="space-y-6 max-w-2xl">
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_site_title'); ?></label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Babybib'); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_site_desc'); ?></label>
                            <textarea name="site_description" rows="3" class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_contact_email'); ?></label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                        </div>
                    </div>
                </div>

                <!-- Usage Limits -->
                <div id="tab-limits" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-vercel-black"><?php echo __('admin_usage_limits'); ?></h3>
                    <p class="text-sm text-vercel-gray-500 mt-1 mb-6 font-medium">
                        <?php echo __('admin_desc_limits'); ?>
                    </p>
                    
                    <div class="space-y-6 max-w-2xl">
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_max_bibs'); ?></label>
                            <input type="number" name="max_bibs_per_user" value="<?php echo htmlspecialchars($settings['max_bibs_per_user'] ?? $settings['max_bibliographies_per_user'] ?? 300); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                            <input type="hidden" name="max_bibliographies_per_user" value="<?php echo htmlspecialchars($settings['max_bibs_per_user'] ?? $settings['max_bibliographies_per_user'] ?? 300); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_max_projects'); ?></label>
                            <input type="number" name="max_projects_per_user" value="<?php echo htmlspecialchars($settings['max_projects_per_user'] ?? 30); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_bib_lifetime'); ?> <?php echo __('admin_bib_days'); ?></label>
                            <input type="number" name="bib_lifetime_days" value="<?php echo htmlspecialchars($settings['bib_lifetime_days'] ?? 730); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                            <p class="text-[12px] text-vercel-gray-500 mt-2 font-medium"><?php echo __('admin_desc_bib_days'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div id="tab-security" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-vercel-black"><?php echo __('admin_security'); ?></h3>
                    <p class="text-sm text-vercel-gray-500 mt-1 mb-6 font-medium">
                        <?php echo __('admin_desc_security'); ?>
                    </p>
                    
                    <div class="space-y-6 max-w-2xl">
                        <div class="flex items-center justify-between p-4 border border-vercel-gray-200 rounded-lg">
                            <div>
                                <h4 class="text-sm font-bold text-vercel-black"><?php echo __('admin_maintenance'); ?></h4>
                                <p class="text-xs text-vercel-gray-500 mt-1 font-medium"><?php echo __('admin_desc_maintenance'); ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-vercel-black"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border border-vercel-gray-200 rounded-lg">
                            <div>
                                <h4 class="text-sm font-bold text-vercel-black"><?php echo __('admin_allow_reg'); ?></h4>
                                <p class="text-xs text-vercel-gray-500 mt-1 font-medium"><?php echo __('admin_desc_allow_reg'); ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="allow_registration" value="1" <?php echo ($settings['allow_registration'] ?? '1') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-vercel-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-vercel-black"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SMTP -->
                <div id="tab-smtp" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-vercel-black"><?php echo __('admin_smtp_settings'); ?></h3>
                    <p class="text-sm text-vercel-gray-500 mt-1 mb-6 font-medium">
                        <?php echo __('admin_desc_smtp'); ?>
                    </p>
                    
                    <div class="space-y-6 max-w-2xl">
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_smtp_user'); ?></label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-vercel-black mb-2"><?php echo __('admin_smtp_pass'); ?></label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" 
                                   class="w-full px-4 py-2.5 border border-vercel-gray-200 rounded-lg text-sm font-medium text-vercel-black outline-none focus:border-vercel-black transition-all">
                            <p class="text-[12px] text-vercel-gray-500 mt-2 font-medium"><?php echo __('admin_desc_smtp_pass'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 pt-4">
                    <button type="button" onclick="saveSettings()" id="btn-save-bottom" class="px-5 py-2.5 bg-[#0f0f0f] text-white rounded-md font-medium text-[13px] hover:bg-black transition-all flex items-center justify-center gap-2">
                        <span><?php echo __('admin_save_changes'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showTab(tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });
        
        // Reset all buttons style
        document.querySelectorAll('.tab-button').forEach(el => {
            el.classList.remove('bg-vercel-gray-100', 'text-vercel-black');
            el.classList.add('text-vercel-gray-500', 'hover:bg-vercel-gray-50', 'hover:text-vercel-black');
        });

        // Show active content
        const activeTab = document.getElementById('tab-' + tabId);
        if (activeTab) {
            activeTab.classList.remove('hidden');
            activeTab.classList.add('block');
        }

        // Active button style
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('text-vercel-gray-500', 'hover:bg-vercel-gray-50', 'hover:text-vercel-black');
            activeBtn.classList.add('bg-vercel-gray-100', 'text-vercel-black');
        }
    }

    async function saveSettings() {
        const form = document.getElementById('settings-form');
        const btn = document.getElementById('btn-save-bottom');
        if (!form || !btn) return;
        
        setLoading(btn, true);

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Fill redundant keys
        data.max_bibliographies_per_user = data.max_bibs_per_user;
        data.maintenance_mode = form.querySelector('input[name="maintenance_mode"]').checked ? '1' : '0';
        data.allow_registration = form.querySelector('input[name="allow_registration"]').checked ? '1' : '0';

        try {
            const res = await API.post('<?php echo SITE_URL; ?>/api/admin/update-settings.php', data);
            if (res.success) {
                Toast.success('Settings saved successfully');
            } else {
                Toast.error(res.error || 'Failed to save settings');
            }
        } catch (e) {
            Toast.error('System error occurred');
        } finally {
            setLoading(btn, false);
        }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>