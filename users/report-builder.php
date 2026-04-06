<?php

/**
 * Babybib - Report Builder Page
 * ==============================
 * สร้างรายงานจาก Template พร้อม Preview และ Export
 */

require_once '../includes/session.php';
requireAuth();

$userId = getCurrentUserId();
$templateId = htmlspecialchars($_GET['template'] ?? 'academic_general');

$validTemplates = ['academic_general', 'research', 'internship', 'project', 'thesis', 'thesis_master'];
if (!in_array($templateId, $validTemplates)) {
    $templateId = 'academic_general';
}

$pageTitle = 'สร้างรายงาน';
$hideRating = true;
require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

// Load user's projects
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.id, p.name, p.color,
               (SELECT COUNT(*) FROM bibliographies WHERE project_id = p.id) as bib_count
        FROM projects p
        WHERE p.user_id = ?
        ORDER BY p.updated_at DESC
    ");
    $stmt->execute([$userId]);
    $userProjects = $stmt->fetchAll();
} catch (Exception $e) {
    $userProjects = [];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ===== BUILDER LAYOUT ===== */
    body { overflow: hidden; }

    .builder-wrap {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--nav-height, 96px));
        background: #1a1a2e;
        overflow: hidden;
    }

    /* Top bar */
    .builder-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        height: 54px;
        background: #0f0f1a;
        border-bottom: 1px solid #2a2a3e;
        flex-shrink: 0;
        z-index: 10;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .topbar-back {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #aaa;
        text-decoration: none;
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 8px;
        transition: all 0.15s;
    }

    .topbar-back:hover {
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .topbar-title {
        font-size: 14px;
        font-weight: 600;
        color: white;
    }

    .topbar-template-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .topbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .topbar-btn-pdf {
        background: rgba(239, 68, 68, 0.15);
        color: #FCA5A5;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .topbar-btn-pdf:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #FCA5A5;
    }

    .topbar-btn-docx {
        background: linear-gradient(135deg, #8B5CF6, #6366F1);
        color: white;
    }

    .topbar-btn-docx:hover {
        filter: brightness(1.1);
    }

    .topbar-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Main body */
    .builder-body {
        display: grid;
        grid-template-columns: 240px 1fr 320px;
        flex: 1;
        overflow: hidden;
    }

    /* ===== LEFT SIDEBAR ===== */
    .builder-sidebar {
        background: #13131f;
        border-right: 1px solid #2a2a3e;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sidebar-section-title {
        padding: 14px 16px 10px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
    }

    .section-nav-list {
        flex: 1;
        overflow-y: auto;
        padding: 0 8px 8px;
    }

    .section-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s;
        margin-bottom: 2px;
    }

    .section-nav-item:hover {
        background: rgba(255,255,255,0.05);
    }

    .section-nav-item.active {
        background: rgba(139, 92, 246, 0.15);
    }

    .section-nav-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.05);
        color: #666;
    }

    .section-nav-item.active .section-nav-icon {
        background: rgba(139, 92, 246, 0.2);
        color: #A78BFA;
    }

    .section-nav-label {
        font-size: 12.5px;
        color: #888;
        font-weight: 500;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .section-nav-item.active .section-nav-label {
        color: #D4BBFF;
        font-weight: 600;
    }

    /* Format settings in sidebar */
    .sidebar-format {
        padding: 12px;
        border-top: 1px solid #2a2a3e;
        flex-shrink: 0;
    }

    .sidebar-format-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
        margin-bottom: 10px;
    }

    .format-row {
        margin-bottom: 8px;
    }

    .format-row label {
        display: block;
        font-size: 11px;
        color: #666;
        margin-bottom: 4px;
    }

    .format-row select {
        width: 100%;
        background: #1e1e2e;
        border: 1px solid #333;
        color: #ccc;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 12px;
        appearance: none;
        cursor: pointer;
    }

    /* ===== CENTER PREVIEW ===== */
    .builder-preview {
        background: #1e1e2e;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 20px;
        gap: 16px;
    }

    .preview-header-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #555;
        align-self: flex-start;
        margin-left: 10px;
    }

    /* A4 Paper preview
       Use 96dpi A4 sizing to visually match the default paper model most browsers use.
       Base body size stays at 16px and academic cover preview remains smaller than Word by design. */
    .a4-paper {
        width: min(794px, 100%);
        max-width: 794px;
        min-height: 1123px;
        --page-top: 145px;
        --page-right: 96px;
        --page-bottom: 96px;
        --page-left: 145px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        padding: 145px 96px 96px 145px; /* 1.5in top/left, 1in right/bottom at 96dpi */
        position: relative;
        font-family: 'Sarabun', 'Tahoma', serif;
        font-size: 16px;
        line-height: 1.65;
        color: #111;
        box-sizing: border-box;
        transition: all 0.3s;
    }

    /* Cover page styles */
    .cover-institution {
        text-align: center;
        font-size: 13px;
        margin-bottom: 6px;
        color: #333;
    }

    .cover-logo-placeholder {
        text-align: center;
        margin: 20px 0;
        color: #DDD;
        font-size: 50px;
    }

    .cover-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        margin: 30px 0 12px;
        color: #000;
    }

    .cover-subtitle {
        text-align: center;
        font-size: 14px;
        color: #444;
        margin-bottom: 40px;
    }

    .cover-info-block {
        text-align: center;
        margin-bottom: 14px;
    }

    .cover-info-label {
        font-size: 13px;
        color: #555;
        margin-bottom: 2px;
    }

    .cover-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #111;
    }

    .cover-bottom {
        position: absolute;
        bottom: var(--page-bottom, 96px);
        left: var(--page-left, 145px);
        right: var(--page-right, 96px);
        text-align: center;
        font-size: 16px;
        color: #444;
        line-height: 1.8;
    }

    /* Chapter styles */
    .chapter-heading {
        text-align: center;
        font-size: 18px; /* 18pt at 72dpi */
        font-weight: 700;
        margin-bottom: 8px;
        color: #000;
    }

    .chapter-sub-heading {
        font-size: 16px; /* 16pt at 72dpi */
        font-weight: 700;
        margin: 18px 0 8px;
        color: #000;
    }

    .chapter-body-placeholder {
        background: #F9FAFB;
        border-left: 3px solid #E5E7EB;
        padding: 12px 16px;
        border-radius: 0 6px 6px 0;
        margin: 8px 0;
    }

    .chapter-body-placeholder p {
        font-size: 12px;
        color: #9CA3AF;
        margin: 0 0 4px;
        line-height: 1.6;
    }

    /* Bibliography preview */
    .bib-section-title {
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 24px;
        color: #000;
    }

    .bib-preview-item {
        text-indent: -36px;
        padding-left: 36px;
        margin-bottom: 12px;
        font-size: 16px; /* 16pt at 72dpi */
        line-height: 1.6;
        color: #222;
    }

    .bib-preview-item i {
        font-style: italic;
    }

    .bib-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #aaa;
    }

    .bib-empty-state i {
        font-size: 32px;
        margin-bottom: 12px;
        display: block;
    }

    /* Page break hint */
    .page-break-hint {
        width: 794px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #444;
        font-size: 11px;
    }

    .page-break-hint::before,
    .page-break-hint::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #333;
        border-top: 1px dashed #333;
    }

    /* ===== RIGHT PANEL ===== */
    .builder-panel {
        background: #13131f;
        border-left: 1px solid #2a2a3e;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #2a2a3e;
        flex-shrink: 0;
    }

    .panel-header-copy {
        min-width: 0;
        flex: 1;
    }

    .panel-header h3 {
        font-size: 13px;
        font-weight: 700;
        color: #ddd;
        margin: 0 0 2px;
    }

    .panel-header p {
        font-size: 11px;
        color: #555;
        margin: 0;
    }

    .panel-header-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 8px;
        border: 1px solid rgba(139, 92, 246, 0.28);
        background: rgba(139, 92, 246, 0.12);
        color: #c4b5fd;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .panel-header-action:hover {
        background: rgba(139, 92, 246, 0.18);
        color: #ddd6fe;
    }

    .panel-header-action[hidden] {
        display: none;
    }

    .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    /* Panel form groups */
    .panel-form-group {
        margin-bottom: 14px;
    }

    .panel-form-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: #888;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .panel-form-group label i {
        font-size: 10px;
        color: #666;
    }

    .panel-input,
    .panel-textarea,
    .panel-select {
        width: 100%;
        background: #1e1e2e;
        border: 1.5px solid #2a2a3e;
        color: #ddd;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }

    .panel-input:focus,
    .panel-textarea:focus,
    .panel-select:focus {
        outline: none;
        border-color: #8B5CF6;
        background: #1a1a2e;
    }

    .panel-textarea {
        min-height: 70px;
        resize: vertical;
    }

    .panel-select option {
        background: #1e1e2e;
    }

    .panel-hint {
        font-size: 11px;
        color: #444;
        margin-top: 4px;
        line-height: 1.4;
    }

    .panel-divider {
        border: none;
        border-top: 1px solid #2a2a3e;
        margin: 16px 0;
    }

    /* Chapter info panel */
    .chapter-guide-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .chapter-guide-title {
        font-size: 12px;
        font-weight: 700;
        color: #A78BFA;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .chapter-guide-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .chapter-guide-list li {
        font-size: 12px;
        color: #888;
        padding: 3px 0;
        padding-left: 16px;
        position: relative;
    }

    .chapter-guide-list li::before {
        content: '•';
        position: absolute;
        left: 4px;
        color: #555;
    }

    /* Format specs display */
    .format-spec-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
    }

    .format-spec-card h4 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        margin: 0 0 10px;
    }

    .spec-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .spec-label {
        font-size: 12px;
        color: #666;
    }

    .spec-value {
        font-size: 12px;
        font-weight: 600;
        color: #A78BFA;
        background: rgba(139, 92, 246, 0.1);
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* Project selector */
    .project-selector-card {
        background: #1e1e2e;
        border: 1px solid #2a2a3e;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .project-selector-card h4 {
        font-size: 12px;
        font-weight: 700;
        color: #aaa;
        margin: 0 0 10px;
    }

    .project-option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
        margin-bottom: 4px;
        border: 1.5px solid transparent;
    }

    .project-option-item:hover {
        background: rgba(255,255,255,0.04);
    }

    .project-option-item.selected {
        background: rgba(139, 92, 246, 0.1);
        border-color: rgba(139, 92, 246, 0.3);
    }

    .project-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .project-option-name {
        flex: 1;
        font-size: 12.5px;
        color: #ccc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .project-option-count {
        font-size: 11px;
        color: #555;
        white-space: nowrap;
    }

    .no-projects-hint {
        text-align: center;
        padding: 20px;
        color: #555;
        font-size: 12px;
    }

    .no-projects-hint a {
        color: #A78BFA;
    }

    .bib-loading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #555;
        padding: 10px 0;
        justify-content: center;
    }

    .bib-count-badge {
        display: inline-block;
        background: rgba(139, 92, 246, 0.15);
        color: #A78BFA;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        margin-left: 6px;
    }

    /* Loading spinner */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #333;
        border-top-color: #A78BFA;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .builder-body {
            grid-template-columns: 200px 1fr 280px;
        }
        .a4-paper { width: 640px; }
        .page-break-hint { width: 640px; }
    }

    @media (max-width: 768px) {
        body { overflow: auto; }
        .builder-body {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
        }
        .builder-sidebar {
            border-right: none;
            border-bottom: 1px solid #2a2a3e;
        }
        .section-nav-list {
            display: flex;
            flex-direction: row;
            gap: 4px;
            overflow-x: auto;
            padding: 8px;
        }
        .section-nav-item {
            flex-shrink: 0;
        }
        .a4-paper {
            width: 95%;
            padding: 40px 30px;
        }
        .page-break-hint { width: 95%; }
    }
