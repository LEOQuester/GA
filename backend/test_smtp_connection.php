<?php
/**
 * Quick SMTP Connection Test for Gmail
 * Use this to test your Gmail App Password
 */

require_once 'vendor/autoload.php';
require_once 'config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>🔒 Gmail SMTP Authentication Test</h1>";

// Display current settings
echo "<h2>Current Settings:</h2>";
echo "<ul>";
echo "<li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>";
echo "<li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>";
echo "<li><strong>Username:</strong> " . SMTP_USERNAME . "</li>";
echo "<li><strong>Password:</strong> " . (SMTP_PASSWORD === 'YOUR_NEW_APP_PASSWORD_HERE' ? '❌ NOT SET' : '✅ SET') . "</li>";
echo "<li><strong>Encryption:</strong> " . SMTP_ENCRYPTION . "</li>";
echo "</ul>";

if (SMTP_PASSWORD === 'YOUR_NEW_APP_PASSWORD_HERE') {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Setup Required</h3>";
    echo "<p><strong>You need to update the App Password first!</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
    echo "<li>Enable 2-Factor Authentication if not already enabled</li>";
    echo "<li>Go to <strong>App passwords</strong></li>";
    echo "<li>Generate a new password for 'Mail' → 'Other (G-Arena)'</li>";
    echo "<li>Copy the 16-character password (no spaces)</li>";
    echo "<li>Replace 'YOUR_NEW_APP_PASSWORD_HERE' in email_config.php</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

// Test SMTP connection
echo "<h2>🧪 Testing SMTP Connection...</h2>";

try {
    $mail = new PHPMailer(true);
    
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    $mail->Debugoutput = function($str, $level) {
        echo "<pre style='background: #f8f9fa; padding: 5px; margin: 2px 0; font-size: 12px;'>" . htmlspecialchars($str) . "</pre>";
    };
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port       = SMTP_PORT;
    
    // Test connection only (don't send email)
    echo "<p>Attempting to connect to Gmail SMTP...</p>";
    
    // This will test the connection and authentication
    $mail->smtpConnect();
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ SUCCESS!</h3>";
    echo "<p>SMTP authentication successful! Your Gmail App Password is working correctly.</p>";
    echo "<p>You can now send emails through the G-Arena system.</p>";
    echo "</div>";
    
    $mail->smtpClose();
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ FAILED!</h3>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    
    // Provide specific troubleshooting based on error
    if (strpos($e->getMessage(), 'Could not authenticate') !== false) {
        echo "<h4>🔧 Troubleshooting Steps:</h4>";
        echo "<ol>";
        echo "<li><strong>Check App Password:</strong> Make sure you're using a 16-character App Password, not your regular Gmail password</li>";
        echo "<li><strong>Regenerate App Password:</strong> Create a new App Password in your Google Account</li>";
        echo "<li><strong>Check 2FA:</strong> Ensure 2-Factor Authentication is enabled on your Google Account</li>";
        echo "<li><strong>Check Username:</strong> Make sure the email address is correct</li>";
        echo "<li><strong>Try Alternative Settings:</strong> Use port 465 with SSL instead of 587 with TLS</li>";
        echo "</ol>";
    } elseif (strpos($e->getMessage(), 'Connection failed') !== false) {
        echo "<h4>🔧 Connection Issues:</h4>";
        echo "<ol>";
        echo "<li>Check your internet connection</li>";
        echo "<li>Try port 465 with SSL encryption</li>";
        echo "<li>Check if your firewall is blocking SMTP connections</li>";
        echo "</ol>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>If test passes: Try sending emails through the G-Arena booking system</li>";
echo "<li>If test fails: Follow the troubleshooting steps above</li>";
echo "<li>Alternative: Consider using a different email service like SendGrid or Mailgun</li>";
echo "</ol>";

echo "<p><a href='test/email_test.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📧 Test Email Sending</a></p>";
?>

<style>
pre {
    font-family: 'Courier New', monospace;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
