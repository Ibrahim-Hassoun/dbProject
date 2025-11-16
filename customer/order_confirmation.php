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
    header('Location: index.php');
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
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Restaurant</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
        }
        
        .confirmation-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #27ae60;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: #fff3cd;
            color: #856404;
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
            margin: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-box">
            <div class="success-icon">✅</div>
            <h1>Order Placed Successfully!</h1>
            <p class="subtitle">Thank you for your order. We'll start preparing it right away.</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value">#<?php echo $order['order_id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Time:</span>
                    <span class="detail-value"><?php echo date('M d, Y h:i A', strtotime($order['order_time'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><span class="status-badge"><?php echo ucfirst($order['status']); ?></span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value" style="font-size: 20px; font-weight: bold; color: #f5576c;">$<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>
            
            <div>
                <a href="my_orders.php" class="btn">View My Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>
</html>
