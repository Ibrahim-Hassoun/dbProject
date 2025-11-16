<?php

/**
 * Seed Admin User
 * Creates the admin user with ID = 1
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE customer_id = 1");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "Admin user already exists (ID: 1)\n";
        exit;
    }
    
    // Admin credentials
    $full_name = "Admin";
    $phone = "1111111111";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $address = "Restaurant Headquarters";
    
    // Insert admin user with ID = 1
    $stmt = $pdo->prepare("
        INSERT INTO customers (customer_id, full_name, phone, password, address)
        VALUES (1, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$full_name, $phone, $password, $address]);
    
    echo "✓ Admin user created successfully\n";
    echo "  Phone: $phone\n";
    echo "  Password: admin123\n";
    echo "  Customer ID: 1\n";
    
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}
