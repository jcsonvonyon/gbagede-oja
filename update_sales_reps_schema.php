<?php
require_once 'includes/db.php';

try {
    echo "Starting Sales Reps table migration update...\n";
    $pdo->exec("ALTER TABLE sales_reps 
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER email;
    ");
    echo "SUCCESS: Sales Reps table updated successfully with status column!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Sales Reps table already has the status column.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
