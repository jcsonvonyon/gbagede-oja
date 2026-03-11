<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $method = $_POST['payment_method'] ?? 'CASH';
    $date = $_POST['expense_date'] ?? date('Y-m-d');
    $vendor = $_POST['vendor_payee'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($category) || $amount <= 0) {
        die(json_encode(['success' => false, 'message' => 'Please fill all required fields.']));
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category, amount, payment_method, expense_date, vendor_payee, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category, $amount, $method, $date, $vendor, $description]);

        logActivity($pdo, $user_id, 'ADD', 'EXPENSES', "Recorded expense: $category - ₦$amount ($vendor)");

        header('Location: ../../dashboard.php?page=expenses&success=1');
        exit;
    } catch (PDOException $e) {
        die(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}
?>
