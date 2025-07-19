<?php
// Enhanced Bookings API with Multi-Station Support
error_reporting(E_ALL);
ini_set('display_errors', 0);

function handleError($errno, $errstr, $errfile, $errline)
{
    $error = [
        'success' => false,
        'message' => 'Internal server error',
        'debug' => [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

set_error_handler('handleError');

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
$connection = getDbConnection();
if (!$connection) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['stats'])) {
            $user = getUserInfo();
            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'You must be logged in to view stats']);
                exit;
            }
            echo json_encode(['success' => true, 'stats' => getUserStats($user['id'])]);
        } elseif (isset($_GET['admin']) && isAdminLoggedIn()) {
            echo json_encode(['success' => true, 'data' => getAllBookingsWithDetails()]);
        } elseif (isset($_GET['user_bookings'])) {
            $user = getUserInfo();
            echo json_encode(['success' => true, 'data' => getUserBookings($user['id'])]);
        } else {
            echo json_encode(['success' => true, 'data' => getAllBookings()]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $user = getUserInfo();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'You must be logged in to make a booking']);
            exit;
        }

        // Check if this is a multi-station booking
        if (isset($input['station_ids']) && is_array($input['station_ids']) && count($input['station_ids']) > 1) {
            // Multi-station booking
            $result = createMultiStationBooking($user['id'], $input);
        } else {
            // Single station booking (legacy support)
            $result = createSingleStationBooking($user['id'], $input);
        }

        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        break;

    case 'PUT':
        if (!isAdminLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $booking_id = $_GET['id'] ?? 0;
        $status = $input['status'] ?? '';

        if (!$booking_id || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID and status are required']);
            exit;
        }

        $result = updateBookingStatus($booking_id, $status);
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

/**
 * Create a multi-station booking (multiple stations for the same time slot)
 */
function createMultiStationBooking($user_id, $input) {
    global $connection;
    
    $station_ids = $input['station_ids'];
    $date = $input['booking_date'] ?? '';
    $start_time = $input['start_time'] ?? '';
    $end_time = $input['end_time'] ?? '';
    $notes = $input['notes'] ?? '';
    
    // Validate stations
    if (empty($station_ids) || !is_array($station_ids)) {
        return ['success' => false, 'message' => 'At least one station is required'];
    }
    
    if (count($station_ids) > 5) {
        return ['success' => false, 'message' => 'Maximum 5 stations allowed per booking'];
    }
    
    // Remove duplicates
    $station_ids = array_unique($station_ids);
    
    // Basic validation
    if (empty($date) || empty($start_time) || empty($end_time)) {
        return ['success' => false, 'message' => 'Date, start time, and end time are required'];
    }
    
    // Validate date is not in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        return ['success' => false, 'message' => 'Cannot book for past dates'];
    }
    
    // Calculate hours for the time slot
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $diff = $end->diff($start);
    $total_hours = $diff->h + ($diff->i / 60);
    
    if ($total_hours <= 0) {
        return ['success' => false, 'message' => 'Invalid time range'];
    }
    
    $total_amount = 0;
    $validated_stations = [];
    $primary_station_id = null;
    
    // Validate each station
    foreach ($station_ids as $index => $station_id) {
        if ($station_id <= 0) {
            return ['success' => false, 'message' => 'Invalid station ID'];
        }
        
        // Validate station exists and is active
        $station = getStationById($station_id);
        if (!$station || $station['status'] !== 'active') {
            return ['success' => false, 'message' => "Station '{$station['station_name']}' is not available for booking"];
        }
        
        // Check for booking conflicts for this station
        if (checkBookingConflict($station_id, $date, $start_time, $end_time)) {
            return ['success' => false, 'message' => "Station '{$station['station_name']}' is already booked for the selected time"];
        }
        
        // Check arena-wide unavailable slots
        if (checkArenaUnavailable($date, $start_time, $end_time)) {
            return ['success' => false, 'message' => 'Arena is unavailable during the selected time'];
        }
        
        $station_amount = $total_hours * $station['hourly_rate'];
        $total_amount += $station_amount;
        
        $validated_stations[] = [
            'station_id' => $station_id,
            'station_name' => $station['station_name'],
            'station_type' => $station['station_type'],
            'hourly_rate' => $station['hourly_rate'],
            'amount' => $station_amount
        ];
        
        // Use first station as primary
        if ($index === 0) {
            $primary_station_id = $station_id;
        }
    }
    
    // Generate booking reference
    $booking_reference = generateBookingReference();
    
    // Begin transaction
    mysqli_autocommit($connection, false);
    
    try {
        // Create main booking record with primary station
        $sql = "INSERT INTO bookings (
                    user_id, station_id, booking_date, start_time, end_time, 
                    total_hours, total_amount, status, booking_reference, notes, 
                    booking_type, station_count
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'multi_station', ?)";
        
        $stmt = mysqli_prepare($connection, $sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare booking query: ' . mysqli_error($connection));
        }
        
        $station_count = count($validated_stations);
        mysqli_stmt_bind_param($stmt, "iisssddssi", 
            $user_id, $primary_station_id, $date, $start_time, $end_time, 
            $total_hours, $total_amount, $booking_reference, $notes, $station_count
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to create booking: ' . mysqli_stmt_error($stmt));
        }
        
        $booking_id = mysqli_insert_id($connection);
        mysqli_stmt_close($stmt);
        
        // Add ALL stations to booking_stations table for multi-station bookings
        if (count($validated_stations) > 0) {
            $station_sql = "INSERT INTO booking_stations (booking_id, station_id) VALUES (?, ?)";
            $station_stmt = mysqli_prepare($connection, $station_sql);
            
            if (!$station_stmt) {
                throw new Exception('Failed to prepare station query: ' . mysqli_error($connection));
            }
            
            // Add ALL stations to booking_stations table (including the primary one)
            foreach ($validated_stations as $station) {
                $station_id = $station['station_id'];
                mysqli_stmt_bind_param($station_stmt, "ii", $booking_id, $station_id);
                
                if (!mysqli_stmt_execute($station_stmt)) {
                    throw new Exception('Failed to add station to booking: ' . mysqli_stmt_error($station_stmt));
                }
            }
            
            mysqli_stmt_close($station_stmt);
        }
        
        // Commit transaction
        mysqli_commit($connection);
        mysqli_autocommit($connection, true);
        
        return [
            'success' => true,
            'message' => 'Multi-station booking created successfully',
            'reference' => $booking_reference,
            'booking_id' => $booking_id,
            'details' => [
                'station_count' => $station_count,
                'total_hours' => $total_hours,
                'total_amount' => $total_amount,
                'date' => $date,
                'time' => "$start_time - $end_time",
                'stations' => $validated_stations
            ]
        ];
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        
        error_log("Multi-station booking error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create booking: ' . $e->getMessage()];
    }
}

