<?php
// Simple runner to produce a sample research-report.docx using the export processor
$_POST['payload'] = json_encode([
    'template' => 'research',
    'format' => 'docx',
    'coverData' => [
        'title' => 'ตัวอย่างรายงานการวิจัย',
        'authors' => 'นักศึกษา ตัวอย่าง',
        'studentIds' => '64000001',
        'department' => 'ภาควิชาตัวอย่าง',
        'institution' => 'มหาวิทยาลัยตัวอย่าง',
        'semester' => '1',
        'year' => '2566'
    ]
]);

include __DIR__ . '/../api/template/export-report-research.php';
