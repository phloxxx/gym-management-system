<?php
session_start();
require_once '../../config/member_functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'administrator') {
    header("Location: ../../login.php");
    exit();
}

// Get user data from session
$fullName = $_SESSION['name'] ?? 'Unknown User';
$role = ucfirst(strtolower($_SESSION['role'] ?? 'Unknown Role'));

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'getMembers':
            $result = getAllMembers();
            echo json_encode($result);
            break;
            
        case 'add':
            $result = addMember($_POST);
            echo json_encode($result);
            break;
            
        case 'edit':
            $result = updateMember($_POST);
            echo json_encode($result);
            break;
            
        case 'delete':
            if (!isset($_POST['memberId'])) {
                echo json_encode(['success' => false, 'message' => 'Member ID is required']);
                exit;
            }
            $result = deleteMember($_POST['memberId']);
            echo json_encode($result);
            break;
            
        case 'getMember':
            if (!isset($_POST['memberId'])) {
                echo json_encode(['success' => false, 'message' => 'Member ID is required']);
                exit;
            }
            $result = getMember($_POST['memberId']);
            echo json_encode($result);
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
    <title>Manage Members | Gymaster</title>
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
                                    <a href="manage-users.php" class="sidebar-dropdown-item">User</a>
                                </li>
                                <li>
                                    <a href="manage-members.php" class="sidebar-dropdown-item bg-white/10">Member</a>
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
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Members</h1>
                    
                    <!-- Right Section - User Profile and Notifications -->
                    <div class="flex items-center space-x-3">
                        <!-- User Profile - Direct link to edit profile -->
                        <div class="flex items-center space-x-3 pr-2">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($fullName); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($role); ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-white">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                        </div>
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
                        <h2 class="text-xl font-semibold text-primary-dark">Member Management</h2>
                        <p class="text-gray-600 text-sm">Add, edit, and manage gym members</p>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex gap-2 flex-wrap">
                        <div class="relative">
                            <input type="text" id="searchMembers" placeholder="Search members..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <!-- Program Filter Dropdown -->
                        <div class="relative">
                            <select id="programFilter" class="pl-4 pr-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light appearance-none text-primary">
                                <option value="">All Programs</option>
                                <option value="1">Strength Training</option>
                                <option value="2">Cardio</option>
                                <option value="3">Yoga</option>
                                <option value="4">CrossFit</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <button id="addMemberBtn" class="bg-primary-dark hover:bg-black text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Member
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Members Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="membersTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="memberTableBody">
                            <!-- Member rows will be inserted here dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div id="emptyState" class="py-8 text-center hidden">
                    <i class="fas fa-users text-gray-300 text-5xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-600">No members found</h3>
                    <p class="text-gray-500 mb-4" id="emptyStateMessage">Add members to get started or try a different search term.</p>
                    <button id="emptyStateAddBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Add New Member
                    </button>
                </div>
                
                <!-- Loading state -->
                <div id="loadingState" class="py-8 text-center hidden">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                    <p class="mt-2 text-gray-600">Loading members...</p>
                </div>
            </div>
        </div>

        <!-- Add/Edit Member Modal -->
        <div id="memberModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 modal-content transform scale-95 overflow-hidden">
                <!-- Modal Title Banner -->
                <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                    <div class="flex items-center z-10">
                        <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                            <i id="modalIcon" class="fas fa-user-plus text-xl"></i>
                        </div>
                        <div>
                            <h2 id="modalTitle" class="text-lg font-medium text-white leading-tight">Add New Member</h2>
                            <p class="text-xs text-white/90">Enter the required information below</p>
                        </div>
                    </div>
                    <button type="button" id="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Form -->
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <form id="memberForm" class="space-y-4">
                        <input type="hidden" id="memberId" name="memberId">
                        
                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="MEMBER_FNAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="MEMBER_LNAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="EMAIL" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="tel" name="PHONE_NUMBER" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <!-- Program Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Program</label>
                                <select name="PROGRAM_ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Select Program</option>
                                    <option value="1">Strength Training</option>
                                    <option value="2">Cardio</option>
                                    <option value="3">Yoga</option>
                                    <option value="4">CrossFit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Coach</label>
                                <select name="COACH_ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Select Coach</option>
                                    <!-- Will be populated based on selected program -->
                                </select>
                            </div>
                        </div>

                        <!-- Subscription Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subscription Plan</label>
                                <select name="SUB_ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Select Plan</option>
                                    <option value="1">Monthly (₱1,500)</option>
                                    <option value="2">Quarterly (₱4,000)</option>
                                    <option value="3">Annual (₱15,000)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select name="PAYMENT_ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Credit Card</option>
                                    <option value="3">Debit Card</option>
                                    <option value="4">GCash</option>
                                </select>
                            </div>
                        </div>

                        <!-- Membership Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="START_DATE" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="END_DATE" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required readonly>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="IS_ACTIVE" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                    <button type="button" id="cancelButton" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="saveMemberButton" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Member
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add toast notification div after main content -->
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
            // Elements
            const addMemberBtn = document.getElementById('addMemberBtn');
            const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
            const memberModal = document.getElementById('memberModal');
            const closeModalBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelButton');
            const memberForm = document.getElementById('memberForm');
            const saveMemberButton = document.getElementById('saveMemberButton');

            // Initialize with today's date
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="START_DATE"]').value = today;

            // Event Listeners
            addMemberBtn.addEventListener('click', openModal);
            if (emptyStateAddBtn) {
                emptyStateAddBtn.addEventListener('click', openModal);
            }
            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            saveMemberButton.addEventListener('click', saveMember);

            function openModal() {
                memberForm.reset();
                document.querySelector('input[name="START_DATE"]').value = today;
                memberModal.style.display = 'flex';
                memberModal.classList.remove('hidden');
            }

            function closeModal() {
                memberModal.style.display = 'none';
                memberModal.classList.add('hidden');
                memberForm.reset();
            }

            function saveMember() {
                if (!memberForm.checkValidity()) {
                    memberForm.reportValidity();
                    return;
                }

                const formData = new FormData(memberForm);
                formData.append('action', 'add');

                // Show loading state on button
                saveMemberButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                saveMemberButton.disabled = true;

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Member added successfully', true);
                        closeModal();
                        // Reload page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error adding member', false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error adding member', false);
                })
                .finally(() => {
                    // Reset button state
                    saveMemberButton.innerHTML = 'Save Member';
                    saveMemberButton.disabled = false;
                });
            }

            // Calculate end date based on subscription plan
            document.querySelector('select[name="SUB_ID"]').addEventListener('change', function() {
                const startDate = new Date(document.querySelector('input[name="START_DATE"]').value);
                let endDate = new Date(startDate);
                
                switch(this.value) {
                    case '1': // Monthly
                        endDate.setMonth(endDate.getMonth() + 1);
                        break;
                    case '2': // Quarterly
                        endDate.setMonth(endDate.getMonth() + 3);
                        break;
                    case '3': // Annual
                        endDate.setFullYear(endDate.getFullYear() + 1);
                        break;
                }
                
                document.querySelector('input[name="END_DATE"]').value = endDate.toISOString().split('T')[0];
            });

            // Base toast functionality
            window.hideToast = function() {
                const toast = document.getElementById('toast');
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            };

            window.showToast = function(message, isSuccess = true) {
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
                
                setTimeout(hideToast, 5000);
            };
        });
    </script>
</body>
</html>
