<?php
session_start(); // Ensure session is started here if not done earlier

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate the number of items in the cart
$cartItemCount = count($_SESSION['cart']);
?>