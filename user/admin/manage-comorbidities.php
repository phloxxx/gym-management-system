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
    <title>Manage Comorbidities - Gymaster</title>
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
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200 rotate-180" id="management-chevron"></i>
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
                                    <a href="manage-comorbidities.php" class="sidebar-dropdown-item bg-white/10">Comorbidities</a>
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
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Comorbidities</h1>
                    
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
            <!-- Main Content Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Health Conditions Management</h2>
                        <p class="text-gray-600 text-sm">Add and manage health conditions for member profiles</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" id="search-comorbidities" placeholder="Search conditions..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <button type="button" id="addComorbidityBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Condition
                        </button>
                    </div>
                </div>
            </div>

            <!-- Comorbidities Table Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="comorbiditiesTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="name">
                                    Name</i>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="status">
                                    Status</i>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Hypertension</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-comorbidity h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="001">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Diabetes</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-comorbidity h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="002">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Asthma</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-comorbidity h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="003">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Heart Disease</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-comorbidity h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="004">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Osteoporosis</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex space-x-2 justify-center">
                                        <button class="text-primary-dark hover:text-primary-light edit-comorbidity h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="005">
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
                    <i class="fas fa-notes-medical text-gray-300 text-5xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-600">No health conditions found</h3>
                    <p class="text-gray-500 mb-4" id="emptyStateMessage">Add health conditions or try a different search term.</p>
                    <button id="emptyStateAddBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Add New Condition
                    </button>
                </div>
                <!-- Loading state -->
                <div id="loadingState" class="py-8 text-center hidden">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                    <p class="mt-2 text-gray-600">Loading health conditions...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Health Condition Modal -->
    <div id="comorbidityModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 modal-content transform scale-95 overflow-hidden">
            <!-- Modal Title Banner -->
            <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i id="modalIcon" class="fas fa-notes-medical text-xl"></i>
                    </div>
                    <div>
                        <h2 id="modalTitle" class="text-lg font-medium text-white leading-tight">Add New Health Condition</h2>
                        <p class="text-xs text-white/90">Enter the required information below</p>
                    </div>
                </div>
                <button type="button" id="closeComorbidityModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>
            <!-- Modal Body -->
            <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <form id="comorbidityForm" class="space-y-3">
                    <input type="hidden" id="comorbidityId" value="">
                    
                    <!-- Condition Information Section -->
                    <div class="mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-heartbeat text-primary-light mr-2"></i>
                            <span>Condition Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="comorbidityName" class="block text-sm font-medium text-gray-700 mb-1">Condition Name</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <input type="text" id="comorbidityName" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="mb-1 mt-6">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-toggle-on text-primary-light mr-2"></i>
                            <span>Condition Status</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                        <div class="flex items-center">
                            <div class="relative inline-block w-12 mr-3 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="status" id="status" checked
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
                <button type="button" id="cancelComorbidityBtn" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10">
                    Cancel
                </button>
                <button type="button" id="saveConditionBtn" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                    <i class="fas fa-save mr-2"></i> <span id="saveButtonText">Save Condition</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Discard Changes Confirmation Dialog -->
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
        // Toast notification functions
        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }

        // Show success toast
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            toastMessage.textContent = message;
            toast.style.display = 'flex';
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
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

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

            // Comorbidity Modal Functions
            const comorbidityModal = document.getElementById('comorbidityModal');
            const addComorbidityBtn = document.getElementById('addComorbidityBtn');
            const closeComorbidityModal = document.getElementById('closeComorbidityModal');
            const cancelComorbidityBtn = document.getElementById('cancelComorbidityBtn');
            const comorbidityForm = document.getElementById('comorbidityForm');
            const modalTitle = document.getElementById('modalTitle');
            const saveButtonText = document.getElementById('saveButtonText');
            const comorbidityId = document.getElementById('comorbidityId');
            const comorbidityName = document.getElementById('comorbidityName');
            const saveConditionBtn = document.getElementById('saveConditionBtn');
            const discardChangesDialog = document.getElementById('discardChangesDialog');
            const continueEditingBtn = document.getElementById('continueEditing');
            const discardChangesBtn = document.getElementById('discardChanges');

            // Track form changes
            let originalFormState = {};
            let formChanged = false;

            // Capture the initial form state when opening the modal
            function captureFormState() {
                originalFormState = {
                    name: comorbidityName.value,
                    status: document.getElementById('status').checked
                };
            }

            // Check if form has changed
            function hasFormChanged() {
                return comorbidityName.value !== originalFormState.name || 
                       document.getElementById('status').checked !== originalFormState.status;
            }

            // Form change detection
            comorbidityForm.addEventListener('input', function() {
                formChanged = true;
            });

            comorbidityForm.addEventListener('change', function() {
                formChanged = true;
            });

            // Add new comorbidity button click
            addComorbidityBtn.addEventListener('click', function() {
                modalTitle.textContent = 'Add New Health Condition';
                saveButtonText.textContent = 'Save Condition';
                comorbidityId.value = '';
                comorbidityName.value = '';
                document.getElementById('status').checked = true;
                document.getElementById('statusLabel').innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                document.getElementById('statusLabel').classList.remove('text-red-600');
                document.getElementById('statusLabel').classList.add('text-green-600');
                showModal(comorbidityModal);

                // Reset form change tracking
                formChanged = false;
                captureFormState();
            });

            // Edit comorbidity button click
            const editButtons = document.querySelectorAll('.edit-comorbidity');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    modalTitle.textContent = 'Edit Health Condition';
                    saveButtonText.textContent = 'Update Condition';
                    comorbidityId.value = id;

                    // For demo purposes, populate with sample data
                    if (id === '001') {
                        comorbidityName.value = 'Hypertension';
                        document.getElementById('status').checked = true;
                        document.getElementById('statusLabel').innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        document.getElementById('statusLabel').classList.remove('text-red-600');
                        document.getElementById('statusLabel').classList.add('text-green-600');
                    } else if (id === '002') {
                        comorbidityName.value = 'Diabetes';
                        document.getElementById('status').checked = true;
                        document.getElementById('statusLabel').innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        document.getElementById('statusLabel').classList.remove('text-red-600');
                        document.getElementById('statusLabel').classList.add('text-green-600');
                    } else if (id === '004') { // Heart Disease is inactive
                        comorbidityName.value = 'Heart Disease';
                        document.getElementById('status').checked = false;
                        document.getElementById('statusLabel').innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                        document.getElementById('statusLabel').classList.remove('text-green-600');
                        document.getElementById('statusLabel').classList.add('text-red-600');
                    } else {
                        comorbidityName.value = 'Sample Condition';
                        document.getElementById('status').checked = true;
                        document.getElementById('statusLabel').innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        document.getElementById('statusLabel').classList.remove('text-red-600');
                        document.getElementById('statusLabel').classList.add('text-green-600');
                    }

                    showModal(comorbidityModal);

                    // Reset form change tracking and capture initial state
                    formChanged = false;
                    captureFormState();
                });
            });

            // Close modal buttons - check for unsaved changes
            closeComorbidityModal.addEventListener('click', function() {
                handleModalClose();
            });

            cancelComorbidityBtn.addEventListener('click', function() {
                handleModalClose();
            });

            // Handle modal close with change detection
            function handleModalClose() {
                if (formChanged && hasFormChanged()) {
                    // Show confirmation dialog
                    showModal(discardChangesDialog);
                } else {
                    // No changes, close directly
                    hideModal(comorbidityModal);
                }
            }

            // Discard changes dialog buttons
            continueEditingBtn.addEventListener('click', function() {
                hideModal(discardChangesDialog);
            });

            discardChangesBtn.addEventListener('click', function() {
                hideModal(discardChangesDialog);
                hideModal(comorbidityModal);
            });

            // Form submission
            saveConditionBtn.addEventListener('click', function() {
                if(comorbidityForm.checkValidity()) {
                    // Get condition name to show in notification
                    const conditionName = comorbidityName.value;
                    // Get action (add or update)
                    const isUpdate = comorbidityId.value ? true : false;
                    // Show confirmation dialog
                    // Close modal
                    hideModal(comorbidityModal);
                    // Show success notification
                    if (isUpdate) {
                        showToast(`${conditionName} condition updated successfully!`, true);
                    } else {
                        showToast(`${conditionName} condition added successfully!`, true);
                    }
                    // Reset form change tracking
                    formChanged = false;
                    // In a real application, you would refresh the table or add/update the row here
                } else {
                    // Trigger browser's default validation
                    const submitButton = document.createElement('button');
                    submitButton.type = 'submit';
                    comorbidityForm.appendChild(submitButton);
                    submitButton.click();
                    comorbidityForm.removeChild(submitButton);
                }
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
                }, 200);
            }

            // Toggle status in add/edit form
            const statusToggle = document.getElementById('status');
            const statusLabel = document.getElementById('statusLabel');
            
            if (statusToggle && statusLabel) {
                statusToggle.addEventListener('change', function() {
                    if (this.checked) {
                        statusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        statusLabel.classList.remove('text-red-600');
                        statusLabel.classList.add('text-green-600');
                    } else {
                        statusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                        statusLabel.classList.remove('text-green-600');
                        statusLabel.classList.add('text-red-600');
                    }
                });
            }
        });
    </script>
</body>
</html>