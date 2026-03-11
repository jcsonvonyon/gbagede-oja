<?php
require 'includes/db.php';
$stmt = $pdo->query("DESCRIBE transactions");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
