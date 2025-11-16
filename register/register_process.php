<?php

/**
 * Registration Process
 * Handles customer registration
 */

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Get form data
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$address = trim($_POST['address'] ?? '');

// Validate required fields
if (empty($full_name) || empty($phone) || empty($password)) {
    header('Location: index.html?error=invalid');
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    header('Location: index.html?error=password_short');
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    header('Location: index.html?error=password_mismatch');
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if phone already exists
    $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    
    if ($stmt->fetch()) {
        header('Location: index.html?error=duplicate');
        exit;
    }
    
    // Insert new customer
    $stmt = $pdo->prepare("
        INSERT INTO customers (full_name, phone, password, address)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$full_name, $phone, $password_hash, $address]);
    
    // Registration successful
    header('Location: index.html?success=1');
    exit;
    
} catch (PDOException $e) {
    // Log error and redirect
    error_log("Registration error: " . $e->getMessage());
    header('Location: index.html?error=database');
    exit;
}
