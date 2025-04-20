<?php
session_start();
require_once '../../config/program_coach_functions.php';

// Get initial data
$programsResult = getAllPrograms();
$coachesResult = getAllCoaches();

$programs = $programsResult['success'] ? $programsResult['programs'] : [];
$coaches = $coachesResult['success'] ? $coachesResult['coaches'] : [];

// Get user data from session
$fullName = $_SESSION['name'];
$role = ucfirst(strtolower($_SESSION['role']));
?>
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
                                <!-- Dynamic content will be loaded here -->
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Program Modal -->
    <div id="programModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold" id="programModalTitle">Add Program</h3>
            </div>
            <form id="programForm">
                <div class="p-6">
                    <input type="hidden" id="programId" name="PROGRAM_ID">
                    <div class="mb-4">
                        <label for="programName" class="block text-sm font-medium text-gray-700">Program Name</label>
                        <input type="text" id="programName" name="PROGRAM_NAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="programStatus" name="IS_ACTIVE" class="rounded border-gray-300 text-primary-dark focus:ring-primary-light" checked>
                                <span class="ml-2">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" class="closeModal px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" id="saveProgramButton" class="px-4 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90">Save Program</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Coach Modal -->
    <div id="coachModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold" id="coachModalTitle">Add Coach</h3>
            </div>
            <form id="coachForm">
                <div class="p-6">
                    <input type="hidden" id="coachId" name="COACH_ID">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="coachFname" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" id="coachFname" name="COACH_FNAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                        </div>
                        <div>
                            <label for="coachLname" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" id="coachLname" name="COACH_LNAME" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="coachEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="coachEmail" name="EMAIL" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div class="mb-4">
                        <label for="coachPhone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" id="coachPhone" name="PHONE_NUMBER" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <div class="mt-2 space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="GENDER" value="MALE" class="text-primary-dark focus:ring-primary-light" checked>
                                <span class="ml-2">Male</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="GENDER" value="FEMALE" class="text-primary-dark focus:ring-primary-light">
                                <span class="ml-2">Female</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Program Assignments</label>
                        <div class="mt-2 space-y-2" id="programAssignments">
                            <?php foreach ($programs as $program): ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="PROGRAM_ASSIGNMENTS[]" value="<?php echo $program['PROGRAM_ID']; ?>" class="rounded border-gray-300 text-primary-dark focus:ring-primary-light">
                                <span class="ml-2"><?php echo htmlspecialchars($program['PROGRAM_NAME']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="coachStatus" name="IS_ACTIVE" class="rounded border-gray-300 text-primary-dark focus:ring-primary-light" checked>
                                <span class="ml-2">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" class="closeModal px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" id="saveCoachButton" class="px-4 py-2 bg-primary-dark text-white rounded-md hover:bg-opacity-90">Save Coach</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Refresh functions
        function refreshProgramsTable() {
            $.ajax({
                url: 'program_coach_handler.php',
                type: 'POST',
                data: { action: 'get_programs' },
                success: function(response) {
                    if (response.success) {
                        updateProgramsTable(response.programs);
                    } else {
                        console.error('Error fetching programs:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        function refreshCoachesTable() {
            $.ajax({
                url: 'program_coach_handler.php',
                type: 'POST',
                data: { action: 'get_coaches' },
                success: function(response) {
                    if (response.success) {
                        updateCoachesTable(response.coaches);
                    } else {
                        console.error('Error fetching coaches:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        // Initialize tables when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            refreshProgramsTable();
            refreshCoachesTable();
        });

        // Add tab switching with refresh
        document.getElementById('programs-tab').addEventListener('click', function() {
            document.getElementById('programs').classList.remove('hidden');
            document.getElementById('coaches').classList.add('hidden');
            refreshProgramsTable();
        });

        document.getElementById('coaches-tab').addEventListener('click', function() {
            document.getElementById('coaches').classList.remove('hidden');
            document.getElementById('programs').classList.add('hidden');
            refreshCoachesTable();
        });

        // Table update functions
        function updateProgramsTable(programs) {
            const tbody = document.querySelector('#programsTable tbody');
            tbody.innerHTML = '';
            
            programs.forEach(program => {
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${program.PROGRAM_NAME}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${program.IS_ACTIVE ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                <i class="fas ${program.IS_ACTIVE ? 'fa-check-circle' : 'fa-times-circle'} mr-1.5"></i>
                                ${program.IS_ACTIVE ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex space-x-2 justify-center">
                                <button onclick="editProgram(${program.PROGRAM_ID}, '${program.PROGRAM_NAME}', ${program.IS_ACTIVE})" 
                                    class="text-primary-dark hover:text-primary-light h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button onclick="deleteProgram(${program.PROGRAM_ID})" 
                                    class="text-red-600 hover:text-red-800 h-9 w-9 inline-flex items-center justify-center bg-red-100 hover:bg-red-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        function updateCoachesTable(coaches) {
            const tbody = document.querySelector('#coachesTable tbody');
            tbody.innerHTML = '';
            
            coaches.forEach(coach => {
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${coach.COACH_FNAME} ${coach.COACH_LNAME}</div>
                            <div class="text-sm text-gray-500">${coach.EMAIL}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                ${coach.SPECIALIZATION ? coach.SPECIALIZATION.split(',').map(spec => `
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-dumbbell mr-1"></i>${spec.trim()}
                                    </span>
                                `).join('') : ''}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${coach.IS_ACTIVE ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                <i class="fas ${coach.IS_ACTIVE ? 'fa-check-circle' : 'fa-times-circle'} mr-1.5"></i>
                                ${coach.IS_ACTIVE ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex space-x-2 justify-center">
                                <button onclick="editCoach(${JSON.stringify(coach)})" 
                                    class="text-primary-dark hover:text-primary-light h-9 w-9 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button onclick="deleteCoach(${coach.COACH_ID})" 
                                    class="text-red-600 hover:text-red-800 h-9 w-9 inline-flex items-center justify-center bg-red-100 hover:bg-red-200 rounded-full transition-all duration-200">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        // Edit functions
        function editProgram(id, name, isActive) {
            document.getElementById('programId').value = id;
            document.getElementById('programName').value = name;
            document.getElementById('programStatus').checked = isActive;
            document.getElementById('programModalTitle').textContent = 'Edit Program';
            document.getElementById('programModal').classList.remove('hidden');
        }

        function editCoach(coach) {
            document.getElementById('coachId').value = coach.COACH_ID;
            document.getElementById('coachFname').value = coach.COACH_FNAME;
            document.getElementById('coachLname').value = coach.COACH_LNAME;
            document.getElementById('coachEmail').value = coach.EMAIL;
            document.getElementById('coachPhone').value = coach.PHONE_NUMBER;
            document.querySelector(`input[name="GENDER"][value="${coach.GENDER}"]`).checked = true;
            document.getElementById('coachStatus').checked = coach.IS_ACTIVE;

            // Reset and set program assignments
            document.querySelectorAll('input[name="PROGRAM_ASSIGNMENTS[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            if (coach.SPECIALIZATION) {
                const assignments = coach.SPECIALIZATION.split(',').map(p => p.trim());
                document.querySelectorAll('input[name="PROGRAM_ASSIGNMENTS[]"]').forEach(checkbox => {
                    if (assignments.includes(checkbox.nextElementSibling.textContent.trim())) {
                        checkbox.checked = true;
                    }
                });
            }

            document.getElementById('coachModalTitle').textContent = 'Edit Coach';
            document.getElementById('coachModal').classList.remove('hidden');
        }

        // Delete functions
        function deleteProgram(id) {
            if (confirm('Are you sure you want to delete this program?')) {
                $.ajax({
                    url: 'program_coach_handler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_program',
                        PROGRAM_ID: id
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Program deleted successfully');
                            refreshProgramsTable();
                        } else {
                            alert(response.message || 'Error deleting program');
                        }
                    },
                    error: function() {
                        alert('Error connecting to server');
                    }
                });
            }
        }

        function deleteCoach(id) {
            if (confirm('Are you sure you want to delete this coach?')) {
                $.ajax({
                    url: 'program_coach_handler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_coach',
                        COACH_ID: id
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Coach deleted successfully');
                            refreshCoachesTable();
                        } else {
                            alert(response.message || 'Error deleting coach');
                        }
                    },
                    error: function() {
                        alert('Error connecting to server');
                    }
                });
            }
        }

        // Save functions
        function saveProgram(formData) {
            $.ajax({
                url: 'program_coach_handler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Program saved successfully');
                        document.getElementById('programModal').classList.add('hidden');
                        document.getElementById('programForm').reset();
                        refreshProgramsTable();
                    } else {
                        alert(response.message || 'Error saving program');
                    }
                },
                error: function() {
                    alert('Error connecting to server');
                }
            });
        }

        function saveCoach(formData) {
            $.ajax({
                url: 'program_coach_handler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Coach saved successfully');
                        document.getElementById('coachModal').classList.add('hidden');
                        document.getElementById('coachForm').reset();
                        refreshCoachesTable();
                    } else {
                        alert(response.message || 'Error saving coach');
                    }
                },
                error: function() {
                    alert('Error connecting to server');
                }
            });
        }

        // Initialize everything when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh tables on load
            refreshProgramsTable();
            refreshCoachesTable();

            // Program form submission
            document.getElementById('saveProgramButton').addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('programForm');
                if (form.checkValidity()) {
                    const formData = {
                        action: document.getElementById('programId').value ? 'update_program' : 'add_program',
                        PROGRAM_ID: document.getElementById('programId').value,
                        PROGRAM_NAME: document.getElementById('programName').value.trim(),
                        IS_ACTIVE: document.getElementById('programStatus').checked ? 1 : 0
                    };
                    
                    if (!formData.PROGRAM_NAME) {
                        alert('Program name cannot be empty');
                        return;
                    }
                    
                    saveProgram(formData);
                } else {
                    form.reportValidity();
                }
            });

            // Single Coach Save Handler
            document.getElementById('saveCoachButton').addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('coachForm');
                if (form.checkValidity()) {
                    const formData = new FormData(form);
                    const data = {
                        action: document.getElementById('coachId').value ? 'update_coach' : 'add_coach',
                        COACH_ID: document.getElementById('coachId').value,
                        COACH_FNAME: document.getElementById('coachFname').value.trim(),
                        COACH_LNAME: document.getElementById('coachLname').value.trim(),
                        EMAIL: document.getElementById('coachEmail').value.trim(),
                        PHONE_NUMBER: document.getElementById('coachPhone').value.trim(),
                        GENDER: document.querySelector('input[name="GENDER"]:checked').value,
                        IS_ACTIVE: document.getElementById('coachStatus').checked ? 1 : 0
                    };

                    // Get program assignments
                    const programAssignments = [];
                    document.querySelectorAll('input[name="PROGRAM_ASSIGNMENTS[]"]:checked').forEach(checkbox => {
                        programAssignments.push(checkbox.value);
                    });
                    data.PROGRAM_ASSIGNMENTS = JSON.stringify(programAssignments);
                    
                    saveCoach(data);
                } else {
                    form.reportValidity();
                }
            });
        });

        // Initialize event listeners for add buttons
        document.getElementById('addProgramBtn').addEventListener('click', function() {
            // Reset form
            document.getElementById('programForm').reset();
            document.getElementById('programId').value = '';
            document.getElementById('programModalTitle').textContent = 'Add Program';
            document.getElementById('programModal').classList.remove('hidden');
        });

        document.getElementById('addCoachBtn').addEventListener('click', function() {
            // Reset form
            document.getElementById('coachForm').reset();
            document.getElementById('coachId').value = '';
            document.getElementById('coachModalTitle').textContent = 'Add Coach';
            document.getElementById('coachModal').classList.remove('hidden');
        });

        // Close modal functionality
        document.querySelectorAll('.closeModal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('programModal').classList.add('hidden');
                document.getElementById('coachModal').classList.add('hidden');
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        });

        // Initial table load
        refreshProgramsTable();
        refreshCoachesTable();
    </script>
</body>
</html>