-- Example migration: Add user status and profile fields
-- Usage: This demonstrates how to add new features incrementally

ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active' AFTER role_id;

ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER updated_at;

ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER bio;

CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_email ON users(email);
