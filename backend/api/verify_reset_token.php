<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

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
    
    $token = $input['token'] ?? '';
    
    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Token is required']);
        exit;
    }
    
    $conn = getDbConnection();
    
    // Check if token exists and is valid
    $stmt = mysqli_prepare($conn, "
        SELECT prt.*, u.username, u.email 
        FROM password_reset_tokens prt 
        JOIN users u ON prt.user_id = u.id 
        WHERE prt.token = ? 
        AND prt.expires_at > NOW() 
        AND prt.used_at IS NULL
        AND u.status = 'active'
    ");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tokenData = mysqli_fetch_assoc($result);
    
    if (!$tokenData) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid or expired reset token'
        ]);
        exit;
    }
    
    // Token is valid
    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'user_id' => $tokenData['user_id']
    ]);
    
} catch (Exception $e) {
    error_log("Verify reset token error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while verifying the token'
    ]);
}
?>
