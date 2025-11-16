<?php

/**
 * Seed Orders
 * Creates sample orders for customers
 */

// Use global PDO connection
global $pdo;

try {
    // Get customer IDs (excluding admin)
    $stmt = $pdo->query("SELECT customer_id, address FROM customers WHERE customer_id > 1");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($customers)) {
        echo "⊗ No customers found. Please seed customers first.\n";
        return;
    }
    
    // Get menu item IDs
    $stmt = $pdo->query("SELECT item_id, price FROM menu_items");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($menu_items)) {
        echo "⊗ No menu items found. Please seed menu items first.\n";
        return;
    }
    
    // Sample orders with realistic data
    $orders_data = [
        // Recent completed orders
        ['customer_idx' => 0, 'status' => 'completed', 'days_ago' => 1, 'items' => [0, 4, 15]], // Caesar Salad, Cheeseburger, Lemonade
        ['customer_idx' => 1, 'status' => 'completed', 'days_ago' => 1, 'items' => [5, 12, 18]], // Salmon, Lava Cake, Cappuccino
        ['customer_idx' => 2, 'status' => 'completed', 'days_ago' => 2, 'items' => [6, 13, 16]], // Chicken Alfredo, Cheesecake, Iced Tea
        ['customer_idx' => 0, 'status' => 'completed', 'days_ago' => 3, 'items' => [8, 1, 17]], // Pizza, Buffalo Wings, Iced Tea
        ['customer_idx' => 3, 'status' => 'completed', 'days_ago' => 4, 'items' => [7, 3, 19]], // Ribeye, French Onion Soup, Beer
        
        // Orders in progress
        ['customer_idx' => 1, 'status' => 'preparing', 'days_ago' => 0, 'items' => [9, 15, 15]], // BBQ Ribs, 2x Lemonade
        ['customer_idx' => 2, 'status' => 'pending', 'days_ago' => 0, 'items' => [10, 2, 16]], // Fish and Chips, Mozzarella Sticks, Iced Tea
        ['customer_idx' => 3, 'status' => 'delivering', 'days_ago' => 0, 'items' => [11, 14, 17]], // Vegetarian Stir Fry, Tiramisu, Cappuccino
        
        // Older completed orders
        ['customer_idx' => 0, 'status' => 'completed', 'days_ago' => 7, 'items' => [4, 1, 15]], // Cheeseburger, Wings, Lemonade
        ['customer_idx' => 1, 'status' => 'completed', 'days_ago' => 10, 'items' => [7, 0, 19]], // Ribeye, Caesar Salad, Wine
        ['customer_idx' => 2, 'status' => 'completed', 'days_ago' => 12, 'items' => [8, 12, 16]], // Pizza, Lava Cake, Iced Tea
        ['customer_idx' => 3, 'status' => 'completed', 'days_ago' => 15, 'items' => [5, 13, 17]], // Salmon, Cheesecake, Cappuccino
        
        // Canceled order
        ['customer_idx' => 0, 'status' => 'canceled', 'days_ago' => 5, 'items' => [6]], // Chicken Alfredo
    ];
    
    $created = 0;
    
    foreach ($orders_data as $order_data) {
        $customer = $customers[$order_data['customer_idx'] % count($customers)];
        
        // Calculate order time
        $order_time = date('Y-m-d H:i:s', strtotime("-{$order_data['days_ago']} days"));
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_id, order_time, status, delivery_address, total)
            VALUES (?, ?, ?, ?, 0.00)
        ");
        
        $stmt->execute([
            $customer['customer_id'],
            $order_time,
            $order_data['status'],
            $customer['address']
        ]);
        
        $order_id = $pdo->lastInsertId();
        $total = 0.00;
        
        // Add order items
        foreach ($order_data['items'] as $item_idx) {
            if (isset($menu_items[$item_idx])) {
                $menu_item = $menu_items[$item_idx];
                $quantity = 1; // Default quantity
                
                // Insert order item
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, item_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $order_id,
                    $menu_item['item_id'],
                    $quantity,
                    $menu_item['price']
                ]);
                
                $total += $menu_item['price'] * $quantity;
            }
        }
        
        // Update order total
        $stmt = $pdo->prepare("UPDATE orders SET total = ? WHERE order_id = ?");
        $stmt->execute([$total, $order_id]);
        
        echo "✓ Order created: ID $order_id | Customer {$customer['customer_id']} | Status: {$order_data['status']} | Total: $" . number_format($total, 2) . "\n";
        $created++;
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "  Created: $created orders\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "Error seeding orders: " . $e->getMessage() . "\n";
    return;
}
