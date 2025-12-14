<?php
/**
 * Deliveries Management - Super Admin
 * Manage delivery assignments and track delivery status
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
$filter_rider = isset($_GET['rider']) ? intval($_GET['rider']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get delivery statistics
$stats_query = "SELECT
                    COUNT(*) as total_deliveries,
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned_count,
                    COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up_count,
                    COUNT(CASE WHEN status = 'in_transit' THEN 1 END) as in_transit_count,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_count,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count
                FROM delivery_assignments";
$stats = $conn->query($stats_query)->fetch_assoc();

// Get active riders
$riders_query = "SELECT rider_id, rider_name, rider_phone, status, total_deliveries, rating
                 FROM riders
                 ORDER BY rider_name";
$riders_result = $conn->query($riders_query);
$riders = [];
while ($row = $riders_result->fetch_assoc()) {
    $riders[] = $row;
}

// Build deliveries query with filters
$deliveries_query = "SELECT da.*,
                            r.rider_name, r.rider_phone, r.status as rider_status,
                            c.customer_name, c.customer_contact,
                            p.customer_name as pharmacy_name,
                            o.invoice_no, o.order_status
                     FROM delivery_assignments da
                     INNER JOIN riders r ON da.rider_id = r.rider_id
                     INNER JOIN customer c ON da.customer_id = c.customer_id
                     LEFT JOIN customer p ON da.pharmacy_id = p.customer_id
                     INNER JOIN orders o ON da.order_id = o.order_id
                     WHERE 1=1";

$params = [];
$types = "";

if ($filter_status !== 'all') {
    $deliveries_query .= " AND da.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if ($filter_rider > 0) {
    $deliveries_query .= " AND da.rider_id = ?";
    $params[] = $filter_rider;
    $types .= "i";
}

if (!empty($search_query)) {
    $deliveries_query .= " AND (o.invoice_no LIKE ? OR c.customer_name LIKE ? OR r.rider_name LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$deliveries_query .= " ORDER BY da.assigned_at DESC LIMIT 100";

$stmt = $conn->prepare($deliveries_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$deliveries_result = $stmt->get_result();
$deliveries = [];
while ($row = $deliveries_result->fetch_assoc()) {
    $deliveries[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deliveries Management - PharmaVault Admin</title>

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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stats-card.assigned { border-left-color: var(--info); }
        .stats-card.in-transit { border-left-color: var(--warning); }
        .stats-card.delivered { border-left-color: var(--success); }
        .stats-card.cancelled { border-left-color: var(--danger); }

        .stats-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-card, .deliveries-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .badge-delivery {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-assigned { background: #dbeafe; color: #1e40af; }
        .badge-picked-up { background: #fef3c7; color: #92400e; }
        .badge-in-transit { background: #fef3c7; color: #92400e; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.4rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: white;
            border: 2px solid #667eea;
        }

        .timeline-item.completed::before {
            background: #10b981;
            border-color: #10b981;
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
            <h1><i class="fas fa-truck me-3"></i>Deliveries Management</h1>
            <p class="mb-0">Track and manage delivery assignments</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stats-card assigned">
                <div class="stats-number text-info"><?php echo $stats['assigned_count']; ?></div>
                <div class="stats-label">Assigned</div>
            </div>

            <div class="stats-card in-transit">
                <div class="stats-number text-warning">
                    <?php echo $stats['picked_up_count'] + $stats['in_transit_count']; ?>
                </div>
                <div class="stats-label">In Transit</div>
            </div>

            <div class="stats-card delivered">
                <div class="stats-number text-success"><?php echo $stats['delivered_count']; ?></div>
                <div class="stats-label">Delivered</div>
            </div>

            <div class="stats-card cancelled">
                <div class="stats-number text-danger"><?php echo $stats['cancelled_count']; ?></div>
                <div class="stats-label">Cancelled</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-filter me-2"></i>Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="assigned" <?php echo $filter_status === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="picked_up" <?php echo $filter_status === 'picked_up' ? 'selected' : ''; ?>>Picked Up</option>
                        <option value="in_transit" <?php echo $filter_status === 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                        <option value="delivered" <?php echo $filter_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-motorcycle me-2"></i>Rider</label>
                    <select name="rider" class="form-select">
                        <option value="0">All Riders</option>
                        <?php foreach ($riders as $rider): ?>
                            <option value="<?php echo $rider['rider_id']; ?>"
                                    <?php echo $filter_rider == $rider['rider_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rider['rider_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Invoice, customer, rider..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>

                <div class="col-12">
                    <a href="deliveries.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Deliveries List -->
        <div class="deliveries-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Delivery Assignments</h4>
                <span class="text-muted"><?php echo count($deliveries); ?> deliveries</span>
            </div>

            <?php if (count($deliveries) > 0): ?>
                <?php foreach ($deliveries as $delivery): ?>
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="fas fa-receipt me-2 text-primary"></i>
                                    Invoice: <?php echo htmlspecialchars($delivery['invoice_no']); ?>
                                </h5>

                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-user text-info me-2"></i>
                                            <strong>Customer:</strong> <?php echo htmlspecialchars($delivery['customer_name']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-phone text-success me-2"></i>
                                            <?php echo htmlspecialchars($delivery['customer_contact']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-motorcycle text-warning me-2"></i>
                                            <strong>Rider:</strong> <?php echo htmlspecialchars($delivery['rider_name']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-phone text-success me-2"></i>
                                            <?php echo htmlspecialchars($delivery['rider_phone']); ?>
                                        </p>
                                    </div>
                                </div>

                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <strong>Delivery Address:</strong> <?php echo htmlspecialchars($delivery['delivery_address']); ?>
                                </p>

                                <?php if (!empty($delivery['distance_km'])): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-route text-primary me-2"></i>
                                        <strong>Distance:</strong> <?php echo number_format($delivery['distance_km'], 2); ?> km
                                    </p>
                                <?php endif; ?>

                                <p class="mb-1">
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                                    <strong>Delivery Fee:</strong> GH₵ <?php echo number_format($delivery['delivery_fee'], 2); ?>
                                </p>
                            </div>

                            <div class="col-md-4">
                                <?php
                                $status_class = '';
                                switch ($delivery['status']) {
                                    case 'assigned': $status_class = 'badge-assigned'; break;
                                    case 'picked_up': $status_class = 'badge-picked-up'; break;
                                    case 'in_transit': $status_class = 'badge-in-transit'; break;
                                    case 'delivered': $status_class = 'badge-delivered'; break;
                                    case 'cancelled': $status_class = 'badge-cancelled'; break;
                                }
                                ?>
                                <span class="badge-delivery <?php echo $status_class; ?> mb-3 d-inline-block">
                                    <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                                </span>

                                <div class="mb-2">
                                    <small class="text-muted">Assigned: <?php echo date('M d, Y H:i', strtotime($delivery['assigned_at'])); ?></small>
                                </div>

                                <?php if (!empty($delivery['delivered_at'])): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">Delivered: <?php echo date('M d, Y H:i', strtotime($delivery['delivered_at'])); ?></small>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2">
                                    <button class="btn btn-sm btn-primary" onclick="viewDeliveryDetails(<?php echo $delivery['assignment_id']; ?>)">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </button>
                                    <?php if ($delivery['status'] !== 'delivered' && $delivery['status'] !== 'cancelled'): ?>
                                        <button class="btn btn-sm btn-warning" onclick="updateDeliveryStatus(<?php echo $delivery['assignment_id']; ?>)">
                                            <i class="fas fa-edit me-2"></i>Update Status
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-4x text-muted mb-3"></i>
                    <h5>No Deliveries Found</h5>
                    <p class="text-muted">No delivery assignments match your criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delivery Details Modal -->
    <div class="modal fade" id="deliveryDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="deliveryDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Delivery Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="assignmentId" name="assignment_id">
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="assigned">Assigned</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitStatusUpdate()">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewDeliveryDetails(assignmentId) {
            const modal = new bootstrap.Modal(document.getElementById('deliveryDetailsModal'));
            modal.show();

            fetch(`../actions/get_delivery_details.php?assignment_id=${assignmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDeliveryDetails(data.delivery);
                    } else {
                        document.getElementById('deliveryDetailsContent').innerHTML =
                            '<div class="alert alert-danger">Failed to load delivery details</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('deliveryDetailsContent').innerHTML =
                        '<div class="alert alert-danger">An error occurred</div>';
                });
        }

        function displayDeliveryDetails(delivery) {
            const timeline = generateTimeline(delivery);
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Delivery Information</h6>
                        <p><strong>Assignment ID:</strong> #${delivery.assignment_id}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${getStatusBadge(delivery.status)}">${delivery.status}</span></p>
                        <p><strong>Delivery Fee:</strong> GH₵ ${parseFloat(delivery.delivery_fee).toFixed(2)}</p>
                        <p><strong>Distance:</strong> ${delivery.distance_km ? delivery.distance_km + ' km' : 'N/A'}</p>
                        <p><strong>Pickup Address:</strong> ${delivery.pickup_address}</p>
                        <p><strong>Delivery Address:</strong> ${delivery.delivery_address}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Timeline</h6>
                        <div class="timeline">
                            ${timeline}
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('deliveryDetailsContent').innerHTML = html;
        }

        function generateTimeline(delivery) {
            let timeline = '';
            if (delivery.assigned_at) {
                timeline += `<div class="timeline-item completed"><strong>Assigned</strong><br><small>${new Date(delivery.assigned_at).toLocaleString()}</small></div>`;
            }
            if (delivery.picked_up_at) {
                timeline += `<div class="timeline-item completed"><strong>Picked Up</strong><br><small>${new Date(delivery.picked_up_at).toLocaleString()}</small></div>`;
            }
            if (delivery.delivered_at) {
                timeline += `<div class="timeline-item completed"><strong>Delivered</strong><br><small>${new Date(delivery.delivered_at).toLocaleString()}</small></div>`;
            }
            if (delivery.cancelled_at) {
                timeline += `<div class="timeline-item completed"><strong>Cancelled</strong><br><small>${new Date(delivery.cancelled_at).toLocaleString()}</small></div>`;
            }
            return timeline || '<p class="text-muted">No timeline data available</p>';
        }

        function getStatusBadge(status) {
            const badges = {
                'assigned': 'info',
                'picked_up': 'warning',
                'in_transit': 'warning',
                'delivered': 'success',
                'cancelled': 'danger'
            };
            return badges[status] || 'secondary';
        }

        function updateDeliveryStatus(assignmentId) {
            document.getElementById('assignmentId').value = assignmentId;
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        }

        function submitStatusUpdate() {
            const form = document.getElementById('updateStatusForm');
            const formData = new FormData(form);

            fetch('../actions/update_delivery_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Delivery status updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update status'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
