<?php
require_once 'includes/db.php';

$tables = ['products', 'transactions', 'transaction_items', 'purchase_orders', 'purchase_order_items', 'payment_methods', 'vendors'];

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "  Table does not exist.\n\n";
    }
}
