<?php
session_start();

/**
 * Update cart quantity
 */

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$item_id = intval($_POST['item_id'] ?? 0);
$change = intval($_POST['change'] ?? 0);

if ($item_id <= 0 || $change == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if (!isset($_SESSION['cart'][$item_id])) {
    echo json_encode(['success' => false, 'message' => 'Item not in cart']);
    exit;
}

// Update quantity
$_SESSION['cart'][$item_id]['quantity'] += $change;

// Remove if quantity is 0 or less
if ($_SESSION['cart'][$item_id]['quantity'] <= 0) {
    unset($_SESSION['cart'][$item_id]);
    echo json_encode(['success' => true, 'removed' => true]);
    exit;
}

echo json_encode([
    'success' => true,
    'quantity' => $_SESSION['cart'][$item_id]['quantity'],
    'removed' => false
]);
