<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE manufacturers");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['Field'] === 'id') {
        print_r($row);
    }
}
?>
