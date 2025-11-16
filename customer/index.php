<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login/index.html');
    exit;
}

// Redirect admin to admin panel
if ($_SESSION['customer_id'] == 1) {
    header('Location: ../admin/index.php');
    exit;
}

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get menu items
try {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE in_stock > 0 ORDER BY item_name");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by category if category column exists, otherwise show all together
    $categories = [];
    if (!empty($menu_items)) {
        $categories['Menu'] = $menu_items; // Default category since we don't have category column
    }
} catch (PDOException $e) {
    $menu_items = [];
    $categories = [];
}

// Get cart count
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Restaurant</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #f5576c;
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info span {
            color: #666;
        }
        
        .btn {
            background: #f5576c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .btn:hover {
            background: #e04556;
        }
        
        .btn-secondary {
            background: #667eea;
        }
        
        .btn-secondary:hover {
            background: #5568d3;
        }
        
        .logout-btn {
            background: #e74c3c;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .navigation {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
        }
        
        .menu-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .menu-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #f5576c;
            padding-bottom: 10px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .menu-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .item-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            min-height: 40px;
        }
        
        .item-price {
            font-size: 20px;
            color: #f5576c;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .add-to-cart {
            width: 100%;
            background: #f5576c;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .add-to-cart:hover {
            background: #e04556;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
        }
        
        .quantity-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
        
        .quantity-btn:hover {
            background: #5568d3;
        }
        
        .quantity-display {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .cart-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        
        .cart-widget:hover {
            background: #5568d3;
        }
        
        .cart-count {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Welcome to Our Restaurant</h1>
            <div class="user-info">
                <span>Hello, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="logout.php" class="logout-btn btn">Logout</a>
            </div>
        </div>
        
        <div class="navigation">
            <a href="index.php" class="btn">Menu</a>
            <a href="my_orders.php" class="btn btn-secondary">My Orders</a>
            <a href="cart.php" class="btn btn-secondary">Cart</a>
        </div>
        
        <?php if (empty($categories)): ?>
            <div class="menu-section">
                <h2>Menu</h2>
                <p>No items available at the moment. Please check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $category => $items): ?>
                <div class="menu-section">
                    <h2><?php echo htmlspecialchars(ucfirst($category)); ?></h2>
                    <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-item">
                                <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                <div class="item-description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></div>
                                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                
                                <?php
                                $item_id = $item['item_id'];
                                $in_cart = isset($_SESSION['cart'][$item_id]);
                                $quantity = $in_cart ? $_SESSION['cart'][$item_id]['quantity'] : 0;
                                ?>
                                
                                <?php if ($in_cart): ?>
                                    <form method="POST" action="quick_cart_action.php" style="margin: 0;">
                                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                        <div class="quantity-controls">
                                            <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                                            <span class="quantity-display"><?php echo $quantity; ?> in cart</span>
                                            <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="quick_cart_action.php" style="margin: 0;">
                                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="add-to-cart">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="cart-widget" onclick="location.href='cart.php'">
        🛒 Cart <span class="cart-count"><?php echo $cart_count; ?></span>
    </div>
</body>
</html>
