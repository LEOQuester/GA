<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

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
$confirmPassword = $input['confirmPassword'] ?? '';
$full_name = $input['full_name'] ?? '';
$phone = $input['phone'] ?? '';

if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($full_name)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Validate username - only letters
if (!preg_match('/^[a-zA-Z]+$/', $username)) {
    echo json_encode(['success' => false, 'message' => 'Username must contain only letters (no numbers or special characters)']);
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

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

$result = userRegister($username, $email, $password, $full_name, $phone);
echo json_encode($result);
?>
