-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 11:31 AM
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
-- Database: `frågesport db`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `Achv_Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `Exercise_Id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Type` enum('true_false','mcq','match','ordering','fill_blank') NOT NULL,
  `Is_Template` tinyint(1) DEFAULT 0,
  `Created_By` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`Exercise_Id`, `Title`, `Description`, `Type`, `Is_Template`, `Created_By`) VALUES
(1, 'Tågresan', 'Albin skulle åka tåg till sin mormor...', 'true_false', 1, 1),
(2, 'Lisa i affären', 'Lisa gick till affären för att köpa frukt.', 'mcq', 1, 1),
(3, 'Den borttappade mössan', 'Sätt meningarna i rätt ordning.', 'ordering', 1, 1),
(4, 'Para ihop begrepp', 'Matcha orden med rätt förklaring.', 'match', 1, 1),
(5, 'Textluckor', 'Fyll i de ord som saknas i texten.', 'fill_blank', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `exercise_questions`
--

CREATE TABLE `exercise_questions` (
  `Question_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Statement` text NOT NULL,
  `Correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise_questions`
--

INSERT INTO `exercise_questions` (`Question_Id`, `Exercise_Id`, `Statement`, `Correct`) VALUES
(1, 1, 'Albin reste tillsammans med sin mamma hela vägen.', 0),
(2, 1, 'Albin satt bredvid en äldre dam som bjöd honom på karamell.', 1),
(3, 1, 'Tågresan gick till Stockholm.', 0),
(4, 2, 'Vilken frukt köpte Lisa?', NULL),
(5, 3, 'Albin tappade sin mössa.', NULL),
(6, 3, 'Han gick tillbaka till parken.', NULL),
(7, 3, 'Han hittade mössan under bänken.', NULL),
(8, 4, 'Hund = Djur som skäller.', NULL),
(9, 4, 'Katt = Djur som jamar.', NULL),
(10, 5, 'Det var en ____ dag i parken.', NULL),
(11, 5, 'Barnen lekte med en ____ boll.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `experience_levels`
--

CREATE TABLE `experience_levels` (
  `Level_Id` int(11) NOT NULL,
  `Level_Name` varchar(50) DEFAULT NULL,
  `XP_Required` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experience_levels`
--

INSERT INTO `experience_levels` (`Level_Id`, `Level_Name`, `XP_Required`) VALUES
(1, 'Beginner', 0),
(2, 'Intermediate', 100),
(3, 'Advanced', 300),
(4, 'Master', 600);

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `Option_Id` int(11) NOT NULL,
  `Question_Id` int(11) NOT NULL,
  `Option_Text` varchar(255) NOT NULL,
  `Is_Correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`Option_Id`, `Question_Id`, `Option_Text`, `Is_Correct`) VALUES
(1, 4, 'Äpple', 1),
(2, 4, 'Banan', 0),
(3, 4, 'Apelsin', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'student'),
(2, 'teacher'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `email`, `password`, `role_id`, `xp`, `class_id`, `created_at`) VALUES
(1, 'teacher1', 'teacher@example.com', 'hashed_password', 3, 0, NULL, '2025-11-11 10:05:35'),
(2, 'Admin', 'admin@g.fi', 'Admin@1', 3, 0, NULL, '2025-11-11 10:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `User_Id` int(11) NOT NULL,
  `Achv_Id` int(11) NOT NULL,
  `Earned_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_results`
--

CREATE TABLE `user_results` (
  `Result_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Score` int(11) DEFAULT 0,
  `Completed` tinyint(1) DEFAULT 0,
  `Completed_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`Achv_Id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`Exercise_Id`),
  ADD KEY `Created_By` (`Created_By`);

--
-- Indexes for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  ADD PRIMARY KEY (`Question_Id`),
  ADD KEY `Exercise_Id` (`Exercise_Id`);

--
-- Indexes for table `experience_levels`
--
ALTER TABLE `experience_levels`
  ADD PRIMARY KEY (`Level_Id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`Option_Id`),
  ADD KEY `Question_Id` (`Question_Id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`User_Id`,`Achv_Id`),
  ADD KEY `Achv_Id` (`Achv_Id`);

--
-- Indexes for table `user_results`
--
ALTER TABLE `user_results`
  ADD PRIMARY KEY (`Result_Id`),
  ADD KEY `User_Id` (`User_Id`),
  ADD KEY `Exercise_Id` (`Exercise_Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `Achv_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `Exercise_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  MODIFY `Question_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `experience_levels`
--
ALTER TABLE `experience_levels`
  MODIFY `Level_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `Option_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_results`
--
ALTER TABLE `user_results`
  MODIFY `Result_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `exercises_ibfk_1` FOREIGN KEY (`Created_By`) REFERENCES `users` (`u_id`) ON DELETE SET NULL;

--
-- Constraints for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  ADD CONSTRAINT `exercise_questions_ibfk_1` FOREIGN KEY (`Exercise_Id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`Question_Id`) REFERENCES `exercise_questions` (`Question_Id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`Achv_Id`) REFERENCES `achievements` (`Achv_Id`) ON DELETE CASCADE;

--
-- Constraints for table `user_results`
--
ALTER TABLE `user_results`
  ADD CONSTRAINT `user_results_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_results_ibfk_2` FOREIGN KEY (`Exercise_Id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
