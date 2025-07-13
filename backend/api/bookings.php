<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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
        
        $user = getUserInfo();
        $station_id = $input['station_id'] ?? 0;
        $date = $input['booking_date'] ?? '';
        $start_time = $input['start_time'] ?? '';
        $end_time = $input['end_time'] ?? '';
        $notes = $input['notes'] ?? '';
        // Arena-wide unavailable slot check
        $check_arena_unavailable = "SELECT COUNT(*) as count FROM unavailable_slots
            WHERE unavailable_date = ? 
            AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
        $stmt = mysqli_prepare($connection, $check_arena_unavailable);
        mysqli_stmt_bind_param($stmt, "sssss", $date, $end_time, $start_time, $start_time, $end_time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if ($row['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'The arena is unavailable during the selected time. Please choose another slot.'
            ]);
            exit;
        }
        
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
        if (!isAdminLoggedIn()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }
        
        $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$booking_id || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID and status required']);
            exit;
        }
        
        $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($input['status'], $allowed_statuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        $result = updateBookingStatus($booking_id, $input['status']);
        echo json_encode($result);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
