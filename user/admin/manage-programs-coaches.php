<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs & Coaches | Gymaster</title>
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
                                    <a href="manage-members.php" class="sidebar-dropdown-item">Member</a>
                                </li>
                                <li>
                                    <a href="manage-programs-coaches.php" class="sidebar-dropdown-item bg-white/10">Program & Coach</a>
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
                    <h1 class="text-xl font-semibold text-primary-dark">Manage Programs & Coaches</h1>
                    
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
                        <h2 class="text-xl font-semibold text-primary-dark">Programs & Coaches Management</h2>
                        <p class="text-gray-600 text-sm">Add, edit, and manage programs and coaches</p>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex gap-2 flex-wrap">
                        <!-- Search field - Updated to match members page style with increased width -->
                        <div class="relative">
                            <input type="text" id="globalSearch" placeholder="Search programs or coaches..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-primary-light w-72 md:w-80">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <button id="addProgramBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Program
                        </button>
                        <button id="addCoachBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Coach
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tabs - Keep only Programs and Coaches tabs -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-4" aria-label="Tabs">
                        <button id="programs-tab" role="tab" aria-selected="true" class="px-4 py-2 text-sm font-medium text-primary-dark border-b-2 border-primary-light">
                            Programs
                        </button>
                        <button id="coaches-tab" role="tab" aria-selected="false" class="px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-primary-dark hover:border-primary-light">
                            Coaches
                        </button>
                    </nav>
                </div>
                
                <!-- Tab Panels -->
                <div id="programs" role="tabpanel" class="p-6">
                    <!-- Programs Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="programsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Strength Training</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-program-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="1">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Cardio</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-program-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="2">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Yoga</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-program-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="3">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">CrossFit</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-program-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="4">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="coaches" role="tabpanel" class="p-6 hidden">
                    <!-- Coaches Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="coachesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coach</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                                MJ
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Mike Johnson</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">mike.j@example.com</div>
                                        <div class="text-sm text-gray-500">+1 (555) 123-4567</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                bg-blue-100 text-blue-800">
                                                <i class="fas fa-dumbbell mr-1"></i>Strength Training
                                            </span>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                bg-purple-100 text-purple-800">
                                                <i class="fas fa-running mr-1"></i>CrossFit
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-coach-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="1">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                                SW
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">Sarah Williams</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">sarah.w@example.com</div>
                                        <div class="text-sm text-gray-500">+1 (555) 987-6543</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                bg-green-100 text-green-800">
                                                <i class="fas fa-spa mr-1"></i>Yoga
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-coach-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="2">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-primary-light rounded-full flex items-center justify-center text-white text-xs">
                                                DR
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">David Rodriguez</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">david.r@example.com</div>
                                        <div class="text-sm text-gray-500">+1 (555) 456-7890</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                bg-purple-100 text-purple-800">
                                                <i class="fas fa-running mr-1"></i>CrossFit
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button class="text-primary-dark hover:text-primary-light edit-coach-button h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200" data-id="3">
                                                <i class="fas fa-edit text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty state for coaches (add this) -->
                    <div id="emptyCoachesState" class="py-8 text-center hidden">
                        <i class="fas fa-user-tie text-gray-300 text-5xl mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-600">No coaches found</h3>
                        <p class="text-gray-500 mb-4" id="emptyCoachesStateMessage">Add coaches to get started or try a different search term.</p>
                        <button id="emptyStateAddCoachBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors">
                            <i class="fas fa-plus mr-2"></i> Add New Coach
                        </button>
                    </div>
                    
                    <!-- Loading state for coaches (add this) -->
                    <div id="loadingCoachesState" class="py-8 text-center hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                        <p class="mt-2 text-gray-600">Loading coaches...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Program Modal -->
    <div id="programModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 modal-content transform scale-95 overflow-hidden">
            <!-- Modal Title Banner -->
            <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i id="modalIcon" class="fas fa-dumbbell text-xl"></i>
                    </div>
                    <div>
                        <h2 id="programModalTitle" class="text-lg font-medium text-white leading-tight">Add New Program</h2>
                        <p class="text-xs text-white/90">Enter the required information below</p>
                    </div>
                </div>
                <button type="button" id="closeProgramModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <form id="programForm" class="space-y-3">
                    <input type="hidden" id="programId" name="programId">

                    <!-- Program Information Section -->
                    <div class="mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-info-circle text-primary-light mr-2"></i>
                            <span>Program Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <label for="programName" class="block text-sm font-medium text-gray-700 mb-1">Program Name</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <input type="text" id="programName" name="PROGRAM_NAME" 
                                class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" 
                                placeholder="Enter program name" required>
                        </div>
                    </div>

                    <!-- Program Status Section -->
                    <div class="mb-1 mt-6">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-toggle-on text-primary-light mr-2"></i>
                            <span>Program Status</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program Status</label>
                        <div class="flex items-center">
                            <div class="relative inline-block w-12 mr-3 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="programStatus" id="programStatus" checked
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-2 border-gray-300 appearance-none cursor-pointer transition-transform duration-300 ease-in-out">
                                <label for="programStatus" 
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300 ease-in-out"></label>
                            </div>
                            <span id="programStatusLabel" class="text-sm text-green-600 font-medium flex items-center">
                                <i class="fas fa-check-circle mr-1.5"></i> Active
                            </span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" id="cancelProgramBtn" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10">
                    Cancel
                </button>
                <button type="button" id="saveProgramButton" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                    <i class="fas fa-save mr-2"></i> Save Program
                </button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Coach Modal - Modified to include program assignments -->
    <div id="coachModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden modal backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 modal-content transform scale-95 overflow-hidden">
            <!-- Modal Title Banner -->
            <div id="modalBanner" class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-primary-dark to-primary-light relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i id="modalIcon" class="fas fa-user-tie text-xl"></i>
                    </div>
                    <div>
                        <h2 id="coachModalTitle" class="text-lg font-medium text-white leading-tight">Add New Coach</h2>
                        <p class="text-xs text-white/90">Enter the required information below</p>
                    </div>
                </div>
                <button type="button" id="closeCoachModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 pt-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <form id="coachForm" class="space-y-3">
                    <input type="hidden" id="coachId" name="coachId">

                    <!-- Personal Information Section -->
                    <div class="mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-id-card text-primary-light mr-2"></i>
                            <span>Personal Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="coachFname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input type="text" id="coachFname" name="COACH_FNAME" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" 
                                    placeholder="Enter first name" required>
                            </div>
                        </div>
                        <!-- Last Name -->
                        <div>
                            <label for="coachLname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input type="text" id="coachLname" name="COACH_LNAME" 
                                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" 
                                    placeholder="Enter last name" required>
                            </div>
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="GENDER" value="MALE" class="text-primary-light focus:ring-primary-light h-4 w-4" checked>
                                <span class="ml-2 text-sm text-gray-700">Male</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="GENDER" value="FEMALE" class="text-primary-light focus:ring-primary-light h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Female</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="GENDER" value="OTHER" class="text-primary-light focus:ring-primary-light h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Other</span>
                            </label>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="mb-1 mt-6">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-address-card text-primary-light mr-2"></i>
                            <span>Contact Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <label for="coachEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" id="coachEmail" name="EMAIL" 
                                class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" 
                                placeholder="Enter email address" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="coachPhone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-phone"></i>
                            </div>
                            <input type="tel" id="coachPhone" name="PHONE_NUMBER" 
                                class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" 
                                placeholder="Enter phone number" required>
                        </div>
                    </div>

                    <!-- Program Assignments Section - Renamed to Specializations -->
                    <div class="mb-1 mt-6">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-dumbbell text-primary-light mr-2"></i>
                            <span>Specializations</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-xs text-gray-500 mb-3">Select the coach's specializations and program assignments:</p>
                        <div id="programAssignments" class="grid grid-cols-1 gap-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="assign_strength" name="program_assignments[]" value="1" class="h-4 w-4 rounded text-primary-dark focus:ring-primary-light mr-2">
                                <span class="inline-flex items-center px-2 py-1 mr-2 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-dumbbell mr-1"></i>Strength Training
                                </span>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="assign_cardio" name="program_assignments[]" value="2" class="h-4 w-4 rounded text-primary-dark focus:ring-primary-light mr-2">
                                <span class="inline-flex items-center px-2 py-1 mr-2 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-heartbeat mr-1"></i>Cardio
                                </span>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="assign_yoga" name="program_assignments[]" value="3" class="h-4 w-4 rounded text-primary-dark focus:ring-primary-light mr-2">
                                <span class="inline-flex items-center px-2 py-1 mr-2 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-spa mr-1"></i>Yoga
                                </span>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="assign_crossfit" name="program_assignments[]" value="4" class="h-4 w-4 rounded text-primary-dark focus:ring-primary-light mr-2">
                                <span class="inline-flex items-center px-2 py-1 mr-2 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                    <i class="fas fa-running mr-1"></i>CrossFit
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="mb-1 mt-6">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-toggle-on text-primary-light mr-2"></i>
                            <span>Status</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coach Status</label>
                        <div class="flex items-center">
                            <div class="relative inline-block w-12 mr-3 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="IS_ACTIVE" id="coachStatus" checked
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-2 border-gray-300 appearance-none cursor-pointer transition-transform duration-300 ease-in-out">
                                <label for="coachStatus" 
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300 ease-in-out"></label>
                            </div>
                            <span id="coachStatusLabel" class="text-sm text-green-600 font-medium flex items-center">
                                <i class="fas fa-check-circle mr-1.5"></i> Active
                            </span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" id="cancelCoachBtn" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10">
                    Cancel
                </button>
                <button type="button" id="saveCoachButton" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                    <i class="fas fa-save mr-2"></i> Save Coach
                </button>
            </div>
        </div>
    </div>

    <!-- Remove the Assignment Modal since we're now handling assignments in the coach modal -->

    <!-- Delete Confirmation Dialog -->
    <div id="deleteConfirmDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform transition-all">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mr-4">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800" id="deleteConfirmTitle">Remove Assignment</h3>
                        <p class="text-sm text-gray-600" id="deleteConfirmText">Are you sure you want to remove this assignment?</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Dialog -->
    <div id="logoutConfirmDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform transition-all">
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

    <!-- Discard Changes Dialog -->
    <div id="discardChangesDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform transition-all">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 mr-4">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Unsaved Changes</h3>
                        <p class="text-sm text-gray-600">You have unsaved changes. Are you sure you want to discard them?</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button id="continueEditingBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Continue Editing
                    </button>
                    <button id="discardChangesBtn" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition-colors">
                        Discard Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-md shadow-lg z-50 transform translate-x-full opacity-0 transition-all duration-300 flex items-center" style="display: none;">
        <i id="toastIcon" class="fas fa-check-circle mr-2"></i>
        <span id="toastMessage">Operation successful!</span>
        <button class="ml-3 text-white focus:outline-none" onclick="hideToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        // ------ Utility Functions ------
        // Toast Notification
        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }

        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            toast.style.display = 'flex';
            toastMessage.textContent = message;
            toast.classList.remove('translate-x-full', 'opacity-0');
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
            setTimeout(hideToast, 5000);
        }

        // ------ Tab Navigation ------
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tabs - Remove the assignments tab
            const tabElements = [
                {
                    id: 'programs-tab',
                    triggerEl: document.getElementById('programs-tab'),
                    targetEl: document.getElementById('programs')
                },
                {
                    id: 'coaches-tab',
                    triggerEl: document.getElementById('coaches-tab'),
                    targetEl: document.getElementById('coaches')
                }
            ];

            tabElements.forEach(tab => {
                tab.triggerEl.addEventListener('click', function() {
                    // Hide all tabs
                    document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                        panel.classList.add('hidden');
                    });

                    // Show target tab
                    tab.targetEl.classList.remove('hidden');

                    // Update active states
                    document.querySelectorAll('[role="tab"]').forEach(tabButton => {
                        tabButton.setAttribute('aria-selected', 'false');
                        tabButton.classList.remove('border-primary-light', 'text-primary-dark');
                        tabButton.classList.add('border-transparent');
                    });

                    tab.triggerEl.setAttribute('aria-selected', 'true');
                    tab.triggerEl.classList.remove('border-transparent');
                    tab.triggerEl.classList.add('border-primary-light', 'text-primary-dark');
                });
            });

            // Initialize Management Dropdown
            const dropdownButtons = document.querySelectorAll('[data-collapse-toggle]');
            dropdownButtons.forEach(button => {
                const targetId = button.getAttribute('data-collapse-toggle');
                const targetElement = document.getElementById(targetId);
                const chevronIcon = document.getElementById(targetId.replace('dropdown-', '') + '-chevron');

                if (targetElement && targetElement.querySelector('.bg-white\\/10')) {
                    targetElement.style.maxHeight = targetElement.scrollHeight + 'px';
                    targetElement.classList.remove('hidden');
                    if (chevronIcon) {
                        chevronIcon.style.transform = 'rotate(180deg)';
                    }
                }

                button.addEventListener('click', function() {
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

            // Mobile search toggle
            const mobileSearchBtn = document.getElementById('mobileSearchBtn');
            const mobileSearchPanel = document.getElementById('mobileSearchPanel');
            if (mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', function() {
                    mobileSearchPanel.classList.toggle('hidden');
                });
            }

            // Global search functionality
            function setupSearch(searchInputId, tableIds) {
                const searchInput = document.getElementById(searchInputId);
                if (!searchInput) return;

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    tableIds.forEach(tableId => {
                        const table = document.getElementById(tableId);
                        if (!table) return;

                        const rows = table.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const rowText = row.textContent.toLowerCase();
                            if (searchTerm === '' || rowText.includes(searchTerm)) {
                                row.classList.remove('hidden');
                            } else {
                                row.classList.add('hidden');
                            }
                        });

                        // Show "no results" message if all rows are hidden
                        const visibleRows = table.querySelectorAll('tbody tr:not(.hidden)');
                        const noResultsRow = table.querySelector('.no-results-row');
                        if (visibleRows.length === 0) {
                            // Create "no results" row if it doesn't exist
                            if (!noResultsRow) {
                                const tbody = table.querySelector('tbody');
                                const tr = document.createElement('tr');
                                tr.className = 'no-results-row';
                                const td = document.createElement('td');
                                td.setAttribute('colspan', '10'); // Span all columns
                                td.className = 'px-6 py-4 text-center text-gray-500';
                                td.textContent = `No matching results found for "${searchTerm}"`;
                                tr.appendChild(td);
                                tbody.appendChild(tr);
                            } else {
                                noResultsRow.querySelector('td').textContent = `No matching results found for "${searchTerm}"`;
                                noResultsRow.classList.remove('hidden');
                            }
                        } else if (noResultsRow) {
                            noResultsRow.classList.add('hidden');
                        }
                    });
                });
            }

            // Initialize search for both inputs (desktop and mobile)
            setupSearch('globalSearch', ['programsTable', 'coachesTable']);
            setupSearch('mobileGlobalSearch', ['programsTable', 'coachesTable']);

            // Sync the search boxes (typing in one updates the other)
            const globalSearch = document.getElementById('globalSearch');
            const mobileGlobalSearch = document.getElementById('mobileGlobalSearch');
            if (globalSearch && mobileGlobalSearch) {
                globalSearch.addEventListener('input', function() {
                    mobileGlobalSearch.value = this.value;
                    const event = new Event('input');
                    mobileGlobalSearch.dispatchEvent(event);
                });

                mobileGlobalSearch.addEventListener('input', function() {
                    globalSearch.value = this.value;
                    const event = new Event('input');
                    globalSearch.dispatchEvent(event);
                });
            }
        });

        // ------ Program Modal Functions ------
        function initProgramModal() {
            const addProgramBtn = document.getElementById('addProgramBtn');
            const programModal = document.getElementById('programModal');
            const closeProgramModal = document.getElementById('closeProgramModal');
            const cancelProgramBtn = document.getElementById('cancelProgramBtn');
            const programForm = document.getElementById('programForm');
            const editProgramButtons = document.querySelectorAll('.edit-program-button');
            const discardChangesDialog = document.getElementById('discardChangesDialog');
            const continueEditingBtn = document.getElementById('continueEditingBtn');
            const discardChangesBtn = document.getElementById('discardChangesBtn');
            const programStatus = document.getElementById('programStatus');
            const programStatusLabel = document.getElementById('programStatusLabel');

            let originalProgramFormData = '';

            // Function to check if form has been modified
            function isProgramFormModified() {
                const currentFormData = new FormData(programForm);
                let currentDataString = '';
                for (let pair of currentFormData.entries()) {
                    currentDataString += pair[0] + '=' + pair[1] + '&';
                }
                return currentDataString !== originalProgramFormData;
            }

            // Function to save original form state
            function saveProgramFormState() {
                const formData = new FormData(programForm);
                originalProgramFormData = '';
                for (let pair of formData.entries()) {
                    originalProgramFormData += pair[0] + '=' + pair[1] + '&';
                }
            }

            // Open modal for adding new program
            addProgramBtn.addEventListener('click', function() {
                // Reset form
                programForm.reset();
                document.getElementById('programId').value = '';
                document.getElementById('programModalTitle').textContent = 'Add New Program';
                programStatus.checked = true;
                programStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                programStatusLabel.classList.remove('text-red-600');
                programStatusLabel.classList.add('text-green-600');
                programModal.classList.remove('hidden');
                saveProgramFormState();
            });

            // Confirm before closing modal if there are changes
            function confirmCloseModal() {
                if (isProgramFormModified()) {
                    discardChangesDialog.classList.remove('hidden');
                    discardChangesBtn.setAttribute('data-caller', 'program');
                } else {
                    programModal.classList.add('hidden');
                }
            }

            closeProgramModal.addEventListener('click', confirmCloseModal);
            cancelProgramBtn.addEventListener('click', confirmCloseModal);

            // Edit program functionality
            editProgramButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const programId = this.getAttribute('data-id');
                    document.getElementById('programId').value = programId;
                    document.getElementById('programModalTitle').textContent = 'Edit Program';

                    // In a real app, you would fetch program data from the server
                    // For this example, we'll use dummy data based on the table
                    const programs = {
                        '1': { name: 'Strength Training', isActive: true },
                        '2': { name: 'Cardio', isActive: true },
                        '3': { name: 'Yoga', isActive: true },
                        '4': { name: 'CrossFit', isActive: false }
                    };

                    const program = programs[programId];
                    document.getElementById('programName').value = program.name;
                    programStatus.checked = program.isActive;
                    if (program.isActive) {
                        programStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        programStatusLabel.classList.remove('text-red-600');
                        programStatusLabel.classList.add('text-green-600');
                    } else {
                        programStatusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                        programStatusLabel.classList.remove('text-green-600');
                        programStatusLabel.classList.add('text-red-600');
                    }
                    programModal.classList.remove('hidden');
                    saveProgramFormState();
                });
            });

            // Update the form submission handling to use the new button
            const saveProgramButton = document.getElementById('saveProgramButton');
            saveProgramButton.addEventListener('click', function() {
                const programForm = document.getElementById('programForm');
                if (programForm.checkValidity()) {
                    const programId = document.getElementById('programId').value;
                    const programName = document.getElementById('programName').value;
                    const isActive = programStatus.checked;
                    if (programId) {
                        showToast(`Program "${programName}" updated successfully!`);
                    } else {
                        showToast(`Program "${programName}" added successfully!`);
                    }
                    programModal.classList.add('hidden');
                } else {
                    programForm.reportValidity();
                }
            });

            // Toggle status label based on checkbox state
            programStatus.addEventListener('change', function() {
                if (this.checked) {
                    programStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                    programStatusLabel.classList.remove('text-red-600');
                    programStatusLabel.classList.add('text-green-600');
                } else {
                    programStatusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                    programStatusLabel.classList.remove('text-green-600');
                    programStatusLabel.classList.add('text-red-600');
                }
            });
        }

        // ------ Coach Modal Functions with integrated assignments ------
        function initCoachModal() {
            const addCoachBtn = document.getElementById('addCoachBtn');
            const coachModal = document.getElementById('coachModal');
            const closeCoachModal = document.getElementById('closeCoachModal');
            const cancelCoachBtn = document.getElementById('cancelCoachBtn');
            const coachForm = document.getElementById('coachForm');
            const editCoachButtons = document.querySelectorAll('.edit-coach-button');
            const coachStatus = document.getElementById('coachStatus');
            const coachStatusLabel = document.getElementById('coachStatusLabel');
            const discardChangesDialog = document.getElementById('discardChangesDialog');
            const continueEditingBtn = document.getElementById('continueEditingBtn');
            const discardChangesBtn = document.getElementById('discardChangesBtn');

            let originalCoachFormData = '';

            // Function to check if form has been modified
            function isCoachFormModified() {
                const currentFormData = new FormData(coachForm);
                let currentDataString = '';
                for (let pair of currentFormData.entries()) {
                    currentDataString += pair[0] + '=' + pair[1] + '&';
                }
                const assignmentCheckboxes = document.querySelectorAll('input[name="program_assignments[]"]');
                let assignmentsString = '';
                assignmentCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        assignmentsString += checkbox.value + ',';
                    }
                });
                currentDataString += 'assignments=' + assignmentsString;
                return currentDataString !== originalCoachFormData;
            }

            // Function to save original form state
            function saveCoachFormState() {
                const formData = new FormData(coachForm);
                originalCoachFormData = '';
                for (let pair of formData.entries()) {
                    originalCoachFormData += pair[0] + '=' + pair[1] + '&';
                }
                const assignmentCheckboxes = document.querySelectorAll('input[name="program_assignments[]"]');
                let assignmentsString = '';
                assignmentCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        assignmentsString += checkbox.value + ',';
                    }
                });
                originalCoachFormData += 'assignments=' + assignmentsString;
            }

            // Open modal for adding new coach
            addCoachBtn.addEventListener('click', function() {
                // Reset form
                coachForm.reset();
                document.getElementById('coachId').value = '';
                document.getElementById('coachModalTitle').textContent = 'Add New Coach';
                coachStatus.checked = true;
                coachStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                coachStatusLabel.classList.remove('text-red-600');
                coachStatusLabel.classList.add('text-green-600');
                coachModal.classList.remove('hidden');
                saveCoachFormState();
            });

            // Confirm before closing modal if there are changes
            function confirmCloseModal() {
                if (isCoachFormModified()) {
                    discardChangesDialog.classList.remove('hidden');
                    discardChangesBtn.setAttribute('data-caller', 'coach');
                } else {
                    coachModal.classList.add('hidden');
                }
            }

            closeCoachModal.addEventListener('click', confirmCloseModal);
            cancelCoachBtn.addEventListener('click', confirmCloseModal);

            // Edit coach functionality
            editCoachButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const coachId = this.getAttribute('data-id');
                    document.getElementById('coachId').value = coachId;
                    document.getElementById('coachModalTitle').textContent = 'Edit Coach';

                    // In a real app, you would fetch coach data from the server
                    // For this example, we'll use dummy data based on the table
                    const coaches = {
                        '1': { firstName: 'Mike', lastName: 'Johnson', email: 'mike.j@example.com', phone: '+1 (555) 123-4567', gender: 'MALE', isActive: true, programAssignments: ['1', '4'] },
                        '2': { firstName: 'Sarah', lastName: 'Williams', email: 'sarah.w@example.com', phone: '+1 (555) 987-6543', gender: 'FEMALE', isActive: true, programAssignments: ['3'] },
                        '3': { firstName: 'David', lastName: 'Rodriguez', email: 'david.r@example.com', phone: '+1 (555) 456-7890', gender: 'MALE', isActive: false, programAssignments: ['4'] }
                    };

                    const coach = coaches[coachId];
                    document.getElementById('coachFname').value = coach.firstName;
                    document.getElementById('coachLname').value = coach.lastName;
                    document.getElementById('coachEmail').value = coach.email;
                    document.getElementById('coachPhone').value = coach.phone;
                    coachStatus.checked = coach.isActive;
                    if (coach.isActive) {
                        coachStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                        coachStatusLabel.classList.remove('text-red-600');
                        coachStatusLabel.classList.add('text-green-600');
                    } else {
                        coachStatusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                        coachStatusLabel.classList.remove('text-green-600');
                        coachStatusLabel.classList.add('text-red-600');
                    }

                    // Set program assignments checkboxes
                    const assignmentCheckboxes = document.querySelectorAll('input[name="program_assignments[]"]');
                    assignmentCheckboxes.forEach(checkbox => {
                        checkbox.checked = coach.programAssignments.includes(checkbox.value);
                    });

                    // Set gender
                    const genderRadios = document.querySelectorAll('input[name="GENDER"]');
                    genderRadios.forEach(radio => {
                        if (radio.value === coach.gender) {
                            radio.checked = true;
                        }
                    });

                    coachModal.classList.remove('hidden');
                    saveCoachFormState();
                });
            });

            // Update the form submission handling to use the new button and handle assignments
            const saveCoachButton = document.getElementById('saveCoachButton');
            saveCoachButton.addEventListener('click', function() {
                const coachForm = document.getElementById('coachForm');
                if (coachForm.checkValidity()) {
                    const coachId = document.getElementById('coachId').value;
                    const firstName = document.getElementById('coachFname').value;
                    const lastName = document.getElementById('coachLname').value;
                    const assignedPrograms = [];
                    document.querySelectorAll('input[name="program_assignments[]"]:checked').forEach(checkbox => {
                        assignedPrograms.push(checkbox.value);
                    });
                    if (coachId) {
                        showToast(`Coach "${firstName} ${lastName}" updated with ${assignedPrograms.length} program assignments!`);
                    } else {
                        showToast(`Coach "${firstName} ${lastName}" added with ${assignedPrograms.length} program assignments!`);
                    }
                    coachModal.classList.add('hidden');
                } else {
                    coachForm.reportValidity();
                }
            });

            // Toggle status label based on checkbox state
            coachStatus.addEventListener('change', function() {
                if (this.checked) {
                    coachStatusLabel.innerHTML = '<i class="fas fa-check-circle mr-1.5"></i> Active';
                    coachStatusLabel.classList.remove('text-red-600');
                    coachStatusLabel.classList.add('text-green-600');
                } else {
                    coachStatusLabel.innerHTML = '<i class="fas fa-times-circle mr-1.5"></i> Inactive';
                    coachStatusLabel.classList.remove('text-green-600');
                    coachStatusLabel.classList.add('text-red-600');
                }
            });
        }

        // ------ Status Toggle Functions ------
        function initStatusToggles() {
            const statusToggles = document.querySelectorAll('.status-toggle');
            statusToggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const id = this.getAttribute('data-id');
                    const isActive = this.checked;
                    const type = this.closest('table').id === 'programsTable' ? 'Program' : 'Coach';
                    showToast(`${type} ${isActive ? 'activated' : 'deactivated'} successfully!`, true);
                    // In a real implementation, you would make an API call here
                    console.log(`${type} ${id} status changed to ${isActive ? 'active' : 'inactive'}`);
                });
            });
        }

        // ------ Logout Confirmation Functions ------
        function initLogoutConfirmation() {
            const logoutButton = document.querySelector('a[href="../../login.php"]');
            const logoutConfirmDialog = document.getElementById('logoutConfirmDialog');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');

            if (logoutButton) {
                logoutButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    logoutConfirmDialog.classList.remove('hidden');
                });
            }

            if (cancelLogout) {
                cancelLogout.addEventListener('click', function() {
                    logoutConfirmDialog.classList.add('hidden');
                });
            }

            if (confirmLogout) {
                confirmLogout.addEventListener('click', function() {
                    window.location.href = "../../login.php";
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initProgramModal();
            initCoachModal();
            initStatusToggles();
            initLogoutConfirmation();
        });
    </script>
</body>
</html>