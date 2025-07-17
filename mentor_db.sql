-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 11:39 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mentor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  `type` enum('Departmental','General') DEFAULT NULL,
  `semester` enum('Fall','Spring') DEFAULT NULL,
  `specialty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `name`, `description`, `credits`, `type`, `semester`, `specialty_id`) VALUES
(2, 'SWE123WEB', 'Web Programming', '', 4, 'Departmental', 'Spring', 3),
(3, 'NWS101', 'Cyber Criminology', '', 8, 'Departmental', 'Spring', 4),
(4, 'SWE123APPDEV', 'Android Programming', '', 7, 'Departmental', 'Spring', 3),
(5, 'SWE123APPDEV', 'Android Programming 2', '', 4, 'Departmental', 'Spring', 3);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('enrolled','completed','dropped') NOT NULL DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_date`, `status`) VALUES
(1, 7, 2, '2025-07-08', 'enrolled'),
(2, 2, 2, '2025-07-09', 'enrolled'),
(3, 8, 3, '2025-07-09', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `assessment_type` enum('CA','Exam') NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `status` enum('Passed','Failed') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `enrollment_id`, `assessment_type`, `score`, `grade`, `status`) VALUES
(1, 1, 'CA', '28.00', 'A', 'Passed'),
(3, 3, 'CA', '29.00', 'F', 'Failed');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `level` enum('HND','Bachelor','Master') DEFAULT NULL,
  `specialty_id` int(11) DEFAULT NULL,
  `program_code` varchar(100) NOT NULL,
  `duration_years` int(11) DEFAULT 3,
  `degree_type` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `description` text DEFAULT NULL,
  `total_credits` int(11) DEFAULT NULL,
  `tuition_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `program_name`, `school_id`, `level`, `specialty_id`, `program_code`, `duration_years`, `degree_type`, `status`, `description`, `total_credits`, `tuition_fee`) VALUES
(1, 'Higher National Diploma', NULL, 'HND', NULL, 'AUTO-CODE-0001', 3, NULL, 'Active', NULL, NULL, NULL),
(2, 'Bachelor of Technology', NULL, 'Bachelor', NULL, 'AUTO-CODE-0002', 3, NULL, 'Active', NULL, NULL, NULL),
(3, 'HND', 1, NULL, NULL, 'HND001', 2, 'Diploma', 'Active', 'Testing', 64, '2800000.00'),
(4, 'Bachelor\'s Degree', 1, NULL, NULL, 'BTech25', 1, 'Bachelor\'s', 'Active', 'Top up your diploma with a one year professional bachelors', 12, '4500000.00');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `description`, `image`) VALUES
(1, 'School of Computer Engineering', 'Focuses on the design, development, and application of computer hardware and software systems.', NULL),
(3, 'School of Management', 'Offers programs in leadership, organizational behavior, human resources, and strategic management.', NULL),
(4, 'School of Medical and Biomedical Sciences', 'Dedicated to advancing health sciences through research, education, and clinical practice.', NULL),
(5, 'School of Communication', 'Explores various aspects of human communication, media, journalism, and public relations.', NULL),
(6, 'School of Home Economics', 'Focuses on the study of home and family life, consumer sciences, nutrition, and textiles.', NULL),
(7, 'School of Electronics', 'Deals with all sorts of electronics', '');

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialties`
--

INSERT INTO `specialties` (`id`, `name`, `description`, `school_id`) VALUES
(3, 'Software Engineering', 'Develop web and mobile applications', 1),
(4, 'Network Security', 'Get the most out of our cybersecurity program', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transcripts`
--

CREATE TABLE `transcripts` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `matricule_number` varchar(100) NOT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `specialty_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `matricule_number`, `phone_number`, `profile_image`, `bio`, `role`, `specialty_id`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@mentor.edu', '5f4dcc3b5aa765d61d8327deb882cf99', 'Admin123', NULL, NULL, NULL, 'admin', NULL, '2025-07-07 12:00:00', '2025-07-07 12:00:00'),
(2, 'Dr Alchemy', 'alchemy@gmail.com', '$2y$10$beJIW/24hQy7cX8Tsgb9b.wgXzGY8T66gsB/QVWg5vzRMFz0vqHr2', 'YIBS123', '679403531', NULL, '', 'student', 3, '2025-07-08 00:14:14', '2025-07-14 09:33:00'),
(7, 'Kanjo Elkamira Ndi', 'kanjoel39@gmail.com', '$2y$10$QKYa0bL0TbpAEdo7QaFkluqYExjXlOYQRwFUvhP4nhucMviRuyUHe', 'YIBS1234', '679403530', NULL, NULL, 'student', 3, '2025-07-08 14:08:15', '2025-07-08 14:08:15'),
(8, 'Asanga Sheikina', 'asanga@gmail.com', '$2y$10$/jJHcE7IxjKEfienOdHN/OUOBH.5H2zq86CcSJjyKis7pCIMpMQQK', 'YIBS12345', '679403531', NULL, NULL, 'student', 4, '2025-07-09 11:49:13', '2025-07-09 11:49:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `specialty_id` (`specialty_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_code` (`program_code`),
  ADD KEY `specialty_id` (`specialty_id`),
  ADD KEY `programs_school_fk` (`school_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `transcripts`
--
ALTER TABLE `transcripts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricule_number` (`matricule_number`),
  ADD KEY `specialty_id` (`specialty_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `specialties`
--
ALTER TABLE `specialties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transcripts`
--
ALTER TABLE `transcripts`
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
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`);

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`),
  ADD CONSTRAINT `programs_school_fk` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `specialties`
--
ALTER TABLE `specialties`
  ADD CONSTRAINT `specialties_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `transcripts`
--
ALTER TABLE `transcripts`
  ADD CONSTRAINT `transcripts_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
