<?php
session_start();
include 'db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart'
    ]);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

$productId = (int) $_POST['product_id'];
$userId = (int) $_SESSION['user_id'];

// Fetch product details from products table
$stmt = $conn->prepare("SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit;
}

// Check if product already exists in user's cart
$checkStmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$checkStmt->bind_param("ii", $userId, $productId);
$checkStmt->execute();
$cartResult = $checkStmt->get_result();
$checkStmt->close();

if ($cartResult->num_rows > 0) {
    // Product already in cart, update quantity
    $cartItem = $cartResult->fetch_assoc();
    $newQuantity = $cartItem['quantity'] + 1;
    
    $updateStmt = $conn->prepare("UPDATE cart SET quantity = ?, added_at = NOW() WHERE cart_id = ?");
    $updateStmt->bind_param("ii", $newQuantity, $cartItem['cart_id']);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // Product not in cart, insert new record
    $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity, added_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    $insertStmt->bind_param("iisds", $userId, $productId, $product['product_name'], $product['product_price'], $product['product_image']);
    $insertStmt->execute();
    $insertStmt->close();
}

// Calculate new cart count from database
$countStmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$cartCount = $countResult->fetch_assoc()['total'] ?? 0;
$countStmt->close();

// Return success response with updated cart count
echo json_encode([
    'success' => true,
    'message' => 'Product added to cart successfully',
    'cartCount' => $cartCount
]);
?> 