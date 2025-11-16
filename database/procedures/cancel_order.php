<?php

/**
 * Stored Procedure: cancel_order
 * Cancels an order
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_cancel_order_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS cancel_order");
    
    $sql = "
        CREATE PROCEDURE cancel_order (
            IN p_order_id INT
        )
        BEGIN
            UPDATE orders
            SET status = 'canceled'
            WHERE order_id = p_order_id
            AND status NOT IN ('completed', 'canceled');
            
            SELECT ROW_COUNT() AS affected_rows;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'cancel_order' created successfully.\n";
}

function drop_cancel_order_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS cancel_order";
    $pdo->exec($sql);
    echo "Procedure 'cancel_order' dropped successfully.\n";
}

function use_cancel_order($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL cancel_order(?)");
    $stmt->execute([$order_id]);
    $result = $stmt->fetch();
    return $result['affected_rows'] ?? 0;
}
