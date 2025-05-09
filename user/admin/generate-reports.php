<?php
require_once '../../config/db_connection.php';

// Report generation function
function generateReport($reportType = 'subscription', $startDate = null, $endDate = null, $programId = 'all', $subId = 'all', $status = 'all') {
    $conn = getConnection();
    $reportData = [];
    
    // Set default dates if not provided
    if (!$startDate) $startDate = date('Y-m-d', strtotime('-30 days'));
    if (!$endDate) $endDate = date('Y-m-d');
    
    try {
        // Common WHERE clause parts
        $programFilter = ($programId !== 'all') ? "AND m.PROGRAM_ID = $programId" : "";
        $subFilter = ($subId !== 'all') ? "AND ms.SUB_ID = $subId" : "";
        $statusFilter = ($status !== 'all') ? "AND ms.IS_ACTIVE = $status" : "";
        
        // Adjust query based on report type
        if ($reportType === 'subscription') {
            $sql = "SELECT 
                        m.MEMBER_ID, 
                        CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) AS Name, 
                        s.SUB_NAME AS Subscription, 
                        ms.START_DATE AS 'Start Date', 
                        ms.END_DATE AS 'End Date',
                        ms.IS_ACTIVE AS Status,
                        s.PRICE AS Revenue,
                        p.PROGRAM_NAME AS Program
                    FROM member m
                    JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
                    JOIN subscription s ON ms.SUB_ID = s.SUB_ID
                    JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                    WHERE (ms.START_DATE BETWEEN ? AND ? OR ms.END_DATE BETWEEN ? AND ?)
                    $programFilter $subFilter $statusFilter
                    ORDER BY ms.START_DATE DESC";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $startDate, $endDate, $startDate, $endDate);
        } 
        else if ($reportType === 'revenue') {
            $sql = "SELECT 
                        CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) AS Name,
                        s.SUB_NAME AS Subscription,
                        t.TRANSAC_DATE AS 'Transaction Date', 
                        ms.START_DATE AS 'Start Date', 
                        ms.END_DATE AS 'End Date',
                        p.PAY_METHOD AS 'Payment Method',
                        s.PRICE AS Revenue,
                        prog.PROGRAM_NAME AS Program
                    FROM transaction t
                    JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                    JOIN member_subscription ms ON t.MEMBER_ID = ms.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
                    JOIN subscription s ON t.SUB_ID = s.SUB_ID
                    JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                    JOIN program prog ON m.PROGRAM_ID = prog.PROGRAM_ID
                    WHERE t.TRANSAC_DATE BETWEEN ? AND ?
                    $programFilter $subFilter $statusFilter
                    ORDER BY t.TRANSAC_DATE DESC";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
        }
        else {
            throw new Exception("Invalid report type");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch the data into an array
        while ($row = $result->fetch_assoc()) {
            $reportData[] = $row;
        }
        
        // Calculate totals
        $totals = [
            'count' => count($reportData),
            'revenue' => array_sum(array_column($reportData, 'Revenue'))
        ];
        
        return [
            'data' => $reportData,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
    } catch (Exception $e) {
        error_log("Report generation error: " . $e->getMessage());
        return [
            'error' => $e->getMessage(),
            'data' => [],
            'totals' => ['count' => 0, 'revenue' => 0]
        ];
    } finally {
        if ($conn instanceof mysqli) {
            $conn->close();
        }
    }
}

// Check if this is an AJAX request for report data
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    
    // Get filter parameters from request
    $reportType = $_GET['type'] ?? 'subscription';
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;
    $programId = $_GET['program'] ?? 'all';
    $subId = $_GET['subscription'] ?? 'all';
    $status = $_GET['status'] ?? 'all';
    
    // Generate report data
    $reportData = generateReport($reportType, $startDate, $endDate, $programId, $subId, $status);
    
    // Return JSON response
    echo json_encode($reportData);
    exit;
}

