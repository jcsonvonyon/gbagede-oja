-- Inventory Management System - Final Consolidated Schema
SET FOREIGN_KEY_CHECKS = 0;

-- 1. User Roles
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `user_roles` (id, role_name, description) VALUES
(1, 'Admin', 'Full system control and user management'),
(2, 'Manager', 'Management of inventory and reports'),
(3, 'Accountant', 'Focus on financial reports and transactions'),
(4, 'Cashier', 'Sales and basic transaction entry');

-- 2. Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `last_login` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `user_roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial admin (password: admin123)
INSERT IGNORE INTO `users` (username, password_hash, full_name, role_id) VALUES
('admin', '$2y$10$N7YDmADmFzCYHq3y6o3GrexSvzr9T3wCDXQJJO0A5.OkfPQfj4jqm', 'Super Admin', 1);

-- 3. Categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `purchase_price` DECIMAL(15,2) DEFAULT 0.00,
  `sale_price` DECIMAL(15,2) DEFAULT 0.00,
  `current_stock` DECIMAL(15,2) DEFAULT 0.00,
  `min_stock` DECIMAL(15,2) DEFAULT 5.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('Sale', 'Stock-In', 'Stock-Out', 'Adjustment') NOT NULL,
  `total_amount` DECIMAL(15,2) DEFAULT 0.00,
  `transaction_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Transaction Items
CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` DECIMAL(15,2) NOT NULL,
  `unit_price` DECIMAL(15,2) NOT NULL,
  `subtotal` DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Company
CREATE TABLE IF NOT EXISTS `company` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(255) NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'NGN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
