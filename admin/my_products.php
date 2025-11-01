<?php
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

// Get products for this pharmacy
$products_result = get_products_by_pharmacy_ctr($user_id);
$products = $products_result['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header h1 {
            color: #1e293b;
            font-weight: 700;
            margin: 0;
        }

        .btn {
            padding: 0.75rem 1.5rem;
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

        .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f1f5f9;
        }

        .product-body {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
        }

        .product-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-category {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-brand {
            background: #fce7f3;
            color: #9f1239;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            margin: 0.75rem 0;
        }

        .product-stock {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .stock-good {
            color: #10b981;
        }

        .stock-low {
            color: #f59e0b;
        }

        .stock-out {
            color: #ef4444;
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h2 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .products-grid {
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
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($products); ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $in_stock = array_filter($products, fn($p) => $p['product_stock'] > 0);
                    echo count($in_stock);
                    ?>
                </div>
                <div class="stat-label">In Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $low_stock = array_filter($products, fn($p) => $p['product_stock'] > 0 && $p['product_stock'] <= 10);
                    echo count($low_stock);
                    ?>
                </div>
                <div class="stat-label">Low Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $out_of_stock = array_filter($products, fn($p) => $p['product_stock'] == 0);
                    echo count($out_of_stock);
                    ?>
                </div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-boxes"></i> My Products</h1>
            <a href="add_product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <?php if (empty($products)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h2>No Products Yet</h2>
                <p>Start building your inventory by adding your first product</p>
                <a href="add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if (!empty($product['product_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>"
                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                 class="product-image">
                        <?php else: ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                <i class="fas fa-image" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="product-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></h3>

                            <div class="product-meta">
                                <span class="product-badge badge-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($product['cat_name']); ?>
                                </span>
                                <span class="product-badge badge-brand">
                                    <i class="fas fa-certificate"></i>
                                    <?php echo htmlspecialchars($product['brand_name']); ?>
                                </span>
                            </div>

                            <div class="product-price">
                                GHS <?php echo number_format($product['product_price'], 2); ?>
                            </div>

                            <div class="product-stock <?php
                                if ($product['product_stock'] == 0) echo 'stock-out';
                                elseif ($product['product_stock'] <= 10) echo 'stock-low';
                                else echo 'stock-good';
                            ?>">
                                <i class="fas fa-boxes"></i>
                                Stock: <?php echo $product['product_stock']; ?> units
                                <?php if ($product['product_stock'] <= 10 && $product['product_stock'] > 0): ?>
                                    <span style="color: #f59e0b;">(Low Stock!)</span>
                                <?php elseif ($product['product_stock'] == 0): ?>
                                    <span style="color: #ef4444;">(Out of Stock!)</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-actions">
                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($product['product_title'])); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        function deleteProduct(productId, productTitle) {
            Swal.fire({
                title: 'Delete Product?',
                html: `Are you sure you want to delete <strong>${productTitle}</strong>?<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    fetch('../actions/delete_product_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                window.location.reload();
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
                            text: 'An error occurred while deleting the product',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
