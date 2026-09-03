-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 08:45 AM
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
-- Database: `digital_tutor_directory`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Development', 'development'),
(2, 'Design', 'design'),
(3, 'Business', 'business'),
(4, 'Marketing', 'marketing'),
(5, 'Data Science', 'data-science'),
(6, 'bussniess', 'bussniess'),
(7, 'English', 'english');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Test User', 'test@email.com', 'General inquiry', 'Hello, I have a question about your platform.', '2026-05-31 08:09:29'),
(2, 'Ahsan Malik', 'ahsan@email.com', 'Teacher registration', 'How long does the teacher verification process take?', '2026-05-31 08:09:29'),
(3, 'Sara Ahmed', 'sara.a@email.com', 'Course refund', 'I would like a refund for the Data Science course.', '2026-05-31 08:09:29'),
(4, 'saith', 'gulfamsaith267@gmail.com', 'Payment / billing', 'payment issue', '2026-07-04 16:20:16'),
(5, 'ahmad', 'digitaltutordirectory@gmail.com', 'Course support', 'error of video lectures', '2026-07-19 16:53:09'),
(6, 'ahmad', 'ahmad@gmail.com', 'Payment / billing', 'i am sending a money but couse is not delivered to me', '2026-08-13 15:52:51'),
(7, 'ahmad', 'ahmad@gmail.com', 'Course support', 'i purchase a course but this course is not deleivered to me', '2026-08-14 07:58:10');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `thumb` varchar(255) DEFAULT 'assets/images/courses/placeholder.jpg',
  `status` enum('draft','pending','published','rejected') NOT NULL DEFAULT 'pending',
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `teacher_id`, `category_id`, `title`, `slug`, `description`, `price`, `thumb`, `status`, `rating`, `created_at`, `updated_at`) VALUES
(16, 21, 5, 'data science', 'data-science', 'data scienceis field of rebortics', 50.00, 'uploads/courses/course-thumb_6a491f45ef3e70.37162838.jpg', 'published', 0.00, '2026-06-11 02:35:10', '2026-07-04 14:57:09'),
(17, 21, 1, 'c++', 'c', 'geuhdviohregh;', 50.00, 'uploads/courses/course-thumb_6a491f351485b0.56019405.jpg', 'published', 5.00, '2026-06-30 04:13:52', '2026-07-04 16:16:03'),
(18, 21, 1, 'python 2', 'python-2', 'yty6yujyukiiu', 50.00, 'uploads/courses/course-thumb_6a491f26ca5c09.04113793.jpg', 'published', 3.00, '2026-07-01 07:09:01', '2026-07-19 16:43:14'),
(19, 21, 6, 'javascript', 'javascript', 'ryeuhr', 50.00, 'uploads/courses/course-thumb_6a491eef638507.79984889.jpg', 'published', 0.00, '2026-07-01 09:33:43', '2026-07-04 14:59:39'),
(20, 21, 7, 'English', 'english', 'English is a main language of global , media and website', 70.00, 'uploads/courses/course-thumb_6a4920fc5592c6.21064312.jpg', 'published', 4.00, '2026-07-04 15:04:28', '2026-07-04 15:52:56'),
(21, 21, 1, 'python 2', 'python-2', 'grpnvy', 50.00, 'uploads/courses/course-thumb_6a92fed6cb9927.44125816.png', 'published', 0.00, '2026-08-29 15:46:30', '2026-08-29 15:47:24');

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `course_rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `teacher_rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_reviews`
--

INSERT INTO `course_reviews` (`id`, `student_id`, `course_id`, `teacher_id`, `course_rating`, `teacher_rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 24, 20, 21, 4, 4, 'welldone', '2026-07-04 15:52:56', '2026-07-04 15:52:56'),
(2, 24, 18, 21, 5, 5, 'done', '2026-07-04 15:55:48', '2026-07-04 15:57:21'),
(6, 24, 17, 21, 5, 5, 'average', '2026-07-04 16:16:03', '2026-07-04 16:16:03'),
(7, 23, 18, 21, 1, 2, 'not well for me', '2026-07-09 15:49:08', '2026-07-19 16:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `otp` varchar(255) NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `last_access` date DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `progress`, `status`, `last_access`, `enrolled_at`) VALUES
(12, 22, 16, 0, 'active', '2026-06-11', '2026-06-11 02:37:00'),
(14, 24, 17, 100, 'completed', '2026-07-04', '2026-06-30 04:15:28'),
(15, 23, 18, 100, 'completed', '2026-07-09', '2026-07-01 07:10:27'),
(16, 24, 18, 100, 'completed', '2026-07-04', '2026-07-01 07:19:42'),
(17, 24, 20, 100, 'completed', '2026-07-04', '2026-07-04 15:36:25'),
(18, 24, 21, 0, 'active', '2026-08-29', '2026-08-29 16:17:23');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `duration` varchar(10) DEFAULT '10:00',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `content_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `duration`, `sort_order`, `content_url`) VALUES
(51, 18, 'basic python', '10:00', 1, 'uploads/videos/video_6a44bcf9c70344.86388974.mp4'),
(52, 18, 'python structure', '10:00', 2, 'uploads/videos/video_6a44bd00314e11.81839524.mp4'),
(53, 17, 'c++', '10:00', 1, 'uploads/videos/video_6a434263cb1455.12826430.mp4'),
(54, 17, 'advance', '10:00', 2, 'uploads/videos/video_6a43426d928e36.32388385.mp4'),
(55, 16, 'data science basic', '10:00', 1, 'uploads/videos/video_6a2a1eded4c3e6.94825271.mp4'),
(56, 16, 'advance data science', '10:00', 2, 'uploads/videos/video_6a2a1eded52584.33314677.mp4'),
(57, 19, 'bdg', '10:00', 1, 'uploads/videos/video_6a44debc346bd7.35769289.mp4'),
(58, 19, 'yhuje5', '10:00', 2, 'uploads/videos/video_6a44dec3ad38f7.92010489.mp4'),
(61, 20, 'basic english', '17:36', 1, 'uploads/lessons/lesson_6a4920f345c748.22714094.mp4'),
(62, 20, 'advance english', '10:00', 2, 'uploads/lessons/lesson_6a4920fa583ad8.12455425.mp4'),
(63, 21, 'basic python', '6:18', 1, 'uploads/videos/video_6a92fea87e60a9.03523054.mp4'),
(64, 21, 'python structure', '12:16', 2, 'uploads/videos/video_6a92fed05c6271.45773898.mp4');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `enrollment_id`, `lesson_id`, `completed_at`) VALUES
(13, 17, 61, '2026-07-04 15:52:19'),
(14, 17, 62, '2026-07-04 15:52:24'),
(15, 16, 51, '2026-07-04 15:55:33'),
(17, 16, 52, '2026-07-04 15:55:43'),
(19, 14, 53, '2026-07-04 16:15:33'),
(20, 14, 54, '2026-07-04 16:15:36'),
(21, 15, 51, '2026-07-09 15:48:56'),
(24, 15, 52, '2026-07-09 15:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `otp` char(6) NOT NULL,
  `attempts` tinyint(3) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp`, `attempts`, `created_at`, `expires_at`) VALUES
