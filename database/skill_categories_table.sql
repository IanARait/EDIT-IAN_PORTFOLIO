CREATE TABLE `skill_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `skills` ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `name`;

ALTER TABLE `skills` ADD CONSTRAINT `fk_skills_category` FOREIGN KEY (`category_id`) REFERENCES `skill_categories`(`id`) ON DELETE SET NULL;

INSERT INTO `skill_categories` (`name`, `sort_order`, `created_at`) VALUES
('Software', 1, NOW()),
('Technique', 2, NOW());

UPDATE `skills` SET `category_id` = 1 WHERE `name` IN ('Premiere Pro', 'After Effects', 'DaVinci Resolve', 'CapCut');
UPDATE `skills` SET `category_id` = 2 WHERE `name` IN ('Color Grading', 'Sound Design');
