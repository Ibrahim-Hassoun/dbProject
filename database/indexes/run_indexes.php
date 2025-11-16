<?php

/**
 * Run All Indexes
 * This file executes all index files
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

echo "Starting index creation...\n\n";

try {
    // Index files
    $indexes = [
        'idx_customers_phone_unique.php',
        'idx_menu_items_name.php',
        'idx_orders_customer_status_composite.php'
    ];
    
    foreach ($indexes as $index) {
        $file = __DIR__ . '/' . $index;
        
        if (file_exists($file)) {
            echo "Creating index from: {$index}\n";
            require_once $file;
            
            // Extract function name from filename
            $functionName = 'create_' . str_replace('.php', '', $index);
            
            if (function_exists($functionName)) {
                $functionName($pdo);
            } else {
                echo "Warning: Function {$functionName} not found in {$index}\n";
            }
            
            echo "\n";
        } else {
            echo "Warning: Index file {$index} not found\n\n";
        }
    }
    
    echo "All indexes created successfully!\n";
    
} catch (Exception $e) {
    echo "Index creation failed: " . $e->getMessage() . "\n";
    exit(1);
}
