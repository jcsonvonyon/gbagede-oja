<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // 1. Create inventory_transfers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_transfers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        from_branch_id INT UNSIGNED NOT NULL,
        to_branch_id INT UNSIGNED NOT NULL,
        transfer_date DATE NOT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (from_branch_id),
        INDEX (to_branch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create inventory_transfer_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_transfer_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        transfer_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity DECIMAL(15,2) NOT NULL,
        INDEX (transfer_id),
        INDEX (product_id),
        FOREIGN KEY (transfer_id) REFERENCES inventory_transfers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Inventory transfer tables created successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
