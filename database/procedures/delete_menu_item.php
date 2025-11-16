<?php

/**
 * Stored Procedure: delete_menu_item
 * Deletes a menu item
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_delete_menu_item_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS delete_menu_item");
    
    $sql = "
        CREATE PROCEDURE delete_menu_item (
            IN p_item_id INT
        )
        BEGIN
            DELETE FROM menu_items
            WHERE item_id = p_item_id;
            
            SELECT ROW_COUNT() AS affected_rows;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'delete_menu_item' created successfully.\n";
}

function drop_delete_menu_item_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS delete_menu_item";
    $pdo->exec($sql);
    echo "Procedure 'delete_menu_item' dropped successfully.\n";
}

function use_delete_menu_item($item_id) {
    global $pdo;
    $stmt = $pdo->prepare("CALL delete_menu_item(?)");
    $stmt->execute([$item_id]);
    $result = $stmt->fetch();
    return $result['affected_rows'] ?? 0;
}
