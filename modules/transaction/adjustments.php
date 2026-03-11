<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$products_stmt = $pdo->query("SELECT id, name, current_stock FROM products ORDER BY name ASC");
$products = $products_stmt->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
            <i class="fas fa-sliders-h"></i>
        </div>
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Inventory Adjustment</h2>
            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Manually correct stock levels based on physical counts.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle"></i>
            Inventory adjustments have been applied successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 15px 20px; border-radius: 12px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="modules/transaction/process_adjustment.php" method="POST" id="adjustmentForm">
        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px; align-items: start;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <!-- Adjustment Details -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                        <i class="fas fa-tasks" style="color: #0d9488;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Adjustment Details</h3>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Adjustment Date</label>
                            <input type="date" name="adjustment_date" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Reason</label>
                            <select name="reason" id="reasonSelect" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                                <option value="Physical Count Correction">Physical Count Correction</option>
                                <option value="Damaged Goods">Damaged Goods</option>
                                <option value="Stock Expiry">Stock Expiry</option>
                                <option value="Return to Vendor">Return to Vendor</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Additional Notes</label>
                            <input type="text" name="notes" placeholder="Optional note..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-boxes" style="color: #6366f1;"></i>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Items</h3>
                        </div>
                        <div style="position: relative; width: 300px;">
                            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50.5%); color: #94a3b8; font-size: 14px;"></i>
                            <select id="productSearch" style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #e2e8f0; border-radius: 20px; background: #f8fafc; font-size: 13px; appearance: none; outline: none; transition: border-color 0.2s;">
                                <option value="">Search and add item...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-stock="<?= $p['current_stock'] ?>"><?= htmlspecialchars($p['name']) ?> (Stock: <?= number_format($p['current_stock'], 0) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                            <thead>
                                <tr style="text-align: left; background: #f8fafc;">
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9;">Item</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Current Stock</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">New Stock</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Change</th>
                                    <th style="padding: 15px; border-bottom: 1px solid #f1f5f9;"></th>
                                </tr>
                            </thead>
                            <tbody id="adjustmentTable">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                        <div id="emptyState" style="padding: 60px 0; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #cbd5e1; font-size: 24px;">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0;">Search and add items to adjust stock quantities.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Summary Card -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 20px 0;">Summary</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Total Items</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryTotalItems">0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Items with Changes</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryChangedItems">0</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 4px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                            <span style="color: #64748b; font-size: 14px;">Reason</span>
                            <span style="font-weight: 700; color: #334155; font-size: 13px; text-align: right;" id="summaryReason">Physical Count Correction</span>
                        </div>
                    </div>
                </div>

                <!-- Action Card -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <p style="color: #64748b; font-size: 12px; margin-top: 0; line-height: 1.5; margin-bottom: 20px;">
                        Saving will immediately <strong>update stock levels</strong> and create a permanent entry in inventory history.
                    </p>
                    <input type="hidden" name="adjustment_data" id="adjustmentData">
                    <button type="submit" id="saveBtn" disabled style="width: 100%; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s;">
                        <i class="fas fa-save"></i> Save Adjustment
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
let items = [];

const productSearch = document.getElementById('productSearch');
const adjustmentTable = document.getElementById('adjustmentTable');
const emptyState = document.getElementById('emptyState');
const saveBtn = document.getElementById('saveBtn');
const summaryTotalItems = document.getElementById('summaryTotalItems');
const summaryChangedItems = document.getElementById('summaryChangedItems');
const summaryReason = document.getElementById('summaryReason');
const reasonSelect = document.getElementById('reasonSelect');
const adjustmentDataInput = document.getElementById('adjustmentData');

reasonSelect.addEventListener('change', () => {
    summaryReason.textContent = reasonSelect.value;
});

productSearch.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (!selected.value) return;

    const id = selected.value;
    const name = selected.text.split(' (')[0];
    const stock = parseFloat(selected.getAttribute('data-stock'));

    if (!items.find(i => i.id === id)) {
        items.push({ id, name, currentStock: stock, newStock: stock });
        renderItems();
    }
    this.value = '';
});

function renderItems() {
    adjustmentTable.innerHTML = '';
    
    if (items.length === 0) {
        emptyState.style.display = 'block';
        saveBtn.disabled = true;
        saveBtn.style.background = '#cbd5e1';
        saveBtn.style.cursor = 'not-allowed';
    } else {
        emptyState.style.display = 'none';
        saveBtn.disabled = false;
        saveBtn.style.background = '#6366f1';
        saveBtn.style.cursor = 'pointer';
        
        items.forEach((item, index) => {
            const change = item.newStock - item.currentStock;
            const changeColor = change > 0 ? '#059669' : (change < 0 ? '#dc2626' : '#64748b');
            const changePrefix = change > 0 ? '+' : '';

            adjustmentTable.innerHTML += `
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px; font-weight: 700; color: #334155;">${item.name}</td>
                    <td style="padding: 15px; text-align: center; color: #64748b; font-weight: 600;">${Math.round(item.currentStock).toLocaleString()}</td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="1" value="${Math.round(item.newStock)}" 
                            onchange="updateNewStock(${index}, this.value)" 
                            style="width: 100px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none; transition: border-color 0.2s;">
                    </td>
                    <td style="padding: 15px; text-align: center; font-weight: 800; color: ${changeColor};">
                        ${changePrefix}${Math.round(change).toLocaleString()}
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <button type="button" onclick="removeItem(${index})" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    updateSummary();
}

function updateNewStock(index, value) {
    items[index].newStock = parseInt(value) || 0;
    renderItems();
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
}

function updateSummary() {
    const total = items.length;
    const changed = items.filter(i => i.newStock !== i.currentStock).length;
    
    summaryTotalItems.textContent = total;
    summaryChangedItems.textContent = changed;
    
    adjustmentDataInput.value = JSON.stringify(items);
}

// Initial update
updateSummary();
</script>
