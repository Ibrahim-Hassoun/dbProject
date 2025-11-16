<?php

/**
 * Stored Procedure: get_menu_item
 * Retrieves a specific menu item by ID
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_get_menu_item_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS get_menu_item");
    
    $sql = "
        CREATE PROCEDURE get_menu_item (
            IN p_item_id INT
        )
        BEGIN
            SELECT 
                item_id,
                item_name,
                description,
                price,
                in_stock,
                created_at,
                updated_at
            FROM menu_items
            WHERE item_id = p_item_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'get_menu_item' created successfully.\n";
}

function drop_get_menu_item_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS get_menu_item";
    $pdo->exec($sql);
    echo "Procedure 'get_menu_item' dropped successfully.\n";
}

function use_get_menu_item($item_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL get_menu_item(?)");
    $stmt->execute([$item_id]);
    return $stmt->fetch();
}
