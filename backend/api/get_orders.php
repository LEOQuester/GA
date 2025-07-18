<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'gaming_arena_v2';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Get JSON input
    $json_input = file_get_contents('php://input');
    $request_data = json_decode($json_input, true);

    // Validate input data
    if (!$request_data) {
        throw new Exception('Invalid JSON data');
    }

    // Get parameters
    $report_type = isset($request_data['reportType']) ? $request_data['reportType'] : 'all';
    $date_from = isset($request_data['dateFrom']) ? $request_data['dateFrom'] : null;
    $date_to = isset($request_data['dateTo']) ? $request_data['dateTo'] : null;

    // Build SQL query based on report type
    $sql = "SELECT receipt_number, floor_number, room_number, food_items, total_amount, 
                   card_holder_name, order_date
            FROM food";

    $where_conditions = [];
    $params = [];

    // Add date filtering based on report type
    switch ($report_type) {
        case 'daily':
            if ($date_from) {
                $where_conditions[] = "DATE(order_date) = :date_from";
                $params[':date_from'] = $date_from;
            } else {
                $where_conditions[] = "DATE(order_date) = CURDATE()";
            }
            break;

        case 'weekly':
            if ($date_from && $date_to) {
                $where_conditions[] = "DATE(order_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $date_from;
                $params[':date_to'] = $date_to;
            } else {
                $where_conditions[] = "order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            }
            break;

        case 'monthly':
            if ($date_from && $date_to) {
                $where_conditions[] = "DATE(order_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $date_from;
                $params[':date_to'] = $date_to;
            } else {
                $where_conditions[] = "MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
            }
            break;

        case 'all':
            if ($date_from && $date_to) {
                $where_conditions[] = "DATE(order_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $date_from;
                $params[':date_to'] = $date_to;
            }
            break;
    }

    // Add WHERE clause if conditions exist
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // Add ORDER BY clause
    $sql .= " ORDER BY order_date DESC";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch all results
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add status field (assuming all orders in database are successful)
    foreach ($orders as &$order) {
        $order['status'] = 'success';
    }

    // Calculate summary statistics
    $total_orders = count($orders);
    $total_revenue = array_sum(array_column($orders, 'total_amount'));
    $successful_orders = $total_orders; // Since all in DB are successful
    $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

    // Return success response
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'summary' => [
            'total_orders' => $total_orders,
            'total_revenue' => $total_revenue,
            'successful_orders' => $successful_orders,
            'avg_order_value' => $avg_order_value
        ],
        'report_type' => $report_type,
        'date_range' => [
            'from' => $date_from,
            'to' => $date_to
        ]
    ]);
} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);

    // Log error for debugging
    error_log("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    // General error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

    // Log error for debugging
    error_log("Admin Panel Error: " . $e->getMessage());
}

// Close database connection
$pdo = null;
