-- Rollback for: 2026-05-22_000000_create_initial_schema.php

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS role_modules;
DROP TABLE IF EXISTS role_module_permissions;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS workshops;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;
