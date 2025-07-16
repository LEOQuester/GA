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
    <title>Dashboard - Gaming Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="assets/css/admin-gaming-theme.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-50 admin-theme">
    <div class="flex h-screen">
        <?php renderAdminSidebar('dashboard'); ?>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php renderAdminTopbar('Dashboard', [
                ['name' => 'Home', 'url' => 'dashboard.php'],
                ['name' => 'Dashboard']
            ]); ?>
            
            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="admin-card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 mr-4">
                        <i class="fas fa-desktop text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-admin-text-muted">Total Stations</p>
                        <p class="text-2xl font-bold text-admin-text-light" id="totalStations">0</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-gradient-to-br from-green-600 to-green-800 mr-4">
                        <i class="fas fa-calendar-check text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-admin-text-muted">Total Bookings</p>
                        <p class="text-2xl font-bold text-admin-text-light" id="totalBookings">0</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-gradient-to-br from-yellow-600 to-yellow-800 mr-4">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-admin-text-muted">Pending Bookings</p>
                        <p class="text-2xl font-bold text-admin-text-light" id="pendingBookings">0</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-gradient-to-br from-purple-600 to-purple-800 mr-4">
                        <i class="fas fa-dollar-sign text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-admin-text-muted">Revenue Today</p>
                        <p class="text-2xl font-bold text-admin-text-light" id="todayRevenue">LKR 0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Section -->
        <div class="admin-card p-6">
            <h2 class="text-2xl font-semibold text-admin-text-light mb-4">
                <i class="fas fa-tachometer-alt text-pink-400 mr-2"></i>Dashboard Overview
            </h2>
            <p class="text-admin-text-muted mb-6">Welcome to the Gaming Arena Admin Panel. Use the sidebar navigation to manage your gaming arena.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="stations.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-4 hover:from-blue-600 hover:to-blue-700 transition-all duration-200 transform hover:scale-105">
                    <div class="flex items-center">
                        <i class="fas fa-desktop text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Gaming Stations</h3>
                            <p class="text-sm text-blue-100">Manage stations</p>
                        </div>
                    </div>
                </a>
                
                <a href="bookings.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-4 hover:from-purple-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Bookings</h3>
                            <p class="text-sm text-purple-100">Monitor bookings</p>
                        </div>
                    </div>
                </a>
                
                <a href="unavailable-slots.php" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-4 hover:from-orange-600 hover:to-orange-700 transition-all duration-200 transform hover:scale-105">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-times text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Unavailable Slots</h3>
                            <p class="text-sm text-orange-100">Manage maintenance</p>
                        </div>
                    </div>
                </a>
                
                <a href="reports.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-4 hover:from-green-600 hover:to-green-700 transition-all duration-200 transform hover:scale-105">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Reports</h3>
                            <p class="text-sm text-green-100">View analytics</p>
                        </div>
                    </div>
                </a>
            </div>
            </main>
        </div>
    </div>

    <script>
        // Simple dashboard functionality
        $(document).ready(function() {
            loadDashboardStats();
        });
        
        function loadDashboardStats() {
            // Load basic stats for the cards
            fetch('../../backend/api/analytics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#totalStations').text(data.metrics.total_stations || 0);
                        $('#totalBookings').text(data.metrics.total_bookings || 0);
                        $('#pendingBookings').text(data.metrics.pending_bookings || 0);
                        $('#todayRevenue').text('LKR ' + (data.metrics.today_revenue || 0).toFixed(2));
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
        }
    </script>
        </div>
    </div>
</body>
</html>
