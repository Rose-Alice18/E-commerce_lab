/**
 * Profile Page JavaScript
 * Handles tab switching, form submissions, password validation, and avatar upload
 * Following MVC approach - This is the View Controller
 */

// ========================================
// TAB SWITCHING
// ========================================

/**
 * Initialize tab functionality
 */
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            switchTab(targetTab);
        });
    });
}

/**
 * Switch to a specific tab
 * @param {string} tabName - The name of the tab to switch to
 */
function switchTab(tabName) {
    // Remove active class from all buttons and contents
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Add active class to selected button and content
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    const activeContent = document.getElementById(`${tabName}-tab`);

    if (activeButton && activeContent) {
        activeButton.classList.add('active');
        activeContent.classList.add('active');

        // Smooth scroll to top of tabs
        activeContent.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// ========================================
// AVATAR UPLOAD
// ========================================

/**
 * Initialize avatar upload functionality
 */
function initializeAvatarUpload() {
    const avatarInput = document.getElementById('avatarUploadInput');
    const avatarPreview = document.getElementById('profileAvatar');

    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validate file type
                if (!file.type.match('image.*')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File',
                        text: 'Please select an image file',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Image must be less than 5MB',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }

                // Preview and upload
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update preview
                    avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Profile">`;

                    // Upload to server
                    uploadAvatar(file);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Upload avatar to server
 * @param {File} file - The image file to upload
 */
function uploadAvatar(file) {
    const formData = new FormData();
    formData.append('profile_image', file);

    Swal.fire({
        title: 'Uploading...',
        html: 'Please wait while we update your profile picture',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../actions/update_profile_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Profile picture updated successfully',
                confirmButtonColor: '#667eea'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: data.message || 'Failed to update profile picture',
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while uploading',
            confirmButtonColor: '#ef4444'
        });
    });
}

// ========================================
// EDIT PROFILE FORM
// ========================================

/**
 * Initialize edit profile form
 */
function initializeEditProfileForm() {
    const form = document.getElementById('editProfileForm');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEditProfileSubmit(this);
        });
    }
}

/**
 * Handle edit profile form submission
 * @param {HTMLFormElement} form - The form element
 */
function handleEditProfileSubmit(form) {
    const formData = new FormData(form);

    // Show loading
    Swal.fire({
        title: 'Updating Profile...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../actions/update_profile_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#667eea'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: data.message,
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while updating your profile',
            confirmButtonColor: '#ef4444'
        });
    });
}

// ========================================
// PASSWORD CHANGE FORM
// ========================================

/**
 * Initialize change password form
 */
function initializePasswordForm() {
    const form = document.getElementById('changePasswordForm');
    const newPasswordInput = document.getElementById('newPassword');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handlePasswordChangeSubmit(this);
        });
    }

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordRequirements(this.value);
        });
    }
}

/**
 * Handle password change form submission
 * @param {HTMLFormElement} form - The form element
 */
function handlePasswordChangeSubmit(form) {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validate passwords match
    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Passwords Don\'t Match',
            text: 'New password and confirmation must match',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    // Validate password strength
    if (!isPasswordStrong(newPassword)) {
        Swal.fire({
            icon: 'warning',
            title: 'Weak Password',
            text: 'Please choose a stronger password that meets all requirements',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    // Check if new password is same as current
    if (currentPassword === newPassword) {
        Swal.fire({
            icon: 'warning',
            title: 'Same Password',
            text: 'New password must be different from current password',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    const formData = new FormData(form);

    // Show loading
    Swal.fire({
        title: 'Updating Password...',
        html: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../actions/change_password_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#667eea'
            }).then(() => {
                form.reset();
                switchTab('overview');
                // Clear password strength indicator
                document.getElementById('passwordStrength').className = 'password-strength';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: data.message,
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while changing your password',
            confirmButtonColor: '#ef4444'
        });
    });
}

// ========================================
// PASSWORD STRENGTH & VALIDATION
// ========================================

/**
 * Check password strength and update indicator
 * @param {string} password - The password to check
 */
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');

    if (!strengthIndicator) return;

    let strength = 0;

    // Check length
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;

    // Check for uppercase
    if (/[A-Z]/.test(password)) strength++;

    // Check for lowercase
    if (/[a-z]/.test(password)) strength++;

    // Check for numbers
    if (/[0-9]/.test(password)) strength++;

    // Check for special characters
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    // Update indicator
    strengthIndicator.className = 'password-strength';

    if (strength <= 2) {
        strengthIndicator.classList.add('weak');
    } else if (strength <= 4) {
        strengthIndicator.classList.add('medium');
    } else {
        strengthIndicator.classList.add('strong');
    }
}

/**
 * Validate password against requirements
 * @param {string} password - The password to validate
 */
function validatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };

    // Update UI for each requirement
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(`req-${req}`);
        if (element) {
            if (requirements[req]) {
                element.classList.add('met');
                element.querySelector('i').className = 'fas fa-check-circle';
            } else {
                element.classList.remove('met');
                element.querySelector('i').className = 'fas fa-circle';
            }
        }
    });
}

