<?php

/**
 * Migration: Create customers table
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function up_create_customers_table() {
    global $pdo;
    $sql = "
        CREATE TABLE IF NOT EXISTS customers (
            customer_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) UNIQUE,
            address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "Table 'customers' created successfully.\n";
}

function down_create_customers_table() {
    global $pdo;
    $sql = "DROP TABLE IF EXISTS customers";
    $pdo->exec($sql);
    echo "Table 'customers' dropped successfully.\n";
}
