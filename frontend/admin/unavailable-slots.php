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
    <title>Unavailable Slots Management - Gaming Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="stylesheet" href="assets/css/admin-gaming-theme.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-50 admin-theme">
    <div class="flex h-screen">
        <?php renderAdminSidebar('unavailable-slots'); ?>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php renderAdminTopbar('Unavailable Slots Management', [
                ['name' => 'Dashboard', 'url' => 'dashboard.php'],
                ['name' => 'Unavailable Slots']
            ]); ?>
            
            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-admin-text-light mb-2">Arena Unavailable Slots</h2>
            <p class="text-admin-text-muted">Manage times when the entire gaming arena is unavailable for bookings</p>
        </div>

        <!-- Add New Slot Form -->
        <div class="admin-card p-6 mb-8">
            <h3 class="text-xl font-semibold text-admin-text-light mb-4">
                <i class="fas fa-plus-circle text-pink-400 mr-2"></i>Add New Arena Unavailable Period
            </h3>
            
            <form id="slotForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <input type="hidden" id="slotId">

                <div>
                    <label for="slotDate" class="block text-sm font-medium text-admin-text-light mb-2">Date</label>
                    <input type="text" id="slotDate" required class="admin-input w-full px-4 py-3">
                </div>

                <div>
                    <label for="startTime" class="block text-sm font-medium text-admin-text-light mb-2">Start Time</label>
                    <div class="relative">
                        <input type="text" id="startTime" required class="admin-input w-full pl-12 pr-4 py-3">
                        <i class="fas fa-clock absolute left-4 top-4 text-pink-400"></i>
                    </div>
                </div>

                <div>
                    <label for="endTime" class="block text-sm font-medium text-admin-text-light mb-2">End Time</label>
                    <div class="relative">
                        <input type="text" id="endTime" required class="admin-input w-full pl-12 pr-4 py-3">
                        <i class="fas fa-clock absolute left-4 top-4 text-pink-400"></i>
                    </div>
                </div>

                <div>
                    <label for="reason" class="block text-sm font-medium text-admin-text-light mb-2">Reason</label>
                    <select id="reason" required class="admin-input w-full px-4 py-3">
                        <option value="Arena maintenance">Arena maintenance</option>
                        <option value="System upgrade">System upgrade</option>
                        <option value="Deep cleaning">Deep cleaning</option>
                        <option value="Private event">Private event</option>
                        <option value="Equipment maintenance">Equipment maintenance</option>
                        <option value="Staff training">Staff training</option>
                        <option value="Emergency closure">Emergency closure</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="admin-btn w-full">
                        <i class="fas fa-save mr-2"></i><span id="submitText">Add Period</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Slots Table -->
        <div class="admin-card p-6">
            <h3 class="text-xl font-semibold text-admin-text-light mb-4">
                <i class="fas fa-list text-pink-400 mr-2"></i>Arena Unavailable Periods
            </h3>
            
            <div class="overflow-x-auto admin-table">
                <table id="slotsTable" class="w-full table-auto">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar mr-2"></i>Date</th>
                            <th><i class="fas fa-clock mr-2"></i>Time Period</th>
                            <th><i class="fas fa-exclamation-triangle mr-2"></i>Reason</th>
                            <th><i class="fas fa-calendar-plus mr-2"></i>Created</th>
                            <th><i class="fas fa-cog mr-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
            </main>
        </div>
    </div>

    <script src="../js/unavailable-slots.js"></script>
</body>
</html>