</style>

<div class="builder-wrap">

    <!-- Top Bar -->
    <div class="builder-topbar">
        <div class="topbar-left">
            <a href="<?php echo SITE_URL; ?>/users/report-template.php" class="topbar-back">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
            <span style="color: #333; font-size: 14px;">|</span>
            <span class="topbar-title">สร้างรายงาน</span>
            <span class="topbar-template-badge" id="template-badge">
                <i class="fas fa-file-lines"></i>
                <span id="template-badge-name">กำลังโหลด...</span>
            </span>
        </div>
        <div class="topbar-actions">
            <button class="topbar-btn topbar-btn-pdf" onclick="exportReport('pdf')" id="btn-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button class="topbar-btn topbar-btn-docx" onclick="exportReport('docx')" id="btn-docx">
                <i class="fas fa-file-word"></i> Export Word
            </button>
        </div>
    </div>

    <!-- Builder Body -->
    <div class="builder-body">

        <!-- ===== LEFT: Section Navigation ===== -->
        <div class="builder-sidebar">
            <div class="sidebar-section-title">โครงสร้างเอกสาร</div>
            <div class="section-nav-list" id="section-nav-list">
                <!-- Populated by JS -->
            </div>
            <div class="sidebar-format">
                <div class="sidebar-format-title">การจัดรูปแบบ</div>
                <div class="format-row">
                    <label>ฟอนต์เอกสาร</label>
                    <select id="setting-font" onchange="updateFormatSettings()">
                        <option value="Angsana New">Angsana New (มาตรฐาน)</option>
                        <option value="TH Sarabun New">TH Sarabun New</option>
                        <option value="TH Niramit AS">TH Niramit AS</option>
                        <option value="Times New Roman">Times New Roman</option>
                    </select>
                </div>
                <div class="format-row">
                    <label>ขนาดตัวอักษรเนื้อหา</label>
                    <select id="setting-body-size" onchange="updateFormatSettings()">
                        <option value="14">14pt</option>
                        <option value="15">15pt</option>
                        <option value="16" selected>16pt (มาตรฐาน)</option>
                    </select>
                </div>
                <div class="format-row">
                    <label>ระยะขอบกระดาษ</label>
                    <select id="setting-margin" onchange="updateFormatSettings()">
                        <option value="standard" selected>มาตรฐาน (1.5"/1")</option>
                        <option value="wide">กว้าง (2"/1.5")</option>
                        <option value="narrow">แคบ (1"/1")</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ===== CENTER: A4 Preview ===== -->
        <div class="builder-preview" id="builder-preview">
            <div class="preview-header-label">
                <i class="fas fa-eye"></i> ตัวอย่างเอกสาร (A4)
            </div>
            <!-- Sections rendered by JS -->
            <div id="preview-pages"></div>
        </div>

        <!-- ===== RIGHT: Content Panel ===== -->
        <div class="builder-panel">
            <div class="panel-header">
                <div class="panel-header-copy">
                    <h3 id="panel-section-title">กำลังโหลด...</h3>
                    <p id="panel-section-desc">กรอกข้อมูลสำหรับส่วนนี้</p>
                </div>
                <button type="button" class="panel-header-action" id="panel-autofill-btn" onclick="autofillAcademicCoverSample()" hidden>
                    <i class="fas fa-wand-magic-sparkles"></i>
                    กรอกข้อมูลอัตโนมัติ
                </button>
            </div>
            <div class="panel-body" id="panel-body">
                <!-- Template-specific form loaded by JS -->
            </div>
        </div>

    </div>
</div>

