<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $pdo->exec("ALTER TABLE transactions ADD COLUMN payment_status ENUM('Paid', 'Partial', 'Unpaid') DEFAULT 'Paid' AFTER payment_method");
    echo "Added payment_status column to transactions table!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column payment_status already exists.\n";
    } else {
        die("Error: " . $e->getMessage() . "\n");
    }
}
?>
