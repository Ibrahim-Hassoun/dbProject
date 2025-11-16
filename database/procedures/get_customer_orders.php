<?php

/**
 * Stored Procedure: get_customer_orders
 * Retrieves all orders for a specific customer
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_get_customer_orders_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS get_customer_orders");
    
    $sql = "
        CREATE PROCEDURE get_customer_orders (
            IN p_customer_id INT
        )
        BEGIN
            SELECT 
                order_id,
                customer_id,
                order_time,
                status,
                total,
                delivery_address,
                created_at,
                updated_at
            FROM orders
            WHERE customer_id = p_customer_id
            ORDER BY order_time DESC;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'get_customer_orders' created successfully.\n";
}

function drop_get_customer_orders_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS get_customer_orders";
    $pdo->exec($sql);
    echo "Procedure 'get_customer_orders' dropped successfully.\n";
}

function use_get_customer_orders($customer_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL get_customer_orders(?)");
    $stmt->execute([$customer_id]);
    return $stmt->fetchAll();
}
