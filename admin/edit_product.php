<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions and controllers
require_once('../settings/core.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');
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

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$product_result = get_product_ctr($product_id, $user_id);
if (!$product_result['success']) {
    header("Location: my_products.php");
    exit();
}
$product = $product_result['data'];

// Get ALL categories and brands (platform-wide)
require_once('../classes/category_class.php');
require_once('../classes/brand_class.php');

$category_obj = new Category();
$brand_obj = new Brand();

$categories = $category_obj->fetch_all_categories();
$brands = $brand_obj->get_all_brands(null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - PharmaVault</title>

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

        .current-image {
            max-width: 200px;
            max-height: 200px;
            margin: 1rem auto;
            border-radius: 10px;
            display: block;
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
                <h2><i class="fas fa-edit"></i> Edit Product</h2>
                <p>Update your product details below</p>
            </div>

            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

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
                        value="<?php echo htmlspecialchars($product['product_title']); ?>"
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
                                <option value="<?php echo $category['cat_id']; ?>"
                                    <?php echo ($category['cat_id'] == $product['product_cat']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Brand <span class="required">*</span>
                        </label>
                        <select class="form-control" id="productBrand" name="product_brand" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"
                                    <?php echo ($brand['brand_id'] == $product['product_brand']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                            value="<?php echo $product['product_price']; ?>"
                            step="0.01"
                            min="0"
                            required
                        >
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
                            value="<?php echo $product['product_stock']; ?>"
                            min="0"
                            required
                        >
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
                    ><?php echo htmlspecialchars($product['product_desc'] ?? ''); ?></textarea>
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
                        value="<?php echo htmlspecialchars($product['product_keywords'] ?? ''); ?>"
                    >
                    <small class="form-text">Comma-separated keywords for better search results</small>
                </div>

                <!-- Image Upload -->
                <div class="form-group">
                    <label class="form-label">
                        Product Image
                    </label>

                    <?php if (!empty($product['product_image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Current Image:</p>
                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                 alt="Current product image"
                                 class="current-image"
                                 id="currentImage">
                        </div>
                    <?php endif; ?>

                    <div class="image-upload-area" id="imageUploadArea" onclick="document.getElementById('productImage').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <p style="margin: 0; color: #64748b;">Click to upload new product image</p>
                        <small style="color: #94a3b8;">PNG, JPG, JPEG (Max 5MB) - Leave empty to keep current image</small>
                    </div>
                    <input
                        type="file"
                        id="productImage"
                        name="product_image"
                        accept="image/png, image/jpeg, image/jpg"
                        style="display: none;"
                        onchange="previewImage(this)"
                    >
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
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
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            const uploadArea = document.getElementById('imageUploadArea');
            const currentImage = document.getElementById('currentImage');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    uploadArea.classList.add('active');

                    // Hide current image when new one is selected
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('pharmacy_id', <?php echo $user_id; ?>);

            // Show loading
            Swal.fire({
                title: 'Updating Product...',
                html: 'Please wait while we update your product',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../actions/update_product_action.php', {
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
                    text: 'An error occurred while updating the product',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</body>
</html>
