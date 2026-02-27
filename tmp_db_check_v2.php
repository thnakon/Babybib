<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=babybib_db', 'root', '');
    $stmt = $db->query("DESCRIBE email_verifications");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN); // Just get field names
    file_put_contents('tmp_columns.txt', implode("\n", $columns));
} catch (Exception $e) {
    file_put_contents('tmp_columns.txt', $e->getMessage());
}
