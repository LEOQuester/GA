<?php
function renderAdminSidebar($currentPage = '') {
?>
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" id="sidebar">
        <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-700">
            <div class="flex items-center">
                <img src="../../logo/logo.png" alt="G-Arena Logo" class="h-8 w-auto mr-2">
                <span class="text-white text-xl font-bold">G-Arena Admin</span>
            </div>
        </div>
        
        <div class="h-full px-3 py-4 overflow-y-auto bg-gray-900">
            <ul class="space-y-2 font-medium">
                <!-- Dashboard -->
                <li>
                    <a href="dashboard.php" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'dashboard' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-tachometer-alt w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'dashboard' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                
                <!-- Gaming Stations -->
                <li>
                    <a href="stations.php" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'stations' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-desktop w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'stations' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Gaming Stations</span>
                    </a>
                </li>
                
                <!-- Bookings -->
                <li>
                    <a href="bookings.php" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'bookings' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-calendar-check w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'bookings' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Bookings</span>
                    </a>
                </li>
                
                <!-- Arena Unavailable Slots -->
                <li>
                    <a href="unavailable-slots.php" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'unavailable-slots' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-calendar-times w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'unavailable-slots' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Arena Unavailable</span>
                    </a>
                </li>
                
                <!-- Food Management -->
                <li>
                    <a href="../admin_panel.html" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'food-management' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-utensils w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'food-management' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Food Management</span>
                    </a>
                </li>
                
                <!-- Reports & Analytics -->
                <li>
                    <a href="reports.php" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700 group <?= $currentPage === 'reports' ? 'bg-gray-700 text-white' : '' ?>">
                        <i class="fas fa-chart-line w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white <?= $currentPage === 'reports' ? 'text-white' : '' ?>"></i>
                        <span class="ml-3">Reports & Analytics</span>
                    </a>
                </li>
            </ul>
            
            <!-- Logout Button -->
            <div class="absolute bottom-0 left-0 right-0 p-4">
                <a href="../../backend/api/admin_login.php?logout=1" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-red-600 group">
                    <i class="fas fa-sign-out-alt w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile menu button -->
    <div class="lg:hidden">
        <button type="button" class="inline-flex items-center p-2 mt-2 ml-3 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" onclick="toggleSidebar()">
            <span class="sr-only">Open sidebar</span>
            <i class="fas fa-bars w-6 h-6"></i>
        </button>
    </div>

    <!-- Sidebar overlay for mobile -->
    <div class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden hidden" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
<?php
}

function renderAdminTopbar($title = 'Admin Panel', $breadcrumbs = []) {
    global $admin;
?>
    <!-- Top bar -->
    <div class="bg-admin-dark shadow-sm border-b border-admin-purple/20">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button type="button" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-admin-text-muted hover:text-admin-text-light hover:bg-admin-darker focus:outline-none focus:ring-2 focus:ring-inset focus:ring-admin-purple" onclick="toggleSidebar()">
                        <i class="fas fa-bars w-6 h-6"></i>
                    </button>
                    
                    <div class="ml-4 lg:ml-0">
                        <h1 class="text-2xl font-bold text-admin-text-light"><?= htmlspecialchars($title) ?></h1>
                        <?php if (!empty($breadcrumbs)): ?>
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                        <li class="inline-flex items-center">
                                            <?php if ($index > 0): ?>
                                                <i class="fas fa-chevron-right w-3 h-3 text-admin-text-muted mx-1"></i>
                                            <?php endif; ?>
                                            <?php if (isset($breadcrumb['url'])): ?>
                                                <a href="<?= htmlspecialchars($breadcrumb['url']) ?>" class="text-sm font-medium text-admin-text-muted hover:text-admin-text-light">
                                                    <?= htmlspecialchars($breadcrumb['name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-sm font-medium text-admin-text-muted">
                                                    <?= htmlspecialchars($breadcrumb['name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-admin-text-muted">Welcome, <?= htmlspecialchars($admin['username']) ?></span>
                    <div class="h-8 w-8 bg-admin-purple rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?= strtoupper(substr($admin['username'], 0, 1)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
