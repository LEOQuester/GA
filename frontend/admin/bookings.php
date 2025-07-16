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
    <title>Bookings Management - Gaming Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="assets/css/admin-gaming-theme.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-50 admin-theme">
    <div class="flex h-screen">
        <?php renderAdminSidebar('bookings'); ?>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php renderAdminTopbar('Bookings Management', [
                ['name' => 'Dashboard', 'url' => 'dashboard.php'],
                ['name' => 'Bookings']
            ]); ?>
            
            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Bookings Management -->
                <div class="admin-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-admin-text-light">
                            <i class="fas fa-calendar-check text-pink-400 mr-2"></i>Bookings Management
                        </h2>
                        <div class="flex space-x-3">
                            <select id="statusFilter" class="admin-input">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select id="stationFilter" class="admin-input">
                                <option value="">All Stations</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto admin-table">
                        <table id="bookingsTable" class="w-full table-auto">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag mr-2"></i>Reference</th>
                                    <th><i class="fas fa-user mr-2"></i>User</th>
                                    <th><i class="fas fa-gamepad mr-2"></i>Station</th>
                                    <th><i class="fas fa-calendar mr-2"></i>Date</th>
                                    <th><i class="fas fa-clock mr-2"></i>Time</th>
                                    <th><i class="fas fa-hourglass mr-2"></i>Duration</th>
                                    <th><i class="fas fa-coins mr-2"></i>Amount</th>
                                    <th><i class="fas fa-flag mr-2"></i>Status</th>
                                    <th><i class="fas fa-cog mr-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Booking Details Modal -->
                <div id="bookingModal" class="fixed inset-0 modal-overlay hidden z-50">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="modal-content max-w-lg w-full p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-admin-text-light">Booking Details</h3>
                                <button id="closeModal" class="text-admin-text-muted hover:text-white">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div id="bookingDetails" class="space-y-4">
                                <!-- Dynamic content -->
                            </div>
                            
                            <div class="flex justify-end space-x-3 mt-6">
                                <button id="confirmBtn" class="admin-btn bg-green-600 hover:bg-green-700 hidden">Confirm</button>
                                <button id="cancelBookingBtn" class="admin-btn bg-red-600 hover:bg-red-700 hidden">Cancel</button>
                                <button id="closeDetailsBtn" class="admin-btn bg-gray-600 hover:bg-gray-700">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/bookings.js"></script>
        </div>
    </div>
</body>
</html>
