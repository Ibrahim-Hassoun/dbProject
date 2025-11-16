<?php

/**
 * Index: Non-clustered index on menu_items item_name
 * Created: 2025-11-16
 */

function create_idx_menu_items_name($pdo) {
    $sql = "
        CREATE INDEX idx_menu_items_name
        ON menu_items(item_name)
    ";
    
    $pdo->exec($sql);
    echo "Index 'idx_menu_items_name' created successfully.\n";
}

function drop_idx_menu_items_name($pdo) {
    $sql = "DROP INDEX idx_menu_items_name ON menu_items";
    $pdo->exec($sql);
    echo "Index 'idx_menu_items_name' dropped successfully.\n";
}