<script>
// ======================================================
//  Template Definitions
// ======================================================
const TEMPLATE_DEFS = {
    academic_general: {
        name: 'รายงานวิชาการทั่วไป',
        icon: 'fa-file-lines',
        color: '#8B5CF6',
        gradient: 'linear-gradient(135deg, #8B5CF6, #6366F1)',
        coverType: 'academic',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'inner_cover', type: 'inner_cover', label: 'ปกใน', icon: 'fa-id-card-clip' },
            { id: 'preface', type: 'preface', label: 'คำนำ', icon: 'fa-pen-nib' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ความเป็นมาและความสำคัญของปัญหา', 'วัตถุประสงค์ของการศึกษา', 'ขอบเขตการศึกษา', 'ประโยชน์ที่คาดว่าจะได้รับ', 'นิยามศัพท์'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 เนื้อหา', icon: 'fa-book-open', number: 2, title: 'เนื้อหา',
              subsections: ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'เนื้อหาสาระ', 'รายละเอียดและการวิเคราะห์'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 สรุป', icon: 'fa-book-open', number: 3, title: 'สรุปและอภิปรายผล',
              subsections: ['สรุปผลการศึกษา', 'อภิปรายผล', 'ข้อเสนอแนะ'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' }
        ]
    },
    research: {
        name: 'รายงานการวิจัย',
        icon: 'fa-microscope',
        color: '#3B82F6',
        gradient: 'linear-gradient(135deg, #3B82F6, #06B6D4)',
        coverType: 'research',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'abstract', type: 'abstract', label: 'บทคัดย่อ', icon: 'fa-align-left' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ความเป็นมาและความสำคัญ', 'คำถามวิจัย', 'วัตถุประสงค์การวิจัย', 'สมมติฐาน', 'ขอบเขตการวิจัย', 'ข้อตกลงเบื้องต้น', 'นิยามศัพท์'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 วรรณกรรมที่เกี่ยวข้อง', icon: 'fa-book-open', number: 2, title: 'เอกสารและงานวิจัยที่เกี่ยวข้อง',
              subsections: ['แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง', 'กรอบแนวคิดของการวิจัย'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 วิธีดำเนินการ', icon: 'fa-book-open', number: 3, title: 'วิธีดำเนินการวิจัย',
              subsections: ['ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือวิจัย', 'การตรวจสอบคุณภาพเครื่องมือ', 'การเก็บรวบรวมข้อมูล', 'การวิเคราะห์ข้อมูล', 'สถิติที่ใช้'] },
            { id: 'ch4', type: 'chapter', label: 'บทที่ 4 ผลการวิจัย', icon: 'fa-book-open', number: 4, title: 'ผลการวิจัย',
              subsections: ['ลักษณะกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ข้อมูลตามวัตถุประสงค์'] },
            { id: 'ch5', type: 'chapter', label: 'บทที่ 5 สรุปอภิปราย', icon: 'fa-book-open', number: 5, title: 'สรุป อภิปรายผล และข้อเสนอแนะ',
              subsections: ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยครั้งต่อไป'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' }
        ]
    },
    internship: {
        name: 'รายงานฝึกงาน / สหกิจ',
        icon: 'fa-briefcase',
        color: '#10B981',
        gradient: 'linear-gradient(135deg, #10B981, #059669)',
        coverType: 'internship',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'approval', type: 'approval', label: 'หน้าอนุมัติ', icon: 'fa-file-signature' },
            { id: 'acknowledgment', type: 'acknowledgment', label: 'กิตติกรรมประกาศ', icon: 'fa-heart' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ความเป็นมาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตของรายงาน', 'ประโยชน์ที่ได้รับ'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 ข้อมูลองค์กร', icon: 'fa-book-open', number: 2, title: 'ข้อมูลสถานประกอบการ',
              subsections: ['ประวัติและความเป็นมา', 'วิสัยทัศน์ พันธกิจ', 'โครงสร้างองค์กร', 'ลักษณะการดำเนินงาน'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 งานที่ได้รับ', icon: 'fa-book-open', number: 3, title: 'งานที่ได้รับมอบหมาย',
              subsections: ['ลักษณะตำแหน่งงาน', 'งานที่ได้รับมอบหมายหลัก', 'ขั้นตอนและวิธีการปฏิบัติงาน', 'เครื่องมือและอุปกรณ์ที่ใช้'] },
            { id: 'ch4', type: 'chapter', label: 'บทที่ 4 ผลการปฏิบัติงาน', icon: 'fa-book-open', number: 4, title: 'ผลการปฏิบัติงาน',
              subsections: ['ผลการปฏิบัติงานโดยภาพรวม', 'ปัญหาและอุปสรรค', 'วิธีแก้ปัญหา'] },
            { id: 'ch5', type: 'chapter', label: 'บทที่ 5 สรุป', icon: 'fa-book-open', number: 5, title: 'สรุปและข้อเสนอแนะ',
              subsections: ['สรุปผลการฝึกงาน', 'ความรู้และทักษะที่ได้รับ', 'ข้อเสนอแนะ'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' }
        ]
    },
    project: {
        name: 'รายงานโครงการ',
        icon: 'fa-diagram-project',
        color: '#F59E0B',
        gradient: 'linear-gradient(135deg, #F59E0B, #F97316)',
        coverType: 'project',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ที่มาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตของโครงการ', 'ประโยชน์ที่คาดว่าจะได้รับ'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 ทฤษฎี', icon: 'fa-book-open', number: 2, title: 'แนวคิด ทฤษฎี และงานที่เกี่ยวข้อง',
              subsections: ['ทฤษฎีที่เกี่ยวข้อง', 'เทคโนโลยีที่ใช้', 'งานที่เกี่ยวข้อง'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 การออกแบบ', icon: 'fa-book-open', number: 3, title: 'การออกแบบและพัฒนา',
              subsections: ['การวิเคราะห์ความต้องการ', 'การออกแบบระบบ/ผลิตภัณฑ์', 'ขั้นตอนการพัฒนา', 'เครื่องมือที่ใช้'] },
            { id: 'ch4', type: 'chapter', label: 'บทที่ 4 ผลลัพธ์', icon: 'fa-book-open', number: 4, title: 'ผลการดำเนินงาน',
              subsections: ['ผลลัพธ์ที่ได้', 'การทดสอบ', 'ปัญหาและแนวทางแก้ไข'] },
            { id: 'ch5', type: 'chapter', label: 'บทที่ 5 สรุป', icon: 'fa-book-open', number: 5, title: 'สรุปและข้อเสนอแนะ',
              subsections: ['สรุปผลโครงการ', 'ข้อเสนอแนะ', 'แนวทางการพัฒนาต่อ'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' }
        ]
    },
    thesis: {
        name: 'วิทยานิพนธ์ / สารนิพนธ์',
        icon: 'fa-graduation-cap',
        color: '#EF4444',
        gradient: 'linear-gradient(135deg, #EF4444, #DC2626)',
        coverType: 'thesis',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'acknowledgment', type: 'acknowledgment', label: 'กิตติกรรมประกาศ', icon: 'fa-heart' },
            { id: 'abstract_th', type: 'abstract', label: 'บทคัดย่อ (ไทย)', icon: 'fa-align-left', lang: 'th' },
            { id: 'abstract_en', type: 'abstract', label: 'Abstract (English)', icon: 'fa-align-left', lang: 'en' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ความเป็นมาและความสำคัญ', 'คำถามวิจัย', 'วัตถุประสงค์', 'สมมติฐาน', 'ขอบเขต', 'นิยามศัพท์', 'ประโยชน์'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 วรรณกรรม', icon: 'fa-book-open', number: 2, title: 'วรรณกรรมและงานวิจัยที่เกี่ยวข้อง',
              subsections: ['กรอบแนวคิด', 'ทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้อง'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 วิธีวิจัย', icon: 'fa-book-open', number: 3, title: 'วิธีดำเนินการวิจัย',
              subsections: ['รูปแบบการวิจัย', 'ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือวิจัย', 'การตรวจสอบคุณภาพ', 'การเก็บข้อมูล', 'การวิเคราะห์', 'สถิติ'] },
            { id: 'ch4', type: 'chapter', label: 'บทที่ 4 ผลการวิจัย', icon: 'fa-book-open', number: 4, title: 'ผลการวิจัย',
              subsections: ['ลักษณะกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ตามวัตถุประสงค์'] },
            { id: 'ch5', type: 'chapter', label: 'บทที่ 5 สรุปอภิปราย', icon: 'fa-book-open', number: 5, title: 'สรุป อภิปรายผล และข้อเสนอแนะ',
              subsections: ['สรุปผลการวิจัย', 'อภิปรายผล', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยต่อไป'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' }
        ]
    },
    thesis_master: {
        name: 'วิทยานิพนธ์ ป.โท',
        icon: 'fa-user-graduate',
        color: '#7C3AED',
        gradient: 'linear-gradient(135deg, #7C3AED, #5B21B6)',
        coverType: 'thesis',
        sections: [
            { id: 'cover', type: 'cover', label: 'หน้าปก', icon: 'fa-id-card' },
            { id: 'approval', type: 'approval', label: 'หน้าอนุมัติ', icon: 'fa-file-signature' },
            { id: 'abstract_th', type: 'abstract', label: 'บทคัดย่อ (ไทย)', icon: 'fa-align-left', lang: 'th' },
            { id: 'abstract_en', type: 'abstract', label: 'Abstract (English)', icon: 'fa-align-left', lang: 'en' },
            { id: 'acknowledgment', type: 'acknowledgment', label: 'กิตติกรรมประกาศ', icon: 'fa-heart' },
            { id: 'toc', type: 'toc', label: 'สารบัญ', icon: 'fa-list-ul' },
            { id: 'ch1', type: 'chapter', label: 'บทที่ 1 บทนำ', icon: 'fa-book-open', number: 1, title: 'บทนำ',
              subsections: ['ความเป็นมาและความสำคัญของปัญหา', 'คำถามการวิจัย', 'วัตถุประสงค์การวิจัย', 'ขอบเขตการวิจัย', 'นิยามศัพท์เฉพาะ', 'ประโยชน์ที่คาดว่าจะได้รับ'] },
            { id: 'ch2', type: 'chapter', label: 'บทที่ 2 วรรณกรรมที่เกี่ยวข้อง', icon: 'fa-book-open', number: 2, title: 'เอกสารและงานวิจัยที่เกี่ยวข้อง',
              subsections: ['กรอบแนวคิดการวิจัย', 'แนวคิดและทฤษฎีที่เกี่ยวข้อง', 'งานวิจัยที่เกี่ยวข้องในประเทศ', 'งานวิจัยที่เกี่ยวข้องต่างประเทศ'] },
            { id: 'ch3', type: 'chapter', label: 'บทที่ 3 วิธีดำเนินการวิจัย', icon: 'fa-book-open', number: 3, title: 'วิธีดำเนินการวิจัย',
              subsections: ['รูปแบบการวิจัย', 'ประชากรและกลุ่มตัวอย่าง', 'เครื่องมือที่ใช้ในการวิจัย', 'การตรวจสอบคุณภาพเครื่องมือ', 'การเก็บรวบรวมข้อมูล', 'การวิเคราะห์ข้อมูล', 'สถิติที่ใช้ในการวิเคราะห์'] },
            { id: 'ch4', type: 'chapter', label: 'บทที่ 4 ผลการวิจัย', icon: 'fa-book-open', number: 4, title: 'ผลการวิจัย',
              subsections: ['ลักษณะของกลุ่มตัวอย่าง', 'ผลการวิเคราะห์ข้อมูลตามวัตถุประสงค์การวิจัย', 'ผลการทดสอบสมมติฐาน'] },
            { id: 'ch5', type: 'chapter', label: 'บทที่ 5 สรุปอภิปรายผล', icon: 'fa-book-open', number: 5, title: 'สรุป อภิปรายผล และข้อเสนอแนะ',
              subsections: ['สรุปผลการวิจัย', 'อภิปรายผลการวิจัย', 'ข้อเสนอแนะในการนำผลไปใช้', 'ข้อเสนอแนะสำหรับการวิจัยครั้งต่อไป'] },
            { id: 'bibliography', type: 'bibliography', label: 'บรรณานุกรม', icon: 'fa-book' },
            { id: 'appendix', type: 'appendix', label: 'ภาคผนวก', icon: 'fa-paperclip' },
            { id: 'biography', type: 'biography', label: 'ประวัติผู้เขียน', icon: 'fa-user-circle' }
        ]
    }
};

// Current template
const templateId = <?php echo json_encode($templateId); ?>;
const template = TEMPLATE_DEFS[templateId];
let activeSection = 'cover';
let selectedProjectId = null;
let loadedBibliographies = [];

// Cover data
let coverData = {
    title: '',
    authors: '',
    studentIds: '',
    course: '',
    courseCode: '',
    instructor: '',
    department: '',
    institution: '',
    company: '',
    supervisor: '',
    projectType: '',
    internshipPeriod: '',
    degree: '',
    major: '',
    committee: '',
    semester: '1',
    year: '<?php echo (date('Y') + 543); ?>'
};

let formatSettings = {
    font: 'Angsana New',
    bodySize: 16,
    margin: 'standard'
};

// ======================================================
//  INIT
// ======================================================
document.addEventListener('DOMContentLoaded', function() {
    initBuilder();
});

function initBuilder() {
    // Measure actual navbar height to prevent scroll bleed
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        document.documentElement.style.setProperty('--nav-height', navbar.offsetHeight + 'px');
    }

    // Set badge
    const badge = document.getElementById('template-badge');
    badge.style.background = template.color + '22';
    badge.style.border = '1px solid ' + template.color + '55';
    badge.style.color = template.color;
    document.getElementById('template-badge-name').textContent = template.name;

    // Build section nav
    buildSectionNav();

    // Show first section
    selectSection('cover');

    // Observe scroll in preview — update nav + panel when a page enters view
    initScrollObserver();
}

