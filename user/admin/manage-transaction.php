<?php
require_once '../../config/db_connection.php';
require_once '../../functions/transaction-functions.php';

// Get initial data for dropdowns and summary
$programs = getPrograms();
$subscriptionPlans = getSubscriptionPlans();
$paymentMethods = getPaymentMethods();
$transactionSummary = getTransactionSummary();
$activeSubscriptions = getActiveSubscriptions();
?>
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
            <!-- Transaction Summary Cards - Moved Above Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Transactions</h3>
                    <p class="text-3xl font-bold text-gray-800" id="totalTransactions"><?php echo $transactionSummary['total']; ?></p>
                    <div class="flex items-center mt-2">
                        <span class="text-<?php echo $transactionSummary['growth']['transactions'] >= 0 ? 'green' : 'red'; ?>-600 text-sm mr-1">
                            <?php echo ($transactionSummary['growth']['transactions'] >= 0 ? '+' : '') . $transactionSummary['growth']['transactions']; ?>%
                        </span>
                        <span class="text-gray-500 text-sm">vs previous period</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Revenue</h3>
                    <p class="text-3xl font-bold text-gray-800" id="totalRevenue">$<?php echo number_format($transactionSummary['revenue'], 2); ?></p>
                    <div class="flex items-center mt-2">
                        <span class="text-<?php echo $transactionSummary['growth']['revenue'] >= 0 ? 'green' : 'red'; ?>-600 text-sm mr-1">
                            <?php echo ($transactionSummary['growth']['revenue'] >= 0 ? '+' : '') . $transactionSummary['growth']['revenue']; ?>%
                        </span>
                        <span class="text-gray-500 text-sm">vs previous period</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Recent Transactions</h3>
                    <p class="text-3xl font-bold text-gray-800" id="recentTransactions"><?php echo $transactionSummary['recent']; ?></p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-600 text-sm mr-1">+0%</span>
                        <span class="text-gray-500 text-sm">vs previous period</span>
                    </div>
                </div>
                
                <!-- New Card: Expiring Subscriptions -->
                <div class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow duration-300">
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
            
            <!-- Transaction Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">Transaction Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

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

                    <!-- Subscription Filter -->
                    <div>
                        <label for="subFilter" class="block text-sm font-medium text-gray-700 mb-1">Subscription</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-tag"></i>
                            </div>
                            <select id="subFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Subscriptions</option>
                                <?php 
                                // Modify this query to only fetch active subscriptions
                                $subscriptionPlans = []; 
                                try {
                                    // Include database connection
                                    include '../../includes/db_connection.php';
                                    
                                    // Query to get active subscription plans
                                    $stmt = $conn->prepare("SELECT SUB_ID, SUB_NAME FROM subscription WHERE IS_ACTIVE = 1");
                                    $stmt->execute();
                                    $subscriptionPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } catch(PDOException $e) {
                                    // Log error (don't show to users)
                                    error_log("Database error: " . $e->getMessage());
                                    // Set empty array if error occurs
                                    $subscriptionPlans = [];
                                }
                                
                                foreach ($subscriptionPlans as $sub): 
                                ?>
                                <option value="<?php echo $sub['SUB_ID']; ?>"><?php echo htmlspecialchars($sub['SUB_NAME']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Programs Filter (Changed from Payment Method) -->
                    <div>
                        <label for="programFilter" class="block text-sm font-medium text-gray-700 mb-1">Programs</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <select id="programFilter" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="all">All Programs</option>
                                <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['PROGRAM_ID']; ?>"><?php echo htmlspecialchars($program['PROGRAM_NAME']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                     <!-- Member Search - Adjusted to match date fields -->
                     <div class="lg:col-span-2">
                        <label for="memberSearch" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" id="memberSearch" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" placeholder="Search by name or email">
                            <!-- Add dropdown container for search results -->
                            <div id="filterMemberResults" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto max-h-60 focus:outline-none sm:text-sm hidden">
                                <!-- Search results will appear here -->
                            </div>
                        </div>
                        <!-- Hidden field to store selected member ID -->
                        <input type="hidden" id="selectedFilterMemberId" value="">
                    </div>

                    <!-- Action Buttons - Moved to right side -->
                    <div class="lg:col-span-2 flex justify-end items-end">
                        <div class="grid grid-cols-2 gap-2 w-full">
                            <button id="resetFiltersBtn" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-redo-alt"></i> Reset Filters+
                            </button>
                            <button id="applyFiltersBtn" class="px-4 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction Results Section -->
            <div id="transactionResults">
                <!-- Transaction Header with Transaction History and Add Transaction buttons -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Transactions</h2>
                        <p class="text-gray-500 text-sm mt-1 md:mt-0">Showing all transactions</p>
                    </div>
                    <div class="flex flex-col md:flex-row gap-2 mt-2 md:mt-0">
                        <button id="addTransactionBtn" class="bg-primary-dark hover:bg-black text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Transaction
                        </button>
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
                    
                    <div class="w-full">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="subscriptionStatusBody">
                                <?php if (empty($activeSubscriptions)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No active subscriptions found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activeSubscriptions as $sub): ?>
                                        <tr class="subscription-row <?php echo !$sub['IS_ACTIVE'] ? 'bg-gray-50' : ''; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs">
                                                        <?php echo strtoupper(substr($sub['MEMBER_FNAME'], 0, 1) . substr($sub['MEMBER_LNAME'], 0, 1)); ?>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($sub['MEMBER_FNAME'] . ' ' . $sub['MEMBER_LNAME']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($sub['SUB_NAME']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($sub['START_DATE'])); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($sub['END_DATE'])); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo $sub['PAID_DATE'] ? date('M j, Y', strtotime($sub['PAID_DATE'])) : '-'; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $today = new DateTime();
                                                $endDate = new DateTime($sub['END_DATE']);
                                                $daysLeft = $today->diff($endDate)->days;
                                                $status = '';
                                                $statusClass = '';
                                                
                                                if (!$sub['IS_ACTIVE']) {
                                                    $status = 'Inactive';
                                                    $statusClass = 'bg-red-100 text-red-800';
                                                } elseif ($endDate < $today) {
                                                    $status = 'Expired';
                                                    $statusClass = 'bg-red-100 text-red-800';
                                                } elseif ($daysLeft <= 7) {
                                                    $status = 'Expiring Soon';
                                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                                } else {
                                                    $status = 'Active';
                                                    $statusClass = 'bg-green-100 text-green-800';
                                                }
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($sub['IS_ACTIVE']): ?>
                                                    <button class="text-red-600 hover:text-red-800 transition-colors" 
                                                            title="Deactivate subscription" 
                                                            data-member-id="<?php echo $sub['MEMBER_ID']; ?>"
                                                            data-sub-id="<?php echo $sub['SUB_ID']; ?>" 
                                                            data-action="deactivate">
                                                        <i class="fas fa-toggle-off"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="text-green-600 hover:text-green-800 transition-colors" 
                                                            title="Renew subscription" 
                                                            data-member-id="<?php echo $sub['MEMBER_ID']; ?>"
                                                            data-sub-id="<?php echo $sub['SUB_ID']; ?>" 
                                                            data-action="renew">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Loading state for subscription table -->
                    <div id="subscriptionLoadingState" class="py-8 text-center hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-light"></div>
                        <p class="mt-2 text-gray-600">Loading subscriptions...</p>
                    </div>
                    
                    <!-- Empty state for when no subscriptions match filters -->
                    <div id="subscriptionEmptyState" class="py-8 text-center hidden">
                        <i class="fas fa-filter text-gray-300 text-5xl mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-600">No subscriptions match your filters</h3>
                        <p class="text-gray-500 mb-4">Try adjusting your filter criteria or reset filters.</p>
                        <button id="emptyStateResetBtn" class="bg-primary-dark text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="addTransactionModal" class="fixed inset-0 bg-black bg-opacity-60 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <!-- Modal Title Banner -->
            <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-white leading-tight">Add New Transaction</h2>
                        <p class="text-xs text-white/90">Enter the payment details below</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal(document.getElementById('addTransactionModal'))" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <form id="addTransactionForm" class="space-y-4">
                    
                    <!-- Member Information Section -->
                    <div class="mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-user text-primary-light mr-2"></i>
                            <span>Member Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>

                    <!-- Member Search -->
                    <div>
                        <label for="memberSearch" class="block text-sm font-medium text-gray-700 mb-1">Member Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" id="memberSearch" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" placeholder="Search member by name or email">
                            <div id="memberSearchResults" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto max-h-60 focus:outline-none sm:text-sm hidden">
                                <!-- Search results will appear here -->
                            </div>
                        </div>
                    </div>

                    <!-- Selected Member Information -->
                    <div id="selectedMemberInfo" class="bg-gray-50 p-3 rounded-md mt-2 hidden">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3" id="memberInitials">JD</div>
                            <div>
                                <p id="memberName" class="font-medium text-gray-900">John Doe</p>
                                <p id="memberEmail" class="text-xs text-gray-500">john.doe@example.com</p>
                            </div>
                            <button type="button" id="changeMemberBtn" class="ml-auto text-sm text-blue-600 hover:text-blue-800">
                                Change
                            </button>
                        </div>
                        <input type="hidden" id="selectedMemberId" value="">
                    </div>

                    <!-- Subscription Information Section -->
                    <div class="mt-6 mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-tag text-primary-light mr-2"></i>
                            <span>Subscription Details</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>

                    <!-- Subscription Select -->
                    <div>
                        <label for="subscriptionSelect" class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-tag"></i>
                            </div>
                            <select id="subscriptionSelect" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="">Select Subscription</option>
                                <?php foreach ($subscriptionPlans as $plan): ?>
                                    <option value="<?php echo $plan['SUB_ID']; ?>" 
                                            data-duration="<?php echo isset($plan['DURATION']) ? $plan['DURATION'] : '30 Days'; ?>" 
                                            data-price="<?php echo isset($plan['PRICE']) ? $plan['PRICE'] : '0.00'; ?>">
                                        <?php echo htmlspecialchars($plan['SUB_NAME']); ?> 
                                        (<?php echo isset($plan['DURATION']) ? $plan['DURATION'] : '30 Days'; ?> - 
                                        $<?php echo isset($plan['PRICE']) ? $plan['PRICE'] : '0.00'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transaction Information Section -->
                    <div class="mt-6 mb-1">
                        <h4 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-money-bill-wave text-primary-light mr-2"></i>
                            <span>Payment Information</span>
                        </h4>
                        <div class="w-full h-px bg-gradient-to-r from-primary-light/40 to-transparent mb-3 mt-1"></div>
                    </div>

                    <!-- Payment Method Select -->
                    <div>
                        <label for="paymentSelect" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <select id="paymentSelect" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200 appearance-none bg-white">
                                <option value="">Select Payment Method</option>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?php echo $method['PAYMENT_ID']; ?>">
                                        <?php echo $method['PAY_METHOD']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Add Start Date and End Date Fields -->
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <!-- Start Date -->
                        <div>
                            <label for="startDateInput" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <input type="date" id="startDateInput" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                            </div>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="endDateInput" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <input type="date" id="endDateInput" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200">
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Details Summary -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mt-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Subscription Summary</h3>
                                <div class="mt-2 text-sm space-y-2">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-blue-600 mb-1">Plan</label>
                                            <p id="subName" class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-600 mb-1">Duration</label>
                                            <p id="subDuration" class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-600 mb-1">Start Date</label>
                                            <p id="subStartDate" class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-600 mb-1">End Date</label>
                                            <p id="subEndDate" class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-600 mb-1">Price</label>
                                            <p id="subPrice" class="text-sm font-medium text-gray-900">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors duration-300 shadow-sm font-medium cursor-pointer relative z-10" onclick="closeModal(document.getElementById('addTransactionModal'))">
                    Cancel
                </button>
                <button id="submitTransactionBtn" type="button" class="px-5 py-2.5 bg-primary-dark text-white rounded-lg hover:bg-opacity-90 focus:outline-none transition-all duration-300 shadow-md font-medium flex items-center justify-center cursor-pointer relative z-10">
                    <i class="fas fa-save mr-2"></i> Add Transaction
                </button>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transactionDetailsModal" class="fixed inset-0 bg-black bg-opacity-60 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <!-- Modal Title Banner -->
            <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 relative overflow-hidden">
                <div class="flex items-center z-10">
                    <div class="mr-4 h-10 w-10 rounded-full bg-white/25 flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-file-invoice text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-white leading-tight">Transaction Details</h2>
                        <p class="text-xs text-white/90">View transaction information</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal(document.getElementById('transactionDetailsModal'))" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 focus:outline-none transition-all duration-300 hover:rotate-90 z-20 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Decorative background elements -->
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 max-h-[65vh] overflow-y-auto custom-scrollbar">
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
                            <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-white text-xs" id="detailsMemberInitials">-</div>
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

    <!-- Transaction History Modal -->
    <div id="transactionHistoryModal" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <div class="flex items-center justify-between p-5 border-b border-gray-200">>
                <h3 class="text-lg font-semibold text-gray-800">Transaction History</h3>
                <button onclick="closeModal(document.getElementById('transactionHistoryModal'))" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-5">
                <div class="mb-4">riptions</h4>
                    <h4 class="font-medium text-gray-700 mb-2">Member Information</h4>
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-white text-xs" id="historyMemberInitials">-</div>
                        <div class="ml-3">
                            <p id="historyMemberName" class="text-sm font-medium text-gray-900">-</p>
                            <p id="historyMemberId" class="text-xs text-gray-500">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="transactionHistoryBody">
                            <!-- Sample transaction history records -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TRX-12345</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2023-12-01</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Monthly Membership</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dec 1, 2023 - Dec 31, 2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$49.99</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Credit Card</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center" title="View receipt" data-trx-id="TRX-12345">
                                        <i class="fas fa-file-invoice mr-1"></i> Receipt
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TRX-11456</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2023-11-01</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Monthly Membership</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Nov 1, 2023 - Nov 30, 2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$49.99</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Credit Card</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center" title="View receipt" data-trx-id="TRX-11456">
                                        <i class="fas fa-file-invoice mr-1"></i> Receipt
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TRX-10067</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2023-10-01</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Monthly Membership</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Oct 1, 2023 - Oct 31, 2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$49.99</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Credit Card</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center" title="View receipt" data-trx-id="TRX-10067">
                                        <i class="fas fa-file-invoice mr-1"></i> Receipt
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination controls -->
                <div class="flex justify-between items-center mt-4">
                    <p class="text-sm text-gray-600">Showing <span id="historyStart">1</span> to <span id="historyEnd">3</span> of <span id="historyTotal">3</span> transactions</p>
                    <div class="flex space-x-1">
                        <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</button>
                        <button class="px-3 py-1 bg-primary-dark text-white rounded hover:bg-opacity-90 transition-colors">1</button>
                        <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</button>
                    </div>
                </div>
                
                <!-- Close button at the bottom -->
                <div class="flex justify-end mt-6">
                    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors" onclick="closeModal(document.getElementById('transactionHistoryModal'))">
                        Close
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

    <!-- Confirmation Dialog -->
    <div id="confirmationDialog" class="fixed inset-0 bg-black bg-opacity-30 z-[60] flex items-center justify-center hidden backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform scale-95 overflow-hidden transition-all duration-200">
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                        <i class="fas fa-question-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 id="confirmationTitle" class="text-lg font-semibold text-gray-800">Confirm Action</h3>
                        <p id="confirmationMessage" class="text-sm text-gray-600">Are you sure you want to proceed?</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button id="cancelConfirmation" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmAction" class="px-4 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90 transition-colors">
                        Confirm
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
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script src="../../user/admin/custom-confirmation.js"></script>
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

            // Format date for input fields
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Date range handling for filter inputs - use different variable names to avoid conflict
            const filterStartDate = document.getElementById('startDate');
            const filterEndDate = document.getElementById('endDate');
            
            // Set default date range to current month
            if (filterStartDate && filterEndDate) {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                filterStartDate.value = formatDate(firstDay);
                filterEndDate.value = formatDate(today);
            }

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
            }

            // Add Transaction Button
            const addTransactionBtn = document.getElementById('addTransactionBtn');
            const addTransactionModal = document.getElementById('addTransactionModal');
            if (addTransactionBtn && addTransactionModal) {
                addTransactionBtn.addEventListener('click', function() {
                    openModal(addTransactionModal);
                });
            }

            // Initialize subscription details when subscription is selected - remove redeclarations
            const subscriptionSelect = document.getElementById('subscriptionSelect');
            const startDateInput = document.getElementById('startDateInput');
            const endDateInput = document.getElementById('endDateInput');
            
            if (subscriptionSelect) {
                subscriptionSelect.addEventListener('change', function() {
                    updateSubscriptionDetails();
                });
            }
            
            if (startDateInput) {
                startDateInput.addEventListener('change', function() {
                    if (subscriptionSelect.value) {
                        updateEndDate();
                        updateSubscriptionSummary();
                    }
                });
            }
            
            if (endDateInput) {
                endDateInput.addEventListener('change', function() {
                    updateSubscriptionSummary();
                });
            }
            
            function updateSubscriptionDetails() {
                const subscriptionSelect = document.getElementById('subscriptionSelect');
                const startDateInput = document.getElementById('startDateInput');
                const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                
                if (selectedOption.value) {
                    // If start date is not set yet, default to today
                    if (!startDateInput.value) {
                        const today = new Date();
                        startDateInput.value = formatDate(today);
                    }
                    
                    // Update the end date based on subscription duration
                    updateEndDate();
                    
                    // Update the subscription summary
                    updateSubscriptionSummary();
                } else {
                    // Reset summary values if no subscription selected
                    document.getElementById('subName').textContent = '-';
                    document.getElementById('subDuration').textContent = '-';
                    document.getElementById('subStartDate').textContent = '-';
                    document.getElementById('subEndDate').textContent = '-';
                    document.getElementById('subPrice').textContent = '-';
                }
            }
            
            function updateEndDate() {
                const subscriptionSelect = document.getElementById('subscriptionSelect');
                const startDateInput = document.getElementById('startDateInput');
                const endDateInput = document.getElementById('endDateInput');
                const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                
                if (selectedOption && selectedOption.value && startDateInput.value) {
                    const duration = selectedOption.getAttribute('data-duration');
                    const startDate = new Date(startDateInput.value);
                    const endDate = calculateEndDateFromDuration(startDate, duration);
                    
                    // Set the end date input value
                    endDateInput.value = formatDate(endDate);
                }
            }
            
            function updateSubscriptionSummary() {
                const subscriptionSelect = document.getElementById('subscriptionSelect');
                const startDateInput = document.getElementById('startDateInput');
                const endDateInput = document.getElementById('endDateInput');
                const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                
                if (selectedOption && selectedOption.value && startDateInput.value) {
                    // Get data attributes from the option
                    const duration = selectedOption.getAttribute('data-duration');
                    const price = selectedOption.getAttribute('data-price');
                    const name = selectedOption.text.split('(')[0].trim();
                    
                    // Get dates
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);
                    
                    // Update subscription summary fields
                    document.getElementById('subName').textContent = name;
                    document.getElementById('subDuration').textContent = duration;
                    document.getElementById('subStartDate').textContent = formatDisplayDate(startDate);
                    document.getElementById('subEndDate').textContent = formatDisplayDate(endDate);
                    document.getElementById('subPrice').textContent = '$' + price;
                }
            }
            
            // Function to calculate end date based on start date and duration
            function calculateEndDateFromDuration(startDate, durationString) {
                const date = new Date(startDate);
                
                // Parse the duration string to get the number and unit
                const durationMatch = durationString.match(/(\d+)\s*(Day|Days|Month|Months|Year|Years)/i);
                
                if (!durationMatch) {
                    // If format is not as expected, try numeric parsing
                    const days = parseInt(durationString);
                    if (!isNaN(days)) {
                        date.setDate(date.getDate() + days);
                        return date;
                    }
                    return date; // Return original date if parsing fails
                }
                
                const amount = parseInt(durationMatch[1]);
                const unit = durationMatch[2].toLowerCase();
                
                if (unit.includes('day')) {
                    date.setDate(date.getDate() + amount);
                } else if (unit.includes('month')) {
                    date.setMonth(date.getMonth() + amount);
                } else if (unit.includes('year')) {
                    date.setFullYear(date.getFullYear() + amount);
                }
                
                return date;
            }
            
            // Function to format date for input fields (YYYY-MM-DD)
            function formatDate(date) {
                if (!(date instanceof Date) || isNaN(date)) {
                    return '';
                }
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            
            // Function to format date for display (readable format)
            function formatDisplayDate(date) {
                if (!(date instanceof Date) || isNaN(date)) {
                    return '-';
                }
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long', 
                    day: 'numeric'
                });
            }

            // Set default start date when page loads
            window.addEventListener('load', function() {
                const today = new Date();
                // Use existing variable
                if (startDateInput) {
                    startDateInput.value = formatDate(today);
                }
            });

            // Initialize action buttons (Deactivate, Renew, View)
            initActionButtons();

            function initActionButtons() {
                // Deactivate subscription - Needs confirmation
                document.querySelectorAll('[data-action="deactivate"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const memberId = this.getAttribute('data-member-id');
                        const subId = this.getAttribute('data-sub-id');
                        
                        // Validate that we have both IDs
                        if (!memberId || !subId) {
                            console.error("Missing data attributes:", { memberId, subId });
                            showToast('Error: Missing subscription or member information', false);
                            return;
                        }
                        
                        // Show confirmation dialog for deactivation
                        showConfirmationDialog(
                            'Confirm Deactivation',
                            'Are you sure you want to deactivate this subscription?',
                            () => {
                                // Get the row element
                                const row = this.closest('tr');
                                if (!row) {
                                    showToast('Error: Could not find the subscription row', false);
                                    return;
                                }
                                
                                // Call the deactivation function with all necessary parameters
                                deactivateSubscription(memberId, subId, row);
                            }
                        );
                    });
                });

                // Set up direct renewal modal opening for existing renew buttons
                document.querySelectorAll('[data-action="renew"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const memberId = this.getAttribute('data-member-id');
                        const subId = this.getAttribute('data-sub-id');
                        const row = this.closest('tr');
                        const memberName = row.querySelector('td:nth-child(1) .text-sm.font-medium').textContent;
                        const subscriptionName = row.querySelector('td:nth-child(2) .text-sm').textContent;
                        
                        // Open renewal form modal
                        openRenewModal(memberId, subId, memberName, subscriptionName);
                    });
                });
            }

            // Function to open renew modal with pre-filled data
            function openRenewModal(memberName, subscriptionName) {
                const modal = document.getElementById('addTransactionModal');
                if (modal) {
                    // Show the modal first
                    openModal(modal);
                    
                    // Get member search section and completely remove it for renewal modal
                    const memberSearchContainer = document.getElementById('memberSearch').parentElement.parentElement;
                    memberSearchContainer.classList.add('hidden');
                    
                    // Show member info without the search UI or change option
                    const selectedMemberInfo = document.getElementById('selectedMemberInfo');
                    selectedMemberInfo.classList.remove('hidden');
                    
                    // Replace the heading to indicate member is fixed for renewal
                    const memberInfoSection = document.querySelector('.mb-1');
                    if (memberInfoSection) {
                        const memberHeading = memberInfoSection.querySelector('span');
                        if (memberHeading) {
                            memberHeading.textContent = "Member (Fixed for Renewal)";
                        }
                    }
                    
                    // Set member information in the static display
                    const memberInitials = document.getElementById('memberInitials');
                    const memberNameElement = document.getElementById('memberName');
                    const memberEmail = document.getElementById('memberEmail');
                    const selectedMemberId = document.getElementById('selectedMemberId');
                    const changeMemberBtn = document.getElementById('changeMemberBtn');
                    
                    // Set member details
                    const initials = memberName.split(' ').map(n => n[0]).join('');
                    memberInitials.textContent = initials;
                    memberNameElement.textContent = memberName;
                    memberEmail.textContent = memberName.toLowerCase().replace(' ', '.') + '@example.com';
                    selectedMemberId.value = '1001'; // This would be replaced with actual ID
                    
                    // Completely hide the change button for renewals
                    if (changeMemberBtn) {
                        changeMemberBtn.classList.add('hidden');
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
                    
                    // Set start date to today
                    const today = new Date();
                    document.getElementById('startDateInput').valueAsDate = today;
                    
                    // Calculate and set end date based on subscription
                    const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                    if (selectedOption.value) {
                        const duration = selectedOption.getAttribute('data-duration');
                        const endDate = calculateEndDate(today, duration);
                        document.getElementById('endDateInput').valueAsDate = endDate;
                    }
                    
                    // Change modal title to indicate renewal
                    const modalTitle = modal.querySelector('.text-lg.font-medium.text-white');
                    if (modalTitle) {
                        modalTitle.textContent = "Renew Subscription";
                    }
                    const modalSubtitle = modal.querySelector('.text-xs.text-white/90');
                    if (modalSubtitle) {
                        modalSubtitle.textContent = "Renew subscription for " + memberName;
                    }
                    
                    // Change submit button text
                    const submitButton = modal.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Renew Subscription';
                    }
                }
            }

            // Modify form submission reset to restore the UI for regular add transaction
            const addTransactionForm = document.getElementById('addTransactionForm');
            if (addTransactionForm) {
                addTransactionForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent default form submission
                    
                    // Get form fields
                    const memberId = document.getElementById('selectedMemberId').value;
                    const subscriptionId = document.getElementById('subscriptionSelect').value;
                    const paymentMethod = document.getElementById('paymentSelect').value;
                    const startDate = document.getElementById('startDateInput').value;
                    const endDate = document.getElementById('endDateInput').value;
                    
                    // Validate form
                    if (!memberId) {
                        showToast('Please select a member', false);
                        return;
                    }
                    
                    if (!subscriptionId) {
                        showToast('Please select a subscription plan', false);
                        return;
                    }
                    
                    if (!paymentMethod) {
                        showToast('Please select a payment method', false);
                        return;
                    }
                    
                    if (!startDate || !endDate) {
                        showToast('Please set the subscription period', false);
                        return;
                    }
                    
                    // Show loading state on the button
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Process the transaction submission
                    processTransactionSubmission();
                    
                    function processTransactionSubmission() {
                        // Get subscription details for the notification
                        const subscriptionSelect = document.getElementById('subscriptionSelect');
                        const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                        const subscriptionName = selectedOption.text.split('(')[0].trim();
                        const memberName = document.getElementById('memberName').textContent;
                        
                        // Simulate API call with timeout
                        setTimeout(() => {
                            // Close modal
                            closeModal(document.getElementById('addTransactionModal'));
                            
                            // Reset form
                            addTransactionForm.reset();
                            
                            // Reset the UI for future new transactions
                            resetTransactionModalUI();
                            
                            // Update summary cards (simulating data refresh)
                            updateSummaryCards();
                            
                            // Show success notification using the toast
                            showToast(`${subscriptionName} successfully added for ${memberName}!`, true);
                            
                            // Reset button
                            submitBtn.innerHTML = originalBtnText;
                            submitBtn.disabled = false;
                        }, 1000);
                    }
                });
            }
            
            // Function to update summary cards after transaction
            function updateSummaryCards() {
                // Get current values
                const totalTransactions = document.getElementById('totalTransactions');
                const totalRevenue = document.getElementById('totalRevenue');
                const recentTransactions = document.getElementById('recentTransactions');
                
                // Increment values
                totalTransactions.textContent = (parseInt(totalTransactions.textContent) + 1).toString();
                
                // Update revenue - assuming we have a way to get the price
                const currentRevenue = parseFloat(totalRevenue.textContent.replace('$', ''));
                const subscriptionPrice = parseFloat(document.getElementById('subPrice').textContent.replace('$', ''));
                totalRevenue.textContent = '$' + (currentRevenue + subscriptionPrice).toFixed(2);
                
                // Increment recent transactions
                recentTransactions.textContent = (parseInt(recentTransactions.textContent) + 1).toString();
                
                // Update growth percentages (for demonstration)
                document.getElementById('transactionGrowth').textContent = '+5%';
                document.getElementById('revenueGrowth').textContent = '+7%';
            }

            // When the modal is closed with the cancel button, also reset the UI
            document.querySelector('button[onclick="closeModal(document.getElementById(\'addTransactionModal\'))"]')
                .addEventListener('click', function() {
                    // Check if form has been modified
                    const formChanged = hasFormChanged();
                    
                    if (formChanged) {
                        // Show confirmation dialog
                        showConfirmationDialog(
                            'Discard Changes',
                            'Are you sure you want to cancel? Any unsaved changes will be lost.',
                            () => {
                                // If confirmed, close the modal
                                closeModal(document.getElementById('addTransactionModal'));
                                // Give time for the close animation to finish before resetting
                                setTimeout(resetTransactionModalUI, 300);
                            }
                        );
                    } else {
                        // If no changes, close directly
                        closeModal(document.getElementById('addTransactionModal'));
                        // Give time for the close animation to finish before resetting
                        setTimeout(resetTransactionModalUI, 300);
                    }
                });

            // Improved function to check if form has changed
            function hasFormChanged() {
                const form = document.getElementById('addTransactionForm');
                
                // Check if any field has been filled
                const memberId = document.getElementById('selectedMemberId').value;
                const memberSearch = document.getElementById('memberSearch').value;
                const subscriptionSelect = document.getElementById('subscriptionSelect').value;
                const paymentSelect = document.getElementById('paymentSelect').value;
                const startDateInput = document.getElementById('startDateInput').value;
                const endDateInput = document.getElementById('endDateInput').value;
                
                // Check if any field has been filled
                return memberId || memberSearch || subscriptionSelect || paymentSelect || startDateInput || endDateInput;
            }
            
            // Track form changes for the transaction form
            const transactionFormInputs = document.querySelectorAll('#addTransactionForm input, #addTransactionForm select');
            let formDirty = false;
            
            transactionFormInputs.forEach(input => {
                if (input) {
                    // For all input and change events
                    ['input', 'change'].forEach(eventType => {
                        input.addEventListener(eventType, function() {
                            formDirty = true;
                            console.log('Form modified:', input.id || input.name);
                        });
                    });
                }
            });
            
            // Also track the X button at the top of the modal for confirmation
            const closeModalButton = document.querySelector('#addTransactionModal button[type="button"]');
            if (closeModalButton) {
                closeModalButton.removeAttribute('onclick');
                closeModalButton.addEventListener('click', function() {
                    const formChanged = hasFormChanged() && formDirty;
                    
                    if (formChanged) {
                        // Show confirmation dialog
                        showConfirmationDialog(
                            'Discard Changes',
                            'Are you sure you want to cancel? Any unsaved changes will be lost.',
                            () => {
                                // If confirmed, close the modal
                                closeModal(document.getElementById('addTransactionModal'));
                                // Reset form dirty state
                                formDirty = false;
                                // Give time for the close animation to finish before resetting
                                setTimeout(resetTransactionModalUI, 300);
                            }
                        );
                    } else {
                        // If no changes, close directly
                        closeModal(document.getElementById('addTransactionModal'));
                        // Give time for the close animation to finish before resetting
                        setTimeout(resetTransactionModalUI, 300);
                    }
                });
            }

            // Function to reset the transaction modal UI back to add transaction mode
            function resetTransactionModalUI() {
                const modal = document.getElementById('addTransactionModal');
                if (!modal) return;
                
                // Reset form dirty state
                formDirty = false;
                
                // Restore the original member section heading
                const memberInfoSection = document.querySelector('.mb-1');
                if (memberInfoSection) {
                    const memberHeading = memberInfoSection.querySelector('span');
                    if (memberHeading) {
                        memberHeading.textContent = "Member Information";
                    }
                }
                
                // Show the member search field again
                const memberSearchContainer = document.getElementById('memberSearch').parentElement.parentElement;
                if (memberSearchContainer) {
                    memberSearchContainer.classList.remove('hidden');
                }
                
                // Reset member search field
                const memberSearchInput = document.getElementById('memberSearch');
                if (memberSearchInput) {
                    memberSearchInput.value = '';
                    memberSearchInput.disabled = false;
                }
                
                // Hide the selected member info
                const selectedMemberInfo = document.getElementById('selectedMemberInfo');
                if (selectedMemberInfo) {
                    selectedMemberInfo.classList.add('hidden');
                }
                
                // Show the change member button
                const changeMemberBtn = document.getElementById('changeMemberBtn');
                if (changeMemberBtn) {
                    changeMemberBtn.classList.remove('hidden');
                }
                
                // Reset the modal title
                const modalTitle = modal.querySelector('.text-lg.font-medium.text-white');
                if (modalTitle) {
                    modalTitle.textContent = "Add New Transaction";
                }
                const modalSubtitle = modal.querySelector('.text-xs.text-white/90');
                if (modalSubtitle) {
                    modalSubtitle.textContent = "Enter the payment details below";
                }
                
                // Reset the submit button
                const submitButton = modal.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fas fa-save mr-2"></i> Add Transaction';
                }
            }

            // Add filter application functionality
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    // Show loading state
                    const tableBody = document.getElementById('subscriptionStatusBody');
                    const loadingState = document.getElementById('subscriptionLoadingState');
                    const emptyState = document.getElementById('subscriptionEmptyState');
                    
                    // Hide table body and empty state, show loading
                    tableBody.closest('table').classList.add('hidden');
                    emptyState.classList.add('hidden');
                    loadingState.classList.remove('hidden');
                    
                    // Get filter values
                    const filterData = {
                        startDate: document.getElementById('startDate').value,
                        endDate: document.getElementById('endDate').value,
                        subscription: document.getElementById('subFilter').value,
                        program: document.getElementById('programFilter').value,
                        memberId: document.getElementById('selectedFilterMemberId').value,
                        memberSearch: document.getElementById('selectedFilterMemberId').value ? '' : document.getElementById('memberSearch').value
                    };
                    
                    // Make API call to get filtered data
                    fetch('../../functions/filter-transactions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(filterData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loading state
                        loadingState.classList.add('hidden');
                        
                        if (data.length === 0) {
                            // Show empty state if no results
                            emptyState.classList.remove('hidden');
                        } else {
                            // Populate table with results
                            tableBody.innerHTML = ''; // Clear existing rows
                            
                            data.forEach(sub => {
                                // Calculate status class based on days left
                                let statusClass, statusText;
                                
                                if (!sub.isActive) {
                                    statusClass = 'bg-red-100 text-red-800';
                                    statusText = 'Inactive';
                                } else if (sub.daysLeft < 0) {
                                    statusClass = 'bg-red-100 text-red-800';
                                    statusText = 'Expired';
                                } else if (sub.daysLeft <= 7) {
                                    statusClass = 'bg-yellow-100 text-yellow-800';
                                    statusText = daysLeft === 0 ? 'Expires Today' : 
                                                 daysLeft === 1 ? 'Expires Tomorrow' : 
                                                 `Expires in ${daysLeft} days`;
                                } else {
                                    statusClass = 'bg-green-100 text-green-800';
                                    statusText = 'Active';
                                }
                                
                                // Create row HTML - FIXED: properly set data-member-id and data-sub-id
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs">
                                                ${sub.memberInitials}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    ${sub.memberName}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${sub.subscriptionName}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${sub.startDate}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${sub.endDate}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${sub.paidDate || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                            ${statusText}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        ${sub.isActive ? 
                                            `<button class="text-red-600 hover:text-red-800 transition-colors" 
                                                    title="Deactivate subscription" 
                                                    data-member-id="${sub.memberId}"
                                                    data-sub-id="${sub.subId}" 
                                                    data-action="deactivate">
                                                <i class="fas fa-toggle-off"></i>
                                            </button>` :
                                            `<button class="text-green-600 hover:text-green-800 transition-colors" 
                                                    title="Renew subscription" 
                                                    data-member-id="${sub.memberId}"
                                                    data-sub-id="${sub.subId}" 
                                                    data-action="renew">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>`
                                        }
                                    </td>
                                `;
                                
                                tableBody.appendChild(row);
                            });
                            
                            // Show the table
                            tableBody.closest('table').classList.remove('hidden');
                            
                            // Reinitialize action buttons
                            initActionButtons();
                        }
                        
                        // Update summary numbers
                        document.getElementById('expiringSubscriptions').textContent = 
                            data.filter(sub => sub.daysLeft >= 0 && sub.daysLeft <= 7 && sub.isActive).length;
                            
                        // Show success notification
                        showToast('Filters applied successfully', true);
                    })
                    .catch(error => {
                        console.error('Error applying filters:', error);
                        loadingState.classList.add('hidden');
                        showToast('Error applying filters. Please try again.', false);
                        
                        // Show table again in case of error
                        tableBody.closest('table').classList.remove('hidden');
                    });
                });
            }

            // Add reset filters functionality
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    resetFilters();
                });
            }
            
            // Also add reset functionality to the empty state button
            const emptyStateResetBtn = document.getElementById('emptyStateResetBtn');
            if (emptyStateResetBtn) {
                emptyStateResetBtn.addEventListener('click', function() {
                    resetFilters();
                });
            }
            
            // Function to reset filters
            function resetFilters() {
                // Reset all filter fields
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                
                document.getElementById('startDate').value = formatDate(firstDay);
                document.getElementById('endDate').value = formatDate(today);
                document.getElementById('memberSearch').value = '';
                document.getElementById('selectedFilterMemberId').value = '';
                document.getElementById('subFilter').selectedIndex = 0;
                document.getElementById('programFilter').selectedIndex = 0;
                
                // Show notification
                showToast('Filters reset!', true);
                
                // Reload data with no filters
                document.getElementById('applyFiltersBtn').click();
            }
            
            // Add refresh subscriptions functionality
            const refreshSubsBtn = document.getElementById('refreshSubsBtn');
            if (refreshSubsBtn) {
                refreshSubsBtn.addEventListener('click', function() {
                    // Show loading state
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                    this.disabled = true;
                    
                    // Reset filters and reload data
                    document.getElementById('startDate').value = '';
                    document.getElementById('endDate').value = '';
                    document.getElementById('memberSearch').value = '';
                    document.getElementById('subFilter').selectedIndex = 0;
                    document.getElementById('programFilter').selectedIndex = 0;
                    
                    // Apply empty filters to get all data
                    document.getElementById('applyFiltersBtn').click();
                    
                    // Reset button after a delay
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                        showToast('Subscriptions refreshed!', true);
                        
                        // Update expiring count
                        fetchExpiringCount();
                    }, 800);
                });
            }

            // ...existing code...
            
            // Function to fetch expiring subscriptions count
            function fetchExpiringCount() {
                fetch('../../functions/get-expiring-count.php')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('expiringSubscriptions').textContent = data.count;
                    })
                    .catch(error => console.error('Error fetching expiring count:', error));
            }

            // Call this on page load
            fetchExpiringCount();
            
            // Initialize action buttons
            initActionButtons();
            
            // ...existing code...
        });

        // Functions for the toast notification (matching member UI)
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

        // Show confirmation dialog
        function showConfirmationDialog(title, message, onConfirm) {
            const confirmationDialog = document.getElementById('confirmationDialog');
            const confirmationTitle = document.getElementById('confirmationTitle');
            const confirmationMessage = document.getElementById('confirmationMessage');
            const confirmAction = document.getElementById('confirmAction');
            const cancelConfirmation = document.getElementById('cancelConfirmation');

            confirmationTitle.textContent = title;
            confirmationMessage.textContent = message;

            // Update button styles to match member UI
            if (title === 'Discard Changes') {
                confirmAction.textContent = 'Discard Changes';
                confirmAction.classList.remove('bg-primary-dark');
                confirmAction.classList.add('bg-red-600', 'hover:bg-red-700');
                
                cancelConfirmation.textContent = 'Continue Editing';
            } else {
                confirmAction.textContent = 'Confirm';
                confirmAction.classList.remove('bg-red-600', 'hover:bg-red-700');
                confirmAction.classList.add('bg-primary-dark');
                
                cancelConfirmation.textContent = 'Cancel';
            }

            // Show dialog
            confirmationDialog.classList.remove('hidden');
            setTimeout(() => {
                const dialogContent = confirmationDialog.querySelector('.transform');
                if (dialogContent) {
                    dialogContent.classList.remove('scale-95');
                    dialogContent.classList.add('scale-100');
                }
            }, 10);

            // Confirm action
            confirmAction.onclick = function() {
                hideConfirmationDialog();
                if (onConfirm) onConfirm();
            };

            // Cancel action
            cancelConfirmation.onclick = hideConfirmationDialog;
        }

        // Hide confirmation dialog
        function hideConfirmationDialog() {
            const confirmationDialog = document.getElementById('confirmationDialog');
            const dialogContent = confirmationDialog.querySelector('.transform');
            if (dialogContent) {
                dialogContent.classList.remove('scale-100');
                dialogContent.classList.add('scale-95');
            }
            setTimeout(() => {
                confirmationDialog.classList.add('hidden');
            }, 200);
        }
        
        // Enhanced form validation with visual error indicators - modified to show only inline errors
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to form fields - reuse the existing variables, don't redeclare
            const memberSearchInput = document.getElementById('memberSearch').closest('#addTransactionForm #memberSearch');
            // Don't redeclare these variables, they're already defined above
            // const subscriptionSelect = document.getElementById('subscriptionSelect');
            const paymentSelect = document.getElementById('paymentSelect');
            const submitTransactionBtn = document.getElementById('submitTransactionBtn');
            
            if (submitTransactionBtn) {
                // Replace the existing click event with enhanced validation
                submitTransactionBtn.addEventListener('click', function() {
                    // Get form fields
                    const memberId = document.getElementById('selectedMemberId').value;
                    const subscriptionId = subscriptionSelect.value;
                    const paymentMethod = paymentSelect.value;
                    // Use the existing variables without redeclaration
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    
                    // Reset previous error states
                    document.querySelectorAll('#addTransactionForm .error-border').forEach(el => {
                        el.classList.remove('error-border');
                    });
                    document.querySelectorAll('#addTransactionForm .error-message').forEach(el => {
                        el.remove();
                    });
                    
                    // Validate form and show inline errors
                    let hasErrors = false;
                    
                    // Member validation - check just once, avoid duplicate errors
                    if (!memberId) {
                        hasErrors = true;
                        // Find the member search field and add error only once
                        const memberSearchContainer = document.querySelector('#addTransactionForm #memberSearch').closest('div');
                        if (memberSearchContainer && !memberSearchContainer.querySelector('.error-message')) {
                            highlightError(memberSearchContainer, 'Please select a member');
                        }
                    }
                    
                    // Subscription validation
                    if (!subscriptionId) {
                        hasErrors = true;
                        highlightError(subscriptionSelect.parentElement, 'Please select a subscription plan');
                    }
                    
                    // Payment method validation
                    if (!paymentMethod) {
                        hasErrors = true;
                        highlightError(paymentSelect.parentElement, 'Please select a payment method');
                    }
                    
                    // Date validation
                    if (!startDate) {
                        hasErrors = true;
                        highlightError(startDateInput.parentElement, 'Please set a start date');
                    }
                    
                    if (!endDate) {
                        hasErrors = true;
                        highlightError(endDateInput.parentElement, 'Please set an end date');
                    }
                    
                    // If validation fails, exit
                    if (hasErrors) {
                        return;
                    }
                    
                    // Show loading state on the button
                    const originalBtnText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                    this.disabled = true;
                    
                    // First check if the member already has an active subscription on the start date
                    fetch('../../functions/check-active-subscription.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            memberId: memberId,
                            startDate: startDate,
                            endDate: endDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasActiveSubscription) {
                            // Show error for overlapping subscription
                            highlightError(startDateInput.parentElement, 'Member already has an active subscription during this period');
                            throw new Error('Member already has an active subscription that overlaps with these dates');
                        }
                        
                        // If no active subscription for this date, proceed with creating the transaction
                        // Get subscription details for the notification
                        const selectedOption = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                        const subscriptionName = selectedOption.text.split('(')[0].trim();
                        const memberName = document.getElementById('memberName').textContent;
                        const memberInitials = document.getElementById('memberInitials').textContent;
                        const price = selectedOption.dataset.price;
                        
                        // Create transaction data to send to server
                        const transactionData = {
                            memberId: memberId,
                            subscriptionId: subscriptionId,
                            paymentId: paymentMethod,
                            startDate: startDate,
                            endDate: endDate
                        };
                        
                        // Send transaction data to server
                        return fetch('../../functions/create-transaction.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(transactionData)
                        });
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close modal
                            closeModal(document.getElementById('addTransactionModal'));
                            
                            // Reset form
                            document.getElementById('addTransactionForm').reset();
                            
                            // Reset the UI for future new transactions
                            resetTransactionModalUI();
                            
                            // Update summary cards
                            updateSummaryCards();
                            
                            // Get the entered dates
                            const startDateFormatted = formatDisplayDate(new Date(startDate));
                            const endDateFormatted = formatDisplayDate(new Date(endDate));
                            
                            // Add the new transaction to the table with the admin-entered dates
                            addTransactionToTable({
                                memberId: memberId,
                                memberName: data.transaction.memberName,
                                memberInitials: getInitials(data.transaction.memberName),
                                subscriptionName: data.transaction.subscriptionName,
                                startDate: startDateFormatted,
                                endDate: endDateFormatted,
                                paidDate: formatDisplayDate(new Date()),
                                isActive: 1,
                                daysLeft: calculateDaysLeft(endDate)
                            });
                            
                            // Show success notification
                            showToast(data.message || `${data.transaction.subscriptionName} successfully added for ${data.transaction.memberName}!`, true);
                        } else {
                            // Show error notification
                            showToast(data.message || 'Transaction failed. Please try again.', false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast(error.message || 'An error occurred while processing the transaction. Please try again.', false);
                    })
                    .finally(() => {
                        // Reset button state
                        this.innerHTML = originalBtnText;
                        this.disabled = false;
                    });
                });
            }
            
            // Function to highlight error fields with a red border and message
            function highlightError(fieldContainer, message) {
                // Add red border to the input
                const input = fieldContainer.querySelector('input, select');
                if (input) {
                    input.classList.add('border-red-500', 'error-border');
                    input.classList.remove('border-gray-300');
                }
                
                // Add error message below the field
                const errorMessage = document.createElement('p');
                errorMessage.className = 'text-xs text-red-600 mt-1 error-message';
                errorMessage.textContent = message;
                fieldContainer.appendChild(errorMessage);
            }
            
            // Add event listeners to clear error state when input changes
            const modalFormInputs = document.querySelectorAll('#addTransactionForm input, #addTransactionForm select');
            modalFormInputs.forEach(input => {
                if (input) {
                    input.addEventListener('change', function() {
                        // Remove red border
                        this.classList.remove('border-red-500', 'error-border');
                        this.classList.add('border-gray-300');
                        
                        // Remove error message if it exists
                        const errorMessage = this.parentElement.querySelector('.error-message');
                        if (errorMessage) errorMessage.remove();
                    });
                }
            });
        });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Member search functionality
    const memberSearchInput = document.getElementById('memberSearch');
    const memberSearchResults = document.getElementById('memberSearchResults');
    const selectedMemberInfo = document.getElementById('selectedMemberInfo');
    let searchTimeout;

    memberSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();

        // Clear results if search term is too short
        if (searchTerm.length < 2) {
            memberSearchResults.classList.add('hidden');
            return;
        }

        // Add loading indicator
        memberSearchResults.innerHTML = `
            <div class="px-4 py-2 text-sm text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
            </div>
        `;
        memberSearchResults.classList.remove('hidden');

        // Debounce the search
        searchTimeout = setTimeout(() => {
            // Log the search request
            console.log('Searching for:', searchTerm);

            fetch(`../../functions/search-members.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(members => {
                    console.log('Search results:', members); // Debug log

                    if (members.length === 0) {
                        memberSearchResults.innerHTML = `
                            <div class="px-4 py-2 text-sm text-gray-500">
                                No members found
                            </div>
                        `;
                        return;
                    }

                    memberSearchResults.innerHTML = members.map(member => `
                        <div class="member-result px-4 py-2 hover:bg-gray-100 cursor-pointer" 
                             data-member-id="${member.id}"
                             data-member-name="${member.name}"
                             data-member-email="${member.email}"
                             data-member-initials="${member.initials}">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3">
                                    ${member.initials}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${member.name}</div>
                                    <div class="text-xs text-gray-500">${member.program || 'No Program'}</div>
                                    <div class="text-xs text-gray-500">${member.subscription}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    // Add click handlers to results
                    document.querySelectorAll('.member-result').forEach(result => {
                        result.addEventListener('click', function() {
                            const memberId = this.dataset.memberId;
                            const memberName = this.dataset.memberName;
                            const memberEmail = this.dataset.memberEmail;
                            const memberInitials = this.dataset.memberInitials;

                            // Update selected member info
                            document.getElementById('selectedMemberId').value = memberId;
                            document.getElementById('memberInitials').textContent = memberInitials;
                            document.getElementById('memberName').textContent = memberName;
                            document.getElementById('memberEmail').textContent = memberEmail;

                            // Show selected member info and hide search results
                            selectedMemberInfo.classList.remove('hidden');
                            memberSearchResults.classList.add('hidden');
                            memberSearchInput.value = '';
                        });
                    });
                })
                .catch(error => {
                    console.error('Search error:', error);
                    memberSearchResults.innerHTML = `
                        <div class="px-4 py-2 text-sm text-red-500">
                            Error loading results: ${error.message}
                        </div>
                    `;
                });
        }, 300); // 300ms debounce
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!memberSearchInput.contains(e.target) && !memberSearchResults.contains(e.target)) {
            memberSearchResults.classList.add('hidden');
        }
    });

    // Handle the change member button
    document.getElementById('changeMemberBtn')?.addEventListener('click', function() {
        selectedMemberInfo.classList.add('hidden');
        document.getElementById('selectedMemberId').value = '';
        memberSearchInput.value = '';
        memberSearchInput.focus();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Initialize member search functionality for filter section
    initFilterMemberSearch();

    // Function to initialize filter member search
    function initFilterMemberSearch() {
        const memberSearchInput = document.getElementById('memberSearch');
        const memberSearchResults = document.getElementById('filterMemberResults');
        const selectedMemberId = document.getElementById('selectedFilterMemberId');
        let searchTimeout;

        if (memberSearchInput) {
            memberSearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();

                // Clear results if search term is too short
                if (searchTerm.length < 2) {
                    memberSearchResults.classList.add('hidden');
                    return;
                }

                // Add loading indicator
                memberSearchResults.innerHTML = `
                    <div class="px-4 py-2 text-sm text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                    </div>
                `;
                memberSearchResults.classList.remove('hidden');

                // Debounce the search
                searchTimeout = setTimeout(() => {
                    // Make the AJAX request
                    fetch(`../../functions/search-members.php?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(members => {
                            if (members.length === 0) {
                                memberSearchResults.innerHTML = `
                                    <div class="px-4 py-2 text-sm text-gray-500">
                                        No members found
                                    </div>
                                `;
                                return;
                            }

                            // Build search results HTML
                            let resultsHtml = '';
                            members.forEach(member => {
                                resultsHtml += `
                                    <div class="filter-member-result px-4 py-2 hover:bg-gray-100 cursor-pointer" 
                                         data-member-id="${member.id}"
                                         data-member-name="${member.name}">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3">
                                                ${member.initials}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">${member.name}</div>
                                                <div class="text-xs text-gray-500">${member.email}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            memberSearchResults.innerHTML = resultsHtml;

                            // Add click handlers to results
                            document.querySelectorAll('.filter-member-result').forEach(result => {
                                result.addEventListener('click', function() {
                                    const memberId = this.dataset.memberId;
                                    const memberName = this.dataset.memberName;

                                    // Update input with selected member name
                                    memberSearchInput.value = memberName;
                                    
                                    // Store selected member ID in hidden field
                                    selectedMemberId.value = memberId;

                                    // Hide search results
                                    memberSearchResults.classList.add('hidden');
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            memberSearchResults.innerHTML = `
                                <div class="px-4 py-2 text-sm text-red-500">
                                    Error loading results: ${error.message}
                                </div>
                            `;
                        });
                }, 300); // 300ms debounce
            });
        }

        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (memberSearchInput && memberSearchResults &&
                !memberSearchInput.contains(e.target) && 
                !memberSearchResults.contains(e.target)) {
                memberSearchResults.classList.add('hidden');
            }
        });
    }

    // Update the apply filters function to include the selected member ID
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            // Show loading state
            const tableBody = document.getElementById('subscriptionStatusBody');
            const loadingState = document.getElementById('subscriptionLoadingState');
            const emptyState = document.getElementById('subscriptionEmptyState');
            
            // Hide table body and empty state, show loading
            tableBody.closest('table').classList.add('hidden');
            emptyState.classList.add('hidden');
            loadingState.classList.remove('hidden');
            
            // Get filter values
            const filterData = {
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value,
                subscription: document.getElementById('subFilter').value,
                program: document.getElementById('programFilter').value,
                memberId: document.getElementById('selectedFilterMemberId').value,
                memberSearch: document.getElementById('selectedFilterMemberId').value ? '' : document.getElementById('memberSearch').value
            };
            
            // Make API call to get filtered data
            fetch('../../functions/filter-transactions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filterData)
            })
            .then(response => response.json())
            .then(data => {
                // ...existing code...
            })
            .catch(error => {
                // ...existing code...
            });
        });
    }

    // Reset filters function should also clear the selected member ID
    function resetFilters() {
        // Reset all filter fields
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        document.getElementById('startDate').value = formatDate(firstDay);
        document.getElementById('endDate').value = formatDate(today);
        document.getElementById('memberSearch').value = '';
        document.getElementById('selectedFilterMemberId').value = '';
        document.getElementById('subFilter').selectedIndex = 0;
        document.getElementById('programFilter').selectedIndex = 0;
        
        // Show notification
        showToast('Filters reset!', true);
        
        // Reload data with no filters
        document.getElementById('applyFiltersBtn').click();
    }

    // Keep the existing member search inside the modal untouched
    // ...existing code...
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Enhance member search functionality in the modal
    const modalMemberSearch = document.querySelector('#addTransactionModal #memberSearch');
    const modalMemberResults = document.querySelector('#addTransactionModal #memberSearchResults');
    const selectedMemberInfo = document.getElementById('selectedMemberInfo');
    let modalSearchTimeout;

    if (modalMemberSearch && modalMemberResults) {
        modalMemberSearch.addEventListener('input', function() {
            clearTimeout(modalSearchTimeout);
            const searchTerm = this.value.trim();

            // Clear results if search term is empty
            if (searchTerm.length < 2) {
                modalMemberResults.classList.add('hidden');
                return;
            }

            // Add loading indicator
            modalMemberResults.innerHTML = `
                <div class="px-4 py-2 text-sm text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                </div>
            `;
            modalMemberResults.classList.remove('hidden');

            // Debounce the search
            modalSearchTimeout = setTimeout(() => {
                // Make the AJAX request
                fetch(`../../functions/search-members.php?term=${encodeURIComponent(searchTerm)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(members => {
                        if (members.length === 0) {
                            modalMemberResults.innerHTML = `
                                <div class="px-4 py-2 text-sm text-gray-500">
                                    No members found
                                </div>
                            `;
                            return;
                        }

                        // Build search results HTML
                        modalMemberResults.innerHTML = members.map(member => `
                            <div class="modal-member-result px-4 py-2 hover:bg-gray-100 cursor-pointer" 
                                 data-member-id="${member.id}"
                                 data-member-name="${member.name}"
                                 data-member-email="${member.email}"
                                 data-member-initials="${member.initials}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3">
                                        ${member.initials}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">${member.name}</div>
                                        <div class="text-xs text-gray-500">${member.program || 'No Program'}</div>
                                        <div class="text-xs text-gray-500">${member.subscription || 'No Subscription'}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('');

                        // Add click handlers to results
                        document.querySelectorAll('.modal-member-result').forEach(result => {
                            result.addEventListener('click', function() {
                                const memberId = this.dataset.memberId;
                                const memberName = this.dataset.memberName;
                                const memberEmail = this.dataset.memberEmail;
                                const memberInitials = this.dataset.memberInitials;

                                // Update selected member info
                                document.getElementById('selectedMemberId').value = memberId;
                                document.getElementById('memberInitials').textContent = memberInitials;
                                document.getElementById('memberName').textContent = memberName;
                                document.getElementById('memberEmail').textContent = memberEmail;

                                // Show selected member info and hide search results
                                selectedMemberInfo.classList.remove('hidden');
                                modalMemberResults.classList.add('hidden');
                                modalMemberSearch.value = '';
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        modalMemberResults.innerHTML = `
                            <div class="px-4 py-2 text-sm text-red-500">
                                Error loading results: ${error.message}
                            </div>
                        `;
                    });
            }, 300); // 300ms debounce
        });

        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (!modalMemberSearch.contains(e.target) && !modalMemberResults.contains(e.target)) {
                modalMemberResults.classList.add('hidden');
            }
        });
    }

    // Handle the change member button (keep existing functionality)
    document.getElementById('changeMemberBtn')?.addEventListener('click', function() {
        selectedMemberInfo.classList.add('hidden');
        document.getElementById('selectedMemberId').value = '';
        modalMemberSearch.value = '';
        modalMemberSearch.focus();
    });

    // ...existing code...
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Enhanced member search functionality in the modal with 1-character search trigger
    const modalMemberSearch = document.querySelector('#addTransactionModal #memberSearch');
    const modalMemberResults = document.querySelector('#addTransactionModal #memberSearchResults');
    const selectedMemberInfo = document.getElementById('selectedMemberInfo');
    let modalSearchTimeout;

    if (modalMemberSearch && modalMemberResults) {
        modalMemberSearch.addEventListener('input', function() {
            clearTimeout(modalSearchTimeout);
            const searchTerm = this.value.trim();

            // Clear results if search term is empty
            if (searchTerm.length < 1) {
                modalMemberResults.classList.add('hidden');
                return;
            }

            // Add loading indicator
            modalMemberResults.innerHTML = `
                <div class="px-4 py-2 text-sm text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                </div>
            `;
            modalMemberResults.classList.remove('hidden');

            // Debounce the search
            modalSearchTimeout = setTimeout(() => {
                // Make the AJAX request
                fetch(`../../functions/search-members.php?term=${encodeURIComponent(searchTerm)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(members => {
                        if (members.length === 0) {
                            modalMemberResults.innerHTML = `
                                <div class="px-4 py-2 text-sm text-gray-500">
                                    No members found
                                </div>
                            `;
                            return;
                        }

                        // Build search results HTML
                        modalMemberResults.innerHTML = members.map(member => `
                            <div class="modal-member-result px-4 py-2 hover:bg-gray-100 cursor-pointer" 
                                 data-member-id="${member.id}"
                                 data-member-name="${member.name}"
                                 data-member-email="${member.email}"
                                 data-member-initials="${member.initials}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3">
                                        ${member.initials}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">${member.name}</div>
                                        <div class="text-xs text-gray-500">${member.program || 'No Program'}</div>
                                        <div class="text-xs text-gray-500">${member.subscription || 'No Subscription'}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('');

                        // Add click handlers to results
                        document.querySelectorAll('.modal-member-result').forEach(result => {
                            result.addEventListener('click', function() {
                                const memberId = this.dataset.memberId;
                                const memberName = this.dataset.memberName;
                                const memberEmail = this.dataset.memberEmail;
                                const memberInitials = this.dataset.memberInitials;

                                // Update selected member info
                                document.getElementById('selectedMemberId').value = memberId;
                                document.getElementById('memberInitials').textContent = memberInitials;
                                document.getElementById('memberName').textContent = memberName;
                                document.getElementById('memberEmail').textContent = memberEmail;

                                // Show selected member info and hide search results
                                selectedMemberInfo.classList.remove('hidden');
                                modalMemberResults.classList.add('hidden');
                                modalMemberSearch.value = '';
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        modalMemberResults.innerHTML = `
                            <div class="px-4 py-2 text-sm text-red-500">
                                Error loading results: ${error.message}
                            </div>
                        `;
                    });
            }, 300); // 300ms debounce
        });

        // ...existing code for closing results when clicking outside...
    }

    // Also update the filter member search to trigger after 1 character
    function initFilterMemberSearch() {
        const memberSearchInput = document.getElementById('memberSearch');
        const memberSearchResults = document.getElementById('filterMemberResults');
        const selectedMemberId = document.getElementById('selectedFilterMemberId');
        let searchTimeout;

        if (memberSearchInput) {
            memberSearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();

                // Clear results if search term is empty
                if (searchTerm.length < 1) {
                    memberSearchResults.classList.add('hidden');
                    return;
                }

                // Add loading indicator
                memberSearchResults.innerHTML = `
                    <div class="px-4 py-2 text-sm text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
                    </div>
                `;
                memberSearchResults.classList.remove('hidden');

                // Debounce the search
                searchTimeout = setTimeout(() => {
                    // Make the AJAX request
                    fetch(`../../functions/search-members.php?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(members => {
                            if (members.length === 0) {
                                memberSearchResults.innerHTML = `
                                    <div class="px-4 py-2 text-sm text-gray-500">
                                        No members found
                                    </div>
                                `;
                                return;
                            }

                            // Build search results HTML - keep existing code
                            // ...existing code...
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            memberSearchResults.innerHTML = `
                                <div class="px-4 py-2 text-sm text-red-500">
                                    Error loading results: ${error.message}
                                </div>
                            `;
                        });
                }, 300); // 300ms debounce
            });
        }

        // ...existing code for closing results when clicking outside...
    }

    // ...existing code...
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Helper function to determine subscription status based on days left
    function getSubscriptionStatus(isActive, daysLeft) {
        let statusClass, statusText;
        
        if (!isActive) {
            statusClass = 'bg-red-100 text-red-800';
            statusText = 'Inactive';
        } else if (daysLeft < 0) {
            statusClass = 'bg-red-100 text-red-800';
            statusText = 'Expired';
        } else if (daysLeft <= 7) {
            statusClass = 'bg-yellow-100 text-yellow-800';
            statusText = daysLeft === 0 ? 'Expires Today' : 
                         daysLeft === 1 ? 'Expires Tomorrow' : 
                         `Expires in ${daysLeft} days`;
        } else {
            statusClass = 'bg-green-100 text-green-800';
            statusText = 'Active';
        }
        
        return { statusClass, statusText };
    }
    
    // Function to add a new transaction to the subscription status table
    function addTransactionToTable(transaction) {
        const tableBody = document.getElementById('subscriptionStatusBody');
        const emptyState = document.getElementById('subscriptionEmptyState');
        
        // Hide empty state if it's visible
        if (!emptyState.classList.contains('hidden')) {
            emptyState.classList.add('hidden');
            tableBody.closest('table').classList.remove('hidden');
        }
        
        // Calculate status class based on days left
        const { statusClass, statusText } = getSubscriptionStatus(transaction.isActive, transaction.daysLeft);
        
        // Create a new row
        const row = document.createElement('tr');
        
        // Set row HTML
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-white text-xs mr-3">
                        ${transaction.memberInitials}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">${transaction.memberName}</div>
                        <div class="text-xs text-gray-500">ID: ${transaction.memberId}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${transaction.subscriptionName}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${transaction.startDate}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${transaction.endDate}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${transaction.paidDate}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex justify-center space-x-2">
                    <button data-sub-id="${transaction.subscriptionId}" data-member-id="${transaction.memberId}" data-action="view" class="text-blue-600 hover:text-blue-800 transition-colors" title="View details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button data-sub-id="${transaction.subscriptionId}" data-member-id="${transaction.memberId}" data-action="deactivate" class="text-red-600 hover:text-red-800 transition-colors" title="Deactivate subscription">
                        <i class="fas fa-ban"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Add the row to the beginning of the table
        if (tableBody.firstChild) {
            tableBody.insertBefore(row, tableBody.firstChild);
        } else {
            tableBody.appendChild(row);
        }
        
        // Initialize action buttons for the new row
        initActionButtonsForRow(row);
    }

// ...existing code...
});
</script>
<script>
// ...existing code...

function initActionButtonsForRow(row) {
    // Find deactivate button in this row
    const deactivateButton = row.querySelector('[data-action="deactivate"]');
    if (deactivateButton) {
        deactivateButton.addEventListener('click', function() {
            const subId = this.getAttribute('data-sub-id');
            const memberId = this.getAttribute('data-member-id');
            const memberName = this.getAttribute('data-member-name') || 
                               row.querySelector('td:nth-child(1) .text-sm.font-medium')?.textContent || 
                               'this member';
            const subscriptionName = this.getAttribute('data-subscription-name') || 
                                    row.querySelector('td:nth-child(2) .text-sm')?.textContent || 
                                    'the subscription';
            
            // Validate that we have both IDs
            if (!subId || !memberId) {
                showToast('Missing subscription or member information', false);
                return;
            }
            
            // Show confirmation dialog for deactivation
            showConfirmationDialog(
                'Deactivate Subscription',
                `Are you sure you want to deactivate ${subscriptionName} for ${memberName}?`,
                function() {
                    // Send deactivation request to server
                    deactivateSubscription(memberId, subId, row);
                }
            );
        });
    }
    
    // Find view button in this row
    const viewButton = row.querySelector('[data-action="view"]');
    if (viewButton) {
        // ...existing code...
    }
    
    // Find renew button in this row if it exists
    const renewButton = row.querySelector('[data-action="renew"]');
    if (renewButton) {
        // ...existing code...
    }
}

// Function to handle the deactivation request
function deactivateSubscription(memberId, subId, row) {
    // Show "processing" state on the button
    const deactivateButton = row.querySelector('[data-action="deactivate"]');
    const originalButtonHTML = deactivateButton.innerHTML;
    deactivateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    deactivateButton.disabled = true;
    
    console.log(`Attempting to deactivate subscription - Member ID: ${memberId}, Subscription ID: ${subId}`);
    
    // Send AJAX request to deactivate subscription
    fetch('../../functions/deactivate-subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            memberId: memberId,
            subId: subId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                // If not valid JSON, log the raw response and throw error
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid JSON');
            }
        });
    })
    .then(data => {
        console.log('Deactivation response:', data);
        if (data.success) {
            // Update the UI
            updateRowAfterDeactivation(row);
            
            // Store deactivation state in localStorage for persistence across page reloads
            saveDeactivationState(memberId, subId);
            
            // Show success message
            showToast(data.message || 'Subscription deactivated successfully', true);
        } else {
            // Show error message
            console.error('Deactivation failed:', data.message);
            showToast(data.message || 'Failed to deactivate subscription', false);
            
            // Restore the button
            deactivateButton.innerHTML = originalButtonHTML;
            deactivateButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error during deactivation request:', error);
        showToast('An error occurred while deactivating the subscription', false);
        
        // Restore the button
        deactivateButton.innerHTML = originalButtonHTML;
        deactivateButton.disabled = false;
    });
}

// Function to update UI after deactivation
function updateRowAfterDeactivation(row) {
    // Update status badge
    const statusCell = row.querySelector('td:nth-child(6) span');
    if (statusCell) {
        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        statusCell.textContent = 'Inactive';
    }
    
    // Update days left display if it exists
    const daysLeftEl = row.querySelector('.days-left');
    if (daysLeftEl) {
        daysLeftEl.textContent = 'Subscription inactive';
    }
    
    // Remove deactivate button and add renew button
    const actionsCell = row.querySelector('td:nth-child(7)');
    if (actionsCell) {
        const deactivateButton = actionsCell.querySelector('[data-action="deactivate"]');
        if (deactivateButton) {
            const memberId = deactivateButton.getAttribute('data-member-id');
            const subId = deactivateButton.getAttribute('data-sub-id');
            
            // Create renew button
            const renewButton = document.createElement('button');
            renewButton.setAttribute('data-sub-id', subId);
            renewButton.setAttribute('data-member-id', memberId);
            renewButton.setAttribute('data-action', 'renew');
            renewButton.className = 'text-green-600 hover:text-green-800 transition-colors';
            renewButton.title = 'Renew subscription';
            renewButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
            
            // Add click handler for renewal
            renewButton.addEventListener('click', function() {
                const memberName = row.querySelector('td:nth-child(1) .text-sm.font-medium').textContent;
                const subscriptionName = row.querySelector('td:nth-child(2) .text-sm').textContent;
                openRenewModal(memberId, subId, memberName, subscriptionName);
            });
            
            // Replace deactivate button with renew button
            deactivateButton.replaceWith(renewButton);
        }
    }
    
    // Add subtle background to indicate inactive status
    row.classList.add('bg-gray-50');
}

// Initialize all action buttons when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize action buttons for all rows
    document.querySelectorAll('#subscriptionStatusBody tr').forEach(row => {
        initActionButtonsForRow(row);
    });

    // Set up an observer to initialize buttons for any dynamically added rows
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeName === 'TR') {
                        initActionButtonsForRow(node);
                    } else if (node.querySelectorAll) {
                        node.querySelectorAll('tr').forEach(row => {
                            initActionButtonsForRow(row);
                        });
                    }
                });
            }
        });
    });
    
    const tableBody = document.getElementById('subscriptionStatusBody');
    if (tableBody) {
        observer.observe(tableBody, { childList: true, subtree: true });
    }
});
</script>
<script>
// ...existing code...

