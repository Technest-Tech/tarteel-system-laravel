-- SQL Queries to manually add new columns to the timetable table
-- Run these queries on your production database

-- Add series_id column after id
ALTER TABLE `timetable` 
ADD COLUMN `series_id` VARCHAR(64) NULL AFTER `id`;

-- Add color column after lesson_name
ALTER TABLE `timetable` 
ADD COLUMN `color` VARCHAR(32) NULL AFTER `lesson_name`;

-- Add index on series_id for better query performance
CREATE INDEX `timetable_series_id_idx` ON `timetable` (`series_id`);

-- Add color column to users table for teachers
ALTER TABLE `users` 
ADD COLUMN `color` VARCHAR(32) NULL AFTER `timezone`;

-- Verify the changes
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'timetable' 
-- ORDER BY ORDINAL_POSITION;

-- Verify users table changes
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'users' 
-- AND COLUMN_NAME = 'color';

