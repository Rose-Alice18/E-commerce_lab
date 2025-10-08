/**
 * Category Management JavaScript
 * Handles all category CRUD operations asynchronously
 */

// Global variables
let editModal, deleteModal;

// Initialize modals when DOM loads
document.addEventListener('DOMContentLoaded', function() {
    editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    
    // Setup form handlers
    setupFormHandlers();

    loadCategories();  // ✅ THIS LINE WAS MISSING IN YOUR CODE!
    
    console.log('✓ Category management initialized');
});

/**
 * Setup form event handlers
 */
function setupFormHandlers() {
    // Add category form handler
    const addForm = document.getElementById('addCategoryForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddCategory);
    }
    
    // Edit category form handler
    const editForm = document.getElementById('editCategoryForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditCategory);
    }
}

/**
 * Validate category data
 * @param {string} categoryName - The category name to validate
 * @returns {Object} - Validation result with isValid and message
 */
function validateCategory(categoryName) {
    // Check if empty
    if (!categoryName || categoryName.trim() === '') {
        return {
            isValid: false,
            message: 'Category name is required'
        };
    }
    
    // Check length
    if (categoryName.trim().length > 100) {
        return {
            isValid: false,
            message: 'Category name must be less than 100 characters'
        };
    }
    
    // Check for special characters (basic validation)
    const namePattern = /^[a-zA-Z0-9\s\-&().,]+$/;
    if (!namePattern.test(categoryName.trim())) {
        return {
            isValid: false,
            message: 'Category name contains invalid characters'
        };
    }
    
    return {
        isValid: true,
        message: 'Valid category name'
    };
}

/**
 * Show notification to user
 * @param {string} message - The message to display
 * @param {string} type - The type of alert (success, danger, warning, info)
 */
function showNotification(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : type === 'danger' ? 'Error!' : 'Info!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to body
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Handle add category form submission
 * @param {Event} e - Form submit event
 */
async function handleAddCategory(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const categoryName = formData.get('category_name');
    
    // Validate category data
    const validation = validateCategory(categoryName);
    if (!validation.isValid) {
        showNotification(validation.message, 'danger');
        return;
    }
    
    // Show loading
    const loadingSpinner = form.querySelector('.loading');
    const submitBtn = form.querySelector('button[type="submit"]');
    loadingSpinner.style.display = 'inline-block';
    submitBtn.disabled = true;
    
    try {
        // Make AJAX request
        const response = await fetch('../actions/add_category_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            form.reset();
            loadCategories(); // Reload categories
        } else {
            showNotification(result.message, 'danger');
        }
    } catch (error) {
        console.error('Error adding category:', error);
        showNotification('Failed to add category. Please try again.', 'danger');
    } finally {
        // Hide loading
        loadingSpinner.style.display = 'none';
        submitBtn.disabled = false;
    }
}

/**
 * Load and display categories
 */
async function loadCategories() {
    const container = document.getElementById('categoriesContainer');
    
    try {
        // Show loading
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading categories...</span>
                </div>
                <p class="mt-2 text-muted">Loading your categories...</p>
            </div>
        `;
        
        // Fetch categories
        const response = await fetch('../actions/fetch_category_action.php');
        const result = await response.json();
        
        if (result.success) {
            displayCategories(result.data);
        } else {
            container.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${result.message}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        container.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                Failed to load categories. Please refresh the page.
            </div>
        `;
    }
}

/**
 * Display categories in the container
 * @param {Array} categories - Array of category objects
 */
function displayCategories(categories) {
    const container = document.getElementById('categoriesContainer');
    
    if (!categories || categories.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Categories Found</h5>
                <p class="text-muted">Create your first category using the form above.</p>
            </div>
        `;
        return;
    }
    
    // Create categories grid
    let html = '<div class="row">';
    categories.forEach(category => {
        html += `
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card category-card h-100">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-tag text-primary me-2"></i>
                            ${escapeHtml(category.cat_name)}
                        </h6>
                        <p class="card-text text-muted small">
                            Created: ${formatDate(category.created_at)}
                        </p>
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="editCategory(${category.cat_id}, '${escapeHtml(category.cat_name).replace(/'/g, "\\'")}')">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="deleteCategory(${category.cat_id}, '${escapeHtml(category.cat_name).replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

/**
 * Open edit category modal
 * @param {number} catId - Category ID
 * @param {string} catName - Category name
 */
function editCategory(catId, catName) {
    document.getElementById('editCategoryId').value = catId;
    document.getElementById('editCategoryName').value = catName;
    editModal.show();
}

/**
 * Handle edit category form submission
 * @param {Event} e - Form submit event
 */
async function handleEditCategory(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const categoryName = formData.get('category_name');
    
    // Validate category data
    const validation = validateCategory(categoryName);
    if (!validation.isValid) {
        showNotification(validation.message, 'danger');
        return;
    }
    
    try {
        // Make AJAX request
        const response = await fetch('../actions/update_category_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            editModal.hide();
            loadCategories(); // Reload categories
        } else {
            showNotification(result.message, 'danger');
        }
    } catch (error) {
        console.error('Error updating category:', error);
        showNotification('Failed to update category. Please try again.', 'danger');
    }
}

/**
 * Open delete category modal
 * @param {number} catId - Category ID
 * @param {string} catName - Category name
 */
function deleteCategory(catId, catName) {
    document.getElementById('deleteCategoryId').value = catId;
    document.getElementById('deleteCategoryName').textContent = catName;
    deleteModal.show();
}

/**
 * Confirm and execute category deletion
 */
async function confirmDelete() {
    const catId = document.getElementById('deleteCategoryId').value;
    
    try {
        // Create form data
        const formData = new FormData();
        formData.append('cat_id', catId);
        
        // Make AJAX request
        const response = await fetch('../actions/delete_category_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            deleteModal.hide();
            loadCategories(); // Reload categories
        } else {
            showNotification(result.message, 'danger');
        }
    } catch (error) {
        console.error('Error deleting category:', error);
        showNotification('Failed to delete category. Please try again.', 'danger');
    }
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format date for display
 * @param {string} dateString - Date string from database
 * @returns {string} - Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}