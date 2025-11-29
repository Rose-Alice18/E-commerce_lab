/**
 * Checkout JavaScript - Paystack Integration
 * Handles payment processing via Paystack
 */

document.addEventListener('DOMContentLoaded', function() {
    const btnProceedPayment = document.getElementById('btn-proceed-payment');
    const paystackPublicKey = document.getElementById('paystack-public-key').value;

    if (btnProceedPayment) {
        btnProceedPayment.addEventListener('click', function() {
            initializePayment();
        });
    }

    /**
     * Initialize Paystack payment
     */
    async function initializePayment() {
        // Get payment details from button data attributes
        const amount = parseFloat(btnProceedPayment.dataset.amount);
        const email = btnProceedPayment.dataset.email;
        const customerName = btnProceedPayment.dataset.customerName;

        // Validate email
        if (!email || !email.includes('@')) {
            showErrorMessage('Invalid email address. Please update your profile.');
            return;
        }

        // Disable button to prevent double submission
        btnProceedPayment.disabled = true;
        btnProceedPayment.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Initializing Payment...';

        try {
            // Initialize transaction with backend
            const response = await fetch('../actions/paystack_init_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: amount,
                    email: email
                })
            });

            const data = await response.json();
            console.log('Init response:', data);

            if (data.status === 'success') {
                // Launch Paystack payment modal
                launchPaystackModal(data.authorization_url, data.reference, amount, email);
            } else {
                showErrorMessage(data.message || 'Failed to initialize payment');
                resetPaymentButton();
            }

        } catch (error) {
            console.error('Error initializing payment:', error);
            showErrorMessage('Connection error. Please try again.');
            resetPaymentButton();
        }
    }

    /**
     * Launch Paystack Inline Modal
     */
    function launchPaystackModal(authorizationUrl, reference, amount, email) {
        const handler = PaystackPop.setup({
            key: paystackPublicKey,
            email: email,
            amount: amount * 100, // Convert to pesewas
            currency: 'GHS',
            ref: reference,
            channels: ['card', 'mobile_money'],
            metadata: {
                custom_fields: [
                    {
                        display_name: "Customer Name",
                        variable_name: "customer_name",
                        value: btnProceedPayment.dataset.customerName
                    }
                ]
            },
            callback: function(response) {
                console.log('Payment successful:', response);
                // Verify payment on backend
                verifyPayment(response.reference);
            },
            onClose: function() {
                console.log('Payment window closed');
                showErrorMessage('Payment cancelled. Please try again when ready.');
                resetPaymentButton();
            }
        });

        handler.openIframe();
    }

    /**
     * Verify payment with backend
     */
    async function verifyPayment(reference) {
        try {
            btnProceedPayment.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying Payment...';

            const response = await fetch('../actions/paystack_verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reference: reference,
                    cart_items: null,
                    total_amount: parseFloat(btnProceedPayment.dataset.amount)
                })
            });

            const data = await response.json();
            console.log('Verification response:', data);

            if (data.status === 'success' && data.verified) {
                // Payment verified successfully
                showSuccessMessage(data);
                // Redirect to success page after 2 seconds
                setTimeout(() => {
                    window.location.href = `payment_success.php?reference=${encodeURIComponent(reference)}&invoice=${encodeURIComponent(data.invoice_no)}`;
                }, 2000);
            } else {
                showErrorMessage(data.message || 'Payment verification failed');
                resetPaymentButton();
            }

        } catch (error) {
            console.error('Error verifying payment:', error);
            showErrorMessage('Error verifying payment. Please contact support with your reference.');
            resetPaymentButton();
        }
    }

    /**
     * Reset payment button to original state
     */
    function resetPaymentButton() {
        btnProceedPayment.disabled = false;
        btnProceedPayment.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
    }

    /**
     * Show success message
     */
    function showSuccessMessage(data) {
        const modalHTML = `
            <div class="payment-modal active" id="success-modal" style="display: flex;">
                <div class="payment-modal-content">
                    <div class="payment-icon" style="color: #10b981; font-size: 5rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 style="color: #059669; font-size: 2rem; margin-bottom: 1rem;">Payment Successful!</h3>
                    <p class="text-muted">Your order has been confirmed</p>

                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0; text-align: left;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Invoice No:</strong>
                            <span>${data.invoice_no}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Reference:</strong>
                            <span style="font-family: monospace; font-size: 0.85rem;">${data.payment_reference}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Amount:</strong>
                            <span style="color: #667eea; font-weight: 600;">GHS ${data.total_amount}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <strong>Payment Method:</strong>
                            <span>${data.payment_method}</span>
                        </div>
                    </div>

                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Redirecting to confirmation page...
                    </p>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    /**
     * Show error message
     */
    function showErrorMessage(message) {
        const errorHTML = `
            <div class="payment-modal active" id="error-modal" style="display: flex;">
                <div class="payment-modal-content">
                    <div class="payment-icon" style="color: #dc3545; font-size: 4rem;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3 style="color: #dc2626; margin-bottom: 1rem;">Payment Error</h3>
                    <p style="color: #64748b; margin-bottom: 2rem;">${message}</p>

                    <button class="btn-confirm-payment" onclick="document.getElementById('error-modal').remove()"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
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
        if (errorModal) {
            errorModal.addEventListener('click', function(e) {
                if (e.target === errorModal) {
                    errorModal.remove();
                }
            });
        }
    }
});
