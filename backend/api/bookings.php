<?php
// Prevent PHP errors from being output as HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Function to handle errors and return JSON
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

    // Log the error for server-side debugging
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");

    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

// Set custom error handler
set_error_handler('handleError');

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Ensure we have a database connection
$connection = getDbConnection();

// if (!isUserLoggedIn()) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['admin']) && isAdminLoggedIn()) {
            // Admin view - get all bookings with user details
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

        // Check if user is logged in and get user info
        $user = getUserInfo();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'You must be logged in to make a booking']);
            exit;
        }

        // Get booking details from input
        $station_id = $input['station_id'] ?? 0;
        $date = $input['booking_date'] ?? '';
        $start_time = $input['start_time'] ?? '';
        $end_time = $input['end_time'] ?? '';
        $notes = $input['notes'] ?? '';

        // Input validation with specific error messages
        if ($station_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid station selected']);
            exit;
        }

        if (empty($date)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking date is required']);
            exit;
        }

        if (empty($start_time) || empty($end_time)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Both start time and end time are required']);
            exit;
        }

        // Validate station exists and is active
        $station = getStationById($station_id);
        if (!$station) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Selected station does not exist']);
            exit;
        }
        if ($station['status'] !== 'active') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Selected station is currently not available for booking']);
            exit;
        }

        // Arena-wide unavailable slot check
        $check_arena_unavailable = "SELECT COUNT(*) as count FROM unavailable_slots
            WHERE unavailable_date = ? 
            AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
        // Check if connection is valid
        if (!$connection) {
            error_log("Database connection error in bookings.php: Connection is null");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
            exit;
        }

        $stmt = mysqli_prepare($connection, $check_arena_unavailable);
        if (!$stmt) {
            error_log("Database prepare error in bookings.php: " . mysqli_error($connection));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error preparing database query']);
            exit;
        }

        mysqli_stmt_bind_param($stmt, "sssss", $date, $end_time, $start_time, $start_time, $end_time);

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Database error in bookings.php: " . mysqli_error($connection));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'A database error occurred while checking availability']);
            mysqli_stmt_close($stmt);
            exit;
        }

        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Database result error in bookings.php: " . mysqli_error($connection));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error retrieving results from database']);
            mysqli_stmt_close($stmt);
            exit;
        }

        $row = mysqli_fetch_assoc($result);
        if (!$row) {
            error_log("Database fetch error in bookings.php: " . mysqli_error($connection));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error retrieving data from database']);
            mysqli_stmt_close($stmt);
            exit;
        }

        if ($row['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'The arena is unavailable during the selected time. Please choose another slot.'
            ]);
            mysqli_stmt_close($stmt);
            exit;
        }

        // Clean up
        mysqli_stmt_close($stmt);

        if ($station_id <= 0 || empty($date) || empty($start_time) || empty($end_time)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            exit;
        }

        // Validate date is not in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            echo json_encode(['success' => false, 'message' => 'Cannot book for past dates']);
            exit;
        }

        // Calculate hours and amount
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        $diff = $end->diff($start);
        $total_hours = $diff->h + ($diff->i / 60);

        if ($total_hours <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid time range']);
            exit;
        }

        // Get station rate
        $station = getStationById($station_id);
        if (!$station) {
            echo json_encode(['success' => false, 'message' => 'Station not found']);
            exit;
        }

        $total_amount = $total_hours * $station['hourly_rate'];

        $result = createBooking($user['id'], $station_id, $date, $start_time, $end_time, $total_hours, $total_amount, $notes);
        if (!$result || (isset($result['success']) && !$result['success'])) {
            $errorMsg = isset($result['message']) ? $result['message'] : 'Unknown error occurred while creating booking.';
            error_log('Booking API Error: ' . $errorMsg);
            echo json_encode([
                'success' => false,
                'message' => $errorMsg
            ]);
        } else {
            echo json_encode($result);
        }
        break;

    case 'PUT':
        // Admin only - update booking status
        error_log("G-Arena Debug: PUT request received for booking status update");
        
        if (!isAdminLoggedIn()) {
            error_log("G-Arena Debug: Admin not logged in");
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }

        $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $input = json_decode(file_get_contents('php://input'), true);
        
        error_log("G-Arena Debug: Booking ID: {$booking_id}, Input: " . json_encode($input));

        if (!$booking_id || !isset($input['status'])) {
            error_log("G-Arena Debug: Missing booking ID or status");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID and status required']);
            exit;
        }

        $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($input['status'], $allowed_statuses)) {
            error_log("G-Arena Debug: Invalid status: " . $input['status']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        error_log("G-Arena Debug: About to call updateBookingStatus with ID {$booking_id} and status {$input['status']}");
        $result = updateBookingStatus($booking_id, $input['status']);
        error_log("G-Arena Debug: updateBookingStatus result: " . json_encode($result));
        
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
