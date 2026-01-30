<?php

/**
 * Babybib - Bibliography Summary
 * ======================================
 * Shows bibliography summary for both guests and logged-in users
 */

require_once 'includes/config.php';
require_once 'includes/session.php';

// Get data from POST or session
$parenthetical = sanitize($_POST['citation_parenthetical'] ?? $_SESSION['last_bib']['parenthetical'] ?? '');
$narrative = sanitize($_POST['citation_narrative'] ?? $_SESSION['last_bib']['narrative'] ?? '');
$bibliography = $_POST['bibliography_text'] ?? $_SESSION['last_bib']['bibliography'] ?? '';
$bibliography = strip_tags($bibliography, '<i>'); // Allow italics but strip everything else
$bibId = sanitize($_POST['bib_id'] ?? $_SESSION['last_bib']['id'] ?? null);

// Store in session for page refresh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bibliography_text'])) {
    $_SESSION['last_bib'] = [
        'parenthetical' => $parenthetical,
        'narrative' => $narrative,
        'bibliography' => $bibliography,
        'id' => $bibId
    ];
}

// If no data, redirect to generate
if (empty($bibliography) && empty($_SESSION['last_bib']['bibliography'])) {
    header('Location: ' . SITE_URL . '/generate.php');
    exit;
}

// Use session data if POST is empty
if (empty($bibliography) && !empty($_SESSION['last_bib']['bibliography'])) {
    $parenthetical = $_SESSION['last_bib']['parenthetical'];
    $narrative = $_SESSION['last_bib']['narrative'];
    $bibliography = $_SESSION['last_bib']['bibliography'];
    $bibId = $_SESSION['last_bib']['id'];
}

$pageTitle = 'สรุปบรรณานุกรม';

require_once 'includes/header.php';

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}

$isLoggedIn = isLoggedIn();
$currentLang = getCurrentLanguage();

$projects = [];
$currentProjectId = null;
if ($isLoggedIn) {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT id, name FROM projects WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();

    // Check if this bibliography already has a project assigned
    if (!empty($bibId)) {
        $stmt = $db->prepare("SELECT project_id FROM bibliographies WHERE id = ?");
        $stmt->execute([$bibId]);
        $currentProjectId = $stmt->fetchColumn();
    }
}
?>

<style>
    .summary-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4);
    }

    .summary-header {
        text-align: center;
        margin-bottom: var(--space-8);
    }

    .summary-icon {
        width: 80px;
        height: 80px;
        background: var(--success-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--space-4);
        color: var(--success);
        font-size: 36px;
    }

    .summary-card {
        background: white;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border: 1px solid var(--border-light);
        height: 100%;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
        margin-bottom: var(--space-6);
    }

    @media (max-width: 768px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }

    .summary-section {
        padding: var(--space-6);
        border-bottom: 1px solid var(--border-light);
        transition: background-color 0.2s;
    }

    .summary-section:hover {
        background-color: var(--gray-50);
    }

    .summary-section:last-child {
        border-bottom: none;
    }

    .summary-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-3);
    }

    .summary-label span {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .summary-content {
        padding: var(--space-4);
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        font-size: var(--text-base);
        line-height: 1.6;
        border: 1px solid var(--border-light);
        word-break: break-word;
    }

    .summary-content.bibliography {
        padding-left: 2.5em;
        text-indent: -1.5em;
    }

    .summary-footer {
        display: flex;
        gap: var(--space-4);
        justify-content: center;
        margin-top: var(--space-8);
    }

    .cta-box {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: var(--radius-xl);
        padding: var(--space-8);
        text-align: center;
        margin-top: var(--space-8);
        box-shadow: var(--shadow-md);
    }

    .cta-box.guest {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border: 1px solid var(--primary-light);
    }

    .cta-features {
        display: flex;
        justify-content: center;
        gap: var(--space-6);
        margin: var(--space-6) 0;
        flex-wrap: wrap;
    }

    .cta-feature {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
        color: var(--text-secondary);
    }

    .cta-feature i {
        color: var(--success);
    }

    .project-selection-box {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        margin-top: var(--space-8);
        box-shadow: var(--shadow-md);
        text-align: left;
    }

    .project-select-group {
        display: flex;
        gap: var(--space-3);
        align-items: center;
        margin-top: var(--space-3);
    }

    .project-select-group .form-select {
        flex: 1;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }

    .step {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        background: var(--gray-100);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--text-secondary);
    }

    .step.active {
        background: var(--primary-gradient);
        color: var(--white);
    }

    .step.done {
        background: var(--success-light);
        color: var(--success);
    }

    .step-number {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        font-size: var(--text-xs);
    }
