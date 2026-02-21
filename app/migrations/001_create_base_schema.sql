-- OUTSINC Pathways Database Schema
-- Comprehensive schema for trauma-informed case management platform

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS consent_changes;
DROP TABLE IF EXISTS notification_queue;
DROP TABLE IF EXISTS risk_assessments;
DROP TABLE IF EXISTS hub_discussions;
DROP TABLE IF EXISTS referral_bundles;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS incident_reports;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS case_activities;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS intake_responses;
DROP TABLE IF EXISTS client_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS client_profiles;
DROP TABLE IF EXISTS security_questions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- Core Authentication Tables
-- ============================================================================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'staff', 'provider', 'admin') DEFAULT 'client',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    failed_login_attempts INT DEFAULT 0,
    account_locked_until TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE security_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default security questions
INSERT INTO security_questions (question_text, category) VALUES
('What is the name of your first pet?', 'general'),
('What city were you born in?', 'general'),
('What is your middle name?', 'general'),
('What is your favorite food?', 'general'),
('What is the name of your best friend from childhood?', 'general'),
('What street did you grow up on?', 'general'),
('What is your favorite movie?', 'general'),
('What is your mother maiden name?', 'family'),
('What was your first job?', 'work'),
('What is your favorite hobby?', 'personal');

CREATE TABLE password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    security_question_id_1 INT,
    security_question_id_2 INT,
    security_question_id_3 INT,
    answer_1_hash VARCHAR(255),
    answer_2_hash VARCHAR(255),
    answer_3_hash VARCHAR(255),
    attempts INT DEFAULT 0,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (security_question_id_1) REFERENCES security_questions(question_id),
    FOREIGN KEY (security_question_id_2) REFERENCES security_questions(question_id),
    FOREIGN KEY (security_question_id_3) REFERENCES security_questions(question_id),
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Client Profile & Consent Tables
-- ============================================================================

