<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check authentication
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Initialize database connection
$connection = getDbConnection();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get unavailable slots for a specific station and date
        $station_id = isset($_GET['station_id']) ? (int)$_GET['station_id'] : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : '';
        
        if (!$station_id || !$date) {
            echo json_encode([
                'success' => false,
                'message' => 'Station ID and date are required'
            ]);
            exit;
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD'
            ]);
            exit;
        }
        
        // Get arena-wide unavailable slots for the date (affects all stations)
        $query = "SELECT start_time, end_time, reason 
                 FROM unavailable_slots 
                 WHERE unavailable_date = ?
                 ORDER BY start_time";
        
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $unavailable_slots = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['reason'] = 'Arena ' . $row['reason']; // Prefix to indicate arena-wide
            $unavailable_slots[] = $row;
        }
        
        // Also check existing bookings for the same station and date
        $booking_query = "SELECT start_time, end_time 
                         FROM bookings 
                         WHERE station_id = ? AND booking_date = ? 
                         AND status IN ('pending', 'confirmed')
                         ORDER BY start_time";
        
        $booking_stmt = mysqli_prepare($connection, $booking_query);
        mysqli_stmt_bind_param($booking_stmt, "is", $station_id, $date);
        mysqli_stmt_execute($booking_stmt);
        $booking_result = mysqli_stmt_get_result($booking_stmt);
        
        while ($row = mysqli_fetch_assoc($booking_result)) {
            $unavailable_slots[] = [
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'reason' => 'Already booked'
            ];
        }
        
        // Sort by start time
        usort($unavailable_slots, function($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });
        
        echo json_encode([
            'success' => true,
            'unavailable_slots' => $unavailable_slots,
            'business_hours' => [
                'start' => '09:00:00',
                'end' => '20:00:00'
            ]
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Availability API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
