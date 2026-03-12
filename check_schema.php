<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE transactions");
echo json_encode($stmt->fetchAll());
?>
