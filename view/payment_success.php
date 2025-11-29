<?php
require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login_view.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'] ?? 'Customer';
$invoice_no = isset($_GET['invoice']) ? htmlspecialchars($_GET['invoice']) : '';
$reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - PharmaVault</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 20px 0;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
        }

        .logo {
            font-size: 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            font-weight: 700;
        }

        .container {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .success-box {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .success-icon {
            font-size: 100px;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out;
            color: #10b981;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 3rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }

        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-value {
            color: #6c757d;
            word-break: break-all;
            text-align: right;
        }

        .btn {
            padding: 16px 40px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #495057;
            border: 2px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .buttons-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .confirmation-message {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #6ee7b7;
            padding: 25px;
            border-radius: 12px;
            color: #065f46;
            margin-bottom: 20px;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            h1 { font-size: 2rem; }
            .success-icon { font-size: 70px; }
            .buttons-container { flex-direction: column; }
            .btn { width: 100%; margin: 5px 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <i class="fas fa-pills"></i> PharmaVault
            </a>
            <div style="display: flex; gap: 20px;">
                <a href="product.php" style="color: #495057; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="success-box">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Successful!</h1>
            <p class="subtitle">Your payment has been processed successfully</p>

            <div class="confirmation-message">
                <strong><i class="fas fa-check"></i> Payment Confirmed</strong><br>
                Thank you for your purchase! Your order has been confirmed and will be processed shortly. You will receive a confirmation email soon.
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-file-invoice"></i> Invoice Number
                    </span>
                    <span class="detail-value"><strong><?php echo $invoice_no; ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-receipt"></i> Payment Reference
                    </span>
                    <span class="detail-value"><?php echo $reference; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar"></i> Order Date
                    </span>
                    <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-user"></i> Customer
                    </span>
                    <span class="detail-value"><?php echo htmlspecialchars($customer_name); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-info-circle"></i> Status
                    </span>
                    <span class="detail-value" style="color: #059669; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Paid
                    </span>
                </div>
            </div>

            <div class="buttons-container">
                <a href="../admin/orders.php" class="btn btn-primary">
                    <i class="fas fa-box"></i> View My Orders
                </a>
                <a href="product.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confetti effect
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#10b981', '#3b82f6', '#f59e0b'];
            const confettiCount = 60;

            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.cssText = `
                        position: fixed;
                        width: 10px;
                        height: 10px;
                        background: ${colors[Math.floor(Math.random() * colors.length)]};
                        left: ${Math.random() * 100}%;
                        top: -10px;
                        opacity: 1;
                        transform: rotate(${Math.random() * 360}deg);
                        z-index: 10001;
                        pointer-events: none;
                        border-radius: 50%;
                    `;

                    document.body.appendChild(confetti);

                    const duration = 2000 + Math.random() * 1000;
                    const startTime = Date.now();

                    function animateConfetti() {
                        const elapsed = Date.now() - startTime;
                        const progress = elapsed / duration;

                        if (progress < 1) {
                            const top = progress * (window.innerHeight + 50);
                            const wobble = Math.sin(progress * 10) * 50;

                            confetti.style.top = top + 'px';
                            confetti.style.left = `calc(${confetti.style.left} + ${wobble}px)`;
                            confetti.style.opacity = 1 - progress;
                            confetti.style.transform = `rotate(${progress * 720}deg)`;

                            requestAnimationFrame(animateConfetti);
                        } else {
                            confetti.remove();
                        }
                    }

                    animateConfetti();
                }, i * 30);
            }
        }

        // Trigger confetti on page load
        window.addEventListener('load', createConfetti);
    </script>
</body>
</html>
