<?php
$_POST['payload'] = json_encode([
  "template" => "academic_general",
  "format" => "docx",
  "projectId" => null,
  "coverData" => [
    "title" => "Title"
  ]
]);
require('export-test.php');
