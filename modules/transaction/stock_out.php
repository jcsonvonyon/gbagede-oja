<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$products_stmt = $pdo->query("SELECT * FROM products WHERE current_stock > 0 ORDER BY name ASC");
$products = $products_stmt->fetchAll();
?>
<div style="margin-bottom: 30px;">
    <h2>Stock-Out (Returns / Damages)</h2>
    <p style="color: #64748b;">Record items removed from inventory other than sales.</p>
</div>

<div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 600px;">
    <form action="modules/transaction/process_stock_out.php" method="POST">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select Product</label>
            <select name="product_id" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> (Current: <?= number_format($p['current_stock'], 0) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Quantity to Remove</label>
            <input type="number" name="quantity" min="1" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Reason</label>
            <select name="reason" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #white;">
                <option value="Damage">Damage / Spoilage</option>
                <option value="Expired">Expired</option>
                <option value="Return">Return to Vendor</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Notes / Reference</label>
            <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;"></textarea>
        </div>

        <button type="submit" style="width: 100%; padding: 15px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
            Process Stock-Out
        </button>
    </form>
</div>
