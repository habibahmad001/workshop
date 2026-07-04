-- Rollback: Remove organization, teaching experience, qualification, and age fields from participants table
-- Date: 2026-07-04
-- Description: Removes the 4 fields added in the migration above

-- Remove age field
ALTER TABLE participants DROP COLUMN age;

-- Remove qualification field
ALTER TABLE participants DROP COLUMN qualification;

-- Remove teaching_exp field
ALTER TABLE participants DROP COLUMN teaching_exp;

-- Remove organization field
ALTER TABLE participants DROP COLUMN organization;
