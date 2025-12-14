<?php
/**
 * All Orders Management - Super Admin
 * View and manage all orders across all pharmacies and customers
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');

// Check if user is super admin
if (!isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_pharmacy = isset($_GET['pharmacy']) ? intval($_GET['pharmacy']) : 0;
$filter_customer = isset($_GET['customer']) ? intval($_GET['customer']) : 0;
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get order statistics
$stats_query = "SELECT
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COUNT(DISTINCT CASE WHEN o.order_status = 'Pending' THEN o.order_id END) as pending_count,
                    COUNT(DISTINCT CASE WHEN o.order_status = 'Processing' THEN o.order_id END) as processing_count,
                    COUNT(DISTINCT CASE WHEN o.order_status = 'Shipped' THEN o.order_id END) as shipped_count,
                    COUNT(DISTINCT CASE WHEN o.order_status = 'Delivered' THEN o.order_id END) as delivered_count,
                    COUNT(DISTINCT CASE WHEN o.order_status = 'Cancelled' THEN o.order_id END) as cancelled_count,
                    COALESCE(SUM(CASE WHEN o.order_status = 'Delivered' THEN p.amt ELSE 0 END), 0) as total_revenue
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Build orders query with filters
$orders_query = "SELECT o.*,
                        c.customer_name, c.customer_email, c.customer_contact,
                        COALESCE(p.amt, 0) as order_total,
                        p.currency, p.payment_date
                 FROM orders o
                 INNER JOIN customer c ON o.customer_id = c.customer_id
                 LEFT JOIN payment p ON o.order_id = p.order_id
                 WHERE 1=1";

if ($filter_status != 'all') {
    $orders_query .= " AND o.order_status = '" . $conn->real_escape_string($filter_status) . "'";
}

if ($filter_customer > 0) {
    $orders_query .= " AND o.customer_id = " . $filter_customer;
}

if ($filter_date_from) {
    $orders_query .= " AND DATE(o.order_date) >= '" . $conn->real_escape_string($filter_date_from) . "'";
}

if ($filter_date_to) {
    $orders_query .= " AND DATE(o.order_date) <= '" . $conn->real_escape_string($filter_date_to) . "'";
}

if ($search_query) {
    $orders_query .= " AND (o.invoice_no LIKE '%" . $conn->real_escape_string($search_query) . "%'
                       OR c.customer_name LIKE '%" . $conn->real_escape_string($search_query) . "%'
                       OR c.customer_email LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

$orders_query .= " ORDER BY o.order_date DESC LIMIT 100";

$orders_result = $conn->query($orders_query);
$orders = [];
if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Get all pharmacies for filter dropdown
$pharmacies_query = "SELECT customer_id, customer_name FROM customer WHERE user_role = 1 ORDER BY customer_name";
$pharmacies_result = $conn->query($pharmacies_query);
$pharmacies = [];
if ($pharmacies_result) {
    while ($row = $pharmacies_result->fetch_assoc()) {
        $pharmacies[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - PharmaVault Admin</title>

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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .orders-table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
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
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart me-3"></i>All Orders Management</h1>
            <p class="mb-0">View and manage all orders across the platform</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['processing_count']; ?></div>
                <div class="stat-label">Processing</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['shipped_count']; ?></div>
                <div class="stat-label">Shipped</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['delivered_count']; ?></div>
                <div class="stat-label">Delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $stats['cancelled_count']; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success">GH₵<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Order Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="Pending" <?php echo $filter_status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processing" <?php echo $filter_status == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Shipped" <?php echo $filter_status == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Delivered" <?php echo $filter_status == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo $filter_status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Invoice, Customer..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <a href="all_orders.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="orders-table-container">
            <h3 class="mb-3">Orders List (<?php echo count($orders); ?> results)</h3>

            <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['invoice_no']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td><strong>GH₵<?php echo number_format($order['order_total'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['payment_status'] ?? 'pending'); ?>">
                                            <?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($order['delivery_address']): ?>
                                            <i class="fas fa-truck text-success" title="<?php echo htmlspecialchars($order['delivery_address']); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-store text-muted" title="Pickup"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-action btn-view"
                                                onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4>No Orders Found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewOrderDetails(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();

            fetch(`../actions/get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order, data.items, data.delivery);
                    } else {
                        document.getElementById('orderDetailsContent').innerHTML =
                            '<div class="alert alert-danger">Error loading order details</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailsContent').innerHTML =
                        '<div class="alert alert-danger">Failed to load order details</div>';
                });
        }

        function displayOrderDetails(order, items, delivery) {
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <table class="table table-sm">
                            <tr><th>Invoice #:</th><td>${order.invoice_no}</td></tr>
                            <tr><th>Order Date:</th><td>${new Date(order.order_date).toLocaleString()}</td></tr>
                            <tr><th>Status:</th><td><span class="status-badge status-${order.order_status.toLowerCase()}">${order.order_status}</span></td></tr>
                            <tr><th>Total Amount:</th><td><strong>GH₵${parseFloat(order.order_total).toFixed(2)}</strong></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <table class="table table-sm">
                            <tr><th>Name:</th><td>${order.customer_name}</td></tr>
                            <tr><th>Email:</th><td>${order.customer_email}</td></tr>
                            <tr><th>Phone:</th><td>${order.customer_contact}</td></tr>
                            <tr><th>Delivery Address:</th><td>${order.delivery_address || 'Pickup'}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h6>Order Items</h6>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Pharmacy</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            items.forEach(item => {
                html += `
                    <tr>
                        <td>${item.product_title}</td>
                        <td>${item.pharmacy_name || 'N/A'}</td>
                        <td>${item.qty}</td>
                        <td>GH₵${parseFloat(item.product_price).toFixed(2)}</td>
                        <td>GH₵${(parseFloat(item.product_price) * parseInt(item.qty)).toFixed(2)}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            if (delivery) {
                html += `
                    <hr>
                    <h6>Delivery Information</h6>
                    <table class="table table-sm">
                        <tr><th>Rider:</th><td>${delivery.rider_name}</td></tr>
                        <tr><th>Rider Phone:</th><td>${delivery.rider_phone}</td></tr>
                        <tr><th>Delivery Status:</th><td><span class="status-badge status-${delivery.status}">${delivery.status.replace('_', ' ')}</span></td></tr>
                        <tr><th>Assigned:</th><td>${new Date(delivery.assigned_at).toLocaleString()}</td></tr>
                        ${delivery.delivered_at ? `<tr><th>Delivered:</th><td>${new Date(delivery.delivered_at).toLocaleString()}</td></tr>` : ''}
                    </table>
                `;
            }

            document.getElementById('orderDetailsContent').innerHTML = html;
        }
    </script>
</body>
</html>
