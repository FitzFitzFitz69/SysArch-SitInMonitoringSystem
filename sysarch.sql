-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 02:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sysarch`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `date_posted` datetime DEFAULT current_timestamp(),
  `posted_by` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `posted_by`, `status`) VALUES
(1, '1ST ANNOUNCEMENT', 'Hello World\r\n', '2025-03-06 11:06:59', '00', 'active'),
(2, 'New Tickets in for TYLER, THE CREATOR Concert', 'BUY NOW\r\n', '2025-03-07 00:59:01', '00', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_records`
--

CREATE TABLE `sit_in_records` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `date_in` datetime DEFAULT current_timestamp(),
  `date_out` datetime DEFAULT NULL,
  `language_used` enum('C#','C','Java','ASP.Net','PHP') NOT NULL,
  `status` enum('active','completed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `idno` varchar(20) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `midname` varchar(50) DEFAULT NULL,
  `course` varchar(50) NOT NULL,
  `yearlvl` tinyint(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') DEFAULT 'active',
  `remaining_sessions` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `idno`, `lastname`, `firstname`, `midname`, `course`, `yearlvl`, `email`, `photo`, `password`, `role`, `status`, `remaining_sessions`) VALUES
(1, '1', 'Cutad', 'Arnold', 'Alamo', '1', 4, 'cutadalamo@gmail.com', NULL, '$2y$10$lbPgxaQZkm936QMod2mA3.oUrpnUu7SyM72IHYpXS27.M/qX/kyQ6', 'student', 'active', 10),
(4, '999', 'West', 'Kanye', 'Omari', '2', 4, 'iamtheGOD@yahoo.com', '67c90e0a68c65.jpg', '$2y$10$dInB.FQo/sz9.IEO9xR48uAv706MfzgSEZNyEcoQeQv38giQXfnm2', 'student', 'active', 10),
(7, '00', 'Admin', 'Admin', '', '1', 1, 'admin@example.com', '67c90e2e97c8f.jpg', '$2y$10$dY93JakZvUrDSKZu5hqANeJ6wrCzuGpN05pziFDgBG.AksfpTBIjm', 'student', 'active', 10),
(8, '143', 'Gulfan', 'Charlene', 'June', '2', 4, 'cjg@gmail.com', '67caa5f1b6705.jpg', '$2y$10$6Azhb6xQgxBmPh44fO5fM.wo6ciGRDnMbKL.kwWtTFiDa1dqH8b9O', 'student', 'active', 10);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sit_in_records`
--
ALTER TABLE `sit_in_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idno` (`idno`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sit_in_records`
--
ALTER TABLE `sit_in_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sit_in_records`
--
ALTER TABLE `sit_in_records`
  ADD CONSTRAINT `sit_in_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`idno`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
