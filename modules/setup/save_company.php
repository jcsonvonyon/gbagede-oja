<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currency = trim($_POST['currency'] ?? 'NGN');
    $receipt_footer = trim($_POST['receipt_footer'] ?? '');
    $rc_number = trim($_POST['rc_number'] ?? '');

    $stmt = $pdo->query("SELECT id, logo_path FROM company LIMIT 1");
    $company = $stmt->fetch();
    $logo_path = $company['logo_path'] ?? '';

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $target_dir = "../../assets/img/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $file_name = "company_logo_" . time() . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $logo_path = "assets/img/" . $file_name;
        }
    }

    if ($company) {
        $stmt = $pdo->prepare("UPDATE company SET name = ?, email = ?, phone = ?, address = ?, currency = ?, receipt_footer = ?, rc_number = ?, logo_path = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $currency, $receipt_footer, $rc_number, $logo_path, $company['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO company (name, email, phone, address, currency, receipt_footer, rc_number, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $currency, $receipt_footer, $rc_number, $logo_path]);
    }

    header("Location: ../../dashboard.php?page=company_profile&success=profile_updated");
} else {
    header("Location: ../../dashboard.php?page=company_profile");
}
?>
