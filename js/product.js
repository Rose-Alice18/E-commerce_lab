/**
 * Product Management JavaScript
 * Handles validation and async operations for product add/edit forms
 * As per PDF Week 6 Activity - Part 2 requirements
 */

// Validation helper functions
const ValidationUtils = {
    /**
     * Check if value is empty
     */
    isEmpty: function(value) {
        return !value || value.trim() === '';
    },

    /**
     * Check if value is a valid number
     */
    isValidNumber: function(value) {
        return !isNaN(value) && isFinite(value) && value >= 0;
    },

    /**
     * Check if value is a valid positive number
     */
    isPositiveNumber: function(value) {
        return this.isValidNumber(value) && parseFloat(value) > 0;
    },

    /**
     * Check if value is a valid integer
     */
    isInteger: function(value) {
        return Number.isInteger(Number(value)) && Number(value) >= 0;
    },

    /**
     * Validate file type (images only)
     */
    isValidImageType: function(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        return validTypes.includes(file.type);
    },

    /**
     * Validate file size (max 5MB)
     */
    isValidFileSize: function(file, maxSizeMB = 5) {
        const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
        return file.size <= maxSize;
    },

    /**
     * Show field error
     */
    showFieldError: function(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('error');

            // Remove existing error message
            const existingError = field.parentElement.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }

            // Add error message
            const errorDiv = document.createElement('small');
            errorDiv.className = 'error-message';
            errorDiv.style.color = '#ef4444';
            errorDiv.style.fontSize = '0.85rem';
            errorDiv.style.marginTop = '0.25rem';
            errorDiv.style.display = 'block';
            errorDiv.textContent = message;
            field.parentElement.appendChild(errorDiv);
        }
    },

    /**
     * Clear field error
     */
    clearFieldError: function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('error');
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    },

    /**
     * Clear all errors
     */
    clearAllErrors: function() {
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }
};

// Product Form Validator
const ProductValidator = {
    /**
     * Validate product title
     */
    validateTitle: function(title) {
        if (ValidationUtils.isEmpty(title)) {
            return { valid: false, message: 'Product name is required' };
        }
        if (title.length < 3) {
            return { valid: false, message: 'Product name must be at least 3 characters' };
        }
        if (title.length > 200) {
            return { valid: false, message: 'Product name must not exceed 200 characters' };
        }
        return { valid: true };
    },

    /**
     * Validate category selection
     */
    validateCategory: function(categoryId) {
        if (ValidationUtils.isEmpty(categoryId) || categoryId === '0' || categoryId === 0) {
            return { valid: false, message: 'Please select a category' };
        }
        if (!ValidationUtils.isInteger(categoryId)) {
            return { valid: false, message: 'Invalid category selected' };
        }
        return { valid: true };
    },

    /**
     * Validate brand selection
     */
    validateBrand: function(brandId) {
        if (ValidationUtils.isEmpty(brandId) || brandId === '0' || brandId === 0) {
            return { valid: false, message: 'Please select a brand' };
        }
        if (!ValidationUtils.isInteger(brandId)) {
            return { valid: false, message: 'Invalid brand selected' };
        }
        return { valid: true };
    },

    /**
     * Validate price
     */
    validatePrice: function(price) {
        if (ValidationUtils.isEmpty(price)) {
            return { valid: false, message: 'Price is required' };
        }
        if (!ValidationUtils.isValidNumber(price)) {
            return { valid: false, message: 'Price must be a valid number' };
        }
        if (!ValidationUtils.isPositiveNumber(price)) {
            return { valid: false, message: 'Price must be greater than 0' };
        }
        if (parseFloat(price) > 999999.99) {
            return { valid: false, message: 'Price is too high' };
        }
        return { valid: true };
    },

    /**
     * Validate stock quantity
     */
    validateStock: function(stock) {
        if (ValidationUtils.isEmpty(stock)) {
            return { valid: false, message: 'Stock quantity is required' };
        }
        if (!ValidationUtils.isInteger(stock)) {
            return { valid: false, message: 'Stock must be a whole number' };
        }
        if (parseInt(stock) < 0) {
            return { valid: false, message: 'Stock cannot be negative' };
        }
        if (parseInt(stock) > 999999) {
            return { valid: false, message: 'Stock quantity is too high' };
        }
        return { valid: true };
    },

    /**
     * Validate product image
     */
    validateImage: function(fileInput) {
        // Image is optional, so if no file selected, it's valid
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            return { valid: true };
        }

        const file = fileInput.files[0];

        // Check file type
        if (!ValidationUtils.isValidImageType(file)) {
            return { valid: false, message: 'Please select a valid image file (JPG, JPEG, PNG, GIF)' };
        }

        // Check file size (5MB max)
        if (!ValidationUtils.isValidFileSize(file, 5)) {
            return { valid: false, message: 'Image size must not exceed 5MB' };
        }

        return { valid: true };
    },

    /**
     * Validate entire product form
     */
    validateProductForm: function(formData) {
        ValidationUtils.clearAllErrors();
        let isValid = true;
        const errors = [];

        // Validate title
        const titleResult = this.validateTitle(formData.get('product_title'));
        if (!titleResult.valid) {
            ValidationUtils.showFieldError('productTitle', titleResult.message);
            errors.push(titleResult.message);
            isValid = false;
        }

        // Validate category
        const categoryResult = this.validateCategory(formData.get('product_cat'));
        if (!categoryResult.valid) {
            ValidationUtils.showFieldError('productCategory', categoryResult.message);
            errors.push(categoryResult.message);
            isValid = false;
        }

        // Validate brand
        const brandResult = this.validateBrand(formData.get('product_brand'));
        if (!brandResult.valid) {
            ValidationUtils.showFieldError('productBrand', brandResult.message);
            errors.push(brandResult.message);
            isValid = false;
        }

        // Validate price
        const priceResult = this.validatePrice(formData.get('product_price'));
        if (!priceResult.valid) {
            ValidationUtils.showFieldError('productPrice', priceResult.message);
            errors.push(priceResult.message);
            isValid = false;
        }

        // Validate stock
        const stockResult = this.validateStock(formData.get('product_stock'));
        if (!stockResult.valid) {
            ValidationUtils.showFieldError('productStock', stockResult.message);
            errors.push(stockResult.message);
            isValid = false;
        }

        // Validate image (if present)
        const imageInput = document.getElementById('productImage');
        if (imageInput) {
            const imageResult = this.validateImage(imageInput);
            if (!imageResult.valid) {
                ValidationUtils.showFieldError('productImage', imageResult.message);
                errors.push(imageResult.message);
                isValid = false;
            }
        }

        return { valid: isValid, errors: errors };
    }
};

