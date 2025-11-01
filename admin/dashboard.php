<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/stats_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is SUPER ADMIN (role 0 only)
if (!isSuperAdmin()) {
    header("Location: ../index.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? getUserName();
$user_role = getUserRoleName();

// Get initial stats for page load
$stats_result = get_dashboard_stats_ctr();
$stats = $stats_result['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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

        .stat-change {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: #10b981;
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

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: #f8fafc;
        }

        .table th {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            border: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge-custom {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .category-item {
            padding: 1rem;
            border-radius: 10px;
            background: #f8fafc;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .category-item:hover {
            background: #e2e8f0;
            transform: translateX(5px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
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
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1><i class="fas fa-chart-pie" style="margin-right: 0.75rem;"></i>Super Admin Dashboard</h1>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Platform overview and analytics</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #64748b;">Welcome back,</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($user_name); ?></p>
                    <small style="color: #3b82f6; font-weight: 600;"><?php echo $user_role; ?></small>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary animate-fade-in" style="animation-delay: 0.1s;">
                <div class="stat-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h2 class="stat-value" id="totalPharmacies">
                    <?php echo $stats['total_pharmacies'] ?? 0; ?>
                </h2>
                <p class="stat-label">Total Pharmacies</p>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $stats['recent_registrations'] ?? 0; ?> new (30 days)</span>
                </div>
            </div>

            <div class="stat-card success animate-fade-in" style="animation-delay: 0.2s;">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h2 class="stat-value" id="totalCustomers">
                    <?php echo $stats['total_customers'] ?? 0; ?>
                </h2>
                <p class="stat-label">Registered Customers</p>
                <div class="stat-change positive">
                    <i class="fas fa-users-cog"></i>
                    <span>Active users</span>
                </div>
            </div>

            <div class="stat-card warning animate-fade-in" style="animation-delay: 0.3s;">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h2 class="stat-value" id="totalProducts">
                    <?php echo $stats['total_products'] ?? 0; ?>
                </h2>
                <p class="stat-label">Total Products</p>
                <div class="stat-change positive">
                    <i class="fas fa-plus-circle"></i>
                    <span><?php echo $stats['recent_products'] ?? 0; ?> added (7 days)</span>
                </div>
            </div>

            <div class="stat-card info animate-fade-in" style="animation-delay: 0.4s;">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h2 class="stat-value" id="totalCategories">
                    <?php echo $stats['total_categories'] ?? 0; ?>
                </h2>
                <p class="stat-label">Categories</p>
                <div class="stat-change positive">
                    <i class="fas fa-award"></i>
                    <span><?php echo $stats['total_brands'] ?? 0; ?> brands</span>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Row -->
        <div class="content-grid">
            <!-- Pharmacy Performance -->
            <div class="content-card animate-fade-in" style="animation-delay: 0.5s;">
                <h3><i class="fas fa-store-alt" style="margin-right: 0.5rem;"></i>Pharmacy Performance</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pharmacy Name</th>
                                <th>Location</th>
                                <th>Categories</th>
                                <th>Brands</th>
                                <th>Products</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="pharmacyTableBody">
                            <?php
                            $pharmacies = $stats['pharmacy_stats'] ?? [];
                            if (empty($pharmacies)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                        <i class="fas fa-store" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                        No pharmacies registered yet
                                    </td>
                                </tr>
                            <?php else:
                                foreach ($pharmacies as $pharmacy): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($pharmacy['customer_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pharmacy['customer_city']); ?></td>
                                        <td><span class="badge-custom" style="background: #dbeafe; color: #1e40af;"><?php echo $pharmacy['category_count']; ?></span></td>
                                        <td><span class="badge-custom" style="background: #d1fae5; color: #065f46;"><?php echo $pharmacy['brand_count']; ?></span></td>
                                        <td><span class="badge-custom" style="background: #fef3c7; color: #92400e;"><?php echo $pharmacy['product_count']; ?></span></td>
                                        <td><span class="badge-custom" style="background: #d1fae5; color: #065f46;">Active</span></td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="content-card animate-fade-in" style="animation-delay: 0.6s;">
                <h3><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i>Top Categories</h3>
                <div id="categoryList">
                    <?php
                    $categories = $stats['category_distribution'] ?? [];
                    if (empty($categories)): ?>
                        <p style="text-align: center; color: #94a3b8; padding: 2rem;">
                            <i class="fas fa-tags" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No categories yet
                        </p>
                    <?php else:
                        foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <div class="category-item">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="color: #1e293b;"><?php echo htmlspecialchars($cat['cat_name']); ?></strong>
                                        <br>
                                        <small style="color: #64748b;">
                                            <?php echo $cat['brand_count']; ?> brands • <?php echo $cat['product_count']; ?> products
                                        </small>
                                    </div>
                                    <div>
                                        <i class="fas fa-chevron-right" style="color: #cbd5e1;"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
