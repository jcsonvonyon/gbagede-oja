<?php
require_once 'includes/db.php';
echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Timezone: " . date_default_timezone_get() . "\n";
$stmt = $pdo->query("SELECT NOW() as now");
echo "DB Time: " . $stmt->fetch()['now'] . "\n";
?>
