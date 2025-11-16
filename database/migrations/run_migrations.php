<?php

/**
 * Run All Migrations
 * This file executes all migration files in order
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

echo "Starting migrations...\n\n";

try {
    // Migration files in order
    $migrations = [
        '001_create_customers_table.php',
        '002_create_menu_items_table.php',
        '003_create_orders_table.php',
        '004_create_order_items_table.php'
    ];
    
    foreach ($migrations as $migration) {
        $file = __DIR__ . '/' . $migration;
        
        if (file_exists($file)) {
            echo "Running migration: {$migration}\n";
            require_once $file;
            
            // Extract function name from filename
            $functionName = 'up_' . str_replace('.php', '', substr($migration, 4));
            
            if (function_exists($functionName)) {
                $functionName($pdo);
            } else {
                echo "Warning: Function {$functionName} not found in {$migration}\n";
            }
            
            echo "\n";
        } else {
            echo "Warning: Migration file {$migration} not found\n\n";
        }
    }
    
    echo "All migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
