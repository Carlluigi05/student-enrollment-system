-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2026 at 04:23 AM
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
-- Database: `school_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login`
--

CREATE TABLE `admin_login` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login`
--

INSERT INTO `admin_login` (`id`, `username`, `email`, `password`, `photo`, `created_at`, `last_login`, `is_online`, `last_activity`) VALUES
(12, 'Carl Luigi', 'carlluigi@gmail.com', '12345', '1763641161_baby-duck-lossy.jpeg', '2025-10-13 11:04:12', '2025-12-22 19:30:20', 0, '2025-12-22 20:17:51'),
(13, 'Richard', 'richard@gmail.com', 'richard123', '1767000594_jer.jpg', '2025-10-13 11:08:09', '2026-02-15 21:51:21', 1, '2026-02-15 22:19:25'),
(15, 'Vince', 'vince@gmail.com', '12345', '1763983952_pic.jpg', '2025-11-24 11:31:42', '2026-02-11 10:39:12', 0, '2026-02-11 10:59:21'),
(16, 'Mika', 'mika@gmail.com', 'mika123', '1764070746_admin-Mark.jpg', '2025-11-24 11:35:44', '2025-12-04 06:16:06', 0, '2025-12-04 06:37:46'),
(17, 'Aldren', 'aldren@gmail.com', 'aldren12345', '1764067551_admin-Kyle.jpg', '2025-11-24 11:50:31', '2025-12-29 17:21:47', 0, '2025-12-29 17:31:22'),
(22, 'Jerome', 'jerome@gmail.com', 'jerome123', '1765623163_jer.jpg', '2025-12-11 13:04:00', '2025-12-13 18:52:36', 0, '2025-12-13 19:46:34'),
(24, 'Mary Grace', 'marygrace@gmail.com', 'marygrace123', '1765626410_mary grace.jpg', '2025-12-13 11:44:47', '2025-12-13 19:46:44', 0, '2025-12-13 19:52:20'),
(25, 'bapbap', 'bapbap@gmail.com', '1234', '', '2025-12-29 09:43:55', '2025-12-29 17:44:42', 0, '2025-12-29 17:45:30'),
(26, 'Romae', 'romae@gmail.com', '12345', '', '2026-02-11 02:44:37', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `basic_enrollment_students`
--

CREATE TABLE `basic_enrollment_students` (
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `lrn` varchar(15) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `psa_birth_cert_no` varchar(30) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `mother_tongue` varchar(50) DEFAULT NULL,
  `current_address` varchar(150) DEFAULT NULL,
  `permanent_address` varchar(150) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_maiden_name` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `last_grade_completed` varchar(20) DEFAULT NULL,
  `last_school_attended` varchar(100) DEFAULT NULL,
  `last_school_id` varchar(20) DEFAULT NULL,
  `blended` tinyint(1) DEFAULT 0,
  `homeschooling` tinyint(1) DEFAULT 0,
  `modular_print` tinyint(1) DEFAULT 0,
  `modular_digital` tinyint(1) DEFAULT 0,
  `online` tinyint(1) DEFAULT 0,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `basic_enrollment_students`
--

