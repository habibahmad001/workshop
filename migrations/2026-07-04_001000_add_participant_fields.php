-- Migration: Add organization, teaching experience, qualification, and age fields to participants table
-- Date: 2026-07-04
-- Description: Adds 4 new fields to the participants form for better user information tracking

-- Add organization field after designation
ALTER TABLE participants ADD COLUMN organization VARCHAR(255) DEFAULT NULL AFTER designation;

-- Add teaching_exp field after organization
ALTER TABLE participants ADD COLUMN teaching_exp VARCHAR(255) DEFAULT NULL AFTER organization;

-- Add qualification field after teaching_exp
ALTER TABLE participants ADD COLUMN qualification VARCHAR(255) DEFAULT NULL AFTER teaching_exp;

-- Add age field after qualification
ALTER TABLE participants ADD COLUMN age VARCHAR(50) DEFAULT NULL AFTER qualification;
