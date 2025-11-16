<?php
session_start();

/**
 * Remove item from cart
 */

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$item_id = intval($_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

if (isset($_SESSION['cart'][$item_id])) {
    unset($_SESSION['cart'][$item_id]);
}

echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
