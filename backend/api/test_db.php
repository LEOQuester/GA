<?php
require_once '../config/config.php';
require_once '../config/database.php';

echo "Testing database structure...\n";

$connection = getDbConnection();

// Check current bookings table structure
echo "Current bookings table structure:\n";
$result = mysqli_query($connection, "DESCRIBE bookings");
while ($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\nChecking for booking_stations table:\n";
$result = mysqli_query($connection, "SHOW TABLES LIKE 'booking_stations'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ booking_stations table exists\n";
} else {
    echo "✗ booking_stations table missing - creating it...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS booking_stations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        station_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE,
        UNIQUE KEY unique_booking_station (booking_id, station_id)
    )";
    
    if (mysqli_query($connection, $sql)) {
        echo "✓ booking_stations table created\n";
    } else {
        echo "✗ Error creating table: " . mysqli_error($connection) . "\n";
    }
}

// Check for required columns in bookings table
echo "\nChecking bookings table columns:\n";
$result = mysqli_query($connection, "SHOW COLUMNS FROM bookings LIKE 'booking_type'");
if (mysqli_num_rows($result) == 0) {
    echo "Adding booking_type column...\n";
    mysqli_query($connection, "ALTER TABLE bookings ADD COLUMN booking_type ENUM('single_station', 'multi_station') DEFAULT 'single_station'");
}

$result = mysqli_query($connection, "SHOW COLUMNS FROM bookings LIKE 'station_count'");
if (mysqli_num_rows($result) == 0) {
    echo "Adding station_count column...\n";
    mysqli_query($connection, "ALTER TABLE bookings ADD COLUMN station_count INT DEFAULT 1");
}

echo "Database structure check complete!\n";
?>
