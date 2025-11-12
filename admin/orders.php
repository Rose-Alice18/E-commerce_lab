<?php
/**
 * Orders Management Page
 * Role: Pharmacy Admin (1)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');

// Check if user is logged in as pharmacy admin
if (!isLoggedIn() || !isPharmacyAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}

$user_id = getUserId();

// Get all orders for this pharmacy's products
// For now, we'll get all orders - you can filter by pharmacy later
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">

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
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-out-for-delivery { background: #e0e7ff; color: #5b21b6; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            color: #1f2937;
            font-weight: 500;
        }

        .order-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-status {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-pending { background: #fbbf24; color: white; }
        .btn-pending:hover { background: #f59e0b; }

        .btn-processing { background: #3b82f6; color: white; }
        .btn-processing:hover { background: #2563eb; }

        .btn-out-delivery { background: #8b5cf6; color: white; }
        .btn-out-delivery:hover { background: #7c3aed; }

        .btn-delivered { background: #10b981; color: white; }
        .btn-delivered:hover { background: #059669; }

        .btn-cancelled { background: #ef4444; color: white; }
        .btn-cancelled:hover { background: #dc2626; }

        .filter-section {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .order-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart me-2"></i>Orders Management</h1>
            <p class="text-muted mb-0">View and manage customer orders</p>
        </div>

        <div class="orders-container">
            <!-- Filter Buttons -->
            <div class="filter-section">
                <button class="filter-btn active" onclick="filterOrders('all')">
                    <i class="fas fa-list me-1"></i> All Orders
                </button>
                <button class="filter-btn" onclick="filterOrders('Pending')">
                    <i class="fas fa-clock me-1"></i> Pending
                </button>
                <button class="filter-btn" onclick="filterOrders('Processing')">
                    <i class="fas fa-cog me-1"></i> Processing
                </button>
                <button class="filter-btn" onclick="filterOrders('Out for Delivery')">
                    <i class="fas fa-truck me-1"></i> Out for Delivery
                </button>
                <button class="filter-btn" onclick="filterOrders('Delivered')">
                    <i class="fas fa-check-circle me-1"></i> Delivered
                </button>
            </div>

            <!-- Orders List -->
            <div id="ordersContainer">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Loading orders...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/sidebar.js"></script>

    <script>
        let allOrders = [];
        let currentFilter = 'all';

        // Load orders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
        });

        function loadOrders() {
            fetch('../actions/get_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allOrders = data.orders;
                        displayOrders(allOrders);
                    } else {
                        showError('Failed to load orders');
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    showError('Error loading orders');
                });
        }

        function displayOrders(orders) {
            const container = document.getElementById('ordersContainer');

            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Orders Found</h3>
                        <p class="text-muted">There are no orders matching your filter.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = orders.map(order => `
                <div class="order-card" data-status="${order.order_status}">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #${order.invoice_no}</div>
                            <small class="text-muted">${order.order_date}</small>
                        </div>
                        <span class="status-badge status-${order.order_status.toLowerCase().replace(' ', '-')}">
                            ${order.order_status}
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label">Customer</span>
                            <span class="detail-value">${order.customer_name}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Contact</span>
                            <span class="detail-value">${order.customer_contact || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Items</span>
                            <span class="detail-value">${order.total_items || 0} items</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Amount</span>
                            <span class="detail-value">GH₵ ${order.total_amount || '0.00'}</span>
                        </div>
                    </div>

                    <div class="order-actions">
                        ${order.order_status !== 'Delivered' && order.order_status !== 'Cancelled' ? `
                            <button class="btn-status btn-processing" onclick="updateOrderStatus(${order.order_id}, 'Processing')">
                                <i class="fas fa-cog me-1"></i> Set Processing
                            </button>
                            <button class="btn-status btn-out-delivery" onclick="updateOrderStatus(${order.order_id}, 'Out for Delivery')">
                                <i class="fas fa-truck me-1"></i> Out for Delivery
                            </button>
                            <button class="btn-status btn-delivered" onclick="updateOrderStatus(${order.order_id}, 'Delivered')">
                                <i class="fas fa-check-circle me-1"></i> Mark Delivered
                            </button>
                        ` : ''}
                        <button class="btn btn-outline-secondary btn-sm" onclick="viewOrderDetails(${order.order_id})">
                            <i class="fas fa-eye me-1"></i> View Details
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function filterOrders(status) {
            currentFilter = status;

            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.filter-btn').classList.add('active');

            // Filter and display orders
            const filtered = status === 'all'
                ? allOrders
                : allOrders.filter(order => order.order_status === status);

            displayOrders(filtered);
        }

        function updateOrderStatus(orderId, newStatus) {
            Swal.fire({
                title: 'Update Order Status',
                html: `
                    <p>Change status to <strong>${newStatus}</strong>?</p>
                    <textarea id="statusNotes" class="swal2-input" placeholder="Add notes (optional)" style="width: 100%; height: 80px;"></textarea>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Update Status',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return document.getElementById('statusNotes').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const notes = result.value || '';

                    fetch('../actions/update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `order_id=${orderId}&status=${encodeURIComponent(newStatus)}&notes=${encodeURIComponent(notes)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Updated!', data.message, 'success');
                            loadOrders(); // Reload orders
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Failed to update order status', 'error');
                    });
                }
            });
        }

        function viewOrderDetails(orderId) {
            // TODO: Implement order details modal or redirect to details page
            Swal.fire('Order Details', 'Order details view coming soon!', 'info');
        }

        function showError(message) {
            document.getElementById('ordersContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <h3>Error</h3>
                    <p class="text-muted">${message}</p>
                    <button class="btn btn-primary mt-3" onclick="loadOrders()">
                        <i class="fas fa-redo me-1"></i> Retry
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>
