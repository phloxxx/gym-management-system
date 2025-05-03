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
                        <a href="admin-dashboard.html" class="sidebar-menu-item">
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
                        <a href="manage-transaction.php" class="sidebar-menu-item">
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
                            <!-- Member rows will be dynamically populated from database -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div id="emptyState" class="py-8 text-center">
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
                    
                    <!-- Decorative background elements -->
                    <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                    <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                    <form id="memberForm" class="space-y-3">
                        <input type="hidden" id="memberId" name="memberId" value="">
                        
                        <!-- Personal Information Section -->
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
                                    <input type="text" id="firstName" name="MEMBER_FNAME" 
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
                                    <input type="text" id="lastName" name="MEMBER_LNAME" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <input type="email" id="email" name="EMAIL" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                            <!-- Phone Number -->
                            <div>
                                <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <input type="tel" id="phoneNumber" name="PHONE_NUMBER" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Program Information Section -->
                        <div class="mt-8 mb-3">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-dumbbell text-primary-light mr-2"></i>
                                <span>Program & Membership</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mt-1"></div>
                        </div>
                        
                        <!-- Program Selection -->
                        <div>
                            <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <select id="program" name="PROGRAM_ID" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white" required>
                                    <option value="">Select Program</option>
                                    <option value="1">Strength Training</option>
                                    <option value="2">Cardio</option>
                                    <option value="3">Yoga</option>
                                    <option value="4">CrossFit</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Coach Selection - Member can choose their preferred coach -->
                        <div id="coachSelectionContainer" class="mt-3">
                            <label for="coach" class="block text-sm font-medium text-gray-700 mb-1">Preferred Coach</label>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-xs text-gray-500">Filter by:</span>
                                <div class="flex space-x-1">
                                    <button type="button" id="allCoaches" class="px-2 py-1 rounded-md bg-primary-dark text-white text-xs">All</button>
                                    <button type="button" id="maleCoaches" class="px-2 py-1 rounded-md bg-gray-200 text-gray-700 text-xs">Male</button>
                                    <button type="button" id="femaleCoaches" class="px-2 py-1 rounded-md bg-gray-200 text-gray-700 text-xs">Female</button>
                                </div>
                            </div>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <select id="coach" name="COACH_ID" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white" required>
                                    <option value="">Select Coach</option>
                                    <!-- Coach options will be populated dynamically based on selected program -->
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1">Choose your preferred coach for this program</p>
                        </div>

                        <!-- Subscription Information Section -->
                        <div class="mt-8 mb-3">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-credit-card text-primary-light mr-2"></i>
                                <span>Subscription Information</span>
                                <span class="text-xs text-red-500 ml-2">(Required)</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mt-1"></div>
                            <p class="text-xs text-gray-500 mt-1">Members must have an active subscription</p>
                        </div>
                        
                        <!-- Subscription Type - Updated to match database schema -->
                        <div>
                            <label for="subscriptionType" class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <select id="subscriptionType" name="SUB_ID" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white" required>
                                    <option value="">Select Subscription Plan</option>
                                    <option value="1" data-duration="30" data-price="1500">Monthly Plan - ₱1,500</option>
                                    <option value="2" data-duration="90" data-price="4000">Quarterly Plan - ₱4,000</option>
                                    <option value="3" data-duration="365" data-price="15000">Annual Plan - ₱15,000</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Dates - Matching database schema -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start Date -->
                            <div>
                                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <input type="date" id="startDate" name="START_DATE" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" required>
                                </div>
                            </div>
                            
                            <!-- End Date (calculated automatically) -->
                            <div>
                                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute top-1/2 transform -translate-y-1/2 left-0 pl-3 flex items-center pointer-events-none text-primary-light" style="margin-top: -10px;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <input type="date" id="endDate" name="END_DATE" 
                                        class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 bg-gray-50" readonly required>
                                    <p class="text-xs text-gray-500 mt-1">Auto-calculated based on subscription plan</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method and Transaction Information -->
                        <div class="mt-4">
                            <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <select id="paymentMethod" name="PAYMENT_ID" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Credit Card</option>
                                    <option value="3">Debit Card</option>
                                    <option value="4">Online Banking</option>
                                    <option value="5">GCash</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Date - Auto-generated to current date -->
                        <input type="hidden" id="transactionDate" name="TRANSAC_DATE">

                        <!-- Subscription Status -->
                        <div id="subscriptionStatus" class="mt-4 p-4 rounded-lg border border-gray-200 bg-blue-50 hidden">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Subscription Summary</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p id="subscriptionSummary">No subscription selected</p>
                                        <p id="subscriptionPrice" class="font-semibold mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Information Section -->
                        <div class="mt-8 mb-3">
                            <h4 class="text-base font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-heartbeat text-primary-light mr-2"></i>
                                <span>Health Information</span>
                            </h4>
                            <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mt-1"></div>
                        </div>
                        
                        <!-- Comorbidities Multi-select -->
                        <div>
                            <label for="comorbidities" class="block text-sm font-medium text-gray-700 mb-1">Comorbidities (if any)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <select id="comorbidities" name="COMORBIDITIES" multiple
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                                    <option value="1">Hypertension</option>
                                    <option value="2">Diabetes</option>
                                    <option value="3">Heart Disease</option>
                                    <option value="4">Asthma</option>
                                    <option value="5">Arthritis</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 ml-1">Hold Ctrl (or Command on Mac) to select multiple options</p>
                        </div>

                        <!-- Status Container -->
                        <div id="statusContainer" class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm mt-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Membership Status</label>
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
                    <button type="button" id="saveMemberButton" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                        <i class="fas fa-save mr-2"></i> Save Member
                    </button>
                </div>
            </div>
        </div>
        
        <!-- View Member Details Modal -->
        <div id="viewMemberModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 modal-content transform scale-95 overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                    <div class="flex items-center z-10">
                        <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                            <i class="fas fa-user-circle text-xl"></i>
                        </div>
                        <div>
                            <h2 id="viewMemberName" class="text-lg font-medium text-white leading-tight">John Smith</h2>
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
                    <!-- Member Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal & Contact Details -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                                    <i class="fas fa-id-card text-primary-light mr-2"></i>
                                    <span>Personal Details</span>
                                </h4>
                                
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex flex-col space-y-2">
                                        <div>
                                            <span class="text-xs text-gray-500">Full Name</span>
                                            <p id="viewFullName" class="text-sm font-medium text-gray-800">John Smith</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Email</span>
                                            <p id="viewEmail" class="text-sm font-medium text-gray-800">john.smith@example.com</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Phone</span>
                                            <p id="viewPhone" class="text-sm font-medium text-gray-800">+1 (555) 123-4567</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Joined Date</span>
                                            <p id="viewJoinDate" class="text-sm font-medium text-gray-800">May 15, 2023</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Status</span>
                                            <p id="viewStatus" class="inline-flex items-center mt-1">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Active
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                                    <i class="fas fa-heartbeat text-primary-light mr-2"></i>
                                    <span>Health Information</span>
                                </h4>
                                
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <span class="text-xs text-gray-500">Comorbidities</span>
                                    <div id="viewComorbidities" class="flex flex-wrap gap-2 mt-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">None</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Membership & Program Details -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                                    <i class="fas fa-dumbbell text-primary-light mr-2"></i>
                                    <span>Program Details</span>
                                </h4>
                                
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex flex-col space-y-2">
                                        <div>
                                            <span class="text-xs text-gray-500">Program</span>
                                            <p id="viewProgramDetail" class="text-sm font-medium text-gray-800">Strength Training</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Coach</span>
                                            <p id="viewCoach" class="text-sm font-medium text-gray-800">Michael Johnson</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Subscription Details Section -->
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 flex items-center mb-3">
                                    <i class="fas fa-credit-card text-primary-light mr-2"></i>
                                    <span>Subscription Details</span>
                                </h4>
                                
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex flex-col space-y-2">
                                        <div>
                                            <span class="text-xs text-gray-500">Subscription Plan</span>
                                            <p id="viewSubscriptionPlan" class="text-sm font-medium text-gray-800">Monthly Plan</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Period</span>
                                            <p id="viewSubscriptionPeriod" class="text-sm font-medium text-gray-800">Nov 1, 2023 - Dec 1, 2023</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500">Payment Method</span>
                                            <p id="viewPaymentMethod" class="text-sm font-medium text-gray-800">Cash</p>
                                        </div>
                                        <div class="pt-2 mt-2 border-t border-gray-200">
                                            <span class="text-xs text-blue-600">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Subscriptions can only be modified through the Transaction section
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button type="button" id="viewEditButton" class="px-4 py-2.5 border border-primary-dark text-primary-dark rounded-lg hover:bg-primary-dark hover:text-white focus:outline-none transition-colors duration-300 shadow-sm font-medium flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit Member
                    </button>
                    <button type="button" id="viewCloseButton" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none transition-colors duration-300 shadow-sm font-medium">
                        Close
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
        // Base functionality
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

        // Set today's date as default for start date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            document.getElementById('startDate').value = formattedDate;
            
            // Set transaction date
            document.getElementById('transactionDate').value = formattedDate;
            
            // Initialize event listeners
            initSubscriptionLogic();
            initModalToggle();
            initCoachSelection();
            initViewAndEditButtons();
        });
        
        // Coach selection logic
        function initCoachSelection() {
            const programSelect = document.getElementById('program');
            const coachSelect = document.getElementById('coach');
            const allCoachesBtn = document.getElementById('allCoaches');
            const maleCoachesBtn = document.getElementById('maleCoaches');
            const femaleCoachesBtn = document.getElementById('femaleCoaches');
            
            // Define available coaches per program with gender information (in a real app, this would come from a database)
            const programCoaches = {
                "1": [ // Strength Training
                    { id: "1", firstName: "Michael", lastName: "Johnson", gender: "MALE" },
                    { id: "2", firstName: "Jessica", lastName: "Parker", gender: "FEMALE" }
                ],
                "2": [ // Cardio
                    { id: "3", firstName: "Sarah", lastName: "Williams", gender: "FEMALE" },
                    { id: "4", firstName: "Robert", lastName: "Brown", gender: "MALE" }
                ],
                "3": [ // Yoga
                    { id: "5", firstName: "Emma", lastName: "Davis", gender: "FEMALE" },
                    { id: "6", firstName: "David", lastName: "Wilson", gender: "MALE" }
                ],
                "4": [ // CrossFit
                    { id: "7", firstName: "Lisa", lastName: "Martinez", gender: "FEMALE" },
                    { id: "8", firstName: "James", lastName: "Taylor", gender: "MALE" }
                ]
            };
            
            let currentFilter = 'ALL';
            
            // Filter buttons event listeners
            allCoachesBtn.addEventListener('click', function() {
                setActiveFilter(this);
                currentFilter = 'ALL';
                updateCoachOptions();
            });
            
            maleCoachesBtn.addEventListener('click', function() {
                setActiveFilter(this);
                currentFilter = 'MALE';
                updateCoachOptions();
            });
            
            femaleCoachesBtn.addEventListener('click', function() {
                setActiveFilter(this);
                currentFilter = 'FEMALE';
                updateCoachOptions();
            });
            
            function setActiveFilter(button) {
                // Remove active class from all buttons
                allCoachesBtn.classList.remove('bg-primary-dark', 'text-white');
                maleCoachesBtn.classList.remove('bg-primary-dark', 'text-white');
                femaleCoachesBtn.classList.remove('bg-primary-dark', 'text-white');
                
                allCoachesBtn.classList.add('bg-gray-200', 'text-gray-700');
                maleCoachesBtn.classList.add('bg-gray-200', 'text-gray-700');
                femaleCoachesBtn.classList.add('bg-gray-200', 'text-gray-700');
                
                // Add active class to clicked button
                button.classList.remove('bg-gray-200', 'text-gray-700');
                button.classList.add('bg-primary-dark', 'text-white');
            }
            
            // When program changes, update available coaches
            programSelect.addEventListener('change', updateCoachOptions);
            
            function updateCoachOptions() {
                // Clear current options
                coachSelect.innerHTML = '<option value="">Select Coach</option>';
                
                if (programSelect.value) {
                    let coaches = programCoaches[programSelect.value];
                    
                    // Apply gender filter if needed
                    if (currentFilter !== 'ALL') {
                        coaches = coaches.filter(coach => coach.gender === currentFilter);
                    }
                    
                    // Add coaches for the selected program
                    coaches.forEach(coach => {
                        const option = document.createElement('option');
                        option.value = coach.id;
                        
                        // Display gender icon
                        const genderIcon = coach.gender === 'MALE' ? '♂' : '♀';
                        option.textContent = `${coach.firstName} ${coach.lastName} ${genderIcon}`;
                        
                        option.setAttribute('data-gender', coach.gender);
                        coachSelect.appendChild(option);
                    });
                    
                    // If no coaches match the filter, show message
                    if (coaches.length === 0) {
                        const option = document.createElement('option');
                        option.disabled = true;
                        option.textContent = `No ${currentFilter.toLowerCase()} coaches available for this program`;
                        coachSelect.appendChild(option);
                    }
                }
            }
        }
        
        // Subscription logic
        function initSubscriptionLogic() {
            const subscriptionType = document.getElementById('subscriptionType');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const subscriptionStatus = document.getElementById('subscriptionStatus');
            const subscriptionSummary = document.getElementById('subscriptionSummary');
            const subscriptionPrice = document.getElementById('subscriptionPrice');
            
            // When subscription type changes
            subscriptionType.addEventListener('change', calculateEndDate);
            startDate.addEventListener('change', calculateEndDate);
            
            function calculateEndDate() {
                const selectedOption = subscriptionType.options[subscriptionType.selectedIndex];
                
                if (subscriptionType.value && startDate.value) {
                    const duration = parseInt(selectedOption.dataset.duration);
                    const price = parseFloat(selectedOption.dataset.price);
                    const start = new Date(startDate.value);
                    
                    // Calculate end date by adding duration in days
                    const end = new Date(start);
                    end.setDate(end.getDate() + duration);
                    endDate.value = end.toISOString().split('T')[0];
                    
                    // Show subscription summary
                    subscriptionStatus.classList.remove('hidden');
                    subscriptionSummary.textContent = `${selectedOption.text} from ${formatDate(start)} to ${formatDate(end)}`;
                    subscriptionPrice.textContent = `Total Amount: ₱${price.toLocaleString('en-PH')}`;
                } else {
                    endDate.value = '';
                    subscriptionStatus.classList.add('hidden');
                }
            }
            
            function formatDate(date) {
                return date.toLocaleDateString('en-PH', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
        }
        
        // Modal toggle functionality
        function initModalToggle() {
            const addMemberBtn = document.getElementById('addMemberBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelButton = document.getElementById('cancelButton');
            const confirmDiscard = document.getElementById('confirmDiscard');
            const cancelDiscard = document.getElementById('cancelDiscard');
            const memberModal = document.getElementById('memberModal');
            const confirmDialog = document.getElementById('confirmDialog');
            const memberForm = document.getElementById('memberForm');
            const saveMemberButton = document.getElementById('saveMemberButton');
            const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
            
            // Store original form values
            let originalFormValues = {};
            let isFormDirty = false;
            
            // Open modal
            addMemberBtn.addEventListener('click', function() {
                memberModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                // Reset form dirty state
                isFormDirty = false;
                // Store the initial state of the form
                setTimeout(() => {
                    captureFormState();
                }, 100);
            });
            
            // Empty state add button
            if (emptyStateAddBtn) {
                emptyStateAddBtn.addEventListener('click', function() {
                    memberModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    // Reset form dirty state
                    isFormDirty = false;
                    // Store the initial state of the form
                    setTimeout(() => {
                        captureFormState();
                    }, 100);
                });
            }
            
            // Close modal button - force showing the confirmation dialog
            closeModal.addEventListener('click', function() {
                const formHasChanges = isFormDirty && hasFormChanged();
                console.log("Form dirty:", isFormDirty, "Has changes:", formHasChanges);
                
                // Always show confirmation if form is dirty
                if (isFormDirty) {
                    confirmDialog.classList.remove('hidden');
                } else {
                    closeModalDirectly();
                }
            });
            
            // Cancel button - force showing the confirmation dialog
            cancelButton.addEventListener('click', function() {
                const formHasChanges = isFormDirty && hasFormChanged();
                console.log("Form dirty:", isFormDirty, "Has changes:", formHasChanges);
                
                // Always show confirmation if form is dirty
                if (isFormDirty) {
                    confirmDialog.classList.remove('hidden');
                } else {
                    closeModalDirectly();
                }
            });
            
            // Direct event listeners to all form inputs to better track changes
            const allInputs = memberForm.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                // For text inputs and selects
                input.addEventListener('input', () => {
                    console.log("Input changed:", input.name || input.id);
                    isFormDirty = true;
                });
                
                // For checkboxes, radios, and dropdowns
                input.addEventListener('change', () => {
                    console.log("Input changed:", input.name || input.id);
                    isFormDirty = true;
                });
                
                // For clicks on inputs (additional safety)
                input.addEventListener('click', () => {
                    setTimeout(() => {
                        isFormDirty = true;
                    }, 100);
                });
            });
            
            // Confirm discard
            confirmDiscard.addEventListener('click', function() {
                confirmDialog.classList.add('hidden');
                closeModalDirectly();
            });
            
            // Cancel discard
            cancelDiscard.addEventListener('click', function() {
                confirmDialog.classList.add('hidden');
            });
            
            // Save member
            saveMemberButton.addEventListener('click', function() {
                if (memberForm.checkValidity()) {
                    // Show loading state
                    saveMemberButton.disabled = true;
                    saveMemberButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

                    // Collect form data
                    const formData = new FormData(memberForm);
                    const memberId = document.getElementById('memberId').value;
                    const data = {
                        MEMBER_FNAME: formData.get('MEMBER_FNAME'),
                        MEMBER_LNAME: formData.get('MEMBER_LNAME'),
                        EMAIL: formData.get('EMAIL'),
                        PHONE_NUMBER: formData.get('PHONE_NUMBER'),
                        IS_ACTIVE: document.getElementById('status').checked ? 1 : 0,
                        PROGRAM_ID: document.getElementById('program').value,
                        USER_ID: 1, // Current logged in user ID
                        COMORBIDITIES: Array.from(document.getElementById('comorbidities').selectedOptions).map(opt => opt.value)
                    };

                    // Determine if this is an add or edit operation
                    const url = memberId ? 
                        `../../api/members/update.php?id=${memberId}` : 
                        '../../api/members/create.php';

                    const method = memberId ? 'PUT' : 'POST';

                    // If editing, include subscription data but don't allow changes
                    if (memberId) {
                        data.MEMBER_ID = memberId;
                    } else {
                        // Only include subscription data for new members
                        data.SUB_ID = document.getElementById('subscriptionType').value;
                        data.START_DATE = document.getElementById('startDate').value;
                        data.END_DATE = document.getElementById('endDate').value;
                        data.PAYMENT_ID = document.getElementById('paymentMethod').value;
                        data.TRANSAC_DATE = document.getElementById('transactionDate').value;
                    }

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            showToast(memberId ? 'Member updated successfully!' : 'New member added successfully!', true);
                            closeModalDirectly();
                            loadMembers(); // Reload the members table
                        } else {
                            throw new Error(result.message || 'Failed to save member');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast(error.message || 'Failed to save member. Please try again.', false);
                    })
                    .finally(() => {
                        // Reset button state
                        saveMemberButton.disabled = false;
                        saveMemberButton.innerHTML = '<i class="fas fa-save mr-2"></i> Save Member';
                    });
                } else {
                    memberForm.reportValidity();
                }
            });

            // Function to close modal directly
            function closeModalDirectly() {
                memberModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                memberForm.reset();
                
                // Reset form dirty state
                isFormDirty = false;
                
                // Reset subscription fields to editable state
                toggleSubscriptionEditMode(false);
                
                // Enable program and coach fields
                document.getElementById('program').disabled = false;
                document.getElementById('program').classList.remove('bg-gray-100', 'cursor-not-allowed');
                document.getElementById('coach').disabled = false;
                document.getElementById('coach').classList.remove('bg-gray-100', 'cursor-not-allowed');
                
                // Remove any info notes
                const programEditInfo = document.getElementById('programEditInfo');
                if (programEditInfo) {
                    programEditInfo.remove();
                }
                
                const subscriptionEditInfo = document.getElementById('subscriptionEditInfo');
                if (subscriptionEditInfo) {
                    subscriptionEditInfo.remove();
                }
                
                // Set today's date again
                const today = new Date();
                document.getElementById('startDate').value = today.toISOString().split('T')[0];
                
                // Hide subscription summary
                document.getElementById('subscriptionStatus').classList.add('hidden');
            }
            
            // Capture the initial state of the form
            function captureFormState() {
                originalFormValues = {};
                const formElements = memberForm.elements;
                
                for (let i = 0; i < formElements.length; i++) {
                    const element = formElements[i];
                    const name = element.name || element.id;
                    
                    if (element.type === 'checkbox') {
                        originalFormValues[name] = element.checked;
                    } else if (element.type === 'select-multiple') {
                        const selected = [];
                        for (let j = 0; j < element.options.length; j++) {
                            if (element.options[j].selected) {
                                selected.push(element.options[j].value);
                            }
                        }
                        originalFormValues[name] = selected;
                    } else {
                        originalFormValues[name] = element.value;
                    }
                }
                console.log("Form state captured:", originalFormValues);
            }
            
            // Check if form has been modified (this function is no longer needed, but kept for compatibility)
            function hasFormChanged() {
                return true; // Always return true to ensure dialog shows
            }
            
            // Helper function to compare arrays
            function arraysEqual(a, b) {
                if (a === b) return true;
                if (a == null || b == null) return false;
                if (a.length !== b.length) return false;
                
                for (let i = 0; i < a.length; i++) {
                    if (a[i] !== b[i]) return false;
                }
                
                return true;
            }
        }
        
        // Initialize status toggle buttons
        document.addEventListener('DOMContentLoaded', function() {
            const statusToggles = document.querySelectorAll('.status-toggle');
            
            statusToggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const memberId = this.getAttribute('data-id');
                    const isActive = this.checked;
                    
                    // Show confirmation toast
                    showToast(`Member ${isActive ? 'activated' : 'deactivated'} successfully!`, true);
                    
                    // In a real implementation, you would make an API call here
                    console.log(`Member ${memberId} status changed to ${isActive ? 'active' : 'inactive'}`);
                });
            });
            
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
        
        // Add functionality to view and edit buttons
        function initViewAndEditButtons() {
            // Get all view buttons
            const viewButtons = document.querySelectorAll('.view-button');
            // Get all edit buttons
            const editButtons = document.querySelectorAll('.edit-button');
            
            // Add click event to all view buttons
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const memberId = this.getAttribute('data-id');
                    viewMember(memberId);
                });
            });
            
            // Add click event to all edit buttons
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const memberId = this.getAttribute('data-id');
                    editMember(memberId);
                });
            });
            
            // View member function with subscription data
            function viewMember(id) {
                // Fetch member data from the database instead of static data
                fetch(`/api/members/${id}/details`)
                    .then(response => response.json())
                    .then(member => {
                        if (member) {
                            // Rest of the view member logic remains the same
                            document.getElementById('viewMemberName').textContent = `${member.firstName} ${member.lastName}`;
                            document.getElementById('viewFullName').textContent = `${member.firstName} ${member.lastName}`;
                            document.getElementById('viewEmail').textContent = member.email;
                            document.getElementById('viewPhone').textContent = member.phone;
                            
                            // Display joined date
                            const joinDate = new Date(member.joinDate);
                            document.getElementById('viewJoinDate').textContent = formatDate(joinDate);
                            
                            document.getElementById('viewProgramDetail').textContent = member.programName;
                            document.getElementById('viewCoach').textContent = member.coach;
                            
                            // Populate subscription details
                            document.getElementById('viewSubscriptionPlan').textContent = member.subscriptionName;
                            
                            // Format subscription period
                            const startDate = new Date(member.subscriptionStart);
                            const endDate = new Date(member.subscriptionEnd);
                            document.getElementById('viewSubscriptionPeriod').textContent = 
                                `${formatDate(startDate)} - ${formatDate(endDate)}`;
                            
                            document.getElementById('viewPaymentMethod').textContent = member.paymentMethod;
                            
                            // Status
                            const viewStatus = document.getElementById('viewStatus');
                            if (member.isActive) {
                                viewStatus.innerHTML = `
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                `;
                            } else {
                                viewStatus.innerHTML = `
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactive
                                    </span>
                                `;
                            }
                            
                            // Comorbidities
                            const viewComorbidities = document.getElementById('viewComorbidities');
                            viewComorbidities.innerHTML = '';
                            
                            if (member.comorbidities.length > 0) {
                                member.comorbidities.forEach(comorbidity => {
                                    const span = document.createElement('span');
                                    span.className = 'px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800';
                                    span.textContent = comorbidity;
                                    viewComorbidities.appendChild(span);
                                });
                            } else {
                                const span = document.createElement('span');
                                span.className = 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
                                span.textContent = 'None';
                                viewComorbidities.appendChild(span);
                            }
                            
                            // Set member ID for edit button in view modal
                            document.getElementById('viewEditButton').setAttribute('data-id', id);
                            
                            // Show the view modal
                            document.getElementById('viewMemberModal').classList.remove('hidden');
                            document.body.classList.add('overflow-hidden');
                            
                            // Add event listener to edit button in view modal
                            document.getElementById('viewEditButton').addEventListener('click', function() {
                                const memberId = this.getAttribute('data-id');
                                // Hide view modal
                                document.getElementById('viewMemberModal').classList.add('hidden');
                                // Edit member
                                editMember(memberId);
                            });
                            
                            // Add event listener to close button in view modal
                            document.getElementById('viewCloseButton').addEventListener('click', function() {
                                document.getElementById('viewMemberModal').classList.add('hidden');
                                document.body.classList.remove('overflow-hidden');
                            });
                            
                            // Add event listener to X button in view modal
                            document.getElementById('closeViewModal').addEventListener('click', function() {
                                document.getElementById('viewMemberModal').classList.add('hidden');
                                document.body.classList.remove('overflow-hidden');
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching member details:', error);
                        showToast('Error loading member details', false);
                    });
            }
            
            // Edit member function
            function editMember(id) {
                // Fetch member data from the database instead of static data
                fetch(`/api/members/${id}`)
                    .then(response => response.json())
                    .then(member => {
                        if (member) {
                            // Rest of the edit member logic remains the same
                            document.getElementById('memberId').value = id;
                            document.getElementById('modalTitle').textContent = 'Edit Member';
                            document.getElementById('modalIcon').className = 'fas fa-user-edit text-xl';
                            
                            // Populate form fields with member data
                            document.getElementById('firstName').value = member.firstName;
                            document.getElementById('lastName').value = member.lastName;
                            document.getElementById('email').value = member.email;
                            document.getElementById('phoneNumber').value = member.phone;
                            
                            // Set program and trigger change event to load coaches
                            const programSelect = document.getElementById('program');
                            programSelect.value = member.programId;
                            
                            // Dispatch change event to load coaches
                            const event = new Event('change');
                            programSelect.dispatchEvent(event);
                            
                            // Set coach (with a slight delay to ensure coaches are loaded)
                            setTimeout(() => {
                                const coachSelect = document.getElementById('coach');
                                coachSelect.value = member.coachId;
                            }, 100);
                            
                            // Handle subscription section for edit mode - make read-only
                            toggleSubscriptionEditMode(true, member);
                            
                            // Set status
                            document.getElementById('status').checked = member.isActive;
                            const statusLabel = document.getElementById('statusLabel');
                            if (member.isActive) {
                                statusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                                statusLabel.classList.remove('text-red-600');
                                statusLabel.classList.add('text-green-600');
                            } else {
                                statusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                                statusLabel.classList.remove('text-green-600');
                                statusLabel.classList.add('text-red-600');
                            }
                            
                            // Set comorbidities
                            const comorbidities = document.getElementById('comorbidities');
                            // Reset selection
                            for (let i = 0; i < comorbidities.options.length; i++) {
                                comorbidities.options[i].selected = false;
                            }
                            // Set selected comorbidities
                            member.comorbidities.forEach(comorbidity => {
                                for (let i = 0; i < comorbidities.options.length; i++) {
                                    if (comorbidities.options[i].value === comorbidity) {
                                        comorbidities.options[i].selected = true;
                                    }
                                }
                            });
                            
                            // Show the modal
                            document.getElementById('memberModal').classList.remove('hidden');
                            document.body.classList.add('overflow-hidden');
                            
                            // Store the initial state of the form after populating it
                            // and reset the form dirty state since we're just loading data
                            setTimeout(() => {
                                captureFormState();
                                isFormDirty = false;
                            }, 200);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching member data:', error);
                        showToast('Error loading member data', false);
                    });
            }
        }
        
        // Add this function at the top level of your script section
        function toggleSubscriptionEditMode(isEdit, memberData) {
            // Get subscription elements
            const subscriptionType = document.getElementById('subscriptionType');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const paymentMethod = document.getElementById('paymentMethod');
            const subscriptionStatus = document.getElementById('subscriptionStatus');
            const subscriptionSummary = document.getElementById('subscriptionSummary');
            const subscriptionPrice = document.getElementById('subscriptionPrice');
            
            if (isEdit) {
                // Set subscription display info can't be edited
                const start = new Date(memberData.startDate);
                const end = new Date(memberData.endDate);
                
                // Disable the subscription fields
                subscriptionType.disabled = true;
                subscriptionType.classList.add('bg-gray-100');
                startDate.readOnly = true;
                startDate.classList.add('bg-gray-100');
                paymentMethod.disabled = true;
                paymentMethod.classList.add('bg-gray-100');
                
                // Show subscription info in read-only mode
                subscriptionStatus.classList.remove('hidden');
                
                // Add an info note that subscription can't be edited
                const infoNote = document.createElement('div');
                infoNote.id = 'subscriptionEditInfo';
                infoNote.className = 'mt-2 p-3 bg-yellow-50 rounded-md border border-yellow-100 text-sm text-yellow-800';
                infoNote.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                        <div>
                            <p class="font-medium">Subscription Information</p>
                            <p class="text-xs mt-1">Subscription and payment details cannot be modified in edit mode. To change a subscription, please create a new transaction in the Transactions section.</p>
                        </div>
                    </div>
                `;
                
                // Add the note after the subscription section if it doesn't exist yet
                if (!document.getElementById('subscriptionEditInfo')) {
                    subscriptionStatus.parentNode.insertBefore(infoNote, subscriptionStatus.nextSibling);
                }
                
                // Set values for display
                startDate.value = memberData.startDate;
                endDate.value = memberData.endDate;
                
                // Set subscription plan and payment method for display
                for (let i = 0; i < subscriptionType.options.length; i++) {
                    if (subscriptionType.options[i].value === memberData.subscriptionId) {
                        subscriptionType.selectedIndex = i;
                        break;
                    }
                }
                
                for (let i = 0; i < paymentMethod.options.length; i++) {
                    if (paymentMethod.options[i].text === memberData.paymentMethod) {
                        paymentMethod.selectedIndex = i;
                        break;
                    }
                }
                
                // Update the summary
                subscriptionSummary.textContent = `${memberData.subscriptionName} from ${formatDate(start)} to ${formatDate(end)}`;
                subscriptionPrice.textContent = `Payment Method: ${memberData.paymentMethod}`;
            } else {
                // Enable fields for new member
                subscriptionType.disabled = false;
                subscriptionType.classList.remove('bg-gray-100');
                startDate.readOnly = false;
                startDate.classList.remove('bg-gray-100');
                paymentMethod.disabled = false;
                paymentMethod.classList.remove('bg-gray-100');
                
                // Reset values
                subscriptionType.selectedIndex = 0;
                startDate.value = '';
                endDate.value = '';
                paymentMethod.selectedIndex = 0;
                
                // Set today's date for start date
                const today = new Date();
                startDate.value = today.toISOString().split('T')[0];
                
                // Hide subscription summary
                subscriptionStatus.classList.add('hidden');
                
                // Remove info note if exists
                const infoNote = document.getElementById('subscriptionEditInfo');
                if (infoNote) {
                    infoNote.remove();
                }
            }
        }
        
        // ...existing initialization code...
        
        // Helper function to format date
        function formatDate(date) {
            return date.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // Add this after your existing DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', function() {
            // ...existing initialization code...
            
            // Initialize the program filter dropdown
            initProgramFilter();
            
            // Load members when page loads
            loadMembers();
        });

        // Global variable to track current program filter
        let currentProgramFilter = '';
        let currentSearchTerm = '';

        // Add function to initialize program filter
        function initProgramFilter() {
            const programFilter = document.getElementById('programFilter');
            const searchInput = document.getElementById('searchMembers');
            
            // Load available programs from database
            fetch('../../api/programs/get-all.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Clear existing options except the first one
                        while (programFilter.options.length > 1) {
                            programFilter.remove(1);
                        }
                        
                        // Add program options
                        data.programs.forEach(program => {
                            const option = document.createElement('option');
                            option.value = program.PROGRAM_ID;
                            option.textContent = program.PROGRAM_NAME;
                            programFilter.appendChild(option);
                        });
                    } else {
                        console.error('Error loading programs');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            
            // Add event listener to program filter dropdown
            programFilter.addEventListener('change', function() {
                currentProgramFilter = this.value;
                loadMembers();
            });
            
            // Add event listener to search input
            searchInput.addEventListener('input', function() {
                currentSearchTerm = this.value.trim();
                loadMembers();
            });
        }

        // Modify the loadMembers function to support filtering
        function loadMembers() {
            const tableBody = document.getElementById('memberTableBody');
            const emptyState = document.getElementById('emptyState');
            const loadingState = document.getElementById('loadingState');
            const tableContainer = document.getElementById('membersTable').closest('.overflow-x-auto');
            
            // Show loading state
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            tableContainer.classList.add('hidden');
            
            // Construct URL with filters
            let url = '../../api/members/get-all.php';
            const params = [];
            
            if (currentProgramFilter) {
                params.push(`program_id=${currentProgramFilter}`);
            }
            
            if (currentSearchTerm) {
                params.push(`search=${encodeURIComponent(currentSearchTerm)}`);
            }
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.members.length > 0) {
                            // Clear existing table content
                            tableBody.innerHTML = '';
                            
                            // Add members to table
                            data.members.forEach(member => {
                                const row = document.createElement('tr');
                                const initials = `${member.MEMBER_FNAME[0]}${member.MEMBER_LNAME[0]}`;
                                
                                row.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                                ${initials}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    ${member.MEMBER_FNAME} ${member.MEMBER_LNAME}
                                                </div>
                                                <div class="text-xs text-gray-500">Joined ${formatDate(new Date(member.JOINED_DATE))}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${member.EMAIL}</div>
                                        <div class="text-sm text-gray-500">${member.PHONE_NUMBER}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            ${member.PROGRAM_NAME}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Coach: ${member.COACH_NAME || 'Not Assigned'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${member.IS_ACTIVE == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${member.IS_ACTIVE == 1 ? 'Active' : 'Inactive'}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">${member.SUB_NAME || 'No subscription'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button onclick="handleViewMember(${member.MEMBER_ID})" class="text-primary-dark hover:text-primary-light view-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                                <i class="fas fa-eye text-lg"></i>
                                            </button>
                                            <button onclick="handleEditMember(${member.MEMBER_ID})" class="text-primary-dark hover:text-primary-light edit-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                `;
                                tableBody.appendChild(row);
                            });
                            
                            // Show table, hide empty state
                            emptyState.classList.add('hidden');
                            tableContainer.classList.remove('hidden');
                        } else {
                            // If no members found after filtering
                            const emptyStateMessage = document.getElementById('emptyStateMessage');
                            if (currentProgramFilter) {
                                emptyStateMessage.textContent = `No members found for the selected program. Try another program or clear filters.`;
                            } else {
                                emptyStateMessage.textContent = `No members found. Add members to get started.`;
                            }
                            emptyState.classList.remove('hidden');
                            tableContainer.classList.add('hidden');
                        }
                    } else {
                        showToast('Failed to load members', false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error loading members', false);
                })
                .finally(() => {
                    // Hide loading state
                    loadingState.classList.add('hidden');
                });
        }

        // Add these global functions for handling button clicks
        function handleViewMember(memberId) {
            // Close edit modal if it's open
            const memberModal = document.getElementById('memberModal');
            if (!memberModal.classList.contains('hidden')) {
                memberModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            fetch(`../../api/members/view.php?id=${memberId}`)
                .then(response => response.json())
                .then(member => {
                    if (member) {
                        document.getElementById('viewMemberName').textContent = `${member.MEMBER_FNAME} ${member.MEMBER_LNAME}`;
                        document.getElementById('viewFullName').textContent = `${member.MEMBER_FNAME} ${member.MEMBER_LNAME}`;
                        document.getElementById('viewEmail').textContent = member.EMAIL;
                        document.getElementById('viewPhone').textContent = member.PHONE_NUMBER;
                        document.getElementById('viewJoinDate').textContent = formatDate(new Date(member.JOINED_DATE));
                        document.getElementById('viewProgramDetail').textContent = member.PROGRAM_NAME;
                        document.getElementById('viewCoach').textContent = member.COACH_NAME || 'Not Assigned';
                        
                        // Set status
                        const viewStatus = document.getElementById('viewStatus');
                        if (member.IS_ACTIVE == 1) {
                            viewStatus.innerHTML = `
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Active
                                </span>
                            `;
                        } else {
                            viewStatus.innerHTML = `
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inactive
                                </span>
                            `;
                        }
                        
                        // Set Edit Member button to pass the correct member ID
                        const viewEditButton = document.getElementById('viewEditButton');
                        viewEditButton.setAttribute('data-id', memberId);
                        
                        // Add event listener to the Edit Member button
                        viewEditButton.onclick = function() {
                            // Close view modal
                            document.getElementById('viewMemberModal').classList.add('hidden');
                            // Open edit modal with this member ID
                            handleEditMember(memberId);
                        };
                        
                        // Set subscription details
                        if (member.SUB_NAME) {
                            document.getElementById('viewSubscriptionPlan').textContent = member.SUB_NAME;
                            
                            // Format subscription period from START_DATE and END_DATE
                            if (member.START_DATE && member.END_DATE) {
                                const startDate = new Date(member.START_DATE);
                                const endDate = new Date(member.END_DATE);
                                document.getElementById('viewSubscriptionPeriod').textContent = 
                                    `${formatDate(startDate)} - ${formatDate(endDate)}`;
                            } else {
                                document.getElementById('viewSubscriptionPeriod').textContent = 'No active subscription';
                            }
                        } else {
                            document.getElementById('viewSubscriptionPlan').textContent = 'No subscription plan';
                            document.getElementById('viewSubscriptionPeriod').textContent = 'N/A';
                        }
                        
                        // Add event listeners for closing the modal
                        const viewCloseButton = document.getElementById('viewCloseButton');
                        const closeViewModal = document.getElementById('closeViewModal');
                        const viewMemberModal = document.getElementById('viewMemberModal');
                        
                        // Function to close view modal
                        const closeViewModalHandler = () => {
                            viewMemberModal.classList.add('hidden');
                            document.body.classList.remove('overflow-hidden');
                        };
                        
                        // Remove existing event listeners if any
                        viewCloseButton.removeEventListener('click', closeViewModalHandler);
                        closeViewModal.removeEventListener('click', closeViewModalHandler);
                        
                        // Add event listeners
                        viewCloseButton.addEventListener('click', closeViewModalHandler);
                        closeViewModal.addEventListener('click', closeViewModalHandler);
                        
                        // Show the view modal
                        viewMemberModal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error loading member details', false);
                });
        }

        // Consolidated handleEditMember function (remove duplicate implementations)
        function handleEditMember(memberId) {
            // First close the view modal if it's open
            const viewModal = document.getElementById('viewMemberModal');
            if (!viewModal.classList.contains('hidden')) {
                viewModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            fetch(`../../api/members/view.php?id=${memberId}`)
                .then(response => response.json())
                .then(member => {
                    if (member) {
                        // Set basic info
                        document.getElementById('memberId').value = memberId;
                        document.getElementById('firstName').value = member.MEMBER_FNAME;
                        document.getElementById('lastName').value = member.MEMBER_LNAME;
                        document.getElementById('email').value = member.EMAIL;
                        document.getElementById('phoneNumber').value = member.PHONE_NUMBER;
                        
                        // Set program
                        const programSelect = document.getElementById('program');
                        programSelect.value = member.PROGRAM_ID;
                        
                        // Update coach dropdown with available coaches
                        const coachSelect = document.getElementById('coach');
                        coachSelect.innerHTML = '<option value="">Select Coach</option>';
                        
                        if (member.available_coaches && member.available_coaches.length > 0) {
                            member.available_coaches.forEach(coach => {
                                const option = document.createElement('option');
                                option.value = coach.COACH_ID;
                                const genderIcon = coach.GENDER === 'MALE' ? '♂' : '♀';
                                option.textContent = `${coach.COACH_FNAME} ${coach.COACH_LNAME} ${genderIcon}`;
                                coachSelect.appendChild(option);
                            });
                        } else {
                            // Fallback for when coaches aren't provided by the API
                            const option = document.createElement('option');
                            option.value = "1";  // Default coach ID
                            option.textContent = "Default Coach";
                            coachSelect.appendChild(option);
                        }
                        
                        // Set selected coach
                        if (member.COACH_ID) {
                            setTimeout(() => {
                                coachSelect.value = member.COACH_ID;
                            }, 100);
                        }
                        
                        // Set subscription details
                        const subscriptionType = document.getElementById('subscriptionType');
                        if (member.SUB_ID) {
                            subscriptionType.value = member.SUB_ID;
                            document.getElementById('startDate').value = member.START_DATE;
                            document.getElementById('endDate').value = member.END_DATE;
                        }

                        // Set payment method
                        if (member.PAYMENT_ID) {
                            document.getElementById('paymentMethod').value = member.PAYMENT_ID;
                        }

                        // Set comorbidities if available
                        if (member.comorbidities) {
                            const comorbidities = document.getElementById('comorbidities');
                            // Reset all selections first
                            Array.from(comorbidities.options).forEach(option => {
                                option.selected = false;
                            });
                            // Set selected comorbidities
                            member.comorbidities.forEach(comorbidityId => {
                                Array.from(comorbidities.options).forEach(option => {
                                    if (option.value == comorbidityId) {
                                        option.selected = true;
                                    }
                                });
                            });
                        }

                        // Set status
                        document.getElementById('status').checked = member.IS_ACTIVE == 1;
                        const statusLabel = document.getElementById('statusLabel');
                        if (member.IS_ACTIVE == 1) {
                            statusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                            statusLabel.classList.remove('text-red-600');
                            statusLabel.classList.add('text-green-600');
                        } else {
                            statusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                            statusLabel.classList.remove('text-green-600');
                            statusLabel.classList.add('text-red-600');
                        }

                        // Update subscription summary if exists
                        if (member.SUB_NAME) {
                            document.getElementById('subscriptionStatus').classList.remove('hidden');
                            document.getElementById('subscriptionSummary').textContent = 
                                `${member.SUB_NAME} from ${formatDate(new Date(member.START_DATE))} to ${formatDate(new Date(member.END_DATE))}`;
                        }

                        // Disable program, coach and subscription fields when editing
                        document.getElementById('program').disabled = true;
                        document.getElementById('program').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('coach').disabled = true;
                        document.getElementById('coach').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('subscriptionType').disabled = true;
                        document.getElementById('subscriptionType').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('startDate').readOnly = true;
                        document.getElementById('startDate').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('endDate').readOnly = true;
                        document.getElementById('endDate').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('paymentMethod').disabled = true;
                        document.getElementById('paymentMethod').classList.add('bg-gray-100', 'cursor-not-allowed');

                        // Add info message about non-editable fields
                        const programContainer = document.getElementById('program').closest('div').parentNode;
                        if (!document.getElementById('programEditInfo')) {
                            const infoNote = document.createElement('div');
                            infoNote.id = 'programEditInfo';
                            infoNote.className = 'mt-3 p-3 bg-blue-50 rounded-md border border-blue-100 text-sm text-blue-800';
                            infoNote.innerHTML = `
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                                    <div>
                                        <p class="font-medium">Program & Coach Information</p>
                                        <p class="text-xs mt-1">Program and coach selections cannot be modified. To change these, please create a new member record.</p>
                                    </div>
                                </div>
                            `;
                            programContainer.appendChild(infoNote);
                        }

                        // Add info message about non-editable subscription
                        const subscriptionContainer = document.getElementById('subscriptionType').closest('div').parentNode;
                        if (!document.getElementById('subscriptionEditInfo')) {
                            const infoNote = document.createElement('div');
                            infoNote.id = 'subscriptionEditInfo';
                            infoNote.className = 'mt-3 p-3 bg-yellow-50 rounded-md border border-yellow-100 text-sm text-yellow-800';
                            infoNote.innerHTML = `
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                                    <div>
                                        <p class="font-medium">Subscription Information</p>
                                        <p class="text-xs mt-1">Subscription plan details cannot be modified in edit mode. To change a subscription, please use the Transaction section.</p>
                                    </div>
                                </div>
                            `;
                            subscriptionContainer.appendChild(infoNote);
                        }

                        // Show edit modal with appropriate title
                        document.getElementById('modalTitle').textContent = 'Edit Member';
                        document.getElementById('modalIcon').className = 'fas fa-user-edit text-xl';
                        document.getElementById('memberModal').classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error loading member details', false);
                });
        }
    </script>
</body>
</html>