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
    $order_data = json_decode($json_input, true);

    // Validate input data
    if (!$order_data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['floor', 'room', 'items', 'total', 'cardNumber', 'cardHolder', 'expiry', 'cvv'];
    foreach ($required_fields as $field) {
        if (!isset($order_data[$field]) || empty($order_data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate that items array is not empty
    if (!is_array($order_data['items']) || empty($order_data['items'])) {
        throw new Exception('Cart is empty');
    }

    // Process food items - convert to JSON string with item details
    $food_items_array = [];
    foreach ($order_data['items'] as $item) {
        $food_items_array[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['price'] * $item['quantity']
        ];
    }
    $food_items_json = json_encode($food_items_array);

    // Sanitize and validate data
    $floor_number = filter_var($order_data['floor'], FILTER_SANITIZE_STRING);
    $room_number = filter_var($order_data['room'], FILTER_SANITIZE_STRING);
    $total_amount = filter_var($order_data['total'], FILTER_VALIDATE_FLOAT);
    $card_number = filter_var($order_data['cardNumber'], FILTER_SANITIZE_STRING);
    $card_holder_name = filter_var($order_data['cardHolder'], FILTER_SANITIZE_STRING);
    $expiry_date = filter_var($order_data['expiry'], FILTER_SANITIZE_STRING);
    $cvv = filter_var($order_data['cvv'], FILTER_SANITIZE_STRING);

    // Validate numeric fields
    if ($total_amount === false || $total_amount <= 0) {
        throw new Exception('Invalid total amount');
    }

    // Validate card number (remove spaces and check if numeric)
    $card_number_clean = str_replace(' ', '', $card_number);
    if (!is_numeric($card_number_clean) || strlen($card_number_clean) < 13 || strlen($card_number_clean) > 19) {
        throw new Exception('Invalid card number');
    }

    // Validate expiry date format (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry_date)) {
        throw new Exception('Invalid expiry date format');
    }

    // Validate CVV
    if (!is_numeric($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
        throw new Exception('Invalid CVV');
    }

    // For security, we'll mask the card number (keep only last 4 digits)
    $masked_card_number = '**** **** **** ' . substr($card_number_clean, -4);

    // Generate unique receipt number
    $receipt_number = 'RCP' . date('Ymd') . rand(1000, 9999);

    // Ensure receipt number is unique
    $check_sql = "SELECT COUNT(*) FROM food WHERE receipt_number = :receipt_number";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':receipt_number', $receipt_number);
    $check_stmt->execute();

    // If receipt number exists, generate a new one
    while ($check_stmt->fetchColumn() > 0) {
        $receipt_number = 'RCP' . date('Ymd') . rand(1000, 9999);
        $check_stmt->bindParam(':receipt_number', $receipt_number);
        $check_stmt->execute();
    }

    // Prepare SQL statement
    $sql = "INSERT INTO food (receipt_number, floor_number, room_number, food_items, total_amount, card_number, card_holder_name, expiry_date, cvv, order_date) 
            VALUES (:receipt_number, :floor_number, :room_number, :food_items, :total_amount, :card_number, :card_holder_name, :expiry_date, :cvv, NOW())";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':receipt_number', $receipt_number);
    $stmt->bindParam(':floor_number', $floor_number);
    $stmt->bindParam(':room_number', $room_number);
    $stmt->bindParam(':food_items', $food_items_json);
    $stmt->bindParam(':total_amount', $total_amount);
    $stmt->bindParam(':card_number', $masked_card_number);
    $stmt->bindParam(':card_holder_name', $card_holder_name);
    $stmt->bindParam(':expiry_date', $expiry_date);
    $stmt->bindParam(':cvv', $cvv); // In production, you should hash or not store CVV

    // Execute the statement
    if ($stmt->execute()) {
        // Return success response with the generated receipt number
        echo json_encode([
            'success' => true,
            'message' => 'Order processed successfully',
            'receiptNumber' => $receipt_number,
            'orderDetails' => [
                'floor' => $floor_number,
                'room' => $room_number,
                'items' => $food_items_array,
                'total' => $total_amount,
                'orderDate' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to save order to database');
    }
} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);

    // Log error for debugging (in production, use proper logging)
    error_log("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    // General error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

    // Log error for debugging
    error_log("Order Processing Error: " . $e->getMessage());
}

// Close database connection
$pdo = null;
