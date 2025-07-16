<?php
/**
 * Check User Email Data and Fix Email Issues
 */

require_once 'config/database.php';

echo "<h2>User Email Data Analysis</h2>";

// Check if users have email addresses
try {
    $sql = "SELECT id, username, full_name, email, status FROM users ORDER BY created_at DESC LIMIT 10";
    $result = fetchAllRows($sql);
    
    if ($result) {
        echo "<h3>Recent Users and Their Email Addresses:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Status</th><th>Has Email?</th>";
        echo "</tr>";
        
        $users_without_email = 0;
        foreach ($result as $user) {
            $hasEmail = !empty($user['email']);
            if (!$hasEmail) $users_without_email++;
            
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td style='color: " . ($hasEmail ? 'green' : 'red') . ";'>" . ($user['email'] ?: 'NO EMAIL') . "</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td style='color: " . ($hasEmail ? 'green' : 'red') . ";'>" . ($hasEmail ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($users_without_email > 0) {
            echo "<p style='color: red;'><strong>Warning:</strong> {$users_without_email} users don't have email addresses!</p>";
        }
        
    } else {
        echo "<p>No users found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check recent bookings and their user emails
echo "<h3>Recent Bookings with User Email Status:</h3>";
try {
    $sql = "SELECT b.id, b.booking_reference, b.status, b.booking_date,
                   u.username, u.full_name, u.email as user_email, u.status as user_status
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC 
            LIMIT 10";
    $result = fetchAllRows($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Booking ID</th><th>Reference</th><th>Status</th><th>Date</th><th>User</th><th>Email</th><th>Can Send Email?</th><th>Action</th>";
        echo "</tr>";
        
        foreach ($result as $booking) {
            $canSendEmail = !empty($booking['user_email']);
            echo "<tr>";
            echo "<td>{$booking['id']}</td>";
            echo "<td>{$booking['booking_reference']}</td>";
            echo "<td style='color: " . ($booking['status'] === 'confirmed' ? 'green' : ($booking['status'] === 'cancelled' ? 'red' : 'orange')) . ";'>{$booking['status']}</td>";
            echo "<td>{$booking['booking_date']}</td>";
            echo "<td>{$booking['username']} ({$booking['full_name']})</td>";
            echo "<td style='color: " . ($canSendEmail ? 'green' : 'red') . ";'>" . ($booking['user_email'] ?: 'NO EMAIL') . "</td>";
            echo "<td style='color: " . ($canSendEmail ? 'green' : 'red') . ";'>" . ($canSendEmail ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>";
            if ($canSendEmail) {
                echo "<a href='debug_email.php?test=booking&booking_id={$booking['id']}' style='background: #dc3545; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;'>Test Email</a>";
            } else {
                echo "<span style='color: #666; font-size: 12px;'>Cannot send</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No bookings found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check if there's a specific user we can update with an email for testing
echo "<h3>Quick Fix - Add Test Email to a User:</h3>";

if (isset($_POST['add_email'])) {
    $user_id = (int)$_POST['user_id'];
    $email = escapeString($_POST['email']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $sql = "UPDATE users SET email = '$email' WHERE id = $user_id";
            $result = executeQuery($sql);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Successfully added email {$email} to user ID {$user_id}</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update user email</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid email format</p>";
    }
}

// Show form to add email to a user
$sql = "SELECT id, username, full_name, email FROM users WHERE email IS NULL OR email = '' LIMIT 5";
$users_without_email = fetchAllRows($sql);

if ($users_without_email && count($users_without_email) > 0) {
    echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Add email to a user for testing:</strong></p>";
    echo "<select name='user_id' required style='padding: 5px; margin: 5px;'>";
    echo "<option value=''>Select User</option>";
    foreach ($users_without_email as $user) {
        echo "<option value='{$user['id']}'>{$user['username']} - {$user['full_name']}</option>";
    }
    echo "</select>";
    echo "<input type='email' name='email' placeholder='Enter email address' required style='padding: 5px; margin: 5px; width: 200px;'>";
    echo "<button type='submit' name='add_email' style='padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px;'>Add Email</button>";
    echo "</form>";
}

echo "<hr>";
echo "<h3>Debugging Tools:</h3>";
echo "<p><a href='debug_email.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔧 Email Debug Tool</a></p>";
echo "<p><a href='test/email_test.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📧 Email Test Panel</a></p>";
?>