// ======================================================
//  SCROLL OBSERVER: เลื่อนถึงหน้าไหน → ซ้าย/ขวาเปลี่ยน
// ======================================================
let _scrollObserver = null;
let _isClickScrolling = false; // ป้องกัน observer ยิงตอนคลิก nav

function initScrollObserver() {
    if (_scrollObserver) _scrollObserver.disconnect();

    const previewEl = document.getElementById('builder-preview');
    if (!previewEl) return;

    _scrollObserver = new IntersectionObserver((entries) => {
        if (_isClickScrolling) return;

        // หาหน้าที่มองเห็นมากที่สุด (intersectionRatio สูงสุด)
        let best = null;
        entries.forEach(entry => {
            if (!best || entry.intersectionRatio > best.intersectionRatio) {
                best = entry;
            }
        });

        if (best && best.isIntersecting) {
            const sectionId = best.target.id.replace('preview-', '');
            const section = template.sections.find(s => s.id === sectionId);
            if (section && sectionId !== activeSection) {
                activeSection = sectionId;

                // อัปเดต nav ซ้าย
                document.querySelectorAll('.section-nav-item').forEach(el => {
                    el.classList.toggle('active', el.dataset.sectionId === sectionId);
                });
                // เลื่อน nav ให้ item ปัจจุบันอยู่ในวิว
                const navItem = document.querySelector(`.section-nav-item[data-section-id="${sectionId}"]`);
                if (navItem) navItem.scrollIntoView({ block: 'nearest' });

                // อัปเดต panel ขวา
                renderPanel(section);
            }
        }
    }, {
        root: previewEl,
        threshold: [0.1, 0.3, 0.5, 0.7]
    });

    // Observe ทุก a4-paper
    document.querySelectorAll('.a4-paper').forEach(el => {
        _scrollObserver.observe(el);
    });
}

function buildSectionNav() {
    const list = document.getElementById('section-nav-list');
    list.innerHTML = '';

    template.sections.forEach(section => {
        const item = document.createElement('div');
        item.className = 'section-nav-item' + (section.id === activeSection ? ' active' : '');
        item.dataset.sectionId = section.id;
        item.onclick = () => selectSection(section.id);
        item.innerHTML = `
            <div class="section-nav-icon">
                <i class="fas ${section.icon}"></i>
            </div>
            <span class="section-nav-label">${section.label}</span>
        `;
        list.appendChild(item);
    });
}

