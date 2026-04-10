<?php
$_POST['payload'] = json_encode([
  "template" => "academic_general",
  "format" => "docx",
  "coverData" => [
    "title" => "Title"
  ]
]);
ob_start();
require('api/template/export-report.php');
$out = ob_get_clean();
file_put_contents('corrupted2.docx', $out);
