<?php

/**
 * Babybib - Project Bibliography Preview
 * =======================================
 * Realistic A4 paper preview with proper formatting
 */

$pageTitle = 'Preview บรรณานุกรม';

require_once '../includes/header.php';
require_once '../includes/navbar-user.php';

$userId = getCurrentUserId();
$projectId = intval($_GET['id'] ?? 0);
$format = sanitize($_GET['format'] ?? 'view'); // view, docx, pdf

if (!$projectId) {
    header('Location: projects.php');
    exit;
}

try {
    $db = getDB();

    // Get project info
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();

    if (!$project) {
        header('Location: projects.php');
        exit;
    }

    // Get bibliographies for this project - properly sorted
    $stmt = $db->prepare("
        SELECT b.*, rt.name_th, rt.name_en 
        FROM bibliographies b 
        JOIN resource_types rt ON b.resource_type_id = rt.id 
        WHERE b.project_id = ? 
        ORDER BY 
            CASE WHEN b.language = 'th' THEN 0 ELSE 1 END,
            b.author_sort_key ASC,
            b.year ASC,
            b.year_suffix ASC
    ");
    $stmt->execute([$projectId]);
    $bibliographies = $stmt->fetchAll();

    // Apply disambiguation for same author-year
    $bibliographies = applyDisambiguation($bibliographies);
} catch (Exception $e) {
    $project = null;
    $bibliographies = [];
}

/**
 * Apply disambiguation suffixes (ก,ข,ค for Thai / a,b,c for English)
 */
function applyDisambiguation($bibliographies)
{
    // Group by author_sort_key + year + language
    $groupMap = [];
    foreach ($bibliographies as $index => $bib) {
        $key = ($bib['author_sort_key'] ?? '') . '|' . ($bib['year'] ?? '') . '|' . ($bib['language'] ?? '');
        if (!empty($bib['year']) && $bib['year'] != '0') {
            $groupMap[$key][] = $index;
        }
    }

    // Apply suffixes to duplicates
    foreach ($groupMap as $key => $indices) {
        if (count($indices) > 1) {
            foreach ($indices as $position => $index) {
                $bib = &$bibliographies[$index];
                $text = $bib['bibliography_text'];
                $year = $bib['year'];
                $lang = $bib['language'];

                // Determine suffix
                if ($lang === 'th') {
                    $thaiSuffixes = ['ก', 'ข', 'ค', 'ง', 'จ', 'ฉ', 'ช', 'ซ', 'ฌ', 'ญ', 'ฎ', 'ฏ', 'ฐ', 'ฑ', 'ฒ', 'ณ', 'ด', 'ต', 'ถ', 'ท', 'ธ', 'น', 'บ', 'ป', 'ผ', 'ฝ', 'พ', 'ฟ', 'ภ', 'ม', 'ย', 'ร', 'ล', 'ว', 'ศ', 'ษ', 'ส', 'ห', 'ฬ', 'อ', 'ฮ'];
                    $suffix = $thaiSuffixes[$position] ?? '';
                } else {
                    $suffix = chr(ord('a') + $position);
                }

                if ($suffix && $year && $year != '0') {
                    // Remove existing suffix first
                    $text = preg_replace("/\({$year}[a-zก-ฮ]\)/u", "({$year})", $text);
                    // Apply new suffix
                    $search = '(' . $year . ')';
                    $replace = '(' . $year . $suffix . ')';
                    $text = str_replace($search, $replace, $text);
                    $bib['bibliography_text'] = $text;
                }
            }
        }
    }

    return $bibliographies;
}
?>

<style>
    /* === Preview Page Design === */
    .preview-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--space-6) var(--space-4);
        min-height: calc(100vh - 200px);
    }

    /* Header */
    .preview-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-6);
        flex-wrap: wrap;
        gap: var(--space-4);
    }

    .preview-header-left {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .btn-back {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-4);
        background: transparent;
        border: none;
        border-radius: var(--radius-lg);
        color: var(--text-secondary);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-back:hover {
        color: var(--primary);
        transform: translateX(-4px);
    }

    .preview-header-info h1 {
        font-size: var(--text-xl);
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .preview-header-info h1 .color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .preview-header-info p {
        font-size: var(--text-sm);
        color: var(--text-secondary);
        margin-top: 4px;
    }

    .preview-header-actions {
        display: flex;
        gap: var(--space-3);
    }

    .btn-export {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        border-radius: var(--radius-lg);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .btn-export.word {
        background: linear-gradient(135deg, #2b5797, #1e3f6f);
        color: white;
    }

    .btn-export.word:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(43, 87, 151, 0.4);
    }

    .btn-export.pdf {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .btn-export.pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    /* Paper Container */
    .paper-container {
        background: var(--gray-200);
        border-radius: 16px;
        padding: var(--space-8);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--space-6);
        min-height: 600px;
    }

    /* A4 Paper - Realistic */
    .paper-page {
        background: white;
        width: 210mm;
        max-width: 100%;
        min-height: 297mm;
        padding: 25.4mm 25.4mm 25.4mm 25.4mm;
        /* 1 inch margins */
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.12),
            0 1px 2px rgba(0, 0, 0, 0.24),
            0 10px 40px rgba(0, 0, 0, 0.1);
        border-radius: 2px;
        position: relative;
        /* Font settings matching actual documents */
        font-family: 'Angsana New', 'AngsanaUPC', 'TH Sarabun New', serif;
        font-size: 16px;
        line-height: 1.5;
        color: #000;
    }

    /* Page Number */
    .paper-page::after {
        content: attr(data-page);
        position: absolute;
        bottom: 15mm;
        right: 25.4mm;
        font-size: 12px;
        color: #666;
    }

    /* Title */
    .paper-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 24px;
        color: #000;
    }

    /* Bibliography Entries */
    .paper-entries {
        margin: 0;
        padding: 0;
    }

    .paper-entry {
        text-indent: -0.5in;
        margin-left: 0.5in;
        margin-bottom: 12px;
        font-size: 16px;
        line-height: 1.5;
        text-align: left;
        color: #000;
        word-wrap: break-word;
    }

    .paper-entry em,
    .paper-entry i {
        font-style: italic;
    }

    /* Empty State */
    .paper-empty {
        text-align: center;
        padding: 60px 40px;
        color: var(--text-tertiary);
    }

    .paper-empty i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    /* Page break indicator */
    .page-break-indicator {
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        max-width: 210mm;
        color: var(--text-tertiary);
        font-size: var(--text-sm);
    }

    .page-break-indicator::before,
    .page-break-indicator::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-light);
    }

    /* Responsive */
    @media (max-width: 900px) {
        .paper-page {
            width: 100%;
            min-height: auto;
            padding: 40px 30px;
            font-size: 14pt;
        }

        .paper-entry {
            text-indent: -24px;
            margin-left: 24px;
            font-size: 14pt;
        }

        .paper-title {
            font-size: 16pt;
        }
    }

    @media (max-width: 600px) {
        .preview-page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .preview-header-actions {
            justify-content: center;
        }

        .paper-container {
            padding: var(--space-4);
        }
    }

    /* Print Styles */
    @media print {

        .preview-page-header,
        .navbar {
            display: none !important;
        }

        .paper-container {
            background: none;
            padding: 0;
        }

        .paper-page {
            box-shadow: none;
            margin: 0;
            page-break-after: always;
        }
    }
