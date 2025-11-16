<?php

/**
 * Stored Procedure: update_order_status
 * Updates the status of an order
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_update_order_status_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS update_order_status");
    
    $sql = "
        CREATE PROCEDURE update_order_status (
            IN p_order_id INT,
            IN p_status ENUM('pending','preparing','delivering','completed','canceled')
        )
        BEGIN
            UPDATE orders
            SET status = p_status
            WHERE order_id = p_order_id;
            
            SELECT ROW_COUNT() AS affected_rows;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'update_order_status' created successfully.\n";
}

function drop_update_order_status_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS update_order_status";
    $pdo->exec($sql);
    echo "Procedure 'update_order_status' dropped successfully.\n";
}

function use_update_order_status($order_id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("CALL update_order_status(?, ?)");
    $stmt->execute([$order_id, $status]);
    $result = $stmt->fetch();
    return $result['affected_rows'] ?? 0;
}