function selectSection(sectionId) {
    activeSection = sectionId;

    // Update active nav item
    document.querySelectorAll('.section-nav-item').forEach(el => {
        el.classList.toggle('active', el.dataset.sectionId === sectionId);
    });

    // Find section definition
    const section = template.sections.find(s => s.id === sectionId);
    if (!section) return;

    // Render panel
    renderPanel(section);

    // Render preview
    renderAllPreviews();

    // Re-attach observer to newly rendered pages
    initScrollObserver();

    // Scroll preview to this section (flag prevents observer from interfering)
    _isClickScrolling = true;
    setTimeout(() => {
        const el = document.getElementById('preview-' + sectionId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        // Clear flag after scroll animation settles
        setTimeout(() => { _isClickScrolling = false; }, 800);
    }, 100);
}

// ======================================================
//  PANEL RENDERING
// ======================================================
function renderPanel(section) {
    const panelTitle = document.getElementById('panel-section-title');
    const panelDesc = document.getElementById('panel-section-desc');
    const panelBody = document.getElementById('panel-body');
    const panelAutofillBtn = document.getElementById('panel-autofill-btn');

    if (panelAutofillBtn) {
        const showAutofill = template.coverType === 'academic' && (section.type === 'cover' || section.type === 'inner_cover');
        panelAutofillBtn.hidden = !showAutofill;
    }

    switch (section.type) {
        case 'cover':
            panelTitle.textContent = 'ข้อมูลหน้าปก';
            panelDesc.textContent = 'กรอกข้อมูลเพื่อสร้างหน้าปกอัตโนมัติ';
            renderCoverPanel(panelBody);
            break;
        case 'inner_cover':
            panelTitle.textContent = 'ปกใน';
            panelDesc.textContent = 'จัดทำเช่นเดียวกับหน้าปกนอก (ใช้ข้อมูลเดียวกัน)';
            renderCoverPanel(panelBody);
            break;
        case 'chapter':
            panelTitle.textContent = section.label;
            panelDesc.textContent = 'สารบัญย่อยและแนวทางการเขียน';
            renderChapterPanel(panelBody, section);
            break;
        case 'toc':
            panelTitle.textContent = 'สารบัญ';
            panelDesc.textContent = 'สร้างอัตโนมัติจากโครงสร้างบท';
            renderTocPanel(panelBody);
            break;
        case 'abstract':
            panelTitle.textContent = 'บทคัดย่อ';
            panelDesc.textContent = 'สรุปสาระสำคัญของงาน 150-250 คำ';
            renderAbstractPanel(panelBody, section);
            break;
        case 'acknowledgment':
            panelTitle.textContent = 'กิตติกรรมประกาศ';
            panelDesc.textContent = 'ขอบคุณผู้มีส่วนช่วยเหลือ';
            renderAcknowledgmentPanel(panelBody);
            break;
        case 'bibliography':
            panelTitle.textContent = 'บรรณานุกรม';
            panelDesc.textContent = 'เลือกโครงการที่มีรายการบรรณานุกรม';
            renderBibliographyPanel(panelBody);
            break;
        case 'appendix':
            panelTitle.textContent = 'ภาคผนวก';
            panelDesc.textContent = 'เอกสาร/รูปภาพประกอบเพิ่มเติม';
            renderAppendixPanel(panelBody);
            break;
        case 'preface':
            panelTitle.textContent = 'คำนำ';
            panelDesc.textContent = 'แนะนำและชี้แจงวัตถุประสงค์ของรายงาน';
            renderPrefacePanel(panelBody);
            break;
        case 'approval':
            panelTitle.textContent = 'หน้าอนุมัติ';
            panelDesc.textContent = 'ลายเซ็นผู้อนุมัติและคณะกรรมการ';
            renderApprovalPanel(panelBody);
            break;
        case 'biography':
            panelTitle.textContent = 'ประวัติผู้เขียน';
            panelDesc.textContent = 'ประวัติการศึกษาและข้อมูลผู้วิจัย';
            renderBiographyPanel(panelBody);
            break;
        default:
            panelTitle.textContent = section.label;
            panelDesc.textContent = '';
            panelBody.innerHTML = '<p style="color:#555; font-size:12px; padding:10px 0;">ส่วนนี้จะสร้างโดยอัตโนมัติในไฟล์ที่ export</p>';
    }
}

// Cover panel
function renderCoverPanel(container) {
    let coverFields = '';
    const type = template.coverType;

    // Common fields
    coverFields += formGroup('ชื่อรายงาน / หัวข้อ', 'fa-heading',
        `<textarea class="panel-textarea" id="cv-title" placeholder="เช่น การศึกษาผลของ..." rows="3" oninput="coverData.title=this.value; updateCoverPreview()">${escHtml(coverData.title)}</textarea>`);

    coverFields += formGroup('ผู้จัดทำ / ชื่อผู้เขียน', 'fa-user',
        `<textarea class="panel-textarea" id="cv-authors" placeholder="นาย/นางสาว ชื่อ นามสกุล&#10;หรือหลายคน (แต่ละคนขึ้นบรรทัดใหม่)" rows="3" oninput="coverData.authors=this.value; updateCoverPreview()">${escHtml(coverData.authors)}</textarea>`);

    if (type !== 'thesis') {
        coverFields += formGroup('รหัสนักศึกษา', 'fa-id-badge',
            `<textarea class="panel-textarea" id="cv-ids" placeholder="XXXXXXXXX&#10;หลายคน: แต่ละรหัสขึ้นบรรทัดใหม่" rows="2" oninput="coverData.studentIds=this.value; updateCoverPreview()">${escHtml(coverData.studentIds)}</textarea>`);
    }

    if (type === 'internship') {
        coverFields += formGroup('สถานประกอบการ / บริษัท', 'fa-building',
            `<input class="panel-input" id="cv-company" type="text" placeholder="ชื่อองค์กร/บริษัท" value="${escHtml(coverData.company)}" oninput="coverData.company=this.value; updateCoverPreview()">`);
        coverFields += formGroup('ผู้ควบคุมการฝึกงาน', 'fa-user-tie',
            `<input class="panel-input" id="cv-supervisor" type="text" placeholder="ชื่อ-นามสกุล ผู้ควบคุม" value="${escHtml(coverData.supervisor)}" oninput="coverData.supervisor=this.value; updateCoverPreview()">`);
        coverFields += formGroup('ช่วงเวลาฝึกงาน', 'fa-calendar',
            `<input class="panel-input" id="cv-period" type="text" placeholder="เช่น 1 มิ.ย. - 31 ส.ค. 2567" value="${escHtml(coverData.internshipPeriod)}" oninput="coverData.internshipPeriod=this.value; updateCoverPreview()">`);
    }

    if (type === 'project') {
        coverFields += formGroup('ประเภทโครงการ', 'fa-tag',
            `<input class="panel-input" id="cv-projtype" type="text" placeholder="เช่น โครงงานคอมพิวเตอร์, Senior Project" value="${escHtml(coverData.projectType)}" oninput="coverData.projectType=this.value; updateCoverPreview()">`);
    }

    if (type !== 'internship') {
        const courseLabel = type === 'thesis' ? 'สาขาวิชา' : 'รายวิชา';
        coverFields += formGroup(courseLabel, 'fa-book',
            `<input class="panel-input" id="cv-course" type="text" placeholder="${type === 'thesis' ? 'เช่น บรรณารักษศาสตร์และสารสนเทศศาสตร์' : 'เช่น ภาษาไทยเพื่อการสื่อสาร, TH101'}" value="${escHtml(coverData.course)}" oninput="coverData.course=this.value; updateCoverPreview()">`);
    }

    if (type === 'thesis') {
        coverFields += formGroup('ปริญญา', 'fa-graduation-cap',
            `<input class="panel-input" id="cv-degree" type="text" placeholder="เช่น วิทยาศาสตรมหาบัณฑิต" value="${escHtml(coverData.degree)}" oninput="coverData.degree=this.value; updateCoverPreview()">`);
        coverFields += formGroup('คณะกรรมการที่ปรึกษา', 'fa-users',
            `<textarea class="panel-textarea" id="cv-committee" placeholder="รศ.ดร. ชื่อ นามสกุล (ประธาน)&#10;ผศ.ดร. ชื่อ นามสกุล" rows="3" oninput="coverData.committee=this.value; updateCoverPreview()">${escHtml(coverData.committee)}</textarea>`);
    } else if (type !== 'academic') {
        coverFields += formGroup('อาจารย์ผู้สอน / ที่ปรึกษา', 'fa-user-graduate',
            `<input class="panel-input" id="cv-instructor" type="text" placeholder="เช่น รศ.ดร. ชื่อ นามสกุล" value="${escHtml(coverData.instructor)}" oninput="coverData.instructor=this.value; updateCoverPreview()">`);
    }

    coverFields += formGroup('ภาควิชา / คณะ', 'fa-landmark',
        `<input class="panel-input" id="cv-dept" type="text" placeholder="เช่น ภาควิชาบรรณารักษศาสตร์, คณะมนุษยศาสตร์" value="${escHtml(coverData.department)}" oninput="coverData.department=this.value; updateCoverPreview()">`);

    coverFields += formGroup('สถาบัน / มหาวิทยาลัย', 'fa-university',
        `<input class="panel-input" id="cv-inst" type="text" placeholder="เช่น มหาวิทยาลัยเชียงใหม่" value="${escHtml(coverData.institution)}" oninput="coverData.institution=this.value; updateCoverPreview()">`);

    if (type !== 'thesis') {
        coverFields += `<div class="panel-form-group">
            <label><i class="fas fa-calendar-alt"></i> ภาคเรียน / ปีการศึกษา</label>
            <div style="display:flex; gap:8px;">
                <select class="panel-select" id="cv-semester" style="flex:1" onchange="coverData.semester=this.value; updateCoverPreview()">
                    <option value="1" ${coverData.semester==='1'?'selected':''}>ภาคเรียนที่ 1</option>
                    <option value="2" ${coverData.semester==='2'?'selected':''}>ภาคเรียนที่ 2</option>
                    <option value="3" ${coverData.semester==='3'?'selected':''}>ภาคฤดูร้อน</option>
                </select>
                <input class="panel-input" id="cv-year" type="text" placeholder="เช่น 2567" style="width:80px;"
                    value="${escHtml(coverData.year)}" oninput="coverData.year=this.value; updateCoverPreview()">
            </div>
        </div>`;
    } else {
        coverFields += formGroup('ปีการศึกษา (พ.ศ.)', 'fa-calendar',
            `<input class="panel-input" id="cv-year" type="text" placeholder="เช่น 2567" value="${escHtml(coverData.year)}" oninput="coverData.year=this.value; updateCoverPreview()">`);
    }

    container.innerHTML = coverFields;
}

function autofillAcademicCoverSample() {
    coverData.title = 'ผลกระทบจากการส่งออกอุตสาหกรรมอาหาร\nของจีนยุคใหม่\nต่อประเทศเพื่อนบ้านในอาเซียน';
    coverData.authors = 'นายกนกศักดิ์ ลอยเลิศ';
    coverData.studentIds = '650510276';
    coverData.course = 'รายงานกระบวนวิชาการรู้สารสนเทศและการนําเสนอสารสนเทศ';
    coverData.department = 'ภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร';
    coverData.institution = 'คณะมนุษยศาสตร์ มหาวิทยาลัยเชียงใหม่';

    const active = template.sections.find(s => s.id === activeSection) || template.sections.find(s => s.id === 'cover');
    if (active && (active.type === 'cover' || active.type === 'inner_cover')) {
        renderPanel(active);
    }

    updateCoverPreview();
}

function formGroup(label, icon, input) {
    return `<div class="panel-form-group">
        <label><i class="fas ${icon}"></i> ${label}</label>
        ${input}
    </div>`;
}

// Chapter panel
function renderChapterPanel(container, section) {
    let html = `<div class="chapter-guide-card">
        <div class="chapter-guide-title"><i class="fas fa-list-check"></i> เนื้อหาที่ควรมีในบทนี้</div>
        <ul class="chapter-guide-list">`;
    section.subsections.forEach(sub => {
        html += `<li>${sub}</li>`;
    });
    html += `</ul></div>`;

    html += `<hr class="panel-divider">
    <div class="format-spec-card">
        <h4>การจัดรูปแบบบท</h4>
        <div class="spec-row">
            <span class="spec-label">หัวบท (บทที่ X)</span>
            <span class="spec-value">18pt Bold Center</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">ชื่อบท</span>
            <span class="spec-value">18pt Bold Center</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">หัวข้อย่อย</span>
            <span class="spec-value">16pt Bold</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">เนื้อหา</span>
            <span class="spec-value">16pt ระยะ 1.5 บรรทัด</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">ย่อหน้า</span>
            <span class="spec-value">เว้น 1.5 cm</span>
        </div>
    </div>
    <hr class="panel-divider">
    <div class="format-spec-card">
        <h4>ระยะขอบกระดาษ</h4>
        <div class="spec-row">
            <span class="spec-label">ซ้าย / บน</span>
            <span class="spec-value">1.5 นิ้ว (3.81 cm)</span>
        </div>
        <div class="spec-row">
            <span class="spec-label">ขวา / ล่าง</span>
            <span class="spec-value">1 นิ้ว (2.54 cm)</span>
        </div>
    </div>`;

    container.innerHTML = html;
}

function renderTocPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-info-circle"></i> เกี่ยวกับสารบัญ</div>
            <ul class="chapter-guide-list">
                <li>สารบัญจะสร้างอัตโนมัติใน Word (.docx)</li>
                <li>หน้าสารบัญจะอยู่ก่อนบทที่ 1</li>
                <li>แสดงชื่อบทพร้อมหมายเลขหน้า</li>
                <li>สามารถอัปเดต TOC ใน Word ได้</li>
            </ul>
        </div>
        <hr class="panel-divider">
        <div class="format-spec-card">
            <h4>การจัดรูปแบบสารบัญ</h4>
            <div class="spec-row"><span class="spec-label">หัวข้อ "สารบัญ"</span><span class="spec-value">18pt Bold Center</span></div>
            <div class="spec-row"><span class="spec-label">รายการบท</span><span class="spec-value">16pt</span></div>
            <div class="spec-row"><span class="spec-label">เส้นนำ</span><span class="spec-value">...... (หน้า)</span></div>
        </div>`;
}

function renderAbstractPanel(container, section) {
    const isEn = section.lang === 'en';
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-pen"></i> แนวทางการเขียนบทคัดย่อ${isEn ? ' (English)':''}</div>
            <ul class="chapter-guide-list">
                <li>ความยาว 150–300 คำ</li>
                <li>วัตถุประสงค์ของงาน</li>
                <li>วิธีดำเนินการโดยย่อ</li>
                <li>ผลการศึกษาหลัก</li>
                <li>ข้อสรุปและข้อเสนอแนะ</li>
                ${isEn ? '<li>ใช้ Active voice</li><li>ไม่ใช้ I/We</li>' : ''}
            </ul>
        </div>
        <hr class="panel-divider">
        <div class="format-spec-card">
            <h4>การจัดรูปแบบ</h4>
            <div class="spec-row"><span class="spec-label">หัวข้อ</span><span class="spec-value">18pt Bold Center</span></div>
            <div class="spec-row"><span class="spec-label">เนื้อหา</span><span class="spec-value">16pt ระยะ 1.5 บรรทัด</span></div>
            <div class="spec-row"><span class="spec-label">คำสำคัญ</span><span class="spec-value">ตามด้วย Keywords</span></div>
        </div>`;
}

function renderAcknowledgmentPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-heart"></i> แนวทางกิตติกรรมประกาศ</div>
            <ul class="chapter-guide-list">
                <li>ขอบคุณอาจารย์ที่ปรึกษา</li>
                <li>ขอบคุณผู้เกี่ยวข้องและผู้ให้ข้อมูล</li>
                <li>ขอบคุณครอบครัว</li>
                <li>ลงชื่อผู้วิจัย + วันที่</li>
            </ul>
        </div>`;
}

function renderBibliographyPanel(container) {
    const projects = <?php echo json_encode($userProjects); ?>;

    let html = `<div class="project-selector-card">
        <h4><i class="fas fa-folder" style="color:#A78BFA; margin-right:6px;"></i>เลือกโครงการ</h4>`;

    if (projects.length === 0) {
        html += `<div class="no-projects-hint">
            <i class="fas fa-folder-open" style="font-size:24px; margin-bottom:8px; display:block; color:#333;"></i>
            ยังไม่มีโครงการ<br>
            <a href="<?php echo SITE_URL; ?>/users/projects.php" target="_blank">สร้างโครงการใหม่ →</a>
        </div>`;
    } else {
        html += `<div style="margin-bottom:4px; font-size:11px; color:#555;">คลิกเพื่อเลือก (เลือกได้ 1 โครงการ)</div>`;
        projects.forEach(p => {
            html += `<div class="project-option-item ${selectedProjectId === p.id ? 'selected' : ''}"
                id="proj-${p.id}"
                onclick="selectProject(${p.id})">
                <div class="project-dot" style="background: ${escHtmlAttr(p.color)}"></div>
                <span class="project-option-name">${escHtmlJs(p.name)}</span>
                <span class="project-option-count">${p.bib_count} รายการ</span>
            </div>`;
        });
    }

    html += `</div>`;

    // Loaded bibliographies preview
    html += `<div id="bib-panel-list">`;
    if (selectedProjectId && loadedBibliographies.length > 0) {
        html += renderBibPanelList();
    } else if (selectedProjectId) {
        html += `<div class="bib-loading"><span class="spinner"></span> กำลังโหลด...</div>`;
    } else {
        html += `<p style="font-size:12px; color:#444; text-align:center; padding:16px 0;">
            เลือกโครงการด้านบนเพื่อดูรายการบรรณานุกรม</p>`;
    }
    html += `</div>`;

    // Format specs
    html += `<hr class="panel-divider">
    <div class="format-spec-card">
        <h4>การจัดรูปแบบบรรณานุกรม (APA 7)</h4>
        <div class="spec-row"><span class="spec-label">หัวข้อ "บรรณานุกรม"</span><span class="spec-value">18pt Bold Center</span></div>
        <div class="spec-row"><span class="spec-label">รายการ</span><span class="spec-value">16pt ระยะ 1.5</span></div>
        <div class="spec-row"><span class="spec-label">Hanging Indent</span><span class="spec-value">0.5 นิ้ว</span></div>
        <div class="spec-row"><span class="spec-label">ลำดับ</span><span class="spec-value">ภาษาไทยก่อน > อังกฤษ</span></div>
    </div>`;

    container.innerHTML = html;
}

function renderBibPanelList() {
    if (!loadedBibliographies.length) return '';
    let html = `<div style="margin-bottom:10px;">
        <span style="font-size:12px; color:#777;">รายการบรรณานุกรม</span>
        <span class="bib-count-badge">${loadedBibliographies.length} รายการ</span>
    </div>`;

    loadedBibliographies.slice(0, 5).forEach(bib => {
        const shortText = bib.bibliography_text.replace(/<[^>]*>/g, '').substring(0, 80);
        html += `<div style="font-size:11px; color:#777; padding:6px 8px; background:#1e1e2e; border-radius:6px; margin-bottom:4px; line-height:1.4;">
            ${escHtmlJs(shortText)}${bib.bibliography_text.length > 80 ? '...' : ''}
        </div>`;
    });
    if (loadedBibliographies.length > 5) {
        html += `<div style="font-size:11px; color:#555; text-align:center; padding:4px;">... และอีก ${loadedBibliographies.length - 5} รายการ</div>`;
    }
    return html;
}

function renderAppendixPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-paperclip"></i> ภาคผนวก</div>
            <ul class="chapter-guide-list">
                <li>แบบสอบถาม/แบบทดสอบที่ใช้</li>
                <li>รูปภาพประกอบ</li>
                <li>ข้อมูลดิบ</li>
                <li>หนังสือขออนุญาต</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">ส่วนนี้จะแสดงเป็นหน้าว่างในไฟล์ export เพื่อให้คุณเพิ่มเนื้อหาเองใน Word</p>`;
}

