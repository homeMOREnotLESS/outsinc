-- OUTSINC Pathways: MySQL schema (minimal starter)
-- Run in MAMP/MySQL to create required tables.

CREATE DATABASE IF NOT EXISTS outsinc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outsinc;

-- Users table: authentication + recovery
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  first_name VARCHAR(120),
  last_name VARCHAR(120),
  date_of_birth DATE,
  security_question_id INT,
  security_answer_hash VARCHAR(255),
  role ENUM('client','staff','admin','partner') DEFAULT 'client',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Security questions table (lookup)
CREATE TABLE IF NOT EXISTS security_questions (
  question_id INT AUTO_INCREMENT PRIMARY KEY,
  question_text VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO security_questions (question_id, question_text) VALUES
(1,'What is the name of your first pet?'),
(2,'What city were you born in?'),
(3,'What is your mother\'s maiden name?'),
(4,'What was the model of your first car?'),
(5,'What was your high school name?');

-- Client profiles (identity & trauma-informed fields)
CREATE TABLE IF NOT EXISTS client_profiles (
  client_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  hifis_unique_id VARCHAR(64) DEFAULT NULL,
  alias VARCHAR(120), -- preferred display name
  legal_first_name VARCHAR(120),
  legal_last_name VARCHAR(120),
  date_of_birth DATE,
  age_range ENUM('Under 18','18-24','25-59','60+') DEFAULT NULL,
  preferred_language ENUM('English','French','ASL') DEFAULT 'English',
  consent_to_coordinate ENUM('full','partial','none') DEFAULT 'partial',
  connection_method VARCHAR(128),
  contact_preferences SET('sms','email','in-person') DEFAULT 'in-person',
  email VARCHAR(255),
  phone VARCHAR(32),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Intake responses: store all 60 questions as optional fields
CREATE TABLE IF NOT EXISTS intake_responses (
  response_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- store answers as VARCHAR to allow multi-choice, free text, or coded values
  q1 VARCHAR(255), q2 VARCHAR(50), q3 VARCHAR(50), q4 VARCHAR(50), q5 VARCHAR(255),
  q6 VARCHAR(255), q7 VARCHAR(50), q8 VARCHAR(50), q9 VARCHAR(50), q10 VARCHAR(255),
  q11 VARCHAR(50), q12 VARCHAR(50), q13 VARCHAR(50), q14 VARCHAR(50), q15 VARCHAR(50),
  q16 VARCHAR(50), q17 VARCHAR(50), q18 VARCHAR(50), q19 VARCHAR(255), q20 VARCHAR(255),
  q21 VARCHAR(50), q22 VARCHAR(50), q23 VARCHAR(50), q24 VARCHAR(50), q25 VARCHAR(50),
  q26 VARCHAR(50), q27 VARCHAR(50), q28 VARCHAR(50), q29 VARCHAR(50), q30 VARCHAR(50),
  q31 VARCHAR(50), q32 VARCHAR(50), q33 VARCHAR(50), q34 VARCHAR(50), q35 VARCHAR(50),
  q36 VARCHAR(50), q37 VARCHAR(50), q38 VARCHAR(50), q39 VARCHAR(50), q40 VARCHAR(50),
  q41 VARCHAR(50), q42 VARCHAR(50), q43 VARCHAR(50), q44 VARCHAR(50), q45 VARCHAR(50),
  q46 VARCHAR(50), q47 VARCHAR(50), q48 VARCHAR(255), q49 VARCHAR(50), q50 VARCHAR(50),
  q51 VARCHAR(50), q52 VARCHAR(50), q53 VARCHAR(50), q54 VARCHAR(50), q55 VARCHAR(50),
  q56 VARCHAR(50), q57 VARCHAR(50), q58 VARCHAR(50), q59 VARCHAR(50), q60 TEXT,
  notes TEXT,
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE
);

-- Case management
CREATE TABLE IF NOT EXISTS cases (
  case_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  case_type VARCHAR(100), -- e.g., 'Housing Placement'
  status ENUM('open','closed','pending') DEFAULT 'open',
  sce_weight DECIMAL(5,2) DEFAULT 1.0,
  caseworker_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
  FOREIGN KEY (caseworker_id) REFERENCES users(user_id)
);

-- Case activities / sessions
CREATE TABLE IF NOT EXISTS case_activities (
  activity_id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  caseworker_id INT NOT NULL,
  activity_type VARCHAR(100),
  minutes_spent INT DEFAULT 0,
  description TEXT,
  activity_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
  FOREIGN KEY (caseworker_id) REFERENCES users(user_id)
);

-- Tasks (for clients and staff)
CREATE TABLE IF NOT EXISTS tasks (
  task_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT,
  assigned_to_user_id INT,
  title VARCHAR(255),
  description TEXT,
  due_date DATE DEFAULT NULL,
  is_completed TINYINT(1) DEFAULT 0,
  is_auto_generated TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to_user_id) REFERENCES users(user_id)
);

-- Achievements / badges
CREATE TABLE IF NOT EXISTS achievements (
  achievement_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  key_name VARCHAR(100),
  label VARCHAR(255),
  awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE
);

-- Tags & tagging
CREATE TABLE IF NOT EXISTS tags (
  tag_id INT AUTO_INCREMENT PRIMARY KEY,
  tag_name VARCHAR(100) NOT NULL,
  tag_color VARCHAR(7) DEFAULT '#000000'
);

CREATE TABLE IF NOT EXISTS client_tags (
  client_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (client_id, tag_id),
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE CASCADE
);

-- Incident reports
CREATE TABLE IF NOT EXISTS incident_reports (
  incident_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT,
  reporter_id INT,
  incident_type VARCHAR(100),
  description TEXT,
  occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  follow_up TEXT,
  FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE SET NULL,
  FOREIGN KEY (reporter_id) REFERENCES users(user_id)
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message VARCHAR(512),
  payload JSON DEFAULT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Minimal indexes for typical queries
CREATE INDEX idx_client_user ON client_profiles(user_id);
CREATE INDEX idx_cases_client ON cases(client_id);
CREATE INDEX idx_tasks_client ON tasks(client_id);

-- End of schema
