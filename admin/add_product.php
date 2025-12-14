<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');

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

// Get ALL categories and brands (should be shared across platform)
// Note: According to PDF requirements, categories and brands should be platform-wide
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');

$category_obj = new Category();
$brand_obj = new Brand();

// Get all categories (not filtered by user)
$categories = $category_obj->fetch_all_categories();

// Get all brands (not filtered by user) - pass null to get all brands
$brands = $brand_obj->get_all_brands(null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            max-width: 800px;
        }

        .form-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-header h2 {
            color: #1e293b;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .form-header p {
            color: #64748b;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .form-text {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .image-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-upload-area:hover {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .image-upload-area.active {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 1rem auto;
            display: none;
            border-radius: 10px;
        }

        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        /* Multiple Image Preview Grid */
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
        }

        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .preview-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .preview-item .remove-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .preview-item .primary-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #10b981;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .preview-item .image-name {
            padding: 0.5rem;
            font-size: 0.75rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
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

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                <p>Fill in the details below to add a new product to your inventory</p>
            </div>

            <form id="addProductForm" enctype="multipart/form-data">
                <!-- Product Title -->
                <div class="form-group">
                    <label class="form-label">
                        Product Name <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="productTitle"
                        name="product_title"
                        placeholder="e.g., Panadol Extra 500mg"
                        required
                    >
                    <small class="form-text">Enter the full product name including dosage if applicable</small>
                </div>

                <!-- Category and Brand -->
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">
                            Category <span class="required">*</span>
                        </label>
                        <select class="form-control" id="productCategory" name="product_cat" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>">
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Choose the product category</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Brand <span class="required">*</span>
                        </label>
                        <select class="form-control" id="productBrand" name="product_brand" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>">
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Choose the product brand</small>
                    </div>
                </div>

                <!-- Price and Stock -->
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">
                            Price (GHS) <span class="required">*</span>
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="productPrice"
                            name="product_price"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            required
                        >
                        <small class="form-text">Enter price in Ghana Cedis</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Stock Quantity <span class="required">*</span>
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="productStock"
                            name="product_stock"
                            placeholder="0"
                            min="0"
                            required
                        >
                        <small class="form-text">Available quantity in stock</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">
                        Product Description
                    </label>
                    <textarea
                        class="form-control"
                        id="productDesc"
                        name="product_desc"
                        rows="4"
                        placeholder="Describe the product, its uses, and any important information..."
                    ></textarea>
                    <small class="form-text">Optional: Provide detailed product information</small>
                </div>

                <!-- Keywords -->
                <div class="form-group">
                    <label class="form-label">
                        Keywords
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="productKeywords"
                        name="product_keywords"
                        placeholder="pain relief, headache, fever"
                    >
                    <small class="form-text">Comma-separated keywords for better search results</small>
                </div>

                <!-- Prescription Required -->
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f8fafc; border-radius: 10px;">
                        <input
                            type="checkbox"
                            id="prescriptionRequired"
                            name="prescription_required"
                            value="1"
                            style="width: 20px; height: 20px; cursor: pointer;"
                        >
                        <label for="prescriptionRequired" style="margin: 0; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-prescription" style="color: #ef4444; margin-right: 0.5rem;"></i>
                            This product requires a prescription
                        </label>
                    </div>
                    <small class="form-text">Check this if the product requires a valid prescription to purchase</small>
                </div>

                <!-- Multiple Image Upload -->
                <div class="form-group">
                    <label class="form-label">
                        Product Images <span class="required">*</span>
                    </label>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
                        Upload multiple product images. The first image will be the primary image.
                    </p>
                    <div class="image-upload-area" id="imageUploadArea" onclick="document.getElementById('productImages').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <p style="margin: 0; color: #64748b;"><strong>Click to upload product images</strong></p>
                        <small style="color: #94a3b8;">Select multiple images - PNG, JPG, JPEG (Max 5MB each)</small>
                    </div>
                    <input
                        type="file"
                        id="productImages"
                        name="product_images[]"
                        accept="image/png, image/jpeg, image/jpg"
                        style="display: none;"
                        multiple
                        onchange="previewMultipleImages(this)"
                        required
                    >
                    <div id="imagePreviewContainer" class="image-preview-grid" style="display: none;">
                        <!-- Image previews will be inserted here -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Product
                    </button>
                    <a href="my_products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <!-- Product JS (PDF Requirement - Week 6 Activity Part 2) -->
    <script src="../js/product.js"></script>

    <script>
        let selectedFiles = [];

        function previewMultipleImages(input) {
            const uploadArea = document.getElementById('imageUploadArea');
            const previewContainer = document.getElementById('imagePreviewContainer');

            if (input.files && input.files.length > 0) {
                // Store selected files
                selectedFiles = Array.from(input.files);

                // Clear preview container
                previewContainer.innerHTML = '';
                previewContainer.style.display = 'grid';
                uploadArea.classList.add('active');

                // Preview each image
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            ${index === 0 ? '<span class="primary-badge">PRIMARY</span>' : ''}
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="remove-btn" onclick="removeImage(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="image-name">${file.name}</div>
                        `;
                        previewContainer.appendChild(previewItem);
                    };

                    reader.readAsDataURL(file);
                });
            }
        }

        function removeImage(index) {
            // Remove file from array
            selectedFiles.splice(index, 1);

            // Update file input
            const input = document.getElementById('productImages');
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;

            // Re-render previews
            if (selectedFiles.length > 0) {
                previewMultipleImages(input);
            } else {
                // Hide preview if no files
                const previewContainer = document.getElementById('imagePreviewContainer');
                const uploadArea = document.getElementById('imageUploadArea');
                previewContainer.style.display = 'none';
                previewContainer.innerHTML = '';
                uploadArea.classList.remove('active');
            }
        }

        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('pharmacy_id', <?php echo $user_id; ?>);

            // Show loading
            Swal.fire({
                title: 'Adding Product...',
                html: 'Please wait while we add your product',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../actions/add_product_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location.href = 'my_products.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the product',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</body>
</html>
