<?php
/**
 * Pending Verifications - Super Admin
 * Manage pending prescriptions, pharmacy registrations, and product approvals
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');
require_once('../controllers/product_controller.php');

// Check if user is super admin
if (!isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

// Get statistics
$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Pending prescriptions count
$pending_prescriptions_query = "SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'";
$pending_prescriptions = $conn->query($pending_prescriptions_query)->fetch_assoc()['count'];

// Count of pharmacy registrations (total pharmacies)
$pending_pharmacies_query = "SELECT COUNT(*) as count FROM customer WHERE user_role = 1";
$pending_pharmacies = $conn->query($pending_pharmacies_query)->fetch_assoc()['count'];

// Get all pending prescriptions with customer details
$prescriptions_query = "SELECT p.*, c.customer_name, c.customer_email, c.customer_contact
                       FROM prescriptions p
                       INNER JOIN customer c ON p.customer_id = c.customer_id
                       WHERE p.status = 'pending'
                       ORDER BY p.uploaded_at DESC";
$prescriptions_result = $conn->query($prescriptions_query);
$prescriptions = [];
if ($prescriptions_result) {
    while ($row = $prescriptions_result->fetch_assoc()) {
        $prescriptions[] = $row;
    }
}

// Get recent pharmacy registrations
$pharmacies_query = "SELECT customer_id, customer_name, customer_email, customer_contact,
                            customer_city, customer_country
                     FROM customer
                     WHERE user_role = 1
                     ORDER BY customer_id DESC
                     LIMIT 20";
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
    <title>Pending Verifications - PharmaVault Admin</title>

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

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .verification-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .verification-item:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .prescription-image {
            max-width: 150px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .prescription-image:hover {
            transform: scale(1.05);
        }

        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-action {
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-approve {
            background: var(--success);
            color: white;
            border: none;
        }

        .btn-approve:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-reject {
            background: var(--danger);
            color: white;
            border: none;
        }

        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            padding: 1rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border-bottom: 3px solid var(--primary);
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
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-clipboard-check me-3"></i>Pending Verifications</h1>
            <p class="mb-0">Review and approve pending items</p>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $pending_prescriptions; ?></div>
                    <div class="stats-label">Pending Prescriptions</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-info"><?php echo $pending_pharmacies; ?></div>
                    <div class="stats-label">Total Pharmacies</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-success">
                        <?php echo $pending_prescriptions + $pending_pharmacies; ?>
                    </div>
                    <div class="stats-label">Total Pending Items</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="verificationTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#prescriptions">
                    <i class="fas fa-file-prescription me-2"></i>Prescriptions
                    <?php if ($pending_prescriptions > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $pending_prescriptions; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#pharmacies">
                    <i class="fas fa-store me-2"></i>Pharmacy Registrations
                    <?php if ($pending_pharmacies > 0): ?>
                        <span class="badge bg-info ms-2"><?php echo $pending_pharmacies; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Prescriptions Tab -->
            <div class="tab-pane fade show active" id="prescriptions">
                <div class="section-card">
                    <h3 class="section-title">Pending Prescription Verifications</h3>

                    <?php if (count($prescriptions) > 0): ?>
                        <?php foreach ($prescriptions as $rx): ?>
                            <div class="verification-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if (!empty($rx['prescription_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($rx['prescription_image']); ?>"
                                                 alt="Prescription"
                                                 class="prescription-image"
                                                 onclick="viewImage('../<?php echo htmlspecialchars($rx['prescription_image']); ?>')">
                                        <?php else: ?>
                                            <div class="text-center text-muted">
                                                <i class="fas fa-file-medical fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="mb-2"><?php echo htmlspecialchars($rx['customer_name']); ?></h5>
                                        <p class="mb-1"><strong>Prescription #:</strong> <?php echo htmlspecialchars($rx['prescription_number']); ?></p>
                                        <p class="mb-1"><strong>Doctor:</strong> <?php echo htmlspecialchars($rx['doctor_name'] ?? 'N/A'); ?></p>
                                        <p class="mb-1"><strong>Issue Date:</strong> <?php echo date('M d, Y', strtotime($rx['issue_date'])); ?></p>
                                        <p class="mb-1"><strong>Uploaded:</strong> <?php echo date('M d, Y H:i', strtotime($rx['uploaded_at'])); ?></p>
                                        <span class="badge-status badge-pending">Pending</span>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button class="btn btn-action btn-approve mb-2"
                                                onclick="verifyPrescription(<?php echo $rx['prescription_id']; ?>, 'verified')">
                                            <i class="fas fa-check me-2"></i>Approve
                                        </button>
                                        <button class="btn btn-action btn-reject"
                                                onclick="verifyPrescription(<?php echo $rx['prescription_id']; ?>, 'rejected')">
                                            <i class="fas fa-times me-2"></i>Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>No Pending Prescriptions</h4>
                            <p class="text-muted">All prescriptions have been verified</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pharmacies Tab -->
            <div class="tab-pane fade" id="pharmacies">
                <div class="section-card">
                    <h3 class="section-title">Recent Pharmacy Registrations</h3>

                    <?php if (count($pharmacies) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pharmacy Name</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Pharmacy ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pharmacies as $pharmacy): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($pharmacy['customer_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($pharmacy['customer_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($pharmacy['customer_contact']); ?></td>
                                            <td><?php echo htmlspecialchars($pharmacy['customer_city'] . ', ' . $pharmacy['customer_country']); ?></td>
                                            <td>ID: <?php echo $pharmacy['customer_id']; ?></td>
                                            <td>
                                                <a href="view_pharmacy.php?id=<?php echo $pharmacy['customer_id']; ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-store fa-4x text-muted mb-3"></i>
                            <h4>No Recent Registrations</h4>
                            <p class="text-muted">No new pharmacies registered in the last 7 days</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prescription Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Prescription" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewImage(imagePath) {
            document.getElementById('modalImage').src = imagePath;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }

        function verifyPrescription(prescriptionId, status) {
            const action = status === 'verified' ? 'approve' : 'reject';

            if (!confirm(`Are you sure you want to ${action} this prescription?`)) {
                return;
            }

            // Show loading state
            const buttons = event.target.closest('.col-md-4').querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = true);

            fetch('../actions/verify_prescription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `prescription_id=${prescriptionId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Prescription ${action}d successfully!`);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update prescription'));
                    buttons.forEach(btn => btn.disabled = false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                buttons.forEach(btn => btn.disabled = false);
            });
        }
    </script>
</body>
</html>
