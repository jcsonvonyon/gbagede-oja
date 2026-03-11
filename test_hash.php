<?php
$hash = '$2y$10$N7YDmADmFzCYHq3y6o3GrexSvzr9T3wCDXQJJO0A5.OkfPQfj4jqm';
$password = 'admin123';
if (password_verify($password, $hash)) {
    echo "Hash matches admin123\n";
} else {
    echo "Hash does NOT match admin123\n";
}

$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "New hash for admin123: " . $new_hash . "\n";
?>
