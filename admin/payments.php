<?php
/**
 * Payments Management - Super Admin
 * View and manage all payment transactions
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
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get payment statistics
// Note: payment table doesn't have payment_status, so we assume all payments are completed
$stats_query = "SELECT
                    COUNT(*) as total_transactions,
                    SUM(amt) as total_revenue,
                    0 as pending_amount,
                    0 as failed_amount,
                    COUNT(*) as completed_count,
                    0 as pending_count,
                    0 as failed_count
                FROM payment";
$stats = $conn->query($stats_query)->fetch_assoc();

// Build payments query with filters
// Note: orders table doesn't have delivery_address, payment table doesn't have payment_status or transaction_ref
$payments_query = "SELECT p.pay_id, p.amt, p.currency, p.payment_date, p.customer_id, p.order_id,
                          o.invoice_no, o.order_date, o.order_status,
                          c.customer_name, c.customer_email, c.customer_contact, c.customer_address
                   FROM payment p
                   INNER JOIN orders o ON p.order_id = o.order_id
                   INNER JOIN customer c ON p.customer_id = c.customer_id
                   WHERE 1=1";

// Remove filter_status since payment table doesn't have payment_status
if (!empty($filter_date_from)) {
    $payments_query .= " AND DATE(p.payment_date) >= '" . $conn->real_escape_string($filter_date_from) . "'";
}

if (!empty($filter_date_to)) {
    $payments_query .= " AND DATE(p.payment_date) <= '" . $conn->real_escape_string($filter_date_to) . "'";
}

if (!empty($search_query)) {
    $payments_query .= " AND (o.invoice_no LIKE '%" . $conn->real_escape_string($search_query) . "%'
                         OR c.customer_name LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

$payments_query .= " ORDER BY p.payment_date DESC LIMIT 100";

$payments_result = $conn->query($payments_query);
$payments = [];
while ($row = $payments_result->fetch_assoc()) {
    $payments[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Management - PharmaVault Admin</title>

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
            --info: #3b82f6;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stats-card.revenue { border-left-color: var(--success); }
        .stats-card.pending { border-left-color: var(--warning); }
        .stats-card.failed { border-left-color: var(--danger); }
        .stats-card.total { border-left-color: var(--info); }

        .stats-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            float: right;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .payments-table-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .badge-payment {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .btn-view {
            background: var(--info);
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-2px);
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
            <h1><i class="fas fa-money-bill-wave me-3"></i>Payments Management</h1>
            <p class="mb-0">Monitor and manage all payment transactions</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stats-card revenue">
                <i class="fas fa-dollar-sign stats-icon text-success"></i>
                <div class="stats-number text-success">
                    GH₵ <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?>
                </div>
                <div class="stats-label">Total Revenue</div>
                <small class="text-muted"><?php echo $stats['completed_count']; ?> completed</small>
            </div>

            <div class="stats-card pending">
                <i class="fas fa-clock stats-icon text-warning"></i>
                <div class="stats-number text-warning">
                    GH₵ <?php echo number_format($stats['pending_amount'] ?? 0, 2); ?>
                </div>
                <div class="stats-label">Pending Payments</div>
                <small class="text-muted"><?php echo $stats['pending_count']; ?> pending</small>
            </div>

            <div class="stats-card failed">
                <i class="fas fa-times-circle stats-icon text-danger"></i>
                <div class="stats-number text-danger">
                    GH₵ <?php echo number_format($stats['failed_amount'] ?? 0, 2); ?>
                </div>
                <div class="stats-label">Failed Payments</div>
                <small class="text-muted"><?php echo $stats['failed_count']; ?> failed</small>
            </div>

            <div class="stats-card total">
                <i class="fas fa-receipt stats-icon text-info"></i>
                <div class="stats-number text-info">
                    <?php echo number_format($stats['total_transactions'] ?? 0); ?>
                </div>
                <div class="stats-label">Total Transactions</div>
                <small class="text-muted">All time</small>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-filter me-2"></i>Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="failed" <?php echo $filter_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar me-2"></i>From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar me-2"></i>To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Invoice, name, reference..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                    <a href="payments.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                    <button type="button" class="btn btn-success" onclick="exportPayments()">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </button>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="payments-table-card">
            <div class="table-header">
                <h4 class="mb-0">Payment Transactions</h4>
                <span class="text-muted"><?php echo count($payments); ?> transactions</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><code>#<?php echo $payment['pay_id']; ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['invoice_no']); ?></strong>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($payment['customer_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['customer_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?php echo htmlspecialchars($payment['currency'] ?? 'GHS'); ?> <?php echo number_format($payment['amt'], 2); ?></strong>
                                    </td>
                                    <td>Cash/Mobile Money</td>
                                    <td>
                                        <span class="badge-payment badge-completed">
                                            Completed
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-action btn-view"
                                                onclick="viewPaymentDetails(<?php echo $payment['pay_id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                    <h5>No Payments Found</h5>
                                    <p class="text-muted">No payment transactions match your criteria</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewPaymentDetails(paymentId) {
            const modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
            modal.show();

            fetch(`../actions/get_payment_details.php?pay_id=${paymentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPaymentDetails(data.payment);
                    } else {
                        document.getElementById('paymentDetailsContent').innerHTML =
                            '<div class="alert alert-danger">Failed to load payment details</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('paymentDetailsContent').innerHTML =
                        '<div class="alert alert-danger">An error occurred</div>';
                });
        }

        function displayPaymentDetails(payment) {
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Payment Information</h6>
                        <p><strong>Transaction ID:</strong> #${payment.pay_id}</p>
                        <p><strong>Amount:</strong> <span class="text-success">GH₵ ${parseFloat(payment.amount).toFixed(2)}</span></p>
                        <p><strong>Payment Method:</strong> ${payment.payment_method || 'N/A'}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${getStatusBadge(payment.payment_status)}">${payment.payment_status}</span></p>
                        <p><strong>Transaction Reference:</strong> ${payment.transaction_ref || 'N/A'}</p>
                        <p><strong>Payment Date:</strong> ${new Date(payment.payment_date).toLocaleString()}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Order Information</h6>
                        <p><strong>Invoice:</strong> ${payment.invoice_no}</p>
                        <p><strong>Customer:</strong> ${payment.customer_name}</p>
                        <p><strong>Email:</strong> ${payment.customer_email}</p>
                        <p><strong>Contact:</strong> ${payment.customer_contact}</p>
                        <p><strong>Order Date:</strong> ${new Date(payment.order_date).toLocaleString()}</p>
                    </div>
                </div>
            `;
            document.getElementById('paymentDetailsContent').innerHTML = html;
        }

        function getStatusBadge(status) {
            switch(status) {
                case 'completed': return 'success';
                case 'pending': return 'warning';
                case 'failed': return 'danger';
                default: return 'secondary';
            }
        }

        function exportPayments() {
            const url = new URL('../actions/export_payments.php', window.location.href);
            const params = new URLSearchParams(window.location.search);
            window.location.href = url + '?' + params.toString();
        }
    </script>
</body>
</html>
