-- Insert sample data for testing

-- Insert admin user
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `group_name`) 
VALUES ('admin', '$2y$10$YIjlrHmVx3t0Z8VQzQq1uOK2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2', 'admin@university.dz', 'Admin', 'User', 'admin', NULL)
ON DUPLICATE KEY UPDATE id=id;

-- Insert professor
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `group_name`) 
VALUES ('professor', '$2y$10$YIjlrHmVx3t0Z8VQzQq1uOK2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2', 'professor@university.dz', 'Dr.', 'Ahmed', 'professor', NULL)
ON DUPLICATE KEY UPDATE id=id;

-- Insert students
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `group_name`) 
VALUES ('student1', '$2y$10$YIjlrHmVx3t0Z8VQzQq1uOK2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2', 'student1@university.dz', 'Ali', 'Yacine', 'student', 'G1')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `group_name`) 
VALUES ('student2', '$2y$10$YIjlrHmVx3t0Z8VQzQq1uOK2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2', 'student2@university.dz', 'Sara', 'Ahmed', 'student', 'G1')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `group_name`) 
VALUES ('student3', '$2y$10$YIjlrHmVx3t0Z8VQzQq1uOK2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2Q2', 'student3@university.dz', 'Mohamed', 'Hassan', 'student', 'G2')
ON DUPLICATE KEY UPDATE id=id;

-- Insert courses (Professor will teach these)
INSERT INTO `courses` (`course_code`, `course_name`, `professor_id`, `group_name`) 
VALUES ('ALG-001', 'Algorithmique Avancée', 2, 'G1')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `courses` (`course_code`, `course_name`, `professor_id`, `group_name`) 
VALUES ('DB-001', 'Bases de Données', 2, 'G1')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `courses` (`course_code`, `course_name`, `professor_id`, `group_name`) 
VALUES ('WEB-001', 'Développement Web', 2, 'G2')
ON DUPLICATE KEY UPDATE id=id;

-- Enroll students in courses
INSERT INTO `enrollments` (`course_id`, `student_id`) 
VALUES (1, 1), (1, 2), (2, 1), (2, 2), (3, 3)
ON DUPLICATE KEY UPDATE id=id;

-- Create sample sessions
INSERT INTO `sessions` (`course_id`, `session_number`, `session_date`, `topic`, `is_open`) 
VALUES (1, 1, CURDATE(), 'Introduction', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `sessions` (`course_id`, `session_number`, `session_date`, `topic`, `is_open`) 
VALUES (1, 2, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Structures de Données', 0)
ON DUPLICATE KEY UPDATE id=id;
