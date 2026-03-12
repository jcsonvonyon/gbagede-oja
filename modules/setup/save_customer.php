<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requirePermission('customers', 'edit');
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $customer_type = trim($_POST['customer_type'] ?? 'Retail');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $credit_limit = $_POST['credit_limit'] ?? 0.00;
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=customers&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, customer_type = ?, phone = ?, email = ?, credit_limit = ?, address = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $customer_type, $phone, $email, $credit_limit, $address, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'CUSTOMERS', "Updated customer: $name");
            
            $msg = "customer_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO customers (name, customer_type, phone, email, credit_limit, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $customer_type, $phone, $email, $credit_limit, $address, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'CUSTOMERS', "Created new customer: $name");
            
            $msg = "customer_created";
        }
        header("Location: ../../dashboard.php?page=customers&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=customers&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch customer name before deletion
    $stmt = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer_name = $stmt->fetchColumn() ?: 'Unknown';
    
    // Check if customer is in use by sales/transactions
    $can_delete = true;
    try {
         $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE customer_id = ?");
         $stmt->execute([$id]);
         if ($stmt->fetchColumn() > 0) {
             $can_delete = false;
         }
    } catch (PDOException $e) {
        // 'sales' table might not exist yet
    }

    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'CUSTOMERS', "Deleted customer: $customer_name (ID: $id)");
        
        header("Location: ../../dashboard.php?page=customers&success=customer_deleted");
    } else {
        header("Location: ../../dashboard.php?page=customers&error=customer_in_use");
    }
} else {
    header("Location: ../../dashboard.php?page=customers");
}
