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
    <title>Gamer Dashboard - G-Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/gaming-theme.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* Gaming Background Pattern */
        .bg-gaming-pattern {
            background: linear-gradient(135deg, #18122B 0%, #393053 50%, #18122B 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        /* Custom Dashboard Styles */
        .dashboard-nav {
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            border-bottom: 2px solid var(--primary-purple);
            box-shadow: 0 4px 20px rgba(138, 43, 226, 0.3);
        }
        
        .tab-active, .tab-button.active {
            background: linear-gradient(135deg, var(--primary-purple) 0%, #6A1B9A 100%);
            color: white !important;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.6);
            border-color: var(--primary-purple) !important;
        }
        
        .tab-button {
            transition: all 0.3s ease;
        }
        
        .tab-button:hover {
            color: white !important;
            background: rgba(138, 43, 226, 0.2);
        }
        
        /* Enhanced DataTable Gaming Styles */
        .dataTables_wrapper {
            background: transparent !important;
            color: var(--text-light) !important;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-light) !important;
        }
        
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--text-light) !important;
            font-weight: 600;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(34, 34, 51, 0.8) !important;
            border: 2px solid rgba(138, 43, 226, 0.3) !important;
            color: white !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            margin: 0 8px !important;
        }
        
        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-purple) !important;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.4) !important;
            outline: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: rgba(34, 34, 51, 0.8) !important;
            border: 2px solid rgba(138, 43, 226, 0.3) !important;
            color: var(--text-light) !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
            padding: 8px 12px !important;
            transition: all 0.3s ease !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(138, 43, 226, 0.3) !important;
            border-color: var(--primary-purple) !important;
            color: white !important;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.5) !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--primary-purple) 0%, #6A1B9A 100%) !important;
            border-color: var(--primary-purple) !important;
            color: white !important;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.6) !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            background: rgba(34, 34, 51, 0.3) !important;
            border-color: rgba(138, 43, 226, 0.1) !important;
            color: rgba(209, 179, 255, 0.3) !important;
        }
        
        /* Gaming Table Enhancements */
        .gaming-table table {
            background: transparent !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .gaming-table th {
            background: linear-gradient(135deg, var(--primary-purple) 0%, #6A1B9A 100%) !important;
            color: white !important;
            padding: 16px 12px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            border: none !important;
            position: relative;
        }
        
        .gaming-table th:first-child {
            border-top-left-radius: 12px;
        }
        
        .gaming-table th:last-child {
            border-top-right-radius: 12px;
        }
        
        .gaming-table td {
            padding: 12px !important;
            color: var(--text-light) !important;
            border-bottom: 1px solid rgba(138, 43, 226, 0.2) !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            background: rgba(34, 34, 51, 0.6) !important;
            transition: all 0.3s ease !important;
        }
        
        .gaming-table tbody tr:hover td {
            background: rgba(138, 43, 226, 0.1) !important;
            box-shadow: inset 0 0 20px rgba(138, 43, 226, 0.2) !important;
        }
        
        .gaming-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }
        
        .gaming-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }
        
        /* Status Badge Styling */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
            color: white;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.4);
        }
        
        .status-confirmed {
            background: linear-gradient(135deg, #00CED1 0%, #008B8B 100%);
            color: white;
            box-shadow: 0 0 10px rgba(0, 206, 209, 0.4);
        }
        
        .status-completed {
            background: linear-gradient(135deg, #32CD32 0%, #228B22 100%);
            color: white;
            box-shadow: 0 0 10px rgba(50, 205, 50, 0.4);
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            color: white;
            box-shadow: 0 0 10px rgba(220, 20, 60, 0.4);
        }
        
        /* DataTable Search and Info Styling */
        .dataTables_wrapper .dataTables_filter {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 20px;
        }
        
        .dataTables_wrapper .dataTables_info {
            padding-top: 20px;
            color: var(--text-light) !important;
            font-style: italic;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 20px;
        }
        
        /* No data message styling */
        .dataTables_empty {
            text-align: center !important;
            padding: 40px !important;
            color: var(--text-light) !important;
            font-style: italic;
            background: rgba(138, 43, 226, 0.1) !important;
        }
        
        .stat-card {
            background: var(--card-bg);
            border: 2px solid var(--primary-purple);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.4);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(138, 43, 226, 0.6);
        }
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(138, 43, 226, 0.6);
        }
    </style>
