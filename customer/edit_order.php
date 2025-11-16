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

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header('Location: my_orders.php');
    exit;
}

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT order_id, order_time, status, delivery_address, total
        FROM orders
        WHERE order_id = ? AND customer_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['customer_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: my_orders.php');
        exit;
    }
    
    // Check if order can be edited
    if (!in_array($order['status'], ['pending', 'preparing'])) {
        header("Location: view_order.php?order_id=$order_id&error=cannot_edit");
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.item_id, oi.quantity, oi.unit_price,
               mi.item_name, mi.description, mi.price as current_price
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get available menu items to add
    $stmt = $pdo->query("SELECT item_id, item_name, price FROM menu_items WHERE in_stock > 0 ORDER BY item_name");
    $available_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: my_orders.php');
    exit;
}

// Handle adding new item to order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $new_item_id = intval($_POST['new_item_id'] ?? 0);
    $new_item_quantity = intval($_POST['new_item_quantity'] ?? 1);
    
    if ($new_item_id > 0 && $new_item_quantity > 0) {
        try {
            // Get item price
            $stmt = $pdo->prepare("SELECT price FROM menu_items WHERE item_id = ?");
            $stmt->execute([$new_item_id]);
            $menu_item = $stmt->fetch();
            
            if ($menu_item) {
                $pdo->beginTransaction();
                
                // Check if item already exists in order
                $stmt = $pdo->prepare("SELECT order_item_id, quantity FROM order_items WHERE order_id = ? AND item_id = ?");
                $stmt->execute([$order_id, $new_item_id]);
                $existing_item = $stmt->fetch();
                
                if ($existing_item) {
                    // Update existing item quantity
                    $new_qty = $existing_item['quantity'] + $new_item_quantity;
                    $stmt = $pdo->prepare("UPDATE order_items SET quantity = ? WHERE order_item_id = ?");
                    $stmt->execute([$new_qty, $existing_item['order_item_id']]);
                } else {
                    // Add new item to order
                    $stmt = $pdo->prepare("
                        INSERT INTO order_items (order_id, item_id, quantity, unit_price)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$order_id, $new_item_id, $new_item_quantity, $menu_item['price']]);
                }
                
                // Update order total
                $stmt = $pdo->prepare("UPDATE orders SET total = total + ? WHERE order_id = ?");
                $stmt->execute([$menu_item['price'] * $new_item_quantity, $order_id]);
                
                $pdo->commit();
                
                // Reload page to show updated order
                header("Location: edit_order.php?order_id=$order_id&message=item_added");
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to add item to order.";
        }
    }
}

