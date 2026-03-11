<?php
require_once 'includes/db.php';

try {
    echo "Starting Customers table migration update for Type and Credit Limit...\n";
    $pdo->exec("ALTER TABLE customers 
        ADD COLUMN customer_type VARCHAR(50) DEFAULT 'Retail' AFTER name,
        ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0.00 AFTER email;
    ");
    echo "SUCCESS: Customers table updated successfully!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Customers table already has these columns.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
