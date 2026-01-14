-- =====================================================
-- Babybib - Database Indexes for Performance
-- =====================================================
-- Run this SQL to add performance-optimizing indexes
-- Execute after initial database setup
-- =====================================================

USE babybib_db;

-- =====================================================
-- Users Table Indexes
-- =====================================================
-- Index for email lookup (login, password reset)
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Index for username lookup
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Index for role-based queries (admin filtering)
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Index for active users
CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active);

-- Index for LIS CMU members
CREATE INDEX IF NOT EXISTS idx_users_lis_cmu ON users(is_lis_cmu);

-- Index for user creation date (reporting)
CREATE INDEX IF NOT EXISTS idx_users_created ON users(created_at);

-- Index for last login (tracking)
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login);

-- Composite index for login verification
CREATE INDEX IF NOT EXISTS idx_users_login ON users(email, is_active);

-- =====================================================
-- Bibliographies Table Indexes
-- =====================================================
-- Index for user's bibliographies
CREATE INDEX IF NOT EXISTS idx_bib_user ON bibliographies(user_id);

-- Index for project's bibliographies
CREATE INDEX IF NOT EXISTS idx_bib_project ON bibliographies(project_id);

-- Index for resource type filtering
CREATE INDEX IF NOT EXISTS idx_bib_resource_type ON bibliographies(resource_type_id);

-- Index for bibliography language
CREATE INDEX IF NOT EXISTS idx_bib_language ON bibliographies(language);

-- Index for year sorting/filtering
CREATE INDEX IF NOT EXISTS idx_bib_year ON bibliographies(year);

-- Index for author sorting
CREATE INDEX IF NOT EXISTS idx_bib_author_sort ON bibliographies(author_sort_key);

-- Index for created date (sorting, cleanup)
CREATE INDEX IF NOT EXISTS idx_bib_created ON bibliographies(created_at);

-- Composite index for user's bibliographies with project
CREATE INDEX IF NOT EXISTS idx_bib_user_project ON bibliographies(user_id, project_id);

-- Composite index for filtering and sorting
CREATE INDEX IF NOT EXISTS idx_bib_user_created ON bibliographies(user_id, created_at DESC);

-- =====================================================
-- Projects Table Indexes
-- =====================================================
-- Index for user's projects
CREATE INDEX IF NOT EXISTS idx_projects_user ON projects(user_id);

-- Index for project creation date
CREATE INDEX IF NOT EXISTS idx_projects_created ON projects(created_at);

-- =====================================================
-- Activity Logs Table Indexes
-- =====================================================
-- Index for user activity
CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_logs(user_id);

-- Index for action type
CREATE INDEX IF NOT EXISTS idx_activity_action ON activity_logs(action);

-- Index for entity filtering
CREATE INDEX IF NOT EXISTS idx_activity_entity ON activity_logs(entity_type, entity_id);

-- Index for date-based queries (log cleanup)
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_logs(created_at);

-- Composite index for user activity timeline
CREATE INDEX IF NOT EXISTS idx_activity_user_created ON activity_logs(user_id, created_at DESC);

-- =====================================================
-- Announcements Table Indexes
-- =====================================================
-- Index for active announcements
CREATE INDEX IF NOT EXISTS idx_announcements_active ON announcements(is_active);

-- Index for date range filtering
CREATE INDEX IF NOT EXISTS idx_announcements_dates ON announcements(start_date, end_date);

-- =====================================================
-- Feedback Table Indexes
-- =====================================================
-- Index for feedback status
CREATE INDEX IF NOT EXISTS idx_feedback_status ON feedback(status);

-- Index for user feedback
CREATE INDEX IF NOT EXISTS idx_feedback_user ON feedback(user_id);

-- Index for feedback date
CREATE INDEX IF NOT EXISTS idx_feedback_created ON feedback(created_at);

-- =====================================================
-- System Settings Table Indexes
-- =====================================================
-- Index for setting key lookup (already UNIQUE, but explicit)
CREATE INDEX IF NOT EXISTS idx_settings_key ON system_settings(setting_key);

-- =====================================================
-- Resource Types Table Indexes
-- =====================================================
-- Index for category filtering
CREATE INDEX IF NOT EXISTS idx_resource_category ON resource_types(category);

-- Index for active resources
CREATE INDEX IF NOT EXISTS idx_resource_active ON resource_types(is_active);

-- Index for sorting
CREATE INDEX IF NOT EXISTS idx_resource_sort ON resource_types(sort_order);

-- =====================================================
-- Email Verifications Table Indexes (if exists)
-- =====================================================
-- Run these only if email_verifications table exists

-- DROP INDEX IF EXISTS idx_email_verify_user ON email_verifications;
-- DROP INDEX IF EXISTS idx_email_verify_code ON email_verifications;
-- DROP INDEX IF EXISTS idx_email_verify_expires ON email_verifications;

-- CREATE INDEX idx_email_verify_user ON email_verifications(user_id);
-- CREATE INDEX idx_email_verify_code ON email_verifications(code);
-- CREATE INDEX idx_email_verify_expires ON email_verifications(expires_at);

-- =====================================================
-- Verify Indexes Created
-- =====================================================
-- Run this to see all indexes:
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM bibliographies;
-- SHOW INDEX FROM projects;
-- SHOW INDEX FROM activity_logs;
