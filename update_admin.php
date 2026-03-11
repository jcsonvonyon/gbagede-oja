<?php
require_once 'includes/db.php';

try {
    $new_hash = '$2y$10$6BF/ayMuLY7QZDlCI0jVG.XxG0Ogo447emq508SHZPDr6SRyAkzDy';
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$new_hash]);
    
    echo "Admin password updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
