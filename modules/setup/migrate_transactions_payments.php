<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // Check and add columns individually to be safe
    $stmt = $pdo->query("DESCRIBE transactions");
    $existing_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('amount_paid', $existing_cols)) {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN amount_paid DECIMAL(15,2) DEFAULT 0 AFTER total_amount");
        echo "Added column: amount_paid\n";
    }
    
    if (!in_array('balance_amount', $existing_cols)) {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN balance_amount DECIMAL(15,2) DEFAULT 0 AFTER amount_paid");
        echo "Added column: balance_amount\n";
    }
    
    if (!in_array('payment_details', $existing_cols)) {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN payment_details TEXT NULL AFTER payment_method");
        echo "Added column: payment_details\n";
    }
    
    echo "Migration successful: Essential payment columns are present in transactions table.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
