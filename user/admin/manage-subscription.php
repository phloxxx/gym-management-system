<?php
session_start();
require_once '../../config/db_functions.php';
require_once '../../config/subscription_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Get user data from session
$userId = $_SESSION['user_id'];
$conn = getConnection();
$stmt = $conn->prepare("SELECT USER_FNAME, USER_LNAME, USER_TYPE FROM user WHERE USER_ID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$fullName = $userData['USER_FNAME'] . ' ' . $userData['USER_LNAME'];
$role = ucfirst(strtolower($userData['USER_TYPE']));
if ($role === 'Administrator') $role = 'Administrator';
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
                                <?php echo "<span class='font-medium'>" . strtoupper(substr($userData['USER_FNAME'], 0, 1)) . "</span>"; ?>
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
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div id="emptyState" class="py-8 text-center hidden">
                    <i class="fas fa-tag text-gray-300 text-5xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-600">No subscription plans found</h3>
                    <p class="text-gray-500 mb-4" id="emptyStateMessage">Add plans to get started or try a different search term.</p>
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
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 modal-content transform scale-95">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Add New Subscription</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" id="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <form id="subscriptionForm" class="p-6">
                <input type="hidden" id="subscriptionId" name="SUB_ID">
                <div class="space-y-4">
                    <div>
                        <label for="subName" class="block text-sm font-medium text-gray-700">Subscription Name</label>
                        <input type="text" id="subName" name="SUB_NAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duration (Days)</label>
                        <input type="number" id="duration" name="DURATION" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                        <input type="number" id="price" name="PRICE" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="isActive" name="IS_ACTIVE" class="rounded border-gray-300 text-primary-dark focus:ring-primary-light" checked>
                        <label for="isActive" class="ml-2 block text-sm text-gray-700">Active</label>
                    </div>
                </div>
            </form>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md" id="cancelBtn">Cancel</button>
                <button type="button" class="px-4 py-2 text-white bg-primary-dark hover:bg-opacity-90 rounded-md" id="saveBtn">Save</button>
            </div>
        </div>
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

            // Modal elements
            const modal = document.getElementById('subscriptionModal');
            const addButton = document.getElementById('addSubscriptionBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const saveBtn = document.getElementById('saveBtn');
            const form = document.getElementById('subscriptionForm');

            // Show modal
            function showModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            // Hide modal
            function hideModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                form.reset();
            }

            // Add subscription button click
            addButton.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add New Subscription';
                document.getElementById('subscriptionId').value = '';
                showModal();
            });

            // Close modal buttons
            [closeModal, cancelBtn].forEach(btn => {
                btn.addEventListener('click', hideModal);
            });

            // Save button click
            saveBtn.addEventListener('click', async () => {
                if (form.checkValidity()) {
                    const formData = new FormData(form);
                    const subscriptionId = document.getElementById('subscriptionId').value;
                    formData.append('action', subscriptionId ? 'update' : 'add');

                    try {
                        const response = await fetch('../../config/subscription_handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        
                        if (result.success) {
                            hideModal();
                            loadSubscriptions();
                            showToast(result.message, true);
                        } else {
                            showToast(result.message, false);
                        }
                    } catch (error) {
                        showToast('An error occurred', false);
                    }
                } else {
                    form.reportValidity();
                }
            });

            // Update subscription table
            function updateSubscriptionTable(subscriptions) {
                const tbody = document.getElementById('subscriptionTableBody');
                tbody.innerHTML = '';

                subscriptions.forEach(sub => {
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${sub.SUB_NAME}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    ${sub.DURATION} Days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(sub.PRICE).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${sub.IS_ACTIVE == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${sub.IS_ACTIVE == 1 ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <button onclick="editSubscription(${sub.SUB_ID})" class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button onclick="deleteSubscription(${sub.SUB_ID})" class="text-red-600 hover:text-red-800 delete-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }

            // Edit subscription
            window.editSubscription = async (id) => {
                try {
                    const response = await fetch(`../../config/subscription_handler.php?action=get&id=${id}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        const sub = result.subscription;
                        document.getElementById('modalTitle').textContent = 'Edit Subscription';
                        document.getElementById('subscriptionId').value = sub.SUB_ID;
                        document.getElementById('subName').value = sub.SUB_NAME;
                        document.getElementById('duration').value = sub.DURATION;
                        document.getElementById('price').value = sub.PRICE;
                        document.getElementById('isActive').checked = sub.IS_ACTIVE == 1;
                        showModal();
                    }
                } catch (error) {
                    showToast('Error loading subscription details', false);
                }
            };

            // Delete subscription
            window.deleteSubscription = async (id) => {
                if (confirm('Are you sure you want to delete this subscription?')) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('SUB_ID', id);

                        const response = await fetch('../../config/subscription_handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(result.message, true);
                            // Reload subscriptions after successful deletion
                            loadSubscriptions();
                        } else {
                            showToast(result.message || 'Failed to delete subscription', false);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('Error deleting subscription', false);
                    }
                }
            };

            // Load subscriptions when page loads
            async function loadSubscriptions() {
                try {
                    const response = await fetch('../../config/subscription_handler.php?action=getAll');
                    const result = await response.json();
                    
                    if (result.success) {
                        updateSubscriptionTable(result.subscriptions);
                        
                        // Toggle empty state
                        const emptyState = document.getElementById('emptyState');
                        const tableBody = document.getElementById('subscriptionTableBody');
                        if (result.subscriptions.length === 0) {
                            emptyState.classList.remove('hidden');
                            tableBody.classList.add('hidden');
                        } else {
                            emptyState.classList.add('hidden');
                            tableBody.classList.remove('hidden');
                        }
                    } else {
                        showToast(result.message || 'Failed to load subscriptions', false);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error loading subscriptions', false);
                }
            }

            // Add toast function if not already present
            function showToast(message, isSuccess) {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-md text-white ${
                    isSuccess ? 'bg-green-500' : 'bg-red-500'
                } transition-opacity duration-300`;
                toast.textContent = message;

                // Add to document
                document.body.appendChild(toast);

                // Remove after 3 seconds
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }

            loadSubscriptions();
        });
    </script>
</body>
</html>