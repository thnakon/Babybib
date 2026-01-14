-- Add is_lis_cmu column to users table
-- สำหรับเก็บข้อมูลนักศึกษาภาควิชาบรรณารักษศาสตร์และสารสนเทศศาสตร์ มช.

ALTER TABLE users ADD COLUMN is_lis_cmu TINYINT(1) DEFAULT 0 AFTER province;

-- Index for quick filtering
CREATE INDEX idx_users_is_lis_cmu ON users(is_lis_cmu);
