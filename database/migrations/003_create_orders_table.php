<?php

/**
 * Migration: Create orders table
 * Created: 2025-11-16
 */

function up_create_orders_table($pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            order_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending','preparing','delivering','completed','canceled') DEFAULT 'pending',
            total DECIMAL(10,2) DEFAULT 0.00,
            delivery_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id)
                REFERENCES customers(customer_id)
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $pdo->exec($sql);
    echo "Table 'orders' created successfully.\n";
}

function down_create_orders_table($pdo) {
    $sql = "DROP TABLE IF EXISTS orders";
    $pdo->exec($sql);
    echo "Table 'orders' dropped successfully.\n";
}
