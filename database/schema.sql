-- Video Editor Portfolio CMS - Database Schema
-- MySQL 8.0+ / MariaDB 10.5+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `portfolio_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

-- Admins Table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(500) DEFAULT NULL,
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `client` VARCHAR(200) DEFAULT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `description` TEXT DEFAULT NULL,
  `video_url` VARCHAR(500) DEFAULT NULL,
  `github_url` VARCHAR(500) DEFAULT NULL,
  `video_file` VARCHAR(500) DEFAULT NULL,
  `thumbnail` VARCHAR(500) DEFAULT NULL,
  `thumbnail_url` VARCHAR(500) DEFAULT NULL,
  `year` YEAR DEFAULT NULL,
  `duration` VARCHAR(50) DEFAULT NULL,
  `software_used` VARCHAR(255) DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_project_category` (`category_id`),
  KEY `idx_project_featured` (`featured`),
  KEY `idx_project_status` (`status`),
  CONSTRAINT `fk_project_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Table
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials Table
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_name` VARCHAR(150) NOT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `review` TEXT NOT NULL,
  `avatar` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages Table
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `company` VARCHAR(200) DEFAULT NULL,
  `budget` VARCHAR(100) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_message_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

-- Default Admin (password: Admin@123)
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@portfolio.com', '$2y$12$QQrVa0iQIWzYM.cb837ymuUsleqjkvQoUsJ5Nv4RjnqqOBu1N4Vjy', 'admin');

-- Categories
INSERT INTO `categories` (`name`, `slug`, `sort_order`) VALUES
('VSL', 'vsl', 1),
('UGC', 'ugc', 2),
('Commercial', 'commercial', 3),
('YouTube', 'youtube', 4),
('TikTok', 'tiktok', 5),
('Facebook Ads', 'facebook-ads', 6),
('Podcast', 'podcast', 7),
('Motion Graphics', 'motion-graphics', 8);

-- Services
INSERT INTO `services` (`title`, `description`, `icon`, `sort_order`) VALUES
('Short Form Editing', 'Fast-paced, engaging edits for TikTok, Reels, and Shorts that capture attention in the first second.', 'bi-phone', 1),
('Long Form Editing', 'Professional editing for YouTube videos, documentaries, and brand films with narrative structure.', 'bi-film', 2),
('VSL Editing', 'High-converting Video Sales Letter editing designed to maximize watch time and drive conversions.', 'bi-megaphone', 3),
('UGC Editing', 'Authentic User-Generated Content editing that builds trust and drives engagement.', 'bi-people', 4),
('Motion Graphics', 'Custom animations, lower thirds, transitions, and visual effects that elevate your content.', 'bi-stars', 5),
('Color Grading', 'Professional color correction and cinematic grading to set the perfect mood and tone.', 'bi-palette', 6),
('Audio Editing', 'Crystal clear audio mixing, noise reduction, and sound design for professional results.', 'bi-soundwave', 7),
('Thumbnail Design', 'Eye-catching YouTube thumbnails designed to maximize click-through rates.', 'bi-image', 8);

-- Testimonials
INSERT INTO `testimonials` (`client_name`, `company`, `rating`, `review`, `sort_order`) VALUES
('Sarah Mitchell', 'TechVision Labs', 5, 'Absolutely incredible work! The editing quality transformed our YouTube channel completely. Our average view duration increased by 40% within the first month.', 1),
('James Rodriguez', 'GrowthMedia Co.', 5, 'Professional, creative, and always on deadline. The VSL edits he produced for us directly contributed to a 6-figure product launch. Highly recommended.', 2),
('Emily Chen', 'BrandFlow Inc.', 5, 'We have worked with many editors but none come close to this level of quality. Our social media engagement tripled after switching to his editing style.', 3),
('Marcus Thompson', 'Pixel Perfect Studios', 5, 'The motion graphics and animations he created for our brand campaign were absolutely stunning. True professional with an incredible eye for detail.', 4),
('Olivia Parker', 'NextLevel Marketing', 5, 'From concept to final delivery, the entire process was seamless. Our UGC ads perform significantly better with his editing. A true creative partner.', 5);

