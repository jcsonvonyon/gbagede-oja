<?php
require 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'vendors'");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tables)) {
    echo "No vendors table found.\n";
} else {
    $stmt = $pdo->query("DESCRIBE vendors");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
