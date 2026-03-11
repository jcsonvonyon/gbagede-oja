<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Wholesale Hub (Test)';
$_POST['customer_type'] = 'Wholesale';
$_POST['phone'] = '090 1234 5678';
$_POST['email'] = 'wholesale@hub.com';
$_POST['credit_limit'] = 500000.00;
$_POST['address'] = '101 Bulk Avenue';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO customers (name, customer_type, phone, email, credit_limit, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['customer_type'], 
        $_POST['phone'], 
        $_POST['email'], 
        $_POST['credit_limit'], 
        $_POST['address'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted customer ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