CREATE TABLE client_profiles (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    preferred_name VARCHAR(100),
    date_of_birth DATE,
    age_range ENUM('Under 18', '18–24', '25–59', '60 or older (senior)'),
    gender VARCHAR(50),
    pronouns VARCHAR(50),
    preferred_language ENUM('English', 'French', 'American Sign Language (ASL)') DEFAULT 'English',
    phone VARCHAR(32),
    email VARCHAR(255),
    contact_preference ENUM('SMS', 'email', 'In-person only') DEFAULT 'SMS',
    connection_method ENUM('Street outreach', '310 Division Street drop-in', 'Online form', 'Referral from another agency or person') DEFAULT 'Street outreach',
    consent_to_coordinate ENUM('full', 'partial', 'none') DEFAULT 'full',
    consent_partial_fields JSON COMMENT 'If partial consent, which fields are shareable',
    has_pet TINYINT(1) DEFAULT 0,
    pet_description VARCHAR(255),
    is_part_of_couple TINYINT(1) DEFAULT 0,
    partner_id INT,
    status ENUM('active', 'completed', 'inactive') DEFAULT 'active',
    intake_started_at TIMESTAMP NULL,
    intake_completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES client_profiles(client_id),
    INDEX idx_age_range (age_range),
    INDEX idx_consent (consent_to_coordinate),
    INDEX idx_intake_status (intake_completed_at),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consent_changes (
    consent_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    previous_consent VARCHAR(50),
    new_consent VARCHAR(50),
    changed_by INT COMMENT 'Staff member if assisted by staff',
    reason TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Assessment & Intake Response Tables
-- ============================================================================

CREATE TABLE intake_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assessment_status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
    q1_connection_method VARCHAR(100),
    q2_age_range VARCHAR(50),
    q3_preferred_language VARCHAR(50),
    q4_consent_to_coordinate VARCHAR(50),
    q5_preferred_name VARCHAR(100),
    q6_where_slept VARCHAR(100),
    q7_physical_pain VARCHAR(100),
    q8_last_meal VARCHAR(100),
    q9_feel_safe VARCHAR(50),
    q10_fleeing_violence VARCHAR(100),
    q11_belongings_safe VARCHAR(50),
    q12_water_access VARCHAR(50),
    q13_weather_clothing VARCHAR(50),
    q14_has_pet VARCHAR(50),
    q15_part_of_couple VARCHAR(50),
    q16_housing_since VARCHAR(100),
    q17_homelessness_episodes INT,
    q18_evicted TINYINT(1),
    q19_legal_barriers VARCHAR(255),
    q20_biggest_barrier VARCHAR(255),
    q21_transition_house TINYINT(1),
    q22_housing_waitlist VARCHAR(50),
    q23_photo_id VARCHAR(50),
    q24_has_sin TINYINT(1),
    q25_housing_preference VARCHAR(100),
    q26_mental_health_diagnosed VARCHAR(50),
    q27_connected_mh_service TINYINT(1),
    q28_referral_clinic TINYINT(1),
    q29_spacing_out VARCHAR(50),
    q30_head_injury TINYINT(1),
    q31_mh_medication VARCHAR(50),
    q32_crisis_plan VARCHAR(50),
    q33_loss_of_control VARCHAR(50),
    q34_peer_support TINYINT(1),
    q35_hoarding TINYINT(1),
    q36_substance_impact_housing VARCHAR(50),
    q37_raam_referral TINYINT(1),
    q38_red_path TINYINT(1),
    q39_naloxone_access VARCHAR(50),
    q40_recovery_days VARCHAR(100),
    q41_social_circle_use VARCHAR(50),
    q42_emotional_pain_substance VARCHAR(50),
    q43_support_type VARCHAR(100),
    q44_pension_income VARCHAR(50),
    q45_retirement_housing_loss TINYINT(1),
    q46_mobility VARCHAR(100),
    q47_senior_checkin TINYINT(1),
    q48_family_contact VARCHAR(255),
    q49_medical_conditions VARCHAR(255),
    q50_age_60_plus TINYINT(1),
    q51_meal_assistance TINYINT(1),
    q52_family_doctor TINYINT(1),
    q53_court_dates TINYINT(1),
    q54_life_skills TINYINT(1),
    q55_self_advocate VARCHAR(50),
    q56_forced_pressure TINYINT(1),
    q57_emergency_shelter VARCHAR(50),
    q58_daily_activity VARCHAR(50),
    q59_tax_help TINYINT(1),
    q60_30day_goal TEXT,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_status (assessment_status),
    INDEX idx_completed (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Case Management Tables
-- ============================================================================

CREATE TABLE cases (
    case_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    caseworker_id INT NOT NULL,
    case_type ENUM('New Case', 'Housing Placement', 'Housing Loss Prevention', 'Intake & Stabilization') DEFAULT 'New Case',
    status ENUM('Open', 'Closed', 'Pending') DEFAULT 'Open',
    sce_weight DECIMAL(3,2) DEFAULT 1.0 COMMENT 'Standard Caseload Equivalent for staff burnout monitoring',
    funding_program VARCHAR(100) COMMENT 'E.g., Reaching Home',
    target_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    FOREIGN KEY (caseworker_id) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_caseworker (caseworker_id),
    INDEX idx_status (status),
    INDEX idx_type (case_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE case_activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    caseworker_id INT NOT NULL,
    activity_type VARCHAR(50) COMMENT 'Face-to-Face, Phone, Advocacy, Email, etc.',
    time_spent_minutes INT,
    description TEXT,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (caseworker_id) REFERENCES users(user_id),
    INDEX idx_case (case_id),
    INDEX idx_activity_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Task & Goal Tables
-- ============================================================================

CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    case_id INT,
    assigned_by INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    task_type ENUM('auto_generated', 'manual', 'system_alert') DEFAULT 'manual',
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id),
    FOREIGN KEY (assigned_by) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    goal_type VARCHAR(100) COMMENT 'E.g., Housing, Recovery, Health, Employment',
    progress_percent INT DEFAULT 0,
    milestone_count INT DEFAULT 1,
    target_date DATE,
    status ENUM('active', 'completed', 'paused') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Achievement & Gamification Tables
-- ============================================================================

CREATE TABLE achievements (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    badge_key VARCHAR(100) NOT NULL,
    badge_label VARCHAR(100),
    badge_description TEXT,
    badge_icon VARCHAR(255),
    points INT DEFAULT 0,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_badge (badge_key),
    INDEX idx_unlocked (unlocked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Incident & Report Tables
-- ============================================================================

CREATE TABLE incident_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    reporting_staff_id INT NOT NULL,
    incident_type ENUM('safety', 'discrimination', 'service_denial', 'abuse', 'other') DEFAULT 'other',
    incident_date DATE,
    description TEXT NOT NULL,
    follow_up_notes TEXT,
    status ENUM('reported', 'under_review', 'resolved', 'escalated') DEFAULT 'reported',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE SET NULL,
    FOREIGN KEY (reporting_staff_id) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_date (incident_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Smart Tagging System
-- ============================================================================

CREATE TABLE tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(50) UNIQUE NOT NULL,
    tag_color VARCHAR(7) DEFAULT '#000000',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (tag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tags (tag_name, tag_color) VALUES
('Unsheltered', '#FF6B6B'),
('Senior', '#4ECDC4'),
('Pets', '#95E1D3'),
('No ID', '#FFA07A'),
('Mental Health', '#87CEEB'),
('Substance Use', '#DDA0DD'),
('Youth (Under 18)', '#FFB6C1'),
('Violence Survivor', '#FF69B4'),
('Chronic Homelessness', '#8B0000'),
('Housing Ready', '#32CD32');

CREATE TABLE client_tags (
    tag_id INT NOT NULL,
    client_id INT NOT NULL,
    PRIMARY KEY (tag_id, client_id),
    FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Four-Filter Hub & Privacy Tables
-- ============================================================================

CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(100) COMMENT 'view_identifiable, share_data, filter_advance, consent_change',
    user_id INT,
    client_id INT,
    filter_level INT COMMENT '1, 2, 3, or 4',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client_audit (client_id),
    INDEX idx_user_audit (user_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hub_discussions (
    discussion_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    filter_level INT DEFAULT 2 COMMENT '1-4 for current filter stage',
    de_identified_summary TEXT COMMENT 'Filter 2: de-identified data',
    agencies_invited JSON,
    consensus_reached TINYINT(1) DEFAULT 0,
    intervention_needed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_filter (filter_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Referral & Collaboration Tables
-- ============================================================================

CREATE TABLE referral_bundles (
    bundle_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    provider_id INT COMMENT 'Service provider user_id',
    referring_staff_id INT NOT NULL,
    referral_type VARCHAR(100) COMMENT 'E.g., housing, health, employment',
    status ENUM('created', 'sent', 'accepted', 'completed', 'declined') DEFAULT 'created',
    data_fields JSON COMMENT 'What fields were shared',
    provider_notes TEXT,
    appointment_details TEXT,
    shared_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(user_id),
    FOREIGN KEY (referring_staff_id) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Risk Assessment Tables
-- ============================================================================

CREATE TABLE risk_assessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    response_id INT,
    crisis_cluster_score DECIMAL(5,2) COMMENT '0-100 risk score',
    flags VARCHAR(500) COMMENT 'Comma-separated crisis triggers',
    aer_triggered TINYINT(1) DEFAULT 0 COMMENT 'Acutely Elevated Risk',
    escalation_level VARCHAR(50) COMMENT 'low, medium, high, critical',
    assessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assessor_id INT,
    FOREIGN KEY (client_id) REFERENCES client_profiles(client_id) ON DELETE CASCADE,
    FOREIGN KEY (response_id) REFERENCES intake_responses(response_id),
    FOREIGN KEY (assessor_id) REFERENCES users(user_id),
    INDEX idx_client (client_id),
    INDEX idx_aer (aer_triggered),
    INDEX idx_level (escalation_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Notification & Communication Tables
-- ============================================================================

CREATE TABLE notification_queue (
    queue_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    notification_type VARCHAR(100) COMMENT 'task_assigned, referral_accepted, urgent_alert',
    recipient_phone VARCHAR(32),
    recipient_email VARCHAR(255),
    message_body TEXT NOT NULL,
    delivery_method ENUM('sms', 'email', 'socket', 'in-app') DEFAULT 'in-app',
    status ENUM('pending', 'sent', 'failed', 'read') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Indexes for Performance
-- ============================================================================

CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_client_profiles_status ON client_profiles(status);
CREATE INDEX idx_intake_responses_client_status ON intake_responses(client_id, assessment_status);
CREATE INDEX idx_cases_caseworker_status ON cases(caseworker_id, status);
CREATE INDEX idx_tasks_client_status ON tasks(client_id, status);
CREATE INDEX idx_goals_client_status ON goals(client_id, status);

-- ============================================================================
-- End of Schema
-- ============================================================================
