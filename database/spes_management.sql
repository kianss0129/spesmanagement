-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2026 at 02:04 PM
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
-- Database: `managements`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'Posted a new announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:09:21', '2026-06-09 08:09:21'),
(2, 'default', 'Deleted announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:31:22', '2026-06-09 08:31:22'),
(3, 'default', 'Posted a new announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:32:19', '2026-06-09 08:32:19'),
(4, 'default', 'Updated announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:38:50', '2026-06-09 08:38:50'),
(5, 'default', 'Updated announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:40:25', '2026-06-09 08:40:25'),
(6, 'default', 'Updated announcement: 📢 SPES Application Announcement', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 08:45:28', '2026-06-09 08:45:28'),
(7, 'default', 'Interview scheduled by PESO', 'App\\Models\\Interview', NULL, 1, 'App\\Models\\User', 1, '[]', NULL, '2026-06-09 19:36:53', '2026-06-09 19:36:53'),
(8, 'default', 'Interview marked as passed', 'App\\Models\\Interview', NULL, 1, 'App\\Models\\User', 3, '[]', NULL, '2026-06-09 19:48:03', '2026-06-09 19:48:03'),
(9, 'default', 'Interview scheduled by PESO', 'App\\Models\\Interview', NULL, 3, 'App\\Models\\User', 1, '[]', NULL, '2026-06-13 22:51:32', '2026-06-13 22:51:32'),
(10, 'default', 'Interview marked as passed', 'App\\Models\\Interview', NULL, 3, 'App\\Models\\User', 3, '[]', NULL, '2026-06-13 23:00:07', '2026-06-13 23:00:07'),
(11, 'default', 'Interview scheduled by PESO', 'App\\Models\\Interview', NULL, 4, 'App\\Models\\User', 1, '[]', NULL, '2026-06-14 05:25:30', '2026-06-14 05:25:30'),
(12, 'default', 'Interview marked as passed', 'App\\Models\\Interview', NULL, 4, 'App\\Models\\User', 3, '[]', NULL, '2026-06-14 05:50:44', '2026-06-14 05:50:44'),
(13, 'default', 'Beneficiary registered account', 'App\\Models\\User', NULL, 17, 'App\\Models\\User', 17, '{\"module\":\"Beneficiary\",\"user_id\":17,\"status\":\"registered\"}', NULL, '2026-06-14 08:01:51', '2026-06-14 08:01:51'),
(14, 'default', 'Beneficiary submitted SPES application', 'App\\Models\\Application', NULL, 14, 'App\\Models\\User', 17, '{\"module\":\"Beneficiary\",\"user_id\":17,\"status\":\"applied\"}', NULL, '2026-06-14 08:07:30', '2026-06-14 08:07:30'),
(15, 'default', 'Requirement correction requested', 'App\\Models\\Beneficiary', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Requirements\",\"user_id\":7,\"status\":\"needs_correction\"}', NULL, '2026-06-14 08:33:52', '2026-06-14 08:33:52'),
(16, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 1, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":6,\"status\":\"approved\"}', NULL, '2026-06-17 13:56:00', '2026-06-17 13:56:00'),
(17, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":7,\"status\":\"approved\"}', NULL, '2026-06-17 13:56:16', '2026-06-17 13:56:16'),
(18, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 7, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":14,\"status\":\"approved\"}', NULL, '2026-06-17 13:56:27', '2026-06-17 13:56:27'),
(19, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 1, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":6,\"status\":\"approved\"}', NULL, '2026-06-17 13:57:39', '2026-06-17 13:57:39'),
(20, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":7,\"status\":\"approved\"}', NULL, '2026-06-17 17:40:58', '2026-06-17 17:40:58'),
(21, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 10, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":17,\"status\":\"approved\"}', NULL, '2026-06-17 17:41:10', '2026-06-17 17:41:10'),
(22, 'default', 'Beneficiary submitted SPES application', 'App\\Models\\Application', NULL, 15, 'App\\Models\\User', 20, '{\"module\":\"Beneficiary\",\"user_id\":20,\"status\":\"applied\"}', NULL, '2026-06-17 18:20:26', '2026-06-17 18:20:26'),
(23, 'default', 'Beneficiary application approved', 'App\\Models\\Beneficiary', NULL, 11, 'App\\Models\\User', 1, '{\"module\":\"Beneficiary\",\"user_id\":20,\"status\":\"approved\"}', NULL, '2026-06-17 18:20:57', '2026-06-17 18:20:57'),
(24, 'default', 'Exam scheduled by PESO', 'App\\Models\\Exam', NULL, 1, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":1,\"application_id\":1,\"beneficiary_id\":1,\"schedule_group_id\":\"198f8b68-261a-4bcd-8b9e-0910e7e9c5e2\",\"batch_title\":\"Batch 6\\/10\\/2026\",\"exam_date\":\"2026-06-11 08:30:00\",\"location\":\"City hall\",\"status\":\"completed\",\"backfilled\":true}', NULL, '2026-06-09 19:25:48', '2026-06-09 19:25:48'),
(25, 'default', 'Exam marked as passed', 'App\\Models\\Exam', NULL, 1, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":1,\"application_id\":1,\"beneficiary_id\":1,\"status\":\"completed\",\"result\":\"passed\",\"backfilled\":true}', NULL, '2026-06-09 19:29:02', '2026-06-09 19:29:02'),
(26, 'default', 'Exam scheduled by PESO', 'App\\Models\\Exam', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":2,\"application_id\":10,\"beneficiary_id\":7,\"schedule_group_id\":\"ba31deca-d46d-4034-815f-efff7b69c18e\",\"batch_title\":\"BATCH A\",\"exam_date\":\"2026-06-15 08:00:00\",\"location\":\"CPESO OFFICE\",\"status\":\"completed\",\"backfilled\":true}', NULL, '2026-06-13 22:44:13', '2026-06-13 22:44:13'),
(27, 'default', 'Exam marked as passed', 'App\\Models\\Exam', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":2,\"application_id\":10,\"beneficiary_id\":7,\"status\":\"completed\",\"result\":\"passed\",\"backfilled\":true}', NULL, '2026-06-13 22:47:01', '2026-06-13 22:47:01'),
(28, 'default', 'Exam scheduled by PESO', 'App\\Models\\Exam', NULL, 3, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":3,\"application_id\":12,\"beneficiary_id\":9,\"schedule_group_id\":\"bd016f4d-0009-4f69-a6ab-def0ce6988a1\",\"batch_title\":\"Batch 6\\/14\\/2026\",\"exam_date\":\"2026-06-16 08:22:00\",\"location\":\"CPESO OFFICE\",\"status\":\"completed\",\"backfilled\":true}', NULL, '2026-06-14 05:23:43', '2026-06-14 05:23:43'),
(29, 'default', 'Exam marked as passed', 'App\\Models\\Exam', NULL, 3, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":3,\"application_id\":12,\"beneficiary_id\":9,\"status\":\"completed\",\"result\":\"passed\",\"backfilled\":true}', NULL, '2026-06-14 05:24:10', '2026-06-14 05:24:10'),
(30, 'default', 'Exam scheduled by PESO', 'App\\Models\\Exam', NULL, 4, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":4,\"application_id\":15,\"beneficiary_id\":11,\"schedule_group_id\":\"689dffd6-7707-4134-bbcb-742facf522dd\",\"batch_title\":\"Batch 6\\/18\\/2026\",\"exam_date\":\"2026-06-19 08:00:00\",\"location\":\"cpeso office\",\"status\":\"completed\",\"backfilled\":true}', NULL, '2026-06-17 18:22:13', '2026-06-17 18:22:13'),
(31, 'default', 'Exam marked as passed', 'App\\Models\\Exam', NULL, 4, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":4,\"application_id\":15,\"beneficiary_id\":11,\"status\":\"completed\",\"result\":\"passed\",\"backfilled\":true}', NULL, '2026-06-17 18:22:25', '2026-06-17 18:22:25'),
(32, 'default', 'Exam scheduled by PESO', 'App\\Models\\Exam', NULL, 5, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":5,\"application_id\":14,\"beneficiary_id\":10,\"schedule_group_id\":\"689dffd6-7707-4134-bbcb-742facf522dd\",\"batch_title\":\"Batch 6\\/18\\/2026\",\"exam_date\":\"2026-06-19 08:00:00\",\"location\":\"cpeso office\",\"status\":\"completed\",\"backfilled\":true}', NULL, '2026-06-17 18:22:18', '2026-06-17 18:22:18'),
(33, 'default', 'Exam marked as passed', 'App\\Models\\Exam', NULL, 5, 'App\\Models\\User', 1, '{\"module\":\"Exam\",\"exam_id\":5,\"application_id\":14,\"beneficiary_id\":10,\"status\":\"completed\",\"result\":\"passed\",\"backfilled\":true}', NULL, '2026-06-17 18:22:31', '2026-06-17 18:22:31'),
(34, 'default', 'Interview scheduled by PESO', 'App\\Models\\Interview', NULL, 5, 'App\\Models\\User', 1, '{\"module\":\"Interview\",\"application_id\":15,\"beneficiary_id\":11,\"interviewer_id\":3,\"scheduled_at\":\"2026-06-18 02:50:00\",\"end_at\":\"2026-06-18 03:20:00\",\"schedule_group_id\":\"8d3bbfd8-0e24-4171-abc6-aa12ac90be3f\",\"batch_title\":null}', NULL, '2026-06-17 18:44:51', '2026-06-17 18:44:51'),
(35, 'default', 'Interview scheduled by PESO', 'App\\Models\\Interview', NULL, 6, 'App\\Models\\User', 1, '{\"module\":\"Interview\",\"application_id\":14,\"beneficiary_id\":10,\"interviewer_id\":3,\"scheduled_at\":\"2026-06-18 03:20:00\",\"end_at\":\"2026-06-18 03:50:00\",\"schedule_group_id\":\"8d3bbfd8-0e24-4171-abc6-aa12ac90be3f\",\"batch_title\":null}', NULL, '2026-06-17 18:44:56', '2026-06-17 18:44:56'),
(36, 'default', 'Interview marked as passed', 'App\\Models\\Interview', NULL, 5, 'App\\Models\\User', 3, '[]', NULL, '2026-06-17 18:49:53', '2026-06-17 18:49:53'),
(37, 'default', 'Interview marked as passed', 'App\\Models\\Interview', NULL, 6, 'App\\Models\\User', 3, '[]', NULL, '2026-06-17 18:50:04', '2026-06-17 18:50:04'),
(38, 'default', 'Beneficiary assigned to employer', 'App\\Models\\Beneficiary', NULL, 11, 'App\\Models\\User', 1, '{\"module\":\"Placement\",\"user_id\":20,\"status\":\"assigned\"}', NULL, '2026-06-17 18:51:04', '2026-06-17 18:51:04'),
(39, 'default', 'Beneficiary assigned to employer', 'App\\Models\\Beneficiary', NULL, 10, 'App\\Models\\User', 1, '{\"module\":\"Placement\",\"user_id\":17,\"status\":\"assigned\"}', NULL, '2026-06-17 18:51:07', '2026-06-17 18:51:07'),
(40, 'default', 'Beneficiary submitted attendance', 'App\\Models\\Attendance', NULL, 8, 'App\\Models\\User', 20, '{\"module\":\"Attendance\",\"user_id\":20,\"status\":\"submitted\"}', NULL, '2026-06-17 19:01:24', '2026-06-17 19:01:24'),
(41, 'default', 'Beneficiary timed out attendance', 'App\\Models\\Attendance', NULL, 8, 'App\\Models\\User', 20, '{\"module\":\"Attendance\",\"user_id\":20,\"status\":\"timed_out\"}', NULL, '2026-06-17 19:01:42', '2026-06-17 19:01:42'),
(42, 'default', 'Beneficiary submitted daily accomplishment report', 'App\\Models\\WorkOutput', NULL, 5, 'App\\Models\\User', 20, '{\"module\":\"Work Output\",\"user_id\":20,\"status\":\"submitted\"}', NULL, '2026-06-17 19:04:41', '2026-06-17 19:04:41'),
(43, 'default', 'Work output approved', 'App\\Models\\WorkOutput', NULL, 5, 'App\\Models\\User', 5, '{\"module\":\"Work Output\",\"user_id\":5,\"status\":\"approved\"}', NULL, '2026-06-17 19:05:22', '2026-06-17 19:05:22'),
(44, 'default', 'Beneficiary submitted daily accomplishment report', 'App\\Models\\WorkOutput', NULL, 6, 'App\\Models\\User', 20, '{\"module\":\"Work Output\",\"user_id\":20,\"status\":\"submitted\"}', NULL, '2026-06-17 19:05:38', '2026-06-17 19:05:38'),
(45, 'default', 'Work output returned for correction', 'App\\Models\\WorkOutput', NULL, 6, 'App\\Models\\User', 5, '{\"module\":\"Work Output\",\"user_id\":5,\"status\":\"needs_correction\"}', NULL, '2026-06-17 19:05:53', '2026-06-17 19:05:53'),
(46, 'default', 'Beneficiary resubmitted daily accomplishment report', 'App\\Models\\WorkOutput', NULL, 6, 'App\\Models\\User', 20, '{\"module\":\"Work Output\",\"user_id\":20,\"status\":\"resubmitted\",\"original_submitted_at\":\"2026-06-18 03:05:38\",\"resubmitted_at\":\"2026-06-18 03:13:07\"}', NULL, '2026-06-17 19:13:07', '2026-06-17 19:13:07'),
(47, 'default', 'Employer posted job', 'App\\Models\\JobListing', NULL, 13, 'App\\Models\\User', 5, '{\"module\":\"Employer\",\"user_id\":5,\"status\":\"posted\"}', NULL, '2026-06-24 11:01:55', '2026-06-24 11:01:55'),
(48, 'default', 'Employer posted job', 'App\\Models\\JobListing', NULL, 14, 'App\\Models\\User', 5, '{\"module\":\"Employer\",\"user_id\":5,\"status\":\"posted\"}', NULL, '2026-06-24 11:08:33', '2026-06-24 11:08:33'),
(49, 'default', 'Beneficiary assigned to employer', 'App\\Models\\Beneficiary', NULL, 2, 'App\\Models\\User', 1, '{\"module\":\"Placement\",\"user_id\":7,\"status\":\"assigned\"}', NULL, '2026-06-24 12:02:13', '2026-06-24 12:02:13');

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `target_role` varchar(255) NOT NULL DEFAULT 'all',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `image`, `target_role`, `created_by`, `created_at`, `updated_at`) VALUES
(2, '📢 SPES Application Announcement', 'Special Program for Employment of Students (SPES)\r\n\r\nThe Special Program for Employment of Students (SPES) is now open for applications! This program provides temporary employment opportunities to deserving students and out-of-school youth, helping them earn income while gaining valuable work experience during school breaks.\r\n\r\nInterested applicants are encouraged to submit their requirements within the application period. Qualified participants will have the opportunity to develop their skills, gain workplace experience, and contribute to their community.\r\n\r\nFor application requirements, eligibility criteria, and submission details, please contact the designated SPES coordinator or visit the nearest Public Employment Service Office (PESO).\r\n\r\nApply now and take a step toward a brighter future through SPES!', 'announcements/lrcqFfCLajd2wGo5XCD75svmWPAukAERLTDsRaLQ.png', 'all', NULL, '2026-06-09 08:32:19', '2026-06-09 08:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_reads`
--

CREATE TABLE `announcement_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcement_reads`
--

INSERT INTO `announcement_reads` (`id`, `announcement_id`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 2, 5, '2026-06-09 08:39:03', '2026-06-09 08:39:03'),
(3, 2, 6, '2026-06-09 18:01:09', '2026-06-09 18:01:09'),
(4, 2, 7, '2026-06-11 12:45:46', '2026-06-11 12:45:46'),
(8, 2, 17, '2026-06-14 08:33:16', '2026-06-14 08:33:16'),
(9, 2, 16, '2026-06-14 08:43:07', '2026-06-14 08:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED NOT NULL,
  `job_listing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'applied',
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employer_acknowledged_at` timestamp NULL DEFAULT NULL,
  `employer_acknowledged_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `beneficiary_id`, `job_listing_id`, `status`, `certificate_path`, `created_at`, `updated_at`, `batch_id`, `employer_acknowledged_at`, `employer_acknowledged_by`) VALUES
(1, 1, NULL, 'approved', NULL, '2026-06-09 19:25:48', '2026-06-17 13:55:59', NULL, NULL, NULL),
(3, 1, 2, 'rejected', 'certificates/4tmbiCImx3j1JmEEiMcGi5W8ugBlDVulmrkipcR4.jpg', '2026-06-09 20:27:55', '2026-06-13 22:14:28', NULL, NULL, NULL),
(10, 7, NULL, 'approved', NULL, '2026-06-13 22:28:52', '2026-06-17 13:56:27', NULL, NULL, NULL),
(11, 7, 4, 'completed', 'certificates/PA5KiY9AjB2X3ALPpHc9Wx0fcsUoovFUBIM8Y2B6.jpg', '2026-06-13 23:02:33', '2026-06-14 00:11:54', NULL, '2026-06-13 23:11:01', 5),
(12, 9, NULL, 'qualified', NULL, '2026-06-14 05:19:13', '2026-06-14 05:51:02', NULL, NULL, NULL),
(13, 9, 5, 'completed', 'certificates/7D4ko7I3vtIJnp6BFBgPAUE6710qwNBUTgZmp3HF.jpg', '2026-06-14 05:52:36', '2026-06-14 06:27:23', NULL, '2026-06-14 06:03:41', 5),
(14, 10, NULL, 'qualified', NULL, '2026-06-14 08:07:30', '2026-06-17 18:50:23', NULL, NULL, NULL),
(15, 11, NULL, 'qualified', NULL, '2026-06-17 18:20:26', '2026-06-17 18:50:18', NULL, NULL, NULL),
(16, 11, 2, 'ongoing', NULL, '2026-06-17 18:51:04', '2026-06-17 19:01:24', NULL, '2026-06-17 18:58:51', 5),
(17, 10, 2, 'deployed', NULL, '2026-06-17 18:51:07', '2026-06-17 18:58:49', NULL, '2026-06-17 18:58:49', 5),
(18, 2, 14, 'assigned', NULL, '2026-06-24 12:02:13', '2026-06-24 12:02:13', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `review_status` varchar(255) DEFAULT NULL,
  `review_remarks` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `beneficiary_id`, `employer_id`, `application_id`, `date`, `status`, `remarks`, `time_in`, `time_out`, `notes`, `created_at`, `updated_at`, `review_status`, `review_remarks`, `reviewed_by`, `reviewed_at`) VALUES
(5, 1, 1, 3, '2026-06-10', 'present', NULL, '14:49:00', '14:49:00', '{\"in\":\"dtr_proofs\\/m2dTfkswTg8zf2naf9Hj7qEqrYBGxvavv0qheA8J.pdf\",\"out\":\"dtr_proofs\\/8YG6TBiFMTwS6IGIctD2W7CMGmZKMz4r8Th3dco0.pdf\"}', '2026-06-10 06:49:34', '2026-06-10 08:09:29', 'approved', NULL, 5, '2026-06-10 08:09:29'),
(6, 7, 1, 11, '2026-06-14', 'present', NULL, '07:13:00', '07:13:00', '{\"in\":\"dtr_proofs\\/LnPO2YVX5lI8PcciWFWmReGnXAi7mgjsOoem021g.jpg\",\"out\":\"dtr_proofs\\/bnxgCqTcgaxsvQR5LmBnGxIw3GEZJ3sgzKF3XUeb.jpg\"}', '2026-06-13 23:13:11', '2026-06-13 23:20:28', 'approved', NULL, 5, '2026-06-13 23:20:28'),
(7, 9, 1, 13, '2026-06-14', 'present', NULL, '14:20:00', '14:20:00', '{\"in\":\"dtr_proofs\\/joRrXdecPkQg7s0KFEk6iPabB9alBjdXT6U7bvyD.png\",\"out\":\"dtr_proofs\\/2dPGR5A2f5LOetH2FkGFB5d4gMJ24W82zbX3y0pI.png\"}', '2026-06-14 06:20:11', '2026-06-14 06:21:20', 'approved', NULL, 5, '2026-06-14 06:21:20'),
(8, 11, 1, 16, '2026-06-18', 'present', NULL, '03:01:00', '03:01:00', '{\"in\":\"dtr_proofs\\/EYB9JkdxKW0SpwYsJazsT3KomybQw55A73tttU47.jpg\",\"out\":\"dtr_proofs\\/Iq3b7zJdOgKXvmshgOpuE6wKeC3mXhbBVbVX0Am7.jpg\"}', '2026-06-17 19:01:24', '2026-06-17 19:03:50', 'approved', 'good', 5, '2026-06-17 19:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `beneficiaries`
--

CREATE TABLE `beneficiaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(10) UNSIGNED DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `civil_status` varchar(255) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `citizenship` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `facebook_account` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_contact` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_contact` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `family_income` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_address` text DEFAULT NULL,
  `education_level` varchar(255) DEFAULT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `course` varchar(255) DEFAULT NULL,
  `last_school_attended` varchar(255) DEFAULT NULL,
  `highest_attainment` varchar(255) DEFAULT NULL,
  `year_last_attended` varchar(255) DEFAULT NULL,
  `parent_guardian_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `displacement_reason` text DEFAULT NULL,
  `former_employer` varchar(255) DEFAULT NULL,
  `displacement_date` date DEFAULT NULL,
  `previous_spes` tinyint(1) DEFAULT NULL,
  `spes_count` int(10) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `onboarding_step` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `completion_percentage` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `completed_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`completed_steps`)),
  `birthdate` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `onboarding_completed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `temporary_password` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `approval_status` varchar(255) NOT NULL DEFAULT 'pending',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `draft_status` varchar(255) NOT NULL DEFAULT 'saved',
  `employment_status` enum('unassigned','assigned','employed','unemployed','completed') NOT NULL DEFAULT 'unassigned',
  `job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `resubmit_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `beneficiaries`
--

INSERT INTO `beneficiaries` (`id`, `user_id`, `student_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `birth_date`, `age`, `sex`, `civil_status`, `place_of_birth`, `citizenship`, `email`, `phone`, `contact_number`, `facebook_account`, `category`, `present_address`, `barangay`, `city`, `province`, `father_name`, `father_contact`, `father_occupation`, `mother_name`, `mother_contact`, `mother_occupation`, `family_income`, `school_name`, `school_address`, `education_level`, `school_year`, `course`, `last_school_attended`, `highest_attainment`, `year_last_attended`, `parent_guardian_name`, `relationship`, `displacement_reason`, `former_employer`, `displacement_date`, `previous_spes`, `spes_count`, `submitted_at`, `onboarding_step`, `completion_percentage`, `completed_steps`, `birthdate`, `gender`, `address`, `school_id`, `program`, `year_level`, `skills`, `parent_name`, `documents`, `created_at`, `updated_at`, `approved`, `onboarding_completed_at`, `completed_at`, `temporary_password`, `approved_by`, `approved_at`, `rejected_at`, `approval_status`, `status`, `draft_status`, `employment_status`, `job_id`, `employer_id`, `rejection_reason`, `resubmit_at`) VALUES
(1, 6, NULL, 'ADRIAN', 'T', 'YALUNG', NULL, '2003-06-01', 23, 'Male', 'Single', 'Magalang Pampanga', 'Filipino', 'adrianyalung76@gmail.com', '+636363636363', '+636363636363', NULL, 'student', 'Sitio 9 Santo Niño Viejo Road', 'Santo Niño', 'San Fernando', 'Pampanga', 'ALGIE C YALUNG', '+639123412412', 'TIREMAN', 'JENNIFFER T YALUNG', '+639123412423', 'HOUSER WIFE', 'Below ₱5,000', 'CITY COLLEGE OF SAN FERNANDO PAMPANGA', 'CIVIC CENTER', 'College', '2020-2021', 'BSBA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-09 19:12:11', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, 1, NULL, '3RD YEAR', NULL, NULL, '[{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/6\\/SZ7Nn3DOsXtEg6zAQ46pUucx80YxsXaBkHlp1DFM.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-10T03:02:49+08:00\"},{\"type\":\"school_enrollment\",\"path\":\"documents\\/beneficiaries\\/6\\/JExMs3YRS6TVABQB2JAkCv38cjR7sMeHlGL6aXFI.jpg\",\"name\":\"FORM 138.jpg\",\"uploaded_at\":\"2026-06-10T03:02:49+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/6\\/WL2Zx2U5Ff1ZPvIhYRqFP4NBa8aPD2MgI9hriPHo.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-10T03:02:49+08:00\"},{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/6\\/KK6p4gdQdwBv2Rd1fAEgszIUZ9OmaRBU1eNFizyr.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-10T03:12:11+08:00\"},{\"type\":\"school_enrollment\",\"path\":\"documents\\/beneficiaries\\/6\\/oc89hcryqcWjoW8Zi3au5S0f1Z9ifcx4ePx7Bs5O.jpg\",\"name\":\"FORM 138.jpg\",\"uploaded_at\":\"2026-06-10T03:12:11+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/6\\/cNgREzfafEMTu7e39r9iJC1BWxe0KRpAp77wHhz3.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-10T03:12:11+08:00\"}]', '2026-06-09 19:02:49', '2026-06-17 13:57:39', 1, '2026-06-09 19:12:11', NULL, NULL, NULL, '2026-06-17 13:57:39', NULL, 'approved', 'needs_correction', 'submitted', 'assigned', 2, 1, 'a', NULL),
(2, 7, NULL, 'KISSEL', 'C', 'TIOMICO', NULL, '2004-01-29', 22, 'Female', 'Single', 'BULAON HOSPITAL', 'Filipino', 'katyytiomico@gmail.com', '+636363639876', '+636363639876', 'Kissel Tiomico', 'student', 'Sto. Nino Viejo, Sitio 9 CCSFP', 'Sto. Nino Viejo', 'City of San Fernando Pampanga', 'Pampanga', 'Harold Tiomico', '+639910402938', 'Computer shop owner', 'Joanne Calosa', '+639910402938', 'House wife', 'Below ₱5,000', 'City College of San Fernando Pampanga', 'Civic Center Del Rosario, CSFP', 'College', '2026-2027', 'Bsit', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-11 12:45:19', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, 1, NULL, '3rd Year', NULL, NULL, '[{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/7\\/AmpHe49Xso2NBXkGjRvJPVRm2BkJvrb6oqoVCAOM.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-11T20:45:18+08:00\"},{\"type\":\"school_enrollment\",\"path\":\"documents\\/beneficiaries\\/7\\/f3D4VLpfUBCXV8REhI1hsoyitBNlJubdWvT1p7sn.jpg\",\"name\":\"FORM 138.jpg\",\"uploaded_at\":\"2026-06-11T20:45:18+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/7\\/r0FI20knugkNxELybYXWvnDgY4HmehLYsq6QPiS1.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-11T20:45:18+08:00\"}]', '2026-06-11 12:45:19', '2026-06-24 12:02:13', 1, '2026-06-11 12:45:19', NULL, NULL, NULL, '2026-06-17 17:40:58', NULL, 'approved', 'needs_correction', 'submitted', 'assigned', 14, 1, 'please resubmit your valid id', NULL),
(7, 14, NULL, 'Jerome', 'C', 'Lacson', NULL, '2003-01-01', 23, 'Male', 'Single', 'magalang hospital', 'Filipino', 'spsclprgrmfrmplmntfstdnts@gmail.com', '+636391231231', '+636391231231', NULL, 'osy', '123', 'Baliti', 'San Fernando', 'Pampanga', 'arturo lacson', '+636391231412', 'vendor', 'linda lacson', '+636391234234', 'house wife', 'Below ₱5,000', NULL, NULL, NULL, NULL, NULL, 'ccsfp', 'first year college', '2025', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-13 22:38:23', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/14\\/tBLFYnRx0rzm8HV2h6M4Wc4FKDrYjvXJobHU1Rss.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-14T06:28:52+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/14\\/xy7QXiGacdbWO8cHpFqDR3i5FMhqJeLAm2NlyNWg.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-14T06:28:52+08:00\"},{\"type\":\"birth_certificate\",\"path\":\"documents\\/beneficiaries\\/14\\/KWrWBpVqQPos2AE2T4bZQA9k11mTFuIwBnh9ksaF.jpg\",\"name\":\"BIRTH CERT.jpg\",\"uploaded_at\":\"2026-06-14T06:28:52+08:00\"},{\"type\":\"osy_certificate\",\"path\":\"documents\\/beneficiaries\\/14\\/AO0EvFUv94hWJX9cY0GX2S3pxilJtGHHuIxUqrzJ.jpg\",\"name\":\"OSY CERT.jpg\",\"uploaded_at\":\"2026-06-14T06:28:52+08:00\"}]', '2026-06-12 07:13:25', '2026-06-17 13:56:27', 1, '2026-06-13 22:38:23', '2026-06-14 00:11:54', NULL, NULL, '2026-06-17 13:56:27', NULL, 'approved', 'pending', 'submitted', 'completed', 4, 1, NULL, NULL),
(9, 16, NULL, 'Sarah', 'P', 'Tolentino', NULL, '2003-01-01', 23, 'Female', 'Single', 'bulaon hospital', 'Filipino', 'pinktulip012923@gmail.com', '+636391234123', '+636391234123', 'sarah', 'student', '132', 'Dela Paz Norte', 'San Fernando', 'Pampanga', 'arturo tolentino', '+636391234124', 'vendor', 'scarlet tolentino', '+636391243123', 'house wife', 'Below ₱5,000', 'CCSFP', 'Civic Center', 'College', '2024-2025', 'BSIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 05:21:23', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, 3, NULL, '2nd Year', NULL, NULL, '[{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/16\\/B3HInGwN2QeHCs4pOGNxyqw3SsQojHyH4ivAJok0.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-14T13:19:13+08:00\"},{\"type\":\"school_enrollment\",\"path\":\"documents\\/beneficiaries\\/16\\/6qEwoXjBCHcC288o5e6B0V02ZCDCaJ2ckzvBWxO8.jpg\",\"name\":\"FORM 138.jpg\",\"uploaded_at\":\"2026-06-14T13:19:13+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/16\\/lmdAZSOP3M3NugbqC20rzLVjotfITSohv5San8J6.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-14T13:19:13+08:00\"},{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/16\\/8sqoUGVdLLwauiPzAuLYDBr6Rhkb6VHLW0Ufcmd6.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-14T13:21:23+08:00\"}]', '2026-06-14 05:19:13', '2026-06-14 06:27:23', 1, '2026-06-14 05:21:23', '2026-06-14 06:27:23', NULL, NULL, '2026-06-14 05:21:51', NULL, 'approved', 'pending', 'submitted', 'completed', 5, 1, NULL, NULL),
(10, 17, NULL, 'James', 'F', 'Marcelo', NULL, '2003-01-01', 23, 'Male', 'Single', 'jbl hospital', 'Filipino', 'dianyalung01@gmail.com', '+639123124312', '+639123124312', NULL, 'dependent', '123', 'Del Carmen', 'San Fernando', 'Pampanga', 'Alex marcelo', '+639124123412', 'vendor', 'Linda Marcelo', '+639123141243', 'house wife', 'Below ₱5,000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alex Marcelo', 'father', 'company bankrupt', 'abc', '2003-01-01', 0, NULL, '2026-06-14 08:07:29', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[{\"type\":\"birth_certificate\",\"path\":\"documents\\/beneficiaries\\/17\\/HuYllu3c1v9D1LCIeezvhgl10StMw54DehYKKgFz.jpg\",\"name\":\"BIRTH CERT.jpg\",\"uploaded_at\":\"2026-06-14T16:07:29+08:00\"},{\"type\":\"income_proof\",\"path\":\"documents\\/beneficiaries\\/17\\/nEmuJFMDVF8uKoZ6iWBZgf1SgZsQdDy8iNgg3rLu.jpg\",\"name\":\"employer rating.jpg\",\"uploaded_at\":\"2026-06-14T16:07:29+08:00\"},{\"type\":\"displacement_proof\",\"path\":\"documents\\/beneficiaries\\/17\\/PTondhkDK4igdVLc4uPNb3gsVq5QV8BfJ58wpLEL.jpg\",\"name\":\"Displacement Certification.jpg\",\"uploaded_at\":\"2026-06-14T16:07:29+08:00\"},{\"type\":\"parent_valid_id\",\"path\":\"documents\\/beneficiaries\\/17\\/7gpnAdwlOL3VXM1uKtWPaNPdCrKLfoyKrJmaLaia.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-14T16:07:29+08:00\"}]', '2026-06-14 08:01:43', '2026-06-17 18:51:07', 1, '2026-06-14 08:07:29', NULL, NULL, NULL, '2026-06-17 17:41:10', NULL, 'approved', 'pending', 'submitted', 'assigned', 2, 1, NULL, NULL),
(11, 20, NULL, 'gohan', 'm', 'david', NULL, '2003-01-01', 23, 'Male', 'Single', 'bulaon hospital', 'Filipino', 'githubpilot6@gmail.com', '+639127331241', '+639127331241', NULL, 'student', '123', 'Del Pilar', 'San Fernando', 'Pampanga', 'killua', '+639123123123', 'tireman', 'lucian', '+639123412124', 'house wife', 'Below ₱5,000', 'ccsfp', 'civic center', 'College', '2025-2026', 'bsba', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-17 18:20:25', 5, 100, '[1,2,3,4,5]', NULL, NULL, NULL, 3, NULL, '2nd Year', NULL, NULL, '[{\"type\":\"valid_id\",\"path\":\"documents\\/beneficiaries\\/20\\/ywAm2VeSLpfKv9Eg5NiCmVzgfWrRCU3882keogDz.jpg\",\"name\":\"VALID ID.jpg\",\"uploaded_at\":\"2026-06-18T02:20:25+08:00\"},{\"type\":\"school_enrollment\",\"path\":\"documents\\/beneficiaries\\/20\\/gZ3XU4CVJFq83MhrHK0hcVMHRmh0WealJtwtZ6Hx.jpg\",\"name\":\"BARANGGAY CERT.jpg\",\"uploaded_at\":\"2026-06-18T02:20:25+08:00\"},{\"type\":\"barangay_certificate\",\"path\":\"documents\\/beneficiaries\\/20\\/IZMLpvWzneplPWz1x0AAZ1AxInIe1c7ut9Ip9s6q.png\",\"name\":\"Business Permit.png\",\"uploaded_at\":\"2026-06-18T02:20:25+08:00\"}]', '2026-06-17 18:20:25', '2026-06-17 18:51:03', 1, '2026-06-17 18:20:25', NULL, NULL, NULL, '2026-06-17 18:20:57', NULL, 'approved', 'pending', 'submitted', 'assigned', 2, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `beneficiary_ratings`
--

CREATE TABLE `beneficiary_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `beneficiary_skill`
--

CREATE TABLE `beneficiary_skill` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `skill_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `beneficiary_skill`
--

INSERT INTO `beneficiary_skill` (`id`, `beneficiary_id`, `skill_id`, `created_at`, `updated_at`) VALUES
(2, 1, 2, '2026-06-09 19:12:11', '2026-06-09 19:12:11'),
(3, 1, 3, '2026-06-09 19:12:11', '2026-06-09 19:12:11'),
(4, 1, 5, '2026-06-09 19:12:11', '2026-06-09 19:12:11'),
(5, 1, 4, '2026-06-09 19:12:11', '2026-06-09 19:12:11'),
(6, 2, 3, '2026-06-11 12:45:19', '2026-06-11 12:45:19'),
(7, 2, 7, '2026-06-11 12:45:19', '2026-06-11 12:45:19'),
(18, 7, 4, '2026-06-13 22:28:52', '2026-06-13 22:28:52'),
(19, 7, 3, '2026-06-13 22:28:52', '2026-06-13 22:28:52'),
(20, 7, 7, '2026-06-13 22:28:52', '2026-06-13 22:28:52'),
(21, 7, 5, '2026-06-13 22:28:52', '2026-06-13 22:28:52'),
(28, 10, 2, '2026-06-14 08:07:30', '2026-06-14 08:07:30'),
(29, 10, 5, '2026-06-14 08:07:30', '2026-06-14 08:07:30'),
(30, 10, 4, '2026-06-14 08:07:30', '2026-06-14 08:07:30'),
(31, 10, 3, '2026-06-14 08:07:30', '2026-06-14 08:07:30'),
(32, 11, 2, '2026-06-17 18:20:26', '2026-06-17 18:20:26'),
(33, 11, 3, '2026-06-17 18:20:26', '2026-06-17 18:20:26'),
(34, 11, 5, '2026-06-17 18:20:26', '2026-06-17 18:20:26');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `contract_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `result` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `schedule_group_id` varchar(255) DEFAULT NULL,
  `batch_title` varchar(255) DEFAULT NULL,
  `scheduled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `original_schedule_at` datetime DEFAULT NULL,
  `rescheduled_at` datetime DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `notify_beneficiaries` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `application_id`, `contract_date`, `location`, `notes`, `status`, `result`, `created_at`, `updated_at`, `schedule_group_id`, `batch_title`, `scheduled_by`, `end_at`, `original_schedule_at`, `rescheduled_at`, `reschedule_reason`, `instructions`, `notify_beneficiaries`) VALUES
(1, 3, '2026-06-11 08:00:00', 'City Hall', NULL, 'completed', 'signed', '2026-06-09 20:36:27', '2026-06-09 20:41:07', '973a492b-af29-47e0-8c3c-1ff161a8f71f', NULL, 1, '2026-06-11 17:00:00', NULL, NULL, NULL, 'Please arrive at least 30 minutes before your scheduled examination.\n\nBring the following:\n\nValid ID\nBallpen\nRequired SPES documents\nThank you and good luck!', 1),
(2, 11, '2026-06-15 08:00:00', 'City Halll', NULL, 'completed', 'signed', '2026-06-13 23:04:33', '2026-06-13 23:06:52', 'ad26d035-da97-4028-94bc-485b26b7c024', NULL, 1, '2026-06-15 17:00:00', NULL, NULL, NULL, 'Bring Valid Id and Required Documents', 1),
(3, 13, '2026-06-15 08:55:00', 'city hall', NULL, 'completed', 'signed', '2026-06-14 05:56:10', '2026-06-14 06:02:43', 'd2bef72f-18df-4a6b-b66e-5260950dc3d0', NULL, 1, '2026-06-15 17:55:00', NULL, NULL, NULL, NULL, 1),
(4, 17, '2026-06-19 08:56:00', 'city hall', NULL, 'completed', 'signed', '2026-06-17 18:56:50', '2026-06-17 18:57:30', '25125de4-4e26-4cad-8ba3-e49406a924e1', NULL, 1, '2026-06-19 14:56:00', NULL, NULL, NULL, NULL, 1),
(5, 16, '2026-06-19 08:56:00', 'city hall', NULL, 'completed', 'signed', '2026-06-17 18:56:55', '2026-06-17 18:57:28', '25125de4-4e26-4cad-8ba3-e49406a924e1', NULL, 1, '2026-06-19 14:56:00', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `onboarding_completed_at` timestamp NULL DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approval_status` varchar(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`id`, `user_id`, `company_name`, `contact_person`, `email`, `phone`, `address`, `documents`, `details`, `onboarding_completed_at`, `approved`, `approval_status`, `rejection_reason`, `created_at`, `updated_at`, `approved_at`, `status`, `approved_by`, `rejected_at`) VALUES
(1, 5, 'KISSEL TIOMICO', 'GOTEN T YALUNG T', 'ramcbagoong@gmail.com', '9876578909876', 'tagumapay 3rd street, sindalan, calulut, san fernando, pampamnga, 2000', '[{\"type\":\"business_permit\",\"path\":\"documents\\/employers\\/5\\/I5HJzhbJ87QJrL7sx87kDT19w5f8teQnICQJ5NoO.png\",\"name\":\"Business Permit.png\",\"uploaded_at\":\"2026-06-09T17:03:49+08:00\"},{\"type\":\"mayors_permit\",\"path\":\"documents\\/employers\\/5\\/cJebWm0h2vAOhezA2UxtVZyrDny2aRyxsM0g0TMZ.jpg\",\"name\":\"pledge of comittment.jpg\",\"uploaded_at\":\"2026-06-09T17:03:49+08:00\"},{\"type\":\"registration_certificate\",\"path\":\"documents\\/employers\\/5\\/Qwk1ZkjeE3rVbltsqrO8XGvXUBSmtifcOjZcJQMV.png\",\"name\":\"Notice of Termination.png\",\"uploaded_at\":\"2026-06-09T17:03:49+08:00\"},{\"type\":\"bir_certificate\",\"path\":\"documents\\/employers\\/5\\/Bcc0FMJLT7PCvp985dsd9q5p32DouhBrXmPWNJ2h.png\",\"name\":\"cert of completion.png\",\"uploaded_at\":\"2026-06-09T17:03:49+08:00\"},{\"type\":\"letter_of_intent\",\"path\":\"documents\\/employers\\/5\\/iSAGqIMjtMqnqMzLFTZAp4kJsBP37nxfrGEgOMHh.png\",\"name\":\"LOW INCOME CERT.png\",\"uploaded_at\":\"2026-06-09T17:03:49+08:00\"}]', '{\"business_trade_name\":\"RAMC FOOD PRODUCT\",\"nature_of_business\":\"BAGOONG\",\"industry_type\":\"BUSINESS\",\"sector\":\"PRIVATE\",\"company_website\":\"https:\\/\\/https\\/\\/:ramc.online\",\"facebook_page\":\"https:\\/\\/www.facebook.com\",\"number_of_employees\":\"100\",\"available_spes_slots\":\"50\",\"house_number\":\"tagumapay 3rd street\",\"street\":\"sindalan\",\"barangay\":\"calulut\",\"city\":\"san fernando\",\"province\":\"pampamnga\",\"zip_code\":\"2000\",\"authorized_representative\":{\"first_name\":\"ALFRED\",\"middle_name\":\"C\",\"last_name\":\"DAVID\",\"suffix\":\"T\",\"position\":\"CEO\",\"mobile\":\"9876545678\",\"email\":\"ramcbagoong@gmai.com\"},\"contact_person\":{\"first_name\":\"GOTEN\",\"middle_name\":\"T\",\"last_name\":\"YALUNG\",\"suffix\":\"T\",\"position\":\"VICE PRESIDENT\",\"mobile\":\"9876546789\",\"email\":\"ramcfoodproducts@gmail.com\"},\"finance_officer\":{\"first_name\":\"TRUNKS\",\"middle_name\":\"I\",\"last_name\":\"TULABUT\",\"suffix\":\"I\",\"position\":\"TRESURRER\",\"mobile\":\"9876545678\",\"email\":\"foodindustr@gmail.com\"},\"company_contact\":{\"telephone_number\":\"9876578909876\",\"mobile_number\":\"9876567890\",\"official_company_email\":\"ramcbagoong@gmail.com\",\"alternative_email\":\"ramcfoodproducts@gmail.com\"},\"spes_participation\":{\"previous_participation\":\"Yes\",\"years_participated\":\"2\",\"preferred_beneficiaries\":\"50\",\"preferred_department\":\"COOP\",\"employment_period\":\"JUNE 2026 -AUGUST 2027\",\"work_schedules\":\"MON - FRI\",\"work_assignments\":\"TAGA HALO NANG MANGGA\"},\"employment_opportunities\":{\"position_title\":\"ASDA\",\"number_of_vacancies\":\"2\",\"minimum_qualification\":\"5\",\"assigned_department\":\"SDD\",\"work_schedule\":\"8AM TO 5PM\",\"expected_duration\":\"ASD\"}}', '2026-06-09 09:03:49', 1, 'approved', NULL, '2026-06-09 08:02:52', '2026-06-09 13:32:20', '2026-06-09 13:32:20', 'pending', 1, NULL),
(2, 9, 'abc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'pending', NULL, '2026-06-11 14:44:52', '2026-06-11 14:44:52', NULL, NULL, NULL, NULL),
(3, 21, 'BrightCompany', 'Maria Santos', 'brightcompany@example.test', '0917-555-0101', 'Rizal Street, Santa Cruz, Laguna', NULL, NULL, '2026-06-19 08:04:09', 1, 'approved', NULL, '2026-06-19 08:04:09', '2026-06-19 08:04:09', '2026-06-19 08:04:09', 'active', NULL, NULL),
(4, 22, 'LagunaTech Solutions', 'Jose Ramirez', 'lagunatech@example.test', '0917-555-0102', 'National Highway, Los Banos, Laguna', NULL, NULL, '2026-06-19 08:04:10', 1, 'approved', NULL, '2026-06-19 08:04:10', '2026-06-19 08:04:10', '2026-06-19 08:04:10', 'active', NULL, NULL),
(5, 23, 'GreenFields Market', 'Ana Dela Cruz', 'greenfields@example.test', '0917-555-0103', 'Public Market Road, Victoria, Laguna', NULL, NULL, '2026-06-19 08:04:10', 1, 'approved', NULL, '2026-06-19 08:04:10', '2026-06-19 08:04:10', '2026-06-19 08:04:10', 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employer_compliances`
--

CREATE TABLE `employer_compliances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_feedback`
--

CREATE TABLE `employer_feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_ratings`
--

CREATE TABLE `employer_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `punctuality` tinyint(4) DEFAULT NULL,
  `work_attitude` tinyint(4) DEFAULT NULL,
  `output_quality` tinyint(4) DEFAULT NULL,
  `communication` tinyint(4) DEFAULT NULL,
  `overall` tinyint(4) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employer_ratings`
--

INSERT INTO `employer_ratings` (`id`, `employer_id`, `beneficiary_id`, `application_id`, `punctuality`, `work_attitude`, `output_quality`, `communication`, `overall`, `comment`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 3, 5, 5, 5, 5, 5, 'well done!', '2026-06-10 07:52:50', '2026-06-10 07:52:50'),
(4, 1, 7, 11, 5, 5, 5, 5, 5, 'well done!', '2026-06-13 23:48:20', '2026-06-13 23:48:20'),
(5, 1, 9, 13, 5, 5, 5, 5, 5, 'well done!', '2026-06-14 06:24:19', '2026-06-14 06:24:19');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `exam_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `result` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `schedule_group_id` varchar(255) DEFAULT NULL,
  `batch_title` varchar(255) DEFAULT NULL,
  `scheduled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `original_schedule_at` datetime DEFAULT NULL,
  `rescheduled_at` datetime DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `notify_beneficiaries` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `application_id`, `exam_date`, `location`, `notes`, `status`, `result`, `created_at`, `updated_at`, `schedule_group_id`, `batch_title`, `scheduled_by`, `end_at`, `original_schedule_at`, `rescheduled_at`, `reschedule_reason`, `instructions`, `notify_beneficiaries`) VALUES
(1, 1, '2026-06-11 08:30:00', 'City hall', NULL, 'completed', 'passed', '2026-06-09 19:25:48', '2026-06-09 19:29:02', '198f8b68-261a-4bcd-8b9e-0910e7e9c5e2', 'Batch 6/10/2026', 1, '2026-06-11 16:00:00', NULL, NULL, NULL, 'Arrive 15–30 minutes early.\nEat a light meal and stay hydrated.\nRead all instructions carefully before starting.\nBudget your time across sections.\nAnswer easier questions first if allowed.\nDouble-check calculations and responses if time remains.', 1),
(2, 10, '2026-06-15 08:00:00', 'CPESO OFFICE', NULL, 'completed', 'passed', '2026-06-13 22:44:13', '2026-06-13 22:47:01', 'ba31deca-d46d-4034-815f-efff7b69c18e', 'BATCH A', 1, '2026-06-15 17:00:00', NULL, NULL, NULL, NULL, 1),
(3, 12, '2026-06-16 08:22:00', 'CPESO OFFICE', NULL, 'completed', 'passed', '2026-06-14 05:23:43', '2026-06-14 05:24:10', 'bd016f4d-0009-4f69-a6ab-def0ce6988a1', 'Batch 6/14/2026', 1, '2026-06-16 17:22:00', NULL, NULL, NULL, NULL, 1),
(4, 15, '2026-06-19 08:00:00', 'cpeso office', NULL, 'completed', 'passed', '2026-06-17 18:22:13', '2026-06-17 18:22:25', '689dffd6-7707-4134-bbcb-742facf522dd', 'Batch 6/18/2026', 1, '2026-06-19 17:00:00', NULL, NULL, NULL, NULL, 1),
(5, 14, '2026-06-19 08:00:00', 'cpeso office', NULL, 'completed', 'passed', '2026-06-17 18:22:18', '2026-06-17 18:22:31', '689dffd6-7707-4134-bbcb-742facf522dd', 'Batch 6/18/2026', 1, '2026-06-19 17:00:00', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_listing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `beneficiary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `google_meet_link` varchar(255) DEFAULT NULL,
  `meet_link` varchar(255) DEFAULT NULL,
  `result` enum('pending','passed','failed','needs_review') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `calendar_event_id` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `schedule_group_id` varchar(255) DEFAULT NULL,
  `batch_title` varchar(255) DEFAULT NULL,
  `scheduled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `interviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `original_schedule_at` datetime DEFAULT NULL,
  `rescheduled_at` datetime DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `notify_beneficiaries` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `job_listing_id`, `employer_id`, `beneficiary_id`, `application_id`, `scheduled_at`, `google_meet_link`, `meet_link`, `result`, `remarks`, `evaluated_at`, `calendar_event_id`, `status`, `created_at`, `updated_at`, `schedule_group_id`, `batch_title`, `scheduled_by`, `interviewer_id`, `end_at`, `original_schedule_at`, `rescheduled_at`, `reschedule_reason`, `instructions`, `notify_beneficiaries`) VALUES
(1, NULL, NULL, 1, 1, '2026-06-10 03:50:00', NULL, 'https://meet.google.com/eyt-fgwv-zxd', 'passed', 'Positive Remarks\n\nThe applicant communicated clearly and confidently throughout the interview. They demonstrated a good understanding of the position and possessed relevant skills and qualifications. The applicant also exhibited professionalism, enthusiasm, and a positive attitude, making a favorable impression during the interview process.\n\nNeeds Improvement Remarks\n\nThe applicant experienced some difficulty expressing ideas clearly and concisely during the interview. Responses would benefit from additional detail and more relevant examples to better demonstrate knowledge, skills, and experience related to the position. Further development in communication and interview presentation skills is recommended.', '2026-06-09 19:47:59', NULL, 'completed', '2026-06-09 19:36:52', '2026-06-09 19:47:59', '91d53027-c0ad-43ff-b9b9-0c79851e45ef', NULL, 1, 3, '2026-06-10 04:51:00', NULL, NULL, NULL, 'Goodluck!', 1),
(3, NULL, NULL, 7, 10, '2026-06-14 08:00:00', NULL, 'https://meet.google.com/nno-sbwz-iew', 'passed', 'this applicant is good', '2026-06-13 23:00:03', NULL, 'completed', '2026-06-13 22:51:32', '2026-06-13 23:00:03', '491ed42a-1eb0-4b69-90b3-5bf073fbc166', NULL, 1, 3, '2026-06-14 10:00:00', NULL, NULL, NULL, NULL, 1),
(4, NULL, NULL, 9, 12, '2026-06-15 08:24:00', NULL, 'https://meet.google.com/bqu-unkw-zny', 'passed', 'this applicant is good', '2026-06-14 05:50:40', NULL, 'completed', '2026-06-14 05:25:29', '2026-06-14 05:50:40', 'f8705c10-cf9f-4c7e-bc27-121e980a938f', NULL, 1, 3, '2026-06-15 10:24:00', NULL, NULL, NULL, NULL, 1),
(5, NULL, NULL, 11, 15, '2026-06-18 02:50:00', NULL, 'https://meet.google.com/khj-zjjj-ptj', 'passed', 'test', '2026-06-17 18:49:48', NULL, 'completed', '2026-06-17 18:44:51', '2026-06-17 18:49:48', '8d3bbfd8-0e24-4171-abc6-aa12ac90be3f', NULL, 1, 3, '2026-06-18 03:20:00', NULL, NULL, NULL, NULL, 1),
(6, NULL, NULL, 10, 14, '2026-06-18 03:20:00', NULL, 'https://meet.google.com/khj-zjjj-ptj', 'passed', 'test', '2026-06-17 18:50:00', NULL, 'completed', '2026-06-17 18:44:56', '2026-06-17 18:50:00', '8d3bbfd8-0e24-4171-abc6-aa12ac90be3f', NULL, 1, 3, '2026-06-18 03:50:00', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_listings`
--

CREATE TABLE `job_listings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `slots` int(11) NOT NULL DEFAULT 1,
  `assigned_beneficiary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employer_choice` enum('approved','rejected','pending') NOT NULL DEFAULT 'pending',
  `closing_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_listings`
--

INSERT INTO `job_listings` (`id`, `employer_id`, `title`, `description`, `location`, `salary`, `type`, `slots`, `assigned_beneficiary_id`, `employer_choice`, `closing_date`, `created_at`, `updated_at`) VALUES
(2, 1, 'Office Assistant', 'The Office Assistant is responsible for providing administrative and clerical support to ensure the efficient operation of the office. Duties include organizing files and documents, encoding and updating records, answering phone calls and emails, assisting clients and visitors, preparing reports, scheduling appointments, and performing other office-related tasks assigned by the supervisor. The position requires attention to detail, basic computer skills, communication skills, and the ability to work effectively in a team environment.', 'Manila', NULL, 'Full-time', 10, NULL, 'pending', '2026-06-30', '2026-06-09 18:28:26', '2026-06-09 18:28:26'),
(4, 1, 'Factory Worker', 'Assembly and packaging tasks.', 'Davao', NULL, 'Full-time', 1, NULL, 'pending', '2026-06-30', '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(5, 1, 'Warehouse Helper', 'Inventory handling and loading.', 'Laguna', NULL, 'Full-time', 1, NULL, 'pending', '2026-07-01', '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(6, 3, 'Office Assistant', 'Assist with filing, encoding, and front desk support.', 'Santa Cruz, Laguna', NULL, 'Part-time', 4, NULL, 'approved', '2026-07-19', '2026-06-05 08:04:08', '2026-06-19 08:04:09'),
(7, 3, 'Inventory Clerk', 'Help monitor supplies and update inventory records.', 'Pila, Laguna', NULL, 'Full-time', 3, NULL, 'pending', '2026-07-24', '2026-06-09 08:04:08', '2026-06-19 08:04:09'),
(8, 3, 'Customer Service Aide', 'Support customer inquiries and basic office transactions.', 'Calamba, Laguna', NULL, 'Part-time', 2, NULL, 'rejected', '2026-08-03', '2026-06-13 08:04:08', '2026-06-19 08:04:09'),
(9, 4, 'Data Encoder', 'Encode client records and organize digital files.', 'Los Banos, Laguna', NULL, 'Part-time', 5, NULL, 'approved', '2026-07-14', '2026-06-07 08:04:08', '2026-06-19 08:04:10'),
(10, 4, 'IT Support Trainee', 'Assist with basic troubleshooting and workstation setup.', 'Calamba, Laguna', NULL, 'Full-time', 2, NULL, 'pending', '2026-07-29', '2026-06-11 08:04:08', '2026-06-19 08:04:10'),
(11, 5, 'Store Assistant', 'Assist with shelf arrangement and customer support.', 'Victoria, Laguna', NULL, 'Part-time', 6, NULL, 'approved', '2026-07-09', '2026-06-10 08:04:08', '2026-06-19 08:04:10'),
(12, 5, 'Packaging Helper', 'Help prepare, label, and organize packed goods.', 'Pagsanjan, Laguna', NULL, 'Full-time', 4, NULL, 'rejected', '2026-07-17', '2026-06-14 08:04:08', '2026-06-19 08:04:10'),
(14, 1, 'tireman', 'fixing tires', 'sanfernando', NULL, 'Full-time', 5, NULL, 'pending', '2026-06-30', '2026-06-24 11:08:33', '2026-06-24 11:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `job_listing_skills`
--

CREATE TABLE `job_listing_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_listing_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_listing_skills`
--

INSERT INTO `job_listing_skills` (`id`, `job_listing_id`, `skill_id`, `created_at`, `updated_at`) VALUES
(2, 2, 2, '2026-06-09 18:55:53', '2026-06-09 18:55:53'),
(3, 2, 3, '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(4, 2, 4, '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(5, 2, 5, '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(6, 4, 4, '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(7, 4, 3, '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(8, 4, 5, '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(9, 4, 7, '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(10, 5, 4, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(11, 5, 5, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(12, 5, 7, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(13, 5, 2, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(14, 5, 3, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(15, 5, 8, '2026-06-14 04:47:25', '2026-06-14 04:47:25'),
(19, 14, 2, '2026-06-24 11:08:33', '2026-06-24 11:08:33'),
(20, 14, 7, '2026-06-24 11:08:33', '2026-06-24 11:08:33'),
(21, 14, 8, '2026-06-24 11:08:33', '2026-06-24 11:08:33'),
(22, 14, 3, '2026-06-24 11:08:33', '2026-06-24 11:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_07_08_174856_add_two_factor_columns_to_users_table', 1),
(5, '2025_07_08_174942_create_personal_access_tokens_table', 1),
(6, '2025_07_08_181437_create_permission_tables', 1),
(7, '2025_07_08_182118_create_reports_table', 1),
(8, '2025_07_17_132622_add_otp_fields_to_users_table', 1),
(9, '2025_07_18_170737_create_password_otp_resets_table', 1),
(10, '2025_12_10_095049_create_beneficiaries_table', 1),
(11, '2025_12_10_095050_create_employers_table', 1),
(12, '2025_12_10_095051_create_job_listings_table', 1),
(13, '2025_12_10_095052_create_applications_table', 1),
(14, '2025_12_10_095054_create_attendances_table', 1),
(15, '2025_12_10_095055_create_employer_ratings_table', 1),
(16, '2025_12_10_095056_create_interviews_table', 1),
(17, '2025_12_10_100524_add_assignment_fields_to_job_listings', 1),
(18, '2025_12_10_114809_create_notifications_table', 1),
(19, '2025_12_10_131617_create_activity_log_table', 1),
(20, '2025_12_10_131618_add_event_column_to_activity_log_table', 1),
(21, '2025_12_10_131619_add_batch_uuid_column_to_activity_log_table', 1),
(22, '2025_12_15_000001_create_reports_table', 1),
(23, '2025_12_15_000002_create_work_outputs_table', 1),
(24, '2025_12_24_000000_create_password_resets_table', 1),
(25, '2025_12_30_000001_create_batches_table', 1),
(26, '2025_12_30_000002_add_batch_id_to_applications_table', 1),
(27, '2025_12_30_000003_add_salary_to_job_listings', 1),
(28, '2025_12_30_000004_add_employer_fields_to_reports', 1),
(29, '2025_12_30_000005_recreate_reports_table', 1),
(30, '2025_12_30_000006_add_application_id_to_interviews', 1),
(31, '2025_12_30_000007_add_status_remarks_to_attendances', 1),
(32, '2025_12_30_000008_fix_employer_ratings_foreign', 1),
(33, '2025_12_30_103752_create_analytics_table', 1),
(34, '2025_12_30_103847_add_meet_link_to_interviews', 1),
(35, '2026_01_07_171338_add_user_id_to_beneficiaries_table', 1),
(36, '2026_01_07_180731_create_schools_table', 1),
(37, '2026_01_18_154439_add_approved_to_beneficiaries_table', 1),
(38, '2026_01_21_190121_add_onboarding_completed_to_users_table', 1),
(39, '2026_01_22_051002_add_approval_columns_to_beneficiaries_table', 1),
(40, '2026_01_22_071442_add_onboarding_completed_at_to_beneficiaries_table', 1),
(41, '2026_01_22_072314_add_approved_to_employers_table', 1),
(42, '2026_01_23_000000_add_onboarding_fields_to_employers_table', 1),
(43, '2026_01_23_000001_add_approval_status_to_beneficiaries_table', 1),
(44, '2026_01_23_083702_add_status_to_beneficiaries_table', 1),
(45, '2026_02_01_000000_add_approval_fields_to_beneficiaries_table', 1),
(46, '2026_02_01_000000_add_osy_dependent_fields_to_beneficiaries', 1),
(47, '2026_02_13_054354_add_temporary_password_to_users_table', 1),
(48, '2026_02_22_171050_create_announcements_table', 1),
(49, '2026_02_25_153114_add_profile_fields_to_beneficiaries_table', 1),
(50, '2026_02_27_013427_add_job_and_employer_to_beneficiaries_table', 1),
(51, '2026_03_07_074812_add_target_role_to_announcements_table', 1),
(52, '2026_03_08_054222_create_employer_compliances_table', 1),
(53, '2026_03_08_054337_create_beneficiary_ratings_table', 1),
(54, '2026_03_11_125702_add_completion_status_to_beneficiaries_table', 1),
(55, '2026_03_11_125740_add_result_to_interviews_table', 1),
(56, '2026_03_11_125805_create_work_schedules_table', 1),
(57, '2026_03_11_125828_create_employer_feedback_table', 1),
(58, '2026_04_07_002125_add_employer_id_to_beneficiaries_table', 1),
(59, '2026_04_08_091014_create_work_schedules_table', 1),
(60, '2026_04_13_083334_update_applications_fk', 1),
(61, '2026_04_19_194639_create_exams_table', 1),
(62, '2026_05_02_000000_fix_beneficiary_job_fk', 1),
(63, '2026_05_04_093111_add_job_and_employer_to_beneficiaries_table', 1),
(64, '2026_05_07_092907_create_contracts_table', 1),
(65, '2026_05_07_120000_make_job_listing_nullable_in_applications', 1),
(66, '2026_05_09_111628_add_status_and_result_to_exams_table', 1),
(67, '2026_05_10_000000_fix_exam_and_interview_results_columns', 1),
(68, '2026_05_10_085823_add_certificate_path_to_applications_table', 1),
(69, '2026_05_11_211914_create_announcement_reads_table', 1),
(70, '2026_05_17_090000_add_application_id_to_attendances', 1),
(71, '2026_05_21_012052_update_employment_status_enum_in_beneficiaries_table', 1),
(72, '2026_05_28_000000_add_employer_details_to_employers_table', 1),
(73, '2026_05_29_000001_add_employer_details_column_to_employers_table', 1),
(74, '2026_05_29_181920_expand_beneficiary_spes_onboarding_fields', 1),
(75, '2026_05_29_183343_update_employment_status_enum_beneficiaries_table', 1),
(76, '2026_05_29_190532_add_missing_onboarding_columns_to_beneficiaries', 1),
(77, '2026_06_02_cleanup_job_listing_skills', 1),
(78, '2026_06_03_create_skills_table', 1),
(79, '2026_06_04_create_job_listing_skills_table', 1),
(80, '2026_06_05_095813_create_skill_categories_table', 1),
(81, '2026_06_05_095909_add_skill_category_id_to_skills_table', 1),
(82, '2026_06_05_100901_create_beneficiary_skill_table', 1),
(83, '2026_06_05_161105_add_preferred_job_id_to_beneficiaries_table', 1),
(84, '2026_06_05_171056_update_skills_table_add_category_field', 1),
(85, '2026_06_05_173546_add_preferred_job_id_to_beneficiaries', 1),
(86, '2026_06_05_192025_add_employer_details_to_employers_table', 1),
(87, '2026_06_08_000001_add_schedule_module_fields_to_exams_interviews_contracts', 1),
(88, '2026_06_09_000001_add_daily_report_fields_to_work_outputs', 1),
(89, '2026_06_09_082957_add_completed_at_to_beneficiaries_table', 1),
(90, '2026_06_09_120000_add_evaluation_fields_to_interviews_table', 1),
(91, '2026_06_09_130000_add_employer_workflow_fields', 1),
(92, '2026_06_10_000001_add_name_to_skill_categories_table', 2),
(93, '2026_06_10_000002_add_missing_columns_to_beneficiary_skill_table', 3),
(94, '2026_06_18_000001_add_resubmission_fields_to_work_outputs', 4);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 4),
(4, 'App\\Models\\User', 5),
(4, 'App\\Models\\User', 9),
(4, 'App\\Models\\User', 21),
(4, 'App\\Models\\User', 22),
(4, 'App\\Models\\User', 23),
(5, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 7),
(5, 'App\\Models\\User', 14),
(5, 'App\\Models\\User', 16),
(5, 'App\\Models\\User', 17),
(5, 'App\\Models\\User', 18),
(5, 'App\\Models\\User', 20);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_otp_resets`
--

CREATE TABLE `password_otp_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `report_type` varchar(255) DEFAULT NULL,
  `report_details` text DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `patient_id`, `doctor_id`, `employer_id`, `title`, `body`, `file_path`, `report_type`, `report_details`, `report_date`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 1, 'Daily Accomplishment Report – June 14, 2026', 'Sa araw na ito, matagumpay na naisagawa ang mga sumusunod na gawain:\r\n\r\n• Nakatanggap at nakapagproseso ng aplikasyon ng mga job seekers para sa employment assistance program.\r\n• Nagbigay ng orientation at career guidance sa mga aplikante hinggil sa mga available na trabaho at requirements.\r\n• Nagsagawa ng encoding at pag-update ng records sa CPESO database.\r\n• Tumulong sa pag-verify ng mga dokumento ng mga aplikante para sa iba\'t ibang programa ng tanggapan.\r\n• Nakipag-ugnayan sa mga employer at partner agencies ukol sa job vacancies at recruitment activities.\r\n• Nakatugon sa mga katanungan ng mga kliyente tungkol sa employment opportunities, livelihood programs, at iba pang serbisyo ng CPESO.\r\n• Nagsumite ng kinakailangang reports at dokumentasyon para sa monitoring ng mga aktibidad.\r\n\r\nResulta:\r\nNakapagserbisyo sa mga aplikante nang maayos at napapanahon, na-update ang records ng opisina, at napalakas ang koordinasyon sa mga employer para sa mga susunod na employment activities.', 'reports/z2cc3gjjKc6evxelbsbUzdpDhVWjKrnitCMfXvvG.jpg', NULL, NULL, NULL, '2026-06-14 02:02:21', '2026-06-14 02:02:21');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', '2026-06-09 07:48:53', '2026-06-09 07:48:53'),
(2, 'PESO', 'web', '2026-06-09 07:48:53', '2026-06-09 07:48:53'),
(3, 'PESO Admin', 'web', '2026-06-09 07:48:53', '2026-06-09 07:48:53'),
(4, 'Employer', 'web', '2026-06-09 07:48:53', '2026-06-09 07:48:53'),
(5, 'Beneficiary', 'web', '2026-06-09 07:48:53', '2026-06-09 07:48:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `address`, `contact_number`, `created_at`, `updated_at`) VALUES
(1, 'CITY COLLEGE OF SAN FERNANDO PAMPANGA', NULL, NULL, '2026-06-09 19:02:49', '2026-06-09 19:02:49'),
(2, 'City College', NULL, NULL, '2026-06-11 14:31:43', '2026-06-11 14:31:43'),
(3, 'CCSFP', NULL, NULL, '2026-06-14 05:19:13', '2026-06-14 05:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `role` varchar(255) NOT NULL,
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `skill_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `skill_category_id`, `description`, `category`, `created_at`, `updated_at`) VALUES
(2, 'Organizational Skills', 2, 'Employer-defined required skill', 'Administrative Skills', '2026-06-09 18:55:53', '2026-06-09 18:55:53'),
(3, 'Teamwork', 3, 'Employer-defined required skill', 'Soft Skills', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(4, 'Computer Literacy', 4, 'Employer-defined required skill', 'Computer Skills', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(5, 'Data Entry and Encoding', 5, 'Employer-defined required skill', 'Data Entry', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(7, 'Customer Service', 6, 'Employer-defined required skill', 'Interpersonal Skills', '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(8, 'Carpenter', 7, 'Employer-defined required skill', 'Creative', '2026-06-14 04:47:25', '2026-06-14 04:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `skill_categories`
--

CREATE TABLE `skill_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skill_categories`
--

INSERT INTO `skill_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Administrative Skills', 'Employer-defined skill category', '2026-06-09 18:55:53', '2026-06-09 18:55:53'),
(3, 'Soft Skills', 'Employer-defined skill category', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(4, 'Computer Skills', 'Employer-defined skill category', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(5, 'Data Entry', 'Employer-defined skill category', '2026-06-09 18:57:54', '2026-06-09 18:57:54'),
(6, 'Interpersonal Skills', 'Employer-defined skill category', '2026-06-09 19:55:43', '2026-06-09 19:55:43'),
(7, 'Creative', 'Employer-defined skill category', '2026-06-14 04:47:25', '2026-06-14 04:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'beneficiary',
  `beneficiary_type` enum('student','osy','dependent') DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_otp` varchar(255) DEFAULT NULL,
  `email_otp_expires_at` timestamp NULL DEFAULT NULL,
  `onboarding_completed` tinyint(1) NOT NULL DEFAULT 0,
  `is_temporary_password` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `role`, `beneficiary_type`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`, `email_otp`, `email_otp_expires_at`, `onboarding_completed`, `is_temporary_password`) VALUES
(1, 'Super Admin', 'admin@spes.com', '2026-06-09 07:48:54', '$2y$12$OL00ZjoDM48RT8Mzb7l8z.wa5Wx1fy4m/aBkcT4Ry5fb7LtjDq1QO', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, 'profile-photos/lljjVja91ZETtPhUG8CxMVAPH6iiVJvsHGGUTHcb.jpg', '2026-06-09 07:48:54', '2026-06-18 05:04:08', NULL, NULL, 0, 0),
(2, 'PESO User 1', 'peso1@spes.com', '2026-06-09 07:48:54', '$2y$12$OL00ZjoDM48RT8Mzb7l8z.wa5Wx1fy4m/aBkcT4Ry5fb7LtjDq1QO', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-09 07:48:54', '2026-06-10 03:04:09', NULL, NULL, 0, 0),
(3, 'PESO User 2', 'peso2@spes.com', '2026-06-09 07:48:54', '$2y$12$OL00ZjoDM48RT8Mzb7l8z.wa5Wx1fy4m/aBkcT4Ry5fb7LtjDq1QO', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-09 07:48:54', '2026-06-10 03:04:09', NULL, NULL, 0, 0),
(4, 'Employer 1', 'employer1@spes.com', '2026-06-09 07:48:55', '$2y$12$OL00ZjoDM48RT8Mzb7l8z.wa5Wx1fy4m/aBkcT4Ry5fb7LtjDq1QO', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-09 07:48:55', '2026-06-10 03:04:09', NULL, NULL, 0, 0),
(5, 'KATY TIOMICO', 'pinkian29@gmail.com', '2026-06-09 08:04:16', '$2y$12$OL00ZjoDM48RT8Mzb7l8z.wa5Wx1fy4m/aBkcT4Ry5fb7LtjDq1QO', NULL, NULL, NULL, 'employer', NULL, '4c21yZWHMQYXikOl1mUQZANpk6fJuBkYotHRHCOEOSLBz3dI6zL0mSI8Z7yv', NULL, 'profile-photos/q8w8SMMD9nzagJgqjNlYh90Q7ySkftJf3dt2hYBr.jpg', '2026-06-09 08:02:52', '2026-06-24 11:36:06', NULL, NULL, 0, 0),
(6, 'Adrian T Yalung', 'adrianyalung76@gmail.com', '2026-06-09 17:58:52', '$2y$12$tsVUtFMydr7jMB8YRA4yNewICebIh7zusB1.O9Cakfh.bL2blgUqy', NULL, NULL, NULL, 'beneficiary', 'student', 'MNhM7NH4QXy49TLuFIJURjemS9sflqpuVq5EwDDzo7BtIr8iHXeu9S8uF1ok', NULL, 'profile-photos/SJTB4GZBLEFnWUSHIq8QTlJbltCqGRQiK69R7sKB.jpg', '2026-06-09 17:56:36', '2026-06-17 13:57:39', NULL, NULL, 0, 0),
(7, 'Kissel C Tiomico', 'katyytiomico@gmail.com', '2026-06-11 06:42:58', '$2y$12$/NHH8sDBeSjdelRGuWd1lOHYktZflAzqByQEyfsx7SeFSH/AOwcc.', NULL, NULL, NULL, 'beneficiary', 'student', '0z82k9W8iw7JldTva3AiuR7efVdYnI29guiUEpdYfv4b15hdgJyMwQyGF2EY', NULL, NULL, '2026-06-11 06:42:10', '2026-06-17 17:40:57', NULL, NULL, 0, 0),
(9, 'Jerome C Lacson', 'abcd@example.com', NULL, '$2y$12$hpFVRI5bM5WkSHL2I0FTWelzkLhH6POUk738nt1dHjSsOYkbgyEjC', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-11 14:44:52', '2026-06-11 14:44:52', NULL, NULL, 0, 0),
(14, 'Jerome C Lacson', 'spsclprgrmfrmplmntfstdnts@gmail.com', '2026-06-12 07:13:52', '$2y$12$uVbf/zhIeIkaDt1D5tgn7ek.TdbvTjMM/WBQzD7BXriCyK8IHrNwm', NULL, NULL, NULL, 'beneficiary', 'osy', 'W4L7G4BpCvRO0lqLMUjGLjI9J4Mj236elKnMEGF4lxJjVfYyeabRHgFc7BTJ', NULL, 'profile-photos/qKx2no7OBGxG4eIXbmttn6o80XkQvcQRdQ81pnAg.jpg', '2026-06-12 07:13:25', '2026-06-17 13:56:27', NULL, NULL, 0, 0),
(16, 'Sarah P Tolentino', 'pinktulip012923@gmail.com', '2026-06-14 05:14:32', '$2y$12$NErSh5Gl809siih3SJw6vuxEGDQ5yAKX3/hOw0jnSqr3mfUSxHgcK', NULL, NULL, NULL, 'beneficiary', 'student', 'jBrjE2ixCqF71TyMPcKNo2MNOhEVxzytZbwhTkKU6y3hLTs8TCaNZo9oFRed', NULL, NULL, '2026-06-14 05:13:31', '2026-06-14 05:21:51', NULL, NULL, 0, 0),
(17, 'James F Marcelo', 'dianyalung01@gmail.com', '2026-06-14 08:04:06', '$2y$12$53q8EFI9P53GopAhXi0Xc.QqyEXWHZvROEpCsxsbVbf/nfbInqri2', NULL, NULL, NULL, 'beneficiary', 'dependent', 'MU0RM5ks8pHS80NkobCMcpSdM3jIDXGEJKAfPR33pfIn0gWtQvgPfsldZ4qG', NULL, NULL, '2026-06-14 08:01:43', '2026-06-17 17:41:10', NULL, NULL, 0, 0),
(18, 'Alagad ni Charice', 'alagadnicharice@gmail.com', NULL, '$2y$12$ZLBR6c8PERNKKm.UbzE86uav2Vl5FexN2.Gv6MaW3qkOVeYQTq7ny', NULL, NULL, NULL, 'beneficiary', NULL, 'ExN75dFXkEc9zOL4UOQMF7vQ16RC5KW0ErbrTOZ8B2wdI6N3SZscMVBFzb4v', NULL, NULL, '2026-06-14 19:26:52', '2026-06-14 19:26:52', NULL, NULL, 0, 0),
(20, 'Gohan M David', 'githubpilot6@gmail.com', '2026-06-24 11:50:00', '$2y$12$g5.BmnsOEe.uszZIl1La0ulUFjr2LORdMVZ6S8W3ARS.mytAOT/Wq', NULL, NULL, NULL, 'beneficiary', 'student', '8tLsIYiQspYcXRhuWGpDP228f8CylpB90b0izTnYjFjCU9HAIv7pUCmrLFcb', NULL, NULL, '2026-06-17 18:08:24', '2026-06-24 11:50:00', NULL, NULL, 0, 0),
(21, 'Maria Santos', 'brightcompany@example.test', '2026-06-19 08:04:09', '$2y$12$ixIkUt4ZBsHhjWFOTODqnOcq5fcYEzQx9PglOBob5mMWZw1dmRyti', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-19 08:04:09', '2026-06-19 08:04:09', NULL, NULL, 0, 0),
(22, 'Jose Ramirez', 'lagunatech@example.test', '2026-06-19 08:04:10', '$2y$12$zJBpAUwYotMHLw9THRreFOGy749uwczv5rhmTgq.5Q25uJRrPMy56', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-19 08:04:10', '2026-06-19 08:04:10', NULL, NULL, 0, 0),
(23, 'Ana Dela Cruz', 'greenfields@example.test', '2026-06-19 08:04:10', '$2y$12$yL2dT3tUoM1UKdvi.Au4juOsWNcbek1yl8h4oDAUDqAAtve3SUMuu', NULL, NULL, NULL, 'beneficiary', NULL, NULL, NULL, NULL, '2026-06-19 08:04:10', '2026-06-19 08:04:10', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `work_outputs`
--

CREATE TABLE `work_outputs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `job_listing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `accomplishments` text DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'submitted',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `original_submitted_at` timestamp NULL DEFAULT NULL,
  `resubmitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_remarks` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_outputs`
--

INSERT INTO `work_outputs` (`id`, `employer_id`, `beneficiary_id`, `application_id`, `job_listing_id`, `work_date`, `title`, `description`, `accomplishments`, `hours_worked`, `status`, `submitted_by`, `original_submitted_at`, `resubmitted_at`, `reviewed_by`, `reviewed_at`, `review_remarks`, `file_path`, `original_name`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 2, '2026-06-10', 'Office Assistant', NULL, 'Assisted in organizing and filing office documents, updating records, and preparing necessary paperwork for daily office operations. Provided administrative support by responding to inquiries, managing correspondence, and ensuring documents were properly maintained.\r\n\r\nCompleted. All assigned documents were organized and filed accurately, records were updated, and administrative tasks were accomplished within the required timeframe.', 8.00, 'approved', 6, NULL, NULL, 5, '2026-06-09 22:01:59', 'The employee successfully completed all assigned tasks in a timely and organized manner. Documents were properly filed, records were updated accurately, and administrative duties were carried out efficiently. Demonstrated attention to detail, reliability, and a positive work attitude throughout the assignment. Overall performance met expectations and contributed to the smooth operation of the office.', 'work_outputs/DuaWvHR5h2e9jrau4qre2a9ZOb9ktJLM8wGpmhba.pdf', 'adrian resumee.pdf', '2026-06-09 21:59:14', '2026-06-09 22:01:59'),
(3, 1, 7, 11, 4, '2026-06-14', 'Factory Worker', NULL, 'ntapos kupo yung pagrerepak ng produktong inassigned saken', 8.00, 'approved', 14, NULL, NULL, 5, '2026-06-13 23:32:05', NULL, 'work_outputs/ypyz5VQlv8Knyn76huBp1F17YKsX8mFruSA3LW1H.jpg', 'BIRTH CERT.jpg', '2026-06-13 23:18:10', '2026-06-13 23:32:05'),
(4, 1, 9, 13, 5, '2026-06-13', 'Warehouse Helper', NULL, 'na oraganized kuna lahat nang gamit', 8.00, 'submitted', 16, NULL, NULL, NULL, NULL, NULL, 'work_outputs/x4uc62ugvNr296N8u21ooKGgisOBQWxfvxEw4h0Z.png', 'Business Permit.png', '2026-06-14 06:23:37', '2026-06-14 06:23:37'),
(5, 1, 11, 16, 2, '2026-06-18', 'test', NULL, 'test', 8.00, 'approved', 20, NULL, NULL, 5, '2026-06-17 19:05:22', 'resubmit', 'work_outputs/nnohMWBYaInPllqPKzKuR7Zj9hQfZlSwQosH2psy.jpg', 'VALID ID.jpg', '2026-06-17 19:04:41', '2026-06-17 19:05:22'),
(6, 1, 11, 16, 2, '2026-06-18', 'test', NULL, 'test', 8.00, 'submitted', 20, '2026-06-17 19:05:38', '2026-06-17 19:13:07', 5, '2026-06-17 19:05:53', 'test', 'work_outputs/gRejtIHLeQvxa3OBAU0jC9DRhd6RO5CDCTvHVh2Y.jpg', 'PA5KiY9AjB2X3ALPpHc9Wx0fcsUoovFUBIM8Y2B6.jpg', '2026-06-17 19:05:38', '2026-06-17 19:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary_id` bigint(20) UNSIGNED NOT NULL,
  `day` varchar(255) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_created_by_foreign` (`created_by`);

--
-- Indexes for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_reads_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `announcement_reads_user_id_foreign` (`user_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_beneficiary_id_foreign` (`beneficiary_id`),
  ADD KEY `applications_batch_id_foreign` (`batch_id`),
  ADD KEY `applications_job_listing_id_foreign` (`job_listing_id`),
  ADD KEY `applications_employer_acknowledged_by_foreign` (`employer_acknowledged_by`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_application_id_index` (`application_id`),
  ADD KEY `attendances_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `beneficiaries_student_id_unique` (`student_id`),
  ADD UNIQUE KEY `beneficiaries_user_id_unique` (`user_id`),
  ADD KEY `beneficiaries_employer_id_foreign` (`employer_id`),
  ADD KEY `beneficiaries_job_id_foreign` (`job_id`);

--
-- Indexes for table `beneficiary_ratings`
--
ALTER TABLE `beneficiary_ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `beneficiary_skill`
--
ALTER TABLE `beneficiary_skill`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `beneficiary_skill_unique` (`beneficiary_id`,`skill_id`),
  ADD KEY `beneficiary_skill_skill_id_foreign` (`skill_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contracts_application_id_foreign` (`application_id`),
  ADD KEY `contracts_scheduled_by_foreign` (`scheduled_by`),
  ADD KEY `contracts_schedule_group_id_index` (`schedule_group_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employers_user_id_foreign` (`user_id`);

--
-- Indexes for table `employer_compliances`
--
ALTER TABLE `employer_compliances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employer_feedback`
--
ALTER TABLE `employer_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_feedback_employer_id_foreign` (`employer_id`);

--
-- Indexes for table `employer_ratings`
--
ALTER TABLE `employer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_ratings_employer_id_foreign` (`employer_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_application_id_foreign` (`application_id`),
  ADD KEY `exams_scheduled_by_foreign` (`scheduled_by`),
  ADD KEY `exams_schedule_group_id_index` (`schedule_group_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interviews_job_listing_id_foreign` (`job_listing_id`),
  ADD KEY `interviews_employer_id_foreign` (`employer_id`),
  ADD KEY `interviews_beneficiary_id_foreign` (`beneficiary_id`),
  ADD KEY `interviews_scheduled_by_foreign` (`scheduled_by`),
  ADD KEY `interviews_schedule_group_id_index` (`schedule_group_id`),
  ADD KEY `interviews_interviewer_id_foreign` (`interviewer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_listings`
--
ALTER TABLE `job_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_listings_employer_id_foreign` (`employer_id`),
  ADD KEY `job_listings_assigned_beneficiary_id_foreign` (`assigned_beneficiary_id`);

--
-- Indexes for table `job_listing_skills`
--
ALTER TABLE `job_listing_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_listing_skills_job_listing_id_skill_id_unique` (`job_listing_id`,`skill_id`),
  ADD KEY `job_listing_skills_skill_id_foreign` (`skill_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_otp_resets`
--
ALTER TABLE `password_otp_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_otp_resets_email_index` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_patient_id_foreign` (`patient_id`),
  ADD KEY `reports_doctor_id_foreign` (`doctor_id`),
  ADD KEY `reports_employer_id_foreign` (`employer_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skills_name_unique` (`name`),
  ADD KEY `skills_skill_category_id_foreign` (`skill_category_id`);

--
-- Indexes for table `skill_categories`
--
ALTER TABLE `skill_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `work_outputs`
--
ALTER TABLE `work_outputs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_outputs_application_id_foreign` (`application_id`),
  ADD KEY `work_outputs_job_listing_id_foreign` (`job_listing_id`),
  ADD KEY `work_outputs_submitted_by_foreign` (`submitted_by`),
  ADD KEY `work_outputs_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_schedules_beneficiary_id_foreign` (`beneficiary_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `analytics`
--
ALTER TABLE `analytics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `beneficiary_ratings`
--
ALTER TABLE `beneficiary_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `beneficiary_skill`
--
ALTER TABLE `beneficiary_skill`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employer_compliances`
--
ALTER TABLE `employer_compliances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_feedback`
--
ALTER TABLE `employer_feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_ratings`
--
ALTER TABLE `employer_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_listings`
--
ALTER TABLE `job_listings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `job_listing_skills`
--
ALTER TABLE `job_listing_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `password_otp_resets`
--
ALTER TABLE `password_otp_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `skill_categories`
--
ALTER TABLE `skill_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `work_outputs`
--
ALTER TABLE `work_outputs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD CONSTRAINT `announcement_reads_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_beneficiary_id_foreign` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`),
  ADD CONSTRAINT `applications_employer_acknowledged_by_foreign` FOREIGN KEY (`employer_acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_job_listing_id_foreign` FOREIGN KEY (`job_listing_id`) REFERENCES `job_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD CONSTRAINT `beneficiaries_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `beneficiaries_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `job_listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `beneficiaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `beneficiary_skill`
--
ALTER TABLE `beneficiary_skill`
  ADD CONSTRAINT `beneficiary_skill_beneficiary_id_foreign` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `beneficiary_skill_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_scheduled_by_foreign` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_feedback`
--
ALTER TABLE `employer_feedback`
  ADD CONSTRAINT `employer_feedback_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_ratings`
--
ALTER TABLE `employer_ratings`
  ADD CONSTRAINT `employer_ratings_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_scheduled_by_foreign` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_beneficiary_id_foreign` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_interviewer_id_foreign` FOREIGN KEY (`interviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_job_listing_id_foreign` FOREIGN KEY (`job_listing_id`) REFERENCES `job_listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_scheduled_by_foreign` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_listings`
--
ALTER TABLE `job_listings`
  ADD CONSTRAINT `job_listings_assigned_beneficiary_id_foreign` FOREIGN KEY (`assigned_beneficiary_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_listings_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_listing_skills`
--
ALTER TABLE `job_listing_skills`
  ADD CONSTRAINT `job_listing_skills_job_listing_id_foreign` FOREIGN KEY (`job_listing_id`) REFERENCES `job_listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_listing_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_skill_category_id_foreign` FOREIGN KEY (`skill_category_id`) REFERENCES `skill_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `work_outputs`
--
ALTER TABLE `work_outputs`
  ADD CONSTRAINT `work_outputs_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `work_outputs_job_listing_id_foreign` FOREIGN KEY (`job_listing_id`) REFERENCES `job_listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `work_outputs_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `work_outputs_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `work_schedules_beneficiary_id_foreign` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
