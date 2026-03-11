<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // Drop the table to ensure a clean slate with the correct columns
    $pdo->exec("DROP TABLE IF EXISTS payments");
    
    $sql = "CREATE TABLE payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        entity_type ENUM('Customer', 'Vendor') NOT NULL,
        entity_id INT UNSIGNED NOT NULL,
        payment_type ENUM('Receipt', 'Payment', 'Refund', 'Credit Settlement') NOT NULL,
        amount DECIMAL(15,0) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_date DATE NOT NULL,
        reference_no VARCHAR(100) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (payment_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Payments table recreated successfully with all required columns!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
