<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=sales_reps&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE sales_reps SET name = ?, phone = ?, email = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'SALES_REPS', "Updated sales rep: $name");
            
            $msg = "rep_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO sales_reps (name, phone, email, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'SALES_REPS', "Created new sales rep: $name");
            
            $msg = "rep_created";
        }
        header("Location: ../../dashboard.php?page=sales_reps&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=sales_reps&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch sales rep name before deletion
    $stmt = $pdo->prepare("SELECT name FROM sales_reps WHERE id = ?");
    $stmt->execute([$id]);
    $rep_name = $stmt->fetchColumn() ?: 'Unknown';
    
    // Check if sales rep is in use by transactions/orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE sales_rep_id = ?");
    try {
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../../dashboard.php?page=sales_reps&error=rep_in_use");
            exit();
        }
    } catch (PDOException $e) {
        // Table or column might not exist yet, we can safely ignore or proceed
    }
    
    $stmt = $pdo->prepare("DELETE FROM sales_reps WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'SALES_REPS', "Deleted sales rep: $rep_name (ID: $id)");
    
    header("Location: ../../dashboard.php?page=sales_reps&success=rep_deleted");
} else {
    header("Location: ../../dashboard.php?page=sales_reps");
}
