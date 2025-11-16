<?php
session_start();

/**
 * Get cart item count
 */

header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

echo json_encode(['count' => $count]);
