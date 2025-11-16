<?php

/**
 * Index: Non-clustered index on menu_items item_name
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_idx_menu_items_name() {
    global $pdo;
    $sql = "
        CREATE INDEX idx_menu_items_name
        ON menu_items(item_name)
    ";
    
    $pdo->exec($sql);
    echo "Index 'idx_menu_items_name' created successfully.\n";
}

function drop_idx_menu_items_name() {
    global $pdo;
    $sql = "DROP INDEX idx_menu_items_name ON menu_items";
    $pdo->exec($sql);
    echo "Index 'idx_menu_items_name' dropped successfully.\n";
}
