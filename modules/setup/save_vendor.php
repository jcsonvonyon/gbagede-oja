<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('vendors', 'edit');
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=vendors&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE vendors SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'VENDORS', "Updated vendor: $name");
            
            $msg = "vendor_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO vendors (name, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'VENDORS', "Created new vendor: $name");
            
            $msg = "vendor_created";
        }
        header("Location: ../../dashboard.php?page=vendors&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=vendors&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch vendor name before deletion
    $stmt = $pdo->prepare("SELECT name FROM vendors WHERE id = ?");
    $stmt->execute([$id]);
    $vendor_name = $stmt->fetchColumn() ?: 'Unknown';

    // Check if vendor is in use by products/purchases
    // For now we assume 'purchases' table might exist, if not it just ignores and deletes
    // If you have a specific purchases table, adjust verify query below:
    $can_delete = true;
    try {
         $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE vendor_id = ?");
         $stmt->execute([$id]);
         if ($stmt->fetchColumn() > 0) {
             $can_delete = false;
         }
    } catch (PDOException $e) {
        // 'purchases' table might not exist yet
    }

    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM vendors WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'VENDORS', "Deleted vendor: $vendor_name (ID: $id)");
        
        header("Location: ../../dashboard.php?page=vendors&success=vendor_deleted");
    } else {
        header("Location: ../../dashboard.php?page=vendors&error=vendor_in_use");
    }
} else {
    header("Location: ../../dashboard.php?page=vendors");
}
