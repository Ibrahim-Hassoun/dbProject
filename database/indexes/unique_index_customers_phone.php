<?php

/**
 * Index: Unique index on customers phone
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_idx_customers_phone_unique() {
    global $pdo;
    $sql = "
        CREATE UNIQUE INDEX idx_customers_phone 
        ON customers(phone)
    ";
    
    $pdo->exec($sql);
    echo "Unique index 'idx_customers_phone' created successfully.\n";
}

function drop_idx_customers_phone_unique() {
    global $pdo;
    $sql = "DROP INDEX idx_customers_phone ON customers";
    $pdo->exec($sql);
    echo "Unique index 'idx_customers_phone' dropped successfully.\n";
}
