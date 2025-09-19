/**
 * Login Form Validation and Submission (js/login.js)
 * Validates login form using regex and asynchronously invokes login_customer_action
 */

$(document).ready(function() {
    // Real-time field validation
    $('#login-form input').on('blur', function() {
        validateField($(this));
    });

    // Clear field errors on input
    $('#login-form input').on('input', function() {
        clearFieldError($(this));
    });

    // Form submission handler - asynchronously invoke login_customer_action script
    $('#login-form').submit(function(e) {
        e.preventDefault();

        // Get form values
        const email = $('#email').val().trim().toLowerCase();
        const password = $('#password').val();

        // Validate all fields with comprehensive validation
        if (!validateAllFields()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fix the errors below and try again.',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        // Show loading state - CSS/JS loading feature when login button is clicked
        const submitBtn = $('#login-form button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Signing In...');

        // Asynchronously invoke login_customer_action script
        $.ajax({
            url: '../actions/login_customer_action.php',
            type: 'POST',
            dataType: 'json',
            data: {
                email: email,
                password: password
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: response.message,
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Direct user to landing page (index.php) on successful login
                            window.location.href = response.redirect || '../index.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Login error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'An unexpected error occurred. Please try again later.',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    /**
     * Comprehensive validation patterns using regex for form validation
     */
    function getValidationPatterns() {
        return {
            email: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
            password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{6,150}$/ // Must have uppercase, lowercase, number, 6-150 chars
        };
    }

    /**
     * Validate individual field using regex with comprehensive checks
     * Validation checks for types, length, format, and security
     */
    function validateField($field) {
        const fieldName = $field.attr('name');
        const value = $field.val().trim();
        const rawValue = $field.val(); // Don't trim password for accurate validation
        const patterns = getValidationPatterns();
        
        // Clear previous errors
        clearFieldError($field);
        
        // Check if field is required and empty
        if ($field.prop('required') && !value) {
            showFieldError($field, `${formatFieldName(fieldName)} is required`);
            return false;
        }
        
        // Skip pattern validation if field is empty and not required
        if (!value && !$field.prop('required')) {
            return true;
        }
        
        // Comprehensive regex pattern validation for types
        switch (fieldName) {
            case 'email':
                // Check email format with comprehensive regex
                if (!patterns.email.test(value)) {
                    showFieldError($field, 'Please enter a valid email address');
                    return false;
                }
                // Check length against database constraint
                if (value.length > 50) {
                    showFieldError($field, 'Email is too long (max 50 characters)');
                    return false;
                }
                // Check for common email security issues
                if (value.includes('..') || value.startsWith('.') || value.endsWith('.')) {
                    showFieldError($field, 'Email format is invalid');
                    return false;
                }
                break;
                
            case 'password':
                // Use raw value for password validation (don't trim)
                const passwordValue = rawValue;
                
                // Check if password is empty
                if (passwordValue.length === 0) {
                    showFieldError($field, 'Password is required');
                    return false;
                }
                
                // Check minimum length
                if (passwordValue.length < 6) {
                    showFieldError($field, 'Password must be at least 6 characters long');
                    return false;
                }
                
                // Check maximum length against database constraint
                if (passwordValue.length > 150) {
                    showFieldError($field, 'Password is too long (max 150 characters)');
                    return false;
                }
                
                // Check password complexity with regex
                if (!patterns.password.test(passwordValue)) {
                    showFieldError($field, 'Password must contain at least one uppercase letter, one lowercase letter, and one number');
                    return false;
                }
                
                // Check for common weak passwords
                const weakPasswords = ['password', '123456', 'password123', 'admin', 'letmein', 'welcome', 'monkey', '1234567890'];
                if (weakPasswords.includes(passwordValue.toLowerCase())) {
                    showFieldError($field, 'Password is too common, please choose a stronger password');
                    return false;
                }
                
                // Check for whitespace at beginning or end
                if (passwordValue !== passwordValue.trim()) {
                    showFieldError($field, 'Password cannot start or end with spaces');
                    return false;
                }
                
                break;
        }
        
        return true;
    }

    /**
     * Validate all required fields with comprehensive checks
     */
    function validateAllFields() {
        let isValid = true;
        const requiredFields = ['email', 'password'];
        
        // Validate each required field
        requiredFields.forEach(function(fieldName) {
            const $field = $(`#${fieldName}`);
            if ($field.length && !validateField($field)) {
                isValid = false;
            }
        });

        // Additional cross-field validation
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        // Check for SQL injection attempts
        const sqlPatterns = /('|(\\')|(;)|(\\)|(\/\*)|(\\*\/)|(xp_)|(sp_)|(exec)|(execute)|(select)|(insert)|(update)|(delete)|(drop)|(create)|(alter)|(union)|(script))/i;
        
        if (sqlPatterns.test(email) || sqlPatterns.test(password)) {
            showFieldError($('#email'), 'Invalid characters detected');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Show field-specific error
     */
    function showFieldError($field, message) {
        $field.addClass('error');
        
        // Remove existing error message
        $field.siblings('.field-error').remove();
        
        // Add new error message
        $field.after(`<div class="field-error">${message}</div>`);
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        $field.removeClass('error');
        $field.siblings('.field-error').remove();
    }

    /**
     * Format field name for display
     */
    function formatFieldName(fieldName) {
        return fieldName.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
            return l.toUpperCase();
        });
    }
});