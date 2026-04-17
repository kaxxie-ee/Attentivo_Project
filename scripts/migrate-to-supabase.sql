-- Attentivo Database Migration to Supabase PostgreSQL
-- This script creates all tables and imports data from existing MySQL database

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Drop existing tables if they exist (for clean migration)
DROP TABLE IF EXISTS mcq_responses CASCADE;
DROP TABLE IF EXISTS mcq_questions CASCADE;
DROP TABLE IF EXISTS attention_scores CASCADE;
DROP TABLE IF EXISTS class_sessions CASCADE;
DROP TABLE IF EXISTS class_participants CASCADE;
DROP TABLE IF EXISTS classes CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Create users table
CREATE TABLE users (
  user_id SERIAL PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL CHECK (role IN ('teacher', 'learner')),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create classes table
CREATE TABLE classes (
  class_id SERIAL PRIMARY KEY,
  teacher_id INTEGER NOT NULL,
  class_name VARCHAR(100) NOT NULL,
  class_code VARCHAR(20) NOT NULL UNIQUE,
  description TEXT,
  start_time TIMESTAMP,
  end_time TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_classes_teacher FOREIGN KEY (teacher_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- Create class_participants table
CREATE TABLE class_participants (
  participant_id SERIAL PRIMARY KEY,
  class_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_participant_class FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE CASCADE,
  CONSTRAINT fk_participant_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- Create class_sessions table
CREATE TABLE class_sessions (
  session_id SERIAL PRIMARY KEY,
  class_id INTEGER NOT NULL,
  teacher_id INTEGER NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE', 'ENDED')),
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP,
  CONSTRAINT fk_session_class FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE CASCADE,
  CONSTRAINT fk_session_teacher FOREIGN KEY (teacher_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- Create attention_scores table
CREATE TABLE attention_scores (
  score_id SERIAL PRIMARY KEY,
  class_id INTEGER NOT NULL,
  session_id INTEGER NOT NULL,
  score DECIMAL(5,2) NOT NULL,
  level VARCHAR(20) NOT NULL CHECK (level IN ('High', 'Medium', 'Low')),
  computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  user_id INTEGER,
  CONSTRAINT fk_attention_class FOREIGN KEY (class_id) REFERENCES classes (class_id),
  CONSTRAINT fk_attention_session FOREIGN KEY (session_id) REFERENCES class_sessions (session_id) ON DELETE CASCADE,
  CONSTRAINT fk_attention_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- Create mcq_questions table
CREATE TABLE mcq_questions (
  question_id SERIAL PRIMARY KEY,
  class_id INTEGER NOT NULL,
  session_id INTEGER,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) NOT NULL,
  option_d VARCHAR(255) NOT NULL,
  correct_option CHAR(1) NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mcq_class FOREIGN KEY (class_id) REFERENCES classes (class_id) ON DELETE CASCADE,
  CONSTRAINT fk_mcq_session FOREIGN KEY (session_id) REFERENCES class_sessions (session_id) ON DELETE SET NULL,
  CONSTRAINT fk_mcq_creator FOREIGN KEY (created_by) REFERENCES users (user_id)
);

-- Create mcq_responses table
CREATE TABLE mcq_responses (
  response_id SERIAL PRIMARY KEY,
  question_id INTEGER NOT NULL,
  session_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  selected_option CHAR(1),
  is_correct BOOLEAN,
  response_time_ms INTEGER,
  answered BOOLEAN DEFAULT TRUE,
  responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mcq_response_question FOREIGN KEY (question_id) REFERENCES mcq_questions (question_id),
  CONSTRAINT fk_mcq_response_session FOREIGN KEY (session_id) REFERENCES class_sessions (session_id) ON DELETE CASCADE,
  CONSTRAINT fk_mcq_response_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_classes_teacher_id ON classes(teacher_id);
CREATE INDEX idx_classes_code ON classes(class_code);
CREATE INDEX idx_class_participants_user_id ON class_participants(user_id);
CREATE INDEX idx_class_participants_class_id ON class_participants(class_id);
CREATE INDEX idx_class_sessions_class_id ON class_sessions(class_id);
CREATE INDEX idx_class_sessions_teacher_id ON class_sessions(teacher_id);
CREATE INDEX idx_attention_scores_user_id ON attention_scores(user_id);
CREATE INDEX idx_attention_scores_session_id ON attention_scores(session_id);
CREATE INDEX idx_mcq_questions_class_id ON mcq_questions(class_id);
CREATE INDEX idx_mcq_questions_created_by ON mcq_questions(created_by);
CREATE INDEX idx_mcq_responses_user_id ON mcq_responses(user_id);
CREATE INDEX idx_mcq_responses_question_id ON mcq_responses(question_id);

-- Insert data into users table
INSERT INTO users (user_id, full_name, email, password, role, created_at) VALUES
(1, 'Juan Dela Cruz', 'teacher@example.com', '$2y$10$X/xc6Z8.nc0bNM4h9jFpqO4d.ddmBmJ564NfAUIRrLssJPmG4oIqi', 'teacher', '2025-12-31 00:32:44'),
(2, 'Maria Santos', 'learner@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'learner', '2025-12-31 00:33:00'),
(4, 'Francis Linong', 'linong@gmail.com', '$2y$10$PU1K7YLDadbChDdoXTjeXOpf0hLOpcHm1Pl3xNwITkCHSEH25n5Zy', 'learner', '2026-01-18 08:26:01'),
(6, 'Katrina Pedigal', 'pedrigalkatrina1@gmail.com', '$2y$10$V46AV2EGxjVtwhB3mb6hUemw0.tTHjhc/6WJfKqaVBW3tZsv8l28y', 'learner', '2026-01-29 01:11:57'),
(7, 'Christine Joy Matas', 'christine@gmail.com', '$2y$10$dIlteTolAwIYkBSP1XHVIua2v64BhuvkiyUmHMEi1k8/7cfSg2KM2', 'learner', '2026-02-18 03:19:59');

-- Insert data into classes table
INSERT INTO classes (class_id, teacher_id, class_name, class_code, description, start_time, end_time, created_at) VALUES
(1, 1, 'Introduction to Computer Science', 'FB5227', 'Sample live class for capstone testing', '2025-12-31 00:47:09', '2025-12-31 01:47:09', '2025-12-31 00:47:09'),
(2, 1, 'Programming 3', '2CC2FF', 'About programming 3', NULL, NULL, '2026-03-07 22:56:27');

-- Insert data into class_participants table
INSERT INTO class_participants (participant_id, class_id, user_id, joined_at) VALUES
(1, 1, 2, '2025-12-31 00:47:25'),
(2, 1, 6, '2026-02-02 08:54:08'),
(3, 2, 6, '2026-03-07 23:03:55');

-- Insert data into class_sessions table
INSERT INTO class_sessions (session_id, class_id, teacher_id, status, started_at, ended_at) VALUES
(1, 1, 1, 'ENDED', '2026-01-17 04:33:12', NULL),
(2, 1, 1, 'ENDED', '2026-01-17 21:58:05', '2026-01-17 22:00:41'),
(3, 1, 1, 'ENDED', '2026-02-13 07:36:37', '2026-02-13 07:37:56'),
(4, 1, 1, 'ENDED', '2026-02-13 07:45:11', '2026-02-13 07:45:27'),
(5, 1, 1, 'ENDED', '2026-02-13 08:07:05', '2026-02-13 08:29:25'),
(6, 1, 1, 'ENDED', '2026-02-13 08:33:16', '2026-02-13 08:50:30'),
(7, 1, 1, 'ENDED', '2026-02-13 08:56:05', '2026-02-13 09:10:36'),
(8, 1, 1, 'ENDED', '2026-02-13 09:28:21', '2026-02-13 23:46:35'),
(9, 1, 1, 'ENDED', '2026-02-14 01:15:28', '2026-02-14 01:17:29'),
(10, 1, 1, 'ENDED', '2026-03-06 21:50:32', '2026-03-06 21:50:56'),
(11, 1, 1, 'ENDED', '2026-03-06 22:22:55', '2026-03-06 22:29:01'),
(12, 2, 1, 'ENDED', '2026-03-25 06:27:22', '2026-03-25 06:30:48'),
(13, 2, 1, 'ACTIVE', '2026-03-30 21:01:47', NULL);

-- Insert data into attention_scores table
INSERT INTO attention_scores (score_id, class_id, session_id, score, level, computed_at, user_id) VALUES
(1, 1, 1, 85.00, 'High', '2025-12-31 00:48:51', 2),
(2, 1, 3, 0.00, 'Low', '2026-02-13 07:37:56', 2),
(3, 1, 3, 0.00, 'Low', '2026-02-13 07:37:56', 6),
(4, 1, 4, 0.00, 'Low', '2026-02-13 07:45:27', 2),
(5, 1, 4, 0.00, 'Low', '2026-02-13 07:45:27', 6),
(6, 1, 5, 0.00, 'Low', '2026-02-13 08:29:25', 2),
(7, 1, 5, 0.00, 'Low', '2026-02-13 08:29:25', 6),
(8, 1, 6, 0.00, 'Low', '2026-02-13 08:50:30', 2),
(9, 1, 6, 0.00, 'Low', '2026-02-13 08:50:30', 6),
(10, 1, 7, 0.00, 'Low', '2026-02-13 09:10:36', 2),
(11, 1, 7, 100.00, 'High', '2026-02-13 09:10:36', 6),
(12, 1, 8, 0.00, 'Low', '2026-02-13 23:46:35', 2),
(13, 1, 8, 0.00, 'Low', '2026-02-13 23:46:35', 6),
(14, 1, 9, 0.00, 'Low', '2026-02-14 01:17:30', 2),
(15, 1, 9, 100.00, 'High', '2026-02-14 01:17:30', 6),
(16, 1, 10, 0.00, 'Low', '2026-03-06 21:50:56', 2),
(17, 1, 10, 0.00, 'Low', '2026-03-06 21:50:56', 6),
(18, 1, 11, 0.00, 'Low', '2026-03-06 22:29:01', 2),
(19, 1, 11, 100.00, 'High', '2026-03-06 22:29:01', 6);

-- Insert data into mcq_questions table
INSERT INTO mcq_questions (question_id, class_id, session_id, question_text, option_a, option_b, option_c, option_d, correct_option, created_by, created_at) VALUES
(1, 1, NULL, 'What does CPU stand for?', 'Central Processing Unit', 'Computer Personal Unit', 'Central Program Utility', 'Control Processing User', 'A', 1, '2025-12-31 00:48:25'),
(2, 1, NULL, 'Maganda ba ang admin ng system na ''ito?', 'Yes', 'Absolutely', 'Agree', 'Legit', 'D', 1, '2026-03-06 22:08:56'),
(3, 1, NULL, 'Panget ba si Argie Pilar?', 'Oo', 'Sobra', 'Super', 'Iw. kadiri', 'D', 1, '2026-03-06 22:10:29');

-- Insert data into mcq_responses table
INSERT INTO mcq_responses (response_id, question_id, session_id, user_id, selected_option, is_correct, response_time_ms, answered, responded_at) VALUES
(1, 1, 1, 2, 'A', TRUE, 4500, TRUE, '2025-12-31 00:48:41'),
(2, 1, 7, 6, 'A', TRUE, NULL, TRUE, '2026-02-13 09:09:22'),
(3, 1, 9, 6, 'A', TRUE, NULL, TRUE, '2026-02-14 01:16:57'),
(4, 1, 11, 6, 'B', FALSE, NULL, TRUE, '2026-03-06 22:27:56'),
(5, 3, 11, 6, 'C', FALSE, NULL, TRUE, '2026-03-06 22:28:26'),
(6, 2, 11, 6, 'A', FALSE, NULL, TRUE, '2026-03-06 22:28:53');

-- Set sequences to correct values
SELECT setval('users_user_id_seq', 8);
SELECT setval('classes_class_id_seq', 3);
SELECT setval('class_participants_participant_id_seq', 4);
SELECT setval('class_sessions_session_id_seq', 14);
SELECT setval('attention_scores_score_id_seq', 20);
SELECT setval('mcq_questions_question_id_seq', 4);
SELECT setval('mcq_responses_response_id_seq', 7);
