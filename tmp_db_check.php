<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=babybib_db', 'root', '');
    $stmt = $db->query("DESCRIBE email_verifications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (Exception $e) {
    echo $e->getMessage();
}
