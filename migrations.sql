-- Migration to simplify booking system
-- Run these commands to update your database structure

-- 1. Drop the complex station_availability table
DROP TABLE IF EXISTS station_availability;

-- 2. Create a simple unavailable_slots table
CREATE TABLE unavailable_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    station_id INT NOT NULL,
    unavailable_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    reason VARCHAR(255) DEFAULT 'Maintenance',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE,
    INDEX idx_station_date (station_id, unavailable_date),
    INDEX idx_date_time (unavailable_date, start_time, end_time)
);

-- 3. Insert some sample unavailable slots for testing
-- Only insert if corresponding stations exist
INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:00:00', 'Scheduled maintenance'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 1);

INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00', 'Hardware upgrade'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 1);

-- Station 2 unavailable
INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '18:00:00', 'Tournament setup'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 2);

INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '11:00:00', 'Cleaning'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 2);

-- Station 3 unavailable
INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '20:00:00', 'Private event'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 3);

INSERT INTO unavailable_slots (station_id, unavailable_date, start_time, end_time, reason) 
SELECT 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00', '14:00:00', 'System update'
WHERE EXISTS (SELECT 1 FROM gaming_stations WHERE id = 3);

-- Note: Business hours are hardcoded in UI as 9:00 AM to 8:00 PM (20:00)
-- Date selection is limited to next 5 days from current date
-- Past dates are automatically disabled in the frontend
