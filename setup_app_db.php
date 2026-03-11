<?php
require_once 'includes/db.php';

try {
    // 1. Setup Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS `customers` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `email` VARCHAR(255) NULL,
        `address` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `vendors` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `contact_person` VARCHAR(255) NULL,
        `phone` VARCHAR(50) NULL,
        `email` VARCHAR(255) NULL,
        `address` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `manufacturers` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `sales_reps` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `email` VARCHAR(255) NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_methods` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `method_name` VARCHAR(100) NOT NULL UNIQUE,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Financial & Purchase Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS `expenses` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL,
        `category` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `expense_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `notes` TEXT NULL,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `transaction_id` INT UNSIGNED NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `method` VARCHAR(50) DEFAULT 'Cash',
        `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `notes` TEXT NULL,
        FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "All database tables created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
