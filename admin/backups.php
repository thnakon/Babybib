<?php
/**
 * Babybib - Admin Backup Management Page (Tailwind Redesign)
 */

require_once '../includes/session.php';
requireAdmin();

$pageTitle = __('admin_backup_mgmt');
$extraStyles = '';

require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

// Backup directory
$backupDir = __DIR__ . '/../backups';
$logsDir = __DIR__ . '/../logs';

// Get backup files
$backups = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '/*.{sql.gz,tar.gz}', GLOB_BRACE);
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file),
            'type' => strpos($file, '.sql.gz') !== false ? 'database' : 'files'
        ];
    }
    // Sort by date descending
    usort($backups, function ($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Format file size
function formatSize($bytes)
{
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
?>

<div class="space-y-10 animate-in fade-in duration-500">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-vercel-gray-200 pb-8">
        <div>
            <h1 class="text-3xl font-black text-vercel-black tracking-tight"><?php echo __('admin_backup_mgmt'); ?></h1>
            <p class="text-vercel-gray-500 text-sm mt-2 font-medium">
                <?php echo __('admin_backup_desc'); ?>
            </p>
        </div>
        <button onclick="createBackup()" class="px-6 py-2.5 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all flex items-center gap-2">
            <i data-lucide="database" class="w-4 h-4"></i>
            <span><?php echo __('admin_create_snapshot'); ?></span>
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="border border-vercel-gray-200 rounded-lg p-8 bg-white shadow-sm group hover:border-vercel-black transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded border border-vercel-gray-200 bg-vercel-gray-50 flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-vercel-black block tracking-tighter"><?php echo count(array_filter($backups, fn($b) => $b['type'] === 'database')); ?></span>
                    <span class="text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_db_snapshots'); ?></span>
                </div>
            </div>
        </div>
        <div class="border border-vercel-gray-200 rounded-lg p-8 bg-white shadow-sm group hover:border-vercel-black transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded border border-vercel-gray-200 bg-vercel-gray-50 flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                    <i data-lucide="folder-archive" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-vercel-black block tracking-tighter"><?php echo count(array_filter($backups, fn($b) => $b['type'] === 'files')); ?></span>
                    <span class="text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_file_archives'); ?></span>
                </div>
            </div>
        </div>
        <div class="border border-vercel-gray-200 rounded-lg p-8 bg-white shadow-sm group hover:border-vercel-black transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded border border-vercel-gray-200 bg-vercel-gray-50 flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                    <i data-lucide="hard-drive" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-3xl font-black text-vercel-black block tracking-tighter"><?php echo formatSize(array_sum(array_column($backups, 'size'))); ?></span>
                    <span class="text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_storage'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Table -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
        <div class="px-8 py-6 border-b border-vercel-gray-100 bg-vercel-gray-50/50 flex items-center justify-between">
            <h3 class="text-xs font-black text-vercel-black uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4"></i>
                <?php echo __('admin_backup_list'); ?>
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <?php if (empty($backups)): ?>
                <div class="py-20 text-center text-vercel-gray-400 font-medium"><?php echo __('admin_no_backups'); ?></div>
            <?php else: ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-vercel-gray-50/50">
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_asset_name'); ?></th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_size'); ?></th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest"><?php echo __('admin_timestamp'); ?></th>
                            <th class="px-8 py-4 text-[10px] font-black text-vercel-gray-400 uppercase tracking-widest text-right"><?php echo __('admin_operations'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-vercel-gray-100">
                        <?php foreach ($backups as $backup): ?>
                            <tr class="hover:bg-vercel-gray-50/40 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded border border-vercel-gray-200 flex items-center justify-center text-vercel-gray-400 group-hover:bg-vercel-black group-hover:text-white group-hover:border-vercel-black transition-all">
                                            <i data-lucide="<?php echo $backup['type'] === 'database' ? 'database' : 'folder'; ?>" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-vercel-black tracking-tight underline-offset-4 decoration-vercel-gray-200 group-hover:underline"><?php echo htmlspecialchars($backup['name']); ?></div>
                                            <div class="text-[9px] font-black uppercase text-vercel-gray-400 tracking-widest"><?php echo $backup['type']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs font-bold text-vercel-black leading-none bg-vercel-gray-100 px-2 py-1 rounded"><?php echo formatSize($backup['size']); ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-xs font-bold text-vercel-black"><?php echo date('d M Y', $backup['date']); ?></div>
                                    <div class="text-[10px] font-black text-vercel-gray-400 uppercase tracking-tight"><?php echo date('H:i:s', $backup['date']); ?></div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="<?php echo SITE_URL; ?>/api/admin/download-backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                           class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-black transition-colors" title="Download Snapshot">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')" 
                                                class="p-2 hover:bg-vercel-gray-100 rounded-md text-vercel-gray-400 hover:text-vercel-red transition-colors" title="Purge Archive">
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

    <!-- CLI Instructions -->
    <div class="border border-vercel-gray-200 rounded-lg bg-white shadow-sm overflow-hidden p-8">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-8 border-b border-vercel-gray-100 pb-6">
                <h3 class="text-sm font-black text-vercel-black uppercase tracking-widest flex items-center gap-3">
                    <i data-lucide="terminal" class="w-5 h-5 text-vercel-gray-400"></i>
                    <?php echo __('admin_cli_title'); ?>
                </h3>
                <div class="flex gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-vercel-red/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-vercel-amber-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-vercel-green-500/50"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="space-y-8">
                    <div>
                        <div class="text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest mb-3"><?php echo __('admin_db_trigger'); ?></div>
                        <div class="bg-vercel-gray-50 p-4 rounded-md border border-vercel-gray-100 font-mono text-sm text-vercel-gray-800 flex items-center justify-between group">
                            <code>./scripts/backup_database.sh</code>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest mb-3"><?php echo __('admin_file_compress'); ?></div>
                        <div class="bg-vercel-gray-50 p-4 rounded-md border border-vercel-gray-100 font-mono text-sm text-vercel-gray-800">
                            <code>./scripts/backup_files.sh</code>
                        </div>
                    </div>
                </div>
                <div class="space-y-8">
                    <div>
                        <div class="text-[10px] font-black text-vercel-gray-500 uppercase tracking-widest mb-3"><?php echo __('admin_cron_title'); ?></div>
                        <div class="bg-vercel-gray-50 p-4 rounded-md border border-vercel-gray-100 font-mono text-sm text-vercel-blue-600 overflow-x-auto">
                            <code>0 2 * * * /bin/bash backup.sh --cron</code>
                        </div>
                    </div>
                    <div class="p-5 border border-vercel-blue-500/20 bg-vercel-blue-500/5 rounded-md">
                         <p class="text-[11px] text-vercel-gray-400 leading-relaxed font-bold italic tracking-tight uppercase">
                             <i data-lucide="info" class="w-3 h-3 inline mr-2 text-vercel-blue-500"></i>
                             <?php echo __('admin_rotation_note'); ?>
                         </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Style Overrides
    const MODAL_CLASSES = {
        label: 'block text-[10px] font-bold text-vercel-gray-400 uppercase tracking-widest mb-2',
        input: 'w-full px-4 py-2.5 bg-white border border-vercel-gray-200 rounded-md text-sm outline-none focus:border-vercel-black transition-all',
        btnPrimary: 'px-6 py-2 bg-vercel-black text-white rounded-md font-bold text-sm hover:bg-vercel-gray-800 transition-all',
        btnSecondary: 'px-6 py-2 text-vercel-gray-500 hover:text-vercel-black font-bold text-sm transition-all'
    };

    async function createBackup() {
        Modal.create({
            title: '<?php echo __('admin_init_snapshot'); ?>',
            content: '<p class="text-vercel-gray-600 text-sm py-4 font-medium"><?php echo __('admin_snapshot_desc'); ?></p>',
            footer: `<div class="flex items-center gap-2 w-full">
                <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                <button class="${MODAL_CLASSES.btnPrimary} flex-1" id="exec-backup"><?php echo __('admin_start_snapshot'); ?></button>
            </div>`,
            onOpen: (m) => {
                m.querySelector('#exec-backup').onclick = async (e) => {
                    const btn = e.currentTarget;
                    setLoading(btn, true);
                    try {
                        const res = await API.post('<?php echo SITE_URL; ?>/api/admin/create-backup.php', {});
                        if (res.success) { Toast.success('<?php echo __('admin_snapshot_done'); ?>'); setTimeout(() => location.reload(), 1000); }
                        else { Toast.error(res.error); setLoading(btn, false); }
                    } catch (err) { Toast.error('Communication error'); setLoading(btn, false); }
                }
            }
        });
    }

    async function deleteBackup(filename) {
        Modal.create({
            title: '<?php echo __('admin_purge_archive'); ?>',
            content: '<div class="py-4 space-y-4"><p class="text-vercel-red text-sm font-bold border border-vercel-red/20 p-4 bg-vercel-red/5 rounded"><?php echo __('admin_purge_warn'); ?></p><div class="bg-vercel-gray-50 p-4 border border-vercel-gray-100 rounded font-mono text-xs text-vercel-gray-500 break-all">' + filename + '</div></div>',
            footer: `<div class="flex items-center gap-2 w-full">
                <button class="${MODAL_CLASSES.btnSecondary} flex-1" onclick="Modal.close(this)"><?php echo __('cancel'); ?></button>
                <button class="flex-1 py-2 bg-vercel-red text-white rounded-md font-bold text-sm transition-all grayscale hover:grayscale-0" id="exec-del"><?php echo __('admin_confirm_purge'); ?></button>
            </div>`,
            onOpen: (m) => {
                m.querySelector('#exec-del').onclick = async (e) => {
                    const btn = e.currentTarget;
                    setLoading(btn, true);
                    try {
                        const res = await API.post('<?php echo SITE_URL; ?>/api/admin/delete-backup.php', { filename });
                        if (res.success) { Toast.success('<?php echo __('admin_purge_done'); ?>'); setTimeout(() => location.reload(), 800); }
                        else { Toast.error(res.error); setLoading(btn, false); }
                    } catch (err) { Toast.error('Purge failed'); setLoading(btn, false); }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>