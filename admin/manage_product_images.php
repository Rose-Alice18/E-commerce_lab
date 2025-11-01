<?php
/**
 * Manage Product Images Page
 * EXTRA CREDIT Feature: Bulk Image Upload
 *
 * Allows pharmacy admins to upload multiple images for their products
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');

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

// Get product ID from query string
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$product_id) {
    header("Location: my_products.php");
    exit();
}

// Get product details
$product_result = get_product_ctr($product_id, $user_id);
if (!$product_result['success']) {
    header("Location: my_products.php");
    exit();
}

$product = $product_result['data'];

// Get existing images
$images_result = get_product_images_ctr($product_id);
$existing_images = $images_result['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Product Images - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
            --danger-color: #ef4444;
        }

        body {
            background: #f8fafc;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .page-header h1 {
            color: #1e293b;
            margin: 0 0 0.5rem 0;
            font-size: 1.75rem;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .product-info img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-details h3 {
            color: #1e293b;
            margin: 0;
            font-size: 1.1rem;
        }

        .product-details p {
            color: #64748b;
            margin: 0.25rem 0 0 0;
        }

        /* Bulk Upload Area */
        .upload-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .upload-section h2 {
            color: #1e293b;
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .upload-area {
            border: 3px dashed #cbd5e1;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
            background: #f0fdf4;
        }

        .upload-area.dragging {
            border-color: var(--primary-color);
            background: #d1fae5;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .upload-area:hover .upload-icon,
        .upload-area.dragging .upload-icon {
            color: var(--primary-color);
        }

        .upload-text h3 {
            color: #1e293b;
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .upload-text p {
            color: #64748b;
            margin: 0 0 1rem 0;
        }

        .upload-limits {
            font-size: 0.875rem;
            color: #94a3b8;
        }

        .upload-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        /* File Preview */
        .preview-section {
            margin-top: 2rem;
            display: none;
        }

        .preview-section.active {
            display: block;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            background: #f1f5f9;
            aspect-ratio: 1;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-remove {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .preview-remove:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .preview-filename {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.5rem;
            font-size: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Existing Images */
        .existing-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .existing-section h2 {
            color: #1e293b;
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .image-card {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
        }

        .image-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .image-info {
            padding: 1rem;
        }

        .primary-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .image-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .btn-set-primary,
        .btn-delete-image {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-set-primary {
            background: #e0e7ff;
            color: #3b82f6;
        }

        .btn-set-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-delete-image {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-delete-image:hover {
            background: #ef4444;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            text-align: center;
        }

        .spinner {
            border: 4px solid #e2e8f0;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('../view/components/sidebar_pharmacy_admin.php'); ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h1><i class="fas fa-images"></i> Manage Product Images</h1>
                    <p style="color: #64748b; margin: 0;">Upload multiple images for your product</p>
                </div>
                <a href="my_products.php" style="background: #e2e8f0; color: #1e293b; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="product-info">
                <?php if ($product['product_image']): ?>
                    <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" alt="Product">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="font-size: 2rem; color: #cbd5e1;"></i>
                    </div>
                <?php endif; ?>
                <div class="product-details">
                    <h3><?php echo htmlspecialchars($product['product_title']); ?></h3>
                    <p>GH₵ <?php echo number_format($product['product_price'], 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Section -->
        <div class="upload-section">
            <h2><i class="fas fa-cloud-upload-alt"></i> Bulk Upload Images</h2>

            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div class="upload-text">
                    <h3>Drag & Drop Images Here</h3>
                    <p>or click to browse files</p>
                    <div class="upload-limits">
                        <i class="fas fa-info-circle"></i> Maximum 10 images | 5MB per file | JPG, JPEG, PNG only<br>
                        <strong style="color: #10b981; margin-top: 0.5rem; display: inline-block;">
                            <i class="fas fa-lightbulb"></i> Tip: Hold <kbd style="background: white; padding: 0.2rem 0.4rem; border-radius: 4px; border: 1px solid #cbd5e1;">Ctrl</kbd> (Windows) or <kbd style="background: white; padding: 0.2rem 0.4rem; border-radius: 4px; border: 1px solid #cbd5e1;">Cmd</kbd> (Mac) and click to select multiple images
                        </strong>
                    </div>
                </div>
                <input
                    type="file"
                    id="fileInput"
                    name="product_images[]"
                    multiple="multiple"
                    accept="image/png,image/jpeg,image/jpg"
                    style="display: none;"
                >
                <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-folder-open"></i> Choose Files
                </button>
            </div>

            <!-- Preview Section -->
            <div class="preview-section" id="previewSection">
                <h3 style="color: #1e293b; margin: 0 0 1rem 0;">
                    <i class="fas fa-eye"></i> Selected Images (<span id="fileCount">0</span>)
                </h3>
                <div class="preview-grid" id="previewGrid"></div>
                <button type="button" class="upload-btn" onclick="uploadImages()" style="margin-top: 1.5rem;">
                    <i class="fas fa-upload"></i> Upload <span id="fileCount2">0</span> Image(s)
                </button>
            </div>
        </div>

        <!-- Existing Images Section -->
        <div class="existing-section">
            <h2>
                <i class="fas fa-images"></i> Existing Images
                <span style="font-size: 1rem; color: #64748b; font-weight: normal;">
                    (<?php echo count($existing_images); ?> image<?php echo count($existing_images) !== 1 ? 's' : ''; ?>)
                </span>
            </h2>

            <?php if (empty($existing_images)): ?>
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <p>No images uploaded yet. Use the bulk upload feature above to add images.</p>
                </div>
            <?php else: ?>
                <div class="image-grid" id="imageGrid">
                    <?php foreach ($existing_images as $image): ?>
                        <div class="image-card" data-image-id="<?php echo $image['image_id']; ?>">
                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Product Image">
                            <div class="image-info">
                                <?php if ($image['is_primary']): ?>
                                    <span class="primary-badge">
                                        <i class="fas fa-star"></i> Primary Image
                                    </span>
                                <?php endif; ?>
                                <div class="image-actions">
                                    <?php if (!$image['is_primary']): ?>
                                        <button class="btn-set-primary" onclick="setPrimaryImage(<?php echo $image['image_id']; ?>, <?php echo $product_id; ?>)">
                                            <i class="fas fa-star"></i> Set Primary
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete-image" onclick="deleteImage(<?php echo $image['image_id']; ?>, '<?php echo addslashes($image['image_path']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p style="color: #1e293b; font-weight: 600; margin: 0;">Uploading images...</p>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const productId = <?php echo $product_id; ?>;
        let selectedFiles = [];

        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const previewSection = document.getElementById('previewSection');
        const previewGrid = document.getElementById('previewGrid');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('dragging');
        }

        function unhighlight() {
            uploadArea.classList.remove('dragging');
        }

        // Handle dropped files
        uploadArea.addEventListener('drop', handleDrop, false);
        uploadArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFiles);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({target: {files: files}});
        }

        function handleFiles(e) {
            const files = Array.from(e.target.files);

            // Debug: Show how many files were selected
            console.log(`Selected ${files.length} file(s)`);

            // If no files selected, just return
            if (files.length === 0) {
                return;
            }

            // Validate file count
            if (files.length > 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Too Many Files',
                    text: 'Maximum 10 images allowed per upload'
                });
                return;
            }

            // Validate each file
            const validFiles = [];
            for (const file of files) {
                // Check file type
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: `${file.name} is not a valid image type. Only JPG, JPEG, PNG allowed.`
                    });
                    continue;
                }

                // Check file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: `${file.name} exceeds 5MB limit`
                    });
                    continue;
                }

                validFiles.push(file);
            }

            if (validFiles.length === 0) {
                return;
            }

            selectedFiles = validFiles;
            displayPreviews();
        }

        function displayPreviews() {
            previewGrid.innerHTML = '';
            document.getElementById('fileCount').textContent = selectedFiles.length;
            document.getElementById('fileCount2').textContent = selectedFiles.length;

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.createElement('div');
                    preview.className = 'preview-item';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}">
                        <button class="preview-remove" onclick="removeFile(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="preview-filename">${file.name}</div>
                    `;
                    previewGrid.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });

            previewSection.classList.add('active');
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            if (selectedFiles.length === 0) {
                previewSection.classList.remove('active');
                fileInput.value = '';
            } else {
                displayPreviews();
            }
        }

        async function uploadImages() {
            if (selectedFiles.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Files Selected',
                    text: 'Please select images to upload'
                });
                return;
            }

            const formData = new FormData();
            formData.append('product_id', productId);

            selectedFiles.forEach((file) => {
                formData.append('product_images[]', file);
            });

            // Show loading
            document.getElementById('loadingOverlay').classList.add('active');

            try {
                const response = await fetch('../actions/bulk_upload_images_action.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                // Hide loading
                document.getElementById('loadingOverlay').classList.remove('active');

                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonColor: '#10b981'
                    });

                    // Reload page to show new images
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: result.message
                    });
                }
            } catch (error) {
                document.getElementById('loadingOverlay').classList.remove('active');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during upload'
                });
                console.error('Upload error:', error);
            }
        }

        async function deleteImage(imageId, imagePath) {
            const result = await Swal.fire({
                title: 'Delete Image?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../actions/delete_product_image_action.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({image_id: imageId, image_path: imagePath})
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            confirmButtonColor: '#10b981'
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete image'
                    });
                }
            }
        }

        async function setPrimaryImage(imageId, productId) {
            try {
                const response = await fetch('../actions/set_primary_image_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({image_id: imageId, product_id: productId})
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to set primary image'
                });
            }
        }
    </script>
</body>
</html>
