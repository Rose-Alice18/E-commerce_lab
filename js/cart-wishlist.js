/**
 * Cart and Wishlist JavaScript Functions
 * Reusable functions for adding products to cart and wishlist
 */

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="font-size: 1.5rem; color: ${type === 'success' ? '#10b981' : '#ef4444'};"></i>
        <span style="flex: 1;">${message}</span>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add to cart function with smart quantity display
function addToCart(productId) {
    const btn = event.target.closest('.btn-add-cart');
    const productCard = btn.closest('.product-card') || btn.closest('.product-body');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

    // Determine the correct path to actions folder
    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/cart_actions.php';
    } else {
        actionsPath = './actions/cart_actions.php';
    }

    fetch(actionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');

            // Update cart count in sidebar if it exists
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge && data.cart_count) {
                cartBadge.textContent = data.cart_count;
            }

            // Replace button with quantity controls
            const actionsDiv = btn.parentElement;
            actionsDiv.querySelector('.btn-add-cart').outerHTML = `
                <div class="cart-quantity-controls">
                    <button class="btn-qty-minus" onclick="updateCartQuantity(${productId}, -1, this)" title="Decrease quantity">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="cart-qty-display">${data.product_quantity}</span>
                    <button class="btn-qty-plus" onclick="updateCartQuantity(${productId}, 1, this)" title="Increase quantity">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            `;
        } else {
            showToast(data.message, 'error');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

// Update cart quantity
function updateCartQuantity(productId, change, btn) {
    const quantityControls = btn.closest('.cart-quantity-controls');
    const qtyDisplay = quantityControls.querySelector('.cart-qty-display');
    const currentQty = parseInt(qtyDisplay.textContent);
    const newQty = currentQty + change;

    if (newQty < 1) {
        // Remove from cart if quantity becomes 0
        if (confirm('Remove this item from cart?')) {
            removeFromCart(productId, quantityControls);
        }
        return;
    }

    // Disable buttons during update
    quantityControls.querySelectorAll('button').forEach(b => b.disabled = true);

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/cart_actions.php';
    } else {
        actionsPath = './actions/cart_actions.php';
    }

    fetch(actionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${newQty}&set_quantity=true`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qtyDisplay.textContent = data.product_quantity;

            // Update cart count
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
            }

            // Show warning if quantity was capped
            if (data.capped) {
                showToast(data.message, 'error');
            }
        } else {
            showToast(data.message, 'error');
        }

        // Re-enable buttons
        quantityControls.querySelectorAll('button').forEach(b => b.disabled = false);
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        quantityControls.querySelectorAll('button').forEach(b => b.disabled = false);
    });
}

// Remove from cart
function removeFromCart(productId, quantityControls) {
    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/cart_actions.php';
    } else {
        actionsPath = './actions/cart_actions.php';
    }

    fetch(actionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Item removed from cart', 'success');

            // Replace quantity controls with add to cart button
            quantityControls.outerHTML = `
                <button class="btn-add-cart" onclick="addToCart(${productId})">
                    <i class="fas fa-shopping-cart me-1"></i>
                    <span>Add to Cart</span>
                </button>
            `;

            // Update cart count
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
            }
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// Toggle wishlist function
function toggleWishlist(productId, btn) {
    const icon = btn.querySelector('i');
    const wasActive = btn.classList.contains('active');

    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';

    // Determine the correct path to actions folder
    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/wishlist_actions.php';
    } else {
        actionsPath = './actions/wishlist_actions.php';
    }

    fetch(actionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            if (data.in_wishlist) {
                btn.classList.add('active');
                icon.className = 'fas fa-heart';
            } else {
                btn.classList.remove('active');
                icon.className = 'far fa-heart';
            }
            // Update wishlist count if exists
            const wishlistBadge = document.getElementById('wishlistCount');
            if (wishlistBadge && data.wishlist_count !== undefined) {
                wishlistBadge.textContent = data.wishlist_count;
            }
        } else {
            showToast(data.message, 'error');
            icon.className = wasActive ? 'fas fa-heart' : 'far fa-heart';
        }
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        icon.className = wasActive ? 'fas fa-heart' : 'far fa-heart';
        btn.disabled = false;
    });
}

// Check product cart status on page load
function checkProductsInCart() {
    const addToCartButtons = document.querySelectorAll('.btn-add-cart[data-product-id]');

    if (addToCartButtons.length === 0) return;

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/cart_actions.php';
    } else {
        actionsPath = './actions/cart_actions.php';
    }

    addToCartButtons.forEach(btn => {
        const productId = btn.getAttribute('data-product-id');

        fetch(actionsPath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_quantity&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.in_cart && data.quantity > 0) {
                // Replace button with quantity controls
                const actionsDiv = btn.parentElement;
                btn.outerHTML = `
                    <div class="cart-quantity-controls">
                        <button class="btn-qty-minus" onclick="updateCartQuantity(${productId}, -1, this)" title="Decrease quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="cart-qty-display">${data.quantity}</span>
                        <button class="btn-qty-plus" onclick="updateCartQuantity(${productId}, 1, this)" title="Increase quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error checking cart status:', error));
    });
}

// Load cart and wishlist counts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Determine the correct path to actions folder based on current location
    const path = window.location.pathname;
    let cartActionsPath, wishlistActionsPath;

    if (path.includes('/view/')) {
        cartActionsPath = '../actions/cart_actions.php';
        wishlistActionsPath = '../actions/wishlist_actions.php';
    } else if (path.includes('/admin/')) {
        cartActionsPath = '../actions/cart_actions.php';
        wishlistActionsPath = '../actions/wishlist_actions.php';
    } else {
        cartActionsPath = './actions/cart_actions.php';
        wishlistActionsPath = './actions/wishlist_actions.php';
    }

    // Load cart count
    fetch(cartActionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count || 0;
            }
        }
    })
    .catch(error => console.error('Error loading cart count:', error));

    // Load wishlist count
    fetch(wishlistActionsPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const wishlistBadge = document.getElementById('wishlistCount');
            if (wishlistBadge) {
                wishlistBadge.textContent = data.wishlist_count || 0;
            }
        }
    })
    .catch(error => console.error('Error loading wishlist count:', error));

    // Check which products are already in cart
    checkProductsInCart();
});
