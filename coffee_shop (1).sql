-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2026 at 08:42 AM
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
-- Database: `coffee_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Test User', 'test@email.com', 'Hello', 'This is a test message from the contact form.', '2026-04-10 10:27:20'),
(2, 'juls', 'julskristy8@gmail.com', 'General Inquiry', 'hello', '2026-04-10 11:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `pickup_time` varchar(50) NOT NULL,
  `coffee` varchar(255) DEFAULT NULL,
  `tea` varchar(255) DEFAULT NULL,
  `pastry` varchar(255) DEFAULT NULL,
  `pancake` varchar(255) DEFAULT NULL,
  `waffle` varchar(255) DEFAULT NULL,
  `toast` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quantity` int(5) NOT NULL DEFAULT 1,
  `delivery_method` varchar(20) NOT NULL DEFAULT 'pickup',
  `address` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `payment_phone` varchar(20) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `order_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `name`, `email`, `phone`, `pickup_time`, `coffee`, `tea`, `pastry`, `pancake`, `waffle`, `toast`, `notes`, `quantity`, `delivery_method`, `address`, `payment_method`, `payment_phone`, `payment_status`, `order_time`) VALUES
(1, 'Kristy Namuyiga', 'kristy@email.com', '+256779265906', '10:30 AM', 'Cappuccino', 'Green Tea', 'Croissant', NULL, NULL, NULL, 'Extra hot please!', 1, 'pickup', NULL, 'cash', NULL, 'pending', '2026-04-10 10:27:20'),
(2, 'kristy', 'julskristy8@gmail.com', '+256 700658019', '22:22', '', '', '', '', '', 'Cheese', '', 1, 'pickup', '', 'cash', NULL, 'pending', '2026-04-10 09:33:37'),
(3, 'Juls Kristy', 'julskristy8@gmail.com', '0700658019', '22:43', 'Americano', 'Black Tea', 'Bread', '', '', '', 'none', 1, 'delivery', '', 'Cash', NULL, 'pending', '2026-04-10 10:33:14'),
(4, 'Juls Kristy', 'julskristy8@gmail.com', '0700658019', '03:33', '', '', 'Croissant', '', '', '', '', 1, 'pickup', '', 'Cash', NULL, 'pending', '2026-04-10 11:47:20'),
(5, 'kristy', 'julskristy8@gmail.com', '+256 700658019', '03:33', '', 'Herbal Tea', '', '', '', 'Cinnamon', '', 1, 'pickup', '', 'Cash', NULL, 'pending', '2026-04-10 11:54:49'),
(6, 'Juls Kristy', 'julskristy8@gmail.com', '0700658019', '22:02', '', 'Herbal Tea', 'Chocolate Eclaire', '', '', '', '', 1, 'pickup', '', 'Cash', NULL, 'pending', '2026-04-10 11:59:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