/**
 * Create a single station booking (legacy support)
 */
function createSingleStationBooking($user_id, $input) {
    // Extract station_id from either single value or first element of array
    $station_id = 0;
    if (isset($input['station_id'])) {
        $station_id = $input['station_id'];
    } elseif (isset($input['station_ids']) && is_array($input['station_ids']) && !empty($input['station_ids'])) {
        $station_id = $input['station_ids'][0];
    }
    
    $date = $input['booking_date'] ?? '';
    $start_time = $input['start_time'] ?? '';
    $end_time = $input['end_time'] ?? '';
    $notes = $input['notes'] ?? '';
    
    // Input validation
    if ($station_id <= 0 || empty($date) || empty($start_time) || empty($end_time)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
    }
    
    // Validate station exists and is active
    $station = getStationById($station_id);
    if (!$station || $station['status'] !== 'active') {
        return ['success' => false, 'message' => 'Selected station is not available for booking'];
    }
    
    // Validate date is not in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        return ['success' => false, 'message' => 'Cannot book for past dates'];
    }
    
    // Calculate hours and amount
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $diff = $end->diff($start);
    $total_hours = $diff->h + ($diff->i / 60);
    
    if ($total_hours <= 0) {
        return ['success' => false, 'message' => 'Invalid time range'];
    }
    
    $total_amount = $total_hours * $station['hourly_rate'];
    
    // Check for conflicts
    if (checkBookingConflict($station_id, $date, $start_time, $end_time)) {
        return ['success' => false, 'message' => 'Time slot conflicts with existing booking or unavailable period'];
    }
    
    if (checkArenaUnavailable($date, $start_time, $end_time)) {
        return ['success' => false, 'message' => 'Arena is unavailable during selected time'];
    }
    
    // Use existing createBooking function for single stations
    return createBooking($user_id, $station_id, $date, $start_time, $end_time, $total_hours, $total_amount, $notes);
}

/**
 * Check if arena is unavailable during specified time
 */
function checkArenaUnavailable($date, $start_time, $end_time) {
    global $connection;
    
    $query = "SELECT COUNT(*) as count FROM unavailable_slots
              WHERE unavailable_date = ?
              AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        return false; // If we can't check, allow the booking
    }
    
    mysqli_stmt_bind_param($stmt, "sssss", $date, $end_time, $start_time, $start_time, $end_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return ($row['count'] > 0);
}

/**
 * Get user statistics for dashboard
 */
function getUserStats($user_id) {
    $user_id = escapeString($user_id);
    
    // Get total bookings for this user
    $total_bookings_result = fetchSingleRow("SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id'");
    $total_bookings = $total_bookings_result ? (int)$total_bookings_result['count'] : 0;
    
    // Get total hours and amount for this user
    $stats_result = fetchSingleRow("SELECT SUM(total_hours) as total_hours, SUM(total_amount) as total_amount 
                    FROM bookings WHERE user_id = '$user_id' AND status = 'confirmed'");
    
    return [
        'total_bookings' => $total_bookings,
        'total_hours' => $stats_result ? (float)($stats_result['total_hours'] ?? 0) : 0,
        'total_amount' => $stats_result ? (float)($stats_result['total_amount'] ?? 0) : 0
    ];
}

?>
