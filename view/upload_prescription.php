<?php
/**
 * Prescription Upload Page - Enhanced Version
 * Sleek, modern design with custom popups and animations
 */
session_start();
require_once('../settings/core.php');

// Check if user is logged in as customer
if (!isLoggedIn() || !isRegularCustomer()) {
    header("Location: ../login/login_view.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Prescription - PharmaVault</title>
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

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-dark);
            transform: translateX(-5px);
        }

        .upload-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 3rem;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .upload-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .upload-header h1 {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .upload-area {
            border: 3px dashed #cbd5e1;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #f8f9ff;
        }

        .upload-area.dragover {
            border-color: var(--primary);
            background: #f0f3ff;
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .upload-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .file-input {
            display: none;
        }

        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .preview-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .preview-item:hover {
            transform: translateY(-5px);
        }

        .preview-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .preview-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .preview-remove:hover {
            background: #dc2626;
            transform: scale(1.1) rotate(90deg);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            transition: all 0.3s;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-submit.uploading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            to {
                left: 100%;
            }
        }

        .file-count {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Modal/Popup */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .custom-modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .custom-modal.show .modal-content {
            transform: scale(1);
        }

        .modal-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .modal-icon.success {
            color: var(--success);
            animation: scaleIn 0.5s ease;
        }

        .modal-icon.error {
            color: var(--danger);
            animation: shake 0.5s ease;
        }

        .modal-icon.warning {
            color: var(--warning);
            animation: swing 0.5s ease;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            text-align: center;
        }

        .modal-message {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            line-height: 1.8;
            white-space: pre-line;
        }

        .modal-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        /* Progress Bar */
        .progress-bar-container {
            display: none;
            margin-top: 1rem;
        }

        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            width: 0%;
            transition: width 0.3s ease;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .upload-container { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_customer.php'); ?>

    <div class="main-content">
        <!-- Back Button -->
        <a href="../admin/customer_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="upload-container">
            <div class="upload-header">
                <h1><i class="fas fa-file-medical"></i> Upload Prescription</h1>
                <p class="text-muted">Upload clear photos of your prescription (front and back)</p>
            </div>

            <form id="prescriptionForm" enctype="multipart/form-data">
                <!-- Drag & Drop Upload Area -->
                <div class="upload-area" id="uploadArea">
                    <input type="file"
                           id="fileInput"
                           name="prescription_images[]"
                           class="file-input"
                           accept="image/*,.pdf"
                           multiple>
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Drag & Drop Images Here</h3>
                    <p class="text-muted">or click to browse</p>
                    <p class="text-muted"><small>Supports: JPG, PNG, PDF (Max 5MB per file)</small></p>
                </div>

                <div id="fileCount" class="text-center" style="display: none;">
                    <span class="file-count">
                        <i class="fas fa-images"></i> <span id="countText">0 files selected</span>
                    </span>
                </div>

                <!-- Image Preview Grid -->
                <div class="preview-container" id="previewContainer"></div>

                <!-- Optional Notes -->
                <div class="prescription-form" style="margin-top: 2rem;">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Additional Notes (Optional)
                        </label>
                        <textarea class="form-control"
                                  id="prescriptionNotes"
                                  rows="3"
                                  placeholder="Any special instructions, allergies, or notes you'd like the pharmacist to know..."></textarea>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Note: All prescription details (doctor, date, medications) will be read from your uploaded images by the pharmacist.
                        </small>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress-bar-container" id="progressContainer">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <p class="text-center text-muted mt-2">
                            <i class="fas fa-spinner fa-spin"></i> Uploading...
                        </p>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <div style="background: #f8f9fa; padding: 1.25rem; border-radius: 12px; border-left: 4px solid var(--primary);">
                            <label class="form-label mb-3" style="font-weight: 600; color: #1e293b;">
                                <i class="fas fa-shield-alt"></i> Privacy Settings
                            </label>
                            <div class="form-check" style="display: flex; align-items: center; gap: 0.75rem;">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="allowPharmacyAccess"
                                       name="allow_pharmacy_access"
                                       checked
                                       style="width: 20px; height: 20px; cursor: pointer;">
                                <label class="form-check-label" for="allowPharmacyAccess" style="cursor: pointer; flex: 1;">
                                    <strong>Allow pharmacies to view this prescription</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Pharmacies can use your prescription to recommend products and fulfill orders.
                                        Superadmin can always view for verification purposes.
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Modal -->
    <div class="custom-modal" id="customModal">
        <div class="modal-content">
            <div class="modal-icon" id="modalIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="modal-title" id="modalTitle">Success!</h3>
            <p class="modal-message" id="modalMessage">Your prescription has been uploaded successfully.</p>
            <button class="modal-btn" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
        let selectedFiles = [];
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        const fileCountDiv = document.getElementById('fileCount');
        const countText = document.getElementById('countText');
        const submitBtn = document.getElementById('submitBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');

        // Click to upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // Drag & Drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // File selection
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!validTypes.includes(file.type)) {
                    showModal('warning', 'Invalid File Type', `${file.name} is not a valid file. Please upload JPG, PNG, or PDF files only.`);
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showModal('error', 'File Too Large', `${file.name} exceeds 5MB. Please choose a smaller file.`);
                    return;
                }

                selectedFiles.push(file);
                previewFile(file);
            });

            updateFileCount();
        }

        function previewFile(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';

                if (file.type === 'application/pdf') {
                    previewItem.innerHTML = `
                        <div style="background: #f1f5f9; height: 200px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                            <i class="fas fa-file-pdf" style="font-size: 3rem; color: #ef4444;"></i>
                            <p style="margin-top: 1rem; font-weight: 600;">${file.name}</p>
                        </div>
                        <button type="button" class="preview-remove" onclick="removeFile('${file.name}')">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                } else {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="preview-img">
                        <button type="button" class="preview-remove" onclick="removeFile('${file.name}')">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                }

                previewContainer.appendChild(previewItem);
            };

            reader.readAsDataURL(file);
        }

        function removeFile(fileName) {
            selectedFiles = selectedFiles.filter(f => f.name !== fileName);
            renderPreviews();
            updateFileCount();
        }

        function renderPreviews() {
            previewContainer.innerHTML = '';
            selectedFiles.forEach(file => previewFile(file));
        }

        function updateFileCount() {
            if (selectedFiles.length > 0) {
                fileCountDiv.style.display = 'block';
                countText.textContent = `${selectedFiles.length} file${selectedFiles.length > 1 ? 's' : ''} selected`;
            } else {
                fileCountDiv.style.display = 'none';
            }
        }

        // Form submission
        document.getElementById('prescriptionForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            if (selectedFiles.length === 0) {
                showModal('warning', 'No Files Selected', 'Please select at least one prescription image to upload.');
                return;
            }

            const formData = new FormData();
            selectedFiles.forEach(file => {
                formData.append('prescription_images[]', file);
            });

            const notes = document.getElementById('prescriptionNotes').value;
            if (notes) {
                formData.append('prescription_notes', notes);
            }

            // Add privacy setting
            const allowPharmacyAccess = document.getElementById('allowPharmacyAccess').checked ? 1 : 0;
            formData.append('allow_pharmacy_access', allowPharmacyAccess);

            // Show progress
            submitBtn.disabled = true;
            submitBtn.classList.add('uploading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            progressContainer.style.display = 'block';
            progressFill.style.width = '70%';

            try {
                const response = await fetch('../actions/upload_prescription_action.php', {
                    method: 'POST',
                    body: formData
                });

                progressFill.style.width = '100%';

                // Get response text first to debug
                const responseText = await response.text();
                console.log('Response:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response was:', responseText);
                    throw new Error('Invalid response from server: ' + responseText.substring(0, 200));
                }

                // Hide progress
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('uploading');
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Prescription';

                    if (data.success) {
                        // Clear form
                        selectedFiles = [];
                        previewContainer.innerHTML = '';
                        document.getElementById('prescriptionNotes').value = '';
                        fileCountDiv.style.display = 'none';

                        showModal('success', 'Prescription Uploaded Successfully!',
                            `Your prescription has been submitted successfully and is now under review.\n\nReference Number: ${data.prescription_number || 'N/A'}\n\nYou uploaded ${data.image_count || 1} image(s). Our pharmacist will review your prescription shortly and contact you if needed.`,
                            () => {
                                window.location.href = '../view/my_prescriptions.php';
                            }
                        );
                    } else {
                        showModal('error', 'Upload Failed', data.message || 'An error occurred. Please try again.');
                    }
                }, 500);

            } catch (error) {
                console.error('Upload error:', error);
                progressContainer.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.classList.remove('uploading');
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Prescription';
                showModal('error', 'Upload Failed', error.message || 'A network error occurred. Please check your connection and try again.');
            }
        });

        // Custom Modal Functions
        function showModal(type, title, message, callback = null) {
            const modal = document.getElementById('customModal');
            const icon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');

            // Set icon based on type
            icon.className = 'modal-icon ' + type;
            if (type === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle"></i>';
            } else if (type === 'error') {
                icon.innerHTML = '<i class="fas fa-times-circle"></i>';
            } else if (type === 'warning') {
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            }

            modalTitle.textContent = title;
            modalMessage.textContent = message;

            modal.classList.add('show');

            // Store callback
            if (callback) {
                window.modalCallback = callback;
            } else {
                window.modalCallback = null;
            }
        }

        function closeModal() {
            const modal = document.getElementById('customModal');
            modal.classList.remove('show');

            if (window.modalCallback) {
                window.modalCallback();
                window.modalCallback = null;
            }
        }

        // Close modal on outside click
        document.getElementById('customModal').addEventListener('click', (e) => {
            if (e.target.id === 'customModal') {
                closeModal();
            }
        });
    </script>
</body>
</html>
