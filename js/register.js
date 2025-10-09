/**
 * Registration Form - Complete Client-Side Logic
 * Handles validation, country selection, and AJAX submission
 */

// Country data with phone codes (46 countries)
const countries = [
    { name: "Ghana", code: "+233", flag: "🇬🇭" },
    { name: "Nigeria", code: "+234", flag: "🇳🇬" },
    { name: "Kenya", code: "+254", flag: "🇰🇪" },
    { name: "South Africa", code: "+27", flag: "🇿🇦" },
    { name: "Egypt", code: "+20", flag: "🇪🇬" },
    { name: "Tanzania", code: "+255", flag: "🇹🇿" },
    { name: "Uganda", code: "+256", flag: "🇺🇬" },
    { name: "Ethiopia", code: "+251", flag: "🇪🇹" },
    { name: "Ivory Coast", code: "+225", flag: "🇨🇮" },
    { name: "Senegal", code: "+221", flag: "🇸🇳" },
    { name: "Cameroon", code: "+237", flag: "🇨🇲" },
    { name: "Zimbabwe", code: "+263", flag: "🇿🇼" },
    { name: "Rwanda", code: "+250", flag: "🇷🇼" },
    { name: "Botswana", code: "+267", flag: "🇧🇼" },
    { name: "Namibia", code: "+264", flag: "🇳🇦" },
    { name: "Mozambique", code: "+258", flag: "🇲🇿" },
    { name: "Zambia", code: "+260", flag: "🇿🇲" },
    { name: "Malawi", code: "+265", flag: "🇲🇼" },
    { name: "Benin", code: "+229", flag: "🇧🇯" },
    { name: "Togo", code: "+228", flag: "🇹🇬" },
    { name: "Burkina Faso", code: "+226", flag: "🇧🇫" },
    { name: "Mali", code: "+223", flag: "🇲🇱" },
    { name: "Niger", code: "+227", flag: "🇳🇪" },
    { name: "Guinea", code: "+224", flag: "🇬🇳" },
    { name: "Sierra Leone", code: "+232", flag: "🇸🇱" },
    { name: "Liberia", code: "+231", flag: "🇱🇷" },
    { name: "Mauritius", code: "+230", flag: "🇲🇺" },
    { name: "Angola", code: "+244", flag: "🇦🇴" },
    { name: "Congo", code: "+242", flag: "🇨🇬" },
    { name: "Democratic Republic of Congo", code: "+243", flag: "🇨🇩" },
    { name: "Algeria", code: "+213", flag: "🇩🇿" },
    { name: "Morocco", code: "+212", flag: "🇲🇦" },
    { name: "Tunisia", code: "+216", flag: "🇹🇳" },
    { name: "Libya", code: "+218", flag: "🇱🇾" },
    { name: "Sudan", code: "+249", flag: "🇸🇩" },
    { name: "Somalia", code: "+252", flag: "🇸🇴" },
    { name: "United States", code: "+1", flag: "🇺🇸" },
    { name: "United Kingdom", code: "+44", flag: "🇬🇧" },
    { name: "Canada", code: "+1", flag: "🇨🇦" },
    { name: "Germany", code: "+49", flag: "🇩🇪" },
    { name: "France", code: "+33", flag: "🇫🇷" },
    { name: "China", code: "+86", flag: "🇨🇳" },
    { name: "India", code: "+91", flag: "🇮🇳" },
    { name: "Brazil", code: "+55", flag: "🇧🇷" },
    { name: "Australia", code: "+61", flag: "🇦🇺" },
    { name: "Japan", code: "+81", flag: "🇯🇵" }
];

let selectedCountry = null;

$(document).ready(function() {
    console.log("✓ register.js loaded");
    
    // Initialize all functionality
    initializeCountrySelection();
    initializeFormValidation();
    initializeFormSubmission();
    initializePhoneProtection(); // ✅ NEW: Protect phone code from deletion
});

/**
 * Initialize country selection dropdown
 */
function initializeCountrySelection() {
    // Show countries on focus
    $('#country').on('focus', function() {
        showCountrySuggestions('');
    });

    // Filter countries as user types
    $('#country').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        showCountrySuggestions(searchTerm);
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.country-dropdown').length) {
            $('#country-suggestions').removeClass('show');
        }
    });

    // ✅ FIXED: Validate country on blur AND update phone code
    $('#country').on('blur', function() {
        setTimeout(() => {
            const enteredCountry = $(this).val().trim();
            
            if (enteredCountry) {
                const isValidCountry = countries.some(country => 
                    country.name.toLowerCase() === enteredCountry.toLowerCase()
                );

                if (!isValidCountry) {
                    $(this).addClass('error');
                    $(this).siblings('.field-error').remove();
                    $(this).after('<div class="field-error"><i class="fas fa-exclamation-circle"></i> Please select a valid country from the list</div>');
                    selectedCountry = null;
                } else {
                    const matchedCountry = countries.find(country => 
                        country.name.toLowerCase() === enteredCountry.toLowerCase()
                    );
                    if (matchedCountry) {
                        selectCountry(matchedCountry); // ✅ This now updates phone code
                    }
                }
            }
        }, 200);
    });

    // Ensure phone starts with country code
    $('#phone_number').on('focus', function() {
        if (selectedCountry && !$(this).val().startsWith(selectedCountry.code)) {
            $(this).val(selectedCountry.code + ' ');
        }
    });
}

