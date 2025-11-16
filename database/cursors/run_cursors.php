<?php

/**
 * Run All Cursors
 * Creates all cursor-based stored procedures
 */

echo "========================================\n";
echo "Creating Cursor-Based Procedures\n";
echo "========================================\n\n";

// Create process pending orders cursor
echo "1. Creating Process Pending Orders Cursor...\n";
echo "----------------------------------------\n";
require_once __DIR__ . '/process_pending_orders.php';
create_process_pending_orders_cursor();
echo "\n";

// Create customer lifetime value cursor
echo "2. Creating Customer Lifetime Value Cursor...\n";
echo "----------------------------------------\n";
require_once __DIR__ . '/customer_lifetime_value.php';
create_customer_lifetime_value_cursor();
echo "\n";

// Create low stock items cursor
echo "3. Creating Low Stock Items Cursor...\n";
echo "----------------------------------------\n";
require_once __DIR__ . '/low_stock_items.php';
create_update_low_stock_cursor();
echo "\n";

echo "========================================\n";
echo "All cursor procedures created!\n";
echo "========================================\n";
