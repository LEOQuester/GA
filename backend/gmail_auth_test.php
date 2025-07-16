<?php
/**
 * Detailed SMTP Configuration Checker for Gmail Authentication Issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>🔍 SMTP Configuration Analysis</h1>";

// Check current configuration
echo "<h2>Current Configuration Values:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Setting</th><th>Value</th><th>Status</th></tr>";

$configs = [
    'SMTP_HOST' => SMTP_HOST,
    'SMTP_PORT' => SMTP_PORT,
    'SMTP_USERNAME' => SMTP_USERNAME,
    'SMTP_PASSWORD' => substr(SMTP_PASSWORD, 0, 4) . '***' . substr(SMTP_PASSWORD, -4),
    'SMTP_ENCRYPTION' => SMTP_ENCRYPTION,
    'FROM_EMAIL' => FROM_EMAIL,
];

foreach ($configs as $key => $value) {
    $status = '✅';
    $color = 'green';
    
    if ($key === 'SMTP_PASSWORD' && SMTP_PASSWORD === 'yynsrpuheytffvwd') {
        $status = '⚠️ OLD PASSWORD';
        $color = 'orange';
    } elseif ($key === 'SMTP_PASSWORD' && strlen(SMTP_PASSWORD) !== 16) {
        $status = '❌ WRONG LENGTH';
        $color = 'red';
    }
    
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td style='color: {$color};'>{$value}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

// Check PHPMailer constants
echo "<h2>PHPMailer Encryption Constants:</h2>";
echo "<ul>";
echo "<li><strong>ENCRYPTION_STARTTLS:</strong> " . PHPMailer::ENCRYPTION_STARTTLS . " (should be 'tls')</li>";
echo "<li><strong>ENCRYPTION_SMTPS:</strong> " . PHPMailer::ENCRYPTION_SMTPS . " (should be 'ssl')</li>";
echo "<li><strong>Current Setting (SMTP_ENCRYPTION):</strong> " . SMTP_ENCRYPTION . "</li>";
echo "</ul>";

// Test different Gmail configurations
echo "<h2>🧪 Testing Different Gmail Configurations:</h2>";

$test_configs = [
    [
        'name' => 'Standard TLS (Port 587)',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
        'description' => 'Most common Gmail setup'
    ],
    [
        'name' => 'SSL (Port 465)',
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'encryption' => PHPMailer::ENCRYPTION_SMTPS,
        'description' => 'Alternative Gmail setup'
    ]
];

foreach ($test_configs as $config) {
    echo "<h3>{$config['name']} - {$config['description']}</h3>";
    
    try {
        $mail = new PHPMailer(true);
        
        // Capture debug output
        $debugOutput = '';
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= htmlspecialchars($str) . "\n";
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port = $config['port'];
        
        echo "<p><strong>Configuration:</strong></p>";
        echo "<ul>";
        echo "<li>Host: {$config['host']}</li>";
        echo "<li>Port: {$config['port']}</li>";
        echo "<li>Encryption: {$config['encryption']}</li>";
        echo "<li>Username: " . SMTP_USERNAME . "</li>";
        echo "<li>Password Length: " . strlen(SMTP_PASSWORD) . " characters</li>";
        echo "</ul>";
        
        echo "<p><strong>Testing connection...</strong></p>";
        
        // Test connection
        $result = $mail->smtpConnect();
        
        if ($result) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ SUCCESS!</strong> This configuration works!";
            echo "</div>";
            
            // Update the config file suggestion
            if ($config['port'] != SMTP_PORT || $config['encryption'] != SMTP_ENCRYPTION) {
                echo "<p><strong>💡 Suggested config update:</strong></p>";
                echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
                echo "define('SMTP_PORT', {$config['port']});\n";
                echo "define('SMTP_ENCRYPTION', '" . ($config['encryption'] === PHPMailer::ENCRYPTION_SMTPS ? 'ssl' : 'tls') . "');\n";
                echo "</pre>";
            }
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ FAILED!</strong> This configuration doesn't work.";
            echo "</div>";
        }
        
        $mail->smtpClose();
        
        // Show debug output
        if (!empty($debugOutput)) {
            echo "<details style='margin: 10px 0;'>";
            echo "<summary>📋 Debug Output (click to expand)</summary>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
            echo $debugOutput;
            echo "</pre>";
            echo "</details>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ ERROR:</strong> " . $e->getMessage();
        echo "</div>";
        
        // Specific error analysis
        if (strpos($e->getMessage(), 'Could not authenticate') !== false) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>🔑 Authentication Issue Detected</strong><br>";
            echo "This usually means:<br>";
            echo "• Your App Password has expired<br>";
            echo "• 2-Factor Authentication is disabled<br>";
            echo "• You're using your regular password instead of App Password<br>";
            echo "• The App Password was typed incorrectly<br>";
            echo "</div>";
        }
    }
    
    echo "<hr>";
}

// Show instructions for fixing
echo "<h2>🛠️ How to Fix Authentication Issues:</h2>";
echo "<div style='background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px;'>";
echo "<h3>Step 1: Generate New App Password</h3>";
echo "<ol>";
echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
echo "<li>Make sure <strong>2-Step Verification</strong> is ON</li>";
echo "<li>Click on <strong>App passwords</strong></li>";
echo "<li>Select <strong>Mail</strong> and <strong>Other (Custom name)</strong></li>";
echo "<li>Enter 'G-Arena Gaming Center'</li>";
echo "<li>Copy the <strong>16-character password</strong> (like: abcd efgh ijkl mnop)</li>";
echo "</ol>";

echo "<h3>Step 2: Update Configuration</h3>";
echo "<ol>";
echo "<li>Open <code>backend/config/email_config.php</code></li>";
echo "<li>Replace the password with your new App Password (no spaces)</li>";
echo "<li>Save the file</li>";
echo "<li>Test again</li>";
echo "</ol>";
echo "</div>";

if (SMTP_PASSWORD === 'yynsrpuheytffvwd') {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>⚠️ URGENT:</strong> You're still using an old App Password. This likely needs to be regenerated.";
    echo "</div>";
}

echo "<p><a href='test/email_test.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📧 Test Email After Fix</a></p>";
?>
