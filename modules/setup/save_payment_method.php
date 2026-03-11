<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $method_name = trim($_POST['method_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($method_name)) {
        header("Location: ../../dashboard.php?page=payment_methods&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE payment_methods SET method_name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$method_name, $description, $status, $id]);
            $msg = "method_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, description, status) VALUES (?, ?, ?)");
            $stmt->execute([$method_name, $description, $status]);
            $msg = "method_created";
        }
        header("Location: ../../dashboard.php?page=payment_methods&success=" . $msg);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: ../../dashboard.php?page=payment_methods&error=duplicate_name");
        } else {
            header("Location: ../../dashboard.php?page=payment_methods&error=db_error");
        }
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if method is in use by transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE payment_method_id = ?");
    try {
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../../dashboard.php?page=payment_methods&error=method_in_use");
            exit();
        }
    } catch (PDOException $e) {
        // Table might not exist yet or column might not exist, proceed with caution or ignore check if schema doesn't support it
    }
    
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../../dashboard.php?page=payment_methods&success=method_deleted");
} else {
    header("Location: ../../dashboard.php?page=payment_methods");
}
