<?php
session_start();

/**
 * Login Process
 * Handles user authentication
 */

// Get database connection
$pdo = require_once __DIR__ . '/../database/connection/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Get form data
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// Validate required fields
if (empty($phone) || empty($password)) {
    header('Location: index.html?error=required');
    exit;
}

try {
    // Find user by phone
    $stmt = $pdo->prepare("SELECT customer_id, full_name, phone, password FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not found
        header('Location: index.html?error=notfound');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Invalid password
        header('Location: index.html?error=invalid');
        exit;
    }
    
    // Login successful - set session variables
    $_SESSION['customer_id'] = $user['customer_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['phone'] = $user['phone'];
    
    // Redirect based on customer ID
    if ($user['customer_id'] == 1) {
        // Admin user
        header('Location: ../admin/index.php');
    } else {
        // Regular customer
        header('Location: ../customer/index.php');
    }
    exit;
    
} catch (PDOException $e) {
    // Log error and redirect
    error_log("Login error: " . $e->getMessage());
    header('Location: index.html?error=database');
    exit;
}
