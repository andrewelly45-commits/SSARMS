-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 15, 2026 at 07:21 AM
-- Server version: 11.8.6-MariaDB-6 from Debian
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ssarms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `stream` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reg_prefix` varchar(2) NOT NULL,
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_name`, `stream`, `created_at`, `reg_prefix`, `level`) VALUES
(34, 'FORM ONE', '', '2026-06-28 17:09:29', '11', 1),
(35, 'FORM TWO', '', '2026-06-28 17:12:33', '12', 2),
(36, 'FORM THREE', '', '2026-06-28 17:12:51', '13', 3),
(37, 'FORM FOUR', '', '2026-06-28 17:13:10', '14', 4);

-- --------------------------------------------------------

--
-- Table structure for table `class_subject`
--

CREATE TABLE `class_subject` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `class_subject`
--

INSERT INTO `class_subject` (`id`, `class_id`, `subject_id`, `status`) VALUES
(27, 36, 54, 'active'),
(28, 36, 64, 'active'),
(29, 36, 55, 'active'),
(30, 36, 59, 'active'),
(31, 36, 63, 'active'),
(32, 36, 57, 'active'),
(33, 36, 61, 'active'),
(34, 36, 58, 'active'),
(35, 36, 62, 'active'),
(36, 36, 60, 'active'),
(37, 36, 56, 'active'),
(38, 36, 53, 'active'),
(39, 37, 54, 'active'),
(40, 37, 64, 'active'),
(41, 37, 55, 'active'),
(42, 37, 59, 'active'),
(43, 37, 63, 'active'),
(44, 37, 57, 'active'),
(45, 37, 61, 'active'),
(46, 37, 58, 'active'),
(48, 37, 60, 'active'),
(49, 37, 56, 'active'),
(50, 37, 53, 'active'),
(51, 35, 54, 'active'),
(52, 35, 64, 'active'),
(53, 35, 55, 'active'),
(54, 35, 59, 'active'),
(55, 35, 63, 'active'),
(56, 35, 57, 'active'),
(57, 35, 61, 'active'),
(58, 35, 58, 'active'),
(59, 35, 62, 'active'),
(60, 35, 60, 'active'),
(61, 35, 56, 'active'),
(62, 35, 53, 'active'),
(63, 34, 54, 'active'),
(64, 34, 64, 'active'),
(65, 34, 55, 'active'),
(66, 34, 59, 'active'),
(67, 34, 63, 'active'),
(68, 34, 57, 'active'),
(69, 34, 61, 'active'),
(70, 34, 58, 'active'),
(71, 34, 62, 'active'),
(72, 34, 60, 'active'),
(73, 34, 56, 'active'),
(74, 34, 53, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`) VALUES
(6, 'Science'),
(7, 'Arts'),
(8, 'Mathematics'),
(9, 'Bussiness'),
(10, 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `marks` int(11) NOT NULL CHECK (`marks` between 0 and 100),
  `term` varchar(20) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `status` enum('pending','approved','published') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `school_code` varchar(10) DEFAULT NULL,
  `school_logo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `school_name`, `school_code`, `school_logo`, `address`) VALUES
(2, 'secondary', '0227', NULL, 'mwanza');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_no` varchar(30) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `academic_year` year(4) NOT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `admission_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `user_id`, `registration_no`, `class_id`, `date_of_birth`, `academic_year`, `status`, `admission_no`) VALUES
(60, 109, '14/0227/0001/26', 37, '2008-11-21', '2026', 'active', 'ADM/2026/001'),
(61, 113, '11/0227/0001/26', 34, '2008-07-30', '2026', 'active', 'ADM/2026/002'),
(62, 114, '11/0227/0002/26', 34, '2010-07-30', '2026', 'active', 'ADM/2026/003'),
(63, 115, '12/0227/0001/26', 35, '2010-11-30', '2026', 'active', 'ADM/2026/004'),
(64, 116, '12/0227/0002/26', 35, '2009-10-30', '2026', 'active', 'ADM/2026/005'),
(65, 117, '14/0227/0002/26', 37, '2011-12-30', '2026', 'active', 'ADM/2026/006'),
(66, 118, '13/0227/0001/26', 36, '2013-11-30', '2026', 'active', 'ADM/2026/007'),
(67, 119, '13/0227/0002/26', 36, '2010-03-30', '2026', 'active', 'ADM/2026/008'),
(68, 123, '13/0227/0003/26', 36, '2010-02-18', '2026', 'active', 'ADM/2026/009');

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `term` varchar(20) NOT NULL,
  `academic_year` year(4) NOT NULL,
  `total_marks` int(11) DEFAULT 0,
  `total_points` int(11) DEFAULT 0,
  `average` decimal(5,2) DEFAULT 0.00,
  `division` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `student_results`
