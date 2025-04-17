<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions - Gymaster Admin</title>
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
                        <button type="button" class="sidebar-menu-item w-full justify-between" aria-controls="dropdown-management" data-collapse-toggle="dropdown-management">
                            <div class="flex items-center">
                                <i class="fas fa-th-large"></i>
                                <span>Management</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="management-chevron"></i>
                        </button>
                        <div id="dropdown-management" class="hidden overflow-hidden transition-all duration-300 ease-in-out">
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
                        <a href="manage-transaction.php" class="sidebar-menu-item active">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transaction</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- Logout placed at the very bottom -->
            <div class="mt-auto border-t border-white/10 relative">
                <a href="#" class="sidebar-menu-item text-white/90 hover:text-white mt-3" id="logoutBtn">
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
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Transactions</h1>
                    
                    <!-- Right Section - User Profile and Notifications -->
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell -->
                        <div class="header-icon-button">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="notification-badge">3</span>
                        </div>
                        
                        <!-- Divider -->
                        <div class="h-8 w-px bg-gray-200 mx-2"></div>
                        
                        <!-- User Profile -->
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

        <div class="container mx-auto px-4 py-4">
            <!-- Transaction Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">Transaction Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Date Range -->
                    <div>
                        <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <select id="dateRange" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="last7days">Last 7 Days</option>
                                <option value="last30days">Last 30 Days</option>
                                <option value="thisMonth">This Month</option>
                                <option value="lastMonth">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range - Start -->
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <input type="date" id="startDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" disabled>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range - End -->
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <input type="date" id="endDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" disabled>
                        </div>
                    </div>
                    
                    <!-- Member Search -->
                    <div>
                        <label for="memberSearch" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" id="memberSearch" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" placeholder="Search by name or ID">
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                    <!-- Subscription Filter -->
                    <div>
                        <label for="subFilter" class="block text-sm font-medium text-gray-700 mb-1">Subscription</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-tag"></i>
                            </div>
                            <select id="subFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Subscriptions</option>
                                <option value="1">Monthly</option>
                                <option value="2">Quarterly</option>
                                <option value="3">Annually</option>
                                <option value="4">Trial</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method Filter -->
                    <div>
                        <label for="paymentFilter" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <select id="paymentFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Methods</option>
                                <option value="1">Credit Card</option>
                                <option value="2">Debit Card</option>
                                <option value="3">Cash</option>
                                <option value="4">Bank Transfer</option>
                                <option value="5">Mobile Payment</option>
                                <option value="6">Online Payment</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 justify-end">
                    <button id="resetFiltersBtn" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-redo-alt"></i> Reset Filters
                    </button>
                    <button id="applyFiltersBtn" class="px-4 py-2.5 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors flex items-center gap-2">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
            
            <!-- Transaction Results Section -->
            <div id="transactionResults">
                <!-- Transaction Header with Export Options -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Transactions</h2>
                        <p class="text-gray-500 text-sm">Showing all transactions</p>
                    </div>
                    <div class="flex gap-2 mt-3 md:mt-0">
                        <button id="printBtn" class="px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button id="exportBtn" class="px-3 py-2 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Transaction Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Transactions</h3>
                        <p class="text-3xl font-bold text-gray-800" id="totalTransactions">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1" id="transactionGrowth">+0%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Revenue</h3>
                        <p class="text-3xl font-bold text-gray-800" id="totalRevenue">$0.00</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1" id="revenueGrowth">+0%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Recent Transactions</h3>
                        <p class="text-3xl font-bold text-gray-800" id="recentTransactions">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1">+0%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <!-- New Card: Expiring Subscriptions -->
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Expiring Soon</h3>
                        <p class="text-3xl font-bold text-orange-500" id="expiringSubscriptions">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-orange-600 text-sm mr-1">
                                <i class="fas fa-clock"></i>
                            </span>
                            <span class="text-gray-500 text-sm">In next 7 days</span>
                        </div>
                    </div>
                </div>
                
                <!-- Subscription Status Section -->
                <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-medium text-gray-500 uppercase">Subscription Status</h3>
                        <div class="flex gap-2">
                            <button id="refreshSubsBtn" class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors flex items-center gap-2">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="subscriptionStatusBody">
                                <!-- Subscription status rows will be populated here -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs">JD</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Monthly Membership</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">2023-12-01</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">2023-12-31</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            <button class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors flex items-center" title="Deactivate subscription" data-sub-id="1001" data-action="deactivate">
                                                <i class="fas fa-toggle-on mr-1"></i> Deactivate
                                            </button>
                                            <button class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center" title="View transaction details" data-sub-id="1001" data-action="view">
                                                <i class="fas fa-eye mr-1"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs">JS</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Quarterly Membership</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">2023-10-15</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">2024-01-15</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Expiring Soon</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            <button class="px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors flex items-center" title="Renew subscription" data-sub-id="1002" data-action="renew">
                                                <i class="fas fa-sync-alt mr-1"></i> Renew
                                            </button>
                                            <button class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center" title="View transaction details" data-sub-id="1002" data-action="view">
                                                <i class="fas fa-eye mr-1"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Transaction Button -->
                <div class="flex justify-end mb-6">
                    <button id="addTransactionBtn" class="px-4 py-2.5 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add Transaction
                    </button>
                </div>
                
                <!-- Transaction Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Transaction rows will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="addTransactionModal" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Add New Transaction</h3>
                <button onclick="closeModal(document.getElementById('addTransactionModal'))" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addTransactionForm" class="p-5">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Member Select -->
                    <div>
                        <label for="memberSelect" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                        <select id="memberSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                            <option value="">Select Member</option>
                            <option value="1001" data-email="john.doe@example.com" data-phone="555-123-4567">John Doe</option>
                            <option value="1002" data-email="jane.smith@example.com" data-phone="555-234-5678">Jane Smith</option>
                            <option value="1003" data-email="robert.j@example.com" data-phone="555-345-6789">Robert Johnson</option>
                            <option value="1004" data-email="m.rodriguez@example.com" data-phone="555-456-7890">Michael Rodriguez</option>
                            <option value="1005" data-email="amanda.lee@example.com" data-phone="555-567-8901">Amanda Lee</option>
                        </select>
                    </div>
                    
                    <!-- Subscription Select -->
                    <div>
                        <label for="subscriptionSelect" class="block text-sm font-medium text-gray-700 mb-1">Subscription</label>
                        <select id="subscriptionSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                            <option value="">Select Subscription</option>
                            <option value="1" data-duration="1 Month" data-price="49.99">Monthly Membership ($49.99)</option>
                            <option value="2" data-duration="3 Months" data-price="129.99">Quarterly Membership ($129.99)</option>
                            <option value="3" data-duration="12 Months" data-price="499.99">Annual Membership ($499.99)</option>
                        </select>
                    </div>
                    
                    <!-- Payment Method Select -->
                    <div>
                        <label for="paymentSelect" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select id="paymentSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                            <option value="">Select Payment Method</option>
                            <option value="1">Credit Card</option>
                            <option value="2">Debit Card</option>
                            <option value="3">Cash</option>
                            <option value="4">Bank Transfer</option>
                            <option value="5">Mobile Payment</option>
                            <option value="6">Online Payment</option>
                        </select>
                    </div>
                    
                    <!-- Transaction Date -->
                    <div>
                        <label for="transactionDate" class="block text-sm font-medium text-gray-700 mb-1">Transaction Date</label>
                        <input type="date" id="transactionDate" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                    </div>
                </div>
                
                <!-- Subscription Details -->
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Subscription Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Plan</label>
                            <p id="subName" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Duration</label>
                            <p id="subDuration" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                            <p id="subStartDate" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                            <p id="subEndDate" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Price</label>
                            <p id="subPrice" class="text-sm text-gray-900">-</p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 justify-end">
                    <button type="button" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors" onclick="closeModal(document.getElementById('addTransactionModal'))">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2.5 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors">
                        Add Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transactionDetailsModal" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                        <i class="fas fa-info-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Transaction Details</h3>
                        <p class="text-sm text-gray-600">View the details of the transaction.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <!-- Transaction ID and Date -->
                    <div class="flex justify-between items-center">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Transaction ID</label>
                            <p id="detailsTransactionId" class="text-sm text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                            <p id="detailsTransactionDate" class="text-sm text-gray-900">-</p>
                        </div>
                    </div>
                    
                    <!-- Member Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Member Details</h4>
                        <div class="flex items-center mb-2">
                            <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-white text-xs" id="memberInitials">-</div>
                            <div class="ml-3">
                                <p id="detailsMemberName" class="text-sm font-medium text-gray-900">-</p>
                                <p id="detailsMemberId" class="text-xs text-gray-500">-</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                                <p id="detailsMemberEmail" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                                <p id="detailsMemberPhone" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subscription Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Subscription Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Plan</label>
                                <p id="detailsSubName" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Duration</label>
                                <p id="detailsSubDuration" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                                <p id="detailsSubStartDate" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                                <p id="detailsSubEndDate" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Payment Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Method</label>
                                <p id="detailsPaymentMethod" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                                <p id="detailsAmount" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 justify-end">
                    <button type="button" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors" onclick="closeModal(document.getElementById('transactionDetailsModal'))">
                        Close
                    </button>
                    <button type="button" class="px-4 py-2.5 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors" id="printReceiptBtn">
                        Print Receipt
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dropdown toggle functionality
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

            // Date range change handler
            document.getElementById('dateRange').addEventListener('change', function(e) {
                const dateRange = e.target.value;
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                
                // Enable/disable custom date inputs based on selection
                if (dateRange === 'custom') {
                    startDateInput.disabled = false;
                    endDateInput.disabled = false;
                } else {
                    startDateInput.disabled = true;
                    endDateInput.disabled = true;
                    
                    // Set default values based on selection
                    const today = new Date();
                    let startDate = new Date();
                    
                    switch(dateRange) {
                        case 'last7days':
                            startDate.setDate(today.getDate() - 7);
                            break;
                        case 'last30days':
                            startDate.setDate(today.getDate() - 30);
                            break;
                        case 'lastYear':
                            startDate.setFullYear(today.getFullYear() - 1);
                            break;
                    }
                    
                    startDateInput.value = formatDate(startDate);
                    endDateInput.value = formatDate(today);
                }
            });
            
            // Format date for input fields
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            
            // Set up initial date range
            document.getElementById('dateRange').dispatchEvent(new Event('change'));

            // Add logout confirmation functionality
            const logoutButton = document.getElementById('logoutBtn');
            const logoutConfirmDialog = document.getElementById('logoutConfirmDialog');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');
            
            // Change logout link behavior to show confirmation
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
            
            // Function to show logout confirmation dialog
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
            
            // Function to hide logout confirmation dialog
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
            
            // Modal functions
            window.openModal = function(modal) {
                if (!modal) return;
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    const modalContent = modal.querySelector('.transform');
                    if (modalContent) {
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                    }
                }, 10);
            }
            
            window.closeModal = function(modal) {
                if (!modal) return;
                
                const modalContent = modal.querySelector('.transform');
                if (modalContent) {
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                }
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 200);
            }

            // Notification function
            function showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white flex items-center space-x-3 transition-all duration-300 transform translate-y-10 opacity-0 z-50`;
                
                // Set background color based on type
                if (type === 'success') {
                    notification.classList.add('bg-green-600');
                } else if (type === 'error') {
                    notification.classList.add('bg-red-600');
                } else {
                    notification.classList.add('bg-blue-600');
                }
                
                // Set icon based on type
                let icon;
                if (type === 'success') {
                    icon = 'fa-check-circle';
                } else if (type === 'error') {
                    icon = 'fa-exclamation-circle';
                } else {
                    icon = 'fa-info-circle';
                }
                
                // Set content
                notification.innerHTML = `
                    <i class="fas ${icon}"></i>
                    <span>${message}</span>
                `;
                
                // Add notification to body
                document.body.appendChild(notification);
                
                // Show notification with animation
                setTimeout(() => {
                    notification.classList.remove('translate-y-10', 'opacity-0');
                }, 10);
                
                // Hide notification after 3 seconds
                setTimeout(() => {
                    notification.classList.add('translate-y-10', 'opacity-0');
                    
                    // Remove notification from DOM after animation completes
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            };

            // Add Transaction Button
            const addTransactionBtn = document.getElementById('addTransactionBtn');
            const addTransactionModal = document.getElementById('addTransactionModal');
            
            if (addTransactionBtn && addTransactionModal) {
                addTransactionBtn.addEventListener('click', function() {
                    openModal(addTransactionModal);
                });
            }
            
            // Initialize subscription details when subscription is selected
            const subscriptionSelect = document.getElementById('subscriptionSelect');
            if (subscriptionSelect) {
                subscriptionSelect.addEventListener('change', function() {
                    updateSubscriptionDetails();
                });
            }

            function updateSubscriptionDetails() {
                const subscriptionSelect = document.getElementById('subscriptionSelect');
                const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                
                if (selectedOption.value) {
                    // Get data attributes
                    const duration = selectedOption.getAttribute('data-duration');
                    const price = selectedOption.getAttribute('data-price');
                    const name = selectedOption.text.split('(')[0].trim();
                    
                    // Calculate dates
                    const today = new Date();
                    const startDate = formatDate(today);
                    
                    // Calculate end date based on duration
                    const endDate = calculateEndDate(today, duration);
                    
                    // Update UI
                    document.getElementById('subName').textContent = name;
                    document.getElementById('subDuration').textContent = duration;
                    document.getElementById('subStartDate').textContent = startDate;
                    document.getElementById('subEndDate').textContent = formatDate(endDate);
                    document.getElementById('subPrice').textContent = `$${price}`;
                } else {
                    // Reset values
                    document.getElementById('subName').textContent = '-';
                    document.getElementById('subDuration').textContent = '-';
                    document.getElementById('subStartDate').textContent = '-';
                    document.getElementById('subEndDate').textContent = '-';
                    document.getElementById('subPrice').textContent = '-';
                }
            }
            
            // Calculate end date based on duration
            function calculateEndDate(startDate, duration) {
                const date = new Date(startDate);
                if (duration.includes('Month')) {
                    const months = parseInt(duration);
                    date.setMonth(date.getMonth() + months);
                } else if (duration.includes('Year')) {
                    const years = parseInt(duration);
                    date.setFullYear(date.getFullYear() + years);
                }
                return date;
            }
            
            // Initialize action buttons (Deactivate, Renew, View)
            initActionButtons();
            
            function initActionButtons() {
                // Deactivate subscription
                document.querySelectorAll('[data-action="deactivate"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const subId = this.getAttribute('data-sub-id');
                        if (confirm('Are you sure you want to deactivate this subscription?')) {
                            // Show loading state
                            const originalHTML = this.innerHTML;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            this.disabled = true;
                            
                            // Simulate network request
                            setTimeout(() => {
                                // Update UI
                                const row = this.closest('tr');
                                const statusCell = row.querySelector('td:nth-child(5) span');
                                statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
                                statusCell.textContent = 'Inactive';
                                
                                // Update button
                                this.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Renew';
                                this.classList.remove('bg-red-100', 'text-red-700', 'hover:bg-red-200');
                                this.classList.add('bg-green-100', 'text-green-700', 'hover:bg-green-200');
                                this.setAttribute('data-action', 'renew');
                                this.disabled = false;
                                
                                // Show notification
                                showNotification('Subscription deactivated successfully!', 'success');
                            }, 800);
                        }
                    });
                });
                
                // We no longer need the separate event listener for activation
                // The renew functionality is already handled by the existing renew button logic
                // So we're removing the "Activate" event listener and relying on the existing renew functionality

                // Renew subscription
                document.querySelectorAll('[data-action="renew"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const subId = this.getAttribute('data-sub-id');
                        
                        // Get member info from the row
                        const row = this.closest('tr');
                        const memberName = row.querySelector('td:nth-child(1) .text-sm.font-medium').textContent;
                        const subscriptionName = row.querySelector('td:nth-child(2) .text-sm').textContent;
                        
                        // Open Add Transaction modal
                        const modal = document.getElementById('addTransactionModal');
                        if (modal) {
                            // Pre-fill member select with the selected member
                            const memberSelect = document.getElementById('memberSelect');
                            for (let i = 0; i < memberSelect.options.length; i++) {
                                if (memberSelect.options[i].text.includes(memberName)) {
                                    memberSelect.selectedIndex = i;
                                    break;
                                }
                            }
                            
                            // Pre-fill subscription select
                            const subscriptionSelect = document.getElementById('subscriptionSelect');
                            for (let i = 0; i < subscriptionSelect.options.length; i++) {
                                if (subscriptionSelect.options[i].text.includes(subscriptionName)) {
                                    subscriptionSelect.selectedIndex = i;
                                    // Update subscription details
                                    updateSubscriptionDetails();
                                    break;
                                }
                            }
                            
                            // Set transaction date to today
                            const today = new Date();
                            document.getElementById('transactionDate').valueAsDate = today;
                            
                            // Open modal
                            openModal(modal);
                        }
                    });
                });
                
                // View transaction details
                document.querySelectorAll('[data-action="view"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const subId = this.getAttribute('data-sub-id');
                        
                        // Get info from the row
                        const row = this.closest('tr');
                        const memberName = row.querySelector('td:nth-child(1) .text-sm.font-medium').textContent;
                        const subscriptionName = row.querySelector('td:nth-child(2) .text-sm').textContent;
                        const startDate = row.querySelector('td:nth-child(3) .text-sm').textContent;
                        const endDate = row.querySelector('td:nth-child(4) .text-sm').textContent;
                        
                        // Open Transaction Details modal
                        const modal = document.getElementById('transactionDetailsModal');
                        if (modal) {
                            // Set transaction details in the modal
                            document.getElementById('detailsTransactionId').textContent = `TRX-${Math.floor(Math.random() * 10000)}`;
                            document.getElementById('detailsTransactionDate').textContent = startDate;
                            document.getElementById('detailsMemberName').textContent = memberName;
                            document.getElementById('detailsMemberId').textContent = `ID: ${subId}`;
                            document.getElementById('detailsSubName').textContent = subscriptionName;
                            document.getElementById('detailsSubDuration').textContent = subscriptionName.includes('Monthly') ? '1 Month' : 
                                                                                        subscriptionName.includes('Quarterly') ? '3 Months' : '12 Months';
                            document.getElementById('detailsSubStartDate').textContent = startDate;
                            document.getElementById('detailsSubEndDate').textContent = endDate;
                            document.getElementById('detailsPaymentMethod').textContent = 'Credit Card';
                            document.getElementById('detailsAmount').textContent = subscriptionName.includes('Monthly') ? '$49.99' : 
                                                                                        subscriptionName.includes('Quarterly') ? '$129.99' : '$499.99';
                            
                            // Set member initials
                            const nameParts = memberName.split(' ');
                            const initials = nameParts.length > 1 ? 
                                            `${nameParts[0][0]}${nameParts[1][0]}` : 
                                            memberName[0];
                            document.getElementById('memberInitials').textContent = initials;
                            
                            // Open modal
                            openModal(modal);
                        }
                    });
                });
            }
            
            // Add form validation and submission
            const addTransactionForm = document.getElementById('addTransactionForm');
            if (addTransactionForm) {
                addTransactionForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form data
                    const memberSelect = document.getElementById('memberSelect');
                    const subscriptionSelect = document.getElementById('subscriptionSelect');
                    const paymentSelect = document.getElementById('paymentSelect');
                    
                    // Validate form
                    if (!memberSelect.value) {
                        showNotification('Please select a member', 'error');
                        return;
                    }
                    
                    if (!subscriptionSelect.value) {
                        showNotification('Please select a subscription', 'error');
                        return;
                    }
                    
                    if (!paymentSelect.value) {
                        showNotification('Please select a payment method', 'error');
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Simulate form submission
                    setTimeout(() => {
                        // Close modal
                        closeModal(addTransactionModal);
                        
                        // Reset form
                        addTransactionForm.reset();
                        
                        // Reset subscription details
                        document.getElementById('subName').textContent = '-';
                        document.getElementById('subDuration').textContent = '-';
                        document.getElementById('subStartDate').textContent = '-';
                        document.getElementById('subEndDate').textContent = '-';
                        document.getElementById('subPrice').textContent = '-';
                        
                        // Show success notification
                        showNotification('Transaction added successfully!', 'success');
                        
                        // Reset button
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }, 1000);
                });
            }
            
            // Set up print receipt button
            const printReceiptBtn = document.getElementById('printReceiptBtn');
            if (printReceiptBtn) {
                printReceiptBtn.addEventListener('click', function() {
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Preparing...';
                    this.disabled = true;
                    
                    // Simulate printing receipt
                    setTimeout(() => {
                        showNotification('Receipt printed successfully!', 'success');
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 1000);
                });
            }
        });
    </script>
</body>
</html>