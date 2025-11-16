<?php

/**
 * Run All Seeders
 * Executes all seeder files in order
 * Automatically runs migrations if tables don't exist
 */

echo "========================================\n";
echo "Running Database Seeders\n";
echo "========================================\n\n";

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

// Check if tables exist
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'customers'");
    $customersTableExists = $stmt->rowCount() > 0;
    
    if (!$customersTableExists) {
        echo "⚠ Tables not found. Running migrations first...\n";
        echo "========================================\n\n";
        require_once __DIR__ . '/../migrations/run_migrations.php';
        echo "\n========================================\n";
        echo "Migrations completed. Continuing with seeders...\n";
        echo "========================================\n\n";
    }
} catch (PDOException $e) {
    echo "⚠ Error checking tables. Running migrations...\n";
    echo "========================================\n\n";
    require_once __DIR__ . '/../migrations/run_migrations.php';
    echo "\n========================================\n";
    echo "Migrations completed. Continuing with seeders...\n";
    echo "========================================\n\n";
}

// Seed admin user first
echo "1. Seeding Admin User...\n";
echo "----------------------------------------\n";
require_once __DIR__ . '/seed_admin.php';
echo "\n";

// Seed customers
echo "2. Seeding Customers...\n";
echo "----------------------------------------\n";
require_once __DIR__ . '/seed_customers.php';
echo "\n";

echo "========================================\n";
echo "All seeders completed!\n";
echo "========================================\n";
