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
    <title>Gaming Stations Management - Gaming Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="assets/css/admin-gaming-theme.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-50 admin-theme">
    <div class="flex h-screen">
        <?php renderAdminSidebar('stations'); ?>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php renderAdminTopbar('Gaming Stations Management', [
                ['name' => 'Dashboard', 'url' => 'dashboard.php'],
                ['name' => 'Gaming Stations']
            ]); ?>
            
            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Gaming Stations Management -->
                <div class="admin-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-admin-text-light">
                            <i class="fas fa-desktop text-pink-400 mr-2"></i>Gaming Stations Management
                        </h2>
                        <button id="addStationBtn" class="admin-btn">
                            <i class="fas fa-plus mr-2"></i>Add Station
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto admin-table">
                        <table id="stationsTable" class="w-full table-auto">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-gamepad mr-2"></i>Station Name</th>
                                    <th><i class="fas fa-tag mr-2"></i>Type</th>
                                    <th><i class="fas fa-coins mr-2"></i>Hourly Rate</th>
                                    <th><i class="fas fa-power-off mr-2"></i>Status</th>
                                    <th><i class="fas fa-info-circle mr-2"></i>Description</th>
                                    <th><i class="fas fa-cog mr-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Station Modal -->
                <div id="stationModal" class="fixed inset-0 modal-overlay hidden z-50">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="modal-content max-w-md w-full p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 id="modalTitle" class="text-lg font-semibold text-admin-text-light">Add Gaming Station</h3>
                                <button id="closeModal" class="text-admin-text-muted hover:text-white">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <form id="stationForm">
                                <input type="hidden" id="stationId">
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="stationName" class="block text-sm font-medium text-admin-text-light">Station Name</label>
                                        <input type="text" id="stationName" required class="admin-input mt-1 block w-full">
                                    </div>
                                    
                                    <div>
                                        <label for="stationType" class="block text-sm font-medium text-admin-text-light">Station Type</label>
                                        <select id="stationType" required class="admin-input mt-1 block w-full">
                                            <option value="">Select Type</option>
                                            <option value="Gaming PC">Gaming PC</option>
                                            <option value="Console">Console</option>
                                            <option value="VR Station">VR Station</option>
                                            <option value="Racing Simulator">Racing Simulator</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="hourlyRate" class="block text-sm font-medium text-admin-text-light">Hourly Rate (LKR)</label>
                                        <input type="number" id="hourlyRate" min="0" step="0.01" required class="admin-input mt-1 block w-full">
                                    </div>
                                    
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-admin-text-light">Status</label>
                                        <select id="status" required class="admin-input mt-1 block w-full">
                                            <option value="active">Active</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-admin-text-light">Description</label>
                                        <textarea id="description" rows="3" class="admin-input mt-1 block w-full"></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" id="cancelBtn" class="px-4 py-2 border border-admin-purple rounded-md text-admin-text-muted hover:bg-admin-dark">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-admin-purple text-white rounded-md hover:bg-pink-600">Save Station</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/stations.js"></script>
        </div>
    </div>
</body>
</html>
