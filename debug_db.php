<?php
require_once 'backend/config/database.php';
require_once 'backend/includes/functions.php';

echo "Checking database tables...\n";

try {
    // Check if tables exist
    $tables = fetchAllRows('SHOW TABLES');
    echo "Available tables:\n";
    foreach($tables as $table) {
        echo "- " . current($table) . "\n";
    }
    
    // Check gaming_stations count
    $stations = fetchSingleRow('SELECT COUNT(*) as count FROM gaming_stations');
    echo "\nGaming stations count: " . ($stations ? $stations['count'] : 'Error') . "\n";
    
    // Show some sample stations
    $sample_stations = fetchAllRows('SELECT id, name, hourly_rate FROM gaming_stations LIMIT 3');
    echo "Sample stations:\n";
    foreach($sample_stations as $station) {
        echo "- ID: " . $station['id'] . ", Name: " . $station['name'] . ", Rate: " . $station['hourly_rate'] . "\n";
    }
    
    // Check bookings count
    $bookings = fetchSingleRow('SELECT COUNT(*) as count FROM bookings');
    echo "\nBookings count: " . ($bookings ? $bookings['count'] : 'Error') . "\n";
    
    // Check pending bookings
    $pending = fetchSingleRow('SELECT COUNT(*) as count FROM bookings WHERE status = "pending"');
    echo "Pending bookings: " . ($pending ? $pending['count'] : 'Error') . "\n";
    
    // Check today's revenue
    $today = date('Y-m-d');
    $revenue = fetchSingleRow("SELECT SUM(total_amount) as revenue FROM bookings WHERE DATE(created_at) = '$today' AND status = 'confirmed'");
    echo "Today's revenue: " . ($revenue ? ($revenue['revenue'] ?? 0) : 'Error') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
