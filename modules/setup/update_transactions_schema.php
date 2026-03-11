<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $pdo->exec("ALTER TABLE transactions ADD COLUMN payment_method VARCHAR(50) DEFAULT 'CASH' AFTER total_amount");
    echo "Added payment_method column to transactions table!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column payment_method already exists.\n";
    } else {
        die("Error: " . $e->getMessage() . "\n");
    }
}
?>
