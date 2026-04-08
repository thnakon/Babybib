<?php

/**
 * Babybib - Projects Page (New Design)
 * =====================================
 */

$pageTitle = 'โครงการ';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();

// Search, Filter and Pagination
$search = sanitize($_GET['search'] ?? '');
$sortOrder = sanitize($_GET['sort'] ?? 'newest');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

try {
    $db = getDB();

    $where = "p.user_id = ?";
    $params = [$userId];

    if ($search) {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Count total
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM projects p WHERE $where");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $perPage);

    // Sort order
    $orderBy = "p.created_at DESC";
    if ($sortOrder === 'oldest') {
        $orderBy = "p.created_at ASC";
    } elseif ($sortOrder === 'az') {
        $orderBy = "p.name ASC";
    }

    // Get projects with pagination
    $stmt = $db->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM bibliographies WHERE project_id = p.id) as bib_count
        FROM projects p 
        WHERE $where
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $total = 0;
    $totalPages = 0;
}

$colors = ['#8B5CF6', '#EF4444', '#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#6366F1', '#14B8A6'];
?>

<style>
    /* === Professional 3-Column Workspace Design === */
    body {
        overflow: hidden; /* Prevent body scroll, use container scroll */
    }

    .workspace-wrapper {
        display: grid;
        grid-template-columns: 280px 1fr 300px;
        height: calc(100vh - 80px); /* Navbar height */
        background: var(--gray-50);
        position: relative;
    }

    /* Sidebar Left: Project List */
    .sidebar-left {
        background: var(--white);
        border-right: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-light);
    }

    .sidebar-project-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }

    .sidebar-project-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        border-radius: 12px;
        margin-bottom: 4px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        position: relative;
    }

    .sidebar-project-item:hover {
        background: var(--gray-50);
    }

    .sidebar-project-item.active {
        background: var(--primary-light);
        border-color: var(--primary-lighter);
    }

    .sidebar-project-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        background: var(--primary-gradient);
        border-radius: 0 4px 4px 0;
    }

    .project-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .project-item-info {
        flex: 1;
        min-width: 0;
    }

    .project-item-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-item-count {
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    /* Create Button at Sidebar Top */
    .btn-create-sidebar {
        width: 100%;
        background: var(--primary-gradient);
        color: var(--white);
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: var(--shadow-primary);
        transition: all 0.2s;
        margin-bottom: 10px;
    }

    .btn-create-sidebar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }

    /* Workspace Center: Paper View */
    .workspace-center {
        background: #e2e8f0; /* Slightly darker gray for better paper contrast */
        overflow-y: auto;
        padding: 40px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 30px;
    }

    /* A4 Paper Styling */
    .paper-sheet {
        background: white;
        width: 210mm;
        max-width: 100%;
        min-height: 297mm;
        padding: 25.4mm; /* 1 inch margin */
        box-shadow: 
            0 0 0 1px rgba(0,0,0,0.05),
            0 10px 40px rgba(0,0,0,0.1),
            0 2px 10px rgba(0,0,0,0.05);
        border-radius: 2px;
        font-family: 'Tahoma', sans-serif;
        font-size: 16px;
        line-height: 1.5;
        color: #000;
        position: relative;
    }

    .paper-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .paper-entry {
        text-indent: -0.5in;
        margin-left: 0.5in;
        margin-bottom: 0.8rem;
        line-height: 2;
        font-size: 16px;
        text-align: justify;
        text-justify: inter-cluster;
    }

    .paper-entry em, .paper-entry i { font-style: italic; }

    /* Paper Empty/Loading */
    .paper-status {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px;
        color: var(--text-tertiary);
        text-align: center;
    }

    .paper-status i {
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    /* Sidebar Right: Context & Tools */
    .sidebar-right {
        background: var(--white);
        border-left: 1px solid var(--border-light);
        padding: 25px;
        overflow-y: auto;
    }

    .sidebar-right-section {
        margin-bottom: 30px;
    }

    .right-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-tertiary);
        letter-spacing: 0.1em;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .project-detail-card {
        background: var(--gray-50);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .detail-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .detail-value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .export-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-export-full {
        padding: 12px;
        border-radius: 10px;
        border: 1px solid var(--border-light);
        background: var(--white);
        color: var(--text-primary);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .btn-export-full:hover {
        background: var(--gray-50);
        border-color: var(--primary);
    }

    .btn-export-full.word i { color: #2b579a; }
    .btn-export-full.pdf i { color: #f40f02; }

    .btn-action-outline {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid var(--border-light);
        background: transparent;
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .btn-action-outline:hover {
        background: var(--gray-50);
        color: var(--text-primary);
    }

    .btn-action-outline.danger:hover {
        background: #FEF2F2;
        color: var(--danger);
        border-color: #FECACA;
    }

    /* Mobile Warning */
    @media (max-width: 1024px) {
        body { overflow: auto; }
        .workspace-wrapper {
            grid-template-columns: 1fr;
            height: auto;
        }
        .sidebar-left, .sidebar-right { border: none; height: auto; }
        .workspace-center { padding: 20px 10px; }
        .paper-sheet { padding: 40px 20px; }
    }

    /* Empty State for entire app */
    .empty-app-state {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 100px;
        text-align: center;
    }
</style>

<div class="workspace-wrapper">
    <!-- Sidebar Left: Project List -->
    <aside class="sidebar-left">
        <div class="sidebar-header">
            <button class="btn-create-sidebar" onclick="showCreateProjectModal()">
                <i class="fas fa-plus-circle"></i>
                <?php echo $currentLang === 'th' ? 'สร้างโครงการใหม่' : 'Create Project'; ?>
            </button>
            <div class="proj-search-wrapper" style="max-width: none; margin-top: 10px;">
                <i class="fas fa-search proj-search-icon"></i>
                <input type="text" id="sidebar-search" class="proj-search-input" 
                       style="padding: 10px 10px 10px 40px; height: 40px;"
                       placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาโครงการ...' : 'Search...'; ?>">
            </div>
        </div>
        <div class="sidebar-project-list" id="sidebar-project-list">
            <?php if (empty($projects)): ?>
                <div style="padding: 40px 20px; text-align: center; color: var(--text-tertiary); font-size: 0.9rem;">
                    <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                    <?php echo $currentLang === 'th' ? 'ยังไม่มีโครงการ' : 'No projects yet'; ?>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="sidebar-project-item" data-id="<?php echo $project['id']; ?>" onclick="selectProject(<?php echo $project['id']; ?>)">
                        <span class="project-dot" style="background: <?php echo $project['color']; ?>;"></span>
                        <div class="project-item-info">
                            <div class="project-item-name"><?php echo htmlspecialchars($project['name']); ?></div>
                            <div class="project-item-count"><?php echo $project['bib_count']; ?> <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Workspace Center: Paper View -->
    <main class="workspace-center" id="workspace-center">
        <div class="paper-status" id="center-status">
            <i class="fas fa-hand-pointer"></i>
            <h3><?php echo $currentLang === 'th' ? 'เลือกโครงการเพื่อเริ่มต้น' : 'Select a project to start'; ?></h3>
            <p><?php echo $currentLang === 'th' ? 'คลิกที่ชื่อโครงการด้านซ้ายมือเพื่อดูบรรณานุกรม' : 'Click a project on the left to view its bibliography'; ?></p>
        </div>
    </main>

    <!-- Sidebar Right: Project Tools -->
    <aside class="sidebar-right" id="sidebar-right">
        <div class="paper-status" style="padding: 40px 0;">
            <i class="fas fa-info-circle"></i>
            <p style="font-size: 0.85rem;"><?php echo $currentLang === 'th' ? 'ขอมูลโครงการจะแสดงที่นี่' : 'Project details will appear here'; ?></p>
        </div>
    </aside>
</div>

<!-- Export Preview Modal -->
<div class="preview-overlay" id="export-preview-modal">
    <div class="preview-container">
        <div class="preview-header">
            <h3>
                <i class="fas fa-file-alt"></i>
                <span id="preview-title"><?php echo $currentLang === 'th' ? 'ตัวอย่างบรรณานุกรม' : 'Bibliography Preview'; ?></span>
            </h3>
            <button class="preview-close-btn" onclick="closePreviewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="preview-body">
            <div class="preview-paper" id="preview-paper">
                <div class="preview-loading" id="preview-loading">
                    <i class="fas fa-spinner"></i>
                    <div><?php echo $currentLang === 'th' ? 'กำลังโหลด...' : 'Loading...'; ?></div>
                </div>
            </div>
        </div>
        <div class="preview-footer">
            <span class="preview-count" id="preview-count">0 <?php echo $currentLang === 'th' ? 'รายการ' : 'items'; ?></span>
            <button class="preview-download-btn word" id="btn-download-word" onclick="downloadExport('docx')">
                <i class="fas fa-file-word"></i> <?php echo $currentLang === 'th' ? 'ดาวน์โหลด Word' : 'Download Word'; ?>
            </button>
            <button class="preview-download-btn pdf" id="btn-download-pdf" onclick="downloadExport('pdf')">
                <i class="fas fa-file-pdf"></i> <?php echo $currentLang === 'th' ? 'พิมพ์ PDF' : 'Print PDF'; ?>
            </button>
        </div>
    </div>
</div>

<script>
    const colors = <?php echo json_encode($colors); ?>;
    const canCreateProject = <?php echo canCreateProject($userId) ? 'true' : 'false'; ?>;
    const isEn = <?php echo $currentLang === 'en' ? 'true' : 'false'; ?>;
    let currentProject = null;

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        // Auto-select first project if exists
        const firstProject = document.querySelector('.sidebar-project-item');
        if (firstProject) {
            firstProject.click();
        }

        // Sidebar Search
        document.getElementById('sidebar-search').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.sidebar-project-item').forEach(item => {
                const name = item.querySelector('.project-item-name').textContent.toLowerCase();
                if (name.includes(term)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    async function selectProject(id) {
        // Update Sidebar UI
        document.querySelectorAll('.sidebar-project-item').forEach(item => {
            item.classList.remove('active');
            if (parseInt(item.dataset.id) === id) item.classList.add('active');
        });

        const center = document.getElementById('workspace-center');
        const right = document.getElementById('sidebar-right');

        // Loading State
        center.innerHTML = `
            <div class="paper-status">
                <i class="fas fa-spinner fa-spin"></i>
                <p>${isEn ? 'Loading bibliography...' : 'กำลังโหลดบรรณานุกรม...'}</p>
            </div>
        `;

        try {
            const response = await fetch(`<?php echo SITE_URL; ?>/api/projects/get-content.php?id=${id}`);
            const data = await response.json();

            if (data.success) {
                currentProject = data.project;
                renderProjectWorkspace(data);
            } else {
                Toast.error(data.message);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            Toast.error(isEn ? 'Failed to load project content' : 'ไม่สามารถโหลดข้อมูลโครงการได้');
        }
    }

    function renderProjectWorkspace(data) {
        const center = document.getElementById('workspace-center');
        const right = document.getElementById('sidebar-right');
        const project = data.project;
        const bibs = data.bibliographies;

        // 1. Center: Paper View
        let paperHtml = `
            <div class="paper-sheet">
                <div class="paper-title">${isEn ? 'References' : 'บรรณานุกรม'}</div>
                <div class="paper-entries">
        `;

        if (bibs.length === 0) {
            paperHtml += `
                <div class="paper-status" style="padding: 100px 0;">
                    <i class="fas fa-book-open"></i>
                    <p>${isEn ? 'This project has no bibliography entries.' : 'โครงการนี้ยังไม่มีรายการบรรณานุกรม'}</p>
                </div>
            `;
        } else {
            bibs.forEach(bib => {
                paperHtml += `<div class="paper-entry">${formatBibText(bib.bibliography_text)}</div>`;
            });
        }

        paperHtml += `
                </div>
            </div>
        `;
        center.innerHTML = paperHtml;
        center.scrollTop = 0;

        // 2. Right: Tools & Details
        right.innerHTML = `
            <div class="sidebar-right-section">
                <h4 class="right-section-title">
                    <i class="fas fa-project-diagram"></i>
                    ${isEn ? 'Project Information' : 'ข้อมูลโครงการ'}
                </h4>
                <div class="project-detail-card" style="border-left: 4px solid ${project.color};">
                    <div class="detail-label">${isEn ? 'Name' : 'ชื่อโครงการ'}</div>
                    <div class="detail-value">${project.name}</div>
                    
                    <div class="detail-label" style="margin-top: 15px;">${isEn ? 'Bibliographies' : 'จำนวนรายการ'}</div>
                    <div class="detail-value">${data.count} ${isEn ? 'entries' : 'รายการ'}</div>
                    
                    <div class="detail-label" style="margin-top: 15px;">${isEn ? 'Created' : 'สร้างเมื่อ'}</div>
                    <div class="detail-value">${project.created_at}</div>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                    ${project.description || (isEn ? 'No description provided.' : 'ไม่มีคำอธิบาย')}
                </p>
            </div>

            <div class="sidebar-right-section">
                <h4 class="right-section-title">
                    <i class="fas fa-download"></i>
                    ${isEn ? 'Export Workspace' : 'ส่งออกไฟล์'}
                </h4>
                <div class="export-group">
                    <button class="btn-export-full word" onclick="downloadExport('docx')">
                        <i class="fas fa-file-word"></i>
                        ${isEn ? 'Download Word (.docx)' : 'ดาวน์โหลดไฟล์ Word'}
                    </button>
                    <button class="btn-export-full pdf" onclick="downloadExport('pdf')">
                        <i class="fas fa-file-pdf"></i>
                        ${isEn ? 'Print to PDF' : 'สั่งพิมพ์ PDF'}
                    </button>
                </div>
            </div>

            <div class="sidebar-right-section">
                <h4 class="right-section-title">
                    <i class="fas fa-gear"></i>
                    ${isEn ? 'Management' : 'จัดการ'}
                </h4>
                <button class="btn-action-outline" onclick="editProject(${project.id}, '${project.name.replace(/'/g, "\\'")}', '${(project.description || '').replace(/'/g, "\\'")}', '${project.color}')">
                    <i class="fas fa-pen"></i>
                    ${isEn ? 'Edit Project Settings' : 'แก้ไขการจัดโครงการ'}
                </button>
                <button class="btn-action-outline danger" onclick="deleteProject(${project.id}, '${project.name.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash-can"></i>
                    ${isEn ? 'Delete Project' : 'ลบโครงการนี้'}
                </button>
            </div>
        `;
    }

    function formatBibText(text) {
        return text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    }

    // Modal & Action functions (adapted from old code)
    function showCreateProjectModal() {
        if (!canCreateProject) {
            Toast.warning(isEn ? 'You have reached the project limit' : 'คุณสร้างโครงการถึงขีดจำกัดแล้ว');
            return;
        }

        const colorOptions = colors.map(c =>
            `<button type="button" class="color-option" style="background: ${c};" onclick="selectColor(this, '${c}')"></button>`
        ).join('');

        Modal.create({
            title: isEn ? 'Create New Project' : 'สร้างโครงการใหม่',
            content: `
                <form id="create-project-form">
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Project Name' : 'ชื่อโครงการ'}<span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" required placeholder="${isEn ? 'Enter project name' : 'กรอกชื่อโครงการ'}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Description' : 'รายละเอียด'}</label>
                        <textarea name="description" class="form-input form-textarea" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Color Label' : 'สีโครงการ'}</label>
                        <div class="color-picker">${colorOptions}</div>
                        <input type="hidden" name="color" value="#8B5CF6">
                    </div>
                </form>
            `,
            footer: `
                <button class="btn-modal btn-modal-cancel" onclick="Modal.close(this)">${isEn ? 'Cancel' : 'ยกเลิก'}</button>
                <button class="btn-modal btn-modal-confirm" onclick="createProject()">${isEn ? 'Create' : 'สร้าง'}</button>
            `
        });

        setTimeout(() => {
            const firstColor = document.querySelector('.color-option');
            if (firstColor) firstColor.classList.add('selected');
        }, 100);
    }

    function selectColor(el, color) {
        document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.querySelector('input[name="color"]').value = color;
    }

    async function createProject() {
        const form = document.getElementById('create-project-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.name.trim()) {
            Toast.error(isEn ? 'Project name is required' : 'กรุณากรอกชื่อโครงการ');
            return;
        }

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/projects/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CONFIG.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            const res = await response.json();
            if (res.success) {
                location.reload();
            } else {
                Toast.error(res.error);
            }
        } catch (e) {
            Toast.error(isEn ? 'Failed to create project' : 'เกิดข้อผิดพลาดในการสร้างโครงการ');
        }
    }

    function editProject(id, name, description, color) {
        const colorOptions = colors.map(c =>
            `<button type="button" class="color-option ${c === color ? 'selected' : ''}" style="background: ${c};" onclick="selectColor(this, '${c}')"></button>`
        ).join('');

        Modal.create({
            title: isEn ? 'Edit Project' : 'แก้ไขโครงการ',
            content: `
                <form id="edit-project-form">
                    <input type="hidden" name="id" value="${id}">
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Project Name' : 'ชื่อโครงการ'}<span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" required value="${name}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Description' : 'รายละเอียด'}</label>
                        <textarea name="description" class="form-input form-textarea" rows="3">${description}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isEn ? 'Color Label' : 'สีโครงการ'}</label>
                        <div class="color-picker">${colorOptions}</div>
                        <input type="hidden" name="color" value="${color}">
                    </div>
                </form>
            `,
            footer: `
                <button class="btn-modal btn-modal-cancel" onclick="Modal.close(this)">${isEn ? 'Cancel' : 'ยกเลิก'}</button>
                <button class="btn-modal btn-modal-confirm" onclick="updateProject()">${isEn ? 'Save Changes' : 'บันทึกการเปลี่ยนแปลง'}</button>
            `
        });
    }

    async function updateProject() {
        const form = document.getElementById('edit-project-form');
        const data = Object.fromEntries(new FormData(form).entries());

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/projects/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CONFIG.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            const res = await response.json();
            if (res.success) {
                location.reload();
            } else {
                Toast.error(res.error);
            }
        } catch (e) {
            Toast.error(isEn ? 'Failed to update project' : 'เกิดข้อผิดพลาดในการแก้ไขโครงการ');
        }
    }

    function deleteProject(id, projectName) {
        Modal.create({
            title: isEn ? 'Delete Project' : 'ลบโครงการ',
            icon: 'fas fa-exclamation-triangle',
            content: `
                <div style="text-align: left;">
                    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <p style="color: #991B1B; font-size: 0.9rem; margin: 0;">
                            ${isEn ? 'All bibliographies in <b>' + projectName + '</b> will be permanently removed.' : 'บรรณานุกรมทั้งหมดใน <b>' + projectName + '</b> จะถูกลบออกอย่างถาวร'}
                        </p>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">
                        ${isEn ? 'Type project name to confirm:' : 'พิมพ์ชื่อโครงการเพื่อยืนยัน:'}
                    </p>
                    <input type="text" id="confirm-name" class="form-input" style="width:100%">
                </div>
            `,
            footer: `
                <button class="btn-modal btn-modal-cancel" onclick="Modal.close(this)">${isEn ? 'Cancel' : 'ยกเลิก'}</button>
                <button class="btn-modal btn-modal-confirm danger" id="btn-confirm-delete" disabled>${isEn ? 'Delete Now' : 'ยืนยันการลบ'}</button>
            `
        });

        const input = document.getElementById('confirm-name');
        const btn = document.getElementById('btn-confirm-delete');
        input.addEventListener('input', () => btn.disabled = input.value.trim() !== projectName);
        btn.onclick = async () => {
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/projects/delete.php', {
                    method: 'POST', // or DELETE depending on your API
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CONFIG.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id })
                });
                const res = await response.json();
                if (res.success) {
                    location.reload();
                }
            } catch (e) {
                Toast.error('Error');
            }
        };
    }

    function downloadExport(format) {
        if (!currentProject) return;
        if (format === 'pdf') {
            window.open(`<?php echo SITE_URL; ?>/api/export/project.php?id=${currentProject.id}&format=pdf`, '_blank');
        } else {
            window.open(`<?php echo SITE_URL; ?>/api/export/project.php?id=${currentProject.id}&format=docx`, '_blank');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>