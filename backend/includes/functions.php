<?php
require_once __DIR__ . '/../config/database.php';

function getAllStations()
{
    $sql = "SELECT * FROM gaming_stations ORDER BY station_name";
    return fetchAllRows($sql);
}

function getStationById($id)
{
    $id = escapeString($id);
    $sql = "SELECT * FROM gaming_stations WHERE id = '$id'";
    return fetchSingleRow($sql);
}

function createStation($name, $type, $description, $rate)
{
    // Escape all inputs to prevent SQL injection
    $name = escapeString($name);
    $type = escapeString($type);
    $description = escapeString($description);
    $rate = escapeString($rate);

    $sql = "INSERT INTO gaming_stations (station_name, station_type, description, hourly_rate) 
            VALUES ('$name', '$type', '$description', '$rate')";

    $result = executeQuery($sql);

    if ($result) {
        return ['success' => true, 'message' => 'Station created successfully', 'id' => getLastInsertId()];
    } else {
        return ['success' => false, 'message' => 'Failed to create station'];
    }
}

function updateStation($id, $name, $type, $description, $rate, $status)
{
    // Escape all inputs to prevent SQL injection
    $id = escapeString($id);
    $name = escapeString($name);
    $type = escapeString($type);
    $description = escapeString($description);
    $rate = escapeString($rate);
    $status = escapeString($status);

    $sql = "UPDATE gaming_stations 
            SET station_name = '$name', station_type = '$type', description = '$description', 
                hourly_rate = '$rate', status = '$status' 
            WHERE id = '$id'";

    $result = executeQuery($sql);

    if ($result) {
        return ['success' => true, 'message' => 'Station updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update station'];
    }
}

function deleteStation($id)
{
    $id = escapeString($id);
    $sql = "DELETE FROM gaming_stations WHERE id = '$id'";

    $result = executeQuery($sql);

    if ($result) {
        return ['success' => true, 'message' => 'Station deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete station'];
    }
}

function getUnavailableSlots($date)
{
    $date = escapeString($date);
    $sql = "SELECT * FROM unavailable_slots 
            WHERE unavailable_date = '$date' 
            ORDER BY start_time";
    return fetchAllRows($sql);
}

function checkBookingConflict($station_id, $date, $start_time, $end_time, $exclude_booking_id = null)
{
    // Escape inputs
    $station_id = escapeString($station_id);
    $date = escapeString($date);
    $start_time = escapeString($start_time);
    $end_time = escapeString($end_time);

    // Check for existing booking conflicts
    $sql = "SELECT COUNT(*) as count FROM bookings 
            WHERE station_id = '$station_id' AND booking_date = '$date' 
            AND status != 'cancelled'
            AND ((start_time < '$end_time' AND end_time > '$start_time') OR (start_time < '$start_time' AND end_time > '$end_time'))";

    if ($exclude_booking_id) {
        $exclude_booking_id = escapeString($exclude_booking_id);
        $sql .= " AND id != '$exclude_booking_id'";
    }

    $result = fetchSingleRow($sql);
    if ($result['count'] > 0) {
        return true; // Booking conflict found
    }

    // Check for arena-wide unavailable slots
    $unavailable_sql = "SELECT COUNT(*) as count FROM unavailable_slots 
                       WHERE unavailable_date = '$date' 
                       AND ((start_time < '$end_time' AND end_time > '$start_time') 
                            OR (start_time < '$start_time' AND end_time > '$end_time'))";

    $unavailable_result = fetchSingleRow($unavailable_sql);
    return ($unavailable_result['count'] > 0); // Return true if unavailable slot conflict found
}

function createBooking($user_id, $station_id, $date, $start_time, $end_time, $total_hours, $total_amount, $notes = '')
{
    // Check for conflicts first
    $conflict_check = checkBookingConflict($station_id, $date, $start_time, $end_time);
    if ($conflict_check === true) {
        return ['success' => false, 'message' => 'This time slot is already booked. Please choose a different time.'];
    }

    // Validate business hours (9 AM to 8 PM)
    $start_hour = (int)substr($start_time, 0, 2);
    $end_hour = (int)substr($end_time, 0, 2);
    $end_minute = (int)substr($end_time, 3, 2);

    if ($start_hour < 9 || $end_hour > 20 || ($end_hour === 20 && $end_minute > 0)) {
        return ['success' => false, 'message' => 'Booking times must be between 9:00 AM and 8:00 PM'];
    }

    // Generate booking reference
    $booking_reference = 'GA' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Escape all inputs
    $user_id = escapeString($user_id);
    $station_id = escapeString($station_id);
    $date = escapeString($date);
    $start_time = escapeString($start_time);
    $end_time = escapeString($end_time);
    $total_hours = escapeString($total_hours);
    $total_amount = escapeString($total_amount);
    $booking_reference = escapeString($booking_reference);
    $notes = escapeString($notes);

    // Validate the station exists and is active
    $station = getStationById($station_id);
    if (!$station) {
        return ['success' => false, 'message' => 'The selected station does not exist'];
    }
    if ($station['status'] !== 'active') {
        return ['success' => false, 'message' => 'The selected station is currently not available for booking'];
    }

    $sql = "INSERT INTO bookings (user_id, station_id, booking_date, start_time, end_time, total_hours, total_amount, booking_reference, notes) 
            VALUES ('$user_id', '$station_id', '$date', '$start_time', '$end_time', '$total_hours', '$total_amount', '$booking_reference', '$notes')";

    $result = executeQuery($sql);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Booking created successfully',
            'reference' => $booking_reference,
            'details' => [
                'station' => $station['station_name'],
                'date' => $date,
                'time' => "$start_time - $end_time",
                'total_hours' => $total_hours,
                'total_amount' => $total_amount
            ]
        ];
    } else {
        error_log("Database error in createBooking: " . mysqli_error($GLOBALS['connection']));
        return ['success' => false, 'message' => 'A database error occurred while creating your booking. Please try again.'];
    }
}

function getUserBookings($user_id)
{
    $user_id = escapeString($user_id);

    $sql = "SELECT b.*, s.station_name, s.station_type 
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            WHERE b.user_id = '$user_id' 
            ORDER BY b.booking_date DESC, b.start_time DESC";

    return fetchAllRows($sql);
}

function getAllBookings()
{
    $sql = "SELECT b.*, s.station_name, s.station_type, u.username, u.full_name 
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.booking_date DESC, b.start_time DESC";

    return fetchAllRows($sql);
}

function updateBookingStatus($booking_id, $status)
{
    $booking_id = escapeString($booking_id);
    $status = escapeString($status);

    $sql = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";
    $result = executeQuery($sql);

    if ($result) {
        return ['success' => true, 'message' => 'Booking status updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update booking status'];
    }
}

function generateBookingReference()
{
    return 'GA' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function getAllBookingsWithDetails()
{
    $sql = "SELECT b.*, s.station_name, s.station_type, u.email as user_email 
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC";

    return fetchAllRows($sql);
}
