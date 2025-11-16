<?php

/**
 * Stored Procedure: get_all_menu_items
 * Retrieves all menu items
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_get_all_menu_items_procedure() {
    global $pdo;
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS get_all_menu_items");
    
    $sql = "
        CREATE PROCEDURE get_all_menu_items ()
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
            ORDER BY item_name;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'get_all_menu_items' created successfully.\n";
}

function drop_get_all_menu_items_procedure() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS get_all_menu_items";
    $pdo->exec($sql);
    echo "Procedure 'get_all_menu_items' dropped successfully.\n";
}

function use_get_all_menu_items() {
    global $pdo;
    $stmt = $pdo->query("CALL get_all_menu_items()");
    return $stmt->fetchAll();
}
