<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core functions
require_once('../settings/core.php');
require_once('../controllers/customer_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserId();
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 2;

// Get user details
$user_result = get_customer_by_id_ctr($user_id);
$user = $user_result['data'] ?? null;

if (!$user) {
    die("User not found");
}

// Determine which sidebar to include
$sidebar_file = '';
if ($user_role == 0) {
    $sidebar_file = 'sidebar_super_admin.php';
} elseif ($user_role == 1) {
    $sidebar_file = 'sidebar_pharmacy_admin.php';
} else {
    $sidebar_file = 'sidebar_customer.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .edit-profile-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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

        .avatar-upload-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 15px;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            overflow: hidden;
            border: 4px solid #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-preview i {
            font-size: 4rem;
            color: #cbd5e1;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
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

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include Appropriate Sidebar -->
    <?php include('../view/components/' . $sidebar_file); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="edit-profile-container">
            <div style="margin-bottom: 1rem;">
                <a href="profile.php" style="color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                    <p>Update your account information</p>
                </div>

                <form id="editProfileForm" enctype="multipart/form-data">
                    <!-- Avatar Upload -->
                    <div class="avatar-upload-section">
                        <div class="avatar-preview" id="avatarPreview">
                            <?php if (!empty($user['customer_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['customer_image']); ?>" alt="Avatar" id="avatarImg">
                            <?php else: ?>
                                <i class="fas fa-user" id="avatarIcon"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="profileImage" name="profile_image" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('profileImage').click()">
                            <i class="fas fa-camera"></i> Change Profile Picture
                        </button>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($user['customer_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="customer_email" value="<?php echo htmlspecialchars($user['customer_email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="customer_contact" value="<?php echo htmlspecialchars($user['customer_contact']); ?>" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="customer_country" value="<?php echo htmlspecialchars($user['customer_country']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="customer_city" value="<?php echo htmlspecialchars($user['customer_city']); ?>" required>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        function previewAvatar(input) {
            const preview = document.getElementById('avatarPreview');
            const avatarImg = document.getElementById('avatarImg');
            const avatarIcon = document.getElementById('avatarIcon');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    if (avatarImg) {
                        avatarImg.src = e.target.result;
                    } else {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Avatar" id="avatarImg">`;
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading
            Swal.fire({
                title: 'Updating Profile...',
                html: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../actions/update_profile_action.php', {
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
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        window.location.href = 'profile.php';
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
                    text: 'An error occurred while updating your profile',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</body>
</html>
