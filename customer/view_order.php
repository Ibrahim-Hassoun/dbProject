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
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.quantity, oi.unit_price,
               mi.item_name, mi.description
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: my_orders.php');
    exit;
}

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Restaurant</title>
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
        
        .view-section {
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
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-preparing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-delivering {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-canceled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .info-value {
            color: #333;
            font-weight: 600;
            font-size: 16px;
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
        
        .btn {
            background: #f5576c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
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
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Order #<?php echo $order['order_id']; ?></h1>
            <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </div>
        
        <?php if ($message === 'updated'): ?>
            <div class="view-section">
                <div class="success-message">✓ Order updated successfully!</div>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'cannot_edit'): ?>
            <div class="view-section">
                <div class="error-message">This order cannot be edited because it is <?php echo $order['status']; ?>.</div>
            </div>
        <?php endif; ?>
        
        <div class="view-section">
            <h2 class="section-title">Order Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($order['order_time'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Delivery Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="view-section">
            <h2 class="section-title">Order Items</h2>
            <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?></div>
                    </div>
                    <div class="item-price">$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></div>
                </div>
            <?php endforeach; ?>
            
            <div class="total-row">
                <span>Total:</span>
                <span class="amount">$<?php echo number_format($order['total'], 2); ?></span>
            </div>
        </div>
        
        <div class="view-section">
            <div class="actions">
                <?php if (in_array($order['status'], ['pending', 'preparing'])): ?>
                    <a href="edit_order.php?order_id=<?php echo $order_id; ?>" class="btn">Edit Order</a>
                <?php endif; ?>
                <a href="my_orders.php" class="btn btn-secondary">Back to Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>
</html>
