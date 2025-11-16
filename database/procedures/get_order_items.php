<?php

/**
 * Stored Procedure: get_order_items
 * Retrieves all items in a specific order
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_get_order_items_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS get_order_items");
    
    $sql = "
        CREATE PROCEDURE get_order_items (
            IN p_order_id INT
        )
        BEGIN
            SELECT 
                oi.order_item_id,
                oi.order_id,
                oi.item_id,
                mi.item_name,
                oi.quantity,
                oi.unit_price,
                (oi.quantity * oi.unit_price) AS subtotal,
                oi.created_at
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE oi.order_id = p_order_id
            ORDER BY oi.created_at;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'get_order_items' created successfully.\n";
}

function drop_get_order_items_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS get_order_items";
    $pdo->exec($sql);
    echo "Procedure 'get_order_items' dropped successfully.\n";
}

function use_get_order_items($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL get_order_items(?)");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}
