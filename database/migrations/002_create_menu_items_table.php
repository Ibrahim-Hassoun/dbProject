<?php

/**
 * Migration: Create menu_items table
 * Created: 2025-11-16
 */

function up_create_menu_items_table() {
    global $pdo;
    $sql = "
        CREATE TABLE IF NOT EXISTS menu_items (
            item_id INT AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(120) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
            in_stock INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "Table 'menu_items' created successfully.\n";
}

function down_create_menu_items_table() {
    global $pdo;
    $sql = "DROP TABLE IF EXISTS menu_items";
    $pdo->exec($sql);
    echo "Table 'menu_items' dropped successfully.\n";
}
