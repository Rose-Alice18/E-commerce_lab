/**
 * Registration Form Validation and Submission (js/register.js)
 * Validates registration form using regex and asynchronously invokes register_user_action
 */

$(document).ready(function() {
    // Real-time field validation
    $('#register-form input').on('blur', function() {
        validateField($(this));
    });

    // Real-time email availability check - Check if email is available before adding new customer
    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && isValidEmail(email)) {
            checkEmailAvailability(email);
        }
    });

    // Clear field errors on input
    $('#register-form input').on('input', function() {
        clearFieldError($(this));
    });

    // Form submission handler - asynchronously invoke register_user_action script
    $('#register-form').submit(function(e) {
        e.preventDefault();

        // Get form values
        const name = $('#name').val().trim();
        const email = $('#email').val().trim().toLowerCase();
        const password = $('#password').val();
        const country = $('#country').val().trim();
        const city = $('#city').val().trim();
        const phone_number = $('#phone_number').val().trim();
        const role = $('input[name="role"]:checked').val() || 2;

        // Validate all fields using regex
        if (!validateAllFields()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fix the errors below and try again.',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        // Show loading state - CSS/JS loading feature when register button is clicked
        const submitBtn = $('#register-form button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');

        // Asynchronously invoke register_user_action script
        $.ajax({
            url: '../actions/register_user_action.php',
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
                email: email,
                password: password,
                country: country,
                city: city,
                phone_number: phone_number,
                role: role
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: response.message,
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear form and direct user to login page on successful registration
                            $('#register-form')[0].reset();
                            window.location.href = '../login/login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Registration error:', error);
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
     * Validation patterns using regex for form validation
     */
    function getValidationPatterns() {
        return {
            name: /^[a-zA-Z\s]{2,100}$/,
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{6,}$/,
            country: /^[a-zA-Z\s]{2,30}$/,
            city: /^[a-zA-Z\s]{2,30}$/,
            phone_number: /^[\+]?[0-9\s\-\(\)]{10,15}$/
        };
    }

    /**
     * Validate individual field using regex
     * Validation checks for types: email, phone number, etc.
     * Check field length and validate accordingly
     */
    function validateField($field) {
        const fieldName = $field.attr('name');
        const value = $field.val().trim();
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
        
        // Regex pattern validation
        switch (fieldName) {
            case 'name':
                if (!patterns.name.test(value)) {
                    showFieldError($field, 'Name should contain only letters and spaces (2-50 characters)');
                    return false;
                }
                break;
                
            case 'email':
                if (!patterns.email.test(value)) {
                    showFieldError($field, 'Please enter a valid email address');
                    return false;
                }
                if (value.length > 50) {
                    showFieldError($field, 'Email is too long (max 50 characters)');
                    return false;
                }
                break;
                
            case 'password':
                if (!patterns.password.test(value)) {
                    showFieldError($field, 'Password must be at least 6 characters with uppercase, lowercase, and number');
                    return false;
                }
                break;
                
            case 'confirm_password':
                // Remove this case since we don't have confirm_password field
                break;
                
            case 'country':
                if (!patterns.country.test(value)) {
                    showFieldError($field, 'Country should contain only letters and spaces (2-30 characters)');
                    return false;
                }
                break;
                
            case 'city':
                if (!patterns.city.test(value)) {
                    showFieldError($field, 'City should contain only letters and spaces (2-30 characters)');
                    return false;
                }
                break;
                
            case 'phone_number':
                if (!patterns.phone_number.test(value)) {
                    showFieldError($field, 'Please enter a valid contact number (10-15 digits)');
                    return false;
                }
                break;
        }
        
        return true;
    }

    /**
     * Validate all required fields using regex
     * All fields are required in the database
     */
    function validateAllFields() {
        let isValid = true;
        const requiredFields = ['name', 'email', 'password', 'country', 'city', 'phone_number'];
        
        // Validate each required field
        requiredFields.forEach(function(fieldName) {
            const $field = $(`#${fieldName}`);
            if ($field.length && !validateField($field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Check if email format is valid using regex
     */
    function isValidEmail(email) {
        return getValidationPatterns().email.test(email);
    }

    /**
     * Check email availability before adding new customer
     */
    function checkEmailAvailability(email) {
        $.ajax({
            url: '../actions/check_email_action.php',
            type: 'POST',
            dataType: 'json',
            data: { email: email },
            success: function(response) {
                if (response.status === 'taken') {
                    showFieldError($('#email'), 'Email is already registered');
                }
            },
            error: function() {
                console.log('Error checking email availability');
            }
        });
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

console.log("test");