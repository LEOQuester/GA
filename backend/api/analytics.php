<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Only allow authenticated admin users
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $metrics = getOverallMetrics();
        echo json_encode(['success' => true, 'metrics' => $metrics]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching analytics: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

/**
 * Get overall dashboard metrics
 */
function getOverallMetrics() {
    // Get total stations
    $stations_result = fetchSingleRow("SELECT COUNT(*) as count FROM gaming_stations");
    $total_stations = $stations_result ? (int)$stations_result['count'] : 0;
    
    // Get total bookings
    $bookings_result = fetchSingleRow("SELECT COUNT(*) as count FROM bookings");
    $total_bookings = $bookings_result ? (int)$bookings_result['count'] : 0;
    
    // Get pending bookings
    $pending_result = fetchSingleRow("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $pending_bookings = $pending_result ? (int)$pending_result['count'] : 0;
    
    // Get today's revenue
    $today = date('Y-m-d');
    $revenue_result = fetchSingleRow("SELECT SUM(total_amount) as revenue FROM bookings WHERE DATE(created_at) = '$today' AND status = 'confirmed'");
    $today_revenue = $revenue_result ? (float)($revenue_result['revenue'] ?? 0) : 0;
    
    return [
        'total_stations' => $total_stations,
        'total_bookings' => $total_bookings,
        'pending_bookings' => $pending_bookings,
        'today_revenue' => number_format($today_revenue, 2)
    ];
}
?>
