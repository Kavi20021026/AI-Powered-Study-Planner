-- database.sql
--
-- MySQL schema for the AI-Powered Study Planner
-- Suggested database name: study_planner_db

CREATE DATABASE IF NOT EXISTS study_planner_db;
USE study_planner_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    daily_study_hours DECIMAL(4,1) NOT NULL DEFAULT 4.0,
    browser_reminders_enabled TINYINT(1) NOT NULL DEFAULT 0,
    reminder_time TIME NOT NULL DEFAULT '18:00:00',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject_name VARCHAR(120) NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    difficulty_value TINYINT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_subjects_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS study_plan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    days_left INT UNSIGNED NOT NULL,
    priority_score DECIMAL(10, 2) NOT NULL,
    recommended_hours DECIMAL(5, 2) NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_study_plan_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_study_plan_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE CASCADE
);

-- Optional seed example:
-- INSERT INTO users (full_name, email, password_hash)
-- VALUES ('Demo User', 'demo@example.com', '$2y$10$examplehashgoeshere');
