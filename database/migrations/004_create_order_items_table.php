<?php

/**
 * Migration: Create order_items table
 * Created: 2025-11-16
 */

function up_create_order_items_table() {
    global $pdo;
    $sql = "
        CREATE TABLE IF NOT EXISTS order_items (
            order_item_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            item_id INT NOT NULL,
            quantity INT NOT NULL CHECK (quantity > 0),
            unit_price DECIMAL(10,2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
                REFERENCES orders(order_id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            CONSTRAINT fk_order_items_item FOREIGN KEY (item_id)
                REFERENCES menu_items(item_id)
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "Table 'order_items' created successfully.\n";
}

function down_create_order_items_table() {
    global $pdo;
    $sql = "DROP TABLE IF EXISTS order_items";
    $pdo->exec($sql);
    echo "Table 'order_items' dropped successfully.\n";
}
