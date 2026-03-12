<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE sales_reps");
echo json_encode($stmt->fetchAll());
?>