/**
 * Check if password meets all requirements
 * @param {string} password - The password to check
 * @returns {boolean} True if password is strong
 */
function isPasswordStrong(password) {
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[a-z]/.test(password) &&
           /[0-9]/.test(password) &&
           /[^A-Za-z0-9]/.test(password);
}

// ========================================
// PASSWORD VISIBILITY TOGGLE
// ========================================

/**
 * Toggle password visibility
 * @param {string} inputId - The ID of the password input
 * @param {HTMLElement} button - The toggle button element
 */
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================

/**
 * Initialize keyboard shortcuts
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Alt+1 = Overview tab
        if (e.altKey && e.key === '1') {
            e.preventDefault();
            switchTab('overview');
        }

        // Alt+2 = Edit Profile tab
        if (e.altKey && e.key === '2') {
            e.preventDefault();
            switchTab('edit-profile');
        }

        // Alt+3 = Security tab
        if (e.altKey && e.key === '3') {
            e.preventDefault();
            switchTab('security');
        }

        // Alt+4 = Pharmacy Info tab (if exists)
        if (e.altKey && e.key === '4') {
            e.preventDefault();
            const pharmTab = document.querySelector('[data-tab="pharmacy-info"]');
            if (pharmTab) {
                switchTab('pharmacy-info');
            }
        }

        // Escape = Switch to overview
        if (e.key === 'Escape') {
            switchTab('overview');
        }
    });
}

// ========================================
// AUTO-SAVE FUNCTIONALITY
// ========================================

/**
 * Auto-save form data to localStorage
 */
function initializeAutoSave() {
    const editForm = document.getElementById('editProfileForm');

    if (editForm) {
        const inputs = editForm.querySelectorAll('input, textarea');

        inputs.forEach(input => {
            // Load saved data
            const savedValue = localStorage.getItem(`profile_${input.name}`);
            if (savedValue && input.value === '') {
                input.value = savedValue;
            }

            // Save on change
            input.addEventListener('input', function() {
                localStorage.setItem(`profile_${input.name}`, input.value);
            });
        });

        // Clear saved data on successful submit
        editForm.addEventListener('submit', function() {
            inputs.forEach(input => {
                localStorage.removeItem(`profile_${input.name}`);
            });
        });
    }
}

// ========================================
// INITIALIZATION
// ========================================

/**
 * Initialize all profile page functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page loaded - Initializing...');

    // Initialize all features
    initializeTabs();
    initializeAvatarUpload();
    initializeEditProfileForm();
    initializePasswordForm();
    initializeKeyboardShortcuts();
    initializeAutoSave();

    console.log('Profile page initialized successfully');

    // Show welcome message for first-time visitors
    if (!sessionStorage.getItem('profileVisited')) {
        sessionStorage.setItem('profileVisited', 'true');

        // Optional: Show tips for keyboard shortcuts
        // Swal.fire({
        //     icon: 'info',
        //     title: 'Tip!',
        //     html: 'Use <kbd>Alt+1</kbd>, <kbd>Alt+2</kbd>, <kbd>Alt+3</kbd> to quickly switch between tabs',
        //     confirmButtonColor: '#667eea',
        //     timer: 3000
        // });
    }
});

// Make switchTab function global so it can be called from onclick handlers
window.switchTab = switchTab;
window.togglePasswordVisibility = togglePasswordVisibility;

// ========================================
// DELIVERY SETTINGS (Pharmacy Only)
// ========================================

/**
 * Initialize delivery settings functionality
 */
function initializeDeliverySettings() {
    const deliveryToggle = document.getElementById('offersDelivery');
    const deliveryDetails = document.getElementById('deliveryDetails');
    const deliveryForm = document.getElementById('deliverySettingsForm');

    // Toggle delivery details visibility
    if (deliveryToggle && deliveryDetails) {
        deliveryToggle.addEventListener('change', function() {
            if (this.checked) {
                deliveryDetails.style.display = 'block';
            } else {
                deliveryDetails.style.display = 'none';
            }
        });
    }

    // Handle delivery settings form submission
    if (deliveryForm) {
        deliveryForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                Swal.fire({
                    title: 'Updating...',
                    text: 'Saving your delivery settings',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch('../actions/update_delivery_settings.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonColor: '#667eea',
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message,
                        confirmButtonColor: '#667eea'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating delivery settings',
                    confirmButtonColor: '#667eea'
                });
            }
        });
    }
}

// Initialize delivery settings when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDeliverySettings();
});
