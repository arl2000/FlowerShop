<?php
session_start();
include 'db_connection.php';

// Check if a delete request is made for customized products
if (isset($_GET['delete_id'])) {
    $productId = $_GET['delete_id'];
    // Call the soft delete function
    softDeleteCustomizedProduct($productId);

    // Redirect back to the homepage after deletion
    header("Location: homepage.php");
    exit;
}

// Initialize cart count from database if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = (int) $_SESSION['user_id'];
    $cartCountQuery = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartCountQuery->bind_param("i", $userId);
    $cartCountQuery->execute();
    $cartCountResult = $cartCountQuery->get_result();
    $cartCount = $cartCountResult->fetch_assoc()['total'] ?? 0;
    $cartCountQuery->close();
}

// Fetch order count securely using prepared statements
$order_count_query = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders");
$order_count_query->execute();
$order_count_result = $order_count_query->get_result();
$order_count = $order_count_result->fetch_assoc()['total_orders'] ?? 0;
$order_count_query->close();

// Add to cart functionality
if (isset($_GET['add']) && is_numeric($_GET['add']) && isset($_SESSION['user_id'])) {
    $productId = (int) $_GET['add'];
    $stmt = $conn->prepare("SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'name' => $product['product_name'],
                'price' => $product['product_price'],
                'quantity' => 1,
                'image' => $product['product_image']
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity']++;
        }
    }
    header("Location: cart.php");
    exit();
} elseif (isset($_GET['add']) && is_numeric($_GET['add']) && !isset($_SESSION['user_id'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginModal').style.display = 'flex';
        });
    </script>";
}

