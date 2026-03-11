<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch Products with Category and Unit
$stmt = $pdo->query("SELECT p.*, c.name as category_name, u.abbreviation as unit_name, p.unit_value 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN units u ON p.unit_id = u.id 
                    ORDER BY p.name ASC");
$products = $stmt->fetchAll();

// Fetch Categories and Units for modal
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
$units = $pdo->query("SELECT id, name, abbreviation FROM units WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'product_added') echo "New product listed successfully!";
        if ($success == 'product_updated') echo "Product details updated.";
        if ($success == 'product_deleted') echo "Product removed from inventory.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Product Management</h2>
        <p class="page-subtitle">Catalog and manage your inventory items, pricing, and stock levels.</p>
    </div>
    <button onclick="openProductModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Add New Item
    </button>
</div>

<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Product Details</th>
                <th>Category</th>
                <th>Purchase Price</th>
                <th>Sale Price</th>
                <th>Stock Level</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; color: #10b981;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #0d3d36;"><?= htmlspecialchars($p['name']) ?></div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="font-size: 11px; color: #64748b; font-weight: 500;"><?= htmlspecialchars($p['unit_value'] . ($p['unit_name'] ?? '')) ?></div>
                                <?php if($p['barcode']): ?>
                                    <div style="font-size: 10px; color: #0d9488; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; font-weight: 700;">
                                        <i class="fas fa-barcode" style="font-size: 8px; margin-right: 3px;"></i> <?= htmlspecialchars($p['barcode']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size: 12px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 20px;">
                        <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                    </span>
                </td>
                <td style="font-size: 13px;">
                    <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Cost Price</div>
                    <div style="font-weight: 700; color: #0d3d36;">₦<?= number_format($p['purchase_price'], 0) ?></div>
                </td>
                <td style="font-size: 13px;">
                    <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Sale Price</div>
                    <div style="font-weight: 700; color: #0d3d36;">₦<?= number_format($p['sale_price'], 0) ?></div>
                </td>
                <td style="font-size: 13px;">
                    <?php 
                    $stock_color = '#0d3d36';
                    $warning_text = '';
                    $warning_color = '';
                    
                    if ($p['current_stock'] <= 0) {
                        $stock_color = '#ef4444';
                        $warning_text = 'Out of Stock';
                        $warning_color = '#ef4444';
                    } elseif ($p['current_stock'] <= $p['min_stock']) {
                        $stock_color = '#f59e0b';
                        $warning_text = 'Low Stock Warning';
                        $warning_color = '#f59e0b';
                    }
                    ?>
                    <div style="font-weight: 700; color: <?= $stock_color ?>;">
                        <?= number_format($p['current_stock'], 0) ?>
                    </div>
                    <?php if ($warning_text): ?>
                        <div style="color: <?= $warning_color ?>; font-size: 10px; font-weight: 700; text-transform: uppercase;"><?= $warning_text ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $badge_class = 'badge-green';
                    $badge_icon = 'fa-check-circle';
                    $badge_text = 'In Stock';
                    $badge_style = '';

                    if ($p['current_stock'] <= 0) {
                        $badge_class = 'badge-red';
                        $badge_icon = 'fa-times-circle';
                        $badge_text = 'Out of Stock';
                    } elseif ($p['current_stock'] <= $p['min_stock']) {
                        $badge_class = ''; // Custom amber style
                        $badge_style = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
                        $badge_icon = 'fa-exclamation-triangle';
                        $badge_text = 'Restock Needed';
                    }
                    ?>
                    <span class="premium-badge <?= $badge_class ?>" style="<?= $badge_style ?>">
                        <i class="fas <?= $badge_icon ?>"></i>
                        <?= $badge_text ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick="quickGenerateBarcode(<?= $p['id'] ?>, '<?= $p['barcode'] ?>')" class="action-btn" style="margin-right: 8px; color: #6366f1;" title="Quick Barcode">
                        <i class="fas fa-barcode" style="font-size: 12px;"></i>
                    </button>
                    <button onclick='editProduct(<?= json_encode($p) ?>)' class="action-btn" style="margin-right: 8px;">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <button onclick="deleteProduct(<?= $p['id'] ?>)" class="action-btn" style="color: #ef4444;">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div id="productModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 550px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add New Product</h3>
            <button onclick="closeProductModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="productForm" action="modules/setup/save_product.php" method="POST">
            <input type="hidden" id="product_id" name="id">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Product Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="e.g. Peak Milk 400g" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Barcode / SKU</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="barcode" name="barcode" placeholder="Scan or generate..." style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                        <button type="button" onclick="generateBarcode()" class="action-btn" title="Generate Barcode" style="width: 45px; height: 45px; border-radius: 10px; background: #f1f5f9; color: #0d3d36;">
                            <i class="fas fa-barcode"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Category / Sub-group</label>
                    <select id="category_id" name="category_id" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
                        <option value="">-- No Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Unit of Measurement</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" id="unit_value" name="unit_value" value="1" min="1" style="width: 80px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                        <select id="unit_id" name="unit_id" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
                            <option value="">-- Select Unit --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['abbreviation']) ?> (<?= htmlspecialchars($u['name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Purchase Price (₦)</label>
                    <input type="number" step="1" id="purchase_price" name="purchase_price" required placeholder="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Sale Price (₦)</label>
                    <input type="number" step="1" id="sale_price" name="sale_price" required placeholder="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Current Stock</label>
                    <input type="number" step="1" id="current_stock" name="current_stock" value="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Alert at Level</label>
                    <input type="number" step="1" id="min_stock" name="min_stock" value="5" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeProductModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 3000; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
    <div class="premium-card modal-content" style="width: 400px; padding: 30px; text-align: center; border-radius: 20px;">
        <div id="confirmIcon" style="width: 60px; height: 60px; border-radius: 50%; background: #f0f9ff; color: #0369a1; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px;">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 id="confirmTitle" style="margin: 0 0 10px 0; font-size: 18px; font-weight: 800; color: #0f172a;">Are you sure?</h3>
        <p id="confirmMessage" style="color: #64748b; font-size: 14px; margin: 0 0 25px 0; line-height: 1.5;">This action cannot be undone.</p>
        
        <div style="display: flex; gap: 12px;">
            <button id="confirmCancelBtn" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px; cursor: pointer; font-weight: 700; color: #64748b; transition: all 0.2s;">Cancel</button>
            <button id="confirmActionBtn" style="flex: 1; padding: 12px; border: none; background: #0d9488; color: white; border-radius: 12px; cursor: pointer; font-weight: 700; transition: all 0.2s;">Confirm</button>
        </div>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('productModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add New Product';
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    // Auto focus name
    setTimeout(() => document.getElementById('name').focus(), 100);
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function editProduct(data) {
    document.getElementById('productModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Product Info';
    document.getElementById('product_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('barcode').value = data.barcode || '';
    document.getElementById('category_id').value = data.category_id || '';
    document.getElementById('unit_id').value = data.unit_id || '';
    document.getElementById('unit_value').value = data.unit_value || 1;
    document.getElementById('purchase_price').value = data.purchase_price;
    document.getElementById('sale_price').value = data.sale_price;
    document.getElementById('current_stock').value = data.current_stock;
    document.getElementById('min_stock').value = data.min_stock;
}

function generateBarcode() {
    // Generate a simple 12 digit random number for the barcode
    let barcode = '';
    for(let i = 0; i < 12; i++) {
        barcode += Math.floor(Math.random() * 10);
    }
    document.getElementById('barcode').value = barcode;
}

function quickGenerateBarcode(id, existingBarcode) {
    let message = 'Generate a unique barcode for this product?';
    if (existingBarcode && existingBarcode !== 'null' && existingBarcode !== '') {
        message = 'This product already has a barcode (' + existingBarcode + '). Generate a new one?';
    }

    showConfirmModal({
        title: 'Generate Barcode',
        message: message,
        icon: 'fa-barcode',
        iconBg: '#f5f3ff',
        iconColor: '#7c3aed',
        confirmText: 'Generate Now',
        confirmBg: '#7c3aed',
        onConfirm: () => {
            // Generate
            let newBarcode = '';
            for(let i = 0; i < 12; i++) {
                newBarcode += Math.floor(Math.random() * 10);
            }

            // Send via AJAX
            fetch('modules/setup/quick_generate_barcode.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&barcode=' + newBarcode
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            });
        }
    });
}

function showConfirmModal(options) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const iconContainer = document.getElementById('confirmIcon');
    const actionBtn = document.getElementById('confirmActionBtn');
    const cancelBtn = document.getElementById('confirmCancelBtn');

    title.innerText = options.title || 'Are you sure?';
    message.innerText = options.message || '';
    actionBtn.innerText = options.confirmText || 'Confirm';
    actionBtn.style.background = options.confirmBg || '#0d9488';
    
    if (options.icon) {
        iconContainer.innerHTML = `<i class="fas ${options.icon}"></i>`;
        iconContainer.style.background = options.iconBg || '#f0f9ff';
        iconContainer.style.color = options.iconColor || '#0369a1';
    }

    modal.style.display = 'flex';

    const handleConfirm = () => {
        modal.style.display = 'none';
        actionBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
        if (options.onConfirm) options.onConfirm();
    };

    const handleCancel = () => {
        modal.style.display = 'none';
        actionBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
        if (options.onCancel) options.onCancel();
    };

    actionBtn.onclick = handleConfirm;
    cancelBtn.onclick = handleCancel;
}

function deleteProduct(id) {
    showConfirmModal({
        title: 'Delete Product',
        message: 'Warning: Deleting this product will remove it from inventory. This action cannot be undone.',
        icon: 'fa-trash-alt',
        iconBg: '#fef2f2',
        iconColor: '#dc2626',
        confirmText: 'Delete Anyway',
        confirmBg: '#dc2626',
        onConfirm: () => {
            window.location.href = 'modules/setup/save_product.php?delete=' + id;
        }
    });
}
</script>
