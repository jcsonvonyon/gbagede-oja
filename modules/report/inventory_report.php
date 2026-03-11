<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// 1. Fetch All Products with Category
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
$products = $stmt->fetchAll();

// 2. Calculate Dashboard Metrics
$inventory_value = 0;
$potential_revenue = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

foreach ($products as $p) {
    $inventory_value += $p['current_stock'] * $p['purchase_price'];
    $potential_revenue += $p['current_stock'] * $p['sale_price'];
    if ($p['current_stock'] <= 0) {
        $out_of_stock_count++;
    } elseif ($p['current_stock'] <= $p['min_stock']) {
        $low_stock_count++;
    }
}

// 3. Fetch Recent Stock Movements (Last 5 transactions involving items)
$move_stmt = $pdo->query("
    SELECT t.type, t.transaction_date, p.name as product_name, ti.quantity 
    FROM transactions t 
    JOIN transaction_items ti ON t.id = ti.transaction_id 
    JOIN products p ON ti.product_id = p.id 
    ORDER BY t.transaction_date DESC 
    LIMIT 5
");
$movements = $move_stmt->fetchAll();
?>

<div style="display: flex; flex-direction: column; gap: 30px; font-family: 'Inter', system-ui, sans-serif;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Stock Report</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Comprehensive overview of your inventory levels, valuations, and movements.</p>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
        <!-- Inventory Value -->
        <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 600; color: #64748b;">Inventory Value</span>
                <div style="color: #10b981; font-size: 16px;"><i class="fas fa-box-open"></i></div>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;">₦<?= number_format($inventory_value, 0) ?></div>
            <div style="font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 4px;">Based on Cost</div>
        </div>

        <!-- Potential Revenue -->
        <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 600; color: #64748b;">Potential Revenue</span>
                <div style="color: #3b82f6; font-size: 16px;"><i class="fas fa-chart-line"></i></div>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;">₦<?= number_format($potential_revenue, 0) ?></div>
            <div style="font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 4px;">Based on Sale Price</div>
        </div>

        <!-- Low Stock -->
        <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 600; color: #64748b;">Low Stock Items</span>
                <div style="color: #f59e0b; font-size: 16px;"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $low_stock_count ?></div>
            <div style="font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 4px;">Below min level</div>
        </div>

        <!-- Out of Stock -->
        <div style="background: white; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 600; color: #64748b;">Out of Stock</span>
                <div style="color: #ef4444; font-size: 16px;"><i class="fas fa-minus-circle"></i></div>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $out_of_stock_count ?></div>
            <div style="font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 4px;">Critical attention</div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        
        <!-- Inventory List -->
        <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 8px;" id="statusTabs">
                    <button class="active-tab" data-filter="all" style="padding: 8px 16px; border-radius: 8px; border: none; background: #064e3b; color: white; font-weight: 600; font-size: 12px; cursor: pointer;">All Items</button>
                    <button data-filter="Low Stock" style="padding: 8px 16px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-weight: 600; font-size: 12px; cursor: pointer;">Low Stock</button>
                    <button data-filter="Out of Stock" style="padding: 8px 16px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-weight: 600; font-size: 12px; cursor: pointer;">Out of Stock</button>
                    <button data-filter="Healthy" style="padding: 8px 16px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-weight: 600; font-size: 12px; cursor: pointer;">Healthy</button>
                </div>
                <!-- Search -->
                <div style="position: relative; width: 200px;">
                    <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                    <input type="text" id="productSearch" placeholder="Find product..." style="width: 100%; padding: 8px 10px 8px 32px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 12px;">
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 16px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Product</th>
                        <th style="padding: 16px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Category</th>
                        <th style="padding: 16px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: center;">In Stock</th>
                        <th style="padding: 16px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</th>
                        <th style="padding: 16px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: right;">Value (Cost)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr id="emptyState">
                        <td colspan="5" style="padding: 60px; text-align: center;">
                            <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 16px;"><i class="fas fa-box-open" style="opacity: 0.2;"></i></div>
                            <div style="color: #64748b; font-weight: 500;">No products found matching your filters.</div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <!-- Hidden empty state for JS toggle -->
                        <tr id="emptyState" style="display: none;">
                            <td colspan="5" style="padding: 60px; text-align: center;">
                                <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 16px;"><i class="fas fa-box-open" style="opacity: 0.2;"></i></div>
                                <div style="color: #64748b; font-weight: 500;">No products found matching your filters.</div>
                            </td>
                        </tr>
                        <?php foreach ($products as $p): 
                            $status_color = '#10b981';
                            $status_bg = '#ecfdf5';
                            $status_text = 'Healthy';
                            
                            if ($p['current_stock'] <= 0) {
                                $status_color = '#ef4444';
                                $status_bg = '#fef2f2';
                                $status_text = 'Out of Stock';
                            } elseif ($p['current_stock'] <= $p['min_stock']) {
                                $status_color = '#f59e0b';
                                $status_bg = '#fffbeb';
                                $status_text = 'Low Stock';
                            }
                        ?>
                        <tr class="product-row" data-status="<?= $status_text ?>" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px 20px; font-weight: 600; color: #0f172a;" class="prod-name"><?= htmlspecialchars($p['name']) ?></td>
                            <td style="padding: 16px 20px; color: #64748b; font-size: 13px;"><?= htmlspecialchars($p['category_name'] ?: 'None') ?></td>
                            <td style="padding: 16px 20px; font-weight: 700; text-align: center; color: #0f172a;"><?= number_format($p['current_stock'], 0) ?></td>
                            <td style="padding: 16px 20px;">
                                <span style="background: <?= $status_bg ?>; color: <?= $status_color ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;"><?= $status_text ?></span>
                            </td>
                            <td style="padding: 16px 20px; text-align: right; font-weight: 700; color: #0f172a;">₦<?= number_format($p['current_stock'] * $p['purchase_price'], 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Movements Feed -->
        <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1e293b;">
                    <i class="fas fa-wave-square"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">Recent Movements</h3>
            </div>

            <?php if (empty($movements)): ?>
                <div style="padding: 60px 0; text-align: center;">
                    <div style="color: #cbd5e1; font-size: 32px; margin-bottom: 16px;"><i class="fas fa-wave-square" style="opacity: 0.1;"></i></div>
                    <div style="color: #94a3b8; font-size: 12px;">No recent stock movements recorded.</div>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px; position: relative;">
                    <!-- Simple timeline vertical line -->
                    <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #f1f5f9;"></div>
                    
                    <?php foreach ($movements as $m): 
                        $move_color = $m['type'] == 'Sale' ? '#ef4444' : '#10b981';
                        $move_icon = $m['type'] == 'Sale' ? 'fa-arrow-down' : 'fa-arrow-up';
                    ?>
                    <div style="display: flex; gap: 16px; position: relative; z-index: 1;">
                        <div style="width: 32px; height: 32px; background: white; border: 2px solid #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: <?= $move_color ?>; font-size: 10px;">
                            <i class="fas <?= $move_icon ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b;"><?= htmlspecialchars($m['product_name']) ?></div>
                                <div style="font-size: 11px; font-weight: 700; color: <?= $move_color ?>;"><?= $m['type'] == 'Sale' ? '-' : '+' ?><?= number_format($m['quantity'], 0) ?></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2px;">
                                <div style="font-size: 11px; color: #94a3b8; font-weight: 500;"><?= $m['type'] ?></div>
                                <div style="font-size: 10px; color: #cbd5e1;"><?= date('M d, H:i', strtotime($m['transaction_date'])) ?></div>
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
    const searchInput = document.getElementById('productSearch');
    const tabButtons = document.querySelectorAll('#statusTabs button');
    const rows = document.querySelectorAll('.product-row');
    const emptyState = document.getElementById('emptyState');
    let activeFilter = 'all';

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const nameCell = row.querySelector('.prod-name');
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const status = row.getAttribute('data-status');
            
            const matchesSearch = name.includes(searchTerm);
            const matchesTab = activeFilter === 'all' || status === activeFilter;

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

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            tabButtons.forEach(b => {
                b.style.background = '#f1f5f9';
                b.style.color = '#475569';
                b.classList.remove('active-tab');
            });
            
            this.style.background = '#064e3b';
            this.style.color = 'white';
            this.classList.add('active-tab');
            
            activeFilter = this.getAttribute('data-filter');
            filterTable();
        });
    });
});
</script>

