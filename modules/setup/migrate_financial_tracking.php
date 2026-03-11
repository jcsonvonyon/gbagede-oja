<?php
require_once 'includes/db.php';

try {
    echo "Starting migration...\n";
    
    // Add customer_id, vendor_id, and payment_status to transactions
    $sql = "ALTER TABLE transactions 
            ADD COLUMN customer_id INT UNSIGNED NULL AFTER user_id,
            ADD COLUMN vendor_id INT UNSIGNED NULL AFTER customer_id,
            ADD COLUMN payment_status ENUM('Pending', 'Partial', 'Paid') DEFAULT 'Paid' AFTER total_amount,
            ADD CONSTRAINT fk_transaction_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
            ADD CONSTRAINT fk_transaction_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL";
    
    $pdo->exec($sql);
    echo "Successfully updated transactions table.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
