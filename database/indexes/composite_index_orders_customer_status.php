<?php

/**
 * Index: Composite index on orders customer_id and status
 * Created: 2025-11-16
 */

function create_idx_orders_customer_status_composite($pdo) {
    $sql = "
        CREATE INDEX idx_orders_customer_status
        ON orders(customer_id, status)
    ";
    
    $pdo->exec($sql);
    echo "Composite index 'idx_orders_customer_status' created successfully.\n";
}

function drop_idx_orders_customer_status_composite($pdo) {
    $sql = "DROP INDEX idx_orders_customer_status ON orders";
    $pdo->exec($sql);
    echo "Composite index 'idx_orders_customer_status' dropped successfully.\n";
}
