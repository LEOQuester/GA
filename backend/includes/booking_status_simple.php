<?php
/**
 * Simple Booking Status Update Function (Without Email)
 * Use this temporarily if email service is causing issues
 */

function updateBookingStatusSimple($booking_id, $status)
{
    $booking_id = escapeString($booking_id);
    $status = escapeString($status);

    $sql = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";
    $result = executeQuery($sql);

    if ($result) {
        error_log("G-Arena: Booking {$booking_id} status updated to {$status} successfully (email disabled)");
        return ['success' => true, 'message' => 'Booking status updated successfully'];
    } else {
        error_log("G-Arena: Failed to update booking {$booking_id} status to {$status}");
        return ['success' => false, 'message' => 'Failed to update booking status'];
    }
}

// Temporarily replace the function to test without email
if (!function_exists('updateBookingStatusOriginal')) {
    
    // Save original function with a different name if needed
    function updateBookingStatusOriginal($booking_id, $status) {
        return updateBookingStatusSimple($booking_id, $status);
    }
    
    // Override the original function temporarily
    function updateBookingStatus($booking_id, $status) {
        return updateBookingStatusSimple($booking_id, $status);
    }
}
