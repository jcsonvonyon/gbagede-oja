<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch vendors
$vendors = $pdo->query("SELECT id, name FROM vendors WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

// Fetch products for item search
$products = $pdo->query("SELECT id, name, purchase_price FROM products ORDER BY name ASC")->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
            <i class="fas fa-file-contract"></i>
        </div>
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Create Purchase Order</h2>
            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Draft a new procurement request for your suppliers.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle"></i>
            Purchase order has been drafted successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 15px 20px; border-radius: 12px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="modules/transaction/process_purchase_order.php" method="POST" id="poForm">
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <!-- Order Details -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                        <i class="fas fa-clipboard-list" style="color: #0d9488;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Order Details</h3>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="fas fa-user-tie"></i> Supplier / Vendor Name
                            </label>
                            <select name="vendor_id" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendors as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="far fa-calendar-check"></i> Order Date
                            </label>
                            <input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="fas fa-truck-loading"></i> Expected Delivery
                            </label>
                            <input type="date" name="expected_delivery" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="fas fa-hashtag"></i> Reference No. / Quote No.
                            </label>
                            <input type="text" name="reference_no" placeholder="Supplier Quote ID" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                    </div>
                </div>

                <!-- Items to Order -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-shopping-basket" style="color: #6366f1;"></i>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Items to Order</h3>
                        </div>
                        <div style="position: relative; width: 300px;">
                            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50.5%); color: #94a3b8; font-size: 14px;"></i>
                            <select id="productSearch" style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #e2e8f0; border-radius: 20px; background: #f8fafc; font-size: 13px; appearance: none;">
                                <option value="">Search inventory to add...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-cost="<?= $p['purchase_price'] ?>"><?= htmlspecialchars($p['name']) ?> (Cost: ₦<?= number_format($p['purchase_price'], 0) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                            <thead>
                                <tr style="text-align: left; background: #f8fafc;">
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9;">Item Name</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Quantity</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Expected Cost (₦)</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Total (₦)</th>
                                    <th style="padding: 15px; border-bottom: 1px solid #f1f5f9;"></th>
                                </tr>
                            </thead>
                            <tbody id="poTable">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                        <div id="emptyState" style="padding: 60px 0; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #cbd5e1; font-size: 24px;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0;">No items added yet. Search inventory above to add items.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Order Summary -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 20px 0;">Order Summary</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Total Items (<span id="summaryTotalItems">0</span>)</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryTotalAmount">₦ 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: 5px;">
                            <span style="color: #0d3d36; font-size: 15px; font-weight: 700;">Estimated Cost</span>
                            <span style="font-size: 18px; font-weight: 800; color: #10b981;" id="summaryEstimatedCost">₦ 0</span>
                        </div>
                    </div>
                </div>

                <!-- Action Card -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <p style="color: #64748b; font-size: 12px; margin-top: 0; line-height: 1.5; margin-bottom: 20px;">
                        Creating a Purchase Order will <strong>draft</strong> this request. It will <strong>not</strong> immediately update inventory levels or create a financial transaction.
                    </p>
                    <input type="hidden" name="po_data" id="poData">
                    <button type="submit" id="createBtn" disabled style="width: 100%; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s;">
                        <i class="fas fa-file-invoice"></i> Create Purchase Order
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
let cart = [];

const productSearch = document.getElementById('productSearch');
const poTable = document.getElementById('poTable');
const emptyState = document.getElementById('emptyState');
const createBtn = document.getElementById('createBtn');
const summaryTotalItems = document.getElementById('summaryTotalItems');
const summaryTotalAmount = document.getElementById('summaryTotalAmount');
const summaryEstimatedCost = document.getElementById('summaryEstimatedCost');
const poDataInput = document.getElementById('poData');

productSearch.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (!selected.value) return;

    const id = selected.value;
    const name = selected.text.split(' (')[0];
    const cost = parseFloat(selected.getAttribute('data-cost'));

    if (!cart.find(i => i.id === id)) {
        cart.push({ id, name, cost, qty: 1 });
        renderCart();
    }
    this.value = '';
});

function renderCart() {
    poTable.innerHTML = '';
    
    if (cart.length === 0) {
        emptyState.style.display = 'block';
        createBtn.disabled = true;
        createBtn.style.background = '#cbd5e1';
        createBtn.style.cursor = 'not-allowed';
    } else {
        emptyState.style.display = 'none';
        createBtn.disabled = false;
        createBtn.style.background = '#6366f1';
        createBtn.style.cursor = 'pointer';
        
        cart.forEach((item, index) => {
            const total = item.qty * item.cost;
            poTable.innerHTML += `
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px; font-weight: 700; color: #334155;">${item.name}</td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="0.01" value="${item.qty}" min="0.01"
                            onchange="updateField(${index}, 'qty', this.value)" 
                            style="width: 80px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none;">
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="1" value="${Math.round(item.cost)}" 
                            onchange="updateField(${index}, 'cost', this.value)" 
                            style="width: 120px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none;">
                    </td>
                    <td style="padding: 15px; text-align: center; font-weight: 800; color: #0d9488;">
                        ₦${Math.round(total).toLocaleString()}
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

function updateField(index, field, value) {
    cart[index][field] = parseFloat(value) || 0;
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateSummary() {
    let totalAmount = 0;
    cart.forEach(i => totalAmount += (i.qty * i.cost));
    
    summaryTotalItems.textContent = cart.length;
    summaryTotalAmount.textContent = '₦' + Math.round(totalAmount).toLocaleString();
    summaryEstimatedCost.textContent = '₦' + Math.round(totalAmount).toLocaleString();
    
    poDataInput.value = JSON.stringify(cart);
}
</script>
