<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - Gymaster Admin</title>
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
                        <a href="admin-dashboard.php" class="sidebar-menu-item active">
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
                        <a href="manage-transaction.php" class="sidebar-menu-item">
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
                    <h1 class="text-xl font-semibold text-primary-dark">Generate Reports</h1>
                    
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
            <!-- Report Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">Report Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Report Type -->
                    <div>
                        <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <select id="reportType" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="membership">Membership</option>
                                <option value="revenue">Revenue</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date Range -->
                    <div>
                        <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <select id="dateRange" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="last7days">Last 7 Days</option>
                                <option value="last30days">Last 30 Days</option>
                                <option value="lastYear">Last Year</option>
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
                            <input type="date" id="startDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                        </div>
                    </div>
                    
                    <!-- Custom Date Range - End -->
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <input type="date" id="endDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4" id="additionalFilters">
                    <!-- Program Filter -->
                    <div>
                        <label for="programFilter" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <select id="programFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Programs</option>
                                <option value="1">Strength Training</option>
                                <option value="2">Cardio</option>
                                <option value="3">Yoga</option>
                                <option value="4">CrossFit</option>
                                <option value="5">Zumba</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subscription Type -->
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
                    
                    <!-- Member Status -->
                    <div>
                        <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-toggle-on"></i>
                            </div>
                            <select id="statusFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
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
                    <button id="generateReportBtn" class="px-4 py-2.5 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors flex items-center gap-2">
                        <i class="fas fa-chart-line"></i> Generate Report
                    </button>
                </div>
            </div>
            
            <!-- Loading Indicator -->
            <div id="loadingReport" class="hidden bg-white rounded-lg shadow-sm p-10 mb-6 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-dark mb-4"></div>
                <p class="text-gray-600">Generating your report, please wait...</p>
            </div>
            
            <!-- Report Results Section - Initially hidden -->
            <div id="reportResults" class="hidden transition-opacity duration-300 opacity-0">
                <!-- Report Header with Export Options -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Membership Report</h2>
                        <p class="text-gray-500 text-sm" id="reportDateRange">May 1, 2023 - May 31, 2023</p>
                    </div>
                    <div class="flex gap-2 mt-3 md:mt-0">
                        <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="px-3 py-2 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                
                <!-- Report Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Members</h3>
                        <p class="text-3xl font-bold text-gray-800">248</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1">+12%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">New Members</h3>
                        <p class="text-3xl font-bold text-gray-800">37</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1">+6%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Subscriptions</h3>
                        <p class="text-3xl font-bold text-gray-800">42</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1">+8%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                </div>
                
                <!-- Visualization Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Chart 1: Main Report Chart (will hold either membership or revenue data) -->
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-4">Membership Trend</h3>
                        <div class="h-72 relative">
                            <!-- Create separate canvases for each chart type -->
                            <div class="absolute inset-0">
                                <canvas id="membershipTrendChart" class="chart-canvas"></canvas>
                            </div>
                            <div class="absolute inset-0">
                                <canvas id="revenueTrendChart" class="chart-canvas hidden"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart 2: Distribution by Program -->
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-4">Distribution by Program</h3>
                        <div class="h-72">
                            <canvas id="programDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Data Table -->
                <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-medium text-gray-500 uppercase">Detailed Member Data</h3>
                        <div class="relative">
                            <input type="text" placeholder="Search members..." class="px-4 py-2 rounded-md border border-gray-300 focus:border-primary-light focus:ring-primary-light">
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Transaction</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">JD</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                <div class="text-xs text-gray-500">john.doe@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Strength Training</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Monthly ($49.99)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 12, 2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 12, 2023</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">JS</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                                                <div class="text-xs text-gray-500">jane.smith@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Cardio</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Annual ($499.99)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 10, 2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 10, 2023</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">RJ</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Robert Johnson</div>
                                                <div class="text-xs text-gray-500">robert.j@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yoga</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Quarterly ($199.99)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mar 8, 2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mar 8, 2023</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">AL</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Amanda Lee</div>
                                                <div class="text-xs text-gray-500">amanda.lee@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">CrossFit</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Monthly ($49.99)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 15, 2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 15, 2023</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">MR</div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Michael Rodriguez</div>
                                                <div class="text-xs text-gray-500">m.rodriguez@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Strength Training</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Monthly ($49.99)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 2, 2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 2, 2023</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6 mt-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-primary-light hover:bg-gray-50">Previous</a>
                            <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-primary-light hover:bg-gray-50">Next</a>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">24</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left h-5 w-5"></i>
                                    </a>
                                    <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-primary-light px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-light">1</a>
                                    <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-primary-light ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">2</a>
                                    <a href="#" class="relative hidden items-center px-4 py-2 text-sm font-semibold text-primary-light ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 md:inline-flex">3</a>
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-primary-light ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>
                                    <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right h-5 w-5"></i>
                                    </a>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- No Results Message - Initially hidden -->
            <div id="noReportResults" class="hidden bg-white rounded-lg shadow-sm p-10 mb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-chart-bar text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Reports Generated Yet</h3>
                <p class="text-gray-500 max-w-md mx-auto">Please use the filters above to select your report criteria and click the "Generate Report" button.</p>
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

    <!-- Success Notification Popup - Moved to top -->
    <div id="successNotification" class="fixed top-6 right-6 bg-green-50 border-l-4 border-green-500 shadow-md rounded-md p-4 w-80 transform -translate-y-16 opacity-0 transition-all duration-500 z-50 hidden">
        <div class="flex items-center">
            <div class="flex-shrink-0 pt-0.5">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Success!</h3>
                <div class="mt-1 text-sm text-green-700">
                    Report has been successfully generated.
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button id="closeNotification" type="button" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <span class="sr-only">Dismiss</span>
                        <i class="fas fa-times"></i>
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

            // Initialize separate charts for different report types
            // Membership chart
            const membershipTrendCtx = document.getElementById('membershipTrendChart').getContext('2d');
            let membershipTrendChart = null;
            
            // Create the membership chart after a small delay to ensure the canvas is ready
            setTimeout(() => {
                membershipTrendChart = new Chart(membershipTrendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        datasets: [{
                            label: 'Active Members',
                            data: [180, 195, 210, 225, 248],
                            borderColor: '#5C6C90',
                            backgroundColor: 'rgba(92, 108, 144, 0.1)',
                            tension: 0.1,
                            fill: true
                        }, {
                            label: 'New Members',
                            data: [25, 18, 22, 29, 37],
                            borderColor: '#647590',
                            backgroundColor: 'rgba(100, 117, 144, 0.1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false // Remove the legend/radio buttons
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    precision: 0
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }, 100);

            // Revenue chart - use the separate canvas
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            let revenueTrendChart = null;
            
            // Create the revenue chart after a small delay to ensure the canvas is ready
            setTimeout(() => {
                revenueTrendChart = new Chart(revenueTrendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        datasets: [{
                            label: 'Total Revenue',
                            data: [8500, 9200, 10500, 11800, 14200],
                            borderColor: '#4CAF50',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false // Remove the legend/radio buttons
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('en-US', {
                                                style: 'currency',
                                                currency: 'USD'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }, 200);

            // Initialize chart for program distribution
            const programDistributionCtx = document.getElementById('programDistributionChart').getContext('2d');
            const programDistributionChart = new Chart(programDistributionCtx, {
                type: 'bar',
                data: {
                    labels: ['Strength Training', 'Cardio', 'Yoga', 'CrossFit', 'Zumba'],
                    datasets: [{
                        label: 'Members',
                        data: [75, 62, 48, 35, 28],
                        backgroundColor: [
                            'rgba(92, 108, 144, 0.8)',
                            'rgba(100, 117, 144, 0.8)',
                            'rgba(165, 179, 201, 0.8)',
                            'rgba(209, 217, 230, 0.8)',
                            'rgba(234, 238, 245, 0.8)'
                        ],
                        borderColor: [
                            'rgba(92, 108, 144, 1)',
                            'rgba(100, 117, 144, 1)',
                            'rgba(165, 179, 201, 1)',
                            'rgba(209, 217, 230, 1)',
                            'rgba(234, 238, 245, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Already removed
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Store charts for reference
            const charts = {
                membership: membershipTrendChart,
                revenue: revenueTrendChart
            };
            
            // Current active chart
            let activeChart = 'membership';
            
            // Report Type change handler
            document.getElementById('reportType').addEventListener('change', function(e) {
                // No automatic data updates - just change the selection value
                // User needs to click Generate Report button to see changes
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
            
            // Show initial state - No reports generated yet
            document.getElementById('noReportResults').classList.remove('hidden');
            
            // Generate Report Button
            document.getElementById('generateReportBtn').addEventListener('click', function() {
                // Hide previous content and show loading
                document.getElementById('noReportResults').classList.add('hidden');
                document.getElementById('reportResults').classList.add('hidden');
                document.getElementById('reportResults').classList.remove('opacity-100');
                document.getElementById('reportResults').classList.add('opacity-0');
                document.getElementById('loadingReport').classList.remove('hidden');
                    
                // Get selected report type and date range
                const reportType = document.getElementById('reportType').value;
                const dateRange = document.getElementById('dateRange').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                // Update report title based on type
                const reportTitle = document.querySelector('#reportResults h2');
                if (reportTitle) {
                    reportTitle.textContent = reportType === 'membership' ? 'Membership Report' : 'Revenue Report';
                }
                
                // Update date range display
                const dateRangeDisplay = document.getElementById('reportDateRange');
                const formattedStartDate = new Date(startDate).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                const formattedEndDate = new Date(endDate).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                
                if (dateRangeDisplay) {
                    dateRangeDisplay.textContent = `${formattedStartDate} - ${formattedEndDate}`;
                }
                
                // Simulate API call delay - would be replaced with actual data fetching
                setTimeout(function() {
                    // Hide loading and show report
                    document.getElementById('loadingReport').classList.add('hidden');
                    document.getElementById('reportResults').classList.remove('hidden');
                    
                    // Update report content based on report type
                    updateReportDisplay(reportType);
                        
                    // Use setTimeout to ensure transition works properly
                    setTimeout(function() {
                        document.getElementById('reportResults').classList.remove('opacity-0');
                        document.getElementById('reportResults').classList.add('opacity-100');
                    }, 10);
                    
                    // Show success notification
                    showNotification();
                    
                }, 1500); // Simulate 1.5s delay to fetch report data
            });
            
            // Function to update report display based on type
            function updateReportDisplay(reportType) {
                // Get elements to update
                const summaryCards = document.querySelectorAll('#reportResults .grid.grid-cols-1.md\\:grid-cols-3.lg\\:grid-cols-3 .bg-white');
                const chartTitles = document.querySelectorAll('#reportResults .grid.grid-cols-1.lg\\:grid-cols-2 h3');
                
                // Update report title
                const reportTitle = document.querySelector('#reportResults h2');
                if (reportTitle) {
                    reportTitle.textContent = reportType === 'membership' ? 'Membership Report' : 'Revenue Report';
                }
                
                if (reportType === 'membership') {
                    // Update metrics in cards
                    if (summaryCards.length >= 3) {
                        // Card titles
                        summaryCards[0].querySelector('h3').textContent = 'TOTAL MEMBERS';
                        summaryCards[1].querySelector('h3').textContent = 'NEW MEMBERS';
                        summaryCards[2].querySelector('h3').textContent = 'RENEWED SUBSCRIPTIONS';
                        
                        // Card values
                        summaryCards[0].querySelector('p').textContent = '248';
                        summaryCards[1].querySelector('p').textContent = '37';
                        summaryCards[2].querySelector('p').textContent = '42';
                        
                        // Update percentage changes
                        summaryCards[0].querySelector('.flex.items-center .text-green-600').textContent = '+12%';
                        summaryCards[1].querySelector('.flex.items-center .text-green-600').textContent = '+6%';
                        summaryCards[2].querySelector('.flex.items-center .text-green-600').textContent = '+8%';
                    }
                    
                    // Update chart titles
                    if (chartTitles.length >= 1) {
                        chartTitles[0].textContent = 'MEMBERSHIP TREND';
                    }
                    
                    // Show membership chart, hide revenue chart
                    document.getElementById('membershipTrendChart').classList.remove('hidden');
                    document.getElementById('revenueTrendChart').classList.add('hidden');
                } else {
                    // Update metrics for revenue report
                    if (summaryCards.length >= 3) {
                        // Card titles
                        summaryCards[0].querySelector('h3').textContent = 'TOTAL REVENUE';
                        summaryCards[1].querySelector('h3').textContent = 'NEW SUBSCRIPTIONS';
                        summaryCards[2].querySelector('h3').textContent = 'RENEWAL REVENUE';
                        
                        // Card values
                        summaryCards[0].querySelector('p').textContent = '$14,200';
                        summaryCards[1].querySelector('p').textContent = '$4,500';
                        summaryCards[2].querySelector('p').textContent = '$9,700';
                        
                        // Update percentage text colors
                        summaryCards[0].querySelector('.flex.items-center .text-green-600').textContent = '+18%';
                        summaryCards[1].querySelector('.flex.items-center .text-green-600').textContent = '+12%';
                        summaryCards[2].querySelector('.flex.items-center .text-green-600').textContent = '+5%';
                    }
                    
                    // Update chart titles
                    if (chartTitles.length >= 1) {
                        chartTitles[0].textContent = 'REVENUE TREND';
                    }
                    
                    // Show revenue chart, hide membership chart
                    document.getElementById('membershipTrendChart').classList.add('hidden');
                    document.getElementById('revenueTrendChart').classList.remove('hidden');
                }
                
                // Update table headers and data based on report type
                const tableHeaders = document.querySelectorAll('#reportResults table thead th');
                if (reportType === 'membership') {
                    if (tableHeaders.length >= 6) {
                        tableHeaders[0].textContent = 'Member Name';
                        tableHeaders[1].textContent = 'Program';
                        tableHeaders[2].textContent = 'Subscription';
                        tableHeaders[3].textContent = 'Join Date';
                        tableHeaders[4].textContent = 'Status';
                        tableHeaders[5].textContent = 'Last Transaction';
                    }
                    
                    // Reset table data for membership view
                    const tableRows = document.querySelectorAll('#reportResults table tbody tr');
                    tableRows.forEach((row, index) => {
                        const cells = row.querySelectorAll('td');
                        
                        if (cells.length >= 6) {
                            // Program column
                            cells[1].textContent = ['Strength Training', 'Cardio', 'Yoga', 'CrossFit', 'Strength Training'][index];
                            
                            // Subscription column
                            cells[2].textContent = ['Monthly ($49.99)', 'Annual ($499.99)', 'Quarterly ($199.99)', 'Monthly ($49.99)', 'Monthly ($49.99)'][index];
                            
                            // Status column - restore badges
                            const statusBadges = [
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>'
                            ];
                            cells[4].innerHTML = statusBadges[index];
                            
                            // Last transaction
                            const transactionDates = ['May 12, 2023', 'Jan 10, 2023', 'Mar 8, 2023', 'Apr 15, 2023', 'May 2, 2023'];
                            cells[5].textContent = transactionDates[index];
                        }
                    });
                } else {
                    if (tableHeaders.length >= 6) {
                        tableHeaders[0].textContent = 'Member Name';
                        tableHeaders[1].textContent = 'Subscription Type';
                        tableHeaders[2].textContent = 'Payment Amount';
                        tableHeaders[3].textContent = 'Payment Date';
                        tableHeaders[4].textContent = 'Payment Method';
                        tableHeaders[5].textContent = 'Next Billing';
                    }
                    
                    // Update table data for revenue report
                    const tableRows = document.querySelectorAll('#reportResults table tbody tr');
                    tableRows.forEach((row, index) => {
                        const cells = row.querySelectorAll('td');
                        // Keep the first cell (member name) unchanged
                        
                        // Update other cells for revenue view
                        if (cells.length >= 6) {
                            // Subscription Type
                            cells[1].textContent = ['Monthly', 'Annual', 'Quarterly', 'Monthly', 'Monthly'][index];
                            
                            // Payment Amount
                            cells[2].textContent = ['$49.99', '$499.99', '$199.99', '$49.99', '$49.99'][index];
                            
                            // Payment Date remains the same
                            
                            // Payment Method
                            const methodBadges = [
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Credit Card</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">PayPal</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Bank Transfer</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Credit Card</span>',
                                '<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Credit Card</span>'
                            ];
                            cells[4].innerHTML = methodBadges[index];
                            
                            // Next Billing
                            const nextDates = ['Jun 12, 2023', 'Jan 10, 2024', 'Jun 8, 2023', 'May 15, 2023', 'Jun 2, 2023'];
                            cells[5].textContent = nextDates[index];
                        }
                    });
                }
                
                // Force chart resize to render properly
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    
                    // Update chart visibility again after resize
                    if (reportType === 'membership') {
                        document.getElementById('membershipTrendChart').style.display = 'block';
                        document.getElementById('revenueTrendChart').style.display = 'none';
                    } else {
                        document.getElementById('membershipTrendChart').style.display = 'none';
                        document.getElementById('revenueTrendChart').style.display = 'block';
                    }
                }, 100);
            }
            
            // Reset Filters Button - use the ID instead of the text content selector
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                // Reset all form inputs
                document.getElementById('reportType').value = 'membership';
                document.getElementById('dateRange').value = 'last30days';
                document.getElementById('dateRange').dispatchEvent(new Event('change'));
                document.getElementById('programFilter').value = 'all';
                document.getElementById('subFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
            });
            
            // Success notification functions
            function showNotification() {
                const notification = document.getElementById('successNotification');
                notification.classList.remove('hidden');
                // Use setTimeout to ensure CSS transition works
                setTimeout(() => {
                    notification.classList.remove('-translate-y-16', 'opacity-0');
                }, 10);
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideNotification();
                }, 5000);
            }
            
            function hideNotification() {
                const notification = document.getElementById('successNotification');
                notification.classList.add('-translate-y-16', 'opacity-0');
                
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 500);
            }
            
            // Close notification button
            document.getElementById('closeNotification').addEventListener('click', hideNotification);
            
            // Initialize charts for reports
            // ...existing code...
        });
    </script>
</body>
</html>
