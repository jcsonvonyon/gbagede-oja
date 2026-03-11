<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$products_stmt = $pdo->query("SELECT * FROM products WHERE current_stock > 0 ORDER BY name ASC");
$products = $products_stmt->fetchAll();
?>
<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">New Sales Transaction</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Record a new customer purchase and issue a receipt.</p>
    </div>


<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
    <!-- Sales Form -->
    <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <form action="modules/transaction/process_sale.php" method="POST" id="saleForm">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select Customer</label>
                <select name="customer_id" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <option value="">-- Walking Customer (Guest) --</option>
                    <?php
                    $customers = $pdo->query("SELECT id, name FROM customers WHERE status = 'Active' ORDER BY name")->fetchAll();
                    foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Payment Method</label>
                <select name="payment_method" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <option value="CASH">CASH</option>
                    <option value="TRANSFER">TRANSFER</option>
                    <option value="POS">POS</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Invoice Status</label>
                <select name="payment_status" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <option value="Paid">Paid</option>
                    <option value="Partial">Partial</option>
                    <option value="Unpaid">Unpaid</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select Product</label>
                <select id="productSelect" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                    <option value="">-- Search Products --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" data-price="<?= $p['sale_price'] ?>" data-stock="<?= $p['current_stock'] ?>">
                            <?= htmlspecialchars($p['name']) ?> (Stock: <?= number_format($p['current_stock'], 0) ?>) - ₦<?= number_format($p['sale_price'], 0) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;" id="cartTable">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 10px 0;">Item</th>
                            <th style="padding: 10px 0;">Qty</th>
                            <th style="padding: 10px 0;">Price</th>
                            <th style="padding: 10px 0;">Total</th>
                            <th style="padding: 10px 0;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <!-- Items added dynamically -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #f1f5f9; padding-top: 20px; margin-top: 10px;">
                <span style="font-size: 18px; font-weight: 600;">Total Amount:</span>
                <span style="font-size: 24px; font-weight: 700; color: var(--primary);" id="grandTotal">₦ 0</span>
            </div>

            <input type="hidden" name="cart_data" id="cartData">
            
            <button type="submit" style="width: 100%; margin-top: 30px; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                Complete Transaction
            </button>
        </form>
    </div>

    <!-- Instructions / Summary -->
    <div style="background: #f1f5f9; padding: 25px; border-radius: 12px; height: fit-content;">
        <h3 style="margin-bottom: 15px; color: var(--sidebar-bg);">Quick Help</h3>
        <p style="color: #64748b; line-height: 1.6; font-size: 14px;">
            1. Select a product from the dropdown to add it to the cart.<br>
            2. Adjust the quantity as needed.<br>
            3. Review the total and click "Complete Transaction" to record the sale.<br><br>
            <strong>Note:</strong> Stock levels will be automatically updated upon completion.
        </p>
    </div>
</div>

<script>
let cart = [];

document.getElementById('productSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (!selected.value) return;

    const id = selected.value;
    const name = selected.text.split(' (')[0];
    const price = parseFloat(selected.getAttribute('data-price'));
    const stock = parseFloat(selected.getAttribute('data-stock'));

    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < stock) {
            existing.qty++;
        }
    } else {
        cart.push({ id, name, price, qty: 1, stock });
    }

    renderCart();
    this.value = ''; // Reset select
});

function renderCart() {
    const body = document.getElementById('cartBody');
    body.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.qty;
        total += itemTotal;
        body.innerHTML += `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 0;">${item.name}</td>
                <td style="padding: 10px 0;">
                    <input type="number" value="${item.qty}" min="1" max="${item.stock}" 
                        onchange="updateQty(${index}, this.value)" 
                        style="width: 50px; padding: 5px; border: 1px solid #e2e8f0; border-radius: 4px;">
                </td>
                <td style="padding: 10px 0;">₦${item.price.toFixed(0)}</td>
                <td style="padding: 10px 0;">₦${itemTotal.toFixed(0)}</td>
                <td style="padding: 10px 0; text-align: right;">
                    <button type="button" onclick="removeItem(${index})" style="background: none; border: none; color: #ef4444; cursor: pointer;">&times;</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('grandTotal').innerText = '₦ ' + total.toFixed(0);
    document.getElementById('cartData').value = JSON.stringify(cart);
}

function updateQty(index, val) {
    cart[index].qty = parseInt(val);
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}
</script>
</div>
