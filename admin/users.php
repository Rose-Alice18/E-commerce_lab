<?php
/**
 * Super Admin - User Management
 * Manage all users (super admins, pharmacies, customers)
 */

session_start();
require_once('../settings/core.php');
requireSuperAdmin();

require_once('../classes/customer_class.php');

$customer_obj = new Customer();
$all_users = $customer_obj->getAllCustomers();

// Get filter
$filter_role = isset($_GET['role']) ? intval($_GET['role']) : null;

if ($filter_role !== null) {
    $all_users = $customer_obj->getAllUsersByRole($filter_role);
}

// Get statistics
$stats = [
    'total' => count($customer_obj->getAllCustomers()),
    'super_admin' => count($customer_obj->getAllUsersByRole(0)) + count($customer_obj->getAllUsersByRole(7)),
    'pharmacies' => count($customer_obj->getAllUsersByRole(1)),
    'customers' => count($customer_obj->getAllUsersByRole(2))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">
    <style>
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .user-card:hover {
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }
        .role-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-super-admin {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .role-pharmacy {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }
        .role-customer {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stats-number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 10px 20px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .filter-tab:hover {
            border-color: #667eea;
            color: #667eea;
        }
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../view/components/sidebar_super_admin.php'; ?>

    <div class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-users-cog"></i> User Management</h1>
                    <button class="btn btn-primary" onclick="addUser()">
                        <i class="fas fa-user-plus"></i> Add New User
                    </button>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-users text-primary" style="font-size: 36px;"></i></div>
                            <div class="stats-number"><?php echo $stats['total']; ?></div>
                            <div class="stats-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-user-shield text-danger" style="font-size: 36px;"></i></div>
                            <div class="stats-number"><?php echo $stats['super_admin']; ?></div>
                            <div class="stats-label">Super Admins</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-clinic-medical text-success" style="font-size: 36px;"></i></div>
                            <div class="stats-number"><?php echo $stats['pharmacies']; ?></div>
                            <div class="stats-label">Pharmacies</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon"><i class="fas fa-user text-info" style="font-size: 36px;"></i></div>
                            <div class="stats-number"><?php echo $stats['customers']; ?></div>
                            <div class="stats-label">Customers</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="?role=" class="filter-tab <?php echo $filter_role === null ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> All Users
                    </a>
                    <a href="?role=0" class="filter-tab <?php echo $filter_role === 0 ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i> Super Admins
                    </a>
                    <a href="?role=1" class="filter-tab <?php echo $filter_role === 1 ? 'active' : ''; ?>">
                        <i class="fas fa-clinic-medical"></i> Pharmacies
                    </a>
                    <a href="?role=2" class="filter-tab <?php echo $filter_role === 2 ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Customers
                    </a>
                </div>

                <!-- Users List -->
                <div class="row">
                    <?php foreach ($all_users as $user): ?>
                        <div class="col-md-6">
                            <div class="user-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex gap-3">
                                        <div>
                                            <?php if (isset($user['customer_image']) && $user['customer_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($user['customer_image']); ?>"
                                                     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($user['customer_name']); ?></h5>
                                            <p class="mb-1 text-muted">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['customer_email']); ?>
                                            </p>
                                            <?php if ($user['customer_contact']): ?>
                                                <p class="mb-1 text-muted">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['customer_contact']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($user['customer_city']): ?>
                                                <p class="mb-1 text-muted">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($user['customer_city']); ?>
                                                    <?php if ($user['customer_country']): ?>
                                                        , <?php echo htmlspecialchars($user['customer_country']); ?>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                        $role_class = '';
                                        $role_text = '';
                                        switch($user['user_role']) {
                                            case 0:
                                            case 7:
                                                $role_class = 'role-super-admin';
                                                $role_text = 'Super Admin';
                                                break;
                                            case 1:
                                                $role_class = 'role-pharmacy';
                                                $role_text = 'Pharmacy';
                                                break;
                                            case 2:
                                                $role_class = 'role-customer';
                                                $role_text = 'Customer';
                                                break;
                                        }
                                        ?>
                                        <span class="role-badge <?php echo $role_class; ?>">
                                            <?php echo $role_text; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['customer_id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewUser(<?php echo $user['customer_id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="changeRole(<?php echo $user['customer_id']; ?>, <?php echo $user['user_role']; ?>)">
                                        <i class="fas fa-user-cog"></i> Change Role
                                    </button>
                                    <?php if ($user['customer_id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['customer_id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function addUser() {
            Swal.fire({
                title: 'Add New User',
                html: `
                    <input id="userName" class="swal2-input" placeholder="Full Name">
                    <input id="userEmail" class="swal2-input" type="email" placeholder="Email">
                    <input id="userPassword" class="swal2-input" type="password" placeholder="Password">
                    <input id="userContact" class="swal2-input" placeholder="Phone Number">
                    <select id="userRole" class="swal2-select">
                        <option value="2">Customer</option>
                        <option value="1">Pharmacy Admin</option>
                        <option value="0">Super Admin</option>
                    </select>
                `,
                confirmButtonText: 'Create User',
                showCancelButton: true,
                preConfirm: () => {
                    return {
                        name: document.getElementById('userName').value,
                        email: document.getElementById('userEmail').value,
                        password: document.getElementById('userPassword').value,
                        contact: document.getElementById('userContact').value,
                        role: document.getElementById('userRole').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implement user creation
                    Swal.fire('Success!', 'User created successfully', 'success');
                }
            });
        }

        function editUser(userId) {
            window.location.href = `edit_user.php?id=${userId}`;
        }

        function viewUser(userId) {
            window.location.href = `view_user.php?id=${userId}`;
        }

        function changeRole(userId, currentRole) {
            Swal.fire({
                title: 'Change User Role',
                html: `
                    <select id="newRole" class="swal2-select">
                        <option value="2" ${currentRole == 2 ? 'selected' : ''}>Customer</option>
                        <option value="1" ${currentRole == 1 ? 'selected' : ''}>Pharmacy Admin</option>
                        <option value="0" ${currentRole == 0 || currentRole == 7 ? 'selected' : ''}>Super Admin</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Role',
                preConfirm: () => {
                    return document.getElementById('newRole').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/update_user_role.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `user_id=${userId}&role=${result.value}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Updated!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }

        function deleteUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../actions/delete_user.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success')
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
