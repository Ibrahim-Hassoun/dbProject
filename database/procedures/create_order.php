<?php

/**
 * Stored Procedure: create_order
 * Creates a new order for a customer
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_create_order_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS create_order");
    
    $sql = "
        CREATE PROCEDURE create_order (
            IN p_customer_id INT,
            IN p_delivery_address TEXT,
            IN p_status ENUM('pending','preparing','delivering','completed','canceled')
        )
        BEGIN
            INSERT INTO orders (customer_id, delivery_address, status)
            VALUES (p_customer_id, p_delivery_address, p_status);
            
            SELECT LAST_INSERT_ID() AS order_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'create_order' created successfully.\n";
}

function drop_create_order_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS create_order";
    $pdo->exec($sql);
    echo "Procedure 'create_order' dropped successfully.\n";
}

function use_create_order($customer_id, $delivery_address, $status = 'pending') {
    global $pdo;
    $stmt = $pdo->prepare("CALL create_order(?, ?, ?)");
    $stmt->execute([$customer_id, $delivery_address, $status]);
    $result = $stmt->fetch();
    return $result['order_id'] ?? null;
}
