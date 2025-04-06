<?php
/**
 * Application Bootstrap
 * 
 * This file initializes the application by loading all required configurations
 * and setting up the environment.
 */

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Autoload classes
spl_autoload_register(function ($className) {
    // Convert namespace to file path
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    
    // Define the base directories to look for classes
    $directories = [
        ROOT_PATH . DIRECTORY_SEPARATOR,
    ];
    
    // Check each directory
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Set up error handling
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logPath = ROOT_PATH . '/logs';
    if (!is_dir($logPath)) {
        mkdir($logPath, 0755, true);
    }
    
    $logFile = $logPath . '/error.log';
    $date = date('Y-m-d H:i:s');
    $message = "[{$date}] Error ({$errno}): {$errstr} in {$errfile} on line {$errline}" . PHP_EOL;
    
    // Log the error
    error_log($message, 3, $logFile);
    
    // In production, don't display errors to the user
    if (!DISPLAY_ERRORS && $errno != E_NOTICE && $errno != E_WARNING) {
        // Redirect to error page for serious errors
        header('Location: /error/500');
        exit;
    }
    
    // Let PHP handle the error according to its settings
    return false;
}

// Set custom error handler
set_error_handler('customErrorHandler', E_ALL);

// Set exception handler
set_exception_handler(function ($exception) {
    $logPath = ROOT_PATH . '/logs';
    if (!is_dir($logPath)) {
        mkdir($logPath, 0755, true);
    }
    
    $logFile = $logPath . '/exceptions.log';
    $date = date('Y-m-d H:i:s');
    $message = "[{$date}] Exception: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}" . PHP_EOL;
    $message .= "Stack Trace: " . $exception->getTraceAsString() . PHP_EOL;
    $message .= "--------------------" . PHP_EOL;
    
    // Log the exception
    error_log($message, 3, $logFile);
    
    // In production, don't display errors to the user
    if (!DISPLAY_ERRORS) {
        // Redirect to error page
        header('Location: /error/500');
        exit;
    } else {
        // In development, show detailed error information
        echo "<h1>Exception Occurred</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($exception->getLine()) . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    }
    
    exit;
});

// Function to get current user if logged in
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        $userModel = new \App\Models\User();
        return $userModel->findById($_SESSION['user_id']);
    }
    return null;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect
function redirect($path) {
    header("Location: $path");
    exit;
}

// Function to get base URL
function baseUrl($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

// Function to generate CSRF token
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to get settings
function getSetting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        // Load settings from database
        try {
            $settings = db_fetch_all("SELECT * FROM settings");
            // Convert to key-value format
            $settings = array_column($settings, 'value', 'key');
        } catch (\Exception $e) {
            $settings = [];
        }
    }
    
    return $settings[$key] ?? $default;
}