// Modified function to handle renewal with database IDs
function openRenewModal(memberId, subId, memberName, subscriptionName) {
    console.log(`Opening renewal modal for member ID: ${memberId}, subscription ID: ${subId}`);
    
    if (!memberId) {
        showToast('Error: Member ID is missing', false);
        return;
    }
    
    const modal = document.getElementById('addTransactionModal');
    if (modal) {
        // Show the modal first
        openModal(modal);
        
        // Get member search section and completely remove it for renewal modal
        const memberSearchContainer = document.getElementById('memberSearch').parentElement.parentElement;
        memberSearchContainer.classList.add('hidden');
        
        // Show member info without the search UI or change option
        const selectedMemberInfo = document.getElementById('selectedMemberInfo');
        selectedMemberInfo.classList.remove('hidden');
        
        // Replace the heading to indicate member is fixed for renewal
        const memberInfoSection = document.querySelector('.mb-1');
        if (memberInfoSection) {
            const memberHeading = memberInfoSection.querySelector('span');
            if (memberHeading) {
                memberHeading.textContent = "Member (Fixed for Renewal)";
            }
        }
        
        // Set member information in the static display
        const memberInitials = document.getElementById('memberInitials');
        const memberNameElement = document.getElementById('memberName');
        const memberEmail = document.getElementById('memberEmail');
        const selectedMemberId = document.getElementById('selectedMemberId');
        const changeMemberBtn = document.getElementById('changeMemberBtn');
        
        // Set member details
        const initials = memberName.split(' ').map(n => n[0]).join('');
        memberInitials.textContent = initials;
        memberNameElement.textContent = memberName;
        
        // IMPORTANT: Set the actual member ID from the parameter, not hardcoded
        selectedMemberId.value = memberId;
        
        // Fetch actual member email from database
        fetch(`../../functions/get-member-details.php?id=${memberId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    memberEmail.textContent = data.member.email;
                } else {
                    memberEmail.textContent = memberName.toLowerCase().replace(' ', '.') + '@example.com';
                    console.warn(`Could not fetch email for member ID ${memberId}: ${data.message}`);
                }
            })
            .catch((error) => {
                console.error('Error fetching member details:', error);
                memberEmail.textContent = memberName.toLowerCase().replace(' ', '.') + '@example.com';
            });
        
        // Add data attribute to form to indicate this is a renewal
        document.getElementById('addTransactionForm').setAttribute('data-is-renewal', 'true');
        document.getElementById('addTransactionForm').setAttribute('data-previous-sub-id', subId);
        
        // Completely hide the change button for renewals
        if (changeMemberBtn) {
            changeMemberBtn.classList.add('hidden');
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
        
        // Set start date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('startDateInput').valueAsDate = tomorrow;
        
        // Calculate and set end date based on subscription
        updateEndDate();
        
        // Change modal title to indicate renewal
        const modalTitle = modal.querySelector('.text-lg.font-medium.text-white');
        if (modalTitle) {
            modalTitle.textContent = "Renew Subscription";
        }
        const modalSubtitle = modal.querySelector('.text-xs.text-white/90');
        if (modalSubtitle) {
            modalSubtitle.textContent = "Renew subscription for " + memberName;
        }
        
        // Change submit button text
        const submitButton = document.getElementById('submitTransactionBtn');
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Renew Subscription';
        }
    }
}

function initActionButtonsForRow(row) {
    // Find deactivate button in this row
    const deactivateButton = row.querySelector('[data-action="deactivate"]');
    if (deactivateButton) {
        deactivateButton.addEventListener('click', function() {
            const subId = this.getAttribute('data-sub-id');
            const memberId = this.getAttribute('data-member-id');
            const memberName = this.getAttribute('data-member-name') || 
                               row.querySelector('td:nth-child(1) .text-sm.font-medium')?.textContent || 
                               'this member';
            const subscriptionName = this.getAttribute('data-subscription-name') || 
                                    row.querySelector('td:nth-child(2) .text-sm')?.textContent || 
                                    'the subscription';
            
            // Validate that we have both IDs
            if (!subId || !memberId) {
                showToast('Missing subscription or member information', false);
                return;
            }
            
            // Show confirmation dialog for deactivation
            showConfirmationDialog(
                'Deactivate Subscription',
                `Are you sure you want to deactivate ${subscriptionName} for ${memberName}?`,
                function() {
                    // Send deactivation request to server
                    deactivateSubscription(memberId, subId, row);
                }
            );
        });
    }
    
    // Find view button in this row
    const viewButton = row.querySelector('[data-action="view"]');
    if (viewButton) {
        // ...existing code...
    }
    
    // Find renew button in this row if it exists
    const renewButton = row.querySelector('[data-action="renew"]');
    if (renewButton) {
        // ...existing code...
    }
}

// Function to handle the deactivation request
function deactivateSubscription(memberId, subId, row) {
    // Show "processing" state on the button
    const deactivateButton = row.querySelector('[data-action="deactivate"]');
    const originalButtonHTML = deactivateButton.innerHTML;
    deactivateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    deactivateButton.disabled = true;
    
    console.log(`Attempting to deactivate subscription - Member ID: ${memberId}, Subscription ID: ${subId}`);
    
    // Send AJAX request to deactivate subscription
    fetch('../../functions/deactivate-subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            memberId: memberId,
            subId: subId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                // If not valid JSON, log the raw response and throw error
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid JSON');
            }
        });
    })
    .then(data => {
        console.log('Deactivation response:', data);
        if (data.success) {
            // Update the UI
            updateRowAfterDeactivation(row);
            
            // Store deactivation state in localStorage for persistence across page reloads
            saveDeactivationState(memberId, subId);
            
            // Show success message
            showToast(data.message || 'Subscription deactivated successfully', true);
        } else {
            // Show error message
            console.error('Deactivation failed:', data.message);
            showToast(data.message || 'Failed to deactivate subscription', false);
            
            // Restore the button
            deactivateButton.innerHTML = originalButtonHTML;
            deactivateButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deactivating the subscription', false);
        
        // Restore the button
        deactivateButton.innerHTML = originalButtonHTML;
        deactivateButton.disabled = false;
    });
}

// Function to update the row UI after deactivation
function updateRowAfterDeactivation(row) {
    // Update the status badge to "Inactive"
    const statusCell = row.querySelector('td:nth-child(6) span');
    if (statusCell) {
        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        statusCell.textContent = 'Inactive';
    }
    
    // Update days left text if exists
    const daysLeftEl = row.querySelector('.days-left');
    if (daysLeftEl) {
        daysLeftEl.textContent = 'Subscription inactive';
    }
    
    // IMPORTANT: Completely remove the deactivate button directly from DOM
    const actionsContainer = row.querySelector('td:nth-child(7) .flex');
    if (actionsContainer) {
        const deactivateButton = actionsContainer.querySelector('[data-action="deactivate"]');
        if (deactivateButton) {
            deactivateButton.parentNode.removeChild(deactivateButton);
        }
    }
    
    // Add a subtle background to indicate inactive status
    row.classList.add('bg-gray-50');
}

// ...existing code...
</script>
<script>
// ...existing code...

// Function to update the row UI after deactivation
function updateRowAfterDeactivation(row) {
    // Update the status badge to "Inactive"
    const statusCell = row.querySelector('td:nth-child(6) span');
    if (statusCell) {
        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        statusCell.textContent = 'Inactive';
    }
    
    // Update days left text if exists
    const daysLeftEl = row.querySelector('.days-left');
    if (daysLeftEl) {
        daysLeftEl.textContent = 'Subscription inactive';
    }
    
    // IMPORTANT: Completely remove the deactivate button directly from DOM
    const actionsContainer = row.querySelector('td:nth-child(7) .flex');
    if (actionsContainer) {
        const deactivateButton = actionsContainer.querySelector('[data-action="deactivate"]');
        if (deactivateButton) {
            deactivateButton.parentNode.removeChild(deactivateButton);
        }
    }
    
    // Add a subtle background to indicate inactive status
    row.classList.add('bg-gray-50');
}

// ...existing code...
</script>
<script>
// Function to handle subscription deactivation
function deactivateSubscription(memberId, subId, row) {
    const deactivateButton = row.querySelector('[data-action="deactivate"]');
    const originalButtonHTML = deactivateButton.innerHTML;
    deactivateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    deactivateButton.disabled = true;
    
    console.log(`Attempting to deactivate subscription - Member ID: ${memberId}, Subscription ID: ${subId}`);
    
    // Send AJAX request to deactivate subscription
    fetch('../../functions/deactivate-subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            memberId: memberId,
            subId: subId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                // If not valid JSON, log the raw response and throw error
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid JSON');
            }
        });
    })
    .then(data => {
        console.log('Deactivation response:', data);
        if (data.success) {
            // Update the UI
            updateRowAfterDeactivation(row);
            
            // Store deactivation state in localStorage for persistence across page reloads
            saveDeactivationState(memberId, subId);
            
            // Show success message
            showToast(data.message || 'Subscription deactivated successfully', true);
        } else {
            // Show error message
            console.error('Deactivation failed:', data.message);
            showToast(data.message || 'Failed to deactivate subscription', false);
            
            // Restore the button
            deactivateButton.innerHTML = originalButtonHTML;
            deactivateButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error during deactivation request:', error);
        showToast('An error occurred while deactivating the subscription', false);
        
        // Restore the button
        deactivateButton.innerHTML = originalButtonHTML;
        deactivateButton.disabled = false;
    });
}

// Function to update UI after deactivation
function updateRowAfterDeactivation(row) {
    // Update status badge
    const statusCell = row.querySelector('td:nth-child(6) span');
    if (statusCell) {
        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        statusCell.textContent = 'Inactive';
    }
    
    // Update days left display if it exists
    const daysLeftEl = row.querySelector('.days-left');
    if (daysLeftEl) {
        daysLeftEl.textContent = 'Subscription inactive';
    }
    
    // Remove deactivate button and add renew button
    const actionsCell = row.querySelector('td:nth-child(7) .flex');
    if (actionsCell) {
        const deactivateButton = actionsCell.querySelector('[data-action="deactivate"]');
        if (deactivateButton) {
            const memberId = deactivateButton.getAttribute('data-member-id');
            const subId = deactivateButton.getAttribute('data-sub-id');
            
            // Create renew button
            const renewButton = document.createElement('button');
            renewButton.setAttribute('data-sub-id', subId);
            renewButton.setAttribute('data-member-id', memberId);
            renewButton.setAttribute('data-action', 'renew');
            renewButton.className = 'text-green-600 hover:text-green-800 transition-colors';
            renewButton.title = 'Renew subscription';
            renewButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
            
            // Add click handler for renewal
            renewButton.addEventListener('click', function() {
                const memberName = row.querySelector('td:nth-child(1) .text-sm.font-medium').textContent;
                const subscriptionName = row.querySelector('td:nth-child(2) .text-sm').textContent;
                openRenewModal(memberId, subId, memberName, subscriptionName);
            });
            
            // Replace deactivate button with renew button
            deactivateButton.replaceWith(renewButton);
        }
    }
    
    // Add subtle background to indicate inactive status
    row.classList.add('bg-gray-50');
}
</script>
<script>
// Final fix to ensure the deactivation functionality works
document.addEventListener('DOMContentLoaded', function() {
    // Direct implementation of deactivation functionality
    document.querySelectorAll('[data-action="deactivate"]').forEach(button => {
        button.addEventListener('click', function() {
            const memberId = this.getAttribute('data-member-id');
            const subId = this.getAttribute('data-sub-id');
            const row = this.closest('tr');
            
            if (!memberId) {
                console.error("Missing member ID");
                showToast('Error: Missing member information', false);
                return;
            }
            
            console.log(`Button clicked - Member ID: ${memberId}, Sub ID: ${subId}`);
            
            // Use our custom confirmation dialog
            showCustomConfirmation(
                'Deactivate Subscription',
                'Are you sure you want to deactivate this subscription?',
                () => {
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.disabled = true;
                    
                    // Simple fetch request
                    fetch('../../functions/deactivate-subscription.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            memberId: memberId,
                            subId: subId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status cell
                            const statusCell = row.querySelector('td:nth-child(6) span');
                            if (statusCell) {
                                statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                                statusCell.textContent = 'Inactive';
                            }
                            
                            // Replace deactivate button with renew button
                            const renewButton = document.createElement('button');
                            renewButton.className = 'text-green-600 hover:text-green-800 transition-colors';
                            renewButton.title = 'Renew subscription';
                            renewButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
                            renewButton.dataset.memberId = memberId;
                            renewButton.dataset.subId = subId;
                            renewButton.dataset.action = 'renew';
                            this.replaceWith(renewButton);
                            
                            // Add background to indicate inactive state
                            row.classList.add('bg-gray-50');
                            
                            // Show success message using toast instead of alert
                            showToast('Subscription deactivated successfully!', true);
                        } else {
                            // Restore button on error
                            this.innerHTML = '<i class="fas fa-toggle-off"></i>';
                            this.disabled = false;
                            showToast('Failed to deactivate subscription: ' + (data.message || 'Unknown error'), false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.innerHTML = '<i class="fas fa-toggle-off"></i>';
                        this.disabled = false;
                        showToast('An error occurred while deactivating the subscription', false);
                    });
                }
            );
        });
    });
});
</script>
</body>
</html>