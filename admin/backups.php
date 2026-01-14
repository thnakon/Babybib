<?php

/**
 * Babybib - Admin Backup Management Page
 * =======================================
 * Web interface for backup operations
 */

require_once '../includes/session.php';
requireAdmin();

$pageTitle = 'จัดการ Backup';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '">';
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

<div class="admin-content-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="header-info">
                <h1><?php echo $currentLang === 'th' ? 'จัดการ Backup' : 'Backup Management'; ?></h1>
                <p><?php echo $currentLang === 'th' ? 'สำรองและกู้คืนข้อมูลระบบ' : 'Backup and restore system data'; ?></p>
            </div>
        </div>
        <button onclick="createBackup()" class="btn btn-primary" style="border-radius: 14px; padding: 12px 24px;">
            <i class="fas fa-plus"></i>
            <span><?php echo $currentLang === 'th' ? 'สร้าง Backup' : 'Create Backup'; ?></span>
        </button>
    </header>

    <!-- Quick Stats -->
    <div class="stats-grid slide-up stagger-1" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #DBEAFE; color: #1D4ED8;">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo count(array_filter($backups, fn($b) => $b['type'] === 'database')); ?></span>
                <span class="stat-label"><?php echo $currentLang === 'th' ? 'Database Backups' : 'Database Backups'; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #DCFCE7; color: #16A34A;">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo count(array_filter($backups, fn($b) => $b['type'] === 'files')); ?></span>
                <span class="stat-label"><?php echo $currentLang === 'th' ? 'Files Backups' : 'Files Backups'; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #FEF3C7; color: #D97706;">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatSize(array_sum(array_column($backups, 'size'))); ?></span>
                <span class="stat-label"><?php echo $currentLang === 'th' ? 'พื้นที่ใช้งาน' : 'Storage Used'; ?></span>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card slide-up stagger-2">
        <div class="card-header">
            <h3><i class="fas fa-archive"></i> <?php echo $currentLang === 'th' ? 'รายการ Backup' : 'Backup List'; ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
                <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                    <i class="fas fa-box-open" style="font-size: 48px; color: var(--text-tertiary); margin-bottom: 20px;"></i>
                    <h4><?php echo $currentLang === 'th' ? 'ยังไม่มี Backup' : 'No backups yet'; ?></h4>
                    <p><?php echo $currentLang === 'th' ? 'กดปุ่ม "สร้าง Backup" เพื่อเริ่มต้น' : 'Click "Create Backup" to get started'; ?></p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?php echo $currentLang === 'th' ? 'ชื่อไฟล์' : 'Filename'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'ประเภท' : 'Type'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'ขนาด' : 'Size'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'วันที่' : 'Date'; ?></th>
                            <th><?php echo $currentLang === 'th' ? 'การดำเนินการ' : 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas <?php echo $backup['type'] === 'database' ? 'fa-database' : 'fa-folder'; ?>"
                                            style="color: <?php echo $backup['type'] === 'database' ? '#1D4ED8' : '#16A34A'; ?>;"></i>
                                        <span><?php echo htmlspecialchars($backup['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $backup['type'] === 'database' ? 'badge-primary' : 'badge-success'; ?>">
                                        <?php echo $backup['type'] === 'database' ? 'Database' : 'Files'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatSize($backup['size']); ?></td>
                                <td><?php echo date('d/m/Y H:i', $backup['date']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo SITE_URL; ?>/api/admin/download-backup.php?file=<?php echo urlencode($backup['name']); ?>"
                                            class="btn btn-sm btn-outline" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')"
                                            class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
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

    <!-- Instructions Card -->
    <div class="card slide-up stagger-3" style="margin-top: 20px;">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> <?php echo $currentLang === 'th' ? 'วิธีใช้งาน Command Line' : 'Command Line Usage'; ?></h3>
        </div>
        <div class="card-body">
            <div style="background: var(--gray-50); border-radius: 12px; padding: 20px; font-family: monospace; font-size: 13px;">
                <p style="margin-bottom: 10px; color: var(--text-secondary);"><strong># Backup Database</strong></p>
                <code style="color: var(--primary);">./scripts/backup_database.sh</code>

                <p style="margin: 15px 0 10px; color: var(--text-secondary);"><strong># Backup Files</strong></p>
                <code style="color: var(--primary);">./scripts/backup_files.sh</code>

                <p style="margin: 15px 0 10px; color: var(--text-secondary);"><strong># Restore Database</strong></p>
                <code style="color: var(--primary);">./scripts/restore_backup.sh babybib_db_20260114.sql.gz</code>

                <p style="margin: 15px 0 10px; color: var(--text-secondary);"><strong># Cron Job (Daily 2AM)</strong></p>
                <code style="color: var(--primary);">0 2 * * * /path/to/babybib/scripts/backup_database.sh --cron</code>
            </div>
        </div>
    </div>
</div>

<script>
    async function createBackup() {
        if (!confirm('<?php echo $currentLang === 'th' ? 'ต้องการสร้าง Backup ใช่หรือไม่?' : 'Create backup now?'; ?>')) return;

        try {
            Toast.info('<?php echo $currentLang === 'th' ? 'กำลังสร้าง Backup...' : 'Creating backup...'; ?>');
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/create-backup.php', {});

            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'An error occurred'; ?>');
        }
    }

    async function deleteBackup(filename) {
        if (!confirm('<?php echo $currentLang === 'th' ? 'ต้องการลบ Backup นี้ใช่หรือไม่?' : 'Delete this backup?'; ?>')) return;

        try {
            const response = await API.post('<?php echo SITE_URL; ?>/api/admin/delete-backup.php', {
                filename
            });

            if (response.success) {
                Toast.success(response.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.error(response.error);
            }
        } catch (e) {
            Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'An error occurred'; ?>');
        }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>