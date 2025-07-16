<?php
/**
 * Database Structure Check for Email Functionality
 */

require_once 'config/database.php';

echo "<h2>Database Structure Check</h2>";

// Check if users table has email column
echo "<h3>1. Users Table Structure:</h3>";
try {
    $sql = "DESCRIBE users";
    $result = fetchAllRows($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        $hasEmail = false;
        foreach ($result as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
            
            if ($column['Field'] === 'email') {
                $hasEmail = true;
            }
        }
        echo "</table>";
        
        if (!$hasEmail) {
            echo "<p style='color: red;'><strong>❌ ISSUE FOUND:</strong> Users table does not have an 'email' column!</p>";
            echo "<p>This is why emails are not being sent. The system cannot find user email addresses.</p>";
            echo "<p><strong>Fix:</strong> Add an email column to the users table:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL;</pre>";
        } else {
            echo "<p style='color: green;'><strong>✅ Good:</strong> Users table has email column</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Could not retrieve users table structure</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking users table: " . $e->getMessage() . "</p>";
}

// Check existing users and their email data
echo "<h3>2. Sample User Data:</h3>";
try {
    $sql = "SELECT id, username, full_name, email FROM users LIMIT 5";
    $result = fetchAllRows($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th></tr>";
        
        foreach ($result as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td style='" . (empty($user['email']) ? 'color: red;' : 'color: green;') . "'>" . ($user['email'] ?: 'NO EMAIL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking user data: " . $e->getMessage() . "</p>";
}

// Check bookings table
echo "<h3>3. Recent Bookings with User Info:</h3>";
try {
    $sql = "SELECT b.id, b.booking_reference, b.status, 
                   u.username, u.full_name, u.email as user_email
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC 
            LIMIT 5";
    $result = fetchAllRows($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Booking ID</th><th>Reference</th><th>Status</th><th>Username</th><th>Full Name</th><th>Email</th></tr>";
        
        foreach ($result as $booking) {
            echo "<tr>";
            echo "<td>{$booking['id']}</td>";
            echo "<td>{$booking['booking_reference']}</td>";
            echo "<td>{$booking['status']}</td>";
            echo "<td>{$booking['username']}</td>";
            echo "<td>{$booking['full_name']}</td>";
            echo "<td style='" . (empty($booking['user_email']) ? 'color: red;' : 'color: green;') . "'>" . ($booking['user_email'] ?: 'NO EMAIL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No bookings found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking booking data: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='debug_email.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔧 Go to Email Debug Tool</a></p>";
echo "<p><a href='check_bookings.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📋 Check Bookings</a></p>";
?>
