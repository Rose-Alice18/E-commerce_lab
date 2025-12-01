<?php
/**
 * All Products Management (Super Admin View)
 * Role: Super Admin (0)
 */
session_start();
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');

// Check if user is logged in as super admin
if (!isLoggedIn() || !isSuperAdmin()) {
    header("Location: ../login/login_view.php");
    exit();
}

// Get all products from all pharmacies
$all_products = get_all_products_ctr();
if (!$all_products || !is_array($all_products)) {
    $all_products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - PharmaVault</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            font-weight: 700;
        }

        .products-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-pills me-2"></i>All Products</h1>
            <p class="text-muted mb-0">View and manage all products across all pharmacies</p>
        </div>

        <div class="products-container">
            <table id="productsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Pharmacy</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_products)): ?>
                        <?php foreach ($all_products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($product['product_image']) ? '../' . $product['product_image'] : '../uploads/placeholder.jpg'; ?>"
                                         alt="<?php echo htmlspecialchars($product['product_title'] ?? ''); ?>"
                                         class="product-image">
                                </td>
                                <td><?php echo htmlspecialchars($product['product_title'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['pharmacy_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['cat_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?></td>
                                <td>GH₵ <?php echo number_format($product['product_price'] ?? 0, 2); ?></td>
                                <td><?php echo $product['product_stock'] ?? 0; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-box-open" style="font-size: 3rem; color: #ddd;"></i>
                                <p class="mt-2 text-muted">No products found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                pageLength: 25,
                order: [[1, 'asc']]
            });
        });
    </script>
</body>
</html>
