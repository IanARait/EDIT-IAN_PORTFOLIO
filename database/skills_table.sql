CREATE TABLE `skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `skills` (`name`, `percentage`, `sort_order`, `created_at`) VALUES
('Premiere Pro', 95, 1, NOW()),
('After Effects', 90, 2, NOW()),
('DaVinci Resolve', 85, 3, NOW()),
('CapCut', 90, 4, NOW()),
('Color Grading', 88, 5, NOW()),
('Sound Design', 82, 6, NOW());
