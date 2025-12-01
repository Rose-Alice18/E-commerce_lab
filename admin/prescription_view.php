<?php
/**
 * Admin Prescription Details Page
 * View full prescription details for verification and processing
 */

session_start();
require_once('../settings/core.php');
requireAdmin();

require_once('../classes/prescription_class.php');

$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_role = $_SESSION['user_role'];

if (!$prescription_id) {
    header("Location: prescriptions.php");
    exit();
}

$prescription_obj = new Prescription();

// Check access permission
if (!$prescription_obj->can_pharmacy_access($prescription_id, $user_role)) {
    header("Location: prescriptions.php?error=access_denied");
    exit();
}

$prescription = $prescription_obj->get_prescription($prescription_id);
$items = $prescription_obj->get_prescription_items($prescription_id);
$history = $prescription_obj->get_prescription_history($prescription_id);

if (!$prescription) {
    header("Location: prescriptions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Details - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">
    <style>
        <?php if ($user_role == 1): ?>
        :root {
            --primary-color: #11998e;
            --secondary-color: #38ef7d;
        }
        <?php else: ?>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        <?php endif; ?>

        .prescription-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .medication-item {
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        .prescription-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .prescription-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .prescription-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php
    if ($user_role == 0 || $user_role == 7) {
        include '../view/components/sidebar_super_admin.php';
    } else {
        include '../view/components/sidebar_pharmacy_admin.php';
    }
    ?>

    <div class="main-content">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-prescription"></i> Prescription Details</h1>
                    <a href="prescriptions.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <!-- Prescription Header -->
                <div class="prescription-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2><i class="fas fa-barcode"></i> <?php echo htmlspecialchars($prescription['prescription_number']); ?></h2>
                            <p class="mb-0">
                                <i class="fas fa-calendar"></i> Uploaded: <?php echo date('F d, Y', strtotime($prescription['uploaded_at'])); ?>
                            </p>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $prescription['status']; ?>">
                                <?php echo strtoupper($prescription['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Patient Information -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4><i class="fas fa-user text-primary"></i> Patient Information</h4>
                            <hr>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Name:</strong></td>
                                    <td><?php echo htmlspecialchars($prescription['customer_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($prescription['customer_email']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Contact:</strong></td>
                                    <td><?php echo htmlspecialchars($prescription['customer_contact']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4><i class="fas fa-user-md text-primary"></i> Doctor Information</h4>
                            <hr>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Doctor Name:</strong></td>
                                    <td><?php echo htmlspecialchars($prescription['doctor_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>License:</strong></td>
                                    <td><?php echo htmlspecialchars($prescription['doctor_license'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Issue Date:</strong></td>
                                    <td><?php echo $prescription['issue_date'] ? date('M d, Y', strtotime($prescription['issue_date'])) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Expiry Date:</strong></td>
                                    <td><?php echo $prescription['expiry_date'] ? date('M d, Y', strtotime($prescription['expiry_date'])) : 'N/A'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="info-card">
                    <h4><i class="fas fa-pills text-primary"></i> Prescribed Medications</h4>
                    <hr>
                    <?php if (empty($items)): ?>
                        <p class="text-muted">No medication details available</p>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <div class="medication-item">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><?php echo htmlspecialchars($item['medication_name']); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <i class="fas fa-prescription-bottle"></i> Dosage: <?php echo htmlspecialchars($item['dosage'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <i class="fas fa-hashtag"></i> Quantity: <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <i class="fas fa-clock"></i> Frequency: <?php echo htmlspecialchars($item['frequency'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <?php if ($item['duration']): ?>
                                    <div class="mt-2">
                                        <i class="fas fa-calendar-alt"></i> Duration: <?php echo htmlspecialchars($item['duration']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Prescription Images -->
                <?php if ($prescription['prescription_image']): ?>
                    <div class="info-card">
                        <h4><i class="fas fa-images text-primary"></i> Prescription Images</h4>
                        <hr>
                        <div class="prescription-images">
                            <img src="../<?php echo htmlspecialchars($prescription['prescription_image']); ?>"
                                 class="prescription-image"
                                 onclick="viewImage(this.src)"
                                 alt="Prescription">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Verification History -->
                <?php if (!empty($history)): ?>
                    <div class="info-card">
                        <h4><i class="fas fa-history text-primary"></i> Verification History</h4>
                        <hr>
                        <div class="timeline">
                            <?php foreach ($history as $log): ?>
                                <div class="timeline-item mb-3">
                                    <strong><?php echo htmlspecialchars($log['performed_by_name']); ?></strong>
                                    <span class="text-muted">- <?php echo ucfirst($log['action']); ?></span>
                                    <div class="text-muted small">
                                        <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                    </div>
                                    <?php if ($log['notes']): ?>
                                        <div class="mt-1 text-muted"><?php echo htmlspecialchars($log['notes']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php if ($prescription['status'] == 'pending'): ?>
                    <div class="info-card">
                        <h4><i class="fas fa-tasks text-primary"></i> Actions</h4>
                        <hr>
                        <div class="d-flex gap-3">
                            <button class="btn btn-success btn-lg" onclick="verifyPrescription()">
                                <i class="fas fa-check-circle"></i> Verify Prescription
                            </button>
                            <button class="btn btn-danger btn-lg" onclick="rejectPrescription()">
                                <i class="fas fa-times-circle"></i> Reject Prescription
                            </button>
                        </div>
                    </div>
                <?php elseif ($prescription['status'] == 'verified'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        This prescription has been verified by <strong><?php echo htmlspecialchars($prescription['verified_by_name'] ?? 'Administrator'); ?></strong>
                        on <?php echo date('M d, Y', strtotime($prescription['verified_at'])); ?>
                    </div>
                <?php elseif ($prescription['status'] == 'rejected'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        This prescription was rejected.
                        <?php if ($prescription['rejection_reason']): ?>
                            <br><strong>Reason:</strong> <?php echo htmlspecialchars($prescription['rejection_reason']); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function viewImage(src) {
            Swal.fire({
                imageUrl: src,
                imageAlt: 'Prescription Image',
                showCloseButton: true,
                showConfirmButton: false,
                width: '80%'
            });
        }

        function verifyPrescription() {
            Swal.fire({
                title: 'Verify Prescription',
                html: '<textarea id="notes" class="swal2-textarea" placeholder="Add verification notes (optional)"></textarea>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Verify',
                confirmButtonColor: '#10b981',
                preConfirm: () => {
                    return document.getElementById('notes').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/verify_prescription.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `prescription_id=<?php echo $prescription_id; ?>&action=verify&notes=${encodeURIComponent(result.value)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }

        function rejectPrescription() {
            Swal.fire({
                title: 'Reject Prescription',
                html: '<textarea id="reason" class="swal2-textarea" placeholder="Enter reason for rejection (required)" required></textarea>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#ef4444',
                preConfirm: () => {
                    const reason = document.getElementById('reason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please enter a reason');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/verify_prescription.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `prescription_id=<?php echo $prescription_id; ?>&action=reject&notes=${encodeURIComponent(result.value)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Rejected', data.message, 'success')
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
