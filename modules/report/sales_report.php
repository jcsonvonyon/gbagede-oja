<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT t.*, u.full_name as sold_by FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type = 'Sale' AND DATE(t.transaction_date) BETWEEN ? AND ? ORDER BY t.transaction_date DESC");
$stmt->execute([$start_date, $end_date]);
$sales = $stmt->fetchAll();

$total_revenue = 0;
$today_revenue = 0;
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
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
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
</div>

<div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 15px;">Date & Time</th>
                <th style="padding: 15px;">Transaction ID</th>
                <th style="padding: 15px;">Sold By</th>
                <th style="padding: 15px;">Total Amount (₦)</th>
                <th style="padding: 15px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $s): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px;"><?= date('M d, Y H:i', strtotime($s['transaction_date'])) ?></td>
                <td style="padding: 15px;">#TRANS-<?= str_pad($s['id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td style="padding: 15px;"><?= htmlspecialchars($s['sold_by']) ?></td>
                <td style="padding: 15px; font-weight: 600;">₦ <?= number_format($s['total_amount'], 0) ?></td>
                <td style="padding: 15px; text-align: right;">
                    <a href="modules/transaction/receipt.php?id=<?= $s['id'] ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 600;">View Receipt</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($sales)): ?>
            <tr>
                <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">No sales recorded yet.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
