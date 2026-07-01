-- Digital Tutor Directory — Database Schema
-- Run via database/install.php or import in phpMyAdmin

CREATE DATABASE IF NOT EXISTS digital_tutor_directory
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE digital_tutor_directory;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS lesson_progress;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS teacher_documents;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS teacher_profiles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
  status ENUM('active','inactive','pending') NOT NULL DEFAULT 'active',
  bio TEXT DEFAULT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE teacher_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  qualification VARCHAR(190) DEFAULT NULL,
  cnic VARCHAR(20) DEFAULT NULL,
  subject VARCHAR(80) DEFAULT NULL,
  experience VARCHAR(50) DEFAULT NULL,
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  verified_at DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teacher_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_profile_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_profile_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  thumb VARCHAR(255) DEFAULT 'assets/images/avatars/placeholder.svg',
  status ENUM('draft','pending','published','rejected') NOT NULL DEFAULT 'pending',
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_courses_status (status),
  INDEX idx_courses_teacher (teacher_id)
) ENGINE=InnoDB;

CREATE TABLE lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  duration VARCHAR(10) DEFAULT '10:00',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  content_url VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  INDEX idx_lessons_course (course_id)
) ENGINE=InnoDB;

CREATE TABLE enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('active','completed') NOT NULL DEFAULT 'active',
  last_access DATE DEFAULT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_enrollment (student_id, course_id),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED NOT NULL,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_lesson_progress (enrollment_id, lesson_id),
  FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  student_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(40) NOT NULL DEFAULT 'Card',
  status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  teacher_share DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  INDEX idx_payments_status (status),
  INDEX idx_payments_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  student_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  session_date DATE DEFAULT NULL,
  session_time VARCHAR(20) DEFAULT NULL,
  session_duration VARCHAR(20) DEFAULT NULL,
  subject VARCHAR(190) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending_payment','paid','cancelled') NOT NULL DEFAULT 'pending_payment',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_bookings_status (status),
  INDEX idx_bookings_teacher (teacher_id)
) ENGINE=InnoDB;

CREATE TABLE booking_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  booking_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(40) NOT NULL DEFAULT 'JazzCash',
  status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
  gateway_reference VARCHAR(60) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_booking_payments_status (status),
  INDEX idx_booking_payments_booking (booking_id)
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    otp CHAR(6) NOT NULL,
    attempts TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    INDEX idx_email_otp (email, otp),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;
