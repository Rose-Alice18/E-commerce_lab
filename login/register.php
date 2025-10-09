<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -100px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            left: -100px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .register-container {
            width: 100%;
            max-width: 1000px;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.6s ease-out;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .register-left {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
            overflow: hidden;
            min-height: 100%;
        }

        .register-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -100px;
        }

        .brand-section {
            position: relative;
            z-index: 2;
            margin-bottom: 3rem;
        }

        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo i {
            font-size: 3rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            position: relative;
            z-index: 2;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
            animation: slideInRight 0.6s ease-out;
            animation-fill-mode: both;
        }

        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.3s; }
        .feature-item:nth-child(3) { animation-delay: 0.4s; }
        .feature-item:nth-child(4) { animation-delay: 0.5s; }
        .feature-item:nth-child(5) { animation-delay: 0.6s; }
        .feature-item:nth-child(6) { animation-delay: 0.7s; }
        .feature-item:nth-child(7) { animation-delay: 0.8s; }
        .feature-item:nth-child(8) { animation-delay: 0.9s; }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .feature-text h5 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .feature-text p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .register-right {
            padding: 60px 50px;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #718096;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #667eea;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control.error {
            border-color: #f56565;
            animation: shake 0.3s ease;
        }

        .field-error {
            color: #f56565;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .role-selection {
            display: flex;
            gap: 15px;
            margin-top: 0.5rem;
        }

        .role-card {
            flex: 1;
            position: relative;
        }

        .role-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-card label {
            display: block;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .role-card label:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .role-card input[type="radio"]:checked + label {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .role-card label i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
            color: #667eea;
        }

        .role-card label .role-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .role-card label .role-desc {
            font-size: 0.875rem;
            color: #718096;
        }

        .btn-register {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-register.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-left: 10px;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #718096;
        }

        .login-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .country-dropdown {
            position: relative;
        }

        .country-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #667eea;
            border-top: none;
            border-radius: 0 0 12px 12px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            margin-top: -2px;
        }

        .country-suggestions.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }

        .country-suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .country-suggestion-item:last-child {
            border-bottom: none;
        }

        .country-suggestion-item:hover {
            background: rgba(102, 126, 234, 0.1);
            padding-left: 20px;
        }

        .country-suggestions::-webkit-scrollbar {
            width: 8px;
        }

        .country-suggestions::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 0 12px 0;
        }

        .country-suggestions::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }

        .country-suggestions::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }

        @media (max-width: 992px) {
            .register-left {
                padding: 40px 30px;
            }

            .register-right {
                padding: 40px 30px;
            }

            .brand-logo {
                font-size: 2rem;
            }

            .form-header h2 {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .register-left {
                display: none;
            }

            .register-right {
                padding: 30px 20px;
            }

            .role-selection {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="row g-0">
                <!-- Left Panel -->
                <div class="col-lg-5">
                    <div class="register-left">
                        <div class="brand-section">
                            <div class="brand-logo">
                                <i class="fas fa-pills"></i>
                                <span>PharmaVault</span>
                            </div>
                            <p style="font-size: 1.1rem; opacity: 0.95;">Join Ghana's most trusted healthcare marketplace</p>
                        </div>

                        <ul class="feature-list">
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Verified & Secure</h5>
                                    <p>All pharmacies thoroughly verified</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Fast Delivery</h5>
                                    <p>Same-day delivery in major and close-by cities</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Authentic Products</h5>
                                    <p>100% genuine medications only</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Best Prices</h5>
                                    <p>Compare prices and get the best deals</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>24/7 Support</h5>
                                    <p>Expert help anytime you need</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Data Privacy</h5>
                                    <p>Your information stays secure</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Mobile Friendly</h5>
                                    <p>Access PharmaVault on any device</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Smart Search</h5>
                                    <p>Find what you need with our intelligent search system</p>
                                </div>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="feature-text">
                                    <h5>Health First</h5>
                                    <p>Your wellbeing is our top priority, always</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Panel - Registration Form -->
                <div class="col-lg-7">
                    <div class="register-right">
                        <div class="form-header">
                            <h2>Create Account</h2>
                            <p>Get started with your free account</p>
                        </div>

                        <form method="POST" id="register-form">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Enter your full name" required maxlength="100">
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="your.email@example.com" required maxlength="50">
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Minimum 6 characters" required minlength="6">
                            </div>

                            <!-- Country -->
                            <div class="mb-3">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe"></i>
                                    Country
                                </label>
                                <div class="country-dropdown">
                                    <input type="text" class="form-control" id="country" name="country" 
                                           placeholder="Type to search countries..." required maxlength="30" autocomplete="off">
                                    <div class="country-suggestions" id="country-suggestions"></div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Contact Number -->
                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Contact Number
                                    </label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                           placeholder="+233 XX XXX XXXX" required maxlength="15">
                                </div>

                                <!-- City -->
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        City
                                    </label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           placeholder="e.g. Accra" required maxlength="30">
                                </div>
                            </div>

                            <!-- User Role -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-tag"></i>
                                    I want to register as
                                </label>
                                <div class="role-selection">
                                    <div class="role-card">
                                        <input type="radio" name="role" id="customer" value="2" checked>
                                        <label for="customer">
                                            <i class="fas fa-shopping-cart"></i>
                                            <div class="role-title">Customer</div>
                                            <div class="role-desc">Browse & buy medications</div>
                                        </label>
                                    </div>
                                    <div class="role-card">
                                        <input type="radio" name="role" id="admin" value="1">
                                        <label for="admin">
                                            <i class="fas fa-hospital"></i>
                                            <div class="role-title">Pharmacy</div>
                                            <div class="role-desc">Manage your pharmacy</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-register">
                                <i class="fas fa-rocket me-2"></i>Create Account
                            </button>

                            <div class="login-link">
                                Already have an account? <a href="login.php">Sign in here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/register.js"></script>
</body>
</html>