(12, 'itzgulfam267@gmail.com', '704393', 0, '2026-06-07 11:26:45', '2026-06-07 16:36:45');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(20) NOT NULL,
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(40) NOT NULL DEFAULT 'Card',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `teacher_share` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reference`, `stripe_session_id`, `student_id`, `course_id`, `amount`, `method`, `status`, `teacher_share`, `created_at`) VALUES
(13, 'PAY-58946', NULL, 22, 16, 50.00, 'Bank Transfer (manual approval)', 'completed', 35.00, '2026-06-11 02:36:32'),
(15, 'PAY-98394', NULL, 24, 17, 50.00, 'JazzCash', 'completed', 35.00, '2026-06-30 04:15:28'),
(16, 'PAY-51209', NULL, 23, 18, 50.00, 'JazzCash', 'refunded', 35.00, '2026-07-01 07:10:27'),
(17, 'PAY-68890', NULL, 24, 18, 50.00, 'JazzCash', 'completed', 35.00, '2026-07-01 07:18:39'),
(18, 'PAY-80404', NULL, 24, 20, 70.00, 'JazzCash', 'completed', 49.00, '2026-07-04 15:35:31'),
(19, 'PAY-85867', NULL, 24, 21, 50.00, 'Credit / Debit Card (Stripe)', 'completed', 35.00, '2026-08-29 16:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_documents`
--

CREATE TABLE `teacher_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_profile_id` int(10) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_documents`
--

INSERT INTO `teacher_documents` (`id`, `teacher_profile_id`, `original_name`, `file_path`, `uploaded_at`) VALUES
(6, 13, 'Screenshot (12).png', 'uploads/teachers/doc_6a278f311a6ea1.80627590.png', '2026-06-09 03:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_favorites`
--

CREATE TABLE `teacher_favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_favorites`
--

