/**
 * Checkout JavaScript
 * Handles payment modal and checkout processing
 */

document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = document.getElementById('payment-modal');
    const btnSimulatePayment = document.getElementById('btn-simulate-payment');
    const btnConfirm = document.getElementById('btn-confirm');
    const btnCancel = document.getElementById('btn-cancel');

    // Open payment modal
    if (btnSimulatePayment) {
        btnSimulatePayment.addEventListener('click', function() {
            paymentModal.classList.add('active');
        });
    }

    // Cancel payment
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            paymentModal.classList.remove('active');
        });
    }

    // Confirm payment
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            processCheckout();
        });
    }

    // Close modal on outside click
    paymentModal.addEventListener('click', function(e) {
        if (e.target === paymentModal) {
            paymentModal.classList.remove('active');
        }
    });

    /**
     * Process checkout via AJAX
     */
    function processCheckout() {
        // Disable confirm button to prevent double submission
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        fetch('../actions/process_checkout_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close payment modal
                paymentModal.classList.remove('active');

                // Show success message
                showSuccessModal(data);
            } else {
                // Show error message
                showErrorMessage(data.message);
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = '<i class="fas fa-check me-2"></i>Yes, I\'ve Paid';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('An error occurred while processing your order. Please try again.');
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="fas fa-check me-2"></i>Yes, I\'ve Paid';
        });
    }

    /**
     * Show success modal with order details
     */
    function showSuccessModal(data) {
        const modalHTML = `
            <div class="payment-modal active" id="success-modal">
                <div class="payment-modal-content">
                    <div class="payment-icon" style="color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Order Placed Successfully!</h3>
                    <p class="text-muted">Thank you for your order</p>

                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Order ID:</strong>
                            <span>#${data.order_id}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Invoice No:</strong>
                            <span>${data.invoice_no}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Total Amount:</strong>
                            <span style="color: #667eea; font-weight: 600;">GH₵ ${data.total_amount}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Order Date:</strong>
                            <span>${data.order_date}</span>
                        </div>
                    </div>

                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        A confirmation will be sent to your email
                    </p>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <a href="../admin/my_orders.php" class="btn btn-primary" style="flex: 1; padding: 1rem; border-radius: 10px; text-decoration: none;">
                            <i class="fas fa-list me-2"></i>View Orders
                        </a>
                        <a href="../view/product.php" class="btn btn-outline-secondary" style="flex: 1; padding: 1rem; border-radius: 10px; text-decoration: none;">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Close success modal on outside click
        const successModal = document.getElementById('success-modal');
        successModal.addEventListener('click', function(e) {
            if (e.target === successModal) {
                window.location.href = '../admin/my_orders.php';
            }
        });
    }

    /**
     * Show error message
     */
    function showErrorMessage(message) {
        const errorHTML = `
            <div class="payment-modal active" id="error-modal">
                <div class="payment-modal-content">
                    <div class="payment-icon" style="color: #dc3545;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3>Payment Failed</h3>
                    <p class="text-danger">${message}</p>

                    <button class="btn-confirm-payment" onclick="document.getElementById('error-modal').remove()" style="margin-top: 2rem;">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', errorHTML);

        // Auto-close error modal after 5 seconds
        setTimeout(() => {
            const errorModal = document.getElementById('error-modal');
            if (errorModal) {
                errorModal.remove();
            }
        }, 5000);

        // Close error modal on outside click
        const errorModal = document.getElementById('error-modal');
        errorModal.addEventListener('click', function(e) {
            if (e.target === errorModal) {
                errorModal.remove();
            }
        });
    }
});
