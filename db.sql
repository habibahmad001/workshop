-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 31, 2026 at 05:14 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unfpa_mis`
--

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `executed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `executed_at`) VALUES
(1, '2026-05-22_000000_create_initial_schema.php', '2026-05-22 18:02:02'),
(2, '2026-05-22_001000_add_user_profile_fields.php', '2026-05-22 18:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Dashboard', 'dashboard', 'Main dashboard overview', '2026-05-22 18:02:02'),
(2, 'Participants', 'participants', 'Participant management', '2026-05-22 18:02:02'),
(3, 'Workshops', 'workshops', 'Workshop management', '2026-05-22 18:02:02'),
(4, 'Analytics', 'analytics', 'Reports and analytics', '2026-05-22 18:02:02'),
(5, 'Export', 'export', 'Data export', '2026-05-22 18:02:02'),
(6, 'Users', 'users', 'User management', '2026-05-22 18:02:02'),
(7, 'Roles', 'roles', 'Role management', '2026-05-22 18:02:02'),
(8, 'Modules', 'modules', 'Module permission management', '2026-05-22 18:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

DROP TABLE IF EXISTS `participants`;
CREATE TABLE IF NOT EXISTS `participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workshop_id` int DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Female','Male') COLLATE utf8mb4_unicode_ci DEFAULT 'Female',
  `attended` tinyint(1) DEFAULT '1',
  `photo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `workshop_id` (`workshop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `name`, `designation`, `workshop_id`, `province`, `contact`, `email`, `gender`, `attended`, `photo`, `created_at`) VALUES
