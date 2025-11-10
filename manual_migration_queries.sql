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

-- Verify the changes
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'timetable' 
-- ORDER BY ORDINAL_POSITION;

