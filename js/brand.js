// Brand Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Load brands on page load
    loadBrands();

    // Add Brand Form Submit
    const addBrandForm = document.getElementById('addBrandForm');
    if (addBrandForm) {
        addBrandForm.addEventListener('submit', handleAddBrand);
    }

    // Edit Brand Form Submit
    const editBrandForm = document.getElementById('editBrandForm');
    if (editBrandForm) {
        editBrandForm.addEventListener('submit', handleEditBrand);
    }
});

/**
 * Load all brands from server
 */
function loadBrands() {
    fetch('../actions/fetch_brand_action.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBrands(data.data);
            } else {
                showAlert('error', data.message || 'Failed to load brands');
            }
        })
        .catch(error => {
            console.error('Error loading brands:', error);
            showAlert('error', 'An error occurred while loading brands');
        });
}

/**
 * Display brands grouped by category
 */
function displayBrands(brands) {
    const container = document.getElementById('brandsContainer');
    const brandCount = document.getElementById('brandCount');

    if (!container) return;

    // Update brand count
    if (brandCount) {
        brandCount.textContent = brands.length;
    }

    // Clear container
    container.innerHTML = '';

    if (brands.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-award"></i>
                <h4>No Brands Yet</h4>
                <p>Start by adding your first product brand (e.g., Panadol, Centrum)</p>
            </div>
        `;
        return;
    }

    // Group brands by category
    const groupedBrands = {};
    brands.forEach(brand => {
        const catName = brand.cat_name;
        if (!groupedBrands[catName]) {
            groupedBrands[catName] = [];
        }
        groupedBrands[catName].push(brand);
    });

    // Display grouped brands
    for (const [catName, catBrands] of Object.entries(groupedBrands)) {
        const categorySection = document.createElement('div');
        categorySection.className = 'category-group';

        categorySection.innerHTML = `
            <div class="category-title">
                <i class="fas fa-folder"></i>
                ${escapeHtml(catName)}
                <span class="badge bg-secondary ms-2">${catBrands.length}</span>
            </div>
            <div class="brands-grid">
                ${catBrands.map(brand => createBrandCard(brand)).join('')}
            </div>
        `;

        container.appendChild(categorySection);
    }
}

/**
 * Create brand card HTML
 */
function createBrandCard(brand) {
    const createdDate = new Date(brand.created_at).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });

    return `
        <div class="brand-card">
            <div class="brand-card-header">
                <div class="brand-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="brand-actions">
                    <button
                        class="btn-edit"
                        onclick="editBrand(${brand.brand_id}, '${escapeHtml(brand.brand_name)}', ${brand.cat_id})"
                        title="Edit Brand"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        class="btn-delete"
                        onclick="deleteBrand(${brand.brand_id}, '${escapeHtml(brand.brand_name)}')"
                        title="Delete Brand"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <h5 class="brand-name">${escapeHtml(brand.brand_name)}</h5>
            <div class="brand-meta">
                <i class="fas fa-clock me-1"></i>
                Added ${createdDate}
            </div>
        </div>
    `;
}

/**
 * Handle add brand form submission
 */
function handleAddBrand(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validate inputs
    const brandName = formData.get('brand_name').trim();
    const catId = formData.get('cat_id');

    if (!brandName) {
        showAlert('error', 'Please enter a brand name');
        return;
    }

    if (!catId) {
        showAlert('error', 'Please select a category');
        return;
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';

    // Send request
    fetch('../actions/add_brand_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Brand added successfully');
            form.reset();
            loadBrands(); // Reload brands list
        } else {
            showAlert('error', data.message || 'Failed to add brand');
        }
    })
    .catch(error => {
        console.error('Error adding brand:', error);
        showAlert('error', 'An error occurred while adding the brand');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Brand';
    });
}

/**
 * Open edit brand modal
 */
function editBrand(brandId, brandName, catId) {
    const modal = new bootstrap.Modal(document.getElementById('editBrandModal'));

    // Populate form
    document.getElementById('editBrandId').value = brandId;
    document.getElementById('editBrandName').value = brandName;
    document.getElementById('editBrandCategory').value = catId;

    modal.show();
}

/**
 * Handle edit brand form submission
 */
function handleEditBrand(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validate inputs
    const brandName = formData.get('brand_name').trim();
    const catId = formData.get('cat_id');

    if (!brandName) {
        showAlert('error', 'Please enter a brand name');
        return;
    }

    if (!catId) {
        showAlert('error', 'Please select a category');
        return;
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

    // Send request
    fetch('../actions/update_brand_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Brand updated successfully');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editBrandModal'));
            modal.hide();

            loadBrands(); // Reload brands list
        } else {
            showAlert('error', data.message || 'Failed to update brand');
        }
    })
    .catch(error => {
        console.error('Error updating brand:', error);
        showAlert('error', 'An error occurred while updating the brand');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
    });
}

/**
 * Delete brand with confirmation
 */
function deleteBrand(brandId, brandName) {
    Swal.fire({
        title: 'Delete Brand?',
        html: `Are you sure you want to delete the brand:<br><strong>"${escapeHtml(brandName)}"</strong>?<br><br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(brandId);
        }
    });
}

/**
 * Perform brand deletion
 */
function performDelete(brandId) {
    const formData = new FormData();
    formData.append('brand_id', brandId);

    fetch('../actions/delete_brand_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Brand deleted successfully');
            loadBrands(); // Reload brands list
        } else {
            showAlert('error', data.message || 'Failed to delete brand');
        }
    })
    .catch(error => {
        console.error('Error deleting brand:', error);
        showAlert('error', 'An error occurred while deleting the brand');
    });
}

/**
 * Show alert message using SweetAlert2
 */
function showAlert(type, message) {
    const iconMap = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };

    Swal.fire({
        icon: iconMap[type] || 'info',
        title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Notice',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
