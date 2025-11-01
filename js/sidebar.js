/**
 * Sidebar Interactive Functionality
 * Handles sidebar toggle, mobile responsiveness, and dynamic features
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const collapseToggle = document.getElementById('sidebarCollapseToggle');

    // Load saved collapse state from localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
    }

    // Desktop collapse/expand toggle
    if (collapseToggle) {
        collapseToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('collapsed');

            // Save state to localStorage
            const collapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', collapsed);
        });
    }

    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }

    // Close sidebar when clicking a link on mobile
    const navLinks = document.querySelectorAll('.nav-item a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        }, 250);
    });

    // Update cart count (for customers)
    updateCartCount();

    // Update order count (for pharmacy admins)
    updateOrderCount();
});

/**
 * Update cart count badge
 */
function updateCartCount() {
    const cartBadge = document.getElementById('cartCount');
    if (!cartBadge) return;

    // Fetch cart count from server
    fetch('../../actions/get_cart_count.php')
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
 * Update order count badge for pharmacy admins
 */
function updateOrderCount() {
    const orderBadge = document.getElementById('orderCount');
    if (!orderBadge) return;

    // Fetch pending orders count from server
    fetch('../../actions/get_pending_orders_count.php')
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
 * Refresh counts periodically
 */
setInterval(function() {
    updateCartCount();
    updateOrderCount();
}, 30000); // Update every 30 seconds
