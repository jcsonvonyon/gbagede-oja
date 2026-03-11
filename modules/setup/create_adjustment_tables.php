<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // 1. Create inventory_adjustments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_adjustments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        adjustment_date DATE NOT NULL,
        reason VARCHAR(100) NOT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (adjustment_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create inventory_adjustment_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_adjustment_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        adjustment_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        current_stock DECIMAL(15,2) NOT NULL,
        new_stock DECIMAL(15,2) NOT NULL,
        change_amount DECIMAL(15,2) NOT NULL,
        INDEX (adjustment_id),
        INDEX (product_id),
        FOREIGN KEY (adjustment_id) REFERENCES inventory_adjustments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Inventory adjustment tables created successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
