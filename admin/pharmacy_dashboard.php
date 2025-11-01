<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');

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

// Get real pharmacy stats from database
require_once('../classes/product_class.php');
$product_obj = new Product();

// Get total products for this pharmacy
$products = $product_obj->get_products_by_pharmacy($user_id);
$total_products = count($products);

// Calculate total stock value
$total_stock_value = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

foreach ($products as $product) {
    $stock = intval($product['product_stock'] ?? 0);
    $price = floatval($product['product_price'] ?? 0);
    $total_stock_value += ($stock * $price);

    if ($stock == 0) {
        $out_of_stock_count++;
    } elseif ($stock <= 10) {
        $low_stock_count++;
    }
}

// Get categories and brands count
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');
$category_obj = new Category();
$brand_obj = new Brand();

$categories = $category_obj->get_categories_by_user($user_id);
$brands = $brand_obj->get_all_brands($user_id); // Fixed: use get_all_brands() instead of get_brands_by_user()
$total_categories = count($categories);
$total_brands = count($brands);

// Orders data (placeholder for now - will be implemented when orders system is built)
$total_orders = 0;
$pending_orders = 0;
$revenue_today = 0;
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
                    <a href="order_history.php">
                        View history <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="stat-card info animate-fade-in" style="animation-delay: 0.4s;">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h2 class="stat-value" id="stockValue">
                    GHS <?php echo number_format($total_stock_value, 2); ?>
                </h2>
                <p class="stat-label">Total Stock Value</p>
                <div class="stat-action">
                    <a href="my_products.php">
                        View inventory <i class="fas fa-arrow-right"></i>
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

        <!-- Recent Activity Grid -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card animate-fade-in" style="animation-delay: 0.6s;">
                <h3><i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Recent Orders</h3>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>No recent orders</p>
                    <small>Orders from customers will appear here</small>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="content-card animate-fade-in" style="animation-delay: 0.7s;">
                <h3><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Low Stock Alerts</h3>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No low stock items</p>
                    <small>Products running low on stock will appear here</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        // You can add dynamic updates here
        console.log('Pharmacy Dashboard loaded');
    </script>
</body>
</html>
