<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');
require_once('../controllers/stats_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is PHARMACY ADMIN (role 1 only)
if (!isPharmacyAdmin()) {
    header("Location: ../index.php");
    exit();
}

$user_id = getUserId();
$user_name = $_SESSION['user_name'] ?? getUserName();
$user_role = getUserRoleName();

// Get comprehensive pharmacy analytics
$analytics_result = get_pharmacy_dashboard_ctr($user_id);
$analytics = $analytics_result['data'] ?? [];

// Extract stats for easy access
$total_products = $analytics['total_products'] ?? 0;
$low_stock_count = $analytics['low_stock_count'] ?? 0;
$out_of_stock_count = $analytics['out_of_stock_count'] ?? 0;
$total_stock_value = $analytics['total_stock_value'] ?? 0;

$total_orders = $analytics['total_orders'] ?? 0;
$pending_orders = $analytics['pending_orders'] ?? 0;
$completed_orders = $analytics['completed_orders'] ?? 0;

$total_revenue = $analytics['total_revenue'] ?? 0;
$monthly_revenue = $analytics['monthly_revenue'] ?? 0;
$today_revenue = $analytics['today_revenue'] ?? 0;
$weekly_revenue = $analytics['weekly_revenue'] ?? 0;

$sales_by_month = $analytics['sales_by_month'] ?? [];
$top_products = $analytics['top_products'] ?? [];
$recent_orders = $analytics['recent_orders'] ?? [];
$low_stock_items = $analytics['low_stock_items'] ?? [];
$order_status_distribution = $analytics['order_status_distribution'] ?? [];

// Get categories and brands count (for display)
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');
$category_obj = new Category();
$brand_obj = new Brand();

$categories = $category_obj->get_categories_by_user($user_id);
$brands = $brand_obj->get_all_brands($user_id);
$total_categories = count($categories);
$total_brands = count($brands);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #059669 0%, #047857 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .dashboard-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .dashboard-header h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.success::before { background: var(--success-gradient); }
        .stat-card.warning::before { background: var(--warning-gradient); }
        .stat-card.info::before { background: var(--info-gradient); }
        .stat-card.danger::before { background: var(--danger-gradient); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-card.primary .stat-icon { background: var(--primary-gradient); }
        .stat-card.success .stat-icon { background: var(--success-gradient); }
        .stat-card.warning .stat-icon { background: var(--warning-gradient); }
        .stat-card.info .stat-icon { background: var(--info-gradient); }
        .stat-card.danger .stat-icon { background: var(--danger-gradient); }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
            font-weight: 500;
        }

        .stat-action {
            margin-top: 1rem;
        }

        .stat-action a {
            color: #059669;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-action a:hover {
            color: #047857;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            height: 100%;
        }

        .content-card h3 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }

        .quick-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .quick-action-btn {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-color: #059669;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }

        .quick-action-btn i {
            font-size: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1><i class="fas fa-store-alt" style="margin-right: 0.75rem;"></i>Pharmacy Dashboard</h1>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Manage your pharmacy inventory and orders</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #64748b;">Welcome back,</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($user_name); ?></p>
                    <small style="color: #059669; font-weight: 600;"><?php echo $user_role; ?></small>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary animate-fade-in" style="animation-delay: 0.1s;">
                <div class="stat-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <h2 class="stat-value" id="totalProducts">
                    <?php echo $total_products; ?>
                </h2>
                <p class="stat-label">My Products</p>
                <div class="stat-action">
                    <a href="my_products.php">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card warning animate-fade-in" style="animation-delay: 0.2s;">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="stat-value" id="pendingOrders">
                    <?php echo $pending_orders; ?>
                </h2>
                <p class="stat-label">Pending Orders</p>
                <div class="stat-action">
                    <a href="orders.php">
                        Manage orders <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card success animate-fade-in" style="animation-delay: 0.3s;">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h2 class="stat-value" id="totalOrders">
                    <?php echo $total_orders; ?>
                </h2>
                <p class="stat-label">Total Orders</p>
                <div class="stat-action">
                    <a href="orders.php">
                        View history <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card info animate-fade-in" style="animation-delay: 0.4s;">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h2 class="stat-value" id="totalRevenue">
                    GH₵ <?php echo number_format($total_revenue, 2); ?>
                </h2>
                <p class="stat-label">Total Revenue</p>
                <div class="stat-action">
                    <a href="sales_report.php">
                        View analytics <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card success animate-fade-in" style="animation-delay: 0.5s;">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h2 class="stat-value">
                    <?php echo $total_categories; ?>
                </h2>
                <p class="stat-label">Categories</p>
                <div class="stat-action">
                    <a href="category.php">
                        Manage <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card primary animate-fade-in" style="animation-delay: 0.6s;">
                <div class="stat-icon">
                    <i class="fas fa-copyright"></i>
                </div>
                <h2 class="stat-value">
                    <?php echo $total_brands; ?>
                </h2>
                <p class="stat-label">Brands</p>
                <div class="stat-action">
                    <a href="brand.php">
                        Manage <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card warning animate-fade-in" style="animation-delay: 0.7s;">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="stat-value">
                    <?php echo $low_stock_count; ?>
                </h2>
                <p class="stat-label">Low Stock Items</p>
                <div class="stat-action">
                    <a href="my_products.php">
                        Check now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card danger animate-fade-in" style="animation-delay: 0.8s;">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="stat-value">
                    <?php echo $out_of_stock_count; ?>
                </h2>
                <p class="stat-label">Out of Stock</p>
                <div class="stat-action">
                    <a href="my_products.php">
                        Restock <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card animate-fade-in" style="animation-delay: 0.5s; margin-bottom: 2rem;">
            <h3><i class="fas fa-bolt" style="margin-right: 0.5rem;"></i>Quick Actions</h3>
            <div class="quick-action-grid">
                <a href="add_product.php" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add New Product</span>
                </a>
                <a href="my_products.php" class="quick-action-btn">
                    <i class="fas fa-boxes"></i>
                    <span>Manage Inventory</span>
                </a>
                <a href="orders.php" class="quick-action-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span>View Orders</span>
                </a>
                <a href="sales_report.php" class="quick-action-btn">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales Analytics</span>
                </a>
            </div>
        </div>

        <!-- Revenue Insights -->
        <div class="content-card animate-fade-in" style="animation-delay: 0.9s; margin-bottom: 2rem;">
            <h3><i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>Revenue Insights</h3>
            <div class="quick-action-grid">
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">Today's Revenue</div>
                    <div style="font-size: 1.75rem; font-weight: 700;">GH₵ <?php echo number_format($today_revenue, 2); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">This Week</div>
                    <div style="font-size: 1.75rem; font-weight: 700;">GH₵ <?php echo number_format($weekly_revenue, 2); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">This Month</div>
                    <div style="font-size: 1.75rem; font-weight: 700;">GH₵ <?php echo number_format($monthly_revenue, 2); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">All Time</div>
                    <div style="font-size: 1.75rem; font-weight: 700;">GH₵ <?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Sales Analytics Chart -->
        <div class="content-card animate-fade-in" style="animation-delay: 1.0s; margin-bottom: 2rem;">
            <h3><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i>Sales Trend (Last 6 Months)</h3>
            <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Recent Activity Grid -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card animate-fade-in" style="animation-delay: 1.1s;">
                <h3><i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Recent Orders</h3>
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <p>No recent orders</p>
                        <small>Orders from customers will appear here</small>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table" style="margin: 0;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="padding: 0.75rem; font-size: 0.8rem;">Invoice</th>
                                    <th style="padding: 0.75rem; font-size: 0.8rem;">Customer</th>
                                    <th style="padding: 0.75rem; font-size: 0.8rem;">Date</th>
                                    <th style="padding: 0.75rem; font-size: 0.8rem;">Amount</th>
                                    <th style="padding: 0.75rem; font-size: 0.8rem;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order):
                                    $status_colors = [
                                        'pending' => 'background: #fef3c7; color: #92400e;',
                                        'completed' => 'background: #d1fae5; color: #065f46;',
                                        'cancelled' => 'background: #fee2e2; color: #991b1b;',
                                        'processing' => 'background: #dbeafe; color: #1e40af;'
                                    ];
                                    $status_style = $status_colors[$order['order_status']] ?? 'background: #f1f5f9; color: #475569;';
                                ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 0.75rem; font-weight: 600;">#<?php echo $order['invoice_no']; ?></td>
                                        <td style="padding: 0.75rem;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td style="padding: 0.75rem; font-size: 0.85rem; color: #64748b;"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td style="padding: 0.75rem; font-weight: 600; color: #059669;">GH₵ <?php echo number_format($order['order_total'], 2); ?></td>
                                        <td style="padding: 0.75rem;">
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; <?php echo $status_style; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="orders.php" style="color: #059669; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                            View All Orders <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Low Stock Alerts -->
            <div class="content-card animate-fade-in" style="animation-delay: 1.2s;">
                <h3><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Low Stock Alerts</h3>
                <?php if (empty($low_stock_items)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No low stock items</p>
                        <small>Products running low on stock will appear here</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($low_stock_items as $item): ?>
                        <div style="display: flex; gap: 1rem; padding: 1rem; border-radius: 10px; background: #fef3c7; margin-bottom: 0.75rem; align-items: center;">
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($item['product_image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-pills" style="color: #94a3b8;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($item['product_title']); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #92400e;">
                                    <strong>Only <?php echo $item['product_stock']; ?> left!</strong> - GH₵ <?php echo number_format($item['product_price'], 2); ?>
                                </div>
                            </div>
                            <a href="my_products.php" style="padding: 0.5rem 1rem; background: #f59e0b; color: white; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; white-space: nowrap;">
                                Restock
                            </a>
                        </div>
                    <?php endforeach; ?>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="my_products.php" style="color: #f59e0b; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                            View All Products <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <?php if (!empty($top_products)): ?>
            <div class="content-card animate-fade-in" style="animation-delay: 1.3s; margin-top: 2rem;">
                <h3><i class="fas fa-trophy" style="margin-right: 0.5rem;"></i>Top Selling Products</h3>
                <div style="overflow-x: auto;">
                    <table class="table" style="margin: 0;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 0.75rem; font-size: 0.8rem;">Product</th>
                                <th style="padding: 0.75rem; font-size: 0.8rem; text-align: center;">Total Sold</th>
                                <th style="padding: 0.75rem; font-size: 0.8rem; text-align: right;">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $index => $product): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">
                                                #<?php echo $index + 1; ?>
                                            </div>
                                            <?php if (!empty($product['product_image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                            <?php endif; ?>
                                            <span style="font-weight: 600;"><?php echo htmlspecialchars($product['product_title']); ?></span>
                                        </div>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <span style="padding: 0.4rem 0.75rem; background: #dbeafe; color: #1e40af; border-radius: 8px; font-weight: 600;">
                                            <?php echo $product['total_sold']; ?> units
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right; font-weight: 700; color: #059669; font-size: 1.1rem;">
                                        GH₵ <?php echo number_format($product['total_revenue'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            const salesData = <?php echo json_encode($sales_by_month); ?>;

            const labels = salesData.map(item => item.month_label || 'N/A');
            const revenues = salesData.map(item => parseFloat(item.revenue || 0));
            const orderCounts = salesData.map(item => parseInt(item.order_count || 0));

            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (GH₵)',
                        data: revenues,
                        backgroundColor: 'rgba(5, 150, 105, 0.8)',
                        borderColor: 'rgba(5, 150, 105, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: orderCounts,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12,
                                    weight: '600'
                                },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 12
                            },
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.label === 'Revenue (GH₵)') {
                                            label += 'GH₵ ' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                        } else {
                                            label += context.parsed.y + ' orders';
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return 'GH₵ ' + value.toFixed(0);
                                }
                            },
                            title: {
                                display: true,
                                text: 'Revenue (GH₵)',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            },
                            title: {
                                display: true,
                                text: 'Number of Orders',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        console.log('Pharmacy Dashboard loaded with analytics');
    </script>
</body>
</html>
