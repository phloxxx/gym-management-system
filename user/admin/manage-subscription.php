<?php 
// Get user data from session - remove the default 'Admin' value to ensure we see the actual session data
$fullName = $_SESSION['name'];
$role = ucfirst(strtolower($_SESSION['role']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subscriptions - Gymaster</title>
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
                        <button type="button" class="sidebar-menu-item w-full justify-between active" aria-controls="dropdown-management" data-collapse-toggle="dropdown-management">
                            <div class="flex items-center">
                                <i class="fas fa-th-large"></i>
                                <span>Management</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="management-chevron"></i>
                        </button>
                        <div id="dropdown-management" class="overflow-hidden transition-all duration-300 ease-in-out">
                            <ul class="pt-1 pb-1">
                                <li>
                                    <a href="manage-users.php" class="sidebar-dropdown-item">User</a>
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
                                    <a href="manage-subscription.php" class="sidebar-dropdown-item active bg-white/10">Subscription</a>
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
                <a href="#" class="sidebar-menu-item text-white/90 hover:text-white mt-3" id="logout-button">
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
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Subscriptions</h1>
                    
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
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($fullName); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($role); ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-white">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="container mx-auto px-4 py-4">
            <!-- Action bar -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Subscription Plans</h2>
                        <p class="text-gray-600 text-sm">Create and manage gym membership subscription plans</p>
                    </div>
                    
                    <!-- Action buttons - Simplified -->
                    <div class="flex gap-2 flex-wrap">
                        <div class="relative">
                            <input type="text" id="searchSubscriptions" placeholder="Search plans..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <!-- Status Filter Dropdown Removed -->
                        
                        <button id="addSubscriptionBtn" class="bg-primary-dark hover:bg-black text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Subscription
                        </button>
                    </div>
                </div>
            </div>

            <!-- Subscription Plans Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="subscriptionTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="subscriptionTableBody">
                            <!-- Subscription rows will be inserted here dynamically -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Monthly Plan</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-blue-100 text-blue-800">
                                        30 Days
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$49.99</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="1">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Quarterly Plan</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-blue-100 text-blue-800">
                                        90 Days
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$129.99</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="2">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-500">Free Trial</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-blue-100 text-blue-800">
                                        7 Days
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-500">$0.00</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="5">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div id="emptyState" class="py-8 text-center hidden">
                    <i class="fas fa-tag text-gray-300 text-5xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-600">No subscription plans found</h3>
                    <p class="text-gray-500 mb-4" id="emptyStateMessage">Add plans to get started or try a different search term.</p>
                    <button id="emptyStateAddBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Add New Plan
                    </button>
                </div>
                
                <!-- Loading state -->
                <div id="loadingState" class="py-8 text-center hidden">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                    <p class="mt-2 text-gray-600">Loading subscription plans...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Subscription Modal -->
    <div id="subscriptionModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 modal-content transform scale-95 overflow-hidden">
            <!-- Modal Title Banner -->
            <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i id="modalIcon" class="fas fa-tags text-xl"></i>
                    </div>
                    <div>
                        <h2 id="modalTitle" class="text-lg font-medium text-white leading-tight">Add New Subscription Plan</h2>
                        <p class="text-xs text-white/90">Enter the required information below</p>
                    </div>
                </div>
                <button type="button" id="closeSubscriptionModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <form id="subscriptionForm" class="space-y-3">
                    <input type="hidden" id="subscriptionId" value="">
                    
                    <!-- Basic Information Section -->
                    <div class="mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-info-circle text-primary-light mr-2"></i>
                            <span>Basic Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="subName" class="block text-sm font-medium text-gray-700 mb-1">Subscription Name</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-tag"></i>
                            </div>
                            <input type="text" id="subName" name="SUB_NAME" 
                                class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                        </div>
                    </div>
                    
                    <!-- Duration and Price in a flex container -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Duration -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (Days)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <input type="text" id="duration" name="DURATION" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Example: 30, 90, 365</p>
                        </div>
                        
                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <input type="number" id="price" name="PRICE" min="0" step="0.01"
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                            </div>
                        </div>
                    </div>

                    <!-- Status Container -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm mt-4">
                        <label for="isActive" class="block text-sm font-medium text-gray-700 mb-2">Subscription Status</label>
                        <div class="flex items-center">
                            <div class="relative inline-block w-12 mr-3 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" id="isActive" name="IS_ACTIVE" checked
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-2 border-gray-300 appearance-none cursor-pointer transition-transform duration-300 ease-in-out">
                                <label for="isActive" 
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300 ease-in-out"></label>
                            </div>
                            <span id="statusText" class="text-sm text-green-600 font-medium flex items-center">
                                Active
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" id="cancelSubscriptionBtn" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10">
                    Cancel
                </button>
                <button type="button" id="saveSubscriptionBtn" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                    <i class="fas fa-save mr-2"></i> <span id="saveButtonText">Save Plan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Changes Confirmation Dialog -->
    <div id="discardChangesDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
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
                    <button id="keepEditingBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Continue Editing
                    </button>
                    <button id="discardChangesBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Discard Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Subscription Details Modal -->
    <div id="viewSubscriptionModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 modal-content transform scale-95 overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-tag text-xl"></i>
                    </div>
                    <div>
                        <h2 id="viewSubscriptionName" class="text-lg font-medium text-white leading-tight">Monthly Plan</h2>
                    </div>
                </div>
                <button type="button" id="closeViewModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <!-- Subscription Information -->
                <div class="space-y-6">
                    <!-- Basic Details -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                            <i class="fas fa-info-circle text-primary-light mr-2"></i>
                            <span>Basic Details</span>
                        </h4>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex flex-col space-y-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-xs text-gray-500">Duration</span>
                                        <p id="viewDuration" class="text-sm font-medium text-gray-800">30 Days</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs text-gray-500">Price</span>
                                        <p id="viewPrice" class="text-lg font-bold text-primary-dark">$49.99</p>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Description</span>
                                    <p id="viewDescription" class="text-sm text-gray-800 mt-1">Basic access to gym facilities</p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Status</span>
                                    <p id="viewStatus" class="inline-flex items-center mt-1">
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features List -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                            <i class="fas fa-list-check text-primary-light mr-2"></i>
                            <span>Included Features</span>
                        </h4>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <ul id="viewFeaturesList" class="space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span class="text-sm">Standard gym equipment access</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span class="text-sm">Locker room access</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span class="text-sm text-gray-500">Personal training sessions (not included)</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Member Usage Stats (just for display) -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                            <i class="fas fa-chart-bar text-primary-light mr-2"></i>
                            <span>Usage Statistics</span>
                        </h4>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex flex-col space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Active Members:</span>
                                    <span id="viewActiveMembers" class="text-sm font-medium text-gray-800">12</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Total Revenue:</span>
                                    <span id="viewTotalRevenue" class="text-sm font-medium text-gray-800">$599.88</span>
                                </div>
                                <div class="pt-2 mt-2 border-t border-gray-200">
                                    <span class="text-xs text-blue-600">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Detailed analytics available in the Reports section
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" id="viewEditButton" class="px-4 py-2.5 border border-primary-dark text-primary-dark rounded-lg hover:bg-primary-dark hover:text-white focus:outline-none transition-colors duration-300 shadow-sm font-medium flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Plan
                </button>
                <button type="button" id="viewCloseButton" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none transition-colors duration-300 shadow-sm font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <div id="deleteConfirmDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mr-4">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Deactivate Subscription</h3>
                        <p class="text-sm text-gray-600">Are you sure you want to deactivate this subscription plan? Members with this subscription will not be affected.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Deactivate
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
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Logout</h3>
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

    <!-- Notification Toast -->
    <div id="toast" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-md shadow-lg z-50 transform translate-x-full opacity-0 transition-all duration-300 flex items-center" style="display: none;">
        <i id="toastIcon" class="fas fa-check-circle mr-2"></i>
        <span id="toastMessage">Operation successful!</span>
        <button class="ml-3 text-white focus:outline-none" onclick="hideToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dropdown toggle functionality for sidebar
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

            // Management dropdown should be open by default since we're on a management page
            const managementDropdown = document.getElementById('dropdown-management');
            const managementChevron = document.getElementById('management-chevron');
            if (managementDropdown && managementChevron) {
                managementDropdown.classList.remove('hidden');
                managementDropdown.style.maxHeight = managementDropdown.scrollHeight + 'px';
                managementChevron.style.transform = 'rotate(180deg)';
            }

            // Form change tracking variables
            let formHasChanges = false;
            let originalFormValues = {};
            
            // Subscription Modal Functions
            const subscriptionModal = document.getElementById('subscriptionModal');
            const viewSubscriptionModal = document.getElementById('viewSubscriptionModal');
            const addSubscriptionBtn = document.getElementById('addSubscriptionBtn');
            const closeSubscriptionModal = document.getElementById('closeSubscriptionModal');
            const cancelSubscriptionBtn = document.getElementById('cancelSubscriptionBtn');
            const saveSubscriptionBtn = document.getElementById('saveSubscriptionBtn');
            const modalTitle = document.getElementById('modalTitle');
            const saveButtonText = document.getElementById('saveButtonText');
            const isActiveToggle = document.getElementById('isActive');
            const statusText = document.getElementById('statusText');
            const discardChangesDialog = document.getElementById('discardChangesDialog');
            const keepEditingBtn = document.getElementById('keepEditingBtn');
            const discardChangesBtn = document.getElementById('discardChangesBtn');
            
            // Form fields to track for changes
            const formFields = [
                document.getElementById('subName'),
                document.getElementById('duration'),
                document.getElementById('price'),
                document.getElementById('isActive')
            ];
            
            // Add change listeners to form fields
            formFields.forEach(field => {
                if (field) {
                    field.addEventListener('input', () => {
                        formHasChanges = true;
                    });
                    field.addEventListener('change', () => {
                        formHasChanges = true;
                    });
                }
            });
            
            // Capture original form state
            function captureFormState() {
                originalFormValues = {
                    subName: document.getElementById('subName').value,
                    duration: document.getElementById('duration').value,
                    price: document.getElementById('price').value,
                    isActive: document.getElementById('isActive').checked
                };
                
                // Reset changes flag
                formHasChanges = false;
            }
            
            // Toggle status text based on checkbox
            isActiveToggle.addEventListener('change', function() {
                updateStatusText(this.checked);
            });
            
            function updateStatusText(isActive) {
                if (isActive) {
                    statusText.innerHTML = 'Active';
                    statusText.classList.remove('text-red-600');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.innerHTML = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-red-600');
                }
            }

            // Add new subscription button click
            addSubscriptionBtn.addEventListener('click', function() {
                modalTitle.textContent = 'Add New Subscription Plan';
                saveButtonText.textContent = 'Save Plan';
                document.getElementById('subscriptionId').value = '';
                document.getElementById('subName').value = '';
                document.getElementById('duration').value = '';
                document.getElementById('price').value = '';
                isActiveToggle.checked = true;
                updateStatusText(true);
                
                // Show modal with animation
                showModal(subscriptionModal);
                
                // Capture initial form state
                captureFormState();
            });

            // Close modal buttons with confirmation if needed
            closeSubscriptionModal.addEventListener('click', function() {
                checkForUnsavedChanges();
            });

            cancelSubscriptionBtn.addEventListener('click', function() {
                checkForUnsavedChanges();
            });
            
            // Check for unsaved changes
            function checkForUnsavedChanges() {
                if (formHasChanges) {
                    showModal(discardChangesDialog);
                } else {
                    hideModal(subscriptionModal);
                }
            }
            
            // Discard changes dialog buttons
            keepEditingBtn.addEventListener('click', function() {
                hideModal(discardChangesDialog);
            });
            
            discardChangesBtn.addEventListener('click', function() {
                hideModal(discardChangesDialog);
                hideModal(subscriptionModal);
                formHasChanges = false;
            });

            // Form submission
            saveSubscriptionBtn.addEventListener('click', function() {
                const form = document.getElementById('subscriptionForm');
                
                if (form.checkValidity()) {
                    // Validate form
                    const subName = document.getElementById('subName').value;
                    const duration = document.getElementById('duration').value;
                    const price = document.getElementById('price').value;
                    
                    if (!subName || !duration || !price) {
                        showToast('Please fill in all required fields', false);
                        return;
                    }
                    
                    // Get subscription ID to determine if this is an add or update
                    const subscriptionId = document.getElementById('subscriptionId').value;
                    const isActive = document.getElementById('isActive').checked;
                    
                    // Prepare data for database operation
                    const subscriptionData = {
                        SUB_NAME: subName,
                        DURATION: duration,
                        PRICE: price,
                        IS_ACTIVE: isActive ? 1 : 0
                    };
                    
                    // If it's an update operation, include the ID
                    if (subscriptionId) {
                        subscriptionData.SUB_ID = subscriptionId;
                        
                        // Update the corresponding row in the table
                        updateTableRow(subscriptionData);
                        showToast('Subscription plan updated successfully!', true);
                    } else {
                        // For a new subscription, generate a temporary ID for demo
                        subscriptionData.SUB_ID = Date.now().toString();
                        
                        // Add a new row to the table
                        addTableRow(subscriptionData);
                        showToast('New subscription plan added successfully!', true);
                    }
                    
                    // Reset form changes flag and close modal
                    formHasChanges = false;
                    hideModal(subscriptionModal);
                } else {
                    // Trigger form validation
                    form.reportValidity();
                }
            });
            
            // Function to update existing table row
            function updateTableRow(subscription) {
                const row = document.querySelector(`.edit-button[data-id="${subscription.SUB_ID}"]`).closest('tr');
                
                // Update plan name
                const titleElement = row.querySelector('td:first-child .text-sm.font-medium');
                titleElement.textContent = subscription.SUB_NAME;
                
                // Update duration
                const durationElement = row.querySelector('td:nth-child(2) span');
                durationElement.textContent = `${subscription.DURATION} Days`;
                
                // Update price
                const priceElement = row.querySelector('td:nth-child(3) div');
                priceElement.textContent = `$${parseFloat(subscription.PRICE).toFixed(2)}`;
                
                // Update status
                const statusElement = row.querySelector('td:nth-child(4) span');
                if (subscription.IS_ACTIVE) {
                    statusElement.textContent = 'Active';
                    statusElement.classList.remove('bg-red-100', 'text-red-800');
                    statusElement.classList.add('bg-green-100', 'text-green-800');
                } else {
                    statusElement.textContent = 'Inactive';
                    statusElement.classList.remove('bg-green-100', 'text-green-800');
                    statusElement.classList.add('bg-red-100', 'text-red-800');
                }
                
                // If status changed to inactive, make the row text gray
                if (!subscription.IS_ACTIVE) {
                    titleElement.classList.add('text-gray-500');
                    titleElement.classList.remove('text-gray-900');
                    row.querySelector('td:first-child .flex-shrink-0').classList.add('bg-gray-400');
                    row.querySelector('td:first-child .flex-shrink-0').classList.remove('bg-primary-light');
                } else {
                    titleElement.classList.remove('text-gray-500');
                    titleElement.classList.add('text-gray-900');
                    row.querySelector('td:first-child .flex-shrink-0').classList.remove('bg-gray-400');
                    row.querySelector('td:first-child .flex-shrink-0').classList.add('bg-primary-light');
                }
            }
            
            // Function to add a new row to the table
            function addTableRow(subscription) {
                const tbody = document.getElementById('subscriptionTableBody');
                const newRow = document.createElement('tr');
                
                // Create the HTML for the new row
                newRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 ${subscription.IS_ACTIVE ? 'bg-primary-light' : 'bg-gray-400'} rounded-full flex items-center justify-center text-white text-xs">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium ${subscription.IS_ACTIVE ? 'text-gray-900' : 'text-gray-500'}">${subscription.SUB_NAME}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            bg-blue-100 text-blue-800">
                            ${subscription.DURATION} Days
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium ${subscription.IS_ACTIVE ? 'text-gray-900' : 'text-gray-500'}">$${parseFloat(subscription.PRICE).toFixed(2)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            ${subscription.IS_ACTIVE ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${subscription.IS_ACTIVE ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex space-x-2 justify-center">
                            <button class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="${subscription.SUB_ID}">
                                <i class="fas fa-edit text-lg"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                // Add the new row to the table
                tbody.insertBefore(newRow, tbody.firstChild);
                
                // Add event listener to the edit button
                const editButton = newRow.querySelector('.edit-button');
                editButton.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    editSubscription(id);
                });
                
                // Make sure the table is visible and empty state is hidden
                document.getElementById('subscriptionTable').style.display = '';
                document.getElementById('emptyState').classList.add('hidden');
            }
            
            // Edit subscription button click handler
            const editButtons = document.querySelectorAll('.edit-button');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    editSubscription(id);
                });
            });
            
            function editSubscription(id) {
                // Retrieve subscription data from the table row instead of hardcoded data
                const row = document.querySelector(`.edit-button[data-id="${id}"]`).closest('tr');
                
                // Set form values from the table data
                const titleElement = row.querySelector('td:first-child .text-sm.font-medium');
                const durationElement = row.querySelector('td:nth-child(2) span');
                const priceElement = row.querySelector('td:nth-child(3) div');
                const statusElement = row.querySelector('td:nth-child(4) span');
                
                // Update modal title and icon
                modalTitle.textContent = 'Edit Subscription Plan';
                saveButtonText.textContent = 'Update Plan';
                document.getElementById('modalIcon').className = 'fas fa-edit text-xl';
                document.getElementById('subscriptionId').value = id;
                
                // Set values in the form
                document.getElementById('subName').value = titleElement.textContent.trim();
                
                // Extract just the number from duration text (e.g., "30 Days" -> "30")
                const durationText = durationElement.textContent.trim();
                document.getElementById('duration').value = durationText.split(' ')[0];
                
                // Extract price value without currency symbol
                const priceText = priceElement.textContent.trim();
                document.getElementById('price').value = priceText.replace('$', '');
                
                // Set active status
                const isActive = statusElement.classList.contains('bg-green-100');
                isActiveToggle.checked = isActive;
                updateStatusText(isActive);
                
                // Show the modal
                showModal(subscriptionModal);
                
                // Capture form state
                captureFormState();
            }
            
            // Helper functions for modals
            function showModal(modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                setTimeout(() => {
                    const dialogContent = modal.querySelector('.transform');
                    if (dialogContent) {
                        dialogContent.classList.remove('scale-95');
                        dialogContent.classList.add('scale-100');
                    }
                }, 10);
            }
            
            function hideModal(modal) {
                const dialogContent = modal.querySelector('.transform');
                if (dialogContent) {
                    dialogContent.classList.remove('scale-100');
                    dialogContent.classList.add('scale-95');
                }
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }, 200);
            }
            
            // Search functionality - updated to work without status filter
            const searchInput = document.getElementById('searchSubscriptions');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#subscriptionTableBody tr');
                    let hasResults = false;
                    
                    rows.forEach(row => {
                        const planName = row.querySelector('td:first-child').textContent.toLowerCase();
                        if (planName.includes(searchTerm)) {
                            row.style.display = '';
                            hasResults = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Toggle empty state
                    const emptyState = document.getElementById('emptyState');
                    const tableDisplay = document.querySelector('#subscriptionTable');
                    
                    if (!hasResults) {
                        if (emptyState) {
                            emptyState.classList.remove('hidden');
                            document.getElementById('emptyStateMessage').textContent = 'No subscription plans matching your search.';
                        }
                        if (tableDisplay) {
                            tableDisplay.style.display = 'none';
                        }
                    } else {
                        if (emptyState) emptyState.classList.add('hidden');
                        if (tableDisplay) tableDisplay.style.display = '';
                    }
                });
            }
            
            // Handle subscription toggle buttons in the table
            const toggleButtons = document.querySelectorAll('.tooltip-toggle');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const planElement = this.closest('.dashboard-card');
                    const planName = planElement.querySelector('h3').textContent;
                    const statusSpan = planElement.querySelector('.bg-green-100, .bg-red-100');
                    const isCurrentlyActive = statusSpan?.classList.contains('bg-green-100');
                    
                    if (isCurrentlyActive) {
                        // Show confirmation dialog before deactivating
                        const deleteDialog = document.getElementById('deleteConfirmDialog');
                        showModal(deleteDialog);
                        
                        // Store reference to the clicked button for later use
                        deleteDialog.setAttribute('data-target-button', this.outerHTML);
                        deleteDialog.querySelector('h3').textContent = `Deactivate ${planName}`;
                        
                        // Set up confirm button
                        document.getElementById('confirmDelete').onclick = function() {
                            // Update status display
                            statusSpan.classList.remove('bg-green-100', 'text-green-800');
                            statusSpan.classList.add('bg-red-100', 'text-red-800');
                            statusSpan.textContent = 'Inactive';
                            
                            // Update toggle button
                            button.innerHTML = '<i class="fas fa-toggle-off"></i>';
                            button.classList.remove('text-red-600', 'hover:text-red-800');
                            button.classList.add('text-green-600', 'hover:text-green-800');
                            button.setAttribute('data-tooltip', 'Activate');
                            
                            // Add opacity to card
                            planElement.classList.add('opacity-70');
                            
                            // Show confirmation toast
                            showToast(`${planName} has been deactivated`, true);
                            
                            // Hide modal
                            hideModal(deleteDialog);
                        };
                        
                        // Set up cancel button
                        document.getElementById('cancelDelete').onclick = function() {
                            hideModal(deleteDialog);
                        };
                    } else {
                        // Activate without confirmation
                        statusSpan.classList.remove('bg-red-100', 'text-red-800');
                        statusSpan.classList.add('bg-green-100', 'text-green-800');
                        statusSpan.textContent = 'Active';
                        
                        // Update toggle button
                        button.innerHTML = '<i class="fas fa-toggle-on"></i>';
                        button.classList.remove('text-green-600', 'hover:text-green-800');
                        button.classList.add('text-red-600', 'hover:text-red-800');
                        button.setAttribute('data-tooltip', 'Deactivate');
                        
                        // Remove opacity from card
                        planElement.classList.remove('opacity-70');
                        
                        // Show confirmation toast
                        showToast(`${planName} has been activated`, true);
                    }
                });
            });

            // Logout functionality
            const logoutButton = document.getElementById('logout-button');
            const logoutConfirmDialog = document.getElementById('logoutConfirmDialog');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');
            
            logoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                showModal(logoutConfirmDialog);
            });
            
            cancelLogout.addEventListener('click', function() {
                hideModal(logoutConfirmDialog);
            });
            
            confirmLogout.addEventListener('click', function() {
                window.location.href = "../../login.php";
            });

            // Helper functions for modals
            function showModal(modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                setTimeout(() => {
                    const dialogContent = modal.querySelector('.transform');
                    if (dialogContent) {
                        dialogContent.classList.remove('scale-95');
                        dialogContent.classList.add('scale-100');
                    }
                }, 10);
            }
            
            function hideModal(modal) {
                const dialogContent = modal.querySelector('.transform');
                if (dialogContent) {
                    dialogContent.classList.remove('scale-100');
                    dialogContent.classList.add('scale-95');
                }
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }, 200);
            }
            
            // Toast notification functions
            window.hideToast = function() {
                const toast = document.getElementById('toast');
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            };

            function showToast(message, isSuccess = true) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                const toastIcon = document.getElementById('toastIcon');
                
                toastMessage.textContent = message;
                
                if (isSuccess) {
                    toast.classList.remove('bg-red-600');
                    toast.classList.add('bg-green-600');
                    toastIcon.classList.remove('fa-times-circle');
                    toastIcon.classList.add('fa-check-circle');
                } else {
                    toast.classList.remove('bg-green-600');
                    toast.classList.add('bg-red-600');
                    toastIcon.classList.remove('fa-check-circle');
                    toastIcon.classList.add('fa-times-circle');
                }
                
                toast.style.display = 'flex';
                setTimeout(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                }, 10);
                
                // Auto hide after 5 seconds
                setTimeout(hideToast, 5000);
            }
        });
    </script>
</body>
</html>
