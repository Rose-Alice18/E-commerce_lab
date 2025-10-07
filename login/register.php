<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Register Sample</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .btn-custom {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .highlight {
            color: #007bff;
            transition: color 0.3s;
        }

        .highlight:hover {
            color: #0056b3;
        }

        body {
            /* Base background color */
            background-color: #f8f9fa;

            /* Gradient-like grid using repeating-linear-gradients */
            background-image:
                repeating-linear-gradient(0deg,
                    #007bff,
                    #007bff 1px,
                    transparent 1px,
                    transparent 20px),
                repeating-linear-gradient(90deg,
                    #007bff,
                    #007bff 1px,
                    transparent 1px,
                    transparent 20px),
                linear-gradient(rgba(0, 123, 255, 0.1),
                    rgba(0, 123, 255, 0.1));

            /* Blend the gradients for a subtle overlay effect */
            background-blend-mode: overlay;

            /* Define the size of the grid */
            background-size: 20px 20px;

            /* Ensure the background covers the entire viewport */
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .register-container {
            margin-top: 50px;
        }

        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
        }

        .custom-radio .form-check-input:checked+.form-check-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            position: relative;
            padding-left: 2rem;
            cursor: pointer;
        }

        .form-check-label::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            border: 2px solid #007bff;
            border-radius: 50%;
            background-color: #fff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .form-check-input:focus+.form-check-label::before {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
        }

        .animate-pulse-custom {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .field-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.error {
            border-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container register-container">
        <div class="row justify-content-center animate__animated animate__fadeInDown">
            <div class="col-md-6">
                <div class="card animate__animated animate__zoomIn">
                    <div class="card-header text-center highlight">
                        <h4>Register</h4>
                    </div>
                    <div class="card-body">
                        <!-- No form action - asynchronous logic in register.js handles onSubmission events -->
                        <form method="POST" class="mt-4" id="register-form">
                            <!-- Full Name Field -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <i class="fa fa-user"></i></label>
                                <input type="text" class="form-control animate__animated animate__fadeInUp" id="name" name="name" required maxlength="100">
                            </div>
                            
                            <!-- Email Field - unique field, check availability before adding customer -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <i class="fa fa-envelope"></i></label>
                                <input type="email" class="form-control animate__animated animate__fadeInUp" id="email" name="email" required maxlength="50">
                            </div>
                            
                            <!-- Password Field - will be encrypted in PHP before database -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <i class="fa fa-lock"></i></label>
                                <input type="password" class="form-control animate__animated animate__fadeInUp" id="password" name="password" required minlength="6">
                            </div>
                            
                            <!-- Contact Number Field -->
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Contact Number <i class="fa fa-phone"></i></label>
                                <input type="text" class="form-control animate__animated animate__fadeInUp" id="phone_number" name="phone_number" required maxlength="15">
                                <div class="field-error" id="phone-error" style="display: none;"></div>
                            </div>

                            <!-- Country Field with dropdown -->
                            <div class="mb-3">
                                <label for="country" class="form-label">Country <i class="fa fa-globe"></i></label>
                                <div class="country-dropdown">
                                    <input type="text" class="form-control animate__animated animate__fadeInUp" id="country" name="country" required maxlength="30" placeholder="Type to search countries..." autocomplete="off">
                                    <div class="country-suggestions" id="country-suggestions"></div>
                                </div>
                            </div>
                            
                            <!-- City Field -->
                            <div class="mb-3">
                                <label for="city" class="form-label">City <i class="fa fa-map-marker-alt"></i></label>
                                <input type="text" class="form-control animate__animated animate__fadeInUp" id="city" name="city" required maxlength="30">
                            </div>
                            
                            <!-- User Role Field - default value for DB access control -->
                            <div class="mb-4">
                                <label class="form-label">User Role</label>
                                <div class="d-flex justify-content-start">
                                    <div class="form-check me-3 custom-radio">
                                        <input class="form-check-input" type="radio" name="role" id="customer" value="2" checked>
                                        <label class="form-check-label" for="customer">Customer</label>
                                    </div>
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="role" id="admin" value="1">
                                        <label class="form-check-label" for="admin">Pharmacy Administrator</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom w-100 animate-pulse-custom">Register</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Already have an account? <a href="login.php" class="highlight">Login here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/register.js"></script>
    
    
</body>

</html>