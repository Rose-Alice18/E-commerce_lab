<?php
/**
 * My Prescriptions Page
 * Display customer's prescription history with status tracking
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/prescription_controller.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];

// Get customer's prescriptions
$prescriptions = get_customer_prescriptions_ctr($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        /* Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-dark);
            transform: translateX(-5px);
        }

        .upload-new-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .upload-new-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .prescriptions-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }

        .empty-state-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        /* Prescription Cards */
        .prescription-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .prescription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .prescription-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .prescription-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .prescription-date {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: capitalize;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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

        .status-expired {
            background: #e5e7eb;
            color: #374151;
        }

        /* Prescription Details */
        .prescription-body {
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            gap: 0.5rem;
            padding: 0.75rem 0;
            color: #475569;
        }

        .detail-label {
            font-weight: 600;
            color: #1e293b;
            min-width: 140px;
        }

        /* Image Gallery */
        .prescription-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .prescription-image-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .prescription-image-thumb:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Actions */
        .prescription-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #f1f5f9;
        }

        .btn-action {
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-download {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-download:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-modal.show {
            display: flex;
            opacity: 1;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .image-modal-close {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #1e293b;
        }

        .image-modal-close:hover {
            transform: rotate(90deg);
            background: #ef4444;
            color: white;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .prescription-header { flex-direction: column; gap: 1rem; }
            .prescription-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <!-- Back Button -->
        <a href="../admin/customer_dashboard.php" class="back-btn" >
            <!--i class="fas fa-arrow-left">Back to Dashboard</i--> 
        </a>

        <div class="prescriptions-container">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-file-medical"></i> My Prescriptions</h1>
                    <p class="text-muted">View and track your prescription uploads</p>
                </div>
                <a href="upload_prescription.php" class="upload-new-btn">
                    <i class="fas fa-plus"></i> Upload New Prescription
                </a>
            </div>

            <!-- Prescriptions List -->
            <?php if (empty($prescriptions)): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h3>No Prescriptions Yet</h3>
                    <p class="text-muted">You haven't uploaded any prescriptions yet.</p>
                    <a href="upload_prescription.php" class="upload-new-btn" style="margin-top: 1.5rem;">
                        <i class="fas fa-upload"></i> Upload Your First Prescription
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($prescriptions as $index => $prescription): ?>
                    <div class="prescription-card" style="animation-delay: <?= $index * 0.1 ?>s;">
                        <!-- Card Header -->
                        <div class="prescription-header">
                            <div>
                                <div class="prescription-number">
                                    <i class="fas fa-prescription"></i>
                                    <?= htmlspecialchars($prescription['prescription_number']) ?>
                                </div>
                                <div class="prescription-date">
                                    <i class="fas fa-clock"></i>
                                    Uploaded: <?= date('F j, Y - g:i A', strtotime($prescription['uploaded_at'])) ?>
                                </div>
                            </div>
                            <div>
                                <?php
                                $status = $prescription['status'];
                                $status_class = 'status-' . $status;
                                $status_icon = [
                                    'pending' => 'fa-hourglass-half',
                                    'verified' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle',
                                    'expired' => 'fa-calendar-times'
                                ][$status] ?? 'fa-question-circle';
                                ?>
                                <span class="status-badge <?= $status_class ?>">
                                    <i class="fas <?= $status_icon ?>"></i>
                                    <?= ucfirst($status) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="prescription-body">
                            <?php if ($prescription['doctor_name']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-user-md"></i> Doctor:</span>
                                    <span><?= htmlspecialchars($prescription['doctor_name']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prescription['issue_date']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-calendar-alt"></i> Issue Date:</span>
                                    <span><?= date('F j, Y', strtotime($prescription['issue_date'])) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prescription['expiry_date']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-calendar-times"></i> Expiry Date:</span>
                                    <span><?= date('F j, Y', strtotime($prescription['expiry_date'])) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prescription['verified_at']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-check"></i> Verified On:</span>
                                    <span><?= date('F j, Y - g:i A', strtotime($prescription['verified_at'])) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prescription['rejection_reason']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-exclamation-circle"></i> Rejection Reason:</span>
                                    <span style="color: var(--danger);"><?= htmlspecialchars($prescription['rejection_reason']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($prescription['prescription_notes']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-sticky-note"></i> Notes:</span>
                                    <span><?= nl2br(htmlspecialchars($prescription['prescription_notes'])) ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Privacy Setting Display -->
                            <div class="detail-row" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                <span class="detail-label"><i class="fas fa-shield-alt"></i> Privacy:</span>
                                <?php if (isset($prescription['allow_pharmacy_access']) && $prescription['allow_pharmacy_access'] == 0): ?>
                                    <span style="color: var(--danger); font-weight: 600;">
                                        <i class="fas fa-lock"></i> Private (Only you can view)
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--success); font-weight: 600;">
                                        <i class="fas fa-unlock"></i> Public (Pharmacies can view)
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Prescription Images -->
                            <?php if ($prescription['prescription_image']): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-images"></i> Images:</span>
                                </div>
                                <div class="prescription-images">
                                    <?php
                                    // Get all images for this prescription
                                    require_once('../settings/db_class.php');
                                    $db = new db_connection();
                                    $conn = $db->db_conn();
                                    $stmt = $conn->prepare("SELECT * FROM prescription_images WHERE prescription_id = ? ORDER BY is_primary DESC, image_id");
                                    $stmt->bind_param("i", $prescription['prescription_id']);
                                    $stmt->execute();
                                    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                                    if (empty($images)):
                                        // Fallback to main prescription image
                                        $images = [['image_path' => $prescription['prescription_image'], 'is_primary' => 1]];
                                    endif;

                                    foreach ($images as $image):
                                    ?>
                                        <img src="../<?= htmlspecialchars($image['image_path']) ?>"
                                             alt="Prescription Image"
                                             class="prescription-image-thumb"
                                             onclick="showImageModal('../<?= htmlspecialchars($image['image_path']) ?>')">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Actions -->
                        <div class="prescription-actions">
                            <a href="prescription_details.php?id=<?= $prescription['prescription_id'] ?>" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <?php if ($prescription['prescription_image']): ?>
                                <a href="../<?= htmlspecialchars($prescription['prescription_image']) ?>" download class="btn-action btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal">
        <button class="image-modal-close" onclick="closeImageModal()">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="" alt="Prescription">
    </div>

    <script>
        function showImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modal.classList.add('show');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('show');
        }

        // Close modal on click outside
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
