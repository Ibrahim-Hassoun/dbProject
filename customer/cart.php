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

// Get cart items with details
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $item_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
    
    try {
        $stmt = $pdo->prepare("SELECT item_id, item_name, description, price FROM menu_items WHERE item_id IN ($placeholders)");
        $stmt->execute($item_ids);
        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($menu_items as $item) {
            $item_id = $item['item_id'];
            $quantity = $_SESSION['cart'][$item_id]['quantity'];
            $subtotal = $item['price'] * $quantity;
            
            $cart_items[] = [
                'item_id' => $item_id,
                'item_name' => $item['item_name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            
            $total += $subtotal;
        }
    } catch (PDOException $e) {
        $cart_items = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Restaurant</title>
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
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-danger {
            background: #e74c3c;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-danger:hover {
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
        
        .cart-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .cart-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-cart-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .cart-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .item-description {
            font-size: 12px;
            color: #666;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #5568d3;
        }
        
        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
        }
        
        .price {
            font-weight: 600;
            color: #f5576c;
        }
        
        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin-left: auto;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-row.total {
            border-bottom: none;
            font-size: 20px;
            font-weight: bold;
            color: #f5576c;
            padding-top: 15px;
        }
        
        .checkout-btn {
            width: 100%;
            margin-top: 20px;
            padding: 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Shopping Cart</h1>
            <div class="user-info">
                <span>Hello, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="navigation">
            <a href="index.php" class="btn btn-secondary">Menu</a>
            <a href="my_orders.php" class="btn btn-secondary">My Orders</a>
            <a href="cart.php" class="btn">Cart</a>
        </div>
        
        <div class="cart-section">
            <h2>Your Cart</h2>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious items from our menu!</p>
                    <a href="index.php" class="btn" style="margin-top: 20px;">Browse Menu</a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                    <div class="item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                </td>
                                <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['item_id']; ?>, -1)">-</button>
                                        <span class="quantity-display" id="qty-<?php echo $item['item_id']; ?>"><?php echo $item['quantity']; ?></span>
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['item_id']; ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td class="price">$<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="removeItem(<?php echo $item['item_id']; ?>)">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <button class="btn btn-success checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function updateQuantity(itemId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'item_id=' + itemId + '&change=' + change
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.removed) {
                        location.reload();
                    } else {
                        document.getElementById('qty-' + itemId).textContent = data.quantity;
                        location.reload(); // Reload to update totals
                    }
                }
            });
        }
        
        function removeItem(itemId) {
            if (confirm('Remove this item from cart?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'item_id=' + itemId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
        
        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>
