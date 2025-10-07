<?php
// config.php - Template for environment variables
session_start();

// Database configuration from environment
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'onenetwo_memberportal');
define('DB_USER', getenv('DB_USER') ?: 'onenetwo_memberportal');
define('DB_PASS', getenv('DB_PASS') ?: 'Anjina@1985');

// App configuration
define('APP_NAME', 'MEMBERPORTAL');
define('BASE_URL', getenv('BASE_URL') ?: 'https://membership.onenetwork.se/');
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (ENVIRONMENT === 'development') {
        die("Connection failed: " . $e->getMessage());
    } else {
        die("System maintenance in progress. Please try again later.");
    }
}

// Include functions
require_once 'functions.php';
?>