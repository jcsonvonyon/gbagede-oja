<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID missing.']);
    exit();
}

try {
    // Fetch and then delete to "recall"
    $stmt = $pdo->prepare("SELECT * FROM held_transactions WHERE id = ? AND held_by = ?");
    $stmt->execute([$id, $user_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        $del = $pdo->prepare("DELETE FROM held_transactions WHERE id = ?");
        $del->execute([$id]);
        echo json_encode(['success' => true, 'data' => $transaction]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found or unauthorized.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
