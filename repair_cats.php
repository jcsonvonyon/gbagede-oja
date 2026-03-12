<?php
require_once 'includes/db.php';
$stmt = $pdo->prepare("UPDATE categories SET status = 'Active' WHERE status IS NULL OR status = ''");
$stmt->execute();
echo "Categories repaired: " . $stmt->rowCount();
?>
