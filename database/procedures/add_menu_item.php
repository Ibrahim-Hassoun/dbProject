<?php

/**
 * Stored Procedure: add_menu_item
 * Adds a new item to the menu
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_add_menu_item_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS add_menu_item");
    
    $sql = "
        CREATE PROCEDURE add_menu_item (
            IN p_item_name VARCHAR(120),
            IN p_description TEXT,
            IN p_price DECIMAL(10,2),
            IN p_in_stock INT
        )
        BEGIN
            INSERT INTO menu_items (item_name, description, price, in_stock)
            VALUES (p_item_name, p_description, p_price, p_in_stock);
            
            SELECT LAST_INSERT_ID() AS item_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'add_menu_item' created successfully.\n";
}

function drop_add_menu_item_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS add_menu_item";
    $pdo->exec($sql);
    echo "Procedure 'add_menu_item' dropped successfully.\n";
}

function use_add_menu_item($item_name, $description, $price, $in_stock) {
    global $pdo;
    $stmt = $pdo->prepare("CALL add_menu_item(?, ?, ?, ?)");
    $stmt->execute([$item_name, $description, $price, $in_stock]);
    $result = $stmt->fetch();
    return $result['item_id'] ?? null;
}
