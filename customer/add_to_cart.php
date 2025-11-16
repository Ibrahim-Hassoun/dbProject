<?php
session_start();

/**
 * Add item to cart
 * Stores cart in session
 */

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get item ID from POST
$item_id = intval($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or increment item in cart
if (isset($_SESSION['cart'][$item_id])) {
    $_SESSION['cart'][$item_id]['quantity']++;
} else {
    $_SESSION['cart'][$item_id] = [
        'item_id' => $item_id,
        'quantity' => 1
    ];
}

echo json_encode([
    'success' => true, 
    'message' => 'Item added to cart',
    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
]);
