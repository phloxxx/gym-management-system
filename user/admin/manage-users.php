<?php
session_start();
require_once '../../config/db_functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'administrator') {
    header("Location: ../../login.php");
    exit();
}

// Add this after the session check but before the AJAX handling
function getAllUsers() {
    try {
        $conn = getConnection();
        $sql = "SELECT USER_ID, USER_FNAME, USER_LNAME, USERNAME, USER_TYPE, IS_ACTIVE 
                FROM user 
                ORDER BY USER_ID DESC";
        $result = $conn->query($sql);
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            // Format the data to match the frontend expectations
            $users[] = [
                'id' => $row['USER_ID'],
                'firstName' => $row['USER_FNAME'],
                'lastName' => $row['USER_LNAME'],
                'username' => $row['USERNAME'],
                'userType' => strtolower($row['USER_TYPE']) === 'administrator' ? 'admin' : 'staff',
                'isActive' => (int)$row['IS_ACTIVE']
            ];
        }
        return $users;
    } catch (Exception $e) {
        error_log("Error fetching users: " . $e->getMessage());
        return [];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'getUsers':
            $users = getAllUsers();
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        case 'add':
            // Debug logging for password
            error_log("Password attempt - Length: " . strlen($_POST['PASSWORD']));
            
            // Validate required fields
            $requiredFields = ['USER_FNAME', 'USER_LNAME', 'USERNAME', 'PASSWORD', 'USER_TYPE'];
            $postData = $_POST;
            $missingFields = array_filter($requiredFields, function($field) use ($postData) {
                return !isset($postData[$field]) || trim($postData[$field]) === '';
            });
            
            if (!empty($missingFields)) {
                error_log("Missing fields: " . implode(', ', $missingFields));
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            // Validate password length with better error message
            if (strlen($_POST['PASSWORD']) < 8 || strlen($_POST['PASSWORD']) > 15) {
                error_log("Invalid password length: " . strlen($_POST['PASSWORD']));
                echo json_encode([
                    'success' => false, 
                    'message' => 'Password must be between 8 and 15 characters long (current length: ' . strlen($_POST['PASSWORD']) . ')'
                ]);
                exit;
            }
            
            // Hash the password before sending to addUser function
            $_POST['PASSWORD'] = password_hash($_POST['PASSWORD'], PASSWORD_DEFAULT);
            error_log("Password hash length: " . strlen($_POST['PASSWORD']));
            
            $result = addUser($_POST);
            echo json_encode($result);
            break;

        case 'delete':
            if (!isset($_POST['userId'])) {
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            try {
                $conn = getConnection();
                $userId = (int)$_POST['userId'];
                
                // Check if user exists and is not the current logged-in user
                if ($userId === (int)$_SESSION['user_id']) {
                    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
                    exit;
                }
                
                $stmt = $conn->prepare("DELETE FROM user WHERE USER_ID = ?");
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
                }
            } catch (Exception $e) {
                error_log("Error deleting user: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the user']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Gymaster</title>
    <!-- Add Google Fonts - Poppins with multiple weights -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/admin-styles.css">
</head>
<body class="font-poppins bg-gray-50">
    <!-- Mobile menu button -->
    <button data-drawer-target="sidebar-gymaster" data-drawer-toggle="sidebar-gymaster" aria-controls="sidebar-gymaster" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
        <span class="sr-only">Open sidebar</span>
        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
        </svg>
    </button>
    
    <!-- Sidebar Navigation -->
    <aside id="sidebar-gymaster" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-hidden text-white relative flex flex-col sidebar-content">
            <!-- Animated background -->
            <div class="sidebar-background"></div>
            
            <!-- Logo Section - Centered and Enlarged -->
            <div class="flex items-center justify-center mb-3 pb-4 border-b border-white/10 relative">
                <img src="../../src/images/gymaster-logo.png" alt="Gymaster Logo" class="h-20 w-auto filter brightness-0 invert">
            </div>
            
            <nav class="flex-grow relative">
                <ul class="space-y-1 font-medium">
                    <!-- Dashboard -->
                    <li>
                        <a href="admin-dashboard.php" class="sidebar-menu-item">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Management Dropdown -->
                    <li class="mt-2">
                        <button type="button" class="sidebar-menu-item active w-full justify-between" aria-controls="dropdown-management" data-collapse-toggle="dropdown-management">
                            <div class="flex items-center">
                                <i class="fas fa-th-large"></i>
                                <span>Management</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200 rotate-180" id="management-chevron"></i>
                        </button>
                        <div id="dropdown-management" class="overflow-hidden transition-all duration-300 ease-in-out">
                            <ul class="pt-1 pb-1">
                                <li>
                                    <a href="manage-users.php" class="sidebar-dropdown-item bg-white/10">User</a>
                                </li>
                                <li>
                                    <a href="manage-members.php" class="sidebar-dropdown-item">Member</a>
                                </li>
                                <li>
                                    <a href="manage-programs-coaches.php" class="sidebar-dropdown-item">Program & Coach</a>
                                </li>
                                <li>
                                    <a href="manage-comorbidities.php" class="sidebar-dropdown-item">Comorbidities</a>
                                </li>
                                <li>
                                    <a href="manage-subscription.php" class="sidebar-dropdown-item">Subscription</a>
                                </li>
                                <li>
                                    <a href="manage-payment.php" class="sidebar-dropdown-item">Payment</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- Transaction -->
                    <li class="mt-2">
                        <a href="#" class="sidebar-menu-item">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transaction</span>
                        </a>
                    </li>
                    
                </ul>
            </nav>
            
            <!-- Logout placed at the very bottom -->
            <div class="mt-auto border-t border-white/10 relative">
                <a href="../../login.php" class="sidebar-menu-item text-white/90 hover:text-white mt-3">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="p-0 sm:ml-64 main-content">
        <!-- Top Header -->
        <header class="admin-header shadow-sm mb-3">
            <div class="max-w-full px-6">
                <div class="flex justify-between items-center h-16">
                    <!-- Page Title -->
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Users</h1>
                    
                    <!-- Right Section - User Profile and Notifications -->
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell -->
                        <div class="header-icon-button">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="notification-badge">3</span>
                        </div>
                        
                        <!-- Divider -->
                        <div class="h-8 w-px bg-gray-200 mx-2"></div>
                        
                        <!-- User Profile - Direct link to edit profile -->
                        <a href="edit-profile.php" class="flex items-center space-x-3 pr-2 cursor-pointer">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium text-gray-700">John Doe</p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-white">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content container -->
        <div class="container mx-auto px-4 py-4">
            <!-- Action bar -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">User Management</h2>
                        <p class="text-gray-600 text-sm">Add, edit, and manage system users</p>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex gap-2">
                        <div class="relative">
                            <input type="text" id="searchUsers" placeholder="Search users..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <!-- User Type Filter Dropdown -->
                        <div class="relative">
                            <select id="userTypeFilter" class="pl-4 pr-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light appearance-none text-primary">
                                <option value="">All User Types</option>
                                <option value="admin">Administrator</option>
                                <option value="staff">Staff</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <button id="addUserBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add User
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="usersTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                            <!-- User rows will be inserted here dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div id="emptyState" class="py-8 text-center hidden">
                    <i class="fas fa-users text-gray-300 text-5xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-600">No users found</h3>
                    <p class="text-gray-500 mb-4" id="emptyStateMessage">Add users to get started or try a different search term.</p>
                    <button id="emptyStateAddBtn" class="px-4 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors flex items-center mx-auto">
                        <i class="fas fa-plus mr-2"></i> Add User
                    </button>
                </div>
                
                <!-- Loading state -->
                <div id="loadingState" class="py-8 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                    <p class="mt-2 text-gray-600">Loading users...</p>
                </div>
            </div>
        </div>
        
        <!-- Add/Edit User Modal -->
        <div id="userModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 modal-content transform scale-95 overflow-hidden">
                <!-- Modal Title Banner (now serves as main header) -->
                <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                    <div class="flex items-center z-10">
                        <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                            <i id="modalIcon" class="fas fa-user-plus text-xl"></i>
                        </div>
                        <div>
                            <h2 id="modalTitle" class="text-lg font-medium text-white leading-tight">Add New User</h2>
                            <p class="text-xs text-white/90">Enter the required information below</p>
                        </div>
                    </div>
                    <button type="button" id="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <!-- Decorative background elements -->
                    <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                    <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                    <form id="userForm" class="space-y-3">
                        <input type="hidden" id="userId" name="userId" value="">
                        
                        <!-- Personal Information Section - Adjusted to be more compact -->
                        <div class="mb-1">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-id-card text-primary-light mr-2"></i>
                                <span>Personal Information</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                        </div>
                        
                        <!-- First Name and Last Name in a flex container -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- First Name -->
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input type="text" id="firstName" name="USER_FNAME" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                            <!-- Last Name -->
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input type="text" id="lastName" name="USER_LNAME" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information Section -->
                        <div class="mt-8 mb-3">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-user-shield text-primary-light mr-2"></i>
                                <span>Account Information</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mt-1"></div>
                        </div>
                        
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-at"></i>
                                </div>
                                <input type="text" id="username" name="USERNAME" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1">Username must be unique and between 5-20 characters.</p>
                        </div>
                        
                        <!-- Password (shown only for new users) -->
                        <div id="passwordContainer" class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <input type="password" id="password" name="PASSWORD" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required
                                    minlength="8" maxlength="15">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" class="toggle-password text-gray-400 hover:text-gray-600 focus:outline-none transition-colors w-5">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i> Password must be 8-15 characters long.
                            </p>
                        </div>
                        
                        <!-- Change Password Checkbox (shown only for editing) -->
                        <div id="changePasswordContainer" class="hidden">
                            <div class="flex items-center bg-gray-50 p-4 rounded-lg border border-gray-200 gap-2 shadow-sm">
                                <input type="checkbox" id="changePassword" class="h-4 w-4 rounded text-primary-dark focus:ring-primary-light">
                                <label for="changePassword" class="text-sm font-medium text-gray-700">Change Password</label>
                            </div>
                        </div>
                        
                        <!-- New Password (shown when checkbox is checked) -->
                        <div id="newPasswordContainer" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <input type="password" id="newPassword" name="newPassword" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200"
                                    minlength="8" maxlength="15">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" class="toggle-password text-gray-400 hover:text-gray-600 focus:outline-none transition-colors w-5">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i> Password must be 8-15 characters long.
                            </p>
                        </div>
                        
                        <!-- Role Information Section -->
                        <div class="mt-8 mb-3">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-user-cog text-primary-light mr-2"></i>
                                <span>Role & Status</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mt-1"></div>
                        </div>
                        
                        <!-- User Type -->
                        <div>
                            <label for="userType" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <select id="userType" name="USER_TYPE" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white" required>
                                    <option value="">Select User Type</option>
                                    <option value="admin">Administrator</option>
                                    <option value="staff">Staff</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Status -->
                        <div id="statusContainer" class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="flex items-center">
                                <div class="relative inline-block w-12 mr-3 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="IS_ACTIVE" id="status" checked
                                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-2 border-gray-300 appearance-none cursor-pointer transition-transform duration-300 ease-in-out">
                                    <label for="status" 
                                        class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300 ease-in-out"></label>
                                </div>
                                <span id="statusLabel" class="text-sm text-green-600 font-medium flex items-center">
                                    <i class="fas fa-check-circle mr-1.5"></i> Active
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer -->
                <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button type="button" id="cancelButton" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10">
                        Cancel
                    </button>
                    <button type="button" id="saveUserButton" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                        <i class="fas fa-save mr-2"></i> Save User
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Dialog -->
        <div id="confirmDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform scale-95 overflow-hidden transition-all duration-200">
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
                        <button id="cancelDiscard" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                            Continue Editing
                        </button>
                        <button id="confirmDiscard" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Discard Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logout Confirmation Dialog -->
        <div id="logoutConfirmDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform scale-95 overflow-hidden transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Logout Confirmation</h3>
                            <p class="text-sm text-gray-600">Are you sure you want to log out of your account?</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button id="cancelLogout" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button id="confirmLogout" class="px-4 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div id="deleteConfirmDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform scale-95 overflow-hidden transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mr-4">
                            <i class="fas fa-trash-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Delete User</h3>
                            <p class="text-sm text-gray-600">Are you sure you want to delete this user? This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notification Toast -->
        <div id="toast" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-md shadow-lg z-50 transform translate-x-full opacity-0 transition-all duration-300 flex items-center" style="display: none;">
            <i id="toastIcon" class="fas fa-check-circle mr-2"></i>
            <span id="toastMessage">Operation successful!</span>
            <button class="ml-3 text-white focus:outline-none" onclick="hideToast()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        // Define hideToast as a global function first
        function hideToast() {
            const toast = document.getElementById('toast');
            // Add the transition classes
            toast.classList.add('translate-x-full', 'opacity-0');
            // Force immediate hiding after a short delay
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
            // Clear any existing timeout
            if (window.toastTimeout) {
                clearTimeout(window.toastTimeout);
                window.toastTimeout = null;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // DOM elements
            const userTableBody = document.getElementById('userTableBody');
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const userModal = document.getElementById('userModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalIcon = document.getElementById('modalIcon');
            const userForm = document.getElementById('userForm');
            const addUserBtn = document.getElementById('addUserBtn');
            const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelButton = document.getElementById('cancelButton');
            const saveUserButton = document.getElementById('saveUserButton');
            const searchUsers = document.getElementById('searchUsers');
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            const statusCheckbox = document.getElementById('status');
            const statusLabel = document.getElementById('statusLabel');
            const changePasswordCheckbox = document.getElementById('changePassword');
            const passwordContainer = document.getElementById('passwordContainer');
            const changePasswordContainer = document.getElementById('changePasswordContainer');
            const newPasswordContainer = document.getElementById('newPasswordContainer');
            const confirmDialog = document.getElementById('confirmDialog');
            const cancelDiscard = document.getElementById('cancelDiscard');
            const confirmDiscard = document.getElementById('confirmDiscard');
            const logoutConfirmDialog = document.getElementById('logoutConfirmDialog');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');
            const logoutButton = document.querySelector('a[href="../../login.php"]');
            
            // Current selected user ID for editing/deleting
            let currentUserId = null;
            
            // Initialize the page
            initializePage();
            
            // Initialize sidebar dropdown toggle functionality
            initializeSidebar();
            
            // Add event listeners for UI interactions
            addEventListeners();
            
            // Initialize password visibility toggles
            initializePasswordToggles();
            
            // Make the hideToast function globally available
            window.hideToast = hideToast;
            
            function initializePage() {
                // Show loading state
                loadingState.classList.remove('hidden');
                
                // Fetch users from the server
                fetch('?action=getUsers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=getUsers'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUsers(data.users);
                    } else {
                        showToast('Failed to load users', 'error');
                        displayUsers([]);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to load users', 'error');
                    displayUsers([]);
                })
                .finally(() => {
                    loadingState.classList.add('hidden');
                });
            }
            
            function displayUsers(users) {
                // Clear any existing rows
                userTableBody.innerHTML = '';
                
                if (users.length === 0) {
                    // Show empty state
                    emptyState.classList.remove('hidden');
                    
                    // Check if this is from a search or initial empty state
                    const searchTerm = document.getElementById('searchUsers').value;
                    const filterType = document.getElementById('userTypeFilter').value;
                    
                    if (searchTerm || filterType) {
                        // Hide add button if it's a search with no results
                        document.getElementById('emptyStateAddBtn').classList.add('hidden');
                        document.getElementById('emptyStateMessage').textContent = 'No users match your search criteria. Please try a different search.';
                    } else {
                        // Show add button if it's just an empty list
                        document.getElementById('emptyStateAddBtn').classList.remove('hidden');
                        document.getElementById('emptyStateMessage').textContent = 'Add users to get started or try a different search term.';
                    }
                    
                    return;
                }
                
                // Hide empty state
                emptyState.classList.add('hidden');
                
                // Add rows for each user
                users.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${user.id}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                    ${user.firstName.charAt(0)}${user.lastName.charAt(0)}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">${user.firstName} ${user.lastName}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.username}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                ${user.userType === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                                ${user.userType === 'admin' ? 'Administrator' : 'Staff'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                ${user.isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${user.isActive ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="${user.id}">
                                <i class="fas fa-edit text-lg"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800 delete-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="${user.id}">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                });
                
                // Add event listeners to edit buttons
                document.querySelectorAll('.edit-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-id');
                        openEditModal(userId);
                    });
                });

                // Add event listeners to delete buttons
                document.querySelectorAll('.delete-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-id');
                        deleteUser(userId);
                    });
                });
            }
            
            function addEventListeners() {
                // Add User button
                addUserBtn.addEventListener('click', openAddModal);
                emptyStateAddBtn.addEventListener('click', openAddModal);
                
                // Close modal buttons with improved behavior
                closeModal.addEventListener('click', function() {
                    console.log("Close button clicked"); // Debug log
                    
                    // Check if there are any actual modifications to the form
                    const hasChanges = checkForActualChanges();
                    
                    if (hasChanges) {
                        // Show confirmation dialog if there are changes
                        showConfirmDialog(); 
                    } else {
                        // Close directly if there are no changes
                        userModal.style.display = 'none';
                        userModal.classList.add('hidden');
                        userModal.classList.remove('active');
                    }
                });
                
                // Cancel button behavior
                cancelButton.addEventListener('click', function() {
                    console.log("Cancel button clicked"); // Debug log
                    
                    // Check if there are any actual modifications to the form
                    const hasChanges = checkForActualChanges();
                    
                    if (hasChanges) {
                        // Show confirmation dialog if there are changes
                        showConfirmDialog();
                    } else {
                        // Close directly if there are no changes
                        userModal.style.display = 'none';
                        userModal.classList.add('hidden');
                        userModal.classList.remove('active');
                    }
                });
                
                // Confirmation dialog buttons
                cancelDiscard.addEventListener('click', hideConfirmDialog);
                confirmDiscard.addEventListener('click', function() {
                    hideConfirmDialog();
                    userModal.style.display = 'none';
                    userModal.classList.add('hidden');
                    userModal.classList.remove('active');
                });
                
                // Save user button
                saveUserButton.addEventListener('click', saveUser);
                
                // Add escape key event listener to close modal
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && userModal.style.display === 'flex') {
                        // Show confirmation if needed
                        if (hasFormBeenModified()) {
                            showConfirmDialog();
                        } else {
                            userModal.style.display = 'none';
                        }
                    }
                });
                
                // Search functionality
                const userTypeFilter = document.getElementById('userTypeFilter');
                
                // Function to filter users based on search and user type
                function filterUsers() {
                    const searchTerm = searchUsers.value.toLowerCase();
                    const selectedUserType = userTypeFilter.value;
                    
                    // Show loading state
                    loadingState.classList.remove('hidden');
                    
                    // Fetch all users again and filter on the client side
                    fetch('?action=getUsers', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=getUsers'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const filteredUsers = data.users.filter(user => {
                                const matchesSearch = 
                                    user.firstName.toLowerCase().includes(searchTerm) || 
                                    user.lastName.toLowerCase().includes(searchTerm) || 
                                    user.username.toLowerCase().includes(searchTerm);
                                
                                const matchesUserType = selectedUserType === '' || user.userType === selectedUserType;
                                
                                return matchesSearch && matchesUserType;
                            });
                            
                            displayUsers(filteredUsers);
                        }
                    })
                    .finally(() => {
                        loadingState.classList.add('hidden');
                    });
                }
                
                // Add event listeners for filtering
                searchUsers.addEventListener('input', filterUsers);
                userTypeFilter.addEventListener('change', filterUsers);
                
                // Toggle status label
                statusCheckbox.addEventListener('change', function() {
                    statusLabel.textContent = this.checked ? 'Active' : 'Inactive';
                    statusLabel.className = this.checked 
                        ? 'text-sm text-green-600 font-medium flex items-center' 
                        : 'text-sm text-red-600 font-medium flex items-center';
                    
                    // Update icon based on status
                    const icon = statusLabel.querySelector('i') || document.createElement('i');
                    icon.className = this.checked ? 'fas fa-check-circle mr-1.5' : 'fas fa-times-circle mr-1.5';
                    if (!statusLabel.querySelector('i')) {
                        statusLabel.prepend(icon);
                    }
                });
                
                // Change password checkbox
                changePasswordCheckbox.addEventListener('change', function() {
                    newPasswordContainer.classList.toggle('hidden', !this.checked);
                    document.getElementById('newPassword').required = this.checked;
                    
                    // Focus on password field when checkbox is checked
                    if (this.checked) {
                        setTimeout(() => {
                            document.getElementById('newPassword').focus();
                        }, 100);
                    }
                });
                
                // Add logout confirmation functionality
                if (logoutButton) {
                    logoutButton.addEventListener('click', function(e) {
                        e.preventDefault(); // Prevent immediate navigation
                        showLogoutConfirmDialog();
                    });
                }
                
                // Cancel logout button
                if (cancelLogout) {
                    cancelLogout.addEventListener('click', hideLogoutConfirmDialog);
                }
                
                // Confirm logout button
                if (confirmLogout) {
                    confirmLogout.addEventListener('click', function() {
                        // Navigate to login page
                        window.location.href = "../../login.php";
                    });
                }
            }
            
            // New function to check for actual changes rather than always returning true
            function checkForActualChanges() {
                // If we're adding a new user, check if any required field has content
                if (!currentUserId) {
                    const firstName = document.getElementById('firstName').value.trim();
                    const lastName = document.getElementById('lastName').value.trim();
                    const username = document.getElementById('username').value.trim();
                    
                    // If any of the main fields have content, consider it modified
                    return firstName !== '' || lastName !== '' || username !== '';
                } else {
                    // If editing, find the original user
                    const user = sampleUsers.find(u => u.id.toString() === currentUserId.toString());
                    if (user) {
                        // Check if any fields differ from original values
                        const firstNameChanged = document.getElementById('firstName').value !== user.firstName;
                        const lastNameChanged = document.getElementById('lastName').value !== user.lastName;
                        const usernameChanged = document.getElementById('username').value !== user.username;
                        const userTypeChanged = document.getElementById('userType').value !== user.userType;
                        const statusChanged = document.getElementById('status').checked !== (user.isActive === 1);
                        
                        // Check if password change was requested
                        const passwordChangeRequested = document.getElementById('changePassword') && 
                                                      document.getElementById('changePassword').checked;
                        
                        return firstNameChanged || lastNameChanged || usernameChanged || 
                               userTypeChanged || statusChanged || passwordChangeRequested;
                    }
                }
                
                // Default fallback
                return false;
            }
            
            // Replace the hasFormBeenModified function with our more detailed function
            function hasFormBeenModified() {
                return checkForActualChanges();
            }
            
            function initializePasswordToggles() {
                document.querySelectorAll('.toggle-password').forEach(button => {
                    button.addEventListener('click', function() {
                        const input = this.closest('.relative').querySelector('input');
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
            }
            
            function initializeSidebar() {
                // Keep the Management dropdown open and rotate the chevron
                const managementDropdown = document.getElementById('dropdown-management');
                const managementChevron = document.getElementById('management-chevron');
                
                if (managementDropdown) {
                    managementDropdown.classList.remove('hidden');
                    managementDropdown.style.maxHeight = managementDropdown.scrollHeight + 'px';
                }
                
                if (managementChevron) {
                    managementChevron.style.transform = 'rotate(180deg)';
                }
                
                // Add event listeners for dropdown toggles
                const dropdownButtons = document.querySelectorAll('[data-collapse-toggle]');
                
                dropdownButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-collapse-toggle');
                        const targetElement = document.getElementById(targetId);
                        const chevronIcon = document.getElementById(targetId.replace('dropdown-', '') + '-chevron');
                        
                        if (targetElement) {
                            if (targetElement.classList.contains('hidden')) {
                                // Show dropdown
                                targetElement.classList.remove('hidden');
                                targetElement.style.maxHeight = targetElement.scrollHeight + 'px';
                                if (chevronIcon) {
                                    chevronIcon.style.transform = 'rotate(180deg)';
                                }
                            } else {
                                // Hide dropdown
                                targetElement.style.maxHeight = '0px';
                                if (chevronIcon) {
                                    chevronIcon.style.transform = 'rotate(0deg)';
                                }
                                setTimeout(() => {
                                    targetElement.classList.add('hidden');
                                }, 300);
                            }   
                        }
                    });
                });
            }
            
            function openAddModal() {
                // Reset form
                userForm.reset();
                document.getElementById('userId').value = '';
                // Set modal title
                modalTitle.textContent = 'Add New User';
                modalIcon.className = 'fas fa-user-plus text-xl';
                
                // Show password field, hide change password option
                passwordContainer.classList.remove('hidden');
                document.getElementById('password').required = true;
                changePasswordContainer.classList.add('hidden');
                newPasswordContainer.classList.add('hidden');
                
                // Reset user ID
                currentUserId = null;
                document.getElementById('userId').value = '';
                
                // Show status container
                document.getElementById('statusContainer').classList.remove('hidden');
                
                // Show modal - ensure it's visible
                userModal.style.display = 'flex';
                userModal.classList.remove('hidden');
                userModal.classList.add('active');
            }
            
            function openEditModal(userId) {
                // Set current user ID
                currentUserId = userId;
                document.getElementById('userId').value = userId;
                
                // Find user data
                const user = sampleUsers.find(u => u.id.toString() === userId.toString());
                
                if (user) {
                    // Set modal title
                    modalTitle.textContent = 'Edit User';
                    modalIcon.className = 'fas fa-user-edit text-xl';
                    
                    // Populate form fields
                    document.getElementById('firstName').value = user.firstName;
                    document.getElementById('lastName').value = user.lastName;
                    document.getElementById('username').value = user.username;
                    document.getElementById('userType').value = user.userType;
                    document.getElementById('status').checked = user.isActive === 1;
                    
                    // Update status label
                    statusLabel.textContent = user.isActive === 1 ? 'Active' : 'Inactive';
                    statusLabel.className = user.isActive === 1
                        ? 'text-sm text-green-600 font-medium flex items-center'
                        : 'text-sm text-red-600 font-medium flex items-center';
                    
                    // Update the status icon
                    const icon = statusLabel.querySelector('i') || document.createElement('i');
                    icon.className = user.isActive === 1 ? 'fas fa-check-circle mr-1.5' : 'fas fa-times-circle mr-1.5';
                    if (!statusLabel.querySelector('i')) {
                        statusLabel.prepend(icon);
                    }
                    
                    // Hide regular password field, show change password option
                    passwordContainer.classList.add('hidden');
                    document.getElementById('password').required = false;
                    changePasswordContainer.classList.remove('hidden');
                    newPasswordContainer.classList.add('hidden');
                    
                    // Reset change password checkbox
                    document.getElementById('changePassword').checked = false;
                    document.getElementById('newPassword').required = false;
                    
                    // Show modal - ensure it's visible and properly styled
                    userModal.style.display = 'flex';
                    userModal.classList.remove('hidden');
                    userModal.classList.add('active');
                } else {
                    showToast('User not found.', 'error');
                }
            }
            
            function closeUserModal() {
                // Ensure the modal is properly hidden using both CSS classes and style
                userModal.classList.remove('active');
                setTimeout(() => {
                    userModal.classList.add('hidden');
                    userModal.style.display = 'none';
                }, 200);
            }
            
            function showConfirmDialog() {
                confirmDialog.classList.remove('hidden');
                setTimeout(() => {
                    confirmDialog.querySelector('div').classList.remove('scale-95');
                    confirmDialog.querySelector('div').classList.add('scale-100');
                }, 10);
            }
            
            function hideConfirmDialog() {
                confirmDialog.querySelector('div').classList.remove('scale-100');
                confirmDialog.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    confirmDialog.classList.add('hidden');
                }, 200);
            }
            
            function saveUser() {
                // Form validation
                if (!userForm.checkValidity()) {
                    userForm.reportValidity();
                    return;
                }

                // Show loading state
                const saveButton = document.getElementById('saveUserButton');
                const originalButtonText = saveButton.innerHTML;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                saveButton.disabled = true;

                // Get form data
                const formData = new FormData(userForm);
                formData.append('action', 'add');

                // Convert user type value to match database enum exactly
                const userType = document.getElementById('userType').value;
                formData.set('USER_TYPE', userType.toUpperCase() === 'ADMIN' ? 'ADMINISTRATOR' : 'STAFF');

                // Add status
                formData.set('IS_ACTIVE', document.getElementById('status').checked ? 1 : 0);

                // Log the form data for debugging
                console.log('Sending form data:', Object.fromEntries(formData));

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Server response:', data); // Debug log
                    if (data.success) {
                        showToast(data.message);
                        closeUserModal();
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Failed to save user', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while saving the user', 'error');
                })
                .finally(() => {
                    // Reset button state
                    saveButton.innerHTML = originalButtonText;
                    saveButton.disabled = false;
                });
            }
            
            function deleteUser(userId) {
                const deleteConfirmDialog = document.getElementById('deleteConfirmDialog');
                const confirmDeleteBtn = document.getElementById('confirmDelete');
                const cancelDeleteBtn = document.getElementById('cancelDelete');

                // Show delete confirmation dialog
                deleteConfirmDialog.classList.remove('hidden');
                setTimeout(() => {
                    deleteConfirmDialog.querySelector('div').classList.remove('scale-95');
                    deleteConfirmDialog.querySelector('div').classList.add('scale-100');
                }, 10);

                // Handle delete confirmation
                const handleDelete = () => {
                    // Show loading state on button
                    confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
                    confirmDeleteBtn.disabled = true;

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&userId=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            // Refresh the page after a short delay
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast(data.message || 'Failed to delete user', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while deleting the user', 'error');
                    })
                    .finally(() => {
                        // Hide dialog and reset button state
                        hideDeleteConfirmDialog();
                        confirmDeleteBtn.innerHTML = 'Delete User';
                        confirmDeleteBtn.disabled = false;
                    });
                };

                // Add event listeners
                confirmDeleteBtn.addEventListener('click', handleDelete, { once: true });
                cancelDeleteBtn.addEventListener('click', hideDeleteConfirmDialog);
            }

            function hideDeleteConfirmDialog() {
                const deleteConfirmDialog = document.getElementById('deleteConfirmDialog');
                deleteConfirmDialog.querySelector('div').classList.remove('scale-100');
                deleteConfirmDialog.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    deleteConfirmDialog.classList.add('hidden');
                }, 200);
            }
            
            function updateUserStatus(userId, isActive) {
                // Find user
                const userIndex = sampleUsers.findIndex(u => u.id.toString() === userId.toString());
                
                if (userIndex !== -1) {
                    // Update status
                    sampleUsers[userIndex].isActive = isActive ? 1 : 0;
                    const status = isActive ? 'activated' : 'deactivated';
                    showToast(`User ${status} successfully!`, isActive ? 'success' : 'warning');
                    
                    // Refresh the user table to show the updated status
                    displayUsers(sampleUsers);
                }
            }
            
            function showToast(message, type = 'success') {
                // Set icon and color based on type
                if (type === 'error') {
                    toast.classList.remove('bg-green-600', 'bg-yellow-600');
                    toast.classList.add('bg-red-600');
                    toastIcon.classList.remove('fa-check-circle', 'fa-exclamation-circle');
                    toastIcon.classList.add('fa-times-circle');
                } else if (type === 'warning') {
                    toast.classList.remove('bg-green-600', 'bg-red-600');
                    toast.classList.add('bg-yellow-600');
                    toastIcon.classList.remove('fa-check-circle', 'fa-times-circle');
                    toastIcon.classList.add('fa-exclamation-circle');
                } else {
                    toast.classList.remove('bg-red-600', 'bg-yellow-600');
                    toast.classList.add('bg-green-600');
                    toastIcon.classList.remove('fa-times-circle', 'fa-exclamation-circle');
                    toastIcon.classList.add('fa-check-circle');
                }
                
                // Set message
                toastMessage.textContent = message;
                
                // Clear any existing timeout
                if (window.toastTimeout) {
                    clearTimeout(window.toastTimeout);
                }
                
                // Reset the classes before showing
                toast.classList.add('translate-x-full', 'opacity-0');
                
                // Make sure toast is visible in the DOM
                toast.style.display = 'flex';
                
                // Trigger reflow to ensure transition works properly
                void toast.offsetWidth;
                
                // Show toast by removing the transform classes
                toast.classList.remove('translate-x-full', 'opacity-0');
                
                // Hide after 3 seconds
                window.toastTimeout = setTimeout(() => {
                    hideToast();
                }, 3000);
            }
            
            // Handle modal close action - extracted to avoid duplicate code
            function handleModalClose() {
                // Always directly close the modal without confirm dialog
                // This fixes the non-functional cancel and X buttons
                closeUserModal();
            }
            
            // Helper function to check if form has been modified - now used for confirmation
            function hasFormBeenModified() {
                return checkForActualChanges();
            }
            
            function showLogoutConfirmDialog() {
                logoutConfirmDialog.classList.remove('hidden');
                setTimeout(() => {
                    const dialogContent = logoutConfirmDialog.querySelector('.transform');
                    if (dialogContent) {
                        dialogContent.classList.remove('scale-95');
                        dialogContent.classList.add('scale-100');
                    }
                }, 10);
            }
            
            function hideLogoutConfirmDialog() {
                const dialogContent = logoutConfirmDialog.querySelector('.transform');
                if (dialogContent) {
                    dialogContent.classList.remove('scale-100');
                    dialogContent.classList.add('scale-95');
                }
                setTimeout(() => {
                    logoutConfirmDialog.classList.add('hidden');
                }, 200);
            }
        });
    </script>
</body>
</html>