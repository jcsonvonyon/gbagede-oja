<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch branches for From/To locations
$branches = $pdo->query("SELECT id, name FROM branches ORDER BY name ASC")->fetchAll();

// Fetch products for item search
$products = $pdo->query("SELECT id, name, current_stock FROM products ORDER BY name ASC")->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
            <i class="fas fa-sync-alt"></i>
        </div>
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Inventory Transfer</h2>
            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Move stock between branches or warehouses.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle"></i>
            Inventory transfer has been recorded successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 15px 20px; border-radius: 12px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="modules/transaction/process_transfer.php" method="POST" id="transferForm">
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <!-- Transfer Details -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                        <i class="fas fa-info-circle" style="color: #0d9488;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Transfer Details</h3>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 15px; align-items: center;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                    <i class="fas fa-map-marker-alt" style="color: #ef4444; font-size: 10px;"></i> From Location
                                </label>
                                <select name="from_branch_id" id="fromLocation" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="margin-top: 20px; color: #cbd5e1; font-size: 18px;">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                    <i class="fas fa-map-marker-alt" style="color: #10b981; font-size: 10px;"></i> To Location
                                </label>
                                <select name="to_branch_id" id="toLocation" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                    <i class="far fa-calendar-alt"></i> Transfer Date
                                </label>
                                <input type="date" name="transfer_date" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                            </div>
                            <div>
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                    <i class="far fa-file-alt"></i> Notes (Optional)
                                </label>
                                <input type="text" name="notes" placeholder="Reason for transfer..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-boxes" style="color: #6366f1;"></i>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Items to Transfer</h3>
                        </div>
                        <div style="position: relative; width: 300px;">
                            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50.5%); color: #94a3b8; font-size: 14px;"></i>
                            <select id="productSearch" style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #e2e8f0; border-radius: 20px; background: #f8fafc; font-size: 13px; appearance: none;">
                                <option value="">Search inventory...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-stock="<?= $p['current_stock'] ?>"><?= htmlspecialchars($p['name']) ?> (Available: <?= number_format($p['current_stock'], 0) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                            <thead>
                                <tr style="text-align: left; background: #f8fafc;">
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9;">Item</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Available</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Qty to Transfer</th>
                                    <th style="padding: 15px; border-bottom: 1px solid #f1f5f9;"></th>
                                </tr>
                            </thead>
                            <tbody id="transferTable">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                        <div id="emptyState" style="padding: 60px 0; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #cbd5e1; font-size: 24px;">
                                <i class="fas fa-box"></i>
                            </div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0;">Search for inventory items to add to this transfer.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Transfer Summary -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 20px 0;">Transfer Summary</h3>
                    
                    <div style="background: #f8fafc; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; border: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <i class="fas fa-map-marker-alt" style="color: #ef4444; margin-top: 3px; font-size: 12px;"></i>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">From</span>
                                <span style="font-size: 13px; font-weight: 700; color: #0f172a;" id="sumFrom">---</span>
                            </div>
                        </div>
                        <div style="height: 20px; border-left: 2px dashed #e2e8f0; margin-left: 5px; margin-bottom: -5px; margin-top: -10px;"></div>
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <i class="fas fa-map-marker-alt" style="color: #10b981; margin-top: 3px; font-size: 12px;"></i>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">To</span>
                                <span style="font-size: 13px; font-weight: 700; color: #0f172a;" id="sumTo">---</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Products</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryTotalProducts">0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Total Units</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryTotalUnits">0</span>
                        </div>
                    </div>
                </div>

                <!-- Action Card -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <p style="color: #64748b; font-size: 12px; margin-top: 0; line-height: 1.5; margin-bottom: 20px;">
                        Confirming a transfer will <strong>reduce stock</strong> at the origin location and log the movement in history.
                    </p>
                    <input type="hidden" name="transfer_data" id="transferData">
                    <button type="submit" id="confirmBtn" disabled style="width: 100%; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s;">
                        <i class="fas fa-save"></i> Confirm Transfer
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
let cart = [];

const productSearch = document.getElementById('productSearch');
const transferTable = document.getElementById('transferTable');
const emptyState = document.getElementById('emptyState');
const confirmBtn = document.getElementById('confirmBtn');
const summaryTotalProducts = document.getElementById('summaryTotalProducts');
const summaryTotalUnits = document.getElementById('summaryTotalUnits');
const fromLocation = document.getElementById('fromLocation');
const toLocation = document.getElementById('toLocation');
const sumFrom = document.getElementById('sumFrom');
const sumTo = document.getElementById('sumTo');
const transferDataInput = document.getElementById('transferData');

fromLocation.addEventListener('change', () => {
    sumFrom.textContent = fromLocation.options[fromLocation.selectedIndex].text || '---';
    validateTransfer();
});

toLocation.addEventListener('change', () => {
    sumTo.textContent = toLocation.options[toLocation.selectedIndex].text || '---';
    validateTransfer();
});

productSearch.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (!selected.value) return;

    const id = selected.value;
    const name = selected.text.split(' (')[0];
    const stock = parseFloat(selected.getAttribute('data-stock'));

    if (!cart.find(i => i.id === id)) {
        cart.push({ id, name, available: stock, qty: 1 });
        renderCart();
    }
    this.value = '';
});

function renderCart() {
    transferTable.innerHTML = '';
    
    if (cart.length === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
        
        cart.forEach((item, index) => {
            transferTable.innerHTML += `
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px; font-weight: 700; color: #334155;">${item.name}</td>
                    <td style="padding: 15px; text-align: center; color: #64748b; font-weight: 600;">${Math.round(item.available).toLocaleString()}</td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="1" value="${Math.round(item.qty)}" min="1" max="${item.available}"
                            onchange="updateQty(${index}, this.value)" 
                            style="width: 100px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none; transition: border-color 0.2s;">
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <button type="button" onclick="removeItem(${index})" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        } );
    }
    
    updateSummary();
    validateTransfer();
}

function updateQty(index, value) {
    const qty = Math.round(parseFloat(value) || 0);
    cart[index].qty = Math.min(qty, cart[index].available);
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateSummary() {
    const productsCount = cart.length;
    let unitsCount = 0;
    cart.forEach(i => unitsCount += i.qty);
    
    summaryTotalProducts.textContent = productsCount;
    summaryTotalUnits.textContent = unitsCount.toLocaleString();
    
    transferDataInput.value = JSON.stringify(cart);
}

function validateTransfer() {
    const hasItems = cart.length > 0;
    const hasFrom = fromLocation.value !== '';
    const hasTo = toLocation.value !== '';
    const diffLocation = fromLocation.value !== toLocation.value;
    
    if (hasItems && hasFrom && hasTo && diffLocation) {
        confirmBtn.disabled = false;
        confirmBtn.style.background = '#6366f1';
        confirmBtn.style.cursor = 'pointer';
    } else {
        confirmBtn.disabled = true;
        confirmBtn.style.background = '#cbd5e1';
        confirmBtn.style.cursor = 'not-allowed';
    }
}
</script>
