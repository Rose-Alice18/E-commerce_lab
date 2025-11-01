<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is PHARMACY ADMIN (role 1 only)
if (!isPharmacyAdmin()) {
    header("Location: ../index.php");
    exit();
}

$user_id = getUserId();
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload Products - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .upload-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            max-width: 900px;
        }

        .upload-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .upload-header h2 {
            color: #1e293b;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .upload-header p {
            color: #64748b;
            margin: 0;
        }

        .upload-area {
            border: 3px dashed #cbd5e1;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .upload-area:hover {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .upload-area.dragover {
            border-color: #10b981;
            background: #f0fdf4;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }

        .info-box h4 {
            color: #1e40af;
            margin: 0 0 0.5rem 0;
        }

        .info-box ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .sample-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }

        .sample-table th,
        .sample-table td {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .sample-table th {
            background: #f1f5f9;
            font-weight: 600;
            color: #1e293b;
        }

        .sample-table td {
            color: #64748b;
        }

        #uploadProgress {
            display: none;
            margin-top: 2rem;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #10b981, #059669);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .results-container {
            margin-top: 2rem;
            display: none;
        }

        .result-item {
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .result-item.success {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
        }

        .result-item.error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div style="margin-bottom: 1rem;">
            <a href="my_products.php" style="color: #10b981; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Back to My Products
            </a>
        </div>

        <div class="upload-container">
            <div class="upload-header">
                <h2><i class="fas fa-file-upload"></i> Bulk Upload Products</h2>
                <p>Upload multiple products at once using a CSV file</p>
            </div>

            <!-- Instructions -->
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> How to Use Bulk Upload</h4>
                <ol>
                    <li>Download the sample CSV template below</li>
                    <li>Fill in your product data following the format</li>
                    <li>Save the file as CSV (UTF-8)</li>
                    <li>Upload the file using the form below</li>
                </ol>
                <p style="margin-top: 1rem;">
                    <a href="../actions/download_bulk_template.php" class="btn btn-secondary" download>
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </p>
            </div>

            <!-- Sample Format -->
            <h4 style="margin-top: 2rem;">CSV Format Example:</h4>
            <table class="sample-table">
                <thead>
                    <tr>
                        <th>product_title</th>
                        <th>category_name</th>
                        <th>brand_name</th>
                        <th>product_price</th>
                        <th>product_stock</th>
                        <th>product_desc</th>
                        <th>product_keywords</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Panadol Extra 500mg</td>
                        <td>Pain Relief</td>
                        <td>Panadol</td>
                        <td>18.50</td>
                        <td>100</td>
                        <td>Fast pain relief tablets</td>
                        <td>panadol,pain,headache</td>
                    </tr>
                    <tr>
                        <td>Vitamin C 1000mg</td>
                        <td>Vitamins</td>
                        <td>Centrum</td>
                        <td>45.00</td>
                        <td>50</td>
                        <td>Immune system support</td>
                        <td>vitamin,immune,health</td>
                    </tr>
                </tbody>
            </table>

            <!-- Upload Form -->
            <form id="bulkUploadForm" enctype="multipart/form-data" style="margin-top: 2rem;">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <h3 style="color: #1e293b; margin: 0 0 0.5rem 0;">Drag & Drop CSV File Here</h3>
                    <p style="color: #64748b; margin: 0 0 1rem 0;">or click to browse</p>
                    <input type="file" id="csvFile" name="csv_file" accept=".csv" style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('csvFile').click()">
                        <i class="fas fa-folder-open"></i> Choose File
                    </button>
                </div>

                <div id="fileInfo" style="margin-top: 1rem; display: none;">
                    <p style="color: #10b981; font-weight: 600;">
                        <i class="fas fa-file-csv"></i> <span id="fileName"></span>
                    </p>
                </div>

                <div id="uploadProgress">
                    <p style="font-weight: 600; margin-bottom: 0.5rem;">Uploading Products...</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill">0%</div>
                    </div>
                    <p style="margin-top: 0.5rem; color: #64748b;" id="progressText">Processing...</p>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 2rem; width: 100%;">
                    <i class="fas fa-upload"></i> Upload Products
                </button>
            </form>

            <!-- Results Container -->
            <div class="results-container" id="resultsContainer">
                <h3 style="color: #1e293b;">Upload Results</h3>
                <div id="resultsList"></div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const csvFile = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const uploadForm = document.getElementById('bulkUploadForm');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultsList = document.getElementById('resultsList');

        // Drag and drop functionality
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

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].name.endsWith('.csv')) {
                csvFile.files = files;
                fileName.textContent = files[0].name;
                fileInfo.style.display = 'block';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File',
                    text: 'Please upload a CSV file',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        // File input change
        csvFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
                fileInfo.style.display = 'block';
            }
        });

        // Form submission
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (csvFile.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No File Selected',
                    text: 'Please select a CSV file to upload',
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', csvFile.files[0]);
            formData.append('pharmacy_id', <?php echo $user_id; ?>);

            // Show progress
            uploadProgress.style.display = 'block';
            resultsContainer.style.display = 'none';
            progressFill.style.width = '0%';
            progressFill.textContent = '0%';

            try {
                const response = await fetch('../actions/bulk_upload_products_action.php', {
                    method: 'POST',
                    body: formData
                });

                // Simulate progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    if (progress <= 90) {
                        progressFill.style.width = progress + '%';
                        progressFill.textContent = progress + '%';
                    }
                }, 200);

                const data = await response.json();
                clearInterval(interval);

                progressFill.style.width = '100%';
                progressFill.textContent = '100%';

                setTimeout(() => {
                    uploadProgress.style.display = 'none';

                    if (data.success) {
                        // Show results
                        resultsContainer.style.display = 'block';
                        resultsList.innerHTML = '';

                        // Summary
                        const summary = document.createElement('div');
                        summary.style.cssText = 'background: #f0fdf4; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; border-left: 4px solid #10b981;';
                        summary.innerHTML = `
                            <h4 style="color: #10b981; margin: 0 0 0.5rem 0;">
                                <i class="fas fa-check-circle"></i> Upload Complete!
                            </h4>
                            <p style="margin: 0; color: #064e3b;">
                                <strong>${data.successful}</strong> products uploaded successfully,
                                <strong>${data.failed}</strong> failed
                            </p>
                        `;
                        resultsList.appendChild(summary);

                        // Individual results
                        if (data.results && data.results.length > 0) {
                            data.results.forEach(result => {
                                const item = document.createElement('div');
                                item.className = `result-item ${result.success ? 'success' : 'error'}`;
                                item.innerHTML = `
                                    <i class="fas ${result.success ? 'fa-check-circle' : 'fa-exclamation-circle'}"
                                       style="color: ${result.success ? '#10b981' : '#ef4444'}; font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>${result.product_title || 'Unknown Product'}</strong><br>
                                        <span style="color: #64748b; font-size: 0.9rem;">${result.message}</span>
                                    </div>
                                `;
                                resultsList.appendChild(item);
                            });
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Upload Complete!',
                            text: `${data.successful} products uploaded successfully`,
                            confirmButtonColor: '#10b981'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: data.message || 'An error occurred during upload',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }, 500);

            } catch (error) {
                console.error('Error:', error);
                uploadProgress.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while uploading the file',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    </script>
</body>
</html>
