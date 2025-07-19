<?php
// Simple test for email functionality
require_once 'config/database.php';
require_once 'includes/email_service.php';

echo "Testing email service...\n\n";

try {
    // Test 1: Check if EmailService can be instantiated
    echo "1. Creating EmailService instance...\n";
    $emailService = new EmailService();
    echo "✅ EmailService created successfully\n\n";
    
    // Test 2: Check database connection
    echo "2. Testing database connection...\n";
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful\n\n";
    
    // Test 3: Check if user exists
    echo "3. Looking for users in database...\n";
    $stmt = $db->prepare("SELECT id, username, email, full_name FROM users WHERE status = 'active' LIMIT 5");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "✅ Found " . count($users) . " active users:\n";
        foreach ($users as $user) {
            echo "   - ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
        }
    } else {
        echo "❌ No active users found\n";
    }
    echo "\n";
    
    // Test 4: Try to send a test email (if user provides email)
    if (isset($_GET['test_email'])) {
        $testEmail = $_GET['test_email'];
        echo "4. Testing email sending to: $testEmail\n";
        
        $emailData = [
            'user_name' => 'Test User',
            'reset_link' => 'http://localhost/Gaming-Arena/frontend/reset_password.php?token=test123',
            'expiry_time' => '1 hour'
        ];
        
        $result = $emailService->sendPasswordResetEmail($testEmail, 'Test Password Reset', $emailData);
        
        if ($result['success']) {
            echo "✅ Test email sent successfully!\n";
        } else {
            echo "❌ Failed to send test email: " . $result['message'] . "\n";
        }
    } else {
        echo "4. To test email sending, add ?test_email=your@email.com to the URL\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
