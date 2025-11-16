<?php

/**
 * Seed Menu Items
 * Creates sample menu items for the restaurant
 */

// Use global PDO connection
global $pdo;

// Realistic restaurant menu items
$menu_items = [
    // Appetizers
    ['item_name' => 'Caesar Salad', 'description' => 'Fresh romaine lettuce with parmesan cheese, croutons, and Caesar dressing', 'price' => 8.99, 'in_stock' => 50],
    ['item_name' => 'Buffalo Wings', 'description' => '8 pieces of crispy chicken wings tossed in spicy buffalo sauce', 'price' => 12.99, 'in_stock' => 40],
    ['item_name' => 'Mozzarella Sticks', 'description' => '6 pieces of breaded mozzarella cheese sticks served with marinara sauce', 'price' => 7.99, 'in_stock' => 45],
    ['item_name' => 'French Onion Soup', 'description' => 'Classic French onion soup topped with melted cheese and croutons', 'price' => 6.99, 'in_stock' => 30],
    
    // Main Courses
    ['item_name' => 'Classic Cheeseburger', 'description' => 'Angus beef patty with cheddar cheese, lettuce, tomato, and special sauce', 'price' => 14.99, 'in_stock' => 60],
    ['item_name' => 'Grilled Salmon', 'description' => 'Fresh Atlantic salmon fillet with lemon butter sauce and seasonal vegetables', 'price' => 22.99, 'in_stock' => 25],
    ['item_name' => 'Chicken Alfredo Pasta', 'description' => 'Fettuccine pasta with grilled chicken in creamy Alfredo sauce', 'price' => 16.99, 'in_stock' => 35],
    ['item_name' => 'Ribeye Steak', 'description' => '12oz USDA Prime ribeye steak with mashed potatoes and grilled asparagus', 'price' => 32.99, 'in_stock' => 20],
    ['item_name' => 'Margherita Pizza', 'description' => 'Wood-fired pizza with fresh mozzarella, tomatoes, and basil', 'price' => 13.99, 'in_stock' => 40],
    ['item_name' => 'BBQ Ribs', 'description' => 'Full rack of baby back ribs with BBQ sauce, coleslaw, and fries', 'price' => 24.99, 'in_stock' => 18],
    ['item_name' => 'Fish and Chips', 'description' => 'Beer-battered cod with crispy fries and tartar sauce', 'price' => 15.99, 'in_stock' => 30],
    ['item_name' => 'Vegetarian Stir Fry', 'description' => 'Mixed vegetables stir-fried with tofu and teriyaki sauce over rice', 'price' => 12.99, 'in_stock' => 35],
    
    // Desserts
    ['item_name' => 'Chocolate Lava Cake', 'description' => 'Warm chocolate cake with molten center, served with vanilla ice cream', 'price' => 8.99, 'in_stock' => 25],
    ['item_name' => 'New York Cheesecake', 'description' => 'Classic creamy cheesecake with graham cracker crust and berry compote', 'price' => 7.99, 'in_stock' => 30],
    ['item_name' => 'Tiramisu', 'description' => 'Traditional Italian dessert with coffee-soaked ladyfingers and mascarpone', 'price' => 8.49, 'in_stock' => 20],
    
    // Beverages
    ['item_name' => 'Fresh Lemonade', 'description' => 'Homemade lemonade with fresh squeezed lemons', 'price' => 3.99, 'in_stock' => 100],
    ['item_name' => 'Iced Tea', 'description' => 'Freshly brewed sweet or unsweetened iced tea', 'price' => 2.99, 'in_stock' => 100],
    ['item_name' => 'Cappuccino', 'description' => 'Espresso with steamed milk and foam', 'price' => 4.49, 'in_stock' => 80],
    ['item_name' => 'Craft Beer', 'description' => 'Selection of local craft beers on tap', 'price' => 6.99, 'in_stock' => 50],
    ['item_name' => 'House Wine', 'description' => 'Red or white wine by the glass', 'price' => 8.99, 'in_stock' => 40],
];

try {
    $created = 0;
    $skipped = 0;
    
    foreach ($menu_items as $item) {
        // Check if item already exists
        $stmt = $pdo->prepare("SELECT item_id FROM menu_items WHERE item_name = ?");
        $stmt->execute([$item['item_name']]);
        
        if ($stmt->fetch()) {
            echo "⊗ Menu item '{$item['item_name']}' already exists - skipped\n";
            $skipped++;
            continue;
        }
        
        // Insert menu item
        $stmt = $pdo->prepare("
            INSERT INTO menu_items (item_name, description, price, in_stock)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $item['item_name'],
            $item['description'],
            $item['price'],
            $item['in_stock']
        ]);
        
        echo "✓ Menu item created: {$item['item_name']} - \${$item['price']}\n";
        $created++;
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "  Created: $created menu items\n";
    echo "  Skipped: $skipped menu items\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "Error seeding menu items: " . $e->getMessage() . "\n";
    return;
}
