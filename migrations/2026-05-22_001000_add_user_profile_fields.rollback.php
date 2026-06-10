-- Rollback for: 2026-05-22_001000_add_user_profile_fields.php

DROP INDEX idx_users_status ON users;
DROP INDEX idx_users_email ON users;

ALTER TABLE users DROP COLUMN last_login;
ALTER TABLE users DROP COLUMN bio;
ALTER TABLE users DROP COLUMN status;
