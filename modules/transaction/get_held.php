<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT h.*, c.name as customer_name 
                          FROM held_transactions h 
                          LEFT JOIN customers c ON h.customer_id = c.id 
                          WHERE h.held_by = ? 
                          ORDER BY h.created_at DESC");
    $stmt->execute([$user_id]);
    $held = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $held]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
