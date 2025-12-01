<?php
/**
 * Pharmacy Admin - Prescriptions Management
 * View and verify customer prescriptions (only those made public)
 */

session_start();
require_once('../settings/core.php');

// Check if user is logged in and is admin (pharmacy or super admin)
requireAdmin();

require_once('../classes/prescription_class.php');

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Get filter status
$filter_status = isset($_GET['status']) ? $_GET['status'] : null;

// Get prescriptions based on user role
$prescription_obj = new Prescription();
$prescriptions = $prescription_obj->get_pharmacy_accessible_prescriptions($user_role, $filter_status);

// Get statistics
$stats = $prescription_obj->get_prescription_stats();

// Determine page title based on role
$page_title = ($user_role == 0 || $user_role == 7) ? "All Prescriptions" : "Customer Prescriptions";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PharmaVault</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">
    <link rel="stylesheet" href="../css/profile.css">

    <style>
        /* Green theme for pharmacy admin */
        :root {
            --primary-green: #11998e;
            --secondary-green: #38ef7d;
            --dark-green: #0d7a6f;
            --light-green: #e8f8f5;
        }

        .sidebar-pharmacy .sidebar {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        }

        .prescription-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .prescription-card:hover {
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.15);
            transform: translateY(-2px);
            border-left-color: var(--primary-green);
        }

        .prescription-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .prescription-number {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-green);
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .status-verified {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
        }

        .status-rejected {
            background: #ff4444;
            color: white;
        }

        .status-expired {
            background: #9e9e9e;
            color: white;
        }

        .prescription-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: var(--primary-green);
            font-size: 18px;
            width: 25px;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            display: block;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .prescription-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-view {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
            color: white;
        }

        .btn-verify {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-reject {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stats-icon {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .stats-number {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 10px 20px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .filter-tab:hover {
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .filter-tab.active {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            border-color: var(--primary-green);
            color: white;
        }

        .privacy-notice {
            background: var(--light-green);
            border-left: 4px solid var(--primary-green);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .privacy-notice i {
            color: var(--primary-green);
            margin-right: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php
    if ($user_role == 0 || $user_role == 7) {
        include '../view/components/sidebar_super_admin.php';
    } else {
        include '../view/components/sidebar_pharmacy_admin.php';
    }
    ?>

    <!-- Main Content -->
    <div class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-prescription"></i> <?php echo $page_title; ?>
                    </h1>
                </div>

                <!-- Privacy Notice for Pharmacy Admins -->
                <?php if ($user_role == 1): ?>
                <div class="privacy-notice">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Privacy Notice:</strong> You can only view prescriptions that customers have made public.
                    Customers control their prescription privacy in their dashboard.
                </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-prescription text-primary"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['total_prescriptions'] ?? 0; ?></div>
                            <div class="stats-label">Total Prescriptions</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['pending_count'] ?? 0; ?></div>
                            <div class="stats-label">Pending Verification</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['verified_count'] ?? 0; ?></div>
                            <div class="stats-label">Verified</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-times-circle text-danger"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['rejected_count'] ?? 0; ?></div>
                            <div class="stats-label">Rejected</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="?status=" class="filter-tab <?php echo !$filter_status ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> All
                    </a>
                    <a href="?status=pending" class="filter-tab <?php echo $filter_status == 'pending' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Pending
                    </a>
                    <a href="?status=verified" class="filter-tab <?php echo $filter_status == 'verified' ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle"></i> Verified
                    </a>
                    <a href="?status=rejected" class="filter-tab <?php echo $filter_status == 'rejected' ? 'active' : ''; ?>">
                        <i class="fas fa-times-circle"></i> Rejected
                    </a>
                    <a href="?status=expired" class="filter-tab <?php echo $filter_status == 'expired' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-times"></i> Expired
                    </a>
                </div>

                <!-- Prescriptions List -->
                <?php if (empty($prescriptions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-prescription"></i>
                        <h3>No Prescriptions Found</h3>
                        <p>
                            <?php if ($user_role == 1): ?>
                                No customers have made their prescriptions public yet, or there are no <?php echo $filter_status ? $filter_status : ''; ?> prescriptions.
                            <?php else: ?>
                                There are no prescriptions in the system yet.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($prescriptions as $prescription): ?>
                        <div class="prescription-card">
                            <div class="prescription-header">
                                <div class="prescription-number">
                                    <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($prescription['prescription_number']); ?>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $prescription['status']; ?>">
                                        <?php echo ucfirst($prescription['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="prescription-info">
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <span class="info-label">Patient</span>
                                        <div class="info-value"><?php echo htmlspecialchars($prescription['customer_name']); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-md"></i>
                                    <div>
                                        <span class="info-label">Doctor</span>
                                        <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_name'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <span class="info-label">Uploaded</span>
                                        <div class="info-value"><?php echo date('M d, Y', strtotime($prescription['uploaded_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-pills"></i>
                                    <div>
                                        <span class="info-label">Medications</span>
                                        <div class="info-value"><?php echo $prescription['item_count']; ?> items</div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($prescription['expiry_date'])): ?>
                            <div class="info-item mb-3">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <span class="info-label">Expiry Date</span>
                                    <div class="info-value"><?php echo date('M d, Y', strtotime($prescription['expiry_date'])); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="prescription-actions">
                                <a href="prescription_view.php?id=<?php echo $prescription['prescription_id']; ?>"
                                   class="btn btn-view">
                                    <i class="fas fa-eye"></i> View & Process
                                </a>
                                <?php if ($prescription['status'] == 'pending'): ?>
                                <button class="btn btn-verify"
                                        onclick="verifyPrescription(<?php echo $prescription['prescription_id']; ?>)">
                                    <i class="fas fa-check"></i> Verify
                                </button>
                                <button class="btn btn-reject"
                                        onclick="rejectPrescription(<?php echo $prescription['prescription_id']; ?>)">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
    </div>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js?v=2.5"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function verifyPrescription(prescriptionId) {
            Swal.fire({
                title: 'Verify Prescription',
                html: `
                    <textarea id="verifyNotes" class="swal2-textarea" placeholder="Enter verification notes (optional)"></textarea>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Verify',
                confirmButtonColor: '#11998e',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return {
                        notes: document.getElementById('verifyNotes').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/verify_prescription.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `prescription_id=${prescriptionId}&action=verify&notes=${encodeURIComponent(result.value.notes)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Verified!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }

        function rejectPrescription(prescriptionId) {
            Swal.fire({
                title: 'Reject Prescription',
                html: `
                    <textarea id="rejectReason" class="swal2-textarea" placeholder="Enter reason for rejection (required)" required></textarea>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#ff4444',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const reason = document.getElementById('rejectReason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please enter a reason for rejection');
                    }
                    return { reason };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/verify_prescription.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `prescription_id=${prescriptionId}&action=reject&notes=${encodeURIComponent(result.value.reason)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Rejected!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
