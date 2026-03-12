<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch products with their group_id, unit info and linked manufacturer logo
$products_stmt = $pdo->query("SELECT p.*, u.name as unit_name, m.logo_path, c.group_id 
                             FROM products p 
                             LEFT JOIN units u ON p.unit_id = u.id 
                             LEFT JOIN categories c ON p.category_id = c.id
                             LEFT JOIN manufacturers m ON c.manufacturer_id = m.id
                             WHERE p.current_stock > 0 
                             ORDER BY p.name ASC");
$products = $products_stmt->fetchAll();

// Fetch Best-Selling 10 Active Groups for filters
$product_groups = $pdo->query("SELECT pg.id, pg.name, COALESCE(SUM(ti.quantity), 0) as total_sold 
                             FROM product_groups pg 
                             LEFT JOIN categories c ON c.group_id = pg.id 
                             LEFT JOIN products p ON p.category_id = c.id 
                             LEFT JOIN transaction_items ti ON ti.product_id = p.id 
                             WHERE pg.status = 'Active' 
                             GROUP BY pg.id 
                             ORDER BY total_sold DESC 
                             LIMIT 10")->fetchAll();

// Fetch customers for the selection with their current balance and credit limit
$customers = $pdo->query("SELECT c.id, c.name, c.credit_limit, COALESCE(SUM(t.balance_amount), 0) as total_balance 
                          FROM customers c 
                          LEFT JOIN transactions t ON c.id = t.customer_id AND t.type = 'Sale' 
                          WHERE c.status = 'Active' 
                          GROUP BY c.id 
                          ORDER BY c.name ASC")->fetchAll();
?>

<div style="font-family: 'Inter', system-ui, sans-serif; height: calc(100vh - 120px); display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Bar: Search & Status -->
    <div style="display: flex; gap: 20px; align-items: center; background: white; padding: 15px 25px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="flex: 1; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input type="text" id="posSearch" placeholder="Search products by name or scan barcode... (F1)" 
                style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: #f8fafc; transition: all 0.2s;">
        </div>
        <div style="display: flex; gap: 10px;">
            <div id="connectionStatus" style="display: flex; align-items: center; gap: 8px; padding: 8px 15px; background: #f0fdf4; color: #166534; border-radius: 50px; font-size: 12px; font-weight: 700;">
                <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></span> Online
            </div>
            <div style="display: flex; align-items: center; gap: 8px; padding: 8px 15px; background: #f1f5f9; color: #475569; border-radius: 50px; font-size: 12px; font-weight: 700;">
                <i class="fas fa-clock"></i> <span id="posTime">00:00:00</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 20px; flex: 1; overflow: hidden;">
        
        <!-- Left Column: Product Grid -->
        <div style="background: white; border-radius: 15px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden;">
            <div style="padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: flex-start; align-items: center;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="categoryFilters">
                    <button class="cat-btn active" data-id="all" onclick="filterCategory('all')">All</button>
                    <?php foreach ($product_groups as $group): ?>
                        <button class="cat-btn" data-id="<?= $group['id'] ?>" onclick="filterCategory(<?= $group['id'] ?>)"><?= htmlspecialchars($group['name']) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div id="productGrid" style="flex: 1; overflow-y: auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; align-content: start;">
                <?php foreach ($products as $p): ?>
                <div class="pos-item" 
                    data-id="<?= $p['id'] ?>" 
                    data-name="<?= htmlspecialchars($p['name']) ?>" 
                    data-price="<?= $p['sale_price'] ?>" 
                    data-barcode="<?= htmlspecialchars($p['barcode'] ?? '') ?>"
                    data-group-id="<?= $p['group_id'] ?? 'none' ?>"
                    data-stock="<?= number_format($p['current_stock'], 0, '.', '') ?>"
                    data-image="<?= $p['logo_path'] ?: 'assets/img/product-placeholder.png' ?>"
                    onclick="addToCart(this)"
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; text-align: center;">
                    
                    <!-- Product Image (Manufacturer Logo) -->
                    <div style="width: 60px; height: 60px; border-radius: 8px; background: white; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid #f1f5f9; overflow: hidden; padding: 5px;">
                        <?php if (!empty($p['logo_path']) && file_exists($p['logo_path'])): ?>
                            <img src="<?= htmlspecialchars($p['logo_path']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <?php else: ?>
                            <i class="fas fa-box" style="font-size: 24px; color: #cbd5e1;"></i>
                        <?php endif; ?>
                    </div>

                    <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px; height: 32px; overflow: hidden; width: 100%;"><?= htmlspecialchars($p['name']) ?></div>
                    <div style="font-size: 11px; color: #64748b; margin-bottom: 8px; width: 100%;"><?= htmlspecialchars($p['unit_value'] . ' ' . ($p['unit_name'] ?? '')) ?></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: auto;">
                        <span style="font-weight: 800; color: #0d9488;">₦<?= number_format($p['sale_price'], 0) ?></span>
                        <span style="font-size: 10px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;">Stk: <?= number_format($p['current_stock'], 0) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column: Cart & Checkout -->
        <div style="background: white; border-radius: 15px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; box-shadow: -4px 0 15px -10px rgba(0,0,0,0.1);">
            <!-- Customer Selection -->
            <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; background: #fcfcfd;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Customer Information</label>
                <div style="display: flex; gap: 10px;">
                    <select id="posCustomer" style="flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; background: white;">
                        <option value="">Walking Customer (Guest)</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" data-balance="<?= $c['total_balance'] ?>" data-credit-limit="<?= $c['credit_limit'] ?>">
                                <?= htmlspecialchars($c['name']) ?> 
                                <?= $c['total_balance'] > 0 ? ' (Owing: ₦' . number_format($c['total_balance'], 0) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="action-btn" title="Add Customer" style="width: 38px; height: 38px; border-radius: 8px; background: #f1f5f9; border: none; color: #475569; cursor: pointer;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <!-- Cart Table -->
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="posCartTable">
                    <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 10; border-bottom: 1px solid #e2e8f0;">
                        <tr style="text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b;">
                            <th style="padding: 12px 15px;">Item</th>
                            <th style="padding: 12px 15px; text-align: center;">Qty</th>
                            <th style="padding: 12px 15px; text-align: right;">Price</th>
                            <th style="padding: 12px 15px;"></th>
                        </tr>
                    </thead>
                    <tbody id="posCartBody">
                        <!-- Items dynamically added -->
                    </tbody>
                </table>
                <div id="cartEmptyMsg" style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                    <i class="fas fa-shopping-basket" style="font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
                    <p style="font-size: 14px;">Cart is empty.<br>Select a product to begin.</p>
                </div>
            </div>

            <!-- Summary & Actions -->
            <div style="padding: 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; font-size: 14px; color: #64748b;">
                        <span>Subtotal</span>
                        <span id="posSubtotal">₦ 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px; color: #64748b;">
                        <span>Discount</span>
                        <input type="number" id="posDiscount" value="0" onchange="calculateTotals()" style="width: 60px; text-align: right; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 5px;">
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                        <span style="font-weight: 700; font-size: 16px; color: #0f172a;">Grand Total</span>
                        <span style="font-weight: 800; font-size: 24px; color: #0d9488;" id="posGrandTotal">₦ 0</span>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <button onclick="holdTransaction()" id="holdBtn" disabled style="padding: 12px; border: 1px solid #6366f1; background: #eef2ff; border-radius: 12px; font-weight: 700; color: #6366f1; cursor: pointer; transition: all 0.2s;">
                        <i class="fas fa-hand-paper" style="margin-right: 8px;"></i> Hold
                    </button>
                    <button onclick="openRecallModal()" style="padding: 12px; border: 1px solid #e2e8f0; background: white; border-radius: 12px; font-weight: 700; color: #475569; cursor: pointer; transition: all 0.2s;">
                        <i class="fas fa-history" style="margin-right: 8px;"></i> Recall
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <button onclick="clearCart()" style="padding: 15px; border: 1px solid #e2e8f0; background: white; border-radius: 12px; font-weight: 700; color: #ef4444; cursor: pointer; transition: all 0.2s;">
                        <i class="fas fa-trash-alt" style="margin-right: 8px;"></i> Clear
                    </button>
                    <button onclick="openCheckout()" id="checkoutBtn" disabled style="padding: 15px; border: none; background: #94a3b8; color: white; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                        Checkout <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 4000; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
    <div class="premium-card modal-content" style="width: 500px; padding: 30px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a;">Finalize Transaction</h2>
            <button onclick="closeCheckout()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 25px; border: 1px solid #e2e8f0;">
            <div style="font-size: 14px; color: #64748b; margin-bottom: 5px;">Payable Amount</div>
            <div style="font-size: 32px; font-weight: 800; color: #0d9488;" id="modalTotal">₦ 0</div>
        </div>

        <form id="posSaleForm" action="modules/transaction/process_sale.php" method="POST">
            <input type="hidden" name="customer_id" id="hiddenCustomer">
            <input type="hidden" name="cart_data" id="hiddenCartData">
            <input type="hidden" name="payment_mode" id="paymentMode" value="FULL">
            <input type="hidden" name="payment_split_details" id="paymentSplitDetails">

            <!-- Payment Mode Selector -->
            <div style="display: flex; gap: 10px; margin-bottom: 25px;">
                <button type="button" onclick="setPaymentMode('FULL')" class="mode-btn active" id="modeFull" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-weight: 700; color: #475569; cursor: pointer;">Full Pay</button>
                <button type="button" onclick="setPaymentMode('PARTIAL')" class="mode-btn" id="modePartial" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-weight: 700; color: #475569; cursor: pointer;">Partial / Credit</button>
                <button type="button" onclick="setPaymentMode('SPLIT')" class="mode-btn" id="modeSplit" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-weight: 700; color: #475569; cursor: pointer;">Split Pay</button>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Payment Method</label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="CASH" checked style="display: none;">
                        <div class="pay-method-btn" onclick="selectPay(this)">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </div>
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="POS" style="display: none;">
                        <div class="pay-method-btn" onclick="selectPay(this)">
                            <i class="fas fa-credit-card"></i>
                            <span>POS</span>
                        </div>
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="TRANSFER" style="display: none;">
                        <div class="pay-method-btn" onclick="selectPay(this)">
                            <i class="fas fa-university"></i>
                            <span>Transfer</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Amount Inputs -->
            <div id="paymentInputsContainer">
                <div id="fullPaymentInput" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Amount Paid</label>
                    <input type="number" step="any" name="amount_paid" id="amountPaid" oninput="calculateChange()" style="width: 100%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 20px; font-weight: 800; color: #0f172a; outline: none;">
                </div>

                <div id="splitPaymentInputs" style="display: none; margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Split Breakdown</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 10px; border-radius: 10px;">
                            <span style="flex: 1; font-weight: 600; font-size: 13px;">CASH</span>
                            <input type="number" id="splitCash" placeholder="0" oninput="calculateSplitTotal()" style="width: 120px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; font-weight: 700;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 10px; border-radius: 10px;">
                            <span style="flex: 1; font-weight: 600; font-size: 13px;">POS</span>
                            <input type="number" id="splitPos" placeholder="0" oninput="calculateSplitTotal()" style="width: 120px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; font-weight: 700;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 10px; border-radius: 10px;">
                            <span style="flex: 1; font-weight: 600; font-size: 13px;">TRANSFER</span>
                            <input type="number" id="splitTransfer" placeholder="0" oninput="calculateSplitTotal()" style="width: 120px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; font-weight: 700;">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: #f1f5f9; padding: 15px; border-radius: 12px;">
                <div style="font-size: 13px; color: #64748b; font-weight: 600;" id="changeLabel">Change Due</div>
                <div style="font-size: 18px; font-weight: 800; color: #0d172a;" id="posChange">₦ 0</div>
            </div>

            <button type="submit" id="finalCheckoutBtn" style="width: 100%; padding: 18px; border: none; background: #0d9488; color: white; border-radius: 15px; font-size: 16px; font-weight: 800; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);">
                Complete Sale & Print Receipt
            </button>
        </form>
    </div>
</div>

<!-- Recall Modal -->
<div id="recallModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 4000; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
    <div class="premium-card modal-content" style="width: 600px; padding: 30px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a;">Held Transactions</h2>
            <button onclick="closeRecallModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>

        <div id="heldTransactionsList" style="max-height: 400px; overflow-y: auto;">
            <!-- Held items will load here -->
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div id="posAlertModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 5000; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
    <div class="premium-card modal-content" style="width: 400px; padding: 30px; border-radius: 20px; text-align: center;">
        <div id="alertIcon" style="font-size: 50px; margin-bottom: 20px;"></div>
        <h3 id="alertTitle" style="margin: 0 0 10px 0; font-size: 18px; font-weight: 800; color: #0f172a;">Notification</h3>
        <p id="alertMessage" style="margin: 0 0 25px 0; color: #64748b; font-size: 14px; line-height: 1.5;"></p>
        <button onclick="closePosAlert()" style="width: 100%; padding: 12px; border: none; background: #0f172a; color: white; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s;">OK</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="posConfirmModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 5000; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
    <div class="premium-card modal-content" style="width: 400px; padding: 30px; border-radius: 20px; text-align: center;">
        <div style="font-size: 50px; margin-bottom: 20px; color: #f59e0b;"><i class="fas fa-question-circle"></i></div>
        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 800; color: #0f172a;">Are you sure?</h3>
        <p id="confirmMessage" style="margin: 0 0 25px 0; color: #64748b; font-size: 14px; line-height: 1.5;"></p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button onclick="closePosConfirm()" style="padding: 12px; border: 1px solid #e2e8f0; background: white; color: #475569; border-radius: 12px; font-weight: 700; cursor: pointer;">Cancel</button>
            <button id="confirmBtn" style="padding: 12px; border: none; background: #0f172a; color: white; border-radius: 12px; font-weight: 700; cursor: pointer;">Yes, proceed</button>
        </div>
    </div>
</div>

<style>
    .pos-item:hover {
        transform: translateY(-2px);
        border-color: #0d9488 !important;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .pos-item:active {
        transform: scale(0.98);
    }
    .cat-btn {
        padding: 5px 15px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        background: white;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cat-btn.active {
        background: #0d9488;
        color: white;
        border-color: #0d9488;
    }
    .pay-method-btn {
        padding: 15px 10px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: #64748b;
    }
    input[type="radio"]:checked + .pay-method-btn {
        border-color: #0d9488;
        background: #f0fdf4;
        color: #0d9488;
    }
    .pay-method-btn i { font-size: 20px; }
    .pay-method-btn span { font-size: 12px; font-weight: 700; }
    
    #posCartTable tr:hover { background: #fcfcfd; }
    .modal-overlay {
        animation: fadeIn 0.2s ease-out;
    }
    .modal-content {
        animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .mode-btn.active {
        background: #0f172a !important;
        color: white !important;
        border-color: #0f172a !important;
    }
    .pay-method-btn.active {
        background: #0f172a;
        color: white;
        border-color: #0f172a;
    }
</style>

<script>
document.getElementById('posSaleForm').addEventListener('submit', function(e) {
    const paymentMode = document.getElementById('paymentMode').value;
    const totalAmount = posCart.reduce((sum, item) => sum + (item.price * item.qty), 0) - (parseFloat(document.getElementById('posDiscount').value) || 0);
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

    // 1. Full Pay Validation
    if (paymentMode === 'FULL' && amountPaid < totalAmount) {
        e.preventDefault();
        showPosAlert(`Insufficient payment: "Full Pay" requires at least ₦${totalAmount.toLocaleString()}. For partial payments, please select "Partial / Credit".`, 'error');
        return;
    }

    // 2. Credit Limit Validation (for Partial/Credit)
    const customerEl = document.getElementById('posCustomer');
    const selectedOption = customerEl.options[customerEl.selectedIndex];
    
    if (selectedOption.value) {
        const creditLimit = parseFloat(selectedOption.getAttribute('data-credit-limit')) || 0;
        const currentBalance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
        const newBalance = currentBalance + Math.max(0, totalAmount - amountPaid);

        if (newBalance > creditLimit && creditLimit > 0) {
            e.preventDefault();
            showPosAlert(`Transaction denied: This would exceed the customer's credit limit of ₦${creditLimit.toLocaleString()}. Current debt: ₦${currentBalance.toLocaleString()}, New potential debt: ₦${newBalance.toLocaleString()}.`, 'error');
        }
    }
});

let posCart = [];

let currentCategoryId = 'all';

function filterCategory(id) {
    currentCategoryId = id;
    
    // Update active class on buttons
    const btns = document.querySelectorAll('.cat-btn');
    btns.forEach(btn => {
        if (btn.getAttribute('data-id') == id) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Apply filters (Category + Search)
    applyCatalogFilters();
}

function applyCatalogFilters() {
    const query = document.getElementById('posSearch').value.toLowerCase().trim();
    const items = document.querySelectorAll('.pos-item');
    
    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        const barcode = item.getAttribute('data-barcode').toLowerCase();
        const groupId = item.getAttribute('data-group-id');
        
        const matchesCategory = (currentCategoryId === 'all' || groupId == currentCategoryId);
        const matchesSearch = (name.includes(query) || barcode.includes(query));
        
        if (matchesCategory && matchesSearch) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Update Search & Barcode Logic to use combined filters
const searchInput = document.getElementById('posSearch');
searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.pos-item');
    
    // Check if it's a barcode (exactly matching) - skip category check for barcodes
    let barcodeFound = false;
    for (let item of items) {
        const barcode = item.getAttribute('data-barcode').toLowerCase();
        if (query === barcode && query !== '') {
            addToCart(item);
            this.value = '';
            barcodeFound = true;
            applyCatalogFilters(); // Refresh display
            return;
        }
    }

    applyCatalogFilters();
});

// Shortcut F1 to Focus Search
window.addEventListener('keydown', function(e) {
    if (e.key === 'F1') {
        e.preventDefault();
        searchInput.focus();
    }
});

function addToCart(el) {
    const id = el.getAttribute('data-id');
    const name = el.getAttribute('data-name');
    const price = parseFloat(el.getAttribute('data-price'));
    const stock = parseInt(el.getAttribute('data-stock'));

    const existing = posCart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < stock) {
            existing.qty++;
        } else {
            showPosAlert('Selected item is out of stock!', 'error');
        }
    } else {
        posCart.push({ id, name, price, qty: 1, stock });
    }

    renderPosCart();
}

function renderPosCart() {
    const body = document.getElementById('posCartBody');
    const emptyMsg = document.getElementById('cartEmptyMsg');
    body.innerHTML = '';
    
    if (posCart.length === 0) {
        emptyMsg.style.display = 'block';
        document.getElementById('checkoutBtn').disabled = true;
        document.getElementById('checkoutBtn').style.background = '#94a3b8';
        document.getElementById('holdBtn').disabled = true;
        document.getElementById('holdBtn').style.opacity = '0.5';
    } else {
        emptyMsg.style.display = 'none';
        document.getElementById('checkoutBtn').disabled = false;
        document.getElementById('checkoutBtn').style.background = '#0d9488';
        document.getElementById('holdBtn').disabled = false;
        document.getElementById('holdBtn').style.opacity = '1';
        
        posCart.forEach((item, index) => {
            body.innerHTML += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 15px; font-size: 13px;">
                        <div style="font-weight: 700; color: #1e293b;">${item.name}</div>
                        <div style="font-size: 11px; color: #64748b;">₦${item.price.toLocaleString()}</div>
                    </td>
                    <td style="padding: 12px 15px; text-align: center;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <button onclick="updatePosQty(${index}, -1)" style="width: 24px; height: 24px; border-radius: 4px; border: 1px solid #e2e8f0; background: white; cursor: pointer;">-</button>
                            <span style="font-weight: 700; width: 30px;">${item.qty}</span>
                            <button onclick="updatePosQty(${index}, 1)" style="width: 24px; height: 24px; border-radius: 4px; border: 1px solid #e2e8f0; background: white; cursor: pointer;">+</button>
                        </div>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; font-weight: 700; color: #1e293b;">₦${(item.price * item.qty).toLocaleString()}</td>
                    <td style="padding: 12px 15px; text-align: right;">
                        <button onclick="removePosItem(${index})" style="color: #ef4444; background: none; border: none; cursor: pointer;"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
        });
    }
    
    calculateTotals();
}

function updatePosQty(index, delta) {
    const item = posCart[index];
    if (delta > 0 && item.qty < item.stock) {
        item.qty++;
    } else if (delta < 0 && item.qty > 1) {
        item.qty--;
    }
    renderPosCart();
}

function removePosItem(index) {
    posCart.splice(index, 1);
    renderPosCart();
}

function clearCart() {
    if (posCart.length === 0) return;
    showPosConfirm('Are you sure you want to clear all items in the cart?', function() {
        posCart = [];
        renderPosCart();
    });
}

function calculateTotals() {
    const subtotal = posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const discount = parseFloat(document.getElementById('posDiscount').value) || 0;
    const total = Math.max(0, subtotal - discount);

    document.getElementById('posSubtotal').innerText = '₦ ' + subtotal.toLocaleString();
    document.getElementById('posGrandTotal').innerText = '₦ ' + total.toLocaleString();
    document.getElementById('modalTotal').innerText = '₦ ' + total.toLocaleString();
    
    // For form submission
    document.getElementById('hiddenCartData').value = JSON.stringify(posCart);
    
    // Update amountPaid and change based on current mode
    const paymentMode = document.getElementById('paymentMode').value;
    if (paymentMode === 'FULL' || paymentMode === 'PARTIAL') {
        document.getElementById('amountPaid').value = total;
    } else if (paymentMode === 'SPLIT') {
        calculateSplitTotal(); // Recalculate split total if in split mode
    }
    calculateChange();
}

function openCheckout() {
    const customerEl = document.getElementById('posCustomer');
    const customerId = customerEl.value;
    
    document.getElementById('hiddenCustomer').value = customerId;
    document.getElementById('checkoutModal').style.display = 'flex';
    // Ensure initial payment mode is set and UI reflects it
    setPaymentMode('FULL'); 
    document.getElementById('amountPaid').focus();
    document.getElementById('amountPaid').select();
}

function closeCheckout() {
    document.getElementById('checkoutModal').style.display = 'none';
}

function selectPay(el) {
    // UI selection
    const btns = document.querySelectorAll('.pay-method-btn');
    btns.forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    
    // Check hidden radio
    const radio = el.parentElement.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function setPaymentMode(mode) {
    document.getElementById('paymentMode').value = mode;
    
    // Update UI
    const btns = document.querySelectorAll('.mode-btn');
    btns.forEach(b => b.classList.remove('active'));
    document.getElementById('mode' + mode.charAt(0) + mode.slice(1).toLowerCase()).classList.add('active');
    
    // Toggle inputs
    const fullInp = document.getElementById('fullPaymentInput');
    const splitInp = document.getElementById('splitPaymentInputs');
    const methodSec = document.querySelector('label[style*="text-transform: uppercase;"]').parentElement; // Payment Method label container
    
    if (mode === 'SPLIT') {
        fullInp.style.display = 'none';
        splitInp.style.display = 'block';
        methodSec.style.display = 'none';
        document.getElementById('changeLabel').innerText = 'Total Distributed';
        // Clear split inputs when switching to split mode
        document.getElementById('splitCash').value = '';
        document.getElementById('splitPos').value = '';
        document.getElementById('splitTransfer').value = '';
    } else {
        fullInp.style.display = 'block';
        splitInp.style.display = 'none';
        methodSec.style.display = 'block';
        document.getElementById('changeLabel').innerText = mode === 'FULL' ? 'Change Due' : 'Balance Due';
    }
    
    calculateTotals();
}

function calculateSplitTotal() {
    const cash = parseFloat(document.getElementById('splitCash').value) || 0;
    const pos = parseFloat(document.getElementById('splitPos').value) || 0;
    const transfer = parseFloat(document.getElementById('splitTransfer').value) || 0;
    const totalDist = cash + pos + transfer;
    
    document.getElementById('posChange').innerText = '₦ ' + totalDist.toLocaleString();
    
    // Prepare JSON for submission
    const details = {
        'CASH': cash,
        'POS': pos,
        'TRANSFER': transfer
    };
    document.getElementById('paymentSplitDetails').value = JSON.stringify(details);
    document.getElementById('amountPaid').value = totalDist; // Set amountPaid for form submission
}

function calculateChange() {
    const total = posCart.reduce((sum, item) => sum + (item.price * item.qty), 0) - (parseFloat(document.getElementById('posDiscount').value) || 0);
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const mode = document.getElementById('paymentMode').value;
    
    if (mode === 'FULL') {
        const change = Math.max(0, paid - total);
        document.getElementById('posChange').innerText = '₦ ' + change.toLocaleString();
    } else if (mode === 'PARTIAL') {
        const balance = Math.max(0, total - paid);
        document.getElementById('posChange').innerText = '₦ ' + balance.toLocaleString();
    } else { // SPLIT mode
        calculateSplitTotal(); // This function already updates posChange
    }
}

// Custom Alert Logic
function showPosAlert(message, type = 'success') {
    const modal = document.getElementById('posAlertModal');
    const icon = document.getElementById('alertIcon');
    const title = document.getElementById('alertTitle');
    const msg = document.getElementById('alertMessage');
    
    msg.innerText = message;
    
    if (type === 'success') {
        icon.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
        title.innerText = 'Success!';
        title.style.color = '#10b981';
    } else {
        icon.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>';
        title.innerText = 'Error';
        title.style.color = '#ef4444';
    }
    
    modal.style.display = 'flex';
}

function closePosAlert() {
    document.getElementById('posAlertModal').style.display = 'none';
}

// Custom Confirm Logic
function showPosConfirm(message, onConfirm) {
    const modal = document.getElementById('posConfirmModal');
    document.getElementById('confirmMessage').innerText = message;
    
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.onclick = function() {
        onConfirm();
        closePosConfirm();
    };
    
    modal.style.display = 'flex';
}

function closePosConfirm() {
    document.getElementById('posConfirmModal').style.display = 'none';
}

// Hold Logic
async function holdTransaction() {
    if (posCart.length === 0) return;
    
    const formData = new FormData();
    formData.append('customer_id', document.getElementById('posCustomer').value);
    formData.append('cart_data', JSON.stringify(posCart));
    formData.append('discount', document.getElementById('posDiscount').value);

    try {
        const response = await fetch('modules/transaction/process_hold.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            posCart = [];
            renderPosCart();
            showPosAlert('Transaction held successfully!');
        } else {
            showPosAlert(result.message, 'error');
        }
    } catch (error) {
        showPosAlert('An error occurred while holding the transaction.', 'error');
    }
}

async function openRecallModal() {
    const list = document.getElementById('heldTransactionsList');
    list.innerHTML = '<div style="text-align:center; padding:20px;">Loading...</div>';
    document.getElementById('recallModal').style.display = 'flex';

    try {
        const response = await fetch('modules/transaction/get_held.php');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            list.innerHTML = '';
            result.data.forEach(h => {
                const date = new Date(h.created_at).toLocaleString();
                const cart = JSON.parse(h.cart_data);
                const itemsCount = cart.reduce((sum, item) => sum + item.qty, 0);
                const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0) - h.discount;

                list.innerHTML += `
                    <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:700; color:#1e293b;">${h.customer_name || 'Walk-in Customer'}</div>
                            <div style="font-size:12px; color:#64748b;">${date} • ${itemsCount} items</div>
                            <div style="font-weight:800; color:#0d9488; margin-top:5px;">₦${total.toLocaleString()}</div>
                        </div>
                        <button onclick="recallTransaction(${h.id})" style="padding:10px 20px; background:#6366f1; color:white; border:none; border-radius:8px; font-weight:700; cursor:pointer;">Recall</button>
                    </div>
                `;
            });
        } else {
            list.innerHTML = '<div style="text-align:center; padding:40px; color:#94a3b8;">No held transactions found.</div>';
        }
    } catch (error) {
        list.innerHTML = '<div style="text-align:center; padding:20px; color:#ef4444;">Failed to load data.</div>';
    }
}

function closeRecallModal() {
    document.getElementById('recallModal').style.display = 'none';
}

async function recallTransaction(id) {
    if (posCart.length > 0) {
        showPosConfirm('The current cart has items. Recalling will replace them. Continue?', function() {
            execRecall(id);
        });
    } else {
        execRecall(id);
    }
}

async function execRecall(id) {
    try {
        const response = await fetch('modules/transaction/recall_hold.php?id=' + id);
        const result = await response.json();
        
        if (result.success) {
            const h = result.data;
            posCart = JSON.parse(h.cart_data);
            document.getElementById('posCustomer').value = h.customer_id || '';
            document.getElementById('posDiscount').value = h.discount;
            renderPosCart();
            closeRecallModal();
        } else {
            showPosAlert(result.message, 'error');
        }
    } catch (error) {
        showPosAlert('An error occurred while recalling the transaction.', 'error');
    }
}
</script>
