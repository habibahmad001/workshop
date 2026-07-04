-- Rollback: Remove junction table and restore workshop_id column
-- Date: 2026-07-04
-- Description: Reverts the many-to-many relationship changes

-- Note: This rollback will preserve the primary workshop assignment
-- Participants with multiple workshops will have their first workshop restored

-- Drop junction table
DROP TABLE IF EXISTS participant_workshops;
