<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// 1. Calculate Receivables (Owed by Customers)
$receivables_stmt = $pdo->query("SELECT SUM(balance_amount) as total FROM transactions WHERE type = 'Sale' AND payment_status != 'Paid'");
$total_receivables = $receivables_stmt->fetch()['total'] ?? 0;

// 2. Calculate Payables (Owed to Vendors)
$payables_stmt = $pdo->query("SELECT SUM(balance_amount) as total FROM transactions WHERE type = 'Purchase' AND payment_status != 'Paid'");
$total_payables = $payables_stmt->fetch()['total'] ?? 0;

// 3. Current Position (Revenue - Expenses)
$revenue_stmt = $pdo->query("SELECT SUM(amount_paid) as total FROM transactions WHERE type = 'Sale'");
$total_revenue = $revenue_stmt->fetch()['total'] ?? 0;
$expense_stmt = $pdo->query("SELECT SUM(amount) as total FROM expenses");
$total_expenses = $expense_stmt->fetch()['total'] ?? 0;
$net_position = $total_revenue - $total_expenses;

// 4. Fetch Recent Activity
$recent_stmt = $pdo->query("
    (SELECT 'Sale' as act_type, total_amount, transaction_date as act_date, notes FROM transactions WHERE type = 'Sale' ORDER BY transaction_date DESC LIMIT 3)
    UNION
    (SELECT 'Expense' as act_type, amount as total_amount, expense_date as act_date, notes FROM expenses ORDER BY expense_date DESC LIMIT 3)
    ORDER BY act_date DESC LIMIT 5
");
$activities = $recent_stmt->fetchAll();

// 5. Fetch Debtors (Customers with Pending Payments)
$debtors_stmt = $pdo->query("
    SELECT c.id as customer_id, c.name, c.phone, SUM(t.balance_amount) as balance 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    WHERE t.type = 'Sale' AND t.payment_status != 'Paid'
    GROUP BY c.id
");
$debtors = $debtors_stmt->fetchAll();
?>

<div style="display: flex; flex-direction: column; gap: 30px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Balance Report</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">An overview of your business financial health and outstanding balances.</p>
    </div>

    <!-- Top Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
        <!-- Receivables Card -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">Total Receivables</span>
                <div style="color: #10b981; font-size: 18px;"><i class="fas fa-arrow-up-right-from-square"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">₦<?= number_format($total_receivables, 0) ?></div>
            <div style="font-size: 12px; color: #94a3b8; font-weight: 500;">Owed by Customers</div>
        </div>

        <!-- Payables Card -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">Total Payables</span>
                <div style="color: #ef4444; font-size: 18px;"><i class="fas fa-arrow-down-right-to-square"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">₦<?= number_format($total_payables, 0) ?></div>
            <div style="font-size: 12px; color: #94a3b8; font-weight: 500;">Owed to Others</div>
        </div>

        <!-- Net Position Card -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">Net Position</span>
                <div style="color: #3b82f6; font-size: 18px;"><i class="fas fa-wallet"></i></div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">₦<?= number_format($net_position, 0) ?></div>
            <div style="font-size: 12px; color: #94a3b8; font-weight: 500;"><?= $net_position >= 0 ? 'Surplus' : 'Deficit' ?></div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        
        <!-- Detailed List & Tabs -->
        <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 8px;" id="balanceTabs">
                    <button class="active-tab" data-filter="outstanding" style="padding: 8px 16px; border-radius: 8px; border: none; background: #064e3b; color: white; font-weight: 600; font-size: 13px; cursor: pointer;">Outstanding</button>
                    <button data-filter="debtors" style="padding: 8px 16px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer;">Debtors</button>
                    <button data-filter="creditors" style="padding: 8px 16px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer;">Creditors</button>
                </div>
                <!-- Search Bar -->
                <div style="position: relative; width: 240px;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                    <input type="text" id="customerSearch" placeholder="Search customer..." style="width: 100%; padding: 10px 10px 10px 36px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                </div>
            </div>

            <div style="padding: 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; text-align: left;">
                            <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Customer</th>
                            <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Contact</th>
                            <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Balance</th>
                            <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</th>
                            <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($debtors)): ?>
                        <tr id="emptyState">
                            <td colspan="5" style="padding: 60px; text-align: center;">
                                <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 16px;"><i class="fas fa-user-circle" style="opacity: 0.2;"></i></div>
                                <div style="color: #64748b; font-weight: 500;">No customers with outstanding balances found.</div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <!-- Hidden empty state for JS toggle -->
                            <tr id="emptyState" style="display: none;">
                                <td colspan="5" style="padding: 60px; text-align: center;">
                                    <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 16px;"><i class="fas fa-user-circle" style="opacity: 0.2;"></i></div>
                                    <div style="color: #64748b; font-weight: 500;">No customers matching your search found.</div>
                                </td>
                            </tr>
                            <?php foreach ($debtors as $d): ?>
                            <tr class="balance-row" data-type="debtors" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 24px; font-weight: 600; color: #0f172a;" class="cust-name"><?= htmlspecialchars($d['name']) ?></td>
                                <td style="padding: 16px 24px; color: #64748b; font-size: 13px;"><?= htmlspecialchars($d['phone']) ?></td>
                                <td style="padding: 16px 24px; font-weight: 700; color: #0f172a;">₦<?= number_format($d['balance'], 0) ?></td>
                                <td style="padding: 16px 24px;">
                                    <span style="background: #fef2f2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">Overdue</span>
                                </td>
                                <td style="padding: 16px 24px; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="?page=receipt_list&customer_id=<?= $d['customer_id'] ?>" title="View Transaction History" style="color: #64748b; font-size: 14px; text-decoration: none; padding: 6px; border-radius: 6px; border: 1px solid #f1f5f9; background: #f8fafc;">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <button title="Collect Payment" style="background: #0d9488; border: none; color: white; cursor: pointer; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                                            Pay
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1e293b;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">Recent Activity</h3>
            </div>

            <?php if (empty($activities)): ?>
                <div style="padding: 40px 0; text-align: center; color: #94a3b8; font-size: 13px;">
                    No recent financial activity recorded.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($activities as $act): ?>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f8fafc;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; color: #1e293b;"><?= $act['act_type'] ?> Transaction</div>
                                <div style="font-size: 11px; color: #64748b;"><?= date('M d, H:i', strtotime($act['act_date'])) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 13px; font-weight: 700; color: <?= $act['act_type'] == 'Sale' ? '#10b981' : '#ef4444' ?>;">
                                    <?= $act['act_type'] == 'Sale' ? '+' : '-' ?> ₦<?= number_format($act['total_amount'], 0) ?>
                                </div>
                                <div style="font-size: 10px; color: #94a3b8; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($act['notes'] ?: 'No notes') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearch');
    const tabButtons = document.querySelectorAll('#balanceTabs button');
    const rows = document.querySelectorAll('.balance-row');
    const emptyState = document.getElementById('emptyState');
    let activeFilter = 'outstanding';

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const nameCell = row.querySelector('.cust-name');
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const type = row.getAttribute('data-type');
            
            const matchesSearch = name.includes(searchTerm);
            // 'outstanding' shows everything in this mock, 'debtors' shows customers, 'creditors' shows vendors (empty in mock)
            let matchesTab = true;
            if (activeFilter === 'debtors' && type !== 'debtors') matchesTab = false;
            if (activeFilter === 'creditors' && type !== 'creditors') matchesTab = false;

            if (matchesSearch && matchesTab) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => {
                b.style.background = '#f1f5f9';
                b.style.color = '#475569';
            });
            this.style.background = '#064e3b';
            this.style.color = 'white';
            
            activeFilter = this.getAttribute('data-filter');
            filterTable();
        });
    });
});
</script>

