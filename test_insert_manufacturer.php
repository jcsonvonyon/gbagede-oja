<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Test Manufacturer ' . time();
$_POST['contact_person'] = 'John Doe';
$_POST['phone'] = '1234567890';
$_POST['email'] = 'test@example.com';
$_POST['address'] = '123 Test St';
$_POST['status'] = 'Active';

// We bypass auth for testing the direct logic
require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO manufacturers (name, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['contact_person'], 
        $_POST['phone'], 
        $_POST['email'], 
        $_POST['address'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted manufacturer ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