</style>

<main class="container summary-container">
    <div class="step-indicator slide-up">
        <div class="step done">
            <span class="step-number">1</span>
            <?php echo __('select_resource'); ?>
        </div>
        <div class="step done">
            <span class="step-number">2</span>
            <?php echo __('fill_info'); ?>
        </div>
        <div class="step active">
            <span class="step-number">3</span>
            <?php echo __('save'); ?>
        </div>
    </div>

    <div class="summary-header slide-up">
        <div class="summary-icon bounce">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="text-3xl font-bold"><?php echo $isLoggedIn ? __('save_success') : ($currentLang === 'th' ? 'สร้างบรรณานุกรมสำเร็จ!' : 'Bibliography Created!'); ?></h1>
        <p class="text-secondary mt-2">
            <?php if ($isLoggedIn): ?>
                <?php echo $currentLang === 'th' ? 'บรรณานุกรมของคุณถูกบันทึกลงในรายการแล้ว' : 'Your bibliography has been saved to your list.'; ?>
            <?php else: ?>
                <?php echo $currentLang === 'th' ? 'คัดลอกข้อมูลด้านล่างไปใช้งานได้ทันที' : 'Copy the information below to use immediately.'; ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="summary-grid slide-up stagger-1">
        <!-- Left Column: In-text Citations -->
        <div class="summary-card">
            <div class="summary-section">
                <div class="summary-label">
                    <span><?php echo __('citation_parenthetical'); ?></span>
                    <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?php echo addslashes($parenthetical); ?>', this)">
                        <i class="fas fa-copy"></i> <?php echo __('copy'); ?>
                    </button>
                </div>
                <div class="summary-content"><?php echo htmlspecialchars($parenthetical); ?></div>
            </div>

            <div class="summary-section">
                <div class="summary-label">
                    <span><?php echo __('citation_narrative'); ?></span>
                    <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?php echo addslashes($narrative); ?>', this)">
                        <i class="fas fa-copy"></i> <?php echo __('copy'); ?>
                    </button>
                </div>
                <div class="summary-content"><?php echo htmlspecialchars($narrative); ?></div>
            </div>
        </div>

        <!-- Right Column: Bibliography -->
        <div class="summary-card">
            <div class="summary-section" style="height: 100%;">
                <div class="summary-label">
                    <span><?php echo __('bibliography'); ?></span>
                    <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?php echo addslashes($bibliography); ?>', this)">
                        <i class="fas fa-copy"></i> <?php echo __('copy'); ?>
                    </button>
                </div>
                <div class="summary-content bibliography"><?php echo $bibliography; ?></div>
                <div class="mt-4 text-xs text-secondary">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?php echo $currentLang === 'th' ? 'การจัดรูปแบบตัวเอียงเป็นไปตามมาตรฐาน APA 7<sup>th</sup>' : 'Italic formatting follows APA 7<sup>th</sup> standards.'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($isLoggedIn && !empty($bibId)): ?>
        <!-- Project Selection for Logged-in Users -->
        <div class="project-selection-box slide-up stagger-2">
            <h3 class="text-xl font-bold mb-2">
                <i class="fas fa-folder-open text-primary mr-2"></i>
                <?php echo $currentLang === 'th' ? 'จัดเก็บลงโครงการ' : 'Save to Project'; ?>
            </h3>
            <p class="text-secondary text-sm">
                <?php echo $currentLang === 'th' ? 'เลือกโครงการเพื่อจัดระเบียบบรรณานุกรมของคุณ' : 'Select a project to organize your bibliographies.'; ?>
            </p>

            <div class="project-select-group">
                <select id="project-id" class="form-select">
                    <option value=""><?php echo $currentLang === 'th' ? '-- ไม่ระบุโครงการ --' : '-- No Project --'; ?></option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>" <?php echo ($currentProjectId == $project['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-primary" onclick="updateProject(this)">
                    <?php echo __('save'); ?>
                </button>
            </div>

            <div class="mt-4 pt-4 border-t border-light flex justify-between items-center text-sm">
                <span class="text-secondary">
                    <?php echo $currentLang === 'th' ? 'ยังไม่มีโครงการหรือ?' : 'No project yet?'; ?>
                </span>
                <a href="<?php echo SITE_URL; ?>/users/projects.php" class="text-primary font-medium">
                    <i class="fas fa-plus-circle mr-1"></i>
                    <?php echo $currentLang === 'th' ? 'จัดการโครงการ' : 'Manage Projects'; ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isLoggedIn): ?>
        <!-- CTA Box for Guests -->
        <div class="cta-box guest slide-up stagger-2">
            <i class="fas fa-cloud-upload-alt text-4xl text-primary mb-4"></i>
            <h2 class="text-2xl font-bold"><?php echo $currentLang === 'th' ? 'สมัครสมาชิกเพื่อบันทึกข้อมูล' : 'Sign up to save permanently'; ?></h2>
            <p class="text-secondary mt-2"><?php echo $currentLang === 'th' ? 'ป้องกันข้อมูลสูญหายและจัดการบรรณานุกรมของคุณได้ทุกที่' : 'Prevent data loss and manage your bibliographies anywhere.'; ?></p>

            <div class="cta-features">
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $currentLang === 'th' ? 'บันทึกได้ 300 รายการ' : 'Save 300 entries'; ?></span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $currentLang === 'th' ? 'จัดกลุ่มโครงการ' : 'Project Folders'; ?></span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $currentLang === 'th' ? 'ส่งออก Word/PDF' : 'Export Word/PDF'; ?></span>
                </div>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-secondary">
                    <i class="fas fa-plus mr-2"></i> <?php echo __('create'); ?>
                </a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary btn-lg px-8">
                    <?php echo __('register'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Footer Actions for Logged-in Users -->
        <div class="summary-footer slide-up stagger-2">
            <a href="<?php echo SITE_URL; ?>/generate.php" class="btn btn-secondary">
                <i class="fas fa-plus mr-2"></i> <?php echo $currentLang === 'th' ? 'สร้างรายการใหม่' : 'Create New'; ?>
            </a>
            <a href="<?php echo SITE_URL; ?>/users/bibliography-list.php" class="btn btn-primary">
                <i class="fas fa-list mr-2"></i> <?php echo $currentLang === 'th' ? 'ไปที่รายการของฉัน' : 'Go to My List'; ?>
            </a>
            <?php if ($bibId): ?>
                <a href="<?php echo SITE_URL; ?>/generate.php?edit=<?php echo $bibId; ?>" class="btn btn-ghost">
                    <i class="fas fa-edit mr-2"></i> <?php echo __('edit'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    // Animation trigger on load
    document.addEventListener('DOMContentLoaded', () => {
        const items = document.querySelectorAll('.slide-up');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            setTimeout(() => {
                item.classList.add('animate-slide-up');
                item.style.opacity = '1';
            }, index * 100);
        });
    });

    <?php if ($isLoggedIn): ?>
        async function updateProject(btn) {
            const projectId = document.getElementById('project-id').value;
            const bibId = '<?php echo $bibId; ?>';

            if (!bibId) {
                Toast.error('<?php echo $currentLang === 'th' ? 'ไม่พบรหัสบรรณานุกรม กรุณาลองสร้างใหม่อีกครั้ง' : 'Bibliography ID not found. Please try creating it again.'; ?>');
                return;
            }

            try {
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                const response = await API.post('<?php echo SITE_URL; ?>/api/bibliography/update_project.php', {
                    bib_id: bibId,
                    project_id: projectId
                });

                if (response.success) {
                    Toast.success('<?php echo $currentLang === 'th' ? 'อัปเดตโครงการสำเร็จ' : 'Project updated successfully'; ?>');
                } else {
                    Toast.error(response.error || 'Failed to update project');
                }

                btn.disabled = false;
                btn.innerHTML = originalText;
            } catch (error) {
                console.error('Update Project Error:', error);
                Toast.error('An error occurred');
                btn.disabled = false;
                if (typeof originalText !== 'undefined') btn.innerHTML = originalText;
            }
        }
    <?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>