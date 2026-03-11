<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_type = $_POST['payment_type'] ?? '';
    $entity_type = $_POST['entity_type'] ?? '';
    $entity_id = $_POST['entity_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $method = $_POST['payment_method'] ?? 'CASH';
    $date = $_POST['payment_date'] ?? date('Y-m-d');
    $reference = $_POST['reference_no'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($payment_type) || empty($entity_id) || $amount <= 0) {
        die(json_encode(['success' => false, 'message' => 'Please fill all required fields.']));
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, entity_type, entity_id, payment_type, amount, payment_method, payment_date, reference_no, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $entity_type, $entity_id, $payment_type, $amount, $method, $date, $reference, $notes]);

        // Get Entity Name for logging
        $entity_name = "Unknown";
        if ($entity_type === 'Customer') {
            $e_stmt = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
            $e_stmt->execute([$entity_id]);
            $entity_name = $e_stmt->fetch()['name'] ?? 'Unknown';
        } else {
            $e_stmt = $pdo->prepare("SELECT name FROM vendors WHERE id = ?");
            $e_stmt->execute([$entity_id]);
            $entity_name = $e_stmt->fetch()['name'] ?? 'Unknown';
        }

        logActivity($pdo, $user_id, 'ADD', 'PAYMENTS', "Recorded payment: $payment_type - ₦$amount from/to $entity_name ($entity_type)");

        header('Location: ../../dashboard.php?page=payments&success=1');
        exit;
    } catch (PDOException $e) {
        die(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}
?>
