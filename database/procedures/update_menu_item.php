<?php

/**
 * Stored Procedure: update_menu_item
 * Updates an existing menu item
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_update_menu_item_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS update_menu_item");
    
    $sql = "
        CREATE PROCEDURE update_menu_item (
            IN p_item_id INT,
            IN p_item_name VARCHAR(120),
            IN p_description TEXT,
            IN p_price DECIMAL(10,2),
            IN p_in_stock INT
        )
        BEGIN
            UPDATE menu_items
            SET 
                item_name = p_item_name,
                description = p_description,
                price = p_price,
                in_stock = p_in_stock
            WHERE item_id = p_item_id;
            
            SELECT ROW_COUNT() AS affected_rows;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'update_menu_item' created successfully.\n";
}

function drop_update_menu_item_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS update_menu_item";
    $pdo->exec($sql);
    echo "Procedure 'update_menu_item' dropped successfully.\n";
}

function use_update_menu_item($item_id, $item_name, $description, $price, $in_stock) {
    global $pdo;
    $stmt = $pdo->prepare("CALL update_menu_item(?, ?, ?, ?, ?)");
    $stmt->execute([$item_id, $item_name, $description, $price, $in_stock]);
    $result = $stmt->fetch();
    return $result['affected_rows'] ?? 0;
}
