<?php
/**
 * Comprehensive Email Debug Tool for G-Arena
 * This will help identify exactly why emails are not being sent
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 G-Arena Email Comprehensive Debug</h1>";

// Step 1: Check email configuration
echo "<h2>1. Email Configuration Check</h2>";
require_once 'config/email_config.php';

echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
echo "<tr style='background: #f0f0f0;'><th>Setting</th><th>Value</th><th>Status</th></tr>";

$config_checks = [
    'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED',
    'SMTP_PORT' => defined('SMTP_PORT') ? SMTP_PORT : 'NOT DEFINED',
    'SMTP_USERNAME' => defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT DEFINED',
    'SMTP_PASSWORD' => defined('SMTP_PASSWORD') ? (SMTP_PASSWORD ? '***SET***' : 'EMPTY') : 'NOT DEFINED',
    'FROM_EMAIL' => defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT DEFINED',
    'FROM_NAME' => defined('FROM_NAME') ? FROM_NAME : 'NOT DEFINED',
    'EMAIL_DEBUG' => defined('EMAIL_DEBUG') ? (EMAIL_DEBUG ? 'TRUE' : 'FALSE') : 'NOT DEFINED',
    'EMAIL_LOG_ENABLED' => defined('EMAIL_LOG_ENABLED') ? (EMAIL_LOG_ENABLED ? 'TRUE' : 'FALSE') : 'NOT DEFINED'
];

foreach ($config_checks as $key => $value) {
    $status = ($value === 'NOT DEFINED' || $value === 'EMPTY') ? '❌' : '✅';
    $color = ($value === 'NOT DEFINED' || $value === 'EMPTY') ? 'red' : 'green';
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td style='color: {$color};'>{$value}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

// Step 2: Check PHPMailer
echo "<h2>2. PHPMailer Check</h2>";
try {
    require_once 'vendor/autoload.php';
    echo "<p style='color: green;'>✅ PHPMailer autoloader found</p>";
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color: green;'>✅ PHPMailer class available</p>";
    } else {
        echo "<p style='color: red;'>❌ PHPMailer class not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ PHPMailer error: " . $e->getMessage() . "</p>";
}

// Step 3: Check Email Service
echo "<h2>3. Email Service Check</h2>";
try {
    require_once 'includes/email_service.php';
    echo "<p style='color: green;'>✅ EmailService class loaded</p>";
    
    $emailService = new EmailService();
    echo "<p style='color: green;'>✅ EmailService instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EmailService error: " . $e->getMessage() . "</p>";
    echo "<p>Cannot proceed with further tests.</p>";
    exit;
}

// Step 4: Check database and users
echo "<h2>4. Database and User Check</h2>";
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✅ Database connection available</p>";
    
    // Check for users with emails
    $sql = "SELECT COUNT(*) as total_users, 
                   SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as users_with_email
            FROM users";
    $result = fetchSingleRow($sql);
    
    if ($result) {
        echo "<p>👥 Total users: {$result['total_users']}</p>";
        echo "<p>📧 Users with email: {$result['users_with_email']}</p>";
        
        if ($result['users_with_email'] == 0) {
            echo "<p style='color: red;'>❌ <strong>ISSUE FOUND:</strong> No users have email addresses!</p>";
            echo "<p>This is likely why emails are not being sent.</p>";
        } else {
            echo "<p style='color: green;'>✅ Some users have email addresses</p>";
        }
    }
    
    // Check for recent bookings
    $sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = fetchSingleRow($sql);
    echo "<p>📋 Recent bookings (last 7 days): {$result['total_bookings']}</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Step 5: Test email sending
echo "<h2>5. Email Sending Test</h2>";

if (isset($_POST['test_email_now'])) {
    $test_email = $_POST['test_email'];
    $test_type = $_POST['test_type'];
    
    echo "<h3>Testing email send to: {$test_email}</h3>";
    
    try {
        if ($test_type === 'simple') {
            // Simple test email
            $subject = "G-Arena Debug Test - " . date('Y-m-d H:i:s');
            $html_content = "<h2>G-Arena Email Test</h2><p>This is a debug test email. Time: " . date('Y-m-d H:i:s') . "</p>";
            $text_content = "G-Arena Email Test. Time: " . date('Y-m-d H:i:s');
            
            $result = $emailService->sendTestEmail($test_email, 'Debug User', $subject, $html_content, $text_content);
            
        } else {
            // Test with booking data
            $booking_id = (int)$_POST['booking_id'];
            
            if ($test_type === 'confirmation') {
                $result = $emailService->sendBookingConfirmation($booking_id);
            } else {
                $result = $emailService->sendBookingCancellation($booking_id);
            }
        }
        
        if ($result['success']) {
            echo "<p style='color: green; background: #d4edda; padding: 10px; border-radius: 5px;'>✅ <strong>SUCCESS!</strong> Email sent successfully!</p>";
            echo "<p>Message: {$result['message']}</p>";
        } else {
            echo "<p style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>❌ <strong>FAILED!</strong> Email not sent.</p>";
            echo "<p>Error: {$result['message']}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>❌ <strong>EXCEPTION!</strong></p>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

// Step 6: Show recent error logs
echo "<h2>6. Recent Error Logs</h2>";
$php_error_log = ini_get('error_log');
if ($php_error_log && file_exists($php_error_log)) {
    $logs = file($php_error_log);
    $recent_logs = array_slice($logs, -50); // Last 50 lines
    
    echo "<h3>Last 50 log entries (G-Arena related):</h3>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: scroll; font-family: monospace; font-size: 12px;'>";
    
    $found_logs = false;
    foreach ($recent_logs as $log) {
        if (stripos($log, 'g-arena') !== false || stripos($log, 'email') !== false) {
            echo htmlspecialchars($log) . "<br>";
            $found_logs = true;
        }
    }
    
    if (!$found_logs) {
        echo "<p>No G-Arena related logs found in recent entries.</p>";
    }
    
    echo "</div>";
} else {
    echo "<p>PHP error log not found or not accessible.</p>";
}

?>

<hr>
<h2>🧪 Quick Tests</h2>

<form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 10px 0;">
    <h3>Test Email Sending</h3>
    
    <p>
        <label><strong>Email Address:</strong></label><br>
        <input type="email" name="test_email" value="mrrilyaas@gmail.com" required style="width: 300px; padding: 5px;">
    </p>
    
    <p>
        <label><strong>Test Type:</strong></label><br>
        <input type="radio" name="test_type" value="simple" checked> Simple Test Email<br>
        <input type="radio" name="test_type" value="confirmation"> Booking Confirmation (requires booking ID)<br>
        <input type="radio" name="test_type" value="cancellation"> Booking Cancellation (requires booking ID)<br>
    </p>
    
    <p>
        <label><strong>Booking ID (for booking tests):</strong></label><br>
        <input type="number" name="booking_id" placeholder="Enter booking ID" style="width: 150px; padding: 5px;">
    </p>
    
    <button type="submit" name="test_email_now" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        🚀 Run Email Test
    </button>
</form>

<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;">
    <h3>📋 Next Steps:</h3>
    <ol>
        <li>If configuration shows ❌, fix the email_config.php file</li>
        <li>If users don't have emails, add test emails to user accounts</li>
        <li>Run the simple email test first to verify basic functionality</li>
        <li>If simple test works, try booking confirmation/cancellation tests</li>
        <li>Check error logs for detailed debugging information</li>
    </ol>
</div>

<p>
    <a href="check_user_emails.php" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">👥 Check User Emails</a>
    <a href="test/email_test.php" style="background: #6f42c1; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📧 Email Test Panel</a>
</p>
