<?php
/**
 * Revenue Analytics - Super Admin & Pharmacy Admin
 * Detailed revenue analysis and financial insights
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

// Get current month and year for comparison
$current_year = date('Y');
$current_month = date('m');
$last_month = date('m', strtotime('first day of last month'));
$last_month_year = date('Y', strtotime('first day of last month'));

// Get overall revenue statistics
if ($is_pharmacy_admin) {
    $revenue_stats_query = "SELECT
                                COALESCE(SUM(DISTINCT p.amt), 0) as total_revenue,
                                COALESCE(AVG(DISTINCT p.amt), 0) as avg_transaction,
                                COUNT(DISTINCT p.pay_id) as total_transactions,
                                COUNT(DISTINCT o.customer_id) as unique_customers
                            FROM payment p
                            INNER JOIN orders o ON p.order_id = o.order_id
                            WHERE o.order_id IN (
                                SELECT DISTINCT od.order_id
                                FROM orderdetails od
                                INNER JOIN products prod ON od.product_id = prod.product_id
                                WHERE prod.pharmacy_id = ?
                            )";

    $stmt = $conn->prepare($revenue_stats_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $revenue_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    $revenue_stats_query = "SELECT
                                COALESCE(SUM(p.amt), 0) as total_revenue,
                                COALESCE(AVG(p.amt), 0) as avg_transaction,
                                COUNT(DISTINCT p.pay_id) as total_transactions,
                                COUNT(DISTINCT o.customer_id) as unique_customers
                            FROM payment p
                            INNER JOIN orders o ON p.order_id = o.order_id";

    $revenue_stats = $conn->query($revenue_stats_query)->fetch_assoc();
}

// Get current month revenue
if ($is_pharmacy_admin) {
    $current_month_query = "SELECT COALESCE(SUM(DISTINCT p.amt), 0) as revenue
                            FROM payment p
                            INNER JOIN orders o ON p.order_id = o.order_id
                            WHERE o.order_id IN (
                                SELECT DISTINCT od.order_id
                                FROM orderdetails od
                                INNER JOIN products prod ON od.product_id = prod.product_id
                                WHERE prod.pharmacy_id = ?
                            )
                            AND YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?";
    $stmt = $conn->prepare($current_month_query);
    $stmt->bind_param('iii', $user_id, $current_year, $current_month);
} else {
    $current_month_query = "SELECT COALESCE(SUM(p.amt), 0) as revenue
                            FROM payment p
                            WHERE YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?";
    $stmt = $conn->prepare($current_month_query);
    $stmt->bind_param('ii', $current_year, $current_month);
}
$stmt->execute();
$current_month_revenue = $stmt->get_result()->fetch_assoc()['revenue'];
$stmt->close();

// Get last month revenue for comparison
if ($is_pharmacy_admin) {
    $stmt = $conn->prepare($current_month_query);
    $stmt->bind_param('iii', $user_id, $last_month_year, $last_month);
} else {
    $stmt = $conn->prepare($current_month_query);
    $stmt->bind_param('ii', $last_month_year, $last_month);
}
$stmt->execute();
$last_month_revenue = $stmt->get_result()->fetch_assoc()['revenue'];
$stmt->close();

// Calculate growth percentage
$revenue_growth = 0;
if ($last_month_revenue > 0) {
    $revenue_growth = (($current_month_revenue - $last_month_revenue) / $last_month_revenue) * 100;
}

// Get monthly revenue trend (last 12 months)
if ($is_pharmacy_admin) {
    $monthly_revenue_query = "SELECT
                                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                                COALESCE(SUM(DISTINCT p.amt), 0) as revenue,
                                COUNT(DISTINCT p.pay_id) as transactions
                              FROM payment p
                              INNER JOIN orders o ON p.order_id = o.order_id
                              WHERE o.order_id IN (
                                  SELECT DISTINCT od.order_id
                                  FROM orderdetails od
                                  INNER JOIN products prod ON od.product_id = prod.product_id
                                  WHERE prod.pharmacy_id = ?
                              )
                              AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                              GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
                              ORDER BY month ASC";

    $stmt = $conn->prepare($monthly_revenue_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $monthly_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $monthly_revenue_query = "SELECT
                                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                                COALESCE(SUM(p.amt), 0) as revenue,
                                COUNT(DISTINCT p.pay_id) as transactions
                              FROM payment p
                              WHERE p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                              GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
                              ORDER BY month ASC";

    $monthly_revenue = $conn->query($monthly_revenue_query)->fetch_all(MYSQLI_ASSOC);
}

// Get revenue by pharmacy (only for super admin)
$pharmacy_revenue = [];
if ($is_super_admin) {
    $pharmacy_revenue_query = "SELECT
                                ph.customer_id,
                                ph.customer_name as pharmacy_name,
                                ph.customer_city,
                                COALESCE(SUM(p.amt), 0) as total_revenue,
                                COUNT(DISTINCT p.pay_id) as transaction_count,
                                COALESCE(AVG(p.amt), 0) as avg_transaction_value
                              FROM customer ph
                              INNER JOIN products prod ON ph.customer_id = prod.pharmacy_id
                              INNER JOIN orderdetails od ON prod.product_id = od.product_id
                              INNER JOIN orders o ON od.order_id = o.order_id
                              INNER JOIN payment p ON o.order_id = p.order_id
                              WHERE ph.user_role = 1
                              GROUP BY ph.customer_id
                              ORDER BY total_revenue DESC
                              LIMIT 10";

    $pharmacy_revenue = $conn->query($pharmacy_revenue_query)->fetch_all(MYSQLI_ASSOC);
}

// Get revenue by product category
$category_pharmacy_filter = $is_pharmacy_admin ? "WHERE p.pharmacy_id = ?" : "";
$category_revenue_query = "SELECT
                            c.cat_id,
                            c.cat_name,
                            COALESCE(SUM(od.qty * p.product_price), 0) as total_revenue,
                            SUM(od.qty) as total_quantity,
                            COUNT(DISTINCT o.order_id) as order_count
                          FROM categories c
                          INNER JOIN products p ON c.cat_id = p.product_cat
                          INNER JOIN orderdetails od ON p.product_id = od.product_id
                          INNER JOIN orders o ON od.order_id = o.order_id
                          $category_pharmacy_filter
                          GROUP BY c.cat_id
                          ORDER BY total_revenue DESC";

if ($is_pharmacy_admin) {
    $stmt = $conn->prepare($category_revenue_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $category_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $category_revenue = $conn->query($category_revenue_query)->fetch_all(MYSQLI_ASSOC);
}

// Get daily revenue for current month
if ($is_pharmacy_admin) {
    $daily_revenue_query = "SELECT
                                DATE(p.payment_date) as date,
                                COALESCE(SUM(DISTINCT p.amt), 0) as revenue,
                                COUNT(DISTINCT p.pay_id) as transactions
                            FROM payment p
                            INNER JOIN orders o ON p.order_id = o.order_id
                            WHERE o.order_id IN (
                                SELECT DISTINCT od.order_id
                                FROM orderdetails od
                                INNER JOIN products prod ON od.product_id = prod.product_id
                                WHERE prod.pharmacy_id = ?
                            )
                            AND YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?
                            GROUP BY DATE(p.payment_date)
                            ORDER BY date ASC";

    $stmt = $conn->prepare($daily_revenue_query);
    $stmt->bind_param('iii', $user_id, $current_year, $current_month);
} else {
    $daily_revenue_query = "SELECT
                                DATE(p.payment_date) as date,
                                COALESCE(SUM(p.amt), 0) as revenue,
                                COUNT(DISTINCT p.pay_id) as transactions
                            FROM payment p
                            WHERE YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?
                            GROUP BY DATE(p.payment_date)
                            ORDER BY date ASC";

    $stmt = $conn->prepare($daily_revenue_query);
    $stmt->bind_param('ii', $current_year, $current_month);
}
$stmt->execute();
$daily_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get revenue by customer location
if ($is_pharmacy_admin) {
    $location_revenue_query = "SELECT
                                c.customer_city as city,
                                COALESCE(SUM(DISTINCT p.amt), 0) as total_revenue,
                                COUNT(DISTINCT o.order_id) as order_count
                              FROM orders o
                              INNER JOIN customer c ON o.customer_id = c.customer_id
                              INNER JOIN payment p ON o.order_id = p.order_id
                              WHERE o.order_id IN (
                                  SELECT DISTINCT od.order_id
                                  FROM orderdetails od
                                  INNER JOIN products prod ON od.product_id = prod.product_id
                                  WHERE prod.pharmacy_id = ?
                              )
                              AND c.customer_city IS NOT NULL AND c.customer_city != ''
                              GROUP BY c.customer_city
                              ORDER BY total_revenue DESC
                              LIMIT 10";

    $stmt = $conn->prepare($location_revenue_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $location_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $location_revenue_query = "SELECT
                                c.customer_city as city,
                                COALESCE(SUM(p.amt), 0) as total_revenue,
                                COUNT(DISTINCT o.order_id) as order_count
                              FROM orders o
                              INNER JOIN customer c ON o.customer_id = c.customer_id
                              INNER JOIN payment p ON o.order_id = p.order_id
                              WHERE c.customer_city IS NOT NULL AND c.customer_city != ''
                              GROUP BY c.customer_city
                              ORDER BY total_revenue DESC
                              LIMIT 10";

    $location_revenue = $conn->query($location_revenue_query)->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Analytics - PharmaVault Admin</title>

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
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--success);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
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

        .revenue-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
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
            <h1><i class="fas fa-dollar-sign me-3"></i>Revenue Analytics</h1>
            <p class="mb-0">Comprehensive revenue insights and financial performance metrics</p>
        </div>

        <!-- Key Revenue Metrics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number text-success">GHS <?php echo number_format($revenue_stats['total_revenue'], 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="stat-change <?php echo $revenue_growth >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-arrow-<?php echo $revenue_growth >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs(number_format($revenue_growth, 1)); ?>% vs last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number text-primary"><?php echo number_format($revenue_stats['total_transactions']); ?></div>
                        <div class="stat-label">Total Transactions</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number text-warning">GHS <?php echo number_format($revenue_stats['avg_transaction'], 2); ?></div>
                        <div class="stat-label">Avg Transaction Value</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number text-info"><?php echo number_format($revenue_stats['unique_customers']); ?></div>
                        <div class="stat-label">Paying Customers</div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Comparison -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="stat-card">
                    <h5 class="mb-3">Current Month Revenue</h5>
                    <div class="stat-number text-success">GHS <?php echo number_format($current_month_revenue, 2); ?></div>
                    <small class="text-muted"><?php echo date('F Y'); ?></small>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card">
                    <h5 class="mb-3">Last Month Revenue</h5>
                    <div class="stat-number text-secondary">GHS <?php echo number_format($last_month_revenue, 2); ?></div>
                    <small class="text-muted"><?php echo date('F Y', strtotime('first day of last month')); ?></small>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card">
                    <h5 class="mb-3">Growth Rate</h5>
                    <div class="stat-number <?php echo $revenue_growth >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $revenue_growth >= 0 ? '+' : ''; ?><?php echo number_format($revenue_growth, 1); ?>%
                    </div>
                    <small class="text-muted">Month over month</small>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Trend -->
        <div class="chart-container">
            <h4 class="mb-4">Monthly Revenue Trend (Last 12 Months)</h4>
            <canvas id="monthlyRevenueChart" height="80"></canvas>
        </div>

        <!-- Daily Revenue for Current Month -->
        <div class="chart-container">
            <h4 class="mb-4">Daily Revenue - <?php echo date('F Y'); ?></h4>
            <canvas id="dailyRevenueChart" height="80"></canvas>
        </div>

        <!-- Revenue by Pharmacy (Super Admin Only) -->
        <?php if ($is_super_admin): ?>
        <div class="table-container">
            <h4 class="mb-3">Top Pharmacies by Revenue</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Pharmacy</th>
                            <th>City</th>
                            <th>Transactions</th>
                            <th>Avg Transaction</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pharmacy_revenue) > 0): ?>
                            <?php foreach ($pharmacy_revenue as $index => $pharmacy): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : 'bg-info'); ?>">
                                            #<?php echo $index + 1; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($pharmacy['pharmacy_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pharmacy['customer_city']); ?></td>
                                    <td><?php echo number_format($pharmacy['transaction_count']); ?></td>
                                    <td>GHS <?php echo number_format($pharmacy['avg_transaction_value'], 2); ?></td>
                                    <td><strong class="text-success">GHS <?php echo number_format($pharmacy['total_revenue'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No pharmacy revenue data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Revenue by Category and Location -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4">Revenue by Category</h4>
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h4 class="mb-4">Revenue by Location</h4>
                    <canvas id="locationRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown Tables -->
        <div class="row">
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="mb-3">Category Revenue Breakdown</h4>
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
                                <?php if (count($category_revenue) > 0): ?>
                                    <?php foreach ($category_revenue as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['cat_name']); ?></td>
                                            <td><?php echo number_format($category['order_count']); ?></td>
                                            <td class="text-success">GHS <?php echo number_format($category['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No category data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="mb-3">Location Revenue Breakdown</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>City</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($location_revenue) > 0): ?>
                                    <?php foreach ($location_revenue as $location): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($location['city']); ?></td>
                                            <td><?php echo number_format($location['order_count']); ?></td>
                                            <td class="text-success">GHS <?php echo number_format($location['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No location data</td>
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
        // Monthly Revenue Trend Chart
        const monthlyData = <?php echo json_encode($monthly_revenue); ?>;
        const months = monthlyData.map(d => d.month);
        const monthlyRevenue = monthlyData.map(d => parseFloat(d.revenue));
        const monthlyTransactions = monthlyData.map(d => parseInt(d.transactions));

        const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: monthlyRevenue,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 8,
                    yAxisID: 'y'
                }, {
                    label: 'Transactions',
                    data: monthlyTransactions,
                    type: 'line',
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
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
                            text: 'Transactions'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Daily Revenue Chart
        const dailyData = <?php echo json_encode($daily_revenue); ?>;
        const dailyDates = dailyData.map(d => d.date);
        const dailyRevenue = dailyData.map(d => parseFloat(d.revenue));

        const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyDates,
                datasets: [{
                    label: 'Daily Revenue (GHS)',
                    data: dailyRevenue,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    }
                }
            }
        });

        // Category Revenue Pie Chart
        const categoryData = <?php echo json_encode($category_revenue); ?>;
        const categoryNames = categoryData.map(c => c.cat_name);
        const categoryRevenue = categoryData.map(c => parseFloat(c.total_revenue));

        const categoryCtx = document.getElementById('categoryRevenueChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [{
                    data: categoryRevenue,
                    backgroundColor: [
                        '#10b981', '#667eea', '#f59e0b', '#ef4444',
                        '#06b6d4', '#8b5cf6', '#ec4899', '#14b8a6',
                        '#f97316', '#84cc16'
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

        // Location Revenue Bar Chart
        const locationData = <?php echo json_encode($location_revenue); ?>;
        const locationNames = locationData.map(l => l.city);
        const locationRevenue = locationData.map(l => parseFloat(l.total_revenue));

        const locationCtx = document.getElementById('locationRevenueChart').getContext('2d');
        new Chart(locationCtx, {
            type: 'bar',
            data: {
                labels: locationNames,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: locationRevenue,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: GHS ' + context.parsed.x.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (GHS)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
