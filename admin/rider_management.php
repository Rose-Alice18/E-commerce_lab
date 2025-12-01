<?php
/**
 * Rider Management Page - Super Admin
 * Manage motorcycle riders for delivery service
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/rider_controller.php');

// Check if user is logged in as super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 0) {
    header("Location: ../login/login_view.php");
    exit();
}

// Fetch all riders
$riders = get_all_riders_ctr();
$delivery_stats = get_delivery_statistics_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Management - PharmaVault Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <style>
        :root {
            --primary-color: #667eea;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-color);
        }

        .stats-card.success {
            border-left-color: var(--success-color);
        }

        .stats-card.warning {
            border-left-color: var(--warning-color);
        }

        .stats-card.danger {
            border-left-color: var(--danger-color);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .riders-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color), #764ba2);
            color: white;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-busy {
            background: #fed7aa;
            color: #92400e;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-add-rider {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add-rider:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            border: none;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #eff6ff;
            color: #1e40af;
        }

        .btn-edit:hover {
            background: #dbeafe;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fee2e2;
        }

        .btn-view {
            background: #f0fdf4;
            color: #15803d;
        }

        .btn-view:hover {
            background: #dcfce7;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #764ba2);
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2"><i class="fas fa-motorcycle"></i> Rider Management</h1>
                    <p class="text-muted mb-0">Manage delivery riders and assignments</p>
                </div>
                <button class="btn-add-rider" data-bs-toggle="modal" data-bs-target="#addRiderModal">
                    <i class="fas fa-plus"></i> Add New Rider
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($riders); ?></div>
                    <div class="stats-label"><i class="fas fa-users"></i> Total Riders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="stats-number"><?php echo count(array_filter($riders, fn($r) => $r['status'] == 'active')); ?></div>
                    <div class="stats-label"><i class="fas fa-check-circle"></i> Active Riders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="stats-number"><?php echo $delivery_stats['pending'] ?? 0; ?></div>
                    <div class="stats-label"><i class="fas fa-clock"></i> Pending Deliveries</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="stats-number"><?php echo $delivery_stats['delivered'] ?? 0; ?></div>
                    <div class="stats-label"><i class="fas fa-check-double"></i> Completed Today</div>
                </div>
            </div>
        </div>

        <!-- Riders Table -->
        <div class="riders-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Vehicle Number</th>
                        <th>Status</th>
                        <th>Total Deliveries</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-motorcycle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No riders found. Add your first rider to get started.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riders as $rider): ?>
                            <tr>
                                <td>#<?php echo $rider['rider_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($rider['rider_name']); ?></strong></td>
                                <td><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($rider['rider_phone']); ?></td>
                                <td><?php echo htmlspecialchars($rider['vehicle_number']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $rider['status']; ?>">
                                        <?php echo ucfirst($rider['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $rider['total_deliveries']; ?></td>
                                <td>
                                    <i class="fas fa-star text-warning"></i>
                                    <?php echo number_format($rider['rating'], 1); ?>
                                </td>
                                <td>
                                    <button class="action-btn btn-view me-1" onclick="viewRider(<?php echo $rider['rider_id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn btn-edit me-1" onclick="editRider(<?php echo $rider['rider_id']; ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn btn-delete" onclick="deleteRider(<?php echo $rider['rider_id']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Rider Modal -->
    <div class="modal fade" id="addRiderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-motorcycle"></i> Add New Rider</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRiderForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="rider_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" class="form-control" name="rider_phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="rider_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Driver's License</label>
                                <input type="text" class="form-control" name="rider_license">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Number *</label>
                                <input type="text" class="form-control" name="vehicle_number" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="rider_address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Rider
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script>
        // Add rider form submission
        document.getElementById('addRiderForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('../actions/rider_actions.php?action=add', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('Rider added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });

        // View rider details
        function viewRider(riderId) {
            window.location.href = `rider_details.php?id=${riderId}`;
        }

        // Edit rider
        function editRider(riderId) {
            // TODO: Implement edit modal
            alert('Edit functionality will be implemented');
        }

        // Delete rider
        async function deleteRider(riderId) {
            if (!confirm('Are you sure you want to delete this rider?')) {
                return;
            }

            try {
                const response = await fetch(`../actions/rider_actions.php?action=delete&rider_id=${riderId}`, {
                    method: 'POST'
                });

                const result = await response.json();

                if (result.success) {
                    alert('Rider deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    </script>
</body>
</html>