// For normal page load, fetch data for dropdowns
try {
    $conn = getConnection();
    
    // Fetch programs
    $programQuery = "SELECT PROGRAM_ID, PROGRAM_NAME FROM program WHERE IS_ACTIVE = 1";
    $programs = $conn->query($programQuery);
    
    // Fetch subscriptions
    $subQuery = "SELECT SUB_ID, SUB_NAME FROM subscription WHERE IS_ACTIVE = 1";
    $subscriptions = $conn->query($subQuery);
    
    $conn->close();
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
}
?>
<?php 

?>

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
                                <option value="subscription">Subscription Report</option>
                                <option value="revenue">Revenue Report</option>
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
                                <option value="last30days" selected>Last 30 Days</option>
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
                            <input type="date" id="startDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
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
                            <input type="date" id="endDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" value="<?php echo date('Y-m-d'); ?>">
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
                                <?php
                                if (isset($programs)) {
                                    while ($program = $programs->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($program['PROGRAM_ID']) . '">' . 
                                             htmlspecialchars($program['PROGRAM_NAME']) . '</option>';
                                    }
                                }
                                ?>
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
                                <?php
                                if (isset($subscriptions)) {
                                    while ($sub = $subscriptions->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($sub['SUB_ID']) . '">' . 
                                             htmlspecialchars($sub['SUB_NAME']) . '</option>';
                                    }
                                }
                                ?>
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
                    <button class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors flex items-center gap-2">
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
                        <h2 class="text-xl font-semibold text-primary-dark" id="reportTitle">Subscription Report</h2>
                        <p class="text-gray-500 text-sm" id="reportDateRange"></p>
                    </div>
                    <div class="flex gap-2 mt-3 md:mt-0">
                        <button id="printReportBtn" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-print"></i> <span class="hidden md:inline">Print</span>
                        </button>
                        <button id="exportExcelBtn" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-excel"></i> <span class="hidden md:inline">Excel</span>
                        <button id="exportPdfBtn" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> <span class="hidden md:inline">PDF</span>
                        </button>
                    </div>
                </div>
                <!-- Report Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2" id="card1Title">Total Subscriptions</h3>
                        <p class="text-3xl font-bold text-gray-800" id="card1Value">0</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2" id="card2Title">Active Subscriptions</h3>
                        <p class="text-3xl font-bold text-gray-800" id="card2Value">0</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2" id="card3Title">Total Revenue</h3>
                        <p class="text-3xl font-bold text-gray-800" id="card3Value">₱0.00</p>
            <!-- Report Results Section -->
            <div id="reportResults">
                <!-- Report Header with Export Options -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-primary-dark">Membership Report</h2>
                        <p class="text-gray-500 text-sm">May 1, 2023 - May 31, 2023</p>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Renewed Subscriptions</h3>
                        <p class="text-3xl font-bold text-gray-800">42</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm mr-1">+8%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Churned Members</h3>
                        <p class="text-3xl font-bold text-gray-800">12</p>
                        <div class="flex items-center mt-2">
                            <span class="text-red-600 text-sm mr-1">+2%</span>
                            <span class="text-gray-500 text-sm">vs previous period</span>
                        </div>
                    </div>
                </div>
                
                <!-- Visualization Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Chart 1: Revenue Trends -->
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-4">Revenue Trend</h3>
                        <div class="h-72">
                            <canvas id="membershipTrendChart"></canvas>
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
                        <h3 class="text-sm font-medium text-gray-500 uppercase" id="tableTitle">Subscription Details</h3>
                        <div class="relative">
                            <input type="text" id="tableSearch" placeholder="Search..." class="px-4 py-2 rounded-md border border-gray-300 focus:border-primary-light focus:ring-primary-light">
                        <h3 class="text-sm font-medium text-gray-500 uppercase">Detailed Member Data</h3>
                        <div class="relative">
                            <input type="text" placeholder="Search members..." class="px-4 py-2 rounded-md border border-gray-300 focus:border-primary-light focus:ring-primary-light">
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="reportTable">
                            <thead id="reportTableHead">
                                <tr>
                                    <!-- Table headers will be dynamically generated based on report type -->
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="reportTableBody">
                                <!-- Table data will be populated dynamically -->
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
                    
                    <!-- Pagination Controls -->
                    <div class="flex justify-between items-center mt-4">
                        <div class="text-sm text-gray-500" id="tablePagingInfo">
                            Showing <span id="pageStart">0</span> to <span id="pageEnd">0</span> of <span id="totalEntries">0</span> entries
                        </div>
                        <div class="flex gap-2">
                            <button id="prevPageBtn" class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="pageNumbers" class="flex gap-1">
                                <!-- Page numbers will be added dynamically -->
                            </div>
                            <button id="nextPageBtn" class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </button>
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

    <!-- Error Notification Toast -->
    <div id="errorNotification" class="fixed top-6 right-6 bg-red-50 border-l-4 border-red-500 shadow-md rounded-md p-4 w-80 transform -translate-y-16 opacity-0 transition-all duration-500 z-50 hidden">
        <div class="flex items-center">
            <div class="flex-shrink-0 pt-0.5">
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error!</h3>
                <div class="mt-1 text-sm text-red-700" id="errorMessage">
                    Failed to generate report.
                </div>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button id="closeErrorNotification" type="button" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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

            // Current active report type and data
            let currentReportType = 'subscription';
            let currentReportData = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            // Table columns configuration for different report types
            const tableColumns = {
                subscription: [
                    { field: 'Name', label: 'Member Name' },
                    { field: 'Subscription', label: 'Subscription' },
                    { field: 'Start Date', label: 'Start Date' },
                    { field: 'End Date', label: 'End Date' },
                    { field: 'Revenue', label: 'Revenue (₱)', format: 'currency' },
                    { field: 'Status', label: 'Status', format: 'status' },
                    { field: 'Program', label: 'Program' },
                ],
                revenue: [
                    { field: 'Name', label: 'Member Name' },
                    { field: 'Subscription', label: 'Subscription' },
                    { field: 'Transaction Date', label: 'Transaction Date' },
                    { field: 'Start Date', label: 'Start Date' },
                    { field: 'End Date', label: 'End Date' },
                    { field: 'Payment Method', label: 'Payment Method' },
                    { field: 'Revenue', label: 'Amount (₱)', format: 'currency' },
                ],
            };

            // Function to format date for display
            function formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            // Function to format currency values
            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(parseFloat(amount) || 0);
            }

            // Function to generate status badge
            function getStatusBadge(status) {
                if (status == 1) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>';
                } else {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>';
                }
            }

            // Function to format date for API request
            function formatDateForApi(date) {
                return date.toISOString().split('T')[0];
            }

            // Function to handle generating reports
            function generateReport() {
                // Show loading indicator, hide results and no-results sections
                document.getElementById('reportResults').classList.add('hidden', 'opacity-0');
                document.getElementById('noReportResults').classList.add('hidden');
                document.getElementById('loadingReport').classList.remove('hidden');

                // Get filter values
                const reportType = document.getElementById('reportType').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const program = document.getElementById('programFilter').value;
                const subscription = document.getElementById('subFilter').value;
                const status = document.getElementById('statusFilter').value;

                currentReportType = reportType;
                
                // Build the URL for the AJAX request
                const url = `?ajax=true&type=${reportType}&startDate=${startDate}&endDate=${endDate}&program=${program}&subscription=${subscription}&status=${status}`;
                
                // Make the AJAX request
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        // Store the report data
                        currentReportData = data.data;
                        currentPage = 1;
                        
                        // Hide loading indicator
                        document.getElementById('loadingReport').classList.add('hidden');
                        
                        // Check if we have any data
                        if (currentReportData.length === 0) {
                            // Show the no results message with custom text for date range
                            const noResultsElement = document.getElementById('noReportResults');
                            const noResultsTitle = noResultsElement.querySelector('h3');
                            const noResultsText = noResultsElement.querySelector('p');
                            
                            noResultsTitle.textContent = 'No Data Found';
                            noResultsText.textContent = `No ${reportType} data found for the selected date range (${formatDate(startDate)} - ${formatDate(endDate)}). Try adjusting your filters or selecting a different date range.`;
                            
                            noResultsElement.classList.remove('hidden');
                            document.getElementById('reportResults').classList.add('hidden');
                        } else {
                            // Update the UI with the report data
                            updateReportUI(data);
                            
                            // Show the report section
                            document.getElementById('noReportResults').classList.add('hidden');
                            document.getElementById('reportResults').classList.remove('hidden');
                            setTimeout(() => {
                                document.getElementById('reportResults').classList.add('opacity-100');
                            }, 10);
                            
                            // Show success notification
                            showSuccessNotification('Report generated successfully');
                        }
                    })
                    .catch(error => {
                        console.error('Error generating report:', error);
                        document.getElementById('loadingReport').classList.add('hidden');
                        
                        // Show error in the no results element
                        const noResultsElement = document.getElementById('noReportResults');
                        const noResultsTitle = noResultsElement.querySelector('h3');
                        const noResultsText = noResultsElement.querySelector('p');
                        
                        noResultsTitle.textContent = 'Error Generating Report';
                        noResultsText.textContent = `An error occurred: ${error.message}. Please try again or contact support if the problem persists.`;
                        
                        noResultsElement.classList.remove('hidden');
                        document.getElementById('reportResults').classList.add('hidden');
                        
                        // Show error notification
                        showErrorNotification('Error generating report: ' + error.message);
                    });
            }

            // Function to update the UI with report data
            function updateReportUI(data) {
                // Update date range text
                document.getElementById('reportDateRange').textContent = 
                    `${formatDate(data.startDate)} - ${formatDate(data.endDate)}`;

                // Update report title
                document.getElementById('reportTitle').textContent = 
                    currentReportType === 'subscription' ? 'Subscription Report' : 'Revenue Report';
                
                // Update summary cards
                updateSummaryCards(data);

                // Update table headers
                updateTableHeaders();

                // Update table data
                updateTableData();

                // Update pagination
                updatePagination();
            }

            // Function to update summary cards
            function updateSummaryCards(data) {
                const activeSubscriptions = data.data.filter(item => item.Status == 1).length;
                const totalRevenue = data.totals.revenue;

                // Card 1: Total records
                document.getElementById('card1Title').textContent = 
                    currentReportType === 'subscription' ? 'TOTAL SUBSCRIPTIONS' : 'TOTAL TRANSACTIONS';
                document.getElementById('card1Value').textContent = data.totals.count;

                // Card 2: Active subscriptions or count by payment method
                document.getElementById('card2Title').textContent = 
                    currentReportType === 'subscription' ? 'ACTIVE SUBSCRIPTIONS' : 'TRANSACTIONS COUNT';
                document.getElementById('card2Value').textContent = 
                    currentReportType === 'subscription' ? activeSubscriptions : data.totals.count;

                // Card 3: Total revenue
                document.getElementById('card3Title').textContent = 'TOTAL REVENUE';
                document.getElementById('card3Value').textContent = `₱${formatCurrency(totalRevenue)}`;
            }

            // Function to update table headers
            function updateTableHeaders() {
                const columns = tableColumns[currentReportType];
                const headerRow = document.getElementById('reportTableHead').querySelector('tr');

                // Clear existing headers
                headerRow.innerHTML = '';
                
                // Add new headers
                columns.forEach(column => {
                    const th = document.createElement('th');
                    th.scope = 'col';
                    th.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
                    th.textContent = column.label;
                    headerRow.appendChild(th);
                });
            }

            // Function to update table data with pagination
            function updateTableData() {
                const columns = tableColumns[currentReportType];
                const tableBody = document.getElementById('reportTableBody');
                const tableTitle = document.getElementById('tableTitle');

                // Update table title
                tableTitle.textContent = currentReportType === 'subscription' 
                    ? 'Subscription Details' 
                    : 'Transaction Details'; 
                
                // Clear existing rows
                tableBody.innerHTML = '';
                
                // Calculate pagination
                const start = (currentPage - 1) * itemsPerPage;
                const end = Math.min(start + itemsPerPage, currentReportData.length);
                const paginatedData = currentReportData.slice(start, end);

                // Update pagination info text
                document.getElementById('pageStart').textContent = currentReportData.length > 0 ? start + 1 : 0;
                document.getElementById('pageEnd').textContent = end;
                document.getElementById('totalEntries').textContent = currentReportData.length;
                
                // Add new rows
                if (paginatedData.length === 0) {
                    const emptyRow = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = columns.length;
                    cell.className = 'px-6 py-4 whitespace-nowrap text-center text-gray-500';
                    cell.textContent = `No data available for the selected date range (${formatDate(document.getElementById('startDate').value)} - ${formatDate(document.getElementById('endDate').value)})`;
                    emptyRow.appendChild(cell);
                    tableBody.appendChild(emptyRow);
                } else {
                    paginatedData.forEach(item => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        
                        columns.forEach(column => {
                            const cell = document.createElement('td');
                            cell.className = 'px-6 py-4 whitespace-nowrap';
                            
                            // Format cell content based on column type
                            if (column.format === 'currency') {
                                cell.innerHTML = `₱${formatCurrency(item[column.field])}`;
                            } else if (column.format === 'status') {
                                cell.innerHTML = getStatusBadge(item[column.field]);
                            } else if (column.field.toLowerCase().includes('date')) {
                                cell.textContent = formatDate(item[column.field]);
                            } else {
                                cell.textContent = item[column.field] || '';
                            }
                            
                            row.appendChild(cell);
                        });
                        
                        tableBody.appendChild(row);
                    });
                }
            }

            // Function to update pagination controls
            function updatePagination() {
                const totalPages = Math.ceil(currentReportData.length / itemsPerPage);
                const pageNumbers = document.getElementById('pageNumbers');

                // Clear existing page numbers
                pageNumbers.innerHTML = '';
                
                // Add page number buttons
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = `w-8 h-8 flex items-center justify-center rounded ${
                        i === currentPage 
                            ? 'bg-primary-dark text-white' 
                            : 'border border-gray-300 text-gray-700 hover:bg-gray-100'
                    }`;
                    pageBtn.textContent = i;
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        updateTableData();
                        updatePagination();
                    });
                    pageNumbers.appendChild(pageBtn);
                }

                // Update prev/next button states
                document.getElementById('prevPageBtn').disabled = currentPage === 1;
                document.getElementById('nextPageBtn').disabled = currentPage === totalPages || totalPages === 0;
            }

            // Event listeners for pagination buttons
            document.getElementById('prevPageBtn').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    updateTableData();
                    updatePagination();
                }
            });
            
            document.getElementById('nextPageBtn').addEventListener('click', function() {
                const totalPages = Math.ceil(currentReportData.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTableData();
                    updatePagination();
                }
            });

            // Table search functionality
            document.getElementById('tableSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                if (searchTerm === '') {
                    // If search is cleared, restore original data
                    currentPage = 1;
                    updateTableData();
                    updatePagination();
                    return;
                }
                
                // Filter the data based on search term
                const filteredData = currentReportData.filter(item => {
                    // Search in all fields
                    return Object.values(item).some(val => 
                        val && val.toString().toLowerCase().includes(searchTerm)
                    );
                });
                
                // Replace current data with filtered data
                const originalData = currentReportData;
                currentReportData = filteredData;
                currentPage = 1;

                // Update UI
                updateTableData();
                updatePagination();
                
                // Restore original data (but don't re-render)
                currentReportData = originalData;
            });

            // Generate Report Button Click
            document.getElementById('generateReportBtn').addEventListener('click', generateReport);

            // Reset Filters Button Click
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                document.getElementById('reportType').value = 'subscription';
                document.getElementById('dateRange').value = 'last30days';
                
                // Set default dates
                const today = new Date();
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(today.getDate() - 30);
                
                document.getElementById('startDate').value = formatDateForApi(thirtyDaysAgo);
                document.getElementById('endDate').value = formatDateForApi(today);
                document.getElementById('programFilter').value = 'all';
                document.getElementById('subFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
                
                // Update date inputs enabled status based on range selection
                updateDateInputs();
            });

            // Date Range Change
            document.getElementById('dateRange').addEventListener('change', updateDateInputs);
            
            function updateDateInputs() {
                const dateRange = document.getElementById('dateRange').value;
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                
                // Only update the date values if not "custom" range
                if (dateRange !== 'custom') {
                    const today = new Date();
                    let startDate;
                    
                    switch (dateRange) {
                        case 'last7days':
                            startDate = new Date(today);
                            startDate.setDate(today.getDate() - 7);
                            break;
                        case 'last30days':
                            startDate = new Date(today);
                            startDate.setDate(today.getDate() - 30);
                            break;
                        case 'lastYear':
                            startDate = new Date(today);
            // Initialize charts for reports
            const membershipTrendCtx = document.getElementById('membershipTrendChart').getContext('2d');
            const membershipTrendChart = new Chart(membershipTrendCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                    datasets: [{
                        label: 'Total Revenue ($)',
                        data: [8500, 9200, 10500, 11800, 14200],
                        borderColor: '#5C6C90',
                        backgroundColor: 'rgba(92, 108, 144, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'New Subscriptions Revenue ($)',
                        data: [2500, 2100, 3200, 3800, 4500],
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
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
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
                            display: false
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
            
            // Report Type change handler - would update filters and report view
            document.getElementById('reportType').addEventListener('change', function(e) {
                const reportType = e.target.value;
                const reportResults = document.getElementById('reportResults');
                let reportTitle;
                
                switch(reportType) {
                    case 'membership':
                        reportTitle = 'Membership Report';
                        break;
                    case 'revenue':
                        reportTitle = 'Revenue Report';
                        break;
                }
                
                // Update report title - in a real app this would also update the data
                const reportHeader = reportResults.querySelector('h2');
                if (reportHeader) {
                    reportHeader.textContent = reportTitle;
                }
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
                    
                    startDateInput.value = formatDateForApi(startDate);
                    endDateInput.value = formatDateForApi(today);
                }
                
                // Always ensure date inputs are enabled regardless of selection
                // This allows admin to manually adjust dates after selecting a preset range
                startDateInput.disabled = false;
                endDateInput.disabled = false;
            }

            // Export to Excel
            document.getElementById('exportExcelBtn').addEventListener('click', function() {
                const columns = tableColumns[currentReportType];
                const csvContent = generateCSV(columns);

                // Create a download link
                const encodedUri = encodeURI('data:text/csv;charset=utf-8,' + csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', `${currentReportType}_report_${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showSuccessNotification('Report exported to Excel successfully');
            });

            // Function to generate CSV content
            function generateCSV(columns) {
                // Generate header row
                const header = columns.map(col => col.label).join(',');
                
                // Generate data rows
                const rows = currentReportData.map(item => {
                    return columns.map(col => {
                        const value = item[col.field];
                        if (col.format === 'currency') {
                            return formatCurrency(value);
                        } else if (col.format === 'status') {
                            return value == 1 ? 'Active' : 'Inactive';
                        } else if (col.field.toLowerCase().includes('date')) {
                            return formatDate(value);
                        }
                        return value || '';
                    }).join(',');
                }).join('\n');
                
                return header + '\n' + rows;
            }

            // Print report
            document.getElementById('printReportBtn').addEventListener('click', function() {
                window.print();
            });

            // PDF Export Button
            document.getElementById('exportPdfBtn').addEventListener('click', function() {
                // Show loading state
                const originalBtnHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing PDF...';
                this.disabled = true;
                
                const reportTitle = document.getElementById('reportTitle').textContent;
                const dateRange = document.getElementById('reportDateRange').textContent;

                // Create a form to submit the data to the PDF generation script
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../api/reports/export_pdf.php';
                form.target = '_blank'; // Open in new tab

                // Add report type field
                const reportTypeField = document.createElement('input');
                reportTypeField.type = 'hidden';
                reportTypeField.name = 'reportType';
                reportTypeField.value = currentReportType;
                form.appendChild(reportTypeField);

                // Add report title field
                const reportTitleField = document.createElement('input');
                reportTitleField.type = 'hidden';
                reportTitleField.name = 'reportTitle';
                reportTitleField.value = reportTitle;
                form.appendChild(reportTitleField);

                // Add date range field
                const dateRangeField = document.createElement('input');
                dateRangeField.type = 'hidden';
                dateRangeField.name = 'dateRange';
                dateRangeField.value = dateRange;
                form.appendChild(dateRangeField);

                // Add report data as JSON
                const reportDataField = document.createElement('input');
                reportDataField.type = 'hidden';
                reportDataField.name = 'reportData';
                reportDataField.value = JSON.stringify(currentReportData);
                form.appendChild(reportDataField);

                // Add columns configuration
                const columnsField = document.createElement('input');
                columnsField.type = 'hidden';
                columnsField.name = 'columns';
                columnsField.value = JSON.stringify(tableColumns[currentReportType]);
                form.appendChild(columnsField);

                // Add totals data
                const totalsField = document.createElement('input');
                totalsField.type = 'hidden';
                totalsField.name = 'totals';
                totalsField.value = JSON.stringify({
                    count: document.getElementById('card1Value').textContent,
                    activeCount: document.getElementById('card2Value').textContent,
                    revenue: document.getElementById('card3Value').textContent.replace('₱', '')
                });
                form.appendChild(totalsField);

                // Submit the form
                document.body.appendChild(form);
                
                try {
                    form.submit();
                    showSuccessNotification('PDF download initiated');
                } catch (error) {
                    showErrorNotification('Error generating PDF: ' + error.message);
                }
                
                document.body.removeChild(form);
                
                // Restore button state
                this.innerHTML = originalBtnHtml;
                this.disabled = false;
            });

            // Show the initial empty state
            document.getElementById('noReportResults').classList.remove('hidden');

            // Set default dates on page load
            updateDateInputs();

            // Show success notification function
            function showSuccessNotification(message) {
                const notification = document.getElementById('successNotification');
                notification.querySelector('.text-green-700').textContent = message;
                notification.classList.remove('hidden', '-translate-y-16', 'opacity-0');
                
                setTimeout(() => {
                    notification.classList.add('-translate-y-16', 'opacity-0');
                    setTimeout(() => notification.classList.add('hidden'), 500);
                }, 3000);
            }

            // Show error notification function
            function showErrorNotification(message) {
                const notification = document.getElementById('errorNotification');
                document.getElementById('errorMessage').textContent = message;
                notification.classList.remove('hidden', '-translate-y-16', 'opacity-0');
                
                setTimeout(() => {
                    notification.classList.add('-translate-y-16', 'opacity-0');
                    setTimeout(() => notification.classList.add('hidden'), 500);
                }, 3000);
            }
        });
    </script>
    <script src="../../js/report-print.js"></script>
</body>
</html>
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
            
            // Generate Report Button - In real app would collect filter values and generate report
            document.getElementById('generateReportBtn').addEventListener('click', function() {
                // This would be replaced with actual report generation logic
                alert('Report filters applied. Displaying filtered results.');
            });
        });
    </script>
</body>
</html>
