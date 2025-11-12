<?php
/**
 * Order History Page
 * Role: Pharmacy Admin (1)
 * Shows completed and past orders with reporting capabilities
 */
session_start();
require_once('../settings/core.php');

// Check if user is logged in as pharmacy admin
if (!isLoggedIn() || !isPharmacyAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}

$user_id = getUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - PharmaVault</title>

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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.delivered { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-icon.revenue { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-icon.cancelled { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

        .stat-info h6 {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-info h3 {
            margin: 0.25rem 0 0 0;
            color: #1f2937;
            font-weight: 700;
        }

        .filters-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .btn-filter {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-export {
            background: #10b981;
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-export:hover {
            background: #059669;
            color: white;
        }

        .orders-table-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            margin-top: 1rem;
        }

        .orders-table thead {
            background: #f9fafb;
        }

        .orders-table th {
            padding: 1rem;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .orders-table td {
            padding: 1rem;
            color: #6b7280;
            border-bottom: 1px solid #f3f4f6;
        }

        .orders-table tbody tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-out-for-delivery { background: #e0e7ff; color: #5b21b6; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .btn-view {
            padding: 0.4rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            background: #6366f1;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background: #4f46e5;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
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
            .filter-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-history me-2"></i>Order History</h1>
            <p class="text-muted mb-0">View completed and past orders</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container" id="statsContainer">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h6>Total Orders</h6>
                    <h3 id="totalOrders">0</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon delivered">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h6>Delivered Orders</h6>
                    <h3 id="deliveredOrders">0</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h6>Total Revenue</h6>
                    <h3 id="totalRevenue">GH₵ 0.00</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h6>Cancelled Orders</h6>
                    <h3 id="cancelledOrders">0</h3>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Orders</h5>
            <div class="filter-row">
                <div>
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" id="dateFrom">
                </div>
                <div>
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" id="dateTo">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Out for Delivery">Out for Delivery</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Invoice, customer...">
                </div>
                <div>
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-filter w-100" onclick="applyFilters()">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                </div>
                <div>
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-export w-100" onclick="exportToCSV()">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-table-card">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Order History</h5>
            <div class="table-wrapper">
                <div id="loadingContainer" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Loading order history...</p>
                </div>
                <table class="orders-table" id="ordersTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                    </tbody>
                </table>
                <div id="emptyState" style="display: none;">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Orders Found</h3>
                        <p>No orders match your current filters.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/sidebar.js"></script>

    <script>
        let allOrders = [];
        let filteredOrders = [];

        // Load orders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrderHistory();
        });

        function loadOrderHistory() {
            fetch('../actions/get_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allOrders = data.orders;
                        filteredOrders = allOrders;
                        updateStatistics();
                        displayOrders(allOrders);
                    } else {
                        showError('Failed to load order history');
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    showError('Error loading order history');
                });
        }

        function updateStatistics() {
            const total = allOrders.length;
            const delivered = allOrders.filter(o => o.order_status === 'Delivered').length;
            const cancelled = allOrders.filter(o => o.order_status === 'Cancelled').length;

            let totalRevenue = 0;
            allOrders.forEach(order => {
                if (order.order_status === 'Delivered') {
                    totalRevenue += parseFloat(order.total_amount || 0);
                }
            });

            document.getElementById('totalOrders').textContent = total;
            document.getElementById('deliveredOrders').textContent = delivered;
            document.getElementById('cancelledOrders').textContent = cancelled;
            document.getElementById('totalRevenue').textContent = 'GH₵ ' + totalRevenue.toFixed(2);
        }

        function displayOrders(orders) {
            const tableBody = document.getElementById('ordersTableBody');
            const table = document.getElementById('ordersTable');
            const loading = document.getElementById('loadingContainer');
            const empty = document.getElementById('emptyState');

            loading.style.display = 'none';

            if (orders.length === 0) {
                table.style.display = 'none';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            table.style.display = 'table';

            tableBody.innerHTML = orders.map(order => `
                <tr>
                    <td><strong>#${order.invoice_no}</strong></td>
                    <td>${order.customer_name || 'Unknown'}</td>
                    <td>${order.order_date}</td>
                    <td>${order.total_items || 0} items</td>
                    <td><strong>GH₵ ${order.total_amount || '0.00'}</strong></td>
                    <td>
                        <span class="status-badge status-${(order.order_status || 'pending').toLowerCase().replace(' ', '-')}">
                            ${order.order_status || 'Pending'}
                        </span>
                    </td>
                    <td>
                        <button class="btn-view" onclick="viewOrderDetails(${order.order_id})">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function applyFilters() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();

            filteredOrders = allOrders.filter(order => {
                // Date filter
                if (dateFrom) {
                    const orderDate = new Date(order.order_date);
                    const fromDate = new Date(dateFrom);
                    if (orderDate < fromDate) return false;
                }

                if (dateTo) {
                    const orderDate = new Date(order.order_date);
                    const toDate = new Date(dateTo);
                    if (orderDate > toDate) return false;
                }

                // Status filter
                if (status && order.order_status !== status) {
                    return false;
                }

                // Search filter
                if (search) {
                    const invoiceMatch = String(order.invoice_no).toLowerCase().includes(search);
                    const customerMatch = (order.customer_name || '').toLowerCase().includes(search);
                    if (!invoiceMatch && !customerMatch) {
                        return false;
                    }
                }

                return true;
            });

            displayOrders(filteredOrders);
        }

        function exportToCSV() {
            if (filteredOrders.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'No orders to export',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            // Create CSV content
            let csv = 'Invoice No,Customer,Date,Items,Amount,Status\n';

            filteredOrders.forEach(order => {
                csv += `${order.invoice_no},`;
                csv += `"${order.customer_name || 'Unknown'}",`;
                csv += `${order.order_date},`;
                csv += `${order.total_items || 0},`;
                csv += `${order.total_amount || '0.00'},`;
                csv += `${order.order_status || 'Pending'}\n`;
            });

            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `order_history_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'Order history exported successfully',
                confirmButtonColor: '#667eea'
            });
        }

        function viewOrderDetails(orderId) {
            window.location.href = `../view/order_details.php?order_id=${orderId}`;
        }

        function showError(message) {
            document.getElementById('loadingContainer').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <h3>Error</h3>
                    <p>${message}</p>
                    <button class="btn btn-primary mt-3" onclick="loadOrderHistory()">
                        <i class="fas fa-redo me-1"></i> Retry
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>
