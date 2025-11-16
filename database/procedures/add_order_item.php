<?php

/**
 * Stored Procedure: add_order_item
 * Adds an item to an order with price snapshot and updates order total
 * Created: 2025-11-16
 */

function create_add_order_item_procedure($pdo) {
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS add_order_item");
    
    $sql = "
        CREATE PROCEDURE add_order_item (
            IN p_item_id INT,
            IN p_quantity INT
        )
        BEGIN
            DECLARE v_price DECIMAL(10,2);
            DECLARE v_order_id INT;

            -- get the last inserted order_id
            SET v_order_id = LAST_INSERT_ID();

            -- get current item price
            SELECT price INTO v_price
            FROM menu_items
            WHERE item_id = p_item_id;

            -- insert order item snapshot
            INSERT INTO order_items (order_id, item_id, quantity, unit_price)
            VALUES (v_order_id, p_item_id, p_quantity, v_price);

            -- update total of the order
            UPDATE orders
            SET total = total + (v_price * p_quantity)
            WHERE order_id = v_order_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'add_order_item' created successfully.\n";
}

function drop_add_order_item_procedure($pdo) {
    $sql = "DROP PROCEDURE IF EXISTS add_order_item";
    $pdo->exec($sql);
    echo "Procedure 'add_order_item' dropped successfully.\n";
}
