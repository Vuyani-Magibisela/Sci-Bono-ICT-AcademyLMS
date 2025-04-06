<?php
/**
 * Application Configuration
 * 
 * This file contains all the global configuration settings for the application.
 */

// Define site URL
define('SITE_URL', 'http://localhost/Sci-Bono-ICT-AcademyLMS/public'); // Change this to your actual domain in production

//Difine debug mode constants
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true); // Set to false in production
}

// Define paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
// define('ROOT_PATH', dirname(__DIR__, 2)); // Gets the parent directory (project root)
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ydp_lms_system');
define('DB_USER', 'root'); // Change this to your actual database user
define('DB_PASS', ''); // Change this to your actual database password
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'ydp_lms_system');
define('APP_VERSION', '1.0.0');

// Session settings
define('SESSION_LIFETIME', 7200); // Session lifetime in seconds (2 hours)
define('REMEMBER_ME_DAYS', 30); // Remember me cookie lifetime in days

// Email settings
define('EMAIL_FROM', 'noreply@example.com');
define('EMAIL_NAME', 'ydp_lms_system');

// Error reporting
define('DISPLAY_ERRORS', true); // Set to false in production

// Security
define('HASH_COST', 10); // Password hashing cost

// Set up error reporting based on environment
// Determine settings based on environment
$reporting_level = DISPLAY_ERRORS ? E_ALL : 0;
$display_setting = DISPLAY_ERRORS ? 1 : 0;

// Apply settings
error_reporting($reporting_level);
ini_set('display_errors', $display_setting);

// Always enable logging in production
if (!DISPLAY_ERRORS) {
    ini_set('log_errors', 1);
}

// Set error reporting level separately (no conditional)
error_reporting(DISPLAY_ERRORS ? E_ALL : 0);

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Secure in HTTPS environments
    
    // Set session lifetime
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Start the session
    session_start();
}
