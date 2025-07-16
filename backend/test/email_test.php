<?php
/**
 * Email Test Script for G-Arena
 * This script allows admins to test email functionality
 * 
 * Usage: Access this file via browser as an admin to test emails
 * Make sure to configure email settings in email_config.php first
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/email_service.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

header('Content-Type: application/json');

// Check if this is a POST request for testing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['test_email']) || !filter_var($input['test_email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Valid email address required']);
        exit;
    }
    
    try {
        $emailService = new EmailService();
        
        // Create a test booking data structure
        $test_booking = [
            'id' => 999,
            'booking_reference' => 'GA' . date('Ymd') . '9999',
            'user_email' => $input['test_email'],
            'full_name' => 'Test User',
            'username' => 'testuser',
            'station_name' => 'Gaming Station 1',
            'station_type' => 'PC Gaming',
            'description' => 'High-end PC for testing',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'total_hours' => 2.0,
            'total_amount' => 3000.00,
            'status' => 'confirmed'
        ];
        
        $test_type = $input['test_type'] ?? 'confirmation';
        
        if ($test_type === 'confirmation') {
            // Test confirmation email template directly
            $subject = 'Test Booking Confirmed - G-Arena Gaming Center';
            $html_content = $emailService->getConfirmationEmailTemplate($test_booking);
            $text_content = $emailService->getConfirmationEmailText($test_booking);
        } else {
            // Test cancellation email template directly
            $subject = 'Test Booking Cancelled - G-Arena Gaming Center';
            $html_content = $emailService->getCancellationEmailTemplate($test_booking);
            $text_content = $emailService->getCancellationEmailText($test_booking);
        }
        
        $result = $emailService->sendTestEmail(
            $input['test_email'],
            'Test User',
            $subject,
            $html_content,
            $text_content
        );
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Test failed: ' . $e->getMessage()
        ]);
    }
    exit;
}

// If GET request, show test form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - G-Arena Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-purple-600 text-white p-6">
            <h1 class="text-2xl font-bold"><i class="fas fa-envelope mr-2"></i>Email Test Panel</h1>
            <p class="mt-2 opacity-90">Test G-Arena email functionality</p>
        </div>
        
        <div class="p-6">
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                <h3 class="font-semibold text-blue-800 mb-2">Setup Instructions</h3>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                    <li>Configure email settings in <code>backend/config/email_config.php</code></li>
                    <li>For Gmail: Enable 2FA and create an App Password</li>
                    <li>Update SMTP credentials in the config file</li>
                    <li>Test with your own email address first</li>
                </ol>
            </div>
            
            <form id="emailTestForm" class="space-y-4">
                <div>
                    <label for="test_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Test Email Address
                    </label>
                    <input type="email" id="test_email" name="test_email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter email to receive test">
                </div>
                
                <div>
                    <label for="test_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Type
                    </label>
                    <select id="test_type" name="test_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="confirmation">Booking Confirmation</option>
                        <option value="cancellation">Booking Cancellation</option>
                    </select>
                </div>
                
                <button type="submit" 
                        class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <i class="fas fa-paper-plane mr-2"></i>Send Test Email
                </button>
            </form>
            
            <div id="result" class="mt-6 hidden"></div>
        </div>
    </div>

    <script>
        document.getElementById('emailTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const resultDiv = document.getElementById('result');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                resultDiv.className = `mt-6 p-4 rounded-md ${result.success ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'}`;
                resultDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${result.success ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                        <span>${result.message}</span>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
                
            } catch (error) {
                resultDiv.className = 'mt-6 p-4 rounded-md bg-red-50 border border-red-200 text-red-800';
                resultDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>Network error: ${error.message}</span>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
            }
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Test Email';
        });
    </script>
</body>
</html>
