<?php
require 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'customers'");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tables)) {
    echo "No customers table found.\n";
} else {
    $stmt = $pdo->query("DESCRIBE customers");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
