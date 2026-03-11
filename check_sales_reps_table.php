<?php
require 'includes/db.php';
$stmt = $pdo->query('DESCRIBE sales_reps');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
