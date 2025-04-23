<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Gymaster</title>
    <!-- Add Google Fonts - Poppins with multiple weights -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/admin-styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            dark: '#081738',
                            light: '#5C6C90'
                        },
                        secondary: '#647590',
                        tertiary: '#A5B3C9',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Dialog animation - these styles are not in admin-styles.css */
        .modal-transition {
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }
        
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
        }
        
        /* Toast notification - these styles are not in admin-styles.css */
        .toast-notification {
            animation: slideIn 0.3s ease-out forwards;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .toast-exit {
            animation: slideOut 0.3s ease-in forwards;
        }
        
        /* Form input focus style */
        .form-input:focus {
            border-color: #5C6C90;
            box-shadow: 0 0 0 3px rgba(92, 108, 144, 0.2);
        }
    </style>
</head>
<body class="font-poppins bg-gray-50">
    <!-- Main content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="admin-header shadow-sm mb-3">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex justify-between items-center h-16">
                    <!-- Page Title with back button -->
                    <div class="flex items-center">
                        <a href="javascript:void(0)" onclick="handleBackButton()" class="mr-3 text-gray-500 hover:text-primary-dark">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-xl font-semibold text-primary-dark">Edit Profile</h1>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-700">John Doe</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-white">
                            <i class="fas fa-user text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container mx-auto px-4 py-4 max-w-4xl">
            <!-- Form Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-primary-dark mb-1">Personal Information</h2>
                    <p class="text-gray-500 text-sm">Update your personal information and account credentials.</p>
                </div>
                
                <form id="profileForm" class="space-y-6">
                    <!-- Personal Details Section -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="firstName" name="USER_FNAME" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none" value="John" required>
                            </div>
                            
                            <!-- Last Name -->
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" id="lastName" name="USER_LNAME" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none" value="Doe" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <div class="relative">
                                    <input type="text" id="userType" name="USER_TYPE" class="form-input w-full px-4 py-2 border border-gray-200 rounded-md bg-gray-100 text-gray-500 focus:outline-none cursor-not-allowed" value="admin-1" readonly>
                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Type (Read-only) - Enhanced styling to look more untouchable -->
                            <div>
                                <label for="userType" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                                <div class="relative">
                                    <input type="text" id="userType" name="USER_TYPE" class="form-input w-full px-4 py-2 border border-gray-200 rounded-md bg-gray-100 text-gray-500 focus:outline-none cursor-not-allowed" value="Administrator" readonly>
                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <hr class="my-8 border-gray-200">
                    
                    <!-- Password Section -->
                    <div>
                        <h3 class="text-lg font-medium text-primary-dark mb-4">Change Password <span class="text-sm font-normal text-gray-500">(optional)</span></h3>
                        <div class="space-y-4">
                            <!-- Current Password -->
                            <div>
                                <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <div class="relative">
                                    <input type="password" id="currentPassword" name="currentPassword" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none">
                                    <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <div class="relative">
                                    <input type="password" id="newPassword" name="PASSWORD" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none" minlength="8" maxlength="15">
                                    <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Password must be 8-15 characters long</p>
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div>
                                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none">
                                    <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" id="cancelButton" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90 focus:outline-none">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Dialog -->
    <div id="confirmDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 modal-transition modal-enter">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 mr-4">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Discard Changes</h3>
                        <p class="text-sm text-gray-600">Are you sure you want to cancel? Any unsaved changes will be lost.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button id="continueEditing" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Continue Editing
                    </button>
                    <button id="discardChanges" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Discard Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 max-w-md bg-green-600 text-white px-4 py-3 rounded-md shadow-lg z-50 hidden flex items-center">
        <i id="toastIcon" class="fas fa-check-circle mr-3"></i>
        <span id="toastMessage">Profile updated successfully!</span>
        <button class="ml-auto text-white hover:text-white/80" onclick="hideToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initial form values for change detection
            const initialFormData = {};
            const formInputs = document.querySelectorAll('#profileForm input');
            formInputs.forEach(input => {
                initialFormData[input.id] = input.value;
            });
            
            // Toggle password visibility
            const toggleButtons = document.querySelectorAll('.toggle-password');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Handle back button function - reuse confirm dialog logic
            window.handleBackButton = function() {
                if (formHasChanges()) {
                    // Show confirmation dialog
                    showConfirmDialog();
                } else {
                    // No changes, just go back
                    window.history.back();
                }
            };
            
            // Handle cancel button with confirmation
            const cancelButton = document.getElementById('cancelButton');
            const confirmDialog = document.getElementById('confirmDialog');
            const continueEditing = document.getElementById('continueEditing');
            const discardChanges = document.getElementById('discardChanges');
            
            cancelButton.addEventListener('click', function() {
                if (formHasChanges()) {
                    // Show confirmation dialog
                    showConfirmDialog();
                } else {
                    // No changes, just go back
                    window.history.back();
                }
            });
            
            // Dialog control functions
            continueEditing.addEventListener('click', hideConfirmDialog);
            
            discardChanges.addEventListener('click', function() {
                hideConfirmDialog();
                window.history.back();
            });
            
            // Form submission handling
            const profileForm = document.getElementById('profileForm');
            
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                // Only validate passwords if user is trying to change password
                if (currentPassword || newPassword || confirmPassword) {
                    // Check if all password fields are filled
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        showToast('Please fill all password fields to change your password.', 'error');
                        return;
                    }
                    
                    // Check if new passwords match
                    if (newPassword !== confirmPassword) {
                        showToast('New passwords do not match!', 'error');
                        return;
                    }
                }
                
                // In a real application, you would send this to the server
                // For now, just show success toast
                showToast('Profile updated successfully!', 'success');
                
                // Reset form data so cancel doesn't trigger confirmation
                updateInitialFormData();
                
                // Automatically redirect after toast closes
                setTimeout(() => {
                    window.history.back();
                }, 2000);
            });
            
            // Check if form has changes
            function formHasChanges() {
                let hasChanges = false;
                
                // Check regular inputs
                formInputs.forEach(input => {
                    // Ignore read-only fields
                    if (!input.readOnly && initialFormData[input.id] !== input.value) {
                        hasChanges = true;
                    }
                });
                
                return hasChanges;
            }
            
            // Update initial form data (after successful save)
            function updateInitialFormData() {
                formInputs.forEach(input => {
                    initialFormData[input.id] = input.value;
                });
            }
            
            // Show confirmation dialog
            function showConfirmDialog() {
                confirmDialog.classList.remove('hidden');
                
                // Force a reflow so the transition happens
                void confirmDialog.offsetWidth;
                
                // Add active classes for animation
                const dialogContent = confirmDialog.querySelector('.modal-transition');
                dialogContent.classList.remove('modal-enter');
                dialogContent.classList.add('modal-enter-active');
            }
            
            // Hide confirmation dialog
            function hideConfirmDialog() {
                const dialogContent = confirmDialog.querySelector('.modal-transition');
                dialogContent.classList.remove('modal-enter-active');
                dialogContent.classList.add('modal-enter');
                
                // Wait for animation to complete before hiding
                setTimeout(() => {
                    confirmDialog.classList.add('hidden');
                }, 300);
            }
            
            // Toast notification functions
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                const toastIcon = document.getElementById('toastIcon');
                
                // Set message
                toastMessage.textContent = message;
                
                // Set color and icon based on type
                if (type === 'success') {
                    toast.classList.remove('bg-red-600', 'bg-yellow-600');
                    toast.classList.add('bg-green-600');
                    toastIcon.classList.remove('fa-exclamation-circle', 'fa-times-circle');
                    toastIcon.classList.add('fa-check-circle');
                } else if (type === 'error') {
                    toast.classList.remove('bg-green-600', 'bg-yellow-600');
                    toast.classList.add('bg-red-600');
                    toastIcon.classList.remove('fa-check-circle', 'fa-exclamation-circle');
                    toastIcon.classList.add('fa-times-circle');
                } else if (type === 'warning') {
                    toast.classList.remove('bg-green-600', 'bg-red-600');
                    toast.classList.add('bg-yellow-600');
                    toastIcon.classList.remove('fa-check-circle', 'fa-times-circle');
                    toastIcon.classList.add('fa-exclamation-circle');
                }
                
                // Show toast
                toast.classList.remove('hidden');
                toast.classList.add('toast-notification');
                
                // Clear any existing timeout
                if (window.toastTimeout) {
                    clearTimeout(window.toastTimeout);
                }
                
                // Auto hide after delay
                window.toastTimeout = setTimeout(hideToast, 3000);
            }
            
            function hideToast() {
                const toast = document.getElementById('toast');
                toast.classList.remove('toast-notification');
                toast.classList.add('toast-exit');
                
                setTimeout(() => {
                    toast.classList.add('hidden');
                    toast.classList.remove('toast-exit');
                }, 300);
            }
            
            // Make hideToast function global
            window.hideToast = hideToast;
        });
    </script>
</body>
</html>