/**
 * ✅ NEW: Protect phone code from being deleted
 */
function initializePhoneProtection() {
    const $phoneInput = $('#phone_number');
    
    // Prevent deleting country code
    $phoneInput.on('keydown', function(e) {
        if (!selectedCountry) return;
        
        const value = $(this).val();
        const cursorPosition = this.selectionStart;
        const codeLength = selectedCountry.code.length;
        
        // Prevent backspace/delete if cursor is within country code area
        if ((e.key === 'Backspace' || e.key === 'Delete') && cursorPosition <= codeLength + 1) {
            e.preventDefault();
            return false;
        }
    });
    
    // Ensure country code stays at the beginning
    $phoneInput.on('input', function() {
        if (!selectedCountry) return;
        
        const value = $(this).val();
        const code = selectedCountry.code;
        
        // If country code is missing, add it back
        if (!value.startsWith(code)) {
            const numberPart = value.replace(/[^\d\s]/g, '').trim();
            $(this).val(code + ' ' + numberPart);
        }
    });
}

/**
 * Show country suggestions
 */
function showCountrySuggestions(searchTerm) {
    const $suggestions = $('#country-suggestions');
    $suggestions.empty();

    const filteredCountries = countries.filter(country => 
        country.name.toLowerCase().includes(searchTerm)
    );

    if (filteredCountries.length === 0) {
        $suggestions.html('<div class="country-suggestion-item" style="color: #999;">No countries found</div>');
        $suggestions.addClass('show');
        return;
    }

    filteredCountries.forEach(country => {
        const $item = $(`
            <div class="country-suggestion-item" data-country="${country.name}" data-code="${country.code}">
                <span style="margin-right: 8px;">${country.flag}</span>
                <span style="font-weight: 500;">${country.name}</span>
                <span style="color: #999; margin-left: 8px; font-size: 0.9rem;">${country.code}</span>
            </div>
        `);

        $item.on('click', function() {
            selectCountry(country);
        });

        $suggestions.append($item);
    });

    $suggestions.addClass('show');
}

/**
 * ✅ ENHANCED: Select country and update phone code
 */
function selectCountry(country) {
    console.log('→ Country selected:', country.name, country.code);
    
    selectedCountry = country;
    
    // Set country name
    $('#country').val(country.name);
    
    // ✅ FIXED: Update phone with NEW country code
    const $phoneInput = $('#phone_number');
    const currentPhone = $phoneInput.val().trim();
    
    // Remove OLD country code (any code starting with +)
    const phoneWithoutCode = currentPhone.replace(/^\+\d+\s*/, '').trim();
    
    // Add NEW country code
    $phoneInput.val(country.code + ' ' + phoneWithoutCode);
    
    console.log('✓ Phone updated:', country.code + ' ' + phoneWithoutCode);
    
    // Hide suggestions
    $('#country-suggestions').removeClass('show');
    
    // Clear errors
    $('#country').removeClass('error');
    $('#country').siblings('.field-error').remove();
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Real-time validation on blur
    $('#register-form input').on('blur', function() {
        validateField($(this));
    });

    // Email availability check
    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && isValidEmail(email)) {
            checkEmailAvailability(email);
        }
    });

    // Clear errors on input
    $('#register-form input').on('input', function() {
        clearFieldError($(this));
    });
}

/**
 * Initialize form submission
 */
function initializeFormSubmission() {
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

        // Validate all fields
        if (!validateAllFields()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fix the errors below and try again.',
                confirmButtonColor: '#667eea'
            });
            return;
        }

        // Show loading state
        const submitBtn = $('#register-form button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).addClass('loading').html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');

        // Submit form via AJAX
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
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#register-form')[0].reset();
                            window.location.href = 'login.php';
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
                submitBtn.prop('disabled', false).removeClass('loading').html(originalText);
            }
        });
    });
}

/**
 * Validation patterns
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
 * Validate individual field
 */
function validateField($field) {
    const fieldName = $field.attr('name');
    const value = $field.val().trim();
    const patterns = getValidationPatterns();
    
    clearFieldError($field);
    
    if ($field.prop('required') && !value) {
        showFieldError($field, `${formatFieldName(fieldName)} is required`);
        return false;
    }
    
    if (!value && !$field.prop('required')) {
        return true;
    }
    
    switch (fieldName) {
        case 'name':
            if (!patterns.name.test(value)) {
                showFieldError($field, 'Name should contain only letters and spaces (2-100 characters)');
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
 * Validate all required fields
 */
function validateAllFields() {
    let isValid = true;
    const requiredFields = ['name', 'email', 'password', 'country', 'city', 'phone_number'];
    
    requiredFields.forEach(function(fieldName) {
        const $field = $(`#${fieldName}`);
        if ($field.length && !validateField($field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Check if email format is valid
 */
function isValidEmail(email) {
    return getValidationPatterns().email.test(email);
}

/**
 * Check email availability
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
 * Show field error
 */
function showFieldError($field, message) {
    $field.addClass('error');
    $field.siblings('.field-error').remove();
    $field.after(`<div class="field-error"><i class="fas fa-exclamation-circle"></i> ${message}</div>`);
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

console.log("✓ register.js initialized successfully");