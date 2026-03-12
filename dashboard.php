<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$page = $_GET['page'] ?? 'overview';
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Gbàgede-Ọjà</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header" style="justify-content: space-between; padding: 20px 15px;">
            <img src="assets/img/logo.png" alt="Logo" style="max-height: 40px; filter: brightness(0) invert(1);">
            <i class="fas fa-times sidebar-close" id="closeSidebar" style="color: white; cursor: pointer; display: none;"></i>
        </div>

        <div class="nav-menu">
            <!-- 0. POS Menu -->
            <?php if (hasPermission('pos')): ?>
            <div class="nav-item">
                <a class="nav-link" onclick="toggleDropdown(this)" style="background: rgba(99, 102, 241, 0.1); border-left: 4px solid #6366f1;">
                    <div style="color: #6366f1; font-weight: 700;">
                        <i class="fas fa-cash-register"></i>
                        <span>POS</span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 10px; color: #6366f1;"></i>
                </a>
                <div class="dropdown-items">
                    <a href="?page=pos" class="dropdown-link">Terminal</a>
                    <a href="?page=receipt_list" class="dropdown-link">Recent Sales</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 1. Transaction Menu -->
            <?php if (hasPermission('transaction')): ?>
            <div class="nav-item">
                <a class="nav-link" onclick="toggleDropdown(this)">
                    <div>
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transaction</span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                </a>
                <div class="dropdown-items">
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Purchase
                        </a>
                        <div class="submenu-container">
                            <a href="?page=purchase_invoice" class="dropdown-link nested">Invoice</a>
                            <a href="?page=purchase_order" class="dropdown-link nested">Order</a>
                        </div>
                    </div>
                    
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Stock
                        </a>
                        <div class="submenu-container">
                            <a href="?page=inventory_transfer" class="dropdown-link nested">Inventory Transfer</a>
                            <a href="?page=inventory_adjustment" class="dropdown-link nested">Inventory Adjustment</a>
                        </div>
                    </div>
                    
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Receipts
                        </a>
                        <div class="submenu-container">
                            <a href="?page=receipt_new" class="dropdown-link nested">New</a>
                            <a href="?page=receipt_list" class="dropdown-link nested">List</a>
                            <a href="?page=receipt_analysis" class="dropdown-link nested">Analysis</a>
                        </div>
                    </div>
                    <a href="?page=payments" class="dropdown-link">Payments</a>
                    <a href="?page=expenses" class="dropdown-link">Expenses</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. Report Menu -->
            <?php if (hasPermission('reports')): ?>
            <div class="nav-item">
                <a class="nav-link" onclick="toggleDropdown(this)">
                    <div>
                        <i class="fas fa-chart-bar"></i>
                        <span>Report</span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                </a>
                <div class="dropdown-items">
                    <a href="?page=sales_report" class="dropdown-link">Sales</a>
                    <a href="?page=inventory_report" class="dropdown-link">Stock</a>
                    <a href="?page=balance_report" class="dropdown-link">Balance</a>
                    <a href="?page=user_activity" class="dropdown-link">User Activity</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. Set up Menu -->
            <?php if (hasPermission('setup')): ?>
            <div class="nav-item">
                <a class="nav-link" onclick="toggleDropdown(this)">
                    <div>
                        <i class="fas fa-cog"></i>
                        <span>Set up</span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                </a>
                <div class="dropdown-items">
                    <?php if (hasPermission('items')): ?>
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Items
                        </a>
                        <div class="submenu-container">
                            <a href="?page=items" class="dropdown-link nested">Items</a>
                            <a href="?page=units" class="dropdown-link nested">Units</a>
                            <a href="?page=subgroups" class="dropdown-link nested">Sub-groups</a>
                            <a href="?page=groups" class="dropdown-link nested">Groups</a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('customers')): ?><a href="?page=customers" class="dropdown-link">Customers</a><?php endif; ?>
                    <?php if (hasPermission('vendors')): ?><a href="?page=vendors" class="dropdown-link">Vendor</a><?php endif; ?>
                    <?php if (hasPermission('sales_reps')): ?><a href="?page=sales_reps" class="dropdown-link">Sales Representatives</a><?php endif; ?>
                    <?php if (hasPermission('manufacturers')): ?><a href="?page=manufacturers" class="dropdown-link">Manufacturer</a><?php endif; ?>
                    <?php if (hasPermission('payment_methods')): ?><a href="?page=payment_methods" class="dropdown-link">Payment Method</a><?php endif; ?>
                    <?php if (hasPermission('users')): ?>
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Users
                        </a>
                        <div class="submenu-container">
                            <a href="?page=user_accounts" class="dropdown-link nested">Account</a>
                            <a href="?page=user_groups" class="dropdown-link nested">Group</a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (hasPermission('setup', 'edit')): ?>
                    <div class="submenu-wrapper">
                        <a href="javascript:void(0)" class="dropdown-link toggle-submenu" onclick="toggleSubSubmenu(this)">
                            Others
                        </a>
                        <div class="submenu-container">
                            <a href="?page=company_profile" class="dropdown-link nested">Company</a>
                            <a href="?page=branch" class="dropdown-link nested">Branch</a>
                            <a href="?page=till" class="dropdown-link nested">Till</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link logout-btn">
                <div>
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header>
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-bars mobile-toggle" id="mobileToggle" style="font-size: 20px; cursor: pointer; display: none;"></i>
                <div class="breadcrumb" style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
                    <i class="fas fa-home" style="opacity: 0.4;"></i>
                    <span style="opacity: 0.4;">Dashboard</span>
                    <?php
                    $title_map = [
                        'purchase_invoice' => 'Purchase / Invoice',
                        'purchase_order' => 'Purchase / Order',
                        'inventory_transfer' => 'Stock / Transfer',
                        'inventory_adjustment' => 'Stock / Adjustment',
                        'user_accounts' => 'Users / Account',
                        'user_groups' => 'Users / Group',
                        'subgroups' => 'Items / Sub-groups',
                        'groups' => 'Items / Groups',
                        'company_profile' => 'Others / Company',
                        'branch' => 'Others / Branch',
                        'till' => 'Others / Till'
                    ];
                    $display_title = $title_map[$page] ?? ucfirst(str_replace('_', ' ', $page));
                    if ($page !== 'overview') {
                        echo '<i class="fas fa-chevron-right" style="font-size: 10px; opacity: 0.3;"></i>';
                        echo '<span style="color: var(--primary);">' . $display_title . '</span>';
                    }
                    ?>
                </div>
            </div>
            <div class="user-profile" style="background: #f8fafc; padding: 6px 6px 6px 15px; border-radius: 50px; border: 1px solid #e2e8f0;">
                <div style="text-align: right; line-height: 1.2;">
                    <div style="font-weight: 700; color: #1e293b; font-size: 13px;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($role) ?></div>
                </div>
                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; border: 2px solid white; box-shadow: 0 0 0 1px #e2e8f0;">
                    <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                </div>
            </div>
        </header>

        <main class="content-body">
            <?php
            $allowed_pages = [
                'user_accounts' => 'modules/setup/users.php',
                'user_groups' => 'modules/setup/user_groups.php',
                'items' => 'modules/setup/products.php',
                'units' => 'modules/setup/units.php',
                'subgroups' => 'modules/setup/categories.php',
                'groups' => 'modules/setup/groups.php',
                'customers' => 'modules/setup/customers.php',
                'vendors' => 'modules/setup/vendors.php',
                'sales_reps' => 'modules/setup/sales_reps.php',
                'manufacturers' => 'modules/setup/manufacturers.php',
                'payment_methods' => 'modules/setup/payment_methods.php',
                'branch' => 'modules/setup/branch.php',
                'till' => 'modules/setup/till.php',
                'company_profile' => 'modules/setup/company_profile.php',
                'purchase_invoice' => 'modules/transaction/stock_in.php',
                'purchase_order' => 'modules/transaction/purchase_order.php',
                'inventory_transfer' => 'modules/transaction/inventory_transfer.php',
                'inventory_adjustment' => 'modules/transaction/adjustments.php',
                'receipt_new' => 'modules/transaction/sales.php',
                'receipt_list' => 'modules/report/sales_report.php',
                'receipt_analysis' => 'modules/report/sales_analysis.php',
                'payments' => 'modules/transaction/payments.php',
                'expenses' => 'modules/transaction/expenses.php',
                'sales_report' => 'modules/report/sales_report.php',
                'inventory_report' => 'modules/report/inventory_report.php',
                'balance_report' => 'modules/report/balance_report.php',
                'user_activity' => 'modules/report/user_activity.php',
                'pos' => 'modules/transaction/pos.php',
            ];

            if ($page === 'overview') {
                // Fetch stats for widgets
                $today = date('Y-m-d');
                $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM transactions WHERE type = 'Sale' AND DATE(transaction_date) = ?");
                $stmt->execute([$today]);
                $today_sales = $stmt->fetch()['total'] ?? 0;

                $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE current_stock <= min_stock");
                $low_stock = $stmt->fetch()['total'] ?? 0;

                $this_month = date('Y-m-01');
                $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM transactions WHERE type = 'Sale' AND DATE(transaction_date) >= ?");
                $stmt->execute([$this_month]);
                $monthly_sales = $stmt->fetch()['total'] ?? 0;

                $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE type = 'Sale'");
                $total_sales_count = $stmt->fetch()['total'] ?? 0;
                ?>
                <h1>Welcome to the Inventory Dashboard</h1>
                <p>Select a menu item from the sidebar to manage your inventory.</p>
                
                <?php if (hasPermission('reports')): ?>
                <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #0d8a72;">
                        <h3 style="color: #64748b; font-size: 12px; text-transform: uppercase;">Today's Sales</h3>
                        <p style="font-size: 20px; font-weight: 700; margin-top: 5px;">₦ <?= number_format($today_sales, 0) ?></p>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6;">
                        <h3 style="color: #64748b; font-size: 12px; text-transform: uppercase;">Monthly Rev.</h3>
                        <p style="font-size: 20px; font-weight: 700; margin-top: 5px;">₦ <?= number_format($monthly_sales, 0) ?></p>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h3 style="color: #64748b; font-size: 12px; text-transform: uppercase;">Total Sales</h3>
                        <p style="font-size: 20px; font-weight: 700; margin-top: 5px;"><?= $total_sales_count ?></p>
                    </div>
                    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h3 style="color: #64748b; font-size: 12px; text-transform: uppercase;">Low Stock</h3>
                        <p style="font-size: 20px; font-weight: 700; margin-top: 5px; color: #ef4444;"><?= $low_stock ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 40px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 16px;">Recent Transactions (Receipts)</h3>
                        <a href="?page=sales_report" style="color: var(--primary); font-size: 13px; text-decoration: none; font-weight: 600;">View All Reports</a>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead style="background: #f8fafc; font-size: 13px; color: #64748b;">
                            <tr>
                                <th style="padding: 15px;">Receipt ID</th>
                                <th style="padding: 15px;">Customer/Note</th>
                                <th style="padding: 15px;">Amount</th>
                                <th style="padding: 15px;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM transactions WHERE type = 'Sale' ORDER BY transaction_date DESC LIMIT 5");
                            $recent = $stmt->fetchAll();
                            foreach ($recent as $r): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                                <td style="padding: 15px; font-weight: 600;">#REC-<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td style="padding: 15px; color: #64748b;"><?= htmlspecialchars($r['notes'] ?: 'Walk-in Customer') ?></td>
                                <td style="padding: 15px; font-weight: 700; color: #166534;">₦ <?= number_format($r['total_amount'], 0) ?></td>
                                <td style="padding: 15px;"><?= date('M d, H:i', strtotime($r['transaction_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent)): ?>
                            <tr>
                                <td colspan="4" style="padding: 30px; text-align: center; color: #64748b;">No recent sales found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } elseif (is_string($page) && array_key_exists($page, $allowed_pages)) {
                $file_path = $allowed_pages[(string)$page];
                if (isset($allowed_pages[$page])) {
                // Determine module mapping for permission check
                $module_map = [
                    'pos' => 'pos',
                    'receipt_list' => 'pos',
                    'purchase_invoice' => 'transaction',
                    'purchase_order' => 'transaction',
                    'inventory_transfer' => 'transaction',
                    'inventory_adjustment' => 'transaction',
                    'receipt_new' => 'transaction',
                    'payments' => 'transaction',
                    'expenses' => 'transaction',
                    'sales_report' => 'reports',
                    'inventory_report' => 'reports',
                    'balance_report' => 'reports',
                    'user_activity' => 'reports',
                    'items' => 'items',
                    'units' => 'items',
                    'subgroups' => 'items',
                    'groups' => 'items',
                    'customers' => 'customers',
                    'vendors' => 'vendors',
                    'user_accounts' => 'users',
                    'user_groups' => 'users'
                ];
                
                $required_module = $module_map[$page] ?? 'setup';
                
                if (hasPermission($required_module)) {
                    if (file_exists($file_path)) {
                        include $file_path;
                    } else {
                        echo "<h2>Error</h2><p>Module file not found: $file_path</p>";
                    }
                } else {
                    echo '<div class="premium-card" style="padding: 40px; text-align: center; color: #64748b;">';
                    echo '<i class="fas fa-lock" style="font-size: 48px; margin-bottom: 20px; opacity: 0.2;"></i>';
                    echo '<h2>Access Denied</h2>';
                    echo '<p>You do not have permission to access the ' . htmlspecialchars($display_title) . ' module.</p>';
                    echo '</div>';
                }
            } else {
                    echo "<h2>Error</h2><p>Module file not found: $file_path</p>";
                }
            } else {
                echo "<h2>Error</h2><p>Page not found.</p>";
            }
            ?>
        </main>
    </div>

    <script>
        function toggleDropdown(el) {
            const parent = el.parentElement;
            const isOpen = parent.classList.contains('open');
            
            // Close all other dropdowns
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('open');
            });
            
            // If it wasn't open, open it now (toggle behavior)
            if (!isOpen) {
                parent.classList.add('open');
            }
        }

        function toggleSubSubmenu(el) {
            const wrapper = el.parentElement;
            const container = wrapper.querySelector('.submenu-container');
            const isOpen = wrapper.classList.contains('active');
            
            // Close other sibling submenus
            el.closest('.dropdown-items').querySelectorAll('.submenu-wrapper').forEach(item => {
                if (item !== wrapper) {
                    item.classList.remove('active');
                }
            });
            
            wrapper.classList.toggle('active');
        }

        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.getElementById('mobileToggle');
        const closeSidebar = document.getElementById('closeSidebar');

        mobileToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });

        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });

        // Auto-open active dropdown on page load
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const page = params.get('page');
            const error = params.get('error');

            // Show Access Denied Modal if unauthorized
            if (error === 'unauthorized') {
                const modal = document.getElementById('accessDeniedModal');
                if (modal) modal.style.display = 'flex';
                // Remove error from URL without refreshing
                const newUrl = window.location.pathname + (page ? '?page=' + page : '');
                window.history.replaceState({}, document.title, newUrl);
            }

            if (page) {
                const activeLink = document.querySelector(`.dropdown-link[href="?page=${page}"]`);
                if (activeLink) {
                    const navItem = activeLink.closest('.nav-item');
                    if (navItem) {
                        navItem.classList.add('open');
                        
                        // Also open nested submenu wrapper if it exists
                        const submenuWrapper = activeLink.closest('.submenu-wrapper');
                        if (submenuWrapper) {
                            submenuWrapper.classList.add('active');
                        }
                        
                        activeLink.style.color = 'white';
                        activeLink.style.fontWeight = '600';
                    }
                }
            }
        });
    </script>

    <!-- Access Denied Modal -->
    <div id="accessDeniedModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);">
        <div class="premium-card" style="width: 400px; padding: 40px; text-align: center; background: white; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="width: 80px; height: 80px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: #ef4444; font-size: 32px;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 style="font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Access Denied</h3>
            <p style="color: #64748b; line-height: 1.6; margin-bottom: 30px;">You don't have the required permissions to access this area. Please contact your administrator if you believe this is an error.</p>
            <button onclick="document.getElementById('accessDeniedModal').style.display='none'" style="width: 100%; padding: 14px; background: #1e293b; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: transform 0.2s;">
                Got it, take me back
            </button>
        </div>
    </div>
</body>
</html>
