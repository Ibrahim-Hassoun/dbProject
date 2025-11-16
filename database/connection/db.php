<?php

/**
 * Database Connection
 * Singleton pattern - returns the same PDO instance on every call
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Static variable to hold the single PDO instance
static $pdo = null;

// Return existing connection if already created
if ($pdo !== null) {
    return $pdo;
}

// Create PDO instance only on first call
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

return $pdo;
