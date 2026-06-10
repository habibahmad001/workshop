-- Create initial RBAC and workshop schema
-- This migration sets up the database from scratch

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  description TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_modules (
  role_id INT NOT NULL,
  module_id INT NOT NULL,
  PRIMARY KEY(role_id, module_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS workshops (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  code VARCHAR(80) DEFAULT NULL,
  date DATE DEFAULT NULL,
  location VARCHAR(160) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  designation VARCHAR(150) DEFAULT NULL,
  workshop_id INT DEFAULT NULL,
  province VARCHAR(120) DEFAULT NULL,
  contact VARCHAR(120) DEFAULT NULL,
  email VARCHAR(180) DEFAULT NULL,
  gender VARCHAR(20) DEFAULT NULL,
  attended TINYINT(1) NOT NULL DEFAULT 0,
  photo VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL
);

-- Insert default roles
INSERT IGNORE INTO roles (name) VALUES ('Super Admin'), ('Admin'), ('Manager'), ('Lead'), ('User');

-- Insert default modules
INSERT IGNORE INTO modules (name, slug, description) VALUES
  ('Dashboard', 'dashboard', 'Main dashboard overview'),
  ('Participants', 'participants', 'Participant management'),
  ('Workshops', 'workshops', 'Workshop management'),
  ('Analytics', 'analytics', 'Reports and analytics'),
  ('Export', 'export', 'Data export'),
  ('Users', 'users', 'User management'),
  ('Roles', 'roles', 'Role management'),
  ('Modules', 'modules', 'Module permission management');

-- Assign modules to roles
-- Super Admin gets all
INSERT IGNORE INTO role_modules (role_id, module_id)
SELECT r.id, m.id FROM roles r CROSS JOIN modules m WHERE r.name = 'Super Admin';

-- Admin gets most except roles/modules management
INSERT IGNORE INTO role_modules (role_id, module_id)
SELECT r.id, m.id FROM roles r
JOIN modules m ON m.slug IN ('dashboard','participants','workshops','analytics','export','users')
WHERE r.name = 'Admin';

-- Manager gets workshop-related + analytics
INSERT IGNORE INTO role_modules (role_id, module_id)
SELECT r.id, m.id FROM roles r
JOIN modules m ON m.slug IN ('dashboard','participants','workshops','analytics')
WHERE r.name = 'Manager';

-- Lead gets dashboard and analytics only
INSERT IGNORE INTO role_modules (role_id, module_id)
SELECT r.id, m.id FROM roles r
JOIN modules m ON m.slug IN ('dashboard','analytics')
WHERE r.name = 'Lead';

-- User gets dashboard only
INSERT IGNORE INTO role_modules (role_id, module_id)
SELECT r.id, m.id FROM roles r
JOIN modules m ON m.slug = 'dashboard'
WHERE r.name = 'User';

-- Create default Super Admin user
INSERT IGNORE INTO users (name, email, password, role_id)
SELECT 'Super Admin', 'admin@unfpa.local', '$2y$10$mMwhJ49P4ay6wXNzB1nCI.paEQ4Q9yfsKYBQEH.ZFOKcH1eL1pMo2', id FROM roles WHERE name = 'Super Admin';
