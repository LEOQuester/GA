<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Force output debugging
ob_start();

$method = $_SERVER['REQUEST_METHOD'];

// Debug log to console
error_log("stations.php called with method: " . $method);

// Allow both users and admins to GET stations data
if ($method === 'GET') {
    if (!isUserLoggedIn() && !isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
} else {
    // Only admins can modify stations
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

switch ($method) {
    case 'GET':
        echo json_encode(['success' => true, 'data' => getAllStations()]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $name = $input['station_name'] ?? '';
        $type = $input['station_type'] ?? '';
        $description = $input['description'] ?? '';
        $rate = $input['hourly_rate'] ?? 0;

        if (empty($name) || empty($type) || $rate <= 0) {
            echo json_encode(['success' => false, 'message' => 'All fields are required and rate must be positive']);
            exit;
        }

        $result = createStation($name, $type, $description, $rate);
        echo json_encode($result);
        break;

    case 'PUT':
    case 'PATCH':
        $raw_input = file_get_contents('php://input');
        $input = json_decode($raw_input, true);

        // Prepare debug info to send to frontend
        $debug_info = [
            'raw_input' => $raw_input,
            'decoded_input' => $input,
            'json_decode_error' => json_last_error_msg()
        ];

        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid JSON',
                'debug' => $debug_info
            ]);
            exit;
        }

        $id = $input['id'] ?? 0;
        $name = $input['station_name'] ?? '';
        $type = $input['station_type'] ?? '';
        $description = $input['description'] ?? '';
        $rate = $input['hourly_rate'] ?? 0;
        $status = $input['status'] ?? 'active';

        // Add parsed values to debug info
        $debug_info['parsed_values'] = [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'description' => $description,
            'rate' => $rate,
            'status' => $status
        ];

        // More relaxed validation - only check if ID exists
        $debug_info['validation_checks'] = [
            'id_provided' => isset($input['id']),
            'id_value' => $id,
            'id_type' => gettype($id),
            'id_greater_than_0' => $id > 0,
            'name_value' => $name,
            'type_value' => $type,
            'rate_value' => $rate,
            'rate_type' => gettype($rate)
        ];

        // Only require ID to be valid - everything else is optional for PATCH
        if (!isset($input['id'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Valid station ID is required for update',
                'debug' => $debug_info
            ]);
            exit;
        }

        // Add success debug info
        $debug_info['validation_passed'] = true;
        $debug_info['calling_updateStation'] = true;

        $result = updateStation($id, $name, $type, $description, $rate, $status);
        
        // Add function result to debug
        $debug_info['updateStation_result'] = $result;
        
        // Always include debug info in response
        $result['debug'] = $debug_info;
        echo json_encode($result);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $id = $input['id'] ?? 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid station ID is required']);
            exit;
        }

        $result = deleteStation($id);
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
