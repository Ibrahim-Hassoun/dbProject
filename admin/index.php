<?php
session_start();

// Check if user is logged in and is admin (ID = 1)
if (!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] != 1) {
    header('Location: ../register/index.html');
    exit;
}

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restaurant Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .card-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .card-btn:hover {
            background: #5568d3;
        }
        
        .stats {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .stats h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <?php
        // Get statistics
        try {
            $total_items = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
            $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
            $total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
        } catch (PDOException $e) {
            $total_items = $total_orders = $total_customers = $total_revenue = 0;
        }
        ?>
        
        <div class="stats">
            <h2>Quick Stats</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_items; ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="card">
                <h2>📋 Menu Items</h2>
                <p>Add, edit, or delete menu items</p>
                <a href="menu_items.php" class="card-btn">Manage Menu</a>
            </div>
            
            <div class="card">
                <h2>📦 Orders</h2>
                <p>View and manage all orders</p>
                <a href="orders.php" class="card-btn">View Orders</a>
            </div>
            
            <div class="card">
                <h2>📊 Reports</h2>
                <p>View sales reports and analytics</p>
                <a href="reports.php" class="card-btn">View Reports</a>
            </div>
        </div>
    </div>
</body>
</html>
