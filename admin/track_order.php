<?php
/**
 * Track Order Page
 * Role: Customer (2)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/order_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Get customer orders
$orders = get_customer_orders_ctr($customer_id);

// Ensure $orders is always an array
if (!is_array($orders)) {
    $orders = [];
}

// Handle order search by invoice or order ID
$search_order = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    foreach ($orders as $order) {
        if ($order['order_id'] == $search_term || $order['invoice_no'] == $search_term) {
            $search_order = $order;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            font-weight: 700;
        }

        .search-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .tracking-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-list {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .order-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0a3622;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .tracking-timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 3rem;
            padding-bottom: 2rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 2rem;
            width: 2px;
            height: calc(100% - 1.5rem);
            background: #e9ecef;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            color: #6c757d;
            font-size: 1rem;
            z-index: 1;
        }

        .timeline-icon.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-radius: 10px;
        }

        .timeline-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #64748b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .summary-label {
            color: #64748b;
            font-weight: 500;
        }

        .summary-value {
            font-weight: 600;
            color: #1e293b;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shipping-fast me-2"></i>Track Your Orders</h1>
            <p class="text-muted mb-0">Monitor your order status in real-time</p>
        </div>

        <!-- Search Section -->
        <div class="search-container">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <label class="form-label fw-bold">Search Order</label>
                    <input type="text"
                           name="search"
                           class="form-control form-control-lg"
                           placeholder="Enter Order ID or Invoice Number"
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Track
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['search'])): ?>
            <?php if ($search_order): ?>
                <!-- Tracking Details -->
                <div class="tracking-container">
                    <div class="order-summary">
                        <h5 class="mb-3">Order Summary</h5>
                        <div class="summary-row">
                            <span class="summary-label">Order ID:</span>
                            <span class="summary-value">#<?php echo htmlspecialchars($search_order['order_id']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Invoice Number:</span>
                            <span class="summary-value"><?php echo htmlspecialchars($search_order['invoice_no']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Order Date:</span>
                            <span class="summary-value"><?php echo date('M d, Y', strtotime($search_order['order_date'])); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Total Amount:</span>
                            <span class="summary-value"><?php echo $search_order['currency']; ?> <?php echo number_format($search_order['payment_amount'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Current Status:</span>
                            <span class="order-status status-<?php echo strtolower($search_order['order_status']); ?>">
                                <?php echo ucfirst($search_order['order_status']); ?>
                            </span>
                        </div>
                    </div>

                    <h5 class="mb-4">Tracking Timeline</h5>
                    <div class="tracking-timeline">
                        <?php
                        $status = strtolower($search_order['order_status']);
                        $statuses = [
                            'pending' => ['icon' => 'fa-clock', 'title' => 'Order Placed', 'active' => true],
                            'processing' => ['icon' => 'fa-cog', 'title' => 'Processing Order', 'active' => in_array($status, ['processing', 'shipped', 'delivered'])],
                            'shipped' => ['icon' => 'fa-truck', 'title' => 'Order Shipped', 'active' => in_array($status, ['shipped', 'delivered'])],
                            'delivered' => ['icon' => 'fa-check-circle', 'title' => 'Order Delivered', 'active' => $status === 'delivered']
                        ];

                        foreach ($statuses as $key => $stage):
                            $isActive = $stage['active'];
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-icon <?php echo $isActive ? 'active' : ''; ?>">
                                    <i class="fas <?php echo $stage['icon']; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title"><?php echo $stage['title']; ?></div>
                                    <?php if ($key === $status): ?>
                                        <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($search_order['order_date'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($status === 'cancelled'): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: #dc2626; color: white;">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="timeline-content" style="background: #fee2e2;">
                                    <div class="timeline-title" style="color: #dc2626;">Order Cancelled</div>
                                    <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($search_order['order_date'])); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="track_order.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Track Orders
                        </a>
                        <a href="../view/order_details.php?order_id=<?php echo $search_order['order_id']; ?>"
                           class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Full Order Details
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="tracking-container">
                    <div class="empty-state">
                        <i class="fas fa-search empty-icon"></i>
                        <h3>Order Not Found</h3>
                        <p class="text-muted mb-4">We couldn't find an order matching "<?php echo htmlspecialchars($_GET['search']); ?>"</p>
                        <a href="track_order.php" class="btn btn-primary">Try Again</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Recent Orders List -->
            <div class="order-list">
                <h5 class="mb-3">Your Recent Orders</h5>
                <?php if (count($orders) > 0): ?>
                    <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                        <div class="order-item" onclick="window.location.href='track_order.php?search=<?php echo $order['order_id']; ?>'">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-receipt me-2"></i>
                                        Order #<?php echo htmlspecialchars($order['order_id']); ?>
                                    </h6>
                                    <small class="text-muted">Invoice: <?php echo htmlspecialchars($order['invoice_no']); ?></small>
                                    <p class="mb-0 mt-2 text-muted">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                    <p class="mb-0 mt-2 fw-bold" style="color: #667eea;">
                                        <?php echo $order['currency']; ?> <?php echo number_format($order['payment_amount'], 2); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open empty-icon"></i>
                        <h3>No Orders Yet</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to track your orders here!</p>
                        <a href="../view/product.php" class="btn btn-primary">
                            <i class="fas fa-pills me-2"></i>Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
