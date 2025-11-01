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

$user_id = getUserId();
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 2;

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
    <title>Change Password - PharmaVault</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="../css/sidebar.css">

    <style>
        .password-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
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
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .password-input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
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

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #667eea;
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
            width: 100%;
            justify-content: center;
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
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .password-requirements {
            background: #f8fafc;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .password-requirements h4 {
            color: #1e293b;
            font-size: 0.9rem;
            margin: 0 0 0.5rem 0;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Include Appropriate Sidebar -->
    <?php include('../view/components/' . $sidebar_file); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="password-container">
            <div style="margin-bottom: 1rem;">
                <a href="profile.php" style="color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-lock"></i>
                    <h2>Change Password</h2>
                    <p>Ensure your account is using a strong password</p>
                </div>

                <form id="changePasswordForm">
                    <!-- Current Password -->
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('currentPassword', this)"></i>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="8">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('newPassword', this)"></i>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Password
                    </button>

                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <h4><i class="fas fa-info-circle"></i> Password Requirements</h4>
                    <ul>
                        <li>Minimum 8 characters long</li>
                        <li>Include uppercase and lowercase letters</li>
                        <li>Include at least one number</li>
                        <li>Include at least one special character</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sidebar JS -->
    <script src="../js/sidebar.js"></script>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Validate new password
            if (newPassword.length < 8) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Too Short',
                    text: 'Password must be at least 8 characters long',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            // Check if passwords match
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Don\'t Match',
                    text: 'New password and confirmation password must match',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            // Check if new password is same as current
            if (currentPassword === newPassword) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Same Password',
                    text: 'New password must be different from current password',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const formData = new FormData(this);

            // Show loading
            Swal.fire({
                title: 'Updating Password...',
                html: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../actions/change_password_action.php', {
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
                    text: 'An error occurred while changing your password',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</body>
</html>