// Handle order update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $quantities = $_POST['quantity'] ?? [];
    
    if (empty($delivery_address)) {
        $error = "Please provide a delivery address.";
    } else {
        try {
            $pdo->beginTransaction();
            
            $new_total = 0;
            
            // Update quantities and remove items with quantity 0
            foreach ($quantities as $order_item_id => $quantity) {
                $quantity = intval($quantity);
                
                if ($quantity <= 0) {
                    // Remove item
                    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_item_id = ? AND order_id = ?");
                    $stmt->execute([$order_item_id, $order_id]);
                } else {
                    // Update quantity
                    $stmt = $pdo->prepare("UPDATE order_items SET quantity = ? WHERE order_item_id = ? AND order_id = ?");
                    $stmt->execute([$quantity, $order_item_id, $order_id]);
                    
                    // Calculate new total
                    $stmt = $pdo->prepare("SELECT unit_price FROM order_items WHERE order_item_id = ?");
                    $stmt->execute([$order_item_id]);
                    $item = $stmt->fetch();
                    $new_total += $item['unit_price'] * $quantity;
                }
            }
            
            // Check if any items remain
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $item_count = $stmt->fetchColumn();
            
            if ($item_count == 0) {
                // Cancel order if no items left
                $stmt = $pdo->prepare("UPDATE orders SET status = 'canceled' WHERE order_id = ?");
                $stmt->execute([$order_id]);
                $pdo->commit();
                header("Location: my_orders.php?message=order_canceled");
                exit;
            }
            
            // Update order total and address
            $stmt = $pdo->prepare("UPDATE orders SET total = ?, delivery_address = ? WHERE order_id = ?");
            $stmt->execute([$new_total, $delivery_address, $order_id]);
            
            $pdo->commit();
            
            header("Location: view_order.php?order_id=$order_id&message=updated");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Restaurant</title>
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
        }
        
        .header h1 {
            color: #f5576c;
            font-size: 28px;
        }
        
        .edit-section {
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
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .item-price {
            font-size: 14px;
            color: #666;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }
        
        .subtotal {
            min-width: 100px;
            text-align: right;
            font-weight: 600;
            color: #f5576c;
        }
        
        .form-group {
            margin: 20px 0;
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
        
        textarea:focus, .quantity-input:focus {
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
        
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .add-item-form {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .add-item-form .form-group {
            margin: 0;
            flex: 1;
        }
        
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: white;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-add {
            background: #27ae60;
            white-space: nowrap;
        }
        
        .btn-add:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Order #<?php echo $order['order_id']; ?></h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="edit-section">
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['message']) && $_GET['message'] === 'item_added'): ?>
            <div class="edit-section">
                <div class="success-message">✓ Item added to order successfully!</div>
            </div>
        <?php endif; ?>
        
        <div class="edit-section">
            <div class="info-box">
                ℹ️ You can modify quantities, remove items (set to 0), or add new items. Order will be canceled if all items are removed.
            </div>
        </div>
        
        <div class="edit-section">
            <h2 class="section-title">Add Items to Order</h2>
            <form method="POST" class="add-item-form">
                <div class="form-group">
                    <label for="new_item_id">Select Item</label>
                    <select name="new_item_id" id="new_item_id" required>
                        <option value="">Choose an item...</option>
                        <?php foreach ($available_items as $menu_item): ?>
                            <option value="<?php echo $menu_item['item_id']; ?>">
                                <?php echo htmlspecialchars($menu_item['item_name']); ?> - $<?php echo number_format($menu_item['price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="new_item_quantity">Quantity</label>
                    <input type="number" 
                           name="new_item_quantity" 
                           id="new_item_quantity" 
                           value="1" 
                           min="1" 
                           class="quantity-input"
                           required>
                </div>
                <button type="submit" name="add_item" class="btn btn-add">+ Add Item</button>
            </form>
        </div>
        
        <form method="POST">
            <div class="edit-section">
                <h2 class="section-title">Order Items</h2>
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            <div class="item-price">$<?php echo number_format($item['unit_price'], 2); ?> each</div>
                        </div>
                        <div class="quantity-control">
                            <label style="margin: 0;">Qty:</label>
                            <input type="number" 
                                   name="quantity[<?php echo $item['order_item_id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="0" 
                                   class="quantity-input"
                                   onchange="updateSubtotal(this, <?php echo $item['unit_price']; ?>, <?php echo $item['order_item_id']; ?>)">
                            <span class="subtotal" id="subtotal-<?php echo $item['order_item_id']; ?>">
                                $<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="edit-section">
                <h2 class="section-title">Delivery Address</h2>
                <div class="form-group">
                    <label for="delivery_address">Delivery Address <span class="required">*</span></label>
                    <textarea id="delivery_address" name="delivery_address" required><?php echo htmlspecialchars($order['delivery_address']); ?></textarea>
                </div>
            </div>
            
            <div class="edit-section">
                <button type="submit" name="update_order" class="btn btn-success">Save Changes</button>
                <div class="actions">
                    <a href="view_order.php?order_id=<?php echo $order_id; ?>" class="btn btn-secondary">Cancel</a>
                    <a href="my_orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        function updateSubtotal(input, price, itemId) {
            const quantity = parseInt(input.value) || 0;
            const subtotal = price * quantity;
            document.getElementById('subtotal-' + itemId).textContent = '$' + subtotal.toFixed(2);
        }
    </script>
</body>
</html>
