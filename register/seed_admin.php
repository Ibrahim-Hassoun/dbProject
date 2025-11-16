<?php

/**
 * Seed Admin User
 * Creates the admin user with customer_id = 1
 */

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE customer_id = 1");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "Admin user already exists.\n";
    } else {
        // Insert admin user with customer_id = 1
        // Default admin password: 'admin123' (hashed)
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO customers (customer_id, full_name, phone, password, address)
            VALUES (1, 'Admin', '+961 70 000 000', ?, 'Admin Office')
        ");
        
        $stmt->execute([$password_hash]);
        echo "Admin user created successfully with ID = 1.\n";
        echo "Default password: admin123\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
