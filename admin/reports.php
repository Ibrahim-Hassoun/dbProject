<?php
session_start();

// Check if user is logged in and is admin (ID = 1)
if (!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] != 1) {
    header('Location: ../login/index.html');
    exit;
}

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Include cursor functions
require_once __DIR__ . '/../database/cursors/process_pending_orders.php';
require_once __DIR__ . '/../database/cursors/customer_lifetime_value.php';
require_once __DIR__ . '/../database/cursors/low_stock_items.php';

// Get reports based on selected tab
$active_tab = $_GET['tab'] ?? 'pending';
$pending_orders = [];
$customer_values = [];
$low_stock_items = [];

try {
    if ($active_tab === 'pending') {
        $pending_orders = use_process_pending_orders_cursor();
    } elseif ($active_tab === 'customers') {
        $customer_values = use_customer_lifetime_value_cursor();
    } elseif ($active_tab === 'stock') {
        $low_stock_items = use_low_stock_cursor();
    }
} catch (PDOException $e) {
    $error = "Error loading report: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
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
        
        .btn {
            background: #667eea;
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
            background: #5568d3;
        }
        
        .tabs {
            background: white;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .tab-links {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tab-link {
            flex: 1;
            padding: 20px;
            text-align: center;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            text-decoration: none;
            display: block;
        }
        
        .tab-link:hover {
            background: #f8f9fa;
        }
        
        .tab-link.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .report-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .report-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-high {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-low {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-gold {
            background: #ffd700;
            color: #333;
        }
        
        .badge-silver {
            background: #c0c0c0;
            color: #333;
        }
        
        .badge-bronze {
            background: #cd7f32;
            color: white;
        }
        
        .badge-new {
            background: #e0e0e0;
            color: #666;
        }
        
        .badge-critical {
            background: #dc3545;
            color: white;
        }
        
        .badge-out {
            background: #6c757d;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Analytics Reports</h1>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
        
        <div class="tabs">
            <div class="tab-links">
                <a href="?tab=pending" class="tab-link <?php echo $active_tab === 'pending' ? 'active' : ''; ?>">
                    Pending Orders Priority
                </a>
                <a href="?tab=customers" class="tab-link <?php echo $active_tab === 'customers' ? 'active' : ''; ?>">
                    Customer Lifetime Value
                </a>
                <a href="?tab=stock" class="tab-link <?php echo $active_tab === 'stock' ? 'active' : ''; ?>">
                    Low Stock Items
                </a>
            </div>
        </div>
        
        <?php if ($active_tab === 'pending'): ?>
            <div class="report-section">
                <h2>Pending Orders by Priority</h2>
                <?php if (empty($pending_orders)): ?>
                    <div class="empty-state">
                        <p>No pending orders at the moment.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer ID</th>
                                <th>Total Amount</th>
                                <th>Item Count</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['customer_id']; ?></td>
                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($order['priority']); ?>">
                                            <?php echo $order['priority']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        
        <?php elseif ($active_tab === 'customers'): ?>
            <div class="report-section">
                <h2>Customer Lifetime Value Analysis</h2>
                <?php if (empty($customer_values)): ?>
                    <div class="empty-state">
                        <p>No customer data available.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Total Spent</th>
                                <th>Order Count</th>
                                <th>Avg Order Value</th>
                                <th>Tier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer_values as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td>$<?php echo number_format($customer['total_spent'], 2); ?></td>
                                    <td><?php echo $customer['order_count']; ?> orders</td>
                                    <td>$<?php echo number_format($customer['avg_order_value'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($customer['customer_tier']); ?>">
                                            <?php echo $customer['customer_tier']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        
        <?php elseif ($active_tab === 'stock'): ?>
            <div class="report-section">
                <h2>Low Stock & Reorder Recommendations</h2>
                <?php if (empty($low_stock_items)): ?>
                    <div class="empty-state">
                        <p>All items have sufficient stock.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Total Ordered</th>
                                <th>Status</th>
                                <th>Reorder</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo $item['current_stock']; ?></td>
                                    <td><?php echo $item['total_ordered']; ?> sold</td>
                                    <td>
                                        <span class="badge badge-<?php echo $item['stock_status'] === 'OUT_OF_STOCK' ? 'out' : 'critical'; ?>">
                                            <?php echo str_replace('_', ' ', $item['stock_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $item['reorder_recommended'] ? '✓ Yes' : 'No'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
