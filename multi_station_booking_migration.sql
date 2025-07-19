-- Multi-Station Booking System Migration
-- This migration allows users to book multiple stations for the same time slot in a single booking
-- Run these commands to update your database for multi-station booking functionality
-- This script is safe to run multiple times

USE gaming_arena;

-- 1. Create a new booking_stations table to store multiple stations per booking
CREATE TABLE IF NOT EXISTS booking_stations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    station_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_station (booking_id, station_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_station_id (station_id)
);

-- 2. Check if columns exist before adding them to avoid errors
-- Add booking_type column if it doesn't exist
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'gaming_arena' 
               AND TABLE_NAME = 'bookings' 
               AND COLUMN_NAME = 'booking_type') = 0,
              'ALTER TABLE bookings ADD COLUMN booking_type ENUM(''single_station'', ''multi_station'') DEFAULT ''single_station'' AFTER total_amount',
              'SELECT "booking_type column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add station_count column if it doesn't exist
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'gaming_arena' 
               AND TABLE_NAME = 'bookings' 
               AND COLUMN_NAME = 'station_count') = 0,
              'ALTER TABLE bookings ADD COLUMN station_count INT DEFAULT 1 AFTER booking_type',
              'SELECT "station_count column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Make station_id nullable for multi-station bookings (keep for backward compatibility)
-- Only modify if it's still NOT NULL
SET @sql = IF((SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'gaming_arena' 
               AND TABLE_NAME = 'bookings' 
               AND COLUMN_NAME = 'station_id') = 'NO',
              'ALTER TABLE bookings MODIFY COLUMN station_id INT NULL',
              'SELECT "station_id already nullable"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Create indexes if they don't exist
-- Create booking_type index
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE TABLE_SCHEMA = 'gaming_arena' 
               AND TABLE_NAME = 'bookings' 
               AND INDEX_NAME = 'idx_booking_type') = 0,
              'CREATE INDEX idx_booking_type ON bookings(booking_type)',
              'SELECT "idx_booking_type already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create station_count index
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE TABLE_SCHEMA = 'gaming_arena' 
               AND TABLE_NAME = 'bookings' 
               AND INDEX_NAME = 'idx_station_count') = 0,
              'CREATE INDEX idx_station_count ON bookings(station_count)',
              'SELECT "idx_station_count already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Create a view for easy querying of booking data with stations
CREATE VIEW booking_with_stations_view AS
SELECT 
    b.id as booking_id,
    b.user_id,
    b.booking_date,
    b.start_time,
    b.end_time,
    b.total_hours,
    b.total_amount,
    b.status,
    b.booking_reference,
    b.notes,
    b.booking_type,
    b.station_count,
    b.created_at,
    b.updated_at,
    -- For single station bookings (legacy)
    CASE 
        WHEN b.booking_type = 'single_station' THEN b.station_id
        ELSE NULL 
    END as primary_station_id,
    -- For multi-station bookings
    bs.station_id as additional_station_id,
    -- Station info
    gs_primary.station_name as primary_station_name,
    gs_primary.station_type as primary_station_type,
    gs_primary.hourly_rate as primary_station_rate,
    gs_additional.station_name as additional_station_name,
    gs_additional.station_type as additional_station_type,
    gs_additional.hourly_rate as additional_station_rate,
    -- User info
    u.username,
    u.full_name,
    u.email as user_email
FROM bookings b
LEFT JOIN booking_stations bs ON b.id = bs.booking_id
LEFT JOIN gaming_stations gs_primary ON b.station_id = gs_primary.id
LEFT JOIN gaming_stations gs_additional ON bs.station_id = gs_additional.id
LEFT JOIN users u ON b.user_id = u.id;

-- 6. Create a procedure to migrate existing single bookings to the new structure
DELIMITER $$
CREATE PROCEDURE MigrateExistingSingleBookings()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE booking_id INT;
    DECLARE station_id INT;
    
    -- Cursor for existing bookings that have a station_id
    DECLARE booking_cursor CURSOR FOR 
        SELECT id, station_id
        FROM bookings 
        WHERE station_id IS NOT NULL 
        AND booking_type = 'single_station';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN booking_cursor;
    read_loop: LOOP
        FETCH booking_cursor INTO booking_id, station_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- For single station bookings, we don't need to add to booking_stations
        -- as the station_id is already in the bookings table
        -- Just ensure the booking_type and station_count are correct
        UPDATE bookings 
        SET booking_type = 'single_station', station_count = 1 
        WHERE id = booking_id;
        
    END LOOP;
    CLOSE booking_cursor;
END$$
DELIMITER ;

-- 7. Run the migration for existing bookings
CALL MigrateExistingSingleBookings();

-- 8. Clean up the migration procedure
DROP PROCEDURE MigrateExistingSingleBookings;

-- 9. Insert sample multi-station booking for testing (if user and stations exist)
INSERT INTO bookings (
    user_id, station_id, booking_date, start_time, end_time, 
    total_hours, total_amount, status, booking_reference, notes, 
    booking_type, station_count
)
SELECT 1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '18:00:00', 
       4.0, 70.00, 'pending', CONCAT('GA', DATE_FORMAT(NOW(), '%Y%m%d'), '8888'), 
       'Sample multi-station booking - 2 stations for 2 hours each', 'multi_station', 2
WHERE EXISTS (SELECT 1 FROM users WHERE id = 1) 
AND EXISTS (SELECT 1 FROM gaming_stations WHERE id = 1)
LIMIT 1;

-- Get the last inserted booking ID for sample additional stations
SET @last_booking_id = LAST_INSERT_ID();

-- Insert additional stations for the multi-station booking
INSERT INTO booking_stations (booking_id, station_id)
SELECT @last_booking_id, 2
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 2) AND @last_booking_id > 0;

INSERT INTO booking_stations (booking_id, station_id)
SELECT @last_booking_id, 3
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 3) AND @last_booking_id > 0;

-- 10. Add constraints to ensure data integrity
ALTER TABLE bookings 
ADD CONSTRAINT chk_multi_station_consistency 
CHECK (
    (booking_type = 'single_station' AND station_id IS NOT NULL AND station_count = 1) OR
    (booking_type = 'multi_station' AND station_count > 1)
);

-- Migration Complete!
-- The system now supports both single station and multi-station bookings
-- - Single station: Uses station_id in bookings table (legacy support)
-- - Multi-station: Uses station_id as primary + additional stations in booking_stations table
-- - Same time slot, multiple stations for group gaming sessions
