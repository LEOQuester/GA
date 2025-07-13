<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

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
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }
        
        $id = $input['id'] ?? 0;
        $name = $input['station_name'] ?? '';
        $type = $input['station_type'] ?? '';
        $description = $input['description'] ?? '';
        $rate = $input['hourly_rate'] ?? 0;
        $status = $input['status'] ?? 'active';
        
        if ($id <= 0 || empty($name) || empty($type) || $rate <= 0) {
            echo json_encode(['success' => false, 'message' => 'All fields are required and rate must be positive']);
            exit;
        }
        
        $result = updateStation($id, $name, $type, $description, $rate, $status);
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
?>
