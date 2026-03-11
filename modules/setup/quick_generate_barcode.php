<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $barcode = $_POST['barcode'] ?? null;

    if ($id && $barcode) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?");
            $stmt->execute([$barcode, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Barcode generated successfully.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
