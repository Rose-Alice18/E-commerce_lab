<?php
/**
 * Order Tracking Page
 * Shows visual timeline of order status for customers
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/order_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserId();
$user_name = getUserName();

// Get customer's orders
$orders = get_customer_orders_ctr($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">

    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #764ba2;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }

        .page-header p {
            margin: 0;
            opacity: 0.9;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .order-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .order-date {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
            background: #ddd6fe;
            color: #5b21b6;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            max-width: 600px;
            margin: 2rem auto;
        }

        .timeline-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 24px;
            top: 50px;
            width: 2px;
            height: calc(100% + 2rem);
            background: #e2e8f0;
        }

        .timeline-step.completed::after {
            background: var(--success-color);
        }

        .timeline-step.current::after {
            background: linear-gradient(to bottom, var(--info-color), #e2e8f0);
        }

        .timeline-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            z-index: 1;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .timeline-icon.completed {
            background: var(--success-color);
            color: white;
        }

        .timeline-icon.current {
            background: var(--info-color);
            color: white;
            animation: pulse 2s infinite;
        }

        .timeline-icon.pending {
            background: #f1f5f9;
            color: #94a3b8;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            }
            50% {
                box-shadow: 0 4px 16px rgba(59, 130, 246, 0.5);
            }
        }

        .timeline-content {
            margin-left: 1.5rem;
            flex: 1;
        }

        .timeline-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            margin: 0 0 0.25rem 0;
        }

        .timeline-desc {
            font-size: 0.9rem;
            color: #64748b;
            margin: 0;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.5rem;
        }

        .current-status {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 0.5rem;
            display: inline-block;
        }

        .order-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f1f5f9;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .view-details-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .view-details-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 5rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #475569;
            margin: 1rem 0 0.5rem 0;
        }

        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .timeline {
                margin: 1rem 0;
            }

            .order-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_customer.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Back Link -->
        <a href="../admin/customer_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-shipping-fast"></i> Track Your Orders</h1>
            <p>Monitor the status of your medication deliveries in real-time</p>
        </div>

        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No orders yet</h3>
                <p>You haven't placed any orders. Start shopping to see your orders here!</p>
                <a href="../admin/customer_dashboard.php" class="view-details-btn" style="margin-top: 1.5rem;">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <?php foreach ($orders as $order):
                $status = strtolower($order['order_status']);
                $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                $current_index = array_search($status, $statuses);

                $status_info = [
                    'pending' => [
                        'icon' => 'fa-clock',
                        'title' => 'Order Placed',
                        'desc' => 'We have received your order',
                        'date' => date('M j, Y g:i A', strtotime($order['order_date']))
                    ],
                    'processing' => [
                        'icon' => 'fa-box',
                        'title' => 'Processing',
                        'desc' => 'Pharmacy is preparing your medications',
                        'date' => 'In progress'
                    ],
                    'shipped' => [
                        'icon' => 'fa-truck',
                        'title' => 'Shipped',
                        'desc' => 'Your order is on the way',
                        'date' => 'Estimated delivery: 2-3 days'
                    ],
                    'delivered' => [
                        'icon' => 'fa-check-circle',
                        'title' => 'Delivered',
                        'desc' => 'Order has been delivered successfully',
                        'date' => 'Completed'
                    ]
                ];
            ?>
                <div class="order-card">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div>
                            <div class="order-number">
                                Order #<?php echo htmlspecialchars($order['invoice_no']); ?>
                            </div>
                            <div class="order-date">
                                Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>

                    <!-- Timeline -->
                    <div class="timeline">
                        <?php foreach ($statuses as $index => $step_status):
                            $info = $status_info[$step_status];
                            if ($index < $current_index) {
                                $class = 'completed';
                            } elseif ($index == $current_index) {
                                $class = 'current';
                            } else {
                                $class = 'pending';
                            }
                        ?>
                            <div class="timeline-step <?php echo $class; ?>">
                                <div class="timeline-icon <?php echo $class; ?>">
                                    <i class="fas <?php echo $info['icon']; ?>"></i>
                                </div>

                                <div class="timeline-content">
                                    <div class="timeline-title"><?php echo $info['title']; ?></div>
                                    <div class="timeline-desc"><?php echo $info['desc']; ?></div>
                                    <div class="timeline-date"><?php echo $info['date']; ?></div>

                                    <?php if ($index == $current_index): ?>
                                        <div class="current-status">
                                            <i class="fas fa-info-circle"></i> Current Status
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary">
                        <div class="summary-item">
                            <div class="summary-label">Order Total</div>
                            <div class="summary-value">GHS <?php echo number_format($order['order_amount'] ?? 0, 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Payment Status</div>
                            <div class="summary-value" style="color: <?php echo $order['order_status'] == 'delivered' ? 'var(--success-color)' : 'var(--warning-color)'; ?>">
                                <?php echo ucfirst($order['order_status'] ?? 'Pending'); ?>
                            </div>
                        </div>
                        <div class="summary-item">
                            <a href="../view/order_details.php?order_id=<?php echo $order['order_id']; ?>" class="view-details-btn">
                                <i class="fas fa-receipt"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>
</body>
</html>
