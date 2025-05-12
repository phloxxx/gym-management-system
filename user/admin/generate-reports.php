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
                    WHERE (
                        /* Start date falls within filter range */
                        (ms.START_DATE >= ? AND ms.START_DATE <= ?)
                        OR 
                        /* End date falls within filter range */
                        (ms.END_DATE >= ? AND ms.END_DATE <= ?)
                        OR
                        /* Subscription spans across filter range */
                        (ms.START_DATE <= ? AND ms.END_DATE >= ?)
                    )
                    $programFilter $subFilter $statusFilter
                    ORDER BY ms.START_DATE DESC";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
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
                    WHERE t.TRANSAC_DATE >= ? AND t.TRANSAC_DATE <= ?
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
    <!-- Add jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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
    <style>
        /* Custom styles for date inputs in custom range mode */
        input[type="date"]:not(:disabled) {
            background-color: #f8faff; /* Light blue background when enabled */
            cursor: pointer;
        }
        
        /* Styling for date container when in custom date mode */
        .custom-date-active::after {
            content: "Editable";
            position: absolute;
            top: -8px;
            right: 10px;
            font-size: 10px;
            padding: 0 4px;
            background: #5C6C90;
            color: white;
            border-radius: 4px;
            z-index: 10;
        }
        
        /* Highlight animation for date fields when switched to custom mode */
        .highlight-pulse {
            animation: pulse-border 2s ease-out;
        }
        
        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(92, 108, 144, 0.5);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(92, 108, 144, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(92, 108, 144, 0);
            }
        }
        
        /* Smooth transitions for buttons and notifications */
        #generateReportBtn, #resetFiltersBtn {
            transition: background-color 0.15s ease;  /* Keep only a quick transition for hover states */
        }
        
        #successNotification, #errorNotification {
            transition: transform 0.3s ease, opacity 0.3s ease;  /* Faster transitions for notifications */
        }
    </style>
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
                    <h1 class="text-xl font-semibold text-primary-dark">Gym Reports <span class="text-sm font-normal text-primary-light">(Auto-generated)</span></h1>
                    
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
                <h2 class="text-lg font-semibold text-primary-dark mb-1">Report Filters</h2>
                <p class="text-sm text-gray-500 mb-4">Reports automatically update when filters are changed</p>
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
                            <input type="date" id="startDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" disabled>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range - End -->
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-primary-light">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <input type="date" id="endDate" class="pl-10 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-light focus:border-transparent transition-all duration-200" value="<?php echo date('Y-m-d'); ?>" disabled>
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
                        <i class="fas fa-sync-alt"></i> Refresh Report
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
                        </button>
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
                    </div>
                </div>
                
                <!-- Detailed Data Table -->
                <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-medium text-gray-500 uppercase" id="tableTitle">Subscription Details</h3>
                        <div class="relative">
                            <input type="text" id="tableSearch" placeholder="Search..." class="px-4 py-2 rounded-md border border-gray-300 focus:border-primary-light focus:ring-primary-light">
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
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- No Results Message - Initially hidden -->
            <div id="noReportResults" class="hidden bg-white rounded-lg shadow-sm p-10 mb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-search text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Data Found</h3>
                <p class="text-gray-500 max-w-md mx-auto">No matching data was found for your current filter settings. Try adjusting your filters to see different results.</p>
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
                    Report refreshed successfully.
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
            let initialLoad = true;
            let filterChangeTimeout = null;

            // Table columns configuration for different report types
            const tableColumns = {
                subscription: [
                    { field: 'Name', label: 'Member Name' },
                    { field: 'Subscription', label: 'Subscription' },
                    { field: 'Start Date', label: 'Start Date' },
                    { field: 'End Date', label: 'End Date' },
                    { field: 'Revenue', label: 'Revenue (PHP)', format: 'currency' },
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
                    { field: 'Revenue', label: 'Amount (PHP)', format: 'currency' },
                ],
            };

            // Function to format date for API request
            function formatDateForApi(date) {
                if (!date) return '';
                
                // Make sure we're working with a Date object
                if (!(date instanceof Date)) {
                    date = new Date(date);
                }
                
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                return `${year}-${month}-${day}`;
            }
            
            // Function to format date for display
            function formatDate(dateStr) {
                if (!dateStr) return '';
                
                try {
                    const date = new Date(dateStr);
                    
                    // Check if date is valid
                    if (isNaN(date.getTime())) {
                        return 'Invalid date';
                    }
                    
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch (error) {
                    console.error('Error formatting date:', error);
                    return 'Error formatting date';
                }
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

            // Function to handle generating reports
            function generateReport(showLoading = true, isReset = false) {
                // Show loading indicator only if specified (for auto-generation, we might not want to show it)
                if (showLoading) {
                    document.getElementById('reportResults').classList.add('hidden');
                    document.getElementById('noReportResults').classList.add('hidden');
                    document.getElementById('loadingReport').classList.remove('hidden');
                }

                // Get filter values
                const reportType = document.getElementById('reportType').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const program = document.getElementById('programFilter').value;
                const subscription = document.getElementById('subFilter').value;
                const status = document.getElementById('statusFilter').value;

                // Validate date inputs before submitting
                if (document.getElementById('dateRange').value === 'custom') {
                    // Ensure dates are valid
                    if (!startDate || !endDate || new Date(startDate) > new Date(endDate)) {
                        showErrorNotification("Please provide valid date range");
                        document.getElementById('loadingReport').classList.add('hidden');
                        return;
                    }
                }

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
                        
                        // Update the UI with the report data
                        updateReportUI(data);
                        
                        // Show the report section
                        document.getElementById('loadingReport').classList.add('hidden');
                        if (currentReportData.length === 0) {
                            // Get a more user-friendly message for no data
                            updateNoDataMessage(startDate, endDate, reportType);
                            document.getElementById('noReportResults').classList.remove('hidden');
                            document.getElementById('reportResults').classList.add('hidden');
                        } else {
                            document.getElementById('noReportResults').classList.add('hidden');
                            document.getElementById('reportResults').classList.remove('hidden');
                            document.getElementById('reportResults').classList.add('opacity-100');
                        }

                        // Show success notification only if not initial load
                        if (!initialLoad && showLoading && currentReportData.length > 0) {
                            // Show different message based on the action type
                            if (isReset) {
                                showSuccessNotification('Filters reset successfully');
                            } else {
                                showSuccessNotification('Report refreshed successfully');
                            }
                        }
                        
                        initialLoad = false;
                    })
                    .catch(error => {
                        console.error('Error generating report:', error);
                        document.getElementById('loadingReport').classList.add('hidden');
                        document.getElementById('noReportResults').classList.remove('hidden');
                        document.getElementById('reportResults').classList.add('hidden');
                        
                        // Show error notification only if not initial load
                        if (!initialLoad) {
                            showErrorNotification('Error generating report: ' + error.message);
                        }
                        
                        initialLoad = false;
                    });
            }
            
            // Update the no data message with contextual information
            function updateNoDataMessage(startDate, endDate, reportType) {
                const noDataElement = document.getElementById('noReportResults');
                const messageElement = noDataElement.querySelector('p');
                
                if (!messageElement) return;
                
                // Format dates for display
                const formattedStart = formatDate(startDate);
                const formattedEnd = formatDate(endDate);
                
                let message = '';
                // Create a more contextual message based on the report type
                if (reportType === 'subscription') {
                    message = `No subscriptions found with start or end dates between ${formattedStart} and ${formattedEnd}. Try adjusting your date range.`;
                } else {
                    message = `No transactions found between ${formattedStart} and ${formattedEnd}. Try adjusting your date range.`;
                }
                
                // Update the message
                messageElement.textContent = message;
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
                
                // Show a more helpful message if no data was found
                if (data.data.length === 0 && document.getElementById('dateRange').value === 'custom') {
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    
                    // Format dates for display
                    const formattedStart = formatDate(startDate);
                    const formattedEnd = formatDate(endDate);
                    
                    // Show a more contextual message
                    const noDataMessage = `No ${currentReportType} data found between ${formattedStart} and ${formattedEnd}. Try adjusting your date range.`;
                    
                    // Update the no data message
                    const noDataElement = document.getElementById('noReportResults').querySelector('p');
                    if (noDataElement) {
                        noDataElement.textContent = noDataMessage;
                    }
                }
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
                    cell.textContent = 'No data available for the selected filters';
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

            // Refresh Report Button Click - Quick refresh without visual feedback
            document.getElementById('generateReportBtn').addEventListener('click', function() {
                // Generate the report (explicitly not a reset)
                generateReport(true, false);
            });

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
                
                // Flag to indicate this is a reset action (to show different message)
                const isReset = true;
                
                // Auto-generate the report with reset filters
                generateReport(true, isReset);
            });

            // Date Range Change
            document.getElementById('dateRange').addEventListener('change', function() {
                updateDateInputs();
                
                // Show tooltip message if custom range is selected
                if (this.value === 'custom') {
                    showDateRangeHelper();
                }
                
                // Auto-generate report when date range changes
                triggerDelayedReportGeneration();
            });
            
            // Add event listeners to validate date ranges
            document.getElementById('startDate').addEventListener('change', function() {
                validateDateRange();
                triggerDelayedReportGeneration();
            });
            
            document.getElementById('endDate').addEventListener('change', function() {
                validateDateRange();
                triggerDelayedReportGeneration();
            });
            
            function updateDateInputs() {
                const dateRange = document.getElementById('dateRange').value;
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                const startDateContainer = startDateInput.closest('.relative');
                const endDateContainer = endDateInput.closest('.relative');
                
                const today = new Date();
                let startDate;
                
                // Determine if we're using custom range
                const isCustomRange = dateRange === 'custom';
                
                // Enable/disable date inputs based on selection
                startDateInput.disabled = !isCustomRange;
                endDateInput.disabled = !isCustomRange;
                
                // Update visual state for date inputs
                if (isCustomRange) {
                    // Custom range - make inputs look active
                    startDateContainer.classList.add('custom-date-active');
                    endDateContainer.classList.add('custom-date-active');
                    
                    // Add focus outline to indicate editability
                    startDateInput.classList.add('border-primary-light', 'ring-2', 'ring-primary-light', 'ring-opacity-20');
                    endDateInput.classList.add('border-primary-light', 'ring-2', 'ring-primary-light', 'ring-opacity-20');
                    
                    // For custom range, don't change the values - let user set them
                    return;
                } else {
                    // Predefined range - make inputs look disabled
                    startDateContainer.classList.remove('custom-date-active');
                    endDateContainer.classList.remove('custom-date-active');
                    
                    // Remove focus outline
                    startDateInput.classList.remove('border-primary-light', 'ring-2', 'ring-primary-light', 'ring-opacity-20');
                    endDateInput.classList.remove('border-primary-light', 'ring-2', 'ring-primary-light', 'ring-opacity-20');
                    
                    // Update date values based on selection
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
                            startDate.setFullYear(today.getFullYear() - 1);
                            break;
                    }
                    
                    startDateInput.value = formatDateForApi(startDate);
                    endDateInput.value = formatDateForApi(today);
                }
            }
            
            // Function to validate date ranges and ensure they make sense
            function validateDateRange() {
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                
                // Only validate if in custom range mode
                if (document.getElementById('dateRange').value !== 'custom') {
                    return;
                }
                
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const today = new Date();
                
                // Set time to beginning of day for accurate comparison
                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);
                
                // Prevent end date from being before start date
                if (endDate < startDate) {
                    showErrorNotification("End date cannot be earlier than start date");
                    endDateInput.value = startDateInput.value;
                }
                
                // Optional: Prevent future dates if needed
                // if (startDate > today) {
                //     showErrorNotification("Start date cannot be in the future");
                //     startDateInput.value = formatDateForApi(today);
                // }
                
                // Optional: Limit date range to prevent performance issues with very large ranges
                const maxRangeDays = 366; // e.g., 1 year
                const daysDiff = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
                
                if (daysDiff > maxRangeDays) {
                    showErrorNotification(`Date range cannot exceed ${maxRangeDays} days for performance reasons`);
                    endDateInput.value = formatDateForApi(new Date(startDate.getTime() + maxRangeDays * 24 * 60 * 60 * 1000));
                }
            }

            // Auto-generate report when filter changes
            document.getElementById('reportType').addEventListener('change', triggerDelayedReportGeneration);
            document.getElementById('startDate').addEventListener('change', triggerDelayedReportGeneration);
            document.getElementById('endDate').addEventListener('change', triggerDelayedReportGeneration);
            document.getElementById('programFilter').addEventListener('change', triggerDelayedReportGeneration);
            document.getElementById('subFilter').addEventListener('change', triggerDelayedReportGeneration);
            document.getElementById('statusFilter').addEventListener('change', triggerDelayedReportGeneration);
            
            // Function to delay report generation when filters change to avoid multiple requests
            function triggerDelayedReportGeneration() {
                // Clear any existing timeout
                if (filterChangeTimeout) {
                    clearTimeout(filterChangeTimeout);
                }
                
                // Set a new timeout
                filterChangeTimeout = setTimeout(() => {
                    generateReport(false, false); // Don't show loading for auto-generation from filter changes, and it's not a reset
                }, 300);
            }

            // Export to Excel
            document.getElementById('exportExcelBtn').addEventListener('click', function() {
                try {
                    const columns = tableColumns[currentReportType];
                    const csvContent = generateCSV(columns);
                    
                    // Create blob with BOM for Excel to recognize UTF-8
                    const BOM = "\uFEFF"; // UTF-8 BOM
                    const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
                    
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const filename = `${currentReportType}_report_${new Date().toISOString().split('T')[0]}.csv`;
                    
                    // Create temporary link for download
                    const link = document.createElement('a');
                    link.style.display = 'none';
                    link.href = url;
                    link.setAttribute('download', filename);
                    document.body.appendChild(link);
                    
                    // Trigger download and cleanup
                    link.click();
                    setTimeout(function() {
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    }, 100);
                    
                    showSuccessNotification('Report exported to Excel successfully');
                } catch (error) {
                    console.error('Error exporting to Excel:', error);
                    showErrorNotification('Error exporting to Excel: ' + error.message);
                }
            });

            // Function to generate CSV content
            function generateCSV(columns) {
                // Helper function for Philippine Peso formatting in CSV
                const formatPHP = (amount) => {
                    // Format for CSV export - remove symbols that might cause issues
                    const value = amount.toString().replace(/[₱,]/g, '');
                    // Format with proper decimals
                    return parseFloat(value).toFixed(2);
                };
                
                // Helper function to properly escape CSV values
                const escapeCSV = (value) => {
                    if (value === null || value === undefined) return '';
                    
                    // Convert to string
                    value = value.toString();
                    
                    // Check if we need to quote this value (contains comma, newline or quote)
                    if (value.includes(',') || value.includes('\n') || value.includes('"') || value.includes("'")) {
                        // Double any existing quotes and wrap in quotes
                        return '"' + value.replace(/"/g, '""') + '"';
                    }
                    
                    return value;
                };
                
                // Generate header row
                const header = columns.map(col => escapeCSV(col.label)).join(',');
                
                // Generate data rows
                const rows = currentReportData.map(item => {
                    return columns.map(col => {
                        let value = item[col.field];
                        
                        if (col.format === 'currency') {
                            value = formatPHP(value);
                        } else if (col.format === 'status') {
                            value = value == 1 ? 'Active' : 'Inactive';
                        } else if (col.field.toLowerCase().includes('date')) {
                            value = formatDate(value);
                        }
                        
                        return escapeCSV(value);
                    }).join(',');
                }).join('\n');
                
                return header + '\n' + rows;
            }

            // Print report - Remove direct window.print() as it's handled by report-print.js
            document.getElementById('printReportBtn').addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default anchor action if any
                // The actual print functionality is handled by report-print.js
                // No need to call window.print() here
            });

            // PDF Export Button - Client-side PDF generation
            document.getElementById('exportPdfBtn').addEventListener('click', function() {
                try {
                    // Get report data
                    const reportTitle = document.getElementById('reportTitle').textContent;
                    const dateRange = document.getElementById('reportDateRange').textContent;
                    const columns = tableColumns[currentReportType];
                    
                    // Helper function for Philippine Peso formatting
                    const formatPHP = (amount) => {
                        // Remove any existing peso sign and commas
                        const value = amount.toString().replace(/[₱,]/g, '');
                        // Format with proper thousands separators but without currency symbol
                        const formattedNumber = parseFloat(value).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        // Use 'PHP' text instead of ₱ symbol to ensure compatibility with all PDF viewers
                        return `PHP ${formattedNumber}`;
                    };
                    
                    // Initialize jsPDF with unicode support
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF({
                        orientation: 'portrait',
                        unit: 'mm',
                        format: 'a4',
                        putOnlyUsedFonts: true,
                        floatPrecision: 16, // for better text positioning
                        hotfixes: ["px_scaling"] // improve font rendering
                    });
                    
                    // Set font to support UTF-8 characters
                    doc.setFont('helvetica', 'normal');
                    
                    // Document styling constants
                    const primaryColor = [8, 23, 56]; // RGB for primary-dark
                    const accentColor = [92, 108, 144]; // RGB for primary-light
                    const pageWidth = doc.internal.pageSize.width;
                    const margin = 20;
                    
                    // Add header with brand color as background - make it taller to fit all text
                    doc.setFillColor(...primaryColor);
                    doc.rect(0, 0, pageWidth, 40, 'F'); // Increase height to 40mm
                    
                    // Add logo text in white - make it larger and centered
                    doc.setTextColor(255, 255, 255);
                    doc.setFontSize(32); // Larger font size for GYMASTER
                    doc.setFont('helvetica', 'bold');
                    doc.text('GYMASTER', pageWidth / 2, 25, { align: 'center' }); // Move down from 22 to 25
                    
                    // Add subtitle in white - position it lower
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'normal');
                    doc.text('GYM MANAGEMENT SYSTEM', pageWidth / 2, 35, { align: 'center' }); // Move down from 32 to 35
                    
                    // Add current date in top right in white
                    const today = new Date().toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'normal');
                    doc.text(today, pageWidth - margin, 10, { align: 'right' });
                    
                    // Add report title with accent bar
                    doc.setTextColor(0, 0, 0);
                    doc.setFontSize(18);
                    doc.setFont('helvetica', 'bold');
                    doc.text(reportTitle, margin, 45);
                    
                    // Add accent bar under title
                    doc.setDrawColor(...accentColor);
                    doc.setLineWidth(0.5);
                    doc.line(margin, 48, pageWidth - margin, 48);
                    
                    // Add date range info
                    doc.setFontSize(11);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(80, 80, 80);
                    doc.text(`Date Range: ${dateRange}`, margin, 55);
                    doc.text(`Generated on: ${new Date().toLocaleString()}`, margin, 60);
                    
                    // Add summary section with box
                    doc.setFillColor(245, 247, 250); // Light gray background
                    doc.setDrawColor(...accentColor);
                    doc.roundedRect(margin, 68, pageWidth - (margin * 2), 20, 2, 2, 'FD');
                    
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(...primaryColor);
                    doc.text('SUMMARY', margin + 5, 76);
                    
                    // Add summary data in columns
                    const count = document.getElementById('card1Value').textContent;
                    const activeCount = document.getElementById('card2Value').textContent;
                    const revenue = document.getElementById('card3Value').textContent;
                    
                    // Format revenue to ensure it shows the Philippine Peso sign
                    const formattedRevenue = formatPHP(revenue);
                    
                    doc.setFontSize(10);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(60, 60, 60);
                    
                    // Calculate better column positioning
                    const boxWidth = pageWidth - (margin * 2);
                    
                    // Column 1 - First third of the box
                    doc.text('Total Records:', margin + 5, 82);
                    doc.setFont('helvetica', 'bold');
                    doc.text(count, margin + 35, 82);
                    
                    // Column 2 - Middle third of the box
                    const middleX = margin + (boxWidth / 3);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Active Records:', middleX, 82);
                    doc.setFont('helvetica', 'bold');
                    doc.text(activeCount, middleX + 32, 82);
                    
                    // Column 3 - Last third of the box
                    const lastX = margin + (boxWidth * 2/3);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Total Revenue:', lastX, 82);
                    doc.setFont('helvetica', 'bold');
                    doc.text(formattedRevenue, lastX + 32, 82);
                    
                    // Get the index of the revenue column for proper formatting
                    const revenueColumnIndex = columns.findIndex(col => col.format === 'currency');
                    
                    // Prepare columnStyles object for autotable
                    const columnStyles = {
                        0: { fontStyle: 'bold' } // Make first column bold (member name)
                    };
                    
                    // Add right alignment for the revenue column if found
                    if (revenueColumnIndex !== -1) {
                        columnStyles[revenueColumnIndex] = { 
                            halign: 'right',
                            cellWidth: 35 // Wider cell for currency values with PHP prefix
                        };
                    }
                    
                    // Generate the table with fixed column headers to prevent letter spacing issues
                    doc.autoTable({
                        head: [columns.map(col => {
                            // Make sure column labels don't have unwanted spaces
                            return col.label.replace(/\s*\(\s*/g, ' (').replace(/\s*\)\s*/g, ')');
                        })],
                        body: currentReportData.map(row => columns.map(col => {
                            const value = row[col.field];
                            if (col.format === 'currency') {
                                return formatPHP(value);
                            } else if (col.format === 'status') {
                                return value == 1 ? 'Active' : 'Inactive';
                            } else if (col.field.toLowerCase().includes('date')) {
                                return formatDate(value);
                            }
                            return value || '';
                        })),
                        startY: 95,
                        theme: 'grid',
                        headStyles: {
                            fillColor: primaryColor,
                            textColor: [255, 255, 255],
                            fontStyle: 'bold',
                            halign: 'center',
                            lineWidth: 1
                        },
                        bodyStyles: {
                            textColor: [50, 50, 50],
                            fontSize: 9,
                            lineWidth: 0.5
                        },
                        alternateRowStyles: {
                            fillColor: [245, 247, 250]
                        },
                        columnStyles: columnStyles,
                        margin: { top: 95, left: margin, right: margin },
                        tableWidth: 'auto', // Optimize table width
                        didDrawPage: (data) => {
                            // Add header to each page
                            doc.setFillColor(...primaryColor);
                            doc.rect(0, 0, pageWidth, 15, 'F');
                            
                            // Remove GYMASTER text from upper left corner
                            // doc.setTextColor(255, 255, 255);
                            // doc.setFontSize(10);
                            // doc.setFont('helvetica', 'bold');
                            // doc.text('GYMASTER', margin, 10);
                            
                            // Add footer with page number
                            const pageCount = doc.internal.getNumberOfPages();
                            doc.setFontSize(8);
                            doc.setTextColor(100, 100, 100);
                            doc.text(`Page ${data.pageNumber} of ${pageCount}`, pageWidth - margin, doc.internal.pageSize.height - 10, { align: 'right' });
                            
                            // Add line above footer
                            doc.setDrawColor(200, 200, 200);
                            doc.setLineWidth(0.5);
                            doc.line(margin, doc.internal.pageSize.height - 15, pageWidth - margin, doc.internal.pageSize.height - 15);
                            
                            // Add footer text
                            doc.setFontSize(8);
                            doc.setTextColor(100, 100, 100);
                            doc.text('© GYMASTER  Gym Management System', margin, doc.internal.pageSize.height - 10);
                        }
                    });
                    
                    
                    // Save the PDF
                    doc.save(`${currentReportType}_report_${new Date().toISOString().split('T')[0]}.pdf`);
                    
                    // Show success notification
                    showSuccessNotification('PDF downloaded successfully');
                } catch (error) {
                    console.error('Error generating PDF:', error);
                    showErrorNotification('Error generating PDF: ' + error.message);
                }
            });

            // Show success notification function
            function showSuccessNotification(message) {
                const notification = document.getElementById('successNotification');
                notification.querySelector('.text-green-700').textContent = message;
                
                // Show notification immediately
                notification.classList.remove('hidden', '-translate-y-16', 'opacity-0');
                
                // Auto-hide after 3 seconds
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
            
            // Initialize the page with reports loaded automatically
            // Set default dates first
            updateDateInputs();
            
            // Generate report automatically on page load (don't show loading, not a reset)
            generateReport(true, false);

            // Function to show a helper tooltip when switching to custom date range
            function showDateRangeHelper() {
                const message = "You can now edit the Start Date and End Date fields directly.";
                showSuccessNotification(message);
                
                // Also add a subtle highlight effect to the date fields
                const dateFields = [document.getElementById('startDate'), document.getElementById('endDate')];
                
                dateFields.forEach(field => {
                    // Add pulsing highlight effect
                    field.classList.add('highlight-pulse');
                    
                    // Remove after animation completes
                    setTimeout(() => {
                        field.classList.remove('highlight-pulse');
                    }, 2000);
                    
                    // Focus on the start date field to encourage interaction
                    dateFields[0].focus();
                });
            }
        });
    </script>
    <script src="../../js/report-print.js"></script>
</body>
</html>