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
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    
    if (empty($token) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }
    
    $conn = getDbConnection();
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Verify token is still valid
        $stmt = mysqli_prepare($conn, "
            SELECT prt.user_id, u.username, u.email 
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
            throw new Exception('Invalid or expired reset token');
        }
        
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $tokenData['user_id']);
        mysqli_stmt_execute($stmt);
        
        // Mark token as used
        $stmt = mysqli_prepare($conn, "UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?");
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        
        // Delete all other reset tokens for this user
        $stmt = mysqli_prepare($conn, "DELETE FROM password_reset_tokens WHERE user_id = ? AND token != ?");
        mysqli_stmt_bind_param($stmt, 'is', $tokenData['user_id'], $token);
        mysqli_stmt_execute($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password has been successfully reset'
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
