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

// Extract all analytics data
$total_revenue = $analytics['total_revenue'] ?? 0;
$monthly_revenue = $analytics['monthly_revenue'] ?? 0;
$today_revenue = $analytics['today_revenue'] ?? 0;
$weekly_revenue = $analytics['weekly_revenue'] ?? 0;

$total_orders = $analytics['total_orders'] ?? 0;
$pending_orders = $analytics['pending_orders'] ?? 0;
$completed_orders = $analytics['completed_orders'] ?? 0;
$cancelled_orders = $analytics['cancelled_orders'] ?? 0;

$sales_by_month = $analytics['sales_by_month'] ?? [];
$top_products = $analytics['top_products'] ?? [];
$order_status_distribution = $analytics['order_status_distribution'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics - PharmaVault</title>

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
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .content-card h3 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .stat-box-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .stat-box-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-box-info {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .chart-container {
            position: relative;
            height: 350px;
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: #f8fafc;
        }

        .table th {
            padding: 0.75rem;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background: #f8fafc;
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

        .badge {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
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
                    <h1><i class="fas fa-chart-line" style="margin-right: 0.75rem;"></i>Sales Analytics</h1>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Comprehensive sales performance analysis</p>
                </div>
                <div>
                    <button onclick="window.print()" style="background: var(--primary-gradient); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Revenue Overview -->
        <div class="stats-grid animate-fade-in" style="animation-delay: 0.1s;">
            <div class="stat-box" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="stat-box-label">Total Revenue</div>
                <div class="stat-box-value">GH₵ <?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-box-info"><i class="fas fa-coins"></i> All-time earnings</div>
            </div>
            <div class="stat-box" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="stat-box-label">This Month</div>
                <div class="stat-box-value">GH₵ <?php echo number_format($monthly_revenue, 2); ?></div>
                <div class="stat-box-info"><i class="fas fa-calendar-alt"></i> Current month</div>
            </div>
            <div class="stat-box" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <div class="stat-box-label">This Week</div>
                <div class="stat-box-value">GH₵ <?php echo number_format($weekly_revenue, 2); ?></div>
                <div class="stat-box-info"><i class="fas fa-calendar-week"></i> Last 7 days</div>
            </div>
            <div class="stat-box" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="stat-box-label">Today</div>
                <div class="stat-box-value">GH₵ <?php echo number_format($today_revenue, 2); ?></div>
                <div class="stat-box-info"><i class="fas fa-clock"></i> Today's sales</div>
            </div>
        </div>

        <!-- Sales Trend Chart -->
        <div class="content-card animate-fade-in" style="animation-delay: 0.2s;">
            <h3><i class="fas fa-chart-area" style="margin-right: 0.5rem;"></i>Revenue & Orders Trend</h3>
            <div class="chart-container">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="content-card animate-fade-in" style="animation-delay: 0.3s;">
                <h3><i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i>Order Status Distribution</h3>
                <div style="height: 300px;">
                    <canvas id="orderStatusChart"></canvas>
                </div>
                <div style="margin-top: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background: #f8fafc; padding: 1rem; border-radius: 10px;">
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem;">Completed</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;"><?php echo $completed_orders; ?></div>
                        </div>
                        <div style="background: #f8fafc; padding: 1rem; border-radius: 10px;">
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem;">Pending</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #f59e0b;"><?php echo $pending_orders; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card animate-fade-in" style="animation-delay: 0.4s;">
                <h3><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Key Metrics</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 0.9rem; color: #1e40af; margin-bottom: 0.5rem;">Total Orders</div>
                                <div style="font-size: 2.5rem; font-weight: 700; color: #1e40af;"><?php echo $total_orders; ?></div>
                            </div>
                            <div style="width: 60px; height: 60px; background: rgba(30, 64, 175, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-shopping-cart" style="font-size: 1.75rem; color: #1e40af;"></i>
                            </div>
                        </div>
                    </div>

                    <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 0.9rem; color: #065f46; margin-bottom: 0.5rem;">Avg. Order Value</div>
                                <div style="font-size: 2.5rem; font-weight: 700; color: #065f46;">
                                    GH₵ <?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00'; ?>
                                </div>
                            </div>
                            <div style="width: 60px; height: 60px; background: rgba(6, 95, 70, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chart-bar" style="font-size: 1.75rem; color: #065f46;"></i>
                            </div>
                        </div>
                    </div>

                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 1.5rem; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 0.9rem; color: #92400e; margin-bottom: 0.5rem;">Completion Rate</div>
                                <div style="font-size: 2.5rem; font-weight: 700; color: #92400e;">
                                    <?php echo $total_orders > 0 ? number_format(($completed_orders / $total_orders) * 100, 1) : '0'; ?>%
                                </div>
                            </div>
                            <div style="width: 60px; height: 60px; background: rgba(146, 64, 14, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-percentage" style="font-size: 1.75rem; color: #92400e;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="content-card animate-fade-in" style="animation-delay: 0.5s;">
            <h3><i class="fas fa-trophy" style="margin-right: 0.5rem;"></i>Top Selling Products</h3>
            <?php if (empty($top_products)): ?>
                <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <p>No sales data available yet</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th style="text-align: center;">Units Sold</th>
                            <th style="text-align: right;">Revenue Generated</th>
                            <th style="text-align: right;">Avg. Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $index => $product):
                            $avg_price = $product['total_sold'] > 0 ? $product['total_revenue'] / $product['total_sold'] : 0;
                            $rank_colors = ['#f59e0b', '#94a3b8', '#cd7f32', '#059669', '#3b82f6'];
                            $rank_color = $rank_colors[$index] ?? '#64748b';
                        ?>
                            <tr>
                                <td>
                                    <div style="width: 40px; height: 40px; background: <?php echo $rank_color; ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem;">
                                        #<?php echo $index + 1; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        <?php endif; ?>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($product['product_title']); ?></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: #dbeafe; color: #1e40af;">
                                        <?php echo $product['total_sold']; ?> units
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #059669; font-size: 1.1rem;">
                                    GH₵ <?php echo number_format($product['total_revenue'], 2); ?>
                                </td>
                                <td style="text-align: right; color: #64748b;">
                                    GH₵ <?php echo number_format($avg_price, 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        // Sales Trend Chart
        const salesTrendCtx = document.getElementById('salesTrendChart');
        if (salesTrendCtx) {
            const salesData = <?php echo json_encode($sales_by_month); ?>;

            const labels = salesData.map(item => item.month_label || 'N/A');
            const revenues = salesData.map(item => parseFloat(item.revenue || 0));
            const orderCounts = salesData.map(item => parseInt(item.order_count || 0));

            new Chart(salesTrendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (GH₵)',
                        data: revenues,
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderColor: 'rgba(5, 150, 105, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(5, 150, 105, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }, {
                        label: 'Orders',
                        data: orderCounts,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1',
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                                    size: 13,
                                    weight: '600'
                                },
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.label === 'Revenue (GH₵)') {
                                            label += 'GH₵ ' + context.parsed.y.toFixed(2);
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
                            position: 'left',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'GH₵ ' + value.toFixed(0);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }

        // Order Status Distribution Chart
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (orderStatusCtx) {
            const statusData = <?php echo json_encode($order_status_distribution); ?>;

            const labels = statusData.map(item => item.order_status.charAt(0).toUpperCase() + item.order_status.slice(1));
            const counts = statusData.map(item => parseInt(item.count || 0));

            const colors = {
                'Pending': '#f59e0b',
                'Processing': '#3b82f6',
                'Completed': '#10b981',
                'Cancelled': '#ef4444',
                'Delivered': '#8b5cf6'
            };

            const backgroundColors = labels.map(label => colors[label] || '#64748b');

            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: backgroundColors,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: '600'
                                },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        console.log('Sales Analytics loaded');
    </script>
</body>
</html>
