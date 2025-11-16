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

// Get customer orders
try {
    $stmt = $pdo->prepare("
        SELECT order_id, order_time, status, delivery_address, total
        FROM orders
        WHERE customer_id = ?
        ORDER BY order_time DESC
    ");
    $stmt->execute([$_SESSION['customer_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Restaurant</title>
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
        
        .btn-danger {
            background: #e74c3c;
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
        
        .orders-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .orders-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .order-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .order-card:hover {
            border-color: #f5576c;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
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
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            font-size: 14px;
        }
        
        .detail-label {
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 My Orders</h1>
            <div class="user-info">
                <span>Hello, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="navigation">
            <a href="index.php" class="btn btn-secondary">Menu</a>
            <a href="my_orders.php" class="btn">My Orders</a>
            <a href="cart.php" class="btn btn-secondary">Cart</a>
        </div>
        
        <div class="orders-section">
            <h2>Order History</h2>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <div style="font-size: 64px; margin-bottom: 20px;">📦</div>
                    <h3>No orders yet</h3>
                    <p>Start ordering some delicious food!</p>
                    <a href="index.php" class="btn" style="margin-top: 20px;">Browse Menu</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-number">Order #<?php echo $order['order_id']; ?></span>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <div class="detail-label">Order Date</div>
                                <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($order['order_time'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Total Amount</div>
                                <div class="detail-value" style="color: #f5576c;">$<?php echo number_format($order['total'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Delivery Address</div>
                                <div class="detail-value"><?php echo htmlspecialchars(substr($order['delivery_address'], 0, 50)) . (strlen($order['delivery_address']) > 50 ? '...' : ''); ?></div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <a href="view_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-small">View Details</a>
                            <?php if (in_array($order['status'], ['pending', 'preparing'])): ?>
                                <a href="edit_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-small">Edit Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