// Product API Handler
const ProductAPI = {
    /**
     * Add a new product
     */
    addProduct: async function(formData) {
        try {
            const response = await fetch('../actions/add_product_action.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Add product error:', error);
            return {
                success: false,
                message: 'Network error: Could not connect to server'
            };
        }
    },

    /**
     * Update an existing product
     */
    updateProduct: async function(formData) {
        try {
            const response = await fetch('../actions/update_product_action.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Update product error:', error);
            return {
                success: false,
                message: 'Network error: Could not connect to server'
            };
        }
    },

    /**
     * Delete a product
     */
    deleteProduct: async function(productId) {
        try {
            const response = await fetch('../actions/delete_product_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Delete product error:', error);
            return {
                success: false,
                message: 'Network error: Could not connect to server'
            };
        }
    }
};

// Initialize product form handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle Add Product Form
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Validate form
            const validation = ProductValidator.validateProductForm(formData);
            if (!validation.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: validation.errors.join('<br>'),
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Adding Product...',
                html: 'Please wait while we add your product',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            const result = await ProductAPI.addProduct(formData);

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Product added successfully',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    // Redirect to products page
                    window.location.href = 'my_products.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to add product',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        // Clear errors on input
        const formInputs = addProductForm.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                ValidationUtils.clearFieldError(this.id);
            });
            input.addEventListener('change', function() {
                ValidationUtils.clearFieldError(this.id);
            });
        });
    }

    // Handle Edit Product Form
    const editProductForm = document.getElementById('editProductForm');
    if (editProductForm) {
        editProductForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Validate form
            const validation = ProductValidator.validateProductForm(formData);
            if (!validation.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: validation.errors.join('<br>'),
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Updating Product...',
                html: 'Please wait while we update your product',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            const result = await ProductAPI.updateProduct(formData);

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Product updated successfully',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    // Redirect to products page
                    window.location.href = 'my_products.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to update product',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        // Clear errors on input
        const formInputs = editProductForm.querySelectorAll('input, select, textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                ValidationUtils.clearFieldError(this.id);
            });
            input.addEventListener('change', function() {
                ValidationUtils.clearFieldError(this.id);
            });
        });
    }
});

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ValidationUtils,
        ProductValidator,
        ProductAPI
    };
}
