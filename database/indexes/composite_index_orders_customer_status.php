<?php

/**
 * Index: Composite index on orders customer_id and status
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_idx_orders_customer_status_composite() {
    global $pdo;
    $sql = "
        CREATE INDEX idx_orders_customer_status
        ON orders(customer_id, status)
    ";
    
    $pdo->exec($sql);
    echo "Composite index 'idx_orders_customer_status' created successfully.\n";
}

function drop_idx_orders_customer_status_composite() {
    global $pdo;
    $sql = "DROP INDEX idx_orders_customer_status ON orders";
    $pdo->exec($sql);
    echo "Composite index 'idx_orders_customer_status' dropped successfully.\n";
}
