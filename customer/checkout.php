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

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Get cart items with details
$cart_items = [];
$total = 0;

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

// Get customer address
$customer_address = '';
try {
    $stmt = $pdo->prepare("SELECT address FROM customers WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
    $customer_address = $customer['address'] ?? '';
} catch (PDOException $e) {
    $customer_address = '';
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    
    if (empty($delivery_address)) {
        $error = "Please provide a delivery address.";
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_id, order_time, status, delivery_address, total)
                VALUES (?, NOW(), 'pending', ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['customer_id'],
                $delivery_address,
                $total
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, item_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $order_id,
                    $item['item_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=$order_id");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to place order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Restaurant</title>
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
            max-width: 900px;
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
        
        .checkout-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5576c;
        }
        
        .order-summary {
            margin-bottom: 30px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .item-quantity {
            font-size: 14px;
            color: #666;
        }
        
        .item-price {
            font-weight: 600;
            color: #f5576c;
            text-align: right;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 20px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }
        
        .total-row .amount {
            color: #f5576c;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            background: #f5576c;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            font-weight: 600;
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
            width: 100%;
            padding: 18px;
            font-size: 18px;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .required {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Checkout</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="checkout-section">
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>
        
        <div class="checkout-section">
            <h2 class="section-title">Order Summary</h2>
            <div class="order-summary">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-price">$<?php echo number_format($item['subtotal'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-row">
                    <span>Total:</span>
                    <span class="amount">$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="checkout-section">
            <h2 class="section-title">Delivery Information</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="delivery_address">Delivery Address <span class="required">*</span></label>
                    <textarea id="delivery_address" name="delivery_address" required placeholder="Enter your delivery address"><?php echo htmlspecialchars($customer_address); ?></textarea>
                </div>
                
                <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
                
                <div class="actions">
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
