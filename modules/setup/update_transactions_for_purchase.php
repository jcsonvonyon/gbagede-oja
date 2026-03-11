<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // 1. Update type enum
    $pdo->exec("ALTER TABLE transactions MODIFY COLUMN type ENUM('Sale', 'Stock-In', 'Stock-Out', 'Adjustment', 'Purchase') NOT NULL");

    // 2. Add reference_no if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'reference_no'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN reference_no VARCHAR(100) NULL AFTER notes");
    }

    // 3. Add amount_paid if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'amount_paid'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN amount_paid DECIMAL(15,2) DEFAULT 0.00 AFTER total_amount");
    }

    echo "Transactions table updated successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
