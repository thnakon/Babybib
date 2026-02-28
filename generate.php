<?php

/**
 * Babybib - Generate Bibliography Page
 * ======================================
 * Main feature: Create APA 7<sup>th</sup> bibliographies
 */

$pageTitle = 'สร้างบรรณานุกรม';

require_once 'includes/header.php';

// Debug: Force reset session if requested
if (isset($_GET['reset_session'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: ' . SITE_URL . '/generate.php');
    exit;
}

if (isLoggedIn()) {
    require_once 'includes/navbar-user.php';
} else {
    require_once 'includes/navbar-guest.php';
}

// Get all resource types
$resourceTypes = getResourceTypes();
$categories = getResourceCategories();
$authorTypes = getAuthorTypes();

// Edit mode
$isEdit = false;
$editData = null;
if (isset($_GET['edit']) && isLoggedIn()) {
    $editId = intval($_GET['edit']);
    $userId = getCurrentUserId();
    $db = getDB();

    $stmt = $db->prepare("
        SELECT b.*, rt.code as resource_code 
        FROM bibliographies b 
        JOIN resource_types rt ON b.resource_type_id = rt.id 
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$editId, $userId]);
    $editData = $stmt->fetch();

    if ($editData) {
        $isEdit = true;
        $editData['data'] = json_decode($editData['data'], true);
    }
}
?>
<script>
    const EDIT_DATA = <?php echo $isEdit ? json_encode($editData) : 'null'; ?>;
    const IS_EDIT_MODE = <?php echo $isEdit ? 'true' : 'false'; ?>;
</script>

<style>
    /* Layout Wrapper - Split View */
    .generate-layout {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 40px;
        margin-top: var(--space-6);
        align-items: start;
    }

    .form-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .preview-column {
        position: sticky;
        top: 80px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .generate-layout {
            grid-template-columns: 1fr;
        }

        .preview-column {
            position: static;
            order: -1;
            margin-bottom: 30px;
        }
    }

    /* Preview Container Box - Enhanced */
    .preview-container-box {
        background: linear-gradient(145deg, #FFFFFF 0%, #F8F5FF 100%);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: 0 8px 40px rgba(139, 92, 246, 0.15), 0 0 0 2px rgba(139, 92, 246, 0.1);
        border: 2px solid var(--primary);
        position: relative;
        overflow: hidden;
    }

    .preview-container-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .preview-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .preview-box-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-box-title i {
        color: var(--primary);
    }

    /* Guidance Status Badge */
    .guidance-status {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .guidance-status.guidance-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #B45309;
    }

    .guidance-status.guidance-success {
        background: rgba(34, 197, 94, 0.15);
        color: #15803D;
    }

    /* Format Selector */
    .format-selector {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .format-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* Result Box New Layout */
    .result-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        transition: border-color 0.3s, background-color 0.3s;
    }

    .result-box.updated-flash {
        border-color: var(--success) !important;
        background-color: #F0FDF4 !important;
    }

    .result-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .result-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .btn-copy-small {
        width: 32px;
        height: 32px;
        border: none;
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-copy-small:hover {
        background: var(--primary);
        color: white;
    }

    .result-content {
        font-family: 'Times New Roman', serif;
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-primary);
        min-height: 40px;
        word-wrap: break-word;
    }

    .result-content.hanging-indent {
        text-indent: -24px;
        margin-left: 24px;
    }

    .result-placeholder {
        color: #333333;
        font-style: italic;
        font-family: var(--font-thai);
        font-size: 0.9rem;
    }

    .result-footer {
        margin-top: 10px;
        display: flex;
        justify-content: flex-start;
    }

    .result-badge {
        display: inline-block;
        padding: 4px 10px;
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        font-size: 0.7rem;
        font-weight: 500;
        border-radius: 20px;
    }

    /* Editable Preview Styles */
    .result-content[contenteditable="true"] {
        cursor: text;
        outline: none;
        transition: all 0.2s ease;
    }

    .result-content[contenteditable="true"]:focus {
        background-color: white;
        box-shadow: inset 0 0 0 2px var(--primary-light);
        border-radius: 8px;
    }

    .manual-edit-badge {
        display: none;
        padding: 2px 8px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 10px;
        font-weight: 600;
        border-radius: 4px;
        margin-left: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .manual-edit-badge.active {
        display: inline-block;
    }

    .btn-reset-preview {
        display: none;
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 11px;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-reset-preview:hover {
        background: #F1F5F9;
        color: var(--primary);
    }

    .btn-reset-preview.active {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Field Help Button */
    .field-label-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .field-label-wrapper .form-label {
        margin-bottom: 0 !important;
    }

    .btn-field-help {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        border: none;
        border-radius: 50%;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
        line-height: 1;
    }

    .btn-field-help:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
    }

    /* Signup Promo Box */
    .signup-promo-box {
        background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
        border-radius: var(--radius-lg);
        padding: 24px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        border: 1px solid rgba(139, 92, 246, 0.2);
    }

    .promo-icon {
        width: 50px;
        height: 50px;
        background: white;
        color: #F59E0B;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        font-size: 24px;
    }

    .promo-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .promo-text {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .promo-features {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
        text-align: left;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 12px;
        padding: 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .promo-features li {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .promo-features li i {
        color: var(--success);
    }

    .btn-signup-promo {
        background: var(--primary);
        color: white;
        padding: 10px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        font-size: 0.9rem;
        width: 100%;
        justify-content: center;
    }

    .btn-signup-promo:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        color: white;
    }

    /* Form Card Styling */
    .form-card-new {
        background: white;
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        border: 1px solid transparent;
        transition: all 0.2s ease;
        margin-bottom: 20px;
    }

    .form-card-new:hover {
        border-color: var(--primary-light);
        box-shadow: 0 10px 25px rgba(139, 92, 246, 0.12);
    }

    .section-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding-bottom: 15px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--primary-light);
    }

    .section-title-new {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .section-title-new i {
        color: var(--primary);
    }

    .author-count-badge {
        font-size: 0.85rem;
        font-weight: 400;
        color: var(--text-secondary);
        margin-left: 8px;
    }

    /* Author Row Improved */
    .author-row-new {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        position: relative;
        transition: all 0.3s;
    }

    .author-row-new:hover {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .author-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
    }

    .author-number-badge {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        background: rgba(139, 92, 246, 0.1);
        padding: 4px 12px;
        border-radius: 20px;
    }

    .btn-remove-author-new {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: white;
        color: var(--danger);
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }

    .btn-remove-author-new:hover {
        background: var(--danger);
        color: white;
        transform: scale(1.1);
        border-color: var(--danger);
    }

    .name-row {
        display: flex;
        gap: 12px;
        margin-top: 12px;
    }

    .name-field {
        flex: 1;
    }

    .name-field .form-label {
        font-size: 0.8rem;
        margin-bottom: 4px;
    }

    .name-field.hidden,
    .name-row.hidden {
        display: none;
    }

    /* ─── Smart Search v2 Styling ─── */
    .smart-search-btn {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--primary-gradient);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        flex-shrink: 0;
    }

    .smart-search-btn:hover {
        transform: scale(1.1) rotate(15deg);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
    }

    .smart-search-btn.loading i {
        animation: spin 1s infinite linear;
    }

    .type-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-right: 6px;
        display: none;
        flex-shrink: 0;
    }

    .type-badge.active {
        display: inline-block;
    }

    .badge-url { background: #E0F2FE; color: #0369A1; }
    .badge-isbn { background: #F0FDF4; color: #15803D; }
    .badge-doi { background: #FEF2F2; color: #B91C1C; }
    .badge-keyword { background: #F5F3FF; color: #6D28D9; }

    /* Search History Chips */
    .search-history-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
        padding: 0 5px;
    }

    .history-chip {
        background: rgba(139, 92, 246, 0.05);
        border: 1px solid rgba(139, 92, 246, 0.15);
        color: var(--primary);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .history-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 150px;
    }

    .history-chip:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .history-chip i {
        font-size: 0.65rem;
        opacity: 0.7;
    }

    /* Results Dropdown */
    .smart-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        margin-top: 10px;
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #E2E8F0;
        display: none;
        animation: slideDown 0.3s ease-out;
    }

    .smart-results-dropdown.active {
        display: block;
    }

    .smart-result-item {
        padding: 14px 16px;
        display: flex;
        gap: 14px;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 1px solid #F1F5F9;
        align-items: center;
    }

    .smart-result-item:last-child {
        border-bottom: none;
    }

    .smart-result-item:hover {
        background: #F8FAFC;
    }

    .smart-result-img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #F8FAFC;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--primary);
        flex-shrink: 0;
        overflow: hidden;
    }

    .smart-result-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .smart-result-info {
        flex: 1;
        min-width: 0;
    }

    .smart-result-title {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.88rem;
        margin-bottom: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .smart-result-meta {
        font-size: 0.75rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .smart-result-meta .confidence {
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 600;
    }

    .confidence.high { background: #D1FAE5; color: #059669; }
    .confidence.medium { background: #FEF3C7; color: #D97706; }
    .confidence.low { background: #FEE2E2; color: #DC2626; }

    .btn-add-result {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--success-light);
        color: var(--success);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .btn-add-result:hover {
        background: var(--success);
        color: white;
        transform: scale(1.1);
    }

    /* Magic Fill Animation */
    @keyframes magicFill {
        0% { background: rgba(139, 92, 246, 0.2); transform: scale(1.02); }
        50% { background: rgba(139, 92, 246, 0.1); }
        100% { background: transparent; transform: scale(1); }
    }

    /* Skeleton Pulse Animation */
    @keyframes skeletonPulse {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }

    .field-magic-fill {
        animation: magicFill 1s ease-out;
        border-color: var(--primary) !important;
        box-shadow: 0 0 15px rgba(139, 92, 246, 0.2) !important;
        z-index: 10;
        position: relative;
    }

    .isbn-no-results {
        padding: 20px;
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    /* Add Author Button Small */
    .btn-add-author-small {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(139, 92, 246, 0.1);
        color: var(--primary);
        border: 1px solid var(--primary-light);
        border-radius: 20px;
        font-family: var(--font-thai);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-add-author-small:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Action Bar */
    .action-bar {
        display: flex;
        gap: 15px;
        margin-top: 10px;
        justify-content: center;
    }

    .btn-generate {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-thai);
        font-weight: 600;
    }

    .btn-generate:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }

    .btn-clear-form {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid #E2E8F0;
        padding: 12px 25px;
        border-radius: 50px;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        font-family: var(--font-thai);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-clear-form:hover {
        background: #F1F5F9;
        color: var(--text-primary);
    }

    /* Smart Validation Styles */
    .field-warning {
        border-color: #F59E0B !important;
        background: #FFFBEB !important;
    }

    .field-warning-label {
        color: #D97706 !important;
    }

    .validation-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #FEF3C7;
        color: #D97706;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 12px;
        margin-left: 8px;
    }

    .validation-badge.error {
        background: #FEE2E2;
        color: #DC2626;
    }

    .validation-badge.success {
        background: #D1FAE5;
        color: #059669;
    }

    @keyframes pulse-warning {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
        }
    }

    .field-warning-pulse {
        animation: pulse-warning 1.5s ease-in-out;
    }

    .validation-summary {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        border: 1px solid #F59E0B;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 15px;
        display: none;
    }

    .validation-summary.active {
        display: block;
    }

    .validation-summary-title {
        font-weight: 600;
        color: #92400E;
        font-size: 0.85rem;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .validation-summary-list {
        font-size: 0.8rem;
        color: #B45309;
        margin: 0;
        padding-left: 20px;
    }

    /* Page Header New */
    .page-header-new {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        padding: 20px 0;
        margin-bottom: 20px;
    }

    .header-content-new {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-btn-new {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.2s ease;
        background: transparent;
        border: none;
        cursor: pointer;
    }

    .back-btn-new:hover {
        background: var(--primary-light);
        color: var(--primary);
    }

    .header-icon-box {
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(139, 92, 246, 0.25) 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 24px;
    }

    .header-text-new h1 {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 1.5rem;
        margin: 0;
    }

    .header-text-new p {
        color: var(--text-secondary);
        font-weight: 400;
        font-size: 0.85rem;
        margin: 2px 0 0 0;
    }

    /* Quick Type Switcher */
    .type-switcher-btn {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #F5F3FF, #EDE9FE);
        border: 1px solid rgba(139, 92, 246, 0.2);
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--primary);
        transition: all 0.25s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .type-switcher-btn:hover {
        background: linear-gradient(135deg, #EDE9FE, #DDD6FE);
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
    }
    .type-switcher-btn i { font-size: 0.9rem; }

    /* Type Switcher Modal */
    .type-switcher-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(4px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.2s ease;
    }
    .type-switcher-overlay.active { display: flex; }

    .type-switcher-modal {
        background: white;
        border-radius: 20px;
        width: 95%;
        max-width: 680px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: slideUp 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .type-switcher-header {
        padding: 20px 24px 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .type-switcher-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .type-switcher-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .type-switcher-close {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: none;
        background: #F1F5F9;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        transition: all 0.2s;
    }
    .type-switcher-close:hover { background: #FEE2E2; color: #EF4444; }

    .type-switcher-search {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        font-size: 0.85rem;
        outline: none;
        transition: border 0.2s;
        box-sizing: border-box;
    }
    .type-switcher-search:focus { border-color: var(--primary); }

    .type-switcher-body {
        padding: 12px 24px 20px;
        overflow-y: auto;
        flex: 1;
    }
    .type-switcher-category {
        margin-bottom: 12px;
    }
    .type-switcher-category-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 0;
    }
    .type-switcher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 6px;
    }
    .type-switch-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1.5px solid transparent;
    }
    .type-switch-item:hover { background: #F5F3FF; border-color: rgba(139, 92, 246, 0.2); }
    .type-switch-item.current-type {
        background: linear-gradient(135deg, #EDE9FE, #F5F3FF);
        border-color: var(--primary);
    }
    .type-switch-item i {
        width: 28px; height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 8px;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .type-switch-item span {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .type-switch-item.hidden-by-search { display: none; }

    /* Form Grid */
    .form-grid-new {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 16px;
    }

    .col-3 {
        grid-column: span 3;
    }

    .col-4 {
        grid-column: span 4;
    }

    .col-6 {
        grid-column: span 6;
    }

    .col-8 {
        grid-column: span 8;
    }

    .col-12 {
        grid-column: span 12;
    }

    @media (max-width: 768px) {

        .col-3,
        .col-4,
        .col-6,
        .col-8 {
            grid-column: span 12;
        }

        .promo-features {
            grid-template-columns: 1fr;
        }

        .name-row {
            flex-direction: column;
        }
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-4);
        margin-bottom: var(--space-6);
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

    .generate-step-header {
        text-align: center;
        margin-bottom: var(--space-8);
    }

    .generate-step-header h2 {
        font-size: var(--text-2xl);
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .generate-step-header p {
        color: var(--text-secondary);
        font-size: var(--text-base);
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-lg);
        font-weight: 600;
        margin-bottom: var(--space-4);
        color: var(--primary);
    }

    .resource-toolbar {
        display: flex;
        gap: var(--space-3);
        max-width: 700px;
        margin: 0 auto var(--space-8);
        align-items: center;
        justify-content: center;
    }

    .search-bar {
        position: relative;
        flex-grow: 1;
        margin: 0;
    }

    .search-bar i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
        z-index: 1;
    }

    .search-bar .form-input {
        padding-left: 45px;
        border-radius: var(--radius-full) !important;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        height: 40px;
        transition: all var(--transition);
        width: 100%;
        font-size: 13px;
    }

    .search-bar .form-input:focus {
        border-color: var(--primary);
        box-shadow: var(--shadow-primary);
    }

    .category-select {
        padding: 0 35px 0 var(--space-4);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-full);
        font-size: 13px;
        color: var(--text-primary);
        cursor: pointer;
        background-color: var(--white);
        box-shadow: var(--shadow-sm);
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238B5CF6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        height: 40px;
        min-width: 140px;
        transition: all var(--transition);
        font-weight: 500;
    }

    .category-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: var(--shadow-primary);
    }

    .no-results-state {
        text-align: center;
        padding: var(--space-12);
        color: var(--text-tertiary);
        grid-column: 1 / -1;
        display: none;
    }

    .no-results-state i {
        font-size: 3rem;
        margin-bottom: var(--space-4);
        opacity: 0.3;
    }

    .bibliography-preview {
        border: 2px solid var(--primary);
        box-shadow: var(--shadow-lg), 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .guest-signup-box {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05));
        border: 1px dashed var(--primary);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-top: var(--space-4);
        text-align: center;
    }

    .guest-signup-box i {
        font-size: 24px;
        color: var(--primary);
        margin-bottom: var(--space-2);
    }
</style>

<!-- Loading Overlay - Book Writing Animation -->
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-card">
        <div class="book-animation">
            <div class="book-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="pen-wrapper">
                <div class="pen-icon">
                    <i class="fas fa-pen-nib"></i>
                </div>
            </div>
            <div class="writing-line"></div>
        </div>
        <div class="loading-text"><?php echo $currentLang === 'th' ? 'กำลังบันทึกข้อมูล...' : 'Saving Bibliography...'; ?></div>
        <div class="loading-subtext"><?php echo $currentLang === 'th' ? 'กรุณารอสักครู่ ระบบกำลังจดบันทึกข้อมูลของคุณ' : 'Successfully recording your data into the library'; ?></div>
        <div class="loading-progress">
            <div id="loading-progress-bar" class="loading-progress-bar"></div>
        </div>
    </div>
</div>


<style>
    /* Book Writing Loading Animation */
    .book-animation {
        width: 80px;
        height: 70px;
        margin: 0 auto 24px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .book-icon {
        font-size: 50px;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 4px 12px rgba(139, 92, 246, 0.2));
        animation: bookPulse 2s ease-in-out infinite;
    }

    .pen-wrapper {
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 5;
        animation: penMove 3s ease-in-out infinite;
        pointer-events: none;
    }

    .pen-icon {
        position: absolute;
        top: 10px;
        left: 10px;
        font-size: 18px;
        color: var(--primary-dark);
        transform: rotate(-10deg);
        filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.1));
    }

    .writing-line {
        position: absolute;
        bottom: 25px;
        left: 25px;
        height: 2px;
        background: var(--primary-gradient);
        border-radius: 4px;
        width: 0;
        z-index: 2;
        animation: lineDraw 3s ease-in-out infinite;
        opacity: 0.6;
    }

    @keyframes bookPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.9;
        }

        50% {
            transform: scale(1.05);
            opacity: 1;
        }
    }

    @keyframes penMove {
        0% {
            transform: translate(0, 0) rotate(0deg);
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        40% {
            transform: translate(40px, 0px) rotate(5deg);
        }

        50% {
            transform: translate(5px, 15px) rotate(-5deg);
        }

        80% {
            transform: translate(50px, 15px) rotate(5deg);
        }

        90% {
            opacity: 1;
        }

        100% {
            transform: translate(60px, 0px) rotate(0deg);
            opacity: 0;
        }
    }

    @keyframes lineDraw {
        0% {
            width: 0;
            opacity: 0;
        }

        10% {
            opacity: 0.6;
        }

        40% {
            width: 30px;
        }

        50% {
            width: 10px;
            transform: translateY(12px);
        }

        80% {
            width: 40px;
            transform: translateY(12px);
        }

        90% {
            opacity: 0.6;
        }

        100% {
            width: 0;
            opacity: 0;
        }
    }

    .loading-text {
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
        letter-spacing: -0.01em;
    }

    .loading-subtext {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 400;
        max-width: 240px;
        margin: 0 auto;
        line-height: 1.4;
        opacity: 0.7;
    }

    .loading-progress {
        width: 100%;
        height: 4px;
        background: rgba(139, 92, 246, 0.05);
        margin-top: 24px;
        border-radius: 10px;
        overflow: hidden;
    }

    .loading-progress-bar {
        height: 100%;
        background: var(--primary-gradient);
        width: 0%;
        transition: width 1s ease-in-out;
        box-shadow: 0 0 10px rgba(139, 92, 246, 0.3);
    }
</style>



</style>

<!-- Hero Section -->
<section class="hero" style="padding: 240px 0 var(--space-24); border-bottom-left-radius: 60px; border-bottom-right-radius: 60px; min-height: auto; align-items: flex-start;">
    <!-- Floating Decorative Elements -->
    <div class="hero-decorations">
        <i class="fas fa-book decor-1"></i>
        <i class="fas fa-newspaper decor-2"></i>
        <i class="fas fa-graduation-cap decor-3"></i>
        <i class="fas fa-bookmark decor-4"></i>
        <i class="fas fa-quote-right decor-5"></i>
    </div>
    <div class="container">
        <div class="hero-content" style="margin-top: 50px;">
            <!-- Step Indicator (Top) -->
            <div class="step-indicator" style="margin-bottom: 25px;">
                <div class="step active" id="step-1" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.2);">
                    <span class="step-number" style="background: rgba(255,255,255,0.2);">1</span>
                    <?php echo __('select_resource'); ?>
                </div>
                <div class="step" id="step-2" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.1);">
                    <span class="step-number" style="background: rgba(255,255,255,0.1);">2</span>
                    <?php echo __('fill_info'); ?>
                </div>
                <div class="step" id="step-3" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.1);">
                    <span class="step-number" style="background: rgba(255,255,255,0.1);">3</span>
                    <?php echo __('save'); ?>
                </div>
            </div>

            <h1 class="hero-title" style="font-size: 32px; color: white; margin-bottom: 12px; font-weight: 800;">
                <?php echo $currentLang === 'th' ? 'เครื่องมือสร้างบรรณานุกรม' : 'Bibliography Generator'; ?>
            </h1>

            <div class="generate-step-header" id="selection-header">
                <p style="color: rgba(255,255,255,0.85); font-weight: 400; font-size: 16px;">
                    <?php echo $currentLang === 'th'
                        ? 'เลือกประเภททรัพยากรที่ต้องการอ้างอิง ระบบจะจัดรูปแบบ APA 7<sup>th</sup> ให้อัตโนมัติ'
                        : 'Choose the resource type you want to cite, the system will automatically format it in APA 7<sup>th</sup>'; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<main class="container" style="margin-top: -25px; position: relative; z-index: 100; padding: 0 0 var(--space-12);">
    <!-- Step 1: Select Resource Type -->
    <div id="resource-selection" class="slide-up">
        <!-- Smart Search v2 Toolbar -->
        <div class="resource-toolbar-container" style="margin-bottom: 25px; position: relative;">
            <div class="resource-toolbar" style="box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: white; padding: 6px; border-radius: var(--radius-full); display: flex; align-items: center;">
                <div class="search-bar" style="flex: 1; display: flex; align-items: center; padding-left: 15px; position: relative; gap: 10px;">
                    <span id="main-search-type-badge" class="type-badge"></span>
                    <i class="fas fa-magic" style="color: var(--primary); flex-shrink: 0; position: static; transform: none; margin: 0;"></i>
                    <input type="text" id="resource-search" class="form-input" style="border: none; box-shadow: none; flex: 1; padding: 12px 0; background: transparent; margin: 0;"
                        placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาชื่อหนังสือ, ISBN, DOI หรือวาง URL...' : 'Search by title, ISBN, DOI or paste a URL...'; ?>"
                        autocomplete="off">
                </div>

                <select class="category-select" id="category-filter" style="border: none; box-shadow: none; border-left: 1px solid var(--border-light); border-radius: 0; padding: 0 15px; background: transparent;">
                    <option value="all"><?php echo $currentLang === 'th' ? 'ทุกหมวดหมู่' : 'All Categories'; ?></option>
                    <?php foreach ($categories as $key => $cat): ?>
                        <option value="<?php echo $key; ?>">
                            <?php echo $currentLang === 'th' ? $cat['name_th'] : $cat['name_en']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" id="main-search-btn" class="smart-search-btn" style="margin-left: 5px;" onclick="performSmartSearch()">
                    <i class="fas fa-search" style="font-size: 0.9rem;"></i>
                </button>
            </div>
            <div id="main-smart-results" class="smart-results-dropdown" style="margin-top: 10px;"></div>

            <!-- Search History -->
            <div id="search-history" class="search-history-container"></div>
        </div>

        <div style="padding: 0 var(--space-4);">
            <!-- Resource Grid Grouped by Category -->
            <div class="resource-grid" id="resource-grid">
                <?php
                // Group resource types by category
                $groupedResourceTypes = [];
                foreach ($resourceTypes as $type) {
                    $cat = $type['category'];
                    if (!isset($groupedResourceTypes[$cat])) {
                        $groupedResourceTypes[$cat] = [];
                    }
                    $groupedResourceTypes[$cat][] = $type;
                }

                // Known brand icons for correct FA prefix
                $brandIcons = ['fa-youtube', 'fa-facebook', 'fa-line', 'fa-instagram', 'fa-tiktok', 'fa-twitter'];

                // Categories loop
                foreach ($categories as $catKey => $catInfo):
                    if (!isset($groupedResourceTypes[$catKey])) continue;
                ?>
                    <div class="category-group cat-<?php echo $catKey; ?>" data-category-group="<?php echo $catKey; ?>">
                        <div class="category-group-title">
                            <i class="fas <?php echo $catInfo['icon']; ?>"></i>
                            <?php echo $currentLang === 'th' ? $catInfo['name_th'] : $catInfo['name_en']; ?>
                        </div>
                        <div class="category-items">
                            <?php foreach ($groupedResourceTypes[$catKey] as $type):
                                $iconPrefix = in_array($type['icon'], $brandIcons) ? 'fab' : 'fas';
                            ?>
                                <div class="resource-card cat-<?php echo $catKey; ?>"
                                    data-id="<?php echo $type['id']; ?>"
                                    data-code="<?php echo $type['code']; ?>"
                                    data-category="<?php echo $type['category']; ?>"
                                    data-fields-config='<?php echo $type['fields_config']; ?>'
                                    data-name-th="<?php echo htmlspecialchars($type['name_th']); ?>"
                                    data-name-en="<?php echo htmlspecialchars($type['name_en']); ?>"
                                    onclick="selectResource(this)">
                                    <div class="resource-icon">
                                        <i class="<?php echo $iconPrefix; ?> <?php echo $type['icon']; ?>"></i>
                                    </div>
                                    <div class="resource-info">
                                        <h4><?php echo $currentLang === 'th' ? $type['name_th'] : $type['name_en']; ?></h4>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Empty State for Search -->
                <div id="no-results" class="no-results-state">
                    <i class="fas fa-search"></i>
                    <h4><?php echo $currentLang === 'th' ? 'ไม่พบประเภททรัพยากร' : 'No resource types found'; ?></h4>
                    <p><?php echo $currentLang === 'th' ? 'ลองค้นหาด้วยคำอื่นหรือเลือกหมวดหมู่ใหม่' : 'Try searching with another keyword or change category'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Fill Form (Hidden initially) -->
    <div id="form-section" class="hidden">
        <!-- Step Indicator for Form Section -->
        <div class="step-indicator" id="form-step-indicator" style="margin-top: 50px; margin-bottom: 25px; justify-content: center;">
            <div class="step done" style="background: var(--success-light); color: var(--success); font-size: 13px; padding: 6px 12px;">
                <span class="step-number" style="background: var(--success); width: 20px; height: 20px;"><i class="fas fa-check" style="font-size: 9px; color: white;"></i></span>
                <?php echo __('select_resource'); ?>
            </div>
            <div class="step active" style="background: var(--primary-gradient); color: white; font-size: 13px; padding: 6px 12px;">
                <span class="step-number" style="background: rgba(255,255,255,0.2); width: 20px; height: 20px; font-size: 11px;">2</span>
                <?php echo __('fill_info'); ?>
            </div>
            <div class="step" style="background: var(--gray-100); color: var(--text-secondary); font-size: 13px; padding: 6px 12px;">
                <span class="step-number" style="background: var(--gray-200); width: 20px; height: 20px; font-size: 11px;">3</span>
                <?php echo __('save'); ?>
            </div>
        </div>

        <!-- New Page Header -->
        <div class="page-header-new">
            <div class="header-content-new">
                <button type="button" class="back-btn-new" onclick="backToSelection()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="header-icon-box" id="selected-resource-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="header-text-new">
                    <h1 id="selected-resource-title"><?php echo __('fill_info'); ?></h1>
                    <p id="selected-resource-subtitle">
                        <i class="fas fa-check-circle" style="color: var(--success); margin-right: 4px;"></i>
                        APA7th Edition
                    </p>
                </div>
                <button type="button" class="type-switcher-btn" id="open-type-switcher" onclick="openTypeSwitcher()">
                    <i class="fas fa-exchange-alt"></i>
                    <span><?php echo $currentLang === 'th' ? 'เปลี่ยนประเภท' : 'Change Type'; ?></span>
                </button>
            </div>

            <!-- Type Switcher Modal -->
            <div class="type-switcher-overlay" id="type-switcher-overlay" onclick="if(event.target===this)closeTypeSwitcher()">
                <div class="type-switcher-modal">
                    <div class="type-switcher-header">
                        <div class="type-switcher-header-row">
                            <h3><i class="fas fa-exchange-alt" style="margin-right:8px; color:var(--primary);"></i><?php echo $currentLang === 'th' ? 'เปลี่ยนประเภททรัพยากร' : 'Change Resource Type'; ?></h3>
                            <button class="type-switcher-close" onclick="closeTypeSwitcher()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="text" class="type-switcher-search" id="type-switcher-search" 
                               placeholder="<?php echo $currentLang === 'th' ? 'ค้นหาประเภท...' : 'Search type...'; ?>"
                               oninput="filterTypeSwitcher(this.value)">
                    </div>
                    <div class="type-switcher-body" id="type-switcher-body">
                        <?php foreach ($categories as $catKey => $catInfo):
                            if (!isset($groupedResourceTypes[$catKey])) continue;
                        ?>
                        <div class="type-switcher-category" data-cat="<?php echo $catKey; ?>">
                            <div class="type-switcher-category-label">
                                <i class="fas <?php echo $catInfo['icon']; ?>" style="margin-right:4px;"></i>
                                <?php echo $currentLang === 'th' ? $catInfo['name_th'] : $catInfo['name_en']; ?>
                            </div>
                            <div class="type-switcher-grid">
                                <?php foreach ($groupedResourceTypes[$catKey] as $type):
                                    $iconPrefix = in_array($type['icon'], $brandIcons) ? 'fab' : 'fas';
                                    $typeName = $currentLang === 'th' ? $type['name_th'] : $type['name_en'];
                                ?>
                                <div class="type-switch-item" 
                                     data-code="<?php echo $type['code']; ?>"
                                     data-name="<?php echo htmlspecialchars($typeName); ?>"
                                     data-search="<?php echo htmlspecialchars(strtolower($type['name_th'] . ' ' . $type['name_en'])); ?>"
                                     onclick="switchToType('<?php echo $type['code']; ?>')">
                                    <i class="<?php echo $iconPrefix; ?> <?php echo $type['icon']; ?>"></i>
                                    <span><?php echo $typeName; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="generate-layout">
            <!-- Left: Form Column -->
            <div class="form-column">
                <form id="bibliography-form">
                    <input type="hidden" id="bib-id" name="bib_id" value="<?php echo $isEdit ? $editData['id'] : ''; ?>">
                    <input type="hidden" id="resource-type-id" name="resource_type_id">
                    <input type="hidden" id="bib-language" name="language" value="th">

                    <!-- Card 1: Author Section -->
                    <div class="form-card-new">
                        <div class="section-title-row">
                            <h4 class="section-title-new">
                                <i class="fas fa-users"></i>
                                <?php echo __('authors'); ?>
                                <span class="author-count-badge">(<span id="author-count">1</span> <?php echo $currentLang === 'th' ? 'คน' : 'person(s)'; ?>)</span>
                            </h4>
                            <button type="button" class="btn-add-author-small" onclick="addAuthor()">
                                <i class="fas fa-plus"></i>
                                <?php echo $currentLang === 'th' ? 'เพิ่มผู้แต่ง' : 'Add Author'; ?>
                            </button>
                        </div>
                        <div class="author-list" id="author-list">
                            <!-- Authors will be dynamically added -->
                        </div>
                    </div>

                    <!-- Card 2: Resource Info with ISBN Search -->
                    <div class="form-card-new">
                        <div class="section-title-row" style="border-bottom: none; padding-bottom: 0; margin-bottom: 16px;">
                            <h4 class="section-title-new">
                                <i class="fas fa-book-open" style="color: #10b981;"></i>
                                <span id="resource-info-title"><?php echo $currentLang === 'th' ? 'ข้อมูลหนังสือ' : 'Book Information'; ?></span>
                            </h4>
                        </div>


                        <!-- Smart URL Scraper (Visible for Website/Online types) -->
                        <div id="url-scraper-box" style="display: none; margin-bottom: 25px; padding: 20px; background: rgba(139, 92, 246, 0.04); border: 1.5px dashed var(--primary-light); border-radius: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(139,92,246,0.1);">
                                        <i class="fas fa-magic" style="color: var(--primary); font-size: 0.9rem;"></i>
                                    </div>
                                    <strong style="font-size: 0.95rem; color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'ดึงข้อมูลอัตโนมัติจากลิ้งก์' : 'Smart Import from URL'; ?></strong>
                                </div>
                                <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 10px; padding: 2px 8px; border-radius: 4px;">BETA</span>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <input type="url" id="scraper-url-input" class="form-input" style="flex: 1; height: 42px; font-size: 0.9rem; border-color: rgba(139,92,246,0.2);" placeholder="https://thestandard.co/article-title">
                                <button type="button" id="btn-run-scraper" class="btn btn-primary" style="padding: 0 20px; height: 42px; font-size: 0.9rem; white-space: nowrap; border-radius: 12px;">
                                    <i class="fas fa-bolt"></i> <?php echo $currentLang === 'th' ? 'ดึงข้อมูล' : 'Fetch'; ?>
                                </button>
                            </div>
                            <div id="scraper-status" style="margin-top: 10px; font-size: 0.8rem; display: none; padding-left: 5px;"></div>
                        </div>

                        <!-- Dynamic Fields Container -->
                        <div id="dynamic-fields">
                            <!-- Fields based on resource type -->
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-bar">
                        <button type="button" class="btn-clear-form" onclick="clearForm()">
                            <i class="fas fa-rotate-left"></i>
                            <?php echo __('clear_form'); ?>
                        </button>
                        <button type="submit" class="btn-generate">
                            <i class="fas fa-save"></i>
                            <?php echo $isEdit ? ($currentLang === 'th' ? 'อัปเดตบรรณานุกรม' : 'Update Bibliography') : __('save_bibliography'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Preview Column (Sticky) -->
            <div class="preview-column">
                <!-- Format/Language Selector Card (Separate) -->
                <div class="form-card-new" style="margin-bottom: 16px; padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-language" style="color: var(--primary); font-size: 18px;"></i>
                            <span class="format-label" style="font-weight: 600; color: var(--text-primary);"><?php echo $currentLang === 'th' ? 'รูปแบบภาษา' : 'Language Format'; ?></span>
                        </div>
                        <div class="lang-toggle" style="width: fit-content;">
                            <button type="button" class="lang-toggle-btn active" onclick="setBibLang('th')" id="bib-lang-th">
                                🇹🇭 ไทย
                            </button>
                            <button type="button" class="lang-toggle-btn" onclick="setBibLang('en')" id="bib-lang-en">
                                🇺🇸 English
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview Container Box -->
                <div class="preview-container-box">
                    <!-- Preview Header Row -->
                    <div class="preview-header-row">
                        <div class="preview-box-title">
                            <i class="fas fa-eye"></i>
                            <?php echo __('bibliography_preview'); ?>
                        </div>
                        <div id="guidance-status" class="guidance-status guidance-warning">
                            <i class="fas fa-info-circle"></i>
                            <span><?php echo $currentLang === 'th' ? 'รอข้อมูล' : 'Waiting'; ?></span>
                        </div>
                    </div>

                    <!-- Bibliography Result Box -->
                    <div class="result-box" id="result-box-bib">
                        <div class="result-box-header">
                            <div style="display: flex; align-items: center;">
                                <span class="result-title"><?php echo __('bibliography'); ?></span>
                                <span class="manual-edit-badge" id="badge-manual-bib"><?php echo $currentLang === 'th' ? 'แก้ไขเอง' : 'Manual Edit'; ?></span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" class="btn-reset-preview" id="btn-reset-bib" onclick="resetPreview('bibliography')" title="Reset to automatic">
                                    <i class="fas fa-undo"></i> <?php echo $currentLang === 'th' ? 'รีเซ็ต' : 'Reset'; ?>
                                </button>
                                <button type="button" class="btn-copy-small" onclick="copyPreview('bibliography', this)" title="<?php echo $currentLang === 'th' ? 'คัดลอก' : 'Copy'; ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="result-content hanging-indent" id="preview-bibliography" contenteditable="true" oninput="markManualEdit('bibliography')">
                            <span class="result-placeholder"><?php echo $currentLang === 'th' ? 'รายการบรรณานุกรมจะแสดงที่นี่...' : 'Bibliography will appear here...'; ?></span>
                        </div>
                        <div class="result-footer">
                            <span class="result-badge">APA 7<sup>th</sup> Edition</span>
                        </div>
                    </div>

                    <!-- In-text Parenthetical Box -->
                    <div class="result-box" id="result-box-parenthetical">
                        <div class="result-box-header">
                            <div style="display: flex; align-items: center;">
                                <span class="result-title"><?php echo __('citation_parenthetical'); ?></span>
                                <span class="manual-edit-badge" id="badge-manual-parenthetical"><?php echo $currentLang === 'th' ? 'แก้ไขเอง' : 'Manual Edit'; ?></span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" class="btn-reset-preview" id="btn-reset-parenthetical" onclick="resetPreview('parenthetical')" title="Reset to automatic">
                                    <i class="fas fa-undo"></i> <?php echo $currentLang === 'th' ? 'รีเซ็ต' : 'Reset'; ?>
                                </button>
                                <button type="button" class="btn-copy-small" onclick="copyPreview('parenthetical', this)" title="<?php echo $currentLang === 'th' ? 'คัดลอก' : 'Copy'; ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="result-content" id="preview-parenthetical" contenteditable="true" oninput="markManualEdit('parenthetical')">
                            <span class="result-placeholder"><?php echo $currentLang === 'th' ? '(ผู้แต่ง, ปี)' : '(Author, Year)'; ?></span>
                        </div>
                        <div class="result-footer">
                            <span class="result-badge">In-text Parenthetical</span>
                        </div>
                    </div>

                    <!-- In-text Narrative Box -->
                    <div class="result-box" id="result-box-narrative" style="margin-bottom: 0;">
                        <div class="result-box-header">
                            <div style="display: flex; align-items: center;">
                                <span class="result-title"><?php echo __('citation_narrative'); ?></span>
                                <span class="manual-edit-badge" id="badge-manual-narrative"><?php echo $currentLang === 'th' ? 'แก้ไขเอง' : 'Manual Edit'; ?></span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" class="btn-reset-preview" id="btn-reset-narrative" onclick="resetPreview('narrative')" title="Reset to automatic">
                                    <i class="fas fa-undo"></i> <?php echo $currentLang === 'th' ? 'รีเซ็ต' : 'Reset'; ?>
                                </button>
                                <button type="button" class="btn-copy-small" onclick="copyPreview('narrative', this)" title="<?php echo $currentLang === 'th' ? 'คัดลอก' : 'Copy'; ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="result-content" id="preview-narrative" contenteditable="true" oninput="markManualEdit('narrative')">
                            <span class="result-placeholder"><?php echo $currentLang === 'th' ? 'ผู้แต่ง (ปี)' : 'Author (Year)'; ?></span>
                        </div>
                        <div class="result-footer">
                            <span class="result-badge">In-text Narrative</span>
                        </div>
                    </div>
                </div>

                <!-- Guest Signup Promo Box -->
                <?php if (!isLoggedIn()): ?>
                    <div class="signup-promo-box">
                        <div class="promo-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3 class="promo-title"><?php echo $currentLang === 'th' ? 'สมัครฟรี! รับฟีเจอร์เต็ม' : 'Sign up free! Get full features'; ?></h3>
                        <p class="promo-text"><?php echo $currentLang === 'th' ? 'บันทึกบรรณานุกรมถาวร จัดการเป็นโครงการ และ Export เป็น Word' : 'Save bibliographies permanently, organize projects, and export to Word'; ?></p>

                        <ul class="promo-features">
                            <li><i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'จัดการบรรณานุกรม' : 'Manage bibliographies'; ?></li>
                            <li><i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'Export Word' : 'Export Word'; ?></li>
                            <li><i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'จัดการโครงการ' : 'Manage projects'; ?></li>
                            <li><i class="fas fa-check-circle"></i> <?php echo $currentLang === 'th' ? 'Word Preview' : 'Word Preview'; ?></li>
                        </ul>

                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn-signup-promo">
                            <?php echo $currentLang === 'th' ? 'สมัครสมาชิกฟรี' : 'Sign up for free'; ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="<?php echo SITE_URL; ?>/assets/js/apa7-formatter.js"></script>
<script>
    // Initialize Toast first
    if (typeof Toast !== 'undefined') {
        Toast.init();
    }

    // Helper: Debounce function
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // State
    let selectedResource = null;
    let bibLanguage = 'th';
    let authorCount = 1;
    const authorTypes = <?php echo json_encode($authorTypes); ?>;
    const isThai = <?php echo $currentLang === 'th' ? 'true' : 'false'; ?>;

    // Category filter
    document.getElementById('category-filter').addEventListener('change', function() {
        const category = this.value;
        filterResources('', category);
    });

    // ─── Smart Search v2 Logic ─────────────────────────────────────────────

    function detectInputType(val) {
        if (/^https?:\/\//i.test(val)) return 'url';
        if (/^10\.\d{4,}\//i.test(val) || /doi\.org\/10\./i.test(val)) return 'doi';
        const cleaned = val.replace(/[-\s]/g, '');
        if (/^(\d{10}|\d{13}|\d{9}X)$/i.test(cleaned)) return 'isbn';
        return 'keyword';
    }

    let searchAbortController = null;

    async function performSmartSearch() {
        const input = document.getElementById('resource-search');
        const q = input.value.trim();
        const btn = document.getElementById('main-search-btn');
        const resultsDropdown = document.getElementById('main-smart-results');

        if (!q || q.length < 2) {
            resultsDropdown.classList.remove('active');
            return;
        }

        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // ─── Feature 2: Skeleton Loading Animation ───
        resultsDropdown.innerHTML = `
            <div style="padding: 8px;">
                ${[1,2,3].map(() => `
                    <div class="smart-result-item" style="pointer-events:none; opacity:0.6;">
                        <div class="smart-result-img" style="background:#E2E8F0; border-radius:8px; animation: skeletonPulse 1.5s ease-in-out infinite;"><i class="fas fa-spinner fa-spin" style="color:#94A3B8;"></i></div>
                        <div class="smart-result-info" style="flex:1;">
                            <div style="height:12px; background:#E2E8F0; border-radius:4px; width:65%; margin-bottom:8px; animation: skeletonPulse 1.5s ease-in-out infinite;"></div>
                            <div style="height:10px; background:#F1F5F9; border-radius:4px; width:40%; animation: skeletonPulse 1.5s ease-in-out 0.3s infinite;"></div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
        resultsDropdown.classList.add('active');

        // Cancel any ongoing request
        if (searchAbortController) searchAbortController.abort();
        searchAbortController = new AbortController();

        try {
            const response = await fetch(`<?php echo SITE_URL; ?>/api/smart_search.php?q=${encodeURIComponent(q)}`, {
                signal: searchAbortController.signal
            });

            if (response.status === 429) {
                resultsDropdown.innerHTML = `<div class="isbn-no-results" style="color: var(--primary);">${isThai ? 'ขออภัย ระบบกำลังประมวลผลมากเกินไป โปรดลองใหม่ในครู่เดียว' : 'Rate limit reached. Please try again shortly.'}</div>`;
                resultsDropdown.classList.add('active');
                return;
            }

            const res = await response.json();

            if (res.success && res.data && res.data.length > 0) {
                renderSmartResults(res.data, res.type, res.source_errors, res.sources_used);
                saveSearchHistory(q, res.type);
            } else {
                // ─── Feature 3: Show source errors if no results ───
                let errorHtml = '';
                if (res.source_errors && res.source_errors.length > 0) {
                    const failedSources = res.source_errors.map(e => e.url).join(', ');
                    errorHtml = `<div style="padding:6px 16px; font-size:0.75rem; color:#F59E0B; background:#FFFBEB; border-top:1px solid #FEF3C7;">
                        <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>
                        ${isThai ? 'บางแหล่งข้อมูลไม่ตอบสนอง: ' : 'Some sources failed: '}${failedSources}
                    </div>`;
                }
                resultsDropdown.innerHTML = `<div class="isbn-no-results">${isThai ? 'ไม่พบข้อมูลสำหรับ "' + q + '"' : 'No results found for "' + q + '"'}</div>${errorHtml}`;
                resultsDropdown.classList.add('active');
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Smart Search Error:', error);
            resultsDropdown.innerHTML = `<div class="isbn-no-results" style="color: #ef4444;"><i class="fas fa-wifi" style="margin-right:6px;"></i>${isThai ? 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง' : 'Connection error occurred. Please try again.'}</div>`;
            resultsDropdown.classList.add('active');
        } finally {
            btn.classList.remove('loading');
            btn.innerHTML = '<i class="fas fa-search"></i>';
        }
    }

    function renderSmartResults(items, searchType, sourceErrors, sourcesUsed) {
        const resultsDropdown = document.getElementById('main-smart-results');
        resultsDropdown.innerHTML = '';
        resultsDropdown.classList.add('active');

        // ─── Feature 3: Show source error banner if some APIs failed ───
        if (sourceErrors && sourceErrors.length > 0) {
            const failedHosts = [...new Set(sourceErrors.map(e => e.url))].join(', ');
            const errorBanner = document.createElement('div');
            errorBanner.style.cssText = 'padding:6px 16px; font-size:0.75rem; color:#92400E; background:#FEF3C7; border-bottom:1px solid #FDE68A; display:flex; align-items:center; gap:6px;';
            errorBanner.innerHTML = `<i class="fas fa-exclamation-triangle" style="color:#F59E0B;"></i> ${isThai ? 'บางแหล่งไม่ตอบสนอง: ' : 'Some sources failed: '}<b>${failedHosts}</b>`;
            resultsDropdown.appendChild(errorBanner);
        }

        // ─── Feature 6: Get previously used references ───
        const usedRefs = JSON.parse(localStorage.getItem('babybib_used_refs') || '[]');
        function isUsedBefore(item) {
            return usedRefs.some(ref => {
                if (ref.doi && item.doi && ref.doi === item.doi) return true;
                if (ref.title && item.title) {
                    const a = ref.title.toLowerCase().trim();
                    const b = item.title.toLowerCase().trim();
                    if (a === b) return true;
                    // Fuzzy: if 80%+ of the shorter string matches
                    const shorter = a.length < b.length ? a : b;
                    const longer = a.length < b.length ? b : a;
                    if (longer.includes(shorter.substring(0, Math.floor(shorter.length * 0.8)))) return true;
                }
                return false;
            });
        }

        // Mapping resource type codes to localized names
        const typeLabels = {
            'book': isThai ? 'หนังสือ' : 'Book',
            'journal_article': isThai ? 'บทความวารสาร' : 'Journal Article',
            'website': isThai ? 'เว็บไซต์' : 'Website',
            'web': isThai ? 'เว็บไซต์' : 'Website',
            'conference_paper': isThai ? 'บทความประชุมวิชาการ' : 'Conference Paper',
            'book_chapter': isThai ? 'บทในหนังสือ' : 'Book Chapter'
        };

        const PAGE_SIZE = 5;
        let visibleCount = Math.min(PAGE_SIZE, items.length);

        function renderItems() {
            resultsDropdown.innerHTML = '';

            items.slice(0, visibleCount).forEach(item => {
                const el = document.createElement('div');
                el.className = 'smart-result-item';
                const authorsList = item.authors ? item.authors.map(a => a.display).join(', ') : '';

                // Source label mapping
                const sourceLabels = {
                    'thaijo': 'ThaiJO',
                    'openalex_th': 'OpenAlex TH',
                    'openalex': 'OpenAlex',
                    'crossref': 'CrossRef',
                    'crossref_search': 'CrossRef',
                    'openlibrary': 'Open Library',
                    'google_books': 'Google Books',
                    'google_books_th': 'Google Books TH',
                    'semantic_scholar': 'Semantic Scholar',
                    'web': 'Web',
                };
                const sourceColors = {
                    'thaijo': '#E91E63',
                    'openalex_th': '#9C27B0',
                    'openalex': '#673AB7',
                    'crossref': '#FF5722',
                    'crossref_search': '#FF5722',
                    'openlibrary': '#4CAF50',
                    'google_books': '#2196F3',
                    'google_books_th': '#00BCD4',
                    'semantic_scholar': '#795548',
                    'web': '#607D8B',
                };
                // Handle merged sources like "crossref+openalex"
                const primarySource = (item.source || '').split('+')[0];
                const sourceLabel = sourceLabels[primarySource] || item.source || '';
                const sourceColor = sourceColors[primarySource] || '#9E9E9E';

                // Icon logic
                const typeIcon = primarySource === 'thaijo' || primarySource === 'openalex_th'
                    ? 'fa-landmark'
                    : primarySource.includes('crossref') || primarySource.includes('openalex') || primarySource === 'semantic_scholar'
                    ? 'fa-microscope'
                    : primarySource.includes('web') ? 'fa-globe'
                    : 'fa-book';

                const typeName = typeLabels[item.resource_type] || (isThai ? 'ทรัพยากร' : 'Resource');

                const thumbHtml = item.thumbnail
                    ? `<img src="${item.thumbnail}" alt="" onerror="this.parentElement.innerHTML='<i class=\\'fas ${typeIcon}\\'></i>'">`
                    : `<i class="fas ${typeIcon}"></i>`;

                // Journal/venue info
                const venueInfo = item.journal_name ? ` · ${item.journal_name}` : '';

                // ─── Feature 6: Check if used before ───
                const usedBefore = isUsedBefore(item);
                const usedBadge = usedBefore
                    ? `<span style="background:#FEF3C7; color:#92400E; font-size:9px; padding:2px 6px; border-radius:3px; font-weight:600; flex-shrink:0;"><i class="fas fa-check-circle" style="margin-right:2px;"></i>${isThai ? 'เคยใช้' : 'Used'}</span>`
                    : '';

                el.innerHTML = `
                    <div class="smart-result-img">${thumbHtml}</div>
                    <div class="smart-result-info">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px; flex-wrap: wrap;">
                            <span style="background: var(--primary-light); color: var(--primary); font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 700; text-transform: uppercase; flex-shrink: 0;">${typeName}</span>
                            <span style="background: ${sourceColor}15; color: ${sourceColor}; font-size: 9px; padding: 2px 6px; border-radius: 3px; font-weight: 600; flex-shrink: 0;">${sourceLabel}</span>
                            ${usedBadge}
                            <div class="smart-result-title" style="margin: 0;">${item.title}</div>
                        </div>
                        <div class="smart-result-meta">
                            <span>${authorsList || (isThai ? 'ไม่ทราบผู้แต่ง' : 'Unknown author')}</span>
                            ${item.year ? `<span>· ${item.year}</span>` : ''}
                            ${venueInfo ? `<span style="color: #94A3B8; font-size: 0.75rem;">${venueInfo}</span>` : ''}
                        </div>
                    </div>
                    <button type="button" class="btn-add-result">
                        <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                    </button>
                `;

                el.onclick = () => {
                    selectSmartResult(item);
                    // ─── Feature 6: Save to used refs ───
                    saveUsedRef(item);
                    resultsDropdown.classList.remove('active');
                };
                resultsDropdown.appendChild(el);
            });

            // Show "more" button if there are more results
            if (visibleCount < items.length) {
                const moreBtn = document.createElement('div');
                moreBtn.style.cssText = 'padding: 10px 16px; text-align: center; cursor: pointer; color: var(--primary); font-size: 0.85rem; font-weight: 600; border-top: 1px solid #F1F5F9; transition: background 0.2s;';
                moreBtn.innerHTML = `<i class="fas fa-chevron-down" style="margin-right: 6px;"></i>${isThai ? 'แสดงเพิ่มเติม' : 'Show more'} (${items.length - visibleCount} ${isThai ? 'รายการ' : 'more'})`;
                moreBtn.onmouseenter = () => moreBtn.style.background = '#F8FAFC';
                moreBtn.onmouseleave = () => moreBtn.style.background = '';
                moreBtn.onclick = (e) => {
                    e.stopPropagation();
                    visibleCount = Math.min(visibleCount + PAGE_SIZE, items.length);
                    renderItems();
                };
                resultsDropdown.appendChild(moreBtn);
            }

            // ─── Sources summary footer ───
            if (sourcesUsed && sourcesUsed.length > 0) {
                const sourceNames = {
                    'thaijo': 'ThaiJO', 'openalex_th': 'OpenAlex TH',
                    'crossref_search': 'CrossRef', 'openlibrary': 'Open Library',
                    'google_books': 'Google Books', 'google_books_th': 'Google Books TH',
                    'semantic_scholar': 'Semantic Scholar', 'crossref': 'CrossRef',
                    'openalex': 'OpenAlex', 'web': 'Web'
                };
                const names = sourcesUsed.map(s => sourceNames[s] || s).join(', ');
                const footer = document.createElement('div');
                footer.style.cssText = 'padding:6px 16px; font-size:0.7rem; color:#94A3B8; text-align:center; border-top:1px solid #F1F5F9; background:#F8FAFC;';
                footer.innerHTML = `<i class="fas fa-database" style="margin-right:4px;"></i>${isThai ? 'จาก' : 'From'}: ${names} · ${items.length} ${isThai ? 'ผลลัพธ์' : 'results'}`;
                resultsDropdown.appendChild(footer);
            }
        }

        renderItems();
    }

    // Debounced Smart Search
    const debouncedSmartSearch = debounce(performSmartSearch, 600);

    // Input event listener with type detection + resource filtering
    document.getElementById('resource-search').addEventListener('input', function() {
        const val = this.value.trim();
        const badge = document.getElementById('main-search-type-badge');
        const resultsDropdown = document.getElementById('main-smart-results');
        const category = document.getElementById('category-filter').value;

        // Type detection + Smart search only, don't filter local cards here
        // (User wants to use category-filter for that)

        // Reset badge
        badge.className = 'type-badge';
        badge.classList.remove('active');

        if (!val) {
            resultsDropdown.classList.remove('active');
            return;
        }

        // Detect type and show badge
        const type = detectInputType(val);
        const badges = {
            url:     { text: 'URL',  cls: 'badge-url' },
            doi:     { text: 'DOI',  cls: 'badge-doi' },
            isbn:    { text: 'ISBN', cls: 'badge-isbn' },
            keyword: { text: isThai ? 'ค้นหา' : 'SEARCH', cls: 'badge-keyword' }
        };
        badge.innerText = badges[type].text;
        badge.classList.add('active', badges[type].cls);

        // Trigger Smart Search based on type
        if (type === 'keyword') {
            if (val.length >= 3) debouncedSmartSearch();
            else resultsDropdown.classList.remove('active');
        } else if (type === 'isbn') {
            const cleaned = val.replace(/[-\s]/g, '');
            if (cleaned.length >= 10) debouncedSmartSearch();
        } else if (type === 'doi') {
            if (val.length > 8) debouncedSmartSearch();
        } else if (type === 'url') {
            if (val.length > 10) debouncedSmartSearch();
        }
    });

    // ─── Search History ──────────────────────────────────────────────────────

    function saveSearchHistory(q, type) {
        if (!q || q.length < 3) return;
        let history = JSON.parse(localStorage.getItem('babybib_search_history_v2') || '[]');
        history = history.filter(item => item.q.toLowerCase() !== q.toLowerCase());
        history.unshift({ q, type, t: Date.now() });
        history = history.slice(0, 5);
        localStorage.setItem('babybib_search_history_v2', JSON.stringify(history));
        renderSearchHistory();
    }

    function renderSearchHistory() {
        const container = document.getElementById('search-history');
        if (!container) return;
        let history = JSON.parse(localStorage.getItem('babybib_search_history_v2') || '[]');
        const now = Date.now();
        const expirationTime = 10 * 60 * 1000;
        const validHistory = history.filter(item => (now - item.t) < expirationTime);
        if (validHistory.length !== history.length) {
            localStorage.setItem('babybib_search_history_v2', JSON.stringify(validHistory));
        }
        if (validHistory.length === 0) { container.innerHTML = ''; return; }
        const typeIcons = { isbn: 'fa-barcode', doi: 'fa-microscope', url: 'fa-link', keyword: 'fa-search' };
        container.innerHTML = validHistory.map(item => `
            <div class="history-chip" onclick="useHistory('${item.q.replace(/'/g, "\\'")}')"
                 title="${item.q.replace(/"/g, '&quot;')}">
                <i class="fas ${typeIcons[item.type] || 'fa-history'}"></i>
                <span>${item.q}</span>
            </div>
        `).join('');
    }

    window.useHistory = function(q) {
        const input = document.getElementById('resource-search');
        input.value = q;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        performSmartSearch();
    };

    renderSearchHistory();
    setInterval(renderSearchHistory, 60000);

    // ─── Feature 6: Used References Tracker ─────────────────────────────────
    function saveUsedRef(item) {
        if (!item || !item.title) return;
        let refs = JSON.parse(localStorage.getItem('babybib_used_refs') || '[]');
        // Avoid duplicates
        const exists = refs.some(r => r.title === item.title || (r.doi && r.doi === item.doi));
        if (!exists) {
            refs.unshift({ title: item.title, doi: item.doi || '', t: Date.now() });
            refs = refs.slice(0, 50); // Keep max 50
            localStorage.setItem('babybib_used_refs', JSON.stringify(refs));
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.resource-toolbar-container')) {
            const dr = document.getElementById('main-smart-results');
            if (dr) dr.classList.remove('active');
        }
    });

    function filterResources(query, category) {
        let visibleCount = 0;

        document.querySelectorAll('.category-group').forEach(group => {
            let groupVisibleCount = 0;
            const items = group.querySelectorAll('.resource-card');

            items.forEach(card => {
                const nameTh = card.dataset.nameTh.toLowerCase();
                const nameEn = card.dataset.nameEn.toLowerCase();
                const cardCategory = card.dataset.category;

                const matchSearch = query === '' || nameTh.includes(query) || nameEn.includes(query);
                const matchCategory = category === 'all' || cardCategory === category;

                if (matchSearch && matchCategory) {
                    card.style.display = 'flex';
                    groupVisibleCount++;
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/Hide Group Header Based on Items inside
            if (groupVisibleCount === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = 'flex';
            }
        });

        // Show/Hide Empty State
        const emptyState = document.getElementById('no-results');
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }

    // Select resource
    function selectResource(card) {
        document.querySelectorAll('.resource-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');

        selectedResource = {
            id: card.dataset.id,
            code: card.dataset.code,
            fieldsConfig: JSON.parse(card.dataset.fieldsConfig || '{"fields":[]}'),
            name: isThai ? card.dataset.nameTh : card.dataset.nameEn,
            icon: card.querySelector('.resource-icon i').className.replace('fas ', '')
        };

        // Update step indicators
        document.getElementById('step-1').classList.remove('active');
        document.getElementById('step-1').classList.add('done');
        document.getElementById('step-2').classList.add('active');

        // Hide hero section
        document.querySelector('.hero').style.display = 'none';

        // Show form
        document.getElementById('resource-selection').classList.add('hidden');
        document.getElementById('form-section').classList.remove('hidden');

        // Set form values
        document.getElementById('resource-type-id').value = selectedResource.id;
        document.getElementById('selected-resource-title').textContent = selectedResource.name;

        // Use the same icon class from the card
        const cardIcon = card.querySelector('.resource-icon i');
        document.getElementById('selected-resource-icon').innerHTML = `<i class="${cardIcon.className}"></i>`;

        // Initialize author section
        initAuthorSection();

        // Load dynamic fields
        loadDynamicFields(selectedResource.code);

        // Smooth scroll to top of form
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function backToSelection() {
        // If in edit mode, go back to bibliography list
        if (IS_EDIT_MODE) {
            window.location.href = '<?php echo SITE_URL; ?>/users/bibliography-list.php';
            return;
        }

        document.getElementById('form-section').classList.add('hidden');
        document.getElementById('resource-selection').classList.remove('hidden');

        // Show hero section again
        document.querySelector('.hero').style.display = 'flex';

        document.getElementById('step-2').classList.remove('active');
        document.getElementById('step-1').classList.remove('done');
        document.getElementById('step-1').classList.add('active');
    }

    // ─── Quick Type Switcher ─────────────────────────────────────────────────
    function openTypeSwitcher() {
        const overlay = document.getElementById('type-switcher-overlay');
        overlay.classList.add('active');
        // Highlight current type
        document.querySelectorAll('.type-switch-item').forEach(item => {
            item.classList.toggle('current-type', item.dataset.code === (selectedResource?.code || ''));
        });
        setTimeout(() => document.getElementById('type-switcher-search').focus(), 200);
    }

    function closeTypeSwitcher() {
        document.getElementById('type-switcher-overlay').classList.remove('active');
        document.getElementById('type-switcher-search').value = '';
        filterTypeSwitcher('');
    }

    function filterTypeSwitcher(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.type-switch-item').forEach(item => {
            const match = !q || item.dataset.search.includes(q);
            item.classList.toggle('hidden-by-search', !match);
        });
        // Hide empty categories
        document.querySelectorAll('.type-switcher-category').forEach(cat => {
            const visible = cat.querySelectorAll('.type-switch-item:not(.hidden-by-search)');
            cat.style.display = visible.length > 0 ? '' : 'none';
        });
    }

    function switchToType(code) {
        // Find the original card in Step 1 with this code
        const card = document.querySelector(`.resource-card[data-code="${code}"]`);
        if (!card) return;

        // Same type? Just close
        if (selectedResource && selectedResource.code === code) {
            closeTypeSwitcher();
            return;
        }

        // ─── Preserve existing form data ───
        const preservedData = {};
        const fieldsToPreserve = ['bib-year', 'bib-title', 'bib-publisher', 'bib-edition', 
                                   'bib-volume', 'bib-issue', 'bib-pages', 'bib-doi', 
                                   'bib-url', 'bib-access-date', 'bib-isbn', 'bib-journal-title'];
        fieldsToPreserve.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.value) preservedData[id] = el.value;
        });

        // Preserve author data
        const authorData = [];
        document.querySelectorAll('.author-entry').forEach(entry => {
            const fname = entry.querySelector('[name*="fname"]');
            const mname = entry.querySelector('[name*="mname"]');
            const lname = entry.querySelector('[name*="lname"]');
            const atype = entry.querySelector('[name*="author_type"]');
            if (fname || lname) {
                authorData.push({
                    firstName: fname?.value || '',
                    middleName: mname?.value || '',
                    lastName: lname?.value || '',
                    authorType: atype?.value || 'general'
                });
            }
        });

        // Preserve language toggle
        const langValue = document.getElementById('bib-language')?.value || 'th';

        // ─── Switch resource type ───
        selectedResource = {
            id: card.dataset.id,
            code: card.dataset.code,
            fieldsConfig: JSON.parse(card.dataset.fieldsConfig || '{"fields":[]}'),
            name: isThai ? card.dataset.nameTh : card.dataset.nameEn,
            icon: card.querySelector('.resource-icon i').className.replace('fas ', '')
        };

        // Update UI
        document.getElementById('resource-type-id').value = selectedResource.id;
        document.getElementById('selected-resource-title').textContent = selectedResource.name;
        const cardIcon = card.querySelector('.resource-icon i');
        document.getElementById('selected-resource-icon').innerHTML = `<i class="${cardIcon.className}"></i>`;

        // Reload dynamic fields
        loadDynamicFields(selectedResource.code);

        // ─── Restore preserved data after fields load (short delay) ───
        setTimeout(() => {
            // Restore regular fields
            Object.entries(preservedData).forEach(([id, val]) => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = val;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });

            // Restore authors
            if (authorData.length > 0) {
                authorData.forEach((author, i) => {
                    if (i > 0) {
                        const addBtn = document.querySelector('.btn-add-author');
                        if (addBtn) addBtn.click();
                    }
                });
                setTimeout(() => {
                    document.querySelectorAll('.author-entry').forEach((entry, i) => {
                        if (i < authorData.length) {
                            const fname = entry.querySelector('[name*="fname"]');
                            const mname = entry.querySelector('[name*="mname"]');
                            const lname = entry.querySelector('[name*="lname"]');
                            const atype = entry.querySelector('[name*="author_type"]');
                            if (fname) fname.value = authorData[i].firstName;
                            if (mname) mname.value = authorData[i].middleName;
                            if (lname) lname.value = authorData[i].lastName;
                            if (atype) atype.value = authorData[i].authorType;
                        }
                    });
                    // Restore language
                    if (document.getElementById('bib-language')) {
                        document.getElementById('bib-language').value = langValue;
                    }
                    // Trigger preview update
                    if (typeof updatePreview === 'function') updatePreview();
                }, 300);
            }
        }, 400);

        // Flash animation on header
        const headerContent = document.querySelector('.header-content-new');
        headerContent.style.animation = 'magicFill 0.8s ease-out';
        setTimeout(() => headerContent.style.animation = '', 800);

        closeTypeSwitcher();
    }

    function setBibLang(lang) {
        bibLanguage = lang;
        document.getElementById('bib-language').value = lang;
        document.getElementById('bib-lang-th').classList.toggle('active', lang === 'th');
        document.getElementById('bib-lang-en').classList.toggle('active', lang === 'en');

        // Update year label based on language
        const yearLabel = document.getElementById('year-label');
        if (yearLabel) {
            yearLabel.innerHTML = (lang === 'th' ? 'ปี พ.ศ.' : 'Year (A.D.)') + '<span class="required">*</span>';
        }
        const yearInput = document.getElementById('field-year');
        if (yearInput) {
            yearInput.placeholder = lang === 'th' ? 'เช่น 2567' : 'e.g., 2024';
        }

        updatePreview();
    }

    // Author management
    function initAuthorSection() {
        authorCount = 1;
        document.getElementById('author-count').textContent = authorCount;
        renderAuthors();
    }

    function renderAuthors() {
        const container = document.getElementById('author-list');
        container.innerHTML = '';

        for (let i = 1; i <= authorCount; i++) {
            const authorHtml = `
            <div class="author-item" data-author-index="${i}" style="background: #fafafa; border-radius: 12px; padding: 20px; margin-bottom: 12px; border: 1px solid #f0f0f0;">
                <div style="margin-bottom: 16px;">
                    <span style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">${isThai ? 'ผู้แต่งคนที่' : 'Author'} ${i}</span>
                    ${authorCount > 1 ? `<button type="button" onclick="removeAuthorAt(${i})" style="float: right; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 14px;"><i class="fas fa-times"></i></button>` : ''}
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">${isThai ? 'เงื่อนไขผู้แต่ง' : 'Author Condition'}</label>
                    <select class="form-input form-select author-condition" name="author_condition_${i}" onchange="onAuthorConditionChange(${i}, this.value)" style="max-width: 300px;">
                        <option value="0">${isThai ? 'ทั่วไป' : 'Normal'}</option>
                        <option value="1">${isThai ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'Anonymous'}</option>
                        <option value="2">${isThai ? 'ผู้แต่งใช้นามแฝง' : 'Pseudonym'}</option>
                        <option value="3">${isThai ? 'ผู้แต่งเป็นราชสกุล เช่น ม.ร.ว.' : 'Royal Title'}</option>
                        <option value="4">${isThai ? 'ผู้แต่งมีบรรดาศักดิ์ เช่น คุณหญิง' : 'Noble Title'}</option>
                        <option value="5">${isThai ? 'ผู้แต่งเป็นพระสงฆ์' : 'Buddhist Monk'}</option>
                        <option value="6">${isThai ? 'ผู้แต่งเป็นบรรณาธิการ' : 'Editor'}</option>
                        <option value="7">${isThai ? 'ชื่อหน่วยงาน หรือสถาบัน' : 'Organization/Institution'}</option>
                    </select>
                </div>
                <div class="author-condition-field form-group mb-3" id="condition-field-${i}" style="display: none;">
                    <label class="form-label" id="condition-label-${i}">${isThai ? 'เงื่อนไข' : 'Condition'}</label>
                    <input type="text" class="form-input" name="author_condition_value_${i}" 
                           id="condition-input-${i}" placeholder="" oninput="updatePreview()">
                </div>
                <div class="author-fields" id="author-fields-${i}" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">${isThai ? 'ชื่อ' : 'First Name'}</label>
                        <input type="text" class="form-input author-firstname" name="author_firstname_${i}" 
                               placeholder="${isThai ? 'เช่น สมชาย' : 'e.g. John'}" oninput="updatePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isThai ? 'ชื่อกลาง' : 'Middle Name'}</label>
                        <input type="text" class="form-input author-middlename" name="author_middlename_${i}" 
                               placeholder="${isThai ? 'ถ้ามี...' : 'if any...'}" oninput="updatePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">${isThai ? 'นามสกุล' : 'Last Name'}</label>
                        <input type="text" class="form-input author-lastname" name="author_lastname_${i}" 
                               placeholder="${isThai ? 'เช่น ใจดี' : 'e.g. Doe'}" oninput="updatePreview()">
                    </div>
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', authorHtml);
        }

        // Update count display
        document.getElementById('author-count').textContent = authorCount;
    }

    // Remove specific author
    function removeAuthorAt(index) {
        if (authorCount > 1) {
            authorCount--;
            renderAuthors();
            updatePreview();
        }
    }

    // Handle author condition change
    function onAuthorConditionChange(index, value) {
        const conditionField = document.getElementById(`condition-field-${index}`);
        const conditionLabel = document.getElementById(`condition-label-${index}`);
        const conditionInput = document.getElementById(`condition-input-${index}`);
        const authorFields = document.getElementById(`author-fields-${index}`);

        // Reset
        conditionField.style.display = 'none';
        authorFields.style.display = 'grid';
        conditionInput.value = '';

        const labels = {
            '2': isThai ? 'นามแฝง' : 'Pseudonym',
            '3': isThai ? 'ฐานันดรศักดิ์ เช่น ม.ร.ว.' : 'Royal Title (e.g., M.R.)',
            '4': isThai ? 'บรรดาศักดิ์ เช่น คุณหญิง' : 'Noble Title',
            '7': isThai ? 'ชื่อหน่วยงาน/สถาบัน' : 'Organization Name'
        };

        if (value === '1') {
            // Anonymous - hide name fields
            authorFields.style.display = 'none';
        } else if (value === '2' || value === '7') {
            // Pseudonym or Organization - show only condition field, hide name fields
            conditionField.style.display = 'block';
            authorFields.style.display = 'none';
            conditionLabel.textContent = labels[value];
            conditionInput.placeholder = labels[value];
        } else if (value === '3' || value === '4') {
            // Royal/Noble title - show condition field AND name fields
            conditionField.style.display = 'block';
            conditionLabel.textContent = labels[value];
            conditionInput.placeholder = labels[value];
        }

        updatePreview();
    }

    function addAuthor() {
        if (authorCount < 21) {
            authorCount++;
            document.getElementById('author-count').textContent = authorCount;
            renderAuthors();
        }
    }

    function removeAuthor() {
        if (authorCount > 1) {
            authorCount--;
            document.getElementById('author-count').textContent = authorCount;
            renderAuthors();
            updatePreview();
        }
    }

    // Dynamic fields based on resource type
    function loadDynamicFields(code) {
        const container = document.getElementById('dynamic-fields');
        const config = selectedResource.fieldsConfig.fields;

        const fieldLabels = {
            'year': {
                th: 'ปี พ.ศ.',
                en: 'Year (A.D.)',
                placeholder: {
                    th: 'เช่น 2567',
                    en: 'e.g., 2024'
                }
            },
            'title': {
                th: 'ชื่อเรื่อง/ชื่อหนังสือ',
                en: 'Title',
                placeholder: {
                    th: 'ชื่อเรื่องหนังสือหรือบทความ',
                    en: 'Title of book or article'
                }
            },
            'edition': {
                th: 'ครั้งที่พิมพ์',
                en: 'Edition',
                placeholder: {
                    th: 'เช่น พิมพ์ครั้งที่ 2',
                    en: 'e.g., 2nd ed.'
                }
            },
            'publisher': {
                th: 'สำนักพิมพ์',
                en: 'Publisher',
                placeholder: {
                    th: 'ชื่อสำนักพิมพ์',
                    en: 'Publisher'
                }
            },
            'place': {
                th: 'สถานที่พิมพ์',
                en: 'Place of Publication',
                placeholder: {
                    th: 'เช่น กรุงเทพฯ',
                    en: 'e.g., Bangkok'
                }
            },
            'volume': {
                th: 'ปีที่/เล่มที่',
                en: 'Volume',
                placeholder: {
                    th: 'เล่มที่',
                    en: 'Vol.'
                }
            },
            'issue': {
                th: 'ฉบับที่',
                en: 'Issue',
                placeholder: {
                    th: 'ฉบับที่',
                    en: 'No.'
                }
            },
            'pages': {
                th: 'เลขหน้า',
                en: 'Pages',
                placeholder: {
                    th: 'เช่น 15-30',
                    en: 'e.g., 15-30'
                }
            },
            'doi': {
                th: 'DOI',
                en: 'DOI',
                placeholder: {
                    th: 'https://doi.org/10.xxx/xxxx',
                    en: 'https://doi.org/10.xxx/xxxx'
                }
            },
            'url': {
                th: 'URL',
                en: 'URL',
                placeholder: {
                    th: 'https://...',
                    en: 'https://...'
                }
            },
            'journal_name': {
                th: 'ชื่อวารสาร',
                en: 'Journal Name',
                placeholder: {
                    th: 'วารสารวิชาการ...',
                    en: 'Journal of...'
                }
            },
            'article_title': {
                th: 'ชื่อบทความ',
                en: 'Article Title',
                placeholder: {
                    th: 'ชื่อบทความในวารสาร',
                    en: 'Title of the article'
                }
            },
            'book_title': {
                th: 'ชื่อหนังสือ',
                en: 'Book Title',
                placeholder: {
                    th: 'ชื่อหนังสือหลัก',
                    en: 'Main book title'
                }
            },
            'chapter_title': {
                th: 'ชื่อบทในหนังสือ',
                en: 'Chapter Title',
                placeholder: {
                    th: 'ชื่อบทหรือหัวข้อ',
                    en: 'Title of the chapter'
                }
            },
            'editors': {
                th: 'ชื่อบรรณาธิการ',
                en: 'Editors',
                placeholder: {
                    th: 'ชื่อ บรรณาธิการ',
                    en: 'Editor Names'
                }
            },
            'institution': {
                th: 'สถาบัน/หน่วยงาน',
                en: 'Institution',
                placeholder: {
                    th: 'มหาวิทยาลัย...',
                    en: 'University...'
                }
            },
            'organization': {
                th: 'ชื่อหน่วยงาน',
                en: 'Organization',
                placeholder: {
                    th: 'กระทรวง...',
                    en: 'Ministry...'
                }
            },
            'website_name': {
                th: 'ชื่อเว็บไซต์',
                en: 'Website Name',
                placeholder: {
                    th: 'ชื่อสถานีหรือชื่อเว็บ',
                    en: 'Website/Site name'
                }
            },
            'month': {
                th: 'เดือน',
                en: 'Month',
                placeholder: {
                    th: 'เช่น มกราคม',
                    en: 'e.g., January'
                }
            },
            'day': {
                th: 'วันที่',
                en: 'Day',
                placeholder: {
                    th: '1-31',
                    en: '1-31'
                }
            },
            'channel_name': {
                th: 'ชื่อผู้ใช้/ช่อง',
                en: 'Channel/Username',
                placeholder: {
                    th: 'ชื่อช่อง YouTube',
                    en: 'YouTube Channel Name'
                }
            },
            'platform': {
                th: 'แพลตฟอร์ม',
                en: 'Platform',
                placeholder: {
                    th: 'เช่น Facebook, TikTok',
                    en: 'e.g., Facebook, TikTok'
                }
            },
            'newspaper_name': {
                th: 'ชื่อหนังสือพิมพ์',
                en: 'Newspaper Name',
                placeholder: {
                    th: 'ชื่อหนังสือพิมพ์',
                    en: 'Newspaper Name'
                }
            },
            'report_number': {
                th: 'หมายเลขรายงาน',
                en: 'Report No.',
                placeholder: {
                    th: 'ถ้ามี',
                    en: 'If available'
                }
            },
            'degree_type': {
                th: 'ระดับปริญญา',
                en: 'Degree Type',
                placeholder: {
                    th: 'เช่น วิทยานิพนธ์ปริญญาโท',
                    en: 'e.g., Master\'s thesis'
                }
            },
            'conference_name': {
                th: 'ชื่อการประชุม',
                en: 'Conference Name',
                placeholder: {
                    th: 'ชื่อการประชุมวิชาการ',
                    en: 'Conference Name'
                }
            },
            'location': {
                th: 'สถานที่/เมือง',
                en: 'Location',
                placeholder: {
                    th: 'เมือง, ประเทศ',
                    en: 'City, Country'
                }
            },
            'database_name': {
                th: 'ชื่อฐานข้อมูล',
                en: 'Database Name',
                placeholder: {
                    th: 'TCI, ProQuest...',
                    en: 'ProQuest, EBSCO...'
                }
            },
            'ai_name': {
                th: 'ชื่อ AI',
                en: 'AI Name',
                placeholder: {
                    th: 'ChatGPT, Gemini',
                    en: 'ChatGPT, Gemini'
                }
            },
            'version': {
                th: 'เวอร์ชัน',
                en: 'Version',
                placeholder: {
                    th: 'เช่น 4.0',
                    en: 'e.g., 4.0'
                }
            }
        };

        let fieldsHtml = '';
        config.forEach(fieldName => {
            if (fieldName === 'authors') return; // Handled separately

            const labelData = fieldLabels[fieldName] || {
                th: fieldName,
                en: fieldName,
                placeholder: {
                    th: '',
                    en: ''
                }
            };
            const label = bibLanguage === 'th' ? labelData.th : labelData.en;
            const placeholder = bibLanguage === 'th' ? labelData.placeholder.th : labelData.placeholder.en;
            const required = ['year', 'title', 'url', 'doi', 'journal_name', 'publisher'].includes(fieldName) ? 'required' : '';

            // Special handling for degree_type - use dropdown
            if (fieldName === 'degree_type') {
                fieldsHtml += `
                <div class="form-group">
                    <label class="form-label">${label}</label>
                    <select class="form-select" name="${fieldName}" id="field-${fieldName}" onchange="updatePreview()">
                        <option value="master">${bibLanguage === 'th' ? 'ปริญญามหาบัณฑิต (ป.โท)' : "Master's Thesis"}</option>
                        <option value="doctoral">${bibLanguage === 'th' ? 'ปริญญาดุษฎีบัณฑิต (ป.เอก)' : 'Doctoral Dissertation'}</option>
                        <option value="bachelor">${bibLanguage === 'th' ? 'ปริญญาบัณฑิต (ป.ตรี)' : "Bachelor's Thesis"}</option>
                    </select>
                </div>
                `;
            } else {
                fieldsHtml += `
                <div class="form-group">
                    <div class="field-label-wrapper">
                        <label class="form-label" for="field-${fieldName}">${label}${required ? '<span class="required">*</span>' : ''}</label>
                        ${fieldName === 'publisher' ? `
                            <button type="button" class="btn-field-help" onclick="showPublisherHelp()" title="${bibLanguage === 'th' ? 'ช่วยเหลือ' : 'Help'}">
                                <i class="fas fa-question-circle"></i>
                            </button>
                        ` : ''}
                    </div>
                    <input type="${fieldName === 'url' ? 'url' : (fieldName === 'year' ? 'number' : 'text')}" 
                           class="form-input" name="${fieldName}" id="field-${fieldName}" 
                           placeholder="${placeholder}" ${required} oninput="updatePreview()">
                </div>
                `;
            }
        });

        container.innerHTML = fieldsHtml;

        // Show/Hide Scraper Box if URL field exists
        const scraperBox = document.getElementById('url-scraper-box');
        if (config.some(f => f.name === 'url')) {
            scraperBox.style.display = 'block';
        } else {
            scraperBox.style.display = 'none';
        }
    }

    // Manual Edit Logic
    const manualEdits = {
        bibliography: false,
        parenthetical: false,
        narrative: false
    };

    function markManualEdit(type) {
        manualEdits[type] = true;
        document.getElementById(`badge-manual-${type}`).classList.add('active');
        document.getElementById(`btn-reset-${type}`).classList.add('active');
    }

    function resetPreview(type) {
        manualEdits[type] = false;
        document.getElementById(`badge-manual-${type}`).classList.remove('active');
        document.getElementById(`btn-reset-${type}`).classList.remove('active');
        updatePreview();
    }

    // Smart Web Scraper Logic
    async function runWebScraper() {
        const urlInput = document.getElementById('scraper-url-input');
        const url = urlInput.value.trim();
        if (!url) {
            Swal.fire({
                icon: 'warning',
                title: isThai ? 'กรุณาใส่ URL' : 'URL Required',
                text: isThai ? 'โปรดระบุลิงก์เว็บไซต์ที่ต้องการดึงข้อมูล' : 'Please provide a website link to fetch data from.'
            });
            return;
        }

        const btn = document.getElementById('btn-run-scraper');
        const status = document.getElementById('scraper-status');

        btn.disabled = true;
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        status.style.display = 'block';
        status.style.color = 'var(--text-secondary)';
        status.innerHTML = `<i class="fas fa-search"></i> ${isThai ? 'กำลังดึงข้อมูล...' : 'Fetching data...'}`;

        try {
            const response = await fetch(`api/scraper/web.php?url=${encodeURIComponent(url)}`);
            const result = await response.json();

            if (result.success) {
                const data = result.data;

                // Fill metadata fields
                const fieldMappings = {
                    'title': data.title,
                    'website_name': data.website_name,
                    'publisher': data.website_name, // Fallback for some types
                    'year': data.year,
                    'url': data.url
                };

                for (const [fieldName, value] of Object.entries(fieldMappings)) {
                    if (value) {
                        const field = document.getElementById(`field-${fieldName}`);
                        if (field) {
                            field.value = value;
                        }
                    }
                }

                // Handle Author (Simple First/Last parsing)
                if (data.author) {
                    const authorParts = data.author.trim().split(/\s+/);
                    const firstNameField = document.querySelector('.author-firstname');
                    const lastNameField = document.querySelector('.author-lastname');

                    if (firstNameField) {
                        if (authorParts.length === 1) {
                            // If only one word, maybe it's a pseudonym/org?
                            // For simplicity, just put in first name for now
                            firstNameField.value = authorParts[0];
                        } else {
                            firstNameField.value = authorParts[0];
                            if (lastNameField) {
                                lastNameField.value = authorParts.slice(1).join(' ');
                            }
                        }
                    }
                }

                status.style.color = 'var(--success)';
                status.innerHTML = `<i class="fas fa-check-circle"></i> ${isThai ? 'ดึงข้อมูลสำเร็จ!' : 'Fetch successful!'}`;

                // Flash the preview to show it's updated
                updatePreview();
                const resultBox = document.querySelector('.result-box');
                if (resultBox) {
                    resultBox.classList.add('updated-flash');
                    setTimeout(() => resultBox.classList.remove('updated-flash'), 2000);
                }
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            status.style.color = 'var(--danger)';
            status.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${isThai ? 'ดึงข้อมูลล้มเหลว' : 'Failed to fetch'}`;
            console.error('Scraper Error:', error);

            Swal.fire({
                icon: 'error',
                title: isThai ? 'เกิดข้อผิดพลาด' : 'Error',
                text: isThai ? 'ไม่สามารถดึงข้อมูลได้: ' + error.message : 'Could not fetch data: ' + error.message
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
            setTimeout(() => {
                if (status.innerHTML.includes('Success')) {
                    status.style.display = 'none';
                }
            }, 3000);
        }
    }

    // Bind event listener
    document.addEventListener('DOMContentLoaded', function() {
        const scraperBtn = document.getElementById('btn-run-scraper');
        if (scraperBtn) {
            scraperBtn.addEventListener('click', runWebScraper);
        }
    });

    // Update preview in real-time
    function updatePreview() {
        const form = document.getElementById('bibliography-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // 1. COLLECT AUTHORS
        const authors = [];
        for (let i = 1; i <= authorCount; i++) {
            const condition = data[`author_condition_${i}`] || '0';
            const conditionValue = data[`author_condition_value_${i}`] || '';
            const firstName = data[`author_firstname_${i}`] || '';
            const lastName = data[`author_lastname_${i}`] || '';
            const middleName = data[`author_middlename_${i}`] || '';

            let authorObj = {
                type: 'normal',
                first: firstName,
                last: lastName,
                middle: middleName,
                display: ''
            };

            if (condition === '1') {
                authorObj.type = 'anonymous';
                authorObj.display = bibLanguage === 'th' ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'Anonymous';
            } else if (condition === '2') {
                authorObj.type = 'pseudonym';
                authorObj.display = conditionValue;
            } else if (condition === '7') {
                authorObj.type = 'organization';
                authorObj.display = conditionValue;
            } else if (condition === '3' || condition === '4') {
                authorObj.type = 'titled';
                authorObj.display = conditionValue ? `${conditionValue}${firstName} ${lastName}`.trim() : `${firstName} ${lastName}`.trim();
            } else if (condition === '5') {
                authorObj.type = 'monk';
                authorObj.display = `${firstName} ${lastName}`.trim();
            } else if (condition === '6') {
                authorObj.type = 'editor';
                authorObj.display = `${firstName} ${lastName}`.trim();
            } else {
                authorObj.display = `${firstName} ${lastName}`.trim();
            }

            if (authorObj.display || firstName || lastName) {
                authors.push(authorObj);
            }
        }

        // 2. FORMAT BIBLIOGRAPHY STRING (APA 7<sup>th</sup>
        let bib = '';
        const authorStr = formatAuthorsBibAPA7(authors, bibLanguage);
        const year = data.year || (bibLanguage === 'th' ? 'ม.ป.ป.' : 'n.d.');
        const title = data.title || data.article_title || data.chapter_title || data.paper_title ||
            data.page_title || data.entry_title || data.video_title || data.webinar_title ||
            data.presentation_title || data.content_title || data.episode_title ||
            data.patent_title || data.prompt_description || '';
        const code = selectedResource?.code || 'book';

        switch (code) {
            // ===== BOOKS =====
            case 'book':
                bib = formatBookAPA7(data, authorStr, bibLanguage);
                break;
            case 'book_series':
                bib = formatBookSeriesAPA7(data, authorStr, bibLanguage);
                break;
            case 'book_chapter':
                bib = formatBookChapterAPA7(data, authorStr, bibLanguage);
                break;
            case 'ebook_doi':
                bib = formatEbookDoiAPA7(data, authorStr, bibLanguage);
                break;
            case 'ebook_no_doi':
                bib = formatEbookNoDoiAPA7(data, authorStr, bibLanguage);
                break;

                // ===== JOURNALS =====
            case 'journal_article':
                bib = formatJournalArticleAPA7(data, authorStr, bibLanguage);
                break;
            case 'ejournal_doi':
                bib = formatEjournalDoiAPA7(data, authorStr, bibLanguage);
                break;
            case 'ejournal_no_doi':
            case 'ejournal_print':
            case 'ejournal_only':
                bib = formatEjournalUrlAPA7(data, authorStr, bibLanguage);
                break;

                // ===== DICTIONARIES/ENCYCLOPEDIAS =====
            case 'dictionary':
                bib = formatDictionaryAPA7(data, bibLanguage);
                break;
            case 'dictionary_online':
                bib = formatDictionaryOnlineAPA7(data, bibLanguage);
                break;
            case 'encyclopedia':
                bib = formatEncyclopediaAPA7(data, authorStr, bibLanguage);
                break;
            case 'encyclopedia_online':
                bib = formatEncyclopediaOnlineAPA7(data, authorStr, bibLanguage);
                break;

                // ===== NEWSPAPERS =====
            case 'newspaper_print':
                bib = formatNewspaperPrintAPA7(data, authorStr, bibLanguage);
                break;
            case 'newspaper_online':
                bib = formatNewspaperOnlineAPA7(data, authorStr, bibLanguage);
                break;

                // ===== REPORTS =====
            case 'report':
            case 'research_report':
            case 'institutional_report':
                bib = formatReportAPA7(data, authorStr, bibLanguage);
                break;
            case 'government_report':
                bib = formatReportAPA7({
                    ...data,
                    organization: data.organization // formatReportAPA7 handles this
                }, data.organization, bibLanguage);
                break;

                // ===== CONFERENCES =====
            case 'conference_proceeding':
                bib = formatConferenceAPA7(data, authorStr, bibLanguage, 'published');
                break;
            case 'conference_no_proceeding':
                bib = formatConferenceAPA7(data, authorStr, bibLanguage, 'paper');
                break;
            case 'conference_presentation':
                bib = formatConferenceAPA7(data, authorStr, bibLanguage, 'presentation');
                break;

                // ===== THESES =====
            case 'thesis_unpublished':
                bib = formatThesisUnpublishedAPA7(data, authorStr, bibLanguage);
                break;
            case 'thesis_website':
                bib = formatThesisWebsiteAPA7(data, authorStr, bibLanguage);
                break;
            case 'thesis_database':
                bib = formatThesisDatabaseAPA7(data, authorStr, bibLanguage);
                break;

                // ===== ONLINE SOURCES =====
            case 'webpage':
                bib = formatWebpageAPA7(data, authorStr, bibLanguage);
                break;
            case 'social_media':
                bib = formatSocialMediaAPA7(data, authorStr, bibLanguage);
                break;
            case 'royal_gazette':
                bib = formatRoyalGazetteAPA7(data, bibLanguage);
                break;
            case 'patent_online':
                bib = formatPatentAPA7(data, bibLanguage);
                break;
            case 'personal_communication':
                bib = bibLanguage === 'th' ?
                    `<i>(หมายเหตุ: การติดต่อสื่อสารส่วนบุคคลไม่รวมในรายการบรรณานุกรม อ้างอิงในเนื้อหาเท่านั้น)</i>` :
                    `<i>(Note: Personal communication is not included in the reference list, cite only in text)</i>`;
                break;

                // ===== MEDIA =====
            case 'infographic':
                bib = formatInfographicAPA7(data, authorStr, bibLanguage);
                break;
            case 'slides_online':
                bib = formatSlidesAPA7(data, authorStr, bibLanguage);
                break;
            case 'webinar':
                bib = formatWebinarAPA7(data, bibLanguage);
                break;
            case 'youtube_video':
                bib = formatYoutubeVideoAPA7(data, bibLanguage);
                break;
            case 'podcast':
                bib = formatPodcastAPA7(data, bibLanguage);
                break;

                // ===== AI GENERATED =====
            case 'ai_generated':
                bib = formatAIGeneratedAPA7(data, bibLanguage);
                break;

                // ===== DEFAULT =====
            default: {
                let defBib = '';
                if (authorStr) defBib += `${authorStr}. `;
                defBib += `(${year}). `;
                defBib += `<i>${title}</i>. `;
                if (data.publisher) defBib += `${data.publisher}. `;
                if (data.doi) defBib += formatDoiAPA7(data.doi);
                else if (data.url) defBib += data.url;
                bib = defBib;
                break;
            }
        }


        // 3. APPLY TO PREVIEW (Unless manual edit is on)
        if (!manualEdits.bibliography) {
            document.getElementById('preview-bibliography').innerHTML = bib || `<span class="result-placeholder">${bibLanguage === 'th' ? 'รายการบรรณานุกรมจะแสดงที่นี่...' : 'Bibliography will appear here...'}</span>`;
        }

        if (!manualEdits.parenthetical) {
            const pCit = formatParentheticalAPA7(authors, year, bibLanguage, title, code);
            document.getElementById('preview-parenthetical').innerHTML = pCit || `<span class="result-placeholder">${isThai ? '(ผู้แต่ง, ปี)' : '(Author, Year)'}</span>`;
        }

        if (!manualEdits.narrative) {
            const nCit = formatNarrativeAPA7(authors, year, bibLanguage, title, code);
            document.getElementById('preview-narrative').innerHTML = nCit || `<span class="result-placeholder">${isThai ? 'ผู้แต่ง (ปี)' : 'Author (Year)'}</span>`;
        }

        // 4. UPDATE GUIDANCE STATUS (Existing logic)
        const guidanceStatus = document.getElementById('guidance-status');
        if (guidanceStatus) {
            const hasTitle = !!(data.title || data.article_title || data.chapter_title || data.paper_title ||
                data.page_title || data.entry_title || data.video_title || data.webinar_title ||
                data.presentation_title || data.content_title || data.episode_title ||
                data.patent_title || data.prompt_description);
            const hasYear = !!data.year;
            const hasAuthor = authors.length > 0 && authors.some(a => a.display && a.display.trim() !== '');

            let missingFields = [];
            if (!hasTitle) missingFields.push(isThai ? 'ชื่อเรื่อง' : 'Title');
            if (!hasYear) missingFields.push(isThai ? 'ปี' : 'Year');

            if (missingFields.length === 0) {
                // All complete
                guidanceStatus.className = 'guidance-status guidance-success';
                guidanceStatus.innerHTML = `<i class="fas fa-check-circle"></i><span>${isThai ? 'ครบถ้วน' : 'Complete'}</span>`;
            } else if (missingFields.length <= 2 && hasTitle) {
                // Missing some fields but has title
                guidanceStatus.className = 'guidance-status guidance-warning';
                guidanceStatus.innerHTML = `<i class="fas fa-exclamation-triangle"></i><span>${isThai ? 'ขาด ' : 'Missing '}${missingFields.join(', ')}</span>`;
            } else {
                // Waiting for data
                guidanceStatus.className = 'guidance-status guidance-warning';
                guidanceStatus.innerHTML = `<i class="fas fa-info-circle"></i><span>${isThai ? 'รอข้อมูล' : 'Waiting'}</span>`;
            }
        }
    }

    function copyPreview(type, btn) {
        const el = document.getElementById('preview-' + type);
        const htmlContent = el.innerHTML;
        const textContent = el.innerText;

        if (textContent && !el.querySelector('.result-placeholder')) {
            // Pass the HTML content to support rich text copying
            copyToClipboard(htmlContent, btn);
        }
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function selectSmartResult(item) {
        console.log('Selecting Smart Result:', item);

        // 1. Determine best resource type (with fallback mapping)
        let targetType = item.resource_type || 'book';
        
        // Map API resource_type to DB code (fallback table)
        const typeMapping = {
            'conference_paper': 'conference_proceeding',
            'website': 'webpage',
            'e-journal': 'ejournal_doi',
            'e-book': 'ebook_no_doi',
            'thesis': 'thesis_unpublished',
        };
        if (typeMapping[targetType]) {
            targetType = typeMapping[targetType];
        }
        
        let card = document.querySelector(`.resource-card[data-code="${targetType}"]`);
        
        // Fallback: try broader category if exact match not found
        if (!card) {
            const fallbacks = {
                'conference_proceeding': 'conference_no_proceeding',
                'conference_no_proceeding': 'journal_article',
                'ejournal_doi': 'journal_article',
                'ejournal_no_doi': 'journal_article',
                'ebook_doi': 'book',
                'ebook_no_doi': 'book',
                'webpage': 'book',
            };
            const fallbackType = fallbacks[targetType];
            if (fallbackType) {
                card = document.querySelector(`.resource-card[data-code="${fallbackType}"]`);
            }
        }
        
        // Ultimate fallback: use 'book' as default
        if (!card) {
            card = document.querySelector('.resource-card[data-code="book"]');
        }

        // Show loading toast for magic filling
        const loadingToast = Toast.show(isThai ? 'กำลังกรอกข้อมูล...' : 'Filling data...', 'info');

        if (card) {
            selectResource(card);
        }

        // 2. Fill Fields (Wait for dynamic fields to render)
        setTimeout(() => {
            // Convert year to พ.ศ. if bibliography language is Thai
            let yearValue = item.year;
            if (yearValue && bibLanguage === 'th') {
                const yearNum = parseInt(yearValue, 10);
                // If year looks like A.D. (ค.ศ.) — between 1000 and 2600 — convert to พ.ศ.
                if (yearNum >= 1000 && yearNum <= 2600) {
                    yearValue = String(yearNum + 543);
                }
            }

            const mappings = {
                'title': item.title,
                'article_title': item.title,
                'year': yearValue,
                'publisher': item.publisher,
                'pages': item.pages,
                'doi': item.doi,
                'url': item.url,
                'volume': item.volume,
                'issue': item.issue,
                'journal_name': item.journal_name || item.publisher,
                'website_name': item.publisher
            };

            // Sequence filling for "Instant" feel
            let delay = 0;
            const entries = Object.entries(mappings);
            entries.forEach(([key, value]) => {
                if (value) {
                    setTimeout(() => {
                        const field = document.getElementById('field-' + key) || document.querySelector(`[name="${key}"]`);
                        if (field) {
                            field.value = value;
                            field.classList.add('field-magic-fill');

                            // Trigger preview update for this field
                            field.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));

                            // Remove animation class after it finishes
                            setTimeout(() => field.classList.remove('field-magic-fill'), 1000);
                        }
                    }, delay);
                    delay += 100; // Staggered appearance
                }
            });

            // 3. Fill Authors
            setTimeout(() => {
                if (item.authors && item.authors.length > 0) {
                    authorCount = item.authors.length;
                    const authorDisplay = document.getElementById('author-count');
                    if (authorDisplay) authorDisplay.textContent = authorCount;
                    renderAuthors();

                    item.authors.forEach((a, idx) => {
                        const i = idx + 1;
                        setTimeout(() => {
                            const f = document.querySelector(`[name="author_firstname_${i}"]`);
                            const l = document.querySelector(`[name="author_lastname_${i}"]`);
                            if (f) {
                                f.value = a.firstName;
                                f.classList.add('field-magic-fill');
                                f.dispatchEvent(new Event('input', {
                                    bubbles: true
                                }));
                                setTimeout(() => f.classList.remove('field-magic-fill'), 1000);
                            }
                            if (l) {
                                l.value = a.lastName;
                                l.classList.add('field-magic-fill');
                                l.dispatchEvent(new Event('input', {
                                    bubbles: true
                                }));
                                setTimeout(() => l.classList.remove('field-magic-fill'), 1000);
                            }
                        }, idx * 100);
                    });
                } else if (item.author) {
                    authorCount = 1;
                    renderAuthors();
                    const f = document.querySelector(`[name="author_firstname_${1}"]`);
                    if (f) {
                        f.value = item.author;
                        f.classList.add('field-magic-fill');
                        f.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        setTimeout(() => f.classList.remove('field-magic-fill'), 1000);
                    }
                }

                // Final update
                updatePreview();

                // Close dropdowns
                const dr = document.getElementById('main-smart-results');
                if (dr) dr.classList.remove('active');

                Toast.show(isThai ? 'กรอกข้อมูลสำเร็จ' : 'Magic fill complete!', 'success');
            }, delay + 200);

        }, 600);
    }


    function clearForm() {
        document.getElementById('bibliography-form').reset();
        document.getElementById('resource-search').value = '';
        initAuthorSection();
        clearValidationWarnings();
        updatePreview();
    }

    // Smart Validation System
    function validateForm() {
        const warnings = [];
        clearValidationWarnings();

        // Check Year
        const yearField = document.getElementById('field-year');
        if (yearField && !yearField.value.trim()) {
            warnings.push({
                field: yearField,
                label: 'ปี',
                message: isThai ? 'ยังไม่ได้ระบุปี' : 'Year is missing'
            });
        } else if (yearField && yearField.value.trim()) {
            const year = parseInt(yearField.value.trim());
            if (bibLanguage === 'th' && (year < 2400 || year > 2600)) {
                warnings.push({
                    field: yearField,
                    label: 'ปี',
                    message: isThai ? 'ปี พ.ศ. ควรอยู่ระหว่าง 2400-2600' : 'Buddhist year should be 2400-2600'
                });
            } else if (bibLanguage === 'en' && (year < 1800 || year > 2100)) {
                warnings.push({
                    field: yearField,
                    label: 'ปี',
                    message: isThai ? 'ปี ค.ศ. ควรอยู่ระหว่าง 1800-2100' : 'Year should be 1800-2100'
                });
            }
        }

        /* 
        // Check Author (at least one)
        const authorFirstName = document.querySelector('[name="author_firstname_1"]');
        const authorCondition = document.querySelector('[name="author_condition_1"]');
        const conditionInput = document.getElementById('condition-input-1');

        let hasAuthor = false;
        if (authorCondition && authorCondition.value !== '0') {
            // Special condition selected (anonymous, org, etc)
            if (conditionInput && conditionInput.value.trim()) hasAuthor = true;
            else if (['1'].includes(authorCondition.value)) hasAuthor = true; // Anonymous doesn't need input
        } else if (authorFirstName && authorFirstName.value.trim()) {
            hasAuthor = true;
        }

        if (!hasAuthor) {
            warnings.push({
                field: authorFirstName || authorCondition,
                label: 'ผู้แต่ง',
                message: isThai ? 'ยังไม่ได้ระบุผู้แต่ง' : 'Author is missing'
            });
        }
        */

        // Check Title
        const titleField = document.getElementById('field-title');
        if (titleField && !titleField.value.trim()) {
            warnings.push({
                field: titleField,
                label: 'ชื่อเรื่อง',
                message: isThai ? 'ยังไม่ได้ระบุชื่อเรื่อง' : 'Title is missing'
            });
        }

        // Check URL format if provided
        const urlField = document.getElementById('field-url');
        if (urlField && urlField.value.trim() && !urlField.value.trim().startsWith('http')) {
            warnings.push({
                field: urlField,
                label: 'URL',
                message: isThai ? 'URL ควรขึ้นต้นด้วย http:// หรือ https://' : 'URL should start with http:// or https://'
            });
        }

        // Display warnings
        if (warnings.length > 0) {
            showValidationWarnings(warnings);
            return false;
        }

        return true;
    }

    function showValidationWarnings(warnings) {
        // Highlight fields
        warnings.forEach(w => {
            if (w.field) {
                w.field.classList.add('field-warning', 'field-warning-pulse');
                const label = w.field.closest('.form-group')?.querySelector('label');
                if (label) label.classList.add('field-warning-label');
            }
        });

        // Show summary
        const summaryHtml = `
            <div class="validation-summary active" id="validation-summary">
                <div class="validation-summary-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${isThai ? 'กรุณาตรวจสอบข้อมูลต่อไปนี้' : 'Please check the following'}
                </div>
                <ul class="validation-summary-list">
                    ${warnings.map(w => `<li>${w.message}</li>`).join('')}
                </ul>
            </div>
        `;

        // Insert before form actions
        const existingSummary = document.getElementById('validation-summary');
        if (existingSummary) existingSummary.remove();

        const formActions = document.querySelector('.action-bar');
        if (formActions) {
            formActions.insertAdjacentHTML('beforebegin', summaryHtml);
        }

        // Scroll to first warning
        if (warnings[0]?.field) {
            warnings[0].field.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            warnings[0].field.focus();
        }
    }

    function clearValidationWarnings() {
        document.querySelectorAll('.field-warning').forEach(el => {
            el.classList.remove('field-warning', 'field-warning-pulse');
        });
        document.querySelectorAll('.field-warning-label').forEach(el => {
            el.classList.remove('field-warning-label');
        });
        const summary = document.getElementById('validation-summary');
        if (summary) summary.remove();
    }

    // Form submission with Smart Validation
    document.getElementById('bibliography-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Form submitted');

        // Run Smart Validation first
        if (!validateForm()) {
            Toast.show(isThai ? 'กรุณาตรวจสอบข้อมูลที่ยังไม่ครบ' : 'Please check missing information', 'warning');
            return;
        }

        try {

            const btn = this.querySelector('button[type="submit"]');
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            // Collect authors with conditions
            data.authors = [];
            for (let i = 1; i <= authorCount; i++) {
                const condition = data[`author_condition_${i}`] || '0';
                const conditionValue = data[`author_condition_value_${i}`] || '';
                const firstName = data[`author_firstname_${i}`] || '';
                const lastName = data[`author_lastname_${i}`] || '';
                const middleName = data[`author_middlename_${i}`] || '';

                let authorData = {
                    condition,
                    conditionValue,
                    firstName,
                    middleName,
                    lastName
                };

                // Determine type and display based on condition
                if (condition === '1') {
                    authorData.type = 'anonymous';
                    authorData.display = bibLanguage === 'th' ? 'ไม่ปรากฏชื่อผู้แต่ง' : 'Anonymous';
                } else if (condition === '2' && conditionValue) {
                    authorData.type = 'pseudonym';
                    authorData.display = conditionValue;
                } else if (condition === '7' && conditionValue) {
                    authorData.type = 'organization';
                    authorData.display = conditionValue;
                } else if (condition === '3' && (firstName || lastName)) {
                    authorData.type = 'royal';
                    authorData.display = conditionValue ? `${conditionValue}${firstName} ${lastName}`.trim() : `${firstName} ${lastName}`.trim();
                } else if (condition === '4' && (firstName || lastName)) {
                    authorData.type = 'noble';
                    authorData.display = conditionValue ? `${conditionValue}${firstName} ${lastName}`.trim() : `${firstName} ${lastName}`.trim();
                } else if (condition === '5' && (firstName || lastName)) {
                    authorData.type = 'monk';
                    authorData.display = `${firstName} ${lastName}`.trim();
                } else if (condition === '6' && (firstName || lastName)) {
                    authorData.type = 'editor';
                    authorData.display = `${firstName} ${lastName}`.trim();
                } else {
                    authorData.type = 'normal';
                    authorData.display = `${firstName} ${lastName}`.trim();
                }

                // Only add if has any data
                if (authorData.display || firstName || lastName) {
                    data.authors.push(authorData);
                }
            }

            // Get preview content
            data.citation_parenthetical = document.getElementById('preview-parenthetical').innerText;
            data.citation_narrative = document.getElementById('preview-narrative').innerText;
            data.bibliography_text = document.getElementById('preview-bibliography').innerHTML;

            console.log('PHP Login Status: <?php echo isLoggedIn() ? "Logged In" : "Guest"; ?>');
            <?php if (!isLoggedIn()): ?>
                console.log('Executing Guest redirect logic...');
                // Guest - check if bibliography is valid (not empty or placeholder)
                const bibText = data.bibliography_text.trim();
                const isPlaceholder = bibText === '' ||
                    bibText.includes('รายการบรรณานุกรมจะแสดงที่นี่') ||
                    bibText.includes('Bibliography will appear here');

                if (isPlaceholder) {
                    Toast.error('<?php echo $currentLang === 'th' ? 'กรุณากรอกข้อมูลให้ครบก่อนบันทึก' : 'Please fill in all required fields'; ?>');
                    return;
                }

                // Redirect to summary page with form POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo SITE_URL; ?>/summary.php';
                const fields = {
                    'citation_parenthetical': data.citation_parenthetical,
                    'citation_narrative': data.citation_narrative,
                    'bibliography_text': bibText
                };

                for (const [name, value] of Object.entries(fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                }

                // Show loading transition
                const overlay = document.getElementById('loading-overlay');
                const progressBar = document.getElementById('loading-progress-bar');
                overlay.classList.add('active');
                setTimeout(() => progressBar.style.width = '100%', 50);

                setTimeout(() => {
                    document.body.appendChild(form);
                    form.submit();
                }, 1000);
                return;
            <?php endif; ?>

            setLoading(btn, true);

            try {
                const response = await API.post('<?php echo SITE_URL; ?>/api/bibliography/create.php', data);

                if (response.success) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo SITE_URL; ?>/summary.php';

                    const fields = {
                        'bib_id': response.data.id,
                        'citation_parenthetical': data.citation_parenthetical,
                        'citation_narrative': data.citation_narrative,
                        'bibliography_text': data.bibliography_text
                    };

                    for (const [name, value] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        form.appendChild(input);
                    }

                    // Show loading transition
                    const overlay = document.getElementById('loading-overlay');
                    const progressBar = document.getElementById('loading-progress-bar');
                    overlay.classList.add('active');
                    setTimeout(() => progressBar.style.width = '100%', 50);

                    setTimeout(() => {
                        document.body.appendChild(form);
                        form.submit();
                    }, 1000);
                } else {
                    Toast.error(response.error || '<?php echo addslashes(__('error_save')); ?>');
                }
            } catch (error) {
                console.error('API Error:', error);
                Toast.error(error.message || '<?php echo addslashes(__('error_save')); ?>');
            } finally {
                setLoading(btn, false);
            }
        } catch (err) {
            console.error('Form error:', err);
            Toast.error('เกิดข้อผิดพลาด: ' + (err.message || err));
        }
    });

    // Initialize edit mode if data exists
    window.addEventListener('load', function() {
        if (typeof EDIT_DATA !== 'undefined' && EDIT_DATA) {
            console.log('Initializing Edit Mode:', EDIT_DATA);

            // 1. Select Resource
            const card = document.querySelector(`.resource-card[data-id="${EDIT_DATA.resource_type_id}"]`);
            if (card) {
                selectResource(card);

                // 2. Set Language
                setBibLang(EDIT_DATA.language);

                // 3. Populate Authors
                const authors = EDIT_DATA.data.authors || [];
                if (authors.length > 0) {
                    authorCount = authors.length;
                    renderAuthors();
                    authors.forEach((author, idx) => {
                        const i = idx + 1;
                        const conditionSelect = document.querySelector(`select[name="author_condition_${i}"]`);
                        if (conditionSelect) {
                            conditionSelect.value = author.condition || '0';
                            onAuthorConditionChange(i, author.condition || '0');
                        }

                        const conditionInput = document.getElementById(`condition-input-${i}`);
                        if (conditionInput) conditionInput.value = author.conditionValue || author.display || '';

                        const firstInput = document.querySelector(`input[name="author_firstname_${i}"]`);
                        if (firstInput) firstInput.value = author.firstName || author.first || '';

                        const middleInput = document.querySelector(`input[name="author_middlename_${i}"]`);
                        if (middleInput) middleInput.value = author.middleName || author.middle || '';

                        const lastInput = document.querySelector(`input[name="author_lastname_${i}"]`);
                        if (lastInput) lastInput.value = author.lastName || author.last || '';
                    });
                }

                // 4. Populate Dynamic Fields with improved logic
                setTimeout(() => {
                    populateEditData();
                }, 800);

                // Secondary pass for any missed fields
                setTimeout(() => {
                    populateEditData();
                    updatePreview();

                    // After updatePreview, check if stored text differs from generated text
                    // to preserve manual edits from the database
                    if (EDIT_DATA.bibliography_text) {
                        const generatedBib = document.getElementById('preview-bibliography').innerHTML;
                        const storedBib = EDIT_DATA.bibliography_text;
                        // Clean both for comparison (remove placeholders/spaces)
                        if (storedBib && storedBib.trim() !== generatedBib.trim()) {
                            document.getElementById('preview-bibliography').innerHTML = storedBib;
                            markManualEdit('bibliography');
                        }
                    }

                    if (EDIT_DATA.citation_parenthetical) {
                        const generatedParen = document.getElementById('preview-parenthetical').innerText;
                        const storedParen = EDIT_DATA.citation_parenthetical;
                        if (storedParen && storedParen.trim() !== generatedParen.trim()) {
                            document.getElementById('preview-parenthetical').innerText = storedParen;
                            markManualEdit('parenthetical');
                        }
                    }

                    if (EDIT_DATA.citation_narrative) {
                        const generatedNarr = document.getElementById('preview-narrative').innerText;
                        const storedNarr = EDIT_DATA.citation_narrative;
                        if (storedNarr && storedNarr.trim() !== generatedNarr.trim()) {
                            document.getElementById('preview-narrative').innerText = storedNarr;
                            markManualEdit('narrative');
                        }
                    }

                    console.log('Edit mode data population complete');
                }, 1500);
            }
        }
    });

    // Improved function to populate edit data
    function populateEditData() {
        if (!EDIT_DATA || !EDIT_DATA.data) return;

        const data = EDIT_DATA.data;
        console.log('Populating fields with data:', data);

        for (const [key, value] of Object.entries(data)) {
            if (key === 'authors' || !value) continue;

            let field = null;

            // Strategy 1: Try field-[key] ID
            field = document.getElementById('field-' + key);

            // Strategy 2: Try [key] name attribute
            if (!field) {
                field = document.querySelector(`[name="${key}"]`);
            }

            // Strategy 3: Try data-field attribute
            if (!field) {
                field = document.querySelector(`[data-field="${key}"]`);
            }

            // Strategy 4: Try input/select/textarea with matching ID
            if (!field) {
                field = document.querySelector(`input#${key}, select#${key}, textarea#${key}`);
            }

            if (field && value) {
                // Set the value
                field.value = value;

                // Trigger events for proper reactivity
                field.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                if (field.tagName === 'SELECT') {
                    field.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                // Visual feedback - flash effect
                field.style.transition = 'background-color 0.3s ease';
                field.style.backgroundColor = '#EDE9FE';
                setTimeout(() => {
                    field.style.backgroundColor = '';
                }, 1000);

                console.log(`✓ Populated field ${key}:`, value);
            } else if (value) {
                console.log(`✗ Field not found for ${key}:`, value);
            }
        }
    }

    function showPublisherHelp() {
        const isTh = bibLanguage === 'th';
        const title = isTh ? 'วิธีเขียนชื่อสำนักพิมพ์ (APA 7<sup>th</sup>)' : 'Publisher Citation Guide (APA 7<sup>th</sup>)';
        const content = `
            <div class="help-modal-content">
                <p class="mb-4"><b>${isTh ? 'หลักการพื้นฐาน:' : 'Basic Principles:'}</b></p>
                <ul class="list-disc pl-5 space-y-2 mb-4 text-sm">
                    <li>${isTh ? '<b>ระบุชื่อเต็ม:</b> เขียนตามที่ปรากฏในหน้าปกใน' : '<b>Full Name:</b> Write as it appears on the title page.'}</li>
                    <li>${isTh ? '<b>ไม่ต้องใส่สถานที่:</b> APA 7<sup>th</sup> ไม่ต้องใส่เมืองหรือประเทศ' : '<b>No Location:</b> APA 7<sup>th</sup> no longer requires city/country.'}</li>
                    <li>${isTh ? '<b>ตัดคำธุรกิจ:</b> ตัดคำว่า "Co.", "Ltd.", "Inc." ออก' : '<b>Omit Business Types:</b> Remove "Co.", "Ltd.", "Inc."'}</li>
                    <li>${isTh ? '<b>คงคำว่า Press/Books:</b> ถ้าเป็นส่วนหนึ่งของชื่อ เช่น Oxford University Press' : '<b>Keep "Press/Books":</b> If part of core name, e.g., MIT Press'}</li>
                    <li>${isTh ? '<b>หากซ้ำกับชื่อผู้แต่ง:</b> ไม่ต้องใส่ชื่อสำนักพิมพ์ซ้ำ' : '<b>If Same as Author:</b> Do not repeat the name in publisher field.'}</li>
                </ul>
                <div class="p-3 bg-gray-50 rounded-lg mb-4">
                    <p class="text-xs font-bold text-primary mb-1 uppercase">${isTh ? 'ตัวอย่างที่ถูกต้อง' : 'CORRECT EXAMPLES'}</p>
                    <p class="text-sm">Chulalongkorn University Press <span class="text-gray-400">/</span> Pearson <span class="text-gray-400">/</span> นานมีบุ๊คส์</p>
                </div>
                <div class="text-center mt-6">
                    <a href="help-publisher.php" target="_blank" class="text-primary font-bold text-sm">
                        <i class="fas fa-external-link-alt mr-1"></i> ${isTh ? 'ดูรายละเอียดและตัวอย่างเพิ่มเติม' : 'View full guide and more examples'}
                    </a>
                </div>
            </div>
        `;

        Modal.create({
            title: title,
            icon: 'fas fa-building',
            content: content,
            footer: `<button class="btn btn-primary" onclick="Modal.close(this)">${isTh ? 'ตกลง' : 'Got it'}</button>`
        });
    }
</script>


<?php require_once 'includes/footer.php'; ?>