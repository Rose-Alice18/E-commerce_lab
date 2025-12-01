<?php
/**
 * Prescription Details Page
 * View full prescription information and edit privacy settings
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/prescription_controller.php');

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header("Location: ../login/login_view.php");
    exit();
}

// Get prescription ID from URL
if (!isset($_GET['id'])) {
    header("Location: my_prescriptions.php");
    exit();
}

$prescription_id = intval($_GET['id']);
$customer_id = $_SESSION['user_id'];

// Fetch prescription details
$prescription = get_prescription_ctr($prescription_id);

// Verify that prescription belongs to this customer
if (!$prescription || $prescription['customer_id'] != $customer_id) {
    header("Location: my_prescriptions.php");
    exit();
}

// Fetch prescription items
$items = get_prescription_items_ctr($prescription_id);

// Get prescription history
$history = get_prescription_history_ctr($prescription_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Details - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        .details-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .header-section {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .prescription-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
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
            background: #f3f4f6;
            color: #6b7280;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 500;
        }

        .privacy-toggle-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #38bdf8;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .privacy-toggle-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .privacy-info {
            flex: 1;
        }

        .privacy-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0c4a6e;
            margin-bottom: 0.5rem;
        }

        .privacy-description {
            font-size: 0.875rem;
            color: #075985;
            line-height: 1.5;
        }

        .privacy-toggle-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: 0.4s;
            border-radius: 30px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .toggle-slider {
            background-color: var(--success);
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }

        .privacy-status {
            font-weight: 700;
            font-size: 1rem;
        }

        .privacy-public {
            color: var(--success);
        }

        .privacy-private {
            color: var(--danger);
        }

        .images-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .prescription-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .image-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .image-card:hover {
            transform: scale(1.05);
        }

        .image-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            cursor: pointer;
        }

        .items-section {
            margin-bottom: 2rem;
        }

        .items-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .items-table table {
            width: 100%;
            margin-bottom: 0;
        }

        .items-table thead {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .items-table th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
        }

        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-back {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(107, 114, 128, 0.3);
            color: white;
        }

        .history-section {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .history-item {
            padding: 1rem;
            background: white;
            border-left: 3px solid var(--primary);
            border-radius: 5px;
            margin-bottom: 0.75rem;
        }

        .history-action {
            font-weight: 600;
            color: var(--primary);
        }

        .history-date {
            font-size: 0.75rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .privacy-toggle-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .prescription-images {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('components/sidebar_customer.php'); ?>

    <div class="main-content">
        <!-- Header Section -->
        <div class="details-container">
            <div class="header-section">
                <div class="prescription-number">
                    <i class="fas fa-file-medical"></i> <?php echo htmlspecialchars($prescription['prescription_number']); ?>
                </div>
                <span class="status-badge status-<?php echo strtolower($prescription['status']); ?>">
                    <?php echo ucfirst($prescription['status']); ?>
                </span>
            </div>

            <!-- Privacy Toggle Section -->
            <div class="privacy-toggle-section">
                <div class="privacy-toggle-header">
                    <div class="privacy-info">
                        <div class="privacy-title">
                            <i class="fas fa-shield-alt"></i> Privacy Settings
                        </div>
                        <div class="privacy-description">
                            <strong>Public:</strong> Pharmacies can view this prescription and offer you quotes.<br>
                            <strong>Private:</strong> Only you can see this prescription. Pharmacies cannot access it.
                        </div>
                    </div>
                    <div class="privacy-toggle-control">
                        <span class="privacy-status <?php echo $prescription['allow_pharmacy_access'] == 1 ? 'privacy-public' : 'privacy-private'; ?>" id="privacyStatus">
                            <?php echo $prescription['allow_pharmacy_access'] == 1 ? 'Public' : 'Private'; ?>
                        </span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="privacyToggle" <?php echo $prescription['allow_pharmacy_access'] == 1 ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Prescription Information -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user-md"></i> Doctor Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_name'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-id-card"></i> Doctor License</div>
                    <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_license'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar"></i> Issue Date</div>
                    <div class="info-value"><?php echo $prescription['issue_date'] ? date('M d, Y', strtotime($prescription['issue_date'])) : 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar-times"></i> Expiry Date</div>
                    <div class="info-value"><?php echo $prescription['expiry_date'] ? date('M d, Y', strtotime($prescription['expiry_date'])) : 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-clock"></i> Uploaded</div>
                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($prescription['uploaded_at'])); ?></div>
                </div>
                <?php if ($prescription['verified_at']): ?>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-check-circle"></i> Verified</div>
                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($prescription['verified_at'])); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($prescription['prescription_notes']): ?>
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label"><i class="fas fa-notes-medical"></i> Notes</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($prescription['prescription_notes'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Prescription Images -->
        <div class="details-container images-section">
            <div class="section-title">
                <i class="fas fa-images"></i> Prescription Images
            </div>
            <div class="prescription-images">
                <?php
                // Parse prescription images (stored as comma-separated)
                $images = explode(',', $prescription['prescription_image']);
                foreach ($images as $image):
                    $image = trim($image);
                    if ($image):
                ?>
                    <div class="image-card">
                        <img src="../<?php echo htmlspecialchars($image); ?>" alt="Prescription Image" onclick="viewImage(this.src)">
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>

        <!-- Prescription Items -->
        <?php if (!empty($items)): ?>
        <div class="details-container items-section">
            <div class="section-title">
                <i class="fas fa-pills"></i> Medications
            </div>
            <div class="items-table">
                <table>
                    <thead>
                        <tr>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Quantity</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['medication_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($item['dosage']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['frequency']); ?></td>
                            <td><?php echo htmlspecialchars($item['duration']); ?></td>
                            <td>
                                <?php if ($item['is_fulfilled']): ?>
                                    <span class="status-badge status-verified">Fulfilled</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- History -->
        <?php if (!empty($history)): ?>
        <div class="details-container">
            <div class="section-title">
                <i class="fas fa-history"></i> Activity History
            </div>
            <div class="history-section">
                <?php foreach ($history as $log): ?>
                <div class="history-item">
                    <div class="history-action">
                        <?php echo ucfirst(htmlspecialchars($log['action'])); ?>
                    </div>
                    <div class="history-date">
                        <?php echo date('M d, Y h:i A', strtotime($log['action_date'])); ?>
                    </div>
                    <?php if ($log['action_notes']): ?>
                    <div style="margin-top: 0.5rem; font-size: 0.875rem; color: #64748b;">
                        <?php echo htmlspecialchars($log['action_notes']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="my_prescriptions.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to My Prescriptions
            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 10;"></button>
                    <img src="" id="modalImage" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script>
        // View image in modal
        function viewImage(src) {
            document.getElementById('modalImage').src = src;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Privacy toggle functionality
        document.getElementById('privacyToggle').addEventListener('change', async function() {
            const isPublic = this.checked;
            const prescriptionId = <?php echo $prescription_id; ?>;
            const statusEl = document.getElementById('privacyStatus');

            try {
                const response = await fetch('../actions/update_prescription_privacy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        prescription_id: prescriptionId,
                        allow_pharmacy_access: isPublic ? 1 : 0
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Update status text and color
                    statusEl.textContent = isPublic ? 'Public' : 'Private';
                    statusEl.className = 'privacy-status ' + (isPublic ? 'privacy-public' : 'privacy-private');

                    // Show success message
                    showNotification('Privacy settings updated successfully!', 'success');
                } else {
                    // Revert toggle on error
                    this.checked = !isPublic;
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                // Revert toggle on error
                this.checked = !isPublic;
                showNotification('An error occurred. Please try again.', 'error');
            }
        });

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 9999;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</body>:
<?php
// Include chatbot for customer support
include 'components/chatbot.php';
?>

</html>
