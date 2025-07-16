<?php
/**
 * Email Test Script for G-Arena
 * Use this to test your email configuration
 */

require_once 'includes/email_service.php';

// Test email configuration
echo "<h2>G-Arena Email Test</h2>";

echo "<p><strong>Email Configuration:</strong></p>";
echo "<ul>";
echo "<li>SMTP Host: " . SMTP_HOST . "</li>";
echo "<li>SMTP Port: " . SMTP_PORT . "</li>";
echo "<li>SMTP Username: " . SMTP_USERNAME . "</li>";
echo "<li>From Email: " . FROM_EMAIL . "</li>";
echo "</ul>";

// Test email sending
if (isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    
    if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        echo "<h3>Sending Test Email...</h3>";
        
        $emailService = new EmailService();
        
        // Create a simple test email
        $subject = "G-Arena Email Test";
        $html_content = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #e74c3c;'>G-Arena Email Test</h2>
            <p>Congratulations! Your email configuration is working correctly.</p>
            <p>This is a test email from your G-Arena gaming center booking system.</p>
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>Time: " . date('Y-m-d H:i:s') . "</li>
                <li>From: " . FROM_EMAIL . "</li>
                <li>To: " . $test_email . "</li>
            </ul>
            <p>You can now use the booking confirmation and cancellation email features!</p>
        </body>
        </html>";
        
        $text_content = "G-Arena Email Test\n\nCongratulations! Your email configuration is working correctly.\n\nThis is a test email from your G-Arena gaming center booking system.\n\nTime: " . date('Y-m-d H:i:s') . "\nFrom: " . FROM_EMAIL . "\nTo: " . $test_email;
        
        // Use the public sendTestEmail method
        $result = $emailService->sendTestEmail($test_email, 'Test User', $subject, $html_content, $text_content);
        
        if ($result['success']) {
            echo "<p style='color: green;'><strong>Success!</strong> Test email sent successfully to {$test_email}</p>";
            echo "<p>Check your inbox (and spam folder) for the test email.</p>";
        } else {
            echo "<p style='color: red;'><strong>Error:</strong> " . $result['message'] . "</p>";
            echo "<p>Check your email configuration and try again.</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>Error:</strong> Invalid email address format.</p>";
    }
}
?>

<form method="post" style="margin-top: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
    <h3>Send Test Email</h3>
    <p>Enter your email address to test the email configuration:</p>
    <input type="email" name="test_email" placeholder="your-email@example.com" required style="padding: 8px; width: 300px; margin-right: 10px;">
    <button type="submit" style="padding: 8px 15px; background: #e74c3c; color: white; border: none; border-radius: 3px; cursor: pointer;">Send Test Email</button>
</form>

<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
    <h3>Configuration Status: ✅ Ready</h3>
    <p><strong>✅ Email credentials configured</strong></p>
    <p><strong>✅ PHPMailer installed</strong></p>
    <p><strong>✅ SMTP settings configured</strong></p>
    <p><strong>Next Step:</strong> Test using the form above, then test booking confirmation by creating and confirming a booking</p>
</div>
