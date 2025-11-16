<?php

/**
 * View: daily_revenue
 * Shows daily revenue from orders that are completed, delivering, or preparing
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_daily_revenue_view() {
    global $pdo;
    // Drop view if exists
    $pdo->exec("DROP VIEW IF EXISTS daily_revenue");
    
    $sql = "
        CREATE VIEW daily_revenue AS
        SELECT 
            DATE(order_time) AS day,
            SUM(total) AS revenue
        FROM orders
        WHERE status IN ('completed', 'delivering', 'preparing')
        GROUP BY DATE(order_time)
        ORDER BY day DESC
    ";
    
    $pdo->exec($sql);
    echo "View 'daily_revenue' created successfully.\n";
}

function drop_daily_revenue_view() {
    global $pdo;
    $sql = "DROP VIEW IF EXISTS daily_revenue";
    $pdo->exec($sql);
    echo "View 'daily_revenue' dropped successfully.\n";
}