--

INSERT INTO `student_results` (`result_id`, `student_id`, `class_id`, `term`, `academic_year`, `total_marks`, `total_points`, `average`, `division`, `created_at`) VALUES
(42, 61, 34, 'Term 1', '2026', 0, 0, 0.00, 'INC', '2026-07-03 10:30:53'),
(43, 62, 34, 'Term 1', '2026', 0, 0, 0.00, 'INC', '2026-07-03 10:30:53'),
(44, 63, 35, 'Term 1', '2026', 0, 0, 0.00, 'INC', '2026-07-03 10:30:53'),
(45, 64, 35, 'Term 1', '2026', 0, 0, 0.00, 'INC', '2026-07-03 10:30:53'),
(46, 66, 36, 'Term 1', '2026', 0, 0, 0.00, 'INC', '2026-07-04 18:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `department_id`, `teacher_id`, `created_at`) VALUES
(53, 'PHYSICS', 6, NULL, '2026-06-28 18:40:36'),
(54, 'BIOLOGY', 6, NULL, '2026-06-28 18:40:36'),
(55, 'CHEMISTRY', 6, NULL, '2026-06-28 18:40:36'),
(56, 'MATHEMATICS', 8, NULL, '2026-06-28 18:41:45'),
(57, 'ENGLISH', 7, NULL, '2026-06-28 18:43:51'),
(58, 'HISTORY', 7, NULL, '2026-06-28 18:43:51'),
(59, 'CIVICS', 7, NULL, '2026-06-28 18:43:51'),
(60, 'KISWAHILI', 7, NULL, '2026-06-28 18:43:51'),
(61, 'GEOGRAPHY', 7, NULL, '2026-06-28 18:43:51'),
(62, 'INFORMATION TECHNOLOGY', 10, NULL, '2026-06-28 18:44:43'),
(63, 'COMMERCE', 9, NULL, '2026-06-28 18:45:15'),
(64, 'BOOK KEEPING', 9, NULL, '2026-06-28 18:45:15'),
(65, 'SOFTWARE AND HARDWARE MAINTAINANCE', 10, NULL, '2026-06-30 08:47:24');

-- --------------------------------------------------------

--
-- Table structure for table `system_audit_log`
--

CREATE TABLE `system_audit_log` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `module` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `affected_table` varchar(50) DEFAULT NULL,
  `affected_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'success',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `system_audit_log`
--

INSERT INTO `system_audit_log` (`audit_id`, `user_id`, `user_role`, `user_name`, `action_type`, `action_description`, `module`, `ip_address`, `user_agent`, `affected_table`, `affected_id`, `old_values`, `new_values`, `status`, `created_at`) VALUES
(1, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-04 20:47:37'),
(2, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-04 20:52:00'),
(3, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-04 20:52:30'),
(4, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:52:38'),
(5, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-04 20:52:40'),
(6, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:52:45'),
(7, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:52:52'),
(8, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:52:56'),
(9, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:53:00'),
(10, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-04 20:53:16'),
(11, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-04 20:53:45'),
(12, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-04 20:53:49'),
(13, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-04 20:54:26'),
(14, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 12:55:13'),
(15, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 13:02:30'),
(16, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 13:15:29'),
(17, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 13:19:29'),
(18, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 13:20:11'),
(19, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:21:07'),
(20, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:21:49'),
(21, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:26:30'),
(22, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:28:46'),
(23, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:30:41'),
(24, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:31:10'),
(25, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:31:30'),
(26, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 13:33:29'),
(27, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 13:33:59'),
(28, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 13:35:53'),
(29, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 13:35:59'),
(30, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:36:03'),
(31, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:38:25'),
(32, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:49:10'),
(33, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-05 13:51:41'),
(34, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 14:31:32'),
(35, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 14:38:35'),
(36, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 14:39:00'),
(37, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-05 14:44:39'),
(38, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 14:53:28'),
(39, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 14:53:52'),
(40, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-05 14:56:03'),
(41, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-05 14:56:18'),
(42, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 15:05:09'),
(43, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 15:05:29'),
(44, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-05 15:05:55'),
(45, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-05 15:07:18'),
(46, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 15:15:23'),
(47, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-05 15:15:42'),
(48, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-05 15:24:24'),
(49, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 06:48:19'),
(50, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 07:03:04'),
(51, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:09:19'),
(52, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-06 07:09:23'),
(53, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:09:28'),
(54, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-06 07:13:14'),
(55, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 07:14:32'),
(56, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 07:15:16'),
(57, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:18:33'),
(58, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-06 07:18:38'),
(59, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:18:52'),
(60, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:20:14'),
(61, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-06 07:20:17'),
(62, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-06 07:21:44'),
(63, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:26:36'),
(64, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 07:27:44'),
(65, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-06 17:47:21'),
(66, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-06 17:51:15'),
(67, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-06 18:43:32'),
(68, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-06 19:35:44'),
(69, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 19:35:59'),
(70, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 19:41:23'),
(71, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 19:41:47'),
(72, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-06 19:46:24'),
(73, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-06 19:46:27'),
(74, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-06 19:47:20'),
(75, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-06 20:01:23'),
(76, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-06 20:15:41'),
(77, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-06 20:17:10'),
(78, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-06 20:17:39'),
(79, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-06 20:18:10'),
(80, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-07 09:20:05'),
(81, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-07 12:38:48'),
(82, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-07 12:39:55'),
(83, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-07 12:39:58'),
(84, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-07 12:46:55'),
(85, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-07 12:51:40'),
(86, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-07 12:51:59'),
(87, 110, 'academic', 'ELIA ANDREW', 'upload', 'Uploaded  marks for class ID: , subject ID: , term: , year: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"count\":null,\"subject_id\":null,\"term\":null}', 'success', '2026-07-07 12:52:56'),
(88, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-07 13:19:02'),
(89, 121, 'teacher', 'EMMANUEL MNYAMI', 'upload', 'Uploaded  marks for class ID: , subject ID: , term: , year: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"count\":null,\"subject_id\":null,\"term\":null}', 'success', '2026-07-07 13:20:00'),
(90, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-08 14:37:01'),
(91, 121, 'teacher', 'EMMANUEL MNYAMI', 'upload', 'Uploaded  marks for class ID: , subject ID: , term: , year: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"count\":null,\"subject_id\":null,\"term\":null}', 'success', '2026-07-08 15:09:58'),
(92, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 15:29:18'),
(93, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:21:10'),
(94, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:21:26'),
(95, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:21:32'),
(96, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:24:21'),
(97, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:24:36'),
(98, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:27:36'),
(99, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:27:40'),
(100, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:27:44'),
(101, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:27:48'),
(102, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:29:44'),
(103, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:29:48'),
(104, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:29:53'),
(105, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:31:22'),
(106, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:31:23'),
(107, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:31:29'),
(108, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:31:48'),
(109, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:33:45'),
(110, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:36:07'),
(111, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:29'),
(112, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:34'),
(113, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:43'),
(114, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:49'),
(115, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:49'),
(116, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:54'),
(117, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:38:59'),
(118, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:39:04'),
(119, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:40:26'),
(120, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:40:30'),
(121, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:40:35'),
(122, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:40:43'),
(123, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:40:47'),
(124, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:41:00'),
(125, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:41:06'),
(126, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:41:07'),
(127, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:41:10'),
(128, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:41:19'),
(129, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:42:56'),
(130, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:43:04'),
(131, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:43:07'),
(132, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:43:25'),
(133, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:43:28'),
(134, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:24'),
(135, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:42'),
(136, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:45'),
(137, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:48'),
(138, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:51'),
(139, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:53'),
(140, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 16:56:56'),
(141, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted mark ID:  for student: , subject: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 17:01:42'),
(142, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted 2 pending marks for Term 2 in class ID: 37', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', 37, '{\"deleted_records\":2,\"class_id\":\"37\",\"term\":\"Term 2\"}', NULL, 'success', '2026-07-08 17:14:49'),
(143, 121, 'teacher', 'EMMANUEL MNYAMI', 'delete', 'Deleted 0 pending marks for Term 2 in class ID: 37', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', 37, '{\"deleted_records\":0,\"class_id\":\"37\",\"term\":\"Term 2\"}', NULL, 'success', '2026-07-08 17:16:43'),
(144, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-08 19:47:27'),
(145, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-08 19:50:11'),
(146, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 19:55:12'),
(147, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted mark ID:  for student: ', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"marks\":null,\"student\":null}', NULL, 'success', '2026-07-08 19:55:17'),
(148, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 19:59:17'),
(149, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 19:59:19'),
(150, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 20:29:24'),
(151, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 20:29:26'),
(152, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 20:31:15'),
(153, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted all results for 2026 (Term 1)', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"academic_year\":\"2026\",\"term\":\"Term 1\",\"records_deleted\":\"25\"}', NULL, 'success', '2026-07-08 20:31:51'),
(154, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted all results for 2026 (Term 1)', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"academic_year\":\"2026\",\"term\":\"Term 1\",\"records_deleted\":\"0\"}', NULL, 'success', '2026-07-08 20:31:57'),
(155, 110, 'academic', 'ELIA ANDREW', 'delete', 'Deleted all results for 2026 (Term 2)', 'marks', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, '{\"academic_year\":\"2026\",\"term\":\"Term 2\",\"records_deleted\":\"2\"}', NULL, 'success', '2026-07-08 20:32:07'),
(156, 110, 'academic', 'ELIA ANDREW', 'approve', 'Approved results for Class: , Subject: ', 'results', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'marks', NULL, NULL, '{\"class\":null,\"subject\":null}', 'success', '2026-07-08 20:32:13'),
(157, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-08 21:11:14'),
(158, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 11:50:08'),
(159, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 11:53:42'),
(160, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 11:55:40'),
(161, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 11:58:59'),
(162, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:00:18'),
(163, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 12:03:03'),
(164, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:04:46'),
(165, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-14 12:17:50'),
(166, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 12:24:46'),
(167, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:28:44'),
(168, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-14 12:29:11'),
(169, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:35:38'),
(170, 109, 'student', 'RAMADHAN SALUM', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 109, NULL, NULL, 'success', '2026-07-14 12:38:53'),
(171, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 12:42:11'),
(172, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:42:47');
INSERT INTO `system_audit_log` (`audit_id`, `user_id`, `user_role`, `user_name`, `action_type`, `action_description`, `module`, `ip_address`, `user_agent`, `affected_table`, `affected_id`, `old_values`, `new_values`, `status`, `created_at`) VALUES
(173, 121, 'teacher', 'EMMANUEL MNYAMI', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 121, NULL, NULL, 'success', '2026-07-14 12:45:12'),
(174, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:49:52'),
(175, 110, 'academic', 'ELIA ANDREW', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 110, NULL, NULL, 'success', '2026-07-14 12:54:37'),
(176, 108, 'admin', 'ADMIN MNENE', 'login', 'User logged in successfully', 'auth', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'users', 108, NULL, NULL, 'success', '2026-07-14 12:56:57');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `user_id`, `phone_no`, `department_id`, `status`) VALUES
(37, 110, '0679889496', 6, 'active'),
(38, 111, '0679889496', 7, 'active'),
(39, 112, '0679889496', 9, 'active'),
(40, 120, '', 8, 'active'),
(41, 121, '0679889496', 6, 'active'),
(42, 122, '0679889496', 10, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class`
--

CREATE TABLE `teacher_class` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `teacher_class`
--

INSERT INTO `teacher_class` (`id`, `teacher_id`, `class_id`) VALUES
(104, 37, 37),
(105, 37, 35),
(106, 38, 34),
(107, 38, 36),
(108, 39, 34),
(109, 39, 35),
(110, 40, 37),
(111, 40, 35),
(118, 42, 34),
(119, 42, 36),
(120, 42, 35),
(129, 41, 37),
(130, 41, 36);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject`
--

CREATE TABLE `teacher_subject` (
  `teacher_subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `teacher_subject`
--

INSERT INTO `teacher_subject` (`teacher_subject_id`, `teacher_id`, `class_id`, `subject_id`, `created_at`) VALUES
(56, 39, 34, 64, '2026-06-30 09:15:33'),
(57, 39, 35, 63, '2026-06-30 09:15:42'),
(60, 40, 37, 56, '2026-06-30 09:43:04'),
(61, 40, 35, 56, '2026-06-30 09:43:11'),
(62, 42, 34, 62, '2026-06-30 09:45:54'),
(63, 42, 36, 62, '2026-06-30 09:46:01'),
(64, 42, 35, 62, '2026-06-30 09:46:08'),
(65, 37, 37, 54, '2026-07-04 17:56:22'),
(66, 37, 35, 55, '2026-07-04 17:56:31'),
(67, 38, 34, 60, '2026-07-04 18:53:36'),
(68, 38, 34, 57, '2026-07-04 18:53:48'),
(69, 38, 36, 57, '2026-07-04 18:54:07'),
(70, 38, 36, 60, '2026-07-04 19:19:03'),
(71, 41, 36, 53, '2026-07-04 19:27:12'),
(73, 41, 37, 53, '2026-07-04 19:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student','academic') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `status` enum('active','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `phone`, `gender`, `created_at`, `reset_token`, `profile_pic`, `status`) VALUES
(108, 'ADMIN MNENE', 'admin@gmail.com', '$2y$12$wGA02l/yvuRMVRpMJ6RT9.c2SWZ1k7m6p31kI26WxPd0UVgOoC4Ji', 'admin', '0767823545', 'male', '2026-06-28 16:53:11', NULL, 'PROFILE_108_1784033648.jpeg', 'active'),
(109, 'RAMADHAN SALUM', 'student@gmail.com', '$2y$12$8KwrZZOj8zjeoVaiEhAwfOGQTNNWGH8RjWIDy6n26Tq9DxT/KBKKa', 'student', '0679889496', 'male', '2026-06-28 17:23:00', NULL, 'PROFILE_109_1784032902.jpeg', 'active'),
(110, 'ELIA ANDREW', 'teacher@gmail.com', '$2y$12$9SFWyETB6vJ1Gao.cu6l9OtOLA/dwCLYQp5Nq9edObugljbRvzF5W', 'academic', '0679889496', 'male', '2026-06-28 17:31:23', NULL, 'PROFILE_110_1784032945.jpeg', 'active'),
(111, 'KAFUMU JOHN ', 'kafumu@gmail.com', '$2y$12$d2dx13nX73FEbxAw0fnDH.PWzSYUTEg6y9Znm0/8XNlpBPnhYnzSi', 'teacher', NULL, 'male', '2026-06-28 20:24:40', NULL, NULL, 'active'),
(112, 'ELIZABETH ANDREA', 'eliza@gmail.com', '$2y$12$w.fcgrfkpCrGHooHW3HMRex8bzRTt2HuuEa1p0riup9eVpXIKidVm', 'teacher', NULL, 'female', '2026-06-30 09:13:52', NULL, NULL, 'active'),
(113, 'ELISHA JUMA ', 'elisha@gmail.com', '$2y$12$jgrXbs06OGrY1tl23SnrJO/wfEtPETw8qctU..1U15ABolJOb6h6G', 'student', '0679889496', 'male', '2026-06-30 09:23:55', NULL, NULL, 'active'),
(114, 'DEVOTHA ADBI', 'devotha@gmail.com', '$2y$12$wyWTMmn3gtS/hhqGtxrUHew6Rj4nEa993JQ7pa0a6n7A6j97bQZaS', 'student', '0679889496', 'female', '2026-06-30 09:25:11', NULL, NULL, 'active'),
(115, 'MATIMBA JAMES', 'matimba@gmail.com', '$2y$12$4lZnqnZALDZc9lFXKcGhbOdTmUTfnbWfBPIhkbuFh8RdGeosCBWMu', 'student', '0679889496', 'male', '2026-06-30 09:27:44', NULL, NULL, 'active'),
(116, 'TIMOTHEO SANGIJA', 'sangijatimotheo@gmail.com', '$2y$12$Hz67/k/DIvjQFYb9He81ZOInKu5.mxVQiN9derkD8wD4gltAYd/wy', 'student', '0679889496', 'male', '2026-06-30 09:29:28', NULL, NULL, 'active'),
(117, 'KILEMILE GUSTO', 'kilemile@gmail.com', '$2y$12$4kvIvBWIXPpSVYUesFlGS.Fa1NEHQ7Y.xy4TU2Irccge/soUciOHe', 'student', '0679889487', 'male', '2026-06-30 09:31:53', NULL, NULL, 'active'),
(118, 'ABDUL AZIZ ABDUL', 'abdulaziz@gmail.com', '$2y$12$XFdYCJsgUc1.tYe9kgKdgupvjzDdsodtYePolj.yTpVbiKO5/e4VW', 'student', '0679872496', 'male', '2026-06-30 09:33:47', NULL, NULL, 'active'),
(119, 'KELVIN JOHN', 'john@gmail.com', '$2y$12$NmyrHgRUC5ZFTr2E/bNqYOOtWBb04Pdr8irqElwpGQ7fxELN.BxTK', 'student', '0679889496', 'male', '2026-06-30 09:35:15', NULL, NULL, 'active'),
(120, 'GEOFREY LAMBERT', 'geofrey@gmail.com', '$2y$12$hN..jPKv5rhTIxZ.0WCWhOClz5L4pBiCb.L8gTcktRhwuuKMYbNAa', 'teacher', NULL, 'male', '2026-06-30 09:40:04', NULL, NULL, 'active'),
(121, 'EMMANUEL MNYAMI', 'emmanuel@gmail.com', '$2y$12$hZhfZx.XD9V5eWOETtO/1uztDfA0lHBxOOKEoCQd3zUkgL1keoVEG', 'teacher', '0679889496', 'male', '2026-06-30 09:42:08', NULL, 'PROFILE_121_1784032222.jpeg', 'active'),
(122, 'MARCO FIKIRI', 'marco@gmail.com', '$2y$12$Jdv13tN1lYspHXGRRUc6aueep51uFzeECUPrSS2jFDaxB45DPraFW', 'teacher', NULL, 'male', '2026-06-30 09:45:32', NULL, NULL, 'active'),
(123, 'WITNESS LYIMO', 'witness@gmail.com', '$2y$12$f8mOI4ECJ7Gk1rkkC8rS1O8eFM8vWpU9lBzywcQ41jeP4HI10QJd.', 'student', '0679889496', 'female', '2026-07-07 10:25:25', NULL, NULL, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD UNIQUE KEY `registration_no` (`registration_no`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `unique_result` (`student_id`,`class_id`,`term`,`academic_year`),
  ADD KEY `fk_student_results_class` (`class_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `fk_subject_department` (`department_id`);

--
-- Indexes for table `system_audit_log`
--
ALTER TABLE `system_audit_log`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_teacher_department` (`department_id`);

--
-- Indexes for table `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD PRIMARY KEY (`teacher_subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `class_subject`
--
ALTER TABLE `class_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=369;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `system_audit_log`
--
ALTER TABLE `system_audit_log`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `teacher_class`
--
ALTER TABLE `teacher_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  MODIFY `teacher_subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD CONSTRAINT `class_subject_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_4` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_results`
--
ALTER TABLE `student_results`
  ADD CONSTRAINT `fk_student_results_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_results_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `fk_subject_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD CONSTRAINT `teacher_class_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`),
  ADD CONSTRAINT `teacher_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD CONSTRAINT `teacher_subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subject_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subject_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
