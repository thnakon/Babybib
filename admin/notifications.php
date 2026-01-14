<?php

/**
 * Babybib - Admin Notifications Page
 * ===================================
 */

require_once '../includes/session.php';

$pageTitle = 'การแจ้งเตือน';
$extraStyles = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-layout.css?v=' . time() . '?v=' . time() . '">';
$extraStyles .= '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/admin-management.css?v=' . time() . '?v=' . time() . '">';
require_once '../includes/header.php';
require_once '../includes/sidebar-admin.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Filter
$filterType = sanitize($_GET['type'] ?? '');

try {
    $db = getDB();

    // Create temporary table-like array from both sources
    $allNotifications = [];

    // Fetch from admin_notifications
    $notifQuery = "SELECT * FROM admin_notifications ORDER BY created_at DESC";
    $notifications = $db->query($notifQuery)->fetchAll();
    foreach ($notifications as $n) {
        $allNotifications[] = [
            'id' => 'notif_' . $n['id'],
            'type' => $n['type'],
            'title' => $n['title'],
            'message' => $n['message'] ?? '',
            'link' => $n['link'],
            'is_read' => $n['is_read'],
            'created_at' => $n['created_at']
        ];
    }

    // Fetch pending feedback as notifications  
    $feedbackQuery = "SELECT id, subject, message, created_at FROM feedback WHERE status = 'pending' ORDER BY created_at DESC";
    $pendingFeedback = $db->query($feedbackQuery)->fetchAll();
    foreach ($pendingFeedback as $f) {
        $allNotifications[] = [
            'id' => 'feedback_' . $f['id'],
            'type' => 'feedback',
            'title' => ($currentLang === 'th' ? 'ข้อเสนอแนะใหม่: ' : 'New Feedback: ') . $f['subject'],
            'message' => substr($f['message'], 0, 150),
            'link' => '/admin/feedback.php?id=' . $f['id'],
            'is_read' => 0,
            'created_at' => $f['created_at']
        ];
    }

    // Sort by created_at DESC
    usort($allNotifications, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Filter by type
    if ($filterType) {
        $allNotifications = array_filter($allNotifications, function ($n) use ($filterType) {
            return $n['type'] === $filterType;
        });
        $allNotifications = array_values($allNotifications);
    }

    // Pagination
    $total = count($allNotifications);
    $totalPages = ceil($total / $perPage);
    $allNotifications = array_slice($allNotifications, $offset, $perPage);
} catch (Exception $e) {
    error_log("Notifications error: " . $e->getMessage());
    $allNotifications = [];
    $total = 0;
    $totalPages = 0;
}
?>



<div class="admin-notif-wrapper">
    <!-- Header -->
    <header class="page-header slide-up">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="header-info">
                <h1><?php echo __('notifications'); ?></h1>
                <p><?php echo $currentLang === 'th' ? 'ติดตามกิจกรรมและการแจ้งเตือนทั้งหมด' : 'Track all activities and notifications'; ?></p>
            </div>
        </div>
        <button class="btn btn-primary" style="border-radius: 14px;" onclick="markAllRead()">
            <i class="fas fa-check-double"></i>
            <span><?php echo $currentLang === 'th' ? 'อ่านทั้งหมด' : 'Mark All Read'; ?></span>
        </button>
    </header>

    <!-- Toolbar -->
    <div class="toolbar-card slide-up stagger-1">
        <select class="filter-select" onchange="filterByType(this.value)">
            <option value=""><?php echo $currentLang === 'th' ? '-- ประเภททั้งหมด --' : '-- All Types --'; ?></option>
            <option value="feedback" <?php echo $filterType === 'feedback' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ข้อเสนอแนะ' : 'Feedback'; ?></option>
            <option value="user" <?php echo $filterType === 'user' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ผู้ใช้งาน' : 'User'; ?></option>
            <option value="system" <?php echo $filterType === 'system' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ระบบ' : 'System'; ?></option>
            <option value="announcement" <?php echo $filterType === 'announcement' ? 'selected' : ''; ?>><?php echo $currentLang === 'th' ? 'ประกาศ' : 'Announcement'; ?></option>
        </select>

        <div class="stat-pill">
            <i class="fas fa-inbox"></i>
            <span><?php echo $currentLang === 'th' ? 'ทั้งหมด' : 'Total'; ?></span>
            <span class="count"><?php echo number_format($total); ?></span>
        </div>
    </div>

    <!-- Notification List -->
    <div class="notif-list slide-up stagger-2">
        <?php if (empty($allNotifications)): ?>
            <div class="empty-container">
                <i class="fas fa-bell-slash"></i>
                <h3><?php echo $currentLang === 'th' ? 'ไม่มีการแจ้งเตือน' : 'No Notifications'; ?></h3>
                <p><?php echo $currentLang === 'th' ? 'คุณจะเห็นการแจ้งเตือนใหม่ที่นี่' : 'New notifications will appear here'; ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($allNotifications as $index => $notif): ?>
                <?php
                $type = $notif['type'] ?? 'system';
                $icon = 'fas fa-info-circle';
                if ($type === 'feedback') $icon = 'fas fa-comment-dots';
                elseif ($type === 'user') $icon = 'fas fa-user-plus';
                elseif ($type === 'announcement') $icon = 'fas fa-bullhorn';

                $link = isset($notif['link']) ? SITE_URL . $notif['link'] : '#';
                $isUnread = !$notif['is_read'];
                ?>
                <a href="<?php echo htmlspecialchars($link); ?>" class="notif-card <?php echo $isUnread ? 'unread' : ''; ?>">
                    <?php if ($isUnread): ?>
                        <div class="notif-indicator"></div>
                    <?php endif; ?>

                    <div class="type-icon-wrapper <?php echo $type; ?>">
                        <i class="<?php echo $icon; ?>"></i>
                    </div>

                    <div class="notif-main-info">
                        <div class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                        <?php if (!empty($notif['message'])): ?>
                            <div class="notif-preview"><?php echo htmlspecialchars($notif['message']); ?></div>
                        <?php endif; ?>
                        <div class="notif-meta">
                            <span class="badge-type"><?php echo ucfirst($type); ?></span>
                            <span><i class="far fa-clock"></i> <?php echo formatThaiDate($notif['created_at']); ?></span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button class="action-btn" title="<?php echo $currentLang === 'th' ? 'ดู' : 'View'; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <a href="?page=<?php echo max(1, $page - 1); ?>&type=<?php echo $filterType; ?>"
                class="pagination-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="?page=<?php echo $i; ?>&type=<?php echo $filterType; ?>"
                        class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
            <?php endfor; ?>

            <a href="?page=<?php echo min($totalPages, $page + 1); ?>&type=<?php echo $filterType; ?>"
                class="pagination-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function filterByType(type) {
        const url = new URL(window.location);
        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }
        url.searchParams.set('page', 1);
        window.location = url.toString();
    }

    async function markAllRead() {
        try {
            await API.post('<?php echo SITE_URL; ?>/api/admin/mark-notifications-read.php');
            Toast.success('<?php echo $currentLang === "th" ? "อ่านทั้งหมดแล้ว" : "All marked as read"; ?>');

            // Update UI
            document.querySelectorAll('.notif-card.unread').forEach(el => el.classList.remove('unread'));
            document.querySelectorAll('.notif-indicator').forEach(el => el.remove());

            // Update header badge
            const badge = document.getElementById('notif-badge');
            if (badge) badge.style.display = 'none';
        } catch (e) {
            Toast.error('Error');
        }
    }
</script>

<?php require_once '../includes/footer-admin.php'; ?>