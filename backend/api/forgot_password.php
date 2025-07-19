<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/email_service.php';

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }
    
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
        exit;
    }
    
    $conn = getDbConnection();
    
    // Check if user exists with this email
    $stmt = mysqli_prepare($conn, "SELECT id, username, full_name FROM users WHERE email = ? AND status = 'active'");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        // Don't reveal whether email exists or not for security
        echo json_encode([
            'success' => true, 
            'message' => 'If this email is registered, you will receive a password reset link shortly.'
        ]);
        exit;
    }
    
    // Generate password reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
    // Store the token in database
    $stmt = mysqli_prepare($conn, "INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()");
    mysqli_stmt_bind_param($stmt, 'iss', $user['id'], $token, $expires);
    mysqli_stmt_execute($stmt);
    
    // Send reset email
    $emailService = new EmailService();
    $resetLink = "http://localhost/Gaming-Arena/frontend/reset_password.php?token=" . $token;
    
    $subject = "🎮 Gaming Arena - Password Reset Request";
    $emailData = [
        'user_name' => $user['full_name'] ?: $user['username'],
        'reset_link' => $resetLink,
        'expiry_time' => '1 hour'
    ];
    
    $emailSent = $emailService->sendPasswordResetEmail($email, $subject, $emailData);
    
    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Password reset link has been sent to your email address. Please check your inbox.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send reset email. Please try again later.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>
