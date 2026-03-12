<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

$is_admin = hasRole('Admin') || hasRole('Manager');
$personal_only = isset($_GET['personal']) && $_GET['personal'] == 1;

if (!$is_admin && !$personal_only) {
    die("Unauthorized access.");
}

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$user_id = $_SESSION['user_id'];

// Build Query
$sql = "SELECT t.*, u.full_name as sold_by, c.name as customer_name 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN customers c ON t.customer_id = c.id 
        WHERE t.type = 'Sale' 
        AND DATE(t.transaction_date) BETWEEN ? AND ?";

$params = [$start_date, $end_date];

if ($personal_only) {
    $sql .= " AND t.user_id = ?";
    $params[] = $user_id;
} else if (!$is_admin) {
    // Safety check: non-admins can only export their own even if they try to bypass personal flag
    $sql .= " AND t.user_id = ?";
    $params[] = $user_id;
}

$sql .= " ORDER BY t.transaction_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Set Headers for Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_report_' . $start_date . '_to_' . $end_date . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output CSV Header
fputcsv($output, ['Date & Time', 'Invoice Number', 'Customer', 'Sold By', 'Total Amount (N)']);

// Output Data Rows
foreach ($sales as $s) {
    fputcsv($output, [
        date('M d, Y H:i', strtotime($s['transaction_date'])),
        '#INV-' . str_pad($s['id'], 5, '0', STR_PAD_LEFT),
        $s['customer_name'] ?? 'Walking Customer',
        $s['sold_by'],
        number_format($s['total_amount'], 2, '.', '')
    ]);
}

fclose($output);
exit();
?>
