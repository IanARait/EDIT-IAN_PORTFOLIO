<?php
/**
 * Application Configuration
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '/my-portfolio');
define('PUBLIC_URL', BASE_URL . '/public');
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('ASSETS_URL', PUBLIC_URL . '/assets');

// File paths
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('THUMBNAILS_PATH', UPLOADS_PATH . '/thumbnails');
define('PROFILES_PATH', UPLOADS_PATH . '/profiles');
define('LOGOS_PATH', UPLOADS_PATH . '/logos');
define('VIDEOS_PATH', UPLOADS_PATH . '/videos');

// Upload limits
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_VIDEO_SIZE', 100 * 1024 * 1024); // 100MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 10);
