<?php
require_once 'includes/db.php';

echo "--- roles table contents ---\n";
$stmt = $pdo->query("SELECT * FROM roles");
while ($row = $stmt->fetch()) {
    print_r($row);
}

echo "\n--- user_roles table contents ---\n";
$stmt = $pdo->query("SELECT * FROM user_roles");
while ($row = $stmt->fetch()) {
    print_r($row);
}
?>
