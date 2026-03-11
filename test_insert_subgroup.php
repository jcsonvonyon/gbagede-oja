<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Bottled Water (Sub-group Test)';
$_POST['group_id'] = 1; // beverages from earlier test
$_POST['description'] = 'Still and Sparkling bottled water.';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO categories (group_id, name, description, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['group_id'],
        $_POST['name'], 
        $_POST['description'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted sub-group ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
