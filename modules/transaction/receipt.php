<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;

// Fetch Transaction
$stmt = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die("Transaction not found.");
}

// Fetch Items
$stmt = $pdo->prepare("SELECT ti.*, p.name as product_name FROM transaction_items ti JOIN products p ON ti.product_id = p.id WHERE ti.transaction_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Fetch Company Info
$stmt = $pdo->query("SELECT * FROM company LIMIT 1");
$company = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?= $id ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; width: 300px; margin: 20px auto; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .details { margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th { text-align: left; border-bottom: 1px solid #000; padding: 5px 0; }
        .table td { padding: 5px 0; }
        .total-row { font-weight: bold; border-top: 1px solid #000; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        @media print {
            body { margin: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Receipt</button>
        <a href="../../dashboard.php?page=sales" style="margin-left: 10px; text-decoration: none; color: #666;">Back to Sales</a>
    </div>

    <div class="header">
        <?php if (!empty($company['logo_path']) && file_exists('../../' . $company['logo_path'])): ?>
            <img src="../../<?= htmlspecialchars($company['logo_path']) ?>" style="max-width: 180px; max-height: 80px; margin-bottom: 10px;">
        <?php endif; ?>
        <h2><?= htmlspecialchars($company['name'] ?? 'Gbàgede-Ọjà') ?></h2>
        <?php if (!empty($company['rc_number'])): ?>
            <p style="font-size: 11px; margin-top: 5px;">RC: <?= htmlspecialchars($company['rc_number']) ?></p>
        <?php endif; ?>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($company['address'] ?? '')) ?><br>
        Tel: <?= htmlspecialchars($company['phone'] ?? '') ?></p>
    </div>

    <div class="details">
        Date: <?= date('M d, Y H:i', strtotime($transaction['transaction_date'])) ?><br>
        Receipt: #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?><br>
        Cashier: <?= htmlspecialchars($transaction['cashier_name']) ?>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['unit_price'], 0) ?></td>
                <td><?= number_format($item['subtotal'], 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td>₦<?= number_format($transaction['total_amount'], 0) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><?= nl2br(htmlspecialchars($company['receipt_footer'] ?? 'Thank you for your patronage!')) ?></p>
        <p>Software by Gbàgede-Ọjà</p>
    </div>

    <script>
        // Auto-print on load if needed
        // window.print();
    </script>
</body>
</html>
