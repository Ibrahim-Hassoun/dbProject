<?php

/**
 * Stored Procedure: get_order
 * Retrieves a specific order with all its details
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_get_order_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS get_order");
    
    $sql = "
        CREATE PROCEDURE get_order (
            IN p_order_id INT
        )
        BEGIN
            SELECT 
                o.order_id,
                o.customer_id,
                c.full_name,
                c.phone,
                o.order_time,
                o.status,
                o.total,
                o.delivery_address,
                o.created_at,
                o.updated_at
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.customer_id
            WHERE o.order_id = p_order_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'get_order' created successfully.\n";
}

function drop_get_order_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS get_order";
    $pdo->exec($sql);
    echo "Procedure 'get_order' dropped successfully.\n";
}

function use_get_order($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL get_order(?)");
    $stmt->execute([$order_id]);
    return $stmt->fetch();
}
