/**
 * Modern Sidebar with Hamburger Menu
 * Handles sidebar toggle, mobile responsiveness, and dynamic features
 */

// Apply sidebar state IMMEDIATELY (before DOMContentLoaded) to prevent any flash/disruption
(function() {
    const sidebarState = localStorage.getItem('sidebarState') || 'collapsed';

    // Wait for sidebar and hamburger to exist
    const checkElements = setInterval(function() {
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburgerMenu');

        if (sidebar && hamburger) {
            clearInterval(checkElements);

            // Add no-transition class to prevent all animations during initial load
            sidebar.classList.add('no-transition');

            if (sidebarState === 'expanded') {
                sidebar.classList.remove('collapsed');
                hamburger.classList.add('active');
                hamburger.style.left = 'calc(280px + 1rem)';
            } else {
                sidebar.classList.add('collapsed');
                hamburger.classList.remove('active');
                hamburger.style.left = 'calc(70px + 0.75rem)';
            }

            // Remove no-transition class after page is fully loaded to enable smooth user interactions
            setTimeout(function() {
                sidebar.classList.remove('no-transition');
            }, 100);
        }
    }, 10);
})();

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburgerMenu');
    const backdrop = document.getElementById('sidebarBackdrop');

    // State is already applied above, just set up event listeners

    // Hamburger menu toggle
    if (hamburger && sidebar) {
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            hamburger.classList.toggle('active');

            // Move hamburger position based on sidebar state
            if (sidebar.classList.contains('collapsed')) {
                // Sidebar is collapsed - position hamburger outside collapsed sidebar
                hamburger.style.left = 'calc(70px + 0.75rem)';
                // Save user preference
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                // Sidebar is expanded - position hamburger outside expanded sidebar
                hamburger.style.left = 'calc(280px + 1rem)';
                // Save user preference
                localStorage.setItem('sidebarState', 'expanded');
            }
        });
    }

    // Don't auto-close sidebar on navigation - let user control it
    // Removed auto-close functionality to preserve user's sidebar state preference

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // On large screens, optionally keep sidebar open
            // For now, we'll keep it collapsed by default for consistency
        }, 250);
    });

    // Handle escape key to collapse sidebar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !sidebar.classList.contains('collapsed')) {
            sidebar.classList.add('collapsed');
            hamburger.classList.remove('active');
            hamburger.style.left = 'calc(70px + 0.75rem)';
            // Save user preference
            localStorage.setItem('sidebarState', 'collapsed');
        }
    });

    // Scroll active menu item into view
    scrollActiveItemIntoView();

    // Update cart count (for customers)
    updateCartCount();

    // Update order count (for pharmacy admins)
    updateOrderCount();

    // Update wishlist count
    updateWishlistCount();

    // Update pending suggestions count (for super admins)
    updatePendingSuggestionsCount();
});

/**
 * Scroll the active sidebar menu item into view
 */
function scrollActiveItemIntoView() {
    const activeItem = document.querySelector('.sidebar .nav-item.active');
    const sidebarNav = document.querySelector('.sidebar-nav');

    if (activeItem && sidebarNav) {
        // Calculate scroll position
        const activeItemTop = activeItem.offsetTop;
        const sidebarNavHeight = sidebarNav.clientHeight;
        const activeItemHeight = activeItem.clientHeight;
        const scrollPosition = activeItemTop - (sidebarNavHeight / 2) + (activeItemHeight / 2);

        // Scroll instantly without animation to prevent disruption
        sidebarNav.scrollTo({
            top: scrollPosition,
            behavior: 'instant'
        });
    }
}

/**
 * Update cart count badge
 */
function updateCartCount() {
    const cartBadge = document.getElementById('cartCount');
    if (!cartBadge) return;

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/get_cart_count.php';
    } else {
        actionsPath = './actions/get_cart_count.php';
    }

    fetch(actionsPath)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartBadge.textContent = data.count || 0;
                if (data.count > 0) {
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching cart count:', error);
        });
}

/**
 * Update wishlist count badge
 */
function updateWishlistCount() {
    const wishlistBadge = document.getElementById('wishlistCount');
    if (!wishlistBadge) return;

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/get_wishlist_count.php';
    } else {
        actionsPath = './actions/get_wishlist_count.php';
    }

    fetch(actionsPath)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                wishlistBadge.textContent = data.count || 0;
                if (data.count > 0) {
                    wishlistBadge.style.display = 'inline-block';
                } else {
                    wishlistBadge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching wishlist count:', error);
        });
}

/**
 * Update order count badge for pharmacy admins
 */
function updateOrderCount() {
    const orderBadge = document.getElementById('orderCount');
    if (!orderBadge) return;

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/get_pending_orders_count.php';
    } else {
        actionsPath = './actions/get_pending_orders_count.php';
    }

    fetch(actionsPath)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                orderBadge.textContent = data.count || 0;
                if (data.count > 0) {
                    orderBadge.style.display = 'inline-block';
                } else {
                    orderBadge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching order count:', error);
        });
}

/**
 * Update pending suggestions count badge for super admins
 */
function updatePendingSuggestionsCount() {
    const suggestionBadge = document.getElementById('pendingSuggestionCount');
    if (!suggestionBadge) return;

    const path = window.location.pathname;
    let actionsPath;
    if (path.includes('/view/') || path.includes('/admin/')) {
        actionsPath = '../actions/get_pending_suggestions.php';
    } else {
        actionsPath = './actions/get_pending_suggestions.php';
    }

    fetch(actionsPath)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const totalCount = data.total_count || 0;
                suggestionBadge.textContent = totalCount;
                if (totalCount > 0) {
                    suggestionBadge.style.display = 'inline-block';
                } else {
                    suggestionBadge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching suggestion count:', error);
        });
}

/**
 * Refresh counts periodically
 */
setInterval(function() {
    updateCartCount();
    updateOrderCount();
    updateWishlistCount();
    updatePendingSuggestionsCount();
}, 30000); // Update every 30 seconds