// ======================================================
//  PROJECT SELECTION & BIBLIOGRAPHY LOADING
// ======================================================
function selectProject(projectId) {
    selectedProjectId = projectId;

    // Update UI
    document.querySelectorAll('.project-option-item').forEach(el => {
        el.classList.toggle('selected', el.id === 'proj-' + projectId);
    });

    // Show loading
    const listEl = document.getElementById('bib-panel-list');
    if (listEl) {
        listEl.innerHTML = `<div class="bib-loading"><span class="spinner"></span> กำลังโหลดบรรณานุกรม...</div>`;
    }

    // Fetch bibliographies
    fetch(`<?php echo SITE_URL; ?>/api/template/get-project-bibs.php?project_id=${encodeURIComponent(projectId)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadedBibliographies = data.bibliographies;
                if (listEl) {
                    listEl.innerHTML = renderBibPanelList();
                }
                renderAllPreviews();
            } else {
                if (listEl) {
                    listEl.innerHTML = `<p style="color:#EF4444; font-size:12px; text-align:center; padding:10px;">${escHtmlJs(data.message || 'เกิดข้อผิดพลาด')}</p>`;
                }
            }
        })
        .catch(() => {
            if (listEl) {
                listEl.innerHTML = `<p style="color:#EF4444; font-size:12px; text-align:center; padding:10px;">ไม่สามารถโหลดข้อมูลได้</p>`;
            }
        });
}

// ======================================================
//  PREVIEW RENDERING
// ======================================================
function renderAllPreviews() {
    const container = document.getElementById('preview-pages');
    container.innerHTML = '';

    template.sections.forEach((section, i) => {
        // Page break hint (not before first)
        if (i > 0) {
            const hint = document.createElement('div');
            hint.className = 'page-break-hint';
            hint.textContent = 'ตัดหน้า (Page Break)';
            container.appendChild(hint);
        }

        // A4 page
        const page = document.createElement('div');
        page.className = 'a4-paper';
        page.id = 'preview-' + section.id;
        page.innerHTML = renderSectionPreview(section);
        container.appendChild(page);
    });
}

function renderSectionPreview(section) {
    const font = formatSettings.font;
    const bodySize = formatSettings.bodySize;

    switch (section.type) {
        case 'cover': return renderCoverPreview();
        case 'inner_cover': return renderCoverPreview();
        case 'chapter': return renderChapterPreview(section);
        case 'toc': return renderTocPreview();
        case 'abstract': return renderAbstractPreview(section);
        case 'acknowledgment': return renderAcknowledgmentPreview();
        case 'bibliography': return renderBibliographyPreview();
        case 'appendix': return renderAppendixPreview();
        case 'preface': return renderPrefacePreview();
        case 'approval': return renderApprovalPreview();
        case 'biography': return renderBiographyPreview();
        default: return `<div style="text-align:center; color:#aaa; padding:40px;">${section.label}</div>`;
    }
}

function renderCoverPreview() {
    const title = coverData.title || '<span style="color:#ccc">[ชื่อรายงาน]</span>';
    const authors = coverData.authors || '<span style="color:#ccc">[ชื่อ-สกุล ผู้จัดทำ]</span>';
    const ids = coverData.studentIds ? coverData.studentIds.split('\n').join('\n') : '';
    const course = coverData.course || '<span style="color:#ccc">[รายวิชา]</span>';
    const courseCode = coverData.courseCode ? ` (${coverData.courseCode})` : '';
    const instructor = coverData.instructor || '<span style="color:#ccc">[อาจารย์ผู้สอน]</span>';
    const department = coverData.department || '<span style="color:#ccc">[ภาควิชา/คณะ]</span>';
    const institution = coverData.institution || '<span style="color:#ccc">[สถาบัน]</span>';

    const type = template.coverType;
    let html = '';

    // ===== Academic General: 3-zone layout (Title | Author+ID | Course info) =====
    if (type === 'academic') {
        const semText = coverData.semester === '1' ? '1' : coverData.semester === '2' ? '2' : 'ฤดูร้อน';
        // Zone 1: Title at top (normal flow) — bold, 20px, 1.5 line spacing
        html += `<div style="text-align:center; font-size:20px; font-weight:700; line-height:1.5;">${title}</div>`;
        // Zone 2: Author + ID — absolutely centered, bold, 20px, 1.5 line spacing
        html += `
            <div style="position:absolute; left:var(--page-left, 145px); right:var(--page-right, 96px); top:50%; transform:translateY(-50%); text-align:center; line-height:1.5; font-size:20px; font-weight:700;">
                <div>${authors.replace(/\n/g, '<br>')}</div>
                ${ids ? `<div style="margin-top:0.3em;">รหัส ${ids.replace(/\n/g, '<br>รหัส ')}</div>` : ''}
            </div>`;
        // Zone 3: Course info at bottom — bold, 20px, 1.5 line spacing
        html += `
            <div class="cover-bottom" style="font-size:20px; font-weight:700; line-height:1.5;">
                <div>${course}${courseCode}</div>
                <div>${department}</div>
                <div>${institution}</div>
                ${coverData.year ? `<div>ภาคการศึกษาที่ ${semText}/${coverData.year}</div>` : ''}
            </div>`;
        return html;
    }

    // ===== Other cover types =====

    // Institution at top
    html += `<div class="cover-institution">${institution}</div>`;
    if (coverData.department) {
        html += `<div class="cover-institution">${department}</div>`;
    }

    html += `<div class="cover-logo-placeholder"><i class="fas fa-university"></i></div>`;

    // Title
    html += `<div class="cover-title">${title}</div>`;

    // Type label
    if (type === 'internship') {
        html += `<div class="cover-subtitle">รายงานฝึกประสบการณ์วิชาชีพ</div>`;
    } else if (type === 'thesis') {
        const degree = coverData.degree || 'วิทยาศาสตรมหาบัณฑิต';
        const major = coverData.course || '<span style="color:#ccc">[สาขาวิชา]</span>';
        html += `<div class="cover-subtitle">วิทยานิพนธ์นี้เป็นส่วนหนึ่งของการศึกษาตามหลักสูตร<br>${degree} สาขา${major}</div>`;
    } else if (type === 'project') {
        const projType = coverData.projectType || 'รายงานโครงการ';
        html += `<div class="cover-subtitle">${projType}</div>`;
    } else if (type === 'research') {
        html += `<div class="cover-subtitle">รายงานการวิจัย</div>`;
    }

    // Bottom block
    let bottomContent = '';

    if (type === 'internship') {
        const company = coverData.company || '<span style="color:#ccc">[ชื่อสถานประกอบการ]</span>';
        const supervisor = coverData.supervisor || '<span style="color:#ccc">[ผู้ควบคุมการฝึกงาน]</span>';
        const period = coverData.internshipPeriod || '<span style="color:#ccc">[ช่วงเวลา]</span>';
        bottomContent = `
            <div style="margin-bottom:10px;">จัดทำโดย</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${ids ? `<div style="font-size:12px; color:#555;">${ids.replace(/\n/g, '<br>')}</div>` : ''}
            <div style="margin:8px 0 4px;">สถานประกอบการ: ${company}</div>
            ${coverData.supervisor ? `<div>ผู้ควบคุม: ${supervisor}</div>` : ''}
            ${coverData.internshipPeriod ? `<div>ช่วงเวลา: ${period}</div>` : ''}
            ${coverData.instructor ? `<div style="margin-top:4px;">อาจารย์นิเทศ: ${coverData.instructor}</div>` : ''}
            <div style="margin-top:8px;">${institution}</div>
            ${coverData.year ? `<div>ปีการศึกษา ${coverData.year}</div>` : ''}
        `;
    } else if (type === 'thesis') {
        const committee = coverData.committee || '';
        bottomContent = `
            <div style="margin-bottom:10px;">โดย</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${committee ? `<div style="margin:12px 0 4px; font-size:13px;">คณะกรรมการที่ปรึกษา</div><div style="font-size:13px;">${committee.replace(/\n/g, '<br>')}</div>` : ''}
            <div style="margin-top:12px;">${institution}</div>
            ${coverData.year ? `<div>พ.ศ. ${coverData.year}</div>` : ''}
        `;
    } else {
        const semText = coverData.semester === '1' ? '1' : coverData.semester === '2' ? '2' : 'ฤดูร้อน';
        bottomContent = `
            <div style="margin-bottom:8px;">จัดทำโดย</div>
            <div style="font-weight:600;">${authors.replace(/\n/g, '<br>')}</div>
            ${ids ? `<div style="font-size:12px; color:#555;">${ids.replace(/\n/g, '<br>')}</div>` : ''}
            <div style="margin:10px 0 4px;">เสนอ</div>
            <div>${instructor}</div>
            <div style="margin-top:8px;">${institution}</div>
            ${coverData.year ? `<div>ภาคเรียนที่ ${semText} ปีการศึกษา ${coverData.year}</div>` : ''}
        `;
    }

    html += `<div class="cover-bottom">${bottomContent}</div>`;

    return html;
}

function renderChapterPreview(section) {
    let html = `
        <div class="chapter-heading">บทที่ ${section.number}</div>
        <div class="chapter-heading" style="margin-bottom:24px;">${section.title}</div>`;

    section.subsections.forEach(sub => {
        html += `
        <div class="chapter-sub-heading">${sub}</div>
        <div class="chapter-body-placeholder">
            <p>กรอกเนื้อหาส่วนนี้ในไฟล์ Word ที่ export</p>
            <p>ขนาดตัวอักษร 16pt ระยะบรรทัด 1.5 เว้นย่อหน้า 1.5 cm</p>
        </div>`;
    });

    return html;
}

function renderTocPreview() {
    let html = `<div class="chapter-heading" style="margin-bottom:24px;">สารบัญ</div>`;

    function tocLine(label, page) {
        return `<div style="display:flex; margin-bottom:6px; font-size:14px;">
            <span style="flex:1;">${label}</span>
            <span style="color:#999; font-size:12px;">${page}</span>
        </div>`;
    }

    let pageNum = 1;
    template.sections.forEach(section => {
        if (section.type === 'cover') return;
        if (section.type === 'inner_cover') return;
        if (section.type === 'toc') return;
        pageNum++;
        html += tocLine(section.label, pageNum);
        if (section.type === 'chapter' && section.subsections) {
            section.subsections.forEach((sub, i) => {
                const subNum = `${section.number}.${i+1} ${sub}`;
                html += `<div style="display:flex; margin-bottom:4px; font-size:13px; padding-left:16px;">
                    <span style="flex:1; color:#555;">${subNum}</span>
                    <span style="color:#bbb; font-size:11px;">${pageNum}</span>
                </div>`;
            });
        }
    });

    return html;
}

function renderAbstractPreview(section) {
    const isEn = section.lang === 'en';
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">${isEn ? 'Abstract' : 'บทคัดย่อ'}</div>
        <div class="chapter-body-placeholder">
            <p>${isEn ? 'Write a concise summary of 150–300 words.' : 'สรุปสาระสำคัญของงานความยาว 150–300 คำ'}</p>
            <p>${isEn ? 'Include: objective, method, results, conclusion.' : 'ระบุวัตถุประสงค์ วิธีการ ผลการศึกษา และข้อสรุป'}</p>
        </div>
        <div style="margin-top:20px; font-size:13px;">
            <strong>${isEn ? 'Keywords:' : 'คำสำคัญ:'}</strong>
            <span style="color:#aaa;"> คำสำคัญ 1, คำสำคัญ 2, คำสำคัญ 3</span>
        </div>`;
}

function renderAcknowledgmentPreview() {
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">กิตติกรรมประกาศ</div>
        <div class="chapter-body-placeholder">
            <p>ขอขอบพระคุณ [ชื่ออาจารย์ที่ปรึกษา] ที่ให้คำปรึกษาและแนะนำ...</p>
            <p>ขอขอบคุณ... ที่ให้ความอนุเคราะห์...</p>
            <p>ท้ายที่สุด ขอขอบคุณครอบครัว...</p>
        </div>
        <div style="text-align:right; margin-top:30px; font-size:13px;">
            <div>${coverData.authors ? coverData.authors.split('\n')[0] : '(ผู้จัดทำ)'}</div>
            <div style="color:#aaa;">${coverData.year || 'ปีการศึกษา'}</div>
        </div>`;
}

function renderBibliographyPreview() {
    let html = `<div class="bib-section-title">บรรณานุกรม</div>`;

    if (loadedBibliographies.length === 0) {
        html += `<div class="bib-empty-state">
            <i class="fas fa-book-open" style="color:#DDD;"></i>
            <div style="font-size:14px; color:#BBB; margin-bottom:8px;">ยังไม่ได้เลือกโครงการ</div>
            <div style="font-size:12px; color:#999;">เลือกโครงการในแผงด้านขวาเพื่อแสดงบรรณานุกรม</div>
        </div>`;
    } else {
        loadedBibliographies.forEach(bib => {
            const text = bib.bibliography_text;
            html += `<div class="bib-preview-item">${text}</div>`;
        });
    }

    return html;
}

function renderAppendixPreview() {
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">ภาคผนวก</div>
        <div class="chapter-body-placeholder">
            <p>เพิ่มเนื้อหาภาคผนวกในไฟล์ Word ที่ export</p>
            <p>เช่น แบบสอบถาม รูปภาพประกอบ เอกสารอ้างอิง</p>
        </div>`;
}

function renderPrefacePanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-pen-nib"></i> แนวทางการเขียนคำนำ</div>
            <ul class="chapter-guide-list">
                <li>แนะนำที่มาและวัตถุประสงค์ของรายงาน</li>
                <li>ความเป็นมาโดยย่อ</li>
                <li>ขอบคุณผู้ที่ให้ความช่วยเหลือ</li>
                <li>ลงชื่อผู้จัดทำ พร้อมวันที่</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">ความยาวคำนำไม่ควรเกิน 1 หน้า A4</p>`;
}

