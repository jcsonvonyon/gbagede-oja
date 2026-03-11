<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $table = $row[0];
    echo "--- Table: $table ---\n";
    $stmt2 = $pdo->query("DESCRIBE $table");
    while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row2['Field']} - {$row2['Type']}\n";
    }
}
?>
