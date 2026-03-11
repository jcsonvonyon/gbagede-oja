<?php
require_once 'includes/db.php';

try {
    // 1. Create Expenses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `expenses` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL,
        `category` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `expense_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `notes` TEXT NULL,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Create Payments table (for tracking partial payments or specific payment records)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `transaction_id` INT UNSIGNED NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `method` ENUM('Cash', 'Transfer', 'POS', 'Other') DEFAULT 'Cash',
        `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `notes` TEXT NULL,
        FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Financial tables created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
