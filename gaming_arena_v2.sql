-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Jul 13, 2025 at 02:00 PM
-- Server version: 10.1.19-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gaming_arena_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(2, 'admin', '$2y$10$XKTVusF41fr.Wnr2OouMVeSi8JcPjUnIxNNRxZNhXsQTeJ9hx/Xgu', 'admin@gmail.com', '2025-07-13 10:25:25');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_hours` decimal(3,1) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `booking_reference` varchar(20) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `station_id`, `booking_date`, `start_time`, `end_time`, `total_hours`, `total_amount`, `status`, `booking_reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-07-15', '09:00:00', '13:00:00', '4.0', '48.00', 'confirmed', 'GA202507136397', '', '2025-07-13 10:43:29', '2025-07-13 10:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `gaming_stations`
--

CREATE TABLE `gaming_stations` (
  `id` int(11) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `station_type` varchar(50) NOT NULL,
  `description` text,
  `hourly_rate` decimal(10,2) NOT NULL,
  `status` enum('active','maintenance','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gaming_stations`
--

INSERT INTO `gaming_stations` (`id`, `station_name`, `station_type`, `description`, `hourly_rate`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Gaming Station 1', 'PC Gaming', 'High-end PC with RTX 4080, 32GB RAM, perfect for AAA games', '15.00', 'active', '2025-07-13 09:58:35', '2025-07-13 09:58:35'),
(2, 'Gaming Station 2', 'PC Gaming', 'Mid-range PC with RTX 3070, 16GB RAM, great for most games', '12.00', 'active', '2025-07-13 09:58:35', '2025-07-13 09:58:35'),
(3, 'Gaming Station 3', 'Console Gaming', 'PlayStation 5 with 4K TV and premium audio setup', '10.00', 'active', '2025-07-13 09:58:35', '2025-07-13 09:58:35'),
(4, 'Gaming Station 4', 'Console Gaming', 'Xbox Series X with large screen and surround sound', '10.00', 'active', '2025-07-13 09:58:35', '2025-07-13 09:58:35'),
(5, 'Gaming Station 5', 'VR Gaming', 'Meta Quest 3 VR setup with dedicated play area', '20.00', 'active', '2025-07-13 09:58:35', '2025-07-13 09:58:35');

-- --------------------------------------------------------

--
-- Table structure for table `unavailable_slots`
--

CREATE TABLE `unavailable_slots` (
  `id` int(11) NOT NULL,
  `unavailable_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `reason` varchar(255) DEFAULT 'Arena maintenance',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `unavailable_slots`
--

INSERT INTO `unavailable_slots` (`id`, `unavailable_date`, `start_time`, `end_time`, `reason`, `created_at`, `updated_at`) VALUES
(1, '2025-07-14', '14:00:00', '16:00:00', 'Arena-wide maintenance', '2025-07-13 11:23:26', '2025-07-13 11:23:26'),
(2, '2025-07-16', '10:00:00', '12:00:00', 'System upgrade', '2025-07-13 11:23:26', '2025-07-13 11:23:26'),
(3, '2025-07-18', '18:00:00', '20:00:00', 'Private event', '2025-07-13 11:23:26', '2025-07-13 11:23:26'),
(4, '2025-07-20', '09:00:00', '11:00:00', 'Deep cleaning', '2025-07-13 11:23:26', '2025-07-13 11:23:26'),
(5, '2025-07-23', '16:00:00', '18:00:00', 'Equipment maintenance', '2025-07-13 11:23:26', '2025-07-13 11:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `created_at`, `status`) VALUES
(1, 'Leo', 'induwaralakindu09@gmail.com', '$2y$10$MMf2zHddVCq.dGhdJk5czOLvWLNak/IVJZFiCsWs18DgCobxMVNoa', 'Induwara Lakindu', '0724943352', '2025-07-13 10:05:20', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `station_id` (`station_id`);

--
-- Indexes for table `gaming_stations`
--
ALTER TABLE `gaming_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unavailable_slots`
--
ALTER TABLE `unavailable_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_time` (`unavailable_date`,`start_time`,`end_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gaming_stations`
--
ALTER TABLE `gaming_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `unavailable_slots`
--
ALTER TABLE `unavailable_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `gaming_stations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
