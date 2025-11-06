-- Production Database Migration SQL
-- Run these queries in order on your production database

-- ============================================
-- 1. Create families table
-- ============================================
CREATE TABLE IF NOT EXISTS `families` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_name` varchar(255) NOT NULL,
  `whatsapp_number` text DEFAULT NULL,
  `family_billing_link` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Add family_id to users table
-- ============================================
ALTER TABLE `users` 
ADD COLUMN `family_id` bigint(20) UNSIGNED NULL DEFAULT NULL AFTER `user_type`;

ALTER TABLE `users` 
ADD CONSTRAINT `users_family_id_foreign` 
FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE SET NULL;

-- ============================================
-- 3. Add student_type to users table
-- ============================================
ALTER TABLE `users` 
ADD COLUMN `student_type` ENUM('arabic', 'english') NULL DEFAULT NULL AFTER `user_type`;

-- Optional: Set default value for existing students (uncomment if needed)
-- UPDATE `users` SET `student_type` = 'arabic' WHERE `user_type` = 'student' AND `student_type` IS NULL;

-- ============================================
-- 4. Add salary_arabic and salary_english to users table
-- ============================================
ALTER TABLE `users` 
ADD COLUMN `salary_arabic` double NULL DEFAULT NULL AFTER `hour_price`,
ADD COLUMN `salary_english` double NULL DEFAULT NULL AFTER `salary_arabic`;