//product stock count
$query = $conn->query("SELECT * FROM products");
while ($row = $query->fetch_assoc());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Heavenly Bloom</title>
    
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="home.css">
    
    <style>
        /* Navigation from navi.php */
        nav {
            background-color: #fff; /* Clean white background */
            padding: 1rem 20px; /* Adjusted overall padding */
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Align items to the start to accommodate left section */
            flex-wrap: wrap;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08); /* Softer shadow */
            border-bottom: 1px solid #f0ece7; /* Subtle bottom border */
            position: sticky; /* Make the navbar sticky */
            top: 0;
            z-index: 100; /* Ensure it stays on top */
        }

        /* Left Section: Logo and Search */
        nav .left-section {
            display: flex;
            align-items: center;
            margin-right: auto; /* Push the rest of the elements to the right */
        }

        nav .logo {
            font-family: 'Playfair Display', serif; /* Elegant, romantic font */
            font-size: 1.8rem;
            color: #e91e63; /* Romantic pink for the logo */
            margin-right: 1rem; /* Space between logo and search */
        }

        /* Search Container in Left Section */
        nav .left-section .search-container {
            display: flex;
            align-items: center;
            padding: 0.6rem 1rem; /* Adjusted padding */
            background-color: #fcf6f2; /* Very light background */
            border-radius: 25px; /* More rounded */
            border: 1px solid #d4c4b9; /* Softer border */
        }

        nav .left-section .search-container input {
            padding: 0.4rem 0.8rem; /* Adjusted padding */
            border: none;
            outline: none;
            width: 150px; /* Slightly smaller */
            background-color: transparent;
            font-size: 0.85rem;
            color: #5a3e36; /* Soft brown text */
        }

        nav .left-section .search-container button {
            background-color: #f9c5ce; /* Soft pink */
            border: none;
            color: white;
            padding: 0.4rem 0.6rem; /* Adjusted padding */
            border-radius: 50%;
            margin-left: 0.3rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        nav .left-section .search-container button:hover {
            background-color: #f4b6be; /* Slightly darker pink */
        }

        nav .left-section .search-container i {
            font-size: 0.85rem;
        }

        /* Center Navigation Links Container */
        nav .center-nav {
            display: flex;
            justify-content: center; /* Center the content */
            flex-grow: 1; /* Allow it to take up available space */
        }

        /* Navigation Links */
        nav .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem; /* Adjusted gap */
            margin: 0.5rem 0; /* Add some vertical margin */
        }

        nav .nav-links a {
            text-decoration: none;
            color: #5a3e36; /* Soft brown text */
            display: flex;
            align-items: center;
            font-size: 0.95rem; /* Slightly larger font */
            transition: color 0.3s ease;
        }

        nav .nav-links a:hover {
            color: #e91e63; /* Romantic pink on hover */
        }

        nav .nav-links a i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        /* Occasions Dropdown */
        nav .dropdown {
            position: relative;
        }

        nav .dropdown-toggle {
            text-decoration: none;
            color: #5a3e36;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            cursor: pointer;
        }

        nav .dropdown-toggle i {
            margin-left: 0.3rem;
        }

        nav .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            min-width: 180px;
            border: 1px solid #f0ece7; /* Light border */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 999;
            white-space: nowrap;
            padding: 0.5rem 0;
            margin-top: 0.2rem;
        }

        nav .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: #5a3e36;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.9rem;
        }

        nav .dropdown-menu a:hover {
            background-color: #f9d5d3; /* Soft pink hover */
            color: #e91e63;
        }

        nav .dropdown-menu a i {
            margin-right: 0.8rem;
            font-size: 1rem;
        }

        /* Style for the cart count span */
        nav .cart-link span {
            font-size: 0.7em; /* Make the number smaller */
            color: red; /* Make the color red */
            vertical-align: super; /* Position it slightly above */
            margin-left: 0.2em; /* Add a little space */
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            nav {
                padding: 1rem;
                flex-direction: column;
                align-items: stretch;
            }

            nav .left-section {
                flex-direction: column;
                align-items: center;
                margin-bottom: 0.8rem;
                margin-right: 0; /* Reset margin */
            }

            nav .logo {
                margin-right: 0; /* Reset margin for smaller screens */
                margin-bottom: 0.5rem; /* Add space below logo */
            }

            nav .center-nav {
                justify-content: center; /* Center links on smaller screens */
                margin-bottom: 0.8rem;
            }

            nav .nav-links {
                flex-direction: row;
                justify-content: center; /* Center links on smaller screens */
                gap: 1rem;
                margin-bottom: 0.8rem;
            }

            nav .nav-links a {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            nav .left-section {
                align-items: flex-start;
            }

            nav .center-nav {
                justify-content: flex-start;
            }

            nav .nav-links {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }

            nav .nav-links a {
                font-size: 0.9rem;
            }
        }
        
        /* Product card styles */
        .product-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .product-image-wrapper {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image-wrapper img {
            transform: scale(1.1);
        }
        
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 138, 195, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .product-card:hover .product-overlay {
            opacity: 1;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #ff4483;
        }
        
        .icon-button {
            background-color: white;
            color: #ff4483;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin: 0 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .icon-button:hover {
            background-color: #ff4483;
            color: white;
            transform: scale(1.1);
        }
        
        .customized-view-btn {
            background-color: white;
            color: #ff4483;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .customized-view-btn:hover {
            background-color: #ff4483;
            color: white;
            transform: scale(1.05);
        }
        
        /* Updated Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            overflow-y: auto;
        }
        
        .modal-content {
            display: flex;
            flex-direction: row;
            background-color: white;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 1000px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            height: auto;
        }
        
        .modal-content .row {
            width: 100%;
            margin: 0;
        }
        
        .modal-content .col-md-6 {
            padding: 0;
        }
        
        .modal-product-details {
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .close, .close-button {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: #ff4483;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .close:hover, .close-button:hover {
            background-color: #ff4483;
            color: white;
            transform: rotate(90deg);
        }
        
        .modal-image-container {
            padding: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            overflow: hidden;
        }
        
        .modal-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
            box-shadow: none;
            transition: transform 0.3s ease;
        }
        
        .modal-product-details h2 {
            font-family: 'Playfair Display', serif;
            color: #333;
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .description {
            color: #666;
            line-height: 1.6;
            font-size: 1rem;
            margin-bottom: 30px;
        }
        
        .price-container {
            margin-bottom: 30px;
        }
        
        .price {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ff4483;
            margin: 0;
        }
        
        .modal-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .modal-actions .main-button {
            background-color: #ff4483;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 15px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .modal-actions .main-button:hover {
            background-color: #e91e63;
        }
        
        .modal-actions .main-button-secondary {
            background-color: transparent;
            color: #ff4483;
            border: 1px solid #ff4483;
            border-radius: 4px;
            padding: 14px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .modal-actions .main-button-secondary:hover {
            background-color: #fff0f5;
        }
        
        .modal-details {
            padding: 35px 30px;
            border-left: 1px solid #f0ece7;
            height: 100%;
        }
        
        .modal-options-group {
            margin-bottom: 25px;
        }
        
        .modal-options-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        
        .form-check {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            margin-right: 10px;
            width: 10px;
        }
        
        .form-check-label {
            color: #636e72;
        }
        
        .customer-message label {
            font-weight: 600;
            color: #333;
            font-family: 'Playfair Display', serif;
        }
        
        .customer-message textarea {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            resize: none;
            transition: border-color 0.3s ease;
        }
        
        .customer-message textarea:focus {
            border-color: #ff4483;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 68, 131, 0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                flex-direction: column;
            }
            
            .modal-image-container {
                height: 350px;
            }
            
            .modal-product-details {
                padding: 30px 20px;
            }
            
            .price {
                font-size: 1.5rem;
                margin: 15px 0;
            }
        }
        
        /* Enhanced dropdown styles */
        .dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        
        .dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .dropdown-toggle.active::after {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 200px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 10px 0;
            z-index: 1000;
            border: 1px solid #f0ece7;
            margin-top: 10px;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #5a3e36;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .dropdown-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .dropdown-menu a:hover {
            background-color: #fdf6f8;
            color: #ff4483;
        }
        
        /* Responsive adjustments for dropdown */
        @media (max-width: 768px) {
            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                margin-top: 5px;
                border-radius: 5px;
            }
        }
        
        /* Customized Product Modal Enhancements */
        .custom-modal {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .custom-modal .row {
            height: 100%;
            margin: 0;
            flex: 1;
        }
        
        .custom-modal .col-md-5,
        .custom-modal .col-md-7 {
            padding: 0;
            height: 100%;
        }
        
        .custom-scroll {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: thin;
            scrollbar-color: #ff4483 #f5f5f5;
        }
        
        .custom-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .custom-scroll::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 10px;
        }
        
        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #ff4483;
            border-radius: 10px;
            border: 2px solid #f5f5f5;
        }
        
        .custom-title {
            font-family: 'Playfair Display', serif;
            color: #ff4483;
            font-size: 1.6rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0ece7;
        }
        
        .custom-options-container {
            padding-right: 15px;
            padding-left: 5px;
        }
        
        .options-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .options-group-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
        }
        
        .modal-options-group {
            background-color: #fdf6f8;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px; /* Increased spacing between option groups */
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .modal-options-group:hover {
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.1);
        }
        
        .form-check {
            margin-bottom: 8px;
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #f0ece7;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            position: relative;
            overflow: hidden;
        }
        
        .form-check-input {
            margin-right: 10px;
            margin-top: 3px;
            flex-shrink: 0;
        }
        
        .form-check:hover {
            border-color: #ff4483;
            transform: translateY(-2px);
        }
        
        .form-check-input:checked + .form-check-label {
            color: #ff4483;
            font-weight: 500;
        }
        
        
        .form-check-label {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.4;
            display: block;
            width: 100%;
        }
        
        .text-pink {
            color: #ff4483;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        #modal-addons-checkboxes .form-check {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 12px;
        }
        
        #modal-addons-checkboxes .form-check-input {
            margin-bottom: 5px;
        }
        
        #modal-addons-checkboxes .form-check-label {
            display: flex;
            flex-direction: column;
        }
        
        #modal-addons-checkboxes .text-pink {
            margin-top: 4px;
        }
        
        .customer-message textarea {
            border: 1px solid #f0ece7;
            background-color: white;
            border-radius: 10px;
            padding: 12px;
            font-size: 0.9rem;
            resize: none;
            box-shadow: none;
            transition: all 0.3s ease;
        }
        
        .customer-message textarea:focus {
            border-color: #ff4483;
            box-shadow: 0 0 0 3px rgba(255, 68, 131, 0.1);
        }
        
        /* Responsive adjustments for custom modal */
        @media (max-width: 767px) {
            .custom-modal {
                overflow-y: auto;
            }
            
            .custom-modal .row {
                display: block;
            }
            
            .modal-image-container {
                max-height: none;
                border-radius: 20px 20px 0 0;
            }
            
            .modal-details.custom-scroll {
                max-height: none;
                overflow: visible;
            }
            
            .options-group-content {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
        
        /* Adjust spacing for better organization */
        #modal-size-options .options-group-content,
        #modal-ribbon-color-options .options-group-content,
        #modal-wrapper-color-options .options-group-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        
        /* Separate styling for add-ons that might have longer text */
        #modal-addons-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }
        
        /* More targeted fixes for radio buttons */
        #modal-size-options .form-check,
        #modal-ribbon-color-options .form-check,
        #modal-wrapper-color-options .form-check {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            padding: 8px 10px;
            min-height: 36px;
            text-align: left;
        }
        
        #modal-size-options .form-check-input,
        #modal-ribbon-color-options .form-check-input,
        #modal-wrapper-color-options .form-check-input {
            margin-right: 8px;
            margin-top: 0;
            margin-left: 0;
            position: relative;
            flex: 0 0 auto;
        }
        
        #modal-size-options .form-check-label,
        #modal-ribbon-color-options .form-check-label,
        #modal-wrapper-color-options .form-check-label {
            display: inline;
            text-align: left;
            white-space: nowrap;
            color: #333;
            font-weight: normal;
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Radio buttons specific styling */
        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 50%;
            width: 10px;
            height: 10px;
            border: 1px solid #ddd;
            transition: 0.2s all linear;
            outline: none;
            margin-right: 8px;
            position: relative;
            display: inline-block;
            vertical-align: middle;
        }
        
        input[type="radio"]:checked {
            border: 3px solid #ff4483;
            background-color: white;
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .notification.success {
            background-color: #4CAF50;
        }
        
        .notification.error {
            background-color: #f44336;
        }

        .sold-out-label {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4483;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

    </style>
</head>

<body>
    <!-- ***** Preloader Start ***** -->
    <div id="preloader">
        <div class="jumper">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>  
    <!-- ***** Preloader End ***** -->
    
    <!-- ***** Header Area Start ***** -->
    <?php
    // Fetch categories for the dropdown
    $categoryQuery = "SELECT * FROM categories";
    $categoryResult = $conn->query($categoryQuery);
    
    // Fetch cart items from database (without using aliases)
    $stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_price, quantity, is_customized, 
    ribbon_color_id, ribbon_color_name, ribbon_color_price, 
    wrapper_color_id, wrapper_color_name, wrapper_color_price, 
    customer_message, addons
    FROM cart
    WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['product_price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cartItems[] = $row;
    }
    $stmt->close();
    ?>
    
    <nav>
        <div class="left-section">
            <div class="logo">Heavenly Bloom</div>
            <div class="search-container">
                <form action="search_results.php" method="GET" style="display: flex; align-items: center;">
                    <input
                        type="text"
                        name="search_query"
                        placeholder="Search..."
                        style="padding: 4px 8px; border-radius: 20px; border: 1px solid #ccc; outline: none; width: 120px;">
                    <button
                        type="submit"
                        style="background-color: #f48fb1; border: none; color: white; padding: 4px 6px; border-radius: 50%; margin-left: 3px;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="center-nav">
            <div class="nav-links">
                <a href="homepage.php">
                    <i class="fas fa-home"></i> Home
                </a>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-gift"></i> Occasions
                    </a>
                    <div id="occasionDropdown" class="dropdown-menu">
                        <?php while ($row = $categoryResult->fetch_assoc()): ?>
                            <?php
                            // Dynamically assign icons based on category name (or use a default)
                            $categoryIcon = 'fas fa-gift'; // Default icon
                            if (strpos(strtolower($row['name']), 'birthday') !== false) {
                                $categoryIcon = 'fas fa-birthday-cake'; // Birthday icon
                            } elseif (strpos(strtolower($row['name']), 'anniversary') !== false) {
                                $categoryIcon = 'fas fa-heart'; // Anniversary icon
                            } elseif (strpos(strtolower($row['name']), 'corporate') !== false) {
                                $categoryIcon = 'fas fa-briefcase'; // Corporate icon
                            }
                            ?>
                            <a href="occasions.php?category_id=<?php echo $row['id']; ?>">
                                <i class="<?php echo $categoryIcon; ?>"></i>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <a href="customized_products.php" class="customize-link">
                    <i class="fas fa-palette"></i> Customize
                </a>
                <a href="cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i> Cart <span><?= $cartCount ?></span>
                </a>
                <a href="trackorders.php">
                    <i class="fas fa-shipping-fast"></i> Track Orders
                </a>
                <a href="about.php">
                    <i class="fas fa-info-circle"></i> About Us
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="user_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <?php else: ?>
                <a href="user_login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- ***** Header Area End ***** -->

    <!-- ***** Welcome Area Start ***** -->
    <div class="welcome-area" id="welcome">
        <!-- ***** Header Text Start ***** -->
        <div class="header-text">
            <div class="container">
                <div class="row">
                    <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
                        <h1>Let your brand bloom <strong>beautifully</strong>—<br>with <strong>Heavenly Bloom</strong> by your side.</h1>
                        <p>Heavenly Bloom brings you lovingly crafted floral arrangements, tailored to your unique moments — beautifully designed and effortlessly customizable.</p>
                        <a href="#features" class="main-button-slider">Discover More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ***** Header Text End ***** -->
    </div>
    <!-- ***** Welcome Area End ***** -->

    <!-- ***** Features Small Start ***** -->
    <section class="section home-feature">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <!-- ***** Features Small Item Start ***** -->
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">
                            <div class="features-small-item">
                                <div class="icon">
                                    <i><img src="assets/images/featured-item-01.png" alt=""></i>
                                </div>
                                <h5 class="features-title">Custom Bouquets</h5>
                                <p>Design your own floral arrangement — every bloom, every ribbon, just the way you want it.</p>
                            </div>
                        </div>
                        <!-- ***** Features Small Item End ***** -->
    
                        <!-- ***** Features Small Item Start ***** -->
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.4s">
                            <div class="features-small-item">
                                <div class="icon">
                                    <i><img src="assets/images/featured-item-01.png" alt=""></i>
                                </div>
                                <h5 class="features-title">Personal Touch</h5>
                                <p>Our team is here to help craft every order with care — feel free to message us for any request.</p>
                            </div>
                        </div>
                        <!-- ***** Features Small Item End ***** -->
    
                        <!-- ***** Features Small Item Start ***** -->
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.6s">
                            <div class="features-small-item">
                                <div class="icon">
                                    <i><img src="assets/images/featured-item-01.png" alt=""></i>
                                </div>
                                <h5 class="features-title">Moments That Bloom</h5>
                                <p>From birthdays to weddings, we make sure your flowers say exactly what your heart feels.</p>
                            </div>
                        </div>
                        <!-- ***** Features Small Item End ***** -->
                    </div>
                </div>
            </div>
    </div>
</section>
    <!-- ***** Features Small End ***** -->

    <!-- ***** Products Section Start ***** -->
    <section class="section padding-top-70 padding-bottom-0" id="products">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="center-heading">
                        <h2 class="section-title">Our Collection</h2>
                    </div>
                    <div class="center-text">
                        <p>Browse our beautiful selection of handcrafted floral arrangements</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Product Cards -->
            <?php
            $query = "SELECT * FROM products";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stock = isset($row['stock_count']) ? (int)$row['stock_count'] : 0;
            
                    echo '<div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">';
                    echo '<div class="product-card" data-id="' . $row['product_id'] . '"
                            data-name="' . htmlspecialchars($row['product_name']) . '"
                            data-price="' . $row['product_price'] . '"
                            data-description="' . htmlspecialchars($row['product_description']) . '"
                            data-image="uploads/' . htmlspecialchars($row['product_image']) . '">';
            
                    echo '<div class="product-image-wrapper" style="position: relative;">';
                    echo '<img src="uploads/' . htmlspecialchars($row['product_image']) . '" alt="' . htmlspecialchars($row['product_name']) . '" loading="lazy">';
            
                    // Show Sold Out label if stock is 0
                    if ($stock <= 0) {
                        echo '<div class="sold-out-label">Sold Out</div>';
                    }
            
                    echo '<div class="product-overlay">';
                    echo '<button class="view-product icon-button" data-id="' . $row['product_id'] . '" title="View Details">';
                    echo '<i class="fas fa-eye"></i>';
                    echo '</button>';
            
                    // Disable Add to Cart if stock is 0
                    if ($stock > 0) {
                        echo '<button class="add-to-cart-btn icon-button" data-product-id="' . $row['product_id'] . '" title="Add to Cart">';
                        echo '<i class="fas fa-cart-plus"></i>';
                        echo '</button>';
                    } else {
                        echo '<button class="add-to-cart-btn icon-button" title="Out of Stock" disabled style="opacity: 0.5; cursor: not-allowed;">';
                        echo '<i class="fas fa-cart-plus"></i>';
                        echo '</button>';
                    }
            
                    echo '</div>'; // End product-overlay
                    echo '</div>'; // End product-image-wrapper
            
                    echo '<div class="product-info">';
                    echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                    echo '<p class="product-price">₱' . number_format($row['product_price'], 2) . '</p>';
                    echo '</div>'; // End product-info
            
                    echo '</div>'; // End product-card
                    echo '</div>'; // End col
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No products available at the moment.</p></div>';
            }
            
            ?>
                </div>
        </div>
    </section>
    <!-- ***** Products Section End ***** -->

    <!-- ***** Product Modal ***** -->
    <div id="product-modal" class="modal" role="dialog" aria-labelledby="modal-name" aria-hidden="true">
        <div class="modal-content">
            <span class="close" aria-label="Close">&times;</span>
            <div class="row">
                <div class="col-md-6">
                    <div class="modal-image-container">
                        <img id="modal-image" src="" alt="Product Image">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="modal-product-details">
                        <h2 id="modal-name"></h2>
                        <p class="description" id="modal-description"></p>
                        <div class="price-container">
                            <p class="price">₱<span id="modal-price"></span></p>
                        </div>
                        <div class="modal-actions">
                            <form action="homepage.php" method="GET">
                                <input type="hidden" id="modal-add-to-cart" name="add">
                                <button type="submit" class="main-button">ADD TO CART</button>
                            </form>
                            <a href="checkout.php" class="main-button-secondary">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ***** Login Modal ***** -->
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="login-register-modal" id="loginModal" role="dialog" aria-labelledby="loginModalTitle" aria-hidden="true">
        <div class="login-register-content">
            <span class="close" onclick="document.getElementById('loginModal').style.display='none'" aria-label="Close">&times;</span>
            <h2 id="loginModalTitle" class="mb-3">Welcome to Heavenly Bloom</h2>
            <p class="mb-4">Please login or register to place your order</p>
            <div class="modal-buttons">
                <a href="user_login.php" class="btn-login">Login</a>
                <a href="user_register.php" class="btn-register">Register</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    

    <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script> 
    
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
    
    <!-- Custom JavaScript with combined functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dropdown toggle functionality - Fixed version
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const occasionDropdown = document.getElementById('occasionDropdown');
        
        if (dropdownToggle && occasionDropdown) {
            // Handle click on dropdown toggle
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent event from bubbling up
                
                // Toggle the dropdown visibility
                if (occasionDropdown.style.display === 'block') {
                    occasionDropdown.style.display = 'none';
                    dropdownToggle.classList.remove('active');
                } else {
                    occasionDropdown.style.display = 'block';
                    dropdownToggle.classList.add('active');
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownToggle.contains(e.target) && !occasionDropdown.contains(e.target)) {
                    occasionDropdown.style.display = 'none';
                }
            });
        }
        
        // Product modal functionality
        const productModal = document.getElementById('product-modal');
        const modalImage = document.getElementById('modal-image');
        const modalName = document.getElementById('modal-name');
        const modalDescription = document.getElementById('modal-description');
        const modalPrice = document.getElementById('modal-price');
        const modalAddToCart = document.getElementById('modal-add-to-cart');
        const closeBtn = productModal.querySelector('.close');
        
        // Regular product view buttons
        document.querySelectorAll('.view-product').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.product-card');
                
                // Set modal content with smooth loading
                modalImage.style.opacity = '0';
                modalName.style.opacity = '0';
                modalDescription.style.opacity = '0';
                modalPrice.style.opacity = '0';
                
                // Show the modal first for better transition
                productModal.style.display = 'block';
                
                // Load the image
                const img = new Image();
                img.onload = function() {
                    modalImage.src = this.src;
                    modalImage.style.opacity = '1';
                };
                img.src = card.dataset.image;
                
                // Set the other data with a small delay for smooth appearance
                setTimeout(() => {
                    modalName.textContent = card.dataset.name;
                    modalName.style.opacity = '1';
                    
                    // For description, use existing or provide a fallback if empty
                    let description = card.dataset.description;
                    if (!description || description.trim() === '') {
                        description = "Beautiful handcrafted arrangement of fresh flowers, perfect for any special occasion. Order now for a timely delivery to your loved ones.";
                    }
                    modalDescription.textContent = description;
                    modalDescription.style.opacity = '1';
                    
                    modalPrice.textContent = Number(card.dataset.price).toFixed(2);
                    modalPrice.style.opacity = '1';
                    
                    modalAddToCart.value = card.dataset.id;
                }, 100);
            });
        });
        
        // Close regular product modal
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                productModal.style.display = 'none';
            });
        }
        
        // Handle login modal
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            const loginCloseBtn = loginModal.querySelector('.close');
            
            loginCloseBtn.addEventListener('click', function() {
                loginModal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === loginModal) {
                    loginModal.style.display = 'none';
                }
            });
        }
        
        // Add to cart functionality
        function addToCart(productId) {
            // Check if user is logged in
            <?php if (!isset($_SESSION['user_id'])): ?>
                // Show login modal if user is not logged in
                document.getElementById('loginModal').style.display = 'flex';
                return;
            <?php else: ?>
                // Add to cart using AJAX
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        const cartCountElement = document.querySelector('.cart-link span');
                        if (cartCountElement) {
                            cartCountElement.textContent = data.cartCount;
                        }
                        
                        // Show success message
                        showNotification(data.message || 'Product added to cart successfully!', 'success');
                        
                        // Close modal if it's open
                        if (productModal.style.display === 'block') {
                            productModal.style.display = 'none';
                        }
                    } else {
                        showNotification(data.message || 'Failed to add product to cart. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again later.', 'error');
                });
            <?php endif; ?>
        }
        
        // Add event listeners to all add to cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                addToCart(productId);
            });
        });
        
        // Add event listener to modal add to cart button
        const modalAddToCartForm = document.querySelector('#product-modal form');
        if (modalAddToCartForm) {
            modalAddToCartForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const productId = modalAddToCart.value;
                addToCart(productId);
            });
        }
        
        // Notification function
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification ' + type;
            notification.textContent = message;
            
            // Add to body
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    });
    </script>
</body>
</html>

