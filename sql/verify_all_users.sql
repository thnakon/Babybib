-- Verify all existing users
-- ==========================
-- This script marks all existing users as email verified
-- Run this to fix login issues for users created before the verification system

-- First, ensure the is_verified column exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0 AFTER is_active;

-- Update all existing users to be verified
UPDATE users SET is_verified = 1 WHERE is_verified = 0 OR is_verified IS NULL;

-- Check the result
SELECT id, username, email, is_verified, role FROM users;
