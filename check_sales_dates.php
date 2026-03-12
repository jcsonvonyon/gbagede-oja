<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, transaction_date, total_amount FROM transactions WHERE type = 'Sale'");
echo json_encode($stmt->fetchAll());
?>
