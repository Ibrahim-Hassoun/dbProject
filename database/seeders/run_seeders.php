<?php

/**
 * Run All Seeders
 * Executes all seeder files in order
 */

echo "========================================\n";
echo "Running Database Seeders\n";
echo "========================================\n\n";

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
