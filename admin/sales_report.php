<?php
/**
 * Sales Reports - Super Admin & Pharmacy Admin
 * Comprehensive sales analytics and reporting
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');

// Check if user is super admin or pharmacy admin
if (!isSuperAdmin() && !isPharmacyAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

// Get user info
$user_id = getUserId();
$is_super_admin = isSuperAdmin();
$is_pharmacy_admin = isPharmacyAdmin();

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get date range filters
$filter_period = isset($_GET['period']) ? $_GET['period'] : 'this_month';
$custom_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$custom_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Calculate date range based on period
switch ($filter_period) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'yesterday':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d');
        break;
    case 'last_week':
        $start_date = date('Y-m-d', strtotime('monday last week'));
        $end_date = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('first day of last month'));
        $end_date = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'this_year':
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = $custom_start ?: date('Y-m-01');
        $end_date = $custom_end ?: date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
}

// Get overall sales statistics
// For pharmacy admins, we need to filter orders that contain at least one product from their pharmacy
if ($is_pharmacy_admin) {
    $stats_query = "SELECT
                        COUNT(DISTINCT o.order_id) as total_orders,
                        COUNT(DISTINCT o.customer_id) as unique_customers,
                        COALESCE(SUM(DISTINCT p.amt), 0) as total_revenue,
                        COALESCE(AVG(DISTINCT p.amt), 0) as avg_order_value,
                        COUNT(DISTINCT CASE WHEN o.order_status = 'Delivered' THEN o.order_id END) as completed_orders,
                        COUNT(DISTINCT CASE WHEN o.order_status = 'Cancelled' THEN o.order_id END) as cancelled_orders
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.order_id IN (
                        SELECT DISTINCT od.order_id
                        FROM orderdetails od
                        INNER JOIN products prod ON od.product_id = prod.product_id
                        WHERE prod.pharmacy_id = ?
                    )
                    AND DATE(o.order_date) BETWEEN ? AND ?";

    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param('iss', $user_id, $start_date, $end_date);
} else {
    $stats_query = "SELECT
                        COUNT(DISTINCT o.order_id) as total_orders,
                        COUNT(DISTINCT o.customer_id) as unique_customers,
                        COALESCE(SUM(p.amt), 0) as total_revenue,
                        COALESCE(AVG(p.amt), 0) as avg_order_value,
                        COUNT(DISTINCT CASE WHEN o.order_status = 'Delivered' THEN o.order_id END) as completed_orders,
                        COUNT(DISTINCT CASE WHEN o.order_status = 'Cancelled' THEN o.order_id END) as cancelled_orders
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE DATE(o.order_date) BETWEEN ? AND ?";

    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param('ss', $start_date, $end_date);
}

$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get daily sales trend
if ($is_pharmacy_admin) {
    $daily_sales_query = "SELECT
                            DATE(o.order_date) as sale_date,
                            COUNT(DISTINCT o.order_id) as orders_count,
                            COALESCE(SUM(DISTINCT p.amt), 0) as daily_revenue
                          FROM orders o
                          LEFT JOIN payment p ON o.order_id = p.order_id
                          WHERE o.order_id IN (
                              SELECT DISTINCT od.order_id
                              FROM orderdetails od
                              INNER JOIN products prod ON od.product_id = prod.product_id
                              WHERE prod.pharmacy_id = ?
                          )
                          AND DATE(o.order_date) BETWEEN ? AND ?
                          GROUP BY DATE(o.order_date)
                          ORDER BY sale_date ASC";

    $stmt = $conn->prepare($daily_sales_query);
    $stmt->bind_param('iss', $user_id, $start_date, $end_date);
} else {
    $daily_sales_query = "SELECT
                            DATE(o.order_date) as sale_date,
                            COUNT(DISTINCT o.order_id) as orders_count,
                            COALESCE(SUM(p.amt), 0) as daily_revenue
                          FROM orders o
                          LEFT JOIN payment p ON o.order_id = p.order_id
                          WHERE DATE(o.order_date) BETWEEN ? AND ?
                          GROUP BY DATE(o.order_date)
                          ORDER BY sale_date ASC";

    $stmt = $conn->prepare($daily_sales_query);
    $stmt->bind_param('ss', $start_date, $end_date);
}

$stmt->execute();
$daily_sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get top selling products
$product_pharmacy_filter = $is_pharmacy_admin ? "AND p.pharmacy_id = ?" : "";
$top_products_query = "SELECT
                        p.product_id,
                        p.product_title,
                        p.product_image,
                        SUM(od.qty) as total_quantity,
                        COALESCE(SUM(od.qty * p.product_price), 0) as total_revenue,
                        COUNT(DISTINCT od.order_id) as order_count,
                        ph.customer_name as pharmacy_name
                      FROM orderdetails od
                      INNER JOIN products p ON od.product_id = p.product_id
                      INNER JOIN orders o ON od.order_id = o.order_id
                      INNER JOIN customer ph ON p.pharmacy_id = ph.customer_id
                      WHERE DATE(o.order_date) BETWEEN ? AND ? $product_pharmacy_filter
                      GROUP BY p.product_id
                      ORDER BY total_quantity DESC
                      LIMIT 10";

$stmt = $conn->prepare($top_products_query);
if ($is_pharmacy_admin) {
    $stmt->bind_param('ssi', $start_date, $end_date, $user_id);
} else {
    $stmt->bind_param('ss', $start_date, $end_date);
}
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get sales by pharmacy (only for super admin)
$pharmacy_sales = [];
if ($is_super_admin) {
    $pharmacy_sales_query = "SELECT
                                ph.customer_id,
                                ph.customer_name as pharmacy_name,
                                ph.customer_city,
                                COUNT(DISTINCT o.order_id) as total_orders,
                                COALESCE(SUM(p.amt), 0) as total_revenue,
                                COUNT(DISTINCT o.customer_id) as unique_customers
                            FROM customer ph
                            INNER JOIN products prod ON ph.customer_id = prod.pharmacy_id
                            INNER JOIN orderdetails od ON prod.product_id = od.product_id
                            INNER JOIN orders o ON od.order_id = o.order_id
                            LEFT JOIN payment p ON o.order_id = p.order_id
                            WHERE ph.user_role = 1
                            AND DATE(o.order_date) BETWEEN ? AND ?
                            GROUP BY ph.customer_id
                            ORDER BY total_revenue DESC
                            LIMIT 10";

    $stmt = $conn->prepare($pharmacy_sales_query);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $pharmacy_sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get sales by category
$category_pharmacy_filter = $is_pharmacy_admin ? "AND p.pharmacy_id = ?" : "";
$category_sales_query = "SELECT
                            c.cat_id,
                            c.cat_name,
                            COUNT(DISTINCT od.order_id) as total_orders,
                            SUM(od.qty) as total_quantity,
                            COALESCE(SUM(od.qty * p.product_price), 0) as total_revenue
                        FROM categories c
                        INNER JOIN products p ON c.cat_id = p.product_cat
                        INNER JOIN orderdetails od ON p.product_id = od.product_id
                        INNER JOIN orders o ON od.order_id = o.order_id
                        WHERE DATE(o.order_date) BETWEEN ? AND ? $category_pharmacy_filter
                        GROUP BY c.cat_id
                        ORDER BY total_revenue DESC";

$stmt = $conn->prepare($category_sales_query);
if ($is_pharmacy_admin) {
    $stmt->bind_param('ssi', $start_date, $end_date, $user_id);
} else {
    $stmt->bind_param('ss', $start_date, $end_date);
}
$stmt->execute();
$category_sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - PharmaVault Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --sidebar-width: 280px;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php
    // Include appropriate sidebar based on user role
    if ($is_super_admin) {
        include('../view/components/sidebar_super_admin.php');
    } else {
        include('../view/components/sidebar_pharmacy_admin.php');
    }
    ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-chart-line me-3"></i>Sales Report</h1>
            <p class="mb-0">Comprehensive sales analytics from <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></p>
        </div>

        <!-- Date Filter -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select" id="periodSelect">
                            <option value="today" <?php echo $filter_period == 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="yesterday" <?php echo $filter_period == 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                            <option value="this_week" <?php echo $filter_period == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="last_week" <?php echo $filter_period == 'last_week' ? 'selected' : ''; ?>>Last Week</option>
                            <option value="this_month" <?php echo $filter_period == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="last_month" <?php echo $filter_period == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                            <option value="this_year" <?php echo $filter_period == 'this_year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="custom" <?php echo $filter_period == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="startDateCol" style="display: <?php echo $filter_period == 'custom' ? 'block' : 'none'; ?>;">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $custom_start; ?>">
                    </div>
                    <div class="col-md-3" id="endDateCol" style="display: <?php echo $filter_period == 'custom' ? 'block' : 'none'; ?>;">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $custom_end; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success">GHS <?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo number_format($stats['unique_customers']); ?></div>
                <div class="stat-label">Unique Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning">GHS <?php echo number_format($stats['avg_order_value'], 2); ?></div>
                <div class="stat-label">Avg Order Value</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo number_format($stats['completed_orders']); ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo number_format($stats['cancelled_orders']); ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>

        <!-- Daily Sales Trend Chart -->
        <div class="chart-container">
            <h4 class="mb-4">Daily Sales Trend</h4>
            <canvas id="dailySalesChart" height="80"></canvas>
        </div>

        <!-- Top Selling Products -->
        <div class="table-container">
            <h4 class="mb-3">Top Selling Products</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Pharmacy</th>
                            <th>Quantity Sold</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($top_products) > 0): ?>
                            <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($product['product_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                                     alt="Product" class="product-img me-3">
                                            <?php else: ?>
                                                <div class="bg-light rounded p-2 me-3">
                                                    <i class="fas fa-pills text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($product['product_title']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['pharmacy_name']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo number_format($product['total_quantity']); ?></span></td>
                                    <td><?php echo number_format($product['order_count']); ?></td>
                                    <td><strong class="text-success">GHS <?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No product sales in this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Pharmacies by Revenue (Super Admin Only) -->
        <?php if ($is_super_admin): ?>
        <div class="table-container">
            <h4 class="mb-3">Top Pharmacies by Revenue</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>City</th>
                            <th>Total Orders</th>
                            <th>Unique Customers</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pharmacy_sales) > 0): ?>
                            <?php foreach ($pharmacy_sales as $pharmacy): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($pharmacy['pharmacy_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pharmacy['customer_city']); ?></td>
                                    <td><?php echo number_format($pharmacy['total_orders']); ?></td>
                                    <td><?php echo number_format($pharmacy['unique_customers']); ?></td>
                                    <td><strong class="text-success">GHS <?php echo number_format($pharmacy['total_revenue'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No pharmacy sales in this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sales by Category -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4">Sales by Category</h4>
                    <canvas id="categorySalesChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="mb-3">Category Breakdown</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($category_sales) > 0): ?>
                                    <?php foreach ($category_sales as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['cat_name']); ?></td>
                                            <td><?php echo number_format($category['total_orders']); ?></td>
                                            <td class="text-success">GHS <?php echo number_format($category['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No category sales data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Period select change handler
        document.getElementById('periodSelect').addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            document.getElementById('startDateCol').style.display = isCustom ? 'block' : 'none';
            document.getElementById('endDateCol').style.display = isCustom ? 'block' : 'none';
        });

        // Daily Sales Trend Chart
        const dailySalesData = <?php echo json_encode($daily_sales); ?>;
        const salesDates = dailySalesData.map(d => d.sale_date);
        const salesRevenue = dailySalesData.map(d => parseFloat(d.daily_revenue));
        const salesOrders = dailySalesData.map(d => parseInt(d.orders_count));

        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: salesDates,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: salesRevenue,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    data: salesOrders,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Category Sales Pie Chart
        const categoryData = <?php echo json_encode($category_sales); ?>;
        const categoryNames = categoryData.map(c => c.cat_name);
        const categoryRevenue = categoryData.map(c => parseFloat(c.total_revenue));

        const categoryCtx = document.getElementById('categorySalesChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [{
                    data: categoryRevenue,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#10b981', '#f59e0b',
                        '#ef4444', '#06b6d4', '#8b5cf6', '#ec4899',
                        '#14b8a6', '#f97316'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': GHS ' + context.parsed.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
