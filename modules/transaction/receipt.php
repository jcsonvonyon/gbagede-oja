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
        body { font-family: 'Courier New', Courier, monospace; font-size: 13px; width: 300px; margin: 0 auto; color: #000; background: #f8fafc; }
        .receipt-wrapper { 
            background: white; 
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 15px;
            margin: 20px 0;
        }
        .header { text-align: center; margin-bottom: 15px; }
        .header img { max-width: 100px; max-height: 50px; margin-bottom: 8px; }
        .header h2 { margin: 0; font-size: 15px; text-transform: uppercase; font-weight: 800; border-bottom: 1px dashed #000; padding-bottom: 5px; display: inline-block; }
        .header p { margin: 3px 0; font-size: 11px; line-height: 1.3; }
        
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        
        .details { margin-bottom: 10px; font-size: 11px; line-height: 1.4; border-bottom: 1px dashed #000; padding-bottom: 8px; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th { text-align: left; border-bottom: 1px dashed #000; padding: 5px 0; font-size: 10px; text-transform: uppercase; }
        .table td { padding: 5px 0; font-size: 11px; vertical-align: top; }
        
        .total-row { font-weight: bold; border-top: 1px dashed #000; }
        .total-row td { padding-top: 8px; font-size: 12px; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 10px; line-height: 1.4; border-top: 1px dashed #000; padding-top: 10px; }
        .footer p { margin: 2px 0; }
        
        .copy-label {
            text-align: center;
            font-weight: 800;
            margin-bottom: 15px;
            padding: 4px;
            text-transform: uppercase;
            font-size: 10px;
            background: #000;
            color: #fff;
            letter-spacing: 1px;
        }
        
        @media print {
            body { background: white; margin: 0 auto; width: 300px; }
            .no-print { display: none; }
            .receipt-wrapper { box-shadow: none; padding: 0; margin: 0; border: none; }
            .copy-break { border-top: 2px dashed #000; margin: 30px 0; padding-top: 30px; position: relative; }
            .copy-break::after { content: '✂--------------------------------'; position: absolute; top: -15px; left: 0; width: 100%; text-align: center; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 12px 25px; cursor: pointer; background: #0d9488; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Print Receipt
        </button>
        <a href="../../dashboard.php?page=pos" style="margin-left: 15px; text-decoration: none; color: #64748b; font-weight: 700; font-size: 14px; display: inline-block; vertical-align: middle;">
            Back to POS Terminal
        </a>
    </div>

    <?php 
    $copies = ['Customer Copy', 'Office Copy'];
    foreach ($copies as $index => $label): 
    ?>
    <div class="receipt-wrapper <?= $index > 0 ? 'copy-break' : '' ?>">
        <div class="copy-label"><?= $label ?></div>

        <div class="header">
            <?php if (!empty($company['logo_path']) && file_exists('../../' . $company['logo_path'])): ?>
                <img src="../../<?= htmlspecialchars($company['logo_path']) ?>">
            <?php endif; ?>
            <h2><?= htmlspecialchars($company['name'] ?? 'Gbàgede-Ọjà') ?></h2>
            <?php if (!empty($company['rc_number'])): ?>
                <p>RC No: <?= htmlspecialchars($company['rc_number']) ?></p>
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($company['address'] ?? '')) ?></p>
            <p>TEL: <?= htmlspecialchars($company['phone'] ?? '') ?></p>
        </div>

        <div class="details">
            DATE: <?= date('M d, Y H:i', strtotime($transaction['transaction_date'])) ?><br>
            INV:  #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?><br>
            CASHIER: <?= strtoupper(htmlspecialchars($transaction['cashier_name'])) ?>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 45%;">ITEM</th>
                    <th style="width: 15%; text-align: center;">QTY</th>
                    <th style="width: 20%; text-align: right;">PRICE</th>
                    <th style="width: 20%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= strtoupper(htmlspecialchars($item['product_name'])) ?></td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;"><?= number_format($item['unit_price'], 0) ?></td>
                    <td style="text-align: right;"><?= number_format($item['subtotal'], 0) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td colspan="3">TOTAL AMOUNT</td>
                    <td style="text-align: right;">₦<?= number_format($transaction['total_amount'], 0) ?></td>
                </tr>
                
                <tr>
                    <td colspan="4" style="height: 5px;"></td>
                </tr>

                <?php if ($transaction['payment_method'] === 'SPLIT' && !empty($transaction['payment_details'])): 
                    $splits = json_decode($transaction['payment_details'], true); ?>
                    <tr>
                        <td colspan="4" style="font-size: 9px; padding-top: 5px; border-top: 1px dashed #eee;">PAYMENT BREAKDOWN:</td>
                    </tr>
                    <?php foreach ($splits as $method => $amount): if ($amount > 0): ?>
                        <tr style="font-size: 10px;">
                            <td colspan="3"><?= strtoupper($method) ?></td>
                            <td style="text-align: right;">₦<?= number_format($amount, 0) ?></td>
                        </tr>
                    <?php endif; endforeach; ?>
                <?php else: ?>
                    <tr style="font-size: 10px;">
                        <td colspan="3"><?= strtoupper($transaction['payment_method']) ?></td>
                        <td style="text-align: right;">₦<?= number_format($transaction['amount_paid'], 0) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($transaction['balance_amount'] > 0): ?>
                    <tr style="font-weight: bold; border-top: 1px dashed #000; font-size: 11px;">
                        <td colspan="3">BALANCE DUE</td>
                        <td style="text-align: right;">₦<?= number_format($transaction['balance_amount'], 0) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-bottom: 5px; font-size: 10px; text-align: center;">
            STATUS: <strong><?= strtoupper($transaction['payment_status']) ?></strong>
        </div>

        <div class="footer">
            <p><?= nl2br(htmlspecialchars($company['receipt_footer'] ?? 'Thank you for your patronage!')) ?></p>
            <p style="margin-top: 10px; font-weight: bold; font-size: 8px;">Software by Gbàgede-Ọjà</p>
            <div style="font-size: 10px; margin-top: 10px; border-top: 1px dashed #eee; padding-top: 5px;">*** ORIGINAL RECEIPT ***</div>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
        <?php if (isset($_GET['print'])): ?>
        window.print();
        <?php endif; ?>
    </script>
</body>
</html>
