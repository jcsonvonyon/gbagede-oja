<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch vendors
$vendors = $pdo->query("SELECT id, name FROM vendors WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

// Fetch products for item search
$products = $pdo->query("SELECT id, name, purchase_price FROM products ORDER BY name ASC")->fetchAll();

// Fetch payment methods
$payment_methods = $pdo->query("SELECT id, method_name FROM payment_methods WHERE status = 'Active' ORDER BY method_name ASC")->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #0d9488;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Purchase Invoice</h2>
            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Record received stock and track procurement payments.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-check-circle"></i>
            Purchase invoice has been recorded successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 15px 20px; border-radius: 12px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="modules/transaction/process_purchase_invoice.php" method="POST" id="purchaseForm">
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <!-- Purchase Details -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                        <i class="fas fa-file-alt" style="color: #0d9488;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Purchase Details</h3>
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
                                <i class="far fa-calendar-alt"></i> Purchase Date
                            </label>
                            <input type="date" name="purchase_date" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="fas fa-hashtag"></i> Reference / Invoice No.
                            </label>
                            <input type="text" name="reference_no" placeholder="Supplier Invoice ID" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">
                                <i class="far fa-comment-alt"></i> Notes (Optional)
                            </label>
                            <input type="text" name="notes" placeholder="Additional details..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        </div>
                    </div>
                </div>

                <!-- Items Received -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-dolly" style="color: #6366f1;"></i>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Items Received</h3>
                        </div>
                        <div style="position: relative; width: 300px;">
                            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50.5%); color: #94a3b8; font-size: 14px;"></i>
                            <select id="productSearch" style="width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #e2e8f0; border-radius: 20px; background: #f8fafc; font-size: 13px; appearance: none;">
                                <option value="">Search inventory to add...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-cost="<?= $p['purchase_price'] ?>"><?= htmlspecialchars($p['name']) ?> (Current Cost: ₦<?= number_format($p['purchase_price'], 0) ?>)</option>
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
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Unit Cost (₦)</th>
                                    <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; text-align: center;">Total (₦)</th>
                                    <th style="padding: 15px; border-bottom: 1px solid #f1f5f9;"></th>
                                </tr>
                            </thead>
                            <tbody id="purchaseTable">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                        <div id="emptyState" style="padding: 60px 0; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #cbd5e1; font-size: 24px;">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0;">No items added yet. Search inventory above to add items.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Summary -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 20px 0;">Summary</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 14px;">Subtotal (<span id="summaryTotalItems">0</span> items)</span>
                            <span style="font-weight: 800; color: #0f172a;" id="summaryTotalAmount">₦ 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: 5px;">
                            <span style="color: #0f172a; font-size: 15px; font-weight: 700;">Total Cost</span>
                            <span style="font-size: 18px; font-weight: 800; color: #0d9488;" id="summaryGrandTotal">₦ 0</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Reference -->
                <div style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                        <i class="fas fa-credit-card" style="color: #64748b;"></i>
                        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Payment Reference</h3>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Amount Paid (₦)</label>
                            <input type="number" name="amount_paid" id="amountPaid" value="0" step="0.01" style="width: 100%; padding: 12px; border: 2px solid #99f6e4; border-radius: 12px; font-size: 18px; font-weight: 800; color: #0f172a; outline: none;">
                            <p style="color: #94a3b8; font-size: 11px; margin-top: 5px;">Leave exact amount or less if credit</p>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Payment Method</label>
                            <select name="payment_method" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-weight: 500;">
                                <?php foreach ($payment_methods as $pm): ?>
                                    <option value="<?= htmlspecialchars($pm['method_name']) ?>" <?= $pm['method_name'] == 'Bank Transfer' ? 'selected' : '' ?>><?= htmlspecialchars($pm['method_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top: 25px;">
                        <input type="hidden" name="purchase_data" id="purchaseData">
                        <button type="submit" id="completeBtn" disabled style="width: 100%; padding: 15px; background: #cbd5e1; color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s;">
                            <i class="fas fa-save"></i> Complete Purchase
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
let cart = [];

const productSearch = document.getElementById('productSearch');
const purchaseTable = document.getElementById('purchaseTable');
const emptyState = document.getElementById('emptyState');
const completeBtn = document.getElementById('completeBtn');
const summaryTotalItems = document.getElementById('summaryTotalItems');
const summaryTotalAmount = document.getElementById('summaryTotalAmount');
const summaryGrandTotal = document.getElementById('summaryGrandTotal');
const purchaseDataInput = document.getElementById('purchaseData');
const amountPaidInput = document.getElementById('amountPaid');

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
    purchaseTable.innerHTML = '';
    
    if (cart.length === 0) {
        emptyState.style.display = 'block';
        completeBtn.disabled = true;
        completeBtn.style.background = '#cbd5e1';
        completeBtn.style.cursor = 'not-allowed';
    } else {
        emptyState.style.display = 'none';
        completeBtn.disabled = false;
        completeBtn.style.background = '#6366f1';
        completeBtn.style.cursor = 'pointer';
        
        cart.forEach((item, index) => {
            const total = item.qty * item.cost;
            purchaseTable.innerHTML += `
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px; font-weight: 700; color: #334155;">${item.name}</td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="1" value="${item.qty}" min="1"
                            onchange="updateField(${index}, 'qty', this.value)" 
                            style="width: 80px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none;">
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <input type="number" step="1" value="${Math.round(item.cost)}" 
                            onchange="updateField(${index}, 'cost', this.value)" 
                            style="width: 120px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; font-weight: 700; outline: none;">
                    </td>
                    <td style="padding: 15px; text-align: center; font-weight: 800; color: #0f172a;">
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
    let subtotal = 0;
    cart.forEach(i => subtotal += (i.qty * i.cost));
    
    summaryTotalItems.textContent = cart.length;
    summaryTotalAmount.textContent = '₦' + Math.round(subtotal).toLocaleString();
    summaryGrandTotal.textContent = '₦' + Math.round(subtotal).toLocaleString();
    
    // Auto-fill amount paid if it was 0 or strictly equal to old total
    // But for a better UX, usually, we just let the user type.
    // However, the screenshot shows "0" by default.
    
    purchaseDataInput.value = JSON.stringify(cart);
}
</script>
