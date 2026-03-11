<?php
require 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'units'");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tables)) {
    echo "No units table found.\n";
} else {
    $stmt = $pdo->query("DESCRIBE units");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