function renderPrefacePreview() {
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">คำนำ</div>
        <div class="chapter-body-placeholder">
            <p>รายงานฉบับนี้จัดทำขึ้นเพื่อเป็นส่วนหนึ่งของการศึกษาในรายวิชา...</p>
            <p>ผู้จัดทำหวังเป็นอย่างยิ่งว่ารายงานฉบับนี้จะเป็นประโยชน์...</p>
        </div>
        <div style="text-align:right; margin-top:30px; font-size:13px;">
            <div>${coverData.authors ? coverData.authors.split('\n')[0] : '(ผู้จัดทำ)'}</div>
        </div>`;
}

function renderApprovalPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-file-signature"></i> หน้าอนุมัติ</div>
            <ul class="chapter-guide-list">
                <li>ชื่อนักศึกษาและรหัสนักศึกษา</li>
                <li>ลายเซ็นอาจารย์ที่ปรึกษา</li>
                <li>ลายเซ็นคณะกรรมการสอบ (ป.โท/สหกิจ)</li>
                <li>ลายเซ็นคณบดี / หัวหน้าสาขา</li>
                <li>วันที่อนุมัติ</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">หน้าอนุมัติจะสร้างโครงสร้างพื้นฐานใน Word เพื่อให้กรอกลายเซ็นเพิ่มเติมได้</p>`;
}

function renderApprovalPreview() {
    const authorLine = coverData.authors ? coverData.authors.split('\n')[0] : '[ชื่อ-สกุล นักศึกษา]';
    const institution = coverData.institution || '[สถาบัน]';
    return `
        <div class="chapter-heading" style="margin-bottom:20px;">หน้าอนุมัติ</div>
        <div style="text-align:center; font-size:13px; margin-bottom:24px;">
            <div style="font-weight:600;">${authorLine}</div>
            ${coverData.course ? `<div style="color:#555;">${coverData.course}</div>` : ''}
            <div style="color:#555;">${institution}</div>
        </div>
        <div style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:30px; font-size:13px;">
                <div style="width:45%; text-align:center;">
                    <div style="border-top:1px solid #ccc; padding-top:6px; color:#555;">อาจารย์ที่ปรึกษา</div>
                    <div style="color:#bbb; font-size:12px;">(ลายเซ็น / วันที่)</div>
                </div>
                <div style="width:45%; text-align:center;">
                    <div style="border-top:1px solid #ccc; padding-top:6px; color:#555;">หัวหน้าสาขา / คณบดี</div>
                    <div style="color:#bbb; font-size:12px;">(ลายเซ็น / วันที่)</div>
                </div>
            </div>
        </div>`;
}

function renderBiographyPanel(container) {
    container.innerHTML = `
        <div class="chapter-guide-card">
            <div class="chapter-guide-title"><i class="fas fa-user-circle"></i> ประวัติผู้เขียน</div>
            <ul class="chapter-guide-list">
                <li>ชื่อ-นามสกุล และวันเดือนปีเกิด</li>
                <li>ประวัติการศึกษา (ป.ตรี, ป.โท)</li>
                <li>ตำแหน่งงานปัจจุบัน (ถ้ามี)</li>
                <li>สถานที่ทำงาน/ที่อยู่ (optional)</li>
            </ul>
        </div>
        <p class="panel-hint" style="margin-top:10px;">ประวัติผู้เขียนอยู่หน้าสุดท้ายของวิทยานิพนธ์</p>`;
}

function renderBiographyPreview() {
    const authorLine = coverData.authors ? coverData.authors.split('\n')[0] : '[ชื่อ-สกุล]';
    return `
        <div class="chapter-heading" style="margin-bottom:24px;">ประวัติผู้เขียน</div>
        <div style="display:flex; gap:24px; margin-bottom:16px;">
            <div style="width:80px; height:100px; background:#F3F4F6; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-user" style="font-size:28px; color:#D1D5DB;"></i>
            </div>
            <div style="flex:1;">
                <div class="chapter-body-placeholder">
                    <p>ชื่อ-สกุล: ${authorLine}</p>
                    <p>ประวัติการศึกษา: ...</p>
                    <p>ตำแหน่งงานปัจจุบัน: ...</p>
                </div>
            </div>
        </div>`;
}

// ======================================================
//  UPDATE TRIGGERS
// ======================================================
function updateCoverPreview() {
    const coverPage = document.getElementById('preview-cover');
    if (coverPage) {
        coverPage.innerHTML = renderCoverPreview();
    }
    // ปกในใช้ข้อมูลเดียวกัน — อัปเดตพร้อมกันเสมอ
    const innerCoverPage = document.getElementById('preview-inner_cover');
    if (innerCoverPage) {
        innerCoverPage.innerHTML = renderCoverPreview();
    }
}

function updateFormatSettings() {
    formatSettings.font = document.getElementById('setting-font').value;
    formatSettings.bodySize = parseInt(document.getElementById('setting-body-size').value);
    formatSettings.margin = document.getElementById('setting-margin').value;

    // Apply to all preview pages
    const marginMap = {
        standard: {top: '145px', right: '96px', bottom: '96px', left: '145px'},
        wide: {top: '192px', right: '145px', bottom: '145px', left: '192px'},
        narrow: {top: '96px', right: '96px', bottom: '96px', left: '96px'}
    };
    const m = marginMap[formatSettings.margin];

    // CSS font stack: web-safe fallbacks so preview looks close to Word fonts
    const fontStackMap = {
        'Angsana New':    '"Angsana New", "Angsana UPC", Georgia, serif',
        'TH Sarabun New': '"TH Sarabun New", "Sarabun", sans-serif',
        'TH Niramit AS':  '"TH Niramit AS", "Niramit", sans-serif',
        'Times New Roman': '"Times New Roman", Times, serif'
    };
    const fontStack = fontStackMap[formatSettings.font] || fontStackMap['Angsana New'];

    document.querySelectorAll('.a4-paper').forEach(el => {
        el.style.paddingTop    = m.top;
        el.style.paddingRight  = m.right;
        el.style.paddingBottom = m.bottom;
        el.style.paddingLeft   = m.left;
        el.style.setProperty('--page-top', m.top);
        el.style.setProperty('--page-right', m.right);
        el.style.setProperty('--page-bottom', m.bottom);
        el.style.setProperty('--page-left', m.left);
        el.style.fontSize      = formatSettings.bodySize + 'px';
        el.style.fontFamily    = fontStack;
    });
}

// ======================================================
//  EXPORT
// ======================================================
function exportReport(format) {
    const btn = document.getElementById('btn-' + format);
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> กำลังสร้าง...';

    const payload = {
        template: templateId,
        format: format,
        coverData: coverData,
        formatSettings: formatSettings,
        projectId: selectedProjectId
    };

    if (format === 'docx') {
        // POST and download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo SITE_URL; ?>/api/template/export-report.php';
        form.target = '_blank';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'payload';
        input.value = JSON.stringify(payload);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }, 3000);
    } else {
        // PDF: open print preview
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo SITE_URL; ?>/api/template/export-report.php';
        form.target = '_blank';

        [['payload', JSON.stringify(payload)]].forEach(([name, val]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = name;
            inp.value = val;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }, 2000);
    }
}

// ======================================================
//  HELPERS
// ======================================================
function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escHtmlAttr(str) {
    if (!str) return '';
    return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
function escHtmlJs(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>
