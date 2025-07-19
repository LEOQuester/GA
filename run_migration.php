<?php
echo "Running Multi-Station Booking Migration...\n";

require_once 'backend/config/config.php';

try {
    $connection = getDbConnection();
    
    // Read the migration file
    $migrationSQL = file_get_contents('multi_station_booking_migration.sql');
    
    if (!$migrationSQL) {
        throw new Exception("Could not read migration file");
    }
    
    // Split by semicolons and execute each statement
    $statements = explode(';', $migrationSQL);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        if (mysqli_query($connection, $statement)) {
            if (!empty(mysqli_info($connection))) {
                echo "✓ " . mysqli_info($connection) . "\n";
            }
        } else {
            $error = mysqli_error($connection);
            if (strpos($error, 'already exists') === false && 
                strpos($error, 'Duplicate') === false) {
                echo "✗ Error: " . $error . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    // Verify the migration
    echo "\nVerifying migration...\n";
    
    // Check if booking_stations table exists
    $result = mysqli_query($connection, "SHOW TABLES LIKE 'booking_stations'");
    if (mysqli_num_rows($result) > 0) {
        echo "✓ booking_stations table exists\n";
    } else {
        echo "✗ booking_stations table missing\n";
    }
    
    // Check if new columns exist
    $result = mysqli_query($connection, "SHOW COLUMNS FROM bookings LIKE 'booking_type'");
    if (mysqli_num_rows($result) > 0) {
        echo "✓ booking_type column exists\n";
    } else {
        echo "✗ booking_type column missing\n";
    }
    
    $result = mysqli_query($connection, "SHOW COLUMNS FROM bookings LIKE 'station_count'");
    if (mysqli_num_rows($result) > 0) {
        echo "✓ station_count column exists\n";
    } else {
        echo "✗ station_count column missing\n";
    }
    
    echo "\nMigration completed!\n";
    echo "You can now test multi-station bookings.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
