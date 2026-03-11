<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Alice Smith (Test Customer)';
$_POST['phone'] = '080 3333 4444';
$_POST['email'] = 'alice@example.com';
$_POST['address'] = '789 Client Avenue';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['phone'], 
        $_POST['email'], 
        $_POST['address'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted customer ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
