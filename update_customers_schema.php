<?php
require_once 'includes/db.php';

try {
    echo "Starting Customers table migration update...\n";
    $pdo->exec("ALTER TABLE customers 
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER address;
    ");
    echo "SUCCESS: Customers table updated successfully with status column!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Customers table already has the status column.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
