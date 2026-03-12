<?php
require_once 'includes/db.php';
echo "--- groups ---\n";
print_r($pdo->query('SELECT id, name FROM product_groups')->fetchAll(PDO::FETCH_ASSOC));
echo "\n--- categories (subgroups) ---\n";
print_r($pdo->query('SELECT id, name, group_id, status FROM categories')->fetchAll(PDO::FETCH_ASSOC));
?>
