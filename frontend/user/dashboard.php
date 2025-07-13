<?php
require_once '../../backend/config/config.php';
require_once '../../backend/includes/auth.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user = getUserInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - G-Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <!-- Flatpickr CSS for time picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Flatpickr JS for time picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-purple-600">
                        <i class="fas fa-gamepad mr-2"></i>G-Arena
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?= htmlspecialchars($user['full_name']) ?></span>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 mr-4">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Bookings</p>
                        <p class="text-2xl font-bold" id="totalBookings">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 mr-4">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Hours Played</p>
                        <p class="text-2xl font-bold" id="totalHours">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 mr-4">
                        <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Spent</p>
                        <p class="text-2xl font-bold" id="totalSpent">$0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <button class="tab-button active border-b-2 border-purple-500 py-2 px-4 text-sm font-medium text-purple-600" data-tab="book">
                        <i class="fas fa-plus mr-2"></i>Book Station
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-4 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="bookings">
                        <i class="fas fa-list mr-2"></i>My Bookings
                    </button>
                </nav>
            </div>

            <!-- Book Station Tab -->
            <div id="book" class="tab-content p-6">
                <h2 class="text-lg font-semibold mb-6">Book a Gaming Station</h2>
                
                <form id="bookingForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="stationSelect" class="block text-sm font-medium text-gray-700 mb-2">Gaming Station</label>
                            <select id="stationSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Select a station...</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="bookingDate" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" id="bookingDate" required min="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        
                        <div>
                            <label for="startTime" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <div class="relative">
                                <input type="text" id="startTime" required readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 cursor-pointer bg-white" 
                                       placeholder="Select start time...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Arena operates from 9:00 AM to 11:00 PM</p>
                        </div>
                        
                        <div>
                            <label for="endTime" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <div class="relative">
                                <input type="text" id="endTime" required readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 cursor-pointer bg-white" 
                                       placeholder="Select end time...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Must be after start time</p>
                        </div>
                    </div>
                    
                    <!-- Station Info Card -->
                    <div id="stationInfo" class="hidden bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2">Station Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p><strong>Type:</strong> <span id="stationTypeInfo"></span></p>
                                <p><strong>Rate:</strong> <span id="stationRateInfo"></span>/hour</p>
                            </div>
                            <div>
                                <p><strong>Description:</strong></p>
                                <p id="stationDescInfo" class="text-gray-600"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Summary -->
                    <div id="bookingSummary" class="hidden bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-2">Booking Summary</h3>
                        <div class="text-sm space-y-1">
                            <p><strong>Duration:</strong> <span id="summaryDuration"></span></p>
                            <p><strong>Total Amount:</strong> <span id="summaryAmount" class="text-lg font-bold text-blue-600"></span></p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="Any special requests or notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Station
                        </button>
                    </div>
                </form>
            </div>

            <!-- My Bookings Tab -->
            <div id="bookings" class="tab-content p-6 hidden">
                <h2 class="text-lg font-semibold mb-6">My Bookings</h2>
                
                <div class="overflow-x-auto">
                    <table id="userBookingsTable" class="min-w-full">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Station</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Booked On</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/user-dashboard.js"></script>
</body>
</html>
