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
    // First, get the current station data
    $current = getStationById($id);
    if (!$current) {
        return ['success' => false, 'message' => 'Station not found'];
    }

    // Use current values if new ones are empty
    $name = !empty($name) ? $name : $current['station_name'];
    $type = !empty($type) ? $type : $current['station_type'];
    $description = $description !== '' ? $description : $current['description'];
    $rate = $rate > 0 ? $rate : $current['hourly_rate'];
    $status = !empty($status) ? $status : $current['status'];

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

    // Check for existing booking conflicts in main bookings table
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
        return true; // Booking conflict found in main table
    }

    // Check for conflicts in booking_stations table (for multi-station bookings)
    $multi_station_sql = "SELECT COUNT(*) as count FROM booking_stations bs
                         JOIN bookings b ON bs.booking_id = b.id
                         WHERE bs.station_id = '$station_id' AND b.booking_date = '$date' 
                         AND b.status != 'cancelled'
                         AND ((b.start_time < '$end_time' AND b.end_time > '$start_time') 
                              OR (b.start_time < '$start_time' AND b.end_time > '$end_time'))";

    if ($exclude_booking_id) {
        $multi_station_sql .= " AND b.id != '$exclude_booking_id'";
    }

    $multi_result = fetchSingleRow($multi_station_sql);
    if ($multi_result['count'] > 0) {
        return true; // Booking conflict found in multi-station table
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

    $sql = "SELECT b.*, 
                   s.station_name as primary_station_name, 
                   s.station_type as primary_station_type,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           (SELECT GROUP_CONCAT(gs.station_name SEPARATOR ', ') 
                            FROM booking_stations bs2 
                            JOIN gaming_stations gs ON bs2.station_id = gs.id 
                            WHERE bs2.booking_id = b.id)
                       ELSE 
                           s.station_name 
                   END as station_name,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           CONCAT('Multi-Station (', b.station_count, ' stations)')
                       ELSE 
                           s.station_type 
                   END as station_type
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            WHERE b.user_id = '$user_id' 
            ORDER BY b.booking_date DESC, b.start_time DESC";

    return fetchAllRows($sql);
}

function getAllBookings()
{
    $sql = "SELECT b.*, 
                   s.station_name as primary_station_name, 
                   s.station_type as primary_station_type,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           (SELECT GROUP_CONCAT(gs.station_name SEPARATOR ', ') 
                            FROM booking_stations bs2 
                            JOIN gaming_stations gs ON bs2.station_id = gs.id 
                            WHERE bs2.booking_id = b.id)
                       ELSE 
                           s.station_name 
                   END as station_name,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           CONCAT('Multi-Station (', b.station_count, ' stations)')
                       ELSE 
                           s.station_type 
                   END as station_type,
                   u.username, u.full_name 
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
        error_log("G-Arena: Successfully updated booking {$booking_id} to status {$status}");
        
        // Send email notification for confirmed or cancelled bookings
        if ($status === 'confirmed' || $status === 'cancelled') {
            try {
                // Check if email service files exist before attempting to send
                $email_service_path = __DIR__ . '/email_service.php';
                $email_config_path = __DIR__ . '/../config/email_config.php';
                
                if (file_exists($email_service_path) && file_exists($email_config_path)) {
                    require_once $email_service_path;
                    
                    // Check if email configuration is properly set up
                    if (defined('SMTP_USERNAME') && SMTP_USERNAME !== 'your-email@gmail.com') {
                        $emailService = new EmailService();
                        
                        if ($status === 'confirmed') {
                            $emailResult = $emailService->sendBookingConfirmation($booking_id);
                        } else if ($status === 'cancelled') {
                            $emailResult = $emailService->sendBookingCancellation($booking_id);
                        }
                        
                        // Log email result but don't fail the booking update if email fails
                        if (isset($emailResult) && !$emailResult['success']) {
                            error_log("G-Arena: Email notification failed for booking {$booking_id}: " . $emailResult['message']);
                        } else if (isset($emailResult) && $emailResult['success']) {
                            error_log("G-Arena: Email notification sent successfully for booking {$booking_id}");
                        }
                    } else {
                        error_log("G-Arena: Email not configured - skipping email notification for booking {$booking_id}");
                    }
                } else {
                    error_log("G-Arena: Email service files not found - skipping email notification for booking {$booking_id}");
                }
            } catch (Exception $e) {
                error_log("G-Arena: Email service error for booking {$booking_id}: " . $e->getMessage());
            } catch (Error $e) {
                error_log("G-Arena: Email service fatal error for booking {$booking_id}: " . $e->getMessage());
            }
        }
        
        return ['success' => true, 'message' => 'Booking status updated successfully'];
    } else {
        error_log("G-Arena: Failed to update booking {$booking_id} status to {$status}");
        return ['success' => false, 'message' => 'Failed to update booking status'];
    }
}

function generateBookingReference()
{
    return 'GA' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function getAllBookingsWithDetails()
{
    $sql = "SELECT b.*, 
                   s.station_name as primary_station_name, 
                   s.station_type as primary_station_type,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           (SELECT GROUP_CONCAT(gs.station_name SEPARATOR ', ') 
                            FROM booking_stations bs2 
                            JOIN gaming_stations gs ON bs2.station_id = gs.id 
                            WHERE bs2.booking_id = b.id)
                       ELSE 
                           s.station_name 
                   END as station_name,
                   CASE 
                       WHEN b.booking_type = 'multi_station' THEN 
                           CONCAT('Multi-Station (', b.station_count, ' stations)')
                       ELSE 
                           s.station_type 
                   END as station_type,
                   u.email as user_email 
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC";

    return fetchAllRows($sql);
}

// Function to get all stations for a specific booking
function getBookingStations($booking_id)
{
    $booking_id = escapeString($booking_id);

    $sql = "SELECT bs.station_id, s.station_name, s.station_type, s.hourly_rate
            FROM booking_stations bs 
            JOIN gaming_stations s ON bs.station_id = s.id 
            WHERE bs.booking_id = '$booking_id'
            ORDER BY s.station_name";

    return fetchAllRows($sql);
}

// Function to get booking details with all stations
function getBookingWithStations($booking_id)
{
    $booking_id = escapeString($booking_id);
    
    // Get main booking details
    $sql = "SELECT b.*, s.station_name as primary_station_name, s.station_type as primary_station_type
            FROM bookings b 
            JOIN gaming_stations s ON b.station_id = s.id 
            WHERE b.id = '$booking_id'";
    
    $booking = fetchSingleRow($sql);
    
    if ($booking && $booking['booking_type'] === 'multi_station') {
        // Get all stations for multi-station booking
        $booking['stations'] = getBookingStations($booking_id);
        $booking['station_names'] = array_column($booking['stations'], 'station_name');
    }
    
    return $booking;
}
