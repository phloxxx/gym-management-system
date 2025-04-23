<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymaster - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/auth-styles.css">
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
                    },
                    backgroundImage: {
                        'gym-pattern': "url('src/images/gymaster-bg.jpg')"
                    }
                }
            }
        }
    </script>
</head>
<body class="font-poppins">
    <div class="flex min-h-screen">
        <!-- Left Panel - Welcome & Visual Elements -->
        <div class="hidden md:flex md:w-3/5 bg-gradient-to-br from-primary-dark to-primary-light relative overflow-hidden">
            <!-- Abstract design elements -->
            <div class="abstract-lines"></div>
            <div class="abstract-circle-1"></div>
            <div class="abstract-circle-2"></div>
            
            <!-- Welcome content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full text-white px-12">
                <div class="mb-1">
                    <img src="src/images/gymaster-logo.png" alt="Gymaster Logo" class="h-23 w-auto filter brightness-0 invert">
                </div>
                <h1 class="text-4xl font-bold mb-1 text-center">Password Recovery</h1>
                <p class="text-lg text-white/80 text-center mb-3">We'll help you reset your password and get back to managing your fitness business.</p>
            </div>
        </div>

        <!-- Right Panel - Forgot Password Form -->
        <div class="w-full md:w-2/5 flex items-center justify-center bg-gray-50 p-5">
            <div class="w-full max-w-md">
                <!-- Reset Password Card -->
                <div class="bg-white rounded-xl shadow-md p-8">
                    <!-- Mobile Logo - Only visible on small screens -->
                    <div class="flex justify-center mb-6 md:hidden">
                        <div class="logo-organic-container">
                            <div class="organic-splash-effect"></div>
                            <img src="src/images/gymaster-logo.png" alt="Gymaster Logo" class="h-16 w-auto relative z-10">
                        </div>
                    </div>
                    
                    <!-- Password Reset Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-primary-dark mb-1 text-center">Forgot Password</h2>
                        <p class="text-center text-gray-600 text-sm">Enter your username and role to reset your password</p>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-5">
                        <h3 class="text-sm font-medium uppercase text-primary-dark tracking-wide mb-2">Select Your Role</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Admin Role -->
                            <div class="role-card cursor-pointer bg-white border-2 border-tertiary/30 rounded-lg py-3 px-4 hover:border-primary-light transition-all duration-300 shadow-sm" 
                                 data-role="admin" 
                                 onclick="selectRole(this)">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-tertiary/10 p-2 rounded-full">
                                        <i class="fas fa-user-shield text-base text-primary-dark"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-primary-dark text-sm">Admin</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Staff Role -->
                            <div class="role-card cursor-pointer bg-white border-2 border-tertiary/30 rounded-lg py-3 px-4 hover:border-primary-light transition-all duration-300 shadow-sm" 
                                 data-role="staff" 
                                 onclick="selectRole(this)">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-tertiary/10 p-2 rounded-full">
                                        <i class="fas fa-user-tie text-base text-primary-dark"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-primary-dark text-sm">Staff</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Hidden role error element -->
                        <div id="role-error" class="text-red-500 text-xs mt-1 hidden">Please select a role</div>
                    </div>

                    <!-- Reset Form -->
                    <form id="reset-form" class="space-y-5">
                        <!-- Username field -->
                        <div>
                            <label for="username" class="text-sm font-medium uppercase text-primary-dark tracking-wide mb-2 block">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <i class="fas fa-user text-secondary/70"></i>
                                </div>
                                <input type="text" id="username" name="username" 
                                    class="w-full py-2.5 pl-10 pr-3 border border-tertiary/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light custom-input-focus bg-gray-50/50" 
                                    placeholder="Enter your username">
                            </div>
                        </div>

                        <!-- Employee ID field -->
                        <div>
                            <label for="employee-id" class="text-sm font-medium uppercase text-primary-dark tracking-wide mb-2 block">Employee ID</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <i class="fas fa-id-card text-secondary/70"></i>
                                </div>
                                <input type="text" id="employee-id" name="employee-id" 
                                    class="w-full py-2.5 pl-10 pr-3 border border-tertiary/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light custom-input-focus bg-gray-50/50" 
                                    placeholder="Enter your employee ID">
                            </div>
                        </div>

                        <!-- Submit button -->
                        <div class="pt-2">
                            <button type="button" onclick="submitResetRequest()" 
                                class="w-full flex justify-center uppercase py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-primary-dark to-primary-light hover:from-primary-light hover:to-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light transition-all duration-300">
                                Verify Identity
                            </button>
                        </div>
                    </form>

                    <!-- Back to Login link -->
                    <div class="mt-6 text-center">
                        <a href="login.php" class="text-primary-light font-medium hover:text-primary-dark inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Login
                        </a>
                    </div>
                </div>
                
                <!-- Version Info -->
                <div class="text-center mt-4 text-xs text-gray-500">
                    <p>Gymaster v1.0 • © 2024 All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variable to track selected role
        let selectedRole = null;
        let verificationStep = 1;

        // Mock user data for demo purposes
        const accounts = {
            admin: {
                username: "admin",
                employeeId: "ADM001"
            },
            staff: {
                username: "staff",
                employeeId: "STF001"
            }
        };

        function selectRole(element) {
            // Remove active class from all role cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('border-primary-light', 'bg-tertiary/20');
                card.classList.add('border-tertiary/30');
            });
            
            // Add active class to selected card
            element.classList.remove('border-tertiary/30');
            element.classList.add('border-primary-light', 'bg-tertiary/20');
            
            // Update selected role
            selectedRole = element.getAttribute('data-role');
            
            // Hide role error if it was displayed
            if (document.getElementById('role-error')) {
                document.getElementById('role-error').classList.add('hidden');
            }
        }
        
        function validateForm() {
            let isValid = true;
            let errors = [];
            
            if (verificationStep === 1) {
                const username = document.getElementById('username').value.trim();
                const employeeId = document.getElementById('employee-id').value.trim();
                
                // Validate username
                if (!username) {
                    isValid = false;
                    errors.push('Username field cannot be empty');
                    highlightErrorField('username');
                }
                
                // Validate employee ID
                if (!employeeId) {
                    isValid = false;
                    errors.push('Employee ID field cannot be empty');
                    highlightErrorField('employee-id');
                }
                
                // Validate role selection
                if (!selectedRole) {
                    isValid = false;
                    errors.push('Role selection is required - please select Admin or Staff');
                    // Show role error
                    if (document.getElementById('role-error')) {
                        document.getElementById('role-error').classList.remove('hidden');
                    }
                }
            } else if (verificationStep === 2) {
                const verificationCode = document.getElementById('verification-code').value.trim();
                
                if (!verificationCode) {
                    isValid = false;
                    errors.push('Verification code cannot be empty');
                    highlightErrorField('verification-code');
                } else if (verificationCode !== "123456") { // Static code for demo
                    isValid = false;
                    errors.push('Invalid verification code');
                    highlightErrorField('verification-code');
                }
            }
            
            if (!isValid) {
                showError(errors);
            }
            
            return isValid;
        }
        
        function submitResetRequest() {
            if (validateForm()) {
                if (verificationStep === 1) {
                    const username = document.getElementById('username').value.trim();
                    const employeeId = document.getElementById('employee-id').value.trim();
                    
                    // Check if the credentials match our mock data
                    let validCredentials = false;
                    
                    if (selectedRole === 'admin' && 
                        username === accounts.admin.username && 
                        employeeId === accounts.admin.employeeId) {
                        validCredentials = true;
                    } else if (selectedRole === 'staff' && 
                             username === accounts.staff.username && 
                             employeeId === accounts.staff.employeeId) {
                        validCredentials = true;
                    }
                    
                    if (validCredentials) {
                        // Show verification code step
                        showVerificationCodeStep();
                    } else {
                        // Show invalid credentials error
                        showError("The information you provided doesn't match our records. Please verify your username and employee ID.");
                        
                        // Shake the form to indicate error
                        const resetForm = document.getElementById('reset-form');
                        resetForm.classList.add('shake-animation');
                        setTimeout(() => {
                            resetForm.classList.remove('shake-animation');
                        }, 500);
                    }
                } else if (verificationStep === 2) {
                    // Show success message for correct verification code
                    showSuccessMessage();
                }
            }
        }
        
        function showVerificationCodeStep() {
            verificationStep = 2;
            
            // Update form to show verification code field
            const resetForm = document.getElementById('reset-form');
            resetForm.innerHTML = `
                <div class="p-4 bg-blue-50 rounded-lg mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        A verification code has been sent to the gym administrator.
                        <br>For this demo, please use code: <strong>123456</strong>
                    </p>
                </div>
                
                <!-- Verification Code field -->
                <div>
                    <label for="verification-code" class="text-sm font-medium uppercase text-primary-dark tracking-wide mb-2 block">Verification Code</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <i class="fas fa-lock text-secondary/70"></i>
                        </div>
                        <input type="text" id="verification-code" name="verification-code" 
                            class="w-full py-2.5 pl-10 pr-3 border border-tertiary/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light custom-input-focus bg-gray-50/50" 
                            placeholder="Enter 6-digit code">
                    </div>
                </div>
                
                <!-- Submit button -->
                <div class="pt-4">
                    <button type="button" onclick="submitResetRequest()" 
                        class="w-full flex justify-center uppercase py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-primary-dark to-primary-light hover:from-primary-light hover:to-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light transition-all duration-300">
                        Reset Password
                    </button>
                </div>
            `;
            
            // Update page header
            document.querySelector('.mb-6 h2').textContent = "Verify Identity";
            document.querySelector('.mb-6 p').textContent = "Enter the verification code to reset your password";
            
            // Hide role selection since it's no longer needed
            document.querySelector('.mb-5').style.display = 'none';
            
            // Focus on the verification code field
            setTimeout(() => {
                document.getElementById('verification-code').focus();
            }, 100);
        }
        
        function showSuccessMessage() {
            // Replace the form with success message
            const formContainer = document.getElementById('reset-form').parentNode;
            
            // Create success content
            const successContent = document.createElement('div');
            successContent.className = 'text-center py-4';
            successContent.innerHTML = `
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-primary-dark mb-2">Password Reset Successful</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Your password has been reset to: <strong>Temp123!</strong><br>
                    Please change this temporary password after logging in.
                </p>
                <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-dark hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light">
                    Return to Login
                </a>
            `;
            
            // Update page header
            document.querySelector('.mb-6 h2').textContent = "Password Reset";
            document.querySelector('.mb-6 p').textContent = "Your password has been successfully reset";
            
            // Remove form
            const resetForm = document.getElementById('reset-form');
            resetForm.parentNode.replaceChild(successContent, resetForm);
            
            // Hide role selection
            document.querySelector('.mb-5').style.display = 'none';
        }
        
        function showError(errors) {
            // Clear any existing error notification
            clearNotifications();
            
            // Create error notification with all errors
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-3 rounded shadow-lg flex items-start fade-in max-w-md z-50';
            
            let errorMessage = '<i class="fas fa-exclamation-circle mt-1 mr-3"></i><div class="mr-4">';
            
            if (Array.isArray(errors) && errors.length > 0) {
                if (errors.length === 1) {
                    errorMessage += `<span class="font-medium">${errors[0]}</span>`;
                } else {
                    errorMessage += '<span class="font-medium">Please correct the following errors:</span><ul class="list-disc ml-4 mt-1">';
                    errors.forEach(error => {
                        errorMessage += `<li>${error}</li>`;
                    });
                    errorMessage += '</ul>';
                }
            } else if (typeof errors === 'string') {
                errorMessage += `<span class="font-medium">${errors}</span>`;
            } else {
                errorMessage += '<span class="font-medium">An unknown error occurred.</span>';
            }
            
            errorMessage += '</div><button onclick="clearNotifications()" class="ml-auto text-white hover:text-red-200"><i class="fas fa-times"></i></button>';
            notification.innerHTML = errorMessage;
            document.body.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        function clearNotifications() {
            // Remove any existing notifications
            const existingNotifications = document.querySelectorAll('.fixed.top-4.right-4');
            existingNotifications.forEach(notification => {
                notification.remove();
            });
        }
        
        function highlightErrorField(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('border-red-500', 'bg-red-50');
                
                // Reset highlight after user starts typing again
                field.addEventListener('input', function removeHighlight() {
                    field.classList.remove('border-red-500', 'bg-red-50');
                    field.removeEventListener('input', removeHighlight);
                }, { once: true });
            }
        }

        // Add keypress event to allow form submission with Enter key
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.id === 'username' || 
                    activeElement.id === 'employee-id' ||
                    activeElement.id === 'verification-code') {
                    submitResetRequest();
                }
            }
        });
    </script>

    <style>
        /* Add a shake animation for form errors */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake-animation {
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }
        
        /* Fade in animation for notifications */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</body>
</html>
