<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Test Pieces';
$_POST['abbreviation'] = 't-pcs';
$_POST['status'] = 'Active';

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation, status) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['abbreviation'], 
        $_POST['status']
    ]);
    $unit_id = $pdo->lastInsertId();
    echo "SUCCESS: Inserted unit ID $unit_id\n";
    
    // Now test product insertion with this unit
    $_POST['name'] = 'Test Product with Unit';
    $_POST['category_id'] = 1; // beverages test cat
    $_POST['unit_id'] = $unit_id;
    $_POST['purchase_price'] = 100;
    $_POST['sale_price'] = 150;
    $_POST['current_stock'] = 20;
    $_POST['min_stock'] = 5;
    
    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, unit_id, purchase_price, sale_price, current_stock, min_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['category_id'], 
        $_POST['unit_id'], 
        $_POST['purchase_price'], 
        $_POST['sale_price'], 
        $_POST['current_stock'], 
        $_POST['min_stock']
    ]);
    echo "SUCCESS: Inserted product ID " . $pdo->lastInsertId() . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
