<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$user_id = $_SESSION['user_id'];
$is_admin = hasRole('Admin');

$sql = "SELECT t.*, u.full_name as sold_by, c.name as customer_name 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN customers c ON t.customer_id = c.id 
        WHERE t.type = 'Sale' 
        AND DATE(t.transaction_date) BETWEEN ? AND ?";

$params = [$start_date, $end_date];

if (!$is_admin) {
    $sql .= " AND t.user_id = ?";
    $params[] = $user_id;
}

$sql .= " ORDER BY t.transaction_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

$total_revenue = 0;
$today_revenue = 0;
$total_orders = count($sales);
$today = date('Y-m-d');
foreach ($sales as $s) {
    if (date('Y-m-d', strtotime($s['transaction_date'])) === $today) {
        $today_revenue += $s['total_amount'];
    }
    $total_revenue += $s['total_amount'];
}
?>
<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Sales & Receipts History</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Comprehensive log of all customer transactions and issued receipts.</p>
    </div>


<!-- Filters -->
<div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
    <form method="GET" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="page" value="sales_report">
        <div>
            <label style="display: block; font-size: 13px; margin-bottom: 5px;">Start Date</label>
            <input type="date" name="start_date" value="<?= $start_date ?>" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
        <div>
            <label style="display: block; font-size: 13px; margin-bottom: 5px;">End Date</label>
            <input type="date" name="end_date" value="<?= $end_date ?>" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
        <button type="submit" style="background: var(--primary); color: white; padding: 10px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Filter Report</button>
        <button type="button" onclick="window.print()" style="background: #e2e8f0; color: #1e293b; padding: 10px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Print Report</button>
        <?php if (hasRole('Admin')): ?>
        <a href="modules/report/export_sales_csv.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" style="background: #0d9488; color: white; padding: 10px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; font-size: 13.333px; border: 1px solid #0d9488;">Export CSV</a>
        <?php endif; ?>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; border-top: 4px solid var(--primary);">
        <h3 style="color: #64748b; font-size: 14px; text-transform: uppercase;">Range Total Revenue</h3>
        <p style="font-size: 28px; font-weight: 700; margin-top: 10px; color: var(--primary);">₦ <?= number_format($total_revenue, 0) ?></p>
        <p style="font-size: 12px; color: #64748b; margin-top: 5px;">From <?= date('M d', strtotime($start_date)) ?> to <?= date('M d', strtotime($end_date)) ?></p>
    </div>
    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; border-top: 4px solid #3b82f6;">
        <h3 style="color: #64748b; font-size: 14px; text-transform: uppercase;">Today's Revenue</h3>
        <p style="font-size: 28px; font-weight: 700; margin-top: 10px;">₦ <?= number_format($today_revenue, 0) ?></p>
        <p style="font-size: 12px; color: #64748b; margin-top: 5px;"><?= date('l, M d, Y') ?></p>
    </div>
    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; border-top: 4px solid #f59e0b;">
        <h3 style="color: #64748b; font-size: 14px; text-transform: uppercase;">Orders</h3>
        <p style="font-size: 28px; font-weight: 700; margin-top: 10px;"><?= number_format($total_orders) ?></p>
        <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Completed transactions</p>
    </div>
</div>

<div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 15px;">Date & Time</th>
                <th style="padding: 15px;">Invoice Number</th>
                <th style="padding: 15px;">Customer</th>
                <th style="padding: 15px;">Sold By</th>
                <th style="padding: 15px;">Total Amount (₦)</th>
                <th style="padding: 15px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $s): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px;"><?= date('M d, Y H:i', strtotime($s['transaction_date'])) ?></td>
                <td style="padding: 15px;">#INV-<?= str_pad($s['id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td style="padding: 15px;"><?= htmlspecialchars($s['customer_name'] ?? 'Walking Customer') ?></td>
                <td style="padding: 15px;"><?= htmlspecialchars($s['sold_by']) ?></td>
                <td style="padding: 15px; font-weight: 600;">₦ <?= number_format($s['total_amount'], 0) ?></td>
                <td style="padding: 15px; text-align: right;">
                    <a href="modules/transaction/receipt.php?id=<?= $s['id'] ?>" target="_blank" style="color: #64748b; text-decoration: none; font-weight: 600; font-size: 13px; margin-right: 15px; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="modules/transaction/receipt.php?id=<?= $s['id'] ?>&print=1" target="_blank" style="color: #0d9488; text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-print"></i> Print
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($sales)): ?>
            <tr>
                <td colspan="6" style="padding: 40px; text-align: center; color: #64748b;">No sales recorded yet.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
