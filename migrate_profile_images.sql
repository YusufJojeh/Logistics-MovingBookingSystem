-- Migration script to add profile_image column to users table
-- Run this script if you have an existing database without the profile_image column

-- Add profile_image column to users table
ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER company_name;

-- Update existing users to have NULL profile_image (they will show initials by default)
UPDATE users SET profile_image = NULL WHERE profile_image IS NULL;

-- Verify the column was added successfully
DESCRIBE users;
