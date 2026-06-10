# Database Migration System

This is a simple but powerful database migration system for managing schema changes incrementally and maintaining rollback capabilities.

## How It Works

1. **Migrations Table**: Tracks which migrations have been executed
2. **Migration Files**: Named with timestamp for ordering (e.g., `2026-05-22_000000_description.php`)
3. **Rollback Files**: Paired rollback scripts for reverting changes (e.g., `2026-05-22_000000_description.rollback.php`)

## File Naming Convention

```
YYYY-MM-DD_HHmmss_description.php
YYYY-MM-DD_HHmmss_description.rollback.php
```

**Examples:**
- `2026-05-22_000000_create_initial_schema.php`
- `2026-05-22_001000_add_user_profile_fields.php`
- `2026-06-15_143000_add_audit_logging.php`

## Usage

### Run all pending migrations
```bash
php migrate.php
```
or
```bash
php migrate.php run
```

### Check migration status
```bash
php migrate.php status
```

Output:
```
Migration Status:
------------------------------------------------------------
[✓] 2026-05-22_000000_create_initial_schema.php
[✓] 2026-05-22_001000_add_user_profile_fields.php
[⊗] 2026-06-15_143000_add_audit_logging.php

Pending: 1
```

### Rollback the last migration
```bash
php migrate.php rollback
```

## Creating a New Migration

1. Create a `.php` file in the `migrations/` directory with the timestamp naming convention
2. Write SQL code (can use SQL directly or SQL statements)
3. Create a corresponding `.rollback.php` file with the reverse operations

### Migration File Template

**File: `migrations/2026-06-20_100000_add_example_table.php`**
```sql
-- Add example table
CREATE TABLE example (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

**File: `migrations/2026-06-20_100000_add_example_table.rollback.php`**
```sql
-- Rollback: Drop example table
DROP TABLE IF EXISTS example;
```

## Important Notes

- **Timestamps must be unique** for each migration
- **Files are sorted alphabetically** so naming is crucial for execution order
- **Test migrations** on a development database first
- **Always create rollback files** for production safety
- **Migration files are PHP** but contain SQL; the runner executes them with `$pdo->exec()`

## Migration History

Track your migrations in the database:

```sql
SELECT migration, executed_at FROM migrations ORDER BY executed_at DESC;
```

## Advanced: Conditional Migrations

You can add logic to migrations:

```sql
-- migrations/2026-06-20_110000_add_column_if_needed.php
ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER role_id;
```

The migration system uses `$pdo->exec()` so any valid SQL works.

## Troubleshooting

### "No pending migrations"
All migrations have been run. Check status with `php migrate.php status`.

### "No rollback script found"
Create the corresponding `.rollback.php` file. For example:
- Migration: `2026-06-20_110000_add_feature.php`
- Rollback: `2026-06-20_110000_add_feature.rollback.php`

### Foreign key constraint errors
If you see foreign key errors during migration:
1. Check your migration order (alphabetical by filename)
2. Consider disabling foreign keys temporarily in the migration:
   ```sql
   SET FOREIGN_KEY_CHECKS = 0;
   -- Your migration code
   SET FOREIGN_KEY_CHECKS = 1;
   ```

## Best Practices

1. ✅ Keep migrations small and focused
2. ✅ Always include a rollback file
3. ✅ Test migrations thoroughly before deploying
4. ✅ Use descriptive filenames
5. ✅ Document complex migrations with comments
6. ✅ Never modify migration files after they're executed
7. ✅ Keep migrations idempotent (use `IF NOT EXISTS`, etc.)

## Production Deployment

1. Take a database backup
2. Run `php migrate.php status` to see pending migrations
3. Review migration changes
4. Run `php migrate.php` to apply
5. Verify with `php migrate.php status`
6. Monitor application logs for any issues
