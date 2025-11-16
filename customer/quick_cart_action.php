<?php
session_start();

/**
 * Quick cart actions from menu page
 * Handles add, increase, decrease actions
 */

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login/index.html');
    exit;
}

// Get form data
$item_id = intval($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($item_id <= 0 || empty($action)) {
    header('Location: index.php');
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Perform action
switch ($action) {
    case 'add':
        if (!isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] = [
                'item_id' => $item_id,
                'quantity' => 1
            ];
        } else {
            $_SESSION['cart'][$item_id]['quantity']++;
        }
        break;
        
    case 'increase':
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity']++;
        }
        break;
        
    case 'decrease':
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity']--;
            
            // Remove if quantity is 0 or less
            if ($_SESSION['cart'][$item_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$item_id]);
            }
        }
        break;
}

// Redirect back to menu
header('Location: index.php');
exit;
