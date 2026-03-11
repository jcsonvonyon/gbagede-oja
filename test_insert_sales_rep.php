<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Jane Doe (Test)';
$_POST['phone'] = '080 1234 5678';
$_POST['email'] = 'jane.doe@company.com';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO sales_reps (name, phone, email, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['phone'], 
        $_POST['email'], 
        $_POST['status']
    ]);
    echo "SUCCESS: Inserted sales rep ID " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
