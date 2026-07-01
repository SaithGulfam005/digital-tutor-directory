-- Clean Digital Tutor Directory import for XAMPP/MariaDB.
-- This file intentionally excludes phpMyAdmin's internal database and test DB.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `digital_tutor_directory`;
CREATE DATABASE `digital_tutor_directory`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `digital_tutor_directory`;

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_name` (`name`),
  UNIQUE KEY `uq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'assets/images/teachers/placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `teacher_profiles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `qualification` varchar(190) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tp_user` (`user_id`),
  CONSTRAINT `fk_tp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_courses_slug` (`slug`),
  KEY `idx_courses_status` (`status`),
  KEY `idx_courses_teacher` (`teacher_id`),
  KEY `fk_course_category` (`category_id`),
  CONSTRAINT `fk_course_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_course_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `duration` varchar(10) DEFAULT '10:00',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `content_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lessons_course` (`course_id`),
  CONSTRAINT `fk_lesson_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `last_access` date DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enrollment` (`student_id`,`course_id`),
  KEY `fk_enroll_course` (`course_id`),
  CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lesson_progress` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lesson_progress` (`enrollment_id`,`lesson_id`),
  KEY `fk_lp_lesson` (`lesson_id`),
  CONSTRAINT `fk_lp_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(40) NOT NULL DEFAULT 'Card',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `teacher_share` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_ref` (`reference`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_created` (`created_at`),
  KEY `fk_pay_student` (`student_id`),
  KEY `fk_pay_course` (`course_id`),
  CONSTRAINT `fk_pay_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `teacher_documents` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_profile_id` int(10) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_td_profile` (`teacher_profile_id`),
  CONSTRAINT `fk_td_profile` FOREIGN KEY (`teacher_profile_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `otp` char(6) NOT NULL,
  `attempts` tinyint(3) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_otp` (`email`,`otp`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Development', 'development'),
(2, 'Design', 'design'),
(3, 'Business', 'business'),
(4, 'Marketing', 'marketing'),
(5, 'Data Science', 'data-science');

-- Demo logins:
-- admin@digitaltutor.com / admin123
-- teacher@gmail.com / teacher123
-- student@gmail.com / student123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `bio`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@digitaltutor.com', '+92 300 0000001', '$2y$12$cF/DmlFy1/hbzbYjx9MwM..P/w1tNv0Du7ApNHRvB.2onf6pnUqee', 'admin', 'active', 'Platform administrator', 'assets/images/teachers/placeholder.jpg', '2026-05-31 08:09:29', '2026-07-01 08:47:00'),
(21, 'Teacher User', 'teacher@gmail.com', NULL, '$2y$12$RPS01yP1uOcihRYPWFMQOe1pFQ2YCqLmhXoVM5SRUeWAB6NtaWsDq', 'teacher', 'active', NULL, 'uploads/avatars/avatar_21_6a278f92587681.35093354.png', '2026-06-09 03:57:37', '2026-07-01 08:47:00'),
(22, 'Student User', 'student@gmail.com', '03452385839', '$2y$12$gqZIq9bWaPjqPJvW/GOIruc8I5gj0bPmpKXoYORTwz6yYyWlnUtIi', 'student', 'active', NULL, 'assets/images/teachers/placeholder.jpg', '2026-06-09 08:28:42', '2026-07-01 08:47:00'),
(23, 'Riaz', 'riaz@gmail.com', '03032508393', '$2y$12$gqZIq9bWaPjqPJvW/GOIruc8I5gj0bPmpKXoYORTwz6yYyWlnUtIi', 'student', 'active', NULL, 'assets/images/teachers/placeholder.jpg', '2026-06-10 07:20:47', '2026-07-01 08:47:00'),
(24, 'Ahmad', 'ahmad@gmail.com', '03032508393', '$2y$12$gqZIq9bWaPjqPJvW/GOIruc8I5gj0bPmpKXoYORTwz6yYyWlnUtIi', 'student', 'active', NULL, 'assets/images/teachers/placeholder.jpg', '2026-06-30 03:56:25', '2026-07-01 08:47:00');

INSERT INTO `teacher_profiles` (`id`, `user_id`, `qualification`, `cnic`, `subject`, `experience`, `rating`, `verification_status`, `verified_at`, `created_at`, `updated_at`) VALUES
(13, 21, 'Master', '3410124917023', 'General', '0 years', 0.00, 'verified', '2026-06-09', '2026-06-09 03:57:37', '2026-06-09 03:58:03');

INSERT INTO `courses` (`id`, `teacher_id`, `category_id`, `title`, `slug`, `description`, `price`, `thumb`, `status`, `rating`, `created_at`, `updated_at`) VALUES
(14, 21, 1, 'Python', 'python', 'Python is a powerful language.', 55.00, 'assets/images/courses/placeholder.jpg', 'published', 0.00, '2026-06-10 07:23:21', '2026-06-10 07:25:02'),
(15, 21, 1, 'JavaScript', 'javascript', 'JavaScript builds interactive web logic.', 60.00, 'assets/images/courses/placeholder.jpg', 'published', 0.00, '2026-06-10 07:27:46', '2026-06-10 07:28:14'),
(16, 21, 5, 'Data Science', 'data-science', 'Data science basics and practice.', 50.00, 'assets/images/courses/placeholder.jpg', 'published', 0.00, '2026-06-11 02:35:10', '2026-06-11 02:35:39'),
(17, 21, 1, 'C++', 'cpp', 'C++ programming fundamentals.', 50.00, 'assets/images/courses/placeholder.jpg', 'published', 0.00, '2026-06-30 04:13:52', '2026-06-30 04:14:24');

INSERT INTO `lessons` (`id`, `course_id`, `title`, `duration`, `sort_order`, `content_url`) VALUES
(36, 14, 'Python structure', '10:00', 1, NULL),
(37, 14, 'Basic Python', '10:00', 2, NULL),
(38, 14, 'Advanced Python', '10:00', 3, NULL),
(39, 15, 'Basic JavaScript', '10:00', 1, NULL),
(40, 15, 'Advanced JavaScript', '10:00', 2, NULL),
(41, 16, 'Data science basic', '10:00', 1, 'uploads/videos/video_6a2a1eded4c3e6.94825271.mp4'),
(42, 16, 'Advanced data science', '10:00', 2, 'uploads/videos/video_6a2a1eded52584.33314677.mp4'),
(43, 17, 'C++ basics', '10:00', 1, 'uploads/videos/video_6a434263cb1455.12826430.mp4'),
(44, 17, 'Advanced C++', '10:00', 2, 'uploads/videos/video_6a43426d928e36.32388385.mp4');

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `progress`, `status`, `last_access`, `enrolled_at`) VALUES
(11, 23, 14, 0, 'active', '2026-06-11', '2026-06-11 02:20:41'),
(12, 22, 16, 0, 'active', '2026-06-11', '2026-06-11 02:37:00'),
(13, 24, 15, 0, 'active', '2026-06-30', '2026-06-30 03:57:47'),
(14, 24, 17, 0, 'active', '2026-06-30', '2026-06-30 04:15:28');

INSERT INTO `payments` (`id`, `reference`, `student_id`, `course_id`, `amount`, `method`, `status`, `teacher_share`, `created_at`) VALUES
(12, 'PAY-96810', 23, 14, 55.00, 'Bank Transfer (manual approval)', 'completed', 38.50, '2026-06-11 02:20:17'),
(13, 'PAY-58946', 22, 16, 50.00, 'Bank Transfer (manual approval)', 'completed', 35.00, '2026-06-11 02:36:32'),
(14, 'PAY-34326', 24, 15, 60.00, 'JazzCash', 'completed', 42.00, '2026-06-30 03:57:47'),
(15, 'PAY-98394', 24, 17, 50.00, 'JazzCash', 'completed', 35.00, '2026-06-30 04:15:28');

INSERT INTO `teacher_documents` (`id`, `teacher_profile_id`, `original_name`, `file_path`, `uploaded_at`) VALUES
(6, 13, 'Screenshot (12).png', 'uploads/teachers/doc_6a278f311a6ea1.80627590.png', '2026-06-09 03:57:37');

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Test User', 'test@email.com', 'General inquiry', 'Hello, I have a question about your platform.', '2026-05-31 08:09:29'),
(2, 'Ahsan Malik', 'ahsan@email.com', 'Teacher registration', 'How long does the teacher verification process take?', '2026-05-31 08:09:29'),
(3, 'Sara Ahmed', 'sara.a@email.com', 'Course refund', 'I would like a refund for the Data Science course.', '2026-05-31 08:09:29');

ALTER TABLE `categories` AUTO_INCREMENT = 6;
ALTER TABLE `users` AUTO_INCREMENT = 25;
ALTER TABLE `teacher_profiles` AUTO_INCREMENT = 14;
ALTER TABLE `courses` AUTO_INCREMENT = 18;
ALTER TABLE `lessons` AUTO_INCREMENT = 45;
ALTER TABLE `enrollments` AUTO_INCREMENT = 15;
ALTER TABLE `payments` AUTO_INCREMENT = 16;
ALTER TABLE `teacher_documents` AUTO_INCREMENT = 7;
ALTER TABLE `contact_messages` AUTO_INCREMENT = 4;
ALTER TABLE `password_resets` AUTO_INCREMENT = 1;
