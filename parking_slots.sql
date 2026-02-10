-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 04:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parkingsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

CREATE TABLE `parking_slots` (
  `id` int(11) NOT NULL,
  `slot_number` varchar(10) NOT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `slot_type` enum('regular','premium','ev','disabled') DEFAULT 'regular',
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `hourly_rate` decimal(10,2) DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`id`, `slot_number`, `zone`, `slot_type`, `status`, `hourly_rate`, `created_at`) VALUES
(1, 'A01', 'Zone A', 'regular', 'available', 4.00, '2026-01-06 15:30:56'),
(2, 'A02', 'Zone A', 'regular', 'available', 4.00, '2026-01-06 15:30:56'),
(3, 'A03', 'Zone A', 'regular', 'available', 4.00, '2026-01-06 15:30:56'),
(4, 'B01', 'Zone B', 'premium', 'available', 6.00, '2026-01-06 15:30:56'),
(5, 'B02', 'Zone B', 'premium', 'available', 6.00, '2026-01-06 15:30:56'),
(6, 'C01', 'Zone C', 'ev', 'available', 7.00, '2026-01-06 15:30:56'),
(7, 'D01', 'Zone D', 'disabled', 'available', 3.50, '2026-01-06 15:30:56'),
(8, 'E01', 'Zone E', 'regular', 'available', 5.00, '2026-01-06 15:30:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slot_number` (`slot_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
