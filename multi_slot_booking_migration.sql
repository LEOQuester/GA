-- Multi-Slot Booking System Migration
-- This migration allows users to book multiple slots in a single booking transaction
-- Run these commands to update your database for multi-slot booking functionality

-- 1. Create a new booking_slots table to store individual slots
CREATE TABLE booking_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    station_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_hours DECIMAL(3,1) NOT NULL,
    slot_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE,
    INDEX idx_booking_slot (booking_id),
    INDEX idx_station_date_time (station_id, booking_date, start_time, end_time)
);

-- 2. Modify the existing bookings table to serve as parent booking record
-- Remove single-slot specific columns and make them nullable for backward compatibility
ALTER TABLE bookings 
    MODIFY COLUMN station_id INT NULL,
    MODIFY COLUMN booking_date DATE NULL,
    MODIFY COLUMN start_time TIME NULL,
    MODIFY COLUMN end_time TIME NULL,
    ADD COLUMN booking_type ENUM('single', 'multiple') DEFAULT 'single' AFTER total_amount,
    ADD COLUMN total_slots INT DEFAULT 1 AFTER booking_type;

-- 3. Add a comment to clarify the new structure
ALTER TABLE bookings COMMENT = 'Parent booking record - individual slots stored in booking_slots table';
ALTER TABLE booking_slots COMMENT = 'Individual booking slots for multi-slot bookings';

-- 4. Create indexes for better performance
CREATE INDEX idx_booking_type ON bookings(booking_type);
CREATE INDEX idx_booking_date_status ON bookings(created_at, status);
CREATE INDEX idx_slot_date_time ON booking_slots(booking_date, start_time, end_time);

-- 5. Create a view for easy querying of all booking data (single and multi-slot)
CREATE VIEW booking_details_view AS
SELECT 
    b.id as booking_id,
    b.user_id,
    b.total_hours,
    b.total_amount,
    b.status,
    b.booking_reference,
    b.notes,
    b.booking_type,
    b.total_slots,
    b.created_at,
    b.updated_at,
    -- For single slot bookings (legacy)
    CASE 
        WHEN b.booking_type = 'single' THEN b.station_id
        ELSE NULL 
    END as legacy_station_id,
    CASE 
        WHEN b.booking_type = 'single' THEN b.booking_date
        ELSE NULL 
    END as legacy_booking_date,
    CASE 
        WHEN b.booking_type = 'single' THEN b.start_time
        ELSE NULL 
    END as legacy_start_time,
    CASE 
        WHEN b.booking_type = 'single' THEN b.end_time
        ELSE NULL 
    END as legacy_end_time,
    -- For multi-slot bookings
    bs.id as slot_id,
    bs.station_id as slot_station_id,
    bs.booking_date as slot_date,
    bs.start_time as slot_start_time,
    bs.end_time as slot_end_time,
    bs.slot_hours,
    bs.slot_amount,
    -- Station and user info
    gs.station_name,
    gs.station_type,
    gs.hourly_rate,
    u.username,
    u.full_name,
    u.email as user_email
FROM bookings b
LEFT JOIN booking_slots bs ON b.id = bs.booking_id
LEFT JOIN gaming_stations gs ON (
    CASE 
        WHEN b.booking_type = 'single' THEN b.station_id = gs.id
        ELSE bs.station_id = gs.id
    END
)
LEFT JOIN users u ON b.user_id = u.id;

-- 6. Create a procedure to migrate existing single bookings to the new structure
DELIMITER $$
CREATE PROCEDURE MigrateExistingBookings()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE booking_id INT;
    DECLARE station_id INT;
    DECLARE booking_date DATE;
    DECLARE start_time TIME;
    DECLARE end_time TIME;
    DECLARE total_hours DECIMAL(3,1);
    DECLARE total_amount DECIMAL(10,2);
    
    -- Cursor for existing bookings
    DECLARE booking_cursor CURSOR FOR 
        SELECT id, station_id, booking_date, start_time, end_time, total_hours, total_amount
        FROM bookings 
        WHERE station_id IS NOT NULL 
        AND booking_date IS NOT NULL
        AND booking_type = 'single';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN booking_cursor;
    read_loop: LOOP
        FETCH booking_cursor INTO booking_id, station_id, booking_date, start_time, end_time, total_hours, total_amount;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert slot record for existing booking
        INSERT INTO booking_slots (
            booking_id, station_id, booking_date, start_time, end_time, slot_hours, slot_amount
        ) VALUES (
            booking_id, station_id, booking_date, start_time, end_time, total_hours, total_amount
        );
        
    END LOOP;
    CLOSE booking_cursor;
END$$
DELIMITER ;

-- 7. Run the migration for existing bookings
CALL MigrateExistingBookings();

-- 8. Clean up the migration procedure
DROP PROCEDURE MigrateExistingBookings;

-- 9. Insert sample multi-slot booking for testing (if admin user exists)
INSERT INTO bookings (user_id, total_hours, total_amount, status, booking_reference, notes, booking_type, total_slots)
SELECT 1, 4.0, 50.00, 'pending', CONCAT('GA', DATE_FORMAT(NOW(), '%Y%m%d'), '9999'), 'Sample multi-slot booking', 'multiple', 2
WHERE EXISTS (SELECT 1 FROM users WHERE id = 1)
LIMIT 1;

-- Get the last inserted booking ID for sample slots
SET @last_booking_id = LAST_INSERT_ID();

-- Insert sample slots for the multi-slot booking (if stations exist)
INSERT INTO booking_slots (booking_id, station_id, booking_date, start_time, end_time, slot_hours, slot_amount)
SELECT @last_booking_id, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:00:00', 2.0, 30.00
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 1) AND @last_booking_id > 0;

INSERT INTO booking_slots (booking_id, station_id, booking_date, start_time, end_time, slot_hours, slot_amount)
SELECT @last_booking_id, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '18:00:00', 2.0, 20.00
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 2) AND @last_booking_id > 0;

-- 10. Add constraints to ensure data integrity
ALTER TABLE bookings 
ADD CONSTRAINT chk_booking_type_consistency 
CHECK (
    (booking_type = 'single' AND station_id IS NOT NULL AND booking_date IS NOT NULL) OR
    (booking_type = 'multiple' AND station_id IS NULL AND booking_date IS NULL)
);

-- Migration Complete!
-- The system now supports both single and multi-slot bookings
-- Existing single bookings remain functional
-- New bookings can have multiple slots per booking transaction
