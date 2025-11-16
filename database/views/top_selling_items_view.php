<?php

/**
 * View: top_selling_items
 * Shows menu items ranked by total quantity sold
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_top_selling_items_view() {
    global $pdo;
    // Drop view if exists
    $pdo->exec("DROP VIEW IF EXISTS top_selling_items");
    
    $sql = "
        CREATE VIEW top_selling_items AS
        SELECT 
            mi.item_id,
            mi.item_name,
            SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN menu_items mi ON mi.item_id = oi.item_id
        GROUP BY mi.item_id, mi.item_name
        ORDER BY total_sold DESC
    ";
    
    $pdo->exec($sql);
    echo "View 'top_selling_items' created successfully.\n";
}

function drop_top_selling_items_view() {
    global $pdo;
    $sql = "DROP VIEW IF EXISTS top_selling_items";
    $pdo->exec($sql);
    echo "View 'top_selling_items' dropped successfully.\n";
}
