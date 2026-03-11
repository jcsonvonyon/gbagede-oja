<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Beverages';
$_POST['description'] = 'Soft drinks, juices, and water products.';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO product_groups (name, description, status) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['description'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted group ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
