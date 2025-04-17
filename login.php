<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Check for static admin credentials
    if ($username === 'admin' && $password === 'admin123' && $role === 'admin') {
        $_SESSION['user_id'] = 'ADMIN';
        $_SESSION['role'] = 'administrator';
        $_SESSION['name'] = 'Administrator';
        
        $response = [
            'success' => true,
            'redirect' => 'user/admin/admin-dashboard.php'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Convert role to match database enum
    $userType = ($role === 'admin') ? 'ADMINISTRATOR' : 'STAFF';

    try {
        $stmt = $conn->prepare("SELECT USER_ID, USERNAME, PASSWORD, USER_TYPE, USER_FNAME 
                               FROM user 
                               WHERE USERNAME = ? AND USER_TYPE = ? AND IS_ACTIVE = 1");
        $stmt->execute([$username, $userType]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['PASSWORD']) { // Note: Update this to use proper password hashing in production
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $user['USERNAME']; // Add this line
            $_SESSION['role'] = strtolower($user['USER_TYPE']);
            $_SESSION['name'] = $user['USER_FNAME'];
            
            $response = [
                'success' => true,
                'redirect' => $user['USER_TYPE'] === 'ADMINISTRATOR' ? 
                            'user/admin/admin-dashboard.php' : 
                            'user/staff/staff-dashboard.php'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error occurred'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymaster Login</title>
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
                <h1 class="text-4xl font-bold mb-1 text-center">Welcome to Gymaster!</h1>
                <p class="text-lg text-white/80 text-center mb-3">Manage your fitness business with our comprehensive gym management system.</p>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="w-full md:w-2/5 flex items-center justify-center bg-gray-50 p-5">
            <div class="w-full max-w-md">
                <!-- Login Card -->
                <div class="bg-white rounded-xl shadow-md p-8">
                    <!-- Mobile Logo - Only visible on small screens -->
                    <div class="flex justify-center mb-6 md:hidden">
                        <div class="logo-organic-container">
                            <div class="organic-splash-effect"></div>
                            <img src="src/images/gymaster-logo.png" alt="Gymaster Logo" class="h-16 w-auto relative z-10">
                        </div>
                    </div>
                    
                    <!-- Login Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-primary-dark mb-1 text-center">User Login</h2>
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
                    </div>

                    <!-- Login Form -->
                    <form id="login-form" class="space-y-4">
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

                        <!-- Password field -->
                        <div>
                            <label for="password" class="text-sm font-medium uppercase text-primary-dark tracking-wide mb-2 block">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <i class="fas fa-lock text-secondary/70"></i>
                                </div>
                                <input type="password" id="password" name="password" 
                                    class="w-full py-2.5 pl-10 pr-10 border border-tertiary/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light custom-input-focus bg-gray-50/50" 
                                    placeholder="Enter your password">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 cursor-pointer" onclick="togglePasswordVisibility()">
                                    <i id="password-toggle" class="fas fa-eye text-secondary/70 hover:text-primary-dark"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Forgot password -->
                        <div class="flex items-center justify-end pt-1">
                            <div class="text-sm">
                                <a href="forgot-password.php" class="text-primary-light font-medium hover:text-primary-dark">
                                    Forgot password?
                                </a>
                            </div>
                        </div>

                        <!-- Submit button -->
                        <div class="pt-2">
                            <button type="button" onclick="attemptLogin()" 
                                class="w-full flex justify-center uppercase py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-primary-dark to-primary-light hover:from-primary-light hover:to-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light transition-all duration-300">
                                Login
                            </button>
                        </div>
                    </form>
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
            document.getElementById('role-error').classList.add('hidden');
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }

        function validateForm() {
            let isValid = true;
            let errors = [];
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Validate username with specific requirements
            if (!username) {
                isValid = false;
                errors.push('Username field cannot be empty');
            } else if (username.length < 3) {
                isValid = false;
                errors.push('Username must be at least 3 characters');
            }
            
            // Validate password (only check if empty, not length since this is login)
            if (!password) {
                isValid = false;
                errors.push('Password field cannot be empty');
            }
            
            // Validate role selection with specific error
            if (!selectedRole) {
                isValid = false;
                errors.push('Role selection is required - please select Admin or Staff');
            }
            
            if (!isValid) {
                showError(errors);
            }
            
            return isValid;
        }

        function showError(errors) {
            // Clear any existing error notification
            clearNotifications();
            
            // Create error notification with all errors
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-3 rounded shadow-lg flex items-start fade-in max-w-md';
            
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

        function showLoginSuccess(dashboardType) {
            // Clear any existing notifications
            clearNotifications();
            
            // Create success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg flex items-center fade-in';
            notification.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                <span>Login successful. Redirecting to ${dashboardType} dashboard...</span>
            `;
            document.body.appendChild(notification);
            
            // Remove notification and redirect after 2 seconds
            setTimeout(() => {
                notification.remove();
                // Update paths to match the correct file locations
                if (dashboardType === 'admin') {
                    window.location.href = 'user/admin/admin-dashboard.php';
                } else {
                    window.location.href = 'user/staff/staff-dashboard.php';
                }
            }, 2000);
        }

        // Replace the static accounts object with actual AJAX call
        function attemptLogin() {
            if (validateForm()) {
                const formData = new FormData();
                formData.append('username', document.getElementById('username').value);
                formData.append('password', document.getElementById('password').value);
                formData.append('role', selectedRole);

                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showLoginSuccess(selectedRole);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        showError(data.message);
                        const loginForm = document.getElementById('login-form');
                        loginForm.classList.add('shake-animation');
                        setTimeout(() => {
                            loginForm.classList.remove('shake-animation');
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred during login');
                });
            }
        }

        function highlightErrorField(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.add('border-red-500', 'bg-red-50');
            
            // Reset highlight after user starts typing again
            field.addEventListener('input', function removeHighlight() {
                field.classList.remove('border-red-500', 'bg-red-50');
                field.removeEventListener('input', removeHighlight);
            }, { once: true });
        }

        // Add keypress event to allow form submission with Enter key
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.id === 'username' || activeElement.id === 'password') {
                    attemptLogin();
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
    </style>
</body>
</html>