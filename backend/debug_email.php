<?php
/**
 * Quick Email Test for Debugging G-Arena Email Issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/email_service.php';

echo "<h2>G-Arena Email Debug Test</h2>";

// Test 1: Check email configuration
echo "<h3>1. Email Configuration Status:</h3>";
echo "<ul>";
echo "<li>SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED') . "</li>";
echo "<li>SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NOT DEFINED') . "</li>";
echo "<li>SMTP Username: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT DEFINED') . "</li>";
echo "<li>SMTP Password: " . (defined('SMTP_PASSWORD') ? (SMTP_PASSWORD ? 'SET' : 'EMPTY') : 'NOT DEFINED') . "</li>";
echo "<li>From Email: " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT DEFINED') . "</li>";
echo "</ul>";

// Test 2: Test basic email sending
if (isset($_GET['test']) && $_GET['test'] === 'send') {
    echo "<h3>2. Testing Email Sending...</h3>";
    
    $test_email = $_GET['email'] ?? 'mrrilyaas@gmail.com'; // Default to your email
    
    try {
        $emailService = new EmailService();
        
        $subject = "G-Arena Test Email - " . date('Y-m-d H:i:s');
        $html_content = "
        <html>
        <body>
            <h2>G-Arena Email Test</h2>
            <p>This is a test email to verify the email system is working.</p>
            <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
            <p>If you receive this, your email configuration is working correctly!</p>
        </body>
        </html>";
        
        $text_content = "G-Arena Email Test\nThis is a test email to verify the email system is working.\nSent at: " . date('Y-m-d H:i:s');
        
        $result = $emailService->sendTestEmail($test_email, 'Test User', $subject, $html_content, $text_content);
        
        if ($result['success']) {
            echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Test email sent to {$test_email}</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ FAILED:</strong> {$result['message']}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>";
    }
}

// Test 3: Test booking cancellation email with a real booking
if (isset($_GET['test']) && $_GET['test'] === 'booking') {
    echo "<h3>3. Testing Booking Cancellation Email...</h3>";
    
    $booking_id = $_GET['booking_id'] ?? null;
    
    if ($booking_id) {
        try {
            $emailService = new EmailService();
            $result = $emailService->sendBookingCancellation($booking_id);
            
            if ($result['success']) {
                echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Booking cancellation email sent for booking ID {$booking_id}</p>";
            } else {
                echo "<p style='color: red;'><strong>❌ FAILED:</strong> {$result['message']}</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>Please provide a booking_id parameter</p>";
    }
}

// Check recent error logs
echo "<h3>4. Recent Error Logs:</h3>";
$error_log_path = ini_get('error_log');
if ($error_log_path && file_exists($error_log_path)) {
    $logs = array_slice(file($error_log_path), -20); // Last 20 lines
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
    foreach ($logs as $log) {
        if (strpos($log, 'G-Arena') !== false) {
            echo htmlspecialchars($log);
        }
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found or not accessible at: " . ($error_log_path ?: 'not configured') . "</p>";
}

?>

<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
    <h3>Quick Tests:</h3>
    <p><a href="?test=send&email=mrrilyaas@gmail.com" style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📧 Send Test Email</a></p>
    <p><a href="?test=send&email=<?php echo $_GET['custom_email'] ?? ''; ?>" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📧 Send to Custom Email</a> 
    <input type="text" id="customEmail" placeholder="Enter email" style="margin-left: 10px; padding: 5px;">
    <button onclick="window.location.href='?test=send&email=' + document.getElementById('customEmail').value" style="padding: 5px 10px;">Send</button></p>
    
    <p><strong>To test booking cancellation email:</strong></p>
    <p>Go to: <code>?test=booking&booking_id=BOOKING_ID</code> (replace BOOKING_ID with actual booking ID)</p>
</div>
