-- Database schema for Attendance Management System
-- Database name: university_attendance

CREATE DATABASE IF NOT EXISTS `university_attendance` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `university_attendance`;

-- Roles are stored as enum in users for simplicity
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150),
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `role` ENUM('student','professor','admin') NOT NULL DEFAULT 'student',
  `group_name` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(50) UNIQUE NOT NULL,
  `course_name` VARCHAR(200) NOT NULL,
  `professor_id` INT,
  `group_name` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (`course_id`,`student_id`),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `session_number` INT DEFAULT 1,
  `session_date` DATE NOT NULL,
  `topic` VARCHAR(255),
  `is_open` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('present','absent','late') DEFAULT 'absent',
  `participation` TINYINT DEFAULT 0,
  `behavior` TINYINT DEFAULT 0,
  `justification_id` INT DEFAULT NULL,
  `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (`session_id`,`student_id`),
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `justifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `session_id` INT,
  `reason` TEXT,
  `file_path` VARCHAR(255),
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- optional indexes
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_course_group ON courses(group_name);
-
SELECT id, course_name FROM courses WHERE professor_id = <PROF_ID>;
:
SELECT id, session_number, session_date FROM sessions WHERE course_id = <COURSE_ID> ORDER BY session_date DESC LIMIT 6;