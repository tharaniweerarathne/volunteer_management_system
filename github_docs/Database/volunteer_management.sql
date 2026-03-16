-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 09, 2026 at 01:16 PM
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
-- Database: `volunteer_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE IF NOT EXISTS `attendance` (
  `attendanceId` int NOT NULL AUTO_INCREMENT,
  `eventId` int NOT NULL,
  `userId` int NOT NULL,
  `attendanceDate` date NOT NULL,
  `status` enum('Present','Absent') DEFAULT 'Absent',
  `markedBy` int NOT NULL,
  `markedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remarks` text,
  PRIMARY KEY (`attendanceId`),
  UNIQUE KEY `unique_attendance` (`eventId`,`userId`,`attendanceDate`),
  KEY `userId` (`userId`),
  KEY `markedBy` (`markedBy`)
) ENGINE=MyISAM AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendanceId`, `eventId`, `userId`, `attendanceDate`, `status`, `markedBy`, `markedAt`, `remarks`) VALUES
(6, 26, 50, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(7, 26, 51, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(8, 26, 52, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(9, 26, 53, '2026-02-21', 'Absent', 39, '2026-02-20 23:02:29', ''),
(10, 26, 54, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(11, 26, 55, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(12, 26, 56, '2026-02-21', 'Absent', 39, '2026-02-20 23:02:29', ''),
(13, 26, 57, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(14, 26, 58, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(15, 26, 59, '2026-02-21', 'Present', 39, '2026-02-20 23:02:29', ''),
(16, 26, 71, '2026-02-21', 'Absent', 39, '2026-02-20 23:02:29', ''),
(17, 27, 61, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(18, 27, 62, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(19, 27, 63, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(20, 27, 64, '2026-02-21', 'Absent', 39, '2026-02-21 04:51:05', ''),
(21, 27, 65, '2026-02-21', 'Absent', 39, '2026-02-21 04:51:05', ''),
(22, 27, 66, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(23, 27, 67, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(24, 27, 68, '2026-02-21', 'Absent', 39, '2026-02-21 04:51:05', ''),
(25, 27, 69, '2026-02-21', 'Present', 39, '2026-02-21 04:51:05', ''),
(26, 29, 51, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(27, 29, 52, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(28, 29, 53, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(29, 29, 55, '2026-02-21', 'Absent', 48, '2026-02-23 02:04:15', 'Did not attend'),
(30, 29, 56, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(31, 29, 61, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(32, 29, 65, '2026-02-21', 'Present', 48, '2026-02-23 02:04:15', ''),
(33, 29, 66, '2026-02-21', 'Absent', 48, '2026-02-23 02:04:15', 'Did not attend'),
(34, 29, 68, '2026-02-23', 'Present', 48, '2026-02-23 02:04:15', ''),
(35, 30, 50, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(36, 30, 51, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(37, 30, 52, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(38, 30, 53, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(39, 30, 54, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(40, 30, 55, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(41, 30, 56, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(42, 30, 57, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(43, 30, 59, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(44, 30, 60, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(45, 30, 61, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(46, 30, 62, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(47, 30, 63, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(48, 30, 64, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(49, 30, 65, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(50, 30, 66, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(51, 30, 67, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(52, 30, 69, '2026-02-23', 'Present', 43, '2026-02-23 07:00:46', ''),
(53, 30, 71, '2026-02-23', 'Absent', 43, '2026-02-23 07:00:46', 'Did not attend'),
(54, 31, 50, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(55, 31, 52, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(56, 31, 53, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(57, 31, 57, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(58, 31, 58, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(59, 31, 60, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(60, 31, 61, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(61, 31, 63, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(62, 31, 64, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(63, 31, 66, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(64, 31, 67, '2026-02-24', 'Absent', 44, '2026-02-24 01:34:24', 'Did not attend'),
(65, 31, 68, '2026-02-24', 'Absent', 44, '2026-02-24 01:34:24', 'Did not attend'),
(66, 31, 69, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(67, 31, 71, '2026-02-24', 'Present', 44, '2026-02-24 01:34:24', ''),
(68, 32, 50, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(69, 32, 52, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(70, 32, 53, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(71, 32, 55, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(72, 32, 57, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(73, 32, 58, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(74, 32, 60, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(75, 32, 61, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(76, 32, 63, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(77, 32, 64, '2026-02-25', 'Present', 42, '2026-02-25 04:00:15', ''),
(78, 33, 50, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(79, 33, 53, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(80, 33, 54, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(81, 33, 55, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(82, 33, 56, '2026-02-25', 'Absent', 39, '2026-02-25 02:38:01', 'Did not attend'),
(83, 33, 57, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(84, 33, 59, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(85, 33, 61, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(86, 33, 63, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(87, 33, 64, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(88, 33, 66, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(89, 33, 67, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(90, 33, 68, '2026-02-25', 'Present', 39, '2026-02-25 02:38:01', ''),
(91, 33, 71, '2026-02-25', 'Absent', 39, '2026-02-25 02:38:01', 'Did not attend'),
(92, 34, 50, '2026-02-25', 'Absent', 41, '2026-02-25 12:35:15', 'Did not attend'),
(93, 34, 51, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(94, 34, 53, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(95, 34, 54, '2026-02-25', 'Absent', 41, '2026-02-25 12:35:15', 'Did not attend'),
(96, 34, 55, '2026-02-25', 'Absent', 41, '2026-02-25 12:35:15', 'Did not attend'),
(97, 34, 57, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(98, 34, 60, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(99, 34, 61, '2026-02-25', 'Absent', 41, '2026-02-25 12:35:15', 'Did not attend'),
(100, 34, 63, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(101, 34, 64, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(102, 34, 65, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(103, 34, 66, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(104, 34, 67, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(105, 34, 68, '2026-02-25', 'Present', 41, '2026-02-25 12:35:15', ''),
(106, 35, 50, '2026-02-25', 'Present', 45, '2026-02-25 13:10:20', ''),
(107, 35, 53, '2026-02-25', 'Present', 45, '2026-02-25 13:10:20', ''),
(108, 35, 55, '2026-02-25', 'Present', 45, '2026-02-25 13:10:20', ''),
(109, 36, 53, '2026-02-25', 'Present', 47, '2026-02-25 14:20:10', ''),
(110, 36, 54, '2026-02-25', 'Present', 47, '2026-02-25 14:20:10', ''),
(111, 37, 55, '2026-02-25', 'Present', 39, '2026-02-25 14:20:10', ''),
(112, 37, 56, '2026-02-25', 'Present', 39, '2026-02-25 14:20:10', ''),
(113, 38, 55, '2026-02-25', 'Present', 44, '2026-02-25 14:35:10', ''),
(114, 38, 56, '2026-02-25', 'Present', 44, '2026-02-25 14:35:10', ''),
(115, 39, 58, '2026-02-25', 'Present', 49, '2026-02-25 15:10:10', ''),
(116, 39, 59, '2026-02-25', 'Present', 49, '2026-02-25 15:10:10', ''),
(117, 39, 60, '2026-02-25', 'Present', 49, '2026-02-25 15:10:10', ''),
(118, 39, 61, '2026-02-25', 'Present', 49, '2026-02-25 15:10:10', ''),
(119, 40, 63, '2026-02-25', 'Present', 42, '2026-02-25 15:10:10', ''),
(120, 40, 65, '2026-02-25', 'Present', 42, '2026-02-25 15:10:10', ''),
(121, 40, 66, '2026-02-25', 'Present', 42, '2026-02-25 15:10:10', ''),
(122, 40, 68, '2026-02-25', 'Present', 42, '2026-02-25 15:10:10', ''),
(123, 42, 51, '2026-02-25', 'Present', 46, '2026-02-25 04:50:05', ''),
(124, 42, 52, '2026-02-25', 'Present', 46, '2026-02-25 04:50:05', ''),
(125, 42, 53, '2026-02-25', 'Present', 46, '2026-02-25 04:50:05', ''),
(126, 42, 55, '2026-02-25', 'Present', 46, '2026-02-25 04:50:05', ''),
(127, 44, 57, '2026-02-25', 'Present', 41, '2026-02-25 05:50:05', ''),
(128, 44, 59, '2026-02-25', 'Present', 41, '2026-02-25 05:50:05', ''),
(129, 46, 57, '2026-02-26', 'Present', 47, '2026-02-26 06:50:05', ''),
(130, 46, 58, '2026-02-26', 'Absent', 47, '2026-02-26 06:50:05', 'Did not attend'),
(131, 46, 59, '2026-02-26', 'Present', 47, '2026-02-26 06:50:05', ''),
(132, 47, 60, '2026-02-26', 'Present', 45, '2026-02-25 19:40:08', ''),
(133, 47, 61, '2026-02-26', 'Present', 45, '2026-02-25 19:40:08', ''),
(134, 47, 62, '2026-02-26', 'Present', 45, '2026-02-25 19:40:08', ''),
(135, 50, 60, '2026-02-26', 'Present', 44, '2026-02-25 23:50:21', ''),
(136, 50, 62, '2026-02-26', 'Present', 44, '2026-02-25 23:50:21', ''),
(137, 51, 60, '2026-02-26', 'Present', 49, '2026-02-26 00:50:21', ''),
(138, 51, 61, '2026-02-26', 'Present', 49, '2026-02-26 00:50:21', ''),
(139, 51, 62, '2026-02-26', 'Present', 49, '2026-02-26 00:50:21', ''),
(140, 52, 64, '2026-02-26', 'Present', 45, '2026-02-26 05:52:25', ''),
(141, 52, 65, '2026-02-26', 'Present', 45, '2026-02-26 05:52:25', ''),
(142, 52, 67, '2026-02-26', 'Absent', 45, '2026-02-26 05:52:25', 'Did not attend'),
(143, 53, 51, '2026-02-26', 'Present', 47, '2026-02-26 07:22:25', ''),
(144, 53, 53, '2026-02-26', 'Present', 47, '2026-02-26 07:22:25', ''),
(145, 53, 68, '2026-02-26', 'Present', 47, '2026-02-26 07:22:25', ''),
(146, 53, 69, '2026-02-26', 'Present', 47, '2026-02-26 07:22:25', ''),
(147, 53, 70, '2026-02-26', 'Present', 47, '2026-02-26 07:22:25', ''),
(148, 55, 56, '2026-02-26', 'Present', 47, '2026-02-26 08:15:25', ''),
(149, 55, 71, '2026-02-26', 'Present', 47, '2026-02-26 08:15:25', ''),
(150, 57, 55, '2026-02-26', 'Present', 42, '2026-02-26 08:40:20', ''),
(151, 57, 56, '2026-02-26', 'Present', 42, '2026-02-26 08:40:20', ''),
(152, 57, 57, '2026-02-26', 'Present', 42, '2026-02-26 08:40:20', ''),
(153, 58, 51, '2026-02-26', 'Present', 46, '2026-02-26 09:50:30', ''),
(154, 58, 52, '2026-02-26', 'Present', 46, '2026-02-26 09:50:30', ''),
(155, 58, 56, '2026-02-26', 'Present', 46, '2026-02-26 09:50:30', ''),
(156, 58, 60, '2026-02-26', 'Present', 46, '2026-02-26 09:50:30', ''),
(157, 58, 61, '2026-02-26', 'Present', 46, '2026-02-26 09:50:30', ''),
(158, 59, 54, '2026-02-26', 'Present', 43, '2026-02-26 10:20:30', ''),
(159, 59, 55, '2026-02-26', 'Absent', 43, '2026-02-26 10:20:30', 'Did not attend'),
(160, 60, 62, '2026-02-26', 'Present', 40, '2026-02-26 10:40:30', ''),
(161, 60, 63, '2026-02-26', 'Present', 40, '2026-02-26 10:40:30', ''),
(162, 62, 52, '2026-02-26', 'Present', 41, '2026-02-26 12:15:30', ''),
(163, 62, 57, '2026-02-26', 'Present', 41, '2026-02-26 12:15:30', ''),
(164, 62, 59, '2026-02-26', 'Present', 41, '2026-02-26 12:15:30', ''),
(165, 62, 63, '2026-02-26', 'Present', 41, '2026-02-26 12:15:30', ''),
(166, 63, 51, '2026-02-26', 'Present', 45, '2026-02-26 12:45:05', ''),
(167, 63, 52, '2026-02-26', 'Absent', 45, '2026-02-26 12:45:05', 'Did not attend'),
(168, 63, 54, '2026-02-26', 'Present', 45, '2026-02-26 12:45:05', ''),
(169, 64, 62, '2026-02-26', 'Present', 47, '2026-02-26 13:15:05', ''),
(170, 64, 71, '2026-02-26', 'Present', 47, '2026-02-26 13:15:05', ''),
(171, 65, 50, '2026-02-26', 'Present', 42, '2026-02-26 13:15:05', ''),
(172, 65, 53, '2026-02-26', 'Present', 42, '2026-02-26 13:15:05', ''),
(173, 67, 52, '2026-02-26', 'Present', 47, '2026-02-26 13:15:05', ''),
(174, 67, 57, '2026-02-26', 'Present', 47, '2026-02-26 13:15:05', ''),
(175, 67, 59, '2026-02-26', 'Present', 47, '2026-02-26 13:15:05', ''),
(176, 69, 56, '2026-02-26', 'Present', 39, '2026-02-26 16:50:05', ''),
(177, 69, 67, '2026-02-26', 'Present', 39, '2026-02-26 16:50:05', ''),
(178, 69, 68, '2026-02-26', 'Present', 39, '2026-02-26 16:50:05', ''),
(179, 71, 64, '2026-02-26', 'Present', 42, '2026-02-26 17:45:05', ''),
(180, 71, 65, '2026-02-26', 'Present', 42, '2026-02-26 17:45:05', ''),
(181, 71, 66, '2026-02-26', 'Present', 42, '2026-02-26 17:45:05', ''),
(182, 72, 51, '2026-02-27', 'Present', 45, '2026-02-27 11:45:05', ''),
(183, 72, 54, '2026-02-27', 'Present', 45, '2026-02-27 11:45:05', ''),
(184, 72, 55, '2026-02-27', 'Present', 45, '2026-02-27 11:45:05', ''),
(185, 73, 52, '2026-02-27', 'Present', 45, '2026-02-27 11:45:05', ''),
(186, 73, 53, '2026-02-27', 'Present', 45, '2026-02-27 11:45:05', ''),
(187, 74, 51, '2026-02-27', 'Absent', 39, '2026-02-27 12:15:05', 'Did not attend'),
(188, 74, 53, '2026-02-27', 'Present', 39, '2026-02-27 12:15:05', ''),
(189, 74, 54, '2026-02-27', 'Absent', 39, '2026-02-27 12:15:05', 'Did not attend'),
(190, 74, 61, '2026-02-27', 'Present', 39, '2026-02-27 12:15:05', ''),
(191, 75, 55, '2026-02-27', 'Absent', 44, '2026-02-27 12:15:05', 'Did not attend'),
(192, 75, 57, '2026-02-27', 'Present', 44, '2026-02-27 12:15:05', ''),
(193, 79, 50, '2026-03-01', 'Present', 45, '2026-03-01 04:15:05', ''),
(194, 79, 51, '2026-03-01', 'Present', 45, '2026-03-01 04:15:05', ''),
(195, 80, 55, '2026-03-01', 'Present', 47, '2026-03-01 04:15:05', ''),
(196, 80, 58, '2026-03-01', 'Present', 47, '2026-03-01 04:15:05', ''),
(197, 80, 66, '2026-03-01', 'Present', 47, '2026-03-01 04:15:05', ''),
(198, 84, 58, '2026-03-01', 'Present', 42, '2026-03-01 05:25:05', ''),
(199, 84, 66, '2026-03-01', 'Present', 42, '2026-03-01 05:25:05', ''),
(200, 85, 52, '2026-03-01', 'Present', 42, '2026-03-01 05:25:05', ''),
(201, 86, 51, '2026-03-01', 'Absent', 46, '2026-03-01 05:25:05', 'Did not attend'),
(202, 86, 58, '2026-03-01', 'Present', 46, '2026-03-01 05:25:05', ''),
(203, 86, 65, '2026-03-01', 'Absent', 46, '2026-03-01 05:25:05', 'Did not attend'),
(204, 87, 52, '2026-03-01', 'Present', 43, '2026-03-01 05:45:05', ''),
(205, 87, 53, '2026-03-01', 'Present', 43, '2026-03-01 05:45:05', ''),
(206, 89, 51, '2026-03-01', 'Present', 40, '2026-03-01 06:15:05', ''),
(207, 89, 52, '2026-03-01', 'Present', 40, '2026-03-01 06:15:05', ''),
(208, 89, 53, '2026-03-01', 'Present', 40, '2026-03-01 06:15:05', ''),
(209, 90, 54, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(210, 90, 55, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(211, 90, 56, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(212, 90, 57, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(213, 91, 62, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(214, 91, 67, '2026-03-01', 'Present', 41, '2026-03-01 06:15:05', ''),
(215, 92, 55, '2026-03-01', 'Present', 47, '2026-03-01 06:45:05', ''),
(216, 92, 62, '2026-03-01', 'Present', 47, '2026-03-01 06:45:05', ''),
(217, 93, 63, '2026-03-01', 'Present', 44, '2026-03-01 06:45:05', ''),
(218, 93, 64, '2026-03-01', 'Present', 44, '2026-03-01 06:45:05', ''),
(219, 96, 64, '2026-03-01', 'Present', 45, '2026-03-01 07:45:05', ''),
(220, 97, 50, '2026-03-01', 'Present', 44, '2026-03-01 07:45:05', ''),
(221, 97, 51, '2026-03-01', 'Present', 44, '2026-03-01 07:45:05', ''),
(222, 97, 52, '2026-03-01', 'Present', 44, '2026-03-01 07:45:05', ''),
(223, 98, 53, '2026-03-01', 'Present', 49, '2026-03-01 07:45:05', ''),
(224, 98, 54, '2026-03-01', 'Present', 49, '2026-03-01 07:45:05', ''),
(225, 98, 55, '2026-03-01', 'Present', 49, '2026-03-01 07:45:05', ''),
(226, 100, 54, '2026-03-01', 'Present', 42, '2026-03-01 08:15:05', ''),
(227, 100, 60, '2026-03-01', 'Present', 42, '2026-03-01 08:15:05', ''),
(228, 100, 67, '2026-03-01', 'Present', 42, '2026-03-01 08:15:05', ''),
(229, 101, 55, '2026-03-01', 'Present', 46, '2026-03-01 08:15:05', ''),
(230, 101, 58, '2026-03-01', 'Present', 46, '2026-03-01 08:15:05', ''),
(231, 101, 60, '2026-03-01', 'Present', 46, '2026-03-01 08:15:05', ''),
(232, 102, 50, '2026-03-01', 'Present', 42, '2026-02-28 20:35:05', ''),
(233, 102, 51, '2026-03-01', 'Present', 42, '2026-02-28 20:35:05', ''),
(234, 104, 52, '2026-03-01', 'Present', 45, '2026-03-01 08:35:05', ''),
(235, 104, 61, '2026-03-01', 'Present', 45, '2026-03-01 08:35:05', ''),
(236, 105, 53, '2026-03-01', 'Present', 47, '2026-03-01 10:35:05', ''),
(237, 105, 55, '2026-03-01', 'Present', 47, '2026-03-01 10:35:05', ''),
(238, 105, 56, '2026-03-01', 'Present', 47, '2026-03-01 10:35:05', ''),
(239, 106, 60, '2026-03-01', 'Present', 39, '2026-03-01 10:35:05', ''),
(240, 106, 61, '2026-03-01', 'Present', 39, '2026-03-01 10:35:05', ''),
(241, 110, 51, '2026-03-01', 'Present', 39, '2026-03-01 09:35:05', ''),
(242, 110, 52, '2026-03-01', 'Present', 39, '2026-03-01 09:35:05', ''),
(243, 111, 53, '2026-03-01', 'Present', 47, '2026-03-01 11:45:05', ''),
(244, 113, 53, '2026-03-01', 'Present', 40, '2026-03-01 13:45:05', ''),
(245, 113, 53, '2026-03-02', 'Present', 45, '2026-03-02 13:45:05', ''),
(246, 114, 52, '2026-03-02', 'Present', 45, '2026-03-01 23:45:05', ''),
(247, 114, 53, '2026-03-02', 'Present', 45, '2026-03-01 23:45:05', ''),
(248, 114, 55, '2026-03-02', 'Present', 45, '2026-03-01 23:45:05', ''),
(249, 115, 56, '2026-03-02', 'Present', 45, '2026-03-02 01:15:05', ''),
(250, 115, 57, '2026-03-02', 'Present', 45, '2026-03-02 01:15:05', ''),
(251, 115, 58, '2026-03-02', 'Present', 45, '2026-03-02 01:15:05', ''),
(252, 117, 63, '2026-03-02', 'Present', 44, '2026-03-02 01:15:05', ''),
(253, 118, 64, '2026-03-02', 'Present', 49, '2026-03-02 02:15:05', ''),
(254, 118, 65, '2026-03-02', 'Present', 49, '2026-03-02 02:15:05', ''),
(255, 119, 67, '2026-03-02', 'Present', 42, '2026-03-02 02:15:05', ''),
(256, 119, 68, '2026-03-02', 'Present', 42, '2026-03-02 02:15:05', ''),
(257, 121, 50, '2026-03-02', 'Present', 45, '2026-03-02 03:15:05', ''),
(258, 122, 56, '2026-03-02', 'Absent', 39, '2026-03-02 03:15:05', 'Did not attend'),
(259, 122, 62, '2026-03-02', 'Present', 39, '2026-03-02 03:15:05', ''),
(260, 124, 51, '2026-03-02', 'Present', 45, '2026-03-02 05:15:05', ''),
(261, 124, 52, '2026-03-02', 'Present', 45, '2026-03-02 05:15:05', ''),
(262, 124, 53, '2026-03-02', 'Present', 45, '2026-03-02 05:15:05', ''),
(263, 124, 61, '2026-03-02', 'Absent', 45, '2026-03-02 05:15:05', 'Did not attend'),
(264, 125, 54, '2026-03-02', 'Present', 47, '2026-03-02 05:15:05', ''),
(265, 125, 55, '2026-03-02', 'Present', 47, '2026-03-02 05:15:05', ''),
(266, 126, 59, '2026-03-02', 'Present', 39, '2026-03-02 07:15:05', ''),
(267, 126, 60, '2026-03-02', 'Present', 39, '2026-03-02 07:15:05', ''),
(268, 128, 51, '2026-03-02', 'Present', 45, '2026-03-02 10:15:05', ''),
(269, 128, 52, '2026-03-02', 'Present', 45, '2026-03-02 10:15:05', ''),
(270, 130, 71, '2026-03-03', 'Present', 47, '2026-03-03 15:14:31', ''),
(271, 135, 93, '2026-03-07', 'Present', 39, '2026-03-07 16:17:51', ''),
(272, 135, 93, '2026-03-08', 'Present', 39, '2026-03-08 07:19:05', '');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE IF NOT EXISTS `certificates` (
  `certificateId` int NOT NULL AUTO_INCREMENT,
  `eventId` int NOT NULL,
  `userId` int NOT NULL,
  `certificateNumber` varchar(50) NOT NULL,
  `issueDate` date NOT NULL,
  `filePath` varchar(255) NOT NULL,
  `issuedBy` int NOT NULL,
  `issuedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`certificateId`),
  UNIQUE KEY `certificateNumber` (`certificateNumber`),
  UNIQUE KEY `unique_certificate` (`eventId`,`userId`),
  KEY `userId` (`userId`),
  KEY `issuedBy` (`issuedBy`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificateId`, `eventId`, `userId`, `certificateNumber`, `issueDate`, `filePath`, `issuedBy`, `issuedAt`) VALUES
(3, 18, 71, 'CERT-20251215-ED760B1C', '2025-12-15', 'assets/certificates/certificate_CERT-20251215-ED760B1C.pdf', 38, '2025-12-15 14:42:20'),
(8, 135, 93, 'CERT-20260307-F7C708BA', '2026-03-07', 'assets/certificates/certificate_CERT-20260307-F7C708BA.pdf', 38, '2026-03-07 17:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `messageId` int NOT NULL AUTO_INCREMENT,
  `senderName` varchar(100) NOT NULL,
  `senderEmail` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied','archived') DEFAULT 'pending',
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `repliedBy` int DEFAULT NULL,
  `replyMessage` text,
  `repliedAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`messageId`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`createdAt`),
  KEY `idx_email` (`senderEmail`),
  KEY `repliedBy` (`repliedBy`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`messageId`, `senderName`, `senderEmail`, `message`, `status`, `createdAt`, `repliedBy`, `replyMessage`, `repliedAt`) VALUES
(2, 'test', 'tharaniKumari2003@gmail.com', 'test1.0', 'replied', '2025-11-27 10:09:58', 38, 'test1', '2025-11-27 10:11:23'),
(8, 'Tharani Weerarathne', 'tharaniKumari2003@gmail.com', 'test1', 'pending', '2026-01-14 11:26:18', NULL, NULL, NULL),
(9, 'Tharani Weerarathne', 'tharanikumari2003@gmail.com', 'Test Message', 'replied', '2026-03-06 08:42:37', 39, 'Thank You!', '2026-03-06 08:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `eventId` int NOT NULL AUTO_INCREMENT,
  `eventName` varchar(200) NOT NULL,
  `eventDescription` text,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `googleMapLink` varchar(255) DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `startTime` time DEFAULT NULL,
  `endTime` time DEFAULT NULL,
  `maxVolunteers` int DEFAULT '0',
  `requiredSkillId` int DEFAULT NULL,
  `eventImage` varchar(255) DEFAULT NULL,
  `createdBy` int NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Active','Cancelled') DEFAULT 'Active',
  PRIMARY KEY (`eventId`),
  KEY `idx_requiredSkillId` (`requiredSkillId`),
  KEY `idx_createdBy` (`createdBy`),
  KEY `idx_dates` (`startDate`,`endDate`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`eventId`, `eventName`, `eventDescription`, `category`, `location`, `googleMapLink`, `startDate`, `endDate`, `startTime`, `endTime`, `maxVolunteers`, `requiredSkillId`, `eventImage`, `createdBy`, `createdAt`, `status`) VALUES
(26, 'Beach Cleanup', 'Join us for a beach cleanup! Volunteers of all ages are welcome to help collect litter, protect marine life, and keep our coastline beautiful. Let’s work together for a cleaner, greener environment!', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/Cbypw9YY52EDRbNq7', '2026-02-21', '2026-02-21', '09:00:00', '12:00:00', 30, 6, 'assets/event_img/699858d110eda_Beach_Cleanup.jpg', 90, '2026-02-20 12:51:29', 'Active'),
(27, 'Books & Beyond: Community Literacy Day', 'Join us for a literacy day! Volunteers will organize books, run children’s reading sessions, and promote education in the community. Let’s inspire young minds and make a positive impact!', 'Community', 'Raddolugama Public Library', 'https://maps.app.goo.gl/dCFFPdvbWA8FBckj7', '2026-02-21', '2026-02-21', '15:00:00', '18:00:00', 20, 0, 'assets/event_img/6998668cab3d8_Books___Beyond__Community_Literacy_Day.jpeg', 90, '2026-02-20 13:50:04', 'Active'),
(28, 'Exam Prep Workshop (Evening Edition)', '', 'Education', 'Alawathupitiya School', 'https://maps.app.goo.gl/51yPdJCvyhZgz1Kg6', '2026-02-21', '2026-02-21', '19:00:00', '21:39:00', 8, 1, 'assets/event_img/69998a35df468_Exam_Prep_Workshop__Evening_Edition_.jpg', 90, '2026-02-21 10:34:29', 'Active'),
(29, 'Free Health Checkup Camp', 'Join our community health checkup camp! Volunteers and healthcare professionals will offer basic screenings like blood pressure and BMI checks, plus general health advice. Take a step toward a healthier lifestyle!', 'Health', 'Raddolugama Primary School', 'https://maps.app.goo.gl/efJx3F6nyurzfcBS8', '2026-02-23', '2026-02-23', '12:30:00', '14:00:00', 20, 3, 'assets/event_img/699bfaf4242d7_Free_Health_Checkup_Camp.jpg', 90, '2026-02-23 07:00:04', 'Active'),
(30, 'Youth Talent Evening', 'A platform for young people to showcase their talents in singing, dancing, poetry, or drama. Volunteers help manage the event and encourage youth confidence and creativity.', 'Community', 'Alawathupitiya School', 'https://maps.app.goo.gl/JMVg5Z4KePiijjTF6', '2026-02-23', '2026-02-23', '18:00:00', '20:00:00', 20, 12, 'assets/event_img/699c4636b7eec_Youth_Talent_Evening.jpg', 91, '2026-02-23 12:21:10', 'Active'),
(31, 'Green Earth Planting Drive', 'Join our green environment activity! Help plant trees or small plants, learn about the importance of greenery, and support a cleaner, healthier planet. Let’s work together to protect our environment!', 'Environment', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/inA689oFHQkbAXtP6', '2026-02-24', '2026-02-24', '12:30:00', '14:00:00', 20, 6, 'assets/event_img/699d4b45b8007_Green_Earth_Planting_Drive.jpg', 91, '2026-02-24 06:55:01', 'Active'),
(32, 'Morning Meditation & Peace Session', 'Start your day with peace and positivity! Join a morning meditation session at a temple to practice mindfulness, learn simple breathing, and enjoy a calm spiritual environment in Sri Lanka. Promote inner peace and well-being together!', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/RkXk7Fo9wzxikyDX9', '2026-02-25', '2026-02-25', '06:00:00', '08:00:00', 30, 0, 'assets/event_img/699de420eab4f_Morning_Meditation___Peace_Session.jpeg', 91, '2026-02-24 17:47:12', 'Active'),
(33, 'Community Tree Planting ', 'Join our green environment activity! Help plant trees and small plants in a community area to support a greener future in Sri Lanka. Learn about trees’ importance and contribute to a healthier environment!', 'Community', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/5uoULFy8GrzdQYbg9', '2026-02-25', '2026-02-25', '13:30:00', '15:00:00', 30, 6, 'assets/event_img/699eaa4190c54_Community_Tree_Planting_.jpg', 91, '2026-02-25 07:52:33', 'Active'),
(34, 'Youth Talent Evening', 'A platform for young people to showcase their talents in singing, dancing, poetry, or drama. Volunteers help manage the event and encourage youth confidence and creativity.', 'Community', 'Alawathupitiya School', 'https://maps.app.goo.gl/NpT4PGbja4b3FSap6', '2026-02-25', '2026-02-25', '17:30:00', '18:30:00', 15, 12, 'assets/event_img/699ee000134d0_Youth_Talent_Evening.jpg', 38, '2026-02-25 11:41:52', 'Active'),
(35, 'Books & Beyond: Community Literacy Day', 'Promotes learning, imagination, and lifelong reading habits.', 'Community', 'Alawathupitiya School', 'https://maps.app.goo.gl/NpT4PGbja4b3FSap6', '2026-02-25', '2026-02-25', '06:35:00', '19:30:00', 6, 12, 'assets/event_img/699ee79f22ef3_Books___Beyond__Community_Literacy_Day.jpeg', 91, '2026-02-25 12:14:23', 'Active'),
(36, 'Temple Surroundings Cleaning', 'Description1', 'Community', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/BHqHHsxMmnY5aEWM9', '2026-02-25', '2026-02-25', '19:40:00', '20:00:00', 3, 0, 'assets/event_img/699eea696a921_Temple_Surroundings_Cleaning.jpg', 90, '2026-02-25 12:26:17', 'Active'),
(37, 'Event 1', 'Description1', 'Charity', 'Alawathupitiya School', 'https://maps.app.goo.gl/WnqgbwwCNWiX5GpbA', '2026-02-25', '2026-02-25', '19:30:00', '20:00:00', 3, 5, 'assets/event_img/699eec51de2c2_Event_1.png', 91, '2026-02-25 12:34:25', 'Active'),
(38, 'Event 2', 'Description1', 'Arts & Culture', 'Alawathupitiya School', 'https://maps.app.goo.gl/WnqgbwwCNWiX5GpbA', '2026-02-25', '2026-02-25', '20:05:00', '20:30:00', 5, 8, 'assets/event_img/699eede6ef967_Event_2.png', 91, '2026-02-25 12:41:10', 'Active'),
(39, 'Event 3', 'Description1', 'Sports', 'Nawaloka Playground', 'https://maps.app.goo.gl/YJuCTNK8o7MAjat28', '2026-02-25', '2026-02-25', '20:35:00', '21:00:00', 5, 0, 'assets/event_img/699ef119109ce_Event_3.png', 91, '2026-02-25 12:54:49', 'Active'),
(40, 'Event 4', 'Description', 'Animal Welfare', 'Uswetakeiyawa Beach', 'https://maps.app.goo.gl/H1LCt4sd2NEd7p9i6', '2026-02-25', '2026-02-25', '21:05:00', '21:30:00', 10, 3, 'assets/event_img/699ef6d55cc63_Event_4.png', 90, '2026-02-25 13:19:17', 'Active'),
(41, 'Event 5', 'Description', 'Charity', 'Alawathupitiya ', 'https://maps.app.goo.gl/9J4JhG7QxSMSywdX6', '2026-02-25', '2026-02-25', '21:35:00', '22:00:00', 5, 6, 'assets/event_img/699ef8c5cabf9_Event_5.png', 90, '2026-02-25 13:27:33', 'Active'),
(42, 'Event 6', 'Description', 'Technology', 'Alawathupitiya', 'https://maps.app.goo.gl/B1y4TUWcHmgyTxYW9', '2026-02-25', '2026-02-25', '22:05:00', '22:20:00', 11, 11, 'assets/event_img/699f144bec19b_Event_6.png', 91, '2026-02-25 15:24:59', 'Active'),
(43, 'Event 7', 'Description', 'Education', 'Alawathupitiya ', 'https://maps.app.goo.gl/B1y4TUWcHmgyTxYW9', '2026-02-25', '2026-02-25', '22:30:00', '23:00:00', 5, 2, 'assets/event_img/699f166c31f8f_Event_28.png', 38, '2026-02-25 15:34:04', 'Active'),
(44, 'Event 8', 'Description\r\n', 'Charity', 'Alawathupitiya ', 'https://maps.app.goo.gl/B1y4TUWcHmgyTxYW9', '2026-02-25', '2026-02-25', '23:05:00', '23:25:00', 3, 4, 'assets/event_img/699f18283d769_Event_8.png', 90, '2026-02-25 15:41:28', 'Active'),
(45, 'Event 9', 'Description', 'Arts & Culture', 'Alawathupitiya ', 'https://maps.app.goo.gl/B1y4TUWcHmgyTxYW9', '2026-02-25', '2026-02-25', '11:35:00', '23:59:00', 2, 6, 'assets/event_img/699f1991f19b7_Event_9.png', 91, '2026-02-25 15:47:29', 'Active'),
(46, 'Photography', 'Description', 'Environment', 'Alawathupitiya ', 'https://maps.app.goo.gl/B1y4TUWcHmgyTxYW9', '2026-02-26', '2026-02-26', '00:00:00', '00:30:00', 6, 4, 'assets/event_img/699f1dbb0d3be_Photography.png', 91, '2026-02-25 16:05:15', 'Active'),
(47, 'Morning Meditation', 'Description', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/UirYZVoDZtt4ubUv8', '2026-02-26', '2026-02-26', '01:00:00', '02:00:00', 10, 0, 'assets/event_img/699f479ea3d7e_Morning_Meditation.png', 91, '2026-02-25 19:03:58', 'Active'),
(48, 'Event 10', 'Description', 'Environment', 'Seeduwa', 'https://maps.app.goo.gl/VQbcyq93b9tUuDKUA', '2026-02-26', '2026-02-26', '02:00:00', '03:00:00', 12, 6, 'assets/event_img/699f4a4d3a182_Event_10.png', 90, '2026-02-25 19:15:25', 'Active'),
(49, 'Event 11', 'Description', 'Arts & Culture', 'Seeduwa', 'https://maps.app.goo.gl/VQbcyq93b9tUuDKUA', '2026-02-26', '2026-02-26', '03:05:00', '04:00:00', 5, 1, 'assets/event_img/699f4acd41f64_Event_11.png', 91, '2026-02-25 19:17:33', 'Active'),
(50, 'Morning Meditation', 'Description', 'Community', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/3hvPqLKw4Umk3vVw8', '2026-02-26', '2026-02-26', '05:00:00', '06:00:00', 6, 2, 'assets/event_img/699f4b8ed1c43_Morning_Meditation.jpeg', 91, '2026-02-25 19:20:46', 'Active'),
(51, 'Morning Meditation & Peace Session', 'Description', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/3hvPqLKw4Umk3vVw8', '2026-02-26', '2026-02-26', '06:00:00', '07:00:00', 5, 2, 'assets/event_img/699f4e18b24ae_Morning_Meditation___Peace_Session.jpeg', 91, '2026-02-25 19:31:36', 'Active'),
(52, 'Beach Cleanup', 'Description', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/7hmC5Kp4faFnpE347', '2026-02-26', '2026-02-26', '11:00:00', '12:00:00', 20, 6, 'assets/event_img/699fd6eaf2ad3_Beach_Cleanup.jpg', 91, '2026-02-26 05:15:22', 'Active'),
(53, 'Free Health Checkup Camp', 'Description', 'Health', 'Davi Samara Maha Vidyalaya', 'https://maps.app.goo.gl/v3rGsotzCdssyC6P7', '2026-02-26', '2026-02-26', '12:00:00', '13:30:00', 20, 3, 'assets/event_img/699fe1ec7c663_Free_Health_Checkup_Camp.jpg', 90, '2026-02-26 06:02:20', 'Active'),
(54, 'Event 12', 'Description', 'Sports', 'Maris Stella College | Playground', 'https://maps.app.goo.gl/X3uVNg4dfFNnYDUCA', '2026-02-26', '2026-02-26', '12:00:00', '13:00:00', 10, 7, 'assets/event_img/699fe56c93a68_Event_12.png', 91, '2026-02-26 06:17:16', 'Active'),
(55, 'Photography', 'Description', 'Charity', 'Guruge Nature Park', 'https://maps.app.goo.gl/MML3tJJdFUeWjE999', '2026-02-26', '2026-02-26', '13:35:00', '14:00:00', 10, 4, 'assets/event_img/699fe8043cf3a_Photography.png', 90, '2026-02-26 06:28:20', 'Active'),
(56, 'Event 14', 'Description', 'Community', 'Seeduwa Happy Kids Pre-School', 'https://maps.app.goo.gl/bSYieb6spf3XYigs5', '2026-02-26', '2026-02-26', '01:00:00', '14:00:00', 7, 7, 'assets/event_img/699febe9bf493_Event_14.png', 91, '2026-02-26 06:44:57', 'Active'),
(57, 'IT Workshop', 'Description', 'Technology', 'Ave Maria Convent, Bolawalana', 'https://maps.app.goo.gl/GTtUfej8NXUqRpc78', '2026-02-26', '2026-02-26', '14:00:00', '15:00:00', 2, 11, 'assets/event_img/69a0032001c4f_IT_Workshop.jpg', 90, '2026-02-26 08:24:00', 'Active'),
(58, 'Event 14', 'Description', 'Environment', 'Davi Samara Maha Vidyalaya', 'https://maps.app.goo.gl/3ZrwmLesZbKXQaAr5', '2026-02-26', '2026-02-26', '15:00:00', '15:25:00', 10, 6, 'assets/event_img/69a00734b88a7_Event_14.jpg', 91, '2026-02-26 08:41:24', 'Active'),
(59, 'Clean & Green Community Drive', 'Description', 'Environment', 'Seeduwa Railway Station', 'https://maps.app.goo.gl/1HCrihuWubvSCFqe7', '2026-02-26', '2026-02-26', '15:30:00', '16:00:00', 12, 6, 'assets/event_img/69a008a677f4e_Clean___Green_Community_Drive.jpg', 91, '2026-02-26 08:47:34', 'Active'),
(60, 'Event 15', 'Description', 'Sports', 'Raddolugama Ground Pavilion', 'https://maps.app.goo.gl/qzHmkPQ6EHRASesp7', '2026-02-26', '2026-02-26', '16:00:00', '16:30:00', 3, 2, 'assets/event_img/69a00be26cbb8_Event_15.png', 90, '2026-02-26 09:01:22', 'Active'),
(61, 'Event 16', 'Description', 'Charity', 'Raddolugama Primary School', 'https://maps.app.goo.gl/yguPhFW2EB7EcoKX7', '2026-02-26', '2026-02-26', '16:30:00', '17:00:00', 5, 10, 'assets/event_img/69a00d5077428_Event_16.png', 91, '2026-02-26 09:07:28', 'Active'),
(62, 'Beach Cleanup', 'Description', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/6vZFDuzhDikRs17P6', '2026-02-26', '2026-02-26', '17:30:00', '18:00:00', 8, 6, 'assets/event_img/69a030e98067f_Beach_Cleanup.jpg', 91, '2026-02-26 11:39:21', 'Active'),
(63, 'Talent Show', 'Description', 'Arts & Culture', 'Loyola College Negombo', 'https://maps.app.goo.gl/UDvyKd5ZvXeZKADH8', '2026-02-26', '2026-02-26', '18:05:00', '18:30:00', 6, 2, 'assets/event_img/69a0345f23d35_Talent_Show.jpg', 38, '2026-02-26 11:54:07', 'Active'),
(64, 'Event 17', 'Description', 'Animal Welfare', 'Alawathupitiya ', 'https://maps.app.goo.gl/PnhEYrGUX35zpyQb7', '2026-02-26', '2026-02-26', '18:35:00', '18:40:00', 5, 3, 'assets/event_img/69a0393dbdaeb_Event_17.png', 38, '2026-02-26 12:14:53', 'Active'),
(65, 'Event 18', 'Description', 'Sports', 'Alawathupitiya School', 'https://maps.app.goo.gl/2GL49j2Q2mLXPskw5', '2026-02-26', '2026-02-26', '19:00:00', '19:30:00', 3, 11, 'assets/event_img/69a03d66da116_Event_18.png', 38, '2026-02-26 12:32:38', 'Active'),
(66, 'Event 19', 'Description', 'Community', 'St.Anne\'s School', 'https://maps.app.goo.gl/F8z3yTo1LygBg6Lu5', '2026-02-26', '2026-02-26', '19:20:00', '19:40:00', 5, 1, 'assets/event_img/69a04076b6c7a_Event_19.png', 91, '2026-02-26 12:45:42', 'Active'),
(67, 'Temple Surroundings Cleaning', 'Description', 'Community', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/uR9QVv22WEF7Pn4g9', '2026-02-26', '2026-02-26', '20:30:00', '21:00:00', 8, 6, 'assets/event_img/69a05e12544cb_Temple_Surroundings_Cleaning.png', 91, '2026-02-26 14:52:02', 'Active'),
(68, 'Event 19', 'Description', 'Community', 'Community Center - Liyanagemulla', 'https://maps.app.goo.gl/XQTCby9WqKaPuenY6', '2026-02-26', '2026-02-26', '21:30:00', '22:00:00', 4, 2, 'assets/event_img/69a05fb5ab938_Event_19.png', 90, '2026-02-26 14:59:01', 'Active'),
(69, 'Exam Prep Workshop', 'Description', 'Education', 'Community Center - Liyanagemulla', 'https://maps.app.goo.gl/XQTCby9WqKaPuenY6', '2026-02-26', '2026-02-26', '22:05:00', '22:30:00', 10, 1, 'assets/event_img/69a06398a72b4_Exam_Prep_Workshop.jpg', 38, '2026-02-26 15:15:36', 'Active'),
(70, 'Event 20', 'Description', 'Sports', 'Alawathupitiya ', 'https://maps.app.goo.gl/Z1aGNihnTyoAiZ6aA', '2026-02-26', '2026-02-26', '22:45:00', '23:00:00', 3, 4, 'assets/event_img/69a0756541123_Event_20.png', 38, '2026-02-26 16:31:33', 'Active'),
(71, 'Event 21', 'Description', 'Arts & Culture', 'Amandoluwa Maha Vidyalaya', 'https://maps.app.goo.gl/oWzhGMJkEpKrgT9FA', '2026-02-26', '2026-02-26', '23:05:00', '23:30:00', 4, 7, NULL, 91, '2026-02-26 16:38:17', 'Active'),
(72, 'Beach Cleanup', 'Description', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/3Mma9qQpmLKwRka4A', '2026-02-27', '2026-02-27', '17:00:00', '17:30:00', 7, 6, 'assets/event_img/69a17d06e484e_Beach_Cleanup.jpg', 91, '2026-02-27 11:16:22', 'Active'),
(73, 'Clean & Green Community Drive', 'Description', 'Community', 'Seeduwa Railway Station', 'https://maps.app.goo.gl/1HCrihuWubvSCFqe7', '2026-02-27', '2026-02-27', '17:00:00', '17:30:00', 6, 2, 'assets/event_img/69a17e8535b68_Clean___Green_Community_Drive.png', 38, '2026-02-27 11:22:45', 'Active'),
(74, 'Photography', 'Description', 'Education', 'Headway Cricket Academy Negombo', 'https://maps.app.goo.gl/PTnRE9G4fNPhJSEdA', '2026-02-27', '2026-02-27', '17:35:00', '18:00:00', 5, 4, 'assets/event_img/69a182e5b76ad_Photography.png', 91, '2026-02-27 11:41:25', 'Active'),
(75, 'Meditation & Peace Session', '', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/3hvPqLKw4Umk3vVw8', '2026-02-27', '2026-02-27', '17:35:00', '18:00:00', 4, 2, 'assets/event_img/69a185c731085_Meditation___Peace_Session.png', 90, '2026-02-27 11:53:43', 'Active'),
(76, 'Free Health Checkup Camp', 'Description', 'Health', 'Negombo Municipal Council', 'https://maps.app.goo.gl/qFHuKGHgnxntWf2W6', '2026-02-27', '2026-02-27', '18:05:00', '18:30:00', 5, 9, 'assets/event_img/69a186e2b6593_Free_Health_Checkup_Camp.png', 91, '2026-02-27 11:58:26', 'Active'),
(79, 'Event 23', 'Description', 'Charity', 'Negombo Beach', '', '2026-03-01', '2026-03-01', '09:00:00', '10:00:00', 5, 5, NULL, 91, '2026-03-01 02:50:51', 'Active'),
(80, 'Event 24', 'Description', 'Education', 'Maris Stella College Negombo', 'https://maps.app.goo.gl/KQ5Qn3iGHwG23et4A', '2026-03-01', '2026-03-01', '09:00:00', '10:30:00', 8, 4, 'assets/event_img/69a4306493b1b_Event_24.png', 38, '2026-03-01 02:26:12', 'Active'),
(81, 'Event 25', 'Description', 'Education', 'Dialog Axiata Experience Center - Negombo', 'https://maps.app.goo.gl/GdTaUSkNLPtbwFig9', '2026-03-01', '2026-03-01', '09:00:00', '09:30:00', 2, 8, 'assets/event_img/69a4327bb49b5_Event_25.png', 91, '2026-03-01 01:36:08', 'Active'),
(82, 'Event 26', 'Description', 'Environment', 'Alawathupitiya School', '', '2026-03-01', '2026-03-01', '09:30:00', '10:00:00', 6, 6, 'assets/event_img/69a432b8a8bf3_Event_26.png', 91, '2026-03-01 01:36:08', 'Active'),
(83, 'Event 27', 'Description', 'Health', 'District General Hospital Negombo', 'https://maps.app.goo.gl/LqnRGvmYxPei6hUt5', '2026-03-01', '2026-03-01', '10:05:00', '10:30:00', 5, 9, 'assets/event_img/69a433bb27be2_Event_27.png', 90, '2026-03-01 12:40:27', 'Active'),
(84, 'Event 28', 'Description', 'Health', 'District General Hospital Negombo', 'https://maps.app.goo.gl/LqnRGvmYxPei6hUt5', '2026-03-01', '2026-03-01', '10:35:00', '11:00:00', 6, 3, 'assets/event_img/69a4352285f86_Event_28.png', 91, '2026-02-28 12:46:26', 'Active'),
(85, 'Event 28', 'Description', 'Community', 'Amandoluwa Maha Vidyalaya', 'https://maps.app.goo.gl/XgzRs69jQPxVN6rx6', '2026-03-01', '2026-03-01', '10:35:00', '11:00:00', 5, 12, 'assets/event_img/69a437050478f_Event_28.png', 91, '2026-02-28 12:54:29', 'Active'),
(86, 'Event 1', 'Description', 'Sports', 'Seeduwa Happy Kids Pre-School (Since 1998)', 'https://maps.app.goo.gl/T6iSw8yZY42en6wL9', '2026-03-01', '2026-03-01', '10:45:00', '11:00:00', 5, 10, 'assets/event_img/69a43a6a044bb_Event_1.png', 90, '2026-02-27 13:08:58', 'Active'),
(87, 'Event 2', 'Description', 'Arts & Culture', 'Dr. Kulasinghe College Udammita, Sri Lanka', 'https://maps.app.goo.gl/aEnmFNRJqTKPa8PP6', '2026-03-01', '2026-03-01', '11:05:00', '11:25:00', 3, 8, 'assets/event_img/69a43c2ca78c8_Event_2.png', 90, '2026-02-28 13:16:28', 'Active'),
(88, 'Event 3', '', 'Community', 'Dr. Kulasinghe College Udammita, Sri Lanka', 'https://maps.app.goo.gl/aEnmFNRJqTKPa8PP6', '2026-03-01', '2026-03-01', '11:00:00', '11:30:00', 3, 5, 'assets/event_img/69a43d5eee548_Event_3.png', 90, '2026-02-28 13:21:34', 'Active'),
(89, 'Event 3', 'Description', 'Sports', 'Nawaloka Playground', 'https://maps.app.goo.gl/z4y4amGtBHjkWL6r7', '2026-03-01', '2026-03-01', '11:35:00', '12:00:00', 5, 7, 'assets/event_img/69a45216cc655_Event_3.png', 91, '2026-02-28 14:49:58', 'Active'),
(90, 'Event 4', 'Description', 'Animal Welfare', 'Negombo Animal Hospital', 'https://maps.app.goo.gl/LYrtK7F1U3QdZ5WE9', '2026-03-01', '2026-03-01', '11:35:00', '12:00:00', 8, 3, 'assets/event_img/69a4550ac3345_Event_4.png', 91, '2026-03-01 15:02:34', 'Active'),
(91, 'Event 91', 'Description', 'Charity', 'Ranmuthugala Temple', 'https://maps.app.goo.gl/tjxfnpPdVwimztRW7', '2026-03-01', '2026-03-01', '11:35:00', '12:00:00', 0, 1, 'assets/event_img/69a457bf82269_Event_91.png', 90, '2026-02-26 15:14:07', 'Active'),
(92, 'Event 5', '', 'Animal Welfare', 'Negombo Animal Hospital', 'https://maps.app.goo.gl/yAKhYDKBJNXETS6v8', '2026-03-01', '2026-03-01', '12:00:00', '12:25:00', 2, 3, 'assets/event_img/69a45aa2607c2_Event_5.png', 91, '2026-03-01 15:26:26', 'Active'),
(93, 'Event 6', 'Description', 'Sports', 'Ave Maria Convent, Bolawalana', 'https://maps.app.goo.gl/dNoWHDG75DG9QWu9A', '2026-03-01', '2026-03-01', '12:00:00', '12:25:00', 3, 1, 'assets/event_img/69a45c139d4d0_Event_6.png', 90, '2026-02-25 15:32:35', 'Active'),
(94, 'Event 7', 'Description', 'Charity', 'Seeduwa', 'https://maps.app.goo.gl/UJNcRpuXpVzb83tB6', '2026-03-01', '2026-03-01', '12:00:00', '12:25:00', 5, 6, 'assets/event_img/69a45ebee98d8_Event_7.png', 38, '2026-02-28 15:43:58', 'Active'),
(95, 'Event 8', 'Description', 'Community', 'Seeduwa', 'https://maps.app.goo.gl/UJNcRpuXpVzb83tB6', '2026-03-01', '2026-03-01', '12:35:00', '12:55:00', 4, 6, 'assets/event_img/69a45f8fad458_Event_8.png', 90, '2026-03-01 15:47:27', 'Active'),
(96, 'Event 8', 'Description', 'Environment', 'Diyasaru Park', 'https://maps.app.goo.gl/eK35iCXoSMQdi5TR8', '2026-03-01', '2026-03-01', '13:00:00', '13:25:00', 5, 6, 'assets/event_img/69a46ea5b6797_Event_8.png', 91, '2026-03-01 16:51:49', 'Active'),
(97, 'Event 9', 'Description', 'Technology', 'Kandana', 'https://maps.app.goo.gl/rq8dW2sBScZoeqvG8', '2026-03-01', '2026-03-01', '13:00:00', '13:25:00', 3, 11, 'assets/event_img/69a472c605760_Event_9.png', 38, '2026-02-28 17:09:26', 'Active'),
(98, 'Event 11', 'Description', 'Community', 'Negombo Beach', 'https://maps.app.goo.gl/qT2N17KRCz1utbCD9', '2026-03-01', '2026-03-01', '13:00:00', '13:25:00', 3, 10, 'assets/event_img/69a47430eaba1_Event_11.png', 91, '2026-03-01 17:15:28', 'Active'),
(99, 'Event 12', 'Description', 'Community', 'Koturupa Sri Pushparama Viharaya', 'https://maps.app.goo.gl/roE1FZ1wGQRBZ8gTA', '2026-03-01', '2026-03-01', '13:00:00', '13:25:00', 5, 12, 'assets/event_img/69a476fda4348_Event_12.png', 91, '2026-02-27 17:27:25', 'Active'),
(100, 'Event 12', 'Description', 'Technology', 'De Mazenod College Playground', 'https://maps.app.goo.gl/2qaKMP3Yhw2B23Kg8', '2026-03-01', '2026-03-01', '13:30:00', '13:55:00', 3, 7, 'assets/event_img/69a478f66a410_Event_12.png', 90, '2026-02-27 17:35:50', 'Active'),
(101, 'Event 13', 'Description', 'Community', 'Sri Subodarama Purana maha Viharaya. ', 'https://maps.app.goo.gl/pCCGVNNVkza7nqtw5', '2026-03-01', '2026-03-01', '13:35:00', '13:55:00', 8, 12, 'assets/event_img/69a47f6447422_Event_13.png', 91, '2026-02-28 18:02:12', 'Active'),
(102, 'Temple Surroundings Cleaning', 'Description', 'Community', 'Sri Subodarama Purana maha Viharaya.', 'https://maps.app.goo.gl/pCCGVNNVkza7nqtw5', '2026-03-01', '2026-03-01', '14:05:00', '15:00:00', 5, 6, 'assets/event_img/69a480a741a14_Temple_Surroundings_Cleaning.png', 91, '2026-02-27 18:08:39', 'Active'),
(103, 'Event 16', 'Description', 'Charity', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/8npoAA8eZ1eYUfoJA', '2026-03-01', '2026-03-01', '14:05:00', '15:00:00', 10, 6, 'assets/event_img/69a481ecf3bf9_Event_16.png', 38, '2026-03-01 18:14:04', 'Active'),
(104, 'Event 15', 'Description', 'Sports', 'Nawaloka Playground', 'https://maps.app.goo.gl/JDmdiLWJZLAhxRZV9', '2026-03-01', '2026-03-01', '14:05:00', '15:00:00', 3, 4, 'assets/event_img/69a483e52373f_Event_15.png', 91, '2026-02-27 18:22:29', 'Active'),
(105, 'Event 28', 'Description', 'Health', 'District General Hospital Negombo', 'https://maps.app.goo.gl/LqnRGvmYxPei6hUt5', '2026-03-01', '2026-03-01', '16:00:00', '16:25:00', 3, 3, 'assets/event_img/69a48721ba5e3_Event_28.png', 91, '2026-02-26 18:36:17', 'Active'),
(106, 'Event 19', 'Description', 'Community', 'Community Center - Liyanagemulla', 'https://maps.app.goo.gl/XQTCby9WqKaPuenY6', '2026-03-01', '2026-03-01', '16:00:00', '16:25:00', 3, 2, 'assets/event_img/69a488d018096_Event_19.png', 38, '2026-03-01 18:43:28', 'Active'),
(107, 'IT Workshop', 'Description', 'Technology', 'Ave Maria Convent, Bolawalana', 'https://maps.app.goo.gl/GTtUfej8NXUqRpc78', '2026-03-01', '2026-03-01', '16:00:00', '16:25:00', 3, 11, 'assets/event_img/69a48cce3e600_IT_Workshop.png', 38, '2026-02-27 19:00:30', 'Active'),
(108, 'Event 20', 'Description', 'Sports', 'Alawathupitiya ', 'https://maps.app.goo.gl/Z1aGNihnTyoAiZ6aA', '2026-03-01', '2026-03-01', '16:35:00', '17:30:00', 5, 4, 'assets/event_img/69a48ddd3f7c2_Event_20.png', 38, '2026-02-26 19:05:01', 'Active'),
(109, 'Event 23', 'Description', 'Community', 'Negombo ', 'https://maps.app.goo.gl/qT2N17KRCz1utbCD9', '2026-03-01', '2026-03-01', '16:30:00', '16:55:00', 5, 2, 'assets/event_img/69a48ebcf3639_Event_23.png', 38, '2026-02-27 19:08:44', 'Active'),
(110, 'Talent Show', 'Description', 'Arts & Culture', 'Loyola College Negombo', 'https://maps.app.goo.gl/UDvyKd5ZvXeZKADH8', '2026-03-01', '2026-03-01', '17:00:00', '18:00:00', 5, 12, 'assets/event_img/69a4901fcb87d_Talent_Show.png', 38, '2026-02-27 19:14:39', 'Active'),
(111, 'Event 16', 'Description', 'Charity', 'Raddolugama Primary School', 'https://maps.app.goo.gl/yguPhFW2EB7EcoKX7', '2026-03-01', '2026-03-01', '17:00:00', '18:00:00', 2, 10, 'assets/event_img/69a4915a51c02_Event_16.png', 91, '2026-02-27 19:19:54', 'Active'),
(112, 'Event 16', 'Description', 'Charity', 'Raddolugama Primary School', 'https://maps.app.goo.gl/yguPhFW2EB7EcoKX7', '2026-03-01', '2026-03-01', '18:00:00', '19:00:00', 2, 2, 'assets/event_img/69a4926282159_Event_16.png', 91, '2026-02-26 19:24:18', 'Active'),
(113, 'Event 5', 'Description', 'Charity', 'Alawathupitiya ', 'https://maps.app.goo.gl/9J4JhG7QxSMSywdX6', '2026-03-01', '2026-03-01', '19:00:00', '20:00:00', 3, 4, 'assets/event_img/69a492eab030c_Event_5.png', 38, '2026-02-27 19:26:34', 'Active'),
(114, 'Morning Meditation', 'Description', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/UirYZVoDZtt4ubUv8', '2026-03-02', '2026-03-02', '05:00:00', '06:00:00', 10, 12, 'assets/event_img/69a51c73c68c9_Morning_Meditation.png', 91, '2026-03-01 01:13:23', 'Active'),
(115, 'Morning Meditation', 'Description', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/RkXk7Fo9wzxikyDX9', '2026-03-02', '2026-03-02', '06:05:00', '06:55:00', 5, 2, 'assets/event_img/69a51ea541e3f_Morning_Meditation.png', 91, '2026-03-01 01:22:45', 'Active'),
(116, 'Event 23', 'Description', 'Community', 'Negombo ', 'https://maps.app.goo.gl/qT2N17KRCz1utbCD9', '2026-03-02', '2026-03-02', '06:05:00', '06:55:00', 3, 2, 'assets/event_img/69a5235a88cf5_Event_23.png', 91, '2026-03-01 00:42:50', 'Active'),
(117, 'Temple Surroundings Cleaning', '', 'Community', 'Sri pabodhanaramaya Viharaya', 'https://maps.app.goo.gl/KDAV3LJhDMkZqPAw8', '2026-03-02', '2026-03-02', '06:05:00', '06:55:00', 5, 6, 'assets/event_img/69a524957bdd8_Temple_Surroundings_Cleaning.png', 90, '2026-03-01 00:48:05', 'Active'),
(118, 'Beach Cleanup', 'Description', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/3Mma9qQpmLKwRka4A', '2026-03-02', '2026-03-02', '07:05:00', '07:55:00', 10, 6, 'assets/event_img/69a5293db7c1f_Beach_Cleanup.jpg', 91, '2026-03-02 01:07:57', 'Active'),
(119, 'Event 10', 'Description', 'Education', 'Seeduwa', 'https://maps.app.goo.gl/VQbcyq93b9tUuDKUA', '2026-03-02', '2026-03-02', '07:05:00', '07:55:00', 3, 8, 'assets/event_img/69a52c7a6f552_Event_10.png', 38, '2026-03-01 00:21:46', 'Active'),
(120, 'Event 16', 'Description', 'Charity', 'Raddolugama Primary School', 'https://maps.app.goo.gl/yguPhFW2EB7EcoKX7', '2026-03-02', '2026-03-02', '07:05:00', '07:55:00', 3, 10, 'assets/event_img/69a52d4fc8d44_Event_16.png', 91, '2026-03-01 00:25:19', 'Active'),
(121, 'IT Workshop', 'Description', 'Technology', 'Ave Maria Convent, Bolawalana', 'https://maps.app.goo.gl/GTtUfej8NXUqRpc78', '2026-03-02', '2026-03-02', '08:05:00', '08:55:00', 3, 11, 'assets/event_img/69a53a181e97a_IT_Workshop.png', 90, '2026-03-01 01:19:52', 'Active'),
(122, 'Event 6', 'Description', 'Sports', 'De Mazenod College Playground', 'https://maps.app.goo.gl/DMUK5gof9NNGLBUE7', '2026-03-02', '2026-03-02', '08:05:00', '08:55:00', 4, 1, 'assets/event_img/69a53ce056056_Event_6.png', 90, '2026-03-01 01:31:44', 'Active'),
(123, 'Beach Cleanup', 'Description', 'Environment', 'Negombo Beach', 'https://maps.app.goo.gl/Cbypw9YY52EDRbNq7', '2026-03-02', '2026-03-02', '09:05:00', '09:55:00', 3, 6, NULL, 91, '2026-03-02 07:41:58', 'Active'),
(124, 'Photography', 'Description', 'Education', 'Headway Cricket Academy Negombo', 'https://maps.app.goo.gl/PTnRE9G4fNPhJSEdA', '2026-03-02', '2026-03-02', '10:05:00', '10:55:00', 5, 4, 'assets/event_img/69a54824b598e_Photography.png', 91, '2026-03-01 01:19:48', 'Active'),
(125, 'Event 91', 'Description', 'Charity', 'Ranmuthugala Temple', 'https://maps.app.goo.gl/tjxfnpPdVwimztRW7', '2026-03-02', '2026-03-02', '10:05:00', '10:55:00', 5, 1, 'assets/event_img/69a54ac8a2b9d_Event_91.png', 90, '2026-03-01 00:31:04', 'Active'),
(126, 'Event 28', 'Description', 'Health', 'District General Hospital Negombo', 'https://maps.app.goo.gl/LqnRGvmYxPei6hUt5', '2026-03-02', '2026-03-02', '12:00:00', '13:00:00', 3, 3, 'assets/event_img/69a54d7744f75_Event_28.png', 91, '2026-03-01 01:42:31', 'Active'),
(127, 'Clean & Green Community Drive', 'Description', 'Community', 'Seeduwa Railway Station', 'https://maps.app.goo.gl/1HCrihuWubvSCFqe7', '2026-03-02', '2026-03-02', '13:00:00', '14:00:00', 4, 6, 'assets/event_img/69a54efc1cbd1_Clean___Green_Community_Drive.png', 38, '2026-03-02 01:49:00', 'Active'),
(128, 'Event 15', 'Description', 'Sports', 'Raddolugama Ground Pavilion', 'https://maps.app.goo.gl/qzHmkPQ6EHRASesp7', '2026-03-02', '2026-03-02', '15:00:00', '16:00:00', 5, 4, 'assets/event_img/69a54f826bfb3_Event_15.png', 91, '2026-03-02 01:51:14', 'Active'),
(136, 'Youth Talent Evening', 'Test', 'Charity', 'Alawathupitiya School', 'https://www.google.com/maps/place/Alawathupitiya+Railway+Station/@7.1158149,79.8844443,17z/data=!3m1!4b1!4m6!3m5!1s0x3ae2f0598d5292bd:0x5ba9aa8d23558284!8m2!3d7.1158149!4d79.8870192!16s%2Fg%2F1tfrqbtx?entry=ttu&g_ep=EgoyMDI1MTEzMC4wIKXMDSoASAFQAw%3D%3D', '2026-03-08', '2026-03-08', '19:00:00', '22:00:00', 5, 12, 'assets/event_img/69ad7a1037ef6_Youth_Talent_Evening.jpg', 90, '2026-03-08 13:30:56', 'Active'),
(130, 'Event 16', 'Description', 'Charity', 'Raddolugama Primary School', 'https://maps.app.goo.gl/yguPhFW2EB7EcoKX7', '2026-03-03', '2026-03-03', '20:00:00', '22:00:00', 2, 6, 'assets/event_img/69a6e867b9a34_Event_16.png', 90, '2026-03-03 13:55:51', 'Active'),
(135, 'Meditation & Peace Session', 'Test', 'Health', 'Alawathupitiya Temple', 'https://maps.app.goo.gl/uR9QVv22WEF7Pn4g9', '2026-03-07', '2026-03-07', '20:00:00', '22:00:00', 10, 2, 'assets/event_img/69ac38b1e4c55_Meditation___Peace_Session.jpeg', 90, '2026-03-07 14:39:45', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `event_coordinators`
--

DROP TABLE IF EXISTS `event_coordinators`;
CREATE TABLE IF NOT EXISTS `event_coordinators` (
  `id` int NOT NULL AUTO_INCREMENT,
  `eventId` int NOT NULL,
  `coordinatorId` int NOT NULL,
  `assignedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`eventId`,`coordinatorId`),
  KEY `idx_eventId` (`eventId`),
  KEY `idx_coordinatorId` (`coordinatorId`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_coordinators`
--

INSERT INTO `event_coordinators` (`id`, `eventId`, `coordinatorId`, `assignedAt`) VALUES
(24, 26, 39, '2026-02-20 12:51:29'),
(25, 27, 39, '2026-02-20 13:50:04'),
(26, 28, 40, '2026-02-21 10:34:29'),
(27, 29, 48, '2026-02-23 07:00:04'),
(28, 30, 43, '2026-02-23 12:21:10'),
(29, 31, 44, '2026-02-24 06:55:01'),
(30, 32, 42, '2026-02-24 17:47:12'),
(31, 33, 39, '2026-02-25 07:52:33'),
(34, 34, 41, '2026-02-25 12:10:26'),
(117, 35, 45, '2026-03-01 18:52:30'),
(36, 36, 47, '2026-02-25 12:26:17'),
(37, 37, 39, '2026-02-25 12:34:25'),
(39, 38, 44, '2026-02-25 12:41:34'),
(40, 39, 49, '2026-02-25 12:54:49'),
(41, 40, 42, '2026-02-25 13:19:17'),
(42, 41, 46, '2026-02-25 13:27:33'),
(43, 42, 46, '2026-02-25 15:24:59'),
(45, 43, 48, '2026-02-25 15:36:45'),
(46, 44, 41, '2026-02-25 15:41:28'),
(47, 45, 40, '2026-02-25 15:47:29'),
(48, 46, 47, '2026-02-25 16:05:15'),
(49, 47, 45, '2026-02-25 19:03:58'),
(50, 48, 47, '2026-02-25 19:15:25'),
(51, 49, 39, '2026-02-25 19:17:33'),
(52, 50, 44, '2026-02-25 19:20:46'),
(53, 51, 49, '2026-02-25 19:31:36'),
(54, 52, 45, '2026-02-26 05:15:23'),
(55, 53, 47, '2026-02-26 06:02:20'),
(56, 54, 39, '2026-02-26 06:17:16'),
(57, 55, 44, '2026-02-26 06:28:20'),
(58, 56, 41, '2026-02-26 06:44:57'),
(59, 57, 42, '2026-02-26 08:24:00'),
(60, 58, 46, '2026-02-26 08:41:24'),
(61, 59, 43, '2026-02-26 08:47:34'),
(63, 60, 40, '2026-02-26 09:02:18'),
(64, 61, 44, '2026-02-26 09:07:28'),
(65, 62, 41, '2026-02-26 11:39:21'),
(66, 63, 45, '2026-02-26 11:54:07'),
(67, 64, 47, '2026-02-26 12:14:53'),
(69, 65, 42, '2026-02-26 12:35:27'),
(70, 66, 48, '2026-02-26 12:45:42'),
(71, 67, 47, '2026-02-26 14:52:02'),
(72, 68, 41, '2026-02-26 14:59:01'),
(73, 69, 39, '2026-02-26 15:15:36'),
(74, 70, 44, '2026-02-26 16:31:33'),
(75, 71, 42, '2026-02-26 16:38:17'),
(78, 72, 45, '2026-02-27 11:23:01'),
(77, 73, 47, '2026-02-27 11:22:45'),
(79, 74, 39, '2026-02-27 11:41:25'),
(80, 75, 44, '2026-02-27 11:53:43'),
(81, 76, 49, '2026-02-27 11:58:26'),
(87, 80, 47, '2026-03-01 12:26:12'),
(86, 79, 45, '2026-03-01 10:50:51'),
(88, 81, 39, '2026-03-01 12:35:07'),
(89, 82, 44, '2026-03-01 12:36:08'),
(90, 83, 49, '2026-03-01 12:40:27'),
(91, 84, 49, '2026-03-01 12:46:26'),
(92, 85, 42, '2026-03-01 12:54:29'),
(93, 86, 46, '2026-03-01 13:09:12'),
(94, 87, 43, '2026-03-01 13:16:28'),
(95, 88, 48, '2026-03-01 13:21:34'),
(96, 89, 40, '2026-03-01 14:49:58'),
(97, 90, 41, '2026-03-01 15:02:34'),
(98, 91, 45, '2026-03-01 15:14:07'),
(101, 92, 39, '2026-03-01 15:35:39'),
(102, 93, 44, '2026-03-01 15:36:35'),
(103, 94, 45, '2026-03-01 15:43:58'),
(104, 95, 47, '2026-03-01 15:47:27'),
(105, 96, 45, '2026-03-01 16:51:49'),
(106, 97, 44, '2026-03-01 17:09:26'),
(107, 98, 47, '2026-03-01 17:15:28'),
(108, 99, 49, '2026-03-01 17:27:25'),
(109, 100, 42, '2026-03-01 17:35:50'),
(111, 101, 46, '2026-03-01 18:03:16'),
(112, 102, 42, '2026-03-01 18:08:39'),
(113, 103, 43, '2026-03-01 18:14:05'),
(114, 104, 45, '2026-03-01 18:22:29'),
(115, 105, 45, '2026-03-01 18:36:17'),
(116, 106, 39, '2026-03-01 18:43:28'),
(118, 107, 41, '2026-03-01 19:00:30'),
(119, 108, 45, '2026-03-01 19:05:01'),
(120, 109, 47, '2026-03-01 19:09:18'),
(121, 110, 39, '2026-03-01 19:14:57'),
(122, 111, 44, '2026-03-01 19:19:54'),
(123, 112, 41, '2026-03-01 19:24:18'),
(124, 113, 40, '2026-03-01 19:26:34'),
(125, 114, 45, '2026-03-02 05:13:23'),
(126, 115, 45, '2026-03-02 05:22:45'),
(127, 116, 39, '2026-03-02 05:42:59'),
(128, 117, 44, '2026-03-02 05:48:05'),
(129, 118, 49, '2026-03-02 06:07:57'),
(130, 119, 42, '2026-03-02 06:21:46'),
(131, 120, 46, '2026-03-02 06:25:19'),
(132, 121, 45, '2026-03-02 07:19:52'),
(133, 122, 39, '2026-03-02 07:31:44'),
(134, 123, 44, '2026-03-02 07:41:58'),
(135, 124, 45, '2026-03-02 08:19:48'),
(136, 125, 47, '2026-03-02 08:31:04'),
(137, 126, 39, '2026-03-02 08:42:31'),
(138, 127, 44, '2026-03-02 08:49:00'),
(139, 128, 45, '2026-03-02 08:51:14'),
(151, 136, 45, '2026-03-08 15:15:09'),
(142, 130, 47, '2026-03-03 15:13:19'),
(148, 135, 39, '2026-03-07 14:39:45');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

DROP TABLE IF EXISTS `event_registrations`;
CREATE TABLE IF NOT EXISTS `event_registrations` (
  `registrationId` int NOT NULL AUTO_INCREMENT,
  `eventId` int NOT NULL,
  `userId` int NOT NULL,
  `registrationDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('registered','cancelled') DEFAULT 'registered',
  `cancellationReason` text,
  PRIMARY KEY (`registrationId`),
  UNIQUE KEY `unique_active_event_user` (`eventId`,`userId`,`status`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=338 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registrationId`, `eventId`, `userId`, `registrationDate`, `status`, `cancellationReason`) VALUES
(14, 26, 71, '2026-02-20 13:10:49', 'registered', NULL),
(15, 26, 50, '2026-02-20 07:45:23', 'registered', ''),
(16, 26, 51, '2026-02-20 07:45:23', 'registered', ''),
(17, 26, 52, '2026-02-20 07:45:23', 'registered', ''),
(18, 26, 53, '2026-02-20 07:45:23', 'registered', ''),
(19, 26, 54, '2026-02-20 07:45:23', 'registered', ''),
(20, 26, 55, '2026-02-20 07:45:23', 'registered', ''),
(21, 26, 56, '2026-02-20 07:45:23', 'registered', ''),
(22, 26, 57, '2026-02-20 07:45:23', 'registered', ''),
(23, 26, 58, '2026-02-20 07:45:23', 'registered', ''),
(24, 26, 59, '2026-02-20 07:45:23', 'registered', ''),
(25, 27, 60, '2026-02-20 08:22:27', 'cancelled', 'Unable to attend'),
(26, 27, 61, '2026-02-20 08:22:27', 'registered', ''),
(27, 27, 62, '2026-02-20 08:22:27', 'registered', ''),
(28, 27, 63, '2026-02-20 08:22:27', 'registered', ''),
(29, 27, 64, '2026-02-20 08:22:27', 'registered', ''),
(30, 27, 65, '2026-02-20 08:22:27', 'registered', ''),
(31, 27, 66, '2026-02-20 08:22:27', 'registered', ''),
(32, 27, 67, '2026-02-20 08:22:27', 'registered', ''),
(33, 27, 68, '2026-02-20 08:22:27', 'registered', ''),
(34, 27, 69, '2026-02-20 08:22:27', 'registered', ''),
(35, 28, 52, '2026-02-21 05:09:26', 'registered', ''),
(36, 28, 54, '2026-02-21 05:09:26', 'registered', ''),
(37, 28, 58, '2026-02-21 05:09:26', 'registered', ''),
(38, 28, 61, '2026-02-21 05:09:26', 'registered', ''),
(39, 28, 64, '2026-02-21 05:09:26', 'registered', ''),
(40, 28, 65, '2026-02-21 05:09:26', 'cancelled', 'Unable to attend'),
(43, 28, 71, '2026-02-21 10:46:59', 'registered', NULL),
(44, 29, 51, '2026-02-23 02:01:08', 'registered', ''),
(45, 29, 52, '2026-02-23 02:01:08', 'registered', ''),
(46, 29, 53, '2026-02-23 02:01:08', 'registered', ''),
(47, 29, 54, '2026-02-23 02:01:08', 'cancelled', ''),
(48, 29, 55, '2026-02-23 02:01:08', 'registered', ''),
(49, 29, 56, '2026-02-23 02:01:08', 'registered', ''),
(50, 29, 61, '2026-02-23 02:01:08', 'registered', ''),
(51, 29, 62, '2026-02-23 02:01:08', 'cancelled', ''),
(52, 29, 65, '2026-02-23 02:01:08', 'registered', ''),
(53, 29, 66, '2026-02-23 02:01:08', 'registered', ''),
(54, 29, 68, '2026-02-23 02:01:08', 'registered', ''),
(55, 30, 50, '2026-02-23 06:56:24', 'registered', ''),
(56, 30, 51, '2026-02-23 06:56:24', 'registered', ''),
(57, 30, 52, '2026-02-23 06:56:24', 'registered', ''),
(58, 30, 53, '2026-02-23 06:56:24', 'registered', ''),
(59, 30, 54, '2026-02-23 06:56:24', 'registered', ''),
(60, 30, 55, '2026-02-23 06:56:24', 'registered', ''),
(61, 30, 56, '2026-02-23 06:56:24', 'registered', ''),
(62, 30, 57, '2026-02-23 06:56:24', 'registered', ''),
(63, 30, 58, '2026-02-23 06:56:24', 'cancelled', 'Unable to attend'),
(64, 30, 59, '2026-02-23 06:56:24', 'registered', ''),
(65, 30, 60, '2026-02-23 06:56:24', 'registered', ''),
(66, 30, 61, '2026-02-23 06:56:24', 'registered', ''),
(67, 30, 62, '2026-02-23 06:56:24', 'registered', ''),
(68, 30, 63, '2026-02-23 06:56:24', 'registered', ''),
(69, 30, 64, '2026-02-23 06:56:24', 'registered', ''),
(70, 30, 65, '2026-02-23 06:56:24', 'registered', ''),
(71, 30, 66, '2026-02-23 06:56:24', 'registered', ''),
(72, 30, 67, '2026-02-23 06:56:24', 'registered', ''),
(73, 30, 68, '2026-02-23 06:56:24', 'cancelled', 'Unable to attend'),
(74, 30, 69, '2026-02-23 06:56:24', 'registered', ''),
(75, 30, 71, '2026-02-23 06:56:24', 'registered', ''),
(76, 31, 50, '2026-02-24 01:32:30', 'registered', ''),
(77, 31, 52, '2026-02-24 01:32:30', 'registered', ''),
(78, 31, 53, '2026-02-24 01:32:30', 'registered', ''),
(79, 31, 55, '2026-02-24 01:32:30', 'cancelled', 'Unable to attend'),
(80, 31, 57, '2026-02-24 01:32:30', 'registered', ''),
(81, 31, 58, '2026-02-24 01:32:30', 'registered', ''),
(82, 31, 60, '2026-02-24 01:32:30', 'registered', ''),
(83, 31, 61, '2026-02-24 01:32:30', 'registered', ''),
(84, 31, 63, '2026-02-24 01:32:30', 'registered', ''),
(85, 31, 64, '2026-02-24 01:32:30', 'registered', ''),
(86, 31, 66, '2026-02-24 01:32:30', 'registered', ''),
(87, 31, 67, '2026-02-24 01:32:30', 'registered', ''),
(88, 31, 68, '2026-02-24 01:32:30', 'registered', ''),
(89, 31, 69, '2026-02-24 01:32:30', 'registered', ''),
(90, 31, 71, '2026-02-24 01:32:30', 'registered', ''),
(91, 32, 50, '2026-02-24 12:22:12', 'registered', ''),
(92, 32, 52, '2026-02-24 12:22:12', 'registered', ''),
(93, 32, 53, '2026-02-24 12:22:12', 'registered', ''),
(94, 32, 55, '2026-02-24 12:22:12', 'registered', ''),
(95, 32, 57, '2026-02-24 12:22:12', 'registered', ''),
(96, 32, 58, '2026-02-24 12:22:12', 'registered', ''),
(97, 32, 60, '2026-02-24 12:22:12', 'registered', ''),
(98, 32, 61, '2026-02-24 12:22:12', 'registered', ''),
(99, 32, 63, '2026-02-24 12:22:12', 'registered', ''),
(100, 32, 64, '2026-02-24 12:22:12', 'registered', ''),
(101, 33, 50, '2026-02-25 02:24:42', 'registered', ''),
(102, 33, 51, '2026-02-25 02:24:42', 'cancelled', 'Unable to attend'),
(103, 33, 53, '2026-02-25 02:24:42', 'registered', ''),
(104, 33, 54, '2026-02-25 02:24:42', 'registered', ''),
(105, 33, 55, '2026-02-25 02:24:42', 'registered', ''),
(106, 33, 56, '2026-02-25 02:24:42', 'registered', ''),
(107, 33, 57, '2026-02-25 02:24:42', 'registered', ''),
(108, 33, 58, '2026-02-25 02:24:42', 'cancelled', 'Unable to attend'),
(109, 33, 59, '2026-02-25 02:24:42', 'registered', ''),
(110, 33, 60, '2026-02-25 02:24:42', 'cancelled', 'Unable to attend'),
(111, 33, 61, '2026-02-25 02:24:42', 'registered', ''),
(112, 33, 63, '2026-02-25 02:24:42', 'registered', ''),
(113, 33, 64, '2026-02-25 02:24:42', 'registered', ''),
(114, 33, 65, '2026-02-25 02:24:42', 'cancelled', 'Unable to attend'),
(115, 33, 66, '2026-02-25 02:24:42', 'registered', ''),
(116, 33, 67, '2026-02-25 02:24:42', 'registered', ''),
(117, 33, 68, '2026-02-25 02:24:42', 'registered', ''),
(118, 33, 71, '2026-02-25 02:24:42', 'registered', ''),
(119, 34, 50, '2026-02-25 12:35:15', 'registered', ''),
(120, 34, 51, '2026-02-25 12:35:15', 'registered', ''),
(121, 34, 53, '2026-02-25 12:35:15', 'registered', ''),
(122, 34, 54, '2026-02-25 12:35:15', 'registered', ''),
(123, 34, 55, '2026-02-25 12:35:15', 'registered', ''),
(124, 34, 56, '2026-02-25 12:35:15', 'cancelled', 'Unable to attend'),
(125, 34, 57, '2026-02-25 12:35:15', 'registered', ''),
(126, 34, 60, '2026-02-25 12:35:15', 'registered', ''),
(127, 34, 61, '2026-02-25 12:35:15', 'registered', ''),
(128, 34, 63, '2026-02-25 12:35:15', 'registered', ''),
(129, 34, 64, '2026-02-25 12:35:15', 'registered', ''),
(130, 34, 65, '2026-02-25 12:35:15', 'registered', ''),
(131, 34, 66, '2026-02-25 12:35:15', 'registered', ''),
(132, 34, 67, '2026-02-25 12:35:15', 'registered', ''),
(133, 34, 68, '2026-02-25 12:35:15', 'registered', ''),
(135, 35, 50, '2026-02-25 13:10:20', 'registered', ''),
(136, 35, 51, '2026-02-25 13:10:20', 'cancelled', 'Unable to attend'),
(137, 35, 53, '2026-02-25 13:10:20', 'registered', ''),
(138, 35, 54, '2026-02-25 13:10:20', 'registered', ''),
(139, 35, 55, '2026-02-25 13:10:20', 'registered', ''),
(140, 36, 53, '2026-02-25 14:20:10', 'registered', ''),
(141, 36, 54, '2026-02-25 14:20:10', 'registered', ''),
(142, 37, 55, '2026-02-25 14:20:10', 'registered', ''),
(143, 37, 56, '2026-02-25 14:20:10', 'registered', ''),
(144, 38, 55, '2026-02-25 14:35:10', 'registered', ''),
(145, 38, 56, '2026-02-25 14:35:10', 'registered', ''),
(146, 39, 58, '2026-02-25 15:10:10', 'registered', ''),
(147, 39, 59, '2026-02-25 15:10:10', 'registered', ''),
(148, 39, 60, '2026-02-25 15:10:10', 'registered', ''),
(149, 39, 61, '2026-02-25 15:10:10', 'registered', ''),
(150, 39, 62, '2026-02-25 15:10:10', 'cancelled', 'Unable to attend'),
(151, 40, 63, '2026-02-25 03:50:10', 'registered', ''),
(152, 40, 64, '2026-02-25 03:50:10', 'cancelled', 'Unable to attend'),
(153, 40, 65, '2026-02-25 03:50:10', 'registered', ''),
(154, 40, 66, '2026-02-25 03:50:10', 'registered', ''),
(155, 40, 67, '2026-02-25 03:50:10', 'cancelled', 'Unable to attend'),
(156, 40, 68, '2026-02-25 03:50:10', 'registered', ''),
(157, 42, 50, '2026-02-25 04:50:05', 'cancelled', 'Unable to attend'),
(158, 42, 51, '2026-02-25 04:50:05', 'registered', ''),
(159, 42, 52, '2026-02-25 04:50:05', 'registered', ''),
(160, 42, 53, '2026-02-25 04:50:05', 'registered', ''),
(161, 42, 54, '2026-02-25 04:50:05', 'cancelled', 'Unable to attend'),
(162, 42, 55, '2026-02-25 04:50:05', 'registered', ''),
(163, 42, 56, '2026-02-25 04:50:05', 'cancelled', 'Unable to attend'),
(164, 44, 57, '2026-02-25 05:50:05', 'registered', ''),
(165, 44, 58, '2026-02-25 05:50:05', 'cancelled', 'Unable to attend'),
(166, 44, 59, '2026-02-25 05:50:05', 'registered', ''),
(167, 46, 57, '2026-02-26 06:50:05', 'registered', ''),
(168, 46, 58, '2026-02-26 06:50:05', 'registered', ''),
(169, 46, 59, '2026-02-26 06:50:05', 'registered', ''),
(170, 47, 60, '2026-02-25 19:40:08', 'registered', ''),
(171, 47, 61, '2026-02-25 19:40:08', 'registered', ''),
(172, 47, 62, '2026-02-25 19:40:08', 'registered', ''),
(173, 50, 60, '2026-02-25 19:40:08', 'registered', ''),
(174, 50, 61, '2026-02-25 19:40:08', 'cancelled', 'Unable to attend'),
(175, 50, 62, '2026-02-25 19:40:08', 'registered', ''),
(176, 51, 60, '2026-02-26 00:50:21', 'registered', ''),
(177, 51, 61, '2026-02-26 00:50:21', 'registered', ''),
(178, 51, 62, '2026-02-26 00:50:21', 'registered', ''),
(179, 52, 63, '2026-02-26 05:22:25', 'cancelled', 'Unable to attend'),
(180, 52, 64, '2026-02-26 05:22:25', 'registered', ''),
(181, 52, 65, '2026-02-26 05:22:25', 'registered', ''),
(182, 52, 66, '2026-02-26 05:22:25', 'cancelled', 'Unable to attend'),
(183, 52, 67, '2026-02-26 05:22:25', 'registered', ''),
(184, 53, 50, '2026-02-26 06:52:25', 'cancelled', 'Unable to attend'),
(185, 53, 51, '2026-02-26 06:52:25', 'registered', ''),
(186, 53, 53, '2026-02-26 06:52:25', 'registered', ''),
(187, 53, 68, '2026-02-26 06:52:25', 'registered', ''),
(188, 53, 69, '2026-02-26 06:52:25', 'registered', ''),
(189, 53, 70, '2026-02-26 06:52:25', 'registered', ''),
(190, 55, 55, '2026-02-25 19:32:25', 'cancelled', 'Unable to attend'),
(191, 55, 56, '2026-02-25 19:32:25', 'registered', ''),
(192, 55, 57, '2026-02-25 19:32:25', 'cancelled', 'Unable to attend'),
(193, 55, 71, '2026-02-25 19:32:25', 'registered', ''),
(194, 57, 55, '2026-02-26 08:27:25', 'registered', ''),
(195, 57, 56, '2026-02-26 08:27:25', 'registered', ''),
(196, 57, 57, '2026-02-26 08:27:25', 'registered', ''),
(197, 57, 71, '2026-02-26 08:27:25', 'cancelled', 'Unable to attend'),
(198, 58, 51, '2026-02-26 08:45:20', 'registered', ''),
(199, 58, 52, '2026-02-26 08:45:20', 'registered', ''),
(200, 58, 56, '2026-02-26 08:45:20', 'registered', ''),
(201, 58, 60, '2026-02-26 08:45:20', 'registered', ''),
(202, 58, 61, '2026-02-26 08:45:20', 'registered', ''),
(203, 59, 53, '2026-02-26 08:55:20', 'cancelled', 'Unable to attend'),
(204, 59, 54, '2026-02-26 08:55:20', 'registered', ''),
(205, 59, 55, '2026-02-26 08:55:20', 'registered', ''),
(206, 60, 62, '2026-02-26 09:15:20', 'registered', ''),
(207, 60, 63, '2026-02-26 09:15:20', 'registered', ''),
(208, 62, 52, '2026-02-26 11:50:20', 'registered', ''),
(209, 62, 57, '2026-02-26 11:50:20', 'registered', ''),
(210, 62, 59, '2026-02-26 11:50:20', 'registered', ''),
(211, 62, 63, '2026-02-26 11:50:20', 'registered', ''),
(212, 63, 51, '2026-02-26 12:00:20', 'registered', ''),
(213, 63, 52, '2026-02-26 12:00:20', 'registered', ''),
(214, 63, 54, '2026-02-26 12:00:20', 'registered', ''),
(215, 64, 62, '2026-02-26 12:20:20', 'registered', ''),
(216, 64, 71, '2026-02-26 12:20:20', 'registered', ''),
(217, 65, 50, '2026-02-26 13:00:20', 'registered', ''),
(218, 65, 53, '2026-02-26 13:00:20', 'registered', ''),
(219, 67, 52, '2026-02-26 15:15:20', 'registered', ''),
(220, 67, 57, '2026-02-26 15:15:20', 'registered', ''),
(221, 67, 59, '2026-02-26 15:15:20', 'registered', ''),
(222, 69, 56, '2026-02-26 16:15:20', 'registered', ''),
(223, 69, 62, '2026-02-26 16:15:20', 'cancelled', 'Unable to attend'),
(224, 69, 67, '2026-02-26 16:15:20', 'registered', ''),
(225, 69, 68, '2026-02-26 16:15:20', 'registered', ''),
(226, 71, 64, '2026-02-26 17:15:20', 'registered', ''),
(227, 71, 65, '2026-02-26 17:15:20', 'registered', ''),
(228, 71, 66, '2026-02-26 17:15:20', 'registered', ''),
(229, 72, 50, '2026-02-27 11:22:20', 'cancelled', ''),
(230, 72, 51, '2026-02-27 11:22:20', 'registered', ''),
(231, 72, 54, '2026-02-27 11:22:20', 'registered', ''),
(232, 72, 55, '2026-02-27 11:22:20', 'registered', ''),
(233, 72, 52, '2026-02-27 11:22:20', 'registered', ''),
(234, 72, 53, '2026-02-27 11:22:20', 'registered', ''),
(235, 73, 52, '2026-02-27 11:25:20', 'registered', ''),
(236, 73, 53, '2026-02-27 11:25:20', 'registered', ''),
(237, 74, 51, '2026-02-27 11:48:05', 'registered', ''),
(238, 74, 52, '2026-02-27 11:48:05', 'cancelled', ''),
(239, 74, 53, '2026-02-27 11:48:05', 'registered', ''),
(240, 74, 54, '2026-02-27 11:48:05', 'registered', ''),
(241, 74, 61, '2026-02-27 11:48:05', 'registered', ''),
(243, 75, 55, '2026-02-27 12:05:05', 'registered', ''),
(244, 75, 57, '2026-02-27 12:05:05', 'registered', ''),
(245, 79, 50, '2026-03-01 03:20:05', 'registered', ''),
(246, 79, 51, '2026-03-01 03:20:05', 'registered', ''),
(247, 80, 55, '2026-03-01 03:20:05', 'registered', ''),
(248, 80, 58, '2026-03-01 03:20:05', 'registered', ''),
(249, 80, 66, '2026-03-01 03:20:05', 'registered', ''),
(250, 84, 55, '2026-03-01 02:20:05', 'cancelled', 'Unable to attend'),
(251, 84, 58, '2026-03-01 02:20:05', 'registered', ''),
(252, 84, 66, '2026-03-01 02:20:05', 'registered', ''),
(253, 85, 51, '2026-03-01 02:20:05', 'cancelled', 'Unable to attend'),
(254, 85, 52, '2026-03-01 02:20:05', 'registered', ''),
(255, 86, 58, '2026-03-01 01:20:05', 'registered', ''),
(256, 86, 65, '2026-03-01 01:20:05', 'registered', ''),
(257, 86, 51, '2026-03-01 01:20:05', 'registered', ''),
(258, 87, 52, '2026-03-01 01:20:05', 'registered', ''),
(259, 87, 53, '2026-03-01 01:20:05', 'registered', ''),
(260, 89, 51, '2026-03-01 01:40:05', 'registered', ''),
(261, 89, 52, '2026-03-01 01:40:05', 'registered', ''),
(262, 89, 53, '2026-03-01 01:40:05', 'registered', ''),
(263, 90, 54, '2026-03-01 01:40:05', 'registered', ''),
(264, 90, 55, '2026-03-01 01:40:05', 'registered', ''),
(265, 90, 56, '2026-03-01 01:40:05', 'registered', ''),
(266, 90, 57, '2026-03-01 01:40:05', 'registered', ''),
(267, 91, 62, '2026-03-01 01:40:05', 'registered', ''),
(268, 91, 67, '2026-03-01 01:40:05', 'registered', ''),
(269, 92, 62, '2026-03-01 02:40:05', 'registered', ''),
(270, 92, 55, '2026-03-01 02:40:05', 'registered', ''),
(271, 93, 63, '2026-03-01 02:40:05', 'registered', ''),
(272, 93, 64, '2026-03-01 02:40:05', 'registered', ''),
(273, 96, 64, '2026-03-01 02:40:05', 'registered', ''),
(274, 97, 50, '2026-03-01 02:40:05', 'registered', ''),
(275, 97, 51, '2026-03-01 02:40:05', 'registered', ''),
(276, 97, 52, '2026-03-01 02:40:05', 'registered', ''),
(277, 98, 53, '2026-03-01 02:40:05', 'registered', ''),
(278, 98, 54, '2026-03-01 02:40:05', 'registered', ''),
(279, 98, 55, '2026-03-01 02:40:05', 'registered', ''),
(280, 98, 56, '2026-03-01 02:40:05', 'cancelled', ''),
(281, 100, 54, '2026-03-01 02:50:05', 'registered', ''),
(282, 100, 60, '2026-03-01 02:50:05', 'registered', ''),
(283, 100, 67, '2026-03-01 02:50:05', 'registered', ''),
(284, 101, 55, '2026-03-01 02:50:05', 'registered', ''),
(285, 101, 58, '2026-03-01 02:50:05', 'registered', ''),
(286, 101, 60, '2026-03-01 02:50:05', 'registered', ''),
(287, 102, 50, '2026-03-01 03:20:05', 'registered', ''),
(288, 102, 51, '2026-03-01 03:20:05', 'registered', ''),
(289, 104, 52, '2026-03-01 03:20:05', 'registered', ''),
(290, 104, 61, '2026-03-01 03:20:05', 'registered', ''),
(291, 105, 53, '2026-03-01 03:20:05', 'registered', ''),
(292, 105, 54, '2026-03-01 03:20:05', 'cancelled', 'Unable to attend'),
(293, 105, 55, '2026-03-01 03:20:05', 'registered', ''),
(294, 105, 56, '2026-03-01 03:20:05', 'registered', ''),
(295, 106, 60, '2026-03-01 03:20:05', 'registered', ''),
(296, 106, 61, '2026-03-01 03:20:05', 'registered', ''),
(297, 109, 63, '2026-03-01 03:50:05', 'registered', ''),
(298, 109, 64, '2026-03-01 03:50:05', 'registered', ''),
(299, 110, 51, '2026-03-01 03:50:05', 'registered', ''),
(300, 110, 52, '2026-03-01 03:50:05', 'registered', ''),
(301, 111, 53, '2026-03-01 03:50:05', 'registered', ''),
(302, 113, 53, '2026-03-01 03:50:05', 'registered', ''),
(303, 114, 51, '2026-03-01 03:50:05', 'cancelled', 'Unable to attend'),
(304, 114, 52, '2026-03-01 03:50:05', 'registered', ''),
(305, 114, 53, '2026-03-01 03:50:05', 'registered', ''),
(306, 114, 54, '2026-03-01 03:50:05', 'cancelled', 'Unable to attend'),
(307, 114, 55, '2026-03-01 03:50:05', 'registered', ''),
(308, 115, 56, '2026-03-01 03:50:05', 'registered', ''),
(309, 115, 57, '2026-03-01 03:50:05', 'registered', ''),
(310, 115, 58, '2026-03-01 03:50:05', 'registered', ''),
(311, 117, 59, '2026-03-01 03:50:05', 'cancelled', 'Unable to attend'),
(312, 117, 63, '2026-03-01 03:50:05', 'registered', ''),
(313, 118, 64, '2026-03-01 04:50:05', 'registered', ''),
(314, 118, 65, '2026-03-01 04:50:05', 'registered', ''),
(315, 118, 66, '2026-03-01 04:50:05', 'cancelled', 'Unable to attend'),
(316, 118, 64, '2026-03-01 04:50:05', 'cancelled', 'Unable to attend'),
(317, 119, 67, '2026-03-01 04:50:05', 'registered', ''),
(318, 119, 68, '2026-03-01 04:50:05', 'registered', ''),
(319, 121, 50, '2026-03-01 04:50:05', 'registered', ''),
(320, 121, 53, '2026-03-01 04:50:05', 'cancelled', 'Unable to attend'),
(321, 122, 56, '2026-03-01 04:50:05', 'registered', ''),
(322, 122, 62, '2026-03-01 04:50:05', 'registered', ''),
(323, 124, 51, '2026-03-01 04:50:05', 'registered', ''),
(324, 124, 61, '2026-03-01 04:50:05', 'registered', ''),
(325, 124, 52, '2026-03-01 04:50:05', 'registered', ''),
(326, 124, 53, '2026-03-01 04:50:05', 'registered', ''),
(327, 125, 54, '2026-03-01 04:50:05', 'registered', ''),
(328, 125, 55, '2026-03-01 04:50:05', 'registered', ''),
(329, 125, 57, '2026-03-01 04:50:05', 'cancelled', 'Unable to attend'),
(330, 126, 59, '2026-03-01 05:50:05', 'registered', ''),
(331, 126, 60, '2026-03-01 05:50:05', 'registered', ''),
(332, 128, 51, '2026-03-01 07:50:05', 'registered', ''),
(333, 128, 52, '2026-03-01 07:50:05', 'registered', ''),
(334, 130, 71, '2026-03-03 14:28:32', 'registered', NULL),
(337, 136, 93, '2026-03-08 13:54:58', 'registered', NULL),
(336, 135, 93, '2026-03-07 16:04:21', 'registered', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_results`
--

DROP TABLE IF EXISTS `event_results`;
CREATE TABLE IF NOT EXISTS `event_results` (
  `resultId` int NOT NULL AUTO_INCREMENT,
  `eventId` int NOT NULL,
  `organizerId` int DEFAULT NULL,
  `resultTitle` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `resultDate` date NOT NULL,
  `skillId` int DEFAULT NULL,
  `resultImage` varchar(500) DEFAULT NULL,
  `resultImage2` varchar(500) DEFAULT NULL,
  `resultImage3` varchar(500) DEFAULT NULL,
  `resultImage4` varchar(500) DEFAULT NULL,
  `resultImage5` varchar(500) DEFAULT NULL,
  `addedBy` int NOT NULL,
  `approvalStatus` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approvedBy` int DEFAULT NULL,
  `approvalNotes` text,
  `approvedDate` datetime DEFAULT NULL,
  `createdDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`resultId`),
  KEY `skillId` (`skillId`),
  KEY `approvedBy` (`approvedBy`),
  KEY `idx_event_results_status` (`approvalStatus`),
  KEY `idx_event_results_date` (`resultDate`),
  KEY `idx_event_results_event` (`eventId`),
  KEY `idx_event_results_addedby` (`addedBy`),
  KEY `organizerId` (`organizerId`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_results`
--

INSERT INTO `event_results` (`resultId`, `eventId`, `organizerId`, `resultTitle`, `description`, `resultDate`, `skillId`, `resultImage`, `resultImage2`, `resultImage3`, `resultImage4`, `resultImage5`, `addedBy`, `approvalStatus`, `approvedBy`, `approvalNotes`, `approvedDate`, `createdDate`, `updatedDate`) VALUES
(14, 135, 90, 'Test ', 'Test Description', '2026-03-07', 2, 'assets/result_img/69ac6975bb80a_result_Test__main.jpeg', 'assets/result_img/69ac6975bc14f_result_Test__2.jpg', NULL, NULL, NULL, 90, 'Approved', 38, '', '2026-03-07 23:39:16', '2026-03-07 18:07:49', '2026-03-07 18:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `event_stats`
--

DROP TABLE IF EXISTS `event_stats`;
CREATE TABLE IF NOT EXISTS `event_stats` (
  `eventId` int NOT NULL,
  `joinedCount` int DEFAULT '0',
  PRIMARY KEY (`eventId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_stats`
--

INSERT INTO `event_stats` (`eventId`, `joinedCount`) VALUES
(26, 11),
(27, 9),
(28, 7),
(29, 9),
(30, 19),
(31, 14),
(32, 10),
(33, 14),
(34, 14),
(35, 3),
(36, 2),
(37, 2),
(38, 2),
(39, 4),
(40, 4),
(41, 0),
(42, 4),
(43, 0),
(44, 2),
(45, 0),
(46, 3),
(47, 3),
(48, 0),
(49, 0),
(50, 2),
(51, 3),
(52, 3),
(53, 7),
(54, 0),
(55, 2),
(56, 0),
(57, 3),
(58, 5),
(59, 2),
(60, 2),
(61, 0),
(62, 4),
(63, 3),
(64, 2),
(65, 2),
(66, 0),
(67, 3),
(68, 0),
(69, 3),
(70, 0),
(71, 3),
(72, 5),
(73, 2),
(74, 4),
(75, 2),
(77, 0),
(78, 0),
(79, 2),
(80, 3),
(81, 0),
(82, 0),
(83, 3),
(84, 2),
(85, 1),
(86, 3),
(87, 2),
(88, 0),
(89, 3),
(90, 4),
(91, 2),
(92, 2),
(93, 2),
(95, 0),
(94, 0),
(96, 1),
(97, 3),
(98, 3),
(99, 0),
(100, 3),
(101, 3),
(102, 2),
(104, 2),
(105, 3),
(106, 2),
(107, 0),
(108, 0),
(109, 2),
(110, 2),
(111, 1),
(112, 0),
(113, 1),
(114, 3),
(115, 3),
(116, 0),
(117, 1),
(119, 2),
(120, 0),
(121, 1),
(122, 2),
(123, 0),
(124, 4),
(125, 2),
(126, 2),
(128, 2),
(130, 1),
(134, 0),
(135, 1),
(136, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `messageId` int NOT NULL AUTO_INCREMENT,
  `senderId` int NOT NULL,
  `receiverId` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `isRead` tinyint(1) DEFAULT '0',
  `sentAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`messageId`),
  KEY `idx_receiver_read` (`receiverId`,`isRead`),
  KEY `idx_sender` (`senderId`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`messageId`, `senderId`, `receiverId`, `subject`, `message`, `isRead`, `sentAt`) VALUES
(47, 38, 71, '✅ Event Registration Confirmed: Beach Cleanup', 'Hello Tharani Weerarathne,\n\nYour registration has been confirmed for \'Beach Cleanup\' on February 21, 2026.\n\nBest regards,\nUnity Volunteers Trust', 0, '2026-02-20 13:10:54'),
(48, 38, 71, '✅ Event Registration Confirmed: Exam Prep Workshop (Evening Edition)', 'Hello Tharani Weerarathne,\n\nYour registration has been confirmed for \'Exam Prep Workshop (Evening Edition)\' on February 21, 2026.\n\nBest regards,\nUnity Volunteers Trust', 0, '2026-02-21 10:47:05'),
(49, 38, 71, '✅ Event Registration Confirmed: Event 16', 'Hello Tharani Weerarathne,\n\nYour registration has been confirmed for \'Event 16\' on March 3, 2026.\n\nBest regards,\nUnity Volunteers Trust', 0, '2026-03-03 14:28:37'),
(50, 38, 71, 'Attendance Marked - Present', 'Your attendance has been marked as Present for \'Event 16\' on March 3, 2026', 0, '2026-03-03 15:14:31'),
(51, 38, 93, '✅ Event Registration Confirmed: Beach Cleanup', 'Hello Tharani Weerarathne,\n\nYour registration has been confirmed for \'Beach Cleanup\' on March 8, 2026.\n\nBest regards,\nUnity Volunteers Trust', 1, '2026-03-07 15:57:16'),
(52, 38, 93, '❌ Registration Cancelled: Beach Cleanup', 'Hello Tharani Weerarathne,\n\nYour registration for \'Beach Cleanup\' has been cancelled.\nReason: Test\n\nBest regards,\nUnity Volunteers Trust', 1, '2026-03-07 16:00:03'),
(53, 38, 93, '🔄 Welcome Back: Beach Cleanup', 'Hello Tharani Weerarathne,\n\nWelcome back! You have re-joined \'Beach Cleanup\'.\n\nBest regards,\nUnity Volunteers Trust', 1, '2026-03-07 16:01:27'),
(54, 38, 93, '🔄 Event Registration Changed', 'Hello Tharani Weerarathne,\n\nYour event registration has been updated:\n\n📅 FROM: Beach Cleanup\n   Date: March 8, 2026\n   Time: 08:00 PM\n\n📅 TO: Meditation & Peace Session\n   Date: March 7, 2026\n   Time: 08:00 PM\n   Location: Alawathupitiya Temple\n\nPlease review the new event details.\n\nBest regards,\nUnity Volunteers Trust', 0, '2026-03-07 16:04:24'),
(55, 38, 45, '❌ Event Cancelled: Beach Cleanup', 'Event \'Beach Cleanup\' scheduled for March 8, 2026 has been cancelled.\nReason: Test', 0, '2026-03-07 16:11:00'),
(56, 38, 93, '❌ Event Cancelled: Beach Cleanup', 'Event \'Beach Cleanup\' scheduled for March 8, 2026 has been cancelled.\nReason: Test', 0, '2026-03-07 16:11:04'),
(57, 38, 93, 'Attendance Marked - Present', 'Your attendance has been marked as Present for \'Meditation & Peace Session\' on March 7, 2026', 0, '2026-03-07 16:17:51'),
(58, 38, 93, 'Certificate Issued - CERT-20260307-F7C708BA', 'A certificate has been issued for your participation in \'Meditation & Peace Session\'. Certificate Number: CERT-20260307-F7C708BA', 1, '2026-03-07 17:21:00'),
(59, 93, 38, 'Test ', 'Test message', 1, '2026-03-08 06:47:47'),
(60, 38, 93, 'Test reply', 'Test message', 1, '2026-03-08 06:53:55'),
(61, 38, 93, 'Attendance Marked - Present', 'Your attendance has been marked as Present for \'Meditation & Peace Session\' on March 8, 2026', 0, '2026-03-08 07:19:05'),
(62, 38, 93, '✅ Event Registration Confirmed: Youth Talent Evening', 'Hello Tharani Weerarathne,\n\nYour registration has been confirmed for \'Youth Talent Evening\' on March 8, 2026.\n\nBest regards,\nUnity Volunteers Trust', 0, '2026-03-08 13:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `organizer_requests`
--

DROP TABLE IF EXISTS `organizer_requests`;
CREATE TABLE IF NOT EXISTS `organizer_requests` (
  `requestId` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `organizationName` varchar(200) DEFAULT NULL,
  `organizationType` varchar(100) DEFAULT NULL,
  `organizationDescription` text,
  `yearsOfExperience` int DEFAULT NULL,
  `previousEvents` text,
  `motivation` text,
  `requestStatus` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requestDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewedBy` int DEFAULT NULL,
  `reviewDate` timestamp NULL DEFAULT NULL,
  `reviewNotes` text,
  PRIMARY KEY (`requestId`),
  KEY `reviewedBy` (`reviewedBy`),
  KEY `idx_request_status` (`requestStatus`),
  KEY `idx_user_id` (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `organizer_requests`
--

INSERT INTO `organizer_requests` (`requestId`, `userId`, `organizationName`, `organizationType`, `organizationDescription`, `yearsOfExperience`, `previousEvents`, `motivation`, `requestStatus`, `requestDate`, `reviewedBy`, `reviewDate`, `reviewNotes`) VALUES
(5, 90, 'Unity Group', 'Independent', 'test1', 1, 'test1', 'test1', 'Approved', '2025-12-29 12:18:10', 38, '2025-12-29 12:20:16', ''),
(6, 91, 'Green Future Volunteers', 'Independent', 'test2', 1, 'test2', 'test2', 'Approved', '2026-02-23 07:23:33', 38, '2026-02-23 07:23:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `result_comments`
--

DROP TABLE IF EXISTS `result_comments`;
CREATE TABLE IF NOT EXISTS `result_comments` (
  `commentId` int NOT NULL AUTO_INCREMENT,
  `resultId` int NOT NULL,
  `userId` int NOT NULL,
  `comment` text NOT NULL,
  `parentCommentId` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDeleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`commentId`),
  KEY `resultId` (`resultId`),
  KEY `userId` (`userId`),
  KEY `parentCommentId` (`parentCommentId`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `result_comments`
--

INSERT INTO `result_comments` (`commentId`, `resultId`, `userId`, `comment`, `parentCommentId`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(7, 14, 93, 'Test', NULL, '2026-03-07 18:19:49', '2026-03-07 18:19:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `result_reactions`
--

DROP TABLE IF EXISTS `result_reactions`;
CREATE TABLE IF NOT EXISTS `result_reactions` (
  `reactionId` int NOT NULL AUTO_INCREMENT,
  `resultId` int NOT NULL,
  `userId` int NOT NULL,
  `reactionType` enum('like','dislike') NOT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reactionId`),
  UNIQUE KEY `unique_reaction` (`resultId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `result_reactions`
--

INSERT INTO `result_reactions` (`reactionId`, `resultId`, `userId`, `reactionType`, `createdAt`) VALUES
(5, 14, 93, 'like', '2026-03-07 18:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE IF NOT EXISTS `skills` (
  `skillId` int NOT NULL AUTO_INCREMENT,
  `skillName` varchar(50) NOT NULL,
  PRIMARY KEY (`skillId`),
  UNIQUE KEY `skillName` (`skillName`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skillId`, `skillName`) VALUES
(1, 'Teaching'),
(2, 'Event Organizing'),
(3, 'First Aid'),
(4, 'Photography'),
(5, 'Cooking'),
(6, 'Environmental Work'),
(7, 'Social Media'),
(8, 'Graphic Design'),
(9, 'Elderly Care'),
(10, 'Translation'),
(11, 'IT and technical support'),
(12, 'Public speaking or presenting');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephoneNo` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') NOT NULL,
  `role` enum('Volunteer','Admin','Coordinator','Organizer','Volunteer+Organizer') DEFAULT 'Volunteer',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `name`, `email`, `password`, `telephoneNo`, `location`, `gender`, `role`) VALUES
(38, 'Admin', 'infocontact256@gmail.com', '$2y$10$ayre/TATEv/YZ4hNqvWHzOLOTZV6RbxmrC599uzFamFv2cb2UBFF.', '0111234567', 'Headquarters', 'Male', 'Admin'),
(39, 'Dharshani Kumari', 'coordinator1@gmail.com', '$2y$10$AWlxf8mSVfVrRkOGIsaqX.OldagKzYE/yX5oDBZ9OVvxmZh1T71tq', '077000001', 'Colombo', 'Female', 'Coordinator'),
(40, 'Ranjani Silva', 'coordinator2@gmail.com', '$2y$10$sRUiiXTTLQ/GE5w5tYDa0.X8OlhyI/8Cbc3v6eDHMZr7st19XuzRa', '077000002', 'Location 2', 'Female', 'Coordinator'),
(41, 'Robert Perera', 'coordinator3@gmail.com', '$2y$10$F8TebkF.kFh.aDP8WQnhUeBB6Q5o5RaNojzbDxrS4xfaCvNHmTqdi', '077000003', 'Location 3', 'Male', 'Coordinator'),
(42, 'Linda', 'coordinator4@gmail.com', '$2y$10$iKTqGGfx1YYXwegAGsPR0Ou9Pr05Nuy3D1AyN5/400xake0YMW6S6', '077000004', 'Location 4', 'Female', 'Coordinator'),
(43, 'Pradeep Alwis', 'coordinator5@gmail.com', '$2y$10$Ucf.CDvqXkNhVRs3keVn3uuehYsdWfScGZjZZ6HRSzZDZsXuBoml2', '077000005', 'Location 5', 'Male', 'Coordinator'),
(44, 'Elizabeth Davis', 'coordinator6@gmail.com', '$2y$10$LFoipfVdaQb2C5E.Il9Kn.HZ5Ziou9rWX1GVMNQsQvaloDBoD6TZ6', '077000006', 'Location 6', 'Female', 'Coordinator'),
(45, 'Anjali', 'coordinator7@gmail.com', '$2y$10$czn1VM2vfimvF2TZcCJ7JusSfWLHEFuN/BmSx7qAfQu2xVLaGKleK', '077000007', 'Negombo', 'Female', 'Coordinator'),
(46, 'Patricia Wilson', 'coordinator8@gmail.com', '$2y$10$5HvAcnBudUeG4TJRNASRbOtXz9kRLcJdRCcQzdz9SRb2KmRAG2.DK', '077000008', 'Location 8', 'Female', 'Coordinator'),
(47, 'David', 'coordinator9@gmail.com', '$2y$10$6.AsCTnn/TGorD.E6j922.rO6.5IhJKssMCze8f1KHb/Mt38XE0DS', '077000009', 'Location 9', 'Male', 'Coordinator'),
(48, 'Priya', 'coordinator10@gmail.com', '$2y$10$qbeVIaUZlcw9ZjHc282SXu4bjtRuiiab99w2KPaVMmKOHMNcGvGX2', '0770000010', 'Location 10', 'Female', 'Coordinator'),
(49, 'James', 'coordinator11@gmail.com', '$2y$10$AUaPF0t4AjU92ma.YTV3vOAGFC30E4JwGYpScFMFg7Vv79eN./hHS', '0770000011', 'Location 11', 'Male', 'Coordinator'),
(50, 'Hasal Dilmith', 'volunteer1@gmail.com', '$2y$10$X6IGOUqzzvAyE3DVRp56Fe1.MpXPCi/DIpRfL2wesPw.8WgY.lLTu', '071089009', 'Kandana', 'Male', 'Volunteer'),
(51, 'Nethul Weerarathne', 'volunteer2@gmail.com', '$2y$10$1MopG2J2E/YHS6hHLiywsOIjsdSU3bwN6nvtDw3.B0Ica9QCGQbRW', '07100002', 'Kelaniya', 'Male', 'Volunteer'),
(52, 'Olivia', 'volunteer3@gmail.com', '$2y$10$rXjtx61D79M5JXMcf9GFtON/Kh3UNdjwHmINQ3nCjZZkgAPPenMyO', '07100003', 'Kandana', 'Female', 'Volunteer'),
(53, 'Thusitha Perera', 'volunteer4@gmail.com', '$2y$10$kg2IBFarGjIP4JLOMyy1WepjGhi8ReWlysSXERyRmAoxjsCr69Rfm', '07100004', 'Seeduwa', 'Male', 'Volunteer'),
(54, 'Mevindi', 'volunteer5@gmail.com', '$2y$10$zlXMvSPjT6PALd9VzyBpVeWA2ElZvbWL.zO7gjFTG7VyYG87Qp4by', '07100005', 'City 5', 'Male', 'Volunteer'),
(55, 'Shakila Navodani', 'volunteer6@gmail.com', '$2y$10$FIcxiQYyCZRSFN/KjsBM.OYtqQrkQp.qxz58io0bH9dv0JEmjrJIC', '07100006', 'Negombo', 'Female', 'Volunteer'),
(56, 'Sophia', 'volunteer7@gmail.com', '$2y$10$oAgvxuLwj5JVMdVDjZlYQuDbDDbH/WUPwrPKV0lpsnQCOQC/JH/yC', '07100007', 'City 7', 'Female', 'Volunteer'),
(57, 'Yenul Perera', 'volunteer8@gmail.com', '$2y$10$2oZL5vI4AEUVydL3GLycT.WnDqGxMb7o/o99gy3Z6K99ILyFGn2Tu', '07100008', 'Liyanagemulla', 'Male', 'Volunteer'),
(58, 'Amaya', 'volunteer9@gmail.com', '$2y$10$Yg2dGIFoHOvkNuEbQUOHouborD4RTslhCjzgDlF2xER8JX7onoGZ2', '07100009', 'Negombo', 'Female', 'Volunteer'),
(59, 'Dulani', 'volunteer10@gmail.com', '$2y$10$zu5PAJ3OsNANRTt/AOWsw.vZexGXG7aTwdpML8OeGcDUG84MLhjau', '071000010', 'City 10', 'Female', 'Volunteer'),
(60, 'Oshini Silva', 'volunteer11@gmail.com', '$2y$10$swW4O8tJPR6gmx/z5nsVbe0rbYcsq.seZRgWBQ2bJIC6CNgwCNghq', '071000011', 'Minuwangoda', 'Female', 'Volunteer'),
(61, 'Sayuri', 'volunteer12@gmail.com', '$2y$10$ERW426eD2l/HDZUB1uBF0eAbGcTD8FIGippjcTpWoRAsF7sH7JNUm', '071000012', 'City 12', 'Female', 'Volunteer'),
(62, 'Amelia', 'volunteer13@gmail.com', '$2y$10$OjiKfXkTNci1d3pVLXarTO229VeJ4AbK2QIFrMsXuRVaZClRm1GAG', '071000013', 'Raddolugama', 'Female', 'Volunteer'),
(63, 'Oliver', 'volunteer14@gmail.com', '$2y$10$ZZKpU3DQE/N3Pb.BVWqFE.Fp.oi4iZMccVscG/JHEz4XuIr7r.zIm', '071000014', 'Ragama', 'Male', 'Volunteer'),
(64, 'Suresh Perera', 'volunteer15@gmail.com', '$2y$10$BAKX3hOYH7hpJG4nqJx4qOVX9YYa8zb7garw2efRb9zsf/gGV7Ibi', '071000015', 'Colombo', 'Male', 'Volunteer'),
(65, 'Heshan', 'volunteer16@gmail.com', '$2y$10$7Mru.2rMkXvel.SinH2J4.QMDzIwnok2CfwX/n/JBdiELR/m8cckm', '071000016', 'City 16', 'Male', 'Volunteer'),
(66, 'Charlotte Mitchell', 'volunteer17@gmail.com', '$2y$10$FYZOGhZUMyv2UNynva/JVOzLwFD8Uy.vFSWHLXexhLOPR2BqWh5yO', '071000017', 'Negombo', 'Female', 'Volunteer'),
(67, 'Akila Perez', 'volunteer18@gmail.com', '$2y$10$5fGjam4gmvuja4ZnBRAPKuQSaso5bDjnmSohsfGBM/EI6iZvpnHFS', '071000018', 'Jaela', 'Male', 'Volunteer'),
(68, 'Pasindu Silva', 'volunteer19@gmail.com', '$2y$10$z4qXukFvkYKSRiDm2MGm0.Hro7i8GoQK4LNVvQTsx0dw7TLIsSRZ6', '071000019', 'Kurana', 'Male', 'Volunteer'),
(69, 'Kaveesha Perera', 'volunteer20@gmail.com', '$2y$10$Up698Fa1pwhd1zsUv9xKOe6j2.Nuo/hxLjnHJWVmCXge50kmq1IPG', '071000020', 'Seeduwa', 'Female', 'Volunteer'),
(71, 'Sasindi Weerarathne', 'tharani2003weerarthne@gmail.com', '$2y$10$kXUkAP5mnTsZdEODBPoqbOI/nugjmq0x4q6zq4/OlTx/Qwhbqw9P6', '0789356589', 'Seeduwa', 'Female', 'Volunteer'),
(90, 'Unity Group', 'sasindikoralagamage@gmail.com', '$2y$10$PgGyO0kIUwWbFjf1jdA79uFHfk0eXT8g2dTnbKyPC4U1dNmJJ.ClW', '0789356580', 'Colombo', 'Female', 'Organizer'),
(91, 'Green Future Volunteers', 'greenfuture1organizer@example.com', '$2y$10$jfGjj35hfJS.VLGV99djiu7SuhMT6RsCIzTtHVUUG.CKdJNoqM8fG', '0771234567', 'Negombo', 'Prefer not to say', 'Organizer'),
(93, 'Tharani Weerarathne', 'tharanikumari2003@gmail.com', '$2y$10$D/a6HlUyHRd1VDGPsskPM.wskoNSY9v4x7lFqSj4VkAt3QKU591am', '0789356578', 'Seeduwa', 'Female', 'Volunteer'),
(97, 'Sarani Perera', 'volunteer45678@gmail.com', '$2y$10$cHDa0rT8fi/I/WRip2hwN.b0IDVL3uvL3DLd76VkBnpMUcZEgZgQa', '0789356580', 'Colombo', 'Female', 'Volunteer');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_skills`
--

DROP TABLE IF EXISTS `volunteer_skills`;
CREATE TABLE IF NOT EXISTS `volunteer_skills` (
  `userId` int NOT NULL,
  `skillId` int NOT NULL,
  PRIMARY KEY (`userId`,`skillId`),
  KEY `skillId` (`skillId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `volunteer_skills`
--

INSERT INTO `volunteer_skills` (`userId`, `skillId`) VALUES
(50, 11),
(51, 4),
(52, 6),
(53, 11),
(54, 2),
(54, 7),
(55, 12),
(56, 1),
(57, 6),
(57, 9),
(58, 10),
(58, 12),
(59, 6),
(59, 9),
(60, 7),
(61, 4),
(62, 1),
(62, 3),
(63, 6),
(64, 5),
(65, 8),
(65, 10),
(66, 8),
(67, 1),
(67, 7),
(68, 4),
(69, 1),
(69, 5),
(71, 3),
(87, 1),
(96, 1),
(97, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
