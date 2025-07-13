-- Gaming Arena Database Structure
-- Run this SQL script in your MySQL database

CREATE DATABASE IF NOT EXISTS gaming_arena;
USE gaming_arena;

-- Admin table (for hardcoded admin credentials)
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Gaming stations table
CREATE TABLE gaming_stations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    station_name VARCHAR(100) NOT NULL,
    station_type VARCHAR(50) NOT NULL,
    description TEXT,
    hourly_rate DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Station availability table (defines available time slots)
CREATE TABLE station_availability (
    id INT PRIMARY KEY AUTO_INCREMENT,
    station_id INT NOT NULL,
    available_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_station_time (station_id, available_date, start_time)
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_hours DECIMAL(3,1) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES gaming_stations(id) ON DELETE CASCADE
);

-- No default admin user - use the secret registration page to create admin accounts

-- Insert sample gaming stations
INSERT INTO gaming_stations (station_name, station_type, description, hourly_rate) VALUES
('Gaming Station 1', 'PC Gaming', 'High-end PC with RTX 4080, 32GB RAM, perfect for AAA games', 15.00),
('Gaming Station 2', 'PC Gaming', 'Mid-range PC with RTX 3070, 16GB RAM, great for most games', 12.00),
('Gaming Station 3', 'Console Gaming', 'PlayStation 5 with 4K TV and premium audio setup', 10.00),
('Gaming Station 4', 'Console Gaming', 'Xbox Series X with large screen and surround sound', 10.00),
('Gaming Station 5', 'VR Gaming', 'Meta Quest 3 VR setup with dedicated play area', 20.00);

-- Insert sample availability for the next 30 days (9 AM to 11 PM)
DELIMITER $$
CREATE PROCEDURE PopulateAvailability()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE station_id INT;
    DECLARE date_counter DATE DEFAULT CURDATE();
    DECLARE end_date DATE DEFAULT DATE_ADD(CURDATE(), INTERVAL 30 DAY);
    DECLARE hour_counter INT;
    
    DECLARE station_cursor CURSOR FOR SELECT id FROM gaming_stations;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    WHILE date_counter <= end_date DO
        OPEN station_cursor;
        station_loop: LOOP
            FETCH station_cursor INTO station_id;
            IF done THEN
                LEAVE station_loop;
            END IF;
            
            SET hour_counter = 9;
            WHILE hour_counter <= 22 DO
                INSERT INTO station_availability (station_id, available_date, start_time, end_time)
                VALUES (station_id, date_counter, 
                       CONCAT(hour_counter, ':00:00'), 
                       CONCAT(hour_counter + 1, ':00:00'));
                SET hour_counter = hour_counter + 1;
            END WHILE;
        END LOOP;
        CLOSE station_cursor;
        SET done = FALSE;
        SET date_counter = DATE_ADD(date_counter, INTERVAL 1 DAY);
    END WHILE;
END$$
DELIMITER ;

CALL PopulateAvailability();
DROP PROCEDURE PopulateAvailability;
