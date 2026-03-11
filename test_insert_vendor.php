<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Global Supplies Co. (Test)';
$_POST['contact_person'] = 'Sarah Smith';
$_POST['phone'] = '080 9876 5432';
$_POST['email'] = 'sarah.smith@globalsupplies.com';
$_POST['address'] = '456 Warehouse Blvd';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO vendors (name, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['contact_person'], 
        $_POST['phone'], 
        $_POST['email'], 
        $_POST['address'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted vendor ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
