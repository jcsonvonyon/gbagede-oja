<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
    $cart_data = $_POST['cart_data'] ?? '';
    $discount = $_POST['discount'] ?? 0;
    $user_id = $_SESSION['user_id'];

    if (empty($cart_data) || $cart_data === '[]') {
        echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO held_transactions (customer_id, cart_data, discount, held_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_id, $cart_data, $discount, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Transaction held successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
