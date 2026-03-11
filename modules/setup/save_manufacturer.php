<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
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

    $logo_path = $_POST['existing_logo_path'] ?? null;
    
    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/img/brands/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'png'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'brand_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $target_file = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                $logo_path = 'assets/img/brands/' . $new_filename;
            }
        }
    }

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=manufacturers&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE manufacturers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, status = ?, logo_path = ? WHERE id = ?");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $status, $logo_path, $id]);
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'MANUFACTURERS', "Updated manufacturer: $name (ID: $id)");
            $msg = "manufacturer_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO manufacturers (name, contact_person, phone, email, address, status, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $status, $logo_path]);
            $new_manufacturer_id = $pdo->lastInsertId();
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'MANUFACTURERS', "Created new manufacturer: $name (ID: $new_manufacturer_id)");
            $msg = "manufacturer_created";
        }
        header("Location: ../../dashboard.php?page=manufacturers&success=" . $msg);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: ../../dashboard.php?page=manufacturers&error=duplicate_name");
        } else {
            header("Location: ../../dashboard.php?page=manufacturers&error=db_error");
        }
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch manufacturer name before deletion
    $stmt = $pdo->prepare("SELECT name FROM manufacturers WHERE id = ?");
    $stmt->execute([$id]);
    $m_name = $stmt->fetchColumn() ?: 'Unknown';
    
    // Check if manufacturer is in use by products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE manufacturer_id = ?");
    try {
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../../dashboard.php?page=manufacturers&error=manufacturer_in_use");
            exit();
        }
    } catch (PDOException $e) {
        // Table or column might not exist yet
    }
    
    $stmt = $pdo->prepare("DELETE FROM manufacturers WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'MANUFACTURERS', "Deleted manufacturer: $m_name (ID: $id)");
    
    header("Location: ../../dashboard.php?page=manufacturers&success=manufacturer_deleted");
} else {
    header("Location: ../../dashboard.php?page=manufacturers");
}
