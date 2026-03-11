<?php
require_once 'includes/db.php';

try {
    echo "Starting expenses migration...\n";
    
    // Drop the old expenses table if it exists (placeholder version)
    // $pdo->exec("DROP TABLE IF EXISTS expenses");
    
    // Create new expenses table
    $sql = "CREATE TABLE IF NOT EXISTS expenses (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        category VARCHAR(100) NOT NULL,
        amount DECIMAL(15,0) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        expense_date DATE NOT NULL,
        vendor_payee VARCHAR(200),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_expense_date (expense_date),
        INDEX idx_category (category)
    )";
    
    $pdo->exec($sql);
    echo "Successfully created/updated expenses table.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