INSERT INTO `teacher_favorites` (`id`, `student_id`, `teacher_id`, `created_at`) VALUES
(1, 24, 21, '2026-07-04 16:16:03');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_profiles`
--

CREATE TABLE `teacher_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `qualification` varchar(190) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_profiles`
--

INSERT INTO `teacher_profiles` (`id`, `user_id`, `qualification`, `cnic`, `subject`, `experience`, `rating`, `verification_status`, `verified_at`, `created_at`, `updated_at`) VALUES
(13, 21, 'Master', '3410124917023', 'General', '0 years', 4.00, 'verified', '2026-06-09', '2026-06-09 03:57:37', '2026-07-19 16:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','pending','pending_verification') NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'assets/images/teachers/placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `email_verified_at`, `bio`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@digitaltutor.com', '+92 300 0000001', '$2y$10$j062Vvt8j8Dt69FQJrTcRe6qririmR0ySDNxxlN4G9qRaiHoScrIC', 'admin', 'active', NULL, 'Platform administrator', 'assets/images/teachers/placeholder.jpg', '2026-05-31 08:09:29', '2026-06-04 10:02:49'),
(21, 'teacher', 'teacher@gmail.com', NULL, '$2y$10$0kWFiWKs/VHl15BlIEsB2eeYxhdK0iWRWBLBI1N.HQ2zDLbb7vcZy', 'teacher', 'active', NULL, NULL, 'uploads/avatars/avatar_21_6a4921a1be55b6.90362478.jpg', '2026-06-09 03:57:37', '2026-07-04 15:07:13'),
(22, 'ali', 'ali@gmail.com', '03452385839', '$2y$10$DkN.pJ41ldVbbbgtCBmZ0O8EdTKegW2S.qR3TOqMFDpyPGmfHhslO', 'student', 'active', NULL, NULL, 'assets/images/teachers/placeholder.jpg', '2026-06-09 08:28:42', '2026-06-09 08:28:42'),
(23, 'riaz', 'riaz@gmail.com', NULL, '$2y$10$NOKtdyafx5Z5.gT27XnCk.Pfd14Qz6AEqf/ePeuuoiCJfGZ0CM8W6', 'student', 'active', NULL, NULL, 'uploads/avatars/avatar_23_6a44e48651ed81.05513489.png', '2026-06-10 07:20:47', '2026-07-01 09:57:26'),
(24, 'ahmad', 'ahmad@gmail.com', NULL, '$2y$10$i0B4Dm6.CWO/QbjcllU7Weptds.GZEevor.xCmvXUQi03kdwm06tu', 'student', 'active', NULL, NULL, 'uploads/avatars/avatar_24_6a44d34b8d0fa7.61656282.png', '2026-06-30 03:56:25', '2026-07-01 08:43:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cat_name` (`name`),
  ADD UNIQUE KEY `uq_cat_slug` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_status` (`status`),
  ADD KEY `idx_courses_teacher` (`teacher_id`),
  ADD KEY `fk_course_category` (`category_id`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_course_review` (`student_id`,`course_id`),
  ADD KEY `idx_course_reviews_course` (`course_id`),
  ADD KEY `idx_course_reviews_teacher` (`teacher_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email_verification_user` (`user_id`),
  ADD KEY `idx_email_verifications_expires` (`expires_at`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enrollment` (`student_id`,`course_id`),
  ADD KEY `fk_enroll_course` (`course_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lessons_course` (`course_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lesson_progress` (`enrollment_id`,`lesson_id`),
  ADD KEY `fk_lp_lesson` (`lesson_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_otp` (`email`,`otp`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payment_ref` (`reference`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_created` (`created_at`),
  ADD KEY `fk_pay_student` (`student_id`),
  ADD KEY `fk_pay_course` (`course_id`);

--
-- Indexes for table `teacher_documents`
--
ALTER TABLE `teacher_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_td_profile` (`teacher_profile_id`);

--
-- Indexes for table `teacher_favorites`
--
ALTER TABLE `teacher_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_teacher_favorite` (`student_id`,`teacher_id`),
  ADD KEY `idx_teacher_favorites_teacher` (`teacher_id`);

--
-- Indexes for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tp_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `teacher_documents`
--
ALTER TABLE `teacher_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher_favorites`
--
ALTER TABLE `teacher_favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_course_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_course_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lesson_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_lp_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_documents`
--
ALTER TABLE `teacher_documents`
  ADD CONSTRAINT `fk_td_profile` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD CONSTRAINT `fk_tp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
