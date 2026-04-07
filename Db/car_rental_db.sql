-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 06:32 PM
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
-- Database: `car_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cars`
--

CREATE TABLE `tbl_cars` (
  `id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Available',
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cars`
--

INSERT INTO `tbl_cars` (`id`, `model`, `type`, `status`, `daily_rate`, `image`) VALUES
(3, 'Toyota Innova', 'Family', 'Available', 1450.00, 'toyota_innova.webp\r\n'),
(4, 'Toyota Wigo', 'Family', 'Available', 800.00, 'toyota_wigo.jpg'),
(5, 'BMW M4', 'Luxury', 'Available', 9000.00, 'bmw_m4.avif'),
(6, 'Tesla Model V3', 'Business', 'Available', 2250.00, 'tesla_v3.jpg'),
(8, 'Toyota LandCruiser Prado', 'Camping', 'Available', 5000.00, '1773222960_toyota_landcruiser_prado.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'Customer',
  `display_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthday` varchar(30) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `password`, `role`, `display_name`, `first_name`, `middle_name`, `last_name`, `birthday`, `email`, `contact_number`, `address`, `gender`, `profile_image`) VALUES
(12, 'NarutoUzumaki', '$2y$10$9Tl8HVw8FneIjaOuQGtAKOC0.Em2s8gGoq1a/0PGonX67bCR/sMP6', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(13, 'SasukeUchiha', '$2y$10$4CLHC9fBdLC1k0MGG4oFae2.8h1X/K1YyIjd37dOqzs2P3QxNb88m', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(14, 'HiashiHyuga', '$2y$10$2bKLh2VksxzvRcEDC16rbefSPZHySTVX2HjNEO52dBFI2qSmkBTem', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(15, 'InoYamanaka', '$2y$10$USJEvUMP6fiMXzvV3yeZnuHuBrQ40xEHM8bhTdBhtd769p8zNjAm6', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(16, 'Dan Carlo', '$2y$10$TZRHGiBYCrJXRqkcjWD92ehAvpg0G3Xz3/QUnWpDT8KRTMnuw3EHe', 'ADMIN', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(17, 'Josh Ngalis', '$2y$10$0ZYcsQ/uhqMa1cqBwOG/hOba6UupfMcSvXjorGi8iCMr95sd/UBxC', 'STAFF', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(18, 'David Pomeranz', '$2y$10$lGkcSvxmbuYVZTJY3ro2Q.hHACkECG3HXqOkpPG0C6DzbwF234Vz6', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(19, 'Shaun', '$2y$10$C5qGba/NYNlZoBf/TPc2tegUADTL48Mz9USnOgXkb3b7ckvydxeFG', 'ADMIN', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(20, 'Administrator', '$2y$10$7NQep1I9FWqZVg8bg8GwKuhpH43QLk34SZVcZ8n7o7ahOfMIgQx5i', 'ADMIN', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(21, 'Staff', '$2y$10$5SSgsQbdurTYsZ3RQ8xn2OGOCM5E8P.k.ruaWLSYMC44JscDsePUa', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(22, 'Customer', '$2y$10$M5LX5cbXo.a474T8B1eNEusO2Kzrd9NYBpCo9uxHSqFHazBMemzR6', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(23, 'qwertyuiop', '$2y$10$CNQFTrxOyS12zoDrhFijWeCUxXTnhDcqVVGU1OUdPsxOymVTWOcU2', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(24, '1234', '$2y$10$cfe5.giJq/IBA9PFmIrSjeYKQHyCrwS/uYKKbp2Juut.o1ihZbNyK', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(25, 'reg', '$2y$10$1JdQRkb0TDIyKkYB49hPMe7.tJFstfWIuNMA4abrMVwMep.VvfhQO', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(26, 'rreeg', '$2y$10$NfciH/n4umxJRrCLG.EmPemd5DACj.y17XFS94sxHcA62B8w4AUWy', 'Customer', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(27, '12345', '$2y$10$/NLqS5Ydr1eF5IQYYaEAwevCioM22E8dwZ1JWj8x.eFHCzOJJtIZ2', 'STAFF', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL),
(28, '123admin', '$2y$10$k73XkKOgLTT5NFpj4rWDb.x0/IyVA55nXpVuDI.H5AFwbI9bgy.Zu', 'ADMIN', 'Admin', 'Mr.', '', 'Admin', '', '', '', '', '', 'admin_28_1773229081.png'),
(30, '123staff', '$2y$10$.SSVFLKSGg/ZzUkPt.cdTOI2YaRSxXVM4eiokZBC6qVRk1iqpDEV.', 'STAFF', 'Homer', 'Homer', '', 'Simpson', '', '', '123', 'Grove Street, San Andreas, USA', '', 'user_30_1773228559.png'),
(31, '123customer', '$2y$10$3/Zmiq7jD4erMsXPYzBQpuJiOSKCcED57EMsXYi3rPcdbJ1IbFdRy', 'Customer', 'Customer', 'Cus', 'To', 'Mer', '', '', '', '', '', 'cust_31_1773229585.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_cars`
--
ALTER TABLE `tbl_cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cars`
--
ALTER TABLE `tbl_cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
