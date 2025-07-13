-- Migration to change unavailable_slots from station-specific to arena-wide
-- Run these commands to update your database structure

-- 1. Drop the current unavailable_slots table
DROP TABLE IF EXISTS unavailable_slots;

-- 2. Create a new arena-wide unavailable_slots table
CREATE TABLE unavailable_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    unavailable_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    reason VARCHAR(255) DEFAULT 'Arena maintenance',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date_time (unavailable_date, start_time, end_time)
);

-- 3. Insert some sample arena-wide unavailable slots for testing
INSERT INTO unavailable_slots (unavailable_date, start_time, end_time, reason) VALUES
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:00:00', 'Arena-wide maintenance'),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00', 'System upgrade'),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', '20:00:00', 'Private event'),
(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', '11:00:00', 'Deep cleaning'),
(DATE_ADD(CURDATE(), INTERVAL 10 DAY), '16:00:00', '18:00:00', 'Equipment maintenance');

-- Note: These slots now represent times when the ENTIRE ARENA is unavailable
-- All gaming stations are affected during these periods