-- Projects
INSERT INTO `projects` (`title`, `client`, `category_id`, `description`, `video_url`, `year`, `duration`, `software_used`, `featured`, `status`, `views`) VALUES
('Product Launch VSL', 'TechVision Labs', 1, 'A high-converting 12-minute Video Sales Letter for a SaaS product launch. Features dynamic text animations, screen recordings, and a compelling narrative arc that drove a 15% conversion rate.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '12 min', 'Premiere Pro, After Effects', 1, 'published', 2450),
('Summer Campaign Reel', 'GrowthMedia Co.', 3, 'A 60-second commercial spot for a summer marketing campaign. Cinematic color grading, dynamic transitions, and synchronized music create an energetic feel.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '60 sec', 'Premiere Pro, DaVinci Resolve', 1, 'published', 1820),
('Brand Story Documentary', 'BrandFlow Inc.', 4, 'A 15-minute brand documentary telling the founders story. Intimate interviews, B-roll footage, and emotional pacing bring the brand narrative to life.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '15 min', 'Premiere Pro, Audition', 1, 'published', 3100),
('TikTok Viral Series', 'NextLevel Marketing', 5, 'A series of 10 TikTok videos that collectively gained over 2 million views. Fast-paced editing, trending sounds, and hook-driven openers.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '30-60 sec', 'CapCut, Premiere Pro', 0, 'published', 5200),
('Skincare UGC Ads', 'Pixel Perfect Studios', 2, 'Authentic UGC-style ads for a skincare brand. Natural transitions, text overlays, and a genuine feel that connects with the target audience.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '15-30 sec', 'CapCut, After Effects', 1, 'published', 1560),
('Facebook Ad Campaign', 'Digital Dynamics', 6, 'A series of 5 Facebook ads for a product launch campaign. Each ad optimized for different audiences with A/B tested variations.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2023, '30 sec', 'Premiere Pro', 0, 'published', 980),
('Tech Podcast Edit', 'Innovate Podcast', 7, 'Full podcast production including multi-cam editing, intro/outro creation, audio enhancement, and highlight reel for social media promotion.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '45 min', 'Premiere Pro, Audition', 0, 'published', 720),
('Brand Identity Animation', 'Creative Labs', 8, 'A 30-second animated brand identity reveal with particle effects, kinetic typography, and sound design. Used across all social channels.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '30 sec', 'After Effects', 1, 'published', 2100),
('E-commerce Product Video', 'ShopSmart', 3, 'Sleek product showcase video for an e-commerce launch. 3D-style rotations, clean backgrounds, and dynamic text overlays.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2023, '45 sec', 'After Effects, Premiere Pro', 0, 'published', 1340),
('Fitness UGC Campaign', 'FitLife Pro', 2, 'High-energy UGC-style fitness content for Instagram Reels. Quick cuts, motivational text, and trending audio integration.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2024, '15-30 sec', 'CapCut, DaVinci Resolve', 0, 'published', 2870);

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'Video Editor Portfolio', 'general'),
('site_tagline', 'Professional Video Editor & Creative Storyteller', 'general'),
('site_description', 'Helping Brands & Businesses Create High-Converting Videos', 'general'),
('logo', 'assets/images/logo.svg', 'general'),
('hero_title', 'Professional Video Editor', 'hero'),
('hero_subtitle', 'Helping Brands & Businesses Create High-Converting Videos.', 'hero'),
('hero_cta_primary', 'View Portfolio', 'hero'),
('hero_cta_secondary', 'Hire Me', 'hero'),
('about_title', 'About Me', 'about'),
('about_text', 'I am a passionate video editor with over 3 years of experience transforming raw footage into compelling visual stories. I specialize in creating high-converting video content for brands, businesses, and content creators across all platforms.', 'about'),
('experience_years', '3', 'stats'),
('total_projects', '150', 'stats'),
('total_clients', '80', 'stats'),
('videos_edited', '500', 'stats'),
('email', 'hello@portfolio.com', 'contact'),
('phone', '+1 (555) 123-4567', 'contact'),
('location', 'Los Angeles, CA', 'contact'),
('social_youtube', 'https://youtube.com/@portfolio', 'social'),
('social_instagram', 'https://instagram.com/portfolio', 'social'),
('social_twitter', 'https://twitter.com/portfolio', 'social'),
('social_linkedin', 'https://linkedin.com/in/portfolio', 'social'),
('social_tiktok', 'https://tiktok.com/@portfolio', 'social'),
('smtp_host', 'smtp.gmail.com', 'email'),
('smtp_port', '587', 'email'),
('smtp_username', '', 'email'),
('smtp_password', '', 'email'),
('smtp_encryption', 'tls', 'email'),
('footer_text', '© 2024 Portfolio. All rights reserved.', 'footer');

SET FOREIGN_KEY_CHECKS = 1;