</head>
<body class="bg-gaming-pattern min-h-screen">
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <div class="logo-glow mr-4">
                        <img src="../../logo/logo.png" alt="G-Arena Logo" class="h-12 w-auto">
                    </div>
                    <h1 class="text-2xl font-bold neon-text">
                        G-Arena Dashboard
                    </h1>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="text-gaming-light">
                        <i class="fas fa-user-circle mr-2"></i>
                        Welcome, <span class="text-purple-300 font-semibold"><?= htmlspecialchars($user['full_name']) ?></span>
                    </div>
                    <a href="logout.php" class="gaming-btn text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>Exit Arena
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 relative z-10">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card p-6">
                <div class="flex items-center">
                    <div class="p-4 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 mr-4">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gaming-light">Total Sessions</p>
                        <p class="text-3xl font-bold neon-text" id="totalBookings">0</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center">
                    <div class="p-4 rounded-full bg-gradient-to-br from-green-600 to-green-800 mr-4">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gaming-light">Hours Played</p>
                        <p class="text-3xl font-bold neon-text" id="totalHours">0</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center">
                    <div class="p-4 rounded-full bg-gradient-to-br from-purple-600 to-purple-800 mr-4">
                        <i class="fas fa-coins text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gaming-light">Total Spent</p>
                        <p class="text-3xl font-bold neon-text">LKR <span id="totalAmount">0</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="gaming-card mb-8">
            <div class="flex border-b border-purple-600/30">
                <button class="tab-button active border-b-2 border-purple-500 px-6 py-4 text-purple-600 font-semibold transition-all duration-300" data-tab="book">
                    <i class="fas fa-plus-circle mr-2"></i>Book Arena
                </button>
                <button class="tab-button border-b-2 border-transparent px-6 py-4 text-gray-500 hover:text-white transition-all duration-300" data-tab="bookings">
                    <i class="fas fa-list-alt mr-2"></i>My Sessions
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="gaming-card">
            <!-- Book Station Tab -->
            <div id="book" class="tab-content p-8">
                <h2 class="text-2xl font-bold neon-text mb-8">
                    <i class="fas fa-rocket mr-3"></i>Book Your Gaming Session
                </h2>
                
                <form id="bookingForm" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="stationSelect" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-gamepad mr-2"></i>Choose Your Weapon
                            </label>
                            <select id="stationSelect" required class="gaming-input w-full text-white">
                                <option value="">Select your gaming station...</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="bookingDate" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-calendar mr-2"></i>Battle Date
                            </label>
                            <input type="date" id="bookingDate" required min="<?= date('Y-m-d') ?>" 
                                   class="gaming-input w-full text-white">
                        </div>
                        
                        <div>
                            <label for="startTime" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-clock mr-2"></i>Start Time
                            </label>
                            <div class="relative">
                                <input type="text" id="startTime" required readonly
                                       class="gaming-input w-full text-white cursor-pointer" 
                                       placeholder="Select start time...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-clock text-purple-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-purple-300 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Arena operates 9:00 AM to 11:00 PM
                            </p>
                        </div>
                        
                        <div>
                            <label for="endTime" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-clock mr-2"></i>End Time
                            </label>
                            <div class="relative">
                                <input type="text" id="endTime" required readonly
                                       class="gaming-input w-full text-white cursor-pointer" 
                                       placeholder="Select end time...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-clock text-purple-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-purple-300 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Must be after start time
                            </p>
                        </div>
                    </div>
                    
                    <!-- Station Info Card -->
                    <div id="stationInfo" class="hidden cyber-border p-6 rounded-lg">
                        <h3 class="font-bold text-purple-300 mb-4 text-lg">
                            <i class="fas fa-info-circle mr-2"></i>Station Arsenal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gaming-light"><strong>Type:</strong> <span id="stationTypeInfo" class="text-purple-300"></span></p>
                                <p class="text-gaming-light"><strong>Rate:</strong> <span id="stationRateInfo" class="text-green-400"></span>/hour</p>
                            </div>
                            <div>
                                <p class="text-gaming-light"><strong>Description:</strong></p>
                                <p id="stationDescInfo" class="text-purple-300"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Summary -->
                    <div id="bookingSummary" class="hidden gaming-alert border-green-500 bg-green-900/30 p-6 rounded-lg">
                        <h3 class="font-bold text-green-300 mb-4 text-lg">
                            <i class="fas fa-calculator mr-2"></i>Battle Plan Summary
                        </h3>
                        <div class="text-sm space-y-2">
                            <p class="text-gaming-light"><strong>Duration:</strong> <span id="summaryDuration" class="text-purple-300"></span></p>
                            <p class="text-gaming-light"><strong>Total Cost:</strong> <span id="summaryAmount" class="text-2xl font-bold text-green-400 glow-text"></span></p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-sticky-note mr-2"></i>Special Requests (Optional)
                        </label>
                        <textarea id="notes" rows="3" 
                                  class="gaming-input w-full text-white resize-none" 
                                  placeholder="Any special requests or battle strategies..."></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="gaming-btn group">
                            <span class="flex items-center">
                                <i class="fas fa-rocket mr-2 group-hover:animate-bounce"></i>
                                Launch Session
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- My Sessions Tab -->
            <div id="bookings" class="tab-content p-8 hidden">
                <h2 class="text-2xl font-bold neon-text mb-8">
                    <i class="fas fa-history mr-3"></i>My Gaming Sessions
                </h2>
                
                <div class="gaming-table overflow-hidden p-5">
                    <table id="userBookingsTable" class="w-full">
                        <thead >
                            <tr>
                                <th><i class="fas fa-hashtag mr-2"></i>Reference</th>
                                <th><i class="fas fa-gamepad mr-2"></i>Station</th>
                                <th><i class="fas fa-calendar mr-2"></i>Date</th>
                                <th><i class="fas fa-clock mr-2"></i>Time</th>
                                <th><i class="fas fa-hourglass mr-2"></i>Duration</th>
                                <th><i class="fas fa-coins mr-2"></i>Amount</th>
                                <th><i class="fas fa-flag mr-2"></i>Status</th>
                                <th><i class="fas fa-calendar-plus mr-2"></i>Booked On</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Particles for Dashboard -->
    <div class="particles">
        <div class="particle" style="left: 10%; animation-delay: 0s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 2s; animation-duration: 10s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 1s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 3s; animation-duration: 9s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 0.5s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 2.5s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 1.5s; animation-duration: 9s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 3.5s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 0.8s; animation-duration: 8s;"></div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 rounded-2xl shadow-2xl max-w-md w-full border border-purple-500/30 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white">Secure Payment</h3>
                    </div>
                    <button id="closePaymentModal" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Booking Summary -->
                <div class="bg-black/40 rounded-lg p-4 mb-4 border border-purple-500/20">
                    <h4 class="text-purple-300 font-semibold mb-2">
                        <i class="fas fa-gamepad mr-2"></i>Booking Summary
                    </h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between text-gray-300">
                            <span>Station:</span>
                            <span id="paymentStation" class="text-cyan-300"></span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Date:</span>
                            <span id="paymentDate" class="text-cyan-300"></span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Time:</span>
                            <span id="paymentTime" class="text-cyan-300"></span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Duration:</span>
                            <span id="paymentDuration" class="text-cyan-300"></span>
                        </div>
                        <div class="border-t border-purple-500/30 pt-2 mt-2">
                            <div class="flex justify-between font-bold">
                                <span class="text-purple-300">Total Amount:</span>
                                <span id="paymentAmount" class="text-green-400 text-lg"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form id="paymentForm" class="space-y-4">
                    <!-- Card Number -->
                    <div>
                        <label class="block text-purple-300 text-sm font-medium mb-2">
                            <i class="fas fa-credit-card mr-2"></i>Card Number
                        </label>
                        <input type="text" id="cardNumber" maxlength="19" placeholder="1234 5678 9012 3456"
                               class="w-full px-3 py-2 bg-black/50 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20 transition-all">
                    </div>

                    <!-- Card Holder Name -->
                    <div>
                        <label class="block text-purple-300 text-sm font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>Cardholder Name
                        </label>
                        <input type="text" id="cardHolder" placeholder="John Doe"
                               class="w-full px-3 py-2 bg-black/50 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20 transition-all">
                    </div>

                    <!-- Expiry and CVV -->
                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                <i class="fas fa-calendar mr-2"></i>Expiry Date
                            </label>
                            <input type="text" id="expiryDate" maxlength="5" placeholder="MM/YY"
                                   class="w-full px-3 py-2 bg-black/50 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20 transition-all">
                        </div>
                        <div class="flex-1">
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                <i class="fas fa-lock mr-2"></i>CVV
                            </label>
                            <input type="text" id="cvv" maxlength="4" placeholder="123"
                                   class="w-full px-3 py-2 bg-black/50 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20 transition-all">
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="flex items-center bg-green-900/20 border border-green-500/30 rounded-lg p-3">
                        <i class="fas fa-shield-alt text-green-400 mr-3"></i>
                        <div class="text-xs text-green-300">
                            <div class="font-semibold">Secure Payment</div>
                            <div class="text-green-400/80">Your payment information is encrypted and secure</div>
                        </div>
                    </div>

                    <!-- Payment Buttons -->
                    <div class="flex space-x-3 pt-2">
                        <button type="button" id="cancelPayment" 
                                class="flex-1 py-2.5 px-4 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" id="processPayment"
                                class="flex-1 py-2.5 px-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg font-medium transition-all duration-200 transform hover:scale-105">
                            <span class="flex items-center justify-center">
                                <i class="fas fa-rocket mr-2"></i>
                                <span id="paymentButtonText">Process Payment</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/user-dashboard.js"></script>
</body>
</html>
