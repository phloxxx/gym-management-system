<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymaster Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles.css">
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
    <!-- Top Navbar -->
    <nav class="bg-primary-dark text-white shadow-md">
        <div class="max-w-full mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <img src="../../src/images/gymaster-logo.png" alt="Gymaster Logo" class="h-8 w-auto filter brightness-0 invert mr-2">
                    <span class="text-lg font-bold">Gymaster</span>
                </div>
                
                <!-- Navigation Section -->
                <div class="hidden md:flex space-x-1">
                    <a href="#" class="px-4 py-2 bg-primary-light rounded-md flex items-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="px-4 py-2 hover:bg-primary-light rounded-md transition-colors flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>Members</span>
                    </a>
                    <a href="#" class="px-4 py-2 hover:bg-primary-light rounded-md transition-colors flex items-center">
                        <i class="fas fa-calendar-check mr-2"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="#" class="px-4 py-2 hover:bg-primary-light rounded-md transition-colors flex items-center">
                        <i class="fas fa-dumbbell mr-2"></i>
                        <span>Classes</span>
                    </a>
                    <a href="#" class="px-4 py-2 hover:bg-primary-light rounded-md transition-colors flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        <span>Schedules</span>
                    </a>
                </div>
                
                <!-- Right Section - User Profile and Notifications -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div>
                        <button class="p-2 rounded-full hover:bg-primary-light">
                            <i class="fas fa-bell"></i>
                        </button>
                    </div>
                    
                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-tertiary flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="ml-2 hidden sm:block">Staff</span>
                            <i class="fas fa-chevron-down ml-1 text-sm"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 w-48 mt-2 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-circle mr-2"></i> Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../../login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu hidden md:hidden px-2 pt-2 pb-4">
            <a href="#" class="block px-4 py-2 bg-primary-light rounded-md flex items-center">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="block px-4 py-2 hover:bg-primary-light rounded-md mt-1 transition-colors flex items-center">
                <i class="fas fa-users mr-3"></i>
                <span>Members</span>
            </a>
            <a href="#" class="block px-4 py-2 hover:bg-primary-light rounded-md mt-1 transition-colors flex items-center">
                <i class="fas fa-calendar-check mr-3"></i>
                <span>Attendance</span>
            </a>
            <a href="#" class="block px-4 py-2 hover:bg-primary-light rounded-md mt-1 transition-colors flex items-center">
                <i class="fas fa-dumbbell mr-3"></i>
                <span>Classes</span>
            </a>
            <a href="#" class="block px-4 py-2 hover:bg-primary-light rounded-md mt-1 transition-colors flex items-center">
                <i class="fas fa-clipboard-list mr-3"></i>
                <span>Schedules</span>
            </a>
            <div class="border-t border-primary-light my-2"></div>
            <a href="../../login.php" class="block px-4 py-2 hover:bg-primary-light rounded-md mt-1 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold text-primary-dark mb-2">Welcome to Staff Dashboard</h2>
            <p class="text-secondary">You are logged in as <strong>Staff</strong>. You have access to member management and daily operations.</p>
            <div class="mt-4 text-sm text-red-500">
                <p>Note: This is a placeholder dashboard for demonstration purposes.</p>
            </div>
        </div>

        <!-- Today's tasks -->
        <h3 class="text-lg font-semibold text-primary-dark mb-4">Today's Tasks</h3>
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center">
                <input type="checkbox" class="mr-3 h-4 w-4">
                <div>
                    <h4 class="font-medium text-primary-dark">Morning check-in verification</h4>
                    <p class="text-sm text-gray-500">8:00 AM - 10:00 AM</p>
                </div>
            </div>
            <div class="p-4 border-b border-gray-200 flex items-center">
                <input type="checkbox" class="mr-3 h-4 w-4">
                <div>
                    <h4 class="font-medium text-primary-dark">New member orientation</h4>
                    <p class="text-sm text-gray-500">11:00 AM - 12:00 PM</p>
                </div>
            </div>
            <div class="p-4 border-b border-gray-200 flex items-center">
                <input type="checkbox" class="mr-3 h-4 w-4">
                <div>
                    <h4 class="font-medium text-primary-dark">Equipment maintenance check</h4>
                    <p class="text-sm text-gray-500">2:00 PM - 3:00 PM</p>
                </div>
            </div>
            <div class="p-4 flex items-center">
                <input type="checkbox" class="mr-3 h-4 w-4">
                <div>
                    <h4 class="font-medium text-primary-dark">Evening attendance monitoring</h4>
                    <p class="text-sm text-gray-500">5:00 PM - 8:00 PM</p>
                </div>
            </div>
        </div>

        <!-- Recent member activities -->
        <h3 class="text-lg font-semibold text-primary-dark mb-4">Recent Member Check-ins</h3>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">John Smith</td>
                        <td class="px-6 py-4 whitespace-nowrap">Today, 8:15 AM</td>
                        <td class="px-6 py-4 whitespace-nowrap">Strength Training</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Maria Rodriguez</td>
                        <td class="px-6 py-4 whitespace-nowrap">Today, 8:30 AM</td>
                        <td class="px-6 py-4 whitespace-nowrap">Yoga</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Robert Johnson</td>
                        <td class="px-6 py-4 whitespace-nowrap">Today, 9:00 AM</td>
                        <td class="px-6 py-4 whitespace-nowrap">CrossFit</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Emily Chen</td>
                        <td class="px-6 py-4 whitespace-nowrap">Today, 9:15 AM</td>
                        <td class="px-6 py-4 whitespace-nowrap">Cardio</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
