-- =====================================================
-- Babybib Database Schema (Complete)
-- APA7 Bibliography Generator
-- Database: babybib_db
-- Version: 2.0.0 (Production Ready)
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS babybib_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE babybib_db;

-- =====================================================
-- Table: users
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    org_type ENUM('university', 'high_school', 'opportunity_school', 'primary_school', 'government', 'private_company', 'personal', 'other') DEFAULT 'personal',
    org_name VARCHAR(255) DEFAULT NULL,
    province VARCHAR(100) DEFAULT NULL,
    is_lis_cmu TINYINT(1) DEFAULT 0,
    role ENUM('user', 'admin') DEFAULT 'user',
    token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    language VARCHAR(2) DEFAULT 'th',
    is_active TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    bibliography_count INT DEFAULT 0,
    project_count INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_username (username),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active),
    INDEX idx_users_lis_cmu (is_lis_cmu),
    INDEX idx_users_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: email_verifications
-- =====================================================
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_email_verify_user (user_id),
    INDEX idx_email_verify_code (code),
    INDEX idx_email_verify_expires (expires_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: password_resets
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_reset_user (user_id),
    INDEX idx_password_reset_code (code),
    INDEX idx_password_reset_expires (expires_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: resource_types
-- =====================================================
CREATE TABLE IF NOT EXISTS resource_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-file',
    fields_config JSON NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_resource_category (category),
    INDEX idx_resource_active (is_active),
    INDEX idx_resource_sort (sort_order)
) ENGINE=InnoDB;

-- =====================================================
-- Table: projects
-- =====================================================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#8B5CF6',
    bibliography_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_projects_user (user_id),
    INDEX idx_projects_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: bibliographies
-- =====================================================
CREATE TABLE IF NOT EXISTS bibliographies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    resource_type_id INT NOT NULL,
    project_id INT DEFAULT NULL,
    data JSON NOT NULL,
    bibliography_text TEXT NOT NULL,
    citation_parenthetical TEXT DEFAULT NULL,
    citation_narrative TEXT DEFAULT NULL,
    language VARCHAR(2) DEFAULT 'th',
    author_sort_key VARCHAR(255) DEFAULT NULL,
    year INT DEFAULT NULL,
    year_suffix VARCHAR(5) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resource_type_id) REFERENCES resource_types(id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_bib_user (user_id),
    INDEX idx_bib_project (project_id),
    INDEX idx_bib_resource_type (resource_type_id),
    INDEX idx_bib_language (language),
    INDEX idx_bib_year (year),
    INDEX idx_bib_author_sort (author_sort_key),
    INDEX idx_bib_created (created_at),
    INDEX idx_bib_user_project (user_id, project_id)
) ENGINE=InnoDB;

-- =====================================================
-- Table: activity_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_entity (entity_type, entity_id),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: announcements
-- =====================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title_th VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    content_th TEXT NOT NULL,
    content_en TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_announcements_active (is_active),
    INDEX idx_announcements_dates (start_date, end_date)
) ENGINE=InnoDB;

-- =====================================================
-- Table: feedback
-- =====================================================
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'read', 'resolved') DEFAULT 'pending',
    admin_response TEXT DEFAULT NULL,
    responded_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_feedback_status (status),
    INDEX idx_feedback_user (user_id),
    INDEX idx_feedback_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- Table: system_settings
-- =====================================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key)
) ENGINE=InnoDB;

-- =====================================================
-- Insert default admin user
-- Password: Admin@123
-- =====================================================
INSERT INTO users (username, name, surname, email, password, role, is_verified, token) VALUES 
('admin', 'System', 'Administrator', 'admin@babybib.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, UUID())
ON DUPLICATE KEY UPDATE username = username;

-- =====================================================
-- Insert default system settings
-- =====================================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'Babybib', 'Website name'),
('site_title', 'Babybib', 'Website title'),
('site_description', 'ระบบสร้างบรรณานุกรม APA 7 อัตโนมัติ', 'Website description'),
('max_bibliographies_per_user', '300', 'Maximum bibliographies per user'),
('max_bibs_per_user', '300', 'Maximum bibliographies per user (alias)'),
('max_projects_per_user', '30', 'Maximum projects per user'),
('maintenance_mode', '0', 'Maintenance mode status'),
('allow_registration', '1', 'Allow new user registration'),
('default_language', 'th', 'Default language'),
('bib_lifetime_days', '730', 'Bibliography lifetime in days (0 = unlimited)')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- =====================================================
-- Insert Thai provinces
-- =====================================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('provinces', '[\"กรุงเทพมหานคร\",\"กระบี่\",\"กาญจนบุรี\",\"กาฬสินธุ์\",\"กำแพงเพชร\",\"ขอนแก่น\",\"จันทบุรี\",\"ฉะเชิงเทรา\",\"ชลบุรี\",\"ชัยนาท\",\"ชัยภูมิ\",\"ชุมพร\",\"เชียงราย\",\"เชียงใหม่\",\"ตรัง\",\"ตราด\",\"ตาก\",\"นครนายก\",\"นครปฐม\",\"นครพนม\",\"นครราชสีมา\",\"นครศรีธรรมราช\",\"นครสวรรค์\",\"นนทบุรี\",\"นราธิวาส\",\"น่าน\",\"บึงกาฬ\",\"บุรีรัมย์\",\"ปทุมธานี\",\"ประจวบคีรีขันธ์\",\"ปราจีนบุรี\",\"ปัตตานี\",\"พระนครศรีอยุธยา\",\"พังงา\",\"พัทลุง\",\"พิจิตร\",\"พิษณุโลก\",\"เพชรบุรี\",\"เพชรบูรณ์\",\"แพร่\",\"พะเยา\",\"ภูเก็ต\",\"มหาสารคาม\",\"มุกดาหาร\",\"แม่ฮ่องสอน\",\"ยโสธร\",\"ยะลา\",\"ร้อยเอ็ด\",\"ระนอง\",\"ระยอง\",\"ราชบุรี\",\"ลพบุรี\",\"ลำปาง\",\"ลำพูน\",\"เลย\",\"ศรีสะเกษ\",\"สกลนคร\",\"สงขลา\",\"สตูล\",\"สมุทรปราการ\",\"สมุทรสงคราม\",\"สมุทรสาคร\",\"สระแก้ว\",\"สระบุรี\",\"สิงห์บุรี\",\"สุโขทัย\",\"สุพรรณบุรี\",\"สุราษฎร์ธานี\",\"สุรินทร์\",\"หนองคาย\",\"หนองบัวลำภู\",\"อ่างทอง\",\"อุดรธานี\",\"อุทัยธานี\",\"อุตรดิตถ์\",\"อุบลราชธานี\",\"อำนาจเจริญ\"]', 'Thai provinces list')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
