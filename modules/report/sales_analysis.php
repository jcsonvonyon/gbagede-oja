<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// 1. Date Range Handling
$range = $_GET['range'] ?? 'month';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if (!$start_date || !$end_date) {
    if ($range === 'today') {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
    } elseif ($range === 'week') {
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
    } elseif ($range === 'month') {
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
    } else {
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
    }
}

// 2. Fetch Metrics
// A. General Stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(id) as total_trans,
        SUM(total_amount) as total_rev,
        SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as collected_rev,
        AVG(total_amount) as avg_val
    FROM transactions 
    WHERE type = 'Sale' AND DATE(transaction_date) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetch();

// B. Top Products by Revenue
$stmt = $pdo->prepare("
    SELECT p.name, SUM(ti.subtotal) as revenue, SUM(ti.quantity) as qty
    FROM transaction_items ti
    JOIN transactions t ON ti.transaction_id = t.id
    JOIN products p ON ti.product_id = p.id
    WHERE t.type = 'Sale' AND DATE(t.transaction_date) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll();

// C. Revenue by Cashier
$stmt = $pdo->prepare("
    SELECT u.full_name as cashier, SUM(t.total_amount) as revenue, COUNT(t.id) as trans_count
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE t.type = 'Sale' AND DATE(t.transaction_date) BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY revenue DESC
");
$stmt->execute([$start_date, $end_date]);
$cashier_stats = $stmt->fetchAll();

// D. Daily Sales Trend (Last 14 days or range)
$stmt = $pdo->prepare("
    SELECT DATE(transaction_date) as day, SUM(total_amount) as daily_rev
    FROM transactions
    WHERE type = 'Sale' AND DATE(transaction_date) BETWEEN ? AND ?
    GROUP BY DATE(transaction_date)
    ORDER BY day ASC
");
$stmt->execute([$start_date, $end_date]);
$trends = $stmt->fetchAll();

// E. Monthly Revenue for Current Year
$stmt = $pdo->prepare("
    SELECT MONTH(transaction_date) as m, SUM(total_amount) as rev 
    FROM transactions 
    WHERE type = 'Sale' AND YEAR(transaction_date) = YEAR(CURDATE()) 
    GROUP BY m
    ORDER BY m ASC
");
$stmt->execute();
$monthly_data = [];
$max_monthly_rev = 0;
while ($row = $stmt->fetch()) {
    $monthly_data[(int)$row['m']] = (float)$row['rev'];
    if ($row['rev'] > $max_monthly_rev) $max_monthly_rev = (float)$row['rev'];
}

// F. Payment Method Breakdown
$stmt = $pdo->prepare("
    SELECT payment_method, SUM(total_amount) as rev, COUNT(id) as count
    FROM transactions
    WHERE type = 'Sale' AND DATE(transaction_date) BETWEEN ? AND ?
    GROUP BY payment_method
    ORDER BY rev DESC
");
$stmt->execute([$start_date, $end_date]);
$payment_stats = $stmt->fetchAll();
$total_period_rev = array_sum(array_column($payment_stats, 'rev')) ?: 1;

// G. Invoice Status Breakdown
$stmt = $pdo->prepare("
    SELECT payment_status, SUM(total_amount) as rev, COUNT(id) as count
    FROM transactions
    WHERE type = 'Sale' AND DATE(transaction_date) BETWEEN ? AND ?
    GROUP BY payment_status
");
$stmt->execute([$start_date, $end_date]);
$status_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $status_stats[$row['payment_status']] = $row;
}

// H. Top 5 Customers by Revenue
$stmt = $pdo->prepare("
    SELECT c.name, SUM(t.total_amount) as rev, COUNT(t.id) as trans_count
    FROM transactions t
    JOIN customers c ON t.customer_id = c.id
    WHERE t.type = 'Sale' AND DATE(t.transaction_date) BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY rev DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$top_customers = $stmt->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading & Range Selector -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Sales Analysis</h2>
            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">In-depth performance metrics and transaction trends.</p>
        </div>
        
        <div style="display: flex; gap: 10px; background: white; padding: 6px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <a href="?page=receipt_analysis&range=today" style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: <?= $range === 'today' ? '#0d9488' : '#64748b' ?>; background: <?= $range === 'today' ? '#ecfdf5' : 'transparent' ?>;">Today</a>
            <a href="?page=receipt_analysis&range=week" style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: <?= $range === 'week' ? '#0d9488' : '#64748b' ?>; background: <?= $range === 'week' ? '#ecfdf5' : 'transparent' ?>;">Week</a>
            <a href="?page=receipt_analysis&range=month" style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: <?= $range === 'month' ? '#0d9488' : '#64748b' ?>; background: <?= $range === 'month' ? '#ecfdf5' : 'transparent' ?>;">Month</a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
        <div style="background: white; border-radius: 16px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Total Revenue</div>
            <div style="font-size: 32px; font-weight: 800; color: #0d9488;">₦<?= number_format($stats['total_rev'] ?: 0, 0) ?></div>
            <div style="color: #94a3b8; font-size: 12px; margin-top: 8px;">From <?= $stats['total_trans'] ?> transactions</div>
        </div>
        
        <div style="background: white; border-radius: 16px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Total Invoices</div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a;"><?= number_format($stats['total_trans'] ?: 0, 0) ?></div>
            <div style="color: #94a3b8; font-size: 12px; margin-top: 8px;">Total processed receipts</div>
        </div>

        <div style="background: white; border-radius: 16px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Collected</div>
            <div style="font-size: 32px; font-weight: 800; color: #0d9488;">₦<?= number_format($stats['collected_rev'] ?: 0, 0) ?></div>
            <div style="color: #94a3b8; font-size: 12px; margin-top: 8px;">Revenue from Paid invoices</div>
        </div>

        <div style="background: #0f172a; border-radius: 16px; padding: 25px; color: white;">
            <div style="color: #94a3b8; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Trans. Count</div>
            <div style="font-size: 32px; font-weight: 800;"><?= number_format($stats['total_trans'] ?: 0, 0) ?></div>
            <div style="color: #64748b; font-size: 12px; margin-top: 8px;">Successful Checkouts</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Monthly Revenue Chart -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); padding: 25px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="fas fa-chart-line" style="color: #0d9488;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Monthly Revenue &mdash; <?= date('Y') ?></h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php 
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                foreach($months as $i => $m_name): 
                    $m_num = $i + 1;
                    $rev = $monthly_data[$m_num] ?? 0;
                    $percent = $max_monthly_rev > 0 ? ($rev / $max_monthly_rev) * 100 : 0;
                ?>
                <div style="display: grid; grid-template-columns: 40px 1fr 100px; align-items: center; gap: 15px;">
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;"><?= $m_name ?></span>
                    <div style="height: 12px; background: #f1f5f9; border-radius: 6px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $percent ?>%; background: #0d9488; border-radius: 6px; transition: width 0.5s ease-out;"></div>
                    </div>
                    <span style="font-size: 12px; color: #0f172a; font-weight: 700; text-align: right;">₦<?= number_format($rev, 0) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); padding: 25px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="fas fa-wallet" style="color: #6366f1;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Payment Methods</h3>
            </div>

            <?php if (empty($payment_stats)): ?>
                <div style="padding: 100px 0; text-align: center;">
                    <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 20px;"><i class="fas fa-pie-chart" style="opacity: 0.2;"></i></div>
                    <div style="color: #94a3b8; font-weight: 500;">No data for <?= date('Y') ?></div>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 10px;">
                    <?php 
                    $colors = ['CASH' => '#0d9488', 'TRANSFER' => '#6366f1', 'POS' => '#f59e0b'];
                    foreach($payment_stats as $stat): 
                        $p_method = $stat['payment_method'];
                        $p_rev = $stat['rev'];
                        $p_percent = ($p_rev / $total_period_rev) * 100;
                        $color = $colors[$p_method] ?? '#94a3b8';
                    ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 10px; height: 10px; border-radius: 3px; background: <?= $color ?>;"></div>
                                <span style="font-size: 13px; font-weight: 700; color: #334155;"><?= $p_method ?></span>
                            </div>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600;"><?= number_format($p_percent, 1) ?>%</span>
                        </div>
                        <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $p_percent ?>%; background: <?= $color ?>; border-radius: 4px;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                            <span style="font-size: 11px; color: #94a3b8;"><?= $stat['count'] ?> sales</span>
                            <span style="font-size: 11px; font-weight: 700; color: #475569;">₦<?= number_format($p_rev, 0) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Top Products -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Top Selling Products</h3>
            </div>
            <div style="padding: 10px 0;">
                <?php if (empty($top_products)): ?>
                    <div style="padding: 40px; text-align: center; color: #94a3b8; font-size: 14px;">No sales data for this period.</div>
                <?php else: ?>
                    <?php foreach($top_products as $index => $p): ?>
                        <div style="padding: 15px 25px; display: flex; align-items: center; justify-content: space-between; <?= $index < count($top_products)-1 ? 'border-bottom: 1px solid #f8fafc;' : '' ?>">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: #64748b;">
                                    <?= $index + 1 ?>
                                </div>
                                <div style="font-weight: 600; color: #334155;"><?= htmlspecialchars($p['name']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 800; color: #0d9488; font-size: 14px;">₦<?= number_format($p['revenue'], 0) ?></div>
                                <div style="font-size: 11px; color: #94a3b8;"><?= number_format($p['qty'], 0) ?> units sold</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cashier Performance -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Sales by Personnel</h3>
            </div>
            <div style="padding: 10px 0;">
                <?php if (empty($cashier_stats)): ?>
                    <div style="padding: 40px; text-align: center; color: #94a3b8; font-size: 14px;">No cashier records found.</div>
                <?php else: ?>
                    <?php foreach($cashier_stats as $index => $c): ?>
                        <div style="padding: 15px 25px; display: flex; align-items: center; justify-content: space-between; <?= $index < count($cashier_stats)-1 ? 'border-bottom: 1px solid #f8fafc;' : '' ?>">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #0d9488;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #334155;"><?= htmlspecialchars($c['cashier']) ?></div>
                                    <div style="font-size: 11px; color: #94a3b8;"><?= $c['trans_count'] ?> orders processed</div>
                                </div>
                            </div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 14px;">₦<?= number_format($c['revenue'], 0) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Invoice Status Breakdown -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); padding: 25px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="fas fa-file-invoice-dollar" style="color: #64748b;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Invoice Status Breakdown</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 15px;">
                <!-- Paid -->
                <div style="background: #ecfdf5; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #d1fae5;">
                    <div>
                        <div style="font-weight: 800; color: #059669; font-size: 14px;">Paid</div>
                        <div style="font-size: 11px; color: #059669; opacity: 0.8;"><?= $status_stats['Paid']['count'] ?? 0 ?> invoices</div>
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: #059669;">₦<?= number_format($status_stats['Paid']['rev'] ?? 0, 0) ?></div>
                </div>

                <!-- Partial -->
                <div style="background: #fffbeb; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #fef3c7;">
                    <div>
                        <div style="font-weight: 800; color: #d97706; font-size: 14px;">Partial</div>
                        <div style="font-size: 11px; color: #d97706; opacity: 0.8;"><?= $status_stats['Partial']['count'] ?? 0 ?> invoices</div>
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: #d97706;">₦<?= number_format($status_stats['Partial']['rev'] ?? 0, 0) ?></div>
                </div>

                <!-- Unpaid -->
                <div style="background: #fef2f2; border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #fee2e2;">
                    <div>
                        <div style="font-weight: 800; color: #dc2626; font-size: 14px;">Unpaid</div>
                        <div style="font-size: 11px; color: #dc2626; opacity: 0.8;"><?= $status_stats['Unpaid']['count'] ?? 0 ?> invoices</div>
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: #dc2626;">₦<?= number_format($status_stats['Unpaid']['rev'] ?? 0, 0) ?></div>
                </div>
            </div>
        </div>

        <!-- Top 5 Customers -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-users" style="color: #6366f1;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Top 5 Customers by Revenue</h3>
            </div>
            <div style="padding: 10px 0;">
                <?php if (empty($top_customers)): ?>
                    <div style="padding: 80px 40px; text-align: center;">
                        <div style="color: #cbd5e1; font-size: 40px; margin-bottom: 15px;"><i class="fas fa-user-friends" style="opacity: 0.2;"></i></div>
                        <div style="color: #94a3b8; font-size: 14px; font-weight: 500;">No customer data for <?= date('Y') ?></div>
                    </div>
                <?php else: ?>
                    <?php foreach($top_customers as $index => $c): ?>
                        <div style="padding: 15px 25px; display: flex; align-items: center; justify-content: space-between; <?= $index < count($top_customers)-1 ? 'border-bottom: 1px solid #f8fafc;' : '' ?>">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: #64748b;">
                                    <?= $index + 1 ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #334155;"><?= htmlspecialchars($c['name']) ?></div>
                                    <div style="font-size: 11px; color: #94a3b8;"><?= $c['trans_count'] ?> transactions</div>
                                </div>
                            </div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 15px;">₦<?= number_format($c['rev'], 0) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</div>
