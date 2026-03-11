<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // 1. Create purchase_orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        vendor_id INT UNSIGNED NOT NULL,
        order_date DATE NOT NULL,
        expected_delivery DATE NULL,
        reference_no VARCHAR(100) NULL,
        total_amount DECIMAL(15,2) DEFAULT 0.00,
        status ENUM('Draft', 'Sent', 'Received', 'Cancelled') DEFAULT 'Draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (vendor_id),
        INDEX (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create purchase_order_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        purchase_order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity DECIMAL(15,2) NOT NULL,
        expected_cost DECIMAL(15,2) NOT NULL,
        subtotal DECIMAL(15,2) NOT NULL,
        INDEX (purchase_order_id),
        INDEX (product_id),
        FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Purchase order tables created successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