(1, 'Dr Ghulam Jelani', 'Consultant PAEDS', 3, 'Balochistan', '0333-2226963', 'Ghulam.jelani01@tih.org.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(2, 'Ms Ghulam Ftaima', 'Principal', 3, 'Balochistan', '0333-4833110', 'Ghulam.shah@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(3, 'Mr Nadeem Shah', 'SBCC Manager', 3, 'Balochistan', '0302-838889', 'Nadeem.shah@jhpeige.org.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(4, 'Dr Samina Bugti', 'DPC MNCH', 3, 'Balochistan', '0335-1316770', 'Dr.saminabugti84@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(5, 'Mr Dilbar Khan', 'DL HPV', 3, 'Balochistan', '0321-5541980', 'Dilbar.khan@jhpiego.org.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(6, 'Mr. Abbas Khan', 'PM HPV', 3, 'Balochistan', '0380-5995960', 'Muhammadabbas.khan@jhpiego.org', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(7, 'Dr. Sajid', 'PC MCH', 3, 'Balochistan', '0300-3866714', 'Dr.sajidpanezai@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(8, 'Dr Hashim', 'Director Public Health', 3, 'Balochistan', '0333-7816382', 'Doctorhashim@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(9, 'Dr. Misbah Hayat', 'QA DHQ', 3, 'Balochistan', '0333-8718469', 'Ayat.burhan786@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(10, 'Dr Gull', 'PC MNCH', 3, 'Balochistan', '', '', 'Male', 0, NULL, '2026-05-13 17:25:01'),
(11, 'Mr Atta Ullah', 'CEO', 3, 'Balochistan', '0300-9386424', '', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(12, 'Dr Riffat Jaleel', 'Assistant Professor Obs/Gyn', 4, 'Sindh', '0333-2350677', 'riffat.jaleel@duhs.edu.pk', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(13, 'Dr Sumbal Saeed', 'Consultant', 4, 'Sindh', '0333-5457209', 'saeedsu@who.int', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(14, 'Dr. Arifa Jabeen', 'Assistant Professor Obs/Gyn', 4, 'Sindh', '0312-7590086', '', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(15, 'Dr Aisha Khan', 'Resident', 4, 'Sindh', '0333-3733724', 'Aishajatoi2@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(16, 'Dr. Riffat Jan', 'President', 4, 'Sindh', '0321-2422661', 'midwiferyassociationofpakistan@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(17, 'Mr. Maqsood Ahmed Khan', 'Director Market Access', 4, 'Sindh', '0321-5855764', 'maqsood.khan@roche.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(18, 'Dr. Duree e Shahwar', 'Paediatric Consultant', 4, 'Sindh', '0333-316671', '', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(19, 'Dr Fatima Bibi', 'Assistant Professor', 4, 'Sindh', '0311-9464648', 'Dr.fatimalashari@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(20, 'Dr. Uzma Chishti', 'Gynecologic Oncologist', 4, 'Sindh', '0302-8247500', 'uzma.chishti@aku.edu', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(21, 'Mr Nasir Ali', 'Associate', 4, 'Sindh', '0300-6846810', 'Nasirali@aku.edu.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(22, 'Zahida Kakar', 'Principal', 2, 'Balochistan', '0331-8354853', 'zahidakakar82@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(23, 'Ms. Afifah', 'T.N.T.E Monitor', 2, 'Balochistan', '0331-8090551', 'afifarawal.ar@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(24, 'Ms. Rukhsana Dost', 'Midwifery Tutor', 2, 'Balochistan', '0336-8178682', 'rukhsanamuhd44@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(25, 'Saima Batool', 'AHI MCH Services', 2, 'Balochistan', '0333-0380293', 'Saimaawan2013@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(26, 'Ms Summaya Gull', 'Nursing Instructor', 2, 'Balochistan', '0336-2819051', 'sumyagul98@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(27, 'Ms Shahnaz Noor', 'Nursing Instructor', 2, 'Balochistan', '0333-8895052', 'shahnaznoor4969@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(28, 'Dr.Sabiha Khanum', 'Associate Professor', 2, 'KPK', '0315-9379308', 'sabiha.ins@kmu.edu.pk', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(29, 'Ms. Sehrish Naz', 'Assistant Professor', 2, 'KPK', '0311-9464648', 'sehrish.ins@kmu.edu.pk', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(30, 'Ms. Shahla Arshad', 'Lecturer', 2, 'KPK', '0305-5021052', 'shahla.arshad.kmu@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(31, 'Ms.Shabnam', 'Lecturer', 2, 'KPK', '0336-9292443', 'shabnam.ins@kmu.edu.pk', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(32, 'Ms. Aafia Sadiq', 'Deputy Nursing Superintendent', 2, 'AJK', '0334-5115897', 'Aafiasadiq73@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(33, 'Ms. Shagufta Aziz', 'Clinical Instructor', 2, 'AJK', '0333-5762797', 'zachdry7@hotmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(34, 'Ms. Kalsoom Anjum', 'Nursing Instructor', 2, 'AJK', '0341-5075655', 'kalsumanjum.bagh@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(35, 'Mr Saqib Yaqoob', 'Procurement & Supply Chain Coordinator', 5, 'Punjab', '0334-9592725', 'Saqibyaqoob22@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(36, 'Dr Tanveer Malik', 'FGPC PGMI', 5, 'Punjab', '0333-5219921', 'Tamalik76@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(37, 'Mr Irfan ullah Khan', 'Manager Supply Chain', 5, 'Punjab', '0342-2122666', 'ikhan@cmu.gov.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(38, 'Mr Khawaja Maqsood', 'Assistant Director', 5, 'Punjab', '0300-5252685', 'Maqsoodkhawaja71@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(39, 'Dr. M Anwar Butt', 'Deputy Director', 5, 'Punjab', '0333-5884817', 'Mabutt2021@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(40, 'Mr Imran', 'Consultant', 5, 'Punjab', '0322-7171085', 'Imranhamid0@gmail.com', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(41, 'Dr Sabeen Afzal', 'JTD FGPC', 5, 'Punjab', '0333-0550127', 'Drsabeenafzal@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(42, 'Prof Shakila Zaman', 'Professor', 5, 'Punjab', '0300-8883268', 'Zaman.shakeela@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(43, 'Dr Shahzad Ali', 'VC', 5, 'Punjab', '', '', 'Male', 0, NULL, '2026-05-13 17:25:01'),
(44, 'Dr Jamil Ahmed', 'Program Assistant', 5, 'Punjab', '0300-9519707', 'jahmed@unfpa.org', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(45, 'Mr Inam Ullah', 'Officer', 5, 'Punjab', '0321-5371001', 'ikhan@unfpa.org', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(46, 'Dr Faisal', 'Assistant', 5, 'Punjab', '0321-5859956', 'Faisal@hsa.edu.pk', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(47, 'Dr Anila Bacha', 'F.P MCH/RH', 6, 'Punjab', '0341-2227980', 'Dr-enu-786@hotmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(48, 'Ms Arsh Bibi', 'Deputy Nursing Suptd', 6, 'Punjab', '0333-9843962', 'Arshbibi74@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(49, 'Ms Saniya Sultan', 'CE Advisor', 6, 'Punjab', '0333-2639499', 'Sultanis@ipar.org', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(50, 'Ms Musarrat Rani', 'V. Principal', 6, 'Punjab', '0332-3780578', 'Musarratrani81@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(51, 'Ms Fouzia Bhatti', 'Principal', 6, 'Punjab', '0332-5509200', 'Fouziabhatti2@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(52, 'Ms Surriya', 'Principal', 6, 'Punjab', '0332-3952017', 'Surriya.faheem16@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(53, 'Ms Rubina', 'Principal', 6, 'Punjab', '0300-1244026', 'rubinasehar@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(54, 'Dr mobashra Batool', 'Principal', 6, 'Punjab', '0313-5193337', 'drmobashrabatool@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(55, 'Dr Nusrat Gulraz', 'Principal', 6, 'Punjab', '0347-5087038', 'Dr.nusratbhatti786@gmail.com', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(56, 'Zubeda Bhutoo', 'Assistant Professor', 6, 'Sindh', '0305-3648038', 'Zubeda.bhutto@duhs.edu.pk', 'Female', 1, NULL, '2026-05-13 17:25:01'),
(57, 'Dr Jawad Afzal', 'Director Health', 6, 'AJK', '0312-9117945', '', 'Male', 1, NULL, '2026-05-13 17:25:01'),
(58, 'Ms Naveela Kausar', 'Principal', 6, 'Punjab', '0333-5711024', 'naveelakausar@yahoo.com', 'Female', 0, NULL, '2026-05-13 17:25:01'),
(60, 'Qazi', 'Principal', 7, 'Isb', '0333-5711024', 'ibtasamahmed02@gmail.com', 'Male', 1, '', '2026-05-13 17:34:43');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_by`, `created_at`) VALUES
(1, 'Super Admin', NULL, '2026-05-22 18:02:02'),
(2, 'Admin', NULL, '2026-05-22 18:02:02'),
(3, 'Manager', NULL, '2026-05-22 18:02:02'),
(4, 'Lead', NULL, '2026-05-22 18:02:02'),
(5, 'User', NULL, '2026-05-22 18:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `role_modules`
--

DROP TABLE IF EXISTS `role_modules`;
CREATE TABLE IF NOT EXISTS `role_modules` (
  `role_id` int NOT NULL,
  `module_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`module_id`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_modules`
--

INSERT INTO `role_modules` (`role_id`, `module_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(4, 1),
(4, 4),
(5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bio` text,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `status`, `created_at`, `updated_at`, `bio`, `last_login`) VALUES
(1, 'Super Admin', 'admin@unfpa.local', '$2y$10$TIBGlmxVFIwGV2BDs0EHFOobvRD3IqQjBeFfd8vlhDDIP/8WciXuW', 1, 'active', '2026-05-22 18:02:02', '2026-05-22 18:04:21', NULL, NULL),
(2, 'Habib habib Ahmad', 'habibahmad001@gmail.com', '$2y$10$36tkudumA0HYFwDe4DW6aebgAPLf1e2lNc1YLxRh05.JARbtycviy', 2, 'active', '2026-05-22 18:11:40', '2026-05-22 18:13:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workshops`
--

DROP TABLE IF EXISTS `workshops`;
CREATE TABLE IF NOT EXISTS `workshops` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workshops`
--

INSERT INTO `workshops` (`id`, `title`, `code`, `date`, `location`, `created_at`) VALUES
(1, 'FDT Phase 1', 'FDT-P1', '2025-09-15', 'Islamabad', '2026-05-13 17:25:01'),
(2, 'FDT Phase 2', 'FDT-P2', '2025-10-20', 'Islamabad', '2026-05-13 17:25:01'),
(3, 'Cervical Cancer - Quetta', 'CC-QTA', '2025-11-05', 'Quetta', '2026-05-13 17:25:01'),
(4, 'Cervical Cancer - Sindh', 'CC-SND', '2025-11-12', 'Karachi', '2026-05-13 17:25:01'),
(5, 'Supply Chain Management', 'SC', '2025-12-15', 'Islamabad', '2026-05-13 17:25:01'),
(6, 'ADM Curriculum Consultative Mtg', 'CM', '2025-12-05', 'Islamabad', '2026-05-13 17:25:01'),
(7, 'IDM', '457354', '2026-05-14', 'Islamabad', '2026-05-13 17:34:12');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
