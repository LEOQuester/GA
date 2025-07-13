<?php
require_once '../../backend/config/config.php';
require_once '../../backend/includes/auth.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: ../admin_login.php');
    exit;
}

$admin = getAdminInfo();
require_once 'components/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Gaming Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php renderAdminSidebar('reports'); ?>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php renderAdminTopbar('Reports & Analytics', [
                ['name' => 'Dashboard', 'url' => 'dashboard.php'],
                ['name' => 'Reports']
            ]); ?>
            
            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Reports & Analytics</h2>
            <p class="text-gray-600">Business insights and performance metrics for Gaming Arena</p>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Total Revenue</p>
                        <p class="text-2xl font-bold" id="totalRevenue">$0.00</p>
                        <p class="text-green-100 text-xs" id="revenueChange">+0% from last month</p>
                    </div>
                    <i class="fas fa-dollar-sign text-3xl text-green-100"></i>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Bookings</p>
                        <p class="text-2xl font-bold" id="totalBookings">0</p>
                        <p class="text-blue-100 text-xs" id="bookingsChange">+0% from last month</p>
                    </div>
                    <i class="fas fa-calendar-check text-3xl text-blue-100"></i>
                </div>
            </div>

            <!-- Total Hours -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Total Hours Booked</p>
                        <p class="text-2xl font-bold" id="totalHours">0h</p>
                        <p class="text-purple-100 text-xs" id="hoursChange">+0% from last month</p>
                    </div>
                    <i class="fas fa-clock text-3xl text-purple-100"></i>
                </div>
            </div>

            <!-- Average Session -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm">Avg Session Duration</p>
                        <p class="text-2xl font-bold" id="avgSession">0h</p>
                        <p class="text-orange-100 text-xs" id="sessionChange">+0% from last month</p>
                    </div>
                    <i class="fas fa-stopwatch text-3xl text-orange-100"></i>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Monthly Revenue Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>Monthly Revenue
                </h3>
                <div class="h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Bookings by Station -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Bookings by Station
                </h3>
                <div class="h-80">
                    <canvas id="stationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Daily Bookings Trend -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-area text-purple-600 mr-2"></i>Daily Bookings (Last 30 Days)
                </h3>
                <div class="h-80">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Peak Hours Analysis -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar text-orange-600 mr-2"></i>Peak Hours Analysis
                </h3>
                <div class="h-80">
                    <canvas id="hoursChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Stations -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-trophy text-yellow-600 mr-2"></i>Top Performing Stations
                </h3>
                <div class="space-y-3" id="topStations">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-activity text-indigo-600 mr-2"></i>Recent Bookings
                </h3>
                <div class="space-y-3" id="recentBookings">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>
            </main>
        </div>
    </div>

    <script src="../js/reports.js"></script>
        </div>
    </div>
</body>
</html>
