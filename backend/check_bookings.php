<?php
/**
 * Check recent bookings for email testing
 */

require_once 'config/database.php';

echo "<h2>Recent Bookings for Email Testing</h2>";

try {
    $sql = "SELECT b.id, b.booking_reference, b.status, b.booking_date, b.start_time, b.end_time,
                   s.station_name, u.email as user_email, u.full_name, u.username
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC 
            LIMIT 10";
    
    $result = fetchAllRows($sql);
    
    if ($result && count($result) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Reference</th><th>Status</th><th>User</th><th>Email</th><th>Station</th><th>Date</th><th>Time</th><th>Actions</th>";
        echo "</tr>";
        
        foreach ($result as $booking) {
            echo "<tr>";
            echo "<td>{$booking['id']}</td>";
            echo "<td>{$booking['booking_reference']}</td>";
            echo "<td style='color: " . ($booking['status'] === 'confirmed' ? 'green' : ($booking['status'] === 'cancelled' ? 'red' : 'orange')) . ";'><strong>{$booking['status']}</strong></td>";
            echo "<td>{$booking['full_name']}</td>";
            echo "<td>{$booking['user_email']}</td>";
            echo "<td>{$booking['station_name']}</td>";
            echo "<td>{$booking['booking_date']}</td>";
            echo "<td>{$booking['start_time']} - {$booking['end_time']}</td>";
            echo "<td>";
            echo "<a href='debug_email.php?test=booking&booking_id={$booking['id']}' style='background: #dc3545; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;'>Test Cancel Email</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='margin-top: 20px;'><strong>Instructions:</strong></p>";
        echo "<ul>";
        echo "<li>Click 'Test Cancel Email' to test sending a cancellation email for that booking</li>";
        echo "<li>You can test with any booking regardless of its current status</li>";
        echo "<li>Check the debug_email.php page for detailed error information</li>";
        echo "</ul>";
        
    } else {
        echo "<p>No bookings found. Create a test booking first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='debug_email.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔧 Go to Email Debug Tool</a></p>";
?>
