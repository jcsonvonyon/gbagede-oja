<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions WHERE type = 'Sale'");
echo json_encode($stmt->fetch());
?>
