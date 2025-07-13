<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check admin authentication
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Admin authentication required'
    ]);
    exit;
}

// Initialize database connection
$connection = getDbConnection();

try {
    // Get overall metrics
    $metrics = getOverallMetrics();
    
    // Get chart data
    $charts = [
        'monthly_revenue' => getMonthlyRevenue(),
        'station_bookings' => getStationBookings(),
        'daily_bookings' => getDailyBookings(),
        'peak_hours' => getPeakHours()
    ];
    
    // Get top stations
    $top_stations = getTopStations();
    
    // Get recent bookings
    $recent_bookings = getRecentBookings();
    
    echo json_encode([
        'success' => true,
        'metrics' => $metrics,
        'charts' => $charts,
        'top_stations' => $top_stations,
        'recent_bookings' => $recent_bookings
    ]);
    
} catch (Exception $e) {
    error_log("Analytics API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

// Get overall metrics
function getOverallMetrics() {
    global $connection;
    
    // Total revenue
    $revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM bookings WHERE status IN ('confirmed', 'completed')";
    $revenue_result = mysqli_query($connection, $revenue_query);
    $revenue_row = mysqli_fetch_assoc($revenue_result);
    
    // Total bookings
    $bookings_query = "SELECT COUNT(*) as total_bookings FROM bookings";
    $bookings_result = mysqli_query($connection, $bookings_query);
    $bookings_row = mysqli_fetch_assoc($bookings_result);
    
    // Total hours
    $hours_query = "SELECT COALESCE(SUM(total_hours), 0) as total_hours FROM bookings WHERE status IN ('confirmed', 'completed')";
    $hours_result = mysqli_query($connection, $hours_query);
    $hours_row = mysqli_fetch_assoc($hours_result);
    
    // Average session duration
    $avg_query = "SELECT COALESCE(AVG(total_hours), 0) as avg_session FROM bookings WHERE status IN ('confirmed', 'completed')";
    $avg_result = mysqli_query($connection, $avg_query);
    $avg_row = mysqli_fetch_assoc($avg_result);
    
    return [
        'total_revenue' => $revenue_row['total_revenue'],
        'total_bookings' => $bookings_row['total_bookings'],
        'total_hours' => $hours_row['total_hours'],
        'avg_session' => $avg_row['avg_session']
    ];
}

// Get monthly revenue for the last 6 months
function getMonthlyRevenue() {
    global $connection;
    
    $query = "SELECT 
                DATE_FORMAT(booking_date, '%Y-%m') as month,
                COALESCE(SUM(total_amount), 0) as revenue
              FROM bookings 
              WHERE status IN ('confirmed', 'completed') 
                AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
              ORDER BY month";
    
    $result = mysqli_query($connection, $query);
    
    $labels = [];
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = date('M Y', strtotime($row['month'] . '-01'));
        $data[] = floatval($row['revenue']);
    }
    
    // Fill in missing months with 0
    $months = [];
    $revenues = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('M Y', strtotime("-$i months"));
        $months[] = $month;
        
        $index = array_search($month, $labels);
        $revenues[] = $index !== false ? $data[$index] : 0;
    }
    
    return [
        'labels' => $months,
        'data' => $revenues
    ];
}

// Get bookings by station
function getStationBookings() {
    global $connection;
    
    $query = "SELECT 
                gs.station_name,
                COUNT(b.id) as booking_count
              FROM gaming_stations gs
              LEFT JOIN bookings b ON gs.id = b.station_id
              WHERE gs.status = 'active'
              GROUP BY gs.id, gs.station_name
              ORDER BY booking_count DESC";
    
    $result = mysqli_query($connection, $query);
    
    $labels = [];
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['station_name'];
        $data[] = intval($row['booking_count']);
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

// Get daily bookings for the last 30 days
function getDailyBookings() {
    global $connection;
    
    $query = "SELECT 
                booking_date,
                COUNT(*) as booking_count
              FROM bookings 
              WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              GROUP BY booking_date
              ORDER BY booking_date";
    
    $result = mysqli_query($connection, $query);
    
    $labels = [];
    $data = [];
    
    // Create array for last 30 days
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('M j', strtotime($date));
        $data[] = 0;
    }
    
    // Fill in actual data
    while ($row = mysqli_fetch_assoc($result)) {
        $date_label = date('M j', strtotime($row['booking_date']));
        $index = array_search($date_label, $labels);
        if ($index !== false) {
            $data[$index] = intval($row['booking_count']);
        }
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

// Get peak hours analysis
function getPeakHours() {
    global $connection;
    
    $query = "SELECT 
                HOUR(start_time) as hour,
                COUNT(*) as booking_count
              FROM bookings 
              WHERE status IN ('confirmed', 'completed')
              GROUP BY HOUR(start_time)
              ORDER BY hour";
    
    $result = mysqli_query($connection, $query);
    
    // Initialize data for business hours (9 AM to 7 PM)
    $data = array_fill(0, 11, 0); // 11 hours from 9 AM to 7 PM
    
    while ($row = mysqli_fetch_assoc($result)) {
        $hour = intval($row['hour']);
        if ($hour >= 9 && $hour <= 19) {
            $data[$hour - 9] = intval($row['booking_count']);
        }
    }
    
    return [
        'data' => $data
    ];
}

// Get top performing stations
function getTopStations() {
    global $connection;
    
    $query = "SELECT 
                gs.station_name,
                gs.station_type,
                COUNT(b.id) as bookings,
                COALESCE(SUM(b.total_amount), 0) as revenue
              FROM gaming_stations gs
              LEFT JOIN bookings b ON gs.id = b.station_id AND b.status IN ('confirmed', 'completed')
              WHERE gs.status = 'active'
              GROUP BY gs.id, gs.station_name, gs.station_type
              ORDER BY revenue DESC, bookings DESC
              LIMIT 5";
    
    $result = mysqli_query($connection, $query);
    
    $stations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stations[] = [
            'station_name' => $row['station_name'],
            'station_type' => $row['station_type'],
            'bookings' => intval($row['bookings']),
            'revenue' => floatval($row['revenue'])
        ];
    }
    
    return $stations;
}

// Get recent bookings
function getRecentBookings() {
    global $connection;
    
    $query = "SELECT 
                b.booking_date,
                b.start_time,
                b.end_time,
                b.total_amount,
                b.status,
                gs.station_name
              FROM bookings b
              JOIN gaming_stations gs ON b.station_id = gs.id
              ORDER BY b.created_at DESC
              LIMIT 10";
    
    $result = mysqli_query($connection, $query);
    
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = [
            'booking_date' => $row['booking_date'],
            'start_time' => date('g:i A', strtotime($row['start_time'])),
            'end_time' => date('g:i A', strtotime($row['end_time'])),
            'total_amount' => floatval($row['total_amount']),
            'status' => $row['status'],
            'station_name' => $row['station_name']
        ];
    }
    
    return $bookings;
}
?>
