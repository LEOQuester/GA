<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$username = $input['username'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Escape inputs to prevent SQL injection
$username = escapeString($username);
$email = escapeString($email);

// Check if admin already exists
$sql = "SELECT id FROM admins WHERE username = '$username' OR email = '$email'";
$existing = fetchSingleRow($sql);

if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Admin username or email already exists']);
    exit;
}

// Hash the password properly
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$hashedPassword = escapeString($hashedPassword);

// Insert new admin
$sql = "INSERT INTO admins (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";

$result = executeQuery($sql);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Admin account created successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create admin account']);
}
?>
