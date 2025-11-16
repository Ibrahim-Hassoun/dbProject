<?php

/**
 * Seed Customer Users
 * Creates 4 sample customers
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

// Sample customers data
$customers = [
    [
        'full_name' => 'John Smith',
        'phone' => '5551234567',
        'password' => 'customer123',
        'address' => '123 Main St, New York, NY 10001'
    ],
    [
        'full_name' => 'Sarah Johnson',
        'phone' => '5552345678',
        'password' => 'customer123',
        'address' => '456 Oak Ave, Los Angeles, CA 90001'
    ],
    [
        'full_name' => 'Michael Brown',
        'phone' => '5553456789',
        'password' => 'customer123',
        'address' => '789 Pine Rd, Chicago, IL 60601'
    ],
    [
        'full_name' => 'Emily Davis',
        'phone' => '5554567890',
        'password' => 'customer123',
        'address' => '321 Elm St, Houston, TX 77001'
    ]
];

try {
    $created = 0;
    $skipped = 0;
    
    foreach ($customers as $customer) {
        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE phone = ?");
        $stmt->execute([$customer['phone']]);
        
        if ($stmt->fetch()) {
            echo "⊗ Customer with phone {$customer['phone']} already exists - skipped\n";
            $skipped++;
            continue;
        }
        
        // Hash password
        $password_hash = password_hash($customer['password'], PASSWORD_DEFAULT);
        
        // Insert customer
        $stmt = $pdo->prepare("
            INSERT INTO customers (full_name, phone, password, address)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $customer['full_name'],
            $customer['phone'],
            $password_hash,
            $customer['address']
        ]);
        
        $customer_id = $pdo->lastInsertId();
        
        echo "✓ Customer created: {$customer['full_name']} (ID: $customer_id)\n";
        echo "  Phone: {$customer['phone']}\n";
        echo "  Password: {$customer['password']}\n";
        $created++;
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "  Created: $created customers\n";
    echo "  Skipped: $skipped customers\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "Error seeding customers: " . $e->getMessage() . "\n";
    exit(1);
}
