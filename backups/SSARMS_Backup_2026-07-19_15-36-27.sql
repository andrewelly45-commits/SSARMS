/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `audit_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `status` varchar(20) DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `affected_id` int(11) DEFAULT NULL,
  `affected_table` varchar(100) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_user_role` (`user_role`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_module` (`module`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` (`audit_id`, `user_id`, `user_name`, `user_role`, `action_type`, `module`, `action_description`, `status`, `ip_address`, `user_agent`, `affected_id`, `affected_table`, `old_values`, `new_values`, `created_at`) VALUES (1,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:27:42'),
(2,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:27:44'),
(3,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:27:45'),
(4,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:27:47'),
(5,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:28:42'),
(6,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:29:58'),
(7,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:32:38'),
(8,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:36:06'),
(9,108,'ADMIN MNENE','admin','clean','system','Cleaned audit logs older than 365 days','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:36:47'),
(10,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:36:48'),
(11,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 11:55:43'),
(12,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:00:46'),
(13,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:09:47'),
(14,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:09:50'),
(15,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:10:16'),
(16,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:10:55'),
(17,NULL,'System','system','login','auth','Login failed: Wrong password for user: ELIA ANDREW (Email: teacher@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:12:28'),
(18,110,'ELIA ANDREW','academic','login','auth','User logged in: ELIA ANDREW (Role: academic, Email: teacher@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 12:12:36'),
(19,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:13:16'),
(20,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:13:18'),
(21,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:14:35'),
(22,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:14:36'),
(23,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:14:40'),
(24,NULL,'System','system','login','auth','Login failed: Wrong password for user: ADMIN MNENE (Email: admin@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:14:47'),
(25,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:14:53'),
(26,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:14:55'),
(27,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:16:38'),
(28,109,'RAMADHAN SALUM','student','login','auth','User logged in: RAMADHAN SALUM (Role: student, Email: student@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:17:32'),
(29,109,'RAMADHAN SALUM','student','logout','auth','User logged out: RAMADHAN SALUM (Role: student)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:24:13'),
(30,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:24:19'),
(31,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:24:20'),
(32,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:24:56'),
(33,109,'RAMADHAN SALUM','student','login','auth','User logged in: RAMADHAN SALUM (Role: student, Email: student@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:25:03'),
(34,109,'RAMADHAN SALUM','student','logout','auth','User logged out: RAMADHAN SALUM (Role: student)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:30:40'),
(35,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:30:46'),
(36,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:30:48'),
(37,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:32:01'),
(38,109,'RAMADHAN SALUM','student','login','auth','User logged in: RAMADHAN SALUM (Role: student, Email: student@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:32:09'),
(39,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:02'),
(40,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:02'),
(41,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:03'),
(42,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:03'),
(43,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:03'),
(44,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:03'),
(45,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(46,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(47,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(48,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(49,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(50,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(51,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(52,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:04'),
(53,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(54,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(55,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(56,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(57,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(58,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(59,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(60,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(61,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(62,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:05'),
(63,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:06'),
(64,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:06'),
(65,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:06'),
(66,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:06'),
(67,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:07'),
(68,NULL,'System Fallback','system','view','results','Student viewed Term 2 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:07'),
(69,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:08'),
(70,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:08'),
(71,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:12'),
(72,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:12'),
(73,109,'RAMADHAN SALUM','student','logout','auth','User logged out: RAMADHAN SALUM (Role: student)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:34:16'),
(74,109,'RAMADHAN SALUM','student','login','auth','User logged in: RAMADHAN SALUM (Role: student, Email: student@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:34:22'),
(75,NULL,'System Fallback','system','view','results','Student viewed results page: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:24'),
(76,NULL,'System Fallback','system','view','results','Student viewed Term 1 results for year 2026: RAMADHAN SALUM','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:24'),
(77,109,'RAMADHAN SALUM','student','logout','auth','User logged out: RAMADHAN SALUM (Role: student)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:34:30'),
(78,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:34:38'),
(79,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:34:40'),
(80,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:37:00'),
(81,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:37:18'),
(82,109,'RAMADHAN SALUM','student','login','auth','User logged in: RAMADHAN SALUM (Role: student, Email: student@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:37:25'),
(83,NULL,'System Fallback','system','view','results','Student checked Term 1 results for year 2026 - No results found: RAMADHAN SALUM','info','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:37:26'),
(84,NULL,'System Fallback','system','view','results','Student checked Term 2 results for year 2026 - No results found: RAMADHAN SALUM','info','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:37:29'),
(85,NULL,'System Fallback','system','view','results','Student checked Term 1 results for year 2026 - No results found: RAMADHAN SALUM','info','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:37:31'),
(86,109,'RAMADHAN SALUM','student','logout','auth','User logged out: RAMADHAN SALUM (Role: student)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',109,'users',NULL,NULL,'2026-07-19 12:37:33'),
(87,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:37:40'),
(88,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:37:42'),
(89,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:38:02'),
(90,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:38:05'),
(91,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:38:28'),
(92,NULL,'System','system','login','auth','Login failed: Wrong password for user: ELIA ANDREW (Email: teacher@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:38:40'),
(93,110,'ELIA ANDREW','academic','login','auth','User logged in: ELIA ANDREW (Role: academic, Email: teacher@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 12:38:49'),
(94,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:42:51'),
(95,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:42:51'),
(96,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:42:54'),
(97,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:42:54'),
(98,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:44:09'),
(99,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:44:09'),
(100,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:49:59'),
(101,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:49:59'),
(102,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:00'),
(103,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:00'),
(104,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:00'),
(105,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:00'),
(106,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(107,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(108,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(109,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(110,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(111,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(112,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(113,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(114,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(115,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(116,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(117,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(118,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:01'),
(119,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:02'),
(120,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:03'),
(121,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:05'),
(122,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:50:07'),
(123,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:51:13'),
(124,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM TWO (ID: 35) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:51:13'),
(125,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:51:50'),
(126,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM TWO (ID: 35) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:51:50'),
(127,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:52:47'),
(128,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM TWO (ID: 35) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:52:47'),
(129,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:52:48'),
(130,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM TWO (ID: 35) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:52:48'),
(131,110,'ELIA ANDREW','academic','logout','auth','User logged out: ELIA ANDREW (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 12:53:25'),
(132,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:53:31'),
(133,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:53:33'),
(134,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:54:03'),
(135,NULL,'System','system','login','auth','Login failed: Wrong password for user: ELIA ANDREW (Email: teacher@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:54:13'),
(136,110,'ELIA ANDREW','academic','login','auth','User logged in: ELIA ANDREW (Role: academic, Email: teacher@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 12:54:20'),
(137,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:54:23'),
(138,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:54:27'),
(139,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM FOUR (ID: 37) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:54:27'),
(140,110,'ELIA ANDREW','academic','logout','auth','User logged out: ELIA ANDREW (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 12:54:52'),
(141,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:55:00'),
(142,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:55:02'),
(143,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:56:05'),
(144,111,'KAFUMU JOHN ','academic','login','auth','User logged in: KAFUMU JOHN  (Role: academic, Email: kafumu@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 12:56:12'),
(145,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:56:16'),
(146,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:56:19'),
(147,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM THREE (ID: 36) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:56:19'),
(148,111,'KAFUMU JOHN ','academic','logout','auth','User logged out: KAFUMU JOHN  (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 12:56:40'),
(149,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:57:00'),
(150,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:57:02'),
(151,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:57:04'),
(152,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 12:57:49'),
(153,111,'KAFUMU JOHN ','academic','login','auth','User logged in: KAFUMU JOHN  (Role: academic, Email: kafumu@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 12:58:17'),
(154,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:58:20'),
(155,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:58:23'),
(156,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM ONE (ID: 34) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:58:23'),
(157,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:58:42'),
(158,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM THREE (ID: 36) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 12:58:42'),
(159,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:01:15'),
(160,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM THREE (ID: 36) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:01:15'),
(161,NULL,'KAFUMU JOHN ','academic','import','marks','Started importing marks for FORM THREE - ENGLISH (Term 1 2026)','pending','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'marks',NULL,NULL,'2026-07-19 13:01:19'),
(162,NULL,'KAFUMU JOHN ','academic','upload','marks','Successfully imported 3 marks for FORM THREE - ENGLISH (Term 1 2026). Inserted: 3, Updated: 0','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'marks',NULL,NULL,'2026-07-19 13:01:19'),
(163,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:01:19'),
(164,NULL,'System Fallback','system','view','marks','Academic staff selected class: FORM THREE (ID: 36) for marks entry','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:01:19'),
(165,111,'KAFUMU JOHN ','academic','logout','auth','User logged out: KAFUMU JOHN  (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 13:01:33'),
(166,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:01:44'),
(167,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:01:45'),
(168,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:02:52'),
(169,111,'KAFUMU JOHN ','academic','login','auth','User logged in: KAFUMU JOHN  (Role: academic, Email: kafumu@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 13:03:19'),
(170,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:03:25'),
(171,NULL,'System Fallback','system','view','marks','Academic staff viewed marks entry page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:06:04'),
(172,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:08:43'),
(173,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:08:46'),
(174,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:08:50'),
(175,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: all)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:08:50'),
(176,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:03'),
(177,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: Term 2)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:03'),
(178,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:06'),
(179,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: all)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:06'),
(180,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:09'),
(181,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: all)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:09'),
(182,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:10'),
(183,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: Term 1)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:10'),
(184,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:13'),
(185,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: Term 2)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:13'),
(186,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks page: KAFUMU JOHN ','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',38,'users',NULL,NULL,'2026-07-19 13:09:15'),
(187,NULL,'KAFUMU JOHN ','academic','view','marks','Academic staff viewed marks for class: FORM THREE (Term: all)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'classes',NULL,NULL,'2026-07-19 13:09:15'),
(188,111,'KAFUMU JOHN ','academic','logout','auth','User logged out: KAFUMU JOHN  (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',111,'users',NULL,NULL,'2026-07-19 13:09:39'),
(189,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:09:47'),
(190,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:09:48'),
(191,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:10:11'),
(192,NULL,'System','system','login','auth','Login failed: Wrong password for user: ELIA ANDREW (Email: teacher@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:10:23'),
(193,110,'ELIA ANDREW','academic','login','auth','User logged in: ELIA ANDREW (Role: academic, Email: teacher@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 13:10:31'),
(194,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed marks page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',37,'users',NULL,NULL,'2026-07-19 13:10:34'),
(195,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:12:34'),
(196,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed marks page: ELIA ANDREW','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',37,'users',NULL,NULL,'2026-07-19 13:12:35'),
(197,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:12:37'),
(198,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:12:41'),
(199,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:13:03'),
(200,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:13:05'),
(201,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:13:37'),
(202,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:15:11'),
(203,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:15:15'),
(204,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:15:15'),
(205,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:17:07'),
(206,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:17:08'),
(207,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:17:10'),
(208,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:17:10'),
(209,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:17:20'),
(210,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:46'),
(211,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:47'),
(212,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:48'),
(213,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:48'),
(214,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:48'),
(215,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:48'),
(216,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:49'),
(217,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:49'),
(218,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:49'),
(219,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:49'),
(220,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:49'),
(221,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:50'),
(222,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:50'),
(223,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:50'),
(224,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:50'),
(225,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:50'),
(226,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:51'),
(227,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:51'),
(228,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:51'),
(229,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:51'),
(230,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:51'),
(231,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:18:52'),
(232,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:19:03'),
(233,110,'ELIA ANDREW','academic','logout','auth','User logged out: ELIA ANDREW (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 13:19:09'),
(234,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:19:16'),
(235,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:19:17'),
(236,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:25:06'),
(237,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:25:12'),
(238,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:25:42'),
(239,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:27:51'),
(240,108,'ADMIN MNENE','admin','view','profile','User viewed profile: ADMIN MNENE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:27:54'),
(241,108,'ADMIN MNENE','admin','view','profile','User viewed profile: ADMIN MNENE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:28:18'),
(242,108,'ADMIN MNENE','admin','view','profile','User viewed profile: ADMIN MNENE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:28:25'),
(243,108,'ADMIN MNENE','admin','edit','profile','User updated profile: ADMIN MNENE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users','{\"full_name\":\"ADMIN MNENE\",\"email\":\"admin@gmail.com\",\"phone\":\"0767823545\",\"gender\":\"male\"}','{\"full_name\":\"ADMIN MNENE\",\"email\":\"admin@gmail.com\",\"phone\":\"0767823577\",\"gender\":\"male\",\"profile_pic_updated\":false}','2026-07-19 13:28:25'),
(244,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:28:33'),
(245,108,'ADMIN MNENE','admin','clean','system','Cleaned audit logs older than 365 days','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:29:50'),
(246,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:29:50'),
(247,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:30:26'),
(248,108,'ADMIN MNENE','admin','logout','auth','User logged out: ADMIN MNENE (Role: admin)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:30:40'),
(249,NULL,'System','system','login','auth','Login failed: Wrong password for user: ELIA ANDREW (Email: teacher@gmail.com)','failed','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:30:46'),
(250,110,'ELIA ANDREW','academic','login','auth','User logged in: ELIA ANDREW (Role: academic, Email: teacher@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 13:30:53'),
(251,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:30:57'),
(252,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:30:59'),
(253,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:31:01'),
(254,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:31:01'),
(255,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:02'),
(256,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:02'),
(257,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:03'),
(258,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:03'),
(259,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:04'),
(260,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:04'),
(261,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:05'),
(262,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:05'),
(263,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:08'),
(264,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:08'),
(265,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed school results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:10'),
(266,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:11'),
(267,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewed approve results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'results',NULL,NULL,'2026-07-19 13:33:12'),
(268,NULL,'ELIA ANDREW','academic','view','results','Academic staff viewing approval details for class: FORM THREE','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',36,'results',NULL,NULL,'2026-07-19 13:33:12'),
(269,NULL,'ELIA ANDREW','academic','view','marks','Academic staff viewed delete results page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:33:18'),
(270,110,'ELIA ANDREW','academic','logout','auth','User logged out: ELIA ANDREW (Role: academic)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',110,'users',NULL,NULL,'2026-07-19 13:33:35'),
(271,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 13:33:43'),
(272,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:36:53'),
(273,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:36:54'),
(274,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:37:05'),
(275,NULL,'ADMIN MNENE','admin','edit','students','Updated student: OMEGA SADICK (ID: 70)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',70,'students',NULL,NULL,'2026-07-19 13:37:05'),
(276,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:37:05'),
(277,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:37:11'),
(278,NULL,'ADMIN MNENE','admin','view','teachers','Admin viewed manage teachers page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'teachers',NULL,NULL,'2026-07-19 13:40:25'),
(279,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:40:27'),
(280,NULL,'ADMIN MNENE','admin','view','teachers','Admin viewed manage teachers page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'teachers',NULL,NULL,'2026-07-19 13:40:28'),
(281,NULL,'ADMIN MNENE','admin','view','teachers','Admin viewed manage teachers page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'teachers',NULL,NULL,'2026-07-19 13:40:41'),
(282,NULL,'ADMIN MNENE','admin','edit','teachers','Updated teacher: MARCO FIKIRI (ID: 42)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',42,'teachers',NULL,NULL,'2026-07-19 13:40:41'),
(283,NULL,'ADMIN MNENE','admin','view','teachers','Admin viewed manage teachers page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'teachers',NULL,NULL,'2026-07-19 13:40:41'),
(284,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:40:47'),
(285,NULL,'ADMIN MNENE','admin','view','classes','Admin viewed manage classes page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'classes',NULL,NULL,'2026-07-19 13:49:21'),
(286,NULL,'ADMIN MNENE','admin','view','classes','Admin viewed manage classes page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'classes',NULL,NULL,'2026-07-19 13:49:24'),
(287,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:49:27'),
(288,NULL,'ADMIN MNENE','admin','view','classes','Admin viewed manage classes page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'classes',NULL,NULL,'2026-07-19 13:49:36'),
(289,NULL,'ADMIN MNENE','admin','view','subjects','Admin viewed manage subjects page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'subjects',NULL,NULL,'2026-07-19 13:52:08'),
(290,NULL,'ADMIN MNENE','admin','view','subjects','Admin viewed manage subjects page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'subjects',NULL,NULL,'2026-07-19 13:52:10'),
(291,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:52:15'),
(292,NULL,'ADMIN MNENE','admin','view','subjects','Admin viewed manage subjects page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'subjects',NULL,NULL,'2026-07-19 13:52:32'),
(293,NULL,'ADMIN MNENE','admin','view','departments','Admin viewed manage departments page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'departments',NULL,NULL,'2026-07-19 13:55:17'),
(294,NULL,'ADMIN MNENE','admin','view','departments','Admin viewed manage departments page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'departments',NULL,NULL,'2026-07-19 13:55:20'),
(295,108,'ADMIN MNENE','admin','view','system','Viewed system history page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,NULL,NULL,NULL,'2026-07-19 13:55:21'),
(296,NULL,'ADMIN MNENE','admin','view','departments','Admin viewed manage departments page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'departments',NULL,NULL,'2026-07-19 13:55:29'),
(297,NULL,'ADMIN MNENE','admin','view','subjects','Admin viewed manage subjects page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'subjects',NULL,NULL,'2026-07-19 13:55:38'),
(298,NULL,'ADMIN MNENE','admin','view','teachers','Admin viewed manage teachers page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'teachers',NULL,NULL,'2026-07-19 13:55:42'),
(299,NULL,'ADMIN MNENE','admin','view','students','Admin viewed manage students page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'students',NULL,NULL,'2026-07-19 13:55:43'),
(300,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:05:17'),
(301,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:05:58'),
(302,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:07'),
(303,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:14'),
(304,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:17'),
(305,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:20'),
(306,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:21'),
(307,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:24'),
(308,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:26'),
(309,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:28'),
(310,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:30'),
(311,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:33'),
(312,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:06:35'),
(313,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:09:03'),
(314,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:09:04'),
(315,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:10:02'),
(316,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:10:04'),
(317,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:10:15'),
(318,NULL,'ADMIN MNENE','admin','backup','system','Created database backup: backup_2026-07-19_14-10-15.sql ( B)','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:10:15'),
(319,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:10:45'),
(320,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:40'),
(321,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:43'),
(322,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:46'),
(323,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:48'),
(324,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:50'),
(325,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:53'),
(326,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:11:54'),
(327,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:42'),
(328,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:43'),
(329,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:46'),
(330,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:47'),
(331,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:49'),
(332,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:53'),
(333,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:13:54'),
(334,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:14:01'),
(335,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:14:02'),
(336,NULL,'ADMIN MNENE','admin','edit','settings','Updated system settings for group: general','success',NULL,NULL,1,'system_settings',NULL,NULL,'2026-07-19 14:14:02'),
(337,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:14:02'),
(338,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:16:08'),
(339,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:17:29'),
(340,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:17:30'),
(341,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:17:31'),
(342,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 14:17:51'),
(343,108,'ADMIN MNENE','admin','login','auth','User logged in: ADMIN MNENE (Role: admin, Email: admin@gmail.com)','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'users',NULL,NULL,'2026-07-19 15:16:42'),
(344,NULL,'ADMIN MNENE','admin','view','classes','Admin viewed manage classes page','success','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',NULL,'classes',NULL,NULL,'2026-07-19 15:16:44'),
(345,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:23:21'),
(346,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:26:08'),
(347,NULL,'ADMIN MNENE','admin','view','settings','Admin viewed system settings','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:27:06'),
(348,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:28:39'),
(349,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:29:26'),
(350,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:30:53'),
(351,NULL,'ADMIN MNENE','admin','backup','system','Created database backup: SSARMS_Backup_2026-07-19_15-30-53.sql (114.33 KB)','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:30:53'),
(352,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:30:57'),
(353,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:16'),
(354,NULL,'ADMIN MNENE','admin','delete','system','Deleted backup: SSARMS_Backup_2026-07-19_15-11-48.sql','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:16'),
(355,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:19'),
(356,NULL,'ADMIN MNENE','admin','delete','system','Deleted backup: SSARMS_Backup_2026-07-19_15-12-30.sql','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:19'),
(357,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:21'),
(358,NULL,'ADMIN MNENE','admin','delete','system','Deleted backup: SSARMS_Backup_2026-07-19_15-13-29.sql','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:21'),
(359,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:24'),
(360,NULL,'ADMIN MNENE','admin','delete','system','Deleted backup: SSARMS_Backup_2026-07-19_15-13-33.sql','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:24'),
(361,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:26'),
(362,NULL,'ADMIN MNENE','admin','delete','system','Deleted backup: SSARMS_Backup_2026-07-19_15-30-53.sql','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:31:26'),
(363,NULL,'ADMIN MNENE','admin','view','backup','Admin viewed backup page','success',NULL,NULL,NULL,'',NULL,NULL,'2026-07-19 15:36:27');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `class` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `stream` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reg_prefix` varchar(2) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `class` WRITE;
/*!40000 ALTER TABLE `class` DISABLE KEYS */;
INSERT INTO `class` (`class_id`, `class_name`, `stream`, `created_at`, `reg_prefix`, `level`) VALUES (34,'FORM ONE','','2026-06-28 17:09:29','11',1),
(35,'FORM TWO','','2026-06-28 17:12:33','12',2),
(36,'FORM THREE','','2026-06-28 17:12:51','13',3),
(37,'FORM FOUR','','2026-06-28 17:13:10','14',4);
/*!40000 ALTER TABLE `class` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `class_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subject_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `class_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `class_subject` WRITE;
/*!40000 ALTER TABLE `class_subject` DISABLE KEYS */;
INSERT INTO `class_subject` (`id`, `class_id`, `subject_id`, `status`) VALUES (27,36,54,'active'),
(28,36,64,'active'),
(29,36,55,'active'),
(30,36,59,'active'),
(31,36,63,'active'),
(32,36,57,'active'),
(33,36,61,'active'),
(34,36,58,'active'),
(35,36,62,'active'),
(36,36,60,'active'),
(37,36,56,'active'),
(38,36,53,'active'),
(39,37,54,'active'),
(40,37,64,'active'),
(41,37,55,'active'),
(42,37,59,'active'),
(43,37,63,'active'),
(44,37,57,'active'),
(45,37,61,'active'),
(46,37,58,'active'),
(48,37,60,'active'),
(49,37,56,'active'),
(50,37,53,'active'),
(51,35,54,'active'),
(52,35,64,'active'),
(53,35,55,'active'),
(54,35,59,'active'),
(55,35,63,'active'),
(56,35,57,'active'),
(57,35,61,'active'),
(58,35,58,'active'),
(59,35,62,'active'),
(60,35,60,'active'),
(61,35,56,'active'),
(62,35,53,'active'),
(63,34,54,'active'),
(64,34,64,'active'),
(65,34,55,'active'),
(66,34,59,'active'),
(67,34,63,'active'),
(68,34,57,'active'),
(69,34,61,'active'),
(70,34,58,'active'),
(71,34,62,'active'),
(72,34,60,'active'),
(73,34,56,'active'),
(74,34,53,'active');
/*!40000 ALTER TABLE `class_subject` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` (`department_id`, `department_name`) VALUES (6,'Science'),
(7,'Arts'),
(8,'Mathematics'),
(9,'Bussiness'),
(10,'Computer Science');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `marks` int(11) NOT NULL CHECK (`marks` between 0 and 100),
  `term` varchar(20) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `status` enum('pending','approved','published') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`mark_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  CONSTRAINT `marks_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `marks_ibfk_4` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `marks` WRITE;
/*!40000 ALTER TABLE `marks` DISABLE KEYS */;
INSERT INTO `marks` (`mark_id`, `student_id`, `subject_id`, `class_id`, `teacher_id`, `marks`, `term`, `academic_year`, `status`, `created_at`) VALUES (369,66,57,36,38,55,'Term 1','2026','pending','2026-07-19 13:01:19'),
(370,67,57,36,38,32,'Term 1','2026','pending','2026-07-19 13:01:19'),
(371,68,57,36,38,44,'Term 1','2026','pending','2026-07-19 13:01:19');
/*!40000 ALTER TABLE `marks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `school_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_name` varchar(200) DEFAULT NULL,
  `school_code` varchar(10) DEFAULT NULL,
  `school_logo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `motto` varchar(255) DEFAULT NULL,
  `current_academic_year` varchar(20) DEFAULT NULL,
  `current_term` varchar(20) DEFAULT NULL,
  `pass_mark` int(11) DEFAULT 50,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `school_settings` WRITE;
/*!40000 ALTER TABLE `school_settings` DISABLE KEYS */;
INSERT INTO `school_settings` (`id`, `school_name`, `school_code`, `school_logo`, `address`, `phone`, `email`, `motto`, `current_academic_year`, `current_term`, `pass_mark`) VALUES (2,'secondary','0227',NULL,'mwanza',NULL,NULL,NULL,NULL,NULL,50);
/*!40000 ALTER TABLE `school_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `student` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `registration_no` varchar(30) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `academic_year` year(4) NOT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `admission_no` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `admission_no` (`admission_no`),
  UNIQUE KEY `registration_no` (`registration_no`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` (`student_id`, `user_id`, `registration_no`, `class_id`, `date_of_birth`, `academic_year`, `status`, `admission_no`) VALUES (60,109,'14/0227/0001/26',37,'2008-11-21',2026,'active','ADM/2026/001'),
(61,113,'11/0227/0001/26',34,'2008-07-30',2026,'active','ADM/2026/002'),
(62,114,'11/0227/0002/26',34,'2010-07-30',2026,'active','ADM/2026/003'),
(63,115,'12/0227/0001/26',35,'2010-11-30',2026,'active','ADM/2026/004'),
(64,116,'12/0227/0002/26',35,'2009-10-30',2026,'active','ADM/2026/005'),
(65,117,'14/0227/0002/26',37,'2011-12-30',2026,'active','ADM/2026/006'),
(66,118,'13/0227/0001/26',36,'2013-11-30',2026,'active','ADM/2026/007'),
(67,119,'13/0227/0002/26',36,'2010-03-30',2026,'active','ADM/2026/008'),
(68,123,'13/0227/0003/26',36,'2010-02-18',2026,'active','ADM/2026/009'),
(69,124,'14/0227/0003/26',37,'2010-06-08',2026,'active','ADM/2026/010'),
(70,125,'14/0227/0004/26',37,'2011-05-10',2026,'active','ADM/2026/011');
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `student_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `term` varchar(20) NOT NULL,
  `academic_year` year(4) NOT NULL,
  `total_marks` int(11) DEFAULT 0,
  `total_points` int(11) DEFAULT 0,
  `average` decimal(5,2) DEFAULT 0.00,
  `division` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `unique_result` (`student_id`,`class_id`,`term`,`academic_year`),
  KEY `fk_student_results_class` (`class_id`),
  CONSTRAINT `fk_student_results_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_results_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `student_results` WRITE;
/*!40000 ALTER TABLE `student_results` DISABLE KEYS */;
INSERT INTO `student_results` (`result_id`, `student_id`, `class_id`, `term`, `academic_year`, `total_marks`, `total_points`, `average`, `division`, `created_at`) VALUES (42,61,34,'Term 1',2026,0,0,0.00,'INC','2026-07-03 10:30:53'),
(43,62,34,'Term 1',2026,0,0,0.00,'INC','2026-07-03 10:30:53'),
(44,63,35,'Term 1',2026,0,0,0.00,'INC','2026-07-03 10:30:53'),
(45,64,35,'Term 1',2026,0,0,0.00,'INC','2026-07-03 10:30:53'),
(46,66,36,'Term 1',2026,0,0,0.00,'INC','2026-07-04 18:39:12');
/*!40000 ALTER TABLE `student_results` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `fk_subject_department` (`department_id`),
  CONSTRAINT `fk_subject_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `subject` WRITE;
/*!40000 ALTER TABLE `subject` DISABLE KEYS */;
INSERT INTO `subject` (`subject_id`, `subject_name`, `department_id`, `teacher_id`, `created_at`) VALUES (53,'PHYSICS',6,NULL,'2026-06-28 18:40:36'),
(54,'BIOLOGY',6,NULL,'2026-06-28 18:40:36'),
(55,'CHEMISTRY',6,NULL,'2026-06-28 18:40:36'),
(56,'MATHEMATICS',8,NULL,'2026-06-28 18:41:45'),
(57,'ENGLISH',7,NULL,'2026-06-28 18:43:51'),
(58,'HISTORY',7,NULL,'2026-06-28 18:43:51'),
(59,'CIVICS',7,NULL,'2026-06-28 18:43:51'),
(60,'KISWAHILI',7,NULL,'2026-06-28 18:43:51'),
(61,'GEOGRAPHY',7,NULL,'2026-06-28 18:43:51'),
(62,'INFORMATION TECHNOLOGY',10,NULL,'2026-06-28 18:44:43'),
(63,'COMMERCE',9,NULL,'2026-06-28 18:45:15'),
(64,'BOOK KEEPING',9,NULL,'2026-06-28 18:45:15'),
(65,'SOFTWARE AND HARDWARE MAINTAINANCE',10,NULL,'2026-06-30 08:47:24');
/*!40000 ALTER TABLE `subject` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `system_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_backups` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_name` varchar(100) NOT NULL,
  `backup_path` varchar(255) NOT NULL,
  `backup_size` varchar(20) DEFAULT NULL,
  `backup_type` enum('manual','scheduled','auto') DEFAULT 'manual',
  `status` enum('completed','failed','in_progress') DEFAULT 'completed',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `restored_at` timestamp NULL DEFAULT NULL,
  `restored_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_backups` WRITE;
/*!40000 ALTER TABLE `system_backups` DISABLE KEYS */;
INSERT INTO `system_backups` (`backup_id`, `backup_name`, `backup_path`, `backup_size`, `backup_type`, `status`, `created_by`, `created_at`, `restored_at`, `restored_by`) VALUES (1,'backup_2026-07-19_14-10-15.sql','../backups/backup_2026-07-19_14-10-15.sql',' B','manual','completed',108,'2026-07-19 14:10:15',NULL,NULL);
/*!40000 ALTER TABLE `system_backups` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error','critical') DEFAULT 'info',
  `log_module` varchar(50) NOT NULL,
  `log_message` text NOT NULL,
  `log_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`log_details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_log_level` (`log_level`),
  KEY `idx_log_module` (`log_module`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
INSERT INTO `system_logs` (`log_id`, `log_level`, `log_module`, `log_message`, `log_details`, `ip_address`, `user_agent`, `user_id`, `created_at`) VALUES (1,'info','backup','Created database backup: backup_2026-07-19_14-10-15.sql','{\"filename\":\"backup_2026-07-19_14-10-15.sql\",\"size\":\" B\"}','::1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',108,'2026-07-19 14:10:15');
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_type` enum('text','number','boolean','json','textarea','file') DEFAULT 'text',
  `is_editable` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_group`, `setting_type`, `is_editable`, `description`, `created_at`, `updated_at`) VALUES (1,'group','general','api','text',1,NULL,'2026-07-19 14:13:54','2026-07-19 14:14:02');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `fk_teacher_department` (`department_id`),
  CONSTRAINT `fk_teacher_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`),
  CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `teacher` WRITE;
/*!40000 ALTER TABLE `teacher` DISABLE KEYS */;
INSERT INTO `teacher` (`teacher_id`, `user_id`, `phone_no`, `department_id`, `status`) VALUES (37,110,'0679889496',6,'active'),
(38,111,'0679889496',7,'active'),
(39,112,'0679889496',9,'active'),
(40,120,'',8,'active'),
(41,121,'0679889496',6,'active'),
(42,122,'0679889491',10,'active');
/*!40000 ALTER TABLE `teacher` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `teacher_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `teacher_class_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`),
  CONSTRAINT `teacher_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `teacher_class` WRITE;
/*!40000 ALTER TABLE `teacher_class` DISABLE KEYS */;
INSERT INTO `teacher_class` (`id`, `teacher_id`, `class_id`) VALUES (104,37,37),
(105,37,35),
(108,39,34),
(109,39,35),
(110,40,37),
(111,40,35),
(129,41,37),
(130,41,36),
(131,38,34),
(132,38,36),
(133,42,34),
(134,42,36),
(135,42,35);
/*!40000 ALTER TABLE `teacher_class` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `teacher_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_subject` (
  `teacher_subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`teacher_subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `teacher_subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `teacher_subject` WRITE;
/*!40000 ALTER TABLE `teacher_subject` DISABLE KEYS */;
INSERT INTO `teacher_subject` (`teacher_subject_id`, `teacher_id`, `class_id`, `subject_id`, `created_at`) VALUES (57,39,35,63,'2026-06-30 09:15:42'),
(60,40,37,56,'2026-06-30 09:43:04'),
(61,40,35,56,'2026-06-30 09:43:11'),
(62,42,34,62,'2026-06-30 09:45:54'),
(63,42,36,62,'2026-06-30 09:46:01'),
(64,42,35,62,'2026-06-30 09:46:08'),
(66,37,35,55,'2026-07-04 17:56:31'),
(67,38,34,60,'2026-07-04 18:53:36'),
(68,38,34,57,'2026-07-04 18:53:48'),
(69,38,36,57,'2026-07-04 18:54:07'),
(70,38,36,60,'2026-07-04 19:19:03'),
(71,41,36,53,'2026-07-04 19:27:12'),
(73,41,37,53,'2026-07-04 19:27:56'),
(74,37,37,55,'2026-07-18 08:06:26');
/*!40000 ALTER TABLE `teacher_subject` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student','academic') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `phone`, `gender`, `created_at`, `reset_token`, `profile_pic`, `status`) VALUES (108,'ADMIN MNENE','admin@gmail.com','$2y$12$wGA02l/yvuRMVRpMJ6RT9.c2SWZ1k7m6p31kI26WxPd0UVgOoC4Ji','admin','0767823577','male','2026-06-28 16:53:11',NULL,'PROFILE_108_1784033648.jpeg','active'),
(109,'RAMADHAN SALUM','student@gmail.com','$2y$12$8KwrZZOj8zjeoVaiEhAwfOGQTNNWGH8RjWIDy6n26Tq9DxT/KBKKa','student','0679889496','male','2026-06-28 17:23:00',NULL,'PROFILE_109_1784032902.jpeg','active'),
(110,'ELIA ANDREW','teacher@gmail.com','$2y$12$9SFWyETB6vJ1Gao.cu6l9OtOLA/dwCLYQp5Nq9edObugljbRvzF5W','academic','0679889496','male','2026-06-28 17:31:23',NULL,'PROFILE_110_1784032945.jpeg','active'),
(111,'KAFUMU JOHN ','kafumu@gmail.com','$2y$12$d2dx13nX73FEbxAw0fnDH.PWzSYUTEg6y9Znm0/8XNlpBPnhYnzSi','academic',NULL,'male','2026-06-28 20:24:40',NULL,NULL,'active'),
(112,'ELIZABETH ANDREA','eliza@gmail.com','$2y$12$w.fcgrfkpCrGHooHW3HMRex8bzRTt2HuuEa1p0riup9eVpXIKidVm','teacher',NULL,'female','2026-06-30 09:13:52',NULL,NULL,'active'),
(113,'ELISHA JUMA ','elisha@gmail.com','$2y$12$jgrXbs06OGrY1tl23SnrJO/wfEtPETw8qctU..1U15ABolJOb6h6G','student','0679889496','male','2026-06-30 09:23:55',NULL,NULL,'active'),
(114,'DEVOTHA ADBI','devotha@gmail.com','$2y$12$wyWTMmn3gtS/hhqGtxrUHew6Rj4nEa993JQ7pa0a6n7A6j97bQZaS','student','0679889496','female','2026-06-30 09:25:11',NULL,NULL,'active'),
(115,'MATIMBA JAMES','matimba@gmail.com','$2y$12$4lZnqnZALDZc9lFXKcGhbOdTmUTfnbWfBPIhkbuFh8RdGeosCBWMu','student','0679889496','male','2026-06-30 09:27:44',NULL,NULL,'active'),
(116,'TIMOTHEO SANGIJA','sangijatimotheo@gmail.com','$2y$12$Hz67/k/DIvjQFYb9He81ZOInKu5.mxVQiN9derkD8wD4gltAYd/wy','student','0679889496','male','2026-06-30 09:29:28',NULL,NULL,'active'),
(117,'KILEMILE GUSTO','kilemile@gmail.com','$2y$12$4kvIvBWIXPpSVYUesFlGS.Fa1NEHQ7Y.xy4TU2Irccge/soUciOHe','student','0679889487','male','2026-06-30 09:31:53',NULL,NULL,'active'),
(118,'ABDUL AZIZ ABDUL','abdulaziz@gmail.com','$2y$12$XFdYCJsgUc1.tYe9kgKdgupvjzDdsodtYePolj.yTpVbiKO5/e4VW','student','0679872496','male','2026-06-30 09:33:47',NULL,NULL,'active'),
(119,'KELVIN JOHN','john@gmail.com','$2y$12$NmyrHgRUC5ZFTr2E/bNqYOOtWBb04Pdr8irqElwpGQ7fxELN.BxTK','student','0679889496','male','2026-06-30 09:35:15',NULL,NULL,'active'),
(120,'GEOFREY LAMBERT','geofrey@gmail.com','$2y$12$hN..jPKv5rhTIxZ.0WCWhOClz5L4pBiCb.L8gTcktRhwuuKMYbNAa','teacher',NULL,'male','2026-06-30 09:40:04',NULL,NULL,'active'),
(121,'EMMANUEL MNYAMI','emmanuel@gmail.com','$2y$12$hZhfZx.XD9V5eWOETtO/1uztDfA0lHBxOOKEoCQd3zUkgL1keoVEG','teacher','0679889496','male','2026-06-30 09:42:08',NULL,'PROFILE_121_1784032222.jpeg','active'),
(122,'MARCO FIKIRI','marco@gmail.com','$2y$12$Jdv13tN1lYspHXGRRUc6aueep51uFzeECUPrSS2jFDaxB45DPraFW','teacher',NULL,'male','2026-06-30 09:45:32',NULL,NULL,'active'),
(123,'WITNESS LYIMO','witness@gmail.com','$2y$12$f8mOI4ECJ7Gk1rkkC8rS1O8eFM8vWpU9lBzywcQ41jeP4HI10QJd.','student','0679889496','female','2026-07-07 10:25:25',NULL,NULL,'active'),
(124,'MAZIKU  JOHN','mazikuelia2@gmail.com','$2y$12$Om1H74RP9YFxB6tIM0NuPu6jdioV3zp4GPOmWeyGbkZsX4zis/Vl6','student','0679889496','male','2026-07-16 18:22:34',NULL,NULL,'active'),
(125,'OMEGA SADICK','omegasadick@gmail.com','$2y$12$VpxaIubkGrQmb9nKL04kl.4TLesh1St3ltfP5GI3LpGoVybEDFUya','student','0679889642','male','2026-07-19 11:29:49',NULL,NULL,'active');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

