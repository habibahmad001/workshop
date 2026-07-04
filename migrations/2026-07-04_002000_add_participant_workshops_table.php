-- Migration: Create junction table for many-to-many relationship between participants and workshops
-- Date: 2026-07-04
-- Description: Allows participants to attend multiple workshops

-- Create junction table
CREATE TABLE IF NOT EXISTS participant_workshops (
    participant_id INT NOT NULL,
    workshop_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (participant_id, workshop_id),
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrate existing workshop_id data to the new table
INSERT INTO participant_workshops (participant_id, workshop_id)
SELECT id, workshop_id FROM participants WHERE workshop_id IS NOT NULL;
