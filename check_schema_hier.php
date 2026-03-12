<?php
require_once 'includes/db.php';
echo "--- categories ---\n";
$stmt = $pdo->query("DESCRIBE categories");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n--- product_groups ---\n";
$stmt = $pdo->query("DESCRIBE product_groups");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n--- products ---\n";
$stmt = $pdo->query("DESCRIBE products");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
