<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// 1. Calculate Metrics
$total_stmt = $pdo->query("SELECT SUM(amount) as total, COUNT(*) as count FROM expenses");
$total_data = $total_stmt->fetch();
$total_expenses = $total_data['total'] ?? 0;
$total_records = $total_data['count'] ?? 0;

$month_stmt = $pdo->query("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = MONTH(CURRENT_DATE) AND YEAR(expense_date) = YEAR(CURRENT_DATE)");
$month_expenses = $month_stmt->fetch()['total'] ?? 0;

// 2. Fetch Expenses History
$stmt = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");
$expenses = $stmt->fetchAll();

// 3. Categories
$categories = ['Rent & Utilities', 'Salaries', 'Marketing', 'Inventory', 'Logistics', 'Maintenance', 'Others'];
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Expenses Management</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Track and manage business operational expenses and outflows.</p>
    </div>

    <!-- Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
        <!-- Total Expenses -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;">
            <div style="width: 48px; height: 48px; background: #fff1f2; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                <i class="fas fa-chart-line" style="font-size: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #64748b;">Total Expenses</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;">₦<?= number_format($total_expenses, 0) ?></div>
            </div>
        </div>

        <!-- This Month -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;">
            <div style="width: 48px; height: 48px; background: #fffbeb; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                <i class="fas fa-calendar-alt" style="font-size: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #64748b;">This Month</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;">₦<?= number_format($month_expenses, 0) ?></div>
            </div>
        </div>

        <!-- Total Records -->
        <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;">
            <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                <i class="fas fa-file-invoice-dollar" style="font-size: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #64748b;">Total Records</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $total_records ?></div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 30px; align-items: start;">
        
        <!-- Record Expense Form -->
        <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="fas fa-plus" style="color: #0d9488;"></i>
                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Record Expense</h3>
            </div>

            <form action="modules/transaction/save_expense.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-tag" style="margin-right: 6px;"></i> Category</label>
                    <select name="category" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-money-bill" style="margin-right: 6px;"></i> Amount (₦)</label>
                        <input type="number" name="amount" required placeholder="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-credit-card" style="margin-right: 6px;"></i> Method</label>
                        <select name="payment_method" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                            <option value="CASH">CASH</option>
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="POS">POS</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-calendar" style="margin-right: 6px;"></i> Date</label>
                        <input type="date" name="expense_date" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-user" style="margin-right: 6px;"></i> Vendor/Payee</label>
                        <input type="text" name="vendor_payee" placeholder="e.g. PHCN, Landlord" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-align-left" style="margin-right: 6px;"></i> Description</label>
                    <textarea name="description" placeholder="Brief note about this expense..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px; min-height: 80px;"></textarea>
                </div>

                <button type="submit" style="width: 100%; padding: 14px; background: #0d9488; color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="fas fa-receipt"></i> Record Expense
                </button>
            </form>
        </div>

        <!-- Expenses History -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Expenses History</h3>
                <div style="display: flex; gap: 12px;">
                    <select id="categoryFilter" style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #475569; background: #f8fafc;">
                        <option value="all">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="position: relative; width: 200px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                        <input type="text" id="expenseSearch" placeholder="Search..." style="width: 100%; padding: 10px 10px 10px 38px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                    </div>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left;">
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Category</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Vendor/Payee</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Date</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Method</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody id="expenseBody">
                    <?php if (empty($expenses)): ?>
                    <tr id="emptyState">
                        <td colspan="5" style="padding: 80px; text-align: center;">
                            <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 20px;"><i class="fas fa-receipt" style="opacity: 0.2;"></i></div>
                            <div style="color: #64748b; font-weight: 500;">No expenses recorded yet.</div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <!-- Hidden empty state for JS toggle -->
                        <tr id="emptyState" style="display: none;">
                            <td colspan="5" style="padding: 80px; text-align: center;">
                                <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 20px;"><i class="fas fa-receipt" style="opacity: 0.2;"></i></div>
                                <div style="color: #64748b; font-weight: 500;">No matching expenses found.</div>
                            </td>
                        </tr>
                        <?php foreach($expenses as $exp): ?>
                        <tr class="expense-row" data-category="<?= $exp['category'] ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 18px 25px;">
                                <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($exp['category']) ?></div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">ID: #<?= $exp['id'] ?></div>
                            </td>
                            <td style="padding: 18px 25px; color: #475569; font-size: 14px;" class="vendor-cell"><?= htmlspecialchars($exp['vendor_payee'] ?: '-') ?></td>
                            <td style="padding: 18px 25px; color: #64748b; font-size: 13px;"><?= date('M d, Y', strtotime($exp['expense_date'])) ?></td>
                            <td style="padding: 18px 25px;">
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700;"><?= $exp['payment_method'] ?></span>
                            </td>
                            <td style="padding: 18px 25px; text-align: right; font-weight: 700; color: #ef4444; font-size: 15px;">₦<?= number_format($exp['amount'], 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('expenseSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const rows = document.querySelectorAll('.expense-row');
    const emptyState = document.getElementById('emptyState');

    function filterExpenses() {
        if (!searchInput) return;
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCat = categoryFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const vendorCell = row.querySelector('.vendor-cell');
            const vendor = vendorCell ? vendorCell.textContent.toLowerCase() : '';
            const category = row.getAttribute('data-category');
            
            const matchesSearch = vendor.includes(searchTerm) || category.toLowerCase().includes(searchTerm);
            const matchesCat = selectedCat === 'all' || category === selectedCat;

            if (matchesSearch && matchesCat) {
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

    if (searchInput) searchInput.addEventListener('input', filterExpenses);
    if (categoryFilter) categoryFilter.addEventListener('change', filterExpenses);

    // Hover effect for rows
    rows.forEach(row => {
        row.addEventListener('mouseenter', () => row.style.background = '#f8fafc');
        row.addEventListener('mouseleave', () => row.style.background = 'transparent');
    });
});
</script>