INSERT INTO `basic_enrollment_students` (`student_id`, `school_year`, `lrn`, `grade_level`, `psa_birth_cert_no`, `last_name`, `first_name`, `middle_name`, `birthdate`, `age`, `sex`, `place_of_birth`, `religion`, `mother_tongue`, `current_address`, `permanent_address`, `father_name`, `mother_maiden_name`, `guardian_name`, `last_grade_completed`, `last_school_attended`, `last_school_id`, `blended`, `homeschooling`, `modular_print`, `modular_digital`, `online`, `date_submitted`, `created_by`) VALUES
(26, '2024-2025', '101943080005', 'Kindergarten', '03405-B14A408-9', 'San Pedro', 'Luna', 'Balesteros', '2018-02-04', 7, 'Male', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', 'Montenegro, Ramon P.', 'Guerero, Marites D.', 'Guerero, Marites D.', '', '', '', 1, 0, 1, 0, 0, '2025-12-03 23:11:49', 17),
(27, '2021-2022', '125618130123', 'Kindergarten', '04501-B14A501-6', 'Tiodoro', 'Gilbert', 'Tiozon', '2018-01-04', 7, 'Male', 'Dagupan, Pangasinan', 'Born Again', 'English, Tagalog, Pangasinan', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', 'Carlito B. Bautista', 'Dominica E. Aquino', 'Dominica E. Aquino', '', '', '', 1, 0, 0, 1, 0, '2025-12-03 23:13:17', 17),
(28, '2024-2025', NULL, 'Kindergarten', '04405-B14A408-5', 'Patricio', 'Miguel', 'Mindiola', '2016-04-01', 9, 'Male', 'Dagupan, Pangasinan', 'Roman Catholic', 'Tagalog, English', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', 'Patrio, Harold T.', 'Midniola, Mary Shane L.', 'Midniola, Mary Shane L.', '', '', '', 1, 0, 0, 0, 0, '2025-12-06 10:22:37', 12),
(30, '2023-2024', NULL, 'Kindergarten', '06401-C14A190-7', 'Amaya', 'Cathrine', 'Soto', '2018-07-05', 7, 'Female', 'Dagupan, Pangasinan', 'Iglesia Ni Cristo', 'English, Tagalog, Pangasinan', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Amaya, John Paul C.', 'Soto, Marina T.', 'Soto, Marina T.', '', '', '', 1, 0, 1, 0, 0, '2025-12-10 13:05:32', 13),
(31, '2023-2024', NULL, 'Kindergarten', '04501-C44A501-8', 'Tibule', 'Patricia', 'Laguardia', '2018-12-05', 7, 'Female', 'Calasiao, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', 'Blk 5 Lot 12, #25 Sampaguita St. Brgy. Maligaya, Quezon City', 'Blk 5 Lot 12, #25 Sampaguita St. Brgy. Maligaya, Quezon City', 'Tibule, Henrick P.', 'Laguardia, Princess K.', 'Laguardia, Princess K.', '', '', '', 1, 0, 0, 1, 0, '2025-12-10 13:11:30', 12),
(33, '2023-2024', NULL, 'Kindergarten', '06405-L15A488-8', 'Aquino', 'Carl Dominic', 'Erpelo', '2015-02-11', 10, 'Male', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '029B Zambale St. Baloling, Mapandan, Pangasinan', '029B Zambale St. Baloling, Mapandan, Pangasinan', 'Bautista, Carlito B.', 'Aquino, Dominica E.', 'Aquino, Dominica E.', '', '', '', 1, 0, 0, 0, 0, '2025-12-10 13:19:36', 15),
(34, '2025-2026', NULL, 'Kindergarten', '07501-Z14A501-10', 'Limliman', 'Jimuel', 'Villanueva', '2014-04-10', 11, 'Male', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', 'Limliman, Manuel L.', 'Villanueva, Shane T.', 'Villanueva, Shane T.', '', '', '', 1, 0, 1, 0, 0, '2025-12-11 11:20:54', 13),
(35, '2023-2024', NULL, 'Kindergarten', '04905-R14B418-2', 'Jones', 'Tommy', 'Pepito', '2019-03-08', 6, 'Male', 'Dagupan, Pangasinan', 'Iglesia Ni Cristo', 'English, Tagalog, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', 'Jones, Mark H.', 'Pepito, Hilda T.', 'Pepito, Hilda T.', '', '', '', 0, 0, 1, 0, 0, '2025-12-11 13:09:05', 22),
(36, '2024-2025', NULL, 'Kindergarten', '27501-Z14A501-9', 'Aquino', 'Allan', 'Frialde', '2018-02-22', 7, 'Male', 'Anda, Pangasinan', 'Iglesia Ni Cristo', 'English, Tagalog, Pangasinan', '029B AmolegSt. Pantal, Anda, Pangasinan', '029B AmolegSt. Pantal, Anda, Pangasinan', 'Aquino, Jomar P.', 'Frialde, Jeraldine S.', 'Frialde, Jeraldine S.', '', '', '', 0, 0, 0, 0, 1, '2025-12-12 11:59:39', 22),
(37, '2023-2024', NULL, 'Kindergarten', '04501-1L4A501-4', 'Gomez', 'Paulita', 'Guerero', '2015-07-17', 10, 'Female', 'Magaldan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', '649 R. Hidalgo Street, Barangay 307, Quiapo, City of Manila,', 'Gomez, Gilbert L.', 'Guerero, Mariz D.', 'Gomez, Gilbert L.', '', '', '', 1, 1, 1, 1, 1, '2025-12-13 11:49:06', 24),
(38, '2023-2024', NULL, 'Kindergarten', '01501-Z14A501-2', 'Piatos', 'Mary Grace', 'Marcos', '2012-04-13', 13, 'Female', 'San Jacinto, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', 'Piatos, Jefferry E.', 'Marcos, Liza T.', 'Marcos, Liza T.', '', '', '', 1, 0, 1, 1, 0, '2025-12-14 11:36:50', 17),
(39, '2024-2025', NULL, 'Kindergarten', '03405-B14A408-8', 'Morales', 'Jeric', 'Zapanta', '2015-02-01', 10, 'Male', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Montenegro, Ramon P.', 'Guerero, Marites D.', 'Guerero, Marites D.', '', '', '', 1, 0, 1, 0, 0, '2026-01-23 10:19:34', 15),
(40, '2025-2026', NULL, 'Kindergarten', '07501-P14A501-9', 'Serain', 'Bernie', 'Hedalgo', '2018-10-26', 7, 'Male', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Maria Santos, 123 Lopez Street, Brgy. Kapitan Kilyong, Quezon City', 'Henry M. Serain', 'Maria P. Hidalgo', 'Maria P. Hidalgo', '', '', '', 1, 0, 1, 0, 0, '2026-01-26 10:07:13', 15),
(41, '2025-2026', NULL, 'Kindergarten', '07501-Z14A501-102', 'Banzon', 'Juna', 'Ferrer', '2019-05-01', 6, 'Female', 'Mapandan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '029B Zambale St. Baloling, Mapandan, Pangasinan', '029B Zambale St. Baloling, Mapandan, Pangasinan', 'Aquino, Jomar P.', 'Dominica E. Aquino', 'Dominica E. Aquino', '', '', '', 1, 0, 1, 0, 0, '2026-02-10 10:30:37', 15),
(42, '2024-2025', NULL, 'Kindergarten', '07501-Z14A501-19', 'Dela Cruz', 'Juan', 'Pepito', '2018-02-01', 8, 'Male', 'Magaldan, Pangasinan', 'Roman Catholic', 'English, Tagalog, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', '029B AmolegSt. Pantal, Dagupan, Pangasinan', 'Jojie P. Muyano', 'Angel M. Mayoyo', 'Angel M. Mayoyo', '', '', '', 1, 0, 1, 0, 0, '2026-02-11 02:57:00', 13);

-- --------------------------------------------------------

--
-- Table structure for table `confirmation_slip_students`
--

CREATE TABLE `confirmation_slip_students` (
  `slip_id` int(11) NOT NULL,
  `learner_name` varchar(100) DEFAULT NULL,
  `lrn` varchar(15) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `confirmation_status` enum('YES','NO') DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `confirmation_slip_students`
--

INSERT INTO `confirmation_slip_students` (`slip_id`, `learner_name`, `lrn`, `grade_level`, `confirmation_status`, `date_submitted`, `created_by`) VALUES
(1, 'Aquino, John Mark L.', '101943080004', 'Grade 2', 'YES', '2025-10-28 02:37:52', 0),
(2, 'Aquino, John Mark L.', '101943080004', 'Grade 2', 'YES', '2025-10-28 02:42:13', 0),
(3, 'Aquino, John Mark L.', '101943080005', 'Grade 5', 'YES', '2025-10-28 02:46:13', 0),
(4, 'Lapuz, Erick K.', '10193209902', 'Grade 2', 'YES', '2025-10-28 02:48:59', 0),
(5, 'Deciles, Paul Ian P.', '101343054004', 'Grade 6', 'YES', '2025-10-28 02:52:32', 0),
(6, 'Micheal, James O.', '101943080004', 'Grade 5', 'YES', '2025-10-28 02:55:24', 0),
(7, 'Patricia, Anne P', '101943080004', 'Grade 3', 'YES', '2025-10-29 12:28:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `student_id`, `note`, `created_by`, `date_created`) VALUES
(36, 32, 'hellow', 13, '2025-12-17 10:40:51'),
(37, 38, 'Type Concern\r\n', 13, '2025-12-27 10:45:01'),
(38, 30, 'out', 13, '2025-12-29 09:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `student_transfers`
--

CREATE TABLE `student_transfers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_admin` int(11) NOT NULL,
  `to_admin` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `seen_by_sender` tinyint(1) DEFAULT 0,
  `seen_by_receiver` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_transfers`
--

INSERT INTO `student_transfers` (`id`, `student_id`, `from_admin`, `to_admin`, `status`, `created_at`, `updated_at`, `seen_by_sender`, `seen_by_receiver`) VALUES
(1, 28, 13, 17, 'accepted', '2025-12-08 10:37:51', '2025-12-08 10:38:17', 1, 0),
(2, 27, 13, 17, 'declined', '2025-12-08 10:40:34', '2025-12-08 10:40:39', 1, 0),
(3, 27, 13, 17, 'declined', '2025-12-08 10:40:56', '2025-12-08 10:41:23', 1, 0),
(4, 27, 13, 17, 'declined', '2025-12-08 10:42:37', '2025-12-08 10:43:21', 1, 0),
(5, 27, 13, 12, 'declined', '2025-12-09 00:38:56', '2025-12-09 00:39:13', 1, 0),
(6, 27, 13, 17, 'declined', '2025-12-09 04:33:18', '2025-12-09 04:33:42', 1, 0),
(7, 27, 13, 17, 'accepted', '2025-12-09 04:34:04', '2025-12-09 04:34:09', 1, 0),
(8, 26, 13, 17, 'accepted', '2025-12-09 06:04:33', '2025-12-09 06:04:55', 1, 0),
(9, 28, 17, 21, 'pending', '2025-12-09 06:06:59', NULL, 0, 0),
(10, 28, 17, 13, 'accepted', '2025-12-09 12:19:50', '2025-12-09 12:19:53', 1, 0),
(11, 32, 12, 13, 'accepted', '2025-12-10 13:20:44', '2025-12-10 13:20:53', 1, 0),
(12, 28, 13, 12, 'accepted', '2025-12-10 13:21:38', '2025-12-10 13:21:46', 1, 0),
(13, 36, 13, 22, 'accepted', '2025-12-12 12:01:01', '2025-12-12 12:01:07', 1, 0),
(14, 34, 13, 17, 'declined', '2025-12-13 13:38:59', '2025-12-13 13:39:03', 1, 0),
(15, 38, 13, 12, 'declined', '2025-12-22 11:30:41', '2025-12-22 11:30:52', 1, 0),
(16, 38, 13, 17, 'accepted', '2025-12-29 09:25:43', '2025-12-29 09:26:11', 1, 0),
(17, 39, 13, 15, 'declined', '2026-01-23 10:43:45', '2026-01-23 10:44:51', 1, 0),
(18, 41, 13, 15, 'accepted', '2026-02-10 10:32:40', '2026-02-10 10:33:11', 1, 0),
(19, 40, 13, 15, 'accepted', '2026-02-10 10:33:40', '2026-02-10 10:33:43', 1, 0),
(20, 39, 13, 15, 'accepted', '2026-02-11 02:39:48', '2026-02-11 02:40:40', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `superadmin_login`
--

CREATE TABLE `superadmin_login` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superadmin_login`
--

INSERT INTO `superadmin_login` (`id`, `username`, `email`, `password`, `photo`) VALUES
(1, 'Master Control', 'masterconsole@gmail.com', '12345', '1764803295_baby-duck-lossy.jpeg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login`
--
ALTER TABLE `admin_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `basic_enrollment_students`
--
ALTER TABLE `basic_enrollment_students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `confirmation_slip_students`
--
ALTER TABLE `confirmation_slip_students`
  ADD PRIMARY KEY (`slip_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_transfers`
--
ALTER TABLE `student_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `from_admin` (`from_admin`),
  ADD KEY `to_admin` (`to_admin`);

--
-- Indexes for table `superadmin_login`
--
ALTER TABLE `superadmin_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login`
--
ALTER TABLE `admin_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `basic_enrollment_students`
--
ALTER TABLE `basic_enrollment_students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `confirmation_slip_students`
--
ALTER TABLE `confirmation_slip_students`
  MODIFY `slip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `student_transfers`
--
ALTER TABLE `student_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `superadmin_login`
--
ALTER TABLE `superadmin_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
