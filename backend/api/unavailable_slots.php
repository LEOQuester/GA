<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all arena-wide unavailable slots
            $query = "SELECT * FROM unavailable_slots 
                     ORDER BY unavailable_date ASC, start_time ASC";
            
            $result = mysqli_query($connection, $query);
            
            if (!$result) {
                throw new Exception('Database query failed: ' . mysqli_error($connection));
            }
            
            $slots = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $slots[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $slots
            ]);
            break;
            
        case 'POST':
            // Create new arena-wide unavailable slot
            $input = json_decode(file_get_contents('php://input'), true);
            
            $unavailable_date = isset($input['unavailable_date']) ? $input['unavailable_date'] : '';
            $start_time = isset($input['start_time']) ? $input['start_time'] : '';
            $end_time = isset($input['end_time']) ? $input['end_time'] : '';
            $reason = isset($input['reason']) ? $input['reason'] : 'Arena maintenance';
            
            // Validation
            if (!$unavailable_date || !$start_time || !$end_time) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All fields are required'
                ]);
                exit;
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $unavailable_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid date format'
                ]);
                exit;
            }
            
            // Validate time format
            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $start_time) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $end_time)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid time format'
                ]);
                exit;
            }
            
            // Check for overlapping slots
            $overlap_query = "SELECT COUNT(*) as count FROM unavailable_slots 
                             WHERE unavailable_date = ? 
                             AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
            
            $overlap_stmt = mysqli_prepare($connection, $overlap_query);
            mysqli_stmt_bind_param($overlap_stmt, "sssss", $unavailable_date, $end_time, $start_time, $start_time, $end_time);
            mysqli_stmt_execute($overlap_stmt);
            $overlap_result = mysqli_stmt_get_result($overlap_stmt);
            $overlap_row = mysqli_fetch_assoc($overlap_result);
            
            if ($overlap_row['count'] > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This time slot overlaps with an existing unavailable slot'
                ]);
                exit;
            }
            
            // Insert new slot
            $insert_query = "INSERT INTO unavailable_slots (unavailable_date, start_time, end_time, reason) 
                            VALUES (?, ?, ?, ?)";
            
            $insert_stmt = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $unavailable_date, $start_time, $end_time, $reason);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Arena unavailable slot created successfully'
                ]);
            } else {
                throw new Exception('Failed to create unavailable slot');
            }
            break;
            
        case 'PUT':
            // Update arena-wide unavailable slot
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Slot ID is required'
                ]);
                exit;
            }
            
            $unavailable_date = isset($input['unavailable_date']) ? $input['unavailable_date'] : '';
            $start_time = isset($input['start_time']) ? $input['start_time'] : '';
            $end_time = isset($input['end_time']) ? $input['end_time'] : '';
            $reason = isset($input['reason']) ? $input['reason'] : 'Arena maintenance';
            
            // Check for overlapping slots (excluding current slot)
            $overlap_query = "SELECT COUNT(*) as count FROM unavailable_slots 
                             WHERE unavailable_date = ? AND id != ?
                             AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
            
            $overlap_stmt = mysqli_prepare($connection, $overlap_query);
            mysqli_stmt_bind_param($overlap_stmt, "sissss", $unavailable_date, $id, $end_time, $start_time, $start_time, $end_time);
            mysqli_stmt_execute($overlap_stmt);
            $overlap_result = mysqli_stmt_get_result($overlap_stmt);
            $overlap_row = mysqli_fetch_assoc($overlap_result);
            
            if ($overlap_row['count'] > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This time slot overlaps with an existing unavailable slot'
                ]);
                exit;
            }
            
            // Update slot
            $update_query = "UPDATE unavailable_slots 
                            SET unavailable_date = ?, start_time = ?, end_time = ?, reason = ? 
                            WHERE id = ?";
            
            $update_stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ssssi", $unavailable_date, $start_time, $end_time, $reason, $id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Arena unavailable slot updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update unavailable slot');
            }
            break;
            
        case 'DELETE':
            // Delete unavailable slot
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Slot ID is required'
                ]);
                exit;
            }
            
            $delete_query = "DELETE FROM unavailable_slots WHERE id = ?";
            $delete_stmt = mysqli_prepare($connection, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "i", $id);
            
            if (mysqli_stmt_execute($delete_stmt)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Arena unavailable slot deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete unavailable slot');
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Unavailable Slots API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
