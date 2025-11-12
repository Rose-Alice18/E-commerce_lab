<?php
/**
 * My Orders Page
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

// Ensure orders is always an array
if (!is_array($orders)) {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - PharmaVault</title>

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

        .orders-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .order-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-id {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
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
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .order-detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1rem;
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
            <h1><i class="fas fa-box me-2"></i>My Orders</h1>
            <p class="text-muted mb-0">View and manage your orders</p>
        </div>

        <div class="orders-container">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">
                                    <i class="fas fa-receipt me-2"></i>
                                    Order #<?php echo htmlspecialchars($order['order_id']); ?>
                                </div>
                                <small class="text-muted">Invoice: <?php echo htmlspecialchars($order['invoice_no']); ?></small>
                            </div>
                            <span class="order-status status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="order-detail-item">
                                <span class="detail-label">Order Date</span>
                                <span class="detail-value">
                                    <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                </span>
                            </div>

                            <div class="order-detail-item">
                                <span class="detail-label">Payment Date</span>
                                <span class="detail-value">
                                    <?php echo $order['payment_date'] ? date('M d, Y', strtotime($order['payment_date'])) : 'N/A'; ?>
                                </span>
                            </div>

                            <div class="order-detail-item">
                                <span class="detail-label">Total Amount</span>
                                <span class="detail-value" style="color: #667eea;">
                                    <?php echo htmlspecialchars($order['currency']); ?> <?php echo number_format($order['payment_amount'], 2); ?>
                                </span>
                            </div>

                            <div class="order-detail-item">
                                <span class="detail-label">Action</span>
                                <a href="../view/order_details.php?order_id=<?php echo $order['order_id']; ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Orders Yet</h3>
                    <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping now!</p>
                    <a href="../view/product.php" class="btn btn-primary">
                        <i class="fas fa-pills me-2"></i>Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cart and Wishlist JS (for sidebar counts) -->
    <script src="../js/cart-wishlist.js"></script>
</body>
</html>