</style>

<main class="preview-page-wrapper">
    <!-- Header -->
    <div class="preview-page-header">
        <div class="preview-header-left">
            <a href="projects.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <?php echo $currentLang === 'th' ? 'ย้อนกลับ' : 'Back'; ?>
            </a>
            <div class="preview-header-info">
                <h1>
                    <span class="color-dot" style="background: <?php echo htmlspecialchars($project['color'] ?? '#8B5CF6'); ?>;"></span>
                    <?php echo htmlspecialchars($project['name']); ?>
                </h1>
                <p>
                    <i class="fas fa-book"></i>
                    <?php echo count($bibliographies); ?> <?php echo $currentLang === 'th' ? 'รายการบรรณานุกรม' : 'bibliography entries'; ?>
                </p>
            </div>
        </div>

        <div class="preview-header-actions">
            <button class="btn-export word" onclick="downloadExport('docx')">
                <i class="fas fa-file-word"></i>
                <?php echo $currentLang === 'th' ? 'ดาวน์โหลด Word' : 'Download Word'; ?>
            </button>
            <button class="btn-export pdf" onclick="downloadExport('pdf')">
                <i class="fas fa-file-pdf"></i>
                <?php echo $currentLang === 'th' ? 'ดาวน์โหลด PDF' : 'Download PDF'; ?>
            </button>
        </div>
    </div>

    <!-- Paper Preview -->
    <div class="paper-container">
        <?php if (empty($bibliographies)): ?>
            <div class="paper-page">
                <div class="paper-empty">
                    <i class="fas fa-book-open"></i>
                    <h3><?php echo $currentLang === 'th' ? 'ไม่มีรายการบรรณานุกรม' : 'No Bibliography Entries'; ?></h3>
                    <p><?php echo $currentLang === 'th' ? 'โครงการนี้ยังไม่มีรายการบรรณานุกรม' : 'This project has no bibliography entries yet.'; ?></p>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Calculate entries per page (approximately 25 entries per page based on typical sizing)
            $entriesPerPage = 18;
            $totalEntries = count($bibliographies);
            $totalPages = ceil($totalEntries / $entriesPerPage);

            for ($pageNum = 1; $pageNum <= $totalPages; $pageNum++):
                $startIndex = ($pageNum - 1) * $entriesPerPage;
                $pageEntries = array_slice($bibliographies, $startIndex, $entriesPerPage);
            ?>
                <?php if ($pageNum > 1): ?>
                    <div class="page-break-indicator">
                        <?php echo $currentLang === 'th' ? 'หน้า ' : 'Page '; ?><?php echo $pageNum; ?>
                    </div>
                <?php endif; ?>

                <div class="paper-page" data-page="<?php echo $pageNum; ?>">
                    <?php if ($pageNum === 1): ?>
                        <div class="paper-title">
                            <?php echo $currentLang === 'th' ? 'บรรณานุกรม' : 'References'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="paper-entries">
                        <?php foreach ($pageEntries as $bib): ?>
                            <div class="paper-entry">
                                <?php echo $bib['bibliography_text']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    const projectId = <?php echo $projectId; ?>;
    const projectName = <?php echo json_encode($project['name']); ?>;

    async function downloadExport(format) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;

        // PDF: Open in new window for print preview
        if (format === 'pdf') {
            window.open(`<?php echo SITE_URL; ?>/api/export/project.php?id=${projectId}&format=pdf`, '_blank');
            return;
        }

        // DOCX: Download file
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $currentLang === 'th' ? 'กำลังสร้างไฟล์...' : 'Generating...'; ?>';
        btn.disabled = true;

        try {
            const response = await fetch(`<?php echo SITE_URL; ?>/api/export/project.php?id=${projectId}&format=${format}`);

            if (!response.ok) {
                throw new Error('Export failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${projectName}_bibliography.docx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

            Toast.success('<?php echo $currentLang === 'th' ? 'ดาวน์โหลดสำเร็จ!' : 'Download complete!'; ?>');
        } catch (error) {
            console.error('Export error:', error);
            Toast.error('<?php echo $currentLang === 'th' ? 'เกิดข้อผิดพลาดในการดาวน์โหลด' : 'Download failed'; ?>');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Keyboard shortcut: Escape to go back
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.location.href = 'projects.php';
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>