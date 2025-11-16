<?php

/**
 * Database Connection
 * Singleton pattern - returns the same PDO instance on every call
 * Automatically creates database if it doesn't exist
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_project');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Static variable to hold the single PDO instance
static $pdo = null;

// Return existing connection if already created
if ($pdo !== null) {
    return $pdo;
}

// Create PDO instance only on first call
try {
    // First, connect without specifying database to check if it exists
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $tempPdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create database if it doesn't exist
    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                    CHARACTER SET utf8mb4 
                    COLLATE utf8mb4_unicode_ci");
    
    // Now connect to the database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Close temporary connection
    $tempPdo = null;
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

return $pdo;
