<?php
require 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'products'");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tables)) {
    echo "No products table found.\n";
} else {
    $stmt = $pdo->query("DESCRIBE products");